<?php
/**
 * Hospital/Health Centers Directory API
 * Nepal hospitals and health centers directory
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

$city = $_GET['city'] ?? null;
$type = $_GET['type'] ?? null; // hospital, clinic, pharmacy

try {
    $hospitals = getHospitals($city, $type);
    
    echo json_encode([
        'ok' => true,
        'hospitals' => $hospitals,
        'count' => count($hospitals),
        'cities' => getAvailableCities(),
        'types' => getAvailableTypes(),
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function getHospitals(?string $city = null, ?string $type = null): array {
    $allHospitals = [
        // Kathmandu
        ['name' => 'Tribhuvan University Teaching Hospital', 'name_ne' => 'त्रिभुवन विश्वविद्यालय शिक्षण अस्पताल', 'city' => 'Kathmandu', 'city_ne' => 'काठमाडौं', 'type' => 'hospital', 'phone' => '01-4423041', 'address' => 'Maharajgunj', 'emergency' => '01-4423041'],
        ['name' => 'B.P. Koirala Institute of Health Sciences', 'name_ne' => 'बी.पी. कोइराला स्वास्थ्य विज्ञान संस्थान', 'city' => 'Kathmandu', 'city_ne' => 'काठमाडौं', 'type' => 'hospital', 'phone' => '01-4423041', 'address' => 'Maharajgunj', 'emergency' => '01-4423041'],
        ['name' => 'Grande International Hospital', 'name_ne' => 'ग्रान्डे अन्तर्राष्ट्रिय अस्पताल', 'city' => 'Kathmandu', 'city_ne' => 'काठमाडौं', 'type' => 'hospital', 'phone' => '01-4372000', 'address' => 'Dhapasi', 'emergency' => '01-4372000'],
        ['name' => 'Nobel Hospital', 'name_ne' => 'नोबेल अस्पताल', 'city' => 'Kathmandu', 'city_ne' => 'काठमाडौं', 'type' => 'hospital', 'phone' => '01-4159000', 'address' => 'Sinamangal', 'emergency' => '01-4159000'],
        ['name' => 'Medicare Hospital', 'name_ne' => 'मेडिकेयर अस्पताल', 'city' => 'Kathmandu', 'city_ne' => 'काठमाडौं', 'type' => 'hospital', 'phone' => '01-4258000', 'address' => 'Naxal', 'emergency' => '01-4258000'],
        ['name' => 'Bir Hospital', 'name_ne' => 'बीर अस्पताल', 'city' => 'Kathmandu', 'city_ne' => 'काठमाडौं', 'type' => 'hospital', 'phone' => '01-4412350', 'address' => 'Kathmandu', 'emergency' => '01-4412350'],
        
        // Pokhara
        ['name' => 'Pokhara Academy of Health Sciences', 'name_ne' => 'पोखरा स्वास्थ्य विज्ञान अकादमी', 'city' => 'Pokhara', 'city_ne' => 'पोखरा', 'type' => 'hospital', 'phone' => '061-520123', 'address' => 'Pokhara', 'emergency' => '061-520123'],
        ['name' => 'Manipal Teaching Hospital', 'name_ne' => 'मणिपाल शिक्षण अस्पताल', 'city' => 'Pokhara', 'city_ne' => 'पोखरा', 'type' => 'hospital', 'phone' => '061-520015', 'address' => 'Phulbari', 'emergency' => '061-520015'],
        ['name' => 'Western Regional Hospital', 'name_ne' => 'पश्चिमाञ्चल क्षेत्रीय अस्पताल', 'city' => 'Pokhara', 'city_ne' => 'पोखरा', 'type' => 'hospital', 'phone' => '061-520123', 'address' => 'Pokhara', 'emergency' => '061-520123'],
        
        // Butwal
        ['name' => 'Butwal Hospital', 'name_ne' => 'बुटवल अस्पताल', 'city' => 'Butwal', 'city_ne' => 'बुटवल', 'type' => 'hospital', 'phone' => '071-540123', 'address' => 'Butwal', 'emergency' => '071-540123'],
        ['name' => 'Lumbini Provincial Hospital', 'name_ne' => 'लुम्बिनी प्रदेशीय अस्पताल', 'city' => 'Butwal', 'city_ne' => 'बुटवल', 'type' => 'hospital', 'phone' => '071-540123', 'address' => 'Butwal', 'emergency' => '071-540123'],
        
        // Biratnagar
        ['name' => 'Birat Medical College', 'name_ne' => 'बिराट मेडिकल कलेज', 'city' => 'Biratnagar', 'city_ne' => 'बिराटनगर', 'type' => 'hospital', 'phone' => '021-525123', 'address' => 'Biratnagar', 'emergency' => '021-525123'],
        ['name' => 'Koshi Hospital', 'name_ne' => 'कोशी अस्पताल', 'city' => 'Biratnagar', 'city_ne' => 'बिराटनगर', 'type' => 'hospital', 'phone' => '021-525123', 'address' => 'Biratnagar', 'emergency' => '021-525123'],
        
        // Chitwan
        ['name' => 'Chitwan Medical College', 'name_ne' => 'चितवन मेडिकल कलेज', 'city' => 'Chitwan', 'city_ne' => 'चितवन', 'type' => 'hospital', 'phone' => '056-580123', 'address' => 'Bharatpur', 'emergency' => '056-580123'],
        ['name' => 'Bharatpur Hospital', 'name_ne' => 'भरतपुर अस्पताल', 'city' => 'Chitwan', 'city_ne' => 'चितवन', 'type' => 'hospital', 'phone' => '056-580123', 'address' => 'Bharatpur', 'emergency' => '056-580123'],
        
        // Nepalgunj
        ['name' => 'Bheri Hospital', 'name_ne' => 'भेरी अस्पताल', 'city' => 'Nepalgunj', 'city_ne' => 'नेपालगञ्ज', 'type' => 'hospital', 'phone' => '081-520123', 'address' => 'Nepalgunj', 'emergency' => '081-520123'],
        ['name' => 'Kohalpur Hospital', 'name_ne' => 'कोहलपुर अस्पताल', 'city' => 'Nepalgunj', 'city_ne' => 'नेपालगञ्ज', 'type' => 'hospital', 'phone' => '081-520123', 'address' => 'Nepalgunj', 'emergency' => '081-520123'],
    ];
    
    // Filter by city
    if ($city) {
        $allHospitals = array_filter($allHospitals, function($h) use ($city) {
            return strtolower($h['city']) === strtolower($city) || strtolower($h['city_ne']) === strtolower($city);
        });
    }
    
    // Filter by type
    if ($type) {
        $allHospitals = array_filter($allHospitals, function($h) use ($type) {
            return strtolower($h['type']) === strtolower($type);
        });
    }
    
    return array_values($allHospitals);
}

function getAvailableCities(): array {
    return [
        ['name' => 'Kathmandu', 'name_ne' => 'काठमाडौं'],
        ['name' => 'Pokhara', 'name_ne' => 'पोखरा'],
        ['name' => 'Butwal', 'name_ne' => 'बुटवल'],
        ['name' => 'Biratnagar', 'name_ne' => 'बिराटनगर'],
        ['name' => 'Chitwan', 'name_ne' => 'चितवन'],
        ['name' => 'Nepalgunj', 'name_ne' => 'नेपालगञ्ज'],
    ];
}

function getAvailableTypes(): array {
    return [
        ['name' => 'hospital', 'name_ne' => 'अस्पताल'],
        ['name' => 'clinic', 'name_ne' => 'क्लिनिक'],
        ['name' => 'pharmacy', 'name_ne' => 'फार्मेसी'],
    ];
}
