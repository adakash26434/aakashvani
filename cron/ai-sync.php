<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  आकाशवाणी — AI News Sync Engine v5
 *  "Zero Human Touch" Pipeline
 *
 *  Cron (cPanel):
 *    0,30 * * * *  /usr/bin/php /home/USER/public_html/cron/ai-sync.php
 *
 *  Pipeline per article:
 *    RSS Fetch → Parse → Scrape Full Content
 *    → AI Rewrite / Translate → AI Categorise → AI Title Clean
 *    → AI Excerpt → Cache Image → Store DB
 * ═══════════════════════════════════════════════════════════════════
 */

define('CRON_RUN', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// ── Log helper ────────────────────────────────────────────────────────────────
$logDir     = __DIR__ . '/../data/logs';
$imgCacheDir = __DIR__ . '/../assets/news-cache';
@mkdir($logDir,     0755, true);
@mkdir($imgCacheDir,0755, true);

function syncLog(string $msg, string $level = 'INFO'): void {
    global $logDir;
    $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $msg . PHP_EOL;
    @file_put_contents($logDir . '/ai-sync.log', $line, FILE_APPEND | LOCK_EX);
    if (php_sapi_name() === 'cli') echo $line;
}

syncLog('=== AI Sync v5 started (zero-touch pipeline) ===');

// ── AI available? ─────────────────────────────────────────────────────────────
$AI_KEY  = defined('OPENAI_API_KEY')  ? OPENAI_API_KEY  : '';
$AI_URL  = defined('OPENAI_BASE_URL') ? OPENAI_BASE_URL : 'https://api.openai.com/v1';
$AI_MODEL= defined('AI_MODEL')        ? AI_MODEL        : 'gpt-4o-mini';
$AI_ON   = !empty($AI_KEY);

syncLog($AI_ON ? "✔ AI enabled ($AI_MODEL)" : '⚠ AI key not set — keyword fallbacks active', $AI_ON ? 'INFO' : 'WARN');

// ── RSS Sources ───────────────────────────────────────────────────────────────
const RSS_SOURCES = [
    ['name'=>'Onlinekhabar', 'url'=>'https://www.onlinekhabar.com/feed',                    'lang'=>'ne','scope'=>'national','domain'=>'onlinekhabar.com'],
    ['name'=>'Setopati',     'url'=>'https://www.setopati.com/feed',                        'lang'=>'ne','scope'=>'national','domain'=>'setopati.com'],
    ['name'=>'Ratopati',     'url'=>'https://ratopati.com/feed',                            'lang'=>'ne','scope'=>'national','domain'=>'ratopati.com'],
    ['name'=>'Gorkhapatra',  'url'=>'https://www.gorkhapatraonline.com/feed',               'lang'=>'ne','scope'=>'national','domain'=>'gorkhapatraonline.com'],
    ['name'=>'Nagarik',      'url'=>'https://nagariknews.nagariknetwork.com/feed',           'lang'=>'ne','scope'=>'national','domain'=>'nagariknews.nagariknetwork.com'],
    ['name'=>'My Republica', 'url'=>'https://myrepublica.nagariknetwork.com/feed',           'lang'=>'en','scope'=>'national','domain'=>'myrepublica.nagariknetwork.com'],
    ['name'=>'Nepali Times', 'url'=>'https://www.nepalitimes.com/feed/',                    'lang'=>'en','scope'=>'national','domain'=>'nepalitimes.com'],
];

// ── Site-specific content selectors ──────────────────────────────────────────
const SITE_SELECTORS = [
    'onlinekhabar.com'                => ['class="ok18-single-post-content-wrap"','class="ok-news-post-body"','class="ok-single-post-content"','class="detail_news_body"'],
    'setopati.com'                    => ['class="article__body"','class="article_detail_content"','class="article-body"'],
    'ratopati.com'                    => ['class="article-news-body"','class="article_text_section"','class="newsdetail"','class="article-content"','class="post-content"','id="main-content"','class="content-area"','class="entry-content"'],
    'nagariknews.nagariknetwork.com'  => ['class="article_detail"','class="article-content"','class="entry-content"'],
    'myrepublica.nagariknetwork.com'  => ['class="article-body"','class="story-detail"','class="entry-content"'],
    'gorkhapatraonline.com'           => ['class="entry-content"','class="post-content"','itemprop="articleBody"'],
    'nepalitimes.com'                 => ['class="entry-content"','class="article-body"','itemprop="articleBody"'],
];
const GENERIC_SELECTORS = [
    'itemprop="articleBody"','class="article-content"','class="article-body"',
    'class="post-content"','class="entry-content"','class="content-body"',
    'class="news-content"','class="story-body"','class="single-content"',
    '<article','<main',
];

const SKIP_PATTERNS = [
    '/^(Share|Tags?:|Category:|Published|Read more|Advertisement|Related|Subscribe|Follow us|Click here|Also read|Copyright|Download|Comment|Disclaimer)/i',
    '/^(सेयर|ट्याग|श्रेणी|प्रकाशित|थप पढ्नुस्|विज्ञापन|सम्बन्धित|सदस्यता|कमेन्ट|कपिराइट|डाउनलोड)/u',
    '/^(फेसबुक|ट्विटर|भाइबर|व्हाट्सएप|इन्स्टाग्राम|यूट्युब)/u',
    '/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}/',
    '/^[A-Z\s]{4,}:/',
];

const CATEGORY_KEYWORDS = [
    'Technology'    => ['AI','tech','software','digital','internet','app','cyber','प्रविधि','डिजिटल','कम्प्युटर','मोबाइल'],
    'Business'      => ['NEPSE','stock','IPO','bank','economy','व्यापार','बैंक','शेयर','बजार','अर्थ','रुपैयाँ','बजेट','कर'],
    'Sports'        => ['cricket','football','sports','खेल','क्रिकेट','फुटबल','भलिबल','हकी','अन्तर्राष्ट्रिय खेल'],
    'Entertainment' => ['film','music','actor','movie','चलचित्र','गीत','कलाकार','मनोरञ्जन','सिरियल','टेलिभिजन'],
    'International' => ['UN','USA','world','global','अन्तर्राष्ट्रिय','विश्व','भारत','चीन','अमेरिका','रुस','युक्रेन'],
    'National'      => ['Nepal','government','parliament','minister','नेपाल','सरकार','संसद','मन्त्री','प्रधानमन्त्री','राष्ट्रपति'],
];

const BREAKING_KW = ['earthquake','flood','disaster','killed','accident','भूकम्प','बाढी','विपद','मृत्यु','दुर्घटना','हत्या','ब्रेकिङ','तत्काल'];

// ══════════════════════════════════════════════════════════════════════════════
//  AI HELPERS
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Call OpenAI chat and return the text response. Returns null on any failure.
 */
function aiCall(string $systemPrompt, string $userMsg, int $maxTokens = 800, float $temp = 0.4): ?string {
    global $AI_KEY, $AI_URL, $AI_MODEL;
    if (!$AI_KEY) return null;

    $payload = json_encode([
        'model'       => $AI_MODEL,
        'messages'    => [
            ['role'=>'system','content'=>$systemPrompt],
            ['role'=>'user',  'content'=>$userMsg],
        ],
        'max_tokens'  => $maxTokens,
        'temperature' => $temp,
    ]);

    $ch = curl_init(rtrim($AI_URL, '/') . '/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $AI_KEY,
            'Content-Type: application/json',
        ],
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err || $code !== 200) {
        syncLog("AI call failed [$code] $err", 'WARN');
        return null;
    }
    $data = json_decode($resp, true);
    return trim($data['choices'][0]['message']['content'] ?? '') ?: null;
}

