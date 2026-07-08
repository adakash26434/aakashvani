<?php
/**
 * Auto-Sync Functions
 * Sync data from live APIs to database
 * 
 * SECURITY: All external URLs are validated against an allowlist to prevent SSRF.
 * Uses nh_fetchUrl() for all HTTP requests (consolidated HTTP client).
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/http.php';

// ── SSRF Protection: Allowlist of permitted domains ───────────────────────────
const SYNC_ALLOWED_HOSTS = [
    'nepalstock.com.np',
    'www.nrb.org.np',
    'noc.gov.np',
    'merolagani.com',
    'www.hamropatro.com',
    'ppmo.gov.np',
];

/**
 * Validate URL against allowlist to prevent SSRF attacks.
 * Only allows HTTPS URLs to known, trusted domains.
 */
function syncValidateUrl(string $url): bool {
    $parsed = @parse_url($url);
    if (!$parsed || !isset($parsed['host'])) return false;
    if (!in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) return false;
    if ($parsed['scheme'] !== 'https') return false; // Force HTTPS
    return in_array(strtolower($parsed['host']), SYNC_ALLOWED_HOSTS, true);
}

/**
 * Fetch URL with SSRF protection.
 * Returns null if URL is not in allowlist or fetch fails.
 */
function syncFetch(string $url, int $timeout = 30): ?string {
    if (!syncValidateUrl($url)) {
        error_log("[sync] SSRF block: $url");
        return null;
    }
    return nh_fetchUrl($url, [], $timeout, true);
}

// ── Cache directory (consistent across all sync functions) ──────────────────────
function syncCacheDir(): string {
    $dir = __DIR__ . '/../data/cache/sync';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir;
}

// ── Sync status tracking ───────────────────────────────────────────────────────
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

// ── Individual sync functions (use allowlisted URLs + nh_fetchUrl) ──────────────

// NEPSE Sync (Web Scraping - NEPSE has no public API)
function syncNepse(): bool {
    $url = 'https://nepalstock.com.np';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('nepse', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    // Store raw HTML (limited) for processing — pipeline is now scraper → market-data.php
    $cacheFile = syncCacheDir() . '/nepse-live.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('nepse', true, 'Synced NEPSE data (web scraping)');
    return true;
}

// Gold/Silver Sync (FENEGOSIDA - Web Scraping)
function syncGoldSilver(): bool {
    $url = 'https://www.fenegosida.org/';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('gold', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    $cacheFile = syncCacheDir() . '/gold-silver.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('gold', true, 'Synced gold/silver rates (web scraping)');
    return true;
}

// Forex Sync (NRB - Web Scraping)
function syncForex(): bool {
    $url = 'https://www.nrb.org.np';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('forex', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    $cacheFile = syncCacheDir() . '/forex-rates.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('forex', true, 'Synced forex rates (web scraping)');
    return true;
}

// Petrol Price Sync (NOC - Web Scraping)
function syncPetrol(): bool {
    $url = 'https://noc.gov.np';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('petrol', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    $cacheFile = syncCacheDir() . '/petrol-prices.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('petrol', true, 'Synced petrol prices (web scraping)');
    return true;
}

// IPO Sync (Mero Lagani - Web Scraping)
function syncIPO(): bool {
    $url = 'https://merolagani.com';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('ipo', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    $cacheFile = syncCacheDir() . '/ipo-data.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('ipo', true, 'Synced IPO data (web scraping)');
    return true;
}

// Rashifal Sync (Web Scraping)
function syncRashifal(): bool {
    $url = 'https://www.hamropatro.com/rashifal';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('rashifal', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    $cacheFile = syncCacheDir() . '/rashifal-data.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('rashifal', true, 'Synced rashifal data');
    return true;
}

// Government Tender Sync (PPMO Scraping)
function syncTenders(): bool {
    $url = 'https://ppmo.gov.np/tenders';
    $data = syncFetch($url, 30);
    if (!$data) {
        logSync('tenders', false, 'Fetch failed or SSRF blocked');
        return false;
    }
    $cacheFile = syncCacheDir() . '/tenders-data.json';
    file_put_contents($cacheFile, json_encode(['html' => substr($data, 0, 10000), 'synced_at' => date('Y-m-d H:i:s')]));
    logSync('tenders', true, 'Synced tender data');
    return true;
}

// Master sync function
function syncAll(): array {
    $results = [
        'nepse'    => syncNepse(),
        'gold'     => syncGoldSilver(),
        'forex'    => syncForex(),
        'petrol'   => syncPetrol(),
        'ipo'      => syncIPO(),
        'rashifal' => syncRashifal(),
        'tenders'  => syncTenders(),
    ];
    return $results;
}
