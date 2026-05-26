<?php
/** utilities.php v14 — Market + LIVE Weather/Utilities snapshot
 *  v14 fix: पहिले यो file ले /cache/market_cache.json (जुन कुनै API ले लेख्दैनथ्यो) पढ्थ्यो
 *  → सधैं hardcoded fallback (petrol=175, gold=148500) देखाउँथ्यो जुन index.php सँग
 *  मेल खाँदैनथ्यो। अब includes/market.php (single source of truth) बाट पढ्ने।
 */
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/market.php';
$market = getMarket(true); // refresh stale cache once
$gold   = $market['gold'];
$fuel   = $market['fuel'];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <h1 class="text-[20px] font-bold text-slate-900 mb-3"><?= $tH('बजार र उपयोगी','Market & Utilities') ?></h1>

    <!-- LIVE Weather -->
    <div id="weather-card" class="rounded-2xl p-4 text-white shadow-app bg-gradient-to-br from-sky-500 to-blue-700 flex items-center gap-4">
      <i data-lucide="cloud-sun" class="w-14 h-14" id="wx-icon"></i>
      <div class="flex-1">
        <div class="text-[11px] opacity-80 flex items-center gap-2">
          <?= $tH('मौसम','Weather') ?> • <span id="wx-city">काठमाडौं</span>
          <span class="inline-flex items-center gap-1 bg-white/20 px-1.5 py-0.5 rounded-full text-[9px] font-bold"><span class="w-1 h-1 rounded-full bg-emerald-300 animate-pulse"></span>LIVE</span>
        </div>
        <div class="text-[28px] font-extrabold leading-none" id="wx-temp">—°C</div>
        <div class="text-[12px] opacity-90" id="wx-cond">लोड हुँदै…</div>
      </div>
      <div class="text-right text-[11px] opacity-90">
        <div>H <span id="wx-hi">—</span>°</div>
        <div>L <span id="wx-lo">—</span>°</div>
      </div>
    </div>
    <div class="flex gap-1.5 mt-2 overflow-x-auto pb-1" id="wx-cities" style="scrollbar-width:none">
      <?php foreach(['काठमाडौं','पोखरा','विराटनगर','नेपालगन्ज','धनगढी','चितवन','धरान','बुटवल'] as $c): ?>
        <button data-c="<?= $c ?>" class="wx-chip text-[11px] px-2.5 py-1 rounded-full bg-white text-slate-700 border border-slate-200 whitespace-nowrap"><?= $c ?></button>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Fuel + Gold -->
  <section class="px-4 mt-4 grid grid-cols-2 gap-3">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
        <i data-lucide="fuel" class="w-3.5 h-3.5"></i> <?= $tH('इन्धन','Fuel') ?>
        <a href="<?= htmlspecialchars($fuel['source_url'], ENT_QUOTES) ?>" target="_blank" rel="noopener" class="ml-auto text-[9px] font-bold text-teal-700 bg-teal-50 px-1.5 py-0.5 rounded">NOC</a>
      </div>
      <div class="text-[12px] flex justify-between"><span>पेट्रोल</span><b><?= $fuel['petrol']!==null ? 'रु '.number_format($fuel['petrol'],2) : '<span class="text-slate-400">—</span>' ?></b></div>
      <div class="text-[12px] flex justify-between"><span>डिजेल</span><b><?= $fuel['diesel']!==null ? 'रु '.number_format($fuel['diesel'],2) : '<span class="text-slate-400">—</span>' ?></b></div>
      <div class="text-[12px] flex justify-between"><span>LPG</span><b><?= $fuel['lpg']!==null ? 'रु '.number_format($fuel['lpg']) : '<span class="text-slate-400">—</span>' ?></b></div>
      <?= $fuel['available'] ? nh_marketSourceBadge($fuel, 'स्रोत') : nh_unavailableBlock($fuel, 'इन्धन') ?>
    </div>
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
        <i data-lucide="coins" class="w-3.5 h-3.5"></i> <?= $tH('सुन/चाँदी','Gold/Silver') ?>
        <a href="<?= htmlspecialchars($gold['source_url'], ENT_QUOTES) ?>" target="_blank" rel="noopener" class="ml-auto text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">FeNeGoSiDA</a>
      </div>
      <div class="text-[12px] flex justify-between"><span>फाइन (तोला)</span><b><?= $gold['fine']!==null ? 'रु '.number_format($gold['fine']) : '<span class="text-slate-400">—</span>' ?></b></div>
      <div class="text-[12px] flex justify-between"><span>तेजाबी</span><b><?= $gold['tejabi']!==null ? 'रु '.number_format($gold['tejabi']) : '<span class="text-slate-400">—</span>' ?></b></div>
      <div class="text-[12px] flex justify-between"><span>चाँदी</span><b><?= $gold['silver']!==null ? 'रु '.number_format($gold['silver']) : '<span class="text-slate-400">—</span>' ?></b></div>
      <?= $gold['available'] ? nh_marketSourceBadge($gold, 'स्रोत') : nh_unavailableBlock($gold, 'सुन/चाँदी') ?>
    </div>
  </section>

  <!-- LIVE Load Shedding -->
  <section class="px-4 mt-4">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
        <i data-lucide="zap-off" class="w-4 h-4 text-amber-500"></i> <?= $tH('लोडसेडिङ तालिका','Load Shedding') ?>
        <span class="ml-auto text-[10px] text-slate-500" id="ls-status">लोड हुँदै…</span>
      </div>
      <div id="ls-list" class="space-y-1.5 text-[12px]"></div>
    </div>
  </section>

  <!-- LIVE Bank Interest Rates -->
  <section class="px-4 mt-4">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
        <i data-lucide="landmark" class="w-4 h-4 text-blue-600"></i> <?= $tH('बैंक ब्याजदर','Bank Interest Rates') ?>
      </div>
      <div id="bank-list" class="space-y-1 text-[12px]"><div class="text-slate-400">लोड हुँदै…</div></div>
    </div>
  </section>

  <!-- LIVE Remittance -->
  <section class="px-4 mt-4">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
        <i data-lucide="send" class="w-4 h-4 text-emerald-600"></i> <?= $tH('रेमिट्यान्स दर','Remittance Rates') ?>
      </div>
      <div id="rem-list" class="space-y-1 text-[12px]"><div class="text-slate-400">लोड हुँदै…</div></div>
    </div>
  </section>

  <!-- Water Supply -->
  <section class="px-4 mt-4">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
        <i data-lucide="droplets" class="w-4 h-4 text-sky-600"></i> <?= $tH('पानी आपूर्ति तालिका','Water Supply') ?>
        <a href="https://kathmanduwater.org/" target="_blank" rel="noopener" class="ml-auto text-[9px] font-bold text-sky-700 bg-sky-50 px-1.5 py-0.5 rounded">KUKL</a>
      </div>
      <div id="water-list" class="space-y-1 text-[12px]"><div class="text-slate-400">लोड हुँदै…</div></div>
    </div>
  </section>

  <!-- LIVE Lok Sewa -->
  <section class="px-4 mt-4">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
        <i data-lucide="briefcase" class="w-4 h-4 text-purple-600"></i> <?= $tH('लोक सेवा सूचना','Lok Sewa Notices') ?>
        <a href="https://psc.gov.np/" target="_blank" rel="noopener" class="ml-auto text-[9px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded">PSC</a>
      </div>
      <div id="lok-list" class="space-y-1.5 text-[12px]"><div class="text-slate-400">लोड हुँदै…</div></div>
    </div>
  </section>

  <!-- Data Sources Status -->
  <section class="px-4 mt-4 pb-6">
    <div class="bg-slate-50 rounded-2xl p-3 border border-slate-200">
      <div class="text-[12px] font-bold text-slate-700 mb-3 flex items-center gap-1.5">
        <i data-lucide="database" class="w-4 h-4 text-slate-500"></i> <?= $tH('डेटा स्रोतहरू','Data Sources') ?>
      </div>
      <div class="grid grid-cols-2 gap-2 text-[10px]">
        <div class="bg-white rounded-lg p-2 border border-slate-100">
          <div class="flex items-center gap-1 text-amber-600 font-semibold">
            <i data-lucide="coins" class="w-3 h-3"></i> <?= $tH('सुन/चाँदी','Gold') ?>
          </div>
          <div class="text-slate-500 mt-0.5">FENEGOSIDA · <?= $gold['updatedAt'] ? date('H:i', strtotime($gold['updatedAt'])) : '—' ?></div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-100">
          <div class="flex items-center gap-1 text-teal-600 font-semibold">
            <i data-lucide="fuel" class="w-3 h-3"></i> <?= $tH('इन्धन','Fuel') ?>
          </div>
          <div class="text-slate-500 mt-0.5">NOC Nepal · <?= $fuel['updatedAt'] ? date('H:i', strtotime($fuel['updatedAt'])) : '—' ?></div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-100">
          <div class="flex items-center gap-1 text-blue-600 font-semibold">
            <i data-lucide="landmark" class="w-3 h-3"></i> <?= $tH('ब्याजदर','Bank Rates') ?>
          </div>
          <div class="text-slate-500 mt-0.5">NRB Nepal · Real-time</div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-100">
          <div class="flex items-center gap-1 text-sky-600 font-semibold">
            <i data-lucide="cloud-sun" class="w-3 h-3"></i> <?= $tH('मौसम','Weather') ?>
          </div>
          <div class="text-slate-500 mt-0.5">Open-Meteo · Live</div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-100">
          <div class="flex items-center gap-1 text-amber-500 font-semibold">
            <i data-lucide="zap-off" class="w-3 h-3"></i> <?= $tH('लोडसेडिङ','Load Shedding') ?>
          </div>
          <div class="text-slate-500 mt-0.5">NEA Nepal · Live</div>
        </div>
        <div class="bg-white rounded-lg p-2 border border-slate-100">
          <div class="flex items-center gap-1 text-purple-600 font-semibold">
            <i data-lucide="briefcase" class="w-3 h-3"></i> <?= $tH('लोकसेवा','Loksewa') ?>
          </div>
          <div class="text-slate-500 mt-0.5">PSC Nepal · Live</div>
        </div>
      </div>
      <div class="text-[9px] text-slate-400 mt-2 text-center">
        <?= $tH('सबै डेटा आधिकारिक स्रोतहरूबाट स्वचालित रूपमा sync हुन्छ','All data auto-syncs from official sources') ?>
      </div>
    </div>
  </section>