/**
 * AI: Rewrite scraped raw content into 5-7 clean Nepali paragraphs.
 * Removes ad text, junk, HTML artifacts. Returns plain text with \n\n separators.
 */
function aiRewriteContent(string $title, string $rawContent, string $lang): ?string {
    if (mb_strlen($rawContent) < 100) return null;

    if ($lang === 'en') {
        // Translate English → Nepali AND rewrite
        $sys = 'तपाईं एक अनुभवी नेपाली पत्रकार हुनुहुन्छ। तलको अंग्रेजी समाचार सामग्रीलाई प्रवाहमान नेपालीमा अनुवाद गर्दै ५-७ सफा अनुच्छेदमा लेख्नुहोस्। विज्ञापन, share बटन, र असम्बन्धित सामग्री हटाउनुहोस्। केवल plain text अनुच्छेदहरू — HTML नराख्नुस्। अनुच्छेदहरू खाली लाइनले अलग गर्नुस्।';
    } else {
        $sys = 'तपाईं एक अनुभवी नेपाली पत्रकार हुनुहुन्छ। तलको कच्चा समाचार सामग्रीलाई सफा, प्रवाहमान नेपाली गद्यमा ५-७ अनुच्छेदमा पुनर्लेखन गर्नुहोस्। विज्ञापन, share बटन, र असम्बन्धित सामग्री हटाउनुहोस्। केवल plain text अनुच्छेदहरू — HTML नराख्नुस्। अनुच्छेदहरू खाली लाइनले अलग गर्नुस्।';
    }

    $user = "शीर्षक: $title\n\nसामग्री:\n" . mb_substr($rawContent, 0, 3000);
    $result = aiCall($sys, $user, 900, 0.5);
    if (!$result) return null;

    // Normalise: ensure \n\n paragraph separation
    $result = preg_replace('/\n{3,}/', "\n\n", trim($result));
    return mb_strlen($result) > 100 ? $result : null;
}

