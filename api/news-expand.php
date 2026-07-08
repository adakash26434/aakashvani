<?php
/**
 * News Expand API v2 — AI-powered full article expansion
 *
 * POST /api/news-expand.php
 * Body: { title, slug?, excerpt?, lang }
 * Auth: Admin session OR CRON_KEY
 *
 * Priority:
 *  1. If slug provided → load stored DB content → AI polish → return
 *  2. If no slug → use title+excerpt → AI write full article
 *  3. No AI key → return structured HTML from stored content or excerpt
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
csrfRequire();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// ── Auth: Admin session OR CRON_KEY ─────────────────────────────────────────────
$cronKey = defined('CRON_KEY') ? CRON_KEY : '';
$reqKey  = trim($_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '');
$hasKey   = $cronKey && $reqKey === $cronKey;
$hasAdmin = isAdmin();

if (!$hasKey && !$hasAdmin) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized — admin session or CRON_KEY required']);
    return;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['error'=>'POST only']); return;
}

$body   = json_decode(file_get_contents('php://input'), true);
$title  = trim($body['title']  ?? '');
$slug   = trim($body['slug']   ?? '');
$excerpt= trim($body['excerpt']?? '');
$lang   = ($body['lang'] ?? 'ne') === 'en' ? 'en' : 'ne';

if (!$title) { http_response_code(400); echo json_encode(['error'=>'title required']); return; }

$apiKey  = defined('OPENAI_API_KEY')  ? OPENAI_API_KEY  : '';
$baseUrl = defined('OPENAI_BASE_URL') ? OPENAI_BASE_URL : 'https://api.openai.com/v1';
$model   = defined('AI_MODEL')        ? AI_MODEL        : 'gpt-4o-mini';

// ── Load stored full content from DB if slug given ────────────────────────────
$storedContent = '';
$sourceUrl     = '';
$sourceName    = '';
if ($slug) {
    try {
        $row = db()->prepare("SELECT content, excerpt, source, source_url, source_name, ai_processed FROM tech_news WHERE slug=? LIMIT 1");
        $row->execute([$slug]);
        $r = $row->fetch();
        if ($r) {
            $storedContent = trim($r['content'] ?? '');
            if (!$excerpt) $excerpt = trim($r['excerpt'] ?? '');
            $sourceUrl  = trim($r['source_url']  ?? '');
            $sourceName = trim($r['source_name'] ?? ($r['source'] ?? ''));
            
            // If we have substantial scraped content (more than 200 chars), return it directly
            // Don't use AI to shorten it
            if (mb_strlen($storedContent) > 200) {
                echo json_encode([
                    'content'     => formatAsHtml($title, $storedContent, $lang),
                    'source'      => 'scraped',
                    'source_url'  => $sourceUrl,
                    'source_name' => $sourceName,
                    'word_count'  => str_word_count($storedContent),
                    'note'        => 'full_article_from_source'
                ]);
                return;
            }

            // FIX: DB content too short — try LIVE scrape from original URL before falling back to AI
            if ($sourceUrl && filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
                @require_once __DIR__ . '/../includes/article-fetch.php';
                if (function_exists('aakFetchArticle')) {
                    try {
                        $fetched = aakFetchArticle($sourceUrl);
                        $scraped = trim($fetched['plain'] ?? implode("\n\n", $fetched['paragraphs'] ?? []));
                        if (mb_strlen($scraped) > 300) {
                            // Backfill DB so future requests are instant
                            try {
                                $up = db()->prepare("UPDATE tech_news SET content=?, ai_processed=1 WHERE slug=?");
                                $up->execute([$scraped, $slug]);
                            } catch(\Throwable $e) {}
                            echo json_encode([
                                'content'     => formatAsHtml($title, $scraped, $lang),
                                'source'      => 'live_scrape',
                                'source_url'  => $sourceUrl,
                                'source_name' => $sourceName,
                                'word_count'  => str_word_count($scraped),
                                'note'        => 'full_article_live_fetched'
                            ]);
                            return;
                        }
                    } catch(\Throwable $e) {}
                }
            }
        }
    } catch(\Throwable $e) {}
}

// ── Build context for AI ──────────────────────────────────────────────────────
// Use stored content (richest), then excerpt, never raw source name
$context = $storedContent ?: $excerpt;
$contextPreview = mb_substr($context, 0, 2500);

// ── No API key — return stored content as HTML ────────────────────────────────
if (empty($apiKey)) {
    echo json_encode([
        'content'     => formatAsHtml($title, $context, $lang),
        'source'      => 'stored',
        'source_url'  => $sourceUrl,
        'source_name' => $sourceName,
    ]);
    return;
}

// ── System prompts for FULL article rewrite ───────────────────────────────────
if ($lang === 'ne') {
    $sys = 'तपाईं एक अनुभवी नेपाली पत्रकार हुनुहुन्छ। तलको समाचार शीर्षक र सामग्रीका आधारमा पूर्ण, विस्तृत समाचार लेख्नुहोस्। कडा नियम: (क) दिएको सामग्रीमा नभएको कुनै तथ्य, संख्या, उद्धरण वा नाम कहिल्यै नथप्नुस् — काल्पनिक तथ्य निषेध। (ख) अनिश्चित कुरा "स्रोतले भनेअनुसार" वा "रिपोर्ट अनुसार" भनी लेख्नुस्। (ग) HTML मा (<p>, <h3>, <strong>) — ६००–८०० शब्द। (घ) पाठकलाई पूर्ण जानकारी दिने गरी विस्तृत लेख्नुस्। (ङ) अन्त्यमा "विस्तृत विवरणका लागि मूल स्रोतमा जानुहोस्" भन्नुस्।';
    $user = "शीर्षक: $title\n\nसामग्री:\n$contextPreview";
} else {
    $sys = 'You are an experienced journalist. Write a FULL detailed article based ONLY on the provided title and context. STRICT RULES: (a) NEVER invent facts, numbers, quotes or names not present in the source — no fabrication. (b) Attribute uncertain claims with "according to reports". (c) Use HTML (<p>, <h3>, <strong>) — 600-800 words. (d) Provide complete information to the reader. (e) End by directing readers to the original source for full details.';
    $user = "Title: $title\n\nContext:\n$contextPreview";
}

$payload = json_encode([
    'model'       => $model,
    'messages'    => [['role'=>'system','content'=>$sys],['role'=>'user','content'=>$user]],
    'max_tokens'  => 2500,
    'temperature' => 0.65,
]);

$ch = curl_init(rtrim($baseUrl,'/').'/chat/completions');
curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload,
    CURLOPT_TIMEOUT=>30,
    CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$apiKey,'Content-Type: application/json'],
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$resp || $code !== 200) {
    echo json_encode([
        'content'     => formatAsHtml($title,$context,$lang),
        'source'      => 'stored',
        'source_url'  => $sourceUrl,
        'source_name' => $sourceName,
    ]);
    return;
}

$data = json_decode($resp, true);
$text = trim($data['choices'][0]['message']['content'] ?? '');

echo json_encode([
    'content'     => $text ?: formatAsHtml($title,$context,$lang),
    'source'      => $text ? 'ai' : 'stored',
    'source_url'  => $sourceUrl,
    'source_name' => $sourceName,
]);

// ── Format stored content as readable HTML (no AI needed) ─────────────────────
function formatAsHtml(string $title, string $content, string $lang): string {
    if (!$content) {
        $msg = $lang==='ne'
            ? 'यो समाचारको विस्तृत विवरण अहिले उपलब्ध छैन। मूल स्रोतमा पढ्नुहोस्।'
            : 'Detailed content is not available for this article. Please read from the original source.';
        return "<p class=\"text-slate-500 italic\">$msg</p>";
    }

    // If content already has HTML tags, return sanitised
    if (preg_match('/<[a-z][\s\S]*>/i', $content)) {
        $clean = strip_tags($content, '<p><b><strong><i><em><h3><ul><ol><li><blockquote><br>');
        // Ensure we have paragraphs, not just breaks
        $clean = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/i', '</p><p>', $clean);
        if (!preg_match('/<p>/i', $clean)) {
            $clean = '<p>' . $clean . '</p>';
        }
        return $clean;
    }

    // Plain text → paragraphs
    $paras = preg_split('/\n\n+|\r\n\r\n+/u', $content);
    $html  = '';
    foreach ($paras as $p) {
        $p = trim($p);
        if (mb_strlen($p) > 5) {
            // Wrap in paragraph with proper styling
            $html .= '<p class="leading-relaxed mb-4">'.htmlspecialchars($p,ENT_QUOTES,'UTF-8').'</p>'."\n";
        }
    }
    
    // If no paragraphs were created, wrap entire content
    if (!$html) {
        $html = '<p class="leading-relaxed">'.htmlspecialchars($content,ENT_QUOTES,'UTF-8').'</p>';
    }
    
    return $html;
}
