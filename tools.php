<?php
/**
 * tools.php v12 — Modern organized tools with better icon grouping
 */
require_once __DIR__ . '/header.php';

// Tool categories with organized icons
$categories = [
  'finance' => [
    'title' => $tH('वित्तीय','Finance'),
    'icon' => 'wallet',
    'color' => 'emerald',
    'tools' => [
      ['EMI Calc', '/tool.php?slug=emi', 'calculator', 'गणना'],
      ['Tax Calc', '/tax-calculator.php', 'receipt', 'कर'],
      ['FD/SIP', '/tool.php?slug=fd-sip', 'piggy-bank', 'बचत'],
      ['Loan', '/tool.php?slug=loan', 'landmark', 'ऋण'],
    ]
  ],
  'convert' => [
    'title' => $tH('रूपान्तरण','Convert'),
    'icon' => 'repeat',
    'color' => 'violet',
    'tools' => [
      ['Unit', '/tool.php?slug=unit', 'ruler', 'एकाइ'],
      ['Currency', '/tool.php?slug=currency', 'dollar-sign', 'मुद्रा'],
      ['Date', '/tool.php?slug=date-convert', 'calendar-clock', 'मिति'],
      ['Number', '/tool.php?slug=number', 'hash', 'अंक'],
    ]
  ],
  'pdf' => [
    'title' => $tH('PDF टूलहरू','PDF Tools'),
    'icon' => 'file-text',
    'color' => 'rose',
    'tools' => [
      ['Image→PDF', '/pdf-convert.php', 'image-plus', 'फोटो'],
      ['Merge PDF', '/pdf-merge.php', 'files', 'जोड्ने'],
      ['Split PDF', '/pdf-split.php', 'scissors', 'छुट्याउने'],
    ]
  ],
  'daily' => [
    'title' => $tH('दैनिक उपयोगी','Daily'),
    'icon' => 'sun',
    'color' => 'amber',
    'tools' => [
      ['Load Shed', '/tool.php?slug=load-shedding', 'zap', 'बत्ती'],
      ['Weather', '/tool.php?slug=weather', 'cloud-sun', 'मौसम'],
      ['Emergency', '/emergency.php', 'phone-call', 'आपतकाल'],
      ['Speed Test', '/tool.php?slug=speed-test', 'gauge', 'इन्टरनेट'],
    ]
  ],
  'astro' => [
    'title' => $tH('ज्योतिष र धर्म','Astrology'),
    'icon' => 'sparkles',
    'color' => 'indigo',
    'tools' => [
      ['Rashifal', '/rashifal.php', 'star', 'राशिफल'],
      ['Kundali', '/rashifal.php#kundali', 'heart-handshake', 'कुण्डली'],
      ['Panchang', '/rashifal.php#panchang', 'moon', 'पञ्चाङ्ग'],
      ['Patro', '/nepali-patro.php', 'calendar-days', 'पात्रो'],
    ]
  ],
];

// Featured/Trending tools
$featured = [
  ['PDF Tools', '/pdf-merge.php', 'file-text', 'rose', $tH('नयाँ','New')],
  ['Tax Calc', '/tax-calculator.php', 'receipt', 'emerald', $tH('लोकप्रिय','Hot')],
  ['Rashifal', '/rashifal.php', 'sparkles', 'violet', $tH('दैनिक','Daily')],
  ['EMI Calc', '/tool.php?slug=emi', 'calculator', 'sky', ''],
];

// All tools for search
$allTools = [];
foreach ($categories as $cat) {
  foreach ($cat['tools'] as $tool) {
    $allTools[] = [
      'name' => $tool[0],
      'url' => $tool[1],
      'icon' => $tool[2],
      'category' => $cat['title'],
      'keywords' => strtolower($tool[0] . ' ' . $tool[3] . ' ' . $cat['title'])
    ];
  }
}
?>

