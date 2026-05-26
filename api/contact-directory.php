<?php
/**
 * Contact Directory API
 * Nepal office/organization contact directory (like 198 service)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

$search = $_GET['search'] ?? null;
$category = $_GET['category'] ?? null;
$city = $_GET['city'] ?? null;

try {
    $contacts = getContacts($search, $category, $city);
    
    echo json_encode([
        'ok' => true,
        'contacts' => $contacts,
        'count' => count($contacts),
        'categories' => getAvailableCategories(),
        'cities' => getAvailableCities(),
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function getContacts(?string $search = null, ?string $category = null, ?string $city = null): array {
    $allContacts = [
        // Government Offices
        ['name' => 'Prime Minister Office', 'name_ne' => 'प्रधानमन्त्री कार्यालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Singha Durbar', 'email' => 'pmo@pmo.gov.np'],
        ['name' => 'President Office', 'name_ne' => 'राष्ट्रपति कार्यालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Sheetal Niwas', 'email' => 'president@president.gov.np'],
        ['name' => 'Parliament Secretariat', 'name_ne' => 'संसद सचिवालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'New Baneshwor', 'email' => 'info@parliament.gov.np'],
        ['name' => 'Ministry of Home Affairs', 'name_ne' => 'गृह मन्त्रालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Singha Durbar', 'email' => 'info@moha.gov.np'],
        ['name' => 'Ministry of Finance', 'name_ne' => 'वित्त मन्त्रालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Singha Durbar', 'email' => 'info@mof.gov.np'],
        ['name' => 'Ministry of Health', 'name_ne' => 'स्वास्थ्य मन्त्रालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Ramshahpath', 'email' => 'info@moh.gov.np'],
        ['name' => 'Ministry of Education', 'name_ne' => 'शिक्षा मन्त्रालय', 'category' => 'government', 'category_ne' => 'सरकारी', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Kirtipur', 'email' => 'info@moe.gov.np'],
        
        // Emergency Services
        ['name' => 'Police Emergency', 'name_ne' => 'प्रहरी आपतकालीन', 'category' => 'emergency', 'category_ne' => 'आपतकालीन', 'city' => 'All', 'phone' => '100', 'address' => 'Nationwide', 'email' => ''],
        ['name' => 'Ambulance', 'name_ne' => 'एम्बुलेन्स', 'category' => 'emergency', 'category_ne' => 'आपतकालीन', 'city' => 'All', 'phone' => '102', 'address' => 'Nationwide', 'email' => ''],
        ['name' => 'Fire Service', 'name_ne' => 'आग नियन्त्रण', 'category' => 'emergency', 'category_ne' => 'आपतकालीन', 'city' => 'All', 'phone' => '101', 'address' => 'Nationwide', 'email' => ''],
        ['name' => 'Traffic Police', 'name_ne' => 'यातायात प्रहरी', 'category' => 'emergency', 'category_ne' => 'आपतकालीन', 'city' => 'All', 'phone' => '103', 'address' => 'Nationwide', 'email' => ''],
        
        // Utilities
        ['name' => 'Nepal Electricity Authority', 'name_ne' => 'नेपाल विद्युत प्राधिकरण', 'category' => 'utility', 'category_ne' => 'उपयोगिती', 'city' => 'Kathmandu', 'phone' => '166001-44-444', 'address' => 'Kathmandu', 'email' => 'info@nea.org.np'],
        ['name' => 'Nepal Telecom', 'name_ne' => 'नेपाल टेलिकम', 'category' => 'utility', 'category_ne' => 'उपयोगिती', 'city' => 'Kathmandu', 'phone' => '198', 'address' => 'Bhadrakali', 'email' => 'info@ntc.net.np'],
        ['name' => 'Ncell', 'name_ne' => 'एनसेल', 'category' => 'utility', 'category_ne' => 'उपयोगिती', 'city' => 'Kathmandu', 'phone' => '198', 'address' => 'Naxal', 'email' => 'info@ncell.com.np'],
        ['name' => 'Kathmandu Upatyaka Khanepani', 'name_ne' => 'काठमाडौं उपत्यका खानेपानी', 'category' => 'utility', 'category_ne' => 'उपयोगिती', 'city' => 'Kathmandu', 'phone' => '166001-44-444', 'address' => 'Tripureshwor', 'email' => 'info@kuksl.com.np'],
        
        // Banks
        ['name' => 'Nepal Rastra Bank', 'name_ne' => 'नेपाल राष्ट्र बैंक', 'category' => 'bank', 'category_ne' => 'बैंक', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Baluwatar', 'email' => 'info@nrb.org.np'],
        ['name' => 'Nabil Bank', 'name_ne' => 'नेपाल इन्भेष्टमेन्ट बैंक', 'category' => 'bank', 'category_ne' => 'बैंक', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Thamel', 'email' => 'info@nabilbank.com'],
        ['name' => 'Nepal Investment Bank', 'name_ne' => 'नेपाल इन्भेष्टमेन्ट बैंक', 'category' => 'bank', 'category_ne' => 'बैंक', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Kamaladi', 'email' => 'info@nibl.com.np'],
        ['name' => 'Global IME Bank', 'name_ne' => 'ग्लोबल आईएमई बैंक', 'category' => 'bank', 'category_ne' => 'बैंक', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'New Baneshwor', 'email' => 'info@gimebank.com'],
        
        // Hospitals
        ['name' => 'Tribhuvan University Teaching Hospital', 'name_ne' => 'त्रिभुवन विश्वविद्यालय शिक्षण अस्पताल', 'category' => 'hospital', 'category_ne' => 'अस्पताल', 'city' => 'Kathmandu', 'phone' => '01-4423041', 'address' => 'Maharajgunj', 'email' => 'info@tuthhospital.org'],
        ['name' => 'Bir Hospital', 'name_ne' => 'बीर अस्पताल', 'category' => 'hospital', 'category_ne' => 'अस्पताल', 'city' => 'Kathmandu', 'phone' => '01-4422350', 'address' => 'Kathmandu', 'email' => 'info@birhospital.gov.np'],
        ['name' => 'Grande International Hospital', 'name_ne' => 'ग्रान्डे अन्तर्राष्ट्रिय अस्पताल', 'category' => 'hospital', 'category_ne' => 'अस्पताल', 'city' => 'Kathmandu', 'phone' => '01-4372000', 'address' => 'Dhapasi', 'email' => 'info@grandeinternationalhospital.com'],
        
        // Educational Institutions
        ['name' => 'Tribhuvan University', 'name_ne' => 'त्रिभुवन विश्वविद्यालय', 'category' => 'education', 'category_ne' => 'शिक्षा', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Kirtipur', 'email' => 'info@tu.edu.np'],
        ['name' => 'Kathmandu University', 'name_ne' => 'काठमाडौं विश्वविद्यालय', 'category' => 'education', 'category_ne' => 'शिक्षा', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Dhulikhel', 'email' => 'info@ku.edu.np'],
        ['name' => 'Pokhara University', 'name_ne' => 'पोखरा विश्वविद्यालय', 'category' => 'education', 'category_ne' => 'शिक्षा', 'city' => 'Pokhara', 'phone' => '061-520123', 'address' => 'Pokhara', 'email' => 'info@pu.edu.np'],
        
        // Media
        ['name' => 'Radio Nepal', 'name_ne' => 'रेडियो नेपाल', 'category' => 'media', 'category_ne' => 'मिडिया', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Singha Durbar', 'email' => 'info@radionepal.gov.np'],
        ['name' => 'Nepal Television', 'name_ne' => 'नेपाल टेलिभिजन', 'category' => 'media', 'category_ne' => 'मिडिया', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'Singha Durbar', 'email' => 'info@ntv.gov.np'],
        ['name' => 'The Rising Nepal', 'name_ne' => 'द राइजिङ नेपाल', 'category' => 'media', 'category_ne' => 'मिडिया', 'city' => 'Kathmandu', 'phone' => '01-4423000', 'address' => 'New Baneshwor', 'email' => 'info@risingnepal.com.np'],
    ];
    
    // Filter by search
    if ($search) {
        $searchLower = strtolower($search);
        $allContacts = array_filter($allContacts, function($c) use ($searchLower) {
            return strpos(strtolower($c['name']), $searchLower) !== false ||
                   strpos(strtolower($c['name_ne']), $searchLower) !== false ||
                   strpos(strtolower($c['phone']), $searchLower) !== false;
        });
    }
    
    // Filter by category
    if ($category) {
        $allContacts = array_filter($allContacts, function($c) use ($category) {
            return strtolower($c['category']) === strtolower($category) || strtolower($c['category_ne']) === strtolower($category);
        });
    }
    
    // Filter by city
    if ($city && $city !== 'All') {
        $allContacts = array_filter($allContacts, function($c) use ($city) {
            return strtolower($c['city']) === strtolower($city);
        });
    }
    
    return array_values($allContacts);
}

function getAvailableCategories(): array {
    return [
        ['name' => 'government', 'name_ne' => 'सरकारी'],
        ['name' => 'emergency', 'name_ne' => 'आपतकालीन'],
        ['name' => 'utility', 'name_ne' => 'उपयोगिती'],
        ['name' => 'bank', 'name_ne' => 'बैंक'],
        ['name' => 'hospital', 'name_ne' => 'अस्पताल'],
        ['name' => 'education', 'name_ne' => 'शिक्षा'],
        ['name' => 'media', 'name_ne' => 'मिडिया'],
    ];
}

function getAvailableCities(): array {
    return [
        ['name' => 'All', 'name_ne' => 'सबै'],
        ['name' => 'Kathmandu', 'name_ne' => 'काठमाडौं'],
        ['name' => 'Pokhara', 'name_ne' => 'पोखरा'],
        ['name' => 'Biratnagar', 'name_ne' => 'बिराटनगर'],
        ['name' => 'Chitwan', 'name_ne' => 'चितवन'],
        ['name' => 'Nepalgunj', 'name_ne' => 'नेपालगञ्ज'],
    ];
}
