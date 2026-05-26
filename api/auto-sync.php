<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  आकाशवाणी — Unified Auto-Sync API  v1
 *  Zero-human-touch cron endpoint.
 *
 *  cPanel Cron (every 30 min):
 *    0,30 * * * *  curl -s "https://YOUR_SITE/api/auto-sync.php?key=YOUR_CRON_KEY" >> /dev/null
 *
 *  Jobs: news | market | ipo | brief | all (default)
 *  GET /api/auto-sync.php?key=KEY&job=news
 * ═══════════════════════════════════════════════════════════════════
 */

// ── Execution limits ─────────────────────────────────────────────────────────
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('X-Robots-Tag: noindex');

// ── Auth: CRON_KEY or admin session ──────────────────────────────────────────
$cronKey = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey  = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');

$isAuthenticated = false;
if ($cronKey && $reqKey === $cronKey) $isAuthenticated = true;
if (!$isAuthenticated) {
    // Allow admin session
    if (isset($_SESSION) && !empty($_SESSION['is_admin'])) $isAuthenticated = true;
}
if (!$isAuthenticated && $cronKey) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Unauthorized — wrong cron key']); exit;
}

$job   = trim($_GET['job'] ?? 'all');
$start = microtime(true);
$res   = [];
$errors= [];

// ── Helper: internal HTTP call ────────────────────────────────────────────────
function internalGet(string $path, int $timeout = 45): array {
    $base = defined('SITE_URL') ? SITE_URL : 'http://localhost';
    $url  = rtrim($base,'/') . $path;
    $ctx  = stream_context_create(['http'=>[
        'timeout'       => $timeout,
        'ignore_errors' => true,
        'user_agent'    => 'NSH-AutoSync/1.0',
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    $j   = $raw ? json_decode($raw, true) : null;
    return $j ?: ['ok'=>false,'raw'=>mb_substr((string)$raw,0,200)];
}

// ── Job: News Sync ────────────────────────────────────────────────────────────
function jobNews(): array {
    $phpBin = PHP_BINARY ?: 'php';
    $script = realpath(__DIR__ . '/../cron/ai-sync.php');
    if (!$script) return ['ok'=>false,'error'=>'cron/ai-sync.php not found'];

    // Capture output to log
    $logFile = __DIR__ . '/../data/logs/ai-sync.log';
    @mkdir(dirname($logFile), 0755, true);

    // Execute synchronously (cPanel cron will not wait for background process)
    $output = [];
    $code   = 0;
    exec(escapeshellcmd($phpBin) . ' ' . escapeshellarg($script) . ' 2>&1', $output, $code);

    $lines = count($output);
    $inserted = 0; $skipped = 0;
    foreach ($output as $line) {
        if (preg_match('/ins:\s*(\d+)/', $line, $m)) $inserted += (int)$m[1];
        if (preg_match('/skip:\s*(\d+)/', $line, $m)) $skipped  += (int)$m[1];
    }
    return ['ok'=>$code===0,'exit_code'=>$code,'lines'=>$lines,'inserted'=>$inserted,'skipped'=>$skipped];
}

// ── Job: Market Data ──────────────────────────────────────────────────────────
function jobMarket(): array {
    $r = internalGet('/api/market-data.php?type=all&refresh=1', 30);
    return ['ok'=>!empty($r),'bytes'=>strlen(json_encode($r))];
}

// ── Job: IPO Data ─────────────────────────────────────────────────────────────
function jobIpo(): array {
    $r = internalGet('/api/ipo-data.php?refresh=1', 30);
    return ['ok'=>isset($r['active']),'active'=>count($r['active']??[]),'upcoming'=>count($r['upcoming']??[])];
}

function jobBrief(): array {
    // Delegate to api/morning-brief.php with force-generate flag
    // Works with or without OpenAI key (falls back to news titles)
    $cronKey = defined('CRON_KEY') ? CRON_KEY : '';
    $base    = defined('SITE_URL') ? rtrim(SITE_URL,'/') : ('http://'.(gethostname()?:'localhost'));
    $url     = $base . '/api/morning-brief.php?gen=1' . ($cronKey ? '&key='.urlencode($cronKey) : '');

    $ctx = stream_context_create(['http'=>[
        'timeout'       => 50,
        'ignore_errors' => true,
        'user_agent'    => 'NSH-AutoSync-Brief/1.0',
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) {
        // Fallback: include directly
        $apiFile = __DIR__ . '/../api/morning-brief.php';
        if (file_exists($apiFile)) {
            ob_start();
            $_GET['gen'] = '1';
            $_GET['key'] = $cronKey;
            try { include $apiFile; } catch (\Throwable $e2) {}
            $raw = ob_get_clean();
        }
    }
    if (!$raw) return ['ok'=>false,'error'=>'morning-brief.php unreachable'];
    $d = json_decode($raw, true);
    if (!$d) return ['ok'=>false,'error'=>'invalid_json','raw'=>mb_substr((string)$raw,0,150)];
    return [
        'ok'     => !empty($d['ok']),
        'source' => $d['source'] ?? '?',
        'date'   => $d['date']   ?? date('Y-m-d'),
        'bullets'=> count($d['bullets'] ?? []),
    ];
}


// ── Run requested jobs ────────────────────────────────────────────────────────
$jobs = ($job === 'all') ? ['news','market','ipo','brief'] : [$job];

foreach ($jobs as $j) {
    $t0 = microtime(true);
    try {
        $res[$j] = match($j) {
            'news'   => jobNews(),
            'market' => jobMarket(),
            'ipo'    => jobIpo(),
            'brief'  => jobBrief(),
            default  => ['ok'=>false,'error'=>"Unknown job: $j"],
        };
    } catch(\Throwable $e) {
        $res[$j] = ['ok'=>false,'error'=>$e->getMessage()];
        $errors[] = "$j: ".$e->getMessage();
    }
    $res[$j]['ms'] = (int)((microtime(true)-$t0)*1000);
}

$allOk = empty(array_filter($res, fn($r) => empty($r['ok'])));

echo json_encode([
    'ok'         => $allOk,
    'job'        => $job,
    'ai_enabled' => defined('OPENAI_API_KEY') && (bool)OPENAI_API_KEY,
    'elapsed_ms' => (int)((microtime(true)-$start)*1000),
    'results'    => $res,
    'errors'     => $errors ?: null,
    'ts'         => date('c'),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
