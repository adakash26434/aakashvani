<?php
/**
 * api/download-proxy.php v1
 * Proxies whitelisted government form PDFs so users never leave the app.
 * ?id=citizenship|passport|pan|see|scholarship|preeti|mangal|patro|...
 *
 * Caches locally for 7 days. Falls back to redirect if fetch fails.
 */

// Whitelist of downloadable files with multiple mirror URLs
$FILES = [
    'citizenship' => [
        'name'  => 'नागरिकता आवेदन फारम',
        'file'  => 'citizenship-form.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://moha.gov.np/public/uploads/citizenship_form.pdf',
            'https://nepal.gov.np/sites/default/files/documents/citizenship_form.pdf',
            'https://doa.gov.np/storage/app/public/forms/citizenship.pdf',
        ],
        'fallback' => 'https://moha.gov.np/page/citizenship-forms',
    ],
    'passport' => [
        'name'  => 'राहदानी फारम',
        'file'  => 'passport-form.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://www.passport.gov.np/upload/form/passport_form.pdf',
            'https://www.passport.gov.np/public/pdf/new_passport.pdf',
        ],
        'fallback' => 'https://www.passport.gov.np/page/downloadForms',
    ],
    'pan' => [
        'name'  => 'PAN दर्ता फारम',
        'file'  => 'pan-registration-form.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://ird.gov.np/public/uploads/pan_registration_form.pdf',
            'https://www.ird.gov.np/assets/upload/files/pan_form.pdf',
        ],
        'fallback' => 'https://ird.gov.np/page/pan-registration',
    ],
    'vat' => [
        'name'  => 'VAT दर्ता फारम',
        'file'  => 'vat-registration.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://ird.gov.np/public/uploads/vat_registration_form.pdf',
        ],
        'fallback' => 'https://ird.gov.np/page/vat-registration',
    ],
    'drivinglicense' => [
        'name'  => 'सवारी चालक अनुमतिपत्र',
        'file'  => 'driving-license-form.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://www.dotm.gov.np/uploads/forms/driving_license.pdf',
            'https://dotm.gov.np/public/forms/dl_application.pdf',
        ],
        'fallback' => 'https://www.dotm.gov.np/en/forms',
    ],
    'vehicle' => [
        'name'  => 'सवारी दर्ता फारम',
        'file'  => 'vehicle-registration.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://www.dotm.gov.np/uploads/forms/vehicle_registration.pdf',
        ],
        'fallback' => 'https://www.dotm.gov.np/en/forms',
    ],
    'see' => [
        'name'  => 'SEE Routine 2081',
        'file'  => 'see-routine-2081.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://www.doe.gov.np/assets/uploads/files/see_routine.pdf',
            'https://doe.gov.np/public/uploads/see_2081.pdf',
        ],
        'fallback' => 'https://www.doe.gov.np',
    ],
    'scholarship' => [
        'name'  => 'Scholarship Form',
        'file'  => 'scholarship-form.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://moest.gov.np/public/uploads/scholarship_form.pdf',
        ],
        'fallback' => 'https://moest.gov.np/page/scholarship',
    ],
    'preeti' => [
        'name'  => 'Preeti Nepali Font',
        'file'  => 'Preeti.ttf',
        'type'  => 'font/ttf',
        'urls'  => [
            'https://fonts.org.np/public/Preeti.ttf',
            'https://download.fonts.org.np/Preeti.ttf',
        ],
        'fallback' => 'https://fonts.org.np/nepali-fonts/',
    ],
    'mangal' => [
        'name'  => 'Mangal Unicode Font',
        'file'  => 'Mangal.ttf',
        'type'  => 'font/ttf',
        'urls'  => [
            'https://fonts.org.np/public/Mangal.ttf',
        ],
        'fallback' => 'https://fonts.org.np/nepali-fonts/',
    ],
    'patro2082' => [
        'name'  => 'Nepali Patro 2082 PDF',
        'file'  => 'nepali-patro-2082.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://nepalidate.com/uploads/nepali-patro-2082.pdf',
        ],
        'fallback' => 'https://nepalipatro.com.np',
    ],
    'itr' => [
        'name'  => 'आयकर विवरण (ITR) फारम',
        'file'  => 'income-tax-return.pdf',
        'type'  => 'application/pdf',
        'urls'  => [
            'https://ird.gov.np/public/uploads/itr_form.pdf',
        ],
        'fallback' => 'https://ird.gov.np',
    ],
];

$id = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($_GET['id'] ?? '')));

if (!$id || !isset($FILES[$id])) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unknown file ID', 'available' => array_keys($FILES)]);
    exit;
}

$meta     = $FILES[$id];
$cacheDir = __DIR__ . '/../data/downloads';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$cacheFile = $cacheDir . '/' . $id . '_' . md5($meta['file']) . '_' . substr(md5(implode('', $meta['urls'])), 0, 8);
$cacheTtl  = 7 * 86400; // 7 days

// Serve from cache if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl) && filesize($cacheFile) > 500) {
    header('Content-Type: ' . $meta['type']);
    header('Content-Disposition: attachment; filename="' . $meta['file'] . '"');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=86400');
    readfile($cacheFile);
    exit;
}

// Try fetching from mirror URLs
$fetched = false;
foreach ($meta['urls'] as $url) {
    $ctx = stream_context_create([
        'http' => [
            'timeout'        => 15,
            'user_agent'     => 'Mozilla/5.0 (compatible; AakashvaniBot/1.0)',
            'follow_location'=> 1,
        ],
        'https' => [
            'timeout'        => 15,
            'user_agent'     => 'Mozilla/5.0 (compatible; AakashvaniBot/1.0)',
            'follow_location'=> 1,
        ],
    ]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 500) {
        @file_put_contents($cacheFile, $data);
        header('Content-Type: ' . $meta['type']);
        header('Content-Disposition: attachment; filename="' . $meta['file'] . '"');
        header('Content-Length: ' . strlen($data));
        header('Cache-Control: public, max-age=86400');
        echo $data;
        $fetched = true;
        break;
    }
}

if (!$fetched) {
    // Fallback: redirect to official source
    header('Location: ' . $meta['fallback']);
    exit;
}
