<?php
/**
 * api/alerts.php — Live alerts aggregator
 *  • BIPAD (NDRRMA) — flood / landslide / fire / weather warnings
 *  • USGS earthquakes — Nepal region (lat 26–31, lon 80–89), last 14 days, M ≥ 3
 *
 * Cache: 5 minutes (file-based)
 * No external library, no API key required.
 */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');

$cacheDir  = __DIR__ . '/../cache';
$cacheFile = $cacheDir . '/alerts.json';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
  readfile($cacheFile); exit;
}

function http_get_json(string $url, int $timeout = 8) {
  $ctx = stream_context_create([
    'http' => ['timeout'=>$timeout, 'header'=>"User-Agent: Aakashvani/1.0\r\nAccept: application/json\r\n", 'ignore_errors'=>true],
    'ssl'  => ['verify_peer'=>true, 'verify_peer_name'=>true],
  ]);
  $raw = @file_get_contents($url, false, $ctx);
  if ($raw === false) return null;
  $j = json_decode($raw, true);
  return is_array($j) ? $j : null;
}

$alerts = [];

/* ─── 1. BIPAD (gov.np) — current active alerts ──────────────────────────── */
$bipad = http_get_json('https://bipadportal.gov.np/api/v1/alert/?expand=hazard&limit=15&ordering=-started_on');
if ($bipad && !empty($bipad['results'])) {
  foreach ($bipad['results'] as $r) {
    if (empty($r['public'])) continue;
    $title = $r['titleNe'] ?? $r['title'] ?? '';
    if (!$title) continue;
    // Skip alerts that expired more than 24h ago
    if (!empty($r['expireOn']) && strtotime($r['expireOn']) < (time() - 86400)) continue;

    $isActive = empty($r['expireOn']) || strtotime($r['expireOn']) > time();

    $hazTitle = '';
    if (!empty($r['hazard']) && is_array($r['hazard'])) {
      $hazTitle = $r['hazard']['titleNe'] ?? $r['hazard']['title'] ?? '';
    }
    $alerts[] = [
      'type'      => 'disaster',
      'source'    => 'BIPAD · सरकारी सूचना',
      'hazard'    => $hazTitle ?: 'Alert',
      'title'     => $title,
      'titleEn'   => $r['title'] ?? '',
      'severity'  => $isActive ? 'active' : 'info',
      'startedOn' => $r['startedOn'] ?? $r['createdOn'] ?? null,
      'lat'       => $r['point']['coordinates'][1] ?? null,
      'lon'       => $r['point']['coordinates'][0] ?? null,
      'link'      => 'https://bipadportal.gov.np/',
    ];
  }
}

/* ─── 2. USGS — Nepal-region earthquakes last 14 days, M ≥ 3 ─────────────── */
$from = gmdate('Y-m-d', time() - 14*86400);
$usgs = http_get_json("https://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&starttime={$from}&minlatitude=26&maxlatitude=31&minlongitude=80&maxlongitude=89&minmagnitude=3&orderby=time");
if ($usgs && !empty($usgs['features'])) {
  foreach ($usgs['features'] as $f) {
    $p = $f['properties'] ?? [];
    $g = $f['geometry']['coordinates'] ?? [null,null,null];
    $mag = (float)($p['mag'] ?? 0);
    $sev = $mag >= 5 ? 'severe' : ($mag >= 4 ? 'moderate' : 'minor');
    $alerts[] = [
      'type'      => 'earthquake',
      'source'    => 'USGS · National Seismological',
      'hazard'    => 'भूकम्प',
      'title'     => sprintf('M %.1f — %s', $mag, $p['place'] ?? ''),
      'magnitude' => $mag,
      'severity'  => $sev,
      'startedOn' => isset($p['time']) ? gmdate('c', (int)($p['time']/1000)) : null,
      'lat'       => $g[1] ?? null,
      'lon'       => $g[0] ?? null,
      'depth_km'  => $g[2] ?? null,
      'link'      => $p['url'] ?? 'https://earthquake.usgs.gov/',
    ];
  }
}

