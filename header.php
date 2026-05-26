<?php
/**
 * आकाशवाणी — header.php v10 (APP REDESIGN)
 * Concept: Nepal "Nagarik App" jasto mobile-first. Desktop ma pani
 * phone-column (max 460px) layout + optional side info rails.
 * - Sticky app top-bar (gradient) with greeting + bell + avatar
 * - Floating bottom tab nav (all viewports)
 * - Tile-based service grid, soft cards, large tap targets
 * - Single teal accent, OKLCH-ish modern tokens
 * Drop-in compatible: uses same $mainNav/$moreNav/$bottomNav, siteLang(), isLoggedIn(), getCurrentUser().
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
    '/cricket.php'       => ['ne'=>'🏏 क्रिकेट',    'en'=>'Cricket',       'icon'=>'trophy'],
    '/nokari.php'        => ['ne'=>'💼 नोकरी',       'en'=>'Jobs',          'icon'=>'briefcase'],
    '/loksewa.php'       => ['ne'=>'🏛 लोकसेवा',     'en'=>'Gov Jobs',      'icon'=>'landmark'],
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
<!-- Only 2 weights per family — saves ~40KB vs loading 4-5 weights -->
<link rel="preload" as="style"
  href="https://fonts.googleapis.com/css2?family=Mukta:wght@700;800&family=Hind+Siliguri:wght@400;600&display=swap"
  onload="this.onload=null;this.rel='stylesheet'"/>
<noscript>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Mukta:wght@700;800&family=Hind+Siliguri:wght@400;600&display=swap"/>
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
    sans:['"Hind Siliguri"','system-ui','sans-serif'],
    display:['Mukta','"Hind Siliguri"','system-ui','sans-serif'],
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
  --ink:#0b1220;--muted:#64748b;--page:#f4f6fb;--surface:#fff;--line:#e6eaf2;
  --shadow-app:0 1px 2px rgba(11,18,32,.04),0 8px 24px -12px rgba(11,18,32,.10);
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
.tile .ic{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff}
.tile .lbl{font-size:11.5px;font-weight:600;color:#334155;line-height:1.25}

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
<?php if ($_flash): ?>
<div id="flash-toast"
  class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] max-w-sm w-[92%] flex items-center gap-3 px-4 py-3 rounded-2xl shadow-2xl border text-[13px] font-semibold
    <?= $_flash['type']==='error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' ?>"
  role="alert">
  <span class="text-lg"><?= $_flash['type']==='error' ? '❌' : '✅' ?></span>
  <span class="flex-1"><?= htmlspecialchars($_flash['msg'], ENT_QUOTES, 'UTF-8') ?></span>
  <button onclick="this.parentElement.remove()" class="ml-1 text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
</div>
<script>setTimeout(function(){var t=document.getElementById('flash-toast');if(t){t.style.opacity='0';t.style.transform='translateX(-50%) translateY(-10px)';t.style.transition='all .4s';setTimeout(function(){t&&t.remove()},400);}},4000);</script>
<?php endif; ?>

<!-- ═══ APP TOP BAR ═══════════════════════════════════════════════════════════ -->
<header id="app-bar">
  <div class="bar-inner">
    <a href="/" class="app-logo" aria-label="आकाशवाणी Home" style="background:#fff;padding:0;overflow:hidden">
      <img src="/assets/images/logo.png" alt="आकाशवाणी" style="width:100%;height:100%;object-fit:cover;display:block"/>
    </a>

    <a href="/" class="brand-mark" aria-label="आकाशवाणी — सूचनाको खुला आकाश">
      <span class="bm-name">आकाशवाणी</span>
      <span class="bm-tag">सूचनाको खुला आकाश</span>
    </a>

    <div class="app-greet">
      <span class="g1 ne"><?= $tH($greetNe,$greetEn) ?><?php if($cu): ?>, <?= htmlspecialchars(mb_substr($cu['name']??'',0,12),ENT_QUOTES,'UTF-8') ?><?php endif; ?></span>
      <span class="g2 ne"><?= $bsShort ?></span>
    </div>

    <form action="/search.php" method="get" class="bar-search" role="search" aria-label="Search">
      <i data-lucide="search" class="w-4 h-4"></i>
      <input type="text" name="q" placeholder="<?= $tH('खोज्नुस्…','खोज्नुस्…') ?>" />
    </form>

    <a href="/alerts.php" class="bar-btn" aria-label="Notifications">
      <i data-lucide="bell" class="w-[18px] h-[18px]"></i>
      <span class="dot"></span>
    </a>

    <?php if($cu): ?>
      <a href="/dashboard.php" class="bar-btn" style="background:#fff;color:#0f766e" aria-label="Account">
        <span style="font-weight:800;font-size:14px"><?= htmlspecialchars($userInitial,ENT_QUOTES,'UTF-8') ?></span>
      </a>
    <?php else: ?>
      <a href="/login.php" class="bar-btn" aria-label="Login">
        <i data-lucide="user" class="w-[18px] h-[18px]"></i>
      </a>
    <?php endif; ?>
  </div>
</header>

<!-- ═══ CHIPS ROW (placeholder; index fills with live data) ════════════════════ -->
<div id="app-chips">
  <div class="ch no-sb">
    <span class="chip live ne"><span class="pulse"></span> <?= $bsDateStr ?></span>
    <a href="/utilities.php" class="chip ne" id="ch-nepse">NEPSE <span class="text-slate-400">—</span></a>
    <a href="/utilities.php#gold" class="chip ne" id="ch-gold"><?= $tH('सुन','Gold') ?> <span class="text-slate-400">—</span></a>
    <a href="/utilities.php#fuel" class="chip ne" id="ch-petrol"><?= $tH('पेट्रोल','Petrol') ?> <span class="text-slate-400">—</span></a>
    <a href="/utilities.php#forex" class="chip ne" id="ch-usd">USD <span class="text-slate-400">—</span></a>
    <a href="?lang=<?= $lang==='ne'?'en':'ne' ?>" class="chip"><i data-lucide="globe" class="w-3 h-3"></i> <?= $lang==='ne'?'EN':'नेपा' ?></a>
  </div>
</div>

<!-- ═══ DRAWER (More menu, opened by bottom-tab "More") ════════════════════════ -->
<div id="drawer" onclick="if(event.target===this)closeDrawer()">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="flex items-center justify-between px-1 mb-2">
      <div>
        <div class="flex items-center gap-2">
          <img src="/assets/images/logo.png" alt="" class="w-7 h-7 rounded-lg"/>
          <span class="text-[15px] font-bold text-ink">आकाश<span class="text-brand-600">वाणी</span></span>
        </div>
        <div class="text-[11px] text-slate-500 ne"><?= $bsDateStr ?></div>
      </div>
      <button onclick="closeDrawer()" class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center" aria-label="Close">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="grid grid-cols-1 gap-1 mt-2">
      <?php foreach(array_merge($mainNav,$moreNav) as $href=>$item): $act=navAct($href,$currentPath); ?>
        <a href="<?= $href ?>" class="drw-link <?= $act?'active':'' ?>">
          <span class="ico"><i data-lucide="<?= $item['icon'] ?>" class="w-[18px] h-[18px]"></i></span>
          <span class="ne flex-1"><?= $tH($item['ne'],$item['en']) ?></span>
          <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-2">
      <a href="?lang=<?= $lang==='ne'?'en':'ne' ?>" class="py-3 text-center text-sm font-semibold rounded-xl border border-line bg-slate-50 text-slate-700">
        <?= $lang==='ne'?'Switch to English':'नेपालीमा बदल्नुहोस्' ?>
      </a>
      <?php if($isLoggedIn): ?>
        <a href="/logout.php" class="py-3 text-center text-sm font-semibold rounded-xl bg-red-50 text-red-600 border border-red-100"><?= $tH('लगआउट','Logout') ?></a>
      <?php else: ?>
        <a href="/login.php" class="py-3 text-center text-sm font-semibold rounded-xl bg-brand-600 text-white border border-brand-700"><?= $tH('लगइन','Login') ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ═══ BOTTOM TAB BAR (always visible) ════════════════════════════════════════ -->
<nav id="tabbar" aria-label="App tabs">
  <?php foreach($bottomNav as $href=>$item): $act=navAct($href,$currentPath); ?>
    <a href="<?= $href ?>" class="tab <?= $act?'active':'' ?>">
      <i data-lucide="<?= $item['icon'] ?>" class="w-[20px] h-[20px]"></i>
      <span class="lbl ne"><?= $tH($item['ne'],$item['en']) ?></span>
    </a>
  <?php endforeach; ?>
  <button type="button" onclick="openDrawer()" class="tab tab-more" aria-label="More">
    <i data-lucide="grid-3x3" class="w-[20px] h-[20px]"></i>
    <span class="lbl ne"><?= $tH('थप','More') ?></span>
  </button>
</nav>

<script>
function openDrawer(){document.getElementById('drawer').classList.add('open');document.body.style.overflow='hidden';}
function closeDrawer(){document.getElementById('drawer').classList.remove('open');document.body.style.overflow='';}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeDrawer();});
/* Live chip updater (reads cached market files via sync-status if available) */
(function(){
  function setChip(id,val,chg){
    var el=document.getElementById(id);if(!el||!val)return;
    var arrow=chg==null?'':(chg>=0?'<span class="up">▲'+Math.abs(chg)+'</span>':'<span class="dn">▼'+Math.abs(chg)+'</span>');
    el.innerHTML=el.firstChild.textContent.replace(/—.*/,'').trim()+' <strong>'+val+'</strong> '+arrow;
  }
  function paint(c){
    if(!c) return;
    if(c.nepse) setChip('ch-nepse',Number(c.nepse).toLocaleString(),c.nepse_chg);
    if(c.gold)  setChip('ch-gold','रु '+Number(c.gold).toLocaleString());
    if(c.petrol)setChip('ch-petrol','रु '+Number(c.petrol).toLocaleString());
    if(c.usd)   setChip('ch-usd','रु '+Number(c.usd).toFixed(2));
  }
  /* Server-rendered (home) wins instantly */
  if(window.__chips) paint(window.__chips);
  /* On every other page, hydrate chips from the same single source so all
     pages show identical NEPSE/Gold/Petrol/USD numbers. */
  try {
    var k = 'nsh_chips_v1';
    var cached = JSON.parse(sessionStorage.getItem(k) || 'null');
    if (cached && (Date.now() - cached.t) < 5*60*1000) paint(cached.v);
  } catch(_){}
  fetch('/api/market-data.php', {credentials:'same-origin'})
    .then(function(r){return r.ok?r.json():null;})
    .then(function(d){
      if(!d) return;
      var usd = (d.forex && d.forex.usdNpr) || null;
      if(!usd && d.forex && Array.isArray(d.forex.rates)){
        for(var i=0;i<d.forex.rates.length;i++){
          var r=d.forex.rates[i];
          if(r && r.code==='USD'){ var u=Math.max(1,parseInt(r.unit||1,10)); usd=parseFloat(r.buy||0)/u; break; }
        }
      }
      var v = {
        nepse: d.nepse && d.nepse.index ? d.nepse.index : null,
        nepse_chg: d.nepse && (d.nepse.change!=null) ? d.nepse.change : null,
        gold:  d.gold  && d.gold.hallmarkPerTola ? d.gold.hallmarkPerTola : null,
        petrol:d.fuel  && d.fuel.petrol ? d.fuel.petrol : (d.petrol && d.petrol.petrol ? d.petrol.petrol : null),
        usd:   usd
      };
      paint(v);
      try { sessionStorage.setItem('nsh_chips_v1', JSON.stringify({t:Date.now(), v:v})); } catch(_){}
    }).catch(function(){});
})();

