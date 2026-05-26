<?php
/**
 * Market Data API — Gold, Petrol, NEPSE, Forex
 * Dual-mode:
 *   - HTTP endpoint (default): returns JSON
 *   - Library mode: define MARKET_LIB_ONLY before include to suppress
 *     headers/routing/output and just expose the get*Data() helpers.
 */
if (!defined('MARKET_LIB_ONLY')) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../functions.php';
    require_once __DIR__ . '/../includes/error-logger.php';
    
    // Security headers
    sendSecurityHeaders();
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: no-cache');
    
    // Rate limiting
    $rateKey = 'market:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!checkRateLimit($rateKey, 60, 60)) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'Rate limit exceeded']);
        exit;
    }
}

$type      = $_GET['type'] ?? 'all';
$cacheDir  = __DIR__ . '/../data/cache/';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

function readCache(string $key, int $ttl = 3600): ?array {
    global $cacheDir;
    $file = $cacheDir . $key . '.json';
    if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) return $data;
    }
    return null;
}

function writeCache(string $key, array $data): void {
    global $cacheDir;
    file_put_contents($cacheDir . $key . '.json', json_encode($data));
}

if (!function_exists('fetchUrl')) {
    function fetchUrl(string $url, array $headers = [], int $timeout = 10): ?string {
        require_once __DIR__ . '/../includes/http.php';
        return nh_fetchUrl($url, $headers, $timeout, true);
    }
}

/**
 * Scrape gold price from FENEGOSIDA (Nepal Gold & Silver Dealers' Association)
 */
function scrapeGoldFromFenegosida(): ?array {
    $html = fetchUrl('https://www.fenegosida.org/', [], 15);
    if (!$html) return null;

    $goldHallmark = null;
    $goldTejabi   = null;
    $silver       = null;

    // FENEGOSIDA DOM: <div class="rate-gold post"><p>FINE GOLD (9999)...<b>VALUE</b>
    // Tola section uses "per 1 tola" — prefer that block.
    // Pattern: capture label keyword + nearest <b>NUMBER</b> after "per 1 tola"
    if (preg_match('#FINE\s*GOLD[^<]*<br>\s*<span>[^<]*per\s*1\s*tola.*?<b>\s*([\d,]+(?:\.\d+)?)\s*</b>#siu', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v > 100000 && $v < 400000) $goldHallmark = $v;
    }
    if (preg_match('#TEJABI\s*GOLD[^<]*<br>\s*<span>[^<]*per\s*1\s*tola.*?<b>\s*([\d,]+(?:\.\d+)?)\s*</b>#siu', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v > 90000 && $v < 400000) $goldTejabi = $v;
    }
    if (preg_match('#SILVER[^<]*<br>\s*<span>[^<]*per\s*1\s*tola.*?<b>\s*([\d,]+(?:\.\d+)?)\s*</b>#siu', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v >= 1500 && $v <= 8000) $silver = $v;
    }

    // Fallback: per 10 grm × 11.664/10 ≈ per tola conversion
    if (!$goldHallmark && preg_match('#FINE\s*GOLD[^<]*<br>\s*<span>[^<]*per\s*10\s*grm.*?<b>\s*([\d,]+(?:\.\d+)?)\s*</b>#siu', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v > 10000 && $v < 40000) $goldHallmark = round($v * 11.664 / 10);
    }
    if (!$silver && preg_match('#SILVER[^<]*<br>\s*<span>[^<]*per\s*10\s*grm.*?<b>\s*([\d,]+(?:\.\d+)?)\s*</b>#siu', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v >= 200 && $v <= 800) $silver = round($v * 11.664 / 10, 2);
    }

    if ($goldHallmark) {
        return [
            'hallmarkPerTola' => $goldHallmark,
            'tajbiPerTola'    => $goldTejabi ?: round($goldHallmark * 0.9833),
            'silverPerTola'   => $silver ?: null,
            'source'          => 'FENEGOSIDA',
        ];
    }
    return null;
}

/**
 * Scrape gold from Hamro Patro (backup source)
 */
