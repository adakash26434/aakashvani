<?php
/**
 * Festival Calendar - Nepali festivals and important dates
 * Shows upcoming festivals with BS dates
 */
$pageTitle = 'Festival Calendar | आकाशवाणी';
$pageDesc  = 'Nepali festival calendar with important dates, festivals, and cultural events.';
require_once __DIR__ . '/header.php';

// Festival data
$festivals = [
  [
    'name' => 'नयाँ वर्ष (Naya Barsha)',
    'name_en' => 'Nepali New Year',
    'bs_date' => '1 बैशाख',
    'month' => 'बैशाख',
    'type' => 'national',
    'icon' => 'calendar',
    'color' => 'bg-emerald-100 text-emerald-700'
  ],
  [
    'name' => 'बुद्ध जयन्ती',
    'name_en' => 'Buddha Jayanti',
    'bs_date' => '१५ जेठ',
    'month' => 'जेठ',
    'type' => 'religious',
    'icon' => 'sparkles',
    'color' => 'bg-amber-100 text-amber-700'
  ],
  [
    'name' => 'गाईजात्रा',
    'name_en' => 'Gai Jatra',
    'bs_date' => '१५ भाद्र',
    'month' => 'भाद्र',
    'type' => 'cultural',
    'icon' => 'cow',
    'color' => 'bg-pink-100 text-pink-700'
  ],
  [
    'name' => 'जनै पूर्णिमा',
    'name_en' => 'Janai Purnima',
    'bs_date' => '१ भाद्र',
    'month' => 'भाद्र',
    'type' => 'religious',
    'icon' => 'moon',
    'color' => 'bg-blue-100 text-blue-700'
  ],
  [
    'name' => 'कृष्ण जन्माष्टमी',
    'name_en' => 'Krishna Janmashtami',
    'bs_date' => '३ भाद्र',
    'month' => 'भाद्र',
    'type' => 'religious',
    'icon' => 'sparkles',
    'color' => 'bg-purple-100 text-purple-700'
  ],
  [
    'name' => 'तीज',
    'name_en' => 'Teej',
    'bs_date' => '१७ भाद्र',
    'month' => 'भाद्र',
    'type' => 'cultural',
    'icon' => 'heart',
    'color' => 'bg-red-100 text-red-700'
  ],
  [
    'name' => 'इन्द्रजात्रा',
    'name_en' => 'Indra Jatra',
    'bs_date' => '१५ आश्विन',
    'month' => 'आश्विन',
    'type' => 'cultural',
    'icon' => 'crown',
    'color' => 'bg-orange-100 text-orange-700'
  ],
  [
    'name' => 'घटस्थापना',
    'name_en' => 'Ghatasthapana',
    'bs_date' => '१० आश्विन',
    'month' => 'आश्विन',
    'type' => 'religious',
    'icon' => 'calendar',
    'color' => 'bg-yellow-100 text-yellow-700'
  ],
  [
    'name' => 'फूलपाती',
    'name_en' => 'Fulpati',
    'bs_date' => '१७ आश्विन',
    'month' => 'आश्विन',
    'type' => 'religious',
    'icon' => 'flower-2',
    'color' => 'bg-green-100 text-green-700'
  ],
  [
    'name' => 'महाअष्टमी',
    'name_en' => 'Maha Asthami',
    'bs_date' => '१८ आश्विन',
    'month' => 'आश्विन',
    'type' => 'religious',
    'icon' => 'shield',
    'color' => 'bg-violet-100 text-violet-700'
  ],
  [
    'name' => 'महानवमी',
    'name_en' => 'Maha Navami',
    'bs_date' => '१९ आश्विन',
    'month' => 'आश्विन',
    'type' => 'religious',
    'icon' => 'sword',
    'color' => 'bg-red-100 text-red-700'
  ],
  [
    'name' => 'विजया दशमी',
    'name_en' => 'Vijaya Dashami',
    'bs_date' => '२० आश्विन',
    'month' => 'आश्विन',
    'type' => 'national',
    'icon' => 'swords',
    'color' => 'bg-rose-100 text-rose-700'
  ],
  [
    'name' => 'लक्ष्मी पूजा',
    'name_en' => 'Laxmi Puja',
    'bs_date' => '१३ कार्तिक',
    'month' => 'कार्तिक',
    'type' => 'religious',
    'icon' => 'sparkles',
    'color' => 'bg-amber-100 text-amber-700'
  ],
  [
    'name' => 'भाइटीका',
    'name_en' => 'Bhai Tika',
    'bs_date' => '१५ कार्तिक',
    'month' => 'कार्तिक',
    'type' => 'cultural',
    'icon' => 'heart',
    'color' => 'bg-pink-100 text-pink-700'
  ],
  [
    'name' => 'छठ पर्व',
    'name_en' => 'Chhath Puja',
    'bs_date' => '१९ कार्तिक',
    'month' => 'कार्तिक',
    'type' => 'religious',
    'icon' => 'sun',
    'color' => 'bg-orange-100 text-orange-700'
  ],
  [
    'name' => 'लोकतन्त्र दिवस',
    'name_en' => 'Democracy Day',
    'bs_date' => '७ फागुन',
    'month' => 'फागुन',
    'type' => 'national',
    'icon' => 'flag',
    'color' => 'bg-blue-100 text-blue-700'
  ],
  [
    'name' => 'होली',
    'name_en' => 'Holi',
    'bs_date' => '३० फागुन',
    'month' => 'फागुन',
    'type' => 'cultural',
    'icon' => 'palette',
    'color' => 'bg-pink-100 text-pink-700'
  ],
  [
    'name' => 'शिवरात्री',
    'name_en' => 'Maha Shivaratri',
    'bs_date' => '१४ फागुन',
    'month' => 'फागुन',
    'type' => 'religious',
    'icon' => 'moon',
    'color' => 'bg-violet-100 text-violet-700'
  ],
  [
    'name' => 'राम नवमी',
    'name_en' => 'Ram Navami',
    'bs_date' => '९ चैत्र',
    'month' => 'चैत्र',
    'type' => 'religious',
    'icon' => 'sparkles',
    'color' => 'bg-orange-100 text-orange-700'
  ],
  [
    'name' => 'चैते दशैं',
    'name_en' => 'Chaite Dashain',
    'bs_date' => '१५ चैत्र',
    'month' => 'चैत्र',
    'type' => 'religious',
    'icon' => 'swords',
    'color' => 'bg-red-100 text-red-700'
  ],
];

