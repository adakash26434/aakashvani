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
    $allTenders = [
        [
            'id' => 1,
            'title' => 'Construction of Road Section',
            'title_ne' => 'सडक खण्ड निर्माण',
            'ministry' => 'Ministry of Physical Infrastructure',
            'ministry_ne' => 'भौतिक पूर्वाधार मन्त्रालय',
            'department' => 'Department of Roads',
            'department_ne' => 'सडक विभाग',
            'category' => 'Construction',
            'category_ne' => 'निर्माण',
            'location' => 'Kathmandu',
            'location_ne' => 'काठमाडौं',
            'deadline' => '2081-02-15',
            'deadline_ne' => 'जेष्ठ १५, २०८१',
            'estimated_cost' => 'NPR 50,00,00,000',
            'status' => 'Open',
            'status_ne' => 'खुला',
            'published_date' => '2081-01-20',
            'published_date_ne' => 'बैशाख २०, २०८१',
            'description' => 'Construction of 25 km road section from Kathmandu to Dhading with complete infrastructure.',
            'description_ne' => 'काठमाडौं देखि धादिङ सम्म २५ किमी सडक खण्ड निर्माण पूर्ण पूर्वाधार सहित।',
            'documents' => ['Tender Notice', 'Technical Specifications', 'Bid Form'],
            'link' => 'https://dor.gov.np/tenders',
        ],
        [
            'id' => 2,
            'title' => 'Supply of Medical Equipment',
            'title_ne' => 'चिकित्सा उपकरण आपूर्ति',
            'ministry' => 'Ministry of Health',
            'ministry_ne' => 'स्वास्थ्य मन्त्रालय',
            'department' => 'Department of Health Services',
            'department_ne' => 'स्वास्थ्य सेवा विभाग',
            'category' => 'Supply',
            'category_ne' => 'आपूर्ति',
            'location' => 'All Nepal',
            'location_ne' => 'सम्पूर्ण नेपाल',
            'deadline' => '2081-02-10',
            'deadline_ne' => 'जेष्ठ १०, २०८१',
            'estimated_cost' => 'NPR 15,00,00,000',
            'status' => 'Open',
            'status_ne' => 'खुला',
            'published_date' => '2081-01-18',
            'published_date_ne' => 'बैशाख १८, २०८१',
            'description' => 'Supply of medical equipment for government hospitals across Nepal.',
            'description_ne' => 'नेपाल भरका सरकारी अस्पतालहरूका लागि चिकित्सा उपकरण आपूर्ति।',
            'documents' => ['Tender Notice', 'Equipment List', 'Technical Specifications'],
            'link' => 'https://dohs.gov.np/tenders',
        ],
        [
            'id' => 3,
            'title' => 'IT System Development',
            'title_ne' => 'आईटी प्रणाली विकास',
            'ministry' => 'Ministry of Communication',
            'ministry_ne' => 'सञ्चार मन्त्रालय',
            'department' => 'Department of Information Technology',
            'department_ne' => 'सूचना प्रविधि विभाग',
            'category' => 'Services',
            'category_ne' => 'सेवा',
            'location' => 'Kathmandu',
            'location_ne' => 'काठमाडौं',
            'deadline' => '2081-02-20',
            'deadline_ne' => 'जेष्ठ २०, २०८१',
            'estimated_cost' => 'NPR 8,00,00,000',
            'status' => 'Open',
            'status_ne' => 'खुला',
            'published_date' => '2081-01-22',
            'published_date_ne' => 'बैशाख २२, २०८१',
            'description' => 'Development of integrated IT system for government data management.',
            'description_ne' => 'सरकारी डाटा व्यवस्थापनका लागि एकीकृत आईटी प्रणाली विकास।',
            'documents' => ['Tender Notice', 'Requirements Document', 'Technical Proposal'],
            'link' => 'https://doit.gov.np/tenders',
        ],
        [
            'id' => 4,
            'title' => 'School Building Construction',
            'title_ne' => 'विद्यालय भवन निर्माण',
            'ministry' => 'Ministry of Education',
            'ministry_ne' => 'शिक्षा मन्त्रालय',
            'department' => 'Department of Education',
            'department_ne' => 'शिक्षा विभाग',
            'category' => 'Construction',
            'category_ne' => 'निर्माण',
            'location' => 'Province 3',
            'location_ne' => 'प्रदेश ३',
            'deadline' => '2081-02-25',
            'deadline_ne' => 'जेष्ठ २५, २०८१',
            'estimated_cost' => 'NPR 3,50,00,000',
            'status' => 'Open',
            'status_ne' => 'खुला',
            'published_date' => '2081-01-25',
            'published_date_ne' => 'बैशाख २५, २०८१',
            'description' => 'Construction of 5 school buildings in Province 3.',
            'description_ne' => 'प्रदेश ३ मा ५ विद्यालय भवन निर्माण।',
            'documents' => ['Tender Notice', 'Building Plans', 'Technical Specifications'],
            'link' => 'https://doe.gov.np/tenders',
        ],
        [
            'id' => 5,
            'title' => 'Hydropower Project Survey',
            'title_ne' => 'जलविद्युत परियोजना सर्वेक्षण',
            'ministry' => 'Ministry of Energy',
            'ministry_ne' => 'ऊर्जा मन्त्रालय',
            'department' => 'Department of Electricity',
            'department_ne' => 'विद्युत विभाग',
            'category' => 'Consultancy',
            'category_ne' => 'परामर्श',
            'location' => 'Sindhupalchok',
            'location_ne' => 'सिन्धुपाल्चोक',
            'deadline' => '2081-03-05',
            'deadline_ne' => 'असार ५, २०८१',
            'estimated_cost' => 'NPR 2,00,00,000',
            'status' => 'Open',
            'status_ne' => 'खुला',
            'published_date' => '2081-01-28',
            'published_date_ne' => 'बैशाख २८, २०८१',
            'description' => 'Feasibility study and survey for 50 MW hydropower project.',
            'description_ne' => '५० मेगावाट जलविद्युत परियोजनाको सम्भाव्यता अध्ययन र सर्वेक्षण।',
            'documents' => ['Tender Notice', 'Terms of Reference', 'Survey Guidelines'],
            'link' => 'https://doe.gov.np/tenders',
        ],
    ];
    
    // Filter by category
    if ($category) {
        $allTenders = array_filter($allTenders, function($t) use ($category) {
            return strtolower($t['category']) === strtolower($category) || strtolower($t['category_ne']) === strtolower($category);
        });
    }
    
    // Filter by ministry
    if ($ministry) {
        $allTenders = array_filter($allTenders, function($t) use ($ministry) {
            return strtolower($t['ministry']) === strtolower($ministry) || strtolower($t['ministry_ne']) === strtolower($ministry);
        });
    }
    
    return array_values($allTenders);
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
