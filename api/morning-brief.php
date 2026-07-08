<?php
/**
 * आकाशवाणी — Morning Brief API  v3
 * ─────────────────────────────────────────────────────────────────────────────
 * GET  /api/morning-brief.php          → return today's brief JSON
 * GET  /api/morning-brief.php?gen=1&key=CRON_KEY → force regenerate now
 *
 * Cache: data/cache/morning-brief-YYYY-MM-DD.json  (file, MySQL-safe)
 *
 * JSON output:
 * {
 *   "ok": true, "date": "2024-12-15", "generated_at": "...",
 *   "bullets": ["बुँदा १","बुँदा २","बुँदा ३","बुँदा ४","बुँदा ५"],
 *   "html": "<ul>...</ul>",
 *   "text": "plain text",
 *   "source": "ai" | "fallback"
 * }
 */
ini_set('max_execution_time', '60');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
if (file_exists(__DIR__ . '/../includes/bs-date.php')) require_once __DIR__ . '/../includes/bs-date.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=1800');   // 30 min browser cache
header('Access-Control-Allow-Origin: *');
header('X-Robots-Tag: noindex');

// ─── Config ──────────────────────────────────────────────────────────────────
$AI_KEY   = defined('OPENAI_API_KEY')  ? OPENAI_API_KEY  : (getenv('OPENAI_API_KEY') ?: '');
$AI_URL   = defined('OPENAI_BASE_URL') ? OPENAI_BASE_URL : 'https://api.openai.com/v1';
$AI_MODEL = defined('AI_MODEL')        ? AI_MODEL        : 'gpt-4o-mini';
$CRON_KEY = defined('CRON_KEY')        ? CRON_KEY        : '';

$today    = date('Y-m-d');
$cacheDir = rtrim(defined('CACHE_DIR') ? CACHE_DIR : __DIR__ . '/../data/cache', '/') . '/';
@mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . 'morning-brief-' . $today . '.json';

// ─── Auth: allow force-regen via key ─────────────────────────────────────────
$forceGen = false;
if (!empty($_GET['gen'])) {
    $reqKey = trim($_GET['key'] ?? '');
    if ($CRON_KEY && $reqKey === $CRON_KEY) $forceGen = true;
    elseif (!empty($_SESSION['is_admin']))   $forceGen = true;
}

// ─── Serve from cache if fresh ───────────────────────────────────────────────
if (!$forceGen && file_exists($cacheFile)) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if ($cached && ($cached['date'] ?? '') === $today) {
        echo json_encode($cached, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }
}

// ─── Helper: OpenAI call ─────────────────────────────────────────────────────
function callOpenAI(string $system, string $user, int $maxTokens = 500): ?string {
    global $AI_KEY, $AI_URL, $AI_MODEL;
    if (!$AI_KEY) return null;
    $payload = json_encode([
        'model'       => $AI_MODEL,
        'messages'    => [['role'=>'system','content'=>$system],['role'=>'user','content'=>$user]],
        'max_tokens'  => $maxTokens,
        'temperature' => 0.55,
        'response_format' => ['type' => 'json_object'],
    ]);
    $ch = curl_init(rtrim($AI_URL, '/') . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $AI_KEY,
            'Content-Type: application/json',
        ],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$resp) return null;
    $data = json_decode($resp, true);
    return $data['choices'][0]['message']['content'] ?? null;
}

// ─── Helper: Fetch today's top headlines from MySQL ──────────────────────────
function fetchTopNews(int $limit = 12): array {
    try {
        $pdo = db();
        if (!$pdo) return [];
        $stmt = $pdo->prepare(
            "SELECT title, excerpt, category FROM tech_news
             WHERE is_published=1 AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 2 DAY)
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) { return []; }
}

// ─── Helper: Get live market snapshot ────────────────────────────────────────
function liveMarketSnap(): string {
    // Try to read from market cache file
    $dirs = [
        __DIR__ . '/../data/cache/market-all.json',
        __DIR__ . '/../data/cache/nepse.json',
    ];
    foreach ($dirs as $f) {
        if (file_exists($f) && (time() - filemtime($f)) < 86400) {
            $d = json_decode(file_get_contents($f), true);
            if ($d) {
                $snap = [];
                if (!empty($d['index']))       $snap[] = 'NEPSE: ' . number_format($d['index'], 2);
                if (!empty($d['forex']['USD'])) $snap[] = 'USD: रू' . $d['forex']['USD'];
                if (!empty($d['gold']['hallmarkPerTola'])) $snap[] = 'सुन: रू' . number_format($d['gold']['hallmarkPerTola']) . '/तोला';
                if ($snap) return implode(' | ', $snap);
            }
        }
    }
    // Live fetch from internal API (fast, 8s timeout)
    $host   = ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $ctx    = stream_context_create(['http'=>['timeout'=>8,'ignore_errors'=>true]]);
    $raw    = @file_get_contents("$scheme://$host/api/market-data.php?type=all", false, $ctx);
    if ($raw) {
        $d = json_decode($raw, true);
        $snap = [];
        if (!empty($d['nepse']['index']))       $snap[] = 'NEPSE: ' . number_format($d['nepse']['index'], 2);
        if (!empty($d['forex'][0]['sell']))      $snap[] = 'USD: रू' . $d['forex'][0]['sell'];
        if (!empty($d['gold']['hallmarkPerTola'])) $snap[] = 'सुन: रू' . number_format($d['gold']['hallmarkPerTola']) . '/तोला';
        if ($snap) return implode(' | ', $snap);
    }
    return '';
}