/* ═══ DESKTOP MASTER-DETAIL: open same-origin links inside #detail-pane ═══ */
(function(){
  var mq = window.matchMedia('(min-width:1100px)');
  if(document.body.classList.contains('embed-mode')) return; // don't bind inside iframe
  function dp(){ return document.getElementById('detail-pane'); }
  function setTitle(t){ var el=dp() && dp().querySelector('.dp-title'); if(el) el.textContent = t || 'Detail'; }
  function showEmpty(){
    var p=dp(); if(!p) return;
    p.classList.remove('loading');p.classList.remove('has-content');
    var fr=p.querySelector('iframe'); if(fr) fr.src='about:blank';
    setTitle('आकाशवाणी');
    try{ history.replaceState(null,'',location.pathname+location.search); }catch(e){}
  }
  function openInPane(url, title){
    var p=dp(); if(!p) return false;
    p.classList.add('loading');p.classList.add('has-content');
    var fr=p.querySelector('iframe');
    if(!fr){ fr=document.createElement('iframe'); fr.setAttribute('loading','lazy'); p.querySelector('.dp-body').appendChild(fr); }
    // append embed=1
    var u = new URL(url, location.origin);
    u.searchParams.set('embed','1');
    fr.onload = function(){
      p.classList.remove('loading');
      try{
        var t = fr.contentDocument && fr.contentDocument.title;
        if(t) setTitle(t.replace(/\s*—.*$/,'').trim() || t);
      }catch(e){}
    };
    fr.src = u.pathname + u.search + u.hash;
    setTitle(title || 'Loading…');
    // reflect in URL hash for shareability
    try{ history.replaceState(null,'','#p=' + encodeURIComponent(u.pathname + u.search)); }catch(e){}
    return true;
  }
  window.NSH_openPane = openInPane;
  window.NSH_closePane = showEmpty;

  // Click interception inside the phone column only
  document.addEventListener('click', function(ev){
    if(!mq.matches) return;
    var a = ev.target && ev.target.closest && ev.target.closest('a');
    if(!a) return;
    if(!a.closest('#main > .app-shell') && !a.closest('#detail-pane .dp-dashboard')) return;
    var href = a.getAttribute('href');
    if(!href) return;
    if(a.target && a.target !== '_self') return;
    if(a.hasAttribute('download')) return;
    if(/^(mailto:|tel:|javascript:|#)/i.test(href)) return;
    // external?
    try{
      var u = new URL(href, location.origin);
      if(u.origin !== location.origin) return;
      // skip API endpoints
      if(u.pathname.startsWith('/api/')) return;
      // skip auth flows (need full reload + cookies)
      if(/\/(login|logout|signup|register|forgot)\.php$/.test(u.pathname)) return;
      ev.preventDefault();
      openInPane(u.pathname + u.search + u.hash, a.innerText.trim().slice(0,80));
    }catch(e){}
  });

  // Restore from hash on load
  function restore(){
    if(!mq.matches) return;
    var m = location.hash.match(/^#p=(.+)$/);
    if(m){ try{ openInPane(decodeURIComponent(m[1])); }catch(e){} }
  }
  if(document.readyState!=='loading') restore(); else document.addEventListener('DOMContentLoaded', restore);

  // Esc closes pane
  document.addEventListener('keydown', function(e){ if(e.key==='Escape' && mq.matches) showEmpty(); });
})();
</script>

<!-- Open app shell. Pages render inside. On desktop becomes a master-detail grid. -->
<main id="main" class="desk-grid">
  <!-- LEFT RAIL (desktop only) -->
  <aside class="rail left hidden xl:block">
    <div class="app-card" style="padding:14px">
      <div class="text-[12px] font-bold text-slate-500 uppercase tracking-wide mb-2">Menu</div>
      <?php foreach($mainNav as $href=>$item): $act=navAct($href,$currentPath); ?>
        <a href="<?= $href ?>" class="drw-link <?= $act?'active':'' ?>" style="padding:9px 10px">
          <span class="ico" style="width:30px;height:30px"><i data-lucide="<?= $item['icon'] ?>" class="w-[16px] h-[16px]"></i></span>
          <span class="ne text-[13px]"><?= $tH($item['ne'],$item['en']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- CENTER APP COLUMN -->
  <div class="app-shell fade-up">
