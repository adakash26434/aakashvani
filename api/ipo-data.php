<?php
/**
 * IPO Data API v3 — fetches live IPO / FPO / Right / Mutual Fund / Bond
 * data from Sharesansar's DataTables AJAX endpoint (returns clean JSON).
 *
 * Source: https://www.sharesansar.com/existing-issues?type={1..7}
 *   1 = IPO   2 = FPO   3 = Right   4 = (further) IPO/CD
 *   5 = Mutual Fund   6 = Bond     7 = Listed/Recently Issued
 *
 * Cached for 1 hour in /data/cache/ipo.json. Falls back to the
 * cached copy (even if stale) when the upstream call fails.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/error-logger.php';
require_once __DIR__ . '/../includes/http.php';

// Security headers
sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

// Rate limiting
$rateKey = 'ipo:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, 60, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
    exit;
}

$cacheDir = __DIR__ . '/../data/cache/';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . 'ipo.json';
$force = isset($_GET['refresh']);
$ttl   = 3600; // 1 hour

if (!$force && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
    echo file_get_contents($cacheFile);
    exit;
}

/** Strip HTML and decode entities to plain text. */
function ip_clean(?string $s): string {
    if ($s === null || $s === '') return '';
    return trim(html_entity_decode(strip_tags($s), ENT_QUOTES, 'UTF-8'));
}

/** Extract href from an anchor tag, or empty. */
function ip_href(?string $html): string {
    if (!$html) return '';
    if (preg_match('/href=[\'"]([^\'"]+)[\'"]/i', $html, $m)) return $m[1];
    return '';
}

/** Fetch one Sharesansar issue-type bucket as a clean PHP array. */
function ip_fetch_type(int $type): array {
    $url = 'https://www.sharesansar.com/existing-issues?type=' . $type
         . '&draw=1&start=0&length=100&_=' . time();
    $raw = nh_fetchUrl($url, [
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json, text/javascript, */*; q=0.01',
        'Referer: https://www.sharesansar.com/existing-issues',
    ], 12);
    if (!$raw) return [];
    $j = json_decode($raw, true);
    if (!is_array($j) || empty($j['data']) || !is_array($j['data'])) return [];

    $out = [];
    foreach ($j['data'] as $row) {
        $co     = $row['company'] ?? [];
        $symbol = ip_clean($co['symbol']      ?? '');
        $name   = ip_clean($co['companyname'] ?? '');
        if ($name === '' && $symbol === '') continue;

        $open  = trim((string)($row['opening_date'] ?? ''));
        $close = trim((string)($row['closing_date'] ?? ''));
        $final = trim((string)($row['final_date']   ?? ''));
        $list  = trim((string)($row['listing_date'] ?? ''));

        $units = $row['total_units'] ?? '';
        $price = $row['issue_price'] ?? '';
        $ratio = $row['ratio_value'] ?? '';

        $announce = $row['announcement_link'] ?? '';
        $eligible = $row['right_eligibility_link'] ?? '';

        $out[] = [
            'name'        => $name,
            'symbol'      => $symbol,
            'sector'      => ip_clean($row['displayable_share_type'] ?? ''),
            'shares'      => $units !== '' ? number_format((float)$units) : '',
            'price'       => $price !== '' ? ('Rs. ' . number_format((float)$price, 2)) : '',
            'ratio'       => $ratio !== '' ? (string)$ratio : '',
            'openDate'    => $open,
            'closeDate'   => $close,
            'finalDate'   => $final,
            'listingDate' => $list,
            'manager'     => trim((string)($row['issue_manager'] ?? '')),
            'announceUrl' => $announce ?: '',
            'eligibleUrl' => $eligible ?: '',
            'companyUrl'  => ip_href($co['symbol'] ?? ''),
        ];
    }
    return $out;
}

/** Bucket items by their open/close dates relative to today. */
function ip_bucket(array $items): array {
    $now = strtotime(date('Y-m-d'));
    $b   = ['active' => [], 'upcoming' => [], 'closed' => []];
    foreach ($items as $it) {
        $o = $it['openDate']  ? strtotime($it['openDate'])  : false;
        $c = $it['closeDate'] ? strtotime($it['closeDate']) : false;
        if ($c && $c < $now)        $b['closed'][]   = $it;
        elseif ($o && $o > $now)    $b['upcoming'][] = $it;
        elseif ($o && $c)           $b['active'][]   = $it;
        elseif (!$o && !$c && !empty($it['listingDate'])) $b['closed'][] = $it;
        else                        $b['upcoming'][] = $it;
    }
    // Most recent first inside each bucket
    foreach ($b as &$arr) {
        usort($arr, fn($x,$y) => strcmp($y['openDate'] ?: $y['listingDate'], $x['openDate'] ?: $x['listingDate']));
    }
    return $b;
}

/* ── Pull every category in parallel-ish (sequential, short timeouts) ─── */
$ipo    = ip_fetch_type(1);
$fpo    = ip_fetch_type(2);
$right  = ip_fetch_type(3);
$mf     = ip_fetch_type(5);
$bond   = ip_fetch_type(6);
$listed = ip_fetch_type(7);

$ipoB   = ip_bucket($ipo);
$fpoB   = ip_bucket($fpo);
$rightB = ip_bucket($right);
$mfB    = ip_bucket($mf);
$bondB  = ip_bucket($bond);

$totalLive = count($ipoB['active'])  + count($ipoB['upcoming'])
           + count($fpoB['active'])  + count($fpoB['upcoming'])
           + count($rightB['active'])+ count($rightB['upcoming']);

$data = [
    'available' => $totalLive > 0 || !empty($ipo) || !empty($fpo) || !empty($right),
    // Back-compat keys used by ipo-tracker.php
    'active'    => array_merge($ipoB['active'],   $fpoB['active'],   $rightB['active']),
    'upcoming'  => array_merge($ipoB['upcoming'], $fpoB['upcoming'], $rightB['upcoming']),
    'closed'    => array_slice(array_merge($ipoB['closed'], $fpoB['closed'], $rightB['closed']), 0, 30),
    // Detailed split for the new UI
    'ipo'       => $ipoB,
    'fpo'       => $fpoB,
    'right'     => $rightB,
    'mutual'    => $mfB,
    'bond'      => $bondB,
    'recently_listed' => array_slice($listed, 0, 15),
    'source'        => 'sharesansar.com (existing-issues ajax)',
    'source_url'    => 'https://www.sharesansar.com/ipo',
    'updated_at'    => date('c'),
    'updated_at_np' => date('Y-m-d H:i', time() + 60 * 60 * 5 + 60 * 45) . ' NPT',
];

if (!$data['available']) {
    // Serve stale cache rather than an empty page
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached)) {
            $cached['stale'] = true;
            $cached['note']  = 'Live स्रोत अहिले उपलब्ध छैन — पछिल्लो cached डाटा देखाइएको छ।';
            echo json_encode($cached, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    $data['note'] = 'IPO स्रोत (ShareSansar) अहिले उपलब्ध छैन। पछि फेरि प्रयास गर्नुहोस्।';
}

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
@file_put_contents($cacheFile, $json);
echo $json;
