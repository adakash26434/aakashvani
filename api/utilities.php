<?php
/**
 * आकाशवाणी — Utilities API v3 (REAL DATA ONLY)
 * Bank Rates · Remittance · Load Shedding · Lok Sewa · Traffic · Water
 *
 *  POLICY: NO fabricated numbers. Each section either
 *    (a) fetches LIVE from an authentic Nepal source, OR
 *    (b) returns empty + official link + admin override (if any), OR
 *    (c) reads admin overrides published via /admin/content.php.
 *
 *  Sources (all official / legal):
 *    NRB Forex          — https://www.nrb.org.np  (api/forex/v1/rates)
 *    Bank Interest Rates — NRB monthly publication (admin must publish)
 *    Remittance         — Each provider's official page (link out only)
 *    NEA Load-Shedding  — https://nea.org.np (no current schedule)
 *    PSC Lok Sewa       — https://psc.gov.np  (scraped notice list)
 *    Traffic Police     — https://traffic.nepalpolice.gov.np (link + admin)
 *    KUKL Water         — https://kathmanduwater.org (link + admin)
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=1800');

$type = $_GET['type'] ?? 'all';
$cacheDir = __DIR__ . '/../data/cache/';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

function readCache(string $key, int $ttl = 3600): ?array {
    global $cacheDir;
    $file = $cacheDir . $key . '.json';
    if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
        return json_decode((string)file_get_contents($file), true);
    }
    return null;
}
function writeCache(string $key, array $data): void {
    global $cacheDir;
    $data['cached_at'] = date('Y-m-d H:i:s');
    @file_put_contents($cacheDir . $key . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
}
if (!function_exists('fetchUrl')) {
    function fetchUrl(string $url, int $timeout = 12): ?string {
        if (!function_exists('curl_init')) return null;
        require_once __DIR__ . '/../includes/http.php';
        return nh_fetchUrl($url, [], $timeout, true);
    }
}

/**
 * Load admin overrides published via /admin/content.php.
 * Returns the items array for a given key only when ENABLED.
 */
function adminItems(string $key): array {
    $f = __DIR__ . '/../cache/content-overrides.json';
    if (!is_file($f)) return [];
    $j = json_decode((string)@file_get_contents($f), true);
    $sec = $j[$key] ?? null;
    if (!$sec || empty($sec['enabled'])) return [];
    return [
        'items'      => $sec['items'] ?? [],
        'source'     => $sec['source'] ?? 'आकाशवाणी Admin',
        'source_url' => $sec['source_url'] ?? '',
        'note'       => $sec['note'] ?? '',
        'updatedAt'  => $sec['updatedAt'] ?? '',
    ];
}

// ──────────────────────────────────────────────────────────────────────
//  NRB FOREX (LIVE — official Nepal Rastra Bank API)
// ──────────────────────────────────────────────────────────────────────
function getNrbForex(): array {
    $cached = readCache('nrb_forex', 3600);
    if ($cached) return $cached;
    $resp = fetchUrl('https://www.nrb.org.np/api/forex/v1/rates?per_page=20&page=1');
    $rates = [];
    if ($resp) {
        $j = json_decode($resp, true);
        $payload = $j['data']['payload'][0]['rates'] ?? [];
        foreach ($payload as $r) {
            $iso = $r['currency']['iso3'] ?? '';
            if (!$iso) continue;
            $rates[] = [
                'currency' => $iso,
                'name'     => $r['currency']['name'] ?? $iso,
                'unit'     => (int)($r['currency']['unit'] ?? 1),
                'buy'      => isset($r['buy'])  ? (float)$r['buy']  : null,
                'sell'     => isset($r['sell']) ? (float)$r['sell'] : null,
            ];
        }
    }
    $data = [
        'rates'      => $rates,
        'source'     => 'Nepal Rastra Bank (NRB)',
        'source_url' => 'https://www.nrb.org.np/forex/',
        'is_live'    => !empty($rates),
        'updatedAt'  => date('c'),
    ];
    if (!empty($rates)) writeCache('nrb_forex', $data);
    return $data;
}

// ──────────────────────────────────────────────────────────────────────
//  BANK INTEREST RATES (Admin-published from NRB monthly publication)
//  No hard-coded numbers — admin must publish from authentic source.
// ──────────────────────────────────────────────────────────────────────
function getBankRates(): array {
    $ad = adminItems('bank_rates');
    return [
        'banks'      => $ad['items'] ?? [],
        'is_live'    => !empty($ad['items']),
        'source'     => $ad['source'] ?? 'Nepal Rastra Bank (मासिक प्रकाशन)',
        'source_url' => $ad['source_url'] ?? 'https://www.nrb.org.np/category/bfis-reports/',
        'note'       => $ad['note'] ?? 'ब्याजदर हरेक महिना NRB द्वारा प्रकाशित हुन्छ। आधिकारिक PDF हेर्नुहोस्।',
        'updatedAt'  => $ad['updatedAt'] ?? '',
        'official_links' => [
            ['name'=>'Nepal Bank Ltd',     'url'=>'https://nepalbank.com.np'],
            ['name'=>'Rastriya Banijya',   'url'=>'https://www.rbb.com.np'],
            ['name'=>'NIC Asia',           'url'=>'https://nicasiabank.com'],
            ['name'=>'Nabil Bank',         'url'=>'https://nabilbank.com'],
            ['name'=>'Global IME',         'url'=>'https://globalimebank.com'],
            ['name'=>'Standard Chartered', 'url'=>'https://www.sc.com/np/'],
            ['name'=>'Himalayan Bank',     'url'=>'https://himalayanbank.com'],
            ['name'=>'NMB Bank',           'url'=>'https://nmb.com.np'],
        ],
    ];
}

