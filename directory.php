<?php
/**
 * directory.php v1 — नेपाल सम्पर्क निर्देशिका
 * Search offices, hospitals, banks, schools, emergency numbers
 */
$pageTitle = 'सम्पर्क निर्देशिका | आकाशवाणी';
$pageDesc  = 'नेपालका सरकारी कार्यालय, अस्पताल, बैंक, विद्यालय, इमर्जेन्सी नम्बर र अन्य संस्थाहरूको सम्पर्क नम्बर खोज्नुस्।';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- ── Header ──────────────────────────────────────────────────── -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-3">
    <span class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="book-user" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight ne">सम्पर्क निर्देशिका</h1>
      <p class="text-[11px] text-slate-500">कार्यालय · अस्पताल · बैंक · स्कुल · इमर्जेन्सी</p>
    </div>
  </div>

  <!-- Search box -->
  <div class="relative">
    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
    <input id="dir-search"
      type="search"
      placeholder="नाम, ठेगाना वा नम्बर खोज्नुस्…"
      class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-400 ne"
      autocomplete="off" autocorrect="off" spellcheck="false"
    />
    <button id="dir-clear" class="absolute right-3 top-1/2 -translate-y-1/2 hidden text-slate-400 hover:text-slate-600">
      <i data-lucide="x-circle" class="w-4 h-4"></i>
    </button>
  </div>
</section>

<!-- ── Category chips ───────────────────────────────────────────── -->
<nav class="px-4 mb-3">
  <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
    <?php foreach([
      ['',          '🔍', 'सबै'],
      ['emergency', '🚨', 'इमर्जेन्सी'],
      ['government','🏛️', 'सरकार'],
      ['hospital',  '🏥', 'अस्पताल'],
      ['bank',      '🏦', 'बैंक'],
      ['education', '🎓', 'शिक्षा'],
      ['telecom',   '📡', 'टेलिकम'],
      ['utility',   '⚡', 'युटिलिटी'],
      ['airport',   '✈️', 'हवाई'],
      ['media',     '📺', 'मिडिया'],
    ] as [$c,$ic,$lb]): ?>
    <button data-cat="<?= $c ?>"
      class="dir-chip flex-shrink-0 text-[11px] font-semibold px-3 py-1.5 rounded-full border whitespace-nowrap transition-colors
        <?= $c==='' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-600 border-slate-200' ?>">
      <?= $ic ?> <?= $lb ?>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── Emergency quick dial ──────────────────────────────────────── -->
<section class="px-4 mb-3" id="emergency-strip">
  <div class="bg-red-50 border border-red-200 rounded-xl p-3">
    <p class="text-[11px] font-bold text-red-700 mb-2 flex items-center gap-1.5">
      <i data-lucide="phone-call" class="w-3.5 h-3.5"></i> इमर्जेन्सी नम्बर
    </p>
    <div class="grid grid-cols-4 gap-2">
      <?php foreach([
        ['100','👮','प्रहरी'],
        ['101','🔥','दमकल'],
        ['102','🚑','एम्बुलेन्स'],
        ['1166','💙','मानसिक'],
      ] as [$num,$ic,$lb]): ?>
      <a href="tel:<?= $num ?>"
         class="flex flex-col items-center gap-1 bg-white border border-red-100 rounded-xl py-2 px-1 active:scale-95 transition-transform">
        <span class="text-lg"><?= $ic ?></span>
        <span class="text-[16px] font-black text-red-600"><?= $num ?></span>
        <span class="text-[9px] text-slate-500 font-semibold"><?= $lb ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Results count ─────────────────────────────────────────────── -->
<div class="px-4 mb-2 flex items-center justify-between">
  <p class="text-[11px] text-slate-500" id="dir-count">लोड हुँदैछ…</p>
  <p class="text-[10px] text-slate-400">Tap → Call</p>
</div>

<!-- ── Results list ──────────────────────────────────────────────── -->
<section class="px-4" id="dir-results">
  <div id="dir-loading" class="py-12 text-center">
    <div class="w-7 h-7 border-2 border-brand-400 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
    <p class="text-[12px] text-slate-400">निर्देशिका लोड हुँदैछ…</p>
  </div>
  <div id="dir-list"  class="space-y-2 hidden"></div>
  <div id="dir-empty" class="py-12 text-center hidden">
    <p class="text-4xl mb-2">🔍</p>
    <p class="text-[14px] font-semibold text-slate-700 ne">कुनै नतिजा भेटिएन</p>
    <p class="text-[12px] text-slate-400 mt-1">अर्को keyword प्रयास गर्नुस्</p>
  </div>
</section>

<div class="pb-6"></div>
</main>

