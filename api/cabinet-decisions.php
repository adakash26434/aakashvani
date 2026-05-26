<?php
/**
 * Government Cabinet Decisions API
 * Nepal Government Cabinet meeting decisions
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;

try {
    $decisions = getCabinetDecisions($month, $year);
    
    echo json_encode([
        'ok' => true,
        'decisions' => $decisions,
        'count' => count($decisions),
        'months' => getAvailableMonths(),
        'years' => getAvailableYears(),
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function getCabinetDecisions(?string $month = null, ?string $year = null): array {
    ensureCabinetDecisionsTable();
    $db = db();
    
    $sql = 'SELECT * FROM cabinet_decisions WHERE 1=1';
    $params = [];
    
    if ($month) {
        $sql .= ' AND SUBSTR(date, 6, 2) = ?';
        $params[] = $month;
    }
    
    if ($year) {
        $sql .= ' AND SUBSTR(date, 1, 4) = ?';
        $params[] = $year;
    }
    
    $sql .= ' ORDER BY date DESC';
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $decisions = $stmt->fetchAll();
    
    // Parse JSON fields
    foreach ($decisions as &$d) {
        $d['details'] = json_decode($d['details'] ?? '[]', true) ?: [];
        $d['details_ne'] = json_decode($d['details_ne'] ?? '[]', true) ?: [];
    }
    
    return $decisions;
}

function getAvailableMonths(): array {
    return [
        ['value' => '01', 'label' => 'Baisakh (बैशाख)'],
        ['value' => '02', 'label' => 'Jestha (जेष्ठ)'],
        ['value' => '03', 'label' => 'Ashad (असार)'],
        ['value' => '04', 'label' => 'Shrawan (श्रावण)'],
        ['value' => '05', 'label' => 'Bhadra (भाद्र)'],
        ['value' => '06', 'label' => 'Ashwin (असोज)'],
        ['value' => '07', 'label' => 'Kartik (कार्तिक)'],
        ['value' => '08', 'label' => 'Mangsir (मंसिर)'],
        ['value' => '09', 'label' => 'Poush (पौष)'],
        ['value' => '10', 'label' => 'Magh (माघ)'],
        ['value' => '11', 'label' => 'Falgun (फागुन)'],
        ['value' => '12', 'label' => 'Chaitra (चैत्र)'],
    ];
}

function getAvailableYears(): array {
    return [
        ['value' => '2081', 'label' => '2081 (2024-25)'],
        ['value' => '2080', 'label' => '2080 (2023-24)'],
        ['value' => '2079', 'label' => '2079 (2022-23)'],
    ];
}
