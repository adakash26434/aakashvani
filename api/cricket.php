<?php
/**
 * api/cricket.php — Cricket live scores, results, schedule
 * Sources: TheSportsDB (free), CricAPI fallback, Nepal cricket news RSS
 * Cache: 10 min for live, 30 min for schedule/results
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: *');
@ini_set('default_socket_timeout', 7);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$mode   = $_GET['mode'] ?? 'all';   // all | live | upcoming | results | news | nepal
$cacheDir = __DIR__ . '/../data/cache';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

function ckt_get(string $url, int $timeout = 7): string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; AakashVani/1.0)',
            CURLOPT_HTTPHEADER     => ['Accept: application/json,text/xml,*/*'],
            CURLOPT_ENCODING       => '',
        ]);
        $r = curl_exec($ch);
        $c = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($r !== false && $c >= 200 && $c < 400) return (string)$r;
    }
    $ctx = stream_context_create(['http'=>['timeout'=>$timeout,'user_agent'=>'AakashVani/1.0','follow_location'=>1],'ssl'=>['verify_peer'=>false]]);
    return (string)@file_get_contents($url, false, $ctx);
}

function ckt_ago(int $ts): string {
    $d = time() - $ts;
    if ($d < 60) return 'भर्खर';
    if ($d < 3600) return (int)($d/60) . ' मिनेट अघि';
    if ($d < 86400) return (int)($d/3600) . ' घण्टा अघि';
    return date('M j', $ts);
}

function ckt_cached(string $key, int $ttl, callable $fn): array {
    global $cacheDir;
    $f = "$cacheDir/cricket_{$key}.json";
    if (is_file($f) && (time() - filemtime($f) < $ttl)) {
        $d = @json_decode((string)@file_get_contents($f), true);
        if (is_array($d) && !empty($d)) return $d;
    }
    $data = $fn();
    if (!empty($data)) @file_put_contents($f, json_encode($data, JSON_UNESCAPED_UNICODE));
    return $data;
}

/* ── TheSportsDB league IDs ── */
define('CKT_LEAGUES', [
    'ipl'        => ['id'=>'4337', 'name'=>'IPL',                  'flag'=>'🇮🇳'],
    'intl'       => ['id'=>'4688', 'name'=>'International Cricket', 'flag'=>'🌍'],
    'wpl'        => ['id'=>'5296', 'name'=>'Women Premier League',  'flag'=>'🏏'],
    't20wc'      => ['id'=>'4688', 'name'=>'T20 World Cup',         'flag'=>'🌏'],
]);

/* ── Fetch matches from TheSportsDB ── */
function fetchSportsDB(string $leagueId, string $type = 'next'): array {
    $endpoint = $type === 'next'
        ? "https://www.thesportsdb.com/api/v1/json/3/eventsnextleague.php?id={$leagueId}"
        : "https://www.thesportsdb.com/api/v1/json/3/eventspastleague.php?id={$leagueId}";

    $raw = ckt_get($endpoint, 6);
    if (!$raw) return [];
    $data = @json_decode($raw, true);
    $events = $data['events'] ?? [];
    if (!is_array($events)) return [];

    $matches = [];
    foreach ($events as $ev) {
        $home  = $ev['strHomeTeam'] ?? '';
        $away  = $ev['strAwayTeam'] ?? '';
        $score = '';
        if (!empty($ev['intHomeScore']) || !empty($ev['intAwayScore'])) {
            $score = ($ev['intHomeScore'] ?? '0') . ' - ' . ($ev['intAwayScore'] ?? '0');
        }
        $dateStr = $ev['dateEvent'] ?? '';
        $timeStr = $ev['strTime'] ?? '';
        $ts = $dateStr ? strtotime($dateStr . ' ' . $timeStr) : 0;
        $status = !empty($score) ? 'result' : ($ts > time() ? 'upcoming' : 'live');

        $matches[] = [
            'id'       => $ev['idEvent'] ?? '',
            'title'    => ($home && $away) ? "$home vs $away" : ($ev['strEvent'] ?? ''),
            'home'     => $home,
            'away'     => $away,
            'score'    => $score,
            'status'   => $status,
            'venue'    => $ev['strVenue'] ?? '',
            'date'     => $dateStr,
            'time'     => $timeStr,
            'ts'       => $ts,
            'league'   => $ev['strLeague'] ?? '',
            'season'   => $ev['strSeason'] ?? '',
            'thumb'    => $ev['strThumb'] ?? '',
            'result'   => $ev['strResult'] ?? '',
        ];
    }
    return $matches;
}

