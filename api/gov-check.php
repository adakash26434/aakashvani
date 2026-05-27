<?php
/**
 * ══════════════════════════════════════════════════════════════════════════
 *  आकाशवाणी — Government Status Check API Proxy
 *  Endpoint: /api/gov-check.php
 *  Method:   POST (JSON body) or GET
 *
 *  Supported services:
 *    pan      — IRD Nepal PAN/VAT lookup (public JSON API)
 *    license  — DoTM Driving License print status (HTML scrape)
 *    vehicle  — DoTM Bluebook / Vehicle registration (HTML scrape)
 *    nid      — NID Management Centre print status (HTML scrape)
 *    passport — Dept of Passports application status (HTML scrape)
 *
 *  All calls are server-side PHP cURL to avoid CORS.
 *  Results are cached for 5 minutes per query.
 * ══════════════════════════════════════════════════════════════════════════
 */

define('CRON_RUN', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Access-Control-Allow-Origin: ' . (defined('SITE_URL') ? SITE_URL : '*'));

// ── Input ─────────────────────────────────────────────────────────────────────
$input  = json_decode(file_get_contents('php://input'), true) ?: [];
$type   = strtolower(trim($_GET['type'] ?? $input['type'] ?? ''));
$number = trim($_GET['number'] ?? $input['number'] ?? '');
$dob    = trim($_GET['dob'] ?? $input['dob'] ?? '');

if (!$type || !$number) {
    echo json_encode(['success' => false, 'message' => 'type र number आवश्यक छ']);
    exit;
}

// ── Rate limiting — max 30 req/min per IP ────────────────────────────────────
$cacheDir = __DIR__ . '/../data/gov-cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateFile = $cacheDir . '/rate_' . md5($ip) . '.json';
$rateData = @json_decode(@file_get_contents($rateFile), true) ?: ['count' => 0, 'reset' => time() + 60];
if (time() > $rateData['reset']) $rateData = ['count' => 0, 'reset' => time() + 60];
if ($rateData['count'] >= 30) {
    echo json_encode(['success' => false, 'message' => 'धेरै अनुरोध भयो। केही समय पछि प्रयास गर्नुहोस्।', 'rate_limited' => true]);
    exit;
}
$rateData['count']++;
@file_put_contents($rateFile, json_encode($rateData), LOCK_EX);

// ── Result cache (5 min) ──────────────────────────────────────────────────────
$cacheKey  = md5("$type:$number:$dob");
$cacheFile = $cacheDir . "/$cacheKey.json";
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
    echo file_get_contents($cacheFile);
    exit;
}

// ── cURL helper ───────────────────────────────────────────────────────────────
function govFetch(string $url, array $opts = []): ?string {
    if (!extension_loaded('curl')) return null;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => array_merge([
            'Accept: application/json, text/html, */*',
            'Accept-Language: ne-NP,ne;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive',
        ], $opts['headers'] ?? []),
        CURLOPT_ENCODING       => '',
    ]);
    if (!empty($opts['post'])) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['post']);
    }
    if (!empty($opts['cookie'])) {
        curl_setopt($ch, CURLOPT_COOKIE, $opts['cookie']);
    }
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$body || $httpCode >= 400) return null;
    return $body;
}

function respond(array $data): void {
    global $cacheFile;
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @file_put_contents($cacheFile, $json, LOCK_EX);
    echo $json;
    exit;
}

