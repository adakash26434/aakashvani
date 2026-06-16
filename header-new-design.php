<?php
/**
 * आकाशवाणी — header.php (Professional Desktop Design)
 * Full-width enterprise News Portal layout
 * Desktop-first with responsive mobile support
 * 
 * Based on tankaadhikari.com.np design principles
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/seo-helper.php';

// ── Per-page SEO override (from DB) ───────────────────────────────────────────
$_seoPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$_seoRow  = getPageSeo($_seoPath);

// ── Language Helper ────────────────────────────────────────────────────────────
if (!function_exists('siteLang')) {
    function siteLang(): string {
        return ($_COOKIE['site_lang'] ?? 'ne') === 'en' ? 'en' : 'ne';
    }
}
$lang = siteLang();
$isNepali = ($lang !== 'en');
$tH = function($ne, $en) use ($lang) {
    return $lang === 'ne' ? $ne : $en;
};

// ── SEO Meta ───────────────────────────────────────────────────────────────────
$_dbTitle = !empty($_seoRow['meta_title']) ? $_seoRow['meta_title'] : '';
$_dbDesc  = !empty($_seoRow['meta_description']) ? $_seoRow['meta_description'] : getSeoSetting('meta_description','');
$_dbImg   = !empty($_seoRow['og_image']) ? $_seoRow['og_image'] : getSeoSetting('og_image_default','');
$_noindex = !empty($_seoRow['is_noindex']);

if (!isset($pageTitle)) {
    $_tpl = getSeoSetting('meta_title_template','');
    if ($_dbTitle) {
        $pageTitle = $_dbTitle;
    } elseif ($_tpl) {
        $pageTitle = str_replace('%page%', (defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी'), $_tpl);
    } else {
        $pageTitle = (defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी') . ' — सूचनाको खुला आकाश';
    }
}
if (!isset($pageDesc)) $pageDesc = $_dbDesc ?: 'आकाशवाणी — AI News, Patro, Rashifal, NEPSE, IPO र सरकारी सेवा सबै एकै App मा।';
if (!isset($pageUrl))  $pageUrl  = (defined('SITE_URL') ? SITE_URL : '') . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if (!isset($pageImg))  $pageImg  = $_dbImg ?: (defined('OG_IMAGE') ? OG_IMAGE : (defined('SITE_URL') ? SITE_URL : '') . '/assets/images/og-image.jpg');

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isHome = in_array($currentPath, ['/', '/index.php', '/home.php']);

// ── BS Date ────────────────────────────────────────────────────────────────────
$nepal = new DateTimeZone('Asia/Kathmandu');
$now   = new DateTime('now', $nepal);
[$adY,$adM,$adD,$adDow] = [(int)$now->format('Y'),(int)$now->format('n'),(int)$now->format('j'),(int)$now->format('w')];
$_bsData=[2080=>[0,31,32,31,32,31,30,30,29,30,29,30,30],2081=>[0,31,31,32,31,31,31,30,29,30,29,30,30],
          2082=>[0,31,32,31,32,31,30,30,30,29,30,29,31],2083=>[0,31,32,31,32,31,30,30,30,29,30,30,30],
          2084=>[0,31,31,32,31,31,30,30,30,29,30,30,30],2085=>[0,31,32,31,32,31,30,30,30,29,30,29,31],
          2086=>[0,31,32,31,32,31,30,30,30,29,30,29,31],2087=>[0,31,31,32,31,31,31,30,29,30,29,30,30]];
$refJd=gregoriantojd(4,14,2026); $jdNow=gregoriantojd($adM,$adD,$adY); $diff=$jdNow-$refJd;
$bsY=2083;$bsM=1;$bsD=1;
if($diff>=0){$rem=$diff;while($rem>0){$dim=$_bsData[$bsY][$bsM]??30;$left=$dim-$bsD;if($rem<=$left){$bsD+=$rem;$rem=0;}else{$rem-=($left+1);$bsD=1;$bsM++;if($bsM>12){$bsM=1;$bsY++;}}}}
else{$rem=-$diff;while($rem>0){if($bsD>1){$s=min($rem,$bsD-1);$bsD-=$s;$rem-=$s;}else{$bsM--;if($bsM<1){$bsM=12;$bsY--;}$bsD=$_bsData[$bsY][$bsM]??30;$rem-=1;}}}
$_bsMonths=['','बैशाख','जेठ','असार','श्रावण','भाद्र','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
$_bsDays=['आइतबार','सोमबार','मंगलबार','बुधबार','बिहिबार','शुक्रबार','शनिबार'];
$bsDateStr = $_bsDays[$adDow].', '.$bsD.' '.$_bsMonths[$bsM].' '.$bsY;
$bsShort   = $bsD.' '.$_bsMonths[$bsM].' '.$bsY;

/* Greeting by hour */
$hr = (int)$now->format('G');
$greetNe = $hr < 11 ? 'शुभ प्रभात' : ($hr < 16 ? 'नमस्कार' : ($hr < 19 ? 'शुभ साँझ' : 'शुभ रात्री'));

