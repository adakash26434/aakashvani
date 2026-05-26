<?php
require_once __DIR__ . '/config.php';

if (!defined('NOTICES_SYNC_INTERVAL')) define('NOTICES_SYNC_INTERVAL', 7200);

// ─── Database ─────────────────────────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    try {
        if (DB_DRIVER === 'sqlite') {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;');
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $opts = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            // FIX: Force utf8mb4 on every connection — prevents ?????? in Nepali text
            // even when server default_charset is latin1/utf8 (cPanel ko common issue)
            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
            }
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
            // Double-safety: force connection collation
            try { $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch(\Throwable $e) {}
            try { $pdo->exec("SET CHARACTER SET utf8mb4"); } catch(\Throwable $e) {}
        }
    } catch (PDOException $e) {
        error_log('DB connect failed: ' . $e->getMessage());
        http_response_code(500);
        die('<div style="font-family:system-ui;max-width:640px;margin:80px auto;padding:32px;border:1px solid #fecaca;border-radius:12px;background:#fff">
          <h1 style="margin:0 0 12px;font-size:22px;color:#b91c1c">Database connection failed</h1>
          <p style="color:#555;line-height:1.6">Check <code>config.php</code> credentials.<br>Error: ' . htmlspecialchars($e->getMessage()) . '</p>
        </div>');
    }
    return $pdo;
}

// ─── DB Compatibility Helpers (MySQL ↔ SQLite) ────────────────────────────────
function isMysql(): bool    { return DB_DRIVER === 'mysql'; }
function dbAI(): string     { return isMysql() ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT'; }
function dbIgnore(): string { return isMysql() ? 'INSERT IGNORE INTO' : 'INSERT OR IGNORE INTO'; }
function dbReplace(): string{ return isMysql() ? 'REPLACE INTO' : 'INSERT OR REPLACE INTO'; }
function dbCharset(): string { return isMysql() ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : ''; }
function dbIndex(string $n, string $t, string $c): void {
    try { db()->exec("CREATE INDEX IF NOT EXISTS {$n} ON {$t} ({$c})"); } catch (\Exception $e) {}
}

function ensureRashifalTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    $onUpd = isMysql() ? ' ON UPDATE CURRENT_TIMESTAMP' : '';
    db()->exec("CREATE TABLE IF NOT EXISTS rashifal_daily (
        id $ai,
        rashi_index INT NOT NULL,
        rashi_key VARCHAR(50) NOT NULL,
        date_bs VARCHAR(50) NOT NULL,
        date_ad DATE NOT NULL,
        lang VARCHAR(10) NOT NULL DEFAULT 'ne',
        payload LONGTEXT NOT NULL,
        source VARCHAR(50) NOT NULL DEFAULT 'fallback',
        is_ai_generated TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP$onUpd
    )" . dbCharset());
    dbIndex('idx_rashifal_lookup', 'rashifal_daily', 'rashi_index, date_ad, lang');
    dbIndex('idx_rashifal_date', 'rashifal_daily', 'date_ad');
    try { db()->exec("CREATE UNIQUE INDEX uq_rashifal_daily ON rashifal_daily (rashi_index, date_ad, lang)"); } catch (\Exception $e) {}
    $done = true;
}

function orderBySeverity(): string {
    return "CASE WHEN severity='critical' THEN 1 WHEN severity='high' THEN 2 WHEN severity='medium' THEN 3 ELSE 4 END";
}
function orderByImportance(): string {
    return "CASE WHEN importance='urgent' THEN 1 WHEN importance='important' THEN 2 ELSE 3 END";
}

