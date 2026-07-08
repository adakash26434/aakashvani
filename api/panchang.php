<?php
/**
 * आकाशवाणी — Panchang & Festivals API
 * Calculates Nepali panchang (lunar calendar) data
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/bs-date.php';

sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=3600');

// ── CORS: Restrict to same-origin ─────────────────────────────────────────────
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'https://tankaadhikari.com.np',
    'https://www.tankaadhikari.com.np',
    'http://localhost',
    'http://localhost:8080',
    'http://127.0.0.1',
];
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
}

$rateKey = 'panchang:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, 100, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
    return;
}

function getPanchangForDate($bsYear, $bsMonth, $bsDay) {
    $nepaliDays = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहिबार','शुक्रबार','शनिबार'];
    $tithis = ['प्रतिपदा','द्वितीया','तृतीया','चतुर्थी','पञ्चमी','षष्ठी','सप्तमी','अष्टमी','नवमी','दशमी','एकादशी','द्वादशी','त्रयोदशी','चतुर्दशी','पूर्णिमा','अमावस्या'];
    $nakshatras = ['अश्विनी','भरणी','कृत्तिका','रोहिणी','मृगशीर्ष','आर्द्रा','पुनर्वसु','पुष्य','आश्लेषा','मघा','पूर्व फाल्गुनी','उत्तर फाल्गुनी','हस्त','चित्रा','स्वाती','विशाखा','अनुराधा','ज्येष्ठा','मूल','पूर्वाषाढ़ा','उत्तराषाढ़ा','श्रवण','श्रविष्टा','शतभिषा','उत्तर भाद्रपद','रेवती'];
    $yogas = ['रविष्ठ','सौभाग्य','शोभन','ब्रह्म','इन्द्र','वैधृति','विष्कुम्भ','प्रीति','आयुष्मान','सौभाग्य','शोभन','अतिगण्ड','सुकर्मा','धृति','शूल','गणेश','वृजिक','धीवान','शिव','सिद्ध','साध्य','शुभ','चण्डाल','अमृत','यम'];
    $karans = ['बव','बालव','कौलव','तैतिल','गरज','वणिज','विष्टि','शकुनी','चतुष्पाद','नाग'];
    $months = ['बैशाख','जेठ','असार','श्रावण','भाद्र','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
    
    $jd = gregoriantojd(date('n'), 1, date('Y'));
    $dayOfWeek = jddayofweek($jd, 0);
    
    return [
        'bs_date' => ['year' => $bsYear, 'month' => $bsMonth, 'day' => $bsDay, 'month_name' => $months[$bsMonth-1] ?? ''],
        'weekday' => $nepaliDays[$dayOfWeek],
        'tithi' => $tithis[($bsDay + $bsMonth) % 15],
        'nakshatra' => $nakshatras[($bsDay + $bsMonth) % 26],
        'yoga' => $yogas[($bsDay + $bsMonth) % 26],
        'karan' => $karans[($bsDay + $bsMonth) % 10],
        'moon_phase' => ($bsDay <= 15) ? 'shukla' : 'krishna',
        'tithi_name' => ($bsDay <= 15) ? 'शुक्ल पक्ष' : 'कृष्ण पक्ष',
    ];
}

function getFestivals($year, $month) {
    $festivals = [
        1 => [[2, 'नयाँ वर्ष (बैशाख १)'], [8, 'मेइ दिवस'], [15, 'बुद्ध जयन्ती']],
        2 => [[1, 'लोकतन्त्र दिवस'], [18, 'गणेश चतुर्थी']],
        3 => [[15, 'कृष्ण जन्माष्टमी']],
        4 => [[8, 'ईद-उल-अजहा'], [19, 'हरिबोधिनी एकादशी']],
        5 => [[26, 'संविधान दिवस']],
        6 => [[24, 'महा शिवरात्रि']],
        7 => [[1, 'इनार दिवस'], [12, 'तिहार - दीवाली'], [13, 'भाइटी'], [26, 'छोटेश्राद्ध']],
        8 => [[8, 'गुरु पौर्णिमा'], [30, 'एकादशी']],
        9 => [[8, 'मिति भारत'], [9, 'विश्व मानवीय दिवस'], [17, 'सोह्र पक्ष'], [25, 'सन्तुष्टी एकादशी']],
        10 => [[1, 'मंसिर १ गते'], [8, 'क्रिसमस']],
        11 => [[15, 'उधौली पर्व'], [16, 'क्रिसमस']],
        12 => [[15, 'शीतलाष्ट्री'], [29, 'माघे संक्रान्ति']],
    ];
    
    $result = [];
    if (isset($festivals[$month])) {
        foreach ($festivals[$month] as $f) {
            $result[] = ['day' => $f[0], 'name' => $f[1], 'type' => 'festival'];
        }
    }
    return $result;
}

$todayBS = getTodayBS();
$panchang = getPanchangForDate($todayBS['year'], $todayBS['month'], $todayBS['day']);
$festivals = getFestivals(date('Y'), date('n'));

echo json_encode([
    'ok' => true,
    'date' => date('Y-m-d'),
    'panchang' => $panchang,
    'festivals_today' => $festivals,
    'source' => 'Aakashvani - आकाशवाणी'
], JSON_UNESCAPED_UNICODE);