<?php
/**
 * Sahayak AI — Smart Nepal Assistant
 *
 * Always-on: even without an OpenAI key, answers common Nepali queries
 * (gold price, petrol, NEPSE, forex, rashifal, BS/AD date, news) from
 * the same LIVE data sources the rest of the site uses.
 *
 * If OPENAI_API_KEY is configured in config.php (or env), free-form
 * questions are streamed via OpenAI chat-completions. Otherwise the
 * local knowledge base handles every recognised intent and politely
 * declines unknown ones.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "data: " . json_encode(['error' => 'POST only']) . "\n\n";
    flush(); exit;
}

$body    = json_decode(file_get_contents('php://input'), true) ?: [];
$message = trim((string)($body['message'] ?? ''));
$lang    = ($body['lang'] ?? 'ne') === 'en' ? 'en' : 'ne';
$history = is_array($body['history'] ?? null) ? $body['history'] : [];

if ($message === '') {
    echo "data: " . json_encode(['error' => 'Empty message']) . "\n\n";
    flush(); exit;
}

function sse(array $payload): void {
    echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
    @ob_flush(); @flush();
}

function streamText(string $text): void {
    // simulate streaming by chunking
    $parts = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $p) {
        if ($p === '') continue;
        sse(['content' => $p]);
        usleep(12000);
    }
}

// ─── LIVE DATA HELPERS — reuse market-data API logic ─────────────────────────
function fetchMarket(string $type): ?array {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
         . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/api/market-data.php?type=' . $type;
    $ctx = stream_context_create(['http' => ['timeout' => 6, 'ignore_errors' => true]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    $d = json_decode($raw, true);
    return is_array($d) ? $d : null;
}

// ─── INTENT DETECTION (works in both languages) ──────────────────────────────
function detectIntent(string $msg): ?string {
    $m = mb_strtolower($msg, 'UTF-8');
    $map = [
        'gold'    => ['सुन', 'सुनको', 'gold', 'tola', 'तोला', 'hallmark', 'tajbi'],
        'petrol'  => ['पेट्रोल', 'डिजेल', 'मट्टितेल', 'ग्यास', 'petrol', 'diesel', 'kerosene', 'lpg', 'fuel'],
        'nepse'   => ['nepse', 'नेप्से', 'share', 'सेयर', 'index', 'stock', 'market'],
        'forex'   => ['forex', 'विदेशी मुद्रा', 'usd', 'डलर', 'दर', 'rate', 'exchange', 'euro', 'pound', 'riyal', 'dirham'],
        'rashifal'=> ['राशिफल', 'rashifal', 'horoscope', 'राशि', 'zodiac'],
        'patro'   => ['पात्रो', 'patro', 'bs', 'ad', 'मिति', 'date', 'tihar', 'dashain', 'चाड', 'पर्व'],
        'news'    => ['समाचार', 'news', 'खबर', 'headline'],
        'help'    => ['help', 'मद्दत', 'सहयोग', 'के गर्न', 'what can you', 'features', 'सुविधा'],
    ];
    foreach ($map as $intent => $keys) {
        foreach ($keys as $k) {
            if (mb_strpos($m, mb_strtolower($k, 'UTF-8')) !== false) return $intent;
        }
    }
    return null;
}

function answerGold(string $lang): ?string {
    $d = fetchMarket('gold');
    if (!$d) return null;
    $hm = number_format((float)$d['hallmarkPerTola']);
    $tj = number_format((float)$d['tajbiPerTola']);
    $g  = number_format((float)$d['hallmarkPerGram'], 2);
    $up = $d['updatedAt'] ?? date('Y-m-d H:i');
    if ($lang === 'en') {
        return "**Gold price in Nepal — live**\n\n"
             . "* **Hallmark / tola:** NPR {$hm}\n"
             . "* **Tajbi / tola:** NPR {$tj}\n"
             . "* **Hallmark / gram:** NPR {$g}\n\n"
             . "_Updated: {$up} · source: gold-api + NRB_";
    }
    return "**आजको सुनको भाउ (लाइभ)**\n\n"
         . "* **हलमार्क / तोला:** रू {$hm}\n"
         . "* **तेजाबी / तोला:** रू {$tj}\n"
         . "* **हलमार्क / ग्राम:** रू {$g}\n\n"
         . "_अपडेट: {$up}_";
}

function answerPetrol(string $lang): ?string {
    $d = fetchMarket('petrol');
    if (!$d) return null;
    if ($lang === 'en') {
        return "**Fuel price in Nepal (NOC)**\n\n"
             . "* Petrol: **NPR {$d['petrol']}** / litre\n"
             . "* Diesel: **NPR {$d['diesel']}** / litre\n"
             . "* Kerosene: **NPR {$d['kerosene']}** / litre\n"
             . "* LPG cylinder: **NPR " . number_format((float)$d['lpg_cylinder']) . "**\n\n"
             . "_Updated: {$d['updatedAt']} · source: {$d['source']}_";
    }
    return "**नेपालको ईन्धन मूल्य (NOC)**\n\n"
         . "* पेट्रोल: **रू {$d['petrol']}** / लिटर\n"
         . "* डिजेल: **रू {$d['diesel']}** / लिटर\n"
         . "* मट्टितेल: **रू {$d['kerosene']}** / लिटर\n"
         . "* LPG सिलिन्डर: **रू " . number_format((float)$d['lpg_cylinder']) . "**\n\n"
         . "_अपडेट: {$d['updatedAt']}_";
}

function answerNepse(string $lang): ?string {
    $d = fetchMarket('nepse');
    if (!$d) return null;
    $arrow = $d['change'] >= 0 ? '🟢 +' : '🔴 ';
    $idx = number_format((float)$d['index'], 2);
    $chg = number_format((float)$d['change'], 2);
    $pct = number_format((float)$d['changePercent'], 2);
    $to  = number_format(((float)$d['turnover']) / 1e7, 2);
    if ($lang === 'en') {
        return "**NEPSE — live market**\n\n"
             . "* Index: **{$idx}** ({$arrow}{$chg} / {$pct}%)\n"
             . "* Turnover: **NPR {$to} Cr**\n"
             . "* Advancers / Decliners: **{$d['positiveStocks']} / {$d['negativeStocks']}**\n\n"
             . "_Updated: {$d['updatedAt']}_";
    }
    return "**NEPSE — लाइभ बजार**\n\n"
         . "* सूचकांक: **{$idx}** ({$arrow}{$chg} / {$pct}%)\n"
         . "* कारोबार: **रू {$to} करोड**\n"
         . "* बढेका / घटेका: **{$d['positiveStocks']} / {$d['negativeStocks']}**\n\n"
         . "_अपडेट: {$d['updatedAt']}_";
}

function answerForex(string $lang): ?string {
    $d = fetchMarket('forex');
    if (!$d || empty($d['rates'])) return null;
    $top = array_slice($d['rates'], 0, 8);
    $rows = [];
    foreach ($top as $r) {
        $unit = $r['unit'] > 1 ? " ({$r['unit']})" : '';
        $rows[] = "* **{$r['code']}{$unit}** — Buy रू " . number_format((float)$r['buy'], 2)
                . " · Sell रू " . number_format((float)$r['sell'], 2);
    }
    $head = $lang === 'en' ? "**Forex rates (NRB)**" : "**विदेशी मुद्रा दर (NRB)**";
    return $head . "\n\n" . implode("\n", $rows) . "\n\n_Updated: {$d['updatedAt']}_";
}

function answerRashifal(string $lang): string {
    return $lang === 'en'
        ? "Today's full **Rashifal** for all 12 zodiac signs is on the [Rashifal page](/rashifal.php). Open it for your sign — predictions refresh daily."
        : "आजको पूर्ण **राशिफल** सबै १२ राशिको लागि [राशिफल पेज](/rashifal.php) मा उपलब्ध छ। आफ्नो राशि छान्नुस् — दैनिक अपडेट।";
}

function answerPatro(string $lang): string {
    $today = date('Y-m-d');
    return $lang === 'en'
        ? "**Nepali Patro** — BS/AD date converter, festivals (Tihar, Dashain), holidays and tithi are on the [Patro page](/nepali-patro.php).\n\n_Today (AD): {$today}_"
        : "**नेपाली पात्रो** — BS/AD मिति converter, चाडपर्व (तिहार, दशैं), बिदा र तिथि [पात्रो पेज](/nepali-patro.php) मा।\n\n_आज (AD): {$today}_";
}

function answerNews(string $lang): ?string {
    try {
        $news = function_exists('getPublishedNews') ? getPublishedNews(null, null, 5, 0) : [];
    } catch (\Throwable $e) { $news = []; }
    if (!$news) return null;
    $lines = [];
    foreach ($news as $n) {
        $lines[] = "* [" . trim($n['title']) . "](/news-post.php?slug=" . urlencode($n['slug']) . ")";
    }
    $h = $lang === 'en' ? "**Latest news**" : "**ताजा समाचार**";
    return $h . "\n\n" . implode("\n", $lines) . "\n\n[" . ($lang==='en'?'See all news':'सबै समाचार') . "](/news.php)";
}

function answerHelp(string $lang): string {
    if ($lang === 'en') {
        return "I'm **Sahayak AI** — I can answer about:\n\n"
             . "* 🪙 Gold price (hallmark / tajbi, per tola & gram)\n"
             . "* ⛽ Petrol / diesel / LPG price\n"
             . "* 📈 NEPSE index & market summary\n"
             . "* 💱 Forex rates (USD, EUR, GBP, AED…)\n"
             . "* 🔮 Daily Rashifal\n"
             . "* 📅 Nepali Patro / BS-AD date\n"
             . "* 📰 Latest AI & Nepal news\n\nJust ask in Nepali or English.";
    }
    return "म **सहायक AI** हुँ — मलाई सोध्न सक्नुहुन्छ:\n\n"
         . "* 🪙 सुनको भाउ (हलमार्क / तेजाबी, तोला / ग्राम)\n"
         . "* ⛽ पेट्रोल / डिजेल / LPG मूल्य\n"
         . "* 📈 NEPSE सूचकांक र बजार\n"
         . "* 💱 विदेशी मुद्रा दर (USD, EUR, GBP, AED…)\n"
         . "* 🔮 आजको राशिफल\n"
         . "* 📅 नेपाली पात्रो / BS-AD मिति\n"
         . "* 📰 ताजा AI र नेपाल समाचार\n\nनेपाली वा अंग्रेजी जुनसुकै भाषामा सोध्नुस्।";
}

// ─── Try local intent first (always works, no key needed) ────────────────────
$intent = detectIntent($message);
$local  = null;
if ($intent) {
    $local = match($intent) {
        'gold'     => answerGold($lang),
        'petrol'   => answerPetrol($lang),
        'nepse'    => answerNepse($lang),
        'forex'    => answerForex($lang),
        'rashifal' => answerRashifal($lang),
        'patro'    => answerPatro($lang),
        'news'     => answerNews($lang),
        'help'     => answerHelp($lang),
        default    => null,
    };
}

$apiKey  = defined('OPENAI_API_KEY')  ? OPENAI_API_KEY  : (getenv('OPENAI_API_KEY') ?: '');
$baseUrl = defined('OPENAI_BASE_URL') ? OPENAI_BASE_URL : 'https://api.openai.com/v1';
$model   = defined('OPENAI_MODEL')    ? OPENAI_MODEL    : 'gpt-4o-mini';

// If we have a local answer, send it. (Always live, no API cost.)
if ($local !== null) {
    streamText($local);
    sse(['done' => true]);
    exit;
}

// No matching intent → use OpenAI if key present
if (!$apiKey) {
    streamText($lang === 'en'
        ? answerHelp('en') . "\n\n_(Free-form chat needs an OpenAI key — see config.php → OPENAI_API_KEY.)_"
        : answerHelp('ne') . "\n\n_(अरू free-form प्रश्नका लागि config.php मा OPENAI_API_KEY थप्नुहोस्।)_");
    sse(['done' => true]);
    exit;
}

// ─── OpenAI streaming ────────────────────────────────────────────────────────
$systemPrompt = $lang === 'ne'
    ? "तपाईं आकाशवाणी को सहायक AI हो। नेपालीमा छोटो र सटिक जवाफ दिनुहोस्। Markdown प्रयोग गर्नुहोस्।"
    : "You are Sahayak AI for आकाशवाणी. Be concise, helpful and use Markdown.";

$messages = [['role' => 'system', 'content' => $systemPrompt]];
foreach (array_slice($history, -8) as $h) {
    if (!empty($h['role']) && !empty($h['content'])) {
        $messages[] = ['role' => $h['role'], 'content' => (string)$h['content']];
    }
}
$messages[] = ['role' => 'user', 'content' => $message];

$payload = json_encode([
    'model' => $model, 'messages' => $messages, 'stream' => true, 'max_tokens' => 1024,
]);

$ch = curl_init(rtrim($baseUrl, '/') . '/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 120,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
    CURLOPT_WRITEFUNCTION => function ($ch, $data) {
        foreach (explode("\n", $data) as $line) {
            $line = trim($line);
            if (!str_starts_with($line, 'data: ')) continue;
            $json = substr($line, 6);
            if ($json === '[DONE]') { sse(['done' => true]); return strlen($data); }
            $parsed = json_decode($json, true);
            $c = $parsed['choices'][0]['delta']['content'] ?? null;
            if ($c !== null) sse(['content' => $c]);
        }
        return strlen($data);
    },
]);
curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) sse(['content' => "\n\n❌ " . $err]);
sse(['done' => true]);