/* ─── 3. Weather warning (Open-Meteo current) ─────────────────────────────── */
$wx = http_get_json('https://api.open-meteo.com/v1/forecast?latitude=27.7172&longitude=85.3240&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max&timezone=Asia%2FKathmandu&forecast_days=3');
if ($wx && !empty($wx['daily']['weather_code'])) {
  $codes = $wx['daily']['weather_code'];
  $precs = $wx['daily']['precipitation_sum'] ?? [];
  $winds = $wx['daily']['wind_speed_10m_max'] ?? [];
  $dates = $wx['daily']['time'] ?? [];
  foreach ($codes as $i => $code) {
    $severe = ($code >= 95) || (($precs[$i] ?? 0) > 30) || (($winds[$i] ?? 0) > 50);
    if (!$severe) continue;
    $msg = $code >= 95 ? 'चट्याङ र भारी पानीको सम्भावना' : (($precs[$i] ?? 0) > 30 ? 'भारी पानी पर्ने सम्भावना' : 'तीव्र हावा');
    $alerts[] = [
      'type'      => 'weather',
      'source'    => 'Open-Meteo · मौसम',
      'hazard'    => 'मौसम चेतावनी',
      'title'     => $msg . ' — काठमाडौं (' . ($dates[$i] ?? '') . ')',
      'severity'  => $code >= 95 ? 'severe' : 'moderate',
      'startedOn' => ($dates[$i] ?? '') . 'T00:00:00+05:45',
      'link'      => 'https://www.dhm.gov.np/',
    ];
  }
}

/* ─── 4. Nepal Police — Flash Updates (traffic / road-closure notices) ──── */
function scrape_nepal_police(): array {
  $ctx = stream_context_create([
    'http' => ['timeout'=>10, 'header'=>"User-Agent: Mozilla/5.0 AakashvaniBot\r\n", 'ignore_errors'=>true],
    'ssl'  => ['verify_peer'=>true, 'verify_peer_name'=>true],
  ]);
  $html = @file_get_contents('https://www.nepalpolice.gov.np/flash-updates/', false, $ctx);
  if (!$html) return [];
  $out = [];
  if (preg_match_all(
    '#<div class="textbox-04[^"]*">\s*<a href="((?:https://www\.nepalpolice\.gov\.np)?/notices/\d+/)">\s*<h6[^>]*>(.*?)</h6>\s*<span>(.*?)</span>\s*<p[^>]*>(.*?)</p>#su',
    $html, $m, PREG_SET_ORDER
  )) {
    foreach ($m as $row) {
      $url = $row[1];
      if (strpos($url, 'http') !== 0) $url = 'https://www.nepalpolice.gov.np' . $url;
      $title   = trim(html_entity_decode(strip_tags($row[2]), ENT_QUOTES|ENT_HTML5, 'UTF-8'));
      $date    = trim($row[3]);
      $excerpt = trim(html_entity_decode(strip_tags(str_replace('&nbsp;',' ', $row[4])), ENT_QUOTES|ENT_HTML5, 'UTF-8'));
      if (mb_strlen($excerpt) > 240) $excerpt = mb_substr($excerpt, 0, 240) . '…';
      $isTraffic = (mb_strpos($title, 'बाटो') !== false) || (mb_strpos($title, 'सवारी') !== false) || (mb_strpos($title, 'ट्राफिक') !== false);
      $out[] = [
        'type'       => $isTraffic ? 'traffic' : 'police',
        'source'     => 'नेपाल प्रहरी · आधिकारिक',
        'source_url' => $url,
        'hazard'     => $isTraffic ? 'ट्राफिक/बाटो' : 'प्रहरी सूचना',
        'title'      => $title,
        'msg'        => $excerpt,
        'severity'   => $isTraffic ? 'active' : 'info',
        'startedOn'  => date('c'),
        'date_np'    => $date,
        'link'       => $url,
      ];
    }
  }
  return array_slice($out, 0, 8);
}
foreach (scrape_nepal_police() as $a) { $alerts[] = $a; }

/* ─── Sort: newest first, severe first ───────────────────────────────────── */
usort($alerts, function($a,$b){
  $rank = ['severe'=>0,'active'=>1,'moderate'=>2,'minor'=>3,'info'=>4];
  $ra = $rank[$a['severity'] ?? 'info'] ?? 5;
  $rb = $rank[$b['severity'] ?? 'info'] ?? 5;
  if ($ra !== $rb) return $ra - $rb;
  return strcmp($b['startedOn'] ?? '', $a['startedOn'] ?? '');
});

$out = [
  'ok'      => true,
  'count'   => count($alerts),
  'updated' => date('c'),
  'items'   => array_slice($alerts, 0, 16),
  'alerts'  => array_slice($alerts, 0, 16),
];


$json = json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@file_put_contents($cacheFile, $json);
echo $json;
