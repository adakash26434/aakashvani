<?php
/**
 * loksewa.php v1 — लोकसेवा आयोग, सरकारी विज्ञापन, नतिजा
 * Tabs: सूचना | विज्ञापन | नतिजा | पाठ्यक्रम
 */
$pageTitle = 'लोकसेवा / सरकारी जागिर | आकाशवाणी';
$pageDesc  = 'लोकसेवा आयोग (PSC) सूचना, सरकारी जागिर विज्ञापन, परीक्षा नतिजा र पाठ्यक्रम — Gorkhapatra, PSC Nepal, OnlineKhabar बाट live।';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- ── Page header ──────────────────────────────────────────────── -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="briefcase" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight ne">लोकसेवा / सरकारी</h1>
      <p class="text-[11px] text-slate-500">PSC · Gorkhapatra · Setopati — Live</p>
    </div>
    <span id="lk-badge" class="ml-auto text-[10px] bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full hidden">
      <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse mr-1"></span>Live
    </span>
  </div>
</section>

<!-- ── Tab bar ──────────────────────────────────────────────────── -->
<nav class="px-4 mb-3">
  <div class="flex gap-1.5 bg-slate-100 rounded-xl p-1">
    <?php foreach([
      ['all',      '📋', 'सबै'],
      ['notice',   '🔔', 'सूचना'],
      ['vacancy',  '💼', 'विज्ञापन'],
      ['result',   '📊', 'नतिजा'],
      ['syllabus', '📚', 'पाठ्यक्रम'],
    ] as [$t,$ic,$lb]): ?>
    <button data-tab="<?= $t ?>"
      class="lk-tab flex-1 text-[11px] font-semibold py-1.5 rounded-lg transition-colors text-slate-500"
    ><?= $ic ?> <span class="hidden sm:inline"><?= $lb ?></span></button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── Quick links to official sites ─────────────────────────────── -->
<section class="px-4 mb-3">
  <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
    <?php foreach([
      ['PSC Nepal',      'https://www.psc.gov.np/en/notice',    'bg-brand-50 text-brand-700 border-brand-200'],
      ['Gorkhapatra',    'https://gorkhapatraonline.com/category/job-vacancy', 'bg-amber-50 text-amber-700 border-amber-200'],
      ['Sarkari Lagani', 'https://sarkarilagani.com/',           'bg-emerald-50 text-emerald-700 border-emerald-200'],
      ['लोकसेवा Guide',  'https://loksewa.com.np/',              'bg-purple-50 text-purple-700 border-purple-200'],
      ['Ktm2all Jobs',   'https://www.ktm2all.com/jobs/',        'bg-sky-50 text-sky-700 border-sky-200'],
    ] as [$name,$url,$cls]): ?>
    <a href="<?= $url ?>" target="_blank" rel="noopener"
       class="flex-shrink-0 text-[11px] font-semibold px-3 py-1.5 rounded-full border <?= $cls ?> whitespace-nowrap">
      <?= $name ?> ↗
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Content area ─────────────────────────────────────────────── -->
<section class="px-4" id="lk-content">
  <div id="lk-loading" class="py-16 text-center text-slate-400">
    <div class="w-8 h-8 border-2 border-brand-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
    <p class="text-[13px]">सूचनाहरू लोड हुँदैछ…</p>
  </div>
  <div id="lk-list" class="space-y-2 hidden"></div>
  <div id="lk-empty" class="py-16 text-center hidden">
    <p class="text-4xl mb-3">📋</p>
    <p class="text-[14px] font-semibold text-slate-700 ne">कुनै सूचना भेटिएन</p>
    <p class="text-[12px] text-slate-400 mt-1">अर्को sync मा नया सूचनाहरू आउनेछन्।</p>
    <a href="https://www.psc.gov.np/en/notice" target="_blank"
       class="mt-4 inline-block text-[12px] text-brand-600 font-semibold">PSC Nepal हेर्नुस् →</a>
  </div>
</section>

<!-- ── Source info ───────────────────────────────────────────────── -->
<section class="px-4 mt-4 mb-2">
  <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
    <p class="text-[11px] text-slate-500 font-medium mb-1.5">📡 डेटा स्रोतहरू</p>
    <div class="flex flex-wrap gap-2">
      <?php foreach([
        ['PSC Nepal',    'https://www.psc.gov.np'],
        ['Gorkhapatra',  'https://gorkhapatraonline.com'],
        ['OnlineKhabar', 'https://www.onlinekhabar.com'],
        ['Setopati',     'https://www.setopati.com'],
        ['Kantipur',     'https://ekantipur.com'],
        ['Ratopati',     'https://ratopati.com'],
      ] as [$n,$u]): ?>
      <span class="text-[10px] bg-white border border-slate-200 text-slate-500 px-2 py-0.5 rounded-full">
        <?= $n ?>
      </span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<style>
