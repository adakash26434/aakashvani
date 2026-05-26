<?php
/**
 * cricket.php — Cricket live scores, schedule, Nepal cricket news
 */
$pageTitle = 'क्रिकेट | आकाशवाणी';
$pageDesc  = 'Cricket live scores, IPL results, Nepal cricket team, upcoming matches — आकाशवाणीमा सबै क्रिकेट अपडेट।';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- ── Hero ── -->
<section class="px-4 pt-3 pb-2">
  <div class="rounded-2xl p-5 text-white shadow-app relative overflow-hidden" style="background:linear-gradient(135deg,#1a3a2a 0%,#166534 60%,#15803d 100%)">
    <div class="absolute -right-4 -top-4 text-[110px] opacity-10 leading-none select-none"><i data-lucide="trophy" class="w-24 h-24"></i></div>
    <div class="text-[11px] opacity-80 flex items-center gap-2">
      क्रिकेट स्कोर
      <span id="ckt-live-badge" class="hidden items-center gap-1 bg-white/20 px-2 py-0.5 rounded-full text-[9.5px] font-bold">
        <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span> LIVE
      </span>
    </div>
    <div class="text-[24px] font-extrabold leading-tight mt-0.5"><i data-lucide="trophy" class="w-6 h-6 inline-block mr-2"></i>Cricket</div>
    <div class="text-[12px] opacity-80 mt-1">Live Score · Results · Nepal · IPL</div>
  </div>
</section>

<!-- ── Tabs ── -->
<nav class="px-4 mb-3">
  <div class="flex gap-1 bg-slate-100 rounded-xl p-1 overflow-x-auto scrollbar-none">
    <?php foreach([
      ['news',     '<i data-lucide="newspaper" class="w-3.5 h-3.5 inline-block"></i>', 'समाचार'],
      ['upcoming', '<i data-lucide="calendar" class="w-3.5 h-3.5 inline-block"></i>', 'आगामी'],
      ['results',  '<i data-lucide="trophy" class="w-3.5 h-3.5 inline-block"></i>', 'नतिजा'],
    ] as [$t,$ic,$lb]): ?>
    <button data-tab="<?= $t ?>"
      class="ckt-tab flex-shrink-0 text-[11.5px] font-semibold py-1.5 px-3 rounded-lg transition-colors text-slate-500 whitespace-nowrap">
      <?= $ic ?> <?= $lb ?>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── Content ── -->
<section class="px-4" id="ckt-content">
  <div id="ckt-loading" class="py-16 text-center text-slate-400">
    <div class="w-8 h-8 border-2 border-green-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
    <p class="text-[13px]">लोड हुँदैछ…</p>
  </div>
  <div id="ckt-list" class="hidden space-y-3"></div>
  <div id="ckt-empty" class="py-16 text-center hidden">
    <div class="mb-3"><i data-lucide="trophy" class="w-12 h-12 text-slate-300 mx-auto"></i></div>
    <p class="text-[14px] font-semibold text-slate-700">अहिले कुनै डेटा भेटिएन</p>
    <p class="text-[12px] text-slate-400 mt-1">पछि फेरि हेर्नुस्</p>
  </div>
</section>

<!-- ── Quick links ── -->
<section class="px-4 mt-4 mb-2">
  <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
    <?php foreach([
      ['ESPN Cricinfo', 'https://www.espncricinfo.com/',     'bg-orange-50 text-orange-700 border-orange-200'],
      ['CricBuzz',      'https://www.cricbuzz.com/',         'bg-green-50 text-green-700 border-green-200'],
      ['Nepal Cricket', 'https://cricketnepal.com.np/',      'bg-blue-50 text-blue-700 border-blue-200'],
      ['ICC',           'https://www.icc-cricket.com/',      'bg-slate-50 text-slate-700 border-slate-200'],
      ['IPL',           'https://www.iplt20.com/',           'bg-purple-50 text-purple-700 border-purple-200'],
    ] as [$name,$url,$cls]): ?>
    <a href="<?= $url ?>" target="_blank" rel="noopener"
       class="flex-shrink-0 text-[11px] font-semibold px-3 py-1.5 rounded-full border <?= $cls ?> whitespace-nowrap">
      <?= $name ?> ↗
    </a>
    <?php endforeach; ?>
  </div>
</section>

<div class="pb-4"></div>
</main>

