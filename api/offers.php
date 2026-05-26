<?php
/**
 * api/offers.php v1 — Nepal ISP & Company Offers/Deals
 * Serves curated + live-scraped offers from DB
 * ?cat=isp|telecom|ecommerce|fintech|food|travel|bank|all
 * ?company=NTC|Ncell|Daraz...  ?limit=60
 *
 * Live scraping: runs when DB is stale (>2 hrs), non-blocking on subsequent requests
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../functions.php';

$cat     = strtolower(trim($_GET['cat']     ?? ''));
$company = trim($_GET['company'] ?? '');
$limit   = max(1, min(100, (int)($_GET['limit'] ?? 60)));

/* ── ensure DB seeded ─────────────────────────────────────────────── */
ensureOffersTable();

/* ── try live scrape for ISP offers (if stale > 2h) ──────────────── */
$newest = db()->query('SELECT MAX(fetched_at) FROM offers WHERE is_curated=0')->fetchColumn();
$stale  = $newest ? (time() - (int)@strtotime($newest)) > 7200 : true;
if ($stale) {
    _scrapeLiveOffers();
}

/* ── serve ────────────────────────────────────────────────────────── */
$items     = getActiveOffers($cat === 'all' ? '' : $cat, $company, $limit);
$catCounts = getOfferCatCounts();

echo json_encode([
    'ok'    => true,
    'cat'   => $cat,
    'count' => count($items),
    'cats'  => $catCounts,
    'items' => $items,
], JSON_UNESCAPED_UNICODE);

/* ── live scraper ─────────────────────────────────────────────────── */
function _scrapeLiveOffers(): void {
    _scrapeNcellOffers();
    _scrapeNtcOffers();
    _scrapeDarazOffers();
}

function _scrapeLiveOffers_fetch(string $url, int $timeout = 8): string {
    $ctx = stream_context_create([
        'http'  => ['timeout'=>$timeout,'user_agent'=>'Mozilla/5.0 (compatible; AakashvaniBot/2.0)','follow_location'=>1,'header'=>"Accept-Charset: utf-8\r\n"],
        'https' => ['timeout'=>$timeout,'user_agent'=>'Mozilla/5.0 (compatible; AakashvaniBot/2.0)','follow_location'=>1,'header'=>"Accept-Charset: utf-8\r\n"],
    ]);
    $r = @file_get_contents($url, false, $ctx);
    if ($r === false || $r === '') return '';
    $enc = null;
    if (preg_match('/<meta[^>]+charset=["\']?([^"\'>\s]+)/i', $r, $m)) $enc = strtoupper(trim($m[1]));
    if (!$enc) {
        $det = @mb_detect_encoding($r, ['UTF-8','ISO-8859-1','WINDOWS-1252'], true);
        $enc = $det ?: 'UTF-8';
    }
    if ($enc !== 'UTF-8' && $enc !== 'UTF8') {
        $conv = @mb_convert_encoding($r, 'UTF-8', $enc);
        if ($conv !== false) $r = $conv;
    }
    return $r;
}

function _scrapeNcellOffers(): void {
    $html = _scrapeLiveOffers_fetch('https://www.ncell.axiata.com/en/personal/offers', 10);
    if (!$html) return;
    // Look for offer cards — title + price patterns
    preg_match_all('/<(?:h[234]|strong)[^>]*>\s*([^<]{5,100})\s*<\/(?:h[234]|strong)>/si', $html, $m);
    $inserted = 0;
    foreach (($m[1] ?? []) as $title) {
        $title = trim(html_entity_decode(strip_tags($title), ENT_QUOTES, 'UTF-8'));
        if (!$title || strlen($title) < 5 || $inserted > 5) continue;
        if (!preg_match('/\d/', $title)) continue; // must have a number (GB, Rs, %)
        upsertOffer([
            'slug'        => 'ncell-live-' . md5($title),
            'title'       => $title,
            'summary'     => 'Ncell को लाइभ offer — ncell.axiata.com बाट',
            'company'     => 'Ncell',
            'cat'         => 'isp',
            'badge'       => 'Live',
            'price'       => '',
            'discount_pct'=> 0,
            'url'         => 'https://www.ncell.axiata.com/en/personal/offers',
        ]);
        $inserted++;
    }
}

function _scrapeNtcOffers(): void {
    $html = _scrapeLiveOffers_fetch('https://www.ntc.net.np/pages/data-offers', 10);
    if (!$html) return;
    preg_match_all('/<(?:h[234]|strong)[^>]*>\s*([^<]{5,120})\s*<\/(?:h[234]|strong)>/si', $html, $m);
    $inserted = 0;
    foreach (($m[1] ?? []) as $title) {
        $title = trim(html_entity_decode(strip_tags($title), ENT_QUOTES, 'UTF-8'));
        if (!$title || strlen($title) < 5 || $inserted > 5) continue;
        if (!preg_match('/\d/', $title)) continue;
        upsertOffer([
            'slug'        => 'ntc-live-' . md5($title),
            'title'       => $title,
            'summary'     => 'NTC को लाइभ offer — ntc.net.np बाट',
            'company'     => 'NTC',
            'cat'         => 'isp',
            'badge'       => 'Live',
            'price'       => '',
            'discount_pct'=> 0,
            'url'         => 'https://www.ntc.net.np/pages/data-offers',
        ]);
        $inserted++;
    }
}

function _scrapeDarazOffers(): void {
    // Daraz flash sale page (lightweight scrape for banner titles)
    $html = _scrapeLiveOffers_fetch('https://www.daraz.com.np/campaigns/flash-sale/', 10);
    if (!$html) return;
    // Look for discount percentages
    preg_match_all('/(\d{10,80}%)\s*(?:off|छुट)/i', $html, $m);
    if (!empty($m[0])) {
        $maxDisc = max(array_map(fn($x) => (int)$x, array_map('intval', $m[1])));
        if ($maxDisc > 0) {
            upsertOffer([
                'slug'        => 'daraz-flash-live-' . date('Ymd'),
                'title'       => 'Daraz Flash Sale — आज ' . $maxDisc . '% सम्म छुट!',
                'summary'     => 'Daraz Flash Sale — Electronics, Fashion, Food र अन्यमा ठूलो छुट। App बाट extra छुट!',
                'company'     => 'Daraz',
                'cat'         => 'ecommerce',
                'badge'       => '🔥 Flash',
                'price'       => '',
                'discount_pct'=> $maxDisc,
                'url'         => 'https://www.daraz.com.np/campaigns/flash-sale/',
            ]);
        }
    }
}