/**
 * AI: Generate a clean 2-sentence excerpt for card previews.
 */
function aiExcerpt(string $title, string $body, string $lang): string {
    if (mb_strlen($body) < 80) {
        // Fallback: first 2 Nepali/English sentences
        $sents = preg_split('/(?<=[।॥!?.])\s+/u', $body, 4);
        return implode(' ', array_slice($sents, 0, 2));
    }

    $sys = $lang === 'ne'
        ? 'तपाईं नेपाली समाचार सम्पादक हुनुहुन्छ। तलको समाचार सामग्रीबाट ठ्याक्कै २ वाक्यको संक्षेप तयार गर्नुहोस्। नेपालीमा लेख्नुस्। HTML नराख्नुस्।'
        : 'You are a news editor. Write exactly 2 sentences summarising the article. Plain text only, no HTML.';

    $user = "Title: $title\n\n" . mb_substr($body, 0, 800);
    $result = aiCall($sys, $user, 120, 0.3);

    // Fallback if AI fails
    if (!$result) {
        $sents = preg_split('/(?<=[।॥!?.])\s+/u', $body, 4);
        return mb_substr(implode(' ', array_slice($sents, 0, 2)), 0, 280);
    }
    return mb_substr($result, 0, 320);
}

/**
 * AI: Detect category from title + body.
 * Returns one of: National|Business|Sports|Technology|Entertainment|International
 */
function aiCategorize(string $title, string $body): string {
    $cats = implode('|', array_keys(CATEGORY_KEYWORDS));
    $sys  = "Classify this news article into exactly one category from this list: $cats\nRespond with ONLY the category name, nothing else.";
    $user = "Title: $title\n\n" . mb_substr($body, 0, 400);
    $result = aiCall($sys, $user, 10, 0.0);
    if ($result) {
        $result = trim($result);
        foreach (array_keys(CATEGORY_KEYWORDS) as $cat) {
            if (stripos($result, $cat) !== false) return $cat;
        }
    }
    // Keyword fallback
    return keywordCategory($title, $body);
}

/**
 * AI: Clean up RSS feed titles (fix ALL-CAPS, remove trailing source tags, etc.)
 */