// ──────────────────────────────────────────────────────────────────────
//  REMITTANCE — link-out only (real provider rates change every minute,
//  we will NOT publish unverified numbers).
// ──────────────────────────────────────────────────────────────────────
function getRemittance(): array {
    $forex = getNrbForex();
    $ad    = adminItems('remittance');
    $providers = [
        ['name'=>'IME Remit',      'name_ne'=>'आइएमई रेमिट',     'url'=>'https://ikiremit.com'],
        ['name'=>'Prabhu Remit',   'name_ne'=>'प्रभु रेमिट',      'url'=>'https://prabhuremit.com'],
        ['name'=>'Himal Remit',    'name_ne'=>'हिमाल रेमिट',      'url'=>'https://himalremit.com'],
        ['name'=>'City Express',   'name_ne'=>'सिटी एक्सप्रेस',   'url'=>'https://cityexpress.com.np'],
        ['name'=>'Western Union',  'name_ne'=>'वेस्टर्न युनियन',  'url'=>'https://westernunion.com'],
        ['name'=>'MoneyGram',      'name_ne'=>'मनिग्राम',         'url'=>'https://moneygram.com'],
    ];
    return [
        'providers'  => $providers,
        'nrb_forex'  => $forex['rates'],            // authentic reference rate
        'admin'      => $ad['items'] ?? [],         // optional admin postings (with source)
        'source'     => 'NRB Forex + Official Provider Sites',
        'source_url' => 'https://www.nrb.org.np/forex/',
        'note'       => 'वास्तविक प्रेषण दर सेवा प्रदायकको आधिकारिक App/Web मा मात्र पुष्टि हुन्छ। यहाँ NRB को सन्दर्भ विनिमय दर र आधिकारिक लिङ्क मात्र राखिएको छ।',
        'updatedAt'  => date('c'),
    ];
}

// ──────────────────────────────────────────────────────────────────────
//  LOAD SHEDDING (NEA) — currently none nationally; admin can publish
//  group schedule from NEA Sajilo when needed.
// ──────────────────────────────────────────────────────────────────────
function getLoadShedding(): array {
    $ad = adminItems('loadshedding');
    $hasAdmin = !empty($ad['items']);
    return [
        'status'     => $hasAdmin ? 'active' : 'none',
        'message'    => $hasAdmin ? 'हाल तालिका अनुसार लोडसेडिङ छ।' : 'हाल राष्ट्रिय रूपमा लोडसेडिङ छैन — NEA।',
        'message_en' => $hasAdmin ? 'Load shedding active per schedule.' : 'No nationwide load shedding — NEA.',
        'schedule'   => $ad['items'] ?? [],
        'source'     => $ad['source'] ?? 'Nepal Electricity Authority (NEA)',
        'source_url' => $ad['source_url'] ?? 'https://nea.org.np/',
        'nea_contact'=> ['phone'=>'1103', 'website'=>'https://nea.org.np', 'app'=>'NEA Sajilo'],
        'updatedAt'  => $ad['updatedAt'] ?? date('c'),
    ];
}

// ──────────────────────────────────────────────────────────────────────
//  LOK SEWA (PSC) — LIVE scrape from psc.gov.np + admin pinning
// ──────────────────────────────────────────────────────────────────────
function getLokSewa(): array {
    $cached = readCache('loksewa', 3600);
    $notices = $cached['notices'] ?? [];
    if (!$notices) {
        $html = fetchUrl('https://psc.gov.np/', 15);
        if ($html && preg_match_all('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/iu', $html, $m, PREG_SET_ORDER)) {
            $seen = [];
            foreach ($m as $row) {
                $title = trim(strip_tags($row[2]));
                $href  = $row[1];
                if (mb_strlen($title) < 12) continue;
                $keep = (
                    mb_strpos($title,'विज्ञापन')!==false || mb_strpos($title,'नतिजा')!==false ||
                    mb_strpos($title,'परीक्षा')!==false  || mb_strpos($title,'सूचना')!==false ||
                    stripos($title,'notice')!==false      || stripos($title,'result')!==false
                );
                if (!$keep) continue;
                if (strpos($href,'http')!==0) $href = 'https://psc.gov.np'.(strpos($href,'/')===0?'':'/').$href;
                $k = md5($title);
                if (isset($seen[$k])) continue;
                $seen[$k]=1;
                $notices[] = ['title'=>$title, 'url'=>$href];
                if (count($notices)>=15) break;
            }
        }
        if ($notices) writeCache('loksewa', ['notices'=>$notices]);
    }
    $ad = adminItems('loksewa');
    return [
        'notices'    => $notices,
        'pinned'     => $ad['items'] ?? [],
        'is_live'    => !empty($notices),
        'source'     => 'Public Service Commission (PSC) Nepal',
        'source_url' => 'https://psc.gov.np/',
        'helpline'   => '01-4771489',
        'quick_links'=> [
            ['title'=>'PSC Online Application','url'=>'https://psconline.psc.gov.np'],
            ['title'=>'Exam Centers',          'url'=>'https://psc.gov.np/exam-center'],
        ],
        'updatedAt'  => date('c'),
    ];
}