// ── Navigation ─────────────────────────────────────────────────────────────────
$mainNav=[
    '/'                => ['ne'=>'गृह',          'en'=>'Home',        'icon'=>'home'],
    '/news.php'        => ['ne'=>'समाचार',        'en'=>'News',        'icon'=>'newspaper'],
    '/nepali-patro.php'=> ['ne'=>'पात्रो',        'en'=>'Calendar',    'icon'=>'calendar'],
    '/rashifal.php'    => ['ne'=>'राशिफल',        'en'=>'Rashifal',    'icon'=>'star'],
    '/info-hub.php'    => ['ne'=>'सबै जानकारी',  'en'=>'Info Hub',    'icon'=>'layout-grid'],
    '/ipo-tracker.php' => ['ne'=>'NEPSE/IPO',     'en'=>'NEPSE/IPO',   'icon'=>'trending-up'],
    '/tools.php'       => ['ne'=>'टूलहरू',        'en'=>'Tools',       'icon'=>'wrench'],
    '/gov-services.php'=> ['ne'=>'सरकारी सेवा',   'en'=>'Gov',         'icon'=>'landmark'],
];

$moreNav=[
    '/cricket.php'       => ['ne'=>'क्रिकेट',      'en'=>'Cricket',      'icon'=>'trophy'],
    '/nokari.php'        => ['ne'=>'नोकरी',         'en'=>'Jobs',         'icon'=>'briefcase'],
    '/loksewa.php'       => ['ne'=>'लोकसेवा',       'en'=>'Loksewa',      'icon'=>'building'],
    '/weather.php'       => ['ne'=>'मौसम',          'en'=>'Weather',      'icon'=>'cloud'],
    '/gold-price.php'    => ['ne'=>'सुनको मूल्य',   'en'=>'Gold Price',   'icon'=>'gem'],
    '/currency-converter.php'=> ['ne'=>'मुद्रा',      'en'=>'Currency',     'icon'=>'coins'],
    '/emergency.php'     => ['ne'=>'आपतकालीन',     'en'=>'Emergency',    'icon'=>'phone'],
    '/market.php'        => ['ne'=>'बजार',          'en'=>'Market',       'icon'=>'bar-chart-2'],
    '/tax-calculator.php'=> ['ne'=>'कर Calculator',  'en'=>'Tax Calc',     'icon'=>'calculator'],
    '/ai-guides.php'     => ['ne'=>'AI Guides',      'en'=>'AI Guides',    'icon'=>'bot'],
];

function navActDesk(string $href, string $path): bool {
    if($href==='/') return in_array($path,['/','/index.php','/home.php']);
    return str_starts_with($path, rtrim($href,'/'));
}

