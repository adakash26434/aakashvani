<?php
/**
 * Manual AI Sync Trigger (Admin only)
 * POST /api/sync-trigger.php
 * Requires: Admin session OR valid CRON_KEY
 * Returns JSON with sync results.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// ── Auth: Admin session OR CRON_KEY ───────────────────────────────────────────────
$cronKey  = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey   = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$hasKey   = $cronKey && $reqKey === $cronKey;
$hasAdmin = isAdmin();

if (!$hasKey && !$hasAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized — admin session or CRON_KEY required']);
    return;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    return;
}

if ($hasAdmin) csrfRequire();

// Rate limit: max 1 manual sync per 5 minutes
try {
    $lastSync = db()->query("SELECT MAX(run_at) as last FROM news_sync_log")->fetchColumn();
    if ($lastSync && (time() - strtotime($lastSync)) < 300) {
        echo json_encode(['error' => 'Sync ran recently. Wait 5 minutes.', 'last_sync' => $lastSync]);
        return;
    }
} catch (\Throwable $e) {
    // Table may not exist yet — proceed
}

// Run sync in background (non-blocking)
$phpBin = PHP_BINARY ?: 'php';
$script = escapeshellarg(realpath(__DIR__ . '/../cron/ai-sync.php') ?: '');
$log    = escapeshellarg(__DIR__ . '/../data/logs/ai-sync.log');

if (!$script || !file_exists(dirname(__DIR__) . '/cron/ai-sync.php')) {
    echo json_encode(['error' => 'Sync script not found']);
    return;
}

if (DIRECTORY_SEPARATOR === '\\') {
    pclose(popen("start /B $phpBin $script >> $log 2>&1", 'r'));
} else {
    exec("$phpBin $script >> $log 2>&1 &");
}

echo json_encode([
    'success' => true,
    'message' => 'Sync started in background. Refresh news in ~30 seconds.',
    'timestamp' => date('Y-m-d H:i:s'),
]);
