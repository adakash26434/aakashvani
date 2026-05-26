<?php
/**
 * Runtime article fetcher — fair-use full body extraction with file cache.
 *
 * Use when DB stored content is empty/short. Same site selectors as cron/ai-sync.php.
 * Returns clean paragraphs (text only). Cached to cache/article-<md5>.txt for 6h.
 */

if (!defined('AAK_SITE_SELECTORS')) {
define('AAK_SITE_SELECTORS', [
    'onlinekhabar.com'                => ['class="ok18-single-post-content-wrap"','class="ok-news-post-body"','class="ok-single-post-content"','class="detail_news_body"','class="content-body"'],
    'setopati.com'                    => ['class="article__body"','class="article_detail_content"','class="article-body"','class="content"'],
    'ratopati.com'                    => ['class="news-content"','class="article-content"','class="entry-content"','class="detail-content"','class="body"'],
    'nepalkhabar.com'                 => ['class="news-content"','class="article-body"','class="story-content"','class="article"'],
    'nagariknews.nagariknetwork.com'  => ['class="article_detail"','class="article-content"','class="entry-content"','class="article-text"'],
    'myrepublica.nagariknetwork.com'  => ['class="article-body"','class="story-detail"','class="entry-content"','class="article-content"'],
    'gorkhapatraonline.com'           => ['class="entry-content"','class="post-content"','itemprop="articleBody"'],
    'nepalitimes.com'                 => ['class="entry-content"','class="article-body"','itemprop="articleBody"'],
    'bbc.com'                         => ['data-component="text-block"','class="article__body-content"'],
    'aljazeera.com'                   => ['class="article-body"','class="wysiwyg"','class="article-content"'],
]);
define('AAK_GENERIC_SELECTORS', [
    'itemprop="articleBody"','class="article-content"','class="article-body"',
    'class="post-content"','class="entry-content"','class="content-body"',
    'class="news-content"','class="story-body"','class="single-content"',
    '<article','class="main-content"',
]);
define('AAK_SKIP_PATTERNS', [
    '/^(advertisement|sponsored|read more|related|share this|follow us|subscribe|tags?|comments?)/i',
    '/^(पढ्नुहोस्|सम्बन्धित|विज्ञापन|शेयर|टिप्पणी)/u',
]);
}

if (!function_exists('aakFetchArticle')) {
function aakFetchArticle(string $url, int $ttl = 21600): array {
    $empty = ['paragraphs'=>[], 'plain'=>'', 'source'=>'none'];
    if (!filter_var($url, FILTER_VALIDATE_URL)) return $empty;

    $cacheDir = dirname(__DIR__) . '/cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
    $cacheFile = $cacheDir . '/article-' . md5($url) . '.json';

    if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
        $d = @json_decode((string)@file_get_contents($cacheFile), true);
        if (is_array($d) && !empty($d['paragraphs'])) {
            $d['source'] = 'cache';
            return $d;
        }
    }

    $html = aakHttpGet($url, 7);
    if (!$html) return $empty;

    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    $domain = preg_replace('/^(www|english|en|m)\./', '', $host);

    // Strip noise
    $html = preg_replace('/<(script|style|nav|header|footer|aside|form|iframe|noscript|figure|figcaption|svg|button|select|input)[^>]*>.*?<\/\1>/si', '', $html);
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    $html = preg_replace('/<div[^>]+(class|id)=["\'][^"\']*(advert|advertisement|ad-|sponsor|social-share|share-btn|related-post|sidebar|widget|newsletter|subscribe|comment|pagination|breadcrumb|tags|author-box|popup)[^"\']*["\'][^>]*>.*?<\/div>/si', '', $html);

    $selectors = array_merge(AAK_SITE_SELECTORS[$domain] ?? [], AAK_GENERIC_SELECTORS);
    $chunk = '';
    foreach ($selectors as $sel) {
        $pos = mb_strpos($html, $sel);
        if ($pos !== false) { $chunk = mb_substr($html, $pos, 16000); break; }
    }
    if (!$chunk) {
        $bp = mb_strpos($html, '<body');
        $chunk = $bp !== false ? mb_substr($html, $bp, 20000) : mb_substr($html, 0, 20000);
    }

    preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $chunk, $m);
    $paras = [];
    foreach (($m[1] ?? []) as $raw) {
        $t = trim(strip_tags($raw));
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = str_replace(["\u{2018}","\u{2019}","\u{201C}","\u{201D}","\u{2013}","\u{2014}","\u{00A0}"], ["'","'",'"','"','–','—',' '], $t);
        $t = preg_replace('/\s+/u', ' ', trim($t));
        if (mb_strlen($t) < 40) continue;
        $skip = false;
        foreach (AAK_SKIP_PATTERNS as $pat) { if (preg_match($pat, $t)) { $skip = true; break; } }
        if ($skip) continue;
        $paras[] = $t;
        if (count($paras) >= 20) break;
    }

    $out = [
        'paragraphs' => $paras,
        'plain'      => implode("\n\n", $paras),
        'source'     => 'live',
    ];
    if (!empty($paras)) @file_put_contents($cacheFile, json_encode($out, JSON_UNESCAPED_UNICODE));
    return $out;
}

function aakHttpGet(string $url, int $timeout = 7): string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 4,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Aakashvani/1.0; +https://aakashvani.com)',
            CURLOPT_HTTPHEADER     => ['Accept: text/html,application/xhtml+xml', 'Accept-Language: ne,en;q=0.8'],
            CURLOPT_ENCODING       => '',
        ]);
        $r = curl_exec($ch);
        $c = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($r && $c >= 200 && $c < 400) return (string)$r;
    }
    $ctx = stream_context_create([
        'http' => ['timeout'=>$timeout, 'user_agent'=>'Mozilla/5.0 Aakashvani/1.0', 'follow_location'=>1, 'ignore_errors'=>true],
        'ssl'  => ['verify_peer'=>true, 'verify_peer_name'=>true],
    ]);
    return (string)@file_get_contents($url, false, $ctx);
}

} // end function_exists guard