// Get current BS month
$currentMonth = isset($bsM) ? $bsM : 1;
$bsMonths = ['','बैशाख','जेठ','असार','श्रावण','भाद्र','आश्विन','कार्तिक','मंसिर','पौष','माघ','फाल्गुन','चैत्र'];
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="calendar-days" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Festival Calendar</h1>
      <p class="text-[11px] text-slate-500">पर्व तथा त्योहार क्यालेन्डर</p>
    </div>
  </div>
</section>

<!-- Current Date Display -->
<section class="px-4 mb-4">
  <div class="bg-gradient-to-r from-rose-500 to-pink-500 rounded-2xl p-4 text-white">
    <p class="text-[11px] opacity-90 mb-1">आजको मिति</p>
    <p class="text-[20px] font-bold"><?= isset($bsDateStr) ? $bsDateStr : '—' ?></p>
    <p class="text-[10px] opacity-75 mt-1"><?= date('l, j F Y') ?></p>
  </div>
</section>

<!-- Month Filter -->
<section class="px-4 mb-4">
  <div class="flex gap-2 overflow-x-auto no-sb pb-2">
    <button onclick="filterFestivals('all')" class="month-btn active flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-rose-100 text-rose-700 border border-rose-200">
      सबै
    </button>
    <?php foreach($bsMonths as $i => $month): if($i == 0) continue; ?>
    <button onclick="filterFestivals('<?= $i ?>')" class="month-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      <?= $month ?>
    </button>
    <?php endforeach; ?>
  </div>
</section>

<!-- Festival List -->
<section class="px-4 mb-4">
  <div class="space-y-2" id="festival-list">
    <?php foreach($festivals as $festival): ?>
    <div class="festival-card bg-white rounded-xl border border-slate-100 shadow-app p-4" data-month="<?= array_search($festival['month'], $bsMonths) ?>" data-type="<?= $festival['type'] ?>">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl <?= $festival['color'] ?> flex items-center justify-center flex-shrink-0">
          <i data-lucide="<?= $festival['icon'] ?>" class="w-6 h-6"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-[13px] font-bold text-slate-900 ne"><?= $festival['name'] ?></p>
          <p class="text-[11px] text-slate-500"><?= $festival['name_en'] ?></p>
          <p class="text-[11px] text-rose-600 font-semibold mt-1"><?= $festival['bs_date'] ?></p>
        </div>
        <span class="text-[10px] px-2 py-1 rounded-full font-semibold <?= $festival['type'] === 'national' ? 'bg-blue-100 text-blue-700' : ($festival['type'] === 'religious' ? 'bg-amber-100 text-amber-700' : 'bg-pink-100 text-pink-700') ?>">
          <?= $festival['type'] === 'national' ? 'राष्ट्रिय' : ($festival['type'] === 'religious' ? 'धार्मिक' : 'सांस्कृतिक') ?>
        </span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Type Filter -->
<section class="px-4 mb-4">
  <div class="flex gap-2">
    <button onclick="filterByType('all')" class="type-btn active flex-1 py-2 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      सबै
    </button>
    <button onclick="filterByType('national')" class="type-btn flex-1 py-2 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      राष्ट्रिय
    </button>
    <button onclick="filterByType('religious')" class="type-btn flex-1 py-2 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      धार्मिक
    </button>
    <button onclick="filterByType('cultural')" class="type-btn flex-1 py-2 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      सांस्कृतिक
    </button>
  </div>
</section>

<!-- Info Note -->
<section class="px-4 mb-4">
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <i data-lucide="info" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
      <div class="text-[11px] text-amber-800">
        <p class="font-semibold mb-1">About Festival Dates</p>
        <p>Festival dates are based on Bikram Sambat (BS) calendar. Actual dates may vary slightly based on lunar calendar calculations.</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  window.filterFestivals = function(month){
    var cards = document.querySelectorAll('.festival-card');
    var buttons = document.querySelectorAll('.month-btn');
    
    buttons.forEach(function(btn){
      btn.classList.remove('active', 'bg-rose-100', 'text-rose-700', 'border-rose-200');
      btn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    });
    
    event.target.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
    event.target.classList.add('active', 'bg-rose-100', 'text-rose-700', 'border-rose-200');
    
    cards.forEach(function(card){
      if(month === 'all' || card.dataset.month == month){
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  };
  
  window.filterByType = function(type){
    var cards = document.querySelectorAll('.festival-card');
    var buttons = document.querySelectorAll('.type-btn');
    
    buttons.forEach(function(btn){
      btn.classList.remove('active', 'bg-rose-100', 'text-rose-700', 'border-rose-200');
      btn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    });
    
    event.target.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
    event.target.classList.add('active', 'bg-rose-100', 'text-rose-700', 'border-rose-200');
    
    cards.forEach(function(card){
      if(type === 'all' || card.dataset.type === type){
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  };
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
