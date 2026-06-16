<?php
/**
 * ipo-tracker.php v12 — App-style Market: NEPSE + IPO + Forex + Gold/Fuel
 * v12 fix: पहिले market_cache.json (कुनै API ले नलेख्ने फाइल) पढ्थ्यो
 *          → hardcoded fallback (petrol=175, gold=148500) सधैं देखाउँथ्यो।
 *          अब includes/market.php (single source of truth) बाट पढ्ने —
 *          index.php र utilities.php जस्तै।
 */
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/market.php';

$market = getMarket();
$_nepse = $market['nepse'];
$_gold  = $market['gold'];
$_fuel  = $market['fuel'];
$_forex = $market['forex'];

// ── Normalize for display ──────────────────────────────────────────────────
$nepse = [
    'index'    => (float)($_nepse['index']   ?? 0),
    'change'   => (float)($_nepse['change']  ?? 0),
    'pct'      => (float)($_nepse['percent'] ?? 0),
    'turnover' => $_nepse['turnover'] ? 'रू ' . number_format((float)$_nepse['turnover']/1e7, 1) . ' करोड' : '—',
    'source'   => $_nepse['source']    ?? 'NEPSE',
    'updatedAt'=> $_nepse['updatedAt'] ?? '',
];

$gold = [
    'fine'    => $_gold['fine']    ?? null,
    'tejabi'  => $_gold['tejabi']  ?? null,
    'silver'  => $_gold['silver']  ?? null,
    'available'=> !empty($_gold['available']),
    'source'  => $_gold['source']  ?? 'FENEGOSIDA',
    'source_url'=> $_gold['source_url'] ?? 'https://www.fenegosida.org/',
    'updatedAt'=> $_gold['updatedAt'] ?? '',
];

$fuel = [
    'petrol'   => $_fuel['petrol']   ?? null,
    'diesel'   => $_fuel['diesel']   ?? null,
    'kerosene' => $_fuel['kerosene'] ?? null,
    'lpg'      => $_fuel['lpg']      ?? null,
    'available'=> !empty($_fuel['available']),
    'source'   => $_fuel['source']   ?? 'NOC Nepal',
    'source_url'=> $_fuel['source_url'] ?? 'https://noc.org.np/priceupdate',
    'updatedAt'=> $_fuel['updatedAt'] ?? '',
];

// Forex: build display array from rates list
$flagMap = ['USD'=>'🇺🇸','EUR'=>'🇪🇺','GBP'=>'🇬🇧','AUD'=>'🇦🇺','CAD'=>'🇨🇦','CHF'=>'🇨🇭',
            'SGD'=>'🇸🇬','QAR'=>'🇶🇦','SAR'=>'🇸🇦','AED'=>'🇦🇪','MYR'=>'🇲🇾','JPY'=>'🇯🇵',
            'CNY'=>'🇨🇳','INR'=>'🇮🇳','KRW'=>'🇰🇷'];
$forexDisplay = [];
foreach (($_forex['rates'] ?? []) as $r) {
    $code = $r['code'] ?? '';
    if (!$code) continue;
    $unit = max(1,(int)($r['unit'] ?? 1));
    $forexDisplay[] = [
        'c'    => $code,
        'b'    => round((float)($r['buy']  ?? 0) / $unit, 2),
        's'    => round((float)($r['sell'] ?? 0) / $unit, 2),
        'flag' => $flagMap[$code] ?? '🌏',
        'unit' => $unit,
    ];
}
$forexAvail = !empty($forexDisplay);
$forexUpdated = $_forex['updatedAt'] ?? '';

// IPO: still loaded from live API via JS; server-side fallback hidden
$ipos = [];

