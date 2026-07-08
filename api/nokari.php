<?php
/**
 * api/nokari.php — Private sector job listings aggregator
 * Sources: MeroJob, HamroJob, FroxJob, KumariJob, Bdjobs (RSS/feeds)
 * Cache: 30 min
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=1800');
header('Access-Control-Allow-Origin: *');
@ini_set('default_socket_timeout', 7);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/http.php';

$cat   = isset($_GET['cat']) ? strtolower(trim($_GET['cat'])) : 'all';
$limit = isset($_GET['limit']) ? max(1, min(60, (int)$_GET['limit'])) : 40;
$cacheDir = __DIR__ . '/../data/cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

function job_get(string $url, int $timeout = 7): string {
    $r = nh_fetchUrl($url, ['Accept: text/xml,application/rss+xml,*/*'], $timeout, true);
    return $r ?: '';
}

function job_ago(int $ts): string {
    $d = time() - $ts;
    if ($d < 60) return 'भर्खर';
    if ($d < 3600) return (int)($d/60) . ' मि.';
    if ($d < 86400) return (int)($d/3600) . ' घ.';
    if ($d < 604800) return (int)($d/86400) . ' दिन';
    return date('M j', $ts);
}

function job_category(string $title): string {
    $t = mb_strtolower($title);
    $map = [
        'it'        => ['software','developer','programmer','it ','web ','mobile','app ','data','database','network','system admin','devops','cyber','cloud','tech'],
        'finance'   => ['finance','accounting','account ','banking','bank ','treasury','audit','tax','chartered','ca ','cma','financial'],
        'marketing' => ['marketing','sales','business development','brand','digital market','social media','seo','content'],
        'engineering'=> ['engineer','civil','mechanical','electrical','architecture','construction','survey','structural'],
        'teaching'  => ['teacher','lecturer','professor','instructor','tutor','education','school','college','academic'],
        'health'    => ['doctor','nurse','pharmacist','health','medical','hospital','clinic','paramedic','lab'],
        'admin'     => ['admin','secretary','receptionist','office','coordinator','hr ','human resource','manager','management'],
        'legal'     => ['lawyer','legal','advocate','compliance','law '],
        'driver'    => ['driver','operator'],
    ];
    foreach ($map as $cat => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($t, $kw) !== false) return $cat;
        }
    }
    return 'general';
}

function parseRSSJobs(string $raw, string $source, string $sourceCls): array {
    $items = [];
    preg_match_all('/<item>(.*?)<\/item>/si', $raw, $m);
    foreach (($m[1] ?? []) as $block) {
        $title = '';
        if (preg_match('/<title><!\[CDATA\[(.*?)\]\]><\/title>/si', $block, $t)) $title = trim($t[1]);
        elseif (preg_match('/<title>(.*?)<\/title>/si', $block, $t)) $title = html_entity_decode(strip_tags(trim($t[1])), ENT_QUOTES, 'UTF-8');
        if (!$title) continue;

        // Skip loksewa (govt) items — those go on loksewa.php
        $govKw = ['loksewa','lok sewa','psc ','government job','sarkari','gorkhapatra','नेपाल सरकार','शाखा अधिकृत','नासु','खरिदार'];
        $isGov = false;
        foreach ($govKw as $kw) { if (mb_stripos($title, $kw) !== false) { $isGov = true; break; } }
        if ($isGov) continue;

        $link = '';
        if (preg_match('/<link>(.*?)<\/link>/si', $block, $l)) $link = trim(strip_tags($l[1]));
        elseif (preg_match('/<link[^\/\s][^>]*>(.*?)<\/link>/si', $block, $l)) $link = trim($l[1]);
        if (!$link || !filter_var($link, FILTER_VALIDATE_URL)) continue;

        $desc = '';
        if (preg_match('/<description><!\[CDATA\[(.*?)\]\]><\/description>/si', $block, $d)) $desc = trim(strip_tags($d[1]));
        elseif (preg_match('/<description>(.*?)<\/description>/si', $block, $d)) $desc = trim(strip_tags(html_entity_decode($d[1], ENT_QUOTES, 'UTF-8')));
        $desc = preg_replace('/\s+/', ' ', mb_substr($desc, 0, 250, 'UTF-8'));

        $pubDate = '';
        if (preg_match('/<pubDate>(.*?)<\/pubDate>/si', $block, $pd)) $pubDate = trim($pd[1]);
        $ts = $pubDate ? @strtotime($pubDate) : 0;
        if (!$ts) $ts = time() - rand(3600, 86400);

        $img = null;
        if (preg_match('/<enclosure[^>]+url=["\']([^"\']+\.(?:jpg|jpeg|png|webp))["\']/si', $block, $im)) $img = $im[1];
        if (!$img && preg_match('/<media:content[^>]+url=["\']([^"\']+)["\']/si', $block, $im)) $img = $im[1];

        $cat = job_category($title . ' ' . $desc);
        $items[] = [
            'title'     => $title,
            'link'      => $link,
            'summary'   => $desc,
            'source'    => $source,
            'sourceCls' => $sourceCls,
            'ts'        => $ts,
            'ago'       => job_ago($ts),
            'category'  => $cat,
            'image'     => $img,
        ];
    }
    return $items;
}

