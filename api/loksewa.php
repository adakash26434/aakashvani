<?php
/**
 * api/loksewa.php v2 — DB-backed Loksewa notices
 * Fetches PSC + RSS feeds → stores in DB → serves JSON
 * ?type=notice|vacancy|result|syllabus|all  ?limit=40
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: *');
@ini_set('default_socket_timeout', 8);

require_once __DIR__ . '/../config.php';

$type  = strtolower(trim($_GET['type']  ?? 'all'));
$limit = max(1, min(80, (int)($_GET['limit'] ?? 40)));

/* ── helpers ──────────────────────────────────────────────────────── */
function lk_json2(array $payload): void {
    while (ob_get_level() > 0) ob_end_clean();
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function lk_db2(): ?PDO {
    static $pdo = false;
    if ($pdo instanceof PDO) return $pdo;
    if ($pdo === null) return null;
    try {
        if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            // charset in DSN alone is not enough on some cPanel servers — must also SET NAMES
            $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
            }
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET, DB_USER, DB_PASS, $opts);
            // Double-safety — prevents ?????? for Nepali text on latin1 servers
            try { $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"); } catch (\Throwable $e) {}
            try { $pdo->exec("SET CHARACTER SET utf8mb4"); } catch (\Throwable $e) {}
        }
        return $pdo;
    } catch (Throwable $e) {
        error_log('Loksewa DB unavailable: ' . $e->getMessage());
        $pdo = null;
        return null;
    }
}

function lk_ensure_db2(PDO $db): void {
    $isMysql = defined('DB_DRIVER') && DB_DRIVER === 'mysql';
    $ai      = $isMysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    // ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ensures Nepali text is stored correctly
    // even when the cPanel database default charset is latin1.
    $charset = $isMysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $db->exec("CREATE TABLE IF NOT EXISTS loksewa_notices (
        id $ai,
        title VARCHAR(500) NOT NULL,
        link TEXT,
        summary TEXT,
        source VARCHAR(120),
        source_url TEXT,
        type VARCHAR(40) DEFAULT 'notice',
        pub_ts INTEGER DEFAULT 0,
        fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )$charset");
}

function lk_fetch2(string $url, int $timeout = 8): string {
    $ctx = stream_context_create([
        'http'  => ['timeout' => $timeout, 'user_agent' => 'Mozilla/5.0 AakashvaniBot/2.0', 'follow_location' => 1, 'header' => "Accept-Charset: utf-8\r\n"],
        'https' => ['timeout' => $timeout, 'user_agent' => 'Mozilla/5.0 AakashvaniBot/2.0', 'follow_location' => 1, 'header' => "Accept-Charset: utf-8\r\n"],
    ]);
    $r = @file_get_contents($url, false, $ctx);
    if ($r === false || $r === '') return '';
    // Detect charset & normalize to UTF-8 to prevent ?????? in Nepali text
    $enc = null;
    if (preg_match('/<\?xml[^>]+encoding=["\']([^"\']+)["\']/i', $r, $m)) $enc = strtoupper(trim($m[1]));
    if (!$enc && preg_match('/<meta[^>]+charset=["\']?([^"\'>\s]+)/i', $r, $m)) $enc = strtoupper(trim($m[1]));
    if (!$enc) {
        $det = @mb_detect_encoding($r, ['UTF-8','ISO-8859-1','WINDOWS-1252'], true);
        $enc = $det ?: 'UTF-8';
    }
    if ($enc !== 'UTF-8' && $enc !== 'UTF8') {
        $conv = @mb_convert_encoding($r, 'UTF-8', $enc);
        if ($conv !== false) $r = $conv;
    }
    // Strip XML encoding decl so simplexml doesn't reject our re-encoded string
    $r = preg_replace('/<\?xml[^>]+encoding=["\'][^"\']+["\']/i', '<?xml version="1.0" encoding="UTF-8"', $r, 1);
    return $r;
}

