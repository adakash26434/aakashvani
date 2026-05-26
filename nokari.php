<?php
/**
 * nokari.php — निजी क्षेत्र जागिर / Private Sector Jobs
 */
$pageTitle = 'नोकरी खोज्नुस् | आकाशवाणी';
$pageDesc  = 'नेपालमा निजी क्षेत्रका जागिरहरू — MeroJob, HamroJob, FroxJob, KumariJob बाट Live। IT, Finance, Engineering, Teaching र थप।';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- ── Hero ── -->
<section class="px-4 pt-3 pb-2">
  <div class="rounded-2xl p-5 text-white shadow-app relative overflow-hidden" style="background:linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 60%,#2563eb 100%)">
    <div class="absolute -right-4 -top-4 text-[110px] opacity-10 leading-none select-none">💼</div>
    <div class="text-[11px] opacity-80">निजी क्षेत्र जागिर</div>
    <div class="text-[24px] font-extrabold leading-tight mt-0.5">नोकरी खोज्नुस्</div>
    <div class="text-[12px] opacity-80 mt-1">MeroJob · HamroJob · FroxJob · KumariJob — Live</div>
    <div class="mt-3">
      <a href="/loksewa.php" class="inline-flex items-center gap-1 bg-white/20 hover:bg-white/30 text-white text-[11px] font-semibold px-3 py-1.5 rounded-full border border-white/20">
        🏛 सरकारी जागिर → लोकसेवा
      </a>
    </div>
  </div>
</section>

<!-- ── Category tabs ── -->
<nav class="px-4 mb-3">
  <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-none">
    <?php foreach([
      ['all',         '📋', 'सबै'],
      ['it',          '💻', 'IT/Tech'],
      ['finance',     '💰', 'Finance'],
      ['marketing',   '📣', 'Marketing'],
      ['engineering', '⚙️',  'Engineering'],
      ['teaching',    '📚', 'Teaching'],
      ['health',      '🏥', 'Health'],
      ['admin',       '🗂', 'Admin/HR'],
      ['general',     '🔍', 'अन्य'],
    ] as [$t,$ic,$lb]): ?>
    <button data-cat="<?= $t ?>"
      class="job-tab flex-shrink-0 text-[11px] font-semibold py-1.5 px-3 rounded-full border border-slate-200 bg-white text-slate-600 whitespace-nowrap transition-colors">
      <?= $ic ?> <?= $lb ?>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ── Search ── -->
<section class="px-4 mb-3">
  <div class="relative">
    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
    <input id="job-search" type="text" placeholder="जागिर खोज्नुस्… (e.g. developer, accountant)" autocomplete="off"
      class="w-full pl-9 pr-4 py-2.5 text-[13px] bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400">
  </div>
</section>

<!-- ── Count bar ── -->
<div class="px-4 mb-2 flex items-center gap-2">
  <span id="job-count" class="text-[11px] text-slate-500">लोड हुँदैछ…</span>
  <span id="job-live" class="hidden items-center gap-1 text-[10px] bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full">
    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Live
  </span>
</div>

<!-- ── Job list ── -->
<section class="px-4" id="job-content">
  <div id="job-loading" class="py-16 text-center text-slate-400">
    <div class="w-8 h-8 border-2 border-blue-400 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
    <p class="text-[13px]">जागिरहरू लोड हुँदैछ…</p>
  </div>
  <div id="job-list" class="space-y-2.5 hidden"></div>
  <div id="job-empty" class="py-16 text-center hidden">
    <p class="text-4xl mb-3">💼</p>
    <p class="text-[14px] font-semibold text-slate-700">कुनै जागिर भेटिएन</p>
    <p class="text-[12px] text-slate-400 mt-1">अर्को category छान्नुस् वा पछि फेरि हेर्नुस्</p>
  </div>
</section>

<!-- ── Job portals ── -->
<section class="px-4 mt-4 mb-2">
  <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
    <p class="text-[11px] text-slate-500 font-medium mb-2">🔗 सिधा जागिर Portal</p>
    <div class="grid grid-cols-2 gap-2">
      <?php foreach([
        ['MeroJob',     'https://merojob.com/',              '💼'],
        ['HamroJob',    'https://www.hamrojob.com/',         '🤝'],
        ['FroxJob',     'https://froxjob.com/',              '🚀'],
        ['KumariJob',   'https://kumarijob.com/',            '💼'],
        ['JobAxle',     'https://jobaxle.com/',              '⚙️'],
        ['JobsNepal',   'https://jobsnepal.com/',            '🇳🇵'],
      ] as [$name,$url,$ic]): ?>
      <a href="<?= $url ?>" target="_blank" rel="noopener"
         class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-2 hover:border-blue-300 transition-colors">
        <span class="text-[16px]"><?= $ic ?></span>
        <span class="text-[12px] font-semibold text-slate-700"><?= $name ?></span>
        <i data-lucide="external-link" class="w-3 h-3 text-slate-400 ml-auto"></i>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<style>