.lk-tab.active { background:#fff; color:#0d9488; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.type-notice   { border-left-color:#0284c7; }
.type-vacancy  { border-left-color:#16a34a; }
.type-result   { border-left-color:#dc2626; }
.type-syllabus { border-left-color:#7c3aed; }
</style>

<script>
(function(){
  var current = 'all';
  var allItems = [];

  var tabs    = document.querySelectorAll('.lk-tab');
  var listEl  = document.getElementById('lk-list');
  var loadEl  = document.getElementById('lk-loading');
  var emptyEl = document.getElementById('lk-empty');
  var badge   = document.getElementById('lk-badge');

  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

  var typeInfo = {
    notice:   { label:'🔔 सूचना',      cls:'bg-sky-100 text-sky-700',     bar:'type-notice'   },
    vacancy:  { label:'💼 विज्ञापन',    cls:'bg-green-100 text-green-700', bar:'type-vacancy'  },
    result:   { label:'📊 नतिजा',       cls:'bg-red-100 text-red-700',     bar:'type-result'   },
    syllabus: { label:'📚 पाठ्यक्रम',  cls:'bg-purple-100 text-purple-700',bar:'type-syllabus' },
    notice_default: { label:'📋 सूचना', cls:'bg-slate-100 text-slate-600', bar:'type-notice'   },
  };

  function renderItems(items) {
    loadEl.classList.add('hidden');
    if (!items || !items.length) {
      listEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      return;
    }
    emptyEl.classList.add('hidden');
    listEl.classList.remove('hidden');

    listEl.innerHTML = items.map(function(it){
      var tp   = typeInfo[it.type] || typeInfo['notice_default'];
      var time = it.ago ? '<span class="text-[10px] text-slate-400 ml-auto">' + esc(it.ago) + '</span>' : '';
      var src  = it.source ? '<span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">' + esc(it.source) + '</span>' : '';
      var sum  = it.summary ? '<p class="text-[12px] text-slate-500 leading-relaxed mt-1 line-clamp-2">' + esc(it.summary) + '</p>' : '';

      var safeUrl = encodeURIComponent(it.link || '');
      return '<a href="/notice-detail.php?type=loksewa&title=' + encodeURIComponent(it.title || '') + '&source=' + encodeURIComponent(it.source || 'PSC Nepal') + '&url=' + safeUrl + '"'
        + ' class="block bg-white border border-slate-100 rounded-xl overflow-hidden border-l-4 ' + tp.bar + ' active:scale-[0.98] transition-transform">'
        + '<div class="p-3">'
        + '<div class="flex items-start gap-2 mb-1.5">'
        + '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0 ' + tp.cls + '">' + tp.label + '</span>'
        + time
        + '</div>'
        + '<p class="text-[13.5px] font-semibold text-slate-800 leading-snug ne">' + esc(it.title) + '</p>'
        + sum
        + '<div class="flex items-center gap-2 mt-2">'
        + src
        + '<span class="text-[10px] text-brand-600 ml-auto font-semibold">हेर्नुस् →</span>'
        + '</div>'
        + '</div></a>';
    }).join('');
  }

  function filterAndRender(type) {
    current = type;
    tabs.forEach(function(b){
      b.classList.toggle('active', b.dataset.tab === type);
    });
    var filtered = type === 'all' ? allItems
      : allItems.filter(function(x){ return x.type === type; });
    renderItems(filtered);
  }

  // Tab clicks
  tabs.forEach(function(b){
    b.addEventListener('click', function(){ filterAndRender(this.dataset.tab); });
  });
  // Default active
  tabs[0] && tabs[0].classList.add('active');

  // Fetch
  fetch('/api/loksewa.php?type=all&limit=50')
    .then(function(r){ return r.json(); })
    .then(function(d){
      allItems = (d && d.items) || [];
      if (d && d.count > 0) badge.classList.remove('hidden');
      filterAndRender(current);
    })
    .catch(function(){
      loadEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
    });
})();
</script>

<!-- ══ PREP GUIDE ══════════════════════════════════════════════════════════ -->
<section class="px-4 mt-2 mb-2">
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="book-open" class="w-4 h-4 text-brand-600"></i>
    परीक्षा तयारी गाइड
  </h2>

  <!-- Subject weights -->
  <div class="bg-white rounded-2xl shadow-app p-3 mb-3">
    <div class="text-[11px] font-bold text-slate-600 mb-2">📊 खरिदार / असिस्टेन्ट — विषयगत भार</div>
    <div class="space-y-1.5">
      <?php foreach([
        ['नेपाली भाषा', 25, 'emerald'],
        ['सामान्य ज्ञान', 25, 'sky'],
        ['गणित / तर्क', 25, 'amber'],
        ['कम्प्युटर', 25, 'violet'],
      ] as [$sub,$pct,$cl]): ?>
      <div class="flex items-center gap-2">
        <span class="text-[11px] text-slate-600 w-36 shrink-0 ne"><?= $sub ?></span>
        <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
          <div class="h-2 rounded-full bg-<?= $cl ?>-500" style="width:<?= $pct ?>%"></div>
        </div>
        <span class="text-[10px] font-bold text-slate-700 w-7 text-right"><?= $pct ?>%</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Age limits quick table -->
  <div class="bg-white rounded-2xl shadow-app p-3 mb-3">
    <div class="text-[11px] font-bold text-slate-600 mb-2">⏳ उमेर सीमा — द्रुत सन्दर्भ</div>
    <div class="divide-y divide-slate-100">
      <?php foreach([
        ['खरिदार / असिस्टेन्ट','१८ – ३५ वर्ष'],
        ['अधिकृत (राजपत्र तृ.)','२१ – ३५ वर्ष'],
        ['श्रेणीविहीन / ज्यालादारी','१८ – ४५ वर्ष'],
        ['अपाङ्गता / पिछडिएको','थप ५ वर्ष छुट'],
      ] as [$post,$age]): ?>
      <div class="flex justify-between py-1.5 text-[12px]">
        <span class="text-slate-600 ne"><?= $post ?></span>
        <b class="text-brand-700 ne"><?= $age ?></b>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Exam steps -->
  <div class="bg-brand-50 border border-brand-100 rounded-2xl p-3 mb-3">
    <div class="text-[11px] font-bold text-brand-800 mb-2">🗺 परीक्षाको चरण (PSC Flow)</div>
    <ol class="space-y-1.5">
      <?php foreach([
        'विज्ञापन निकाल्छ — PSC वेबसाइट / गोरखापत्रमा',
        'दरखास्त भर्नुस् — psconline.psc.gov.np',
        'प्रवेशपत्र डाउनलोड गर्नुस्',
        'लिखित / बहुउत्तरीय परीक्षा दिनुस्',
        'नतिजा → अन्तर्वार्ता → नियुक्ति',
      ] as $i=>$s): ?>
      <li class="flex gap-2 items-start text-[11.5px] text-brand-900">
        <span class="w-5 h-5 rounded-full bg-brand-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5"><?= $i+1 ?></span>
        <span class="ne"><?= htmlspecialchars($s) ?></span>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>

  <!-- Important resources -->
  <div class="text-[11px] font-bold text-slate-600 mb-2">📚 महत्त्वपूर्ण वेबसाइटहरू</div>
  <div class="grid grid-cols-2 gap-2 mb-4">
    <?php foreach([
      ['psconline.psc.gov.np','दरखास्त पोर्टल','https://psconline.psc.gov.np/','brand'],
      ['gorkhapatraonline.com','विज्ञापन समाचार','https://gorkhapatraonline.com/category/job-vacancy','amber'],
      ['loksewa.com.np','तयारी सामग्री','https://loksewa.com.np/','emerald'],
      ['hamropsc.com','मोडेल प्रश्न','https://hamropsc.com/','violet'],
    ] as [$site,$desc,$url,$cl]): ?>
    <a href="<?= $url ?>" target="_blank" rel="noopener"
       class="bg-white rounded-xl shadow-app p-2.5 flex items-center gap-2">
      <span class="w-8 h-8 rounded-lg bg-<?= $cl ?>-100 text-<?= $cl ?>-700 flex items-center justify-center shrink-0">
        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
      </span>
      <div class="min-w-0">
        <div class="text-[10.5px] font-bold text-slate-800 truncate"><?= $site ?></div>
        <div class="text-[9.5px] text-slate-500 ne"><?= $desc ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
