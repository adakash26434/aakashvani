<?php
/**
 * आकाशवाणी — header.php (Professional Desktop Design)
 * Consolidated design: Desktop-first with responsive mobile support
 * 
 * Features:
 * - Full-width professional layout
 * - Top bar with date, greeting, language toggle
 * - Sticky navigation with mega menu dropdown
 * - Sidebar with quick links and market widget
 * - Mobile responsive
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/seo-helper.php';

// ── Per-page SEO override (from DB) ───────────────────────────────────────────
$_seoPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$_seoRow  = getPageSeo($_seoPath);

if (!function_exists('siteLang')) {
    function siteLang(): string {
        return ($_COOKIE['site_lang'] ?? 'ne') === 'en' ? 'en' : 'ne';
    }
}
$lang = siteLang();
$tH = function($ne, $en) use ($lang) {
    return $lang === 'ne' ? $ne : $en;
};

// ── Apply per-page SEO overrides from DB ──────────────────────────────────────
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

/* ── BS Date ── */
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
$greetEn = $hr < 11 ? 'Good morning' : ($hr < 16 ? 'Namaste' : ($hr < 19 ? 'Good evening' : 'Good night'));

/* Nav (same keys as before — drop-in compatible) */
$mainNav=[
    '/'                => ['ne'=>'गृह',          'en'=>'Home',        'icon'=>'home'],
    '/news.php'        => ['ne'=>'समाचार',        'en'=>'AI News',     'icon'=>'newspaper'],
    '/nepali-patro.php'=> ['ne'=>'पात्रो',        'en'=>'Patro',       'icon'=>'calendar-days'],
    '/rashifal.php'    => ['ne'=>'राशिफल',        'en'=>'Rashifal',    'icon'=>'sparkles'],
    '/info-hub.php'    => ['ne'=>'सबै जानकारी',  'en'=>'Info Hub',    'icon'=>'layout-grid'],
    '/ipo-tracker.php' => ['ne'=>'IPO/NEPSE',     'en'=>'IPO/NEPSE',   'icon'=>'trending-up'],
    '/tools.php'       => ['ne'=>'टूलहरू',        'en'=>'Tools',       'icon'=>'wrench'],
    '/gov-services.php'=> ['ne'=>'सरकारी सेवा',   'en'=>'Gov',         'icon'=>'landmark'],
];
$moreNav=[
    '/cricket.php'       => ['ne'=>'क्रिकेट',    'en'=>'Cricket',       'icon'=>'trophy'],
    '/nokari.php'        => ['ne'=>'नोकरी',       'en'=>'Jobs',          'icon'=>'briefcase'],
    '/loksewa.php'       => ['ne'=>'लोकसेवा',     'en'=>'Gov Jobs',      'icon'=>'landmark'],
    '/info-hub.php'      => ['ne'=>'सबै जानकारी',   'en'=>'Info Hub',      'icon'=>'layout-grid'],
    '/utilities.php'     => ['ne'=>'बजार / उपयोगी', 'en'=>'Market & Utils','icon'=>'bar-chart-2'],
    '/tax-calculator.php'=> ['ne'=>'कर Calculator',  'en'=>'Tax Calc',      'icon'=>'receipt'],
    '/emergency.php'     => ['ne'=>'आपतकालीन',       'en'=>'Emergency',     'icon'=>'phone-call'],
    '/downloads.php'     => ['ne'=>'डाउनलोड',        'en'=>'Downloads',     'icon'=>'download'],
    '/morning-brief.php' => ['ne'=>'बिहानी ब्रिफ',   'en'=>'Morning Brief', 'icon'=>'sunrise'],
    '/alerts.php'        => ['ne'=>'अलर्टहरू',       'en'=>'Alerts',        'icon'=>'bell'],
    '/ai-guides.php'     => ['ne'=>'AI Guides',      'en'=>'AI Guides',     'icon'=>'bot'],
];
/* Bottom tab — 5 slots (last = More opens drawer) */
$bottomNav=[
    '/'                => ['ne'=>'गृह',       'en'=>'Home',  'icon'=>'home'],
    '/news.php'        => ['ne'=>'समाचार',    'en'=>'News',  'icon'=>'newspaper'],
    '/nepali-patro.php'=> ['ne'=>'पात्रो',   'en'=>'Patro', 'icon'=>'calendar-days'],
    '/ipo-tracker.php' => ['ne'=>'बजार',     'en'=>'Market','icon'=>'trending-up'],
];
function navAct(string $href,string $path):bool{
    if($href==='/')return in_array($path,['/','/index.php','/home.php']);
    return str_starts_with($path,rtrim($href,'/'));
}
$isLoggedIn = function_exists('isLoggedIn') && isLoggedIn();
$cu = ($isLoggedIn && function_exists('getCurrentUser')) ? getCurrentUser() : null;
$userInitial = $cu && !empty($cu['name']) ? mb_substr($cu['name'],0,1) : 'N';
/* Detail-pane embed mode: when ?embed=1 is set, hide app chrome */
$EMBED = isset($_GET['embed']) && $_GET['embed']==='1';
?>
<!DOCTYPE html>
<html lang="<?= $lang==='en'?'en':'ne' ?>" class="scroll-smooth">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
<title><?= htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc,ENT_QUOTES,'UTF-8') ?>"/>
<meta name="robots" content="<?= $_noindex ? 'noindex,nofollow' : 'index,follow,max-image-preview:large' ?>"/>
<?php $kw = !empty($_seoRow['meta_keywords']) ? $_seoRow['meta_keywords'] : getSeoSetting('meta_keywords',''); if ($kw): ?>
<meta name="keywords" content="<?= htmlspecialchars($kw,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>
<link rel="canonical" href="<?= htmlspecialchars($pageUrl,ENT_QUOTES,'UTF-8') ?>"/>
<?php
// ── Google Search Console verification ───────────────────────────────────────
$_gscMeta = getSeoSetting('gsc_meta','');
if ($_gscMeta): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars($_gscMeta,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif;
// ── Bing Webmaster verification ───────────────────────────────────────────────
$_bingMeta = getSeoSetting('bing_meta','');
if ($_bingMeta): ?>
<meta name="msvalidate.01" content="<?= htmlspecialchars($_bingMeta,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>
<meta property="og:type" content="website"/>
<meta property="og:site_name" content="<?= htmlspecialchars(defined('SITE_NAME')?SITE_NAME:'आकाशवाणी',ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:title" content="<?= htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:description" content="<?= htmlspecialchars($pageDesc,ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:image" content="<?= htmlspecialchars($pageImg,ENT_QUOTES,'UTF-8') ?>"/>
<meta property="og:url" content="<?= htmlspecialchars($pageUrl,ENT_QUOTES,'UTF-8') ?>"/>
<?php $_fbAppId = getSeoSetting('facebook_app_id',''); if ($_fbAppId): ?>
<meta property="fb:app_id" content="<?= htmlspecialchars($_fbAppId,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle,ENT_QUOTES,'UTF-8') ?>"/>
<meta name="twitter:description" content="<?= htmlspecialchars($pageDesc,ENT_QUOTES,'UTF-8') ?>"/>
<meta name="twitter:image" content="<?= htmlspecialchars($pageImg,ENT_QUOTES,'UTF-8') ?>"/>
<?php $_twHandle = getSeoSetting('twitter_handle',''); if ($_twHandle): ?>
<meta name="twitter:site" content="@<?= htmlspecialchars($_twHandle,ENT_QUOTES,'UTF-8') ?>"/>
<?php endif;
// ── Schema.org JSON-LD ────────────────────────────────────────────────────────
if (getSeoSetting('schema_enabled','') === '1'):
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
<link rel="manifest" href="/api/pwa-manifest.php"/>
<meta name="theme-color" content="#0d9488"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="apple-mobile-web-app-title" content="<?= defined('PWA_SHORT_NAME') ? htmlspecialchars(PWA_SHORT_NAME) : 'नेपाली Hub' ?>"/>
<link rel="apple-touch-icon" href="/assets/icons/icon-192.png"/>
<link rel="icon" type="image/svg+xml" href="/assets/favicon.svg"/>

<!-- DNS prefetch: resolve CDN hostnames early, before browser needs them -->
<link rel="dns-prefetch" href="https://cdn.tailwindcss.com"/>
<link rel="dns-prefetch" href="https://unpkg.com"/>
<link rel="dns-prefetch" href="https://cdn.jsdelivr.net"/>
<link rel="dns-prefetch" href="https://fonts.googleapis.com"/>
<link rel="dns-prefetch" href="https://fonts.gstatic.com"/>

<!-- Preconnect: full TLS handshake with font servers (saves ~200ms) -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>

<!-- Google Fonts: async load with font-display:swap (no render block) -->
<!-- Noto Sans Devanagari for better Nepali rendering, Mukta for headings -->
<link rel="preload" as="style"
  href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Mukta:wght@600;700;800&family=Hind+Siliguri:wght@400;500;600&display=swap"
  onload="this.onload=null;this.rel='stylesheet'"/>
<noscript>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Mukta:wght@600;700;800&family=Hind+Siliguri:wght@400;500;600&display=swap"/>
</noscript>

<!-- Tailwind CDN config must come BEFORE the CDN script -->
<script>
tailwind={config:{darkMode:'class',theme:{extend:{
  colors:{
    brand:{50:'#f0fdfa',100:'#ccfbf1',200:'#99f6e4',300:'#5eead4',400:'#2dd4bf',500:'#14b8a6',600:'#0d9488',700:'#0f766e',800:'#115e59',900:'#134e4a'},
    ink:'#0b1220',muted:'#64748b',
    page:'#f4f6fb',surface:'#ffffff',line:'#e6eaf2',
  },
  fontFamily:{
    sans:['"Noto Sans Devanagari"','"Hind Siliguri"','system-ui','sans-serif'],
    display:['Mukta','"Noto Sans Devanagari"','system-ui','sans-serif'],
  },
  boxShadow:{
    'app':'0 1px 2px rgba(11,18,32,.04),0 8px 24px -12px rgba(11,18,32,.10)',
    'tab':'0 -1px 0 rgba(11,18,32,.04),0 -10px 30px -12px rgba(11,18,32,.18)',
  },
  borderRadius:{'2xl':'1rem','3xl':'1.25rem','4xl':'1.75rem'}
}}}};
</script>
<!-- Tailwind: fetchpriority=high so browser prioritises it over other CDN requests -->
<script src="https://cdn.tailwindcss.com" fetchpriority="high"></script>
<link rel="stylesheet" href="/assets/css/global.css"/>

<!-- Alpine.js: pinned exact version (avoids version-resolution redirect on every load) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

<!-- Lucide icons: defer so it never blocks rendering.
     onload callback renders icons once script is ready. -->
<script defer src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"
  onload="(function(){
    function _lRender(s){try{if(window.lucide&&lucide.createIcons)lucide.createIcons({nameAttr:'data-lucide',root:s&&s.querySelectorAll?s:document});}catch(e){}}
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',function(){_lRender(document);});}else{_lRender(document);}
    window.relucide=function(s){_lRender(s);};
  })()"></script>

