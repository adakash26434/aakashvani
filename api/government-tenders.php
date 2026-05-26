<?php
/**
 * Government Tender API
 * Nepal Government Tender Notices
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

$category = $_GET['category'] ?? null;
$ministry = $_GET['ministry'] ?? null;
$days = $_GET['days'] ?? 30;

try {
    $tenders = getGovernmentTenders($category, $ministry, $days);
    
    echo json_encode([
        'ok' => true,
        'tenders' => $tenders,
        'count' => count($tenders),
        'categories' => getTenderCategories(),
        'ministries' => getMinistries(),
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function getGovernmentTenders(?string $category = null, ?string $ministry = null, int $days = 30): array {
    ensureGovernmentTendersTable();
    $db = db();
    
    $sql = 'SELECT * FROM government_tenders WHERE 1=1';
    $params = [];
    
    if ($category) {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }
    
    if ($ministry) {
        $sql .= ' AND ministry = ?';
        $params[] = $ministry;
    }
    
    if ($days > 0) {
        $sql .= ' AND deadline >= DATE("now", "-' . (int)$days . ' days")';
    }
    
    $sql .= ' ORDER BY deadline ASC';
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tenders = $stmt->fetchAll();
    
    return $tenders;
}

function getTenderCategories(): array {
    return [
        ['name' => 'Construction', 'name_ne' => 'निर्माण'],
        ['name' => 'Supply', 'name_ne' => 'आपूर्ति'],
        ['name' => 'Services', 'name_ne' => 'सेवा'],
        ['name' => 'Consultancy', 'name_ne' => 'परामर्श'],
        ['name' => 'Works', 'name_ne' => 'काम'],
    ];
}

function getMinistries(): array {
    return [
        ['name' => 'Ministry of Physical Infrastructure', 'name_ne' => 'भौतिक पूर्वाधार मन्त्रालय'],
        ['name' => 'Ministry of Health', 'name_ne' => 'स्वास्थ्य मन्त्रालय'],
        ['name' => 'Ministry of Communication', 'name_ne' => 'सञ्चार मन्त्रालय'],
        ['name' => 'Ministry of Education', 'name_ne' => 'शिक्षा मन्त्रालय'],
        ['name' => 'Ministry of Energy', 'name_ne' => 'ऊर्जा मन्त्रालय'],
        ['name' => 'Ministry of Finance', 'name_ne' => 'वित्त मन्त्रालय'],
        ['name' => 'Ministry of Home Affairs', 'name_ne' => 'गृह मन्त्रालय'],
    ];
}