function aiCleanTitle(string $rawTitle, string $lang): string {
    // Quick check — if title looks clean, skip AI call
    if (mb_strlen($rawTitle) < 10 || mb_strlen($rawTitle) > 200) return $rawTitle;
    if (!preg_match('/[A-Z]{5,}|[-—|]\s*\w+\s*$|^\s*\[/', $rawTitle)) return $rawTitle;

    $sys = $lang === 'ne'
        ? 'तलको समाचार शीर्षकलाई सफा र पाठकमैत्री बनाउनुहोस् (ALL CAPS हटाउनुस्, अन्त्यमा स्रोतको नाम भए हटाउनुस्)। केवल सफा शीर्षक फर्काउनुस्।'
        : 'Clean the news headline: remove ALL CAPS, trailing source tags like "- Reuters" or "[BBC]". Return only the cleaned headline.';
    $result = aiCall($sys, $rawTitle, 80, 0.2);
    return ($result && mb_strlen($result) > 5) ? $result : $rawTitle;
}

// ══════════════════════════════════════════════════════════════════════════════
//  KEYWORD FALLBACKS (when AI key not configured)
// ══════════════════════════════════════════════════════════════════════════════

function keywordCategory(string $title, string $body): string {
    $text = mb_strtolower($title . ' ' . mb_substr($body, 0, 500));
    $scores = [];
    foreach (CATEGORY_KEYWORDS as $cat => $kws) {
        $s = 0;
        foreach ($kws as $kw) {
            if (mb_strpos($text, mb_strtolower($kw)) !== false)
                $s += (mb_strpos(mb_strtolower($title), mb_strtolower($kw)) !== false) ? 3 : 1;
        }
        if ($s > 0) $scores[$cat] = $s;
    }
    if (empty($scores)) return 'National';
    arsort($scores);
    return (string)array_key_first($scores);
}

