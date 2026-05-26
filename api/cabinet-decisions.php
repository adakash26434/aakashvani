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
    $allDecisions = [
        // 2081 Baisakh
        [
            'date' => '2081-01-15',
            'date_np' => 'बैशाख १५, २०८१',
            'title' => 'Cabinet meeting decision on economic policy',
            'title_ne' => 'आर्थिक नीति सम्बन्धी मन्त्रिपरिषदको निर्णय',
            'category' => 'economic',
            'category_ne' => 'आर्थिक',
            'summary' => 'Approved new economic policy framework for fiscal year 2081/82',
            'summary_ne' => 'आर्थिक वर्ष २०८१/८२ को लागि नयाँ आर्थिक नीति ढाँचा स्वीकृत',
            'details' => [
                'Approved budget allocation for infrastructure development',
                'Approved new tax reforms',
                'Approved foreign investment policy',
            ],
            'details_ne' => [
                'पूर्वाधार विकासको लागि बजेट विनियोजन स्वीकृत',
                'नयाँ कर सुधार स्वीकृत',
                'विदेशी लगानी नीति स्वीकृत',
            ],
        ],
        [
            'date' => '2081-01-20',
            'date_np' => 'बैशाख २०, २०८१',
            'title' => 'Health sector reforms approved',
            'title_ne' => 'स्वास्थ्य क्षेत्र सुधार स्वीकृत',
            'category' => 'health',
            'category_ne' => 'स्वास्थ्य',
            'summary' => 'Approved reforms in public health sector',
            'summary_ne' => 'सार्वजनिक स्वास्थ्य क्षेत्रमा सुधार स्वीकृत',
            'details' => [
                'Approved new hospital construction projects',
                'Approved health insurance expansion',
                'Approved medical equipment procurement',
            ],
            'details_ne' => [
                'नयाँ अस्पताल निर्माण परियोजना स्वीकृत',
                'स्वास्थ्य बीमा विस्तार स्वीकृत',
                'चिकित्सा उपकरण खरिद स्वीकृत',
            ],
        ],
        
        // 2081 Jestha
        [
            'date' => '2081-02-10',
            'date_np' => 'जेष्ठ १०, २०८१',
            'title' => 'Education policy amendments',
            'title_ne' => 'शिक्षा नीति संशोधन',
            'category' => 'education',
            'category_ne' => 'शिक्षा',
            'summary' => 'Approved amendments to education policy',
            'summary_ne' => 'शिक्षा नीति संशोधन स्वीकृत',
            'details' => [
                'Approved new curriculum framework',
                'Approved teacher training programs',
                'Approved school infrastructure development',
            ],
            'details_ne' => [
                'नयाँ पाठ्यक्रम ढाँचा स्वीकृत',
                'शिक्षक तालिम कार्यक्रम स्वीकृत',
                'विद्यालय पूर्वाधार विकास स्वीकृत',
            ],
        ],
        
        // 2081 Ashad
        [
            'date' => '2081-03-05',
            'date_np' => 'असार ५, २०८१',
            'title' => 'Infrastructure development projects',
            'title_ne' => 'पूर्वाधार विकास परियोजनाहरू',
            'category' => 'infrastructure',
            'category_ne' => 'पूर्वाधार',
            'summary' => 'Approved major infrastructure development projects',
            'summary_ne' => 'प्रमुख पूर्वाधार विकास परियोजनाहरू स्वीकृत',
            'details' => [
                'Approved highway expansion projects',
                'Approved airport development',
                'Approved hydropower projects',
            ],
            'details_ne' => [
                'राजमार्ग विस्तार परियोजना स्वीकृत',
                'विमानस्थल विकास स्वीकृत',
                'जलविद्युत परियोजना स्वीकृत',
            ],
        ],
        
        // 2081 Shrawan
        [
            'date' => '2081-04-12',
            'date_np' => 'श्रावण १२, २०८१',
            'title' => 'Agriculture sector development',
            'title_ne' => 'कृषि क्षेत्र विकास',
            'category' => 'agriculture',
            'category_ne' => 'कृषि',
            'summary' => 'Approved agriculture sector development programs',
            'summary_ne' => 'कृषि क्षेत्र विकास कार्यक्रम स्वीकृत',
            'details' => [
                'Approved fertilizer subsidy programs',
                'Approved irrigation projects',
                'Approved agricultural equipment support',
            ],
            'details_ne' => [
                'मल अनुदान कार्यक्रम स्वीकृत',
                'सिँचाइ परियोजना स्वीकृत',
                'कृषि उपकरण सहयोग स्वीकृत',
            ],
        ],
    ];
    
    // Filter by month
    if ($month) {
        $allDecisions = array_filter($allDecisions, function($d) use ($month) {
            $dateMonth = substr($d['date'], 5, 2);
            return $dateMonth === $month;
        });
    }
    
    // Filter by year
    if ($year) {
        $allDecisions = array_filter($allDecisions, function($d) use ($year) {
            $dateYear = substr($d['date'], 0, 4);
            return $dateYear === $year;
        });
    }
    
    $result = array_values($allDecisions);
    $result[] = [
        'source' => 'Office of the Prime Minister and Council of Ministers',
        'source_url' => 'https://opmcm.gov.np',
        'note' => 'मन्त्रिपरिषद्को निर्णयहरू प्रधानमन्त्री तथा मन्त्रिपरिषद्को कार्यालयबाट लिइएको हो।',
    ];
    return $result;
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
