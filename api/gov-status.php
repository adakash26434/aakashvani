<?php
/**
 * Government Services Status API
 * License, NID, Passport status check
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/http.php';

$type = $_GET['type'] ?? '';
$number = $_GET['number'] ?? '';
$dob = $_GET['dob'] ?? '';

if (empty($type) || empty($number)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Cache directory
$cacheDir = __DIR__ . '/../cache/gov/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cacheKey = md5($type . $number . $dob);
$cacheFile = $cacheDir . $cacheKey . '.json';
$cacheTime = 3600; // 1 hour cache

// Check cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$result = ['success' => false, 'message' => 'Service not available'];

switch ($type) {
    case 'license':
        $result = checkLicenseStatus($number, $dob);
        break;
    case 'nid':
        $result = checkNIDStatus($number, $dob);
        break;
    case 'passport':
        $result = checkPassportStatus($number, $dob);
        break;
    case 'pan':
        $result = checkPANStatus($number);
        break;
    default:
        $result = ['success' => false, 'message' => 'Invalid service type'];
}

// Cache successful results
if ($result['success']) {
    file_put_contents($cacheFile, json_encode($result));
}

echo json_encode($result);

/**
 * Check Driving License Status from DoTM
 */
function checkLicenseStatus($licenseNo, $dob) {
    // DoTM API endpoint (this is a placeholder - actual API may differ)
    $apiUrl = 'https://dotm.gov.np/api/license-status';
    
    // Try to fetch from DoTM
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'license_no' => $licenseNo,
            'dob' => $dob
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // If API fails, try web scraping fallback
    if ($httpCode !== 200 || empty($response)) {
        return scrapeLicenseStatus($licenseNo, $dob);
    }
    
    $data = json_decode($response, true);
    if ($data && isset($data['status'])) {
        return [
            'success' => true,
            'status' => $data['status'] === 'PRINTED' ? 'printed' : 'pending',
            'statusText' => $data['status'] === 'PRINTED' ? 'Print भइसकेको छ - संकलन गर्नुहोस्' : 'Processing मा छ',
            'name' => $data['name'] ?? '',
            'category' => $data['category'] ?? '',
            'office' => $data['office'] ?? ''
        ];
    }
    
    return ['success' => false, 'message' => 'License जानकारी भेटिएन'];
}

/**
 * Scrape License Status from DoTM website (fallback)
 */
function scrapeLicenseStatus($licenseNo, $dob) {
    // Parse license number format: XX-YY-ZZZZZZZ
    $parts = explode('-', $licenseNo);
    if (count($parts) < 3) {
        return ['success' => false, 'message' => 'Invalid license number format. Use XX-YY-ZZZZZZZ'];
    }
    
    $zone = $parts[0];
    $category = $parts[1];
    $serial = $parts[2];
    
    // For now, return a helpful message directing to official site
    // In production, implement actual scraping with proper selectors
    return [
        'success' => false,
        'message' => 'कृपया आधिकारिक DoTM साइट https://dotm.gov.np मा जाँच गर्नुहोस्',
        'officialUrl' => 'https://dotm.gov.np/license-status'
    ];
}

/**
 * Check National ID Card Status
 */
function checkNIDStatus($nidNo, $dob) {
    // NID Management Center API
    $apiUrl = 'https://nidmc.gov.np/api/status';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'registration_no' => $nidNo,
            'dob' => $dob
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || empty($response)) {
        // Fallback message
        return [
            'success' => false,
            'message' => 'कृपया आधिकारिक NID साइट https://nidmc.gov.np मा जाँच गर्नुहोस्',
            'officialUrl' => 'https://nidmc.gov.np/status'
        ];
    }
    
    $data = json_decode($response, true);
    if ($data && isset($data['print_status'])) {
        $isPrinted = strtoupper($data['print_status']) === 'PRINTED';
        return [
            'success' => true,
            'status' => $isPrinted ? 'printed' : 'pending',
            'statusText' => $isPrinted ? 'Print भइसकेको छ' : 'Print हुन बाँकी छ',
            'name' => $data['full_name'] ?? '',
            'district' => $data['district'] ?? '',
            'collectionPoint' => $data['collection_center'] ?? ''
        ];
    }
    
    return ['success' => false, 'message' => 'NID जानकारी भेटिएन'];
}

/**
 * Check Passport Application Status
 */
function checkPassportStatus($appNo, $dob) {
    return [
        'success' => false,
        'message' => 'कृपया आधिकारिक Passport साइट https://nepalpassport.gov.np मा जाँच गर्नुहोस्',
        'officialUrl' => 'https://nepalpassport.gov.np/track'
    ];
}

/**
 * Check PAN/VAT Status from IRD
 */
function checkPANStatus($panNo) {
    // IRD PAN verification
    $apiUrl = 'https://taxpayerportal.ird.gov.np/taxpayer/api/PanSearch/' . urlencode($panNo);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && !empty($response)) {
        $data = json_decode($response, true);
        if ($data && isset($data['taxpayerName'])) {
            return [
                'success' => true,
                'status' => 'verified',
                'statusText' => 'PAN दर्ता भएको छ',
                'name' => $data['taxpayerName'] ?? '',
                'panType' => $data['panType'] ?? '',
                'address' => $data['address'] ?? ''
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'कृपया IRD Portal https://ird.gov.np मा जाँच गर्नुहोस्',
        'officialUrl' => 'https://taxpayerportal.ird.gov.np/taxpayer/PanSearch'
    ];
}