function cleanParagraphs(string $raw, int $max = 8, int $maxChars = 5000): string {
    // Strip HTML
    $text = strip_tags($raw);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Normalise smart-quotes, dashes
    $text = str_replace(["\u{2018}","\u{2019}","\u{201C}","\u{201D}","\u{2013}","\u{2014}"],
                        ["'","'",'"','"','–','—'], $text);
    $text = preg_replace('/\s+/u', ' ', $text);

    // Split on double-newline
    $paras = preg_split('/\n\n+/u', $text);
    if (count($paras) === 1) {
        $sents = preg_split('/(?<=[।॥!?.])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = array_chunk($sents, 3);
        $paras  = array_map(fn($c) => implode(' ', $c), $chunks);
    }

    $out = [];
    foreach ($paras as $p) {
        $p = trim($p);
        if (mb_strlen($p) < 40) continue;
        $skip = false;
        foreach (SKIP_PATTERNS as $pat) {
            if (preg_match($pat, $p)) { $skip = true; break; }
        }
        if ($skip) continue;
        $out[] = $p;
        if (count($out) >= $max) break;
    }
    return mb_substr(implode("\n\n", $out), 0, $maxChars);
}

function isBreaking(string $title, string $body): bool {
    $text = mb_strtolower($title . ' ' . mb_substr($body, 0, 300));
    foreach (BREAKING_KW as $kw) {
        if (mb_strpos($text, mb_strtolower($kw)) !== false) return true;
    }
    return false;
}

// ══════════════════════════════════════════════════════════════════════════════
//  HTTP / RSS / SCRAPER
// ══════════════════════════════════════════════════════════════════════════════

function syncFetch(string $url, int $timeout = 14, array $extra = []): ?string {
    require_once __DIR__ . '/../includes/http.php';
    $headers = array_merge([
        'Accept: text/html,application/xml,*/*',
        'Accept-Language: ne,en;q=0.8',
    ], $extra);
    $r = nh_fetchUrl($url, $headers, $timeout, true);
    if ($r === null) syncLog("fetch failed: $url", 'WARN');
    return $r;
}

function isImageUrl(string $url): bool {
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $p = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
    foreach (['.jpg','.jpeg','.png','.webp','.gif','.avif'] as $e) {
        if (str_ends_with($p, $e)) return true;
    }
    return preg_match('/(\/uploads\/|\/images\/|\/media\/|\/photo\/|\/img\/|cdn\.|wp-content)/i', $url) === 1;
}

function extractImage(SimpleXMLElement $item, array $ns, string $link): ?string {
    if (isset($ns['media'])) {
        $m = $item->children($ns['media']);
        foreach (['content','thumbnail'] as $k) {
            if (isset($m->$k)) { $u=(string)($m->$k->attributes()['url']??''); if($u&&isImageUrl($u))return$u; }
        }
    }
    if (isset($item->enclosure)) {
        $a=$item->enclosure->attributes(); $u=(string)($a['url']??'');
        if ($u && isImageUrl($u)) return $u;
    }
    $htmlSrc = [];
    if (isset($ns['content'])) { $c=$item->children($ns['content']); if(isset($c->encoded)) $htmlSrc[]=(string)$c->encoded; }
    $htmlSrc[] = (string)($item->description ?? '');
    foreach ($htmlSrc as $h) {
        if (!$h) continue;
        foreach (['/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i','/<img[^>]+data-src=["\']([^"\']+)["\'][^>]*>/i'] as $pat) {
            if (preg_match($pat,$h,$m2)) { $u=htmlspecialchars_decode(trim($m2[1])); if($u&&isImageUrl($u)&&!str_contains($u,'1x1'))return$u; }
        }
    }
    // og:image from article page
    $html = syncFetch($link, 5);
    if ($html && preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i',$html,$m2)) {
        $u = htmlspecialchars_decode(trim($m2[1]));
        if ($u && isImageUrl($u)) return $u;
    }
    return null;
}

function cacheImage(string $remote): string {
    global $imgCacheDir;
    $ext = '.jpg';
    $p   = strtolower(parse_url($remote, PHP_URL_PATH) ?? '');
    foreach (['.jpg','.jpeg','.png','.webp','.gif','.avif'] as $e) { if(str_ends_with($p,$e)){$ext=$e;break;} }
    $name  = md5($remote).$ext;
    $local = $imgCacheDir.'/'.$name;
    $url   = '/assets/news-cache/'.$name;
    if (file_exists($local) && filesize($local) > 500) return $url;
    $ch = curl_init($remote);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_TIMEOUT=>10,CURLOPT_MAXREDIRS=>3,CURLOPT_USERAGENT=>'Mozilla/5.0',CURLOPT_SSL_VERIFYPEER => true]);
    $data=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $type=curl_getinfo($ch,CURLINFO_CONTENT_TYPE); curl_close($ch);
    if (!$data||$code!==200||!str_starts_with((string)$type,'image/')) return $remote;
    @file_put_contents($local,$data);
    return $url;
}

function scrapeArticle(string $url, string $domain): string {
    $html = syncFetch($url, 12);
    if (!$html) return '';

    // Strip noise
    $html = preg_replace('/<(script|style|nav|header|footer|aside|form|iframe|noscript|figure|figcaption|svg|button)[^>]*>.*?<\/\1>/si', '', $html);
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    $html = preg_replace('/<div[^>]+(class|id)=["\'][^"\']*(?:advert|advertisement|ad-|sponsor|social-share|share-btn|related-post|sidebar|widget|newsletter|subscribe|comment|pagination)[^"\']*["\'][^>]*>.*?<\/div>/si', '', $html);

    // Find content container
    $selectors = array_merge(SITE_SELECTORS[$domain] ?? [], GENERIC_SELECTORS);
    $chunk = '';
    foreach ($selectors as $sel) {
        $pos = mb_strpos($html, $sel);
        if ($pos !== false) { $chunk = mb_substr($html, $pos, 14000); break; }
    }
    if (!$chunk) {
        $bp = mb_strpos($html,'<body');
        $chunk = $bp!==false ? mb_substr($html,$bp,18000) : mb_substr($html,0,18000);
    }

    preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $chunk, $m);
    $paras = [];
    foreach (($m[1]??[]) as $raw) {
        $t = trim(strip_tags($raw));
        $t = html_entity_decode($t, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        $t = str_replace(["\u{2018}","\u{2019}","\u{201C}","\u{201D}","\u{2013}","\u{2014}"],["'","'",'"','"','–','—'],$t);
        $t = preg_replace('/\s+/u',' ',trim($t));
        if (mb_strlen($t)<40) continue;
        $skip=false; foreach(SKIP_PATTERNS as $pat){if(preg_match($pat,$t)){$skip=true;break;}} if($skip)continue;
        $paras[]=$t;
        if(count($paras)>=25) break;
    }
    return mb_substr(implode("\n\n",$paras),0,14000);
}

