<?php
/**
 * आकाशवाणी — Government Services API
 * Real government services, offices, and information
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=86400');

$category = $_GET['category'] ?? 'all';

$services = [
    'citizenship' => [
        ['id' => 'c1', 'name' => 'नागरिकता प्रमाणपत्र', 'en' => 'Citizenship Certificate', 'url' => 'https://moit.maanisthana.nepal', 'desc' => 'नागरिकता प्रमाणपत्र पाउनको लागि जिल्ला प्रशासन कार्यालयमा जानुहोस्', 'docs' => ['नागरिकताको प्रमाणपत्र', 'जन्मदर्ता', 'फोटो'], 'fee' => 'रु. १००', 'time' => '१-३ दिन'],
        ['id' => 'c2', 'name' => 'नागरिकता प्रतिलिपि', 'en' => 'Citizenship Copy', 'url' => 'https://moit.maanisthana.nepal', 'desc' => 'हराएको/नष्ट भएको नागरिकताको प्रतिलिपि', 'docs' => ['प्रथम नागरिकताको प्रमाणपत्र', 'ग्वास्थापत्र', 'प्रहरी प्रतिवेदन'], 'fee' => 'रु. ५०', 'time' => '१-७ दिन'],
        ['id' => 'c3', 'name' => 'नागरिकताको सक्कली प्रमाण', 'en' => 'Citizenship Verification', 'url' => 'https://moit.maanisthana.nepal', 'desc' => 'नागरिकताको सक्कली प्रमाण गर्ने', 'docs' => ['नागरिकताको प्रमाणपत्र'], 'fee' => 'रु. २५', 'time' => 'तत्काल'],
    ],
    'passport' => [
        ['id' => 'p1', 'name' => 'नयाँ राहदानी', 'en' => 'New Passport', 'url' => 'https://www.nepalpassport.gov.np', 'desc' => 'पहिलो पटक राहदानी बनाउने', 'docs' => ['नागरिकताको प्रमाणपत्र', 'जन्मदर्ता', 'फोटो'], 'fee' => 'रु. ५,००० (सामान्य)', 'time' => '१-२ हप्ता'],
        ['id' => 'p2', 'name' => 'राहदानी नवीकरण', 'en' => 'Passport Renewal', 'url' => 'https://www.nepalpassport.gov.np', 'desc' => 'म्याद सकिएको राहदानी नवीकरण', 'docs' => ['पुरानो राहदानी', 'नागरिकता'], 'fee' => 'रु. ३,०००', 'time' => '१ हप्ता'],
        ['id' => 'p3', 'name' => 'राहदानी खोजेमा', 'en' => 'Lost Passport', 'url' => 'https://www.nepalpassport.gov.np', 'desc' => 'खोएको/चोरी भएको राहदानीको प्रतिलिपि', 'docs' => ['प्रहरी प्रतिवेदन', 'नागरिकता'], 'fee' => 'रु. ७,५००', 'time' => '२-३ हप्ता'],
    ],
    'tax' => [
        ['id' => 't1', 'name' => 'आयकर दर्ता', 'en' => 'Income Tax Registration', 'url' => 'https://www.ird.gov.np', 'desc' => 'आयकर दर्ता र PAN प्रमाणपत्र', 'docs' => ['नागरिकता', 'लागत विवरण', 'ठेगाना प्रमाण'], 'fee' => 'निःशुल्क', 'time' => '१-३ दिन'],
        ['id' => 't2', 'name' => 'कर विवरण दाखिला', 'en' => 'Tax Return Filing', 'url' => 'https://www.ird.gov.np', 'desc' => 'वार्षिक कर विवरण दाखिला', 'docs' => ['आय विवरण', 'भुक्तानी रसिद'], 'fee' => 'निर्भर', 'time' => 'ऑनलाइन'],
        ['id' => 't3', 'name' => 'मूल्य अभिबृद्धि कर (VAT)', 'en' => 'VAT Registration', 'url' => 'https://www.ird.gov.np', 'desc' => 'VAT दर्ता र प्रमाणपत्र', 'docs' => ['नागरिकता', 'प्रोप्राइटर दर्ता', 'ठेगाना'], 'fee' => 'निःशुल्क', 'time' => '१-५ दिन'],
    ],
    'land' => [
        ['id' => 'l1', 'name' => 'जग्गा दर्ता', 'en' => 'Land Registration', 'url' => 'https://www.molrm.gov.np', 'desc' => 'जग्गा खरिद/विक्री दर्ता', 'docs' => ['कागजात', 'नागरिकता', 'लालपूर्जा'], 'fee' => 'दस्तुर लाग्ने', 'time' => '१५-३० दिन'],
        ['id' => 'l2', 'name' => 'जग्गाको नामसारी', 'en' => 'Land Name Transfer', 'url' => 'https://www.molrm.gov.np', 'desc' => 'जग्गाको नाम हस्तान्तरण', 'docs' => ['लालपूर्जा', 'नागरिकता', 'सक्कली कागजात'], 'fee' => 'दस्तुर लाग्ने', 'time' => '१५-४५ दिन'],
        ['id' => 'l3', 'name' => 'जग्गा चुस्ता', 'en' => 'Land Mapping', 'url' => 'https://www.dolrm.gov.np', 'desc' => 'जग्गाको सीमाना र क्षेत्रफल चुस्त', 'docs' => ['लालपूर्जा', 'नक्सा'], 'fee' => 'रु. ५००-२,०००', 'time' => '७-१५ दिन'],
    ],
    'education' => [
        ['id' => 'e1', 'name' => 'शैक्षिक प्रमाणपत्र', 'en' => 'Educational Certificate', 'url' => 'https://www.utece.gov.np', 'desc' => 'SLC/SEE/ब्याचलर/मास्टर प्रमाणपत्र', 'docs' => ['शैक्षिक कागजात', 'नागरिकता'], 'fee' => 'रु. १,०००-३,०००', 'time' => '१-२ हप्ता'],
        ['id' => 'e2', 'name' => 'त्रि.वि. दर्ता', 'en' => 'TU Registration', 'url' => 'https://www.tu.edu.np', 'desc' => 'त्रिभुवन विश्वविद्यालयमा दर्ता', 'docs' => ['शैक्षिक प्रमाणपत्र', 'नागरिकता'], 'fee' => 'निर्भर', 'time' => 'ऑनलाइन'],
        ['id' => 'e3', 'name' => 'छात्रवृत्ति', 'en' => 'Scholarship', 'url' => 'https://www.moe.gov.np', 'desc' => 'सरकारी छात्रवृत्तिको लागि आवेदन', 'docs' => ['शैक्षिक कागजात', 'आय प्रमाण'], 'fee' => 'निःशुल्क', 'time' => 'ऑनलाइन'],
    ],
    'local' => [
        ['id' => 'lg1', 'name' => 'नगरपालिका दर्ता', 'en' => 'Municipality Registration', 'url' => '', 'desc' => 'नगरपालिकामा घर दर्ता र नागरिक आवास', 'docs' => ['घरको कागजात', 'नागरिकता'], 'fee' => 'निःशुल्क-रु. ५००', 'time' => '१-७ दिन'],
        ['id' => 'lg2', 'name' => 'उपभोक्ता राशनिङ', 'en' => 'Consumer Registration', 'url' => '', 'desc' => 'नेपाली नागरिकको परिचय पत्र', 'docs' => ['नागरिकता', 'फोटो'], 'fee' => 'रु. १००', 'time' => '१ दिन'],
        ['id' => 'lg3', 'name' => 'घर निर्माण इजाजत', 'en' => 'Building permit', 'url' => '', 'desc' => 'घर बनाउनको लागि अनुमति', 'docs' => ['नक्सा', 'जग्गाको कागजात'], 'fee' => 'रु. १,०००-१०,०००', 'time' => '१५-३० दिन'],
    ],
];

if ($category === 'all') {
    echo json_encode(['ok' => true, 'services' => $services, 'categories' => array_keys($services), 'updated' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
} else if (isset($services[$category])) {
    echo json_encode(['ok' => true, 'category' => $category, 'services' => $services[$category], 'updated' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['ok' => false, 'error' => 'Invalid category'], JSON_UNESCAPED_UNICODE);
}