<style>
.ckt-tab.active { background:#fff; color:#166534; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.match-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
.match-card.live-card { border-color:#dc2626; }
.team-row { display:flex; align-items:center; justify-content:space-between; padding:0 14px; }
.team-name { font-size:14px; font-weight:700; color:#0f172a; }
.team-score { font-size:15px; font-weight:800; color:#166534; }
.match-meta { font-size:10.5px; color:#64748b; padding:6px 14px 10px; }
.news-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; display:flex; gap:10px; padding:10px; }
.news-card img { width:72px; height:60px; object-fit:cover; border-radius:8px; flex-shrink:0; }
.news-card .info { flex:1; min-width:0; }
.news-card .ttl { font-size:13px; font-weight:600; color:#0f172a; line-height:1.4; display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
.news-card .src { font-size:10px; color:#64748b; margin-top:4px; }
</style>

<script>
(function(){
  var current = 'news';
  var cache   = {};

  var tabs    = document.querySelectorAll('.ckt-tab');
  var listEl  = document.getElementById('ckt-list');
  var loadEl  = document.getElementById('ckt-loading');
  var emptyEl = document.getElementById('ckt-empty');

  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

  function show(tab) {
    tabs.forEach(function(t){ t.classList.toggle('active', t.dataset.tab===tab); });
    current = tab;
    if (cache[tab]) { render(tab, cache[tab]); return; }
    loadEl.classList.remove('hidden');
    listEl.classList.add('hidden');
    emptyEl.classList.add('hidden');

    fetch('/api/cricket.php?mode=all')
      .then(function(r){ return r.json(); })
      .then(function(d){
        cache['news']     = d.news     || [];
        cache['upcoming'] = d.upcoming || [];
        cache['results']  = d.results  || [];
        // Show live badge if any live matches
        var live = (d.upcoming||[]).concat(d.results||[]).some(function(m){ return m.status==='live'; });
        if (live) {
          var b = document.getElementById('ckt-live-badge');
          if (b) { b.classList.remove('hidden'); b.classList.add('flex'); }
        }
        render(current, cache[current] || []);
      })
      .catch(function(){ loadEl.classList.add('hidden'); emptyEl.classList.remove('hidden'); });
  }

  function render(tab, items) {
    loadEl.classList.add('hidden');
    if (!items || !items.length) {
      listEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      return;
    }
    emptyEl.classList.add('hidden');
    listEl.classList.remove('hidden');

    if (tab === 'news') {
      listEl.innerHTML = items.map(function(it){
        var img = it.image ? '<img src="'+esc(it.image)+'" alt="" onerror="this.style.display=\'none\'">' : '';
        return '<a href="'+esc(it.link)+'" target="_blank" rel="noopener" class="news-card block">'
          + img
          + '<div class="info">'
          + '<div class="ttl ne">'+esc(it.title)+'</div>'
          + '<div class="src">'+esc(it.source)+' · '+esc(it.ago||'')+'</div>'
          + '</div></a>';
      }).join('');
    } else {
      listEl.innerHTML = items.map(function(it){
        var isLive = it.status === 'live';
        var dateStr = it.date ? it.date : '';
        var timeStr = it.time ? it.time.slice(0,5)+' UTC' : '';
        var score   = it.score ? it.score : '';
        var result  = it.result ? '<div class="text-[11px] text-green-700 font-semibold px-3 pb-2">🏆 '+esc(it.result)+'</div>' : '';
        var badge   = isLive
          ? '<span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>LIVE</span>'
          : (it.status==='result'
            ? '<span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">✓ समाप्त</span>'
            : '<span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">📅 आगामी</span>');

        var flagEmoji = it.league_flag || '🏏';
        return '<div class="match-card '+(isLive?'live-card':'')+'">'
          + '<div class="flex items-center justify-between px-3 pt-3 pb-2">'
          + '<span class="text-[11px] font-semibold text-slate-500">'+flagEmoji+' '+esc(it.league||'Cricket')+'</span>'
          + badge
          + '</div>'
          + '<div class="border-t border-slate-100 py-3 space-y-2">'
          + '<div class="team-row">'
          + '<span class="team-name">'+esc(it.home||it.title)+'</span>'
          + (score ? '<span class="team-score">'+esc(score.split('-')[0]||'')+'</span>' : '')
          + '</div>'
          + (it.away ? '<div class="team-row"><span class="team-name">'+esc(it.away)+'</span>'+(score?'<span class="team-score">'+esc(score.split('-')[1]||'')+'</span>':'')+'</div>' : '')
          + '</div>'
          + result
          + '<div class="match-meta">📍 '+esc(it.venue||'—')+' &nbsp;·&nbsp; '+esc(dateStr)+' '+esc(timeStr)+'</div>'
          + '</div>';
      }).join('');
    }
    if (window.lucide && lucide.createIcons) lucide.createIcons();
  }

  tabs.forEach(function(t){
    t.addEventListener('click', function(){ show(t.dataset.tab); });
  });

  show('news');
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