$isLoggedIn = function_exists('isLoggedIn') && isLoggedIn();
$cu = ($isLoggedIn && function_exists('getCurrentUser')) ? getCurrentUser() : null;
$userInitial = $cu && !empty($cu['name']) ? mb_substr($cu['name'],0,1) : 'U';
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>" class="scroll-smooth">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc,ENT_QUOTES,'UTF-8') ?>"/>
<meta name="robots" content="<?= $_noindex ? 'noindex,nofollow' : 'index,follow,max-image-preview:large' ?>"/>
<?php $kw = !empty($_seoRow['meta_keywords']) ? $_seoRow['meta_keywords'] : getSeoSetting('meta_keywords',''); if ($kw): ?>
<meta name="keywords" content="<?= htmlspecialchars($kw,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>
<link rel="canonical" href="<?= htmlspecialchars($pageUrl,ENT_QUOTES,'UTF-8') ?>"/>

<!-- SEO Meta -->
<?php if ($_gscMeta = getSeoSetting('gsc_meta','')): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars($_gscMeta,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>
<?php if ($_bingMeta = getSeoSetting('bing_meta','')): ?>
<meta name="msvalidate.01" content="<?= htmlspecialchars($_bingMeta,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="website"/>
<meta property="og:site_name" content="<?= htmlspecialchars(defined('SITE_NAME')?SITE_NAME:'आकाशवाणी',ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:title" content="<?= htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:description" content="<?= htmlspecialchars($pageDesc,ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:image" content="<?= htmlspecialchars($pageImg,ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:url" content="<?= htmlspecialchars($pageUrl,ENT_QUOTES,'UTF-8') ?>"/>
<?php if ($_fbAppId = getSeoSetting('facebook_app_id','')): ?>
<meta property="fb:app_id" content="<?= htmlspecialchars($_fbAppId,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8') ?>"/>
<meta name="twitter:description" content="<?= htmlspecialchars($pageDesc,ENT_QUOTES,'UTF-8') ?>"/>
<meta name="twitter:image" content="<?= htmlspecialchars($pageImg,ENT_QUOTES,'UTF-8') ?>"/>
<?php if ($_twHandle = getSeoSetting('twitter_handle','')): ?>
<meta name="twitter:site" content="@<?= htmlspecialchars($_twHandle,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>

<!-- Schema.org JSON-LD -->
<?php if (getSeoSetting('schema_enabled','') === '1'):
    $_sName = getSeoSetting('schema_org_name', defined('SITE_NAME')?SITE_NAME:'आकाशवाणी');
    $_sUrl  = getSeoSetting('schema_org_url',  defined('SITE_URL')?SITE_URL:'');
    $_sLogo = getSeoSetting('schema_org_logo', $_sUrl.'/assets/images/logo.png');
    $_sDesc = getSeoSetting('schema_org_desc', '');
    $_sFb   = getSeoSetting('schema_org_fb',   '');
    $_sTw   = getSeoSetting('schema_org_twitter','');
    $_sameAs = array_values(array_filter([$_sFb,$_sTw]));
    $orgSchema = ['@context'=>'https://schema.org','@graph'=>[
        ['@type'=>'Organization','name'=>$_sName,'url'=>$_sUrl,'logo'=>['@type'=>'ImageObject','url'=>$_sLogo],'description'=>$_sDesc,'sameAs'=>$_sameAs],
        ['@type'=>'WebSite','name'=>$_sName,'url'=>$_sUrl,
         'potentialAction'=>getSeoSetting('schema_search','')===('1') ? [['@type'=>'SearchAction','target'=>['@type'=>'EntryPoint','urlTemplate'=>$_sUrl.'/news.php?q={search_term_string}'],'query-input'=>'required name=search_term_string']] : []],
    ]];
?>
<script type="application/ld+json"><?= json_encode($orgSchema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<!-- PWA -->
<link rel="manifest" href="/api/pwa-manifest.php"/>
<meta name="theme-color" content="#10B981"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="apple-mobile-web-app-title" content="<?= defined('PWA_SHORT_NAME') ? htmlspecialchars(PWA_SHORT_NAME) : 'आकाशवाणी' ?>"/>
<link rel="apple-touch-icon" href="/assets/icons/icon-192.png"/>
<link rel="icon" type="image/svg+xml" href="/assets/favicon.svg"/>

<!-- DNS Prefetch -->
<link rel="dns-prefetch" href="https://fonts.googleapis.com"/>
<link rel="dns-prefetch" href="https://fonts.gstatic.com"/>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet"/>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<!-- Design System CSS -->
<link rel="stylesheet" href="/assets/css/global.css"/>

<style>
/* ═══════════════════════════════════════════════════════════════
   PROFESSIONAL DESKTOP HEADER - Enterprise News Portal
   ═══════════════════════════════════════════════════════════════ */

:root {
    --primary: #10B981;
    --primary-dark: #059669;
    --primary-light: #ecfdf5;
    --text-dark: #0f172a;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --bg-light: #f8fafc;
    --bg-white: #ffffff;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-lg: 0 4px 20px rgba(0,0,0,0.1);
    --radius: 8px;
    --radius-lg: 12px;
    --container: 1400px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', 'Hind Siliguri', system-ui, sans-serif;
    background: var(--bg-light);
    color: var(--text-dark);
    line-height: 1.6;
}

/* Container */
.container {
    max-width: var(--container);
    margin: 0 auto;
    padding: 0 24px;
}

/* ─── TOP BAR ─────────────────────────────────────────────── */
.topbar {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #e2e8f0;
    padding: 10px 0;
    font-size: 13px;
}

.topbar-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar-left,
.topbar-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.topbar-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
}

.topbar-sep { color: #475569; }

.topbar-link {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #94a3b8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.topbar-link:hover { color: #fff; }

.topbar-lang {
    background: var(--primary);
    color: #fff !important;
    padding: 5px 12px;
    border-radius: var(--radius);
}

/* ─── MAIN HEADER ─────────────────────────────────────────── */
.main-header {
    background: var(--bg-white);
    padding: 20px 0;
    border-bottom: 1px solid var(--border);
    box-shadow: var(--shadow);
}

.header-inner {
    display: flex;
    align-items: center;
    gap: 32px;
}

/* Logo */
.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    flex-shrink: 0;
}

.logo-icon {
    width: 48px;
    height: 48px;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-dark);
    letter-spacing: -0.02em;
}

.logo-tagline {
    font-size: 11px;
    color: var(--text-muted);
}

/* Search */
.header-search {
    flex: 1;
    max-width: 560px;
}

.search-form {
    position: relative;
    display: flex;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 14px 50px 14px 46px;
    background: var(--bg-light);
    border: 2px solid transparent;
    border-radius: var(--radius-lg);
    font-size: 15px;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    background: var(--bg-white);
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
}

.search-btn {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.search-btn:hover { background: var(--primary-dark); }

/* Header Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    background: transparent;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: var(--bg-light);
    color: var(--text-dark);
}

.user-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    text-decoration: none;
    transition: transform 0.2s;
}

.user-avatar:hover { transform: scale(1.05); }

.login-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s;
}

.login-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16,185,129,0.3);
}

/* ─── NAVIGATION ─────────────────────────────────────────── */
.main-nav {
    background: var(--bg-white);
    border-bottom: 3px solid var(--primary);
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-inner {
    display: flex;
    align-items: center;
    gap: 24px;
}

.nav-list {
    display: flex;
    align-items: center;
    gap: 4px;
    list-style: none;
}

.nav-item { position: relative; }

.nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 18px;
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    border-radius: var(--radius);
    transition: all 0.2s;
}

.nav-link:hover {
    background: var(--bg-light);
    color: var(--text-dark);
}

.nav-link.active {
    background: var(--primary);
    color: #fff;
}

.nav-link i { width: 18px; height: 18px; }

/* More Dropdown */
.more-trigger {
    background: none;
    border: none;
    cursor: pointer;
    font: inherit;
}

.more-icon { width: 14px; height: 14px; transition: transform 0.2s; }
.nav-item.more.open .more-icon { transform: rotate(180deg); }

.more-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 280px;
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
    opacity: 0;
    visibility: hidden;
    transform: translateY(8px);
    transition: all 0.2s;
    z-index: 100;
}

.nav-item.more.open .more-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(4px);
}