<style>
.dir-chip.active { background:#0d9488; color:#fff; border-color:#0d9488; }
</style>

<script>
(function(){
  var allData = [];
  var currentCat = '';
  var currentQ   = '';

  var listEl  = document.getElementById('dir-list');
  var loadEl  = document.getElementById('dir-loading');
  var emptyEl = document.getElementById('dir-empty');
  var countEl = document.getElementById('dir-count');
  var searchEl= document.getElementById('dir-search');
  var clearEl = document.getElementById('dir-clear');
  var eStrip  = document.getElementById('emergency-strip');

  var catColors = {
    emergency:  'bg-red-50 text-red-700 border-red-200',
    government: 'bg-blue-50 text-blue-700 border-blue-200',
    hospital:   'bg-green-50 text-green-700 border-green-200',
    bank:       'bg-amber-50 text-amber-700 border-amber-200',
    education:  'bg-purple-50 text-purple-700 border-purple-200',
    telecom:    'bg-sky-50 text-sky-700 border-sky-200',
    utility:    'bg-orange-50 text-orange-700 border-orange-200',
    airport:    'bg-indigo-50 text-indigo-700 border-indigo-200',
    media:      'bg-pink-50 text-pink-700 border-pink-200',
  };
  var catLabels = {
    emergency:'🚨 इमर्जेन्सी', government:'🏛️ सरकार', hospital:'🏥 अस्पताल',
    bank:'🏦 बैंक', education:'🎓 शिक्षा', telecom:'📡 टेलिकम',
    utility:'⚡ युटिलिटी', airport:'✈️ हवाई', media:'📺 मिडिया',
  };

  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

  function highlight(text, q) {
    if (!q || !text) return esc(text);
    var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');
    return esc(text).replace(re, '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
  }

  function render(items, q) {
    loadEl.classList.add('hidden');
    countEl.textContent = items.length + ' नतिजा';

    if (!items.length) {
      listEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      return;
    }
    emptyEl.classList.add('hidden');
    listEl.classList.remove('hidden');

    listEl.innerHTML = items.map(function(e) {
      var cc  = catColors[e.cat] || 'bg-slate-50 text-slate-600 border-slate-200';
      var cl  = catLabels[e.cat] || e.cat;
      var phones = (e.phone || []);
      var primaryPhone = phones[0] || '';
      var extraPhones  = phones.slice(1);

      var phoneBtns = phones.map(function(p, i) {
        var isMobile = /^98|^97/.test(p);
        return '<a href="tel:' + esc(p) + '"'
          + ' class="inline-flex items-center gap-1 ' + (i===0
            ? 'bg-brand-600 text-white hover:bg-brand-700'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200')
          + ' text-[11px] font-bold px-3 py-1.5 rounded-full transition-colors active:scale-95">'
          + '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 flex-shrink-0"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.73 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.68 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.1a16 16 0 0 0 6 6l1.06-1.06a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 15z"/></svg>'
          + esc(p)
          + '</a>';
      }).join('');

      var webBtn = e.website
        ? '<a href="' + esc(e.website) + '" target="_blank" rel="noopener"'
          + ' class="inline-flex items-center gap-1 text-[11px] text-slate-400 hover:text-brand-600 ml-auto transition-colors">'
          + '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>'
          + 'Website</a>'
        : '';

      return '<div class="bg-white border border-slate-100 rounded-xl p-3 active:bg-slate-50">'
        + '<div class="flex items-start gap-2 mb-2">'
        + '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full border flex-shrink-0 mt-0.5 ' + cc + '">' + cl + '</span>'
        + (e.district ? '<span class="text-[10px] text-slate-400 mt-0.5">📍 ' + esc(e.district) + '</span>' : '')
        + webBtn
        + '</div>'
        + '<p class="text-[14px] font-bold text-slate-900 leading-snug ne mb-0.5">' + highlight(e.name, q) + '</p>'
        + '<p class="text-[11px] text-slate-400 mb-2">' + highlight(e.name_en, q) + (e.address ? ' · ' + esc(e.address) : '') + '</p>'
        + '<div class="flex flex-wrap gap-1.5">' + phoneBtns + '</div>'
        + '</div>';
    }).join('');
  }

  function doFilter() {
    var q   = currentQ.trim().toLowerCase();
    var cat = currentCat;
    var out = allData.filter(function(e) {
      if (cat && e.cat !== cat) return false;
      if (q) {
        var hay = ((e.name||'') + ' ' + (e.name_en||'') + ' ' + (e.address||'') + ' ' + (e.phone||[]).join(' ')).toLowerCase();
        return hay.indexOf(q) !== -1;
      }
      return true;
    });
    // hide emergency strip when filtering
    eStrip.style.display = (cat || q) ? 'none' : '';
    render(out, q);
  }

  // category chips
  document.querySelectorAll('.dir-chip').forEach(function(b) {
    b.addEventListener('click', function() {
      document.querySelectorAll('.dir-chip').forEach(function(x){ x.classList.remove('active'); });
      this.classList.add('active');
      currentCat = this.dataset.cat;
      doFilter();
    });
  });

  // search
  var debounce;
  searchEl.addEventListener('input', function() {
    currentQ = this.value;
    clearEl.classList.toggle('hidden', !currentQ);
    clearTimeout(debounce);
    debounce = setTimeout(doFilter, 220);
  });
  clearEl.addEventListener('click', function() {
    searchEl.value = '';
    currentQ = '';
    this.classList.add('hidden');
    doFilter();
  });

  // Fetch directory
  fetch('/api/directory.php?limit=200')
    .then(function(r){ return r.json(); })
    .then(function(d) {
      allData = (d && d.items) || [];
      doFilter();
    })
    .catch(function() {
      loadEl.classList.add('hidden');
      countEl.textContent = 'डेटा लोड गर्न सकिएन';
    });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
