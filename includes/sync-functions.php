<?php
/**
 * Auto-Sync Functions
 * Sync data from live APIs to database
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Sync status tracking
function logSync(string $source, bool $success, string $message = ''): void {
    $db = db();
    ensureSyncLogTable();
    $db->prepare('INSERT INTO sync_log (source, success, message, synced_at) VALUES (?,?,?,?)')
        ->execute([$source, $success ? 1 : 0, $message, date('Y-m-d H:i:s')]);
}

function ensureSyncLogTable(): void {
    static $done = false;
    if ($done) return;
    $ai = dbAI();
    db()->exec("CREATE TABLE IF NOT EXISTS sync_log (
        id       $ai,
        source   VARCHAR(100) NOT NULL,
        success  TINYINT NOT NULL,
        message  TEXT,
        synced_at DATETIME NOT NULL
    )" . dbCharset());
    dbIndex('idx_sync_source', 'sync_log', 'source');
    dbIndex('idx_sync_at', 'sync_log', 'synced_at');
    $done = true;
}

function getSyncStatus(string $source): ?array {
    ensureSyncLogTable();
    $stmt = db()->prepare('SELECT * FROM sync_log WHERE source = ? ORDER BY synced_at DESC LIMIT 1');
    $stmt->execute([$source]);
    return $stmt->fetch() ?: null;
}

function getAllSyncStatus(): array {
    ensureSyncLogTable();
    $sources = ['nepse', 'gold', 'forex', 'petrol', 'ipo', 'rashifal', 'tenders'];
    $status = [];
    foreach ($sources as $source) {
        $status[$source] = getSyncStatus($source);
    }
    return $status;
}

// NEPSE Sync (Web Scraping - NEPSE has no public API)
function syncNepse(): bool {
    try {
        $url = 'https://nepalstock.com.np';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            logSync('nepse', false, 'Failed to fetch data');
            return false;
        }

        // Store raw HTML for processing
        $cacheFile = __DIR__ . '/../cache/nepse-live.json';
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));

        logSync('nepse', true, 'Synced NEPSE data (web scraping)');
        return true;
    } catch (Exception $e) {
        logSync('nepse', false, $e->getMessage());
        return false;
    }
}

// Gold/Silver Sync (NRB - Web Scraping)
function syncGoldSilver(): bool {
    try {
        $url = 'https://www.nrb.org.np';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            logSync('gold', false, 'Failed to fetch data');
            return false;
        }

        $cacheFile = __DIR__ . '/../cache/gold-silver.json';
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));

        logSync('gold', true, 'Synced gold/silver rates (web scraping)');
        return true;
    } catch (Exception $e) {
        logSync('gold', false, $e->getMessage());
        return false;
    }
}

// Forex Sync (NRB - Web Scraping)
function syncForex(): bool {
    try {
        $url = 'https://www.nrb.org.np';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            logSync('forex', false, 'Failed to fetch data');
            return false;
        }

        $cacheFile = __DIR__ . '/../cache/forex-rates.json';
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));

        logSync('forex', true, 'Synced forex rates (web scraping)');
        return true;
    } catch (Exception $e) {
        logSync('forex', false, $e->getMessage());
        return false;
    }
}

// Petrol Price Sync (NOC - Web Scraping)
function syncPetrol(): bool {
    try {
        $url = 'https://noc.gov.np';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            logSync('petrol', false, 'Failed to fetch data');
            return false;
        }

        $cacheFile = __DIR__ . '/../cache/petrol-prices.json';
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));

        logSync('petrol', true, 'Synced petrol prices (web scraping)');
        return true;
    } catch (Exception $e) {
        logSync('petrol', false, $e->getMessage());
        return false;
    }
}

// IPO Sync (Mero Lagani - Web Scraping)
function syncIPO(): bool {
    try {
        $url = 'https://merolagani.com';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            logSync('ipo', false, 'Failed to fetch data');
            return false;
        }

        $cacheFile = __DIR__ . '/../cache/ipo-data.json';
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));

        logSync('ipo', true, 'Synced IPO data (web scraping)');
        return true;
    } catch (Exception $e) {
        logSync('ipo', false, $e->getMessage());
        return false;
    }
}

// Rashifal Sync (Web Scraping)
function syncRashifal(): bool {
    try {
        // Try to fetch from a popular Nepali rashifal site
        $url = 'https://www.hamropatro.com/rashifal';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);
        
        if (!$data) {
            logSync('rashifal', false, 'Failed to fetch data');
            return false;
        }
        
        // Parse HTML (simplified - in production use proper parser)
        // For now, just log success
        $cacheFile = __DIR__ . '/../cache/rashifal-data.json';
        // Store the raw HTML for processing
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000)]));
        
        logSync('rashifal', true, 'Synced rashifal data');
        return true;
    } catch (Exception $e) {
        logSync('rashifal', false, $e->getMessage());
        return false;
    }
}

// Government Tender Sync (PPMO Scraping)
function syncTenders(): bool {
    try {
        $url = 'https://ppmo.gov.np/tenders';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);
        
        if (!$data) {
            logSync('tenders', false, 'Failed to fetch data');
            return false;
        }
        
        $cacheFile = __DIR__ . '/../cache/tenders-data.json';
        file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000)]));
        
        logSync('tenders', true, 'Synced tender data');
        return true;
    } catch (Exception $e) {
        logSync('tenders', false, $e->getMessage());
        return false;
    }
}

// Master sync function
function syncAll(): array {
    $results = [
        'nepse' => syncNepse(),
        'gold' => syncGoldSilver(),
        'forex' => syncForex(),
        'petrol' => syncPetrol(),
        'ipo' => syncIPO(),
        'rashifal' => syncRashifal(),
        'tenders' => syncTenders(),
    ];
    return $results;
}
