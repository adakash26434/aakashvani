<?php
/**
 * ipo-bulk-check.php v1
 * IPO Allotment Bulk Checker — एकपटकमा धेरै BOLD ID check गर्नुस्
 * - LocalStorage मा save/load BOLDs
 * - DB मा पनि save (server-side persist)
 * - CDSC iporesult.cdsc.com.np बाट real allotment check
 */
$pageTitle = 'IPO Bulk Checker | आकाशवाणी';
$pageDesc  = 'आफ्नो र परिवारका सबै BOLD IDs एकैचोटि check गर्नुस् — IPO allotment result.';
require_once __DIR__ . '/header.php';

// Load saved BOLDs from DB
$savedBolds = [];
try {
    require_once __DIR__ . '/functions.php';
    $savedBolds = getSavedBolds();
} catch (\Throwable $e) {}
?>
<main class="app-main">

<!-- ── Header ─────────────────────────────────────────────────── -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-violet-600 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="scan-line" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">IPO Bulk Checker</h1>
      <p class="text-[11px] text-slate-500">धेरै BOLD ID एकैचोटि check गर्नुस्</p>
    </div>
    <a href="/ipo-tracker.php" class="ml-auto text-[11px] text-violet-600 font-bold bg-violet-50 border border-violet-200 px-3 py-1.5 rounded-full">
      ← IPO List
    </a>
  </div>
</section>

<!-- ── Step 1: Select IPO ─────────────────────────────────────── -->
<section class="px-4 mb-3">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2">① IPO चयन गर्नुस्</p>
    <div id="ipo-select-loading" class="text-[12px] text-slate-400 text-center py-3 flex items-center justify-center gap-2">
      <span class="w-3 h-3 border-2 border-violet-400 border-t-transparent rounded-full animate-spin inline-block"></span>
      IPO सूची लोड हुँदैछ…
    </div>
    <select id="ipo-select" class="hidden w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400 font-semibold">
      <option value="">-- IPO चयन गर्नुस् --</option>
    </select>
    <div id="selected-ipo-badge" class="hidden mt-2 flex items-center gap-2 text-[11px]">
      <span class="bg-violet-100 text-violet-700 font-bold px-2 py-0.5 rounded-full" id="selected-ipo-name">—</span>
      <span class="text-slate-400" id="selected-ipo-status"></span>
    </div>
  </div>
</section>

<!-- ── Step 2: Enter BOLDs ────────────────────────────────────── -->
<section class="px-4 mb-3">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <div class="flex items-center justify-between mb-2">
      <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide">② BOLD IDs</p>
      <button id="load-saved-btn" class="text-[11px] text-violet-600 font-semibold bg-violet-50 border border-violet-200 px-2.5 py-1 rounded-full">
        💾 Saved (<?= count($savedBolds) ?>) लोड
      </button>
    </div>
    <textarea id="bold-input" rows="5"
      class="w-full text-[13px] font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"
      placeholder="BOLD IDs यहाँ लेख्नुस् — एक लाइनमा एउटा&#10;उदाहरण:&#10;1234567890123456&#10;9876543210123456&#10;1111222233334444"></textarea>
    <div class="flex items-center justify-between mt-2">
      <p class="text-[10px] text-slate-400">प्रति लाइन एउटा BOLD (16+ अंक) · अल्पविराम वा space पनि चल्छ</p>
      <span id="bold-count-badge" class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">0 IDs</span>
    </div>
  </div>
</section>

<!-- ── Step 3: Options ────────────────────────────────────────── -->
<section class="px-4 mb-3">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2">③ विकल्प</p>
    <label class="flex items-center gap-2 text-[13px] text-slate-700 mb-2 cursor-pointer">
      <input type="checkbox" id="opt-save" class="accent-violet-600 w-4 h-4" checked />
      <span>यी BOLD IDs server मा save गर्नुस् (अर्को पटको लागि)</span>
    </label>
    <label class="flex items-center gap-2 text-[13px] text-slate-700 cursor-pointer">
      <input type="checkbox" id="opt-ls" class="accent-violet-600 w-4 h-4" checked />
      <span>Browser मा (LocalStorage) पनि save गर्नुस्</span>
    </label>
  </div>