function lk_classify2(string $title): string {
    $t = mb_strtolower($title, 'UTF-8');
    foreach (['vacancy','रिक्त','दरखास्त','विज्ञापन','जागिर','भर्ना','job','bharti','bigyapan','post']
        as $w) if (mb_strpos($t, $w) !== false) return 'vacancy';
    foreach (['result','नतिजा','नाम निकाल','उत्तीर्ण','pass','merit','सिफारिस','final list']
        as $w) if (mb_strpos($t, $w) !== false) return 'result';
    foreach (['syllabus','पाठ्यक्रम','curriculum','course']
        as $w) if (mb_strpos($t, $w) !== false) return 'syllabus';
    return 'notice';
}

function lk_clean2(string $html, int $max = 300): string {
    $t = strip_tags(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
    $t = preg_replace('/\s+/u', ' ', trim($t));
    if (mb_strlen($t, 'UTF-8') <= $max) return $t;
    $cut = mb_substr($t, 0, $max, 'UTF-8');
    $sp  = mb_strrpos($cut, ' ', 0, 'UTF-8');
    return ($sp > 60 ? mb_substr($cut, 0, $sp, 'UTF-8') : $cut) . '…';
}

function lk_parse_rss2(string $xml, string $source, string $srcUrl): array {
    if (!$xml) return [];
    libxml_use_internal_errors(true);
    $sx = @simplexml_load_string($xml);
    if (!$sx) return [];
    $ns    = $sx->getNamespaces(true);
    $items = [];
    foreach (($sx->channel->item ?? $sx->entry ?? []) as $e) {
        $title = trim((string)$e->title);
        if (!$title) continue;
        $link = trim((string)$e->link);
        if (!$link && isset($e->link['href'])) $link = (string)$e->link['href'];
        $desc  = (string)($e->description ?? '');
        if (!$desc && isset($ns['content'])) {
            $c = $e->children($ns['content']);
            if (isset($c->encoded)) $desc = (string)$c->encoded;
        }
        $pubRaw = (string)($e->pubDate ?? $e->published ?? '');
        $pubTs  = $pubRaw ? (int)@strtotime($pubRaw) : 0;
        $items[] = ['title'=>$title,'link'=>$link ?: $srcUrl,'summary'=>lk_clean2($desc),'source'=>$source,'source_url'=>$srcUrl,'type'=>lk_classify2($title),'pubTs'=>$pubTs];
    }
    return $items;
}

function lk_collect_live2(int $limit = 40): array {
    global $lkKw;
    $fetched = [];

    $html = lk_fetch2('https://www.psc.gov.np/en/notice', 10);
    if ($html) {
        preg_match_all('/<a\s[^>]*href=["\']([^"\']*\/notice\/[^"\']+)["\']\s*[^>]*>(.*?)<\/a>/si', $html, $m);
        for ($i = 0; $i < min(25, count($m[1])); $i++) {
            $title = trim(strip_tags($m[2][$i]));
            if (!$title || mb_strlen($title, 'UTF-8') < 8) continue;
            $path = $m[1][$i];
            $url = strpos($path, 'http') === 0 ? $path : 'https://www.psc.gov.np' . $path;
            $fetched[] = ['title'=>$title,'link'=>$url,'summary'=>'','source'=>'PSC Nepal','source_url'=>'https://www.psc.gov.np/en/notice','type'=>lk_classify2($title),'pubTs'=>0];
        }
    }

    $feeds = [
        ['OnlineKhabar', 'https://www.onlinekhabar.com/content/job-vacancy/feed', 'https://www.onlinekhabar.com'],
        ['Gorkhapatra',  'https://gorkhapatraonline.com/feed',                    'https://gorkhapatraonline.com'],
        ['Kantipur',     'https://ekantipur.com/feed',                            'https://ekantipur.com'],
        ['Ratopati',     'https://ratopati.com/feed',                             'https://ratopati.com'],
    ];
    foreach ($feeds as [$src, $feedUrl, $srcUrl]) {
        foreach (lk_parse_rss2(lk_fetch2($feedUrl, 6), $src, $srcUrl) as $it) {
            $hay = mb_strtolower($it['title'] . ' ' . $it['summary'], 'UTF-8');
            foreach ($lkKw as $kw) {
                if (mb_strpos($hay, mb_strtolower($kw, 'UTF-8')) !== false) {
                    $fetched[] = $it;
                    break;
                }
            }
        }
    }

    $seen = [];
    $out = [];
    foreach ($fetched as $n) {
        $key = md5(mb_strtolower(preg_replace('/\s+/u', '', $n['title']), 'UTF-8'));
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = $n;
        if (count($out) >= $limit) break;
    }
    return $out;
}

// Loksewa keywords for RSS filtering
$lkKw = ['लोकसेवा','lok sewa','loksewa','psc','आयोग','vacancy','रिक्त','दरखास्त','भर्ना','विज्ञापन','job','जागिर','bigyapan',
          'result','नतिजा','नाम निकाल','उत्तीर्ण','सिफारिस','मेरिट','syllabus','पाठ्यक्रम','exam','परीक्षा','interview',
          'सरकारी','government job','civil service','निजामती','kharidar','खरिदार','teacher','शिक्षक','police','प्रहरी'];

$db = lk_db2();
$items = [];
$total = 0;
$stale = true;

if ($db) {
    lk_ensure_db2($db);
    $dbCount = (int) $db->query('SELECT COUNT(*) FROM loksewa_notices')->fetchColumn();
    $newest  = $db->query('SELECT MAX(fetched_at) FROM loksewa_notices')->fetchColumn();
    $stale   = $newest ? (time() - (int)@strtotime($newest)) > 1800 : true;
} else {
    $dbCount = 0;
}

if ($db && ($dbCount === 0 || $stale)) {
    foreach (lk_collect_live2(50) as $n) {
        try {
            $ignore = (defined('DB_DRIVER') && DB_DRIVER === 'mysql') ? 'INSERT IGNORE INTO' : 'INSERT OR IGNORE INTO';
            $stmt = $db->prepare($ignore . ' loksewa_notices (title, link, summary, source, source_url, type, pub_ts, fetched_at) VALUES (?,?,?,?,?,?,?,CURRENT_TIMESTAMP)');
            $stmt->execute([$n['title'], $n['link'], $n['summary'], $n['source'], $n['source_url'], $n['type'], (int)($n['pubTs'] ?? 0)]);
        } catch (Throwable $e) {
            error_log('Loksewa upsert failed: ' . $e->getMessage());
        }
    }
}

if ($db) {
    $params = [];
    $where = '';
    if ($type && $type !== 'all') {
        $where = ' WHERE type = ?';
        $params[] = $type;
    }
    $stmt = $db->prepare("SELECT title, link, summary, source, source_url, type, pub_ts, fetched_at FROM loksewa_notices $where ORDER BY fetched_at DESC, pub_ts DESC LIMIT " . (int)$limit);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = (int) $db->query('SELECT COUNT(*) FROM loksewa_notices')->fetchColumn();

    // ── Self-healing: if stored titles are corrupted (?????? from latin1 charset
    //    mismatch before the utf8mb4 fix), clear the table and force a re-fetch.
    $sampleTitle = $items[0]['title'] ?? '';
    if ($sampleTitle && preg_match('/\?{4,}/', $sampleTitle)) {
        try { $db->exec("DELETE FROM loksewa_notices"); } catch (\Throwable $e) {}
        $items = [];
        $stale = true;
    }
}

if (!$items) {
    $items = lk_collect_live2($limit);
    if ($type && $type !== 'all') {
        $items = array_values(array_filter($items, fn($it) => ($it['type'] ?? 'notice') === $type));
    }
    $total = count($items);
}

// Enrich items with time ago
foreach ($items as &$it) {
    $ts = (int)($it['pub_ts'] ?? 0);
    if (!$ts) $ts = (int)@strtotime($it['fetched_at'] ?? '');
    $diff = $ts ? (time() - $ts) : 0;
    $it['ago'] = !$diff ? '' : ($diff < 60 ? 'भर्खरै' :
        ($diff < 3600 ? floor($diff/60).' मिनेट अघि' :
        ($diff < 86400 ? floor($diff/3600).' घण्टा अघि' :
        floor($diff/86400).' दिन अघि')));
}

lk_json2([
    'ok'    => true,
    'type'  => $type,
    'count' => count($items),
    'total' => $total,
    'mode'  => $db ? 'database' : 'live-fallback',
    'items' => $items,
]);