function parseParagraphsFromHtml(string $html, int $max=15): string {
    preg_match_all('/<p[^>]*>(.*?)<\/p>/si',$html,$m);
    $paras=[];
    foreach(($m[1]??[]) as $p){
        $t=trim(strip_tags($p)); $t=html_entity_decode($t,ENT_QUOTES|ENT_HTML5,'UTF-8'); $t=preg_replace('/\s+/u',' ',$t);
        if(mb_strlen($t)>=40){$paras[]=$t; if(count($paras)>=$max)break;}
    }
    return implode("\n\n",$paras);
}

function parseRssFeed(string $xml, string $sourceName, string $domain): array {
    libxml_use_internal_errors(true);
    $doc = @simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
    if(!$doc){syncLog("XML parse fail: $sourceName",'WARN');return[];}
    $ns=$doc->getNamespaces(true); $items=[];

    // RSS 2.0
    $ri=$doc->channel->item??$doc->item??null;
    if($ri){
        foreach($ri as $item){
            $in=$item->getNamespaces(true); $allNs=array_merge($ns,$in);
            $link=trim((string)$item->link); if(!$link)continue;
            $fc='';
            if(isset($allNs['content'])){ $c=$item->children($allNs['content']); if(isset($c->encoded)) $fc=parseParagraphsFromHtml((string)$c->encoded,8); }
            $desc=mb_substr(trim(strip_tags((string)($item->description??''))).$fc,0,300);
            $items[]=['title'=>trim((string)$item->title),'url'=>$link,'desc'=>$desc,'rss_content'=>$fc,'image'=>extractImage($item,$allNs,$link),'pub'=>strtotime((string)($item->pubDate??''))?:time(),'domain'=>$domain];
        }
    }
    // Atom
    if(isset($doc->entry)){
        foreach($doc->entry as $e){
            $link=''; foreach($e->link as $l){$a=$l->attributes();if(!$link||(string)$a['rel']==='alternate')$link=(string)$a['href'];}
            if(!$link)continue;
            $fc=parseParagraphsFromHtml((string)($e->content??''),8);
            $desc=mb_substr($fc?:strip_tags((string)($e->summary??'')),0,300);
            $en=$e->getNamespaces(true);
            $items[]=['title'=>trim((string)$e->title),'url'=>$link,'desc'=>$desc,'rss_content'=>$fc,'image'=>extractImage($e,$en,$link),'pub'=>strtotime((string)($e->published??$e->updated??''))?:time(),'domain'=>$domain];
        }
    }
    return $items;
}

// ══════════════════════════════════════════════════════════════════════════════
//  DB HELPERS
// ══════════════════════════════════════════════════════════════════════════════

