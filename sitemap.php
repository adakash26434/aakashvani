<?php
/**
 * Dynamic sitemap.xml — built from static routes + live DB rows.
 * Served at /sitemap.xml via mod_rewrite (.htaccess) or directly at /sitemap.php.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex'); // the sitemap itself shouldn't rank

$base = rtrim(defined('SITE_URL') ? SITE_URL : '', '/');

/** @var array<int,array{loc:string,lastmod?:string,changefreq?:string,priority?:string}> */
$urls = [];

// ── Static pages ─────────────────────────────────────────────────────────────
$static = [
    ['/',                  'hourly', '1.0'],
    ['/news.php',          'hourly', '0.95'],
    ['/nepali-patro.php',  'daily',  '0.9'],
    ['/rashifal.php',      'daily',  '0.85'],
    ['/ipo-tracker.php',   'hourly', '0.9'],
    ['/tools.php',         'weekly', '0.7'],
    ['/tax-calculator.php','monthly','0.7'],
    ['/gov-services.php',  'monthly','0.75'],
    ['/utilities.php',     'hourly', '0.8'],
    ['/alerts.php',        'hourly', '0.85'],
    ['/morning-brief.php', 'daily',  '0.8'],
    ['/emergency.php',     'monthly','0.7'],
    ['/downloads.php',     'monthly','0.6'],
    ['/transport.php',     'monthly','0.6'],
    ['/ai-guides.php',     'weekly', '0.7'],
    ['/ai-chat.php',       'weekly', '0.6'],
    ['/about.php',         'monthly','0.5'],
    ['/ssf.php',           'weekly', '0.75'],
    ['/vehicle.php',       'weekly', '0.75'],
    ['/contact.php',       'monthly','0.5'],
    ['/privacy.php',       'yearly', '0.3'],
    ['/help.php',          'monthly','0.4'],
    ['/sources.php',       'monthly','0.4'],
    ['/notices.php',       'daily',  '0.8'],
    ['/login.php',         'yearly', '0.3'],
    ['/register.php',      'yearly', '0.3'],
];
foreach ($static as [$path, $cf, $pr]) {
    $urls[] = ['loc'=>$base.$path, 'changefreq'=>$cf, 'priority'=>$pr];
}

// ── News articles (news_cache — the actual table in database.sql) ─────────────
// NOTE: 'tech_news' table does not exist in this schema; news is stored in
// news_cache (source + news_json). We expose the cached RSS articles individually.
try {
    $rows = db()->query(
        "SELECT source, updated_at AS lastmod
         FROM news_cache
         ORDER BY updated_at DESC
         LIMIT 500"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $urls[] = [
            'loc'        => $base . '/news.php?source=' . rawurlencode($r['source']),
            'lastmod'    => date('c', strtotime((string)$r['lastmod'])),
            'changefreq' => 'hourly',
            'priority'   => '0.8',
        ];
    }
} catch (\Throwable $e) { error_log('[sitemap] news_cache: '.$e->getMessage()); }

// ── Notices ──────────────────────────────────────────────────────────────────
try {
    if (function_exists('ensureNoticesTable')) ensureNoticesTable();
    $rows = db()->query(
        "SELECT id, created_at AS lastmod
         FROM notices
         WHERE is_active = 1
         ORDER BY created_at DESC
         LIMIT 2000"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $urls[] = [
            'loc'        => $base . '/notices.php?id=' . (int)$r['id'],
            'lastmod'    => date('c', strtotime((string)$r['lastmod'])),
            'changefreq' => 'monthly',
            'priority'   => '0.6',
        ];
    }
} catch (\Throwable $e) { error_log('[sitemap] notices: '.$e->getMessage()); }

// ── Emit ─────────────────────────────────────────────────────────────────────
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
foreach ($urls as $u) {
    echo '  <url>';
    echo '<loc>'.htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>';
    if (!empty($u['lastmod']))    echo '<lastmod>'.$u['lastmod'].'</lastmod>';
    if (!empty($u['changefreq'])) echo '<changefreq>'.$u['changefreq'].'</changefreq>';
    if (!empty($u['priority']))   echo '<priority>'.$u['priority'].'</priority>';
    echo '</url>'."\n";
}
echo '</urlset>'."\n";