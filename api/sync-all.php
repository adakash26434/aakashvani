<?php
/**
 * sync-all.php v2 — orchestrates all live data jobs.
 * Delegates news sync to auto-sync.php pipeline.
 */
ini_set('max_execution_time','120');
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$cronKey = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey  = $_GET['key'] ?? '';
if ($cronKey && $reqKey !== $cronKey) {
    http_response_code(401); echo json_encode(['ok'=>false,'error'=>'invalid key']); exit;
}

$job=$_GET['job']??'all'; $results=[]; $start=microtime(true);

function cronPath(string $path, array $params = []): string {
    if (defined('CRON_KEY') && CRON_KEY !== '') {
        $params = ['key' => CRON_KEY] + $params;
    }
    return $path . ($params ? '?' . http_build_query($params) : '');
}

function siteGet(string $path,int $timeout=40): array {
    $base=defined('SITE_URL')?SITE_URL:'';
    $ctx=stream_context_create(['http'=>['timeout'=>$timeout,'ignore_errors'=>true,'user_agent'=>'NSH-Cron/2']]);
    $r=@file_get_contents(rtrim($base,'/').$path,false,$ctx);
    return $r?json_decode($r,true)??['ok'=>false,'raw'=>mb_substr($r,0,100)]:['ok'=>false,'error'=>'no_response'];
}

if ($job==='all'||$job==='news') {
    // Delegate to auto-sync (which runs the full AI pipeline)
    $results['news'] = siteGet(cronPath('/api/auto-sync.php', ['job' => 'news']), 120);
}
if ($job==='all'||$job==='ipo')    $results['ipo']    = siteGet('/api/ipo-data.php?refresh=1');
if ($job==='all'||$job==='market') $results['market'] = siteGet('/api/market-data.php?type=all&refresh=1');
if ($job==='all'||$job==='brief')  $results['brief']  = siteGet(cronPath('/api/auto-sync.php', ['job' => 'brief']));

echo json_encode([
    'ok'=>true,'job'=>$job,
    'ai'=>defined('OPENAI_API_KEY')&&OPENAI_API_KEY?'enabled':'disabled',
    'elapsed_ms'=>(int)((microtime(true)-$start)*1000),
    'results'=>$results,'ts'=>date('c'),
],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