$up = $nepse['change'] >= 0;
?>
<main class="app-main">
  <!-- NEPSE Hero -->
  <section class="px-4 pt-3">
    <div class="rounded-2xl p-5 text-white shadow-app bg-gradient-to-br <?= $up?'from-emerald-600 to-teal-700':'from-rose-600 to-red-700' ?>">
      <div class="flex items-center justify-between">
        <div class="text-[12px] opacity-80">NEPSE Index</div>
        <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full flex items-center gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>Live
        </span>
      </div>
      <?php if ($nepse['index'] > 0): ?>
        <div class="text-[32px] font-extrabold leading-tight mt-1"><?= number_format($nepse['index'],2) ?></div>
        <div class="flex items-center gap-2 text-[13px]">
          <i data-lucide="<?= $up?'trending-up':'trending-down' ?>" class="w-4 h-4"></i>
          <?= ($up?'+':'') . number_format($nepse['change'],2) ?> (<?= ($up?'+':'') . number_format($nepse['pct'],2) ?>%)
        </div>
        <div class="mt-3 text-[11px] opacity-80"><?= $tH('कारोबार','Turnover') ?>: <?= htmlspecialchars($nepse['turnover']) ?></div>
      <?php else: ?>
        <div class="text-[18px] font-bold opacity-80 mt-2"><?= $tH('NEPSE डाटा लोड हुँदैछ…','Loading NEPSE data…') ?></div>
      <?php endif; ?>
      <div class="mt-2 text-[10px] opacity-60"><?= $tH('स्रोत','Source') ?>: <?= htmlspecialchars($nepse['source']) ?><?= $nepse['updatedAt'] ? ' · '.htmlspecialchars($nepse['updatedAt']) : '' ?></div>
    </div>
  </section>

  <!-- Gainers / Losers (live from API) -->
  <section class="px-4 mt-4 grid grid-cols-2 gap-3" id="gl-section">
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-emerald-700 mb-2 flex items-center gap-1"><i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i><?= $tH('बढेका','Gainers') ?></div>
      <div id="gainers-list"><div class="text-[11px] text-slate-400"><?= $tH('लोड हुँदैछ…','Loading…') ?></div></div>
    </div>
    <div class="bg-white rounded-2xl p-3 shadow-app">
      <div class="text-[12px] font-bold text-rose-700 mb-2 flex items-center gap-1"><i data-lucide="arrow-down-right" class="w-3.5 h-3.5"></i><?= $tH('घटेका','Losers') ?></div>
      <div id="losers-list"><div class="text-[11px] text-slate-400"><?= $tH('लोड हुँदैछ…','Loading…') ?></div></div>
    </div>
  </section>

  <!-- IPO (live from API) -->
  <section class="px-4 mt-4">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-[15px] font-bold text-slate-900"><?= $tH('IPO ट्र्याकर','IPO Tracker') ?></h2>
      <span id="ipo-source" class="text-[10px] text-slate-400"></span>
    </div>
    <div id="ipo-list" class="space-y-2">
      <div class="text-[12px] text-slate-400 text-center py-3"><?= $tH('IPO डाटा लोड हुँदैछ…','Loading IPO data…') ?></div>
    </div>
  </section>

  <!-- Gold / Silver — LIVE from includes/market.php -->
  <section class="px-4 mt-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-1"><?= $tH('सुन/चाँदी','Gold & Silver') ?></h2>
    <div class="text-[10px] text-slate-400 mb-2">
      <?= $tH('स्रोत','Source') ?>: <a href="<?= htmlspecialchars($gold['source_url']) ?>" target="_blank" rel="noopener" class="text-amber-700 font-semibold"><?= htmlspecialchars($gold['source']) ?></a>
      <?= $gold['updatedAt'] ? ' · '.htmlspecialchars($gold['updatedAt']) : '' ?>
    </div>
    <div class="grid grid-cols-3 gap-2">
      <?php foreach([['fine','फाइन सुन','amber'],['tejabi','तेजाबी','yellow'],['silver','चाँदी','slate']] as $g): ?>
        <div class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-9 h-9 mx-auto rounded-full bg-<?= $g[2] ?>-100 text-<?= $g[2] ?>-700 flex items-center justify-center mb-1">
            <i data-lucide="<?= $g[0]==='silver'?'circle':'gem' ?>" class="w-4 h-4"></i>
          </div>
          <div class="text-[10px] text-slate-500"><?= $g[1] ?></div>
          <?php if ($gold[$g[0]] !== null): ?>
            <div class="text-[13px] font-bold text-slate-900">रू <?= number_format((float)$gold[$g[0]]) ?></div>
            <div class="text-[9px] text-slate-400">/tola</div>
          <?php else: ?>
            <div class="text-[12px] text-slate-400">—</div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Fuel — LIVE from includes/market.php (same source as index.php + utilities.php) -->
  <section class="px-4 mt-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-1"><?= $tH('इन्धन मूल्य','Fuel Price') ?></h2>
    <div class="text-[10px] text-slate-400 mb-2">
      <?= $tH('स्रोत','Source') ?>: <a href="<?= htmlspecialchars($fuel['source_url']) ?>" target="_blank" rel="noopener" class="text-orange-700 font-semibold"><?= htmlspecialchars($fuel['source']) ?></a>
      <?php if (!$fuel['available']): ?>
        · <span class="text-rose-500"><?= $tH('Live डाटा उपलब्ध छैन','Live data unavailable') ?></span>
      <?php endif; ?>
      <?= $fuel['updatedAt'] ? ' · '.htmlspecialchars($fuel['updatedAt']) : '' ?>
    </div>
    <div class="grid grid-cols-4 gap-2">
      <?php
      $fuelItems = [
        ['petrol',  'पेट्रोल',   'red',   'fuel'],
        ['diesel',  'डिजल',      'sky',   'truck'],
        ['kerosene','मट्टितेल',  'amber', 'flame'],
        ['lpg',     'LPG',       'orange','flame-kindling'],
      ];
      foreach($fuelItems as $f): ?>
        <div class="bg-white rounded-2xl p-2.5 shadow-app text-center">
          <div class="w-8 h-8 mx-auto rounded-full bg-<?= $f[2] ?>-100 text-<?= $f[2] ?>-700 flex items-center justify-center mb-1">
            <i data-lucide="<?= $f[3] ?>" class="w-4 h-4"></i>
          </div>
          <div class="text-[10px] text-slate-500"><?= $f[1] ?></div>
          <?php if ($fuel[$f[0]] !== null): ?>
            <div class="text-[12px] font-bold text-slate-900">रू <?= number_format((float)$fuel[$f[0]]) ?></div>
          <?php else: ?>
            <div class="text-[12px] text-slate-400">—</div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Forex — LIVE from NRB via includes/market.php -->
  <section class="px-4 mt-4 pb-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-1"><?= $tH('विदेशी मुद्रा','Forex Rates') ?></h2>
    <div class="text-[10px] text-slate-400 mb-2">
      <?= $tH('स्रोत','Source') ?>: <a href="https://www.nrb.org.np/forex/" target="_blank" rel="noopener" class="text-blue-700 font-semibold">Nepal Rastra Bank (NRB)</a>
      <?= $forexUpdated ? ' · '.htmlspecialchars($forexUpdated) : '' ?>
    </div>
    <?php if ($forexAvail): ?>
    <div class="bg-white rounded-2xl shadow-app overflow-hidden">
      <div class="grid grid-cols-12 text-[11px] font-bold text-slate-500 px-3 py-2 bg-slate-50">
        <div class="col-span-5"><?= $tH('मुद्रा','Currency') ?></div>
        <div class="col-span-3 text-right"><?= $tH('खरिद','Buy') ?></div>
        <div class="col-span-4 text-right"><?= $tH('बिक्री','Sell') ?></div>
      </div>
      <?php foreach($forexDisplay as $fx): ?>
        <div class="grid grid-cols-12 items-center px-3 py-2.5 border-t border-slate-50 text-[13px]">
          <div class="col-span-5 flex items-center gap-2"><span class="text-lg"><?= $fx['flag'] ?></span><span class="font-semibold"><?= htmlspecialchars($fx['c']) ?></span></div>
          <div class="col-span-3 text-right text-slate-700"><?= number_format($fx['b'],2) ?></div>
          <div class="col-span-4 text-right font-semibold text-slate-900"><?= number_format($fx['s'],2) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-2xl shadow-app p-4 text-[13px] text-slate-500 text-center">
      <?= $tH('NRB Forex डाटा अहिले उपलब्ध छैन। ','NRB Forex data unavailable. ') ?>
      <a href="https://www.nrb.org.np/forex/" target="_blank" rel="noopener" class="text-blue-700 font-semibold">nrb.org.np</a>
    </div>
    <?php endif; ?>
  </section>
