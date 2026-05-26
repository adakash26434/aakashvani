<?php
/**
 * आकाशवाणी — AI Rashifal API v13
 * Daily Horoscope with OpenAI or Intelligent Fallback
 * "सूचनाको खुला आकाश"
 */
ob_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/error-logger.php';

// Security headers
sendSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

// Ensure UTF-8 encoding
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Rate limiting
$rateKey = 'rashifal:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!checkRateLimit($rateKey, 100, 60)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
    exit;
}

while (ob_get_level() > 0) ob_end_clean();
ob_start();

$rashi = $_GET['rashi'] ?? null;
$lang = $_GET['lang'] ?? 'ne';
$type = $_GET['type'] ?? 'daily'; // daily, monthly, yearly
$forceRefresh = isset($_GET['refresh']); // Force cache refresh

$cacheDir = __DIR__ . '/../data/cache/';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

// Clear cache if refresh requested
if ($forceRefresh) {
    $files = glob($cacheDir . 'rashifal_*.json');
    foreach ($files as $file) {
        @unlink($file);
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// RASHI DATA
// ═══════════════════════════════════════════════════════════════════════════════

$rashiData = [
    0  => ['ne' => 'मेष',     'en' => 'Aries',       'symbol' => '♈', 'element' => 'fire',  'planet' => 'मंगल',   'dates' => 'Mar 21 – Apr 19'],
    1  => ['ne' => 'वृष',     'en' => 'Taurus',      'symbol' => '♉', 'element' => 'earth', 'planet' => 'शुक्र',  'dates' => 'Apr 20 – May 20'],
    2  => ['ne' => 'मिथुन',   'en' => 'Gemini',      'symbol' => '♊', 'element' => 'air',   'planet' => 'बुध',    'dates' => 'May 21 – Jun 20'],
    3  => ['ne' => 'कर्कट',   'en' => 'Cancer',      'symbol' => '♋', 'element' => 'water', 'planet' => 'चन्द्र', 'dates' => 'Jun 21 – Jul 22'],
    4  => ['ne' => 'सिंह',    'en' => 'Leo',         'symbol' => '♌', 'element' => 'fire',  'planet' => 'सूर्य',  'dates' => 'Jul 23 – Aug 22'],
    5  => ['ne' => 'कन्या',   'en' => 'Virgo',       'symbol' => '♍', 'element' => 'earth', 'planet' => 'बुध',    'dates' => 'Aug 23 – Sep 22'],
    6  => ['ne' => 'तुला',    'en' => 'Libra',       'symbol' => '♎', 'element' => 'air',   'planet' => 'शुक्र',  'dates' => 'Sep 23 – Oct 22'],
    7  => ['ne' => 'वृश्चिक', 'en' => 'Scorpio',     'symbol' => '♏', 'element' => 'water', 'planet' => 'मंगल',   'dates' => 'Oct 23 – Nov 21'],
    8  => ['ne' => 'धनु',     'en' => 'Sagittarius', 'symbol' => '♐', 'element' => 'fire',  'planet' => 'बृहस्पति','dates' => 'Nov 22 – Dec 21'],
    9  => ['ne' => 'मकर',     'en' => 'Capricorn',   'symbol' => '♑', 'element' => 'earth', 'planet' => 'शनि',    'dates' => 'Dec 22 – Jan 19'],
    10 => ['ne' => 'कुम्भ',   'en' => 'Aquarius',    'symbol' => '♒', 'element' => 'air',   'planet' => 'शनि',    'dates' => 'Jan 20 – Feb 18'],
    11 => ['ne' => 'मीन',     'en' => 'Pisces',      'symbol' => '♓', 'element' => 'water', 'planet' => 'बृहस्पति','dates' => 'Feb 19 – Mar 20'],
];

// ═══════════════════════════════════════════════════════════════════════════════
// BS DATE CALCULATION
// ═══════════════════════════════════════════════════════════════════════════════

function getBsDate(): array {
    $nepal = new DateTimeZone('Asia/Kathmandu');
    $now = new DateTime('now', $nepal);
    $adY = (int)$now->format('Y');
    $adM = (int)$now->format('n');
    $adD = (int)$now->format('j');
    $adDow = (int)$now->format('w');
    
    $bsMonthData = [
        2080 => [0,31,32,31,32,31,30,30,29,30,29,30,30],
        2081 => [0,31,31,32,31,31,31,30,29,30,29,30,30],
        2082 => [0,31,32,31,32,31,30,30,30,29,30,29,31],
        2083 => [0,31,32,31,32,31,30,30,30,29,30,30,30],
        2084 => [0,31,31,32,31,31,30,30,30,29,30,30,30],
        2085 => [0,31,32,31,32,31,30,30,30,29,30,29,31],
    ];
    
    $refJd = gregoriantojd(4, 14, 2026);
    $jdNow = gregoriantojd($adM, $adD, $adY);
    $diff = $jdNow - $refJd;
    
    $bsY = 2083; $bsM = 1; $bsD = 1;
    
    if ($diff >= 0) {
        $rem = $diff;
        while ($rem > 0) {
            $dim = $bsMonthData[$bsY][$bsM] ?? 30;
            $left = $dim - $bsD;
            if ($rem <= $left) {
                $bsD += $rem;
                $rem = 0;
            } else {
                $rem -= ($left + 1);
                $bsD = 1;
                $bsM++;
                if ($bsM > 12) { $bsM = 1; $bsY++; }
            }
        }
    } else {
        $rem = -$diff;
        while ($rem > 0) {
            if ($bsD > 1) {
                $s = min($rem, $bsD - 1);
                $bsD -= $s;
                $rem -= $s;
            } else {
                $bsM--;
                if ($bsM < 1) { $bsM = 12; $bsY--; }
                $bsD = $bsMonthData[$bsY][$bsM] ?? 30;
                $rem -= 1;
            }
        }
    }
    
    $bsMonths = ['','बैशाख','जेठ','असार','श्रावण','भाद्र','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
    $bsDays = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहिबार','शुक्रबार','शनिबार'];
    
    return [
        'year'    => $bsY,
        'month'   => $bsM,
        'day'     => $bsD,
        'dow'     => $adDow,
        'monthNe' => $bsMonths[$bsM] ?? '',
        'dayNe'   => $bsDays[$adDow] ?? '',
        'full'    => $bsDays[$adDow] . ', ' . $bsD . ' ' . $bsMonths[$bsM] . ' ' . $bsY,
        'short'   => $bsD . ' ' . $bsMonths[$bsM] . ' ' . $bsY,
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// AI RASHIFAL GENERATION (OpenAI)
// ═══════════════════════════════════════════════════════════════════════════════

function generateAIRashifal(int $rashiIndex, array $rashiInfo, array $bsDate): ?array {
    $apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
    if (empty($apiKey)) return null;
    
    $prompt = "तपाई एक अनुभवी नेपाली ज्योतिषी हो। आज {$bsDate['full']} हो। {$rashiInfo['ne']} ({$rashiInfo['en']}) राशिको लागि आजको राशिफल लेख्नुहोस्। 

कृपया यी ५ वटा खण्डमा लेख्नुहोस् (प्रत्येक २-३ वाक्य):
1. सामान्य (दैनिक जीवन)
2. प्रेम र सम्बन्ध
3. करियर र व्यवसाय
4. स्वास्थ्य
5. आर्थिक स्थिति

अन्त्यमा:
- शुभ अंक: (१-९ बीचको)
- शुभ रंग: (नेपालीमा)
- शुभ समय: (समय दिनुहोस्)

JSON format मा दिनुहोस्:
{
  \"general\": \"...\",
  \"love\": \"...\",
  \"career\": \"...\",
  \"health\": \"...\",
  \"money\": \"...\",
  \"lucky_number\": 7,
  \"lucky_color\": \"हरियो\",
  \"lucky_time\": \"१०-१२ बजे\",
  \"overall_score\": 75
}";

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode([
            'model'       => defined('AI_MODEL') ? AI_MODEL : 'gpt-4o-mini',
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.8,
            'max_tokens'  => 800,
        ]),
        CURLOPT_TIMEOUT        => 30,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) return null;
    
    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    
    // Extract JSON from response
    if (preg_match('/\{[^{}]*"general"[^{}]*\}/s', $content, $m)) {
        $parsed = json_decode($m[0], true);
        if ($parsed) {
            $parsed['ai_generated'] = true;
            return $parsed;
        }
    }
    
    return null;
}

// ═══════════════════════════════════════════════════════════════════════════════
// INTELLIGENT FALLBACK RASHIFAL
// ═══════════════════════════════════════════════════════════════════════════════

function getFallbackRashifal(int $rashiIndex, array $rashiInfo, array $bsDate, string $type = 'daily'): array {
    // Pre-written high-quality Rashifal database
    $generalPool = [
        'fire' => [
            'daily' => [
                'आजको दिन तपाईंको ऊर्जा उच्च रहनेछ। नयाँ काम सुरु गर्न उत्तम समय हो।',
                'आत्मविश्वासले तपाईंलाई सफलताको बाटोमा अगाडि बढाउनेछ। साहस देखाउनुस्।',
                'आज कुनै ठूलो निर्णय लिनुपर्ने हुन सक्छ। मन लगाएर सोच्नुस्।',
                'तपाईंको नेतृत्व क्षमता आज देखिनेछ। अरूलाई प्रेरित गर्न सक्नुहुन्छ।',
            ],
            'monthly' => [
                'यो महिना तपाईंको लागि उत्साहजनक रहनेछ। नयाँ अवसरहरू आउनेछन्।',
                'काममा प्रगति हुनेछ। धैर्य राख्नुस्, फल निश्चित छ।',
                'व्यावसायिक कार्यमा सफलता मिल्नेछ। योजना बनाएर अगाडि बढ्नुस्।',
                'सामाजिक क्षेत्रमा प्रभाव बढ्नेछ। सम्बन्ध बलियो हुनेछ।',
            ],
            'yearly' => [
                'यो वर्ष तपाईंको लागि रूपान्तरणको वर्ष हुनेछ। ठूलो परिवर्तन आउन सक्छ।',
                'करियरमा नयाँ अवसरहरू मिल्नेछन्। तयार रहनुस्।',
                'वित्तीय स्थिति सुधारिनेछ। लगानी गर्न उत्तम समय हो।',
                'वैवाहिक जीवनमा सुख शान्ति बढ्नेछ। परिवारको सहयोग मिल्नेछ।',
            ],
        ],
        'earth' => [
            'daily' => [
                'आज व्यावहारिक कदम चाल्नु फाइदाजनक हुनेछ। योजनाबद्ध तरिकाले अगाडि बढ्नुस्।',
                'धैर्य राख्नुस्। समयले राम्रो फल दिनेछ। कडा मेहनत सार्थक हुनेछ।',
                'आर्थिक मामिलामा सावधान रहनुस्। ठूलो खर्च गर्नुअघि सोच्नुस्।',
                'जमिनसँग जोडिएका काममा सफलता मिल्नेछ। प्रकृतिसँग समय बिताउनुस्।',
            ],
            'monthly' => [
                'यो महिना स्थिरता ल्याउनेछ। धैर्य राख्नु महत्त्वपूर्ण छ।',
                'आर्थिक क्षेत्रमा सुधार हुनेछ। बचत गर्नुस्।',
                'काममा एकाग्रता बढाउनुस्। लक्ष्य प्राप्त हुनेछ।',
                'स्वास्थ्यको ध्यान दिनुस्। नियमित व्यायाम गर्नुस्।',
            ],
            'yearly' => [
                'यो वर्ष स्थिरता र सफलताको वर्ष हुनेछ। ठोस नींव बनाउनुस्।',
                'व्यावसायिक क्षेत्रमा विस्तार हुनेछ। नयाँ सम्झौता हुन सक्छ।',
                'सम्पत्ति बढ्नेछ। जग्गा घर किन्न उत्तम समय हो।',
                'पारिवारिक जीवन सुखमय हुनेछ। सन्तानको सफलता मिल्नेछ।',
            ],
        ],
        'air' => [
            'daily' => [
                'संवाद र सञ्चारमा आज राम्रो दिन हो। आफ्ना विचार खुलेर राख्नुस्।',
                'नयाँ जानकारी र ज्ञान प्राप्त गर्ने मौका मिल्नेछ। पढाइमा ध्यान दिनुस्।',
                'सामाजिक सम्बन्ध बलियो बनाउनुस्। साथीहरूसँग भेट्नुस्।',
                'सिर्जनात्मक सोचले नयाँ बाटो खोल्नेछ। आउट-अफ-द-बक्स सोच्नुस्।',
            ],
            'monthly' => [
                'यो महिना सिर्जनात्मक काममा सफलता मिल्नेछ। नयाँ सिकारहरू सिक्नुहुनेछ।',
                'सञ्चार क्षेत्रमा प्रगति हुनेछ। नेटवर्किङ गर्नुस्।',
                'शैक्षिक कार्यमा राम्रो प्रदर्शन हुनेछ।',
                'यात्राको योग छ। नयाँ स्थानहरू भ्रमण गर्न सक्नुहुन्छ।',
            ],
            'yearly' => [
                'यो वर्ष सिर्जनात्मकता र ज्ञानको वर्ष हुनेछ। नयाँ सीपहरू सिक्नुहुनेछ।',
                'सञ्चार क्षेत्रमा विस्तार हुनेछ। मिडिया वा प्रविधिमा सफलता मिल्नेछ।',
                'शिक्षा र अनुसन्धानमा प्रगति हुनेछ। उच्च डिग्री प्राप्त हुन सक्छ।',
                'सामाजिक प्रभाव बढ्नेछ। समुदाय सेवामा संलग्न हुनुस्।',
            ],
        ],
        'water' => [
            'daily' => [
                'भावनात्मक रूपमा संतुलित रहनुस्। आफ्नो मनको कुरा सुन्नुस्।',
                'अन्तर्मुखी हुने समय हो। ध्यान र चिन्तनले शान्ति दिनेछ।',
                'परिवारसँग समय बिताउनुस्। घरको माहौल सकारात्मक बनाउनुस्।',
                'सहानुभूति र करुणाले तपाईंलाई अरूसँग जोड्नेछ। मद्दत गर्नुस्।',
            ],
            'monthly' => [
                'यो महिना भावनात्मक सन्तुलता महत्त्वपूर्ण छ। मनको कुरा सुन्नुस्।',
                'परिवारसँग समय बिताउनुस्। सम्बन्ध मजबुत हुनेछ।',
                'आध्यात्मिक अभ्यास गर्नुस्। शान्ति मिल्नेछ।',
                'कलात्मक काममा रुचि बढ्नेछ। सिर्जनात्मक हुनुस्।',
            ],
            'yearly' => [
                'यो वर्ष भावनात्मक र आध्यात्मिक विकासको वर्ष हुनेछ।',
                'परिवारिक सम्बन्ध बलियो हुनेछ। शादी वा सम्बन्धमा प्रगति हुन सक्छ।',
                'स्वास्थ्यमा ध्यान दिनुस्। मानसिक शान्तिको लागि ध्यान गर्नुस्।',
                'दान र सेवामा संलग्न हुनुस्। आध्यात्मिक बृद्धि हुनेछ।',
            ],
        ],
    ];
    
    $lovePool = [
        'daily' => [
            'आज प्रेम सम्बन्धमा मिठास आउनेछ। साथीसँग खुला कुराकानी गर्नुस्।',
            'पारिवारिक सम्बन्ध बलियो हुनेछ। प्रियजनलाई समय दिनुस्।',
            'नयाँ सम्बन्धको शुभारम्भ हुन सक्छ। आफ्नो मन खुला राख्नुस्।',
            'विश्वास र समझदारीले सम्बन्ध गहिरिनेछ। धैर्य राख्नुस्।',
            'प्रेममा सानो गलतफहमी आउन सक्छ। कुराकानी गरेर समाधान गर्नुस्।',
        ],
        'monthly' => [
            'यो महिना प्रेम सम्बन्धमा सुधार आउनेछ। बोल्नुस्, सुन्नुस्।',
            'विवाहित जीवनमा सुख बढ्नेछ। साथीसँग समय बिताउनुस्।',
            'नयाँ सम्बन्धको सम्भावना छ। खुलेर रहनुस्।',
            'पारिवारसँग सम्बन्ध मजबुत हुनेछ। समर्थन पाउनुहुनेछ।',
        ],
        'yearly' => [
            'यो वर्ष प्रेम र सम्बन्धमा सकारात्मक परिवर्तन आउनेछ।',
            'विवाहको योग छ। जीवनसाथी भेट्न सक्नुहुन्छ।',
            'पारिवारिक जीवन सुखमय हुनेछ। सन्तानको सफलता मिल्नेछ।',
            'सामाजिक सम्बन्ध बलियो हुनेछ। सम्मान प्राप्त हुनेछ।',
        ],
    ];
    
    $careerPool = [
        'daily' => [
            'पेशागत क्षेत्रमा नयाँ अवसर देखिनेछ। तयार रहनुस्।',
            'कामको थिचोमिचो हुन सक्छ। प्राथमिकता मिलाउनुस्।',
            'सहकर्मीहरूसँग सहयोगले काम सजिलो बनाउनेछ।',
            'बॉससँग राम्रो impression बन्नेछ। आफ्नो क्षमता देखाउनुस्।',
            'व्यापारमा सुधार आउनेछ। नयाँ ग्राहक भेटिनेछन्।',
        ],
        'monthly' => [
            'यो महिना करियरमा प्रगति हुनेछ। नयाँ प्रोजेक्टहरू सुरु हुन सक्छन्।',
            'पदोन्नतिको सम्भावना छ। कडा मेहनत गर्नुस्।',
            'व्यापारमा नाफा बढ्नेछ। नयाँ बजार खोल्न सक्नुहुन्छ।',
            'सिक्नुहुनेछ। नयाँ कौशल्यहरू विकास गर्नुस्।',
        ],
        'yearly' => [
            'यो वर्ष करियरको लागि महत्त्वपूर्ण छ। ठूलो उन्नति हुन सक्छ।',
            'नयाँ जागिर वा व्यवसाय सुरु गर्न उत्तम समय हो।',
            'पदोन्नति वा वेतन वृद्धिको योग छ।',
            'व्यावसायिक सफलता मिल्नेछ। लक्ष्य प्राप्त हुनेछ।',
        ],
    ];
    
    $healthPool = [
        'daily' => [
            'स्वास्थ्य ठीक रहनेछ। सन्तुलित खानपान गर्नुस्।',
            'हल्का व्यायाम र योगाले फाइदा पुर्‍याउनेछ।',
            'पर्याप्त निद्रा लिनुस्। शरीरलाई आराम दिनुस्।',
            'तनाव कम गर्नुस्। मनोरञ्जनको लागि समय निकाल्नुस्।',
            'पानी धेरै पिउनुस्। हाइड्रेटेड रहनु महत्त्वपूर्ण छ।',
        ],
        'monthly' => [
            'यो महिना स्वास्थ्यमा ध्यान दिनुस्। नियमित व्यायाम गर्नुस्।',
            'खानपानमा सुधार गर्नुस्। सन्तुलित आहार लिनुस्।',
            'तनाव कम गर्नुस्। ध्यान र योग गर्नुस्।',
            'निद्राको ध्यान दिनुस्। शरीरलाई आराम दिनुस्।',
        ],
        'yearly' => [
            'यो वर्ष स्वास्थ्यको लागि ध्यान दिनुपर्नेछ। नियमित जाँच गर्नुस्।',
            'स्वस्थ जीवनशैली अपनाउनुस्। व्यायाम र खानपानमा सुधार गर्नुस्।',
            'मानसिक स्वास्थ्यको ध्यान दिनुस्। ध्यान र ध्यान गर्नुस्।',
            'वार्षिक स्वास्थ्य जाँच गर्नुस्। रोगबाट बच्नुस्।',
        ],
    ];
    
    $moneyPool = [
        'daily' => [
            'आर्थिक स्थिति सुधारिनेछ। बचत गर्ने बानी बसाल्नुस्।',
            'अप्रत्याशित खर्च आउन सक्छ। तयार रहनुस्।',
            'लगानीमा राम्रो प्रतिफल मिल्नेछ।',
            'ऋण फिर्ता गर्ने समय उपयुक्त छ।',
            'नयाँ आयको स्रोत खुल्न सक्छ। सतर्क रहनुस्।',
        ],
        'monthly' => [
            'यो महिना आर्थिक स्थिति सुधारिनेछ। बचत गर्नुस्।',
            'नयाँ आयको स्रोत खुल्न सक्छ। लगानी गर्न सकिन्छ।',
            'ऋण फिर्ता गर्न उत्तम समय हो।',
            'व्यापारमा नाफा बढ्नेछ। नयाँ ग्राहक भेटिनेछन्।',
        ],
        'yearly' => [
            'यो वर्ष आर्थिक स्थिति सुधारिनेछ। सम्पत्ति बढ्नेछ।',
            'नयाँ आयको स्रोत खुल्नेछ। लगानी गर्न उत्तम समय हो।',
            'जग्गा वा सम्पत्ति किन्न सक्नुहुन्छ।',
            'व्यापार विस्तार गर्न सक्नुहुन्छ। नाफा बढ्नेछ।',
        ],
    ];
    
    $luckyColors = ['रातो', 'हरियो', 'निलो', 'पहेंलो', 'सुन्तला', 'गुलाबी', 'सेतो', 'बैजनी'];
    $luckyTimes = ['६-८ बजे बिहान', '९-११ बजे', '११-१ बजे', '२-४ बजे', '५-७ बजे साँझ'];
    
    $element = $rashiInfo['element'];
    
    // Use date-based seeding for consistency
    $seed = crc32($bsDate['short'] . $rashiIndex . $type);
    srand($seed);
    
    $generalOptions = $generalPool[$element][$type] ?? $generalPool[$element]['daily'];
    $loveOptions = $lovePool[$type] ?? $lovePool['daily'];
    $careerOptions = $careerPool[$type] ?? $careerPool['daily'];
    $healthOptions = $healthPool[$type] ?? $healthPool['daily'];
    $moneyOptions = $moneyPool[$type] ?? $moneyPool['daily'];
    
    return [
        'type'         => $type,
        'general'      => $generalOptions[array_rand($generalOptions)],
        'love'         => $loveOptions[array_rand($loveOptions)],
        'career'       => $careerOptions[array_rand($careerOptions)],
        'health'       => $healthOptions[array_rand($healthOptions)],
        'money'        => $moneyOptions[array_rand($moneyOptions)],
        'lucky_number' => rand(1, 9),
        'lucky_color'  => $luckyColors[array_rand($luckyColors)],
        'lucky_time'   => $luckyTimes[array_rand($luckyTimes)],
        'overall_score'=> rand(60, 90),
        'ai_generated' => false,
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// CACHE AND RETRIEVE
// ═══════════════════════════════════════════════════════════════════════════════

if (!function_exists('readCache')) {
    function readCache(string $key): ?array {
        global $cacheDir;
        $file = $cacheDir . $key . '.json';
        if (file_exists($file) && (time() - filemtime($file)) < 86400) { // 24 hour cache
            $data = json_decode(file_get_contents($file), true);
            if ($data) return $data;
        }
        return null;
    }
}

if (!function_exists('writeCache')) {
    function writeCache(string $key, array $data): void {
        global $cacheDir;
        $data['cached_at'] = date('Y-m-d H:i:s');
        file_put_contents($cacheDir . $key . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}

function readDbRashifal(int $rashiIndex, string $dateAd, string $lang): ?array {
    try {
        ensureRashifalTable();
        $stmt = db()->prepare("SELECT payload FROM rashifal_daily WHERE rashi_index=? AND date_ad=? AND lang=? LIMIT 1");
        $stmt->execute([$rashiIndex, $dateAd, $lang]);
        $payload = $stmt->fetchColumn();
        $data = $payload ? json_decode((string)$payload, true) : null;
        return is_array($data) ? $data : null;
    } catch (Throwable $e) {
        return null;
    }
}

function writeDbRashifal(int $rashiIndex, array $rashiInfo, array $bsDate, string $lang, array $data): void {
    try {
        ensureRashifalTable();
        $dateAd = date('Y-m-d');
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $source = !empty($data['readings']['ai_generated']) ? 'ai' : 'fallback';
        $isAi = $source === 'ai' ? 1 : 0;
        if (isMysql()) {
            db()->prepare("INSERT INTO rashifal_daily
                (rashi_index,rashi_key,date_bs,date_ad,lang,payload,source,is_ai_generated)
                VALUES (?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE payload=VALUES(payload), source=VALUES(source), is_ai_generated=VALUES(is_ai_generated), updated_at=CURRENT_TIMESTAMP")
                ->execute([$rashiIndex, $rashiInfo['en'], $bsDate['short'], $dateAd, $lang, $payload, $source, $isAi]);
        } else {
            db()->prepare("INSERT OR REPLACE INTO rashifal_daily
                (rashi_index,rashi_key,date_bs,date_ad,lang,payload,source,is_ai_generated)
                VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$rashiIndex, $rashiInfo['en'], $bsDate['short'], $dateAd, $lang, $payload, $source, $isAi]);
        }
    } catch (Throwable $e) {}
}

function getRashifalForRashi(int $rashiIndex, string $type = 'daily'): array {
    global $rashiData;
    
    if (!isset($rashiData[$rashiIndex])) {
        return ['error' => 'Invalid rashi index', 'valid_range' => '0-11'];
    }
    
    $rashiInfo = $rashiData[$rashiIndex];
    $bsDate = getBsDate();
    $cacheKey = 'rashifal_' . $rashiIndex . '_' . $type . '_' . $bsDate['year'] . '_' . $bsDate['month'] . '_' . $bsDate['day'];
    $dateAd = date('Y-m-d');
    $lang = ($_GET['lang'] ?? 'ne') === 'en' ? 'en' : 'ne';
    
    $dbCached = readDbRashifal($rashiIndex, $dateAd, $lang);
    if ($dbCached && isset($dbCached['readings'])) {
        return $dbCached;
    }
    
    // Check cache first
    $cached = readCache($cacheKey);
    if ($cached && isset($cached['readings'])) {
        return $cached;
    }
    
    // Try AI generation first
    $readings = null;
    if (defined('OPENAI_API_KEY') && OPENAI_API_KEY) {
        $readings = generateAIRashifal($rashiIndex, $rashiInfo, $bsDate, $type);
    }
    
    // Fallback to intelligent generator
    if (!$readings) {
        $readings = getFallbackRashifal($rashiIndex, $rashiInfo, $bsDate, $type);
    }
    
    // Deterministic per-day scores (seeded by date+rashi) — no random fakes
    $sd = crc32(($bsDate['short'] ?? date('Y-m-d')) . '|' . $rashiIndex);
    mt_srand($sd);
    $score = fn($lo,$hi) => $lo + (mt_rand() % max(1,$hi-$lo+1));

    $result = [
        'rashi'       => [
            'index'   => $rashiIndex,
            'name_ne' => $rashiInfo['ne'],
            'name_en' => $rashiInfo['en'],
            'symbol'  => $rashiInfo['symbol'],
            'element' => $rashiInfo['element'],
            'planet'  => $rashiInfo['planet'],
            'dates'   => $rashiInfo['dates'],
        ],
        'date'        => $bsDate,
        'readings'    => $readings,
        'scores'      => [
            'love'    => $score(50,95),
            'career'  => $score(50,95),
            'health'  => $score(60,95),
            'money'   => $score(45,90),
            'overall' => $readings['overall_score'] ?? $score(60,85),
        ],
        'source'      => $readings['ai_generated'] ? 'AI Generated (OpenAI)' : 'सामान्य ज्योतिषीय परम्परा',
        'disclaimer'  => 'राशिफल मनोरञ्जन/परम्पराको लागि मात्र हो — चिकित्सा, कानुनी वा वित्तीय निर्णयको लागि होइन।',
        'updatedAt'   => date('Y-m-d'),
    ];
    
    writeCache($cacheKey, $result);
    writeDbRashifal($rashiIndex, $rashiInfo, $bsDate, $lang, $result);
    return $result;
}

function getAllRashifal(string $type = 'daily'): array {
    global $rashiData;
    
    $bsDate = getBsDate();
    $cacheKey = 'rashifal_all_' . $type . '_' . $bsDate['year'] . '_' . $bsDate['month'] . '_' . $bsDate['day'];
    
    $cached = readCache($cacheKey);
    if ($cached) return $cached;
    
    $all = [];
    foreach (array_keys($rashiData) as $index) {
        $all[$index] = getRashifalForRashi($index, $type);
    }
    
    $result = [
        'type'       => $type,
        'date'       => $bsDate,
        'rashifal'   => $all,
        'rashiList'  => $rashiData,
        'updatedAt'  => date('Y-m-d H:i'),
        'source'     => 'आकाशवाणी — सूचनाको खुला आकाश',
    ];
    
    writeCache($cacheKey, $result);
    return $result;
}

// ═══════════════════════════════════════════════════════════════════════════════
// API ROUTER
// ═══════════════════════════════════════════════════════════════════════════════

$response = [];

if ($rashi !== null) {
    // Specific rashi
    $rashiIndex = is_numeric($rashi) ? (int)$rashi : null;
    
    // Map name to index if string provided
    if ($rashiIndex === null) {
        foreach ($rashiData as $idx => $info) {
            if (strcasecmp($info['en'], $rashi) === 0 || $info['ne'] === $rashi) {
                $rashiIndex = $idx;
                break;
            }
        }
    }
    
    if ($rashiIndex !== null && isset($rashiData[$rashiIndex])) {
        $response = getRashifalForRashi($rashiIndex, $type);
    } else {
        $response = [
            'error'      => 'Rashi not found',
            'note'       => 'Use index (0-11) or name (Aries, मेष, etc.)',
            'available'  => array_map(fn($r) => $r['en'] . ' / ' . $r['ne'], $rashiData),
        ];
    }
} else {
    // All rashifal
    $response = getAllRashifal($type);
}

while (ob_get_level() > 0) ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
