<?php
/**
 * AI Sync Status API
 * GET /api/sync-status.php
 * Returns last sync info for admin dashboard widget.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

// ── Auth: Admin session OR CRON_KEY ─────────────────────────────────────────────
$cronKey = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey  = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$hasKey   = $cronKey && $reqKey === $cronKey;
$hasAdmin = isAdmin();

if (!$hasKey && !$hasAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized — admin session or CRON_KEY required']);
    return;
}

try {
    ensureSyncLogTable();
    $rows = db()->query("
        SELECT source, articles_inserted, articles_skipped, run_at, error_msg
        FROM news_sync_log
        ORDER BY run_at DESC
        LIMIT 20
    ")->fetchAll();

    $totalToday = db()->query("
        SELECT COALESCE(SUM(articles_inserted),0) as total
        FROM news_sync_log
        WHERE DATE(run_at) = CURDATE()
    ")->fetchColumn();

    $lastRun = $rows[0]['run_at'] ?? null;
    $nextRun = $lastRun ? date('Y-m-d H:i:s', strtotime($lastRun) + 1800) : null;

    echo json_encode([
        'success'      => true,
        'last_run'     => $lastRun,
        'next_run'     => $nextRun,
        'today_count'  => (int)$totalToday,
        'recent_syncs' => $rows,
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function ensureSyncLogTable(): void {
    $isMysql = defined('DB_DRIVER') && DB_DRIVER !== 'sqlite';
    $ai = $isMysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    db()->exec("CREATE TABLE IF NOT EXISTS news_sync_log (
        id $ai,
        source VARCHAR(100),
        articles_fetched INT DEFAULT 0,
        articles_inserted INT DEFAULT 0,
        articles_skipped INT DEFAULT 0,
        run_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        error_msg TEXT
    )");
}