</main>

<script>
(function(){
  // Load Gainers/Losers + IPO from NEPSE live API
  fetch('/api/market-data.php?type=nepse')
    .then(r=>r.json()).then(function(d){
      var g = d.gainers || d.topGainers || [];
      var l = d.losers  || d.topLosers  || [];
      var gl = document.getElementById('gainers-list');
      var ll = document.getElementById('losers-list');
      if (gl && g.length) {
        gl.innerHTML = g.slice(0,5).map(function(x){
          return '<div class="flex justify-between items-center py-1.5 border-b border-slate-50 last:border-0">'
            +'<div class="text-[12px] font-semibold text-slate-900">'+(x.symbol||x.s||'')+'</div>'
            +'<div class="text-right">'
              +'<div class="text-[12px] font-bold text-slate-900">'+(x.lastTradedPrice||x.p||'')+'</div>'
              +'<div class="text-[10px] text-emerald-600">+'+(x.percentageChange||x.pct||0)+'%</div>'
            +'</div></div>';
        }).join('');
      } else if(gl) { gl.innerHTML='<div class="text-[11px] text-slate-400">डाटा उपलब्ध छैन</div>'; }
      if (ll && l.length) {
        ll.innerHTML = l.slice(0,5).map(function(x){
          return '<div class="flex justify-between items-center py-1.5 border-b border-slate-50 last:border-0">'
            +'<div class="text-[12px] font-semibold text-slate-900">'+(x.symbol||x.s||'')+'</div>'
            +'<div class="text-right">'
              +'<div class="text-[12px] font-bold text-slate-900">'+(x.lastTradedPrice||x.p||'')+'</div>'
              +'<div class="text-[10px] text-rose-600">'+(x.percentageChange||x.pct||0)+'%</div>'
            +'</div></div>';
        }).join('');
      } else if(ll) { ll.innerHTML='<div class="text-[11px] text-slate-400">डाटा उपलब्ध छैन</div>'; }
    }).catch(function(){
      var gl=document.getElementById('gainers-list');
      var ll=document.getElementById('losers-list');
      if(gl) gl.innerHTML='<div class="text-[11px] text-slate-400">डाटा उपलब्ध छैन</div>';
      if(ll) ll.innerHTML='<div class="text-[11px] text-slate-400">डाटा उपलब्ध छैन</div>';
    });

  // Load IPO / FPO / Right share data (full info)
  fetch('/api/ipo-data.php')
    .then(r=>r.json()).then(function(d){
      var el = document.getElementById('ipo-list');
      if (!el) return;
      var active   = Array.isArray(d.active)   ? d.active   : [];
      var upcoming = Array.isArray(d.upcoming) ? d.upcoming : [];
      var closed   = Array.isArray(d.closed)   ? d.closed   : [];
      var items = active.concat(upcoming);
      if (!items.length && closed.length) items = closed.slice(0,10);
      if (!items.length) {
        var note = d && d.note ? d.note : 'IPO डाटा उपलब्ध छैन';
        el.innerHTML = '<div class="text-[12px] text-slate-500 text-center py-4 px-2">'+note
          +'<div class="mt-2"><a class="text-teal-600 underline" href="https://www.sharesansar.com/ipo" target="_blank" rel="noopener">ShareSansar मा हेर्नुस् →</a></div></div>';
        return;
      }
      function esc(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
      function badge(item){
        var today = new Date().toISOString().slice(0,10);
        if (item.openDate && item.closeDate && item.openDate <= today && item.closeDate >= today) return ['खुला','bg-emerald-100 text-emerald-700'];
        if (item.openDate && item.openDate > today)   return ['आगामी','bg-amber-100 text-amber-700'];
        if (item.closeDate && item.closeDate < today) return ['बन्द','bg-slate-100 text-slate-600'];
        return ['—','bg-slate-100 text-slate-600'];
      }
      el.innerHTML = items.slice(0,40).map(function(ip){
        var b = badge(ip);
        var apply = ip.eligibleUrl || 'https://meroshare.cdsc.com.np/#/login';
        return '<div class="bg-white rounded-2xl p-3.5 shadow-app">'
          +'<div class="flex items-start justify-between gap-3">'
            +'<div class="flex-1 min-w-0">'
              +'<div class="text-[14px] font-bold text-slate-900 truncate">'+esc(ip.name||'—')+'</div>'
              +'<div class="text-[11px] text-slate-500 mt-0.5">'+esc(ip.symbol||'—')
                + (ip.sector ? ' · '+esc(ip.sector) : '')
                + (ip.price  ? ' · '+esc(ip.price)  : '')
                + (ip.ratio  ? ' · Ratio '+esc(ip.ratio) : '')
              +'</div>'
            +'</div>'
            +'<span class="text-[10px] font-bold px-2 py-1 rounded-full '+b[1]+'">'+b[0]+'</span>'
          +'</div>'
          +'<div class="grid grid-cols-2 gap-x-3 gap-y-1 mt-2 text-[11px] text-slate-600">'
            +'<span>खुल्ने: <b class="text-slate-800">'+esc(ip.openDate||'—')+'</b></span>'
            +'<span>बन्द: <b class="text-slate-800">'+esc(ip.closeDate||'—')+'</b></span>'
            + (ip.shares  ? '<span>एकाइ: <b class="text-slate-800">'+esc(ip.shares)+'</b></span>' : '')
            + (ip.finalDate ? '<span>Final: <b class="text-slate-800">'+esc(ip.finalDate)+'</b></span>' : '')
          +'</div>'
          + (ip.manager ? '<div class="mt-1.5 text-[10.5px] text-slate-500">Manager: '+esc(ip.manager)+'</div>' : '')
          +'<div class="flex gap-2 mt-2.5">'
            +'<a href="'+esc(apply)+'" target="_blank" rel="noopener" class="flex-1 text-center text-[11.5px] font-bold bg-teal-600 text-white px-3 py-1.5 rounded-lg">Meroshare मा Apply</a>'
            + (ip.announceUrl ? '<a href="'+esc(ip.announceUrl)+'" target="_blank" rel="noopener" class="text-[11.5px] font-semibold border border-slate-200 text-slate-700 px-3 py-1.5 rounded-lg">विवरण</a>' : '')
          +'</div>'
        +'</div>';
      }).join('');
      // Source attribution
      var src = document.getElementById('ipo-source');
      if (src && d.updated_at) src.textContent = 'स्रोत: ShareSansar · अद्यावधिक ' + (d.updated_at_np || d.updated_at);
    }).catch(function(){
      var el=document.getElementById('ipo-list');
      if(el) el.innerHTML='<div class="text-[12px] text-slate-400 text-center py-3">IPO डाटा लोड हुन सकेन — पछि फेरि प्रयास गर्नुस्।</div>';
    });

  if(window.lucide&&lucide.createIcons) lucide.createIcons();
})();
</script>

<!-- ══ IPO HOW-TO GUIDE ════════════════════════════════════════════════════ -->
<?php
$tH2 = function($ne, $en) use($tH) { return $tH($ne, $en); };
?>
<section class="px-4 mt-3 mb-2">
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="book-open-check" class="w-4 h-4 text-teal-600"></i>
    IPO कसरी Apply गर्ने? (Step-by-Step)
  </h2>

  <!-- Steps -->
  <div class="bg-white rounded-2xl shadow-app p-3 mb-3">
    <ol class="space-y-2.5">
      <?php foreach([
        ['DEMAT खाता खोल्नुस्','Broker मार्फत वा CDSC — एकपटक खोलिसकेपछि सधैं प्रयोग हुन्छ'],
        ['Meroshare मा दर्ता गर्नुस्','meroshare.cdsc.com.np — C-ASBA Account लिंक गर्नुस्'],
        ['IPO Open हुँदा Apply गर्नुस्','Meroshare > Apply IPO > Company छनोट > Quantity'],
        ['बैंक खातामा रकम राख्नुस्','C-ASBA मा Linked बैंक Account मा पर्याप्त रकम राख्नुस्'],
        ['Allotment हेर्नुस्','Meroshare > My ASBA मा नतिजा आउँछ'],
        ['Refund / Share आउँछ','Allot भए DEMAT मा Share, नभए रकम फिर्ता हुन्छ'],
      ] as $i=>[$step,$desc]): ?>
      <li class="flex gap-2.5 items-start">
        <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0"><?= $i+1 ?></span>
        <div>
          <div class="text-[12.5px] font-bold text-slate-800 ne"><?= htmlspecialchars($step) ?></div>
          <div class="text-[11px] text-slate-500 mt-0.5 ne"><?= htmlspecialchars($desc) ?></div>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>

  <!-- Key terms -->
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
    महत्त्वपूर्ण शब्दावली
  </h2>
  <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100 mb-3">
    <?php foreach([
      ['IPO','Initial Public Offering — कम्पनीले पहिलोपटक सर्वसाधारणलाई शेयर बेच्ने'],
      ['FPO','Follow-on Public Offering — थप शेयर बेच्ने'],
      ['DEMAT','Dematerialized Account — शेयर राख्ने इलेक्ट्रोनिक खाता (CDSC)'],
      ['C-ASBA','Application Supported by Blocked Amount — बैंकमा रकम Hold राख्ने'],
      ['Meroshare','CDSC को Online Portal — Apply, Transfer, Allotment सब्'],
      ['Allotment','IPO मा पाएको शेयरको सूचना (Lottery system)'],
      ['Premium','Face Value भन्दा बढी मूल्यमा Issue — मुनाफा देखिए'],
      ['Grey Market','Secondary market खुल्नु अगाडि informal trading — जोखिमपूर्ण'],
    ] as [$term,$def]): ?>
    <div class="flex gap-3 p-2.5 items-start">
      <span class="bg-teal-100 text-teal-700 text-[10px] font-bold px-2 py-0.5 rounded flex-shrink-0 mt-0.5 min-w-[56px] text-center"><?= $term ?></span>
      <span class="text-[11.5px] text-slate-600 leading-snug ne"><?= htmlspecialchars($def) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick links -->
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="link" class="w-4 h-4 text-sky-600"></i>
    IPO सम्बन्धी महत्त्वपूर्ण लिंकहरू
  </h2>
  <div class="grid grid-cols-2 gap-2 mb-4">
    <?php foreach([
      ['Meroshare','https://meroshare.cdsc.com.np/#/login','Apply / Allotment','teal'],
      ['ShareSansar','https://www.sharesansar.com/ipo','IPO Calendar','amber'],
      ['NEPSE Online','https://nepalstock.com/','Market Data','blue'],
      ['SEBON','https://www.sebon.gov.np/','नियामक','emerald'],
      ['Mero Lagani','https://merolagani.com/','Analysis','violet'],
      ['CDSC','https://www.cdsc.com.np/','DEMAT info','orange'],
    ] as [$name,$url,$desc,$cl]): ?>
    <a href="<?= $url ?>" target="_blank" rel="noopener"
       class="bg-white rounded-xl shadow-app p-2.5 flex items-center gap-2 active:bg-slate-50">
      <span class="w-8 h-8 rounded-lg bg-<?= $cl ?>-100 text-<?= $cl ?>-700 flex items-center justify-center shrink-0">
        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
      </span>
      <div class="min-w-0">
        <div class="text-[12px] font-bold text-slate-800"><?= $name ?></div>
        <div class="text-[10px] text-slate-500 ne"><?= $desc ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Risk warning -->
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-[11px] text-amber-800">
    <b>⚠ जोखिम सचेतना:</b> शेयर बजारमा लगानी जोखिमपूर्ण छ। IPO Allot भए पनि Listing मा मूल्य घट्न सक्छ। आफ्नो क्षमता अनुसार मात्र लगानी गर्नुस्।
  </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
