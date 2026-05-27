<?php
/**
 * offers.php v1 — Nepal ISP, Daraz, Company Offers & Deals
 */
$pageTitle = 'अफर / डिल | आकाशवाणी';
$pageDesc  = 'NTC, Ncell, WorldLink, Daraz, eSewa लगायत नेपाली कम्पनीहरूका इन्टरनेट प्याकेज, छुट र अफरहरू एकै ठाउँमा।';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- ── Header ──────────────────────────────────────────────────── -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-3">
    <span class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="tag" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-800 leading-tight">अफर / डिल</h1>
      <p class="text-[11px] text-slate-500">ISP प्याकेज · Daraz छुट · Cashback · Travel</p>
    </div>
    <span class="ml-auto text-[10px] bg-rose-100 text-rose-600 font-bold px-2 py-1 rounded-full animate-pulse">
      🔴 Live
    </span>
  </div>

  <!-- Search -->
  <div class="relative">
    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
    <input id="of-search" type="search" placeholder="कम्पनी वा अफर खोज्नुस्…"
      class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400"
      autocomplete="off" />
  </div>
</section>

<!-- ── Category tabs ─────────────────────────────────────────────── -->
<nav class="px-4 mb-3">
  <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
    <?php foreach([
      ['',          '🔍', 'सबै'],
      ['isp',       '📶', 'इन्टरनेट'],
      ['ecommerce', '🛒', 'Shopping'],
      ['fintech',   '💰', 'Cashback'],
      ['food',      '🍕', 'खाना'],
      ['travel',    '✈️', 'यात्रा'],
      ['bank',      '🏦', 'बैंक'],
    ] as [$c,$ic,$lb]): ?>
    <button data-cat="<?= $c ?>"
      class="of-chip flex-shrink-0 text-[11px] font-semibold px-3 py-1.5 rounded-full border whitespace-nowrap transition-colors
        <?= $c==='' ? 'bg-rose-500 text-white border-rose-500' : 'bg-white text-slate-600 border-slate-200' ?>">
      <?= $ic ?> <?= $lb ?>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── ISP Quick Compare ─────────────────────────────────────────── -->
<section class="px-4 mb-3" id="isp-compare">
  <div class="bg-gradient-to-r from-brand-50 to-sky-50 border border-brand-100 rounded-xl p-3">
    <p class="text-[11px] font-bold text-brand-700 mb-2">📶 इन्टरनेट — सबैभन्दा सस्तो तुलना</p>
    <div class="grid grid-cols-3 gap-2">
      <?php foreach([
        ['NTC',      'रू २९/दिन', '1 GB Daily', 'bg-blue-600'],
        ['Ncell',    'रू ३०/दिन', '1.5 GB Daily', 'bg-red-500'],
        ['WorldLink','रू ७९९/म', '15Mbps Fiber', 'bg-emerald-600'],
      ] as [$co,$pr,$pl,$bg]): ?>
      <div class="bg-white rounded-xl p-2 text-center border border-slate-100">
        <span class="inline-block <?= $bg ?> text-white text-[9px] font-bold px-2 py-0.5 rounded-full mb-1"><?= $co ?></span>
        <p class="text-[15px] font-black text-slate-800 leading-none"><?= $pr ?></p>
        <p class="text-[9px] text-slate-400 mt-0.5"><?= $pl ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Results count ─────────────────────────────────────────────── -->
<div class="px-4 mb-2">
  <p class="text-[11px] text-slate-500" id="of-count">लोड हुँदैछ…</p>
</div>

<!-- ── Results ───────────────────────────────────────────────────── -->
<section class="px-4">
  <div id="of-loading" class="py-12 text-center">
    <div class="w-7 h-7 border-2 border-rose-400 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
    <p class="text-[12px] text-slate-400">अफरहरू लोड हुँदैछ…</p>
  </div>
  <div id="of-list" class="grid grid-cols-1 gap-2 hidden"></div>
  <div id="of-empty" class="py-12 text-center hidden">
    <p class="text-4xl mb-2">🏷️</p>
    <p class="text-[14px] font-semibold text-slate-600">कुनै अफर भेटिएन</p>
  </div>
</section>

<!-- ── Sources note ──────────────────────────────────────────────── -->
<section class="px-4 mt-4 mb-2">
  <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
    <p class="text-[10px] text-slate-400 leading-relaxed">
      📡 डेटा स्रोत: NTC, Ncell, Smart Cell, WorldLink, Subisu, Vianet, Classic Tech, Daraz, SastoDeal, eSewa, Khalti र अन्य आधिकारिक वेबसाइटहरू।
      ISP प्याकेज समय-समयमा परिवर्तन हुन सक्छन् — confirm गर्न कम्पनीको official site visit गर्नुस्।
    </p>
  </div>
</section>

<div class="pb-6"></div>
</main>