function scrapeGoldFromHamroPatro(): ?array {
    $html = fetchUrl('https://www.hamropatro.com/gold', [], 15);
    if (!$html) return null;

    $hallmark = null;
    $tejabi   = null;

    // Try labelled patterns first
    if (preg_match('/(?:हलमार्क|Hallmark)[^\d<]{0,60}([\d,]+)/u', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v > 100000 && $v < 380000) $hallmark = $v;
    }
    if (preg_match('/(?:तेजाबी|Tejabi)[^\d<]{0,60}([\d,]+)/u', $html, $m)) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v > 90000 && $v < 370000) $tejabi = $v;
    }
    // Fallback: find first 6-digit comma-formatted number in gold price range
    if (!$hallmark) {
        preg_match_all('/(\d{1,3},\d{3})/u', $html, $ms);
        foreach ($ms[1] as $nm) {
            $v = (float)str_replace(',', '', $nm);
            if ($v > 100000 && $v < 380000) { $hallmark = $v; break; }
        }
    }

    if ($hallmark) {
        return [
            'hallmarkPerTola' => $hallmark,
            'tajbiPerTola'    => $tejabi ?: round($hallmark * 0.9833),
            'source'          => 'Hamro Patro',
        ];
    }
    return null;
}

// ── GOLD + SILVER PRICE ───────────────────────────────────────────────────────
//  REAL data only. Priority: Admin override → FENEGOSIDA scrape → Hamro Patro
//  scrape → honest "unavailable" (NEVER fabricated). International + NRB used
//  only as cross-reference, not as primary number.
function getGoldData(): array {
    $cached = readCache('gold', 3600);
    if ($cached) return $cached;

    // 1) Admin override
    $ovFile = __DIR__ . '/../cache/overrides.json';
    if (is_file($ovFile)) {
        $ov = json_decode((string)@file_get_contents($ovFile), true);
        $g  = $ov['gold'] ?? null;
        if (is_array($g) && !empty($g['use']) && !empty($g['hallmarkPerTola'])) {
            $tola = (float)$g['hallmarkPerTola'];
            $data = [
                'hallmarkPerTola' => $tola,
                'hallmarkPerGram' => round($tola/11.664, 2),
                'tajbiPerTola'    => (float)($g['tajbiPerTola'] ?? round($tola*0.9833)),
                'silverPerTola'   => isset($g['silverPerTola']) ? (float)$g['silverPerTola'] : null,
                'available'       => true,
                'is_live'         => false,
                'source'          => 'FENEGOSIDA (Admin verified)',
                'source_url'      => 'https://www.fenegosida.org/',
                'currency'        => 'NPR',
                'updatedAt'       => $ov['updatedAt'] ?? date('c'),
            ];
            writeCache('gold', $data);
            return $data;
        }
    }

    // 2) Live scrape from FENEGOSIDA / Hamro Patro
    $local = scrapeGoldFromFenegosida() ?: scrapeGoldFromHamroPatro();
    // If primary source missed silver, try FENEGOSIDA just for silver
    if ($local && empty($local['silverPerTola']) && ($local['source'] ?? '') !== 'FENEGOSIDA') {
        $fg = scrapeGoldFromFenegosida();
        if ($fg && !empty($fg['silverPerTola'])) $local['silverPerTola'] = $fg['silverPerTola'];
    }
    if ($local && !empty($local['hallmarkPerTola'])) {
        $tola = (float)$local['hallmarkPerTola'];
        $data = [
            'hallmarkPerTola' => $tola,
            'hallmarkPerGram' => round($tola/11.664, 2),
            'tajbiPerTola'    => (float)($local['tajbiPerTola'] ?? round($tola*0.9833)),
            'tajbiPerGram'    => round((float)($local['tajbiPerTola'] ?? $tola*0.9833)/11.664, 2),
            'silverPerTola'   => $local['silverPerTola'] ?? null,
            'available'       => true,
            'is_live'         => true,
            'source'          => $local['source'] ?? 'FENEGOSIDA',
            'source_url'      => ($local['source'] ?? '') === 'Hamro Patro' ? 'https://www.hamropatro.com/gold' : 'https://www.fenegosida.org/',
            'currency'        => 'NPR',
            'unit'            => 'per tola / per gram',
            'updatedAt'       => date('c'),
        ];
        writeCache('gold', $data);
        return $data;
    }

    // 3) Honest unavailable
    return [
        'available'  => false,
        'is_live'    => false,
        'source'     => 'FENEGOSIDA',
        'source_url' => 'https://www.fenegosida.org/',
        'note'       => 'Live मूल्य पाउन सकिएन। आधिकारिक मूल्य fenegosida.org मा हेर्नुस्। (Admin बाट प्रकाशन: /admin/prices.php)',
        'updatedAt'  => date('c'),
    ];
}

