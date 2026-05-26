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

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin only']);
    exit;
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