.more-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px;
    padding: 10px;
}

.more-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 500;
    border-radius: var(--radius);
    text-decoration: none;
    transition: all 0.15s;
}

.more-item:hover {
    background: var(--bg-light);
    color: var(--text-dark);
}

.more-item i { width: 18px; height: 18px; color: var(--primary); }

/* Live Ticker */
.live-ticker {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
    padding: 8px 0;
}

.live-badge {
    padding: 4px 10px;
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-radius: 4px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.live-text {
    font-size: 13px;
    color: var(--text-muted);
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ─── MAIN CONTENT ────────────────────────────────────────── */
.main-content {
    padding: 32px 0;
}

.content-grid {
    max-width: var(--container);
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 32px;
}

/* Sidebar */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sidebar-card {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    padding: 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.sidebar-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--primary);
}

.sidebar-title i { width: 18px; height: 18px; color: var(--primary); }

.sidebar-nav { list-style: none; }

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 500;
    border-radius: var(--radius);
    text-decoration: none;
    transition: all 0.15s;
}

.sidebar-link:hover {
    background: var(--bg-light);
    color: var(--text-dark);
}

.sidebar-link i:first-child { width: 18px; height: 18px; color: var(--primary); }
.sidebar-link i:last-child { width: 14px; height: 14px; color: #cbd5e1; margin-left: auto; }

/* Categories */
.cat-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.cat-chip {
    padding: 8px 16px;
    background: var(--bg-light);
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.15s;
}

.cat-chip:hover {
    background: var(--primary);
    color: #fff;
}

/* Market Widget */
.market-card {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: #fff;
}

.market-card .sidebar-title { color: #fff; border-bottom-color: var(--primary); }
.market-card .sidebar-title i { color: var(--primary); }

.market-list { display: flex; flex-direction: column; gap: 12px; }

.market-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.market-row:last-child { border-bottom: none; }

.market-label { font-size: 12px; color: #94a3b8; }
.market-value { font-size: 14px; font-weight: 700; color: #fff; }

/* Main Content Area */
.content-area {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    padding: 28px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    min-height: 600px;
}

/* ─── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 1200px) {
    .content-grid { grid-template-columns: 240px 1fr; gap: 24px; }
    .live-ticker { display: none; }
}

@media (max-width: 1024px) {
    .content-grid { grid-template-columns: 1fr; }
    .sidebar { display: none; }
    .logo-tagline { display: none; }
    .header-search { max-width: 400px; }
}

@media (max-width: 768px) {
    .container { padding: 0 16px; }
    .topbar { padding: 8px 0; }
    .topbar-left { display: none; }
    .header-inner { flex-wrap: wrap; gap: 16px; }
    .header-search { order: 3; max-width: 100%; width: 100%; }
    .logo-title { font-size: 20px; }
    .nav-link span { display: none; }
    .nav-link { padding: 14px; }
    .content-area { padding: 20px; }
}

/* Icon sizes */
.icon-sm { width: 14px; height: 14px; }
.icon-md { width: 18px; height: 18px; }
.icon-lg { width: 22px; height: 22px; }

/* Back to Top */
.back-to-top {
    position: fixed;
    bottom: 32px;
    right: 32px;
    width: 48px;
    height: 48px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(16,185,129,0.4);
    z-index: 1000;
}

.back-to-top.visible { opacity: 1; visibility: visible; transform: translateY(0); }
.back-to-top:hover { background: var(--primary-dark); transform: translateY(-2px); }
</style>

</head>
<body>

<!-- ─── TOP BAR ─────────────────────────────────────────────── -->
<div class="topbar">
    <div class="container">
        <div class="topbar-inner">
            <div class="topbar-left">
                <span class="topbar-item">
                    <i data-lucide="calendar" class="icon-sm"></i>
                    <?= $bsDateStr ?>
                </span>
                <span class="topbar-sep">|</span>
                <span class="topbar-item"><?= $greetNe ?></span>
            </div>
            <div class="topbar-right">
                <a href="?lang=<?= $isNepali ? 'en' : 'ne' ?>" class="topbar-link topbar-lang">
                    <i data-lucide="globe" class="icon-sm"></i>
                    <?= $isNepali ? 'EN' : 'नेपाली' ?>
                </a>
                <?php if ($isLoggedIn): ?>
                    <a href="/profile.php" class="topbar-link">
                        <i data-lucide="user" class="icon-sm"></i>
                        Profile
                    </a>
                <?php else: ?>
                    <a href="/login.php" class="topbar-link">
                        <i data-lucide="log-in" class="icon-sm"></i>
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ─── MAIN HEADER ─────────────────────────────────────────── -->
<header class="main-header">
    <div class="container">
        <div class="header-inner">
            
            <!-- Logo -->
            <a href="/" class="logo">
                <div class="logo-icon">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <rect width="48" height="48" rx="12" fill="url(#lg)"/>
                        <path d="M12 33V15L24 9L36 15V33L24 39L12 33Z" stroke="white" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M24 21V27M24 33V36" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="24" cy="16" r="3" fill="white"/>
                        <defs>
                            <linearGradient id="lg" x1="0" y1="0" x2="48" y2="48">
                                <stop stop-color="#10B981"/>
                                <stop offset="1" stop-color="#059669"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title">आकाशवाणी</span>
                    <span class="logo-tagline">सूचनाको खुला आकाश</span>
                </div>
            </a>
            
            <!-- Search -->
            <form action="/search.php" method="GET" class="header-search">
                <div class="search-form">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="search" name="q" placeholder="Search news, info..." class="search-input" autocomplete="off"/>
                    <button type="submit" class="search-btn">
                        <i data-lucide="arrow-right" class="icon-md"></i>
                    </button>
                </div>
            </form>
            
            <!-- Actions -->
            <div class="header-actions">
                <button class="action-btn" title="Notifications">
                    <i data-lucide="bell" class="icon-lg"></i>
                </button>
                <button class="action-btn" title="Bookmarks">
                    <i data-lucide="bookmark" class="icon-lg"></i>
                </button>
                <?php if ($isLoggedIn): ?>
                    <a href="/profile.php" class="user-avatar"><?= $userInitial ?></a>
                <?php else: ?>
                    <a href="/login.php" class="login-btn">
                        <i data-lucide="user" class="icon-md"></i>
                        Login
                    </a>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</header>

<!-- ─── NAVIGATION ─────────────────────────────────────────── -->
<nav class="main-nav">
    <div class="container">
        <div class="nav-inner">
            <ul class="nav-list">
                <?php foreach ($mainNav as $href => $item): ?>
                    <?php $active = navActDesk($href, $currentPath); ?>
                    <li class="nav-item">
                        <a href="<?= $href ?>" class="nav-link <?= $active ? 'active' : '' ?>">
                            <i data-lucide="<?= $item['icon'] ?>" class="nav-icon"></i>
                            <span><?= $isNepali ? $item['ne'] : $item['en'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
                
                <!-- More Dropdown -->
                <li class="nav-item more" id="more-dropdown">
                    <button class="nav-link more-trigger" aria-expanded="false">
                        <span>More</span>
                        <i data-lucide="chevron-down" class="more-icon"></i>
                    </button>
                    <div class="more-dropdown">
                        <div class="more-grid">
                            <?php foreach ($moreNav as $href => $item): ?>
                                <a href="<?= $href ?>" class="more-item">
                                    <i data-lucide="<?= $item['icon'] ?>"></i>
                                    <span><?= $isNepali ? $item['ne'] : $item['en'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
            </ul>
            
            <!-- Live Ticker -->
            <div class="live-ticker">
                <span class="live-badge">Live</span>
                <span class="live-text">Welcome to Aakashbani - Nepal's fastest information platform</span>
            </div>
        </div>
    </div>
</nav>

<!-- ─── MAIN CONTENT ────────────────────────────────────────── -->
<main class="main-content">
    <div class="content-grid">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            
            <!-- Quick Links -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i data-lucide="compass"></i>
                    Quick Links
                </h3>
                <ul class="sidebar-nav">
                    <li><a href="/news.php?sort=latest" class="sidebar-link">
                        <i data-lucide="clock"></i>
                        <span>Latest News</span>
                        <i data-lucide="chevron-right"></i>
                    </a></li>
                    <li><a href="/news.php?sort=trending" class="sidebar-link">
                        <i data-lucide="flame"></i>
                        <span>Trending</span>
                        <i data-lucide="chevron-right"></i>
                    </a></li>
                    <li><a href="/news.php?sort=popular" class="sidebar-link">
                        <i data-lucide="star"></i>
                        <span>Popular</span>
                        <i data-lucide="chevron-right"></i>
                    </a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">
                    <i data-lucide="grid-3x3"></i>
                    Categories
                </h3>
                <div class="cat-grid">
                    <a href="/news.php?category=politics" class="cat-chip">Politics</a>
                    <a href="/news.php?category=economy" class="cat-chip">Economy</a>
                    <a href="/news.php?category=sports" class="cat-chip">Sports</a>
                    <a href="/news.php?category=technology" class="cat-chip">Technology</a>
                    <a href="/news.php?category=entertainment" class="cat-chip">Entertainment</a>
                    <a href="/news.php?category=international" class="cat-chip">International</a>
                </div>
            </div>
            
            <!-- Market Widget -->
            <div class="sidebar-card market-card">
                <h3 class="sidebar-title">
                    <i data-lucide="trending-up"></i>
                    Market Summary
                </h3>
                <div class="market-list">
                    <div class="market-row">
                        <span class="market-label">NEPSE</span>
                        <span class="market-value" id="mkt-nepse">--</span>
                    </div>
                    <div class="market-row">
                        <span class="market-label">Gold (10g)</span>
                        <span class="market-value" id="mkt-gold">--</span>
                    </div>
                    <div class="market-row">
                        <span class="market-label">USD</span>
                        <span class="market-value" id="mkt-usd">--</span>
                    </div>
                    <div class="market-row">
                        <span class="market-label">Petrol</span>
                        <span class="market-value" id="mkt-petrol">--</span>
                    </div>
                </div>
            </div>
            
        </aside>
        
        <!-- Main Content Area -->
        <article class="content-area">

<script>
// Initialize Lucide Icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// More Dropdown
document.querySelectorAll('.more-trigger').forEach(function(trigger) {
    trigger.addEventListener('click', function() {
        var isOpen = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isOpen);
        this.closest('.nav-item.more').classList.toggle('open');
    });
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.nav-item.more')) {
        document.querySelectorAll('.more-trigger').forEach(function(t) {
            t.setAttribute('aria-expanded', 'false');
        });
        document.querySelectorAll('.nav-item.more').forEach(function(d) {
            d.classList.remove('open');
        });
    }
});

// Escape key closes dropdowns
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.nav-item.more').forEach(function(d) {
            d.classList.remove('open');
        });
    }
});

// Back to Top
var backToTop = document.createElement('button');
backToTop.className = 'back-to-top';
backToTop.id = 'backToTop';
backToTop.innerHTML = '<i data-lucide="chevron-up"></i>';
backToTop.title = 'Back to top';
document.body.appendChild(backToTop);

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

backToTop.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