// ─── Helper: Build Nepali weekday/BS date string ─────────────────────────────
function todayBsLabel(): string {
    $days = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहिबार','शुक्रबार','शनिबार'];
    $day  = $days[(int)date('w')];
    if (function_exists('adToBs') && function_exists('bsDate')) {
        $bs = bsDate(date('Y-m-d'));
        return $day . ', ' . $bs;
    }
    return $day . ', ' . date('Y-m-d');
}

// ─── Generate via OpenAI ─────────────────────────────────────────────────────
function generateWithAI(array $newsRows, string $market): ?array {
    $headlines = implode("\n", array_map(
        fn($r) => '• ' . $r['title'] . ($r['excerpt'] ? ' — ' . mb_substr($r['excerpt'], 0, 70) : ''),
        $newsRows
    ));

    $system = <<<PROMPT
तपाईं आकाशवाणी को AI सम्पादक हुनुहुन्छ। तलका आजका मुख्य समाचारहरूको आधारमा
"बिहानको १० सेकेन्ड ब्रिफिङ" तयार गर्नुहोस्।

नियमहरू:
- ठीक ५ वटा बुँदाहरू लेख्नुहोस् — हरेक बुँदा १ वाक्य, ३०-५० शब्द।
- सरल, स्पष्ट नेपाली भाषामा लेख्नुहोस् — पाठकलाई बुझ्न सजिलो होस्।
- महत्वपूर्ण खबर पहिले राख्नुहोस्।
- बजार/आर्थिक data भएमा एउटा बुँदा त्यसमा राख्नुहोस्।
- राजनीति, समाज, अर्थ, खेल, मनोरञ्जन — विविधता राख्नुहोस्।
- कुनै पनि AI हो भन्ने संकेत नदिनुहोस्।

JSON format:
{
  "bullets": ["बुँदा १", "बुँदा २", "बुँदा ३", "बुँदा ४", "बुँदा ५"],
  "summary": "एक वाक्यमा आजको दिनको सार (push notification को लागि, max 100 chars)"
}
PROMPT;

    $user = "आजका समाचार:\n$headlines";
    if ($market) $user .= "\n\nबजार snapshot: $market";

    $raw = callOpenAI($system, $user, 600);
    if (!$raw) return null;
    $parsed = json_decode($raw, true);
    if (empty($parsed['bullets']) || !is_array($parsed['bullets'])) return null;
    return $parsed;
}

// ─── Fallback: build brief from news DB without AI ───────────────────────────
function fallbackBrief(array $newsRows, string $market): array {
    $bullets = [];
    // Take top 5 news titles directly
    foreach (array_slice($newsRows, 0, 5) as $row) {
        $bullets[] = $row['title'] . ($row['excerpt'] ? ' — ' . mb_substr($row['excerpt'], 0, 60) . '।' : '।');
    }
    // If fewer than 5 news, add a market bullet
    if ($market && count($bullets) < 5) {
        $bullets[] = 'आजको बजार: ' . $market . '।';
    }
    // Pad if still short
    while (count($bullets) < 5) {
        $bullets[] = 'नेपालका ताजा समाचारको लागि आकाशवाणी हेर्नुहोस्।';
    }
    return [
        'bullets' => array_slice($bullets, 0, 5),
        'summary' => count($newsRows) > 0 ? mb_substr($newsRows[0]['title'], 0, 95) : 'नेपालका ताजा समाचार — आकाशवाणी',
    ];
}

// ─── Build final response ────────────────────────────────────────────────────
function buildResponse(array $data, string $source, string $dateLabel): array {
    $bullets = $data['bullets'];
    $html    = '<ul class="brief-bullets">';
    foreach ($bullets as $b) {
        $html .= '<li>' . htmlspecialchars($b, ENT_QUOTES) . '</li>';
    }
    $html .= '</ul>';
    if (!empty($data['summary'])) {
        $html .= '<p class="brief-summary">' . htmlspecialchars($data['summary'], ENT_QUOTES) . '</p>';
    }

    $text = implode(' • ', $bullets);
    if (!empty($data['summary'])) $text .= ' | ' . $data['summary'];

    return [
        'ok'           => true,
        'date'         => date('Y-m-d'),
        'date_label'   => $dateLabel,
        'generated_at' => date('c'),
        'bullets'      => $bullets,
        'summary'      => $data['summary'] ?? '',
        'html'         => $html,
        'text'         => mb_substr($text, 0, 280),
        'source'       => $source,
    ];
}

// ─── Main generation flow ────────────────────────────────────────────────────
$newsRows  = fetchTopNews(12);
$market    = liveMarketSnap();
$dateLabel = todayBsLabel();
$source    = 'fallback';
$data      = null;

// Try AI generation first
if ($AI_KEY && count($newsRows) >= 3) {
    $aiResult = generateWithAI($newsRows, $market);
    if ($aiResult) {
        $data   = $aiResult;
        $source = 'ai';
    }
}

// Fallback: use news titles directly (works even without OpenAI)
if (!$data) {
    $data = fallbackBrief($newsRows, $market);
}

$response = buildResponse($data, $source, $dateLabel);

// ─── Cache to file ───────────────────────────────────────────────────────────
file_put_contents($cacheFile, json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

// ─── Clean old cache files (keep last 7 days) ────────────────────────────────
foreach (glob($cacheDir . 'morning-brief-*.json') as $f) {
    $fname = basename($f, '.json');
    $fdate = str_replace('morning-brief-', '', $fname);
    if ($fdate < date('Y-m-d', strtotime('-7 days'))) @unlink($f);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
