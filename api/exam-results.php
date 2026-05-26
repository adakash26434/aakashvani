<?php
/**
 * Exam Results API
 * Nepal exam board results
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Security headers
sendSecurityHeaders();
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

$board = $_GET['board'] ?? null; // SEE, NEB, CTEVT, etc.
$year = $_GET['year'] ?? null;
$symbol = $_GET['symbol'] ?? null; // Symbol number

try {
    $results = getExamResults($board, $year, $symbol);
    
    echo json_encode([
        'ok' => true,
        'results' => $results,
        'count' => count($results),
        'boards' => getAvailableBoards(),
        'years' => getAvailableYears(),
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

function getExamResults(?string $board = null, ?string $year = null, ?string $symbol = null): array {
    $allResults = [
        // SEE Results
        ['board' => 'SEE', 'board_ne' => 'एसईई', 'year' => '2081', 'exam' => 'Secondary Education Examination', 'exam_ne' => 'माध्यमिक शिक्षा परीक्षा', 'result_date' => '2081-04-15', 'status' => 'Published', 'link' => 'https://see.ntc.net.np'],
        ['board' => 'SEE', 'board_ne' => 'एसईई', 'year' => '2080', 'exam' => 'Secondary Education Examination', 'exam_ne' => 'माध्यमिक शिक्षा परीक्षा', 'result_date' => '2080-04-20', 'status' => 'Published', 'link' => 'https://see.ntc.net.np'],
        
        // NEB Results
        ['board' => 'NEB', 'board_ne' => 'एनईबी', 'year' => '2081', 'exam' => 'Class 12 Examination', 'exam_ne' => 'कक्षा १२ परीक्षा', 'result_date' => '2081-09-15', 'status' => 'Published', 'link' => 'https://neb.gov.np'],
        ['board' => 'NEB', 'board_ne' => 'एनईबी', 'year' => '2081', 'exam' => 'Class 11 Examination', 'exam_ne' => 'कक्षा ११ परीक्षा', 'result_date' => '2081-08-20', 'status' => 'Published', 'link' => 'https://neb.gov.np'],
        ['board' => 'NEB', 'board_ne' => 'एनईबी', 'year' => '2080', 'exam' => 'Class 12 Examination', 'exam_ne' => 'कक्षा १२ परीक्षा', 'result_date' => '2080-09-10', 'status' => 'Published', 'link' => 'https://neb.gov.np'],
        
        // CTEVT Results
        ['board' => 'CTEVT', 'board_ne' => 'सीटीईभीटी', 'year' => '2081', 'exam' => 'Diploma Level Examination', 'exam_ne' => 'डिप्लोमा स्तर परीक्षा', 'result_date' => '2081-07-25', 'status' => 'Published', 'link' => 'https://ctevt.org.np'],
        ['board' => 'CTEVT', 'board_ne' => 'सीटीईभीटी', 'year' => '2080', 'exam' => 'Diploma Level Examination', 'exam_ne' => 'डिप्लोमा स्तर परीक्षा', 'result_date' => '2080-07-30', 'status' => 'Published', 'link' => 'https://ctevt.org.np'],
        
        // Lok Sewa Results
        ['board' => 'Lok Sewa', 'board_ne' => 'लोक सेवा', 'year' => '2081', 'exam' => 'Kharidar Examination', 'exam_ne' => 'खरिदार परीक्षा', 'result_date' => '2081-06-10', 'status' => 'Published', 'link' => 'https://psc.gov.np'],
        ['board' => 'Lok Sewa', 'board_ne' => 'लोक सेवा', 'year' => '2081', 'exam' => 'Nayab Subba Examination', 'exam_ne' => 'नायब सुब्बा परीक्षा', 'result_date' => '2081-05-20', 'status' => 'Published', 'link' => 'https://psc.gov.np'],
    ];
    
    // Filter by board
    if ($board) {
        $allResults = array_filter($allResults, function($r) use ($board) {
            return strtolower($r['board']) === strtolower($board) || strtolower($r['board_ne']) === strtolower($board);
        });
    }
    
    // Filter by year
    if ($year) {
        $allResults = array_filter($allResults, function($r) use ($year) {
            return $r['year'] === $year;
        });
    }
    
    return array_values($allResults);
}

function getAvailableBoards(): array {
    return [
        ['name' => 'SEE', 'name_ne' => 'एसईई', 'full_name' => 'Secondary Education Examination'],
        ['name' => 'NEB', 'name_ne' => 'एनईबी', 'full_name' => 'National Examination Board'],
        ['name' => 'CTEVT', 'name_ne' => 'सीटीईभीटी', 'full_name' => 'Council for Technical Education and Vocational Training'],
        ['name' => 'Lok Sewa', 'name_ne' => 'लोक सेवा', 'full_name' => 'Public Service Commission'],
    ];
}

function getAvailableYears(): array {
    return [
        ['value' => '2081', 'label' => '2081 (2024-25)'],
        ['value' => '2080', 'label' => '2080 (2023-24)'],
        ['value' => '2079', 'label' => '2079 (2022-23)'],
        ['value' => '2078', 'label' => '2078 (2021-22)'],
    ];
}
