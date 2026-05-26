<?php
/** morning-brief.php v13 — LIVE morning briefing
 *  v13 fix: पहिले hardcoded gold=१,४८,५००, NEPSE +18.32 placeholder देखाउँथ्यो
 *  जुन utilities/index.php सँग मेल खाँदैनथ्यो। अब includes/market.php (single
 *  source of truth) बाट live values र /api/news-rss.php बाट top news। */
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/market.php';
$mb_m = getMarket(true);
$mb_nepseIdx = (float)($mb_m['nepse']['index'] ?? 0);
$mb_nepseChg = $mb_m['nepse']['change'] ?? null;
$mb_nepsePct = $mb_m['nepse']['percent'] ?? null;
$mb_gold     = (float)($mb_m['gold']['fine'] ?? 0);
$mb_silver   = (float)($mb_m['gold']['silver'] ?? 0);
$mb_petrol   = (float)($mb_m['fuel']['petrol'] ?? 0);
$mb_usd      = (float)($mb_m['forex']['USD'] ?? 0);

function mb_fmt($n){ return $n>0 ? 'रू '.number_format($n,$n<1000?2:0) : '—'; }
function mb_nepseLine($i,$c,$p){
  if($i<=0) return 'लोड हुँदै…';
  if($c===null) return number_format($i,2);
  $sign = $c>=0 ? '+' : '−';
  $pct  = $p!==null ? ' ('.$sign.number_format(abs($p),2).'%)' : '';
  return number_format($i,2).' '.$sign.number_format(abs($c),2).$pct;
}

$brief = [
  'date'    => $bsDateStr,
  'summary' => 'AI सारांश लोड हुँदै…',
  'highlights' => [
    ['बजार',  'NEPSE '.mb_nepseLine($mb_nepseIdx,$mb_nepseChg,$mb_nepsePct), 'trending-up', ($mb_nepseChg!==null && $mb_nepseChg<0)?'rose':'emerald'],
    ['मौसम',  'काठमाडौं — लोड हुँदै…',                         'cloud-sun',   'sky'],
    ['स्वर्ण', mb_fmt($mb_gold).'/तोला',                         'circle',      'amber'],
    ['पेट्रोल', mb_fmt($mb_petrol).'/लिटर',                       'fuel',        'orange'],
  ],
  'top_news' => [], // hydrated client-side from /api/news-rss.php
];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="rounded-2xl p-5 text-white shadow-app bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 relative overflow-hidden">
      <i data-lucide="sunrise" class="absolute -right-4 -bottom-4 w-32 h-32 opacity-20"></i>
      <div class="text-[11px] opacity-90"><?= $tH('बिहानी ब्रिफ','Morning Brief') ?></div>
      <div class="text-[24px] font-extrabold leading-tight"><?= $tH('शुभ प्रभात','Good Morning') ?> 🌅</div>
      <div class="text-[12px] opacity-90 mt-1"><?= htmlspecialchars($brief['date']) ?></div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-app mt-3">
      <div class="flex items-center gap-1.5 mb-2"><i data-lucide="bot" class="w-4 h-4 text-teal-600"></i><span class="text-[11px] font-bold text-teal-700">AI SUMMARY</span></div>
      <p class="text-[14px] text-slate-800 leading-relaxed" id="mb-summary"><?= htmlspecialchars($brief['summary']) ?></p>
      <div class="text-[10px] text-slate-400 mt-2">स्रोत: NEPSE · FENEGOSIDA · NOC · NRB · RSS</div>
    </div>
  </section>

  <section class="px-4 mt-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('मुख्य हाइलाइट','Key Highlights') ?></h2>
    <div class="grid grid-cols-2 gap-2.5">
      <?php foreach($brief['highlights'] as $i=>$h): ?>
        <div class="bg-white rounded-2xl p-3 shadow-app">
          <div class="w-9 h-9 rounded-xl bg-<?= $h[3] ?>-100 text-<?= $h[3] ?>-700 flex items-center justify-center mb-2"><i data-lucide="<?= $h[2] ?>" class="w-4 h-4"></i></div>
          <div class="text-[10px] text-slate-500"><?= htmlspecialchars($h[0]) ?></div>
          <div class="text-[12px] font-bold text-slate-900 leading-snug" data-mb="<?= $i ?>"><?= htmlspecialchars($h[1]) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="px-4 mt-4 pb-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('शीर्ष समाचार','Top Stories') ?></h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100" id="mb-top-news">
      <div class="p-4 text-[12px] text-slate-400">समाचार लोड हुँदै…</div>
    </div>
  </section>
</main>

<script>
(function(){
  // 1) Live weather for "मौसम" highlight
  fetch('/api/weather-alerts.php?type=weather&city='+encodeURIComponent('काठमाडौं'))
    .then(r=>r.json()).then(function(d){
      var w = (d && d.weather) || d || {};
      var t = w.temperature ?? w.temp ?? w.current;
      var c = w.condition || w.cond || w.summary || '';
      var el = document.querySelector('[data-mb="1"]');
      if (el && t!=null) el.textContent = 'काठमाडौं — '+t+'°C'+(c?' · '+c:'');
    }).catch(function(){});

  // 2) Top news from same RSS feed used by /news.php
  fetch('/api/news-rss.php?limit=8').then(r=>r.json()).then(function(d){
    var items = (d && (d.items || d.data || d.news)) || [];
    var box = document.getElementById('mb-top-news');
    if(!items.length){ box.innerHTML = '<div class="p-4 text-[12px] text-slate-400">समाचार उपलब्ध छैन</div>'; return; }
    var html = '';
    items.slice(0,6).forEach(function(it,i){
      var url = it.internalUrl || (it.slug ? '/news-detail.php?slug=' + encodeURIComponent(it.slug) : '/news-detail.php?url=' + encodeURIComponent(it.link || it.url || '') + '&src=' + encodeURIComponent(it.sourceLabel || ''));
      var ttl = (it.title||'').replace(/[<>]/g,'');
      var src = (it.sourceLabel || it.source || '');
      html += '<a href="'+url+'" class="flex items-center gap-3 p-3">'+
        '<div class="w-7 h-7 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-[12px] font-bold">'+(i+1)+'</div>'+
        '<div class="flex-1 min-w-0">'+
          '<div class="text-[13px] font-semibold text-slate-900 leading-snug">'+ttl+'</div>'+
          (src?'<div class="text-[10px] text-slate-500 mt-0.5">'+src+'</div>':'')+
        '</div>'+
        '<i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-400"></i></a>';
    });
    box.innerHTML = html;
    if (window.lucide && lucide.createIcons) lucide.createIcons();
  }).catch(function(){
    var box = document.getElementById('mb-top-news');
    if(box) box.innerHTML = '<div class="p-4 text-[12px] text-slate-400">समाचार लोड हुन सकेन</div>';
  });

  // 3) AI summary (optional, fails silently)
  fetch('/api/morning-brief.php').then(r=>r.json()).then(function(d){
    if(d && (d.text || d.html)){
      var el = document.getElementById('mb-summary');
      if(el) el.textContent = d.text || el.textContent;
    }
  }).catch(function(){});
})();
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