function ensureSyncLogTable(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS news_sync_log (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        source VARCHAR(100), articles_fetched INT DEFAULT 0,
        articles_inserted INT DEFAULT 0, articles_skipped INT DEFAULT 0,
        run_at DATETIME DEFAULT CURRENT_TIMESTAMP, error_msg TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensureNewsColumns(): void {
    $add = ['url_hash'=>'CHAR(32) DEFAULT NULL','is_breaking'=>'TINYINT(1) DEFAULT 0',
            'original_url'=>'VARCHAR(1000) DEFAULT NULL','content'=>'LONGTEXT DEFAULT NULL',
            'lang'=>"VARCHAR(10) DEFAULT 'ne'",'scope'=>"VARCHAR(20) DEFAULT 'national'",
            'source_name'=>'VARCHAR(100) DEFAULT NULL','ai_processed'=>'TINYINT(1) DEFAULT 0'];
    foreach($add as $col=>$def){ try{ db()->exec("ALTER TABLE tech_news ADD COLUMN $col $def"); }catch(\Exception $e){} }
    try{ db()->exec("CREATE INDEX idx_url_hash ON tech_news(url_hash)"); }catch(\Exception $e){}
    try{ db()->exec("CREATE INDEX idx_breaking ON tech_news(is_breaking)"); }catch(\Exception $e){}
}

function makeSlug(string $title,string $hash): string {
    $a=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$title)?:'';
    $a=preg_replace('/[^a-zA-Z0-9\s-]/','',  $a);
    $s=preg_replace('/\s+/','-',strtolower(trim($a)));
    $s=preg_replace('/-+/','-',trim($s,'-'));
    return mb_substr($s?:'article',0,60).'-'.substr($hash,0,6);
}

function logSync(string $src,int $f,int $i,int $sk,?string $e): void {
    try{ db()->prepare("INSERT INTO news_sync_log (source,articles_fetched,articles_inserted,articles_skipped,error_msg) VALUES(?,?,?,?,?)")->execute([$src,$f,$i,$sk,$e]); }catch(\Exception $ex){}
}

// ══════════════════════════════════════════════════════════════════════════════
//  MAIN SYNC
// ══════════════════════════════════════════════════════════════════════════════

function runSync(): void {
    global $AI_ON;
    ensureSyncLogTable();
    ensureNewsColumns();
    ensureNewsTable();

    $totalInserted = 0;
    $maxPerRun     = defined('NEWS_MAX_PER_SYNC') ? (int)NEWS_MAX_PER_SYNC : 25;

    foreach (RSS_SOURCES as $src) {
        if ($totalInserted >= $maxPerRun) { syncLog("Max $maxPerRun reached. Done."); break; }

        syncLog("── {$src['name']} ──────────────────");
        $xml = syncFetch($src['url']);
        if (!$xml) { syncLog("  FAIL: fetch error",'WARN'); logSync($src['name'],0,0,0,'fetch_failed'); continue; }

        $items = parseRssFeed($xml,$src['name'],$src['domain']??'');
        syncLog("  Parsed: ".count($items)." items");

        $inserted=$skipped=0;
        foreach ($items as $item) {
            if ($totalInserted >= $maxPerRun) break;
            if (!$item['url']||!$item['title']) { $skipped++; continue; }

            $hash = md5($item['url']);

            // Dedup
            $s=db()->prepare("SELECT id FROM tech_news WHERE url_hash=? LIMIT 1"); $s->execute([$hash]);
            if($s->fetch()){$skipped++;continue;}
            $s2=db()->prepare("SELECT id FROM tech_news WHERE title=? LIMIT 1"); $s2->execute([mb_substr($item['title'],0,255)]);
            if($s2->fetch()){$skipped++;continue;}

            // ── 1. SCRAPE FULL CONTENT ────────────────────────────────────
            $rawBody = $item['rss_content'] ?? '';
            if (mb_strlen($rawBody) < 300) {
                syncLog("  → scraping article page…");
                $scraped = scrapeArticle($item['url'],$item['domain']??'');
                if (mb_strlen($scraped) > mb_strlen($rawBody)) $rawBody = $scraped;
                syncLog("    scraped: ".mb_strlen($rawBody)." chars");
                usleep(500000);
            }

            // ── 2. AI REWRITE / TRANSLATE ─────────────────────────────────
            $finalBody = null; $aiProcessed = 0;
            if ($AI_ON && mb_strlen($rawBody) > 100) {
                syncLog("  → AI rewriting (lang={$src['lang']})…");
                $finalBody = aiRewriteContent($item['title'], $rawBody, $src['lang']);
                if ($finalBody) { $aiProcessed = 1; syncLog("    ✔ AI rewrite done (".mb_strlen($finalBody)." chars)"); }
                else syncLog("    AI rewrite returned null — using cleaned raw",'WARN');
            }
            // Fallback: clean the raw scraped text
            if (!$finalBody) $finalBody = cleanParagraphs($rawBody, 8, 5000);
            if (!$finalBody) $finalBody = cleanParagraphs($item['desc'], 3, 800);
            $rawLength = mb_strlen(trim($rawBody));
            $finalLength = mb_strlen(trim($finalBody ?: ''));
            $scrapeStatus = $rawLength >= 500 ? 'full' : ($rawLength >= 200 ? 'partial' : 'short');
            $contentStatus = $finalLength >= 1200 ? 'full' : ($finalLength >= 300 ? 'partial' : 'short');

            // ── 3. AI TITLE CLEAN ─────────────────────────────────────────
            $finalTitle = $AI_ON ? aiCleanTitle($item['title'], $src['lang']) : $item['title'];

            // ── 4. AI CATEGORISE ──────────────────────────────────────────
            $category = $AI_ON
                ? aiCategorize($finalTitle, $finalBody)
                : keywordCategory($finalTitle, $finalBody);

            // ── 5. AI EXCERPT ─────────────────────────────────────────────
            $excerpt = aiExcerpt($finalTitle, $finalBody, $src['lang'] === 'en' ? 'ne' : $src['lang']);

            // ── 6. BREAKING DETECT ────────────────────────────────────────
            $breaking = isBreaking($finalTitle, $finalBody);

            // ── 7. IMAGE CACHE ────────────────────────────────────────────
            $imgUrl = null;
            if (!empty($item['image'])) {
                syncLog("  → caching image…");
                $imgUrl = cacheImage($item['image']);
            }

            // ── 8. INSERT DB ──────────────────────────────────────────────
            $slug    = makeSlug($finalTitle, $hash);
            $pubDate = date('Y-m-d H:i:s', min($item['pub'], time()));

            db()->prepare("INSERT INTO tech_news
                (title,slug,excerpt,content,category,lang,scope,
                 is_published,is_featured,is_breaking,ai_processed,
                 image_url,url_hash,source_name,original_url,
                 content_status,content_length,scrape_status,last_scraped_at,published_at,
                 created_at,updated_at)
                VALUES (?,?,?,?,?,?,?, 1,0,?,?, ?,?,?,?, ?,?,?,?,?, ?,?)")
               ->execute([
                    mb_substr($finalTitle,0,255), $slug,
                    $excerpt, $finalBody ?: null,
                    $category, 'ne', $src['scope'],
                    $breaking?1:0, $aiProcessed,
                    $imgUrl?mb_substr($imgUrl,0,500):null,
                    $hash, $src['name'],
                    mb_substr($item['url'],0,999),
                    $contentStatus, $finalLength, $scrapeStatus, date('Y-m-d H:i:s'), $pubDate,
                    $pubDate, $pubDate,
               ]);

            $inserted++; $totalInserted++;
            $icon = $breaking ? '⚠' : ($aiProcessed ? '✔' : '○');
            syncLog("  $icon [{$category}] ".mb_substr($finalTitle,0,60));
        }

        syncLog("  → ins: $inserted  skip: $skipped");
        logSync($src['name'],count($items),$inserted,$skipped,null);
        usleep(800000); // 0.8s polite delay between sources
    }

    // Prune articles older than 30 days
    try {
        $pruned = db()->exec("DELETE FROM tech_news WHERE source_name IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        if ($pruned) syncLog("  pruned $pruned old articles");
    } catch(\Exception $e){}

    syncLog("=== Done. Total inserted this run: $totalInserted ===");
}

try {
    runSync();
} catch (\Throwable $e) {
    syncLog('FATAL: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine(),'ERROR');
    exit(1);
}
