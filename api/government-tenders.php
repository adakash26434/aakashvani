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
    
    // Try to fetch fresh data from PPMO portal
    $freshTenders = fetchPPMOTenders();
    if (!empty($freshTenders)) {
        syncTendersToDB($freshTenders);
    }
    
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
    
    $sql .= ' ORDER BY deadline ASC LIMIT 50';
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tenders = $stmt->fetchAll();
    
    return $tenders ?: [];
}

function fetchPPMOTenders(): array {
    $url = 'https://bolpatra.gov.np/egp/searchOpportunity';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ne-NP,ne;q=0.9,en-US;q=0.8,en;q=0.7',
    ]);
    
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$html) {
        return [];
    }
    
    // Parse HTML to extract tender data
    $tenders = [];
    
    // Look for tender rows in the table
    if (preg_match_all('/<tr[^>]*class="[^"]*row[^"]*"[^>]*>.*?<\/tr>/is', $html, $rows)) {
        foreach ($rows[0] as $row) {
            // Extract tender details from each row
            if (preg_match('/<td[^>]*>(.*?)<\/td>/is', $row, $cells)) {
                $tender = parseTenderRow($cells[1]);
                if ($tender) {
                    $tenders[] = $tender;
                }
            }
        }
    }
    
    return $tenders;
}

function parseTenderRow(string $html): ?array {
    // Extract common tender fields
    $title = '';
    $organization = '';
    $deadline = '';
    $category = 'General';
    $ministry = 'Ministry';
    $link = '';
    
    // Try to extract title
    if (preg_match('/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is', $html, $m)) {
        $link = $m[1];
        $title = strip_tags($m[2]);
    }
    
    // Try to extract organization/ministry
    if (preg_match('/(?:Organization|Ministry|Agency)[^:]*:\s*([^<]+)/is', $html, $m)) {
        $ministry = trim($m[1]);
    }
    
    // Try to extract deadline
    if (preg_match('/(?:Deadline|Closing Date)[^:]*:\s*([^<]+)/is', $html, $m)) {
        $deadline = trim($m[1]);
    }
    
    // Try to extract category
    if (preg_match('/(?:Category|Type)[^:]*:\s*([^<]+)/is', $html, $m)) {
        $category = trim($m[1]);
    }
    
    if (empty($title)) {
        return null;
    }
    
    return [
        'title' => $title,
        'title_ne' => $title,
        'organization' => $organization ?: $ministry,
        'ministry' => $ministry,
        'category' => $category,
        'deadline' => $deadline ?: date('Y-m-d', strtotime('+30 days')),
        'status' => 'Open',
        'link' => $link ?: 'https://bolpatra.gov.np/egp/searchOpportunity',
        'published_date' => date('Y-m-d'),
    ];
}

function syncTendersToDB(array $tenders): void {
    $db = db();
    
    foreach ($tenders as $tender) {
        // Check if tender already exists
        $stmt = $db->prepare('SELECT id FROM government_tenders WHERE title = ? AND deadline = ?');
        $stmt->execute([$tender['title'], $tender['deadline']]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            // Insert new tender
            $stmt = $db->prepare('INSERT INTO government_tenders (title, title_ne, organization, ministry, category, deadline, status, link, published_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $tender['title'],
                $tender['title_ne'],
                $tender['organization'],
                $tender['ministry'],
                $tender['category'],
                $tender['deadline'],
                $tender['status'],
                $tender['link'],
                $tender['published_date'],
            ]);
        }
    }
}

function ensureGovernmentTendersTable(): void {
    $db = db();
    $isMysql = defined('DB_DRIVER') && DB_DRIVER === 'mysql';
    $ai = $isMysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $charset = $isMysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';
    $db->exec("CREATE TABLE IF NOT EXISTS government_tenders (
        id $ai,
        title TEXT NOT NULL,
        title_ne TEXT,
        organization TEXT,
        ministry TEXT,
        category TEXT,
        deadline TEXT,
        status TEXT DEFAULT 'Open',
        link TEXT,
        published_date TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )\$charset");
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