<main class="app-main">
  <!-- Header with Search -->
  <section class="px-4 pt-3">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h1 class="text-[22px] font-bold text-slate-900 ne"><?= $tH('सबै टूलहरू','All Tools') ?></h1>
        <p class="text-[12px] text-slate-500 ne"><?= $tH('आवश्यक परेको टूल खोज्नुहोस्','Find the tool you need') ?></p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white">
        <i data-lucide="wrench" class="w-5 h-5"></i>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="relative mb-4">
      <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
      <input type="text" id="toolSearch" placeholder="<?= $tH('टूल खोज्नुहोस्...','Search tools...') ?>" 
        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100 ne">
    </div>
  </section>

  <!-- Featured Tools (Horizontal Scroll) -->
  <section class="px-4 mb-4">
    <div class="flex items-center gap-2 mb-2">
      <i data-lucide="flame" class="w-4 h-4 text-orange-500"></i>
      <h2 class="text-[13px] font-bold text-slate-700 ne"><?= $tH('लोकप्रिय','Trending') ?></h2>
    </div>
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide" style="scrollbar-width:none">
      <?php foreach($featured as $f): ?>
        <a href="<?= $f[1] ?>" class="flex-shrink-0 bg-white rounded-xl p-3 shadow-app min-w-[100px] text-center relative">
          <?php if($f[4]): ?>
            <span class="absolute -top-1 -right-1 bg-<?= $f[3] ?>-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full"><?= $f[4] ?></span>
          <?php endif; ?>
          <div class="w-10 h-10 mx-auto rounded-lg bg-<?= $f[3] ?>-100 text-<?= $f[3] ?>-600 flex items-center justify-center mb-1.5">
            <i data-lucide="<?= $f[2] ?>" class="w-5 h-5"></i>
          </div>
          <div class="text-[11px] font-semibold text-slate-800 leading-tight ne"><?= $f[0] ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Quick Access - Most Used -->
  <section class="px-4 mb-4" id="quickAccess">
    <div class="grid grid-cols-4 gap-2">
      <a href="/tax-calculator.php" class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-3 text-white text-center shadow-lg">
        <i data-lucide="receipt" class="w-6 h-6 mx-auto mb-1"></i>
        <span class="text-[10px] font-medium ne">कर Calc</span>
      </a>
      <a href="/pdf-merge.php" class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl p-3 text-white text-center shadow-lg">
        <i data-lucide="files" class="w-6 h-6 mx-auto mb-1"></i>
        <span class="text-[10px] font-medium ne">PDF</span>
      </a>
      <a href="/rashifal.php" class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl p-3 text-white text-center shadow-lg">
        <i data-lucide="sparkles" class="w-6 h-6 mx-auto mb-1"></i>
        <span class="text-[10px] font-medium ne">राशि</span>
      </a>
      <a href="/tool.php?slug=emi" class="bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl p-3 text-white text-center shadow-lg">
        <i data-lucide="calculator" class="w-6 h-6 mx-auto mb-1"></i>
        <span class="text-[10px] font-medium ne">EMI</span>
      </a>
    </div>
  </section>

  <!-- Search Results (Hidden by default) -->
  <section class="px-4 mb-4 hidden" id="searchResults">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2 ne"><?= $tH('खोज परिणाम','Search Results') ?></h2>
    <div class="grid grid-cols-2 gap-2" id="searchGrid"></div>
  </section>

  <!-- All Categories -->
  <section id="allCategories">
    <?php foreach($categories as $key => $cat): ?>
      <div class="px-4 mb-4 category-section" data-category="<?= $key ?>">
        <!-- Category Header -->
        <div class="flex items-center gap-2 mb-2">
          <div class="w-7 h-7 rounded-lg bg-<?= $cat['color'] ?>-100 text-<?= $cat['color'] ?>-600 flex items-center justify-center">
            <i data-lucide="<?= $cat['icon'] ?>" class="w-4 h-4"></i>
          </div>
          <h2 class="text-[14px] font-bold text-slate-700 ne"><?= $cat['title'] ?></h2>
          <span class="ml-auto text-[10px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full"><?= count($cat['tools']) ?></span>
        </div>
        
        <!-- Tools Grid -->
        <div class="grid grid-cols-4 gap-2">
          <?php foreach($cat['tools'] as $tool): ?>
            <a href="<?= $tool[1] ?>" class="tool-card bg-white rounded-xl p-3 shadow-app text-center hover:shadow-lg hover:scale-105 transition-all group" data-keywords="<?= strtolower($tool[0] . ' ' . $tool[3]) ?>">
              <div class="w-10 h-10 mx-auto rounded-xl bg-gradient-to-br from-<?= $cat['color'] ?>-100 to-<?= $cat['color'] ?>-200 text-<?= $cat['color'] ?>-600 flex items-center justify-center mb-1.5 group-hover:from-<?= $cat['color'] ?>-500 group-hover:to-<?= $cat['color'] ?>-600 group-hover:text-white transition-all">
                <i data-lucide="<?= $tool[2] ?>" class="w-5 h-5"></i>
              </div>
              <div class="text-[11px] font-semibold text-slate-800 leading-tight ne"><?= $tool[0] ?></div>
              <div class="text-[9px] text-slate-400 mt-0.5 ne"><?= $tool[3] ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- No Results Message -->
  <div class="px-4 hidden" id="noResults">
    <div class="bg-slate-50 rounded-xl p-6 text-center border border-slate-200">
      <i data-lucide="search-x" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
      <p class="ne text-slate-500"><?= $tH('कुनै टूल भेटिएन','No tools found') ?></p>
    </div>
  </div>

  <!-- AI Assistant CTA -->
  <section class="px-4 mt-4 pb-4">
    <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl p-4 text-white shadow-app relative overflow-hidden">
      <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
            <i data-lucide="bot" class="w-4 h-4"></i>
          </div>
          <span class="text-[14px] font-bold">AI सहायक</span>
          <span class="ml-auto text-[10px] bg-white/20 px-2 py-0.5 rounded-full">Beta</span>
        </div>
        <p class="text-[13px] opacity-90 mb-3 ne"><?= $tH('कुनै पनि गणना वा प्रश्न नेपालीमै सोध्नुहोस्','Ask any calculation in Nepali') ?></p>
        <a href="/ai-guides.php" class="inline-flex items-center gap-1.5 text-[12px] font-bold bg-white text-indigo-600 px-4 py-2 rounded-full shadow-lg hover:shadow-xl transition-all">
          <?= $tH('AI सँग कुरा गर्नुहोस्','Chat with AI') ?>
          <i data-lucide="message-circle" class="w-4 h-4"></i>
        </a>
      </div>
    </div>
  </section>