</section>

<!-- ── Check Button ───────────────────────────────────────────── -->
<section class="px-4 mb-4">
  <button id="check-btn"
    class="w-full bg-violet-600 text-white font-bold py-3.5 rounded-2xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform disabled:opacity-60"
    disabled>
    <i data-lucide="search" class="w-5 h-5"></i>
    Allotment Check गर्नुस्
  </button>
  <div id="checking-banner" class="hidden mt-3 bg-violet-50 border border-violet-200 rounded-xl p-3 text-center">
    <div class="flex items-center justify-center gap-2 text-[13px] text-violet-700 font-semibold">
      <span class="w-4 h-4 border-2 border-violet-500 border-t-transparent rounded-full animate-spin inline-block"></span>
      <span id="checking-label">CDSC सँग check गर्दैछ…</span>
    </div>
    <p class="text-[10px] text-violet-400 mt-1">कृपया प्रतीक्षा गर्नुस् — प्रत्येक BOLD को लागि CDSC API call गरिँदैछ</p>
  </div>
</section>

<!-- ── Results ────────────────────────────────────────────────── -->
<section class="px-4 mb-4" id="results-section" style="display:none">
  <!-- Summary -->
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4 mb-3">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2">📊 परिणाम सारांश</p>
    <div class="grid grid-cols-3 gap-2">
      <div class="bg-slate-50 rounded-xl p-3 text-center">
        <div class="text-[22px] font-black text-slate-900" id="stat-total">0</div>
        <div class="text-[10px] text-slate-500">जम्मा</div>
      </div>
      <div class="bg-emerald-50 rounded-xl p-3 text-center border border-emerald-100">
        <div class="text-[22px] font-black text-emerald-700" id="stat-allotted">0</div>
        <div class="text-[10px] text-emerald-600">Allotted ✅</div>
      </div>
      <div class="bg-rose-50 rounded-xl p-3 text-center border border-rose-100">
        <div class="text-[22px] font-black text-rose-700" id="stat-not">0</div>
        <div class="text-[10px] text-rose-600">Allotted भएन ❌</div>
      </div>
    </div>
  </div>
  <!-- Result rows -->
  <div id="result-rows" class="space-y-2"></div>
  <!-- Source -->
  <p class="text-[10px] text-slate-400 text-center mt-2">📡 स्रोत: iporesult.cdsc.com.np (CDSC Official)</p>
</section>

<!-- ── Saved BOLDs ────────────────────────────────────────────── -->
<?php if ($savedBolds): ?>
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <div class="flex items-center justify-between mb-2">
      <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide">💾 Server मा Save भएका BOLDs</p>
      <span class="text-[10px] bg-slate-100 text-slate-600 font-bold px-2 py-0.5 rounded-full"><?= count($savedBolds) ?> वटा</span>
    </div>
    <div class="space-y-1.5">
      <?php foreach ($savedBolds as $b): ?>
      <div class="flex items-center justify-between gap-2 bg-slate-50 rounded-xl px-3 py-2">
        <div>
          <span class="font-mono text-[12px] font-bold text-slate-900"><?= substr($b['bold'],0,4) ?>…<?= substr($b['bold'],-4) ?></span>
          <?php if($b['label']): ?><span class="ml-2 text-[11px] text-slate-400"><?= h($b['label']) ?></span><?php endif; ?>
        </div>
        <button onclick="copyBold('<?= h($b['bold']) ?>')" class="text-[10px] text-violet-600 font-semibold px-2 py-1 rounded-lg bg-violet-50 hover:bg-violet-100">Copy</button>
      </div>
      <?php endforeach; ?>
    </div>
    <button onclick="loadSavedBolds()" class="mt-3 w-full text-[12px] text-violet-600 font-bold border border-violet-200 bg-violet-50 rounded-xl py-2 active:scale-[.98] transition-transform">
      ↑ माथिको textarea मा सबै लोड गर्नुस्
    </button>
  </div>