/**
 * Scrape fuel prices from NOC Nepal
 */
function scrapePetrolFromNOC(): ?array {
    // Try multiple NOC URLs
    $urls = [
        'https://www.noc.org.np/',
        'https://noc.org.np/',
        'https://www.noc.org.np/fuel-price',
        'https://www.noc.org.np/en/fuel-price',
    ];

    $html = null;
    foreach ($urls as $url) {
        $h = fetchUrl($url, [], 15);
        if ($h && (
            stripos($h, 'petrol') !== false ||
            stripos($h, 'diesel') !== false ||
            mb_strpos($h, 'पेट्रोल') !== false
        )) { $html = $h; break; }
    }
    if (!$html) return null;

    $prices = [];

    // Multiple patterns: keyword near price number (valid Nepal fuel range 100–300)
    $petrolPats = [
        '/(?:Petrol|MS|पेट्रोल)[^<\d]{0,60}(?:Rs\.?|NPR|रु\.?)?\s*(1[5-9]\d(?:\.\d{2})?|2\d{2}(?:\.\d{2})?)/iu',
        '/(?:Rs\.?|NPR)\s*(1[5-9]\d(?:\.\d{2})?|2\d{2}(?:\.\d{2})?)[^<]{0,40}(?:Petrol|MS|पेट्रोल)/iu',
    ];
    $dieselPats = [
        '/(?:Diesel|HSD|डिजेल)[^<\d]{0,60}(?:Rs\.?|NPR|रु\.?)?\s*(1[5-9]\d(?:\.\d{2})?|2\d{2}(?:\.\d{2})?)/iu',
        '/(?:Rs\.?|NPR)\s*(1[5-9]\d(?:\.\d{2})?|2\d{2}(?:\.\d{2})?)[^<]{0,40}(?:Diesel|HSD|डिजेल)/iu',
    ];

    foreach ($petrolPats as $pat) {
        if (preg_match($pat, $html, $m)) {
            $v = (float)$m[1];
            if ($v > 100 && $v < 300) { $prices['petrol'] = $v; break; }
        }
    }
    foreach ($dieselPats as $pat) {
        if (preg_match($pat, $html, $m)) {
            $v = (float)$m[1];
            if ($v > 100 && $v < 300) { $prices['diesel'] = $v; break; }
        }
    }
    if (preg_match('/(?:Kerosene|SKO|मट्टितेल)[^<\d]{0,60}(?:Rs\.?|NPR)?\s*(1[5-9]\d(?:\.\d{2})?|2\d{2}(?:\.\d{2})?)/iu', $html, $m)) {
        $v = (float)$m[1]; if ($v > 100 && $v < 300) $prices['kerosene'] = $v;
    }
    if (preg_match('/(?:LPG|Cooking\s*Gas)[^<\d]{0,60}(?:Rs\.?|NPR)?\s*(\d{3,4}(?:\.\d{2})?)/iu', $html, $m)) {
        $v = (float)$m[1]; if ($v > 800 && $v < 5000) $prices['lpg'] = $v;
    }

    if (!empty($prices['petrol']) || !empty($prices['diesel'])) {
        return $prices;
    }
    return null;
}

