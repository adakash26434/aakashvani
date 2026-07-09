<?php
/**
 * आकाशवाणी - Functions
 * Database Query Functions
 */

// Get Published News
if (!function_exists('getPublishedNews')) {
    function getPublishedNews($category = null, $source = null, $limit = 20, $offset = 0) {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $where = "WHERE status = 'published'";
        $params = [];
        
        if ($category) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        if ($source) {
            $where .= " AND source = ?";
            $params[] = $source;
        }
        
        $sql = "SELECT id, title, slug, summary, content, image, category, source, source_name, published_at, view_count 
                FROM news 
                $where 
                ORDER BY published_at DESC 
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Get News by Slug
if (!function_exists('getNewsBySlug')) {
    function getNewsBySlug($slug) {
        $pdo = getDB();
        if (!$pdo) return null;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}

// Get Related News
if (!function_exists('getRelatedNews')) {
    function getRelatedNews($id = 0, $category = '', $limit = 4) {
        $pdo = getDB();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->prepare("
                SELECT id, title, slug, image, category, published_at 
                FROM news 
                WHERE status = 'published' AND id != ? AND category = ?
                ORDER BY published_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$id, $category, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Get Categories
if (!function_exists('getCategories')) {
    function getCategories() {
        $pdo = getDB();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->query("SELECT DISTINCT category FROM news WHERE status = 'published' ORDER BY category");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Get Market Data (from cache)
if (!function_exists('getMarketData')) {
    function getMarketData() {
        $cacheFile = __DIR__ . '/data/cache/market.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
            return json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        return [];
    }
}

// Get IPO List (from cache)
if (!function_exists('getIPOList')) {
    function getIPOList() {
        $cacheFile = __DIR__ . '/data/cache/ipo-list.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
            return json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        return [];
    }
}

// Update View Count
if (!function_exists('incrementViewCount')) {
    function incrementViewCount($newsId) {
        $pdo = getDB();
        if (!$pdo) return false;
        
        try {
            $stmt = $pdo->prepare("UPDATE news SET view_count = view_count + 1 WHERE id = ?");
            return $stmt->execute([$newsId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Search News
if (!function_exists('searchNews')) {
    function searchNews($query, $limit = 20) {
        if (mb_strlen(trim($query ?? ''), 'UTF-8') < 2) return [];
        $pdo = getDB();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->prepare("
                SELECT id, title, slug, image, category, published_at 
                FROM news 
                WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)
                ORDER BY published_at DESC 
                LIMIT ?
            ");
            $search = '%' . $query . '%';
            $stmt->execute([$search, $search, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Security Headers for APIs
if (!function_exists('sendSecurityHeaders')) {
    function sendSecurityHeaders(): void {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        // Content Security Policy — restrict scripts to self + inline for Lucide CDN
        // Note: 'unsafe-inline' needed for inline <script> blocks. For better security, use nonces.
        $siteHost = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';
        $connectSrc = "'self' https://*.{$siteHost} https:";
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src {$connectSrc}; frame-ancestors 'self';");
        // HSTS: Force HTTPS for 1 year (uncomment when SSL is properly configured)
        // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Rate limiting check
if (!function_exists('checkRateLimit')) {
    function checkRateLimit(string $key, int $maxRequests = 60, int $window = 60): bool {
        $cacheDir = __DIR__ . '/data/cache/';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        $file = $cacheDir . 'rate_' . md5($key) . '.json';
        $now = time();
        
        $data = ['count' => 0, 'window' => $now];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: $data;
        }
        
        if ($now - $data['window'] > $window) {
            $data = ['count' => 0, 'window' => $now];
        }
        
        $data['count']++;
        
        if ($data['count'] > $maxRequests) {
            return false;
        }
        
        file_put_contents($file, json_encode($data));
        return true;
    }
}

// Slugify — URL-safe Nepali/English slug generator
if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = preg_replace('/[\x{200c}\x{200d}]/u', '', $text);
        $replace = [
            'आ' => 'a', 'अ' => 'a', 'इ' => 'i', 'ई' => 'i', 'उ' => 'u', 'ऊ' => 'u',
            'ए' => 'e', 'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au', 'ऋ' => 'ri',
            'ा' => 'aa', 'ि' => 'i', 'ी' => 'i', 'ु' => 'u', 'ू' => 'u',
            'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au', 'ृ' => 'ri',
            '्' => '', 'ं' => '', 'ँ' => '',
            'क' => 'ka', 'ख' => 'kha', 'ग' => 'ga', 'घ' => 'gha',
            'च' => 'cha', 'छ' => 'chha', 'ज' => 'ja', 'झ' => 'jha',
            'ट' => 'ta', 'ठ' => 'tha', 'ड' => 'da', 'ढ' => 'dha',
            'ण' => 'na', 'त' => 'ta', 'थ' => 'tha', 'द' => 'da', 'ध' => 'dha',
            'न' => 'na', 'प' => 'pa', 'फ' => 'pha', 'ब' => 'ba', 'भ' => 'bha',
            'म' => 'ma', 'य' => 'ya', 'र' => 'ra', 'ल' => 'la', 'व' => 'wa',
            'श' => 'sha', 'ष' => 'shha', 'स' => 'sa', 'ह' => 'ha',
        ];
        foreach ($replace as $from => $to) {
            $text = str_replace($from, $to, $text);
        }
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
        $text = preg_replace('/\s+/', '-', trim($text));
        $text = preg_replace('/-+/', '-', $text);
        return strtolower(trim($text, '-')) ?: 'post';
    }
}

// ── ensureNewsTable — creates unified news table ─────────────────────────
// Called by news-rss.php, cron/ai-sync.php, admin/dashboard.php
// Uses 'news' table (not tech_news) to match query expectations
if (!function_exists('ensureNewsTable')) {
    function ensureNewsTable(): void {
        $pdo = db();
        if (!$pdo) return;
        try {
            // Create news table with unified schema supporting both
            // getPublishedNews() and data-manager.php queries
            $pdo->exec("CREATE TABLE IF NOT EXISTS `news` (
                `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `title`           VARCHAR(500) NOT NULL,
                `slug`            VARCHAR(500) NOT NULL,
                `excerpt`         TEXT,
                `summary`         TEXT,
                `content`         LONGTEXT,
                `image`           VARCHAR(700),
                `image_url`       VARCHAR(700),
                `category`        VARCHAR(60) NOT NULL DEFAULT 'general',
                `lang`            VARCHAR(5)  NOT NULL DEFAULT 'ne',
                `source`          VARCHAR(100),
                `source_name`     VARCHAR(120),
                `source_url`      VARCHAR(700),
                `author`          VARCHAR(200),
                `url_hash`        VARCHAR(64),
                `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
                `is_published`   TINYINT(1) NOT NULL DEFAULT 1,
                `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
                `is_breaking`     TINYINT(1) NOT NULL DEFAULT 0,
                `view_count`      INT UNSIGNED NOT NULL DEFAULT 0,
                `ai_processed`    TINYINT(1) NOT NULL DEFAULT 0,
                `content_status`  VARCHAR(20) NOT NULL DEFAULT 'unknown',
                `content_length`  INT NOT NULL DEFAULT 0,
                `scrape_status`   VARCHAR(20) NOT NULL DEFAULT 'pending',
                `scrape_error`    TEXT,
                `last_scraped_at` DATETIME DEFAULT NULL,
                `published_at`    DATETIME DEFAULT NULL,
                `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE  KEY `uq_slug`         (`slug`),
                UNIQUE  KEY `uq_url_hash`      (`url_hash`),
                UNIQUE  KEY `uq_source_guid`   (`source_name`, `source_url`),
                KEY     `idx_status_pub_date`  (`status`, `published_at`),
                KEY     `idx_published`        (`is_published`),
                KEY     `idx_news_category`    (`category`),
                KEY     `idx_news_source_date` (`source_name`, `created_at`),
                KEY     `idx_news_view_count`  (`view_count`),
                FULLTEXT KEY `ft_title_summary`(`title`, `summary`, `excerpt`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            error_log('[ensureNewsTable] ' . $e->getMessage());
        }
    }
}

// ── ensureRashifalTable — creates rashifal_daily if it doesn't exist ──────
if (!function_exists('ensureRashifalTable')) {
    function ensureRashifalTable(): void {
        $pdo = db();
        if (!$pdo) return;
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `rashifal_daily` (
                `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `sign`      VARCHAR(60) NOT NULL,
                `lang`      VARCHAR(5) NOT NULL DEFAULT 'ne',
                `date_bs`   DATE NOT NULL,
                `date_ad`   DATE,
                `overall`   TEXT,
                `love`      TEXT,
                `career`    TEXT,
                `health`    TEXT,
                `lucky_num` VARCHAR(10),
                `lucky_col` VARCHAR(30),
                `compatible` VARCHAR(60),
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_rashifal` (`sign`, `lang`, `date_bs`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {
            error_log('[ensureRashifalTable] ' . $e->getMessage());
        }
    }
}

if (!function_exists('ensureStoriesTable')) {
    function ensureStoriesTable(): void {
        $pdo = db();
        if (!$pdo) return;
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `stories` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `title`       VARCHAR(500) NOT NULL,
                `slug`        VARCHAR(500) UNIQUE,
                `excerpt`     TEXT,
                `content`     LONGTEXT,
                `category`    VARCHAR(100) DEFAULT 'general',
                `source`      VARCHAR(100),
                `source_url`  VARCHAR(500),
                `image_url`   VARCHAR(500),
                `is_published` TINYINT(1) DEFAULT 0,
                `views`       INT UNSIGNED DEFAULT 0,
                `tags`        TEXT,
                `tags_en`     TEXT,
                `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_published` (`is_published`),
                INDEX `idx_category` (`category`),
                INDEX `idx_views` (`views`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {
            error_log('[ensureStoriesTable] ' . $e->getMessage());
        }
    }
}

if (!function_exists('ensureOffersTable')) {
    function ensureOffersTable(): void {
        $pdo = db();
        if (!$pdo) return;
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `offers` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `slug`         VARCHAR(255) UNIQUE,
                `title`        VARCHAR(500) NOT NULL,
                `summary`      TEXT,
                `company`      VARCHAR(120),
                `category`     VARCHAR(60) DEFAULT 'general',
                `badge`        VARCHAR(60),
                `price`        VARCHAR(60),
                `discount_pct` INT UNSIGNED DEFAULT 0,
                `url`          TEXT,
                `image_url`    VARCHAR(500),
                `is_curated`   TINYINT(1) DEFAULT 0,
                `is_active`    TINYINT(1) DEFAULT 1,
                `valid_until`  DATETIME,
                `fetched_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_active` (`is_active`),
                INDEX `idx_category` (`category`),
                INDEX `idx_company` (`company`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {
            error_log('[ensureOffersTable] ' . $e->getMessage());
        }
    }
}

if (!function_exists('upsertOffer')) {
    function upsertOffer(array $data): void {
        $pdo = db();
        if (!$pdo) return;
        try {
            $ignore = (defined('DB_DRIVER') && DB_DRIVER === 'mysql') ? 'INSERT IGNORE INTO' : 'INSERT OR IGNORE INTO';
            $stmt = $pdo->prepare("$ignore offers (slug, title, summary, company, category, badge, price, discount_pct, url, is_curated, fetched_at)
                VALUES (:slug, :title, :summary, :company, :cat, :badge, :price, :discount_pct, :url, 0, NOW())
                ON DUPLICATE KEY UPDATE title=VALUES(title), summary=VALUES(summary), fetched_at=NOW()");
            $stmt->execute([
                ':slug'   => $data['slug'] ?? '',
                ':title'  => $data['title'] ?? '',
                ':summary'=> $data['summary'] ?? '',
                ':company'=> $data['company'] ?? '',
                ':cat'    => $data['cat'] ?? 'general',
                ':badge'  => $data['badge'] ?? '',
                ':price'  => $data['price'] ?? '',
                ':discount_pct' => (int)($data['discount_pct'] ?? 0),
                ':url'    => $data['url'] ?? '',
            ]);
        } catch (Throwable $e) {
            error_log('[upsertOffer] ' . $e->getMessage());
        }
    }
}

if (!function_exists('getActiveOffers')) {
    function getActiveOffers(string $cat = '', string $company = '', int $limit = 60): array {
        $pdo = db();
        if (!$pdo) return [];
        try {
            $sql = 'SELECT * FROM offers WHERE is_active=1';
            $params = [];
            if ($cat && $cat !== 'all') { $sql .= ' AND category=?'; $params[] = $cat; }
            if ($company) { $sql .= ' AND company=?'; $params[] = $company; }
            $sql .= ' ORDER BY fetched_at DESC LIMIT ' . (int)$limit;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[getActiveOffers] ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getOfferCatCounts')) {
    function getOfferCatCounts(): array {
        $pdo = db();
        if (!$pdo) return [];
        try {
            $rows = $pdo->query('SELECT category, COUNT(*) c FROM offers WHERE is_active=1 GROUP BY category')->fetchAll(PDO::FETCH_ASSOC);
            $out = [];
            foreach ($rows as $r) $out[$r['category']] = (int)$r['c'];
            return $out;
        } catch (Throwable $e) {
            return [];
        }
    }
}

// ── Flash Messages ────────────────────────────────────────────────────────────
if (!function_exists('flash')) {
    function flash(string $message, string $type = 'success'): void {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
}

if (!function_exists('getFlash')) {
    function getFlash(): ?array {
        if (empty($_SESSION['flash'])) return null;
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}

if (!function_exists('showFlash')) {
    function showFlash(): string {
        $flash = getFlash();
        if (!$flash) return '';
        $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        $class = $type === 'error' ? 'flash-error' : 'flash-success';
        return "<div class=\"$class\">$message</div>";
    }
}

// ── Brand Helpers ─────────────────────────────────────────────────────────────
if (!function_exists('brandName')) {
    function brandName(): string {
        return defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी';
    }
}

if (!function_exists('brandLogoUrl')) {
    function brandLogoUrl(): string {
        return defined('SITE_LOGO') ? SITE_LOGO : '/favicon.svg';
    }
}

if (!function_exists('brandInitials')) {
    function brandInitials(): string {
        $name = brandName();
        // Extract Devanagari or Latin initials
        if (preg_match('/^[आ-ह]/u', $name)) {
            return mb_substr($name, 0, 1, 'UTF-8');
        }
        $words = preg_split('/\s+/', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_substr($word, 0, 1, 'UTF-8');
        }
        return strtoupper($initials) ?: 'आ';
    }
}

// ── DataManager Singleton Alias ───────────────────────────────────────────────
if (!function_exists('dataManager')) {
    function dataManager() {
        return DataManager::getInstance();
    }
}
