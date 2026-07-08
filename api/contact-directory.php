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

function ensureContactDirectoryTable(): void {
    static $done = false;
    if ($done) return;
    $db = db();
    if (!$db) return;
    $db->exec("CREATE TABLE IF NOT EXISTS contact_directory (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        name_ne TEXT,
        phone TEXT,
        phone_alt TEXT,
        email TEXT,
        address TEXT,
        address_ne TEXT,
        city TEXT,
        category TEXT,
        website TEXT,
        is_emergency INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $done = true;
}

function getContacts(?string $search = null, ?string $category = null, ?string $city = null): array {
    ensureContactDirectoryTable();
    $db = db();
    if (!$db) return [];
    
    $sql = 'SELECT * FROM contact_directory WHERE 1=1';
    $params = [];
    
    if ($search) {
        $sql .= ' AND (name LIKE ? OR name_ne LIKE ? OR phone LIKE ?)';
        $term = "%$search%";
        $params = array_merge($params, [$term, $term, $term]);
    }
    
    if ($category && $category !== 'All') {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }
    
    if ($city && $city !== 'All') {
        $sql .= ' AND city = ?';
        $params[] = $city;
    }
    
    $sql .= ' ORDER BY name ASC';
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll();
    
    // If no contacts in database, return empty array (admin can add via admin panel)
    return $contacts;
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