/* ── Fetch Nepal cricket news from sports RSS ── */
function fetchNepalCricketNews(): array {
    $feeds = [
        'https://ratopati.com/category/sports/feed'       => 'Ratopati Sports',
        'https://goalnepal.com/feed/'                     => 'GoalNepal',
        'https://www.onlinekhabar.com/category/sports/feed' => 'OnlineKhabar Sports',
    ];

    $items = [];
    foreach ($feeds as $url => $src) {
        $raw = ckt_get($url, 5);
        if (!$raw) continue;
        preg_match_all('/<item>(.*?)<\/item>/si', $raw, $m);
        foreach (($m[1] ?? []) as $block) {
            $title = '';
            if (preg_match('/<title><!\[CDATA\[(.*?)\]\]><\/title>/si', $block, $t)) $title = trim($t[1]);
            elseif (preg_match('/<title>(.*?)<\/title>/si', $block, $t)) $title = html_entity_decode(trim($t[1]), ENT_QUOTES, 'UTF-8');

            // Only cricket-related
            $titleLower = mb_strtolower($title);
            $cricketKw = ['cricket','क्रिकेट','ipl','t20','odi','test match','team nepal','नेपाल टोली','विश्वकप','world cup'];
            $isCricket = false;
            foreach ($cricketKw as $kw) { if (mb_strpos($titleLower, $kw) !== false) { $isCricket = true; break; } }
            if (!$isCricket) continue;

            $link = '';
            if (preg_match('/<link>(.*?)<\/link>/si', $block, $l)) $link = trim($l[1]);
            elseif (preg_match('/<link[^>]*href=["\']([^"\']+)["\']/si', $block, $l)) $link = trim($l[1]);

            $desc = '';
            if (preg_match('/<description><!\[CDATA\[(.*?)\]\]><\/description>/si', $block, $d)) $desc = trim(strip_tags($d[1]));
            elseif (preg_match('/<description>(.*?)<\/description>/si', $block, $d)) $desc = trim(strip_tags(html_entity_decode($d[1], ENT_QUOTES, 'UTF-8')));
            $desc = mb_substr($desc, 0, 200, 'UTF-8');

            $pubDate = '';
            if (preg_match('/<pubDate>(.*?)<\/pubDate>/si', $block, $pd)) $pubDate = trim($pd[1]);
            $ts = $pubDate ? strtotime($pubDate) : 0;

            $img = null;
            if (preg_match('/<media:thumbnail[^>]+url=["\']([^"\']+)["\']/si', $block, $im)) $img = $im[1];
            elseif (preg_match('/<media:content[^>]+url=["\']([^"\']+)["\']/si', $block, $im)) $img = $im[1];
            elseif (preg_match('/<enclosure[^>]+url=["\']([^"\']+\.(?:jpg|jpeg|png|webp))["\']/si', $block, $im)) $img = $im[1];

            if ($title && $link) {
                $items[] = ['title'=>$title,'link'=>$link,'summary'=>$desc,'source'=>$src,'ts'=>$ts,'image'=>$img,'ago'=>ckt_ago($ts)];
            }
        }
        if (count($items) >= 15) break;
    }
    usort($items, fn($a,$b)=>$b['ts']<=>$a['ts']);
    return array_slice($items, 0, 12);
}

/* ── Main logic ── */
$response = ['ok'=>true, 'mode'=>$mode, 'ts'=>time()];

if ($mode === 'news' || $mode === 'all') {
    $news = ckt_cached('news', 900, fn() => fetchNepalCricketNews());
    $response['news'] = $news;
}

if ($mode === 'upcoming' || $mode === 'all') {
    $upcoming = ckt_cached('upcoming', 1800, function() {
        $matches = [];
        foreach (['ipl','intl'] as $k) {
            $lg = CKT_LEAGUES[$k];
            $m  = fetchSportsDB($lg['id'], 'next');
            foreach ($m as &$ev) { $ev['league_key'] = $k; $ev['league_flag'] = $lg['flag']; }
            $matches = array_merge($matches, $m);
        }
        usort($matches, fn($a,$b)=>$a['ts']<=>$b['ts']);
        return array_slice($matches, 0, 10);
    });
    $response['upcoming'] = $upcoming;
}

if ($mode === 'results' || $mode === 'all') {
    $results = ckt_cached('results', 1800, function() {
        $matches = [];
        foreach (['ipl','intl'] as $k) {
            $lg = CKT_LEAGUES[$k];
            $m  = fetchSportsDB($lg['id'], 'past');
            foreach ($m as &$ev) { $ev['league_key'] = $k; $ev['league_flag'] = $lg['flag']; }
            $matches = array_merge($matches, $m);
        }
        usort($matches, fn($a,$b)=>$b['ts']<=>$a['ts']);
        return array_slice($matches, 0, 10);
    });
    $response['results'] = $results;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
