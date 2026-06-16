<?php
/**
 * Earthquake API - Real-time seismic data for Nepal and surrounding region
 * Uses USGS Earthquake Catalog API
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/error-logger.php';
require_once __DIR__ . '/../includes/http.php';

// Security headers
sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300'); // 5 minute cache

// Rate limiting
$rateKey = 'eq:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, 60, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
    exit;
}

// Nepal region boundaries
$NEPAL_BOUNDS = [
    'minlat' => 26.0,
    'maxlat' => 30.5,
    'minlon' => 80.0,
    'maxlon' => 88.5
];

/**
 * Fetch earthquake data from USGS
 */
function fetchEarthquakes($minmag = 3.0, $days = 30) {
    global $NEPAL_BOUNDS;
    
    $starttime = date('Y-m-d', strtotime("-{$days} days"));
    $endtime = date('Y-m-d');
    
    $url = sprintf(
        'https://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&starttime=%s&endtime=%s&minlatitude=%f&maxlatitude=%f&minlongitude=%f&maxlongitude=%f&minmagnitude=%f&orderby=time',
        $starttime,
        $endtime,
        $NEPAL_BOUNDS['minlat'],
        $NEPAL_BOUNDS['maxlat'],
        $NEPAL_BOUNDS['minlon'],
        $NEPAL_BOUNDS['maxlon'],
        $minmag
    );
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!$data || empty($data['features'])) {
        return [];
    }
    
    // Process and format earthquake data
    $earthquakes = [];
    foreach ($data['features'] as $feature) {
        $props = $feature['properties'];
        $coords = $feature['geometry']['coordinates'];
        
        $earthquakes[] = [
            'id' => $feature['id'],
            'magnitude' => $props['mag'],
            'place' => translateLocation($props['place']),
            'time' => $props['time'],
            'date' => date('Y-m-d H:i:s', $props['time'] / 1000),
            'depth' => $coords[2], // km
            'latitude' => $coords[1],
            'longitude' => $coords[0],
            'url' => $props['url'],
            'alert' => $props['alert'] ?? null,
            'tsunami' => $props['tsunami'] ?? 0,
            'felt' => $props['felt'] ?? 0,
            'significance' => calculateSignificance($props['mag'], $coords[2]),
            'distance_from_kathmandu' => calculateDistance($coords[1], $coords[0])
        ];
    }
    
    return $earthquakes;
}

/**
 * Translate common location names to Nepali
 */
function translateLocation($place) {
    $translations = [
        'Nepal' => 'नेपाल',
        'India' => 'भारत',
        'China' => 'चीन',
        'Tibet' => 'तिब्बत',
        'Kathmandu' => 'काठमाडौं',
        'Pokhara' => 'पोखरा',
        'Biratnagar' => 'विराटनगर',
        'Bhairahawa' => 'भैरहवा',
        'Dharan' => 'धरान',
        'Butwal' => 'बुटवल',
        'Nepalgunj' => 'नेपालगंज',
        'Dhangadhi' => 'धनगढी',
        'Surkhet' => 'सुर्खेत',
        'Jumla' => 'जुम्ला',
        'Gorkha' => 'गोरखा',
        'Sindhupalchok' => 'सिन्धुपाल्चोक',
        'Dolakha' => 'दोलखा',
        'Rasuwa' => 'रasuwa',
        'Janakpur' => 'जनकपुर',
        'Birgunj' => 'वीरगंज',
        'Lumbini' => 'लुम्बिनी',
        'Gandaki' => 'गण्डकी',
        'Bagmati' => 'बाग्मती',
        'Koshi' => 'कोशी',
        'Madhesh' => 'मधेश',
        'Sudurpashchim' => 'सुदूरपश्चिम',
        'Karnali' => 'कर्णाली'
    ];
    
    $translated = $place;
    foreach ($translations as $en => $ne) {
        $translated = str_ireplace($en, $ne, $translated);
    }
    
    return $translated;
}

/**
 * Calculate earthquake significance for Nepal
 */
function calculateSignificance($magnitude, $depth) {
    // Nepal-specific risk calculation
    // Shallow earthquakes (depth < 70km) are more dangerous in Nepal
    // Magnitude >= 5 is significant
    
    $score = $magnitude;
    
    // Depth factor (shallow = more dangerous)
    if ($depth < 15) {
        $score += 2;
    } elseif ($depth < 35) {
        $score += 1;
    } elseif ($depth < 70) {
        $score += 0.5;
    }
    
    // Magnitude classification
    if ($magnitude >= 7.0) {
        return ['level' => 'extreme', 'text' => 'अत्यन्त गम्भीर', 'color' => '#dc2626', 'score' => $score];
    } elseif ($magnitude >= 6.0) {
        return ['level' => 'high', 'text' => 'गम्भीर', 'color' => '#ea580c', 'score' => $score];
    } elseif ($magnitude >= 5.0) {
        return ['level' => 'moderate', 'text' => 'मध्यम', 'color' => '#ca8a04', 'score' => $score];
    } elseif ($magnitude >= 4.0) {
        return ['level' => 'low', 'text' => 'सामान्य', 'color' => '#16a34a', 'score' => $score];
    } else {
        return ['level' => 'minor', 'text' => 'सामान्य', 'color' => '#0891b2', 'score' => $score];
    }
}

/**
 * Calculate distance from Kathmandu
 */
function calculateDistance($lat, $lon) {
    $kathmandu = ['lat' => 27.7172, 'lon' => 85.3240];
    
    $R = 6371; // Earth's radius in km
    $lat1 = deg2rad($kathmandu['lat']);
    $lat2 = deg2rad($lat);
    $deltaLat = deg2rad($lat - $kathmandu['lat']);
    $deltaLon = deg2rad($lon - $kathmandu['lon']);
    
    $a = sin($deltaLat/2) * sin($deltaLat/2) +
         cos($lat1) * cos($lat2) *
         sin($deltaLon/2) * sin($deltaLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return round($R * $c, 1);
}

// Get earthquake data
$minMagnitude = isset($_GET['minmag']) ? floatval($_GET['minmag']) : 3.0;
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;

$earthquakes = fetchEarthquakes($minMagnitude, $days);

if ($earthquakes === null) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'Unable to fetch earthquake data from USGS',
        'data' => []
    ]);
    exit;
}

// Get summary statistics
$stats = [
    'total' => count($earthquakes),
    'significant' => 0,
    'recent_24h' => 0,
    'magnitude_5_plus' => 0
];

$now = time();
foreach ($earthquakes as $eq) {
    if ($eq['significance']['level'] !== 'minor') {
        $stats['significant']++;
    }
    if (($now - ($eq['time'] / 1000)) < 86400) {
        $stats['recent_24h']++;
    }
    if ($eq['magnitude'] >= 5.0) {
        $stats['magnitude_5_plus']++;
    }
}

echo json_encode([
    'ok' => true,
    'updated' => date('Y-m-d H:i:s'),
    'region' => 'Nepal and Surrounding Area',
    'bounds' => $NEPAL_BOUNDS,
    'statistics' => $stats,
    'data' => $earthquakes
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