</main>

<script>
(function(){
  function favCity(v){ try { if(v!==undefined){localStorage.setItem('nsh_fav_city',v);} return localStorage.getItem('nsh_fav_city')||'काठमाडौं'; } catch(_) { return 'काठमाडौं'; } }
  function setText(id,v){ var e=document.getElementById(id); if(e) e.textContent = v; }
  var current = favCity();

  function updateChips(city){
    document.querySelectorAll('.wx-chip').forEach(function(b){
      var active = b.dataset.c === city;
      b.className = 'wx-chip text-[11px] px-2.5 py-1 rounded-full whitespace-nowrap transition-colors '
        + (active
          ? 'bg-sky-600 text-white font-semibold border border-sky-600'
          : 'bg-white text-slate-700 border border-slate-200');
    });
  }

  function loadWeather(city){
    current = city;
    favCity(city);
    setText('wx-city', city);
    setText('wx-temp', '—°C');
    setText('wx-cond', 'लोड हुँदैछ…');
    setText('wx-hi', '—'); setText('wx-lo', '—');
    updateChips(city);

    fetch('/api/weather-alerts.php?type=weather&city=' + encodeURIComponent(city))
      .then(function(r){ if(!r.ok) throw new Error(r.status); return r.json(); })
      .then(function(d){
        // API returns: temp_c, desc_ne, forecast[0].max_c / min_c
        if (d.available === false || d.error) {
          setText('wx-cond', 'डेटा उपलब्ध छैन');
          return;
        }
        var temp = d.temp_c ?? d.temperature ?? d.temp ?? null;
        setText('wx-temp', (temp !== null ? temp : '—') + '°C');
        setText('wx-cond', (d.emoji ? d.emoji + ' ' : '') + (d.desc_ne || d.condition || d.desc_en || '—'));
        // High/Low from today's forecast
        var fc = (d.forecast && d.forecast[0]) || {};
        setText('wx-hi', fc.max_c ?? d.high ?? d.hi ?? '—');
        setText('wx-lo', fc.min_c ?? d.low  ?? d.lo ?? '—');
        // Update weather card gradient by condition
        var card = document.getElementById('weather-card');
        if (card) {
          var code = d.code || 0;
          var grad = code === 0 ? 'from-sky-400 to-blue-600'
                   : code <= 3   ? 'from-sky-500 to-blue-700'
                   : code <= 48  ? 'from-slate-500 to-slate-700'
                   : code <= 67  ? 'from-blue-600 to-indigo-800'
                   : code <= 77  ? 'from-slate-400 to-blue-700'
                   : 'from-indigo-500 to-purple-700';
          card.className = card.className.replace(/from-\S+ to-\S+/, grad);
        }
      })
      .catch(function(){ setText('wx-cond','डेटा लोड हुन सकेन'); });
  }

  document.querySelectorAll('.wx-chip').forEach(function(b){
    b.addEventListener('click', function(){ loadWeather(this.dataset.c); });
  });
  loadWeather(current);

  // Load Shedding (live → admin fallback)
  fetch('/api/utilities.php?type=loadshedding').then(r=>r.json()).then(d=>{
    var arr = (d && (d.schedule || d.loadshedding || d.data)) || [];
    var el = document.getElementById('ls-list');
    function paint(arr, src){
      el.innerHTML = arr.slice(0,8).map(function(g){
        var z = g.group || g.zone || g.z || g.title || '';
        var t = g.time || g.t || g.duration || g.detail || '';
        return '<div class="flex justify-between border-b border-slate-100 pb-1"><span class="font-semibold text-slate-700">'+z+'</span><span class="text-slate-600">'+t+'</span></div>';
      }).join('') + '<div class="text-[10px] text-slate-400 pt-1">स्रोत: '+src+'</div>';
      setText('ls-status', src);
    }
    if (arr.length) { paint(arr, 'NEA'); return; }
    fetch('/api/content-overrides.php?key=loadshedding').then(r=>r.json()).then(o=>{
      var sec = o && o.data;
      if (sec && sec.enabled && (sec.items||[]).length) paint(sec.items, sec.source||'Admin');
      else { el.innerHTML = '<div class="text-emerald-600 font-semibold">✓ हाल लोडसेडिङ छैन — NEA</div>'; setText('ls-status','NEA'); }
    });
  }).catch(()=>{ document.getElementById('ls-list').innerHTML='<div class="text-slate-400">डेटा उपलब्ध छैन</div>'; });

  // Bank Rates
  fetch('/api/utilities.php?type=bank_rates').then(r=>r.json()).then(d=>{
    var arr = (d && (d.banks || d.rates || d.data)) || [];
    var el = document.getElementById('bank-list');
    if (!arr.length) { el.innerHTML = '<div class="text-slate-400">डेटा उपलब्ध छैन</div>'; return; }
    el.innerHTML = arr.slice(0,8).map(function(b){
      var n = b.bank || b.name || '';
      var s = b.savings || b.saving || '';
      var f = b.fd || b.fixed || '';
      return '<div class="flex justify-between"><span class="text-slate-700">'+n+'</span><span class="text-slate-500">बचत <b>'+s+'%</b> · FD <b>'+f+'%</b></span></div>';
    }).join('');
  }).catch(()=>{});

  // Remittance
  fetch('/api/utilities.php?type=remittance').then(r=>r.json()).then(d=>{
    var arr = (d && (d.providers || d.remittance || d.data)) || [];
    var el = document.getElementById('rem-list');
    if (!arr.length) { el.innerHTML = '<div class="text-slate-400">डेटा उपलब्ध छैन</div>'; return; }
    el.innerHTML = arr.slice(0,8).map(function(p){
      var n = p.provider || p.name || '';
      var c = p.currency || 'USD';
      var r = p.rate || p.npr || '';
      return '<div class="flex justify-between"><span class="text-slate-700">'+n+'</span><span><b>१ '+c+' = रु '+r+'</b></span></div>';
    }).join('');
  }).catch(()=>{});

  // Admin overrides (used as fallback when live API empty/fails)
  var ovP = fetch('/api/content-overrides.php').then(r=>r.json()).catch(()=>null);

  // Lok Sewa (live → admin pin fallback)
  fetch('/api/utilities.php?type=loksewa').then(r=>r.json()).then(d=>{
    var arr = (d && (d.notices || d.loksewa || d.data)) || [];
    var el = document.getElementById('lok-list');
    function render(arr, srcLabel, srcUrl){
      if (!arr.length) { el.innerHTML = '<div class="text-slate-400">कुनै सूचना छैन</div>'; return; }
      el.innerHTML = arr.slice(0,8).map(function(n){
        var t = n.title || n.notice || '';
        var d2 = n.date || n.detail || n.deadline || '';
        var u = n.url || n.link || srcUrl || 'https://psc.gov.np/';
        return '<a href="'+u+'" target="_blank" rel="noopener" class="block border-b border-slate-100 pb-1.5"><div class="font-semibold text-slate-800 line-clamp-2">'+t+'</div>'+(d2?'<div class="text-[10px] text-slate-500 mt-0.5">'+d2+'</div>':'')+'</a>';
      }).join('') + '<div class="text-[10px] text-slate-400 pt-1">स्रोत: '+srcLabel+'</div>';
    }
    if (arr.length) { render(arr,'PSC Nepal','https://psc.gov.np/'); return; }
    ovP.then(function(o){
      var sec = o && o.overrides && o.overrides.loksewa;
      if (sec && sec.enabled && sec.items && sec.items.length) render(sec.items, sec.source||'Admin', sec.source_url);
      else render([], 'PSC Nepal');
    });
  }).catch(()=>{});

  // Water supply (admin only — no public API)
  ovP.then(function(o){
    var el = document.getElementById('water-list');
    var sec = o && o.overrides && o.overrides.water;
    if (!sec || !sec.enabled || !(sec.items||[]).length) { el.innerHTML = '<div class="text-slate-400">तालिका उपलब्ध छैन</div>'; return; }
    el.innerHTML = sec.items.slice(0,8).map(function(w){
      return '<div class="flex justify-between border-b border-slate-100 pb-1"><span class="font-semibold text-slate-700">'+(w.title||'')+'</span><span class="text-slate-600">'+(w.detail||'')+'</span></div>';
    }).join('') + '<div class="text-[10px] text-slate-400 pt-1">स्रोत: '+(sec.source||'KUKL')+'</div>';
  });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
