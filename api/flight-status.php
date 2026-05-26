<?php
/**
 * Flight Status API - Nepal Airport Flights
 * Uses FlightAware API with fallback to sample data
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300'); // 5 min cache

$airport = $_GET['airport'] ?? 'VNKT'; // Tribhuvan International Airport
$type = $_GET['type'] ?? 'departures'; // departures, arrivals

// Nepal airports
$airports = [
    'VNKT' => ['name' => 'Tribhuvan International', 'city' => 'Kathmandu', 'code' => 'KTM'],
    'VNKL' => ['name' => 'Pokhara International', 'city' => 'Pokhara', 'code' => 'PKR'],
    'VNRC' => ['name' => 'Gautam Buddha International', 'city' => 'Bhairahawa', 'code' => 'BWA'],
];

$airportInfo = $airports[$airport] ?? $airports['VNKT'];

try {
    // Try FlightAware API if key available
    $apiKey = defined('FLIGHTAWARE_API_KEY') ? FLIGHTAWARE_API_KEY : '';
    
    if ($apiKey) {
        $flights = fetchFlightAwareFlights($airport, $type, $apiKey);
    } else {
        $flights = getSampleFlights($airport, $type);
    }
    
    echo json_encode([
        'ok' => true,
        'airport' => $airportInfo,
        'type' => $type,
        'flights' => $flights,
        'count' => count($flights),
        'updated_at' => date('Y-m-d H:i:s'),
        'source' => $apiKey ? 'FlightAware API' : 'Sample Data',
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'flights' => getSampleFlights($airport, $type),
        'airport' => $airportInfo,
    ], JSON_UNESCAPED_UNICODE);
}

function fetchFlightAwareFlights(string $airport, string $type, string $apiKey): array {
    // FlightAware AeroAPI v4
    $url = 'https://aeroapi.flightaware.com/aero/airports/' . $airport . '/flights/' . $type;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-apikey: ' . $apiKey,
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        return [];
    }
    
    $data = json_decode($response, true);
    if (!isset($data['flights'])) {
        return [];
    }
    
    $flights = [];
    foreach ($data['flights'] as $f) {
        $flight = $f['flight_id'] ?? '';
        $airline = $f['operator'] ?? $f['airline_name'] ?? '';
        $destination = $f['destination'] ? ($f['destination']['icao_code'] ?? '') : '';
        $origin = $f['origin'] ? ($f['origin']['icao_code'] ?? '') : '';
        $scheduled = $f['scheduled_off'] ?? $f['scheduled_in'] ?? '';
        $status = $f['status'] ?? '';
        
        $flights[] = [
            'flight' => $flight,
            'airline' => $airline,
            'destination' => $destination,
            'origin' => $origin,
            'scheduled' => $scheduled,
            'status' => $status,
        ];
    }
    
    return array_slice($flights, 0, 20);
}

function getSampleFlights(string $airport, string $type): array {
    $airlines = ['Nepal Airlines', 'Buddha Air', 'Yeti Airlines', 'Himalaya Airlines', 'Shree Airlines'];
    $destinations = ['DEL', 'BOM', 'DXB', 'SIN', 'BKK', 'KUL', 'HKG', 'DOH', 'ISB', 'DAC'];
    $origins = ['DEL', 'BOM', 'DXB', 'SIN', 'BKK', 'KUL', 'HKG', 'DOH', 'ISB', 'DAC'];
    $statuses = ['On Time', 'Delayed', 'Departed', 'En Route', 'Landed', 'Cancelled'];
    
    $flights = [];
    $count = 10;
    
    for ($i = 0; $i < $count; $i++) {
        $airline = $airlines[array_rand($airlines)];
        $flightNum = rand(100, 999);
        $flight = $airline . ' ' . $flightNum;
        
        if ($type === 'departures') {
            $dest = $destinations[array_rand($destinations)];
            $flights[] = [
                'flight' => $flight,
                'airline' => $airline,
                'destination' => $dest,
                'origin' => $airport,
                'scheduled' => date('H:i', strtotime('+' . ($i * 15) . ' minutes')),
                'status' => $statuses[array_rand($statuses)],
            ];
        } else {
            $orig = $origins[array_rand($origins)];
            $flights[] = [
                'flight' => $flight,
                'airline' => $airline,
                'destination' => $airport,
                'origin' => $orig,
                'scheduled' => date('H:i', strtotime('+' . ($i * 15) . ' minutes')),
                'status' => $statuses[array_rand($statuses)],
            ];
        }
    }
    
    return $flights;
}
