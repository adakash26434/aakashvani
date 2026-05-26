#!/usr/bin/env php
<?php
/**
 * Auto-Sync Cron Job
 * Run this script via cron to sync all data from live APIs
 * 
 * Add to crontab:
 * 0 *\/30 * * * * /usr/bin/php /path/to/cron-sync.php >> /path/to/sync.log 2>&1
 */

require_once __DIR__ . '/includes/sync-functions.php';

echo "=== Auto-Sync Started at " . date('Y-m-d H:i:s') . " ===\n";

$results = syncAll();

foreach ($results as $source => $success) {
    echo sprintf("[%s] %s\n", $success ? '✓' : '✗', strtoupper($source));
}

echo "=== Auto-Sync Completed at " . date('Y-m-d H:i:s') . " ===\n";
echo "Success: " . count(array_filter($results)) . "/" . count($results) . "\n";