/* ── RSS feed sources ── */
$feeds = [
    ['https://merojob.com/rss/', 'MeroJob', 'bg-blue-100 text-blue-700'],
    ['https://www.hamrojob.com/feed/', 'HamroJob', 'bg-emerald-100 text-emerald-700'],
    ['https://froxjob.com/feed/', 'FroxJob', 'bg-violet-100 text-violet-700'],
    ['https://kumarijob.com/feed/', 'KumariJob', 'bg-rose-100 text-rose-700'],
    ['https://jobaxle.com/feed/', 'JobAxle', 'bg-amber-100 text-amber-700'],
    ['https://govnepal.com/feed/', 'GovNepal', 'bg-teal-100 text-teal-700'],
    ['https://www.merojob.com/jobs/feed', 'MeroJob Jobs', 'bg-blue-100 text-blue-700'],
    ['https://kantipurjob.com/feed/', 'KantipurJob', 'bg-orange-100 text-orange-700'],
];

/* ── Fallback sample jobs ── */
function getSampleJobs(): array {
    $positions = [
        ['Software Developer', 'IT', 'Kathmandu', 'रु 50,000 - 80,000'],
        ['Marketing Manager', 'Marketing', 'Lalitpur', 'रु 40,000 - 60,000'],
        ['Accountant', 'Finance', 'Kathmandu', 'रु 35,000 - 50,000'],
        ['Civil Engineer', 'Engineering', 'Pokhara', 'रु 45,000 - 70,000'],
        ['Teacher', 'Teaching', 'Bhaktapur', 'रु 25,000 - 40,000'],
        ['HR Officer', 'Admin', 'Kathmandu', 'रु 30,000 - 45,000'],
        ['Sales Executive', 'Marketing', 'Kathmandu', 'रु 25,000 - 40,000'],
        ['Graphic Designer', 'IT', 'Lalitpur', 'रु 30,000 - 50,000'],
        ['Nurse', 'Health', 'Kathmandu', 'रु 35,000 - 55,000'],
        ['Admin Assistant', 'Admin', 'Kathmandu', 'रु 20,000 - 30,000'],
    ];
    
    $companies = ['ABC Company', 'XYZ Corp', 'Tech Solutions', 'Global Services', 'Nepal Ventures', 'Smart Tech', 'Prime Group'];
    
    $jobs = [];
    foreach ($positions as $i => $pos) {
        $daysAgo = rand(0, 7);
        $jobs[] = [
            'title' => $pos[0],
            'link' => '#',
            'summary' => $pos[0] . ' needed for ' . $companies[array_rand($companies)] . '. ' . $pos[3] . ' per month.',
            'source' => 'Sample',
            'sourceCls' => 'bg-slate-100 text-slate-700',
            'ts' => time() - ($daysAgo * 86400),
            'ago' => $daysAgo === 0 ? 'भर्खर' : $daysAgo . ' दिन अघि',
            'category' => strtolower($pos[1]),
            'image' => null,
        ];
    }
    
    return $jobs;
}

$cacheFile = "$cacheDir/nokari_all.json";
$cacheTtl  = 1800;

$allItems = null;
if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    $cached = @json_decode((string)@file_get_contents($cacheFile), true);
    if (is_array($cached) && count($cached) > 0) $allItems = $cached;
}

if (!$allItems) {
    $allItems = [];
    $errors = [];
    foreach ($feeds as [$url, $src, $cls]) {
        try {
            $raw = job_get($url, 6);
            if ($raw) {
                $items = parseRSSJobs($raw, $src, $cls);
                $allItems = array_merge($allItems, $items);
            } else {
                $errors[] = "$src: No data received";
            }
        } catch (Exception $e) {
            $errors[] = "$src: " . $e->getMessage();
        }
    }
    usort($allItems, fn($a,$b)=>$b['ts']<=>$a['ts']);
    
    // If no data from any source, use fallback
    if (empty($allItems)) {
        $allItems = getSampleJobs();
    }
    
    if (!empty($allItems)) @file_put_contents($cacheFile, json_encode($allItems, JSON_UNESCAPED_UNICODE));
}

/* ── Filter by category ── */
if ($cat && $cat !== 'all') {
    $allItems = array_values(array_filter($allItems, fn($it)=>$it['category']===$cat));
}

echo json_encode([
    'ok'     => true,
    'count'  => count(array_slice($allItems, 0, $limit)),
    'total'  => count($allItems),
    'cat'    => $cat,
    'items'  => array_values(array_slice($allItems, 0, $limit)),
    'ts'     => time(),
    'errors' => $errors ?? [],
    'sources' => count($feeds),
], JSON_UNESCAPED_UNICODE);
