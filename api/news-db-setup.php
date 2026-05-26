<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('X-Robots-Tag: noindex');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

session_start();

$action = strtolower(trim($_GET['action'] ?? 'status'));
$confirm = trim($_GET['confirm'] ?? '');
$cronKey = defined('CRON_KEY') ? (string)CRON_KEY : '';
$reqKey = (string)($_GET['key'] ?? $_GET['cron_key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$isAdmin = !empty($_SESSION['nh_admin']) || !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['is_admin']);
$isCron = $cronKey !== '' && hash_equals($cronKey, $reqKey);
$isAllowed = $isAdmin || $isCron || $confirm === 'CONFIRM';

if (!$isAllowed) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'error' => 'Unauthorized',
        'usage' => '/api/news-db-setup.php?action=status|migrate|reset|fresh&confirm=CONFIRM'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($action) {
        case 'status':
            $result = newsDbStatus();
            break;
        case 'migrate':
            $result = newsDbMigrate(false);
            break;
        case 'reset':
            $result = newsDbReset(false);
            break;
        case 'fresh':
            $result = newsDbFresh();
            break;
        default:
            $result = ['ok' => false, 'error' => 'Unknown action'];
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

function newsDbStatus(): array {
    ensureNewsTable();
    ensureRashifalTable();
    $pdo = db();
    $count = (int)$pdo->query('SELECT COUNT(*) FROM tech_news')->fetchColumn();
    $quality = [];
    try {
        $rows = $pdo->query('SELECT content_status, COUNT(*) c FROM tech_news GROUP BY content_status')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) $quality[$row['content_status'] ?: 'unknown'] = (int)$row['c'];
    } catch (Throwable $e) {}

    return [
        'ok' => true,
        'action' => 'status',
        'articles' => $count,
        'quality' => $quality,
        'rashifal_days' => (int)$pdo->query('SELECT COUNT(*) FROM rashifal_daily')->fetchColumn(),
        'next' => [
            'migrate' => '/api/news-db-setup.php?action=migrate&confirm=CONFIRM',
            'fresh_reset' => '/api/news-db-setup.php?action=fresh&confirm=CONFIRM'
        ]
    ];
}

function newsDbMigrate(bool $dropFirst): array {
    $pdo = db();
    $done = [];

    if ($dropFirst) {
        try { $pdo->exec('DROP TABLE IF EXISTS tech_news'); $done[] = 'drop:tech_news'; } catch (Throwable $e) {}
        try { $pdo->exec('DROP TABLE IF EXISTS news_sync_log'); $done[] = 'drop:news_sync_log'; } catch (Throwable $e) {}
        try { $pdo->exec('DROP TABLE IF EXISTS rashifal_daily'); $done[] = 'drop:rashifal_daily'; } catch (Throwable $e) {}
    }

    ensureNewsTable();
    $done[] = 'ensure:tech_news';
    ensureRashifalTable();
    $done[] = 'ensure:rashifal_daily';

    $columns = [
        'content_status' => "VARCHAR(20) NOT NULL DEFAULT 'unknown'",
        'content_length' => 'INT NOT NULL DEFAULT 0',
        'scrape_status' => "VARCHAR(20) NOT NULL DEFAULT 'pending'",
        'scrape_error' => 'TEXT',
        'last_scraped_at' => 'DATETIME DEFAULT NULL',
        'published_at' => 'DATETIME DEFAULT NULL',
    ];
    foreach ($columns as $name => $definition) {
        try { $pdo->exec("ALTER TABLE tech_news ADD COLUMN {$name} {$definition}"); $done[] = "column:{$name}"; } catch (Throwable $e) {}
    }

    $indexes = [
        'idx_news_hash' => 'url_hash',
        'idx_news_source_date' => 'source_name, created_at',
        'idx_news_quality' => 'content_status, scrape_status',
        'idx_news_published' => 'is_published, published_at',
    ];
    foreach ($indexes as $name => $cols) {
        try { $pdo->exec("CREATE INDEX {$name} ON tech_news({$cols})"); $done[] = "index:{$name}"; } catch (Throwable $e) {}
    }

    try {
        $pdo->exec("UPDATE tech_news
            SET content_length = CHAR_LENGTH(COALESCE(content, '')),
                content_status = CASE
                    WHEN CHAR_LENGTH(COALESCE(content, '')) >= 1200 THEN 'full'
                    WHEN CHAR_LENGTH(COALESCE(content, '')) >= 300 THEN 'partial'
                    WHEN CHAR_LENGTH(COALESCE(content, '')) > 0 THEN 'short'
                    ELSE 'missing'
                END,
                scrape_status = CASE
                    WHEN CHAR_LENGTH(COALESCE(content, '')) >= 500 THEN 'full'
                    WHEN CHAR_LENGTH(COALESCE(content, '')) >= 200 THEN 'partial'
                    WHEN CHAR_LENGTH(COALESCE(content, '')) > 0 THEN 'short'
                    ELSE 'failed'
                END,
                published_at = COALESCE(published_at, created_at)");
        $done[] = 'backfill:quality';
    } catch (Throwable $e) {}

    return ['ok' => true, 'action' => $dropFirst ? 'fresh_schema' : 'migrate', 'done' => $done];
}

function newsDbReset(bool $schemaOnly): array {
    ensureNewsTable();
    $pdo = db();
    $done = [];

    if (!$schemaOnly) {
        if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
            $pdo->exec('DELETE FROM tech_news');
            try { $pdo->exec("DELETE FROM sqlite_sequence WHERE name='tech_news'"); } catch (Throwable $e) {}
        } else {
            $pdo->exec('TRUNCATE TABLE tech_news');
        }
        $done[] = 'truncate:tech_news';
    }

    $done = array_merge($done, clearNewsCaches());
    return ['ok' => true, 'action' => 'reset', 'done' => $done];
}

function newsDbFresh(): array {
    $schema = newsDbMigrate(true);
    $reset = newsDbReset(false);
    return [
        'ok' => true,
        'action' => 'fresh',
        'schema' => $schema['done'] ?? [],
        'reset' => $reset['done'] ?? [],
        'next' => '/api/news-rss.php?cat=all&limit=20'
    ];
}

function clearNewsCaches(): array {
    $paths = [
        __DIR__ . '/../cache/news-rss.json',
        __DIR__ . '/../data/cache/news-rss.json',
        rtrim(defined('CACHE_DIR') ? CACHE_DIR : (__DIR__ . '/../data/cache'), '/') . '/news-rss.json',
    ];
    $done = [];
    foreach (array_unique($paths) as $file) {
        if (is_file($file)) {
            @unlink($file);
            $done[] = 'cache:' . basename($file);
        }
    }
    return $done;
}