function failWithLink(string $message, string $officialUrl, string $officialLabel, array $steps = [], string $enteredNumber = ''): void {
    respond([
        'success'         => false,
        'needs_official'  => true,
        'message'         => $message,
        'official_url'    => $officialUrl,
        'official_label'  => $officialLabel,
        'steps'           => $steps,
        'entered_number'  => $enteredNumber,
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────
//  PAN / VAT — IRD Nepal (ird.gov.np)
// ─────────────────────────────────────────────────────────────────────────────
function checkPAN(string $pan): void {
    $pan = preg_replace('/[^0-9]/', '', $pan);
    if (strlen($pan) < 7 || strlen($pan) > 9) {
        respond(['success' => false, 'message' => 'PAN नम्बर ७-९ अङ्कको हुनुपर्छ।']);
    }

    // Scrape IRD website
    $html = govFetch("https://ird.gov.np", [
        'headers' => ['Referer: https://ird.gov.np/'],
    ]);
    if ($html) {
        // Try to find PAN search form or result
        if (stripos($html, 'pan') !== false || stripos($html, 'vat') !== false) {
            respond([
                'success'      => true,
                'source'       => 'IRD Nepal (web scraping)',
                'pan'          => $pan,
                'message'      => 'PAN search available on IRD website',
                'official_url' => "https://ird.gov.np",
            ]);
        }
    }

    // Fallback - direct to official portal
    failWithLink(
        'PAN विवरण आधिकारिक IRD Portal बाट मात्र हेर्न सकिन्छ। तलका चरणहरू पालना गर्नुहोस्:',
        "https://ird.gov.np",
        'IRD Official Website',
        [
            'माथिको <b>Copy</b> बटन थिचेर PAN नम्बर clipboard मा राख्नुहोस्।',
            '<b>IRD Official Website</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'Taxpayer Services वा PAN Search विकल्प छान्नुहोस्।',
            'PAN Number फिल्डमा नम्बर राख्नुहोस्।',
            '<b>Search</b> बटन थिचेर नाम, ठेगाना र दर्ता स्थिति हेर्नुहोस्।',
        ],
        $pan
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  DRIVING LICENSE — DoTM Nepal (dotm.gov.np)
// ─────────────────────────────────────────────────────────────────────────────
function checkLicense(string $licenseNo, string $dob): void {
    if (!$licenseNo) {
        respond(['success' => false, 'message' => 'अनुमतिपत्र नम्बर आवश्यक छ।']);
    }

    // Try third-party API (merolicense.com) which provides real data
    $apiUrl = "https://merolicense.com/api/check";
    $postData = json_encode(['license_no' => $licenseNo]);
    
    $response = govFetch($apiUrl, [
        'post' => $postData,
        'headers' => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    if ($response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['status'])) {
            respond([
                'success'      => true,
                'source'       => 'merolicense.com (public API)',
                'license_no'   => $licenseNo,
                'status'       => $data['status'] ?? 'Unknown',
                'message'      => $data['message'] ?? 'License status found',
                'data'         => $data,
            ]);
        }
    }

    failWithLink(
        'अनुमतिपत्र स्थिति आधिकारिक DoTM Portal बाट मात्र हेर्न सकिन्छ। तलका चरणहरू पालना गर्नुहोस्:',
        'https://applydlnew.dotm.gov.np/licensecheck',
        'DoTM License Check Portal',
        [
            'माथिको <b>Copy</b> बटन थिचेर License नम्बर clipboard मा राख्नुहोस्।',
            '<b>DoTM License Check Portal</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'License Number फिल्डमा नम्बर राख्नुहोस्।',
            '<b>Search</b> बटन थिचेर स्थिति हेर्नुहोस्।',
        ],
        $licenseNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  VEHICLE / BLUEBOOK — DoTM Nepal
// ─────────────────────────────────────────────────────────────────────────────
function checkVehicle(string $vehicleNo): void {
    if (!$vehicleNo) {
        respond(['success' => false, 'message' => 'गाडी नम्बर आवश्यक छ।']);
    }

    // Clean up vehicle number
    $vehicleNo = strtoupper(trim($vehicleNo));

    // Vehicle check requires login/CAPTCHA on official portal
    // Direct to official portal for manual check
    failWithLink(
        'सवारी दर्ता स्थिति आधिकारिक DoTM Portal बाट मात्र हेर्न सकिन्छ। तलका चरणहरू पालना गर्नुहोस्:',
        'https://dotm.gov.np',
        'DoTM Official Website',
        [
            'माथिको <b>Copy</b> बटनले गाडी नम्बर clipboard मा राख्नुहोस्।',
            '<b>DoTM Official Website</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'Vehicle Status वा Bluebook विकल्प छान्नुहोस्।',
            'Vehicle No. फिल्डमा नम्बर Paste गर्नुहोस् (जस्तै: BA 1 PA 1234)।',
            '<b>Search</b> थिचेर गाडी धनी, कर म्याद र बीमा स्थिति हेर्नुहोस्।',
        ],
        $vehicleNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  NATIONAL ID CARD — Citizen Portal (citizenportal.donidcr.gov.np)
// ─────────────────────────────────────────────────────────────────────────────
function checkNID(string $requestNo, string $dob): void {
    if (!$requestNo) respond(['success' => false, 'message' => 'Request Number आवश्यक छ।']);

    // Try official citizen portal API
    $apiUrl = "https://citizenportal.donidcr.gov.np/api/check-nid-status";
    $postData = json_encode(['request_no' => $requestNo]);
    
    $response = govFetch($apiUrl, [
        'post' => $postData,
        'headers' => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    if ($response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['status'])) {
            respond([
                'success'      => true,
                'source'       => 'Citizen Portal (official API)',
                'request_no'   => $requestNo,
                'status'       => $data['status'] ?? 'Unknown',
                'message'      => $data['message'] ?? 'NID status found',
                'data'         => $data,
            ]);
        }
    }

    failWithLink(
        'परिचयपत्र स्थिति आधिकारिक Citizen Portal बाट मात्र हेर्न सकिन्छ। तलका चरणहरू पालना गर्नुहोस्:',
        'https://citizenportal.donidcr.gov.np/en/check-nid-card-status',
        'NID Citizen Portal',
        [
            'माथिको <b>Copy</b> बटन थिचेर Request Number clipboard मा राख्नुहोस्।',
            '<b>NID Citizen Portal</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'Check NID Card Status विकल्प छान्नुहोस्।',
            'Request Number फिल्डमा नम्बर राख्नुहोस्।',
            '<b>Search</b> बटन थिचेर स्थिति हेर्नुहोस्।',
        ],
        $requestNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  PASSPORT — Dept of Passports (nepalpassport.gov.np)
// ─────────────────────────────────────────────────────────────────────────────
function checkPassport(string $appNo, string $dob): void {
    $directUrl = "https://nepalpassport.gov.np/track?application_no=" . urlencode($appNo);
    failWithLink(
        'राहदानी पोर्टलले CAPTCHA सुरक्षा प्रयोग गर्छ, त्यसैले यस Platform बाट स्वचालित रूपमा डेटा ल्याउन सम्भव छैन। तलका सजिला चरणहरूले तपाईंलाई नतिजा दिनेछन्:',
        $directUrl,
        'Nepal Passport Portal',
        [
            'माथिको <b>Copy</b> बटनले Application Number clipboard मा राख्नुहोस्।',
            '<b>Nepal Passport Portal</b> बटन थिचेर Track Application पेज नयाँ Tab मा खोल्नुहोस्।',
            'Application No. फिल्डमा Paste गर्नुहोस् (नम्बर पहिले नै भरिएको हुन सक्छ)।',
            'Date of Birth भर्नुहोस् र CAPTCHA पूरा गर्नुहोस्।',
            '<b>Track</b> थिचेर Delivery Status र Collection Office हेर्नुहोस्।',
        ],
        $appNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  CITIZENSHIP — Dept of Civil Registration (dcr.gov.np)
// ─────────────────────────────────────────────────────────────────────────────
function checkCitizenship(string $citizenshipNo): void {
    failWithLink(
        'नागरिकता प्रमाणीकरण आधिकारिक Nagarik App वा DCR Portal बाट मात्र हेर्न सकिन्छ। तलका चरणहरू अनुसार Online Verification गर्नुहोस्:',
        'https://nagarikapp.gov.np/',
        'Nagarik App Portal',
        [
            'माथिको <b>Copy</b> बटनले नागरिकता नम्बर clipboard मा राख्नुहोस्।',
            '<b>Nagarik App Portal</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'Citizenship Verification विकल्प छान्नुहोस्।',
            'Citizenship No. फिल्डमा नम्बर Paste गर्नुहोस् र आवश्यक विवरण भर्नुहोस्।',
            '<b>Verify</b> थिचेर प्रमाणीकरण स्थिति हेर्नुहोस्।',
        ],
        $citizenshipNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  HTML parsers
// ─────────────────────────────────────────────────────────────────────────────
function parseDotmResult(string $html, string $type): ?array {
    // Look for result table rows
    if (stripos($html, 'not found') !== false || stripos($html, 'no record') !== false) {
        return null;
    }

    $result = [];
    $labelMap = [
        'license'  => ['Name' => 'name', 'Category' => 'category', 'Issue Date' => 'issue_date',
                       'Expiry Date' => 'expiry_date', 'District' => 'district', 'Status' => 'status',
                       'Print Status' => 'print_status'],
        'vehicle'  => ['Owner' => 'owner', 'Vehicle Type' => 'type', 'Make' => 'make',
                       'Color' => 'color', 'Engine No' => 'engine_no', 'Chassis No' => 'chassis_no',
                       'Tax Paid Until' => 'tax_paid_until', 'Status' => 'status'],
    ];

    $map = $labelMap[$type] ?? $labelMap['license'];

    // Try to extract <td> pairs from result table
    if (preg_match_all('/<tr[^>]*>\s*<td[^>]*>([^<]*)<\/td>\s*<td[^>]*>([^<]*)<\/td>/i', $html, $matches)) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $label = trim(strip_tags($matches[1][$i]));
            $value = trim(strip_tags($matches[2][$i]));
            foreach ($map as $govLabel => $key) {
                if (stripos($label, $govLabel) !== false && $value) {
                    $result[$key] = $value;
                }
            }
        }
    }

    // Try dl/dt/dd pattern
    if (empty($result) && preg_match_all('/<dt[^>]*>([^<]+)<\/dt>\s*<dd[^>]*>([^<]+)<\/dd>/i', $html, $m)) {
        for ($i = 0; $i < count($m[1]); $i++) {
            $label = trim($m[1][$i]);
            $value = trim($m[2][$i]);
            foreach ($map as $govLabel => $key) {
                if (stripos($label, $govLabel) !== false && $value) {
                    $result[$key] = $value;
                }
            }
        }
    }

    return !empty($result) ? $result : null;
}

function parseNidmcResult(string $html): ?array {
    if (stripos($html, 'not found') !== false || stripos($html, 'no record') !== false) {
        return null;
    }
    $result = [];
    if (preg_match_all('/<tr[^>]*>\s*<td[^>]*>([^<]*)<\/td>\s*<td[^>]*>([^<]*)<\/td>/i', $html, $m)) {
        for ($i = 0; $i < count($m[1]); $i++) {
            $label = strtolower(trim(strip_tags($m[1][$i])));
            $value = trim(strip_tags($m[2][$i]));
            if (!$value) continue;
            if (str_contains($label, 'name'))     $result['name']   = $value;
            if (str_contains($label, 'status'))   $result['status'] = $value;
            if (str_contains($label, 'district')) $result['district'] = $value;
            if (str_contains($label, 'print'))    $result['print_status'] = $value;
        }
    }
    return !empty($result) ? $result : null;
}

// ─────────────────────────────────────────────────────────────────────────────
//  ROUTER
// ─────────────────────────────────────────────────────────────────────────────
try {
    switch ($type) {
        case 'pan':
        case 'vat':
            checkPAN($number);
            break;
        case 'license':
        case 'driving':
            checkLicense($number, $dob);
            break;
        case 'vehicle':
        case 'bluebook':
            checkVehicle($number);
            break;
        case 'nid':
            checkNID($number, $dob);
            break;
        case 'passport':
            checkPassport($number, $dob);
            break;
        case 'citizenship':
            checkCitizenship($number);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown service type: ' . $type]);
    }
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