.job-tab.active { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
.job-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:12px 14px; }
.job-card .title { font-size:14px; font-weight:700; color:#0f172a; line-height:1.4; }
.job-card .meta  { font-size:11px; color:#64748b; margin-top:5px; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.job-card .sum   { font-size:12px; color:#475569; margin-top:5px; line-height:1.5; display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
</style>

<script>
(function(){
  var currentCat  = 'all';
  var allItems    = [];
  var searchQuery = '';

  var listEl  = document.getElementById('job-list');
  var loadEl  = document.getElementById('job-loading');
  var emptyEl = document.getElementById('job-empty');
  var countEl = document.getElementById('job-count');
  var liveEl  = document.getElementById('job-live');

  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }

  var catColors = {
    it:          'bg-blue-100 text-blue-700',
    finance:     'bg-green-100 text-green-700',
    marketing:   'bg-orange-100 text-orange-700',
    engineering: 'bg-slate-100 text-slate-700',
    teaching:    'bg-purple-100 text-purple-700',
    health:      'bg-rose-100 text-rose-700',
    admin:       'bg-amber-100 text-amber-700',
    legal:       'bg-indigo-100 text-indigo-700',
    driver:      'bg-teal-100 text-teal-700',
    general:     'bg-gray-100 text-gray-600',
  };
  var catNames = {
    it:'💻 IT/Tech',finance:'💰 Finance',marketing:'📣 Marketing',engineering:'⚙️ Engineering',
    teaching:'📚 Teaching',health:'🏥 Health',admin:'🗂 Admin/HR',legal:'⚖️ Legal',driver:'🚗 Driver',general:'🔍 General'
  };

  function renderItems(items) {
    loadEl.classList.add('hidden');
    if (!items || !items.length) {
      listEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      countEl.textContent = '०';
      return;
    }
    emptyEl.classList.add('hidden');
    listEl.classList.remove('hidden');
    countEl.textContent = items.length + ' जागिर';

    listEl.innerHTML = items.map(function(it){
      var cc   = catColors[it.category] || catColors.general;
      var cn   = catNames[it.category]  || '🔍 General';
      var srcC = it.sourceCls || 'bg-slate-100 text-slate-600';
      var sum  = it.summary ? '<div class="sum">'+esc(it.summary)+'</div>' : '';
      return '<a href="'+esc(it.link)+'" target="_blank" rel="noopener" class="job-card block">'
        + '<div class="title ne">'+esc(it.title)+'</div>'
        + sum
        + '<div class="meta">'
        + '<span class="text-[10px] font-semibold px-2 py-0.5 rounded-full '+cc+'">'+cn+'</span>'
        + '<span class="text-[10px] font-semibold px-2 py-0.5 rounded-full '+srcC+'">'+esc(it.source)+'</span>'
        + '<span class="ml-auto">'+esc(it.ago||'')+'</span>'
        + '</div>'
        + '</a>';
    }).join('');
    if (window.lucide && lucide.createIcons) lucide.createIcons();
  }

  function applyFilter() {
    var filtered = allItems.filter(function(it){
      var catOk = currentCat === 'all' || it.category === currentCat;
      var srchOk = !searchQuery || esc(it.title).toLowerCase().indexOf(searchQuery) !== -1 || (it.summary||'').toLowerCase().indexOf(searchQuery) !== -1;
      return catOk && srchOk;
    });
    renderItems(filtered);
  }

  document.querySelectorAll('.job-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.job-tab').forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      currentCat = btn.dataset.cat;
      applyFilter();
    });
  });

  var searchInput = document.getElementById('job-search');
  searchInput.addEventListener('input', function(){
    searchQuery = this.value.toLowerCase().trim();
    applyFilter();
  });

  // Load all items
  loadEl.classList.remove('hidden');
  fetch('/api/nokari.php?limit=60')
    .then(function(r){ return r.json(); })
    .then(function(d){
      allItems = d.items || [];
      if (allItems.length > 0) {
        liveEl.classList.remove('hidden');
        liveEl.classList.add('flex');
      }
      applyFilter();
    })
    .catch(function(){
      loadEl.classList.add('hidden');
      emptyEl.classList.remove('hidden');
      countEl.textContent = 'डेटा आएन';
    });

  // Default tab active
  var allTab = document.querySelector('.job-tab[data-cat="all"]');
  if (allTab) allTab.classList.add('active');
  if (window.lucide && lucide.createIcons) lucide.createIcons();
})();
</script>