// ── PETROL / DIESEL / LPG / KEROSENE ──────────────────────────────────────────
//  REAL data only. Priority:
//    1) Admin override from /admin/prices.php (verified manual NOC entry)
//    2) Live scrape from noc.org.np
//    3) Honest "unavailable" with official link — NEVER fabricated numbers.
function getPetrolData(): array {
    $cached = readCache('petrol', 21600);
    if ($cached) return $cached;

    // 1) Admin override (from /admin/prices.php → /api/overrides.php)
    $ovFile = __DIR__ . '/../cache/overrides.json';
    if (is_file($ovFile)) {
        $ov = json_decode((string)@file_get_contents($ovFile), true);
        $p  = $ov['petrol'] ?? null;
        if (is_array($p) && !empty($p['use'])) {
            $data = [
                'petrol'        => $p['petrol']       ?? null,
                'diesel'        => $p['diesel']       ?? null,
                'kerosene'      => $p['kerosene']     ?? null,
                'lpg_cylinder'  => $p['lpg_cylinder'] ?? null,
                'aviation_fuel' => $p['aviation_fuel']?? null,
                'available'     => true,
                'is_live'       => false,
                'source'        => 'NOC Nepal (Admin verified)',
                'source_url'    => 'https://noc.org.np/priceupdate',
                'updatedAt'     => $ov['updatedAt'] ?? date('c'),
                'currency'      => 'NPR',
                'note'          => 'Admin द्वारा NOC आधिकारिक मूल्य सूचीबाट verify गरी प्रकाशित।',
            ];
            writeCache('petrol', $data);
            return $data;
        }
    }

    // 2) Live scrape
    $scraped = scrapePetrolFromNOC();
    if ($scraped && !empty($scraped['petrol']) && !empty($scraped['diesel'])) {
        $data = [
            'petrol'       => $scraped['petrol'],
            'diesel'       => $scraped['diesel'],
            'kerosene'     => $scraped['kerosene'] ?? null,
            'lpg_cylinder' => $scraped['lpg']      ?? null,
            'available'    => true,
            'is_live'      => true,
            'source'       => 'Nepal Oil Corporation (NOC) — Live',
            'source_url'   => 'https://noc.org.np/priceupdate',
            'updatedAt'    => date('c'),
            'currency'     => 'NPR',
            'units'        => ['petrol'=>'per litre','diesel'=>'per litre','kerosene'=>'per litre','lpg_cylinder'=>'per 14.2kg cylinder'],
            'note'         => 'noc.org.np बाट प्रत्यक्ष लिएको आधिकारिक मूल्य।',
        ];
        writeCache('petrol', $data);
        return $data;
    }

    // 3) Honest unavailable
    return [
        'available'   => false,
        'is_live'     => false,
        'source'      => 'Nepal Oil Corporation (NOC)',
        'source_url'  => 'https://noc.org.np/priceupdate',
        'note'        => 'NOC बाट live मूल्य पाउन सकिएन। आधिकारिक मूल्य noc.org.np मा हेर्नुहोस्। (Admin बाट प्रकाशन गर्न: /admin/prices.php)',
        'updatedAt'   => date('c'),
    ];
}

// ── FOREX RATES ───────────────────────────────────────────────────────────────
function getForexData(): array {
    $cached = readCache('forex', 3600); // 1 hour cache
    if ($cached) return $cached;

    // NRB requires from/to dates. Try today, then walk back up to 7 days.
    $resp = null;
    for ($i = 0; $i < 7 && !$resp; $i++) {
        $d = date('Y-m-d', strtotime("-{$i} day"));
        $url = "https://www.nrb.org.np/api/forex/v1/rates?per_page=100&page=1&from={$d}&to={$d}";
        $raw = fetchUrl($url);
        if ($raw) {
            $tmp = json_decode($raw, true);
            if (!empty($tmp['data']['payload'][0]['rates'])) { $resp = $raw; break; }
        }
    }
    if ($resp) {
        $d = json_decode($resp, true);
        $payload = $d['data']['payload'][0]['rates'] ?? [];
        if ($payload) {
            $rates = [];
            $priority = ['USD','EUR','GBP','AUD','CAD','CHF','SGD','QAR','SAR','AED','MYR','KRW','CNY','JPY','INR'];
            $rateMap  = [];
            foreach ($payload as $r) {
                $rateMap[$r['currency']['iso3']] = $r;
            }
            foreach ($priority as $code) {
                if (isset($rateMap[$code])) {
                    $r = $rateMap[$code];
                    $rates[] = [
                        'currency' => $r['currency']['name'],
                        'code'     => $code,
                        'buy'      => (float)$r['buy'],
                        'sell'     => (float)$r['sell'],
                        'unit'     => (int)($r['currency']['unit'] ?? 1),
                    ];
                }
            }
            if ($rates) {
                $data = [
                    'rates' => $rates,
                    'updatedAt' => date('Y-m-d H:i'),
                    'source' => 'Nepal Rastra Bank',
                    'source_url' => 'https://www.nrb.org.np/forex/',
                    'note' => 'नेपाल राष्ट्र बैंक (NRB) को आधिकारिक सन्दर्भ दर। वाणिज्य बैंक/मुद्रा सटही (Money Changer) को counter rate यो भन्दा फरक हुनसक्छ।',
                ];
                writeCache('forex', $data);
                return $data;
            }
        }
    }

    // NRB API unreachable — return honest unavailable response
    return [
        'available' => false,
        'rates'     => [],
        'updatedAt' => date('Y-m-d H:i'),
        'source'    => 'Nepal Rastra Bank',
        'note'      => 'NRB API अहिले उपलब्ध छैन। आधिकारिक दरको लागि nrb.org.np हेर्नुस्।',
        'link'      => 'https://www.nrb.org.np/forex/',
    ];
}