<script src="/assets/js/nepali-date-picker.js" defer></script>
<?php
// ── Google Analytics 4 ────────────────────────────────────────────────────────
$_ga4Id = getSeoSetting('ga4_id','');
$_gtmId = getSeoSetting('gtm_id','');
if ($_ga4Id && !$EMBED):
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($_ga4Id) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= htmlspecialchars($_ga4Id) ?>');</script>
<?php elseif ($_gtmId && !$EMBED): ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= htmlspecialchars($_gtmId) ?>');</script>
<?php endif; ?>

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --brand:#0d9488;--brand-2:#14b8a6;--brand-deep:#0f766e;
  --ink:#1e293b;--muted:#64748b;--page:#f8fafc;--surface:#ffffff;--line:#e2e8f0;
  --shadow-app:0 1px 2px rgba(11,18,32,.04),0 8px 24px -12px rgba(11,18,32,.10);
}
/* Dark Mode Colors */
.dark{
  --brand:#2dd4bf;--brand-2:#5eead4;--brand-deep:#14b8a6;
  --ink:#f8fafc;--muted:#cbd5e1;--page:#0f172a;--surface:#1e293b;--line:#334155;
  --shadow-app:0 1px 2px rgba(0,0,0,.3),0 8px 24px -12px rgba(0,0,0,.4);
}
.dark body{
  background-color:var(--page);
  color:var(--ink);
}
.dark .app-card,
.dark .app-shell{
  background-color:var(--surface);
  border-color:var(--line);
}
.dark .bar-btn{
  background-color:var(--surface);
  color:var(--ink);
}
.dark .bar-search{
  background-color:var(--surface);
  border-color:var(--line);
}
.dark .bar-search input{
  background-color:transparent;
  color:var(--ink);
}
.dark .chip{
  background-color:var(--surface);
  border-color:var(--line);
  color:var(--ink);
}
.dark .bg-white{
  background-color:var(--surface) !important;
}
.dark .bg-slate-50{
  background-color:var(--page) !important;
}
.dark .text-slate-900,
.dark .text-ink{
  color:var(--ink) !important;
}
.dark .text-slate-500,
.dark .text-muted{
  color:var(--muted) !important;
}
.dark .border-slate-100,
.dark .border-line{
  border-color:var(--line) !important;
}
.dark .shadow-app{
  box-shadow:var(--shadow-app);
}
/* Dark mode tile icon colors - auto-adjust for readability */
.dark .bg-blue-100{background-color:rgba(59,130,246,.25) !important}
.dark .text-blue-700{color:#60a5fa !important}
.dark .bg-pink-100{background-color:rgba(236,72,153,.25) !important}
.dark .text-pink-700{color:#f472b6 !important}
.dark .bg-purple-100{background-color:rgba(168,85,247,.25) !important}
.dark .text-purple-700{color:#c084fc !important}
.dark .bg-indigo-100{background-color:rgba(99,102,241,.25) !important}
.dark .text-indigo-700{color:#818cf8 !important}
.dark .bg-amber-100{background-color:rgba(245,158,11,.25) !important}
.dark .text-amber-700{color:#fbbf24 !important}
.dark .bg-violet-100{background-color:rgba(139,92,246,.25) !important}
.dark .text-violet-700{color:#a78bfa !important}
.dark .bg-rose-100{background-color:rgba(244,63,94,.25) !important}
.dark .text-rose-700{color:#fb7185 !important}
.dark .bg-orange-100{background-color:rgba(249,115,22,.25) !important}
.dark .text-orange-700{color:#fb923c !important}
.dark .bg-emerald-100{background-color:rgba(16,185,129,.25) !important}
.dark .text-emerald-700{color:#34d399 !important}
.dark .bg-teal-100{background-color:rgba(20,184,166,.25) !important}
.dark .text-teal-700{color:#2dd4bf !important}
.dark .bg-green-100{background-color:rgba(34,197,94,.25) !important}
.dark .text-green-700{color:#4ade80 !important}
.dark .bg-yellow-100{background-color:rgba(234,179,8,.25) !important}
.dark .text-yellow-700{color:#facc15 !important}
.dark .bg-slate-100{background-color:rgba(148,163,184,.25) !important}
.dark .text-slate-700{color:#cbd5e1 !important}
.dark .bg-gray-100{background-color:rgba(107,114,128,.25) !important}
.dark .text-gray-700{color:#d1d5db !important}
.dark .bg-red-100{background-color:rgba(239,68,68,.25) !important}
.dark .text-red-700{color:#f87171 !important}
.dark .bg-cyan-100{background-color:rgba(6,182,212,.25) !important}
.dark .text-cyan-700{color:#22d3ee !important}
.dark .bg-sky-100{background-color:rgba(14,165,233,.25) !important}
.dark .text-sky-700{color:#38bdf8 !important}
:root{
  --col:460px; /* phone-column width on desktop */
  --safe-top:env(safe-area-inset-top,0px);
  --safe-bottom:env(safe-area-inset-bottom,0px);
}
html{font-size:16px;background:var(--page);color:var(--ink);scroll-behavior:smooth}
body{
  font-family:'Hind Siliguri',system-ui,sans-serif;
  line-height:1.6;background:var(--page);color:var(--ink);
  -webkit-font-smoothing:antialiased;
  padding-top:calc(112px + var(--safe-top));   /* app bar + chips */
  padding-bottom:calc(96px + var(--safe-bottom));
  overflow-x:hidden;
}
h1,h2,h3,h4,h5,h6{font-family:'Mukta','Hind Siliguri',system-ui,sans-serif;font-weight:700;color:var(--ink);line-height:1.25;letter-spacing:-.01em}
p{color:#475569;line-height:1.7}
a{color:var(--brand-deep);text-decoration:none}
img{max-width:100%;height:auto;display:block}
::selection{background:rgba(13,148,136,.18);color:var(--ink)}
*:focus-visible{outline:2px solid var(--brand);outline-offset:2px;border-radius:8px}
button,[role="button"],a{-webkit-tap-highlight-color:transparent}
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:6px}
.no-sb::-webkit-scrollbar{display:none}.no-sb{scrollbar-width:none}

/* line clamps */
.lc1{display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
.lc2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.lc3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.ne{font-family:'Hind Siliguri',system-ui,sans-serif;line-height:1.85}

/* skeleton */
.skeleton{background:linear-gradient(90deg,#eef1f6 0%,#e1e6ee 50%,#eef1f6 100%);background-size:200% 100%;animation:ske 1.4s ease-in-out infinite;border-radius:10px}
@keyframes ske{0%{background-position:200% 0}100%{background-position:-200% 0}}
@keyframes fadeUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.fade-up{animation:fadeUp .35s cubic-bezier(.16,1,.3,1) both}
@keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* ═══ APP SHELL ═══════════════════════════════════════════════════════════════ */
/* The whole app lives in a phone-width column even on desktop. */
.app-shell{
  width:100%;max-width:var(--col);margin:0 auto;padding:0 14px;
  position:relative;
}
@media(min-width:1100px){
  /* Desktop master-detail: phone column (left) + detail pane (right) */
  :root{ --col-lg:520px; --pane-gap:28px; }
  /* Lock body scroll — each pane scrolls independently (hover = scroll that side only) */
  html,body{height:100%;overflow:hidden}
  body{padding-top:0 !important;padding-bottom:0 !important}
  .desk-grid{
    display:grid;
    grid-template-columns:var(--col-lg) minmax(0,1fr);
    gap:var(--pane-gap);align-items:start;
    max-width:1480px;margin:0 auto;padding:0 28px;
    height:calc(100vh - 112px - var(--safe-top));
    margin-top:calc(112px + var(--safe-top));
    overflow:hidden;
  }
  .desk-grid > .app-shell{
    padding:4px 6px 40px;max-width:var(--col-lg);width:var(--col-lg);
    height:100%;overflow-y:auto;overscroll-behavior:contain;
    scrollbar-gutter:stable;
  }
  .desk-grid > .rail{height:100%;overflow-y:auto;overscroll-behavior:contain}
  /* Hide legacy info rails — replaced by #detail-pane */
  .desk-grid > .rail{display:none !important}
  body{background:linear-gradient(180deg,#eef2f8 0%,#f4f6fb 240px)}

  /* The right-side detail pane (own scroll container, fills remaining space) */
  #detail-pane{
    position:relative;top:0;height:100%;
    background:#fff;border:1px solid var(--line);border-radius:22px;
    box-shadow:var(--shadow-app);
    display:flex;flex-direction:column;overflow:hidden;min-width:0;
    overscroll-behavior:contain;
  }
  #detail-pane .dp-head{
    display:flex;align-items:center;gap:10px;
    padding:12px 14px;border-bottom:1px solid var(--line);
    background:linear-gradient(180deg,#fff,#f8fafc);flex-shrink:0;
  }
  #detail-pane .dp-head .dp-title{
    flex:1;min-width:0;font-size:14px;font-weight:700;color:var(--ink);
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  }
  #detail-pane .dp-btn{
    width:34px;height:34px;border-radius:10px;border:1px solid var(--line);
    background:#fff;color:#475569;display:inline-flex;align-items:center;justify-content:center;
    cursor:pointer;transition:background .15s,color .15s;
  }
  #detail-pane .dp-btn:hover{background:#f1f5f9;color:#0f766e}
  #detail-pane .dp-body{flex:1;min-height:0;position:relative;background:#f4f6fb}
  #detail-pane iframe{width:100%;height:100%;border:0;display:none;background:#f4f6fb}
  #detail-pane.has-content iframe{display:block}
  #detail-pane.has-content .dp-dashboard{display:none}

  /* DASHBOARD (default right-side content, shown until user clicks something) */
  #detail-pane .dp-dashboard{
    position:absolute;inset:0;overflow-y:auto;overscroll-behavior:contain;
    padding:18px 20px 28px;background:#f4f6fb;
  }
  .dpd-hero{
    background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;
    border-radius:18px;padding:16px 18px;box-shadow:0 10px 24px -12px rgba(13,148,136,.5);
    display:flex;align-items:center;gap:14px;margin-bottom:14px;
  }
  .dpd-hero .h-date{font-size:13px;opacity:.9;font-weight:500}
  .dpd-hero .h-title{font-size:18px;font-weight:800;letter-spacing:-.01em;margin-top:2px}
  .dpd-hero .h-ico{width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(255,255,255,.22)}
  .dpd-grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
  .dpd-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:14px;box-shadow:var(--shadow-app)}
  .dpd-card h4{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;display:flex;align-items:center;gap:6px;margin-bottom:8px}
  .dpd-card .big{font-size:20px;font-weight:800;color:#0b1220;letter-spacing:-.02em}
  .dpd-card .sub{font-size:11.5px;color:#64748b;margin-top:2px}
  .dpd-card .up{color:#059669}.dpd-card .dn{color:#dc2626}
  .dpd-section-t{font-size:13px;font-weight:700;color:#0b1220;margin:6px 2px 8px;display:flex;align-items:center;gap:8px}
  .dpd-section-t .more{margin-left:auto;font-size:11.5px;color:#0f766e;font-weight:600}
  .dpd-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px}
  .dpd-tile{display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 6px;border-radius:14px;background:#fff;border:1px solid var(--line);text-align:center;transition:transform .15s,border-color .15s}
  .dpd-tile:hover{transform:translateY(-2px);border-color:#99f6e4}
  .dpd-tile .ic{width:36px;height:36px;border-radius:11px;display:flex;align-items:center;justify-content:center;color:#fff}
  .dpd-tile .lbl{font-size:11px;font-weight:600;color:#334155;line-height:1.2}
  .dpd-list a{display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border-radius:12px;background:#fff;border:1px solid var(--line);margin-bottom:8px;transition:border-color .15s}
  .dpd-list a:hover{border-color:#5eead4;background:#f0fdfa}
  .dpd-list .num{flex-shrink:0;width:24px;height:24px;border-radius:8px;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800}
  .dpd-list .t{font-size:13px;font-weight:600;color:#0b1220;line-height:1.4}
  .dpd-list .m{font-size:11px;color:#94a3b8;margin-top:2px}
  .dpd-tip{margin-top:6px;padding:12px 14px;border-radius:14px;background:linear-gradient(135deg,#fef3c7,#fde68a);border:1px solid #fcd34d;display:flex;gap:10px;align-items:flex-start}
  .dpd-tip .ic{width:32px;height:32px;border-radius:10px;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .dpd-tip .t{font-size:12.5px;color:#78350f;font-weight:600;line-height:1.5}
  #detail-pane .dp-loading{
    position:absolute;inset:0;display:none;align-items:center;justify-content:center;
    background:rgba(255,255,255,.8);z-index:2;font-size:13px;color:#0f766e;font-weight:600;gap:8px;
  }
  #detail-pane.loading .dp-loading{display:flex}
  #detail-pane .spin{width:18px;height:18px;border:2px solid #99f6e4;border-top-color:#0f766e;border-radius:50%;animation:dpSpin .8s linear infinite}
  @keyframes dpSpin{to{transform:rotate(360deg)}}

  /* Hide bottom tab bar on desktop — left rail/menu inside drawer + chips suffice */
  body{padding-bottom:24px !important}
  #tabbar{display:none}
}
@media(max-width:1099px){ .rail,#detail-pane{display:none} }

/* ── EMBED MODE (page loaded inside detail-pane iframe) ─────────────────── */
body.embed-mode{padding:0 !important;background:#f4f6fb}
body.embed-mode #app-bar,
body.embed-mode #app-chips,
body.embed-mode #tabbar,
body.embed-mode footer.app-shell,
body.embed-mode #pwa-install,
body.embed-mode #pwa-update{display:none !important}
body.embed-mode .desk-grid{display:block;max-width:none;padding:0}
body.embed-mode .desk-grid > .rail,
body.embed-mode #detail-pane{display:none !important}
body.embed-mode .app-shell{max-width:760px;margin:0 auto;padding:14px 16px 40px;width:auto}
/* Rashifal & similar wide grids inside narrow embedded detail-pane */
body.embed-mode .rashi-grid,
body.embed-mode .signs-grid,
body.embed-mode [class*="grid-cols-6"],
body.embed-mode [class*="grid-cols-5"]{grid-template-columns:repeat(3,minmax(0,1fr)) !important}
body.embed-mode [class*="grid-cols-4"]{grid-template-columns:repeat(3,minmax(0,1fr)) !important}
body.embed-mode img,
body.embed-mode iframe,
body.embed-mode video{max-width:100% !important;height:auto}
body.embed-mode .container,
body.embed-mode .max-w-7xl,
body.embed-mode .max-w-6xl,
body.embed-mode .max-w-5xl{max-width:100% !important;padding-left:8px !important;padding-right:8px !important}

/* ═══ APP TOP BAR (gradient, sticky) ════════════════════════════════════════ */
#app-bar{
  position:fixed;top:0;left:0;right:0;z-index:200;
  padding-top:var(--safe-top);
  background:linear-gradient(135deg,#0f766e 0%,#0d9488 55%,#14b8a6 100%);
  color:#fff;
  box-shadow:0 6px 20px -10px rgba(13,148,136,.5);
}
#app-bar .bar-inner{
  max-width:1240px;margin:0 auto;padding:10px 16px 14px;
  display:flex;align-items:center;gap:10px;
}
@media(min-width:1100px){#app-bar .bar-inner{padding:12px 20px 16px}}
.app-logo{
  width:38px;height:38px;border-radius:12px;
  background:rgba(255,255,255,.18);backdrop-filter:blur(6px);
  display:flex;align-items:center;justify-content:center;
  font-weight:800;font-size:18px;flex-shrink:0;
  border:1px solid rgba(255,255,255,.25);
}
.app-greet{display:flex;flex-direction:column;min-width:0;line-height:1.1}
.app-greet .g1{font-size:11.5px;opacity:.85;font-weight:500}
.app-greet .g2{font-size:14.5px;font-weight:700;letter-spacing:-.01em}
.bar-btn{
  width:38px;height:38px;border-radius:12px;
  background:rgba(255,255,255,.12);
  display:inline-flex;align-items:center;justify-content:center;
  color:#fff;flex-shrink:0;position:relative;
  border:1px solid rgba(255,255,255,.18);
  transition:background .15s;
}
.bar-btn:hover{background:rgba(255,255,255,.22)}
.bar-btn .dot{position:absolute;top:7px;right:8px;width:8px;height:8px;border-radius:50%;background:#fbbf24;border:2px solid #0d9488}
.bar-search{
  display:flex;align-items:center;gap:8px;flex:1;
  background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.22);
  border-radius:14px;padding:9px 12px;color:#fff;min-width:0;
}
.bar-search input{background:transparent;border:0;outline:0;color:#fff;font-size:13.5px;width:100%;font-family:inherit}
.bar-search input::placeholder{color:rgba(255,255,255,.75)}
.bar-search svg{flex-shrink:0;opacity:.85}

/* Sub-chips row (live ticker etc.) */
#app-chips{
  position:fixed;top:calc(64px + var(--safe-top));left:0;right:0;z-index:190;
  background:rgba(255,255,255,.85);backdrop-filter:saturate(1.4) blur(10px);
  border-bottom:1px solid var(--line);
}
@media(min-width:1100px){#app-chips{top:calc(72px + var(--safe-top))}}
#app-chips .ch{
  max-width:1240px;margin:0 auto;padding:8px 14px;
  display:flex;gap:8px;overflow-x:auto;
}
.chip{
  flex-shrink:0;display:inline-flex;align-items:center;gap:6px;
  background:#fff;border:1px solid var(--line);
  padding:6px 11px;border-radius:999px;
  font-size:12px;font-weight:600;color:#334155;
  box-shadow:0 1px 2px rgba(11,18,32,.04);
}
.chip .up{color:#059669}.chip .dn{color:#dc2626}
.chip.live{background:linear-gradient(135deg,#fef3c7,#fee2e2);border-color:#fde68a;color:#92400e}
.chip.live .pulse{width:6px;height:6px;border-radius:50%;background:#ef4444;box-shadow:0 0 0 0 rgba(239,68,68,.7);animation:pulse 1.6s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(239,68,68,.6)}70%{box-shadow:0 0 0 8px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0)}}

/* ═══ APP CARD primitives ═══════════════════════════════════════════════════ */
.app-card{
  background:var(--surface);border:1px solid var(--line);border-radius:18px;
  box-shadow:var(--shadow-app);overflow:hidden;
}
.app-card-h{display:flex;align-items:center;gap:8px;padding:14px 16px 6px}
.app-card-h h3{font-size:14px;font-weight:700;letter-spacing:-.01em}
.app-card-h .more{margin-left:auto;font-size:12px;color:var(--brand-deep);font-weight:600;display:inline-flex;align-items:center;gap:2px}

/* Tile (Nagarik-style service tile) */
.tile{
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;
  padding:14px 6px;border-radius:16px;background:#fff;border:1px solid var(--line);
  text-align:center;transition:transform .15s,box-shadow .2s,border-color .15s;
  min-height:88px;
}
.tile:hover{transform:translateY(-2px);box-shadow:var(--shadow-app);border-color:#d6dbe6}
.tile .ic{
  width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;
  color:#fff;flex-shrink:0;
}
.tile .ic i{width:20px;height:20px;stroke-width:2}
.tile .lbl{font-size:11.5px;font-weight:600;color:#334155;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}

/* Tile color variants — single saturated bg, white icon */
.bg-i1{background:linear-gradient(135deg,#0d9488,#14b8a6)}
.bg-i2{background:linear-gradient(135deg,#2563eb,#3b82f6)}
.bg-i3{background:linear-gradient(135deg,#f59e0b,#fbbf24)}
.bg-i4{background:linear-gradient(135deg,#db2777,#f472b6)}
.bg-i5{background:linear-gradient(135deg,#7c3aed,#a78bfa)}
.bg-i6{background:linear-gradient(135deg,#dc2626,#f87171)}
.bg-i7{background:linear-gradient(135deg,#059669,#34d399)}
.bg-i8{background:linear-gradient(135deg,#0891b2,#22d3ee)}

/* ═══ BOTTOM TAB BAR (all viewports) ═════════════════════════════════════════ */
#tabbar{
  position:fixed;left:50%;transform:translateX(-50%);
  bottom:calc(10px + var(--safe-bottom));z-index:210;
  width:calc(100% - 24px);max-width:var(--col);
  background:#fff;border:1px solid var(--line);
  border-radius:24px;box-shadow:var(--shadow-app),0 12px 30px -12px rgba(11,18,32,.18);
  display:grid;grid-template-columns:repeat(5,1fr);
  padding:6px;
}
.tab{
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;
  padding:8px 0;border-radius:18px;font-size:10.5px;font-weight:600;color:#64748b;
  transition:background .15s,color .15s;
}
.tab.active{background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;box-shadow:0 6px 16px -8px rgba(13,148,136,.55)}
.tab.active .lbl{color:#fff}
.tab .lbl{line-height:1}
.tab-more svg{transform:translateY(-1px)}

/* ═══ DRAWER (more menu) ═════════════════════════════════════════════════════ */
#drawer{position:fixed;inset:0;z-index:300;background:rgba(11,18,32,.55);opacity:0;pointer-events:none;transition:opacity .2s;display:flex;align-items:flex-end;justify-content:center}
#drawer.open{opacity:1;pointer-events:auto}
#drawer .sheet{
  width:100%;max-width:520px;background:#fff;border-radius:24px 24px 0 0;
  padding:14px 16px calc(20px + var(--safe-bottom));
  transform:translateY(20px);transition:transform .25s cubic-bezier(.16,1,.3,1);
  max-height:85vh;overflow-y:auto;
}
#drawer.open .sheet{transform:none}
.sheet-handle{width:44px;height:5px;border-radius:3px;background:#e2e8f0;margin:0 auto 14px}
.drw-link{display:flex;align-items:center;gap:12px;padding:12px 12px;border-radius:14px;color:#0b1220;font-weight:600;font-size:14px}
.drw-link.active{background:#f0fdfa;color:var(--brand-deep)}
.drw-link:hover{background:#f4f6fb}
.drw-link .ico{width:36px;height:36px;border-radius:10px;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;color:var(--brand-deep);flex-shrink:0}
.drw-link.active .ico{background:var(--brand);color:#fff}

/* Section title outside cards */
.sec-title{display:flex;align-items:center;gap:8px;margin:18px 4px 10px;font-size:13.5px;font-weight:700;color:var(--ink);letter-spacing:-.01em}
.sec-title .badge{margin-left:auto;font-size:11px;color:var(--brand-deep);font-weight:600}

/* News list item */
.news-row{display:flex;gap:11px;padding:12px;border-radius:16px;background:#fff;border:1px solid var(--line);transition:border-color .15s,transform .15s}
.news-row:hover{border-color:#cbd5e1;transform:translateY(-1px)}
.news-row .thumb{width:74px;height:74px;border-radius:12px;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,#f0fdfa,#cffafe)}
.news-row .thumb img{width:100%;height:100%;object-fit:cover}

/* Featured card */
.feat{position:relative;border-radius:20px;overflow:hidden;background:#0f172a;color:#fff;aspect-ratio:16/10;display:flex;align-items:flex-end}
.feat img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85}
.feat::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 30%,rgba(0,0,0,.78) 100%)}
.feat .meta{position:relative;z-index:1;padding:16px;width:100%}
.feat .pill{display:inline-block;background:rgba(255,255,255,.22);backdrop-filter:blur(4px);font-size:10.5px;font-weight:700;padding:4px 10px;border-radius:999px;letter-spacing:.04em;text-transform:uppercase}
.feat h3{color:#fff;font-size:17px;line-height:1.3;margin-top:8px}

/* Pill row scroller (categories etc.) */
.pill-row{display:flex;gap:8px;overflow-x:auto;padding:4px 2px}
.pill{flex-shrink:0;padding:7px 13px;border-radius:999px;background:#fff;border:1px solid var(--line);font-size:12.5px;font-weight:600;color:#334155}
.pill.active{background:var(--brand-deep);color:#fff;border-color:var(--brand-deep)}

/* hide horizontal scrollbars on chips/pill rows */
.no-sb{scrollbar-width:none}
.no-sb::-webkit-scrollbar{display:none}
</style>
<style>
/* ═══ BRAND MARK (logo + tagline) — visible on mobile & desktop ═══ */
.brand-mark{display:flex;flex-direction:column;line-height:1.05;color:#fff;min-width:0;margin-right:6px}
.brand-mark .bm-name{font-family:'Mukta','Hind Siliguri',system-ui,sans-serif;font-size:16px;font-weight:800;letter-spacing:-.01em;white-space:nowrap}
.brand-mark .bm-tag{font-family:'Hind Siliguri',system-ui,sans-serif;font-size:10.5px;font-weight:500;opacity:.92;white-space:nowrap;margin-top:1px}
@media(min-width:1100px){.brand-mark .bm-name{font-size:19px}.brand-mark .bm-tag{font-size:12px}}
@media(max-width:480px){
  .app-greet{display:none}
  .brand-mark .bm-tag{display:none}
}

/* ═══ UNIFIED TYPOGRAPHY across every inner page ═══════════════════════ */
/* Inner pages historically had inconsistent font-sizes. Normalise here so
   home / news / sources / utilities / etc. all match. */
.app-main, main{font-family:'Hind Siliguri',system-ui,sans-serif;font-size:14px;line-height:1.65;color:#0b1220}
.app-main h1, main h1{font-size:20px;font-weight:800;line-height:1.3;letter-spacing:-.01em;color:#0b1220}
.app-main h2, main h2{font-size:16px;font-weight:700;line-height:1.35;color:#0b1220}
.app-main h3, main h3{font-size:14.5px;font-weight:700;line-height:1.4;color:#0b1220}
.app-main p, main p{font-size:13.5px;line-height:1.7;color:#334155}
.app-main small, main small{font-size:11.5px;color:#64748b}
.app-main .ne, main .ne{font-family:'Hind Siliguri',system-ui,sans-serif}
@media(min-width:1100px){
  .app-main h1, main h1{font-size:22px}
  .app-main h2, main h2{font-size:17px}
  .app-main p, main p{font-size:14px}
}
/* Force consistent button/link font */
button, .btn, .chip, .pill, .tab, .tile{font-family:'Hind Siliguri',system-ui,sans-serif}
</style>
<?php if (!$EMBED): /* skip floating prompts inside embedded detail-pane iframe */ ?>
<script src="/assets/js/pwa-install.js" defer></script>
<script src="/assets/js/push-notify.js" defer></script>
<?php endif; ?>
</head>
<?php
$_flash = getFlash();
?>
<body class="<?= $EMBED ? 'embed-mode' : '' ?>">
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