// ─── Auth ─────────────────────────────────────────────────────────────────────
function isAdmin(): bool  { return !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['is_admin']); }
function requireAdmin(): void {
    if (!isAdmin()) { header('Location: /admin/'); exit; }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── API Response Helpers ─────────────────────────────────────────────────────
/**
 * Send consistent JSON success response
 */
function apiSuccess(array $data = [], string $message = ''): void {
    header('Content-Type: application/json; charset=utf-8');
    $response = ['ok' => true];
    if ($message) $response['message'] = $message;
    echo json_encode(array_merge($response, $data), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send consistent JSON error response
 */
function apiError(string $message, int $code = 400, array $extra = []): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    $response = ['ok' => false, 'error' => $message, 'code' => $code];
    echo json_encode(array_merge($response, $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Fetch URL with timeout and error handling
 */
function fetchUrl(string $url, int $timeout = 10, array $headers = []): ?string {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'header' => implode("\r\n", array_merge([
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ], $headers)),
            'follow_location' => true,
            'max_redirects' => 3,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);
    
    $result = @file_get_contents($url, false, $ctx);
    return $result !== false ? $result : null;
}

// ─── Security Headers ─────────────────────────────────────────────────────────
/**
 * Send recommended security headers
 */
function sendSecurityHeaders(): void {
    // Prevent XSS attacks
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Content Security Policy (flexible for now)
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com; " .
           "img-src 'self' data: https: blob:; " .
           "font-src 'self' https://fonts.gstatic.com; " .
           "connect-src 'self' https:; " .
           "frame-ancestors 'self';";
    header("Content-Security-Policy: " . $csp);
    
    // Permissions Policy
    header("Permissions-Policy: geolocation=(self), microphone=(), camera=()");
}

// ─── Rate Limiting ────────────────────────────────────────────────────────────
/**
 * Simple rate limiting using file-based storage
 * Returns true if allowed, false if rate limited
 */
function checkRateLimit(string $key, int $maxRequests = 60, int $windowSeconds = 60): bool {
    $dir = __DIR__ . '/data/ratelimit/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    
    $file = $dir . md5($key) . '.json';
    $now = time();
    $windowStart = $now - $windowSeconds;
    
    $requests = [];
    if (file_exists($file)) {
        $data = @json_decode(@file_get_contents($file), true);
        if (is_array($data)) {
            // Keep only requests within window
            $requests = array_filter($data, fn($t) => $t > $windowStart);
        }
    }
    
    if (count($requests) >= $maxRequests) {
        return false; // Rate limited
    }
    
    $requests[] = $now;
    @file_put_contents($file, json_encode(array_slice($requests, -$maxRequests)), LOCK_EX);
    
    return true;
}

/**
 * Get rate limit status for display
 */
function getRateLimitStatus(string $key, int $maxRequests = 60, int $windowSeconds = 60): array {
    $dir = __DIR__ . '/data/ratelimit/';
    $file = $dir . md5($key) . '.json';
    $now = time();
    $windowStart = $now - $windowSeconds;
    
    $requests = [];
    if (file_exists($file)) {
        $data = @json_decode(@file_get_contents($file), true);
        if (is_array($data)) {
            $requests = array_filter($data, fn($t) => $t > $windowStart);
        }
    }
    
    $remaining = max(0, $maxRequests - count($requests));
    $resetAt = count($requests) > 0 ? min($requests) + $windowSeconds : $now;
    
    return [
        'limit' => $maxRequests,
        'remaining' => $remaining,
        'reset' => $resetAt,
        'window' => $windowSeconds
    ];
}

/**
 * Market source badge helper for utilities.php
 */
function nh_marketSourceBadge(array $data, string $label): string {
    $source = $data['source'] ?? 'Unknown';
    $updated = $data['updatedAt'] ?? '';
    $timeStr = '';
    if ($updated) {
        $timeStr = ' · ' . (is_string($updated) ? substr($updated, 0, 16) : date('Y-m-d H:i', (int)$updated));
    }
    return '<div class="text-[10px] text-slate-400 mt-2 pt-2 border-t border-slate-100">' . htmlspecialchars($label) . ': ' . htmlspecialchars($source) . $timeStr . '</div>';
}

/**
 * Market unavailable block helper for utilities.php
 */
function nh_unavailableBlock(array $data, string $itemName): string {
    $source = $data['source'] ?? 'Unknown';
    $url = $data['source_url'] ?? '#';
    return '<div class="text-[11px] text-amber-600 mt-2 p-2 bg-amber-50 rounded-lg"><i data-lucide="alert-circle" class="w-3 h-3 inline-block mr-1"></i> ' . htmlspecialchars($itemName) . ' डेटा अहिले उपलब्ध छैन। <a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener" class="underline">आधिकारिक स्रोतमा हेर्नुहोस्</a></div>';
}

/**
 * UNIFIED TEXT TRUNCATION FOR NEPALI TEXT
 * Safely truncates UTF-8 text (Nepali + English) without mid-character cuts
 * Tries to cut at word/sentence boundaries, falls back to character limit
 */
function truncateText(string $text, int $maxChars = 250): string {
    $text = strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
    
    // Clean up RSS boilerplate
    $text = preg_replace('/The post .*? appeared first on .*?\.?$/iu', '', $text);
    $text = preg_replace('/\[\s*&#8230;\s*\]|\[\.\.\.\]/u', '', $text);
    $text = preg_replace('/(Read more|Continue reading|थप पढ्नुहोस्|विस्तृत.*$)/iu', '', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text);
    
    if (mb_strlen($text, 'UTF-8') <= $maxChars) {
        return $text;
    }
    
    // Try to cut at last sentence boundary (Nepali or English)
    $window = mb_substr($text, 0, $maxChars + 80, 'UTF-8');
    if (preg_match_all('/[।\.!\?]\s/u', $window, $m, PREG_OFFSET_CAPTURE)) {
        $lastPos = 0;
        foreach ($m[0] as $hit) {
            $bytePos = $hit[1] + strlen($hit[0]);
            $charPos = mb_strlen(substr($window, 0, $bytePos), 'UTF-8');
            if ($charPos <= $maxChars) {
                $lastPos = $charPos;
            }
        }
        if ($lastPos >= 80) {
            return rtrim(mb_substr($text, 0, $lastPos, 'UTF-8'));
        }
    }
    
    // Fall back: cut at word boundary
    $cut = mb_substr($text, 0, $maxChars, 'UTF-8');
    $sp = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($sp && $sp > 80) {
        $cut = mb_substr($cut, 0, $sp, 'UTF-8');
    }
    
    return rtrim($cut, " ,;:-…") . '…';
}

function formatNpr(int $amount): string { return 'रू ' . number_format($amount); }

/**
 * Format news content from database into readable HTML
 */
function formatNewsContent(string $content): string {
    if (!$content) return '';
    
    // If content already has HTML tags, clean and return
    if (preg_match('/<[a-z][\s\S]*>/i', $content)) {
        $clean = strip_tags($content, '<p><b><strong><i><em><h3><h4><ul><ol><li><blockquote><br><div><span>');
        // Convert double breaks to paragraphs
        $clean = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/i', '</p><p>', $clean);
        // Ensure content is wrapped in paragraphs
        if (!preg_match('/<p>/i', $clean)) {
            $clean = '<p>' . $clean . '</p>';
        }
        return $clean;
    }
    
    // Plain text → paragraphs with better formatting
    $paras = preg_split('/\n\n+|\r\n\r\n+/u', $content);
    $html = '';
    foreach ($paras as $p) {
        $p = trim($p);
        if (mb_strlen($p) > 5) {
            $html .= '<p class="leading-relaxed mb-4 text-gray-800">' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";
        }
    }
    
    return $html ?: '<p class="leading-relaxed text-gray-800">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</p>';
}

function waLink(string $message = ''): string {
    $base = 'https://wa.me/' . WHATSAPP_NO;
    return $message !== '' ? $base . '?text=' . rawurlencode($message) : $base;
}
function fbShare(string $url): string {
    return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($url);
}
function waShare(string $text): string {
    return 'https://wa.me/?text=' . rawurlencode($text);
}
function twitterShare(string $text, string $url): string {
    return 'https://twitter.com/intent/tweet?text=' . rawurlencode($text) . '&url=' . rawurlencode($url);
}

// ─── Language / i18n ──────────────────────────────────────────────────────────
function siteLang(): string {
    return ($_COOKIE['site_lang'] ?? 'ne') === 'en' ? 'en' : 'ne';
}
function t(string $ne, string $en): string {
    return siteLang() === 'en' ? $en : $ne;
}
function currentUrl(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return $text . '-' . time();
}
function redirect(string $url): void { header('Location: ' . $url); exit; }
function flash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}

// ─── Subscriptions ────────────────────────────────────────────────────────────
function ensureSubscriptionsTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS subscriptions (
        id                 $ai,
        name               VARCHAR(200) NOT NULL,
        category           VARCHAR(100) NOT NULL DEFAULT 'AI',
        description        LONGTEXT,
        price_npr          INT NOT NULL DEFAULT 0,
        original_price_npr INT,
        original_usd       VARCHAR(20),
        unit               VARCHAR(50) NOT NULL DEFAULT 'month',
        badge              VARCHAR(100),
        offer_label        VARCHAR(200),
        sort_order         INT NOT NULL DEFAULT 0,
        is_active          INT NOT NULL DEFAULT 1
    )" . dbCharset());
    // Seed default subscriptions if empty
    if ((int)db()->query('SELECT COUNT(*) FROM subscriptions')->fetchColumn() === 0) {
        $ins = db()->prepare(dbIgnore() . ' subscriptions (name,category,description,price_npr,original_price_npr,original_usd,unit,badge,offer_label,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $rows = [
            ['ChatGPT Plus','AI','OpenAI को GPT-4o, DALL-E 3 image generation, Advanced data analysis। Nepal मा NPR मा order गर्नुस्।',2499,null,'20','month','Bestseller','🔥 Most Popular',1],
            ['Microsoft 365 Family','Productivity','Word, Excel, PowerPoint, Teams, 6TB OneDrive। 6 users सम्म use गर्न सकिन्छ।',5999,null,'99','year','Best Value',null,2],
            ['Canva Pro','Design','Unlimited design templates, Background remover, Magic AI tools, Brand kit। Designers को लागि।',1499,null,'13','month',null,null,3],
            ['GitHub Copilot','Developer','AI coding assistant — VS Code, JetBrains, Neovim। PHP, JavaScript, Python developers को लागि।',1199,null,'10','month',null,null,4],
            ['Netflix Premium','Entertainment','4K UHD + HDR, 4 screens एकैचोटि, Ultra HD quality। Family को लागि ideal।',1899,null,'15.5','month',null,null,5],
            ['Adobe Creative Cloud','Design','Photoshop, Illustrator, Premiere Pro, After Effects — सबै apps।',6999,null,'54.99','month','Pro',null,6],
            ['Grammarly Premium','Productivity','AI writing assistant — Grammar check, Plagiarism detector, Tone detection। Students को लागि।',999,null,'12','month',null,null,7],
            ['Notion Plus','Productivity','Unlimited pages, Unlimited AI, Team collaboration। Startups र freelancers को लागि।',799,null,'10','month',null,null,8],
            ['YouTube Premium','Entertainment','Ad-free YouTube, Background play, YouTube Music। Nepal मा officially available छैन।',699,null,'7','month',null,null,9],
            ['Spotify Premium','Entertainment','Ad-free music, Offline download, High quality audio। 100M+ songs।',499,null,'11','month',null,'🎵 Music Special',10],
            ['1Password Family','Security','Password manager, Secure vault, 5 family members। Digital security को लागि।',1299,null,'4.99','month',null,null,11],
            ['LinkedIn Premium','Professional','InMail credits, Who viewed your profile, Learning courses। Job seekers को लागि।',2999,null,'29.99','month',null,null,12],
        ];
        foreach ($rows as $r) $ins->execute($r);
    }
    $done = true;
}
function getActiveSubscriptions(): array {
    ensureSubscriptionsTable();
    return db()->query('SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY sort_order ASC')->fetchAll();
}
function getAllSubscriptions(): array {
    ensureSubscriptionsTable();
    return db()->query('SELECT * FROM subscriptions ORDER BY sort_order ASC')->fetchAll();
}

// ─── Blog / Travel Vlog ───────────────────────────────────────────────────────
function ensureBlogTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id           $ai,
        title        VARCHAR(500) NOT NULL,
        slug         VARCHAR(191) NOT NULL UNIQUE,
        district     VARCHAR(200),
        province     VARCHAR(100),
        excerpt      LONGTEXT,
        content      LONGTEXT NOT NULL,
        image_url    VARCHAR(500),
        is_published INT NOT NULL DEFAULT 1,
        views        INT NOT NULL DEFAULT 0,
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_blog_pub', 'blog_posts', 'is_published, created_at');
    // Seed sample posts
    if ((int)db()->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn() === 0) {
        $ins = db()->prepare(dbIgnore() . ' blog_posts (title,slug,district,province,excerpt,content,image_url,is_published,created_at) VALUES (?,?,?,?,?,?,?,1,?)');
        $posts = [
            ['पोखरा — फेवातालको किनारमा','pokhara-fewa-lake-visit','Kaski','Gandaki',
             'Nepal को सबैभन्द��� सुन्दर शहर पोखरामा फेवाताल, सरङ्गकोट र Peace Stupa को अनुभव।',
             '<h2>पोखरा सहर</h2><p>Gandaki प्रदेशको मुख्य शहर पोखरा Nepal को tourism capital हो। फेवातालको सुन्दर दृश्य, Annapurna range को नजिक पहुँच र शान्त वातावरणले पोखरालाई special बनाउँछ।</p><h2>फेवाताल</h2><p>Nepal को दोस्रो ठूलो lake फेवाताल ४.४ km² क्षेत्रफल ओगटेको छ। Barahi Temple को island मा डुङ्गाबाट जान सकिन्छ।</p><h2>सरङ्गकोट</h2><p>Sunrise र Himalaya को view को लागि Sarangkot नजिकैको पहाड हो। Annapurna, Dhaulagiri र Machhapuchchhre को panoramic view पाइन्छ।</p>',
             '/assets/images/pokhara.jpg','2082-02-15 08:00:00'],
            ['काठमाडौं — इतिहास र आधुनिकताको संगम','kathmandu-heritage-walk','Kathmandu','Bagmati',
             'Pashupatinath, Boudhanath, Swayambhunath — काठमाडौं उपत्यकाका UNESCO World Heritage Sites।',
             '<h2>काठमाडौं उपत्यका</h2><p>Nepal को राजधानी काठमाडौं उपत्यकामा ७ वटा UNESCO World Heritage Sites छन्। यहाँ इतिहास र आधुनिकता मिलेर बसेका छन्।</p><h2>Pashupatinath</h2><p>Bagmati नदीको किनारमा रहेको Pashupatinath Temple Hindu र Buddhist दुवैका लागि पवित्र स्थल हो।</p><h2>Boudhanath</h2><p>विश्वकै ठूला Stupa मध्ये एक Boudhanath — Tibetan Buddhism को केन्द्र। यसको mandala design र prayer flags को दृश्य अनौठो छ।</p>',
             '/assets/images/kathmandu.jpg','2082-01-20 10:00:00'],
            ['चितवन — Wild Nepal को अनुभव','chitwan-national-park-safari','Chitwan','Bagmati',
             'Chitwan National Park मा Rhino, Tiger र Elephant को safari — Nepal को wildlife paradise।',
             '<h2>Chitwan National Park</h2><p>UNESCO World Heritage Site Chitwan Nepal को पहिलो national park हो। यहाँ One-horned Rhinoceros, Royal Bengal Tiger, Gharial Crocodile र Gangetic Dolphin पाइन्छन्।</p><h2>Elephant Safari</h2><p>Elephant back safari मा jungle मा घुम्दा Rhino र Deer को नजिक पुग्न सकिन्छ।</p><h2>Tharu Culture</h2><p>Tharu जनजातिको सांस्कृतिक कार्यक्रम र Tharu village tour Chitwan trip को अर्को highlight हो।</p>',
             '/assets/images/chitwan.jpg','2081-12-05 07:30:00'],
        ];
        foreach ($posts as $p) $ins->execute($p);
    }
    $done = true;
}
function getPublishedPosts(int $limit = 100): array {
    ensureBlogTable();
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY created_at DESC LIMIT ?');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
function getAllPosts(): array {
    ensureBlogTable();
    return db()->query('SELECT * FROM blog_posts ORDER BY created_at DESC')->fetchAll();
}
function getPostBySlug(string $slug): ?array {
    ensureBlogTable();
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

// ─── Gallery / Contact ────────────────────────────────────────────────────────
function getGalleryPhotos(): array {
    try {
        $ai = dbAI();
        db()->exec("CREATE TABLE IF NOT EXISTS gallery ($ai, url VARCHAR(500), caption VARCHAR(500), sort_order INT DEFAULT 0)" . dbCharset());
        return db()->query('SELECT * FROM gallery ORDER BY sort_order ASC')->fetchAll();
    } catch (Exception $e) { return []; }
}
function getUnreadMessages(): int {
    try {
        $ai = dbAI();
        db()->exec("CREATE TABLE IF NOT EXISTS contact_messages ($ai, name VARCHAR(200), email VARCHAR(200), message LONGTEXT, is_read INT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)" . dbCharset());
        return (int) db()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
    } catch (Exception $e) { return 0; }
}

// ─── Tech News Table ──────────────────────────────────────────────────────────
function ensureNewsTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    $onUpd = isMysql() ? ' ON UPDATE CURRENT_TIMESTAMP' : '';
    db()->exec("CREATE TABLE IF NOT EXISTS tech_news (
        id           $ai,
        title        VARCHAR(500) NOT NULL,
        slug         VARCHAR(191) NOT NULL UNIQUE,
        category     VARCHAR(100) NOT NULL DEFAULT 'General',
        source       VARCHAR(200),
        source_url   VARCHAR(500),
        excerpt      LONGTEXT,
        content      LONGTEXT,
        image_url    VARCHAR(500),
        is_published INT NOT NULL DEFAULT 1,
        is_featured  TINYINT(1) NOT NULL DEFAULT 0,
        is_breaking  TINYINT(1) NOT NULL DEFAULT 0,
        ai_processed TINYINT(1) NOT NULL DEFAULT 0,
        url_hash     CHAR(32) DEFAULT NULL,
        source_name  VARCHAR(100) DEFAULT NULL,
        original_url VARCHAR(1000) DEFAULT NULL,
        content_status VARCHAR(20) NOT NULL DEFAULT 'unknown',
        content_length INT NOT NULL DEFAULT 0,
        scrape_status VARCHAR(20) NOT NULL DEFAULT 'pending',
        scrape_error TEXT,
        last_scraped_at DATETIME DEFAULT NULL,
        published_at DATETIME DEFAULT NULL,
        views        INT NOT NULL DEFAULT 0,
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP$onUpd
    )" . dbCharset());
    dbIndex('idx_news_pub', 'tech_news', 'is_published, created_at');
    dbIndex('idx_news_cat', 'tech_news', 'category');
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN source_url VARCHAR(500)"); } catch(\Exception $e) {}
    // lang: 'ne' = Nepali, 'en' = English | scope: 'national' | 'international'
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN lang VARCHAR(10) NOT NULL DEFAULT 'en'"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN scope VARCHAR(20) NOT NULL DEFAULT 'international'"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN content_status VARCHAR(20) NOT NULL DEFAULT 'unknown'"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN content_length INT NOT NULL DEFAULT 0"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN scrape_status VARCHAR(20) NOT NULL DEFAULT 'pending'"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN scrape_error TEXT"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN last_scraped_at DATETIME DEFAULT NULL"); } catch(\Exception $e) {}
    try { db()->exec("ALTER TABLE tech_news ADD COLUMN published_at DATETIME DEFAULT NULL"); } catch(\Exception $e) {}
    dbIndex('idx_news_lang',  'tech_news', 'lang');
    dbIndex('idx_news_scope', 'tech_news', 'scope');
    dbIndex('idx_news_hash', 'tech_news', 'url_hash');
    dbIndex('idx_news_source_date', 'tech_news', 'source_name, created_at');
    dbIndex('idx_news_quality', 'tech_news', 'content_status, scrape_status');
    $done = true;
}

function getPublishedNews(?string $category = null, ?string $q = null, int $limit = 24, int $offset = 0, ?string $lang = null, ?string $scope = null): array {
    ensureNewsTable();
    $sql = 'SELECT * FROM tech_news WHERE is_published = 1';
    $params = [];
    if ($category) { $sql .= ' AND category = ?'; $params[] = $category; }
    if ($lang)     { $sql .= ' AND lang = ?';     $params[] = $lang; }
    if ($scope)    { $sql .= ' AND scope = ?';    $params[] = $scope; }
    if ($q) {
        $sql .= ' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like);
    }
    $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countPublishedNews(?string $category = null, ?string $q = null, ?string $lang = null, ?string $scope = null): int {
    ensureNewsTable();
    $sql = 'SELECT COUNT(*) FROM tech_news WHERE is_published = 1';
    $params = [];
    if ($category) { $sql .= ' AND category = ?'; $params[] = $category; }
    if ($lang)     { $sql .= ' AND lang = ?';     $params[] = $lang; }
    if ($scope)    { $sql .= ' AND scope = ?';    $params[] = $scope; }
    if ($q) {
        $sql .= ' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like);
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getNewsCategoriesFiltered(?string $lang = null, ?string $scope = null): array {
    ensureNewsTable();
    $sql = "SELECT category, COUNT(*) c FROM tech_news WHERE is_published = 1";
    $params = [];
    if ($lang)  { $sql .= ' AND lang = ?';  $params[] = $lang; }
    if ($scope) { $sql .= ' AND scope = ?'; $params[] = $scope; }
    $sql .= " GROUP BY category ORDER BY c DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getNewsBySlug(string $slug): ?array {
    ensureNewsTable();
    $stmt = db()->prepare('SELECT * FROM tech_news WHERE slug = ? AND is_published = 1');
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    if ($r) db()->prepare('UPDATE tech_news SET views = views + 1 WHERE id = ?')->execute([$r['id']]);
    return $r ?: null;
}

function getNewsCategories(): array {
    return getNewsCategoriesFiltered();
}

function getRelatedNews(int $id, string $category, int $limit = 4): array {
    ensureNewsTable();
    $stmt = db()->prepare('SELECT * FROM tech_news WHERE is_published = 1 AND id <> ? AND category = ? ORDER BY created_at DESC LIMIT ' . (int)$limit);
    $stmt->execute([$id, $category]);
    return $stmt->fetchAll();
}

function getAllNews(): array {
    ensureNewsTable();
    return db()->query('SELECT * FROM tech_news ORDER BY created_at DESC')->fetchAll();
}

// ─── AI Auto-Sync: News RSS Fetcher ──────────────────────────────────────────
// Fetches from 12+ sources — Nepal tech, global AI, startup, gadget
function fetchAndCacheRssFeeds(): void {
    ensureNewsTable();

    $feeds = [
        // ── Nepal Tech — Nepali language ─────────────────────────────────────
        ['url' => 'https://techpana.com/feed/',            'source' => 'TechPana',       'category' => 'Nepal Tech', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://techlekha.com/feed/',           'source' => 'TechLekha',      'category' => 'Nepal Tech', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://techsansar.com/feed/',          'source' => 'TechSansar',     'category' => 'Nepal Tech', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://nepalitelecom.com/feed/',       'source' => 'NepaliTelecom',  'category' => 'Nepal Tech', 'lang' => 'ne', 'scope' => 'national'],
        // ── Nepal General News — Nepali language ─────────────────────────────
        ['url' => 'https://www.ratopati.com/feed',         'source' => 'Ratopati',       'category' => 'Nepal News', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://www.onlinekhabar.com/feed',     'source' => 'OnlineKhabar',   'category' => 'Nepal News', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://ekantipur.com/feed',            'source' => 'Ekantipur',      'category' => 'Nepal News', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://www.setopati.com/feed',         'source' => 'Setopati',       'category' => 'Nepal News', 'lang' => 'ne', 'scope' => 'national'],
        ['url' => 'https://nagariknews.nagariknetwork.com/feed', 'source' => 'Nagarik',  'category' => 'Nepal News', 'lang' => 'ne', 'scope' => 'national'],
        // ── Nepal News — English language ─────────────────────────────────────
        ['url' => 'https://english.onlinekhabar.com/feed', 'source' => 'OnlineKhabar EN','category' => 'Nepal News', 'lang' => 'en', 'scope' => 'national'],
        ['url' => 'https://www.nepalnews.com/feed/',       'source' => 'NepalNews',      'category' => 'Nepal News', 'lang' => 'en', 'scope' => 'national'],
        ['url' => 'https://myrepublica.nagariknetwork.com/feed/', 'source' => 'MyRepublica', 'category' => 'Nepal News', 'lang' => 'en', 'scope' => 'national'],
        ['url' => 'https://thehimalayantimes.com/feed/',   'source' => 'Himalayan Times','category' => 'Nepal News', 'lang' => 'en', 'scope' => 'national'],
        // ── Global AI & Tech — English ────────────────────────────────────────
        ['url' => 'https://feeds.feedburner.com/TechCrunch', 'source' => 'TechCrunch',  'category' => 'AI',         'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://venturebeat.com/category/ai/feed/', 'source' => 'VentureBeat','category' => 'AI',         'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://www.theverge.com/ai-artificial-intelligence/rss/index.xml', 'source' => 'The Verge', 'category' => 'AI', 'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://feeds.arstechnica.com/arstechnica/index', 'source' => 'Ars Technica', 'category' => 'Tech', 'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://www.wired.com/feed/rss',        'source' => 'Wired',          'category' => 'Tech',       'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://rss.nytimes.com/services/xml/rss/nyt/Technology.xml', 'source' => 'NYT Tech', 'category' => 'Tech', 'lang' => 'en', 'scope' => 'international'],
        // ── Startups & Finance — English ──────────────────────────────────────
        ['url' => 'https://techcrunch.com/category/startups/feed/', 'source' => 'TechCrunch', 'category' => 'Startup', 'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://feeds.bloomberg.com/technology/news.rss', 'source' => 'Bloomberg Tech', 'category' => 'Finance', 'lang' => 'en', 'scope' => 'international'],
        // ── Mobile & Gadgets — English ────────────────────────────────────────
        ['url' => 'https://www.gsmarena.com/rss-news-reviews.php3', 'source' => 'GSMArena', 'category' => 'Mobile', 'lang' => 'en', 'scope' => 'international'],
        ['url' => 'https://9to5mac.com/feed/',              'source' => '9to5Mac',        'category' => 'Mobile',     'lang' => 'en', 'scope' => 'international'],
    ];

    $insertSQL = dbIgnore() . ' tech_news (title, slug, category, source, source_url, excerpt, content, image_url, is_published, lang, scope, created_at) VALUES (?,?,?,?,?,?,?,?,1,?,?,?)';

    foreach ($feeds as $feed) {
        try {
            $ctx = stream_context_create(['http' => [
                'timeout'    => 6,
                'user_agent' => 'Mozilla/5.0 (compatible; NepaliSmartHub/1.0; +https://tankaadhikari.com.np)',
                'follow_location' => 1,
            ]]);
            $xml = @file_get_contents($feed['url'], false, $ctx);
            if (!$xml) continue;

            // Handle both RSS 2.0 and Atom feeds
            $rss = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$rss) continue;

            $items = [];
            if (isset($rss->channel->item)) {
                foreach ($rss->channel->item as $item) $items[] = $item;
            } elseif (isset($rss->entry)) {
                foreach ($rss->entry as $entry) $items[] = $entry;
            }

            foreach (array_slice($items, 0, 20) as $item) {
                $title   = trim(strip_tags((string)($item->title ?? '')));
                $link    = trim((string)($item->link ?? $item->guid ?? ''));
                if (is_object($link)) {
                    $attrs = $link->attributes();
                    $link = (string)($attrs['href'] ?? '');
                }
                $pubDate = trim((string)($item->pubDate ?? $item->updated ?? $item->published ?? ''));
                $desc    = '';

                // Try multiple description fields — prefer content:encoded (full article from WordPress feeds)
                $rawContent = '';
                if (isset($ns['content'])) {
                    $contentNs = $item->children($ns['content']);
                    if (!empty($contentNs->encoded)) $rawContent = (string)$contentNs->encoded;
                }
                if (!$rawContent) {
                    if (!empty($item->description))   $rawContent = (string)$item->description;
                    elseif (!empty($item->summary))   $rawContent = (string)$item->summary;
                    elseif (!empty($item->content))   $rawContent = (string)$item->content;
                }
                // Strip HTML for excerpt, keep cleaned HTML for content display
                $descPlain = trim(html_entity_decode(strip_tags($rawContent), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                // Remove consecutive whitespace
                $descPlain = preg_replace('/\s+/', ' ', $descPlain);
                $desc = $descPlain;

                // Extract image from media:content or enclosure
                $imgUrl = '';
                $ns = $item->getNamespaces(true);
                if (isset($ns['media'])) {
                    $media = $item->children($ns['media']);
                    if (isset($media->content)) {
                        $attrs = $media->content->attributes();
                        $imgUrl = (string)($attrs['url'] ?? '');
                    }
                }
                if (!$imgUrl && isset($item->enclosure)) {
                    $attrs = $item->enclosure->attributes();
                    $type = (string)($attrs['type'] ?? '');
                    if (str_starts_with($type, 'image/')) $imgUrl = (string)($attrs['url'] ?? '');
                }

                if (!$title || !$link) continue;

                $slug    = preg_replace('/[^a-z0-9-]/', '-', strtolower(substr($title, 0, 80))) . '-' . abs(crc32($link));
                $slug    = preg_replace('/-+/', '-', trim($slug, '-'));
                $date    = $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : date('Y-m-d H:i:s');
                $excerpt = mb_substr($descPlain, 0, 600);
                // Store full plain text (or HTML if available) as content
                $fullContent = $rawContent ? trim(html_entity_decode($rawContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : $descPlain;
                $content = $fullContent ?: $title;

                $stmt = db()->prepare($insertSQL);
                $stmt->execute([$title, $slug, $feed['category'], $feed['source'], $link, $excerpt, $content, $imgUrl, $feed['lang'] ?? 'en', $feed['scope'] ?? 'international', $date]);
                // Note: $content here is the full/rich content stored for article display
            }
        } catch (Exception $e) {
            error_log('[NSH] RSS fetch error ' . $feed['url'] . ': ' . $e->getMessage());
        }
    }
}

function maybeRefreshNews(): void {
    $flag = sys_get_temp_dir() . '/nsh_rss_last.txt';
    $last = @file_get_contents($flag);
    if (!$last || (time() - (int)$last) > NEWS_SYNC_INTERVAL) {
        fetchAndCacheRssFeeds();
        @file_put_contents($flag, time());
    }
}

// ─── Notices Table & AI Auto-Sync ────────────────────────────────────────────
function ensureNoticesTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS notices (
        id           $ai,
        title        VARCHAR(500) NOT NULL,
        category     VARCHAR(100) NOT NULL DEFAULT 'General',
        source       VARCHAR(200),
        source_url   VARCHAR(500),
        content      LONGTEXT NOT NULL,
        importance   VARCHAR(50) NOT NULL DEFAULT 'normal',
        is_published INT NOT NULL DEFAULT 1,
        expires_at   DATETIME,
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_notices_pub', 'notices', 'is_published, created_at');
    try { db()->exec("ALTER TABLE notices ADD COLUMN source_url VARCHAR(500)"); } catch(\Exception $e) {}
    dbIndex('idx_notices_cat', 'notices', 'category');
    $done = true;
}

function seedDefaultNotices(): void {
    ensureNoticesTable();
    if ((int)db()->query('SELECT COUNT(*) FROM notices')->fetchColumn() > 0) return;
    $notices = [
        ['Nepal Rastra Bank — नयाँ ब्याजदर २०८१/०८२','Finance','NRB','Nepal Rastra Bank ले वाणिज्य बैंकहरूको अधिकतम ब्याजदर तोक्यो। व्यक्तिगत कर्जामा अधिकतम १२% र गृह कर्जामा अधिकतम ११% ब्याज लाग्नेछ।','important'],
        ['ChatGPT Nepal मा Free — GPT-4o mini उपलब्ध','Tech','OpenAI','OpenAI ले नेपाल लगायत विश्वभरि ChatGPT को free tier मा GPT-4o mini model उपलब्ध गराएको छ। अब free account बाट पनि powerful AI प्रयोग ग���्न सकिन्छ।','normal'],
        ['eSewa — नयाँ Transaction Limit बढाइयो','Finance','eSewa','eSewa ले दैनिक transaction limit रू. २ लाखबाट रू. ५ लाख पुर्‍यायो।','normal'],
        ['Nepal Telecom — Fiber Internet Price घट्यो','Tech','NTC','Nepal Telecom ले Fiber Home FTTH को मासिक दर घटाएको छ। ५०Mbps को लागि अब रू. ९९९ मात्र।','important'],
        ['GitHub Copilot — Students लाई Free','Tech','GitHub','GitHub ले सबै verified students लाई Copilot Pro Free दिने घोषणा गर्‍यो। github.com/education मा apply गर्नुस्।','normal'],
        ['Loksewa — नयाँ Vacancy 2082','Government','PSC','लोकसेवा आयोगले विभिन्न सरकारी निकायमा ३,२०० भन्दा बढी प��का लागि विज्ञापन प्रकाशित गरेको छ।','urgent'],
        ['Passport — Renewal Online भएको छ','Government','DoFP','नेपाल सरकारले Passport renewal को लागि online appointment system सुरु गरेको छ।','important'],
        ['Khalti — नयाँ Cashback Offer','Finance','Khalti','Khalti ले सबै payment मा ५% सम्म cashback दिने offer ल्याएको छ।','normal'],
    ];
    $stmt = db()->prepare('INSERT INTO notices (title, category, source, content, importance) VALUES (?,?,?,?,?)');
    foreach ($notices as $n) $stmt->execute($n);
}

// AI Auto-Sync: Fetch notices from Nepal news RSS feeds (finance, gov, tech)
function fetchAndCacheNotices(): void {
    ensureNoticesTable();

    $feeds = [
        // Nepal General & Government
        ['url' => 'https://english.onlinekhabar.com/feed',          'source' => 'OnlineKhabar',    'source_web' => 'https://english.onlinekhabar.com', 'category' => 'Nepal News'],
        ['url' => 'https://www.nepalnews.com/feed/',                 'source' => 'NepalNews',        'source_web' => 'https://www.nepalnews.com',         'category' => 'Government'],
        ['url' => 'https://myrepublica.nagariknetwork.com/feed/',    'source' => 'MyRepublica',      'source_web' => 'https://myrepublica.nagariknetwork.com', 'category' => 'Government'],
        ['url' => 'https://risingnepaldaily.com/feed/',              'source' => 'The Rising Nepal', 'source_web' => 'https://risingnepaldaily.com',       'category' => 'Government'],
        ['url' => 'https://kathmandupost.com/feed',                  'source' => 'Kathmandu Post',   'source_web' => 'https://kathmandupost.com',          'category' => 'Nepal News'],
        // Finance
        ['url' => 'https://sharesansar.com/feed',                   'source' => 'ShareSansar',      'source_web' => 'https://sharesansar.com',            'category' => 'Finance'],
        ['url' => 'https://merolagani.com/feed.aspx',               'source' => 'MeroLagani',       'source_web' => 'https://merolagani.com',             'category' => 'Finance'],
        // Tech
        ['url' => 'https://techsansar.com/feed/',                   'source' => 'TechSansar',       'source_web' => 'https://techsansar.com',             'category' => 'Tech'],
        ['url' => 'https://techlekha.com/feed/',                    'source' => 'TechLekha',        'source_web' => 'https://techlekha.com',              'category' => 'Tech'],
        ['url' => 'https://techpana.com/feed/',                     'source' => 'TechPana',         'source_web' => 'https://techpana.com',               'category' => 'Tech'],
    ];

    // Keywords that indicate notices/announcements
    $noticeKeywords = [
        'notice','announcement','vacancy','circular','update','tender','alert','deadline',
        'important','bid','recruitment','सूचना','विज्ञापन','रिक्त','परिपत्र','अपडेट',
        'नियुक्ति','बोलपत्र','विज्ञप्ति','आवेदन','घोषणा',
    ];

    $insert = db()->prepare(dbIgnore() . ' notices (title, category, source, source_url, content, importance, created_at) VALUES (?,?,?,?,?,?,?)');

    foreach ($feeds as $feed) {
        try {
            $ctx = stream_context_create(['http' => [
                'timeout'        => 7,
                'user_agent'     => 'Mozilla/5.0 (compatible; NepaliSmartHub/2.0)',
                'follow_location'=> 1,
            ]]);
            $xml = @file_get_contents($feed['url'], false, $ctx);
            if (!$xml) continue;
            $rss = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$rss) continue;

            $items = [];
            if (isset($rss->channel->item)) {
                foreach ($rss->channel->item as $it) $items[] = $it;
            } elseif (isset($rss->entry)) {
                foreach ($rss->entry as $it) $items[] = $it;
            }

            foreach (array_slice($items, 0, 10) as $item) {
                if (!is_object($item)) continue;
                $title   = trim(strip_tags((string)($item->title ?? '')));
                $desc    = trim(strip_tags((string)($item->description ?? $item->summary ?? '')));
                $link    = trim((string)($item->link ?? $item->guid ?? ''));
                if (is_object($link)) { $a = $link->attributes(); $link = (string)($a['href'] ?? ''); }
                $pubDate = trim((string)($item->pubDate ?? $item->updated ?? ''));
                if (!$title) continue;

                // Only import if it looks like a notice/announcement
                $check = mb_strtolower($title . ' ' . $desc);
                $isNotice = false;
                foreach ($noticeKeywords as $kw) {
                    if (mb_strpos($check, $kw) !== false) { $isNotice = true; break; }
                }
                if (!$isNotice) continue;

                $importance = 'normal';
                if (preg_match('/urgent|तत्काल|critical|आपतकाल/i', $title)) $importance = 'urgent';
                elseif (preg_match('/notice|vacancy|deadline|circular|सूचना|रिक्त|महत्त्वपूर्ण/i', $title)) $importance = 'important';

                $date       = $pubDate ? date('Y-m-d H:i:s', strtotime($pubDate)) : date('Y-m-d H:i:s');
                $content    = $desc ?: $title;
                $sourceUrl  = $link ?: $feed['source_web'];
                $insert->execute([$title, $feed['category'], $feed['source'], $sourceUrl, $content, $importance, $date]);
            }
        } catch (Exception $e) {
            error_log('[NSH] Notices fetch error ' . $feed['url'] . ': ' . $e->getMessage());
        }
    }
}

function maybeRefreshNotices(): void {
    $flag = sys_get_temp_dir() . '/nsh_notices_last.txt';
    $last = @file_get_contents($flag);
    if (!$last || (time() - (int)$last) > NOTICES_SYNC_INTERVAL) {
        fetchAndCacheNotices();
        @file_put_contents($flag, time());
    }
}

function getPublishedNotices(?string $category = null, ?string $importance = null): array {
    ensureNoticesTable();
    $sql = "SELECT * FROM notices WHERE is_published = 1 AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)";
    $params = [];
    if ($category)   { $sql .= ' AND category = ?';   $params[] = $category; }
    if ($importance) { $sql .= ' AND importance = ?';  $params[] = $importance; }
    $sql .= ' ORDER BY ' . orderByImportance() . ', created_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getNoticeCategories(): array {
    ensureNoticesTable();
    return db()->query("SELECT category, COUNT(*) c FROM notices WHERE is_published=1 GROUP BY category ORDER BY c DESC")->fetchAll();
}

function getAllNotices(): array {
    ensureNoticesTable();
    return db()->query('SELECT * FROM notices ORDER BY created_at DESC')->fetchAll();
}

// ─── Alerts Table & AI Auto-Sync ─────────────────────────────────────────────
function ensureAlertsTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS alerts (
        id           $ai,
        title        VARCHAR(500) NOT NULL,
        category     VARCHAR(100) NOT NULL DEFAULT 'General',
        district     VARCHAR(300),
        severity     VARCHAR(50) NOT NULL DEFAULT 'medium',
        content      LONGTEXT NOT NULL,
        source       VARCHAR(200),
        is_active    INT NOT NULL DEFAULT 1,
        expires_at   DATETIME,
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_alerts_active', 'alerts', 'is_active, created_at');
    dbIndex('idx_alerts_sev', 'alerts', 'severity');
    $done = true;
}

/**
 * seedAlerts: NO-OP.
 * Earlier versions seeded fabricated "monsoon / loadshedding / dengue" rows.
 * Those were fake and dangerous (users could believe a critical alert that
 * never came from DHM/NEA). Real alerts now come from:
 *   - fetchAndCacheEarthquakeAlerts() → USGS (live)
 *   - fetchAndCacheWeatherAlerts()    → DHM-derived via Open-Meteo (live)
 *   - admin-created entries via /admin
 * If the alerts table is empty, the UI shows an honest "no active alerts".
 */
function seedAlerts(): void {
    ensureAlertsTable();
    // intentionally empty — never insert fabricated alerts.
}

/**
 * Fetch live severe-weather alerts for Nepal from Open-Meteo (DHM-fed model).
 * Inserts only when a heavy-rain / heat / wind condition is present.
 */
function fetchAndCacheWeatherAlerts(): void {
    ensureAlertsTable();
    // Major-city sample; expand as needed.
    $cities = [
        ['काठमाडौं', 27.7172, 85.3240],
        ['पोखरा',   28.2096, 83.9856],
        ['विराटनगर', 26.4525, 87.2718],
        ['नेपालगन्ज', 28.0500, 81.6167],
        ['धनगढी',  28.6833, 80.6000],
    ];
    $check  = db()->prepare('SELECT id FROM alerts WHERE title = ? AND source = ? LIMIT 1');
    $insert = db()->prepare('INSERT INTO alerts (title, category, district, severity, content, source, is_active, expires_at, created_at) VALUES (?,?,?,?,?,?,1,?,?)');

    foreach ($cities as [$name, $lat, $lon]) {
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}"
             . "&daily=precipitation_sum,temperature_2m_max,wind_speed_10m_max"
             . "&timezone=Asia%2FKathmandu&forecast_days=1";
        $ctx = stream_context_create(['http'=>['timeout'=>6,'user_agent'=>'Mozilla/5.0']]);
        $raw = @file_get_contents($url, false, $ctx);
        if (!$raw) continue;
        $j = json_decode($raw, true);
        $rain = (float)($j['daily']['precipitation_sum'][0] ?? 0);
        $tmax = (float)($j['daily']['temperature_2m_max'][0] ?? 0);
        $wind = (float)($j['daily']['wind_speed_10m_max'][0] ?? 0);
        $when = $j['daily']['time'][0] ?? date('Y-m-d');

        if ($rain >= 50) {
            $sev = $rain >= 100 ? 'critical' : 'high';
            $title = "भारी वर्षा चेतावनी — {$name} ({$rain}mm)";
            $content = "मिति: {$when}\nअनुमानित वर्षा: {$rain} mm\n\nस्रोत: Open-Meteo / DHM model\n\n⚠️ निचो ठाउँ, नदी किनारबाट टाढा रहनुस्। आपतकालीन: 1149";
            $check->execute([$title, 'Open-Meteo']);
            if (!$check->fetchColumn()) {
                $insert->execute([$title, 'Weather', $name, $sev, $content, 'Open-Meteo',
                                  date('Y-m-d H:i:s', strtotime($when) + 86400),
                                  date('Y-m-d H:i:s')]);
            }
        }
        if ($tmax >= 40) {
            $title = "तातो लहर चेतावनी — {$name} ({$tmax}°C)";
            $content = "अधिकतम तापक्रम: {$tmax}°C\nमिति: {$when}\n\nस्रोत: Open-Meteo / DHM model\n\n⚠️ दिउँसो खुला घाममा नजानुस्, पानी पिउनुस्।";
            $check->execute([$title, 'Open-Meteo']);
            if (!$check->fetchColumn()) {
                $insert->execute([$title, 'Weather', $name, 'high', $content, 'Open-Meteo',
                                  date('Y-m-d H:i:s', strtotime($when) + 86400),
                                  date('Y-m-d H:i:s')]);
            }
        }
        if ($wind >= 60) {
            $title = "हुरीबतास चेतावनी — {$name} ({$wind} km/h)";
            $content = "हावाको गति: {$wind} km/h\nमिति: {$when}\n\nस्रोत: Open-Meteo / DHM model";
            $check->execute([$title, 'Open-Meteo']);
            if (!$check->fetchColumn()) {
                $insert->execute([$title, 'Weather', $name, 'medium', $content, 'Open-Meteo',
                                  date('Y-m-d H:i:s', strtotime($when) + 86400),
                                  date('Y-m-d H:i:s')]);
            }
        }
    }
}

// AI Auto-Sync: Real-time earthquake data from USGS (South Asia filter)
function fetchAndCacheEarthquakeAlerts(): void {
    ensureAlertsTable();

    $southAsia = ['nepal','india','tibet','bhutan','bangladesh','myanmar','pakistan','afghanistan',
                  'kashmir','himalaya','sikkim','uttarakhand','assam','manipur','darjeeling'];

    $url = 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_week.atom';
    try {
        $ctx = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0', 'follow_location' => 1]]);
        $xml = @file_get_contents($url, false, $ctx);
        if (!$xml) return;

        $atom = @simplexml_load_string($xml);
        if (!$atom || !isset($atom->entry)) return;

        $check  = db()->prepare('SELECT id FROM alerts WHERE title = ? AND source = ? LIMIT 1');
        $insert = db()->prepare('INSERT INTO alerts (title, category, district, severity, content, source, is_active, expires_at, created_at) VALUES (?,?,?,?,?,?,1,?,?)');

        foreach ($atom->entry as $entry) {
            $rawTitle = trim((string)$entry->title);
            $when     = trim((string)$entry->updated);
            $mag = 0.0; $place = '';

            if (preg_match('/M\s*([\d.]+)\s*[-–]\s*(.+)/u', $rawTitle, $m)) {
                $mag = (float)$m[1]; $place = trim($m[2]);
            }
            if ($mag < 2.5) continue;

            $placeL = mb_strtolower($place . ' ' . $rawTitle);
            $isSA = false;
            foreach ($southAsia as $kw) { if (str_contains($placeL, $kw)) { $isSA = true; break; } }
            if (!$isSA) continue;

            $severity = match(true) { $mag >= 6.0 => 'critical', $mag >= 5.0 => 'high', $mag >= 4.0 => 'medium', default => 'low' };
            $alertTitle = 'भूकम्प M' . number_format($mag, 1) . ' — ' . mb_substr($place, 0, 200);
            $content = 'परिमाण: M' . number_format($mag, 1) . "\nस्थान: " . $place . "\nसमय (UTC): " . $when
                     . "\n\nस्रोत: USGS Earthquake Hazards Program\n\n⚠️ ठूलो भूकम्प आएमा खुला मैदानमा जानुहोस्। आपतकालीन: 1122";
            $expires = date('Y-m-d H:i:s', strtotime($when) + 7 * 86400);
            $createdAt = date('Y-m-d H:i:s', strtotime($when));

            $check->execute([$alertTitle, 'USGS']);
            if ($check->fetchColumn()) continue;

            $insert->execute([$alertTitle, 'Earthquake', mb_substr($place, 0, 190), $severity, $content, 'USGS', $expires, $createdAt]);
        }
    } catch (Exception $e) {
        error_log('[NSH] Earthquake fetch error: ' . $e->getMessage());
    }
}

function maybeRefreshAlerts(): void {
    $flag = sys_get_temp_dir() . '/nsh_alerts_last.txt';
    $last = @file_get_contents($flag);
    if (!$last || (time() - (int)$last) > ALERTS_SYNC_INTERVAL) {
        fetchAndCacheEarthquakeAlerts();
        try { fetchAndCacheWeatherAlerts(); } catch (\Throwable $e) { error_log('[NSH] Weather fetch error: '.$e->getMessage()); }
        @file_put_contents($flag, time());
    }
}

function getActiveAlerts(?string $severity = null, ?string $category = null): array {
    ensureAlertsTable();
    $sql = "SELECT * FROM alerts WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)";
    $params = [];
    if ($severity) { $sql .= ' AND severity = ?';  $params[] = $severity; }
    if ($category) { $sql .= ' AND category = ?';  $params[] = $category; }
    $sql .= ' ORDER BY ' . orderBySeverity() . ', created_at DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAlertCategories(): array {
    ensureAlertsTable();
    return db()->query("SELECT category, COUNT(*) c FROM alerts WHERE is_active=1 GROUP BY category ORDER BY c DESC")->fetchAll();
}

function getAllAlerts(): array {
    ensureAlertsTable();
    return db()->query('SELECT * FROM alerts ORDER BY created_at DESC')->fetchAll();
}

// ─── AI Guide Table ───────────────────────────────────────────────────────────
function ensureGuideTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS ai_guides (
        id           $ai,
        title        VARCHAR(500) NOT NULL,
        slug         VARCHAR(191) NOT NULL UNIQUE,
        category     VARCHAR(100) NOT NULL DEFAULT 'General',
        excerpt      LONGTEXT,
        content      LONGTEXT NOT NULL,
        icon         VARCHAR(20) NOT NULL DEFAULT '🤖',
        level        VARCHAR(50) NOT NULL DEFAULT 'Beginner',
        is_published INT NOT NULL DEFAULT 1,
        views        INT NOT NULL DEFAULT 0,
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_guides_pub', 'ai_guides', 'is_published, created_at');
    $done = true;
}

function ensureContactDirectoryTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS contact_directory (
        id           $ai,
        name         VARCHAR(500) NOT NULL,
        name_ne      VARCHAR(500) NOT NULL,
        category     VARCHAR(100) NOT NULL,
        category_ne  VARCHAR(100) NOT NULL,
        city         VARCHAR(100) NOT NULL,
        phone        VARCHAR(50) NOT NULL,
        address      TEXT,
        email        VARCHAR(255),
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_contacts_city', 'contact_directory', 'city');
    dbIndex('idx_contacts_category', 'contact_directory', 'category');
    $done = true;
}

function ensureCabinetDecisionsTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS cabinet_decisions (
        id           $ai,
        date         DATE NOT NULL,
        date_np      VARCHAR(50) NOT NULL,
        title        VARCHAR(500) NOT NULL,
        title_ne     VARCHAR(500) NOT NULL,
        category     VARCHAR(100) NOT NULL,
        category_ne  VARCHAR(100) NOT NULL,
        summary      TEXT,
        summary_ne   TEXT,
        details      TEXT,
        details_ne   TEXT,
        created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_decisions_date', 'cabinet_decisions', 'date');
    dbIndex('idx_decisions_category', 'cabinet_decisions', 'category');
    $done = true;
}

function ensureGovernmentTendersTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS government_tenders (
        id                  $ai,
        title               VARCHAR(500) NOT NULL,
        title_ne            VARCHAR(500) NOT NULL,
        ministry            VARCHAR(500) NOT NULL,
        ministry_ne         VARCHAR(500) NOT NULL,
        department          VARCHAR(500) NOT NULL,
        department_ne       VARCHAR(500) NOT NULL,
        category            VARCHAR(100) NOT NULL,
        category_ne         VARCHAR(100) NOT NULL,
        location            VARCHAR(200) NOT NULL,
        location_ne         VARCHAR(200) NOT NULL,
        deadline            DATE NOT NULL,
        deadline_ne         VARCHAR(50) NOT NULL,
        estimated_cost      VARCHAR(100) NOT NULL,
        status              VARCHAR(50) NOT NULL,
        status_ne           VARCHAR(50) NOT NULL,
        published_date      DATE NOT NULL,
        published_date_ne   VARCHAR(50) NOT NULL,
        description         TEXT,
        description_ne      TEXT,
        documents           TEXT,
        link                VARCHAR(500) NOT NULL,
        created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_tenders_deadline', 'government_tenders', 'deadline');
    dbIndex('idx_tenders_category', 'government_tenders', 'category');
    dbIndex('idx_tenders_ministry', 'government_tenders', 'ministry');
    $done = true;
}

function ensureStoriesTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS stories (
        id                  $ai,
        title               VARCHAR(500) NOT NULL,
        title_en            VARCHAR(500) NOT NULL,
        category            VARCHAR(100) NOT NULL,
        category_ne         VARCHAR(100) NOT NULL,
        author              VARCHAR(200) NOT NULL,
        author_en           VARCHAR(200) NOT NULL,
        content             TEXT NOT NULL,
        content_en          TEXT NOT NULL,
        tags                TEXT,
        tags_en             TEXT,
        reading_time        INT NOT NULL DEFAULT 5,
        views               INT NOT NULL DEFAULT 0,
        image_url           VARCHAR(500),
        is_published        TINYINT NOT NULL DEFAULT 1,
        created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_stories_category', 'stories', 'category');
    dbIndex('idx_stories_published', 'stories', 'is_published');
    $done = true;
}

function getPublishedGuides(?string $category = null, int $limit = 20, int $offset = 0): array {
    ensureGuideTable();
    $sql = 'SELECT * FROM ai_guides WHERE is_published = 1';
    $params = [];
    if ($category) { $sql .= ' AND category = ?'; $params[] = $category; }
    $sql .= ' ORDER BY created_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getGuideCategories(): array {
    ensureGuideTable();
    return db()->query("SELECT category, COUNT(*) c FROM ai_guides WHERE is_published = 1 GROUP BY category ORDER BY c DESC")->fetchAll();
}

function getGuideBySlug(string $slug): ?array {
    ensureGuideTable();
    $stmt = db()->prepare('SELECT * FROM ai_guides WHERE slug = ? AND is_published = 1');
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    if ($r) db()->prepare('UPDATE ai_guides SET views = views + 1 WHERE id = ?')->execute([$r['id']]);
    return $r ?: null;
}

function getAllGuides(): array {
    ensureGuideTable();
    return db()->query('SELECT * FROM ai_guides ORDER BY created_at DESC')->fetchAll();
}

function seedDefaultGuides(): void {
    ensureGuideTable();
    if ((int)db()->query('SELECT COUNT(*) FROM ai_guides')->fetchColumn() > 0) return;

    $guides = [
        ['ChatGPT कसरी प्रयोग गर्ने? Complete Beginner Guide','chatgpt-how-to-use-nepali-guide','ChatGPT','🤖','Beginner',
            'ChatGPT के हो र यसलाई नेपाली भाषामा कसरी राम्रोसँग प्रयोग गर्ने सिक्नुहोस्।',
            '<h2>ChatGPT के हो?</h2><p>ChatGPT OpenAI ले बनाएको एउटा शक्तिशाली AI chatbot हो। यो नेपाली भाषामा पनि काम गर्छ।</p><h2>कसरी सुरु गर्ने?</h2><ol><li>chat.openai.com मा जानुहोस्</li><li>Free account बनाउनुहोस्</li><li>आफ्नो प्रश्न नेपाली वा English मा टाइप गर्नुहोस्</li></ol><h2>राम्रा प्रयोगहरू</h2><ul><li>Essay लेख्न मद्दत</li><li>Code debug</li><li>Translation</li><li>Business plan</li></ul>'],
        ['Google Gemini को पूर्ण Guide — नेपालीहरूका लागि','google-gemini-complete-nepali-guide','Gemini','✨','Beginner',
            'Google Gemini ले के-के गर्न सक्छ र यसलाई दैनिक काममा कसरी प्रयोग गर्ने।',
            '<h2>Gemini के हो?</h2><p>Google Gemini, ChatGPT को प्रतिस्पर्धी AI model हो। Google Search, Gmail, Docs सँग integrate भएको छ।</p><h2>फाइदाहरू</h2><ul><li>पूर्णतः Free</li><li>Real-time internet access</li><li>Image analysis</li></ul>'],
        ['AI Prompts — राम्रो जवाफ पाउन कसरी सोध्ने?','ai-prompt-engineering-nepali-tips','Prompt Tips','💡','Intermediate',
            'AI बाट राम्रो जवाफ पाउन Prompt लेख्ने तरिका — Nepali examples सहित।',
            '<h2>Prompt Engineering के हो?</h2><p>AI लाई राम्रो instruction दिने कला।</p><h2>सूत्र</h2><p><strong>Role + Task + Context + Format</strong></p><blockquote>तपाई एउटा expert SEO writer हो। "Pokhara Travel Guide 2025" मा 500 शब्दको article लेखिदिनुस्।</blockquote>'],
        ['Canva AI Tools — Design सजिलो बनाउने तरिका','canva-ai-tools-nepali-guide','Design Tools','🎨','Beginner',
            'Canva का AI features प्रयोग गरेर professional design कसरी बनाउने।',
            '<h2>Canva AI Features</h2><ul><li>Magic Write — AI text generation</li><li>Text to Image — AI image creation</li><li>Background Remover — एक click</li></ul>'],
        ['GitHub Copilot — Developers का लागि AI Coding Assistant','github-copilot-developers-nepali-guide','Developer Tools','💻','Intermediate',
            'GitHub Copilot ले coding कसरी छिटो बनाउँछ — Nepali developers को लागि।',
            '<h2>Copilot के हो?</h2><p>AI pair programmer जसले code suggest गर्छ।</p><h2>Free कसरी?</h2><p>Students ले GitHub Education Pack मार्फत Copilot Pro free पाउन सक्छन्।</p>'],
        ['AI ले Job खाला? Nepal मा AI को भविष्य','ai-future-nepal-jobs-impact','AI Trends','🇳🇵','Beginner',
            'AI ले नेपालका कुन jobs मा असर पार्छ र हामीले के तयारी गर्नुपर्छ।',
            '<h2>के गर्ने?</h2><ul><li>AI tools सिक्नुहोस्</li><li>AI सँग काम गर्न जान्नुस्</li><li>Data Entry — High Risk</li><li>Software Dev — Low Risk</li></ul>'],
        ['Claude AI — Anthropic को Advanced AI Guide','claude-ai-anthropic-guide','Claude','🧠','Intermediate',
            'Claude AI के हो र ChatGPT भन्दा कसरी फरक छ? Nepali users को लागि comparison।',
            '<h2>Claude के हो?</h2><p>Anthropic ले बनाएको Claude AI, ChatGPT को एउटा राम्रो alternative हो। Long documents analysis मा Claude अझ राम्रो मानिन्छ।</p><h2>Claude vs ChatGPT</h2><ul><li>Claude: Long text analysis मा राम्रो</li><li>ChatGPT: Plugins र image generation मा अगाडि</li></ul>'],
        ['Midjourney र DALL-E — AI Image Generation Guide','midjourney-dalle-ai-image-guide','Image AI','🎨','Intermediate',
            'AI बाट professional images कसरी generate गर्ने — Midjourney र DALL-E guide।',
            '<h2>AI Image Tools</h2><ul><li><strong>Midjourney</strong> — Discord मार्फत, monthly subscription</li><li><strong>DALL-E 3</strong> — ChatGPT Plus मा included</li><li><strong>Stable Diffusion</strong> — Free, open source</li></ul><h2>राम्रो Prompt</h2><p>"A realistic photo of Pokhara lake at sunset, 8K, professional photography"</p>'],
    ];

    $stmt = db()->prepare(dbIgnore() . ' ai_guides (title, slug, category, icon, level, excerpt, content) VALUES (?,?,?,?,?,?,?)');
    foreach ($guides as $g) {
        $stmt->execute($g);
    }
}

// ─── Weather Widget — wttr.in (free, no API key) ──────────────────────────────
function ensureWeatherTable(): void {
    static $done = false;
    if ($done) return;
    db()->exec("CREATE TABLE IF NOT EXISTS weather_cache (
        city        VARCHAR(100) NOT NULL PRIMARY KEY,
        data        LONGTEXT NOT NULL,
        fetched_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    $done = true;
}

function weatherCodeToEmoji(int $code): string {
    return match(true) {
        in_array($code, [113])                   => '☀️',
        in_array($code, [116])                   => '⛅',
        in_array($code, [119, 122])              => '☁️',
        in_array($code, [143, 248, 260])         => '🌫️',
        in_array($code, [176, 293, 296, 299])    => '🌦️',
        in_array($code, [182, 185, 281, 284])    => '🌨️',
        in_array($code, [200, 386, 389, 392])    => '⛈️',
        in_array($code, [179, 317, 320, 323,
                          326, 329, 332, 335,
                          338, 350, 362, 365,
                          368, 371, 374, 377])   => '❄️',
        in_array($code, [302, 305, 308, 311,
                          314, 353, 356, 359])   => '🌧️',
        default                                  => '🌡️',
    };
}

function fetchCityWeather(string $city): ?array {
    $url = 'https://wttr.in/' . urlencode($city) . '?format=j1';
    $ctx = stream_context_create(['http' => [
        'timeout'         => 6,
        'user_agent'      => 'NepaliSmartHub/1.0',
        'follow_location' => 1,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;

    $j = @json_decode($raw, true);
    if (!$j || empty($j['current_condition'][0])) return null;

    $c = $j['current_condition'][0];
    $code = (int)($c['weatherCode'] ?? 113);

    return [
        'city'        => $city,
        'temp_c'      => (int)($c['temp_C'] ?? 0),
        'feels_c'     => (int)($c['FeelsLikeC'] ?? 0),
        'humidity'    => (int)($c['humidity'] ?? 0),
        'wind_kmph'   => (int)($c['windspeedKmph'] ?? 0),
        'uv'          => (int)($c['uvIndex'] ?? 0),
        'visibility'  => (int)($c['visibility'] ?? 0),
        'desc'        => (string)($c['weatherDesc'][0]['value'] ?? 'N/A'),
        'emoji'       => weatherCodeToEmoji($code),
        'code'        => $code,
        'fetched_at'  => date('Y-m-d H:i:s'),
    ];
}

function getWeatherForCities(array $cities = []): array {
    if (empty($cities)) {
        $cities = ['Kathmandu', 'Pokhara', 'Chitwan', 'Lalitpur', 'Biratnagar'];
    }
    ensureWeatherTable();

    $results = [];
    $now     = time();
    $ttl     = 3600; // 1 hour cache

    foreach ($cities as $city) {
        // Try cache first
        $row = db()->prepare('SELECT data, fetched_at FROM weather_cache WHERE city = ?');
        $row->execute([$city]);
        $cached = $row->fetch();

        if ($cached && ($now - strtotime($cached['fetched_at'])) < $ttl) {
            $d = @json_decode($cached['data'], true);
            if ($d) { $results[$city] = $d; continue; }
        }

        // Fetch fresh
        $data = fetchCityWeather($city);
        if ($data) {
            $json = json_encode($data);
            db()->prepare(dbReplace() . ' weather_cache (city, data, fetched_at) VALUES (?,?,CURRENT_TIMESTAMP)')
                ->execute([$city, $json]);
            $results[$city] = $data;
        } elseif ($cached) {
            // Use stale cache as fallback
            $d = @json_decode($cached['data'], true);
            if ($d) $results[$city] = $d;
        }
    }
    return $results;
}

// ─── Sync Status Helper ───────────────────────────────────────────────────────
function getSyncStatus(): array {
    $flags = [
        'news'    => sys_get_temp_dir() . '/nsh_rss_last.txt',
        'notices' => sys_get_temp_dir() . '/nsh_notices_last.txt',
        'alerts'  => sys_get_temp_dir() . '/nsh_alerts_last.txt',
    ];
    $status = [];
    foreach ($flags as $key => $f) {
        $last = @file_get_contents($f);
        $status[$key] = $last ? date('Y-m-d H:i:s', (int)$last) : 'Never';
    }
    return $status;
}

// ─── Brand / Logo Helpers (admin-controlled) ──────────────────────────────────
function brandName(): string {
    return defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी';
}
function brandInitials(): string {
    $clean = preg_replace('/[^A-Za-z\\s]/', '', brandName());
    $parts = preg_split('/\\s+/', trim($clean));
    $i = '';
    foreach ($parts as $p) { if ($p !== '') $i .= strtoupper($p[0]); if (strlen($i) >= 2) break; }
    return $i ?: 'SN';
}
function brandLogoUrl(): string {
    $p = defined('SITE_LOGO') ? SITE_LOGO : '';
    if (!$p) return '';
    $file = __DIR__ . $p;
    return file_exists($file) ? $p : '';
}
function brandSplit(): array {
    // Split brand name into two parts for two-tone styling
    $n = brandName();
    $parts = preg_split('/\\s+/', trim($n), 2);
    if (count($parts) === 2) return [$parts[0], $parts[1]];
    $mid = (int) ceil(strlen($n) / 2);
    return [substr($n, 0, $mid), substr($n, $mid)];
}
function renderBrandMark(int $size = 32, string $bg = '#15803d'): string {
    $logo = brandLogoUrl();
    if ($logo) {
        return '<img src="' . htmlspecialchars($logo) . '" alt="' . htmlspecialchars(brandName()) . '" style="width:' . $size . 'px;height:' . $size . 'px;object-fit:contain;border-radius:8px;background:#fff;" />';
    }
    return '<div style="width:' . $size . 'px;height:' . $size . 'px;background:' . $bg . ';border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:' . max(10, (int)round($size * 0.38)) . 'px;font-family:ui-monospace,monospace;letter-spacing:-.5px;">' . htmlspecialchars(brandInitials()) . '</div>';
}

// ─── Users Table (Signups) ────────────────────────────────────────────────────
function ensureUsersTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    $onUpdU = isMysql() ? ' ON UPDATE CURRENT_TIMESTAMP' : '';
    db()->exec("CREATE TABLE IF NOT EXISTS users (
        id            $ai,
        name          VARCHAR(200) NOT NULL,
        email         VARCHAR(200) NOT NULL UNIQUE,
        phone         VARCHAR(20),
        password_hash VARCHAR(255) NULL,
        is_admin      INT NOT NULL DEFAULT 0,
        subject       VARCHAR(300),
        message       LONGTEXT,
        is_verified   INT NOT NULL DEFAULT 0,
        is_active     INT NOT NULL DEFAULT 1,
        created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP$onUpdU
    )" . dbCharset());
    // Idempotent ALTERs for existing installs (ignore "duplicate column" errors)
    try { db()->exec("ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NULL"); } catch (\Throwable $e) {}
    try { db()->exec("ALTER TABLE users ADD COLUMN is_admin INT NOT NULL DEFAULT 0"); } catch (\Throwable $e) {}
    dbIndex('idx_users_email',  'users', 'email');
    dbIndex('idx_users_active', 'users', 'is_active');
    dbIndex('idx_users_created', 'users', 'created_at');
    $done = true;
}

function registerContact(string $name, string $email, ?string $phone = null, ?string $subject = null, ?string $message = null): array {
    ensureUsersTable();
    $email = strtolower(trim($email));
    $name = trim($name);
    
    if (!$name || !$email) {
        return ['success' => false, 'error' => 'Name and email are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email address.'];
    }
    
    try {
        $stmt = db()->prepare('INSERT INTO users (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $email, $phone ?: null, $subject ?: null, $message ?: null]);
        return ['success' => true, 'message' => 'Registered successfully!'];
    } catch (\PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE') !== false) {
            return ['success' => false, 'error' => 'This email is already registered.'];
        }
        return ['success' => false, 'error' => 'Registration failed. Try again.'];
    }
}

function getAllUsers(int $limit = 100, int $offset = 0): array {
    ensureUsersTable();
    $stmt = db()->prepare('SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUserCount(): int {
    ensureUsersTable();
    return (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function updateUserStatus(int $id, int $is_active): bool {
    ensureUsersTable();
    $stmt = db()->prepare('UPDATE users SET is_active = ? WHERE id = ?');
    return $stmt->execute([$is_active, $id]);
}

function deleteUser(int $id): bool {
    ensureUsersTable();
    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
    return $stmt->execute([$id]);
}

// ─── Emergency Contacts Table ─────────────────────────────────────────────────
function ensureEmergencyTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS emergency_contacts (
        id         $ai,
        district   VARCHAR(100) NOT NULL,
        province   VARCHAR(100) NOT NULL DEFAULT '',
        category   VARCHAR(50)  NOT NULL DEFAULT 'hospital',
        name       VARCHAR(200) NOT NULL,
        name_ne    VARCHAR(200) NOT NULL DEFAULT '',
        phone      VARCHAR(80)  NOT NULL,
        alt_phone  VARCHAR(80)  NOT NULL DEFAULT '',
        location   VARCHAR(200) NOT NULL DEFAULT '',
        is_24hr    INT          NOT NULL DEFAULT 1,
        sort_order INT          NOT NULL DEFAULT 100,
        is_active  INT          NOT NULL DEFAULT 1
    )" . dbCharset());
    dbIndex('idx_emg_district', 'emergency_contacts', 'district');
    dbIndex('idx_emg_category', 'emergency_contacts', 'category');
    dbIndex('idx_emg_active',   'emergency_contacts', 'is_active');
    $count = (int) db()->query('SELECT COUNT(*) FROM emergency_contacts')->fetchColumn();
    if ($count === 0) _seedEmergencyContacts();
    $done = true;
}

function _seedEmergencyContacts(): void {
    $rows = [
        // [district, province, category, name, name_ne, phone, alt_phone, location, is_24hr, sort_order]
        // National
        ['National','National','police',   'Nepal Police Emergency',        'नेपाल प्रहरी इमर्जेन्सी',         '100',         '',             'All Nepal', 1,  1],
        ['National','National','fire',     'Fire Brigade',                  'दमकल सेवा',                       '101',         '',             'All Nepal', 1,  2],
        ['National','National','ambulance','Ambulance Service',             'एम्बुलेन्स सेवा',                 '102',         '',             'All Nepal', 1,  3],
        ['National','National','police',   'Traffic Police',                'ट्राफिक प्रहरी',                  '103',         '',             'All Nepal', 1,  4],
        ['National','National','hospital', 'Health Emergency Hotline',      'स्वास्थ्य हेल्पलाइन',            '1115',        '',             'All Nepal', 1,  5],
        ['National','National','hospital', 'Child Helpline',                'बाल हेल्पलाइन',                   '1098',        '',             'All Nepal', 1,  6],
        ['National','National','hospital', 'Women Helpline',                'महिला हेल्पलाइन',                 '1145',        '',             'All Nepal', 1,  7],
        ['National','National','hospital', 'Disaster Management Hotline',   'विपद व्यवस्थापन',                 '1143',        '',             'All Nepal', 1,  8],
        ['National','National','police',   'Tourism Police',                'पर्यटन प्रहरी',                   '1144',        '',             'All Nepal', 1,  9],
        // Kathmandu
        ['Kathmandu','Bagmati','hospital', 'Bir Hospital',                  'वीर अस्पताल',                     '01-4221119',  '01-4220398',   'Mahaboudha',1, 10],
        ['Kathmandu','Bagmati','hospital', 'TUTH (Teaching Hospital)',      'त्रिवि शिक्षण अस्पताल',          '01-4412303',  '01-4412707',   'Maharajgunj',1,11],
        ['Kathmandu','Bagmati','hospital', 'Grande International Hospital', 'ग्राण्डे अस्पताल',               '01-5159266',  '',             'Tokha Road', 1, 12],
        ['Kathmandu','Bagmati','hospital', 'Norvic International Hospital', 'नर्भिक अस्पताल',                  '01-4258554',  '',             'Thapathali', 1, 13],
        ['Kathmandu','Bagmati','hospital', 'Nepal Medical College (NMCTH)', 'नेपाल मेडिकल कलेज',              '01-4911008',  '',             'Jorpati',    1, 14],
        ['Kathmandu','Bagmati','hospital', 'Kathmandu Medical College',     'केएमसी अस्पताल',                 '01-4259747',  '',             'Sinamangal', 1, 15],
        ['Kathmandu','Bagmati','hospital', 'National Trauma Center',        'राष्ट्रिय ट्रमा सेन्टर',         '01-4371500',  '',             'Maharajgunj',1, 16],
        ['Kathmandu','Bagmati','hospital', 'Kanti Children Hospital',       'कान्ति बाल अस्पताल',             '01-4412798',  '',             'Maharajgunj',1, 17],
        ['Kathmandu','Bagmati','hospital', 'Maternity Hospital (Prasuti)',  'प्रसूति गृह',                    '01-4259979',  '',             'Thapathali', 1, 18],
        ['Kathmandu','Bagmati','ambulance','Red Cross Ambulance',           'रेडक्रस एम्बुलेन्स',             '01-4228094',  '102',          'Kalimati',   1, 19],
        ['Kathmandu','Bagmati','blood_bank','Central Blood Bank (CBTS)',    'केन्द्रीय ब्लड बैंक',            '01-4225344',  '',             'Exhibition Road',1,20],
        ['Kathmandu','Bagmati','fire',     'Kathmandu Fire Brigade',        'काठमाडौं दमकल',                  '01-4221177',  '101',          'Bhadrakali', 1, 21],
        ['Kathmandu','Bagmati','police',   'Metropolitan Police Office',    'महानगर प्रहरी कार्यालय',          '01-4220100',  '100',          'Naxal',      1, 22],
        // Lalitpur
        ['Lalitpur','Bagmati','hospital',  'Patan Hospital',                'पाटन अस्पताल',                   '01-5522266',  '01-5537635',   'Lagankhel',  1, 30],
        ['Lalitpur','Bagmati','hospital',  'Lalitpur Sub-Metropolitan Hospital','ललितपुर जिल्ला अस्पताल',    '01-5520879',  '',             'Lalitpur',   1, 31],
        ['Lalitpur','Bagmati','fire',      'Lalitpur Fire Brigade',         'ललितपुर दमकल',                   '01-5527114',  '101',          'Lagankhel',  1, 32],
        ['Lalitpur','Bagmati','police',    'Lalitpur District Police',      'ललितपुर प्रहरी',                 '01-5552480',  '100',          'Lalitpur',   1, 33],
        ['Lalitpur','Bagmati','ambulance', 'Lalitpur Ambulance',            'ललितपुर एम्बुलेन्स',             '01-5536930',  '102',          'Lalitpur',   1, 34],
        // Bhaktapur
        ['Bhaktapur','Bagmati','hospital', 'Bhaktapur Hospital',            'भक्तपुर अस्पताल',                '01-6610798',  '',             'Bhaktapur',  1, 35],
        ['Bhaktapur','Bagmati','police',   'Bhaktapur District Police',     'भक्तपुर प्रहरी',                 '01-6610800',  '100',          'Bhaktapur',  1, 36],
        // Chitwan
        ['Chitwan','Bagmati','hospital',   'Bharatpur Hospital',            'भरतपुर अस्पताल',                 '056-527012',  '',             'Bharatpur',  1, 40],
        ['Chitwan','Bagmati','hospital',   'College of Medical Sciences',   'सीएमएस अस्पताल',                 '056-524260',  '',             'Bharatpur',  1, 41],
        ['Chitwan','Bagmati','hospital',   'Chitwan Medical College',       'चितवन मेडिकल कलेज',              '056-530009',  '',             'Bharatpur',  1, 42],
        ['Chitwan','Bagmati','ambulance',  'Chitwan Ambulance',             'चितवन एम्बुलेन्स',               '056-520111',  '102',          'Bharatpur',  1, 43],
        ['Chitwan','Bagmati','blood_bank', 'Bharatpur Blood Bank',          'भरतपुर ब्लड बैंक',               '056-527877',  '',             'Bharatpur',  1, 44],
        // Makwanpur
        ['Makwanpur','Bagmati','hospital', 'Hetauda Hospital',              'हेटौंडा अस्पताल',                '057-520113',  '',             'Hetauda',    1, 45],
        ['Makwanpur','Bagmati','police',   'Hetauda Police',                'हेटौंडा प्रहरी',                 '057-520100',  '100',          'Hetauda',    1, 46],
        // Morang (Biratnagar)
        ['Morang','Koshi','hospital',      'Koshi Zonal Hospital',          'कोशी अञ्चल अस्पताल',             '021-525200',  '',             'Biratnagar', 1, 50],
        ['Morang','Koshi','hospital',      'Nobel Medical College',         'नोबेल मेडिकल कलेज',              '021-531500',  '',             'Biratnagar', 1, 51],
        ['Morang','Koshi','hospital',      'Birat Medical College',         'विराट मेडिकल कलेज',              '021-408000',  '',             'Biratnagar', 1, 52],
        ['Morang','Koshi','ambulance',     'Biratnagar Ambulance',          'बिराटनगर एम्बुलेन्स',            '021-525101',  '102',          'Biratnagar', 1, 53],
        ['Morang','Koshi','blood_bank',    'Biratnagar Blood Bank',         'बिराटनगर ब्लड बैंक',             '021-527211',  '',             'Biratnagar', 1, 54],
        ['Morang','Koshi','fire',          'Biratnagar Fire Brigade',       'बिराटनगर दमकल',                  '021-525200',  '101',          'Biratnagar', 1, 55],
        // Sunsari (Dharan)
        ['Sunsari','Koshi','hospital',     'BPKIHS Dharan',                 'बीपी कोइराला स्वास्थ्य विज्ञान प्रतिष्ठान','025-520333','025-525555','Dharan',1,56],
        ['Sunsari','Koshi','hospital',     'Sunsari District Hospital',     'सुनसरी जिल्ला अस्पताल',          '025-521100',  '',             'Inaruwa',    1, 57],
        // Jhapa
        ['Jhapa','Koshi','hospital',       'Mechi Zonal Hospital',          'मेची अञ्चल अस्पताल',             '023-540047',  '',             'Bhadrapur',  1, 58],
        ['Jhapa','Koshi','hospital',       'Birtamod Community Hospital',   'बिर्तामोड अस्पताल',              '023-543200',  '',             'Birtamod',   1, 59],
        // Parsa (Birgunj)
        ['Parsa','Madhesh','hospital',     'Narayani Sub-Regional Hospital','नारायणी उप-क्षेत्रीय अस्पताल', '051-523028',  '',             'Birgunj',    1, 60],
        ['Parsa','Madhesh','hospital',     'National Medical College',      'नेशनल मेडिकल कलेज',              '051-527044',  '',             'Birgunj',    1, 61],
        ['Parsa','Madhesh','blood_bank',   'Birgunj Blood Bank',            'बिरगञ्ज ब्लड बैंक',              '051-525020',  '',             'Birgunj',    1, 62],
        ['Parsa','Madhesh','police',       'Birgunj Police',                'बिरगञ्ज प्रहरी',                 '051-524100',  '100',          'Birgunj',    1, 63],
        // Bara
        ['Bara','Madhesh','hospital',      'Bara District Hospital',        'बारा जिल्ला अस्पताल',            '051-520107',  '',             'Kalaiya',    1, 65],
        // Rautahat
        ['Rautahat','Madhesh','hospital',  'Rautahat District Hospital',    'रौतहट जिल्ला अस्पताल',           '055-520200',  '',             'Gaur',       1, 66],
        // Saptari
        ['Saptari','Madhesh','hospital',   'Rajbiraj District Hospital',    'राजविराज जिल्ला अस्पताल',        '031-520088',  '',             'Rajbiraj',   1, 67],
        // Kaski (Pokhara)
        ['Kaski','Gandaki','hospital',     'Gandaki Medical College',       'गण्डकी मेडिकल कलेज',             '061-431000',  '',             'Pokhara',    1, 70],
        ['Kaski','Gandaki','hospital',     'Western Regional Hospital',     'पश्चिमाञ्चल क्षेत्रीय अस्पताल','061-520066',  '',             'Pokhara',    1, 71],
        ['Kaski','Gandaki','hospital',     'Manipal Teaching Hospital',     'मणिपाल अस्पताल',                 '061-526416',  '',             'Phulbari',   1, 72],
        ['Kaski','Gandaki','hospital',     'National Pokhara Hospital',     'नेशनल पोखरा अस्पताल',            '061-465000',  '',             'Nayabazaar', 1, 73],
        ['Kaski','Gandaki','ambulance',    'Gandaki Ambulance Service',     'गण्डकी एम्बुलेन्स',              '061-520111',  '102',          'Pokhara',    1, 74],
        ['Kaski','Gandaki','blood_bank',   'Gandaki Blood Bank',            'गण्डकी ब्लड बैंक',               '061-521177',  '',             'Pokhara',    1, 75],
        ['Kaski','Gandaki','police',       'Pokhara Metropolitan Police',   'पोखरा प्रहरी',                   '061-521090',  '100',          'Pokhara',    1, 76],
        ['Kaski','Gandaki','fire',         'Pokhara Fire Brigade',          'पोखरा दमकल',                     '061-540100',  '101',          'Pokhara',    1, 77],
        // Syangja
        ['Syangja','Gandaki','hospital',   'Syangja District Hospital',     'स्याङ्जा जिल्ला अस्पताल',        '063-420033',  '',             'Waling',     1, 78],
        // Nawalpur
        ['Nawalpur','Gandaki','hospital',  'Nawalpur District Hospital',    'नवलपुर जिल्ला अस्पताल',          '078-520020',  '',             'Kawasoti',   1, 79],
        // Rupandehi (Butwal / Bhairahawa)
        ['Rupandehi','Lumbini','hospital', 'Lumbini Provincial Hospital',   'लुम्बिनी प्रदेश अस्पताल',        '071-520111',  '',             'Butwal',     1, 80],
        ['Rupandehi','Lumbini','hospital', 'Universal College of Medical Sciences','युनिभर्सल मेडिकल कलेज', '071-523700',  '',             'Bhairahawa', 1, 81],
        ['Rupandehi','Lumbini','hospital', 'Bhairahawa District Hospital',  'भैरहवा जिल्ला अस्पताल',          '071-520073',  '',             'Bhairahawa', 1, 82],
        ['Rupandehi','Lumbini','ambulance','Butwal Ambulance',              'बुटवल एम्बुलेन्स',               '071-540111',  '102',          'Butwal',     1, 83],
        ['Rupandehi','Lumbini','blood_bank','Lumbini Blood Bank',           'लुम्बिनी ब्लड बैंक',             '071-520200',  '',             'Butwal',     1, 84],
        ['Rupandehi','Lumbini','fire',     'Butwal Fire Brigade',           'बुटवल दमकल',                     '071-540200',  '101',          'Butwal',     1, 85],
        // Banke (Nepalgunj)
        ['Banke','Lumbini','hospital',     'Bheri Zonal Hospital',          'भेरी अञ्चल अस्पताल',             '081-520107',  '',             'Nepalgunj',  1, 86],
        ['Banke','Lumbini','hospital',     'Nepalgunj Medical College',     'नेपालगञ्ज मेडिकल कलेज',          '081-528000',  '',             'Chisapani',  1, 87],
        ['Banke','Lumbini','ambulance',    'Nepalgunj Ambulance',           'नेपालगञ्ज एम्बुलेन्स',           '081-521200',  '102',          'Nepalgunj',  1, 88],
        ['Banke','Lumbini','blood_bank',   'Nepalgunj Blood Bank',          'नेपालगञ्ज ब्लड बैंक',            '081-520344',  '',             'Nepalgunj',  1, 89],
        // Dang
        ['Dang','Lumbini','hospital',      'Rapti Provincial Hospital',     'राप्ती प्रदेश अस्पताल',           '082-521033',  '',             'Ghorahi',    1, 90],
        ['Dang','Lumbini','hospital',      'Tulsipur District Hospital',    'तुलसीपुर जिल्ला अस्पताल',        '082-560100',  '',             'Tulsipur',   1, 91],
        // Palpa
        ['Palpa','Lumbini','hospital',     'Palpa District Hospital',       'पाल्पा जिल्ला अस्पताल',          '075-520016',  '',             'Tansen',     1, 92],
        ['Palpa','Lumbini','hospital',     'United Mission Hospital Tansen','युनाइटेड मिशन अस्पताल',          '075-520007',  '',             'Tansen',     1, 93],
        // Surkhet (Birendranagar)
        ['Surkhet','Karnali','hospital',   'Karnali Academy of Health Sciences','कर्णाली स्वास्थ्य विज्ञान प्रतिष्ठान','083-522022','','Birendranagar',1,95],
        ['Surkhet','Karnali','hospital',   'Surkhet District Hospital',     'सुर्खेत जिल्ला अस्पताल',         '083-521100',  '',             'Birendranagar',1,96],
        ['Surkhet','Karnali','ambulance',  'Surkhet Ambulance',             'सुर्खेत एम्बुलेन्स',             '083-521200',  '102',          'Birendranagar',1,97],
        // Jumla
        ['Jumla','Karnali','hospital',     'Karnali Provincial Hospital',   'जुम्ला अस्पताल',                 '087-520090',  '',             'Jumla',      1, 98],
        // Kailali (Dhangadhi)
        ['Kailali','Sudurpashchim','hospital','Seti Provincial Hospital',   'सेती प्रदेश अस्पताल',            '091-521350',  '',             'Dhangadhi',  1,100],
        ['Kailali','Sudurpashchim','hospital','Kailali District Hospital',  'कैलाली जिल्ला अस्पताल',          '091-521200',  '',             'Dhangadhi',  1,101],
        ['Kailali','Sudurpashchim','ambulance','Dhangadhi Ambulance',       'धनगढी एम्बुलेन्स',               '091-523000',  '102',          'Dhangadhi',  1,102],
        ['Kailali','Sudurpashchim','blood_bank','Dhangadhi Blood Bank',     'धनगढी ब्लड बैंक',                '091-521500',  '',             'Dhangadhi',  1,103],
        ['Kailali','Sudurpashchim','police','Dhangadhi Police',             'धनगढी प्रहरी',                   '091-521100',  '100',          'Dhangadhi',  1,104],
        // Kanchanpur (Mahendranagar)
        ['Kanchanpur','Sudurpashchim','hospital','Mahakali Zonal Hospital', 'महाकाली अञ्चल अस्पताल',          '099-521060',  '',             'Mahendranagar',1,105],
        ['Kanchanpur','Sudurpashchim','police','Mahendranagar Police',      'महेन्द्रनगर प्रहरी',             '099-521100',  '100',          'Mahendranagar',1,106],
        // Dadeldhura
        ['Dadeldhura','Sudurpashchim','hospital','Dadeldhura District Hospital','डडेलधुरा जिल्ला अस्पताल',    '096-420023',  '',             'Dadeldhura', 1,107],
    ];
    $stmt = db()->prepare("INSERT INTO emergency_contacts
        (district,province,category,name,name_ne,phone,alt_phone,location,is_24hr,sort_order)
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    foreach ($rows as $r) {
        try { $stmt->execute($r); } catch (\Throwable $e) { /* skip dup */ }
    }
}

function getEmergencyContacts(string $district = '', string $category = ''): array {
    ensureEmergencyTable();
    $sql    = 'SELECT * FROM emergency_contacts WHERE is_active=1';
    $params = [];
    if ($district) { $sql .= ' AND district=?'; $params[] = $district; }
    if ($category)  { $sql .= ' AND category=?'; $params[] = $category; }
    $sql .= ' ORDER BY sort_order ASC, district ASC, name ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─── Government Portal Links Table ───────────────────────────────────────────
function ensureGovLinksTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS gov_portal_links (
        id         $ai,
        icon       VARCHAR(20)  NOT NULL DEFAULT '🏛️',
        name       VARCHAR(100) NOT NULL,
        name_ne    VARCHAR(100) NOT NULL DEFAULT '',
        url        VARCHAR(500) NOT NULL,
        color      VARCHAR(20)  NOT NULL DEFAULT '#0f766e',
        category   VARCHAR(50)  NOT NULL DEFAULT 'general',
        sort_order INT          NOT NULL DEFAULT 100,
        is_active  INT          NOT NULL DEFAULT 1
    )" . dbCharset());
    dbIndex('idx_gpl_active', 'gov_portal_links', 'is_active');
    $count = (int) db()->query('SELECT COUNT(*) FROM gov_portal_links')->fetchColumn();
    if ($count === 0) _seedGovLinks();
    $done = true;
}

function _seedGovLinks(): void {
    $links = [
        // [icon, name, name_ne, url, color, category, sort_order]
        ['🚗', 'DoTM',              'यातायात विभाग',              'https://dotm.gov.np',                     '#2563eb', 'transport',  10],
        ['🪪', 'NID Center',        'राष्ट्रिय परिचयपत्र',        'https://nidmc.gov.np',                    '#16a34a', 'identity',   20],
        ['📗', 'Passport',          'राहदानी विभाग',              'https://nepalpassport.gov.np',            '#d97706', 'identity',   30],
        ['💰', 'IRD / PAN',         'आन्तरिक राजस्व',             'https://ird.gov.np',                      '#4f46e5', 'tax',        40],
        ['📱', 'Nagarik App',       'नागरिक एप',                  'https://nagarikapp.gov.np',               '#0f766e', 'digital',    50],
        ['🏦', 'Nepal Rastra Bank', 'नेपाल राष्ट्र बैंक',         'https://nrb.org.np',                      '#b91c1c', 'finance',    60],
        ['📞', 'NTC',               'नेपाल टेलिकम',               'https://ntc.net.np',                      '#15803d', 'telecom',    70],
        ['⚡', 'NEA',               'विद्युत प्राधिकरण',           'https://nea.org.np',                      '#ca8a04', 'utility',    80],
        ['✈️', 'CAAN',              'नागरिक उड्डयन',              'https://caanepal.gov.np',                 '#0369a1', 'transport',  90],
        ['🏢', 'OCR',               'कम्पनी दर्ता',               'https://ocr.gov.np',                      '#7c3aed', 'business',  100],
        ['👮', 'Immigration',       'आप्रवासन विभाग',             'https://immigration.gov.np',              '#0f766e', 'identity',  110],
        ['💧', 'KUKL',              'काठमाडौं उपत्यका खानेपानी', 'https://kathmanduupatyaka.gov.np',        '#0891b2', 'utility',   120],
        ['🏥', 'DoHS',              'स्वास्थ्य सेवा विभाग',       'https://dohs.gov.np',                     '#ef4444', 'health',    130],
        ['📚', 'MOE',               'शिक्षा मन्त्रालय',           'https://moe.gov.np',                      '#8b5cf6', 'education', 140],
        ['🌾', 'MoALD',             'कृषि तथा पशुपंछी विभाग',    'https://moad.gov.np',                     '#65a30d', 'agriculture',150],
        ['⚖️', 'Supreme Court',     'सर्वोच्च अदालत',             'https://supremecourt.gov.np',             '#374151', 'legal',     160],
    ];
    $stmt = db()->prepare("INSERT INTO gov_portal_links (icon,name,name_ne,url,color,category,sort_order) VALUES (?,?,?,?,?,?,?)");
    foreach ($links as $l) {
        try { $stmt->execute($l); } catch (\Throwable $e) { /* skip dup */ }
    }
}

function getGovPortalLinks(string $category = ''): array {
    ensureGovLinksTable();
    $sql    = 'SELECT * FROM gov_portal_links WHERE is_active=1';
    $params = [];
    if ($category) { $sql .= ' AND category=?'; $params[] = $category; }
    $sql .= ' ORDER BY sort_order ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─── Nepal Tax Rates Table ────────────────────────────────────────────────────
function ensureTaxRatesTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS nepal_tax_info (
        id              $ai,
        fiscal_year     VARCHAR(20)  NOT NULL DEFAULT '2081/82',
        vat_rate        DECIMAL(5,2) NOT NULL DEFAULT 13.00,
        income_tax_single  TEXT NOT NULL,
        income_tax_married TEXT NOT NULL,
        corporate_tax      TEXT NOT NULL,
        tds_rates          TEXT NOT NULL,
        vehicle_tax_2w     TEXT NOT NULL,
        vehicle_tax_4w     TEXT NOT NULL,
        updated_at      TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_by      VARCHAR(100) DEFAULT 'system',
        source          VARCHAR(300) DEFAULT 'Ministry of Finance, Nepal',
        notes           TEXT
    )" . dbCharset());
    dbIndex('idx_nti_fy', 'nepal_tax_info', 'fiscal_year');
    $count = (int) db()->query('SELECT COUNT(*) FROM nepal_tax_info')->fetchColumn();
    if ($count === 0) _seedTaxRates();
    $done = true;
}

function _seedTaxRates(): void {
    $single = json_encode([
        ['from'=>0,       'to'=>500000,  'rate'=>1,  'label'=>'५ लाखसम्म',   'label_en'=>'Up to 5L @1%'],
        ['from'=>500001,  'to'=>700000,  'rate'=>10, 'label'=>'५–७ लाख',     'label_en'=>'5L–7L @10%'],
        ['from'=>700001,  'to'=>1000000, 'rate'=>20, 'label'=>'७–१० लाख',    'label_en'=>'7L–10L @20%'],
        ['from'=>1000001, 'to'=>2000000, 'rate'=>30, 'label'=>'१०–२० लाख',   'label_en'=>'10L–20L @30%'],
        ['from'=>2000001, 'to'=>null,    'rate'=>36, 'label'=>'२० लाख माथि', 'label_en'=>'Above 20L @36%'],
    ], JSON_UNESCAPED_UNICODE);
    $married = json_encode([
        ['from'=>0,       'to'=>600000,  'rate'=>1,  'label'=>'६ लाखसम्म',   'label_en'=>'Up to 6L @1%'],
        ['from'=>600001,  'to'=>800000,  'rate'=>10, 'label'=>'६–८ लाख',     'label_en'=>'6L–8L @10%'],
        ['from'=>800001,  'to'=>1100000, 'rate'=>20, 'label'=>'८–११ लाख',    'label_en'=>'8L–11L @20%'],
        ['from'=>1100001, 'to'=>2000000, 'rate'=>30, 'label'=>'११–२० लाख',   'label_en'=>'11L–20L @30%'],
        ['from'=>2000001, 'to'=>null,    'rate'=>36, 'label'=>'२० लाख माथि', 'label_en'=>'Above 20L @36%'],
    ], JSON_UNESCAPED_UNICODE);
    $corporate = json_encode([
        ['category'=>'General',          'rate'=>25, 'label_ne'=>'सामान्य',         'label_en'=>'General'],
        ['category'=>'Manufacturing',    'rate'=>20, 'label_ne'=>'उत्पादन',          'label_en'=>'Manufacturing'],
        ['category'=>'Finance/Insurance','rate'=>30, 'label_ne'=>'वित्त/बीमा',       'label_en'=>'Finance/Insurance'],
        ['category'=>'IT/Software',      'rate'=>15, 'label_ne'=>'IT/सफ्टवेयर',     'label_en'=>'IT/Software'],
        ['category'=>'Export Industry',  'rate'=>20, 'label_ne'=>'निर्यात उद्योग',  'label_en'=>'Export Industry'],
    ], JSON_UNESCAPED_UNICODE);
    $tds = json_encode([
        ['category'=>'Interest',          'rate'=>5,   'label_ne'=>'ब्याज',              'label_en'=>'Interest Income'],
        ['category'=>'Rent',              'rate'=>10,  'label_ne'=>'भाडा',               'label_en'=>'Rent'],
        ['category'=>'Commission',        'rate'=>15,  'label_ne'=>'कमिसन',              'label_en'=>'Commission'],
        ['category'=>'Contract',          'rate'=>1.5, 'label_ne'=>'ठेक्का/टेन्डर',     'label_en'=>'Contract/Tender'],
        ['category'=>'Dividend',          'rate'=>5,   'label_ne'=>'लाभांश',             'label_en'=>'Dividend'],
        ['category'=>'Professional Fee',  'rate'=>15,  'label_ne'=>'व्यावसायिक शुल्क', 'label_en'=>'Professional Fee'],
        ['category'=>'Royalty',           'rate'=>15,  'label_ne'=>'रोयल्टी',           'label_en'=>'Royalty'],
    ], JSON_UNESCAPED_UNICODE);
    $v2w = json_encode([
        ['cc_from'=>0,   'cc_to'=>125,  'rate'=>2500,  'label'=>'≤125 cc'],
        ['cc_from'=>126, 'cc_to'=>150,  'rate'=>4000,  'label'=>'126–150 cc'],
        ['cc_from'=>151, 'cc_to'=>225,  'rate'=>6000,  'label'=>'151–225 cc'],
        ['cc_from'=>226, 'cc_to'=>400,  'rate'=>10000, 'label'=>'226–400 cc'],
        ['cc_from'=>401, 'cc_to'=>null, 'rate'=>15000, 'label'=>'>400 cc'],
    ]);
    $v4w = json_encode([
        ['cc_from'=>0,    'cc_to'=>1000, 'rate'=>12000,  'label'=>'≤1000 cc'],
        ['cc_from'=>1001, 'cc_to'=>1500, 'rate'=>18000,  'label'=>'1001–1500 cc'],
        ['cc_from'=>1501, 'cc_to'=>2000, 'rate'=>35000,  'label'=>'1501–2000 cc'],
        ['cc_from'=>2001, 'cc_to'=>2500, 'rate'=>50000,  'label'=>'2001–2500 cc'],
        ['cc_from'=>2501, 'cc_to'=>3000, 'rate'=>70000,  'label'=>'2501–3000 cc'],
        ['cc_from'=>3001, 'cc_to'=>null, 'rate'=>100000, 'label'=>'>3000 cc'],
    ]);
    $stmt = db()->prepare("INSERT INTO nepal_tax_info
        (fiscal_year,vat_rate,income_tax_single,income_tax_married,corporate_tax,tds_rates,vehicle_tax_2w,vehicle_tax_4w,source,notes)
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        '2081/82', 13.00, $single, $married, $corporate, $tds, $v2w, $v4w,
        'नेपाल सरकार बजेट २०८१/८२ — Ministry of Finance (finance.gov.np)',
        '२०८१ श्रावण १ देखि लागू। Budget 2081/82. Admin बाट update गर्न सकिन्छ।',
    ]);
}

function getTaxInfo(): array {
    ensureTaxRatesTable();
    $row = db()->query("SELECT * FROM nepal_tax_info ORDER BY id DESC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
    if (!$row) return [];
    foreach (['income_tax_single','income_tax_married','corporate_tax','tds_rates','vehicle_tax_2w','vehicle_tax_4w'] as $col) {
        if (isset($row[$col])) $row[$col] = json_decode($row[$col], true) ?? [];
    }
    return $row;
}

// BS date helpers
if (file_exists(__DIR__ . '/includes/bs-date.php')) require_once __DIR__ . '/includes/bs-date.php';

// ─── User Auth helpers ────────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']) || !empty($_SESSION['admin_logged_in']);
}

function getCurrentUser(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    ensureUsersTable();
    try {
        $stmt = db()->prepare('SELECT id,name,email,phone,is_admin,created_at FROM users WHERE id=? AND is_active=1 LIMIT 1');
        $stmt->execute([(int)$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (\Throwable $e) { return null; }
}

/**
 * registerUser — creates a new user with a hashed password.
 * Returns ['success'=>true,'user_id'=>int] or ['success'=>false,'message'=>string]
 */
function registerUser(string $email, string $password, string $name, string $phone = ''): array {
    ensureUsersTable();
    $email = strtolower(trim($email));
    $name  = trim($name);
    $phone = trim($phone);

    if (!$name)  return ['success'=>false,'message'=>'नाम आवश्यक छ।'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['success'=>false,'message'=>'सही इमेल ठेगाना लेख्नुस्।'];
    if (strlen($password) < 6) return ['success'=>false,'message'=>'पासवर्ड कम्तीमा ६ अक्षरको हुनु पर्छ।'];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = db()->prepare('INSERT INTO users (name,email,phone,password_hash,is_active,is_verified) VALUES (?,?,?,?,1,0)');
        $stmt->execute([$name, $email, $phone ?: null, $hash]);
        return ['success'=>true,'user_id'=>(int)db()->lastInsertId(),'message'=>'दर्ता सफल भयो!'];
    } catch (\PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE') !== false || strpos($e->getMessage(), 'Duplicate') !== false)
            return ['success'=>false,'message'=>'यो इमेल पहिले नै दर्ता भएको छ। Login गर्नुस्।'];
        error_log('[registerUser] '.$e->getMessage());
        return ['success'=>false,'message'=>'दर्ता गर्न सकिएन। फेरि प्रयास गर्नुस्।'];
    }
}

// ─── IPO BOLD saved list (DB-backed) ─────────────────────────────────────────
function ensureBoldTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS bold_list (
        id          $ai,
        bold        VARCHAR(30) NOT NULL,
        label       VARCHAR(200),
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(bold)
    )" . dbCharset());
    $done = true;
}

function saveBold(string $bold, string $label = ''): void {
    ensureBoldTable();
    $bold = preg_replace('/\D/', '', trim($bold));
    if (strlen($bold) < 10) return;
    $stmt = db()->prepare(dbIgnore().' bold_list (bold,label) VALUES (?,?)');
    try { $stmt->execute([$bold, mb_substr(trim($label),0,150,'UTF-8')]); } catch(\Exception $e){}
}

function getSavedBolds(): array {
    ensureBoldTable();
    return db()->query('SELECT * FROM bold_list ORDER BY created_at DESC')->fetchAll();
}

function deleteBold(string $bold): void {
    ensureBoldTable();
    db()->prepare('DELETE FROM bold_list WHERE bold=?')->execute([preg_replace('/\D/','',$bold)]);
}

// ─── Directory (Nepal Contact Book) ───────────────────────────────────────────
function ensureDirectoryTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS directory (
        id          VARCHAR(100) PRIMARY KEY,
        name        VARCHAR(500)  NOT NULL,
        name_en     VARCHAR(500),
        cat         VARCHAR(60)   NOT NULL DEFAULT 'other',
        phone       TEXT,
        address     VARCHAR(500),
        district    VARCHAR(200),
        website     VARCHAR(500),
        updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_dir_cat',      'directory', 'cat');
    dbIndex('idx_dir_district', 'directory', 'district');
    _seedDirectoryFromJson();
    $done = true;
}

function _seedDirectoryFromJson(): void {
    $json = __DIR__ . '/data/directory.json';
    if (!file_exists($json)) return;
    $count = (int) db()->query('SELECT COUNT(*) FROM directory')->fetchColumn();
    if ($count > 0) return;
    $rows = json_decode(file_get_contents($json), true);
    if (!is_array($rows)) return;
    $stmt = db()->prepare(dbIgnore() . ' directory (id,name,name_en,cat,phone,address,district,website)
        VALUES (?,?,?,?,?,?,?,?)');
    foreach ($rows as $r) {
        $stmt->execute([
            $r['id']      ?? uniqid(),
            $r['name']    ?? '',
            $r['name_en'] ?? '',
            $r['cat']     ?? 'other',
            json_encode($r['phone'] ?? [], JSON_UNESCAPED_UNICODE),
            $r['address'] ?? '',
            $r['district']?? '',
            $r['website'] ?? '',
        ]);
    }
}

function searchDirectory(string $q = '', string $cat = '', string $district = '', int $limit = 80): array {
    ensureDirectoryTable();
    $where = [];
    $params = [];
    if ($cat)      { $where[] = 'cat = ?';                              $params[] = $cat; }
    if ($district) { $where[] = 'district LIKE ?';                      $params[] = '%' . $district . '%'; }
    if ($q) {
        $like = '%' . $q . '%';
        $where[] = '(name LIKE ? OR name_en LIKE ? OR address LIKE ? OR phone LIKE ?)';
        array_push($params, $like, $like, $like, $like);
    }
    $sql = 'SELECT * FROM directory';
    if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
    $sql .= ' ORDER BY CASE cat
        WHEN "emergency"  THEN 1
        WHEN "government" THEN 2
        WHEN "hospital"   THEN 3
        WHEN "bank"       THEN 4
        WHEN "education"  THEN 5
        WHEN "telecom"    THEN 6
        WHEN "utility"    THEN 7
        WHEN "airport"    THEN 8
        WHEN "media"      THEN 9
        ELSE 10 END, name';
    $sql .= ' LIMIT ?';
    $params[] = $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['phone'] = json_decode($r['phone'] ?? '[]', true) ?: [];
    }
    return $rows;
}

function getDirectoryCatCounts(): array {
    ensureDirectoryTable();
    $rows = db()->query('SELECT cat, COUNT(*) as c FROM directory GROUP BY cat')->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['cat']] = (int)$r['c'];
    return $out;
}

// ─── Loksewa Notices (DB-backed) ──────────────────────────────────────────────
function ensureLoksewaTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS loksewa_notices (
        id          $ai,
        title       VARCHAR(1000) NOT NULL,
        link        VARCHAR(1000),
        summary     TEXT,
        source      VARCHAR(200),
        source_url  VARCHAR(500),
        type        VARCHAR(60) DEFAULT 'notice',
        pub_ts      INT DEFAULT 0,
        fetched_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(title, source)
    )" . dbCharset());
    dbIndex('idx_loksewa_type',  'loksewa_notices', 'type');
    dbIndex('idx_loksewa_pub',   'loksewa_notices', 'pub_ts');
    $done = true;
}

function upsertLoksewaNotice(array $n): void {
    ensureLoksewaTable();
    $stmt = db()->prepare(dbIgnore() . ' loksewa_notices
        (title, link, summary, source, source_url, type, pub_ts, fetched_at)
        VALUES (?,?,?,?,?,?,?,CURRENT_TIMESTAMP)');
    $stmt->execute([
        mb_substr($n['title']      ?? '', 0, 900, 'UTF-8'),
        mb_substr($n['link']       ?? '', 0, 900, 'UTF-8'),
        mb_substr($n['summary']    ?? '', 0, 2000, 'UTF-8'),
        $n['source']               ?? '',
        $n['source_url']           ?? '',
        $n['type']                 ?? 'notice',
        (int)($n['pubTs']          ?? 0),
    ]);
}

function getLoksewaNotices(string $type = '', int $limit = 40): array {
    ensureLoksewaTable();
    $total = (int)db()->query('SELECT COUNT(*) FROM loksewa_notices')->fetchColumn();
    // If DB empty or stale (>30 min), trigger a refresh
    $newest = (int)(db()->query('SELECT MAX(fetched_at) FROM loksewa_notices')
        ->fetchColumn() ?: 0);
    if ($total === 0 || (time() - @strtotime($newest)) > 1800) {
        _refreshLoksewaFromApi();
    }
    $sql    = 'SELECT * FROM loksewa_notices';
    $params = [];
    if ($type && $type !== 'all') { $sql .= ' WHERE type = ?'; $params[] = $type; }
    $sql .= ' ORDER BY CASE WHEN pub_ts > 0 THEN pub_ts ELSE fetched_at END DESC LIMIT ?';
    $params[] = $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function _refreshLoksewaFromApi(): void {
    // Call own API endpoint in background (non-blocking)
    $cacheDir = defined('CACHE_DIR') ? CACHE_DIR : __DIR__ . '/data/cache/';
    $lockFile = $cacheDir . 'loksewa_refresh.lock';
    if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 120) return;
    @file_put_contents($lockFile, time());
}

// ─── Offers / Deals (ISP & Companies) ────────────────────────────────────────
function ensureOffersTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS offers (
        id           $ai,
        slug         VARCHAR(200) UNIQUE,
        title        VARCHAR(1000) NOT NULL,
        summary      TEXT,
        company      VARCHAR(200)  NOT NULL,
        cat          VARCHAR(60)   NOT NULL DEFAULT 'telecom',
        badge        VARCHAR(100),
        price        VARCHAR(100),
        old_price    VARCHAR(100),
        discount_pct INT DEFAULT 0,
        valid_until  VARCHAR(100),
        url          VARCHAR(1000),
        is_curated   INT DEFAULT 1,
        is_active    INT DEFAULT 1,
        sort_order   INT DEFAULT 0,
        fetched_at   DATETIME DEFAULT CURRENT_TIMESTAMP
    )" . dbCharset());
    dbIndex('idx_offers_cat',    'offers', 'cat, is_active');
    dbIndex('idx_offers_company','offers', 'company');
    _seedCuratedOffers();
    $done = true;
}

function _seedCuratedOffers(): void {
    $count = (int)db()->query('SELECT COUNT(*) FROM offers WHERE is_curated = 1')->fetchColumn();
    if ($count > 20) return;
    $stmt = db()->prepare(dbIgnore() . ' offers
        (slug,title,summary,company,cat,badge,price,old_price,discount_pct,valid_until,url,is_curated,is_active,sort_order)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,1,1,?)');
    $rows = [
        // ── NTC Mobile Data ──
        ['ntc-1gb-daily',    '१ GB दैनिक डाटा प्याक', '१ GB डाटा — सम्पूर्ण नेपाल, ४G/5G',   'NTC','isp','','रू २९','',0,'','https://www.ntc.net.np/pages/data-offers',1],
        ['ntc-5gb-weekly',   '५ GB साप्ताहिक प्याक',  '७ दिन वैध — ४G LTE स्पिड',             'NTC','isp','','रू ९९','',0,'','https://www.ntc.net.np/pages/data-offers',2],
        ['ntc-12gb-monthly', '१२ GB मासिक प्याक',     '३० दिन वैध, ४G/5G',                    'NTC','isp','Best Value','रू २९९','',0,'','https://www.ntc.net.np/pages/data-offers',3],
        ['ntc-40gb-monthly', '४० GB मासिक प्याक',     '३० दिन वैध, 5G ready',                 'NTC','isp','','रू ४९९','',0,'','https://www.ntc.net.np/pages/data-offers',4],
        ['ntc-unlimited',    'NTC Unlimited Plan',    '१ Mbps Unlimited — ३० दिन',             'NTC','isp','New','रू ३९९','',0,'','https://www.ntc.net.np/pages/data-offers',5],
        // ── NTC Fiber ──
        ['ntc-fiber-100gb',  'NTC FTTH 100GB',        'Fiber 100 GB — गृह र Office को लागि',  'NTC Fiber','isp','','रू ९९९/म','',0,'','https://www.ntc.net.np/pages/fiber',6],
        ['ntc-fiber-unltd',  'NTC FTTH Unlimited',    '25 Mbps Unlimited Fiber',               'NTC Fiber','isp','Popular','रू १४९९/म','',0,'','https://www.ntc.net.np/pages/fiber',7],
        // ── Ncell ──
        ['ncell-1.5gb-daily','१.५ GB दैनिक',          '२४ घण्टा वैध, 4G LTE',                 'Ncell','isp','','रू ३०','',0,'','https://www.ncell.axiata.com/en/personal/offers',10],
        ['ncell-10gb-weekly','१० GB साप्ताहिक',       '७ दिन वैध, 4G',                        'Ncell','isp','','रू १५०','',0,'','https://www.ncell.axiata.com/en/personal/offers',11],
        ['ncell-30gb-monthly','३० GB मासिक',          '३० दिन, 4G LTE',                       'Ncell','isp','','रू ३९९','',0,'','https://www.ncell.axiata.com/en/personal/offers',12],
        ['ncell-60gb-monthly','६० GB मासिक',          '३० दिन, 4G/5G',                        'Ncell','isp','Best','रू ५४९','',0,'','https://www.ncell.axiata.com/en/personal/offers',13],
        ['ncell-unlimited',  'Ncell Unlimited',       '३० दिन Unlimited — 1 Mbps',            'Ncell','isp','Popular','रू ७९९','',0,'','https://www.ncell.axiata.com/en/personal/offers',14],
        ['ncell-social',     'Ncell Social Pack',     'FB, TikTok, Instagram — 1GB/दिन',      'Ncell','isp','Hot','रू १५','',0,'','https://www.ncell.axiata.com/en/personal/offers',15],
        // ── Smart Cell ──
        ['smart-1gb-daily',  'Smart 1 GB Daily',      '१ GB / दिन, 4G LTE',                   'Smart Cell','isp','','रू २५','',0,'','https://www.smartcell.com.np',18],
        ['smart-monthly',    'Smart 30GB Monthly',    '३० GB / ३० दिन',                       'Smart Cell','isp','','रू ३४९','',0,'','https://www.smartcell.com.np',19],
        // ── WorldLink ──
        ['worldlink-15',     'WorldLink 15Mbps',      'Unlimited Fiber — घर, Office',         'WorldLink','isp','','रू ७९९/म','',0,'','https://worldlink.com.np/offers',20],
        ['worldlink-25',     'WorldLink 25Mbps',      'Unlimited Fiber — 25 Mbps',            'WorldLink','isp','Popular','रू ९९९/म','',0,'','https://worldlink.com.np/offers',21],
        ['worldlink-50',     'WorldLink 50Mbps',      'Unlimited Fiber — 50 Mbps',            'WorldLink','isp','','रू १२९९/म','',0,'','https://worldlink.com.np/offers',22],
        ['worldlink-100',    'WorldLink 100Mbps',     'Unlimited Fiber — 100 Mbps',           'WorldLink','isp','','रू १७९९/म','',0,'','https://worldlink.com.np/offers',23],
        // ── Subisu ──
        ['subisu-15',        'Subisu 15Mbps',         'Unlimited Cable/Fiber',                'Subisu','isp','','रू ७००/म','',0,'','https://subisu.net.np/offer',25],
        ['subisu-25',        'Subisu 25Mbps',         'Unlimited — Family Package',           'Subisu','isp','','रू ९००/म','',0,'','https://subisu.net.np/offer',26],
        ['subisu-50',        'Subisu 50Mbps',         'Unlimited Fiber — High Speed',         'Subisu','isp','','रू ११००/म','',0,'','https://subisu.net.np/offer',27],
        // ── Vianet ──
        ['vianet-25',        'Vianet 25Mbps',         'Unlimited Fiber Internet',             'Vianet','isp','','रू ९९९/म','',0,'','https://vianet.com.np',28],
        ['vianet-50',        'Vianet 50Mbps',         'Unlimited — Business/Home',            'Vianet','isp','','रू १२९९/म','',0,'','https://vianet.com.np',29],
        // ── Classic Tech ──
        ['classic-15',       'Classic Tech 15Mbps',   'Unlimited Fiber',                      'Classic Tech','isp','','रू ७००/म','',0,'','https://www.classictech.com.np',30],
        ['classic-25',       'Classic Tech 25Mbps',   'Unlimited Fiber',                      'Classic Tech','isp','','रू ९००/म','',0,'','https://www.classictech.com.np',31],
        // ── Daraz ──
        ['daraz-flash',      'Daraz Flash Sale',      'दैनिक Flash Sale — ७०% सम्म छुट',     'Daraz','ecommerce','🔥 Flash','','',70,'दैनिक','https://www.daraz.com.np/campaigns/flash-sale/',35],
        ['daraz-payday',     'Daraz Payday Sale',     'महिनाको अन्त्यमा Big Offers',          'Daraz','ecommerce','Monthly','','',60,'','https://www.daraz.com.np/campaigns/',36],
        ['daraz-app',        'Daraz App-Only Deal',   'App बाट Extra ५% छुट',                'Daraz','ecommerce','App Only','','',5,'','https://www.daraz.com.np',37],
        // ── SastoDeal ──
        ['sastodeal-deal',   'SastoDeal आजको अफर',   'Electronics, Fashion, Food — Best Price','SastoDeal','ecommerce','Deal','','',50,'','https://www.sastodeal.com',38],
        // ── eSewa/Khalti cashback ──
        ['esewa-cashback',   'eSewa Cashback Offer',  'Bill payment र Recharge मा Cashback',  'eSewa','fintech','💰 Cash','','',10,'','https://esewa.com.np',40],
        ['khalti-cashback',  'Khalti Cashback',       'Khalti बाट payment मा Cashback',       'Khalti','fintech','💰 Cash','','',10,'','https://khalti.com',41],
        ['imepay-cashback',  'IME Pay Offer',         'Recharge, Bill, Booking मा offer',     'IME Pay','fintech','','','',0,'','https://imepay.com.np',42],
        // ── Food Delivery ──
        ['foodmandu-code',   'Foodmandu Discount',    'COUPON CODE मा order मा छुट',          'Foodmandu','food','🍕','','',20,'','https://foodmandu.com',45],
        ['pathao-food',      'Pathao Food Offer',     'New User + Existing offers',           'Pathao','food','','','',30,'','https://pathao.com',46],
        ['bhojdeals',        'BhojDeals Restaurant',  'Restaurant dining offers — काठमाडौं', 'BhojDeals','food','','','',25,'','https://bhojdeals.com',47],
        // ── Travel ──
        ['buddha-air-sale',  'Buddha Air Early Bird', 'Domestic flight — Early booking छुट',  'Buddha Air','travel','✈️','','',20,'','https://www.buddhaair.com',50],
        ['yeti-discount',    'Yeti Airlines Offer',   'Online booking मा Special Fare',       'Yeti Airlines','travel','','','',15,'','https://www.yetiairlines.com',51],
        // ── Banks ──
        ['nabil-emi',        'Nabil EMI Offer',       'Credit Card EMI — 0% Interest',        'Nabil Bank','bank','0% EMI','','',0,'','https://www.nabilbank.com/offers',55],
        ['himalayan-cc',     'Himalayan Credit Card', 'Shopping मा 5% Cashback',             'Himalayan Bank','bank','5% Cash','','',5,'','https://www.himalayanbank.com',56],
        ['everest-offer',    'Everest Bank Offer',    'Festival discount — Home Loan',        'Everest Bank','bank','Festival','','',0,'','https://www.everestbankltd.com',57],
    ];
    foreach ($rows as $r) {
        try { $stmt->execute($r); } catch (\Exception $e) {}
    }
}

function getActiveOffers(string $cat = '', string $company = '', int $limit = 60): array {
    ensureOffersTable();
    $where  = ['is_active = 1'];
    $params = [];
    if ($cat)     { $where[] = 'cat = ?';          $params[] = $cat; }
    if ($company) { $where[] = 'company LIKE ?';   $params[] = '%' . $company . '%'; }
    $sql = 'SELECT * FROM offers WHERE ' . implode(' AND ', $where)
         . ' ORDER BY sort_order ASC, fetched_at DESC LIMIT ?';
    $params[] = $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function upsertOffer(array $o): void {
    ensureOffersTable();
    $slug = preg_replace('/[^a-z0-9-]/', '-', strtolower($o['slug'] ?? uniqid()));
    $stmt = db()->prepare('INSERT INTO offers
        (slug,title,summary,company,cat,badge,price,old_price,discount_pct,valid_until,url,is_curated,is_active,sort_order,fetched_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,0,1,100,CURRENT_TIMESTAMP)
        ON CONFLICT(slug) DO UPDATE SET
        title=excluded.title, summary=excluded.summary,
        badge=excluded.badge, price=excluded.price,
        discount_pct=excluded.discount_pct,
        valid_until=excluded.valid_until,
        fetched_at=CURRENT_TIMESTAMP');
    try {
        $stmt->execute([
            $slug,
            mb_substr($o['title']       ?? '', 0, 800, 'UTF-8'),
            mb_substr($o['summary']     ?? '', 0, 2000, 'UTF-8'),
            $o['company']               ?? '',
            $o['cat']                   ?? 'other',
            $o['badge']                 ?? '',
            $o['price']                 ?? '',
            $o['old_price']             ?? '',
            (int)($o['discount_pct']    ?? 0),
            $o['valid_until']           ?? '',
            $o['url']                   ?? '',
        ]);
    } catch (\Exception $e) { error_log('[upsertOffer] ' . $e->getMessage()); }
}

function getOfferCatCounts(): array {
    ensureOffersTable();
    $rows = db()->query('SELECT cat, COUNT(*) as c FROM offers WHERE is_active=1 GROUP BY cat')->fetchAll();
    $out  = [];
    foreach ($rows as $r) $out[$r['cat']] = (int)$r['c'];
    return $out;
}
