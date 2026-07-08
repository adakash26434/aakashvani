<?php
/**
 * Clear cache API - for admin use
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';

// Session already started by config.php — do NOT call session_start() again here
$isAdmin = !empty($_SESSION['nh_admin']) || !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['is_admin']);
$cronKey = defined('CRON_KEY') ? (string)CRON_KEY : '';
$reqKey = (string)($_GET['key'] ?? $_GET['cron_key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$isCron = $cronKey !== '' && hash_equals($cronKey, $reqKey);
if (!$isAdmin && !$isCron) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    return;
}

$cacheDir = __DIR__ . '/../data/cache';
$cleared = [];

// Clear news cache
$newsCache = $cacheDir . '/news-rss.json';
if (file_exists($newsCache)) {
    unlink($newsCache);
    $cleared[] = 'news-rss.json';
}

// Clear other cache files
$patterns = ['*.json', '*.cache'];
foreach ($patterns as $pattern) {
    foreach (glob($cacheDir . '/' . $pattern) as $file) {
        if (is_file($file)) {
            unlink($file);
            $cleared[] = basename($file);
        }
    }
}

echo json_encode([
    'ok' => true,
    'cleared' => $cleared,
    'message' => 'Cache cleared successfully'
]);
