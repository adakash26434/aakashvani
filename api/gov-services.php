<?php
/**
 * आकाशवाणी — Government Services Data API v13
 * Comprehensive Nepal Government Services Information
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=86400');

$service = $_GET['service'] ?? null;
$category = $_GET['category'] ?? null;

// ═══════════════════════════════════════════════════════════════════════════════
// GOVERNMENT SERVICES DATABASE
// ═══════════════════════════════════════════════════════════════════════════════

$govServices = [
    'passport' => [
        'name_ne' => 'राहदानी / Passport',
        'name_en' => 'Passport',
        'icon'    => 'file-check',
        'color'   => 'blue',
        'office'  => 'Department of Passport',
        'office_ne' => 'राहदानी विभाग',
        'website' => 'https://nepalpassport.gov.np',
        'helpline'=> '01-4429556',
        'documents' => [
            ['ne' => 'नागरिकता प्रमाणपत्र (सक्कल)', 'en' => 'Original Citizenship Certificate'],
            ['ne' => 'पासपोर्ट साइजको फोटो ४ प्रति', 'en' => '4 Passport Size Photos'],
            ['ne' => 'जन्म दर्ता प्रमाणपत्र', 'en' => 'Birth Registration Certificate'],
            ['ne' => 'अनलाइन आवेदन फारम', 'en' => 'Online Application Form'],
            ['ne' => 'दस्तुर रु. २,५०० (सामान्य) / रु. ५,००० (द्रुत)', 'en' => 'Fee: NPR 2,500 (Regular) / NPR 5,000 (Express)'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'nepalpassport.gov.np मा अनलाइन फारम भर्नुस्', 'en' => 'Fill online form at nepalpassport.gov.np'],
            ['step' => 2, 'ne' => 'दस्तुर तिर्नुस् (eSewa, Khalti, Bank)', 'en' => 'Pay fee via eSewa, Khalti, or Bank'],
            ['step' => 3, 'ne' => 'Appointment लिनुस्', 'en' => 'Book appointment'],
            ['step' => 4, 'ne' => 'कागजात सहित कार्यालयमा जानुस्', 'en' => 'Visit office with documents'],
            ['step' => 5, 'ne' => 'Biometric दिनुस्', 'en' => 'Provide biometrics'],
            ['step' => 6, 'ne' => '१५-३० दिनमा राहदानी तयार', 'en' => 'Passport ready in 15-30 days'],
        ],
        'locations' => [
            ['name' => 'Kathmandu (Head Office)', 'address' => 'Tripureshwor', 'phone' => '01-4429556'],
            ['name' => 'Pokhara', 'address' => 'Chipledhunga', 'phone' => '061-526024'],
            ['name' => 'Biratnagar', 'address' => 'Traffic Chowk', 'phone' => '021-528262'],
            ['name' => 'Nepalgunj', 'address' => 'Surkhet Road', 'phone' => '081-520244'],
            ['name' => 'Dhangadhi', 'address' => 'Main Road', 'phone' => '091-521444'],
        ],
        'tracking_url' => 'https://nepalpassport.gov.np/track',
        'fee' => ['regular' => 2500, 'express' => 5000, 'currency' => 'NPR'],
        'processing_days' => ['regular' => 30, 'express' => 7],
    ],
    
    'citizenship' => [
        'name_ne' => 'नागरिकता / Citizenship',
        'name_en' => 'Citizenship Certificate',
        'icon'    => 'id-card',
        'color'   => 'emerald',
        'office'  => 'District Administration Office',
        'office_ne' => 'जिल्ला प्रशासन कार्यालय',
        'helpline'=> '01-4200150',
        'documents' => [
            ['ne' => 'जन्म दर्ता प्रमाणपत्र', 'en' => 'Birth Registration Certificate'],
            ['ne' => 'बाबु/आमाको नागरिकता', 'en' => 'Father/Mother\'s Citizenship'],
            ['ne' => 'पासपोर्ट साइजको फोटो ४ प्रति', 'en' => '4 Passport Size Photos'],
            ['ne' => 'वडा कार्यालयको सिफारिस', 'en' => 'Ward Office Recommendation'],
            ['ne' => 'शैक्षिक प्रमाणपत्र (उपलब्ध भए)', 'en' => 'Educational Certificate (if available)'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'स्थानीय वडा कार्यालयमा आवेदन', 'en' => 'Apply at local Ward Office'],
            ['step' => 2, 'ne' => 'वडाबाट सिफारिस लिनुस्', 'en' => 'Get recommendation from Ward'],
            ['step' => 3, 'ne' => 'जिल्ला प्रशासन कार्यालयमा जानुस्', 'en' => 'Visit District Administration Office'],
            ['step' => 4, 'ne' => 'कागजात पेश गर्नुस्', 'en' => 'Submit documents'],
            ['step' => 5, 'ne' => 'Biometric र Photo', 'en' => 'Biometric and Photo'],
            ['step' => 6, 'ne' => 'सोही दिन वा भोलिपल्ट तयार', 'en' => 'Ready same day or next day'],
        ],
        'fee' => ['regular' => 0, 'copy' => 25, 'currency' => 'NPR'],
        'processing_days' => ['regular' => 1, 'copy' => 1],
    ],
    
    'driving_license' => [
        'name_ne' => 'सवारी चालक अनुमतिपत्र',
        'name_en' => 'Driving License',
        'icon'    => 'car',
        'color'   => 'orange',
        'office'  => 'Department of Transport Management',
        'office_ne' => 'यातायात व्यवस्था विभाग',
        'website' => 'https://dotm.gov.np',
        'helpline'=> '01-4474920',
        'documents' => [
            ['ne' => 'नागरिकता प्रमाणपत्र', 'en' => 'Citizenship Certificate'],
            ['ne' => 'मेडिकल रिपोर्ट', 'en' => 'Medical Report'],
            ['ne' => 'पासपोर्ट साइजको फोटो', 'en' => 'Passport Size Photos'],
            ['ne' => 'अनलाइन फारम भरेको', 'en' => 'Online Form Filled'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'dotm.gov.np मा अनलाइन आवेदन', 'en' => 'Apply online at dotm.gov.np'],
            ['step' => 2, 'ne' => 'मेडिकल चेकअप गर्नुस्', 'en' => 'Complete medical checkup'],
            ['step' => 3, 'ne' => 'दस्तुर तिर्नुस्', 'en' => 'Pay the fee'],
            ['step' => 4, 'ne' => 'लिखित परीक्षा दिनुस्', 'en' => 'Appear for written exam'],
            ['step' => 5, 'ne' => 'Trial (Practical) परीक्षा दिनुस्', 'en' => 'Appear for trial/practical exam'],
            ['step' => 6, 'ne' => 'पास भएपछि License तयार', 'en' => 'License ready after passing'],
        ],
        'categories' => [
            ['code' => 'A', 'ne' => 'मोटरसाइकल', 'en' => 'Motorcycle', 'fee' => 1500],
            ['code' => 'B', 'ne' => 'कार/जीप', 'en' => 'Car/Jeep', 'fee' => 2500],
            ['code' => 'C', 'ne' => 'टेम्पो', 'en' => 'Tempo', 'fee' => 2000],
            ['code' => 'D', 'ne' => 'मिनि ट्रक/बस', 'en' => 'Mini Truck/Bus', 'fee' => 3000],
            ['code' => 'E', 'ne' => 'ट्रक/बस', 'en' => 'Truck/Bus', 'fee' => 4000],
        ],
        'tracking_url' => 'https://dotm.gov.np/license-status',
    ],
    
    'pan_vat' => [
        'name_ne' => 'PAN / VAT दर्ता',
        'name_en' => 'PAN / VAT Registration',
        'icon'    => 'receipt',
        'color'   => 'purple',
        'office'  => 'Inland Revenue Department',
        'office_ne' => 'आन्तरिक राजस्व विभाग',
        'website' => 'https://ird.gov.np',
        'helpline'=> '01-4415802',
        'documents' => [
            ['ne' => 'नागरिकता प्रमाणपत्र', 'en' => 'Citizenship Certificate'],
            ['ne' => 'पासपोर्ट साइजको फोटो', 'en' => 'Passport Size Photos'],
            ['ne' => 'व्यापार दर्ता प्रमाणपत्र', 'en' => 'Business Registration Certificate'],
            ['ne' => 'कार्यालयको भाडा सम्झौता/स्वामित्व', 'en' => 'Office Rent Agreement/Ownership'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'ird.gov.np मा अनलाइन आवेदन', 'en' => 'Apply online at ird.gov.np'],
            ['step' => 2, 'ne' => 'कागजात अपलोड गर्नुस्', 'en' => 'Upload documents'],
            ['step' => 3, 'ne' => 'कर कार्यालयमा जानुस्', 'en' => 'Visit Tax Office'],
            ['step' => 4, 'ne' => 'Verification', 'en' => 'Verification'],
            ['step' => 5, 'ne' => 'PAN नम्बर प्राप्त', 'en' => 'Receive PAN Number'],
        ],
        'fee' => ['pan' => 0, 'vat' => 0, 'currency' => 'NPR'],
    ],
    
    'birth_registration' => [
        'name_ne' => 'जन्म दर्ता',
        'name_en' => 'Birth Registration',
        'icon'    => 'baby',
        'color'   => 'pink',
        'office'  => 'Ward Office / Municipality',
        'office_ne' => 'वडा कार्यालय / नगरपालिका',
        'documents' => [
            ['ne' => 'अस्पताल जन्म प्रमाणपत्र', 'en' => 'Hospital Birth Certificate'],
            ['ne' => 'बाबु/आमाको नागरिकता', 'en' => 'Father/Mother\'s Citizenship'],
            ['ne' => 'बाबु आमाको विवाह दर्ता', 'en' => 'Marriage Certificate'],
        ],
        'fee' => ['within_35_days' => 0, 'late' => 50, 'currency' => 'NPR'],
        'deadline' => '35 days from birth',
    ],
    
    'loksewa' => [
        'name_ne' => 'लोक सेवा आयोग',
        'name_en' => 'Public Service Commission',
        'icon'    => 'graduation-cap',
        'color'   => 'indigo',
        'office'  => 'Public Service Commission',
        'office_ne' => 'लोक सेवा आयोग',
        'website' => 'https://psc.gov.np',
        'helpline'=> '01-4771489',
        'exam_types' => [
            ['ne' => 'राजपत्राङ्कित प्रथम श्रेणी', 'en' => 'Gazetted First Class'],
            ['ne' => 'राजपत्राङ्कित द्वितीय श्रेणी', 'en' => 'Gazetted Second Class'],
            ['ne' => 'राजपत्राङ्कित तृतीय श्रेणी', 'en' => 'Gazetted Third Class'],
            ['ne' => 'राजपत्र अनङ्कित प्रथम श्रेणी', 'en' => 'Non-Gazetted First Class'],
            ['ne' => 'राजपत्र अनङ्कित द्वितीय श्रेणी', 'en' => 'Non-Gazetted Second Class'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'psc.gov.np मा विज्ञापन हेर्नुस्', 'en' => 'Check vacancy at psc.gov.np'],
            ['step' => 2, 'ne' => 'अनलाइन आवेदन भर्नुस्', 'en' => 'Fill online application'],
            ['step' => 3, 'ne' => 'दस्तुर तिर्नुस्', 'en' => 'Pay application fee'],
            ['step' => 4, 'ne' => 'Admit card डाउनलोड', 'en' => 'Download admit card'],
            ['step' => 5, 'ne' => 'परीक्षा दिनुस्', 'en' => 'Appear for exam'],
        ],
    ],
    
    'electricity' => [
        'name_ne' => 'बिजुली सेवा',
        'name_en' => 'Electricity Service',
        'icon'    => 'zap',
        'color'   => 'yellow',
        'office'  => 'Nepal Electricity Authority',
        'office_ne' => 'नेपाल विद्युत प्राधिकरण',
        'website' => 'https://nea.org.np',
        'helpline'=> '1150',
        'bill_payment' => [
            ['method' => 'eSewa', 'url' => 'https://esewa.com.np'],
            ['method' => 'Khalti', 'url' => 'https://khalti.com'],
            ['method' => 'ConnectIPS', 'url' => 'https://connectips.com'],
            ['method' => 'NEA Counter', 'url' => 'https://nea.org.np'],
        ],
    ],
    
    'water' => [
        'name_ne' => 'खानेपानी सेवा',
        'name_en' => 'Water Supply',
        'icon'    => 'droplet',
        'color'   => 'cyan',
        'office'  => 'Kathmandu Upatyaka Khanepani Limited',
        'office_ne' => 'काठमाडौं उपत्यका खानेपानी लिमिटेड',
        'website' => 'https://kathmanduwater.org',
        'helpline'=> '1144',
    ],
    'property_tax' => [
        'name_ne' => 'सम्पत्ति कर',
        'name_en' => 'Property Tax',
        'icon'    => 'home',
        'color'   => 'rose',
        'office'  => 'Local Municipality / Ward Office',
        'office_ne' => 'स्थानीय तह / वडा कार्यालय',
        'website' => 'https://nepal.gov.np',
        'documents' => [
            ['ne' => 'जग्गाधनी प्रमाणपत्र / लालपुर्जा', 'en' => 'Land ownership certificate'],
            ['ne' => 'नागरिकता प्रमाणपत्र', 'en' => 'Citizenship certificate'],
            ['ne' => 'घर/जग्गा विवरण र अघिल्लो कर रसिद', 'en' => 'Property details and previous receipt'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'सम्बन्धित वडा/नगरपालिका कार्यालयमा विवरण पेश गर्नुहोस्', 'en' => 'Submit details to local ward/municipality'],
            ['step' => 2, 'ne' => 'कर निर्धारण भएपछि राजस्व तिर्नुहोस्', 'en' => 'Pay assessed revenue'],
            ['step' => 3, 'ne' => 'कर तिरेको रसिद सुरक्षित राख्नुहोस्', 'en' => 'Keep the payment receipt'],
        ],
    ],
    'education' => [
        'name_ne' => 'शिक्षा सेवा',
        'name_en' => 'Education Services',
        'icon'    => 'graduation-cap',
        'color'   => 'violet',
        'office'  => 'Ministry of Education / Local Education Unit',
        'office_ne' => 'शिक्षा मन्त्रालय / स्थानीय शिक्षा शाखा',
        'website' => 'https://moest.gov.np',
        'documents' => [
            ['ne' => 'जन्मदर्ता/नागरिकता', 'en' => 'Birth certificate/citizenship'],
            ['ne' => 'शैक्षिक प्रमाणपत्र', 'en' => 'Academic certificates'],
            ['ne' => 'फोटो र आवेदन फारम', 'en' => 'Photo and application form'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'सम्बन्धित विद्यालय/क्याम्पस वा शिक्षा शाखामा सम्पर्क गर्नुहोस्', 'en' => 'Contact school/campus or education unit'],
            ['step' => 2, 'ne' => 'आवश्यक कागजात सहित आवेदन दिनुहोस्', 'en' => 'Apply with required documents'],
        ],
    ],
    'health_insurance' => [
        'name_ne' => 'स्वास्थ्य बीमा',
        'name_en' => 'Health Insurance',
        'icon'    => 'heart-pulse',
        'color'   => 'red',
        'office'  => 'Health Insurance Board',
        'office_ne' => 'स्वास्थ्य बीमा बोर्ड',
        'website' => 'https://hib.gov.np',
        'helpline'=> '16600111224',
        'documents' => [
            ['ne' => 'परिवार सदस्यको नागरिकता/जन्मदर्ता', 'en' => 'Citizenship/birth certificates of family members'],
            ['ne' => 'फोटो र सम्पर्क नम्बर', 'en' => 'Photo and contact number'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'बीमा दर्ता सहयोगी वा स्थानीय तहमा सम्पर्क गर्नुहोस्', 'en' => 'Contact enrollment assistant/local level'],
            ['step' => 2, 'ne' => 'परिवार विवरण र शुल्क बुझाउनुहोस्', 'en' => 'Submit family details and fee'],
            ['step' => 3, 'ne' => 'बीमा कार्ड/सेवा उपयोग गर्नुहोस्', 'en' => 'Use insurance card/services'],
        ],
    ],
    'national_id' => [
        'name_ne' => 'राष्ट्रिय परिचयपत्र',
        'name_en' => 'National ID',
        'icon'    => 'id-card',
        'color'   => 'teal',
        'office'  => 'Department of National ID and Civil Registration',
        'office_ne' => 'राष्ट्रिय परिचयपत्र तथा पञ्जीकरण विभाग',
        'website' => 'https://donidcr.gov.np',
        'documents' => [
            ['ne' => 'नागरिकता प्रमाणपत्र', 'en' => 'Citizenship certificate'],
            ['ne' => 'बसाइँसराइ/विवाह दर्ता आवश्यक भए', 'en' => 'Migration/marriage certificate if required'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'अनलाइन pre-enrollment वा जिल्ला प्रशासनमा आवेदन', 'en' => 'Online pre-enrollment or apply at DAO'],
            ['step' => 2, 'ne' => 'Biometric र फोटो दिनुहोस्', 'en' => 'Provide biometrics and photo'],
            ['step' => 3, 'ne' => 'NID नम्बर/कार्ड स्थिति चेक गर्नुहोस्', 'en' => 'Check NID/card status'],
        ],
    ],
    'vehicle' => [
        'name_ne' => 'सवारी दर्ता',
        'name_en' => 'Vehicle Registration',
        'icon'    => 'car-front',
        'color'   => 'orange',
        'office'  => 'Department of Transport Management',
        'office_ne' => 'यातायात व्यवस्था विभाग',
        'website' => 'https://dotm.gov.np',
        'documents' => [
            ['ne' => 'सवारी खरिद बिल/भन्सार कागजात', 'en' => 'Purchase/customs documents'],
            ['ne' => 'नागरिकता/संस्था दर्ता', 'en' => 'Citizenship/company registration'],
            ['ne' => 'बीमा र कर कागजात', 'en' => 'Insurance and tax documents'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'यातायात कार्यालयमा कागजात पेश गर्नुहोस्', 'en' => 'Submit documents at transport office'],
            ['step' => 2, 'ne' => 'जाँच र राजस्व भुक्तानी', 'en' => 'Inspection and revenue payment'],
            ['step' => 3, 'ne' => 'Bluebook/दर्ता प्रमाणपत्र लिनुहोस्', 'en' => 'Receive bluebook/registration certificate'],
        ],
    ],
    'social_security' => [
        'name_ne' => 'सामाजिक सुरक्षा',
        'name_en' => 'Social Security',
        'icon'    => 'shield',
        'color'   => 'teal',
        'office'  => 'Social Security Fund / Local Level',
        'office_ne' => 'सामाजिक सुरक्षा कोष / स्थानीय तह',
        'website' => 'https://ssf.gov.np',
        'documents' => [
            ['ne' => 'नागरिकता प्रमाणपत्र', 'en' => 'Citizenship certificate'],
            ['ne' => 'बैंक खाता विवरण', 'en' => 'Bank account details'],
            ['ne' => 'सम्बन्धित सिफारिस/प्रमाणपत्र', 'en' => 'Relevant recommendation/certificate'],
        ],
        'process' => [
            ['step' => 1, 'ne' => 'योग्य योजना छानेर आवेदन दिनुहोस्', 'en' => 'Choose eligible scheme and apply'],
            ['step' => 2, 'ne' => 'कागजात verification पछि सेवा सुरु हुन्छ', 'en' => 'Service starts after document verification'],
        ],
    ],
];

// ═══════════════════════════════════════════════════════════════════════════════
// EMERGENCY CONTACTS
// ═══════════════════════════════════════════════════════════════════════════════

$emergencyContacts = [
    'national' => [
        ['name_ne' => 'प्रहरी', 'name_en' => 'Police', 'number' => '100', 'icon' => 'shield-alert', 'color' => 'blue'],
        ['name_ne' => 'दमकल', 'name_en' => 'Fire Brigade', 'number' => '101', 'icon' => 'flame', 'color' => 'orange'],
        ['name_ne' => 'एम्बुलेन्स', 'name_en' => 'Ambulance', 'number' => '102', 'icon' => 'activity', 'color' => 'red'],
        ['name_ne' => 'ट्राफिक प्रहरी', 'name_en' => 'Traffic Police', 'number' => '103', 'icon' => 'car', 'color' => 'green'],
        ['name_ne' => 'विपद हेल्पलाइन', 'name_en' => 'Disaster Helpline', 'number' => '1122', 'icon' => 'alert-triangle', 'color' => 'amber'],
        ['name_ne' => 'महिला हेल्पलाइन', 'name_en' => 'Women Helpline', 'number' => '1145', 'icon' => 'heart', 'color' => 'pink'],
        ['name_ne' => 'बाल हेल्पलाइन', 'name_en' => 'Child Helpline', 'number' => '1098', 'icon' => 'baby', 'color' => 'purple'],
        ['name_ne' => 'स्वास्थ्य हेल्पलाइन', 'name_en' => 'Health Helpline', 'number' => '1115', 'icon' => 'stethoscope', 'color' => 'teal'],
        ['name_ne' => 'पर्यटन प्रहरी', 'name_en' => 'Tourism Police', 'number' => '1144', 'icon' => 'compass', 'color' => 'indigo'],
        ['name_ne' => 'विद्युत प्राधिकरण', 'name_en' => 'NEA (Electricity)', 'number' => '1150', 'icon' => 'zap', 'color' => 'yellow'],
    ],
];

// ═══════════════════════════════════════════════════════════════════════════════
// USEFUL LINKS
// ═══════════════════════════════════════════════════════════════════════════════

$usefulLinks = [
    ['name' => 'Nepal Government Portal', 'url' => 'https://nepal.gov.np', 'icon' => 'landmark'],
    ['name' => 'E-Sewa', 'url' => 'https://esewa.com.np', 'icon' => 'wallet'],
    ['name' => 'Khalti', 'url' => 'https://khalti.com', 'icon' => 'credit-card'],
    ['name' => 'Nepal Rastra Bank', 'url' => 'https://nrb.org.np', 'icon' => 'building'],
    ['name' => 'Nepal Stock Exchange', 'url' => 'https://nepalstock.com.np', 'icon' => 'trending-up'],
    ['name' => 'Department of Immigration', 'url' => 'https://immigration.gov.np', 'icon' => 'plane'],
    ['name' => 'TU Exam Controller', 'url' => 'https://exam.tu.edu.np', 'icon' => 'graduation-cap'],
    ['name' => 'SEE Results', 'url' => 'https://see.ntc.net.np', 'icon' => 'file-text'],
    ['name' => 'Nagarik App', 'url' => 'https://nagarikapp.gov.np', 'icon' => 'smartphone'],
];

// ═══════════════════════════════════════════════════════════════════════════════
// API ROUTER
// ═══════════════════════════════════════════════════════════════════════════════

$response = [];

if ($service) {
    // Specific service
    if (isset($govServices[$service])) {
        $response = [
            'service'   => $govServices[$service],
            'related'   => array_slice(array_keys($govServices), 0, 4),
            'emergency' => $emergencyContacts['national'],
        ];
    } else {
        $response = [
            'error'     => 'Service not found',
            'available' => array_keys($govServices),
        ];
    }
} else {
    // All services
    $response = [
        'services'   => $govServices,
        'categories' => [
            'documents'  => ['passport', 'citizenship', 'driving_license', 'birth_registration'],
            'finance'    => ['pan_vat'],
            'employment' => ['loksewa'],
            'utilities'  => ['electricity', 'water'],
        ],
        'emergency'  => $emergencyContacts,
        'links'      => $usefulLinks,
        'updatedAt'  => date('Y-m-d'),
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