</section>
<?php endif; ?>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var LS_KEY_BOLDS   = 'nsh_bold_ids';
  var LS_KEY_COMPANY = 'nsh_bold_company';
  var selectedId     = 0;
  var selectedName   = '';

  /* ── esc helper ── */
  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

  /* ── Load IPO list ── */
  var selEl    = document.getElementById('ipo-select');
  var loadEl   = document.getElementById('ipo-select-loading');
  var badgeEl  = document.getElementById('selected-ipo-badge');
  var nameEl   = document.getElementById('selected-ipo-name');
  var statusEl = document.getElementById('selected-ipo-status');

  fetch('/api/ipo-data.php')
    .then(function(r){ return r.json(); })
    .then(function(d){
      loadEl.classList.add('hidden');
      selEl.classList.remove('hidden');
      var groups = [
        {label:'🟢 खुला (Active)', items: d.active   || []},
        {label:'🟡 आगामी (Upcoming)', items: d.upcoming || []},
        {label:'⚫ बन्द (Closed)', items: (d.closed  || []).slice(0,30)},
      ];
      groups.forEach(function(g){
        if (!g.items.length) return;
        var og = document.createElement('optgroup');
        og.label = g.label;
        g.items.forEach(function(ip){
          var o = document.createElement('option');
          o.value = ip.id || ip.companyShareId || '';
          o.textContent = ip.name + (ip.symbol ? ' ('+ip.symbol+')' : '');
          o.dataset.name = ip.name;
          o.dataset.status = ip.status || '';
          og.appendChild(o);
        });
        selEl.appendChild(og);
      });
      // restore last selection
      var last = localStorage.getItem(LS_KEY_COMPANY);
      if (last) {
        try{
          var p = JSON.parse(last);
          selEl.value = p.id || '';
          if (selEl.value) {
            selectedId = p.id; selectedName = p.name;
            nameEl.textContent = p.name; statusEl.textContent = p.status || '';
            badgeEl.classList.remove('hidden');
            updateCheckBtn();
          }
        }catch(e){}
      }
    })
    .catch(function(){ loadEl.textContent = 'IPO list लोड हुन सकेन'; });

  selEl.addEventListener('change', function(){
    var opt = selEl.options[selEl.selectedIndex];
    selectedId   = parseInt(this.value, 10) || 0;
    selectedName = (opt && opt.dataset.name) ? opt.dataset.name : this.options[this.selectedIndex].textContent;
    if (selectedId) {
      nameEl.textContent = selectedName;
      statusEl.textContent = (opt && opt.dataset.status) ? opt.dataset.status : '';
      badgeEl.classList.remove('hidden');
      if (document.getElementById('opt-ls').checked) {
        localStorage.setItem(LS_KEY_COMPANY, JSON.stringify({id:selectedId,name:selectedName}));
      }
    } else {
      badgeEl.classList.add('hidden');
    }
    updateCheckBtn();
  });

  /* ── BOLD input management ── */
  var boldInput  = document.getElementById('bold-input');
  var cntBadge   = document.getElementById('bold-count-badge');
  var checkBtn   = document.getElementById('check-btn');

  function parseBolds(val){
    return [...new Set(val.split(/[\s,;|]+/).map(function(b){return b.replace(/\D/g,'').trim();}).filter(function(b){return b.length >= 10;}))];
  }

  boldInput.addEventListener('input', function(){
    var bolds = parseBolds(this.value);
    cntBadge.textContent = bolds.length + ' IDs';
    updateCheckBtn();
    if (document.getElementById('opt-ls').checked && bolds.length) {
      localStorage.setItem(LS_KEY_BOLDS, JSON.stringify(bolds));
    }
  });

  function updateCheckBtn(){
    var bolds = parseBolds(boldInput.value);
    checkBtn.disabled = !(bolds.length > 0 && selectedId > 0);
  }

  /* Restore BOLDs from localStorage */
  (function(){
    try{
      var saved = localStorage.getItem(LS_KEY_BOLDS);
      if (saved){
        var arr = JSON.parse(saved);
        if (arr && arr.length){ boldInput.value = arr.join('\n'); boldInput.dispatchEvent(new Event('input')); }
      }
    }catch(e){}
  })();

  /* Load saved BOLDs from server list */
  window.loadSavedBolds = function(){
    var serverBolds = <?= json_encode(array_column($savedBolds,'bold'), JSON_UNESCAPED_UNICODE) ?>;
    if (!serverBolds || !serverBolds.length){ alert('कुनै saved BOLD छैन।'); return; }
    var existing = parseBolds(boldInput.value);
    var merged = [...new Set([...existing,...serverBolds])];
    boldInput.value = merged.join('\n');
    boldInput.dispatchEvent(new Event('input'));
  };

  document.getElementById('load-saved-btn').addEventListener('click', function(){
    // Also try server-side saved
    window.loadSavedBolds();
    // And localStorage
    try{
      var s=localStorage.getItem(LS_KEY_BOLDS);
      if(s){ var a=JSON.parse(s); if(a&&a.length){ var ex=parseBolds(boldInput.value); var mg=[...new Set([...ex,...a])]; boldInput.value=mg.join('\n'); boldInput.dispatchEvent(new Event('input')); } }
    }catch(e){}
  });

  window.copyBold = function(bold){
    navigator.clipboard && navigator.clipboard.writeText(bold).then(function(){alert('Copied: '+bold);});
  };

  /* ── Check button ── */
  var banner   = document.getElementById('checking-banner');
  var label    = document.getElementById('checking-label');
  var resultsS = document.getElementById('results-section');
  var rows     = document.getElementById('result-rows');
  var stTotal  = document.getElementById('stat-total');
  var stAll    = document.getElementById('stat-allotted');
  var stNot    = document.getElementById('stat-not');

  checkBtn.addEventListener('click', function(){
    var bolds = parseBolds(boldInput.value);
    if (!bolds.length || !selectedId){ alert('BOLD IDs र IPO दुवै छान्नुस्।'); return; }

    // Save to localStorage
    if (document.getElementById('opt-ls').checked) {
      localStorage.setItem(LS_KEY_BOLDS, JSON.stringify(bolds));
      localStorage.setItem(LS_KEY_COMPANY, JSON.stringify({id:selectedId,name:selectedName}));
    }

    checkBtn.disabled = true;
    banner.classList.remove('hidden');
    resultsS.style.display = 'none';
    label.textContent = bolds.length + ' BOLD CDSC बाट check गर्दैछ… (~' + Math.ceil(bolds.length * 0.4) + ' सेकेन्ड)';

    var body = JSON.stringify({
      boids: bolds,
      company_share_id: selectedId,
      company_name: selectedName,
      save: document.getElementById('opt-save').checked ? '1' : '0',
    });

    fetch('/api/ipo-allotment.php', {method:'POST',headers:{'Content-Type':'application/json'},body:body})
      .then(function(r){ return r.json(); })
      .then(function(d){
        banner.classList.add('hidden');
        checkBtn.disabled = false;
        if (!d.ok){ alert(d.error || 'Error'); return; }

        stTotal.textContent   = d.checked;
        stAll.textContent     = d.allotted;
        stNot.textContent     = d.not_allotted;
        resultsS.style.display = '';

        rows.innerHTML = (d.results || []).map(function(r){
          var cls = r.allotted
            ? 'bg-emerald-50 border-emerald-200'
            : 'bg-rose-50 border-rose-100';
          var icon = r.allotted ? '✅' : '❌';
          var sharesHtml = r.allotted && r.shares
            ? '<span class="text-[12px] font-black text-emerald-700 mt-0.5">'+esc(r.shares)+' किटा</span>' : '';
          return '<div class="flex items-center gap-3 rounded-xl border px-3 py-2.5 '+esc(cls)+'">'
            +'<span class="text-xl flex-shrink-0">'+icon+'</span>'
            +'<div class="flex-1 min-w-0">'
              +'<p class="font-mono text-[13px] font-bold text-slate-900">'+esc(r.boid_mask)+'</p>'
              +'<p class="text-[11px] text-slate-500">'+esc(r.message)+(r.posted_date?' · '+esc(r.posted_date):'')+'</p>'
            +'</div>'
            + sharesHtml
          +'</div>';
        }).join('');
      })
      .catch(function(err){
        banner.classList.add('hidden');
        checkBtn.disabled = false;
        alert('Check गर्न सकिएन: '+err.message);
      });
  });

  updateCheckBtn();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