<!-- ══ SALARY GUIDE + TIPS ═══════════════════════════════════════════════ -->
<section class="px-4 mt-2 mb-2">

  <!-- Salary guide -->
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="banknote" class="w-4 h-4 text-emerald-600"></i>
    नेपालमा तलब अनुमान (२०२४–२०२५)
  </h2>
  <div class="bg-white rounded-2xl shadow-app overflow-hidden mb-3">
    <table class="w-full text-[11.5px]">
      <thead class="bg-slate-50 border-b border-slate-100">
        <tr>
          <th class="text-left p-2.5 text-slate-600 font-bold ne">क्षेत्र</th>
          <th class="text-right p-2.5 text-slate-600 font-bold">सुरुवात</th>
          <th class="text-right p-2.5 text-slate-600 font-bold">अनुभवी</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach([
          ['IT / Software',    'रू १५,०००–२०,०००','रू ५०,०००–१,२०,०००'],
          ['Finance / Banking','रू १२,०००–१८,०००','रू ३०,०००–७०,०००'],
          ['Teaching',         'रू ८,०००–१५,०००', 'रू २०,०००–४५,०००'],
          ['Marketing / Sales','रू १०,०००–१५,०००','रू २५,०००–६०,०००'],
          ['Engineering',      'रू १५,०००–२५,०००','रू ४५,०००–१,००,०००'],
          ['Healthcare',       'रू १२,०००–२०,०००','रू ३५,०००–८०,०००'],
          ['NGO / INGO',       'रू १८,०००–३०,०००','रू ५०,०००–१,५०,०००'],
        ] as [$cat,$fresh,$exp]): ?>
        <tr class="hover:bg-slate-50">
          <td class="p-2.5 font-semibold text-slate-800 ne"><?= $cat ?></td>
          <td class="p-2.5 text-right text-slate-600 ne"><?= $fresh ?></td>
          <td class="p-2.5 text-right font-bold text-emerald-700 ne"><?= $exp ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="px-3 py-1.5 bg-slate-50 text-[9.5px] text-slate-400">* अनुमानित मात्र — वास्तविक तलब कम्पनी र सीप अनुसार भिन्न हुन्छ।</div>
  </div>

  <!-- Resume tips -->
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="file-user" class="w-4 h-4 text-blue-600"></i>
    राम्रो CV बनाउने सुझाव
  </h2>
  <div class="space-y-2 mb-3">
    <?php foreach([
      ['✅','सम्पर्क स्पष्ट राख्नुस्','नाम, फोन, इमेल, LinkedIn/GitHub — सबैभन्दा माथि'],
      ['📝','उद्देश्य छोटो लेख्नुस्','२–३ लाइनमा तपाईंको लक्ष्य र मूल्य बताउनुस्'],
      ['🏆','उपलब्धि नम्बरमा','"Sales बढायो" भन्दा "Sales ३०% बढायो" राम्रो'],
      ['🔑','Keywords मिलाउनुस्','Job description बाट keyword CV मा use गर्नुस्'],
      ['📄','२ पेजभित्र राख्नुस्','Fresh: १ page, Experienced: २ page अधिकतम'],
      ['🖨','ATS-friendly format','सादा फन्ट, तालिका नगर्नुस् — सफ्टवेयर पढ्छ'],
    ] as [$ic,$tip,$desc]): ?>
    <div class="bg-white rounded-xl shadow-app p-3 flex gap-3">
      <span class="text-[18px] flex-shrink-0 leading-tight mt-0.5"><?= $ic ?></span>
      <div>
        <div class="text-[12px] font-bold text-slate-800"><?= $tip ?></div>
        <div class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($desc) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Top portals -->
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="link" class="w-4 h-4 text-violet-600"></i>
    नेपालका शीर्ष जागिर पोर्टलहरू
  </h2>
  <div class="grid grid-cols-2 gap-2 mb-4">
    <?php foreach([
      ['MeroJob',   'https://merojob.com',      '🇳🇵', 'emerald', 'सबैभन्दा ठूलो'],
      ['HamroJob',  'https://hamrojob.com',     '🇳🇵', 'sky',     'IT केन्द्रित'],
      ['FroxJob',   'https://froxjob.com',      '🇳🇵', 'amber',   'सरल इन्टरफेस'],
      ['KumariJob', 'https://kumarijob.com',    '🇳🇵', 'rose',    'बहु-क्षेत्र'],
      ['LinkedIn',  'https://linkedin.com/jobs','🌐', 'blue',    'Professional'],
      ['JobAxle',   'https://jobaxle.com',      '🇳🇵', 'violet',  'Career guidance'],
    ] as [$name,$url,$fl,$cl,$desc]): ?>
    <a href="<?= $url ?>" target="_blank" rel="noopener"
       class="bg-white rounded-xl shadow-app p-2.5 flex items-center gap-2 active:bg-slate-50">
      <span class="text-[22px]"><?= $fl ?></span>
      <div class="min-w-0">
        <div class="text-[12px] font-bold text-slate-800"><?= $name ?></div>
        <div class="text-[10px] text-<?= $cl ?>-600 font-semibold"><?= $desc ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

</section>

<?php require_once __DIR__ . '/footer.php'; ?>