<script>
(function(){
  var allOffers = [];
  var currentCat = '';
  var currentQ   = '';

  var listEl  = document.getElementById('of-list');
  var loadEl  = document.getElementById('of-loading');
  var emptyEl = document.getElementById('of-empty');
  var countEl = document.getElementById('of-count');
  var searchEl= document.getElementById('of-search');
  var compare = document.getElementById('isp-compare');

  var catColor = {
    isp:       'bg-blue-100 text-blue-700',
    ecommerce: 'bg-orange-100 text-orange-700',
    fintech:   'bg-green-100 text-green-700',
    food:      'bg-red-100 text-red-700',
    travel:    'bg-indigo-100 text-indigo-700',
    bank:      'bg-amber-100 text-amber-700',
    telecom:   'bg-sky-100 text-sky-700',
  };
  var catLabel = {
    isp:'📶 इन्टरनेट', ecommerce:'🛒 Shopping', fintech:'💰 Fintech',
    food:'🍕 खाना', travel:'✈️ यात्रा', bank:'🏦 बैंक', telecom:'📡 Telecom',
  };

  var coColor = {
    'NTC':         'bg-blue-600', 'NTC Fiber':    'bg-blue-500',
    'Ncell':       'bg-red-500',  'Smart Cell':   'bg-purple-600',
    'WorldLink':   'bg-emerald-600','Subisu':     'bg-teal-600',
    'Vianet':      'bg-cyan-600', 'Classic Tech': 'bg-sky-600',
    'Daraz':       'bg-orange-500','SastoDeal':   'bg-yellow-600',
    'eSewa':       'bg-green-600','Khalti':       'bg-purple-500',
    'IME Pay':     'bg-indigo-600','Foodmandu':   'bg-rose-500',
    'Pathao':      'bg-blue-500', 'Buddha Air':  'bg-red-600',
    'Yeti Airlines':'bg-sky-600', 'Nabil Bank':  'bg-amber-600',
    'Himalayan Bank':'bg-teal-600','Everest Bank':'bg-green-700',
  };

  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

  function coInitial(co){ return (co||'?').charAt(0).toUpperCase(); }

  function renderCard(o) {
    var cc  = catColor[o.cat] || 'bg-slate-100 text-slate-600';
    var cl  = catLabel[o.cat] || o.cat;
    var bg  = coColor[o.company] || 'bg-slate-600';
    var disc = o.discount_pct > 0 ? '<span class="text-[11px] font-black text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">' + esc(o.discount_pct) + '% छुट</span>' : '';
    var badge = o.badge ? '<span class="text-[9px] font-bold bg-amber-400 text-white px-1.5 py-0.5 rounded-full">' + esc(o.badge) + '</span>' : '';
    var valid = o.valid_until ? '<span class="text-[10px] text-slate-400">⏰ ' + esc(o.valid_until) + '</span>' : '';
    var price = o.price ? '<span class="text-[15px] font-black text-slate-800">' + esc(o.price) + '</span>' : '';
    var oldPr = o.old_price ? '<span class="text-[11px] text-slate-400 line-through">' + esc(o.old_price) + '</span>' : '';

    return '<a href="' + esc(o.url || '#') + '" target="_blank" rel="noopener"'
      + ' class="flex gap-3 bg-white border border-slate-100 rounded-xl p-3 active:scale-[0.98] transition-transform hover:shadow-sm">'
      // avatar
      + '<div class="w-10 h-10 rounded-xl ' + bg + ' text-white flex items-center justify-center flex-shrink-0 font-black text-[14px]">' + esc(coInitial(o.company)) + '</div>'
      + '<div class="flex-1 min-w-0">'
        + '<div class="flex items-center gap-1.5 flex-wrap mb-0.5">'
          + '<span class="text-[10px] font-bold ' + cc + ' px-2 py-0.5 rounded-full">' + esc(o.company) + '</span>'
          + badge
          + disc
        + '</div>'
        + '<p class="text-[13px] font-bold text-slate-700 leading-snug mb-1">' + esc(o.title) + '</p>'
        + (o.summary ? '<p class="text-[11px] text-slate-500 leading-relaxed line-clamp-2 mb-1">' + esc(o.summary) + '</p>' : '')
        + '<div class="flex items-center gap-2 flex-wrap">'
          + price + oldPr + valid
          + '<span class="text-[10px] text-rose-500 font-semibold ml-auto">हेर्नुस् →</span>'
        + '</div>'
      + '</div>'
    + '</a>';
  }

  function doFilter() {
    var q   = currentQ.toLowerCase();
    var cat = currentCat;
    var out = allOffers.filter(function(o) {
      if (cat && o.cat !== cat) return false;
      if (q) {
        var hay = ((o.title||'') + ' ' + (o.company||'') + ' ' + (o.summary||'')).toLowerCase();
        return hay.indexOf(q) !== -1;
      }
      return true;
    });
    compare.style.display = (!cat && !q) ? '' : 'none';
    loadEl.classList.add('hidden');
    countEl.textContent = out.length + ' अफर';
    if (!out.length) {
      listEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      return;
    }
    emptyEl.classList.add('hidden');
    listEl.classList.remove('hidden');
    listEl.innerHTML = out.map(renderCard).join('');
  }

  document.querySelectorAll('.of-chip').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('.of-chip').forEach(function(x){ x.classList.remove('active'); });
      this.classList.add('active');
      currentCat = this.dataset.cat;
      doFilter();
    });
  });

  var deb;
  searchEl.addEventListener('input', function(){
    currentQ = this.value;
    clearTimeout(deb);
    deb = setTimeout(doFilter, 200);
  });

  fetch('/api/offers.php?limit=100')
    .then(function(r){ return r.json(); })
    .then(function(d){
      allOffers = (d && d.items) || [];
      doFilter();
    })
    .catch(function(){
      loadEl.classList.add('hidden');
      countEl.textContent = 'डेटा लोड हुन सकेन';
    });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