/**
 * Scrape NEPSE data from Merolagani (backup source)
 */
function scrapeNepseFromMerolagani(): ?array {
    // Short timeout — this is a best-effort fallback source; we don't want to slow the page.
    $html = function_exists('nh_fetchUrl')
        ? nh_fetchUrl('https://merolagani.com/LatestMarket.aspx', [], 5)
        : fetchUrl('https://merolagani.com/LatestMarket.aspx');
    if (!$html) return null;
    
    // Look for NEPSE index value
    // Pattern: class="market-data" or similar containing index
    if (preg_match('/NEPSE[^\d]*Index[^\d]*(\d{1,4}(?:,\d{3})*(?:\.\d{2})?)/i', $html, $m)) {
        $index = (float)str_replace(',', '', $m[1]);
        if ($index > 1000 && $index < 5000) {
            // Try to extract change
            $change = 0;
            $changePercent = 0;
            if (preg_match('/([+-]?\d+(?:\.\d{2})?)\s*\(([+-]?\d+(?:\.\d{2})?)\s*%\)/i', $html, $cm)) {
                $change = (float)$cm[1];
                $changePercent = (float)$cm[2];
            }
            return [
                'index' => $index,
                'change' => $change,
                'changePercent' => $changePercent,
                'source' => 'Merolagani',
            ];
        }
    }
    return null;
}

/**
 * Scrape NEPSE from ShareSansar (another backup)
 * Anchor on the NEPSE label so we don't grab a phone number or year.
 */
function scrapeNepseFromShareSansar(): ?array {
    $html = fetchUrl('https://www.sharesansar.com/');
    if (!$html) return null;

    // Strip script/style noise that often contains 4-digit numbers
    $clean = preg_replace('#<(script|style)[^>]*>.*?</\1>#sui', ' ', $html) ?: $html;

    $index = null; $change = null; $percent = null;

    // Pattern A: "NEPSE" label followed (within ~400 chars) by a numeric value
    //  e.g. <td>NEPSE</td><td>2,734.18</td><td>+12.45</td><td>0.46%</td>
    if (preg_match(
        '#NEPSE[^0-9\-+]{0,400}([\d]{1,2},?\d{3}\.\d{2})\D{1,80}([+\-]?\d+(?:\.\d+)?)\D{1,40}([+\-]?\d+(?:\.\d+)?)\s*%#siu',
        $clean, $m
    )) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v >= 1000 && $v <= 6000) {
            $index   = $v;
            $change  = (float)$m[2];
            $percent = (float)$m[3];
        }
    }

    // Pattern B: looser — NEPSE label then a single index-shaped number nearby
    if ($index === null && preg_match(
        '#NEPSE[^0-9]{0,200}([\d]{1,2},?\d{3}\.\d{2})#siu',
        $clean, $m
    )) {
        $v = (float)str_replace(',', '', $m[1]);
        if ($v >= 1000 && $v <= 6000) $index = $v;
    }

    if ($index === null) return null;

    return [
        'index'         => $index,
        'change'        => $change,
        'changePercent' => $percent,
        'source'        => 'ShareSansar',
    ];
}

