<?php
/**
 * आकाशवाणी — Weather & Alerts API v13
 * Real-time Weather for Nepal + Earthquake Alerts
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/error-logger.php';

// Security headers
sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=600');

// Rate limiting
$rateKey = 'weather:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, 60, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
    exit;
}

$type = $_GET['type'] ?? 'all';
$city = $_GET['city'] ?? null;

$cacheDir = __DIR__ . '/../data/cache/';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

if (!function_exists('readCache')) {
    function readCache(string $key, int $ttl = 1800): ?array {
        global $cacheDir;
        $file = $cacheDir . $key . '.json';
        if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) return $data;
        }
        return null;
    }
}

if (!function_exists('writeCache')) {
    function writeCache(string $key, array $data): void {
        global $cacheDir;
        $data['cached_at'] = date('Y-m-d H:i:s');
        file_put_contents($cacheDir . $key . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}

if (!function_exists('fetchUrl')) {
    function fetchUrl(string $url, int $timeout = 10): ?string {
        require_once __DIR__ . '/../includes/http.php';
        return nh_fetchUrl($url, [], $timeout, true);
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// NEPAL CITIES WITH COORDINATES
// ═══════════════════════════════════════════════════════════════════════════════

$nepalCities = [
    // Province 1
    'Biratnagar'  => ['lat' => 26.4525, 'lon' => 87.2718, 'province' => 'Province 1'],
    'Dharan'      => ['lat' => 26.8122, 'lon' => 87.2847, 'province' => 'Province 1'],
    'Itahari'     => ['lat' => 26.6670, 'lon' => 87.2790, 'province' => 'Province 1'],
    'Damak'       => ['lat' => 26.6608, 'lon' => 87.6933, 'province' => 'Province 1'],
    'Ilam'        => ['lat' => 26.9115, 'lon' => 87.9244, 'province' => 'Province 1'],
    
    // Madhesh Province
    'Janakpur'    => ['lat' => 26.7288, 'lon' => 85.9254, 'province' => 'Madhesh'],
    'Birgunj'     => ['lat' => 27.0104, 'lon' => 84.8770, 'province' => 'Madhesh'],
    'Rajbiraj'    => ['lat' => 26.5408, 'lon' => 86.7512, 'province' => 'Madhesh'],
    
    // Bagmati Province
    'Kathmandu'   => ['lat' => 27.7172, 'lon' => 85.3240, 'province' => 'Bagmati'],
    'Lalitpur'    => ['lat' => 27.6644, 'lon' => 85.3188, 'province' => 'Bagmati'],
    'Bhaktapur'   => ['lat' => 27.6710, 'lon' => 85.4298, 'province' => 'Bagmati'],
    'Hetauda'     => ['lat' => 27.4287, 'lon' => 85.0322, 'province' => 'Bagmati'],
    'Chitwan'     => ['lat' => 27.5291, 'lon' => 84.3542, 'province' => 'Bagmati'],
    
    // Gandaki Province
    'Pokhara'     => ['lat' => 28.2096, 'lon' => 83.9856, 'province' => 'Gandaki'],
    'Gorkha'      => ['lat' => 28.0000, 'lon' => 84.6167, 'province' => 'Gandaki'],
    'Damauli'     => ['lat' => 27.9667, 'lon' => 84.2833, 'province' => 'Gandaki'],
    
    // Lumbini Province
    'Butwal'      => ['lat' => 27.7006, 'lon' => 83.4486, 'province' => 'Lumbini'],
    'Siddharthanagar' => ['lat' => 27.5100, 'lon' => 83.4500, 'province' => 'Lumbini'],
    'Nepalgunj'   => ['lat' => 28.0500, 'lon' => 81.6167, 'province' => 'Lumbini'],
    'Tulsipur'    => ['lat' => 28.1340, 'lon' => 82.2960, 'province' => 'Lumbini'],
    
    // Karnali Province
    'Surkhet'     => ['lat' => 28.6000, 'lon' => 81.6167, 'province' => 'Karnali'],
    'Jumla'       => ['lat' => 29.2747, 'lon' => 82.1838, 'province' => 'Karnali'],
    
    // Sudurpashchim Province
    'Dhangadhi'   => ['lat' => 28.6833, 'lon' => 80.6000, 'province' => 'Sudurpashchim'],
    'Mahendranagar' => ['lat' => 28.9667, 'lon' => 80.1833, 'province' => 'Sudurpashchim'],
];

// ═══════════════════════════════════════════════════════════════════════════════
// WEATHER DATA — Open-Meteo API
// ═══════════════════════════════════════════════════════════════════════════════

function weatherCodeToEmoji(int $code): string {
    return match(true) {
        $code === 0 => 'sun',
        $code <= 3 => '⛅',
        in_array($code, [45, 48]) => '🌫️',
        in_array($code, [51, 53, 55, 56, 57]) => '🌦️',
        in_array($code, [61, 63, 65, 66, 67]) => 'cloud-rain',
        in_array($code, [71, 73, 75, 77]) => '❄️',
        in_array($code, [80, 81, 82]) => 'cloud-rain',
        in_array($code, [85, 86]) => '🌨️',
        in_array($code, [95, 96, 99]) => '⛈️',
        default => 'thermometer',
    };
}

function weatherCodeToDesc(int $code, string $lang = 'ne'): string {
    $desc = match(true) {
        $code === 0 => ['ne' => 'खुला आकाश', 'en' => 'Clear sky'],
        $code <= 3 => ['ne' => 'आंशिक बादल', 'en' => 'Partly cloudy'],
        in_array($code, [45, 48]) => ['ne' => 'हुस्सु', 'en' => 'Foggy'],
        in_array($code, [51, 53, 55]) => ['ne' => 'हल्का वर्षा', 'en' => 'Drizzle'],
        in_array($code, [56, 57]) => ['ne' => 'हिउँ मिसित वर्षा', 'en' => 'Freezing drizzle'],
        in_array($code, [61, 63, 65]) => ['ne' => 'वर्षा', 'en' => 'Rain'],
        in_array($code, [66, 67]) => ['ne' => 'जमेको वर्षा', 'en' => 'Freezing rain'],
        in_array($code, [71, 73, 75, 77]) => ['ne' => 'हिउँ', 'en' => 'Snow'],
        in_array($code, [80, 81, 82]) => ['ne' => 'भारी वर्षा', 'en' => 'Rain showers'],
        in_array($code, [85, 86]) => ['ne' => 'हिउँ आँधी', 'en' => 'Snow showers'],
        in_array($code, [95, 96, 99]) => ['ne' => 'गडगडाहट सहित वर्षा', 'en' => 'Thunderstorm'],
        default => ['ne' => 'सामान्य', 'en' => 'Unknown'],
    };
    return $desc[$lang] ?? $desc['en'];
}

function fetchWeatherForCity(string $city, float $lat, float $lon): ?array {
    $url = sprintf(
        'https://api.open-meteo.com/v1/forecast?latitude=%.4f&longitude=%.4f&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,wind_direction_10m,uv_index,surface_pressure&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weather_code&timezone=Asia%%2FKathmandu&forecast_days=5',
        $lat,
        $lon
    );
    
    $resp = fetchUrl($url, 8);
    if (!$resp) return null;
    
    $d = json_decode($resp, true);
    if (empty($d['current'])) return null;
    
    $c = $d['current'];
    $code = (int)($c['weather_code'] ?? 0);
    
    $forecast = [];
    if (!empty($d['daily']['time'])) {
        for ($i = 0; $i < min(5, count($d['daily']['time'])); $i++) {
            $dayCode = (int)($d['daily']['weather_code'][$i] ?? 0);
            $forecast[] = [
                'date'        => $d['daily']['time'][$i],
                'max_c'       => round((float)($d['daily']['temperature_2m_max'][$i] ?? 0)),
                'min_c'       => round((float)($d['daily']['temperature_2m_min'][$i] ?? 0)),
                'rain_chance' => (int)($d['daily']['precipitation_probability_max'][$i] ?? 0),
                'code'        => $dayCode,
                'emoji'       => weatherCodeToEmoji($dayCode),
                'desc_ne'     => weatherCodeToDesc($dayCode, 'ne'),
                'desc_en'     => weatherCodeToDesc($dayCode, 'en'),
            ];
        }
    }
    
    return [
        'city'       => $city,
        'temp_c'     => round((float)($c['temperature_2m'] ?? 0)),
        'feels_c'    => round((float)($c['apparent_temperature'] ?? 0)),
        'humidity'   => (int)($c['relative_humidity_2m'] ?? 0),
        'wind_kmph'  => round((float)($c['wind_speed_10m'] ?? 0)),
        'wind_dir'   => (int)($c['wind_direction_10m'] ?? 0),
        'uv'         => (int)($c['uv_index'] ?? 0),
        'pressure'   => round((float)($c['surface_pressure'] ?? 1000)),
        'code'       => $code,
        'emoji'      => weatherCodeToEmoji($code),
        'desc_ne'    => weatherCodeToDesc($code, 'ne'),
        'desc_en'    => weatherCodeToDesc($code, 'en'),
        'forecast'   => $forecast,
    ];
}

function getAllWeather(): array {
    global $nepalCities;
    
    $cached = readCache('weather_all', 1800);
    if ($cached) return $cached;
    
    $results = [];
    $majorCities = ['Kathmandu', 'Pokhara', 'Biratnagar', 'Lalitpur', 'Chitwan', 
                    'Butwal', 'Dharan', 'Janakpur', 'Hetauda', 'Nepalgunj'];
    
    foreach ($majorCities as $city) {
        if (!isset($nepalCities[$city])) continue;
        $coords = $nepalCities[$city];
        $weather = fetchWeatherForCity($city, $coords['lat'], $coords['lon']);
        if ($weather) {
            $weather['province'] = $coords['province'];
            $results[$city] = $weather;
        }
    }
    
    if (!empty($results)) {
        $data = [
            'cities'         => $results,
            'primaryCity'    => $results['Kathmandu'] ?? reset($results),
            'totalCities'    => count($results),
            'availableCities'=> array_keys($nepalCities),
            'updatedAt'      => date('Y-m-d H:i'),
            'source'         => 'Open-Meteo',
            'live'           => true,
        ];
        writeCache('weather_all', $data);
        return $data;
    }
    
    return [
        'available' => false,
        'cities'    => [],
        'updatedAt' => date('Y-m-d H:i'),
        'source'    => 'Open-Meteo',
        'live'      => false,
        'note'      => 'मौसम डाटा अहिले उपलब्ध छैन',
    ];
}

function getCityWeather(string $cityName): array {
    global $nepalCities;

    // Nepali → English city name mapping
    static $nepaliMap = [
        'काठमाडौं'  => 'Kathmandu',
        'काठमाडौ'   => 'Kathmandu',
        'पोखरा'     => 'Pokhara',
        'विराटनगर'  => 'Biratnagar',
        'नेपालगन्ज' => 'Nepalgunj',
        'नेपालगञ्ज' => 'Nepalgunj',
        'धनगढी'    => 'Dhangadhi',
        'धनगडी'    => 'Dhangadhi',
        'चितवन'    => 'Chitwan',
        'धरान'     => 'Dharan',
        'बुटवल'    => 'Butwal',
        'जनकपुर'   => 'Janakpur',
        'बिरगञ्ज'  => 'Birgunj',
        'ललितपुर'  => 'Lalitpur',
        'भक्तपुर'  => 'Bhaktapur',
        'हेटौंडा'   => 'Hetauda',
        'सुर्खेत'   => 'Surkhet',
        'जुम्ला'    => 'Jumla',
        'महेन्द्रनगर'=> 'Mahendranagar',
        'इटहरी'    => 'Itahari',
        'दमक'     => 'Damak',
        'इलाम'    => 'Ilam',
        'राजविराज' => 'Rajbiraj',
        'गोरखा'    => 'Gorkha',
        'दमौली'   => 'Damauli',
        'सिद्धार्थनगर' => 'Siddharthanagar',
        'तुलसीपुर' => 'Tulsipur',
    ];

    // Translate Nepali city name to English if needed
    $englishName = $nepaliMap[$cityName] ?? $cityName;

    // Find city (case-insensitive, also partial match)
    $found = null;
    foreach ($nepalCities as $name => $coords) {
        if (strcasecmp($name, $englishName) === 0) {
            $found = ['name' => $name, 'coords' => $coords];
            break;
        }
    }
    // Partial match fallback
    if (!$found) {
        foreach ($nepalCities as $name => $coords) {
            if (stripos($name, $englishName) !== false || stripos($englishName, $name) !== false) {
                $found = ['name' => $name, 'coords' => $coords];
                break;
            }
        }
    }
    
    if (!$found) {
        return [
            'available' => false,
            'error'     => 'City not found',
            'note'      => 'उपलब्ध शहरहरू: ' . implode(', ', array_keys($nepalCities)),
        ];
    }
    
    $cacheKey = 'weather_' . strtolower($found['name']);
    $cached = readCache($cacheKey, 1800);
    if ($cached) return $cached;
    
    $weather = fetchWeatherForCity($found['name'], $found['coords']['lat'], $found['coords']['lon']);
    if ($weather) {
        $weather['province'] = $found['coords']['province'];
        $weather['updatedAt'] = date('Y-m-d H:i');
        $weather['source'] = 'Open-Meteo';
        $weather['live'] = true;
        writeCache($cacheKey, $weather);
        return $weather;
    }
    
    return [
        'available' => false,
        'city'      => $found['name'],
        'note'      => 'मौसम डाटा उपलब्ध छैन',
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// EARTHQUAKE DATA — USGS API
// ═══════════════════════════════════════════════════════════════════════════════

function getEarthquakeAlerts(): array {
    $cached = readCache('earthquake_alerts', 600);
    if ($cached) return $cached;
    
    // USGS API for last 7 days, magnitude 2.5+
    $url = 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_week.geojson';
    $resp = fetchUrl($url, 12);
    
    if (!$resp) {
        return [
            'available' => false,
            'quakes'    => [],
            'updatedAt' => date('Y-m-d H:i'),
            'source'    => 'USGS',
            'live'      => false,
            'note'      => 'भूकम्प डाटा उपलब्ध छैन',
        ];
    }
    
    $data = json_decode($resp, true);
    $features = $data['features'] ?? [];
    
    // Nepal and South Asia bounds
    $nepalBounds = ['minLat' => 26.3, 'maxLat' => 30.5, 'minLon' => 80.0, 'maxLon' => 88.5];
    $regionBounds = ['minLat' => 20.0, 'maxLat' => 40.0, 'minLon' => 68.0, 'maxLon' => 98.0];
    
    $keywords = ['nepal','india','tibet','bhutan','bangladesh','myanmar','pakistan','afghanistan',
                 'kashmir','himalaya','sikkim','uttarakhand','assam','bihar','uttar pradesh'];
    
    $nepalQuakes = [];
    $regionQuakes = [];
    
    foreach ($features as $f) {
        $props = $f['properties'] ?? [];
        $geo = $f['geometry']['coordinates'] ?? [];
        
        if (count($geo) < 3) continue;
        
        $lon = $geo[0];
        $lat = $geo[1];
        $depth = $geo[2];
        $mag = (float)($props['mag'] ?? 0);
        $place = strtolower($props['place'] ?? '');
        $title = $props['title'] ?? '';
        
        // Check if in region
        $isInRegion = ($lat >= $regionBounds['minLat'] && $lat <= $regionBounds['maxLat'] &&
                       $lon >= $regionBounds['minLon'] && $lon <= $regionBounds['maxLon']);
        
        $isKeyword = false;
        foreach ($keywords as $kw) {
            if (str_contains($place, $kw)) { $isKeyword = true; break; }
        }
        
        if (!$isInRegion && !$isKeyword) continue;
        
        // Check if specifically Nepal
        $isNepal = ($lat >= $nepalBounds['minLat'] && $lat <= $nepalBounds['maxLat'] &&
                    $lon >= $nepalBounds['minLon'] && $lon <= $nepalBounds['maxLon']) ||
                   str_contains($place, 'nepal');
        
        $time = isset($props['time']) ? date('Y-m-d H:i', $props['time'] / 1000) : null;
        $timeAgo = isset($props['time']) ? getTimeAgo($props['time'] / 1000) : '';
        
        $quake = [
            'magnitude'    => $mag,
            'place'        => $props['place'] ?? 'Unknown',
            'place_ne'     => translatePlace($props['place'] ?? ''),
            'time'         => $time,
            'timeAgo'      => $timeAgo,
            'timeAgo_ne'   => getTimeAgoNepali($props['time'] / 1000),
            'depth_km'     => round($depth),
            'lat'          => round($lat, 4),
            'lon'          => round($lon, 4),
            'isNepal'      => $isNepal,
            'severity'     => match(true) {
                $mag >= 7.0 => 'critical',
                $mag >= 6.0 => 'high',
                $mag >= 5.0 => 'medium',
                $mag >= 4.0 => 'low',
                default => 'minor',
            },
            'alert'        => $props['alert'] ?? null,
            'tsunami'      => (int)($props['tsunami'] ?? 0),
            'felt'         => (int)($props['felt'] ?? 0),
            'url'          => $props['url'] ?? null,
        ];
        
        if ($isNepal) {
            $nepalQuakes[] = $quake;
        }
        $regionQuakes[] = $quake;
    }
    
    // Sort by time
    usort($nepalQuakes, fn($a, $b) => strcmp($b['time'], $a['time']));
    usort($regionQuakes, fn($a, $b) => strcmp($b['time'], $a['time']));
    
    $result = [
        'nepal'           => array_slice($nepalQuakes, 0, 15),
        'region'          => array_slice($regionQuakes, 0, 25),
        'nepalCount'      => count($nepalQuakes),
        'regionCount'     => count($regionQuakes),
        'latestNepal'     => !empty($nepalQuakes) ? $nepalQuakes[0] : null,
        'latestSignificant' => getLatestSignificant($regionQuakes),
        'updatedAt'       => date('Y-m-d H:i'),
        'source'          => 'USGS Earthquake Hazards Program',
        'live'            => true,
        'note_ne'         => 'पछिल्लो ७ दिनको भूकम्पीय गतिविधि',
        'emergency'       => [
            'nepal'   => '1122',
            'police'  => '100',
            'ambulance' => '102',
        ],
    ];
    
    writeCache('earthquake_alerts', $result);
    return $result;
}

function getLatestSignificant(array $quakes): ?array {
    foreach ($quakes as $q) {
        if ($q['magnitude'] >= 5.0) return $q;
    }
    return null;
}

function getTimeAgo(int $timestamp): string {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

function getTimeAgoNepali(int $timestamp): string {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'अहिले भर्खरै';
    if ($diff < 3600) return floor($diff / 60) . ' मिनेट अघि';
    if ($diff < 86400) return floor($diff / 3600) . ' घण्टा अघि';
    return floor($diff / 86400) . ' दिन अघि';
}

function translatePlace(string $place): string {
    $translations = [
        'nepal' => 'नेपाल',
        'india' => 'भारत',
        'tibet' => 'तिब्बत',
        'china' => 'चीन',
        'bhutan' => 'भुटान',
        'km' => 'कि.मी.',
        'of' => 'को',
        'region' => 'क्षेत्र',
    ];
    
    $result = $place;
    foreach ($translations as $en => $ne) {
        $result = str_ireplace($en, $ne, $result);
    }
    return $result;
}

// ═══════════════════════════════════════════════════════════════════════════════
// BIPAD (Nepal Disaster) — NDRRMA
// ═══════════════════════════════════════════════════════════════════════════════

function getBipadAlerts(): array {
    $cached = readCache('bipad_alerts', 1800);
    if ($cached) return $cached;
    
    // Note: BIPAD API may require registration
    // This is a placeholder for when API access is available
    $alerts = [
        'available' => true,
        'alerts'    => [],
        'source'    => 'NDRRMA BIPAD Portal',
        'link'      => 'https://bipadportal.gov.np/',
        'note'      => 'विपद पोर्टलबाट अलर्टहरू',
        'updatedAt' => date('Y-m-d H:i'),
    ];
    
    writeCache('bipad_alerts', $alerts);
    return $alerts;
}

// ═══════════════════════════════════════════════════════════════════════════════
// API ROUTER
// ═══════════════════════════════════════════════════════════════════════════════

$response = [];

switch ($type) {
    case 'weather':
        if ($city) {
            $response = getCityWeather($city);
        } else {
            $response = getAllWeather();
        }
        break;
        
    case 'earthquake':
    case 'quake':
        $response = getEarthquakeAlerts();
        break;
        
    case 'bipad':
    case 'disaster':
        $response = getBipadAlerts();
        break;
        
    case 'cities':
        global $nepalCities;
        $response = [
            'cities' => array_keys($nepalCities),
            'count'  => count($nepalCities),
        ];
        break;
        
    case 'all':
    default:
        $weather = getAllWeather();
        $earthquake = getEarthquakeAlerts();
        
        $response = [
            'weather'       => $weather,
            'earthquake'    => $earthquake,
            'alerts'        => [
                'hasSignificantQuake' => !empty($earthquake['latestSignificant']),
                'nepalQuakeCount'     => $earthquake['nepalCount'] ?? 0,
            ],
            'generatedAt'   => date('Y-m-d H:i:s'),
        ];
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