// ──────────────────────────────────────────────────────────────────────
//  TRAFFIC (Kathmandu Valley) — official Traffic Police only.
//  No fabricated congestion numbers. Admin posts notices verbatim.
// ──────────────────────────────────────────────────────────────────────
function getTraffic(): array {
    $ad = adminItems('traffic');
    return [
        'notices'    => $ad['items'] ?? [],
        'is_live'    => !empty($ad['items']),
        'source'     => $ad['source'] ?? 'Metropolitan Traffic Police Division (MTPD)',
        'source_url' => $ad['source_url'] ?? 'https://traffic.nepalpolice.gov.np/',
        'official'   => [
            ['name'=>'MTPD Website',     'url'=>'https://traffic.nepalpolice.gov.np/'],
            ['name'=>'Nepal Police',     'url'=>'https://nepalpolice.gov.np/'],
            ['name'=>'MTPD Facebook',    'url'=>'https://www.facebook.com/MTPDKathmandu/'],
        ],
        'contacts'   => [
            ['name'=>'ट्राफिक प्रहरी',   'number'=>'103'],
            ['name'=>'Traffic Control',   'number'=>'01-4412835'],
        ],
        'tips' => [
            ['ne'=>'हेल्मेट र सिटबेल्ट अनिवार्य',  'en'=>'Helmet & seatbelt mandatory'],
            ['ne'=>'मोबाइल चलाउँदै नचलाउनुस्',    'en'=>'No phone while driving'],
            ['ne'=>'लेन अनुशासन पालना गर्नुस्',   'en'=>'Follow lane discipline'],
        ],
        'note'       => 'सडक बन्द/प्रतिबन्ध जस्ता सूचना MTPD को आधिकारिक FB/Web बाट मात्र verify गरेर Admin ले प्रकाशन गर्छ।',
        'updatedAt'  => $ad['updatedAt'] ?? date('c'),
    ];
}

// ──────────────────────────────────────────────────────────────────────
//  WATER SUPPLY (KUKL) — admin publishes verified schedule + official links.
// ──────────────────────────────────────────────────────────────────────
function getWater(): array {
    $ad = adminItems('water');
    return [
        'provider'    => 'Kathmandu Upatyaka Khanepani Limited (KUKL)',
        'provider_ne' => 'काठमाडौं उपत्यका खानेपानी लिमिटेड',
        'schedule'    => $ad['items'] ?? [],
        'is_live'     => !empty($ad['items']),
        'source'      => $ad['source'] ?? 'KUKL',
        'source_url'  => $ad['source_url'] ?? 'https://kathmanduwater.org/',
        'contacts'    => [
            ['name'=>'KUKL Helpline','number'=>'1198'],
            ['name'=>'Complaint',    'number'=>'01-4413744'],
        ],
        'official_links' => [
            ['name'=>'KUKL Website',  'url'=>'https://kathmanduwater.org/'],
            ['name'=>'Bill Payment',  'url'=>'https://kathmanduwater.org/'],
        ],
        'note'        => 'क्षेत्रगत तालिका KUKL को अधिकृत सूचना अनुसार Admin ले प्रकाशन गर्छ।',
        'updatedAt'   => $ad['updatedAt'] ?? date('c'),
    ];
}

// ── Router ────────────────────────────────────────────────────────────
switch ($type) {
    case 'forex':                                  $out = getNrbForex();     break;
    case 'bank': case 'bank_rates':                $out = getBankRates();    break;
    case 'remittance': case 'remit':               $out = getRemittance();   break;
    case 'loadshedding': case 'electricity':       $out = getLoadShedding(); break;
    case 'loksewa': case 'psc':                    $out = getLokSewa();      break;
    case 'traffic':                                $out = getTraffic();      break;
    case 'water':                                  $out = getWater();        break;
    default:
        $out = [
            'forex'        => getNrbForex(),
            'bank_rates'   => getBankRates(),
            'remittance'   => getRemittance(),
            'loadshedding' => getLoadShedding(),
            'loksewa'      => getLokSewa(),
            'traffic'      => getTraffic(),
            'water'        => getWater(),
            'generatedAt'  => date('c'),
            'policy'       => 'आकाशवाणी le authentic / official Nepal source bata aaeko data matra publish garchha. Verified nabhayeko number kahile pani dekhaiidaina.',
        ];
}

// Aliases for older client code:
if (!empty($out['banks']))    $out['rates']   = $out['banks'];
if (!empty($out['schedule'])) $out['data']    = $out['schedule'];
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
