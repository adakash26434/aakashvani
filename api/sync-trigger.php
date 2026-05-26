<?php
/**
 * Manual AI Sync Trigger (Admin only)
 * POST /api/sync-trigger.php
 * Returns JSON with sync results.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin only']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit;
}

csrfRequire();

// Rate limit: max 1 manual sync per 5 minutes
$lastSync = db()->query("SELECT MAX(run_at) as last FROM news_sync_log")->fetchColumn();
if ($lastSync && (time() - strtotime($lastSync)) < 300) {
    echo json_encode(['error' => 'Sync ran recently. Wait 5 minutes.', 'last_sync' => $lastSync]);
    exit;
}

// Run sync in background (non-blocking)
$phpBin = PHP_BINARY;
$script = escapeshellarg(__DIR__ . '/../cron/ai-sync.php');
$log    = escapeshellarg(__DIR__ . '/../data/logs/ai-sync.log');

if (DIRECTORY_SEPARATOR === '\\') {
    // Windows (dev only)
    pclose(popen("start /B $phpBin $script >> $log 2>&1", 'r'));
} else {
    exec("$phpBin $script >> $log 2>&1 &");
}

echo json_encode([
    'success' => true,
    'message' => 'Sync started in background. Refresh news in ~30 seconds.',
    'timestamp' => date('Y-m-d H:i:s'),
]);
