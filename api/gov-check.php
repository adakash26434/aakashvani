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
//  PAN / VAT — IRD Nepal (taxpayerportal.ird.gov.np)
// ─────────────────────────────────────────────────────────────────────────────
function checkPAN(string $pan): void {
    $pan = preg_replace('/[^0-9]/', '', $pan);
    if (strlen($pan) < 7 || strlen($pan) > 9) {
        respond(['success' => false, 'message' => 'PAN नम्बर ७-९ अङ्कको हुनुपर्छ।']);
    }

    // Method 1: IRD taxpayer portal JSON endpoint
    $json = govFetch("https://taxpayerportal.ird.gov.np/api/taxpayer/search?pan={$pan}", [
        'headers' => [
            'Referer: https://taxpayerportal.ird.gov.np/taxpayer/PanSearch',
            'X-Requested-With: XMLHttpRequest',
        ],
    ]);
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data)) {
            // Flatten if nested
            $rec = isset($data[0]) ? $data[0] : $data;
            if (!empty($rec['panNo']) || !empty($rec['pan_no']) || !empty($rec['name'])) {
                respond([
                    'success'      => true,
                    'source'       => 'IRD Nepal API',
                    'pan'          => $rec['panNo'] ?? $rec['pan_no'] ?? $pan,
                    'name'         => $rec['name'] ?? $rec['taxpayerName'] ?? null,
                    'type'         => $rec['taxpayerType'] ?? $rec['type'] ?? null,
                    'address'      => $rec['address'] ?? $rec['district'] ?? null,
                    'registration_date' => $rec['registrationDate'] ?? $rec['reg_date'] ?? null,
                    'status'       => !empty($rec['status']) ? $rec['status'] : 'Active',
                    'vat_registered' => !empty($rec['vatRegistered']) || !empty($rec['vat']),
                    'official_url' => "https://taxpayerportal.ird.gov.np/taxpayer/PanSearch",
                ]);
            }
        }
    }

    // Method 2: Scrape the HTML search page
    $html = govFetch("https://taxpayerportal.ird.gov.np/taxpayer/PanSearch?pan={$pan}", [
        'headers' => ['Referer: https://taxpayerportal.ird.gov.np/'],
    ]);
    if ($html) {
        // Parse result table
        $name = '';
        $address = '';
        $status = '';
        $vatReg = false;

        if (preg_match('/Taxpayer Name[^:]*:?\s*<[^>]*>([^<]+)</i', $html, $m)) {
            $name = trim($m[1]);
        }
        if (!$name && preg_match('/<td[^>]*>\s*' . preg_quote($pan) . '\s*<\/td>\s*<td[^>]*>([^<]+)<\/td>/i', $html, $m)) {
            $name = trim($m[1]);
        }
        if (preg_match('/Address[^:]*:?\s*<[^>]*>([^<]+)</i', $html, $m)) {
            $address = trim($m[1]);
        }
        if (preg_match('/Status[^:]*:?\s*<[^>]*>([^<]+)</i', $html, $m)) {
            $status = trim($m[1]);
        }
        if (stripos($html, 'VAT') !== false && stripos($html, 'registered') !== false) {
            $vatReg = true;
        }
        if ($name) {
            respond([
                'success'      => true,
                'source'       => 'IRD Nepal',
                'pan'          => $pan,
                'name'         => $name,
                'address'      => $address ?: null,
                'status'       => $status ?: 'Active',
                'vat_registered' => $vatReg,
                'official_url' => "https://taxpayerportal.ird.gov.np/taxpayer/PanSearch",
            ]);
        }
    }

    // Fallback
    failWithLink(
        'IRD Portal बाट PAN विवरण स्वचालित रूपमा ल्याउन सकिएन। तलका चरणहरू पालना गर्नुहोस्:',
        "https://taxpayerportal.ird.gov.np/taxpayer/PanSearch",
        'IRD Taxpayer Portal',
        [
            'माथिको <b>Copy</b> बटन थिचेर PAN नम्बर clipboard मा राख्नुहोस्।',
            '<b>IRD Taxpayer Portal</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'PAN Number फिल्डमा Ctrl+V (वा tap-and-paste) गरेर नम्बर राख्नुहोस्।',
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

    // Step 1: Get CSRF token from the form page
    $formHtml = govFetch('https://www.dotm.gov.np/en/print-status/');
    $token = '';
    if ($formHtml) {
        if (preg_match('/name="_token"\s+value="([^"]+)"/', $formHtml, $m)) {
            $token = $m[1];
        } elseif (preg_match('/csrf[_-]token["\s]*[=:]["\s]*([a-zA-Z0-9_\-]+)/i', $formHtml, $m)) {
            $token = $m[1];
        }
        // Get session cookie
        preg_match('/Set-Cookie:\s*([^;\r\n]+)/i', $formHtml, $cookieM);
        $cookie = $cookieM[1] ?? '';
    }

    if ($token) {
        // Step 2: POST the form
        $postData = http_build_query([
            '_token'        => $token,
            'license_no'    => $licenseNo,
            'date_of_birth' => $dob,
            'search'        => '1',
        ]);
        $result = govFetch('https://www.dotm.gov.np/en/print-status/', [
            'post'    => $postData,
            'headers' => [
                'Content-Type: application/x-www-form-urlencoded',
                'Referer: https://www.dotm.gov.np/en/print-status/',
                'X-Requested-With: XMLHttpRequest',
            ],
            'cookie'  => $cookie,
        ]);

        if ($result) {
            $data = parseDotmResult($result, 'license');
            if ($data) {
                respond(array_merge(['success' => true, 'source' => 'DoTM Nepal'], $data));
            }
        }
    }

    // Direct API attempt (some DoTM endpoints are REST)
    $api = govFetch("https://www.dotm.gov.np/api/license/status?license_no=" . urlencode($licenseNo) . "&dob=" . urlencode($dob));
    if ($api) {
        $d = json_decode($api, true);
        if (!empty($d['name']) || !empty($d['status'])) {
            respond(['success' => true, 'source' => 'DoTM Nepal'] + $d);
        }
    }

    failWithLink(
        'DoTM Portal बाट लाइसेन्स स्थिति स्वचालित रूपमा ल्याउन सकिएन। तलका चरणहरू पालना गर्नुहोस्:',
        'https://www.dotm.gov.np/en/print-status/',
        'DoTM License Status',
        [
            'माथिको <b>Copy</b> बटनले अनुमतिपत्र नम्बर clipboard मा राख्नुहोस्।',
            '<b>DoTM License Status</b> बटन थिचेर DoTM Portal नयाँ Tab मा खोल्नुहोस्।',
            'License No. फिल्डमा नम्बर Paste गर्नुहोस् र Date of Birth (BS) भर्नुहोस्।',
            '<b>Search</b> थिचेर Print Status, Expiry Date र Category हेर्नुहोस्।',
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

    // Try DoTM API endpoint for bluebook
    $api = govFetch("https://www.dotm.gov.np/api/vehicle?vehicle_no=" . urlencode($vehicleNo), [
        'headers' => ['Referer: https://www.dotm.gov.np/'],
    ]);
    if ($api) {
        $d = json_decode($api, true);
        if (!empty($d['vehicle_no']) || !empty($d['owner_name'])) {
            respond([
                'success'    => true,
                'source'     => 'DoTM Nepal',
                'vehicle_no' => $d['vehicle_no'] ?? $vehicleNo,
                'owner'      => $d['owner_name'] ?? null,
                'type'       => $d['vehicle_type'] ?? null,
                'make'       => $d['make_model'] ?? ($d['make'] ?? null),
                'color'      => $d['color'] ?? null,
                'status'     => $d['status'] ?? 'Found',
                'tax_paid_until' => $d['tax_paid_until'] ?? null,
                'insurance_until' => $d['insurance_until'] ?? null,
            ]);
        }
    }

    // Scrape DoTM vehicle search
    $formHtml = govFetch('https://www.dotm.gov.np/en/vehicle/');
    $token = '';
    if ($formHtml && preg_match('/name="_token"\s+value="([^"]+)"/', $formHtml, $m)) {
        $token = $m[1];
    }
    if ($token) {
        $result = govFetch('https://www.dotm.gov.np/en/vehicle/', [
            'post' => http_build_query(['_token' => $token, 'vehicle_no' => $vehicleNo]),
            'headers' => ['Content-Type: application/x-www-form-urlencoded', 'Referer: https://www.dotm.gov.np/en/vehicle/'],
        ]);
        if ($result) {
            $data = parseDotmResult($result, 'vehicle');
            if ($data) respond(array_merge(['success' => true, 'source' => 'DoTM Nepal'], $data));
        }
    }

    failWithLink(
        'DoTM Portal बाट सवारी दर्ता स्थिति स्वचालित रूपमा ल्याउन सकिएन। तलका चरणहरू पालना गर्नुहोस्:',
        'https://www.dotm.gov.np/',
        'DoTM Vehicle Portal',
        [
            'माथिको <b>Copy</b> बटनले गाडी नम्बर clipboard मा राख्नुहोस्।',
            '<b>DoTM Vehicle Portal</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'Vehicle No. फिल्डमा नम्बर Paste गर्नुहोस् (जस्तै: BA 1 PA 1234)।',
            '<b>Search</b> थिचेर गाडी धनी, कर म्याद र बीमा स्थिति हेर्नुहोस्।',
        ],
        $vehicleNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  NATIONAL ID CARD — NID Management Centre
// ─────────────────────────────────────────────────────────────────────────────
function checkNID(string $nidNo, string $dob): void {
    if (!$nidNo) respond(['success' => false, 'message' => 'NID नम्बर आवश्यक छ।']);

    // Try NIDMC API
    $api = govFetch("https://nidmc.gov.np/api/status?nid=" . urlencode($nidNo) . "&dob=" . urlencode($dob));
    if ($api) {
        $d = json_decode($api, true);
        if (!empty($d['name']) || !empty($d['status'])) {
            respond(['success' => true, 'source' => 'NID Management Centre'] + $d);
        }
    }

    // Scrape NIDMC status page
    $formHtml = govFetch('https://nidmc.gov.np/status/');
    $token = '';
    if ($formHtml && preg_match('/name="_token"\s+value="([^"]+)"/', $formHtml, $m)) {
        $token = $m[1];
    }
    if ($token) {
        $result = govFetch('https://nidmc.gov.np/status/', [
            'post' => http_build_query(['_token' => $token, 'registration_number' => $nidNo, 'date_of_birth' => $dob]),
            'headers' => ['Content-Type: application/x-www-form-urlencoded', 'Referer: https://nidmc.gov.np/status/'],
        ]);
        if ($result) {
            $data = parseNidmcResult($result);
            if ($data) respond(array_merge(['success' => true, 'source' => 'NID Management Centre'], $data));
        }
    }

    failWithLink(
        'NID Management Centre बाट परिचयपत्र स्थिति स्वचालित रूपमा ल्याउन सकिएन। तलका चरणहरू पालना गर्नुहोस्:',
        'https://nidmc.gov.np/status/',
        'NID Management Centre',
        [
            'माथिको <b>Copy</b> बटनले NID दर्ता नम्बर clipboard मा राख्नुहोस्।',
            '<b>NID Management Centre</b> बटन थिचेर नयाँ Tab मा खोल्नुहोस्।',
            'Registration Number फिल्डमा नम्बर Paste गर्नुहोस् र Date of Birth (BS) भर्नुहोस्।',
            '<b>Search</b> थिचेर Print Status र Dispatch विवरण हेर्नुहोस्।',
        ],
        $nidNo
    );
}

// ─────────────────────────────────────────────────────────────────────────────
//  PASSPORT — Dept of Passports (reCAPTCHA protected — inline guide)
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
//  CITIZENSHIP — Dept of Civil Registration
// ─────────────────────────────────────────────────────────────────────────────
function checkCitizenship(string $citizenshipNo): void {
    // Try API
    $api = govFetch("https://www.dcr.gov.np/api/citizenship?no=" . urlencode($citizenshipNo));
    if ($api) {
        $d = json_decode($api, true);
        if (!empty($d['name'])) {
            respond(['success' => true, 'source' => 'Dept of Civil Registration'] + $d);
        }
    }
    failWithLink(
        'नागरिकता प्रमाणीकरण API बाट डेटा ल्याउन सकिएन। तलका चरणहरू अनुसार Nagarik App बाट Online Verification गर्नुहोस्:',
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