</main>

<script>
(function(){
  const searchInput = document.getElementById('toolSearch');
  const allCategories = document.getElementById('allCategories');
  const searchResults = document.getElementById('searchResults');
  const searchGrid = document.getElementById('searchGrid');
  const noResults = document.getElementById('noResults');
  const quickAccess = document.getElementById('quickAccess');

  // Search functionality
  searchInput.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    
    if (!query) {
      allCategories.classList.remove('hidden');
      quickAccess.classList.remove('hidden');
      searchResults.classList.add('hidden');
      noResults.classList.add('hidden');
      return;
    }

    allCategories.classList.add('hidden');
    quickAccess.classList.add('hidden');
    
    const tools = document.querySelectorAll('.tool-card');
    const matches = [];
    
    tools.forEach(tool => {
      const keywords = tool.getAttribute('data-keywords');
      const title = tool.querySelector('div:last-child').textContent.toLowerCase();
      
      if (keywords.includes(query) || title.includes(query)) {
        matches.push(tool.cloneNode(true));
      }
    });

    if (matches.length > 0) {
      searchGrid.innerHTML = '';
      matches.forEach(match => searchGrid.appendChild(match));
      searchResults.classList.remove('hidden');
      noResults.classList.add('hidden');
    } else {
      searchResults.classList.add('hidden');
      noResults.classList.remove('hidden');
    }
    
    if (window.lucide) lucide.createIcons();
  });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
