<?php
/**
 * आकाशवाणी — index.php v10 (APP HOMEPAGE)
 * Nagarik-app style mobile-first home: greeting hero, service tile grid,
 * breaking news strip, featured article, news list, rashifal wheel,
 * market summary, emergency, gov services. Same data sources as before.
 */

/* ── Clean URL fallback router (works when .htaccess rewrite is unavailable) ── */
$__path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__clean = rtrim($__path, '/') ?: '/';
$__routes = [
    '/news'          => '/news.php',
    '/loksewa'       => '/loksewa.php',
    '/rashifal'      => '/rashifal.php',
    '/tools'         => '/tools.php',
    '/alerts'        => '/alerts.php',
    '/gov-services'  => '/gov-services.php',
    '/utilities'     => '/utilities.php',
    '/nepali-patro'  => '/nepali-patro.php',
    '/patro'         => '/nepali-patro.php',
    '/contact'       => '/contact.php',
    '/search'        => '/search.php',
    '/install'       => '/install.php',
    '/emergency'     => '/emergency.php',
    '/ipo-tracker'   => '/ipo-tracker.php',
    '/tax-calculator'=> '/tax-calculator.php',
    '/downloads'     => '/downloads.php',
    '/dashboard'     => '/dashboard.php',
    '/login'         => '/login.php',
    '/register'      => '/register.php',
    '/about'         => '/about.php',
    '/bookmarks'     => '/bookmarks.php',
    '/morning-brief' => '/morning-brief.php',
    '/notices'       => '/notices.php',
    '/offline'       => '/offline.php',
];
if ($__clean !== '/' && isset($__routes[$__clean])) {
    $__target = $__routes[$__clean];
    if (!empty($_SERVER['QUERY_STRING'])) $__target .= '?' . $_SERVER['QUERY_STRING'];
    header('Location: ' . $__target, true, 302);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/auth.php';

/* Market data — v10.1 fix: single normalized loader (was: per-page cache reads
   with wrong key names that always resolved to 0, then hardcoded fallbacks
   appeared elsewhere → mismatched prices across pages). */
require_once __DIR__ . '/includes/market.php';
$market = getMarket(true);
$gold   = $market['gold'];   // keys: fine, tejabi, silver
$forex  = $market['forex'];  // keys: USD, EUR, INR, rates[...]
$nepse  = $market['nepse'];  // keys: index, change, percent
$petrol = $market['fuel'];   // keys: petrol, diesel, kerosene, lpg
try { maybeRefreshNews(); } catch(\Exception $e) {}

$lang = siteLang();
$t    = fn($ne,$en) => $lang==='ne' ? $ne : $en;

$user       = (function_exists('isLoggedIn') && isLoggedIn()) ? (function_exists('getCurrentUser') ? getCurrentUser() : null) : null;
$latestNews = function_exists('getPublishedNews') ? getPublishedNews(null,null,9,0,null,null) : [];
$breaking   = array_values(array_filter($latestNews, fn($n)=>!empty($n['is_breaking'])));
$featured   = !empty($latestNews) ? array_shift($latestNews) : null;
$regular    = array_slice($latestNews,0,6);

$nepseIdx = (float)($nepse['index']  ?? 0);
$nepseChg = $nepse['change']          ?? null;
$nepseUp  = $nepseChg!==null && (float)$nepseChg >= 0;
$goldFine = (float)($gold['fine'] ?? 0);
$petrolP  = (float)($petrol['petrol'] ?? 0);
$usdRate  = (float)($forex['USD'] ?? 0);

$pageTitle = "आकाशवाणी — Nepal's AI App";
$pageDesc  = 'NEPSE, सुन, फरेक्स, AI समाचार, पात्रो, राशिफल, सरकारी सेवा — सबै App मा।';
$pageUrl   = (defined('SITE_URL')?SITE_URL:'').'/';
$jsonLd    = json_encode(['@context'=>'https://schema.org','@type'=>'WebSite','name'=>defined('SITE_NAME')?SITE_NAME:'आकाशवाणी','url'=>defined('SITE_URL')?SITE_URL:'','description'=>$pageDesc,'potentialAction'=>['@type'=>'SearchAction','target'=>['@type'=>'EntryPoint','urlTemplate'=>(defined('SITE_URL')?SITE_URL:'').'/search.php?q={search_term_string}'],'query-input'=>'required name=search_term_string']],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

include __DIR__ . '/header.php';

/* Category badge helper */
function catBadge(string $cat): string {
    $map=['Technology'=>'bg-blue-50 text-blue-700','Business'=>'bg-emerald-50 text-emerald-700','Sports'=>'bg-orange-50 text-orange-700','Entertainment'=>'bg-pink-50 text-pink-700','International'=>'bg-purple-50 text-purple-700','National'=>'bg-teal-50 text-teal-700','राजनीति'=>'bg-red-50 text-red-700','अर्थ'=>'bg-emerald-50 text-emerald-700','खेलकुद'=>'bg-orange-50 text-orange-700'];
    $cls=$map[$cat]??'bg-slate-100 text-slate-600';
    return '<span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded-full '.$cls.'">'.htmlspecialchars($cat,ENT_QUOTES,'UTF-8').'</span>';
}
?>

<!-- Pass live values to header chip updater -->
<script>
window.__chips = {
  nepse: <?= json_encode($nepseIdx ?: null) ?>,
  nepse_chg: <?= json_encode($nepseChg!==null?(float)$nepseChg:null) ?>,
  gold:  <?= json_encode($goldFine ?: null) ?>,
  petrol:<?= json_encode($petrolP ?: null) ?>,
  usd:   <?= json_encode($usdRate ?: null) ?>
};
</script>

<!-- ═══ HERO CARD: greeting + BS date + quick actions ════════════════════════ -->
<section class="mt-3">
  <div class="rounded-3xl overflow-hidden relative text-white p-5"
       style="background:radial-gradient(120% 100% at 0% 0%,#0d9488 0%,#0f766e 40%,#115e59 100%);box-shadow:0 20px 50px -16px rgba(13,148,136,.6)">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <div class="text-[12px] opacity-85 ne font-medium"><?= htmlspecialchars($bsDateStr,ENT_QUOTES,'UTF-8') ?></div>
        <h1 class="text-[20px] font-extrabold leading-tight mt-1 ne">
          <?= $t('स्वागत छ','Welcome to') ?> <span class="text-amber-200">आकाशवाणी</span>
        </h1>
        <p class="text-[12.5px] opacity-90 ne mt-1 leading-relaxed">
          <?= $t('समाचार, बजार, पात्रो, सरकारी सेवा — सबै एकै App मा।','News, market, patro, gov services — all in one app.') ?>
        </p>
      </div>
      <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 flex items-center justify-center">
        <span class="text-2xl">🇳🇵</span>
      </div>
    </div>

    <div class="mt-3 grid grid-cols-6 gap-2">
      <a href="/news.php" onclick="return openInDetailPane('/news.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/news.php">
        <i data-lucide="newspaper" class="w-4 h-4"></i><span class="ne"><?= $t('समाचार','News') ?></span>
      </a>
      <a href="/nepali-patro-enhanced.php" onclick="return openInDetailPane('/nepali-patro-enhanced.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/nepali-patro-enhanced.php">
        <i data-lucide="calendar-days" class="w-4 h-4"></i><span class="ne"><?= $t('पात्रो','Patro') ?></span>
      </a>
      <a href="/rashifal.php" onclick="return openInDetailPane('/rashifal.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/rashifal.php">
        <i data-lucide="sparkles" class="w-4 h-4"></i><span class="ne"><?= $t('राशिफल','Rashi') ?></span>
      </a>
      <a href="/cricket.php" onclick="return openInDetailPane('/cricket.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/cricket.php">
        <i data-lucide="trophy" class="w-4 h-4"></i><span class="ne"><?= $t('क्रिकेट','Cricket') ?></span>
      </a>
      <a href="/weather.php" onclick="return openInDetailPane('/weather.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/weather.php">
        <i data-lucide="cloud-sun" class="w-4 h-4"></i><span class="ne"><?= $t('मौसम','Weather') ?></span>
      </a>
      <a href="/transportation.php" onclick="return openInDetailPane('/transportation.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/transportation.php">
        <i data-lucide="bus" class="w-4 h-4"></i><span class="ne"><?= $t('यातायात','Bus') ?></span>
      </a>
      <a href="/nokari.php" onclick="return openInDetailPane('/nokari.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/nokari.php">
        <i data-lucide="briefcase" class="w-4 h-4"></i><span class="ne"><?= $t('नोकरी','Jobs') ?></span>
      </a>
      <a href="/auction-notices.php" onclick="return openInDetailPane('/auction-notices.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/auction-notices.php">
        <i data-lucide="gavel" class="w-4 h-4"></i><span class="ne"><?= $t('लिलामी','Auction') ?></span>
      </a>
      <a href="/kundali-milan.php" onclick="return openInDetailPane('/kundali-milan.php');" class="hero-icon-btn bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 rounded-xl py-2 text-center text-[11px] font-semibold flex flex-col items-center gap-1 transition-all" data-url="/kundali-milan.php">
        <i data-lucide="heart" class="w-4 h-4"></i><span class="ne"><?= $t('कुण्डली','Kundali') ?></span>
      </a>
    </div>
  </div>
</section>

<!-- ═══ MERO RASHIFAL — Personalized daily rashi card ════════════════════════
     After the user picks their rashi (on /rashifal.php → star button) it is
     stored in localStorage as `nsh_fav_rashi` (0-11).  On every home-page
     load this widget reads that value, shows the personalised card, and
     fetches today's reading from /api/rashifal.php in the background.
     No server-side state — works without login.
═══════════════════════════════════════════════════════════════════════════ -->
<div id="mero-rashi-wrap" class="mt-3"></div>
<script>
(function(){
  var RASHIS=[['मेष','♈','Aries'],['वृष','♉','Taurus'],['मिथुन','♊','Gemini'],['कर्कट','♋','Cancer'],['सिंह','♌','Leo'],['कन्या','♍','Virgo'],['तुला','♎','Libra'],['वृश्चिक','♏','Scorpio'],['धनु','♐','Sagittarius'],['मकर','♑','Capricorn'],['कुम्भ','♒','Aquarius'],['मीन','♓','Pisces']];
  var wrap=document.getElementById('mero-rashi-wrap');
  var saved=null;
  try{var v=localStorage.getItem('nsh_fav_rashi');saved=v!==null?parseInt(v,10):null;}catch(_){}

  if(saved!==null&&saved>=0&&saved<12){
    var r=RASHIS[saved];
    /* ── Personal card ─────────────────────────────────────── */
    wrap.innerHTML='<div class="rounded-3xl overflow-hidden relative" style="background:radial-gradient(140% 120% at 5% 5%,#7c3aed 0%,#4338ca 48%,#1e1b4b 100%);box-shadow:0 16px 40px -14px rgba(99,54,210,.55)">'
      +'<div class="p-4 relative z-10">'
        +'<div class="absolute inset-0 z-0 pointer-events-none" id="mr-stars"></div>'
        +'<div class="relative z-10">'
          +'<div class="flex items-center gap-2 mb-3">'
            +'<span class="text-[10.5px] font-bold text-white bg-white/15 backdrop-blur-sm border border-white/20 px-2.5 py-0.5 rounded-full flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-violet-300 animate-pulse inline-block"></span>मेरो राशिफल</span>'
            +'<span class="ml-auto text-[10px] text-white/60 ne" id="mr-bs-date"></span>'
          +'</div>'
          +'<div class="flex items-center gap-4">'
            +'<span class="text-[52px] leading-none select-none" style="text-shadow:0 0 40px rgba(255,255,255,.3)">'+r[1]+'</span>'
            +'<div class="min-w-0 flex-1">'
              +'<div class="text-[22px] font-extrabold text-white leading-tight ne">'+r[0]+' <span class="text-[13px] font-normal text-white/60">'+r[2]+'</span></div>'
              +'<div class="flex flex-wrap gap-1.5 mt-1.5">'
                +'<span id="mr-num" class="text-[10.5px] text-white/90 bg-white/15 border border-white/10 px-2 py-0.5 rounded-full">शुभ अंक: …</span>'
                +'<span id="mr-col" class="text-[10.5px] text-white/90 bg-white/15 border border-white/10 px-2 py-0.5 rounded-full">रंग: …</span>'
              +'</div>'
            +'</div>'
          +'</div>'
          +'<p id="mr-text" class="text-[12.5px] text-white/85 leading-relaxed mt-3 ne" style="text-shadow:0 1px 3px rgba(0,0,0,.25)">आजको राशिफल लोड हुँदैछ…</p>'
          +'<div class="flex items-center gap-3 mt-3">'
            +'<a href="/rashifal.php?r='+saved+'" class="text-[11.5px] font-bold text-white bg-white/20 hover:bg-white/30 border border-white/20 px-3.5 py-1.5 rounded-full transition-colors ne">पूरा राशिफल हेर्नुस् →</a>'
            +'<button id="mr-clear" class="text-[10px] text-white/45 hover:text-white/80 ml-auto transition-colors ne">बदल्नुस्</button>'
          +'</div>'
        +'</div>'
      +'</div>'
    +'</div>';

    /* Sprinkle CSS star particles */
    (function(){
      var sc=document.getElementById('mr-stars');
      if(!sc)return;
      sc.innerHTML='';
      for(var i=0;i<22;i++){
        var sz=[1.5,2,2.5,3][Math.floor(Math.random()*4)];
        sc.innerHTML+='<span class="mr-star" style="width:'+sz+'px;height:'+sz+'px;top:'+(Math.random()*100)+'%;left:'+(Math.random()*100)+'%;animation:twink '+(1.5+Math.random()*2.5).toFixed(1)+'s ease-in-out '+(Math.random()*2).toFixed(1)+'s infinite;opacity:.3"></span>';
      }
    })();

    /* Inject today's BS date */
    var bsEl=document.getElementById('mr-bs-date');
    if(bsEl){try{var bsRaw='<?= addslashes($bsDateStr) ?>';if(bsRaw)bsEl.textContent=bsRaw;}catch(_){}}

    /* Clear / change rashi */
    var clearBtn=document.getElementById('mr-clear');
    if(clearBtn)clearBtn.addEventListener('click',function(){
      try{localStorage.removeItem('nsh_fav_rashi');}catch(_){}
      wrap.innerHTML='';renderPicker();
    });

    /* Fetch today's reading */
    fetch('/api/rashifal.php?rashi='+saved+'&lang=ne')
      .then(function(r){return r.json();})
      .then(function(d){
        if(!d||!d.readings)return;
        var rd=d.readings;
        var t=document.getElementById('mr-text');if(t)t.textContent=rd.general||rd.love||'आज सकारात्मक ऊर्जा लिएर अगाडि बढ्नुस्।';
        var n=document.getElementById('mr-num');if(n&&rd.lucky_number)n.textContent='शुभ अंक: '+rd.lucky_number;
        var c=document.getElementById('mr-col');if(c&&rd.lucky_color)c.textContent='रंग: '+rd.lucky_color;
      }).catch(function(){
        var t=document.getElementById('mr-text');if(t)t.textContent='आज सकारात्मक सोचसहित अगाडि बढ्नुस् — सफलता नजिक छ।';
      });

  } else {
    renderPicker();
  }

  function renderPicker(){
    var syms=['♈','♉','♊','♋','♌','♍','♎','♏','♐','♑','♒','♓'];
    var names=['मेष','वृष','मिथुन','कर्कट','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'];
    var html='<div class="rounded-3xl bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-100 p-4">'
      +'<div class="flex items-center gap-2 mb-1"><i data-lucide="sparkles" class="w-5 h-5 text-violet-600"></i><div class="text-[14px] font-extrabold text-violet-900 ne">मेरो राशिफल सेट गर्नुस्</div></div>'
      +'<p class="text-[11.5px] text-slate-500 mb-3 ne leading-snug">एक पटक छान्नुभएपछि — हरेच दिन मुख्य पानामा <b>आफ्नो राशिफल</b> स्वतः देखिनेछ।</p>'
      +'<div class="grid grid-cols-6 gap-1.5">';
    syms.forEach(function(s,i){
      html+='<button class="mr-pick-btn flex flex-col items-center gap-0.5 py-2 rounded-xl bg-white hover:bg-violet-100 border border-violet-100 active:scale-95 transition-all text-center" data-i="'+i+'">'
        +'<span class="text-[18px] leading-none">'+s+'</span>'
        +'<span class="text-[9px] font-bold text-slate-600 ne">'+names[i]+'</span>'
        +'</button>';
    });
    html+='</div></div>';
    wrap.innerHTML=html;
    wrap.querySelectorAll('.mr-pick-btn').forEach(function(btn){
      btn.addEventListener('click',function(){
        var idx=parseInt(btn.getAttribute('data-i'),10);
        try{localStorage.setItem('nsh_fav_rashi',String(idx));}catch(_){}
        wrap.innerHTML='';
        /* Force re-run by reloading (simpler than full re-render) */
        location.reload();
      });
    });
  }
})();
</script>

<!-- ═══ BREAKING STRIP ═══════════════════════════════════════════════════════ -->
<?php if(!empty($breaking)): ?>
<section class="mt-3">
  <div class="rounded-2xl bg-rose-50 border border-rose-100 overflow-hidden">
    <div class="flex items-center gap-2 px-3 py-2">
      <span class="bg-rose-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider flex-shrink-0">
        <?= $t('ब्रेकिङ','Live') ?>
      </span>
      <div class="flex-1 overflow-hidden">
        <div class="flex gap-8 whitespace-nowrap" style="animation:marquee 36s linear infinite">
          <?php foreach(array_merge($breaking,$breaking) as $bn): ?>
            <a href="/news-post.php?slug=<?= urlencode($bn['slug']??'') ?>" class="text-[12.5px] font-semibold text-rose-800 ne">
              <?= htmlspecialchars(mb_substr($bn['title'],0,90),ENT_QUOTES,'UTF-8') ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══ SERVICES TILE GRID (Nagarik-style) ═════════════════════════════════════ -->
<div class="sec-title">
  <i data-lucide="layout-grid" class="w-4 h-4 text-brand-600"></i>
  <span class="ne"><?= $t('मुख्य सेवाहरू','Services') ?></span>
  <a href="#all-services" class="badge"><?= $t('सबै हेर्नुस्','View all') ?></a>
</div>
<section class="grid grid-cols-4 gap-2">
  <?php
  $tiles=[
    ['/news.php',         'newspaper',     $t('समाचार','News'),       'bg-i1'],
    ['/nepali-patro.php', 'calendar-days', $t('पात्रो','Patro'),     'bg-i3'],
    ['/rashifal.php',     'sparkles',      $t('राशिफल','Rashifal'),  'bg-i4'],
    ['/ipo-tracker.php',  'trending-up',   'NEPSE',                  'bg-i2'],
    ['/tools.php',        'wrench',        $t('टूलहरू','Tools'),     'bg-i5'],
    ['/gov-services.php', 'landmark',      $t('सरकारी','Gov'),       'bg-i7'],
    ['/tax-calculator.php','receipt',      $t('कर','Tax'),           'bg-i8'],
    ['/emergency.php',    'phone-call',    $t('आपतकाल','SOS'),       'bg-i6'],
  ];
  foreach($tiles as [$h,$ic,$lb,$bg]): ?>
    <a href="<?= $h ?>" class="tile">
      <span class="ic <?= $bg ?>"><i data-lucide="<?= $ic ?>" class="w-[18px] h-[18px]"></i></span>
      <span class="lbl ne"><?= $lb ?></span>
    </a>
  <?php endforeach; ?>
</section>

<div class="sec-title">
  <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
  <span class="ne"><?= $t('नेपाल आज','Nepal Today') ?></span>
  <span class="badge inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span><?= $t('स्मार्ट','Smart') ?></span>
</div>
<section id="nepal-aaja" class="app-card p-3 overflow-hidden">
  <div class="rounded-2xl p-3 text-white bg-gradient-to-br from-slate-900 via-teal-900 to-emerald-800">
    <div class="flex items-start gap-3">
      <div class="w-11 h-11 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center flex-shrink-0">
        <i data-lucide="radar" class="w-5 h-5"></i>
      </div>
      <div class="min-w-0 flex-1">
        <div class="text-[11px] text-white/90 font-medium ne"><?= htmlspecialchars($bsDateStr, ENT_QUOTES, 'UTF-8') ?></div>
        <h2 class="text-[16px] font-extrabold leading-snug text-white ne"><?= $t('आज तपाईंलाई चाहिने मुख्य कुरा','Everything important for today') ?></h2>
        <p id="na-summary" class="text-[12px] text-white font-medium mt-1 ne" style="text-shadow:0 1px 2px rgba(0,0,0,.25)">मौसम, अलर्ट, लोकसेवा र समाचार लोड हुँदै…</p>
      </div>
    </div>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-2" id="na-grid">
    <?php foreach([
      ['/utilities.php', 'cloud-sun', $t('मौसम','Weather'), 'लोड हुँदै…', 'sky'],
      ['/alerts.php', 'siren', $t('अलर्ट','Alerts'), 'लोड हुँदै…', 'rose'],
      ['/loksewa.php', 'briefcase', $t('लोकसेवा','Loksewa'), 'लोड हुँदै…', 'purple'],
      ['/news.php', 'newspaper', $t('समाचार','News'), 'लोड हुँदै…', 'teal'],
    ] as $na): ?>
      <a href="<?= $na[0] ?>" class="rounded-2xl border border-slate-100 bg-slate-50 hover:bg-white p-2.5 flex items-center gap-2 transition-colors">
        <span class="w-9 h-9 rounded-xl bg-<?= $na[4] ?>-100 text-<?= $na[4] ?>-700 flex items-center justify-center flex-shrink-0"><i data-lucide="<?= $na[1] ?>" class="w-4 h-4"></i></span>
        <span class="min-w-0">
          <span class="block text-[11px] font-bold text-slate-900 ne"><?= $na[2] ?></span>
          <span class="block text-[10px] text-slate-500 truncate ne" data-na-label="<?= htmlspecialchars($na[2], ENT_QUOTES) ?>"><?= $na[3] ?></span>
        </span>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="grid grid-cols-3 gap-2 mt-2">
    <a href="/rashifal.php" class="rounded-xl bg-violet-50 text-violet-800 px-2 py-2 text-center text-[11px] font-bold ne"><i data-lucide="sparkles" class="w-3.5 h-3.5 inline-block"></i> <?= $t('राशिफल','Rashi') ?></a>
    <a href="/nepali-patro.php" class="rounded-xl bg-indigo-50 text-indigo-800 px-2 py-2 text-center text-[11px] font-bold ne"><i data-lucide="calendar-days" class="w-3.5 h-3.5 inline-block"></i> <?= $t('पात्रो','Patro') ?></a>
    <a href="/gov-services.php" class="rounded-xl bg-emerald-50 text-emerald-800 px-2 py-2 text-center text-[11px] font-bold ne"><i data-lucide="landmark" class="w-3.5 h-3.5 inline-block"></i> <?= $t('सेवा','Gov') ?></a>
  </div>
</section>
<script>
(function(){
  var box = document.getElementById('nepal-aaja');
  if (!box) return;
  function setCard(label, text){
    var el = Array.prototype.find.call(box.querySelectorAll('[data-na-label]'), function(x){ return x.getAttribute('data-na-label') === label; });
    if (el) el.textContent = text || 'उपलब्ध छैन';
  }
  var facts = [];
  Promise.all([
    fetch('/api/weather-alerts.php?type=weather&city=' + encodeURIComponent('काठमाडौं')).then(function(r){return r.json();}).catch(function(){return null;}),
    fetch('/api/alerts.php').then(function(r){return r.json();}).catch(function(){return null;}),
    fetch('/api/loksewa.php?type=all&limit=3').then(function(r){return r.json();}).catch(function(){return null;}),
    fetch('/api/news-rss.php?limit=3').then(function(r){return r.json();}).catch(function(){return null;})
  ]).then(function(res){
    var w = res[0] || {};
    if (w && w.available !== false && (w.temp_c !== undefined || w.temperature !== undefined)) {
      var wt = (w.temp_c !== undefined ? w.temp_c : w.temperature) + '°C · ' + (w.desc_ne || w.condition || 'काठमाडौं');
      setCard('<?= $t('मौसम','Weather') ?>', wt);
      facts.push('काठमाडौंमा ' + wt);
    } else {
      setCard('<?= $t('मौसम','Weather') ?>', 'काठमाडौं मौसम हेर्नुहोस्');
    }
    var alerts = (res[1] && (res[1].items || res[1].alerts)) || [];
    setCard('<?= $t('अलर्ट','Alerts') ?>', alerts.length ? alerts.length + ' वटा सक्रिय सूचना' : 'हाल ठूलो अलर्ट छैन');
    if (alerts.length) facts.push(alerts.length + ' अलर्ट');
    var lok = (res[2] && (res[2].items || res[2].notices || res[2].data)) || [];
    setCard('<?= $t('लोकसेवा','Loksewa') ?>', lok.length ? lok.length + ' नयाँ सूचना' : 'नयाँ सूचना छैन');
    if (lok.length) facts.push(lok.length + ' लोकसेवा सूचना');
    var news = (res[3] && (res[3].items || res[3].news || res[3].data)) || [];
    setCard('<?= $t('समाचार','News') ?>', news.length ? news.length + ' ताजा headline' : 'समाचार हेर्नुहोस्');
    if (news.length) facts.push(news.length + ' headline');
    var summary = document.getElementById('na-summary');
    if (summary) summary.textContent = facts.length ? facts.slice(0,3).join(' · ') : 'आजका मुख्य अपडेटहरू app भित्रै हेर्नुहोस्।';
    if(window.lucide&&lucide.createIcons) lucide.createIcons();
  }).catch(function(){
    var summary = document.getElementById('na-summary');
    if (summary) summary.textContent = 'आजका मुख्य अपडेटहरू app भित्रै हेर्नुहोस्।';
  });
})();
</script>

<!-- ═══ LATEST NEWS (LIVE RSS — same source as /news.php) ════════════════════ -->
<!-- v11 fix: home news ले DB-bound getPublishedNews() बाट देखाउँथ्यो जुन
     /news.php (RSS-powered) सँग पूर्ण मेल खाँदैनथ्यो। अब दुवै एउटै single
     source — /api/news-rss.php बाट hydrate। -->
<div class="sec-title">
  <i data-lucide="newspaper" class="w-4 h-4 text-brand-600"></i>
  <span class="ne"><?= $t('ताजा समाचार','Latest News') ?></span>
  <a href="/news.php" class="badge"><?= $t('सबै','All →') ?></a>
</div>
<div id="home-news-feat" class="block"></div>
<section class="mt-3 flex flex-col gap-2" id="home-news-list">
  <!-- skeleton -->
  <div class="news-row" style="opacity:.45">
    <div class="thumb" style="background:#e2e8f0"></div>
    <div class="min-w-0 flex-1"><div style="height:10px;width:30%;background:#e2e8f0;border-radius:6px"></div>
      <div style="height:12px;width:90%;background:#e2e8f0;border-radius:6px;margin-top:8px"></div>
      <div style="height:12px;width:60%;background:#e2e8f0;border-radius:6px;margin-top:6px"></div></div>
  </div>
</section>
<a href="/news.php" class="mt-2 block text-center text-[12.5px] font-semibold text-brand-700 bg-brand-50 border border-brand-100 py-2.5 rounded-2xl">
  <?= $t('सबै समाचार हेर्नुस्','See all news') ?> →
</a>
<script>
(function(){
  function esc(s){return String(s||'').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}
  function timeAgo(ts){
    if(!ts) return '';
    var d=(Date.now()/1000)-ts;
    if(d<60)return 'भर्खरै';
    if(d<3600)return Math.floor(d/60)+' मिनेट अघि';
    if(d<86400)return Math.floor(d/3600)+' घण्टा अघि';
    return Math.floor(d/86400)+' दिन अघि';
  }
  fetch('/api/news-rss.php?limit=10').then(function(r){return r.json();}).then(function(d){
    var items=(d&&(d.items||d.data||d.news))||[];
    var feat=document.getElementById('home-news-feat');
    var list=document.getElementById('home-news-list');
    if(!items.length){ list.innerHTML='<div class="text-[12px] text-slate-400 p-2">समाचार उपलब्ध छैन</div>'; return; }
    var f=items[0];
    var fImg=f.image||f.thumbnail||f.enclosure||'';
    var fUrl = f.internalUrl || (f.slug ? '/news-detail.php?slug=' + encodeURIComponent(f.slug) : '/news-detail.php?url=' + encodeURIComponent(f.link || f.url || '') + '&src=' + encodeURIComponent(f.sourceLabel || ''));
    feat.innerHTML='<a href="'+esc(fUrl)+'" class="block"><div class="feat">'+
      (fImg?'<img src="'+esc(fImg)+'" alt="" loading="lazy" onerror="this.style.display=\'none\'">':'')+
      '<div class="meta"><span class="pill">'+esc(f.sourceLabel||f.source||'News')+'</span>'+
      '<h3 class="ne">'+esc(f.title)+'</h3>'+
      '<div class="text-[11px] opacity-80 mt-1 flex items-center gap-2">'+
      '<i data-lucide="clock" class="w-3 h-3"></i>'+esc(timeAgo(f.timestamp||f.pubDate||0))+'</div>'+
      '</div></div></a>';
    var html='';
    items.slice(1,7).forEach(function(n){
      var img=n.image||n.thumbnail||n.enclosure||'';
      var nUrl = n.internalUrl || (n.slug ? '/news-detail.php?slug=' + encodeURIComponent(n.slug) : '/news-detail.php?url=' + encodeURIComponent(n.link || n.url || '') + '&src=' + encodeURIComponent(n.sourceLabel || ''));
      html+='<a href="'+esc(nUrl)+'" class="news-row">'+
        '<div class="thumb">'+(img?'<img src="'+esc(img)+'" alt="" loading="lazy" onerror="this.closest(\'.thumb\').innerHTML=&quot;<div style=\\&quot;display:flex;width:100%;height:100%;align-items:center;justify-content:center;color:#0d9488\\&quot;><i data-lucide=\"newspaper\" class=\"w-6 h-6\"></i></div>&quot;">':'<div style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;color:#0d9488"><i data-lucide=\"newspaper\" class=\"w-6 h-6\"></i></div>')+'</div>'+
        '<div class="min-w-0 flex-1">'+
          '<span class="badge badge-primary badge-sm">'+esc(n.sourceLabel||n.source||'News')+'</span>'+
          '<h4 class="text-[13.5px] font-semibold text-ink leading-snug mt-1 line-clamp-2 ne">'+esc(n.title)+'</h4>'+
          '<div class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i>'+esc(timeAgo(n.timestamp||n.pubDate||0))+'</div>'+
        '</div></a>';
    });
    list.innerHTML=html;
    if(window.lucide&&lucide.createIcons) lucide.createIcons();
  }).catch(function(){
    var list=document.getElementById('home-news-list');
    if(list) list.innerHTML='<div class="text-[12px] text-slate-400 p-2">समाचार लोड हुन सकेन</div>';
  });
})();
</script>

<!-- ═══ MARKET SUMMARY CARD (animated counters) ══════════════════════════════ -->
<?php if($nepseIdx||$goldFine||$petrolP||$usdRate): ?>
<div class="sec-title">
  <i data-lucide="bar-chart-2" class="w-4 h-4 text-brand-600"></i>
  <span class="ne"><?= $t('बजार सारांश','Today\'s Market') ?></span>
  <span class="badge inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Live</span>
</div>
<section class="grid grid-cols-2 gap-2" id="market-grid">
  <?php
  $cards=array_filter([
    $nepseIdx?['NEPSE',$nepseIdx,'idx',2,$nepseChg,$nepseUp,'trending-up','from-blue-500 to-blue-600','/ipo-tracker.php']:null,
    $goldFine?[$t('सुन','Gold'),$goldFine,'gold',0,null,null,'gem','from-amber-500 to-amber-600','/utilities.php#gold']:null,
    $petrolP?[$t('पेट्रोल','Petrol'),$petrolP,'petrol',0,null,null,'fuel','from-orange-500 to-orange-600','/utilities.php#fuel']:null,
    $usdRate?['USD',$usdRate,'usd',2,null,null,'dollar-sign','from-emerald-500 to-emerald-600','/utilities.php#forex']:null,
  ]);
  $prefix=['gold'=>'रु ','petrol'=>'रु ','usd'=>'रु ','idx'=>''];
  foreach($cards as $c):
    [$lbl,$rawVal,$key,$dec,$chg,$up,$ic,$grad,$href]=$c;
    $dispPfx=$prefix[$key]??'';
  ?>
    <a href="<?= $href ?>" class="app-card p-3 flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl bg-gradient-to-br <?= $grad ?> text-white flex items-center justify-center flex-shrink-0">
        <i data-lucide="<?= $ic ?>" class="w-5 h-5"></i>
      </div>
      <div class="min-w-0">
        <div class="text-[11px] text-slate-500 font-medium ne"><?= $lbl ?></div>
        <div class="text-[14px] font-extrabold text-ink ne">
          <span class="mkt-pfx"><?= $dispPfx ?></span><span class="mkt-cnt" data-target="<?= $rawVal ?>" data-dec="<?= $dec ?>">0</span>
        </div>
        <?php if($chg!==null): ?>
          <div class="text-[10.5px] font-bold <?= $up?'text-emerald-600':'text-rose-600' ?>"><i data-lucide="<?= $up?'trending-up':'trending-down' ?>" class="w-3 h-3 inline-block"></i> <?= abs((float)$chg) ?></div>
        <?php endif; ?>
      </div>
    </a>
  <?php endforeach; ?>
</section>
<script>
/* Animated counter: numbers count up from 0 to target on page load */
(function(){
  var els=document.querySelectorAll('.mkt-cnt');
  if(!els.length)return;
  var dur=1100,fps=50,steps=Math.round(dur/(1000/fps));
  els.forEach(function(el){
    var target=parseFloat(el.getAttribute('data-target'))||0;
    var dec=parseInt(el.getAttribute('data-dec'))||0;
    var step=0;
    var timer=setInterval(function(){
      step++;
      var eased=1-Math.pow(1-(step/steps),3); /* ease-out cubic */
      el.textContent=(target*eased).toFixed(dec);
      if(step>=steps){el.textContent=target.toFixed(dec);clearInterval(timer);}
    },1000/fps);
  });
})();
</script>
<?php endif; ?>

<!-- ═══ TODAY'S RASHIFAL ════════════════════════════���════════════════════════ -->
<div class="sec-title">
  <i data-lucide="sparkles" class="w-4 h-4 text-pink-500"></i>
  <span class="ne"><?= $t('आजको राशिफल','Today\'s Rashifal') ?></span>
  <a href="/rashifal.php" class="badge"><?= $t('विस्तृत','Full →') ?></a>
</div>
<section class="app-card p-3">
  <div class="grid grid-cols-6 gap-1.5">
    <?php
    $rashiList=[['मेष','♈'],['वृष','♉'],['मिथुन','♊'],['कर्कट','♋'],['सिंह','♌'],['कन्या','♍'],['तुला','♎'],['वृश्चिक','♏'],['धनु','♐'],['मकर','♑'],['कुम्भ','♒'],['मीन','♓']];
    foreach($rashiList as $i=>[$rName,$sym]): ?>
      <a href="/rashifal.php#rashi-<?= $i ?>" class="flex flex-col items-center gap-0.5 py-2 px-0.5 rounded-xl hover:bg-pink-50 transition-colors">
        <span class="text-[18px] leading-none"><?= $sym ?></span>
        <span class="text-[9.5px] font-semibold text-slate-600 leading-tight ne"><?= $rName ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══ EMERGENCY NUMBERS ═════════════════════════════════════════════════════ -->
<div class="sec-title">
  <i data-lucide="phone-call" class="w-4 h-4 text-rose-600"></i>
  <span class="ne"><?= $t('आपतकालीन नम्बर','Emergency') ?></span>
  <a href="/emergency.php" class="badge"><?= $t('सबै','All →') ?></a>
</div>
<section class="grid grid-cols-4 gap-2">
  <?php foreach([
    ['Police','100','shield-alert','from-blue-500 to-blue-700'],
    ['Ambulance','102','activity','from-rose-500 to-rose-700'],
    ['Fire','101','flame','from-orange-500 to-red-600'],
    ['Traffic','103','car','from-emerald-500 to-emerald-700'],
  ] as [$name,$num,$ic,$grad]): ?>
    <a href="tel:<?= $num ?>" class="app-card p-2.5 flex flex-col items-center gap-1.5 text-center">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $grad ?> text-white flex items-center justify-center">
        <i data-lucide="<?= $ic ?>" class="w-[18px] h-[18px]"></i>
      </div>
      <div class="text-[10.5px] font-semibold text-slate-500"><?= $name ?></div>
      <div class="text-[15px] font-extrabold text-ink leading-none"><?= $num ?></div>
    </a>
  <?php endforeach; ?>
</section>

<!-- ═══ ALL SERVICES (Category-wise balanced view) ═════════════════════════ -->
<div id="all-services" class="sec-title">
  <i data-lucide="grid-3x3" class="w-4 h-4 text-slate-500"></i>
  <span class="ne"><?= $t('हाम्रा सबै सेवाहरू','All Services') ?></span>
  <span class="badge ne"><?= $t('वर्ग अनुसार','By category') ?></span>
</div>

<?php
/* Grouped services — easier to scan, easier to find */
$serviceGroups = [
  [$t('समाचार र जानकारी','News & Info'), 'newspaper', 'text-rose-600', [
    ['/news.php',         'newspaper',     $t('समाचार','News'),       'bg-i1'],
    ['/morning-brief.php','sunrise',       $t('बिहानी','Morning'),    'bg-i3'],
    ['/notices.php',      'bell-ring',     $t('सूचना','Notices'),     'bg-i4'],
    ['/alerts.php',       'bell',          $t('अलर्ट','Alerts'),      'bg-i6'],
  ]],
  [$t('पात्रो र राशिफल','Patro & Rashifal'), 'calendar-days', 'text-indigo-600', [
    ['/nepali-patro.php', 'calendar-days', $t('पात्रो','Patro'),      'bg-i3'],
    ['/rashifal.php',     'sparkles',      $t('राशिफल','Rashifal'),   'bg-i4'],
  ]],
  [$t('बजार र वित्त','Market & Finance'), 'trending-up', 'text-emerald-600', [
    ['/ipo-tracker.php',  'trending-up',   'IPO',                     'bg-i2'],
    ['/ipo-bulk-check.php','scan-line',    $t('BOLD Check','BOLD'),   'bg-i6'],
    ['/utilities.php',    'bar-chart-2',   $t('बजार','Market'),       'bg-i7'],
    ['/tax-calculator.php','receipt',      $t('कर','Tax'),            'bg-i8'],
  ]],
  [$t('सरकारी सेवा','Government'), 'landmark', 'text-teal-700', [
    ['/gov-services.php', 'landmark',      $t('सरकारी','Gov'),        'bg-i7'],
    ['/loksewa.php',      'briefcase',     $t('लोकसेवा','Loksewa'),   'bg-i6'],
    ['/ssf.php',          'shield',        'SSF',                     'bg-i7'],
    ['/vehicle.php',      'car-front',     $t('सवारी','Vehicle'),     'bg-i5'],
  ]],
  [$t('टूल र उपयोगिता','Tools & Utilities'), 'wrench', 'text-violet-600', [
    ['/tools.php',        'wrench',        $t('टूलहरू','Tools'),      'bg-i5'],
    ['/tax-calculator.php','receipt',      $t('कर','Tax'),            'bg-i8'],
    ['/downloads.php',    'download',      $t('डाउनलोड','Files'),     'bg-i5'],
    ['/directory.php',    'book-user',     $t('निर्देशिका','Directory'),'bg-i2'],
    ['/offers.php',       'tag',           $t('अफर','Offers'),        'bg-i4'],
    ['/ai-guides.php',    'bot',           'AI Guides',               'bg-i2'],
    ['/ai-chat.php',      'message-circle',$t('AI च्याट','AI Chat'),  'bg-i6'],
    ['/help.php',         'life-buoy',     $t('सहयोग','Help'),         'bg-i7'],
  ]],
  [$t('मनोरञ्जन र प्रेरणा','Media & Inspiration'), 'sparkles', 'text-rose-600', [
    ['/radio.php',          'radio',         $t('रेडियो','Radio'),         'bg-i1'],
    ['/podcast.php',        'podcast',       $t('पोडकास्ट','Podcast'),      'bg-i2'],
    ['/visit-place.php',    'camera',        $t('फोटोग्राफी','Photography'),'bg-i3'],
    ['/success-stories.php','trophy',        $t('सफलता कथा','Success'),    'bg-i4'],
    ['/story.php',          'book-open',     $t('कथा','Story'),            'bg-i6'],
    ['/visit-nepal.php',    'map-pin',       $t('घुम्ने ठाउँ','Visit'),    'bg-i7'],
  ]],
];
?>

<?php foreach($serviceGroups as [$gTitle, $gIcon, $gColor, $gItems]): ?>
  <div class="flex items-center gap-2 mt-3 mb-2 px-1">
    <i data-lucide="<?= $gIcon ?>" class="w-3.5 h-3.5 <?= $gColor ?>"></i>
    <span class="text-[12px] font-bold text-slate-700 ne"><?= $gTitle ?></span>
    <span class="flex-1 h-px bg-slate-200"></span>
    <span class="text-[10px] text-slate-400 font-semibold"><?= count($gItems) ?></span>
  </div>
  <section class="grid grid-cols-4 gap-2">
    <?php foreach($gItems as [$h,$ic,$lb,$bg]): ?>
      <a href="<?= $h ?>" class="tile">
        <span class="ic <?= $bg ?>"><i data-lucide="<?= $ic ?>" class="w-[18px] h-[18px]"></i></span>
        <span class="lbl ne"><?= $lb ?></span>
      </a>
    <?php endforeach; ?>
  </section>
<?php endforeach; ?>
<div class="mb-6"></div>


<script type="application/ld+json"><?= $jsonLd ?></script>

<?php include __DIR__ . '/footer.php'; ?>