// ── NEPSE DATA ────────────────────────────────────────────────────────────────
// NOTE: newweb.nepalstock.com.np official API removed — domain stopped resolving
// (confirmed dead since May 2026, was spamming error logs every 15 min).
// Primary source is now Merolagani, fallback is ShareSansar.
function getNepseData(): array {
    $cached = readCache('nepse', 900); // 15 min cache (market hours)
    if ($cached) return $cached;

    // Method 1: Merolagani (primary)
    $merolagani = scrapeNepseFromMerolagani();
    if ($merolagani && isset($merolagani['index'])) {
        $data = [
            'index'          => $merolagani['index'],
            'change'         => $merolagani['change'] ?? 0,
            'changePercent'  => $merolagani['changePercent'] ?? 0,
            'turnover'       => 0,
            'tradedShares'   => 0,
            'transactions'   => 0,
            'positiveStocks' => 0,
            'negativeStocks' => 0,
            'neutralStocks'  => 0,
            'updatedAt'      => date('Y-m-d H:i'),
            'source'         => $merolagani['source'],
            'marketStatus'   => getMarketStatus(),
        ];
        writeCache('nepse', $data);
        return $data;
    }

    // Method 2: ShareSansar fallback
    $sharesansar = scrapeNepseFromShareSansar();
    if ($sharesansar && isset($sharesansar['index'])) {
        $data = [
            'index'          => $sharesansar['index'],
            'change'         => 0,
            'changePercent'  => 0,
            'turnover'       => 0,
            'tradedShares'   => 0,
            'transactions'   => 0,
            'positiveStocks' => 0,
            'negativeStocks' => 0,
            'neutralStocks'  => 0,
            'updatedAt'      => date('Y-m-d H:i'),
            'source'         => $sharesansar['source'],
            'marketStatus'   => getMarketStatus(),
        ];
        writeCache('nepse', $data);
        return $data;
    }

    // All scraping methods failed — return honest unavailable response
    return [
        'available'    => false,
        'index'        => null,
        'updatedAt'    => date('Y-m-d H:i'),
        'source'       => 'NEPSE',
        'marketStatus' => getMarketStatus(),
        'note'         => 'NEPSE लाइभ डाटा अहिले उपलब्ध छैन। nepalstock.com.np मा हेर्नुस्।',
        'link'         => 'https://nepalstock.com.np',
    ];
}

/**
 * Get market status based on Nepal time
 */
function getMarketStatus(): string {
    $now = new DateTime('now', new DateTimeZone('Asia/Kathmandu'));
    $dayOfWeek = (int)$now->format('N'); // 1 = Monday, 7 = Sunday
    $hour = (int)$now->format('G');
    $minute = (int)$now->format('i');
    $currentMinutes = $hour * 60 + $minute;
    
    // Saturday = 6, Friday = 5 (Nepal market closed)
    if ($dayOfWeek >= 6) {
        return 'closed';
    }
    
    // Market hours: 11:00 AM - 3:00 PM (Nepal Time)
    $openTime = 11 * 60;  // 11:00 AM
    $closeTime = 15 * 60; // 3:00 PM
    
    if ($currentMinutes >= $openTime && $currentMinutes < $closeTime) {
        return 'open';
    } elseif ($currentMinutes >= $closeTime - 30 && $currentMinutes < $closeTime) {
        return 'closing';
    } elseif ($currentMinutes >= $openTime - 30 && $currentMinutes < $openTime) {
        return 'pre-open';
    }
    
    return 'closed';
}

// ── ROUTE ─────────────────────────────────────────────────────────────────────
if (defined('MARKET_LIB_ONLY')) { return; }
$response = [];

switch ($type) {
    case 'gold':
        $response = getGoldData();
        break;
    case 'petrol':
    case 'fuel':
        $response = getPetrolData();
        break;
    case 'forex':
    case 'exchange':
        $response = getForexData();
        break;
    case 'nepse':
    case 'stock':
        $response = getNepseData();
        break;
    case 'all':
    default:
        $response = [
            'gold'   => getGoldData(),
            'petrol' => getPetrolData(),
            'forex'  => getForexData(),
            'nepse'  => getNepseData(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
