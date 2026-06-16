<?php
/**
 * api/ipo-allotment.php v1 — IPO BOLD Allotment Checker
 *
 * POST body (JSON or form):
 *   boids[]          = array of BOLD IDs (16 digit strings)
 *   company_share_id = CDSC companyShareId (numeric)
 *   company_name     = display name (optional)
 *   save             = "1" → save BOLDs to DB
 *
 * GET ?boids=1234,5678&company_share_id=123  (quick single/multi check, no save)
 *
 * Uses: https://iporesult.cdsc.com.np/result/companyShares/resultStatus (POST JSON)
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../functions.php';

/* ── Read input ─────────────────────────────────────────────────── */
$raw = file_get_contents('php://input');
$inp = @json_decode($raw, true) ?: [];

$boidsRaw     = $inp['boids']           ?? $_POST['boids']           ?? ($_GET['boids'] ?? '');
$companyId    = (int)($inp['company_share_id'] ?? $_POST['company_share_id'] ?? $_GET['company_share_id'] ?? 0);
$companyName  = trim($inp['company_name']  ?? $_POST['company_name']  ?? $_GET['company_name'] ?? '');
$doSave       = !empty($inp['save'])     || !empty($_POST['save'])    || !empty($_GET['save']);

// Normalize boids to array of cleaned strings
if (is_array($boidsRaw)) {
    $boids = $boidsRaw;
} else {
    $boids = preg_split('/[\s,;|]+/', (string)$boidsRaw);
}
$boids = array_values(array_unique(array_filter(array_map(fn($b) => preg_replace('/\D/', '', trim($b)), $boids), fn($b) => strlen($b) >= 10)));

if (!$boids) {
    echo json_encode(['ok'=>false,'error'=>'BOLD ID आवश्यक छ।'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (!$companyId) {
    echo json_encode(['ok'=>false,'error'=>'Company ID आवश्यक छ।'], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ── Save BOLDs to DB ────────────────────────────────────────────── */
if ($doSave) {
    foreach ($boids as $b) saveBold($b, $companyName);
}

/* ── Check each BOLD via CDSC iporesult API ──────────────────────── */
$results = [];
$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'timeout' => 12,
        'header'  => implode("\r\n", [
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*',
            'Origin: https://iporesult.cdsc.com.np',
            'Referer: https://iporesult.cdsc.com.np/',
            'User-Agent: Mozilla/5.0 (compatible; AakashvaniBot/2.0)',
        ]),
    ],
    'https' => [
        'method'  => 'POST',
        'timeout' => 12,
        'header'  => implode("\r\n", [
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*',
            'Origin: https://iporesult.cdsc.com.np',
            'Referer: https://iporesult.cdsc.com.np/',
            'User-Agent: Mozilla/5.0 (compatible; AakashvaniBot/2.0)',
        ]),
    ],
]);

$errors = 0;
foreach ($boids as $boid) {
    $payload = json_encode([
        'boid'           => $boid,
        'companyShareId' => (string)$companyId,
    ]);
    $ctxCopy = stream_context_create([
        'http'  => ['method'=>'POST','timeout'=>12,'content'=>$payload,
            'header'=>"Content-Type: application/json\r\nAccept: application/json\r\nOrigin: https://iporesult.cdsc.com.np\r\nReferer: https://iporesult.cdsc.com.np/\r\nUser-Agent: Mozilla/5.0"],
        'https' => ['method'=>'POST','timeout'=>12,'content'=>$payload,
            'header'=>"Content-Type: application/json\r\nAccept: application/json\r\nOrigin: https://iporesult.cdsc.com.np\r\nReferer: https://iporesult.cdsc.com.np/\r\nUser-Agent: Mozilla/5.0"],
    ]);
    $resp = @file_get_contents('https://iporesult.cdsc.com.np/result/companyShares/resultStatus', false, $ctxCopy);

    $result = [
        'boid'      => $boid,
        'boid_mask' => substr($boid, 0, 4) . '…' . substr($boid, -4),
        'allotted'  => false,
        'shares'    => 0,
        'message'   => 'डाटा उपलब्ध छैन',
        'raw'       => null,
    ];

    if ($resp) {
        $d = @json_decode($resp, true);
        if ($d) {
            $result['raw'] = $d;
            // CDSC returns: {"success":true,"message":"Allotted","postedDate":"..."}
            // or {"success":false,"message":"Not Allotted"}
            $success = !empty($d['success']) || strtolower($d['status'] ?? '') === 'true';
            $msg     = $d['message'] ?? ($d['detail'] ?? '');
            $shares  = (int)($d['sharesAllotted'] ?? ($d['shares'] ?? ($d['quantity'] ?? 0)));

            // Detect allotment from message text too
            $isAllotted = $success && stripos($msg, 'not') === false;

            $result['allotted'] = $isAllotted;
            $result['shares']   = $shares;
            $result['message']  = $msg ?: ($isAllotted ? 'Allotted check-circle' : 'Allotted भएन x-circle');
            $result['posted_date'] = $d['postedDate'] ?? '';
        } else {
            $errors++;
            $result['message'] = 'CDSC बाट response parse गर्न सकिएन';
        }
    } else {
        $errors++;
        $result['message'] = 'CDSC server connect हुन सकेन';
    }

    $results[] = $result;
    // Small delay to avoid rate limiting
    if (count($boids) > 1) usleep(300000); // 300ms
}

$allotted = count(array_filter($results, fn($r) => $r['allotted']));

echo json_encode([
    'ok'            => true,
    'company_id'    => $companyId,
    'company_name'  => $companyName,
    'checked'       => count($results),
    'allotted'      => $allotted,
    'not_allotted'  => count($results) - $allotted,
    'errors'        => $errors,
    'source'        => 'iporesult.cdsc.com.np',
    'results'       => $results,
], JSON_UNESCAPED_UNICODE);
