<?php
/**
 * Enhanced Nepali Calendar (Patro) with Complete Jyotish Features
 * Includes: Panchang, Subh/Asubh times, Rashifal, Festivals, Holidays
 */

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/bs-date.php'; // Nepali date functions

// Get current Nepali date
$todayBS = getTodayBS(); // Function from bs-date.php
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : $todayBS['year'];
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : $todayBS['month'];
$selectedDate = isset($_GET['date']) ? intval($_GET['date']) : $todayBS['day'];

// Nepali months
$nepaliMonths = [
    1 => 'बैशाख', 2 => 'जेठ', 3 => 'आषाढ़', 4 => 'श्रावण',
    5 => 'भाद्र', 6 => 'आश्विन', 7 => 'कार्तिक', 8 => 'मंसिर',
    9 => 'पुष', 10 => 'माघ', 11 => 'फाल्गुन', 12 => 'चैत्र'
];

// Week days in Nepali
$weekDays = [
    'Sunday' => 'आइत', 'Monday' => 'सोम', 'Tuesday' => 'मंगल',
    'Wednesday' => 'बुध', 'Thursday' => 'बिहि', 'Friday' => 'शुक्र', 'Saturday' => 'शनि'
];

// Rashis
$rashis = [
    1 => ['name' => 'मेष', 'icon' => '♈'], 2 => ['name' => 'वृष', 'icon' => '♉'],
    3 => ['name' => 'मिथुन', 'icon' => '♊'], 4 => ['name' => 'कर्कट', 'icon' => '♋'],
    5 => ['name' => 'सिंह', 'icon' => '♌'], 6 => ['name' => 'कन्या', 'icon' => '♍'],
    7 => ['name' => 'तुला', 'icon' => '♎'], 8 => ['name' => 'वृश्चिक', 'icon' => '♏'],
    9 => ['name' => 'धनु', 'icon' => '♐'], 10 => ['name' => 'मकर', 'icon' => '♑'],
    11 => ['name' => 'कुम्भ', 'icon' => '♒'], 12 => ['name' => 'मीन', 'icon' => '♓']
];

/**
 * Calculate Panchang for a given date
 */
function calculatePanchang($date, $month, $year) {
    // This is a simplified calculation - real panchang requires complex astronomical calculations
    // For production, integrate with Swiss Ephemeris or similar library
    
    $tithis = ['प्रतिपदा', 'द्वितीया', 'तृतीया', 'चतुर्थी', 'पंचमी', 'षष्ठी', 'सप्तमी', 
               'अष्टमी', 'नवमी', 'दशमी', 'एकादशी', 'द्वादशी', 'त्रयोदशी', 'चतुर्दशी', 'पूर्णिमा/अमावस्या'];
    $nakshatras = ['अश्विनी', 'भरणी', 'कृत्तिका', 'रोहिणी', 'मृगशिरा', 'आर्द्रा', 'पुनर्वसु', 
                   'पुष्य', 'आश्लेषा', 'मघा', 'पूर्वाफाल्गुनी', 'उत्तराफाल्गुनी', 'हस्त', 'चित्रा',
                   'स्वाति', 'विशाखा', 'अनुराधा', 'ज्येष्ठा', 'मूल', 'पूर्वाषाढ़ा', 'उत्तराषाढ़ा',
                   'श्रवण', 'धनिष्ठा', 'शतभिषा', 'पूर्वाभाद्रपदा', 'उत्तराभाद्रपदा', 'रेवती'];
    $yogs = ['विष्कुम्भ', 'प्रीति', 'आयुष्मान', 'सौभाग्य', 'शोभन', 'अतिगण्ड', 'सुकर्मा', 
             'धृति', 'शूल', 'गण्ड', 'वृद्धि', 'ध्रुव', 'व्याघात', 'हर्षण', 'वज्र',
             'सिद्धि', 'व्यतीपात', 'वरीयान', 'परिघ', 'शिव', 'सिद्ध', 'साध्य', 
             'शुभ', 'शुक्ल', 'ब्रह्म', 'इन्द्र', 'वैधृति'];
    $karans = ['बव', 'बालव', 'कौलव', 'तैतिल', 'गर', 'वणिज', 'विष्टि', 'शकुनि', 
               'चतुष्पद', 'नाग', 'किंस्तुघ्न'];
    
    // Simplified calculation - use day of month for demo
    $dayOfYear = ($month - 1) * 30 + $date;
    
    return [
        'tithi' => $tithis[($dayOfYear) % 15],
        'nakshatra' => $nakshatras[($dayOfYear) % 27],
        'yog' => $yogs[($dayOfYear) % 27],
        'karan' => $karans[($dayOfYear) % 11],
        'paksha' => ($date <= 15) ? 'शुक्ल पक्ष' : 'कृष्ण पक्ष',
        'moon_phase' => ($date <= 15) ? 'वर्धमान' : 'क्षयशील'
    ];
}

/**
 * Calculate Subh/Asubh times
 */
function calculateTimings($weekDay, $date, $month, $year, $sunrise = '05:30', $sunset = '18:30') {
    // Convert times to minutes for calculation
    list($sunriseH, $sunriseM) = explode(':', $sunrise);
    list($sunsetH, $sunsetM) = explode(':', $sunset);
    $sunriseMin = $sunriseH * 60 + $sunriseM;
    $sunsetMin = $sunsetH * 60 + $sunsetM;
    $dayDuration = $sunsetMin - $sunriseMin;
    
    // Rahu Kal (1.5 hours - inauspicious)
    $rahuStart = [
        'Sunday' => 16, 'Monday' => 7, 'Tuesday' => 14, 'Wednesday' => 12,
        'Thursday' => 13, 'Friday' => 10, 'Saturday' => 9
    ];
    
    // Yamaghanta Kal (1.5 hours - inauspicious)
    $yamStart = [
        'Sunday' => 12, 'Monday' => 10, 'Tuesday' => 9, 'Wednesday' => 7,
        'Thursday' => 14, 'Friday' => 16, 'Saturday' => 13
    ];
    
    // Gulik Kal (1.5 hours - inauspicious)
    $gulikStart = [
        'Sunday' => 7, 'Monday' => 14, 'Tuesday' => 12, 'Wednesday' => 13,
        'Thursday' => 10, 'Friday' => 9, 'Saturday' => 16
    ];
    
    // Abhijit Muhurt (48 minutes - most auspicious)
    $abhijitStart = 12; // 12th muhurt from sunrise
    $muhurtDuration = $dayDuration / 15;
    $abhijitMin = $sunriseMin + ($abhijitStart - 1) * $muhurtDuration;
    
    $rahuDay = $rahuStart[$weekDay] ?? 7;
    $yamDay = $yamStart[$weekDay] ?? 7;
    $gulikDay = $gulikStart[$weekDay] ?? 7;
    
    function formatTime($minutes) {
        $h = floor($minutes / 60) % 24;
        $m = floor($minutes % 60);
        return sprintf('%02d:%02d', $h, $m);
    }
    
    return [
        'abhijit_muhurt' => [
            'start' => formatTime($abhijitMin),
            'end' => formatTime($abhijitMin + 48),
            'type' => 'shubh',
            'name' => 'अभिजित मुहूर्त',
            'desc' => 'सबैभन्दा शुभ समय - जेसुकै कार्य गर्न'
        ],
        'rahu_kal' => [
            'start' => formatTime($sunriseMin + ($rahuDay - 1) * $muhurtDuration),
            'end' => formatTime($sunriseMin + $rahuDay * $muhurtDuration),
            'type' => 'ashubh',
            'name' => 'राहु काल',
            'desc' => 'अशुभ समय - नयाँ कार्य नगर्ने'
        ],
        'yamaghanta' => [
            'start' => formatTime($sunriseMin + ($yamDay - 1) * $muhurtDuration),
            'end' => formatTime($sunriseMin + $yamDay * $muhurtDuration),
            'type' => 'ashubh',
            'name' => 'यमघण्ट काल',
            'desc' => 'अशुभ समय - यात्रा नगर्ने'
        ],
        'gulik_kal' => [
            'start' => formatTime($sunriseMin + ($gulikDay - 1) * $muhurtDuration),
            'end' => formatTime($sunriseMin + $gulikDay * $muhurtDuration),
            'type' => 'ashubh',
            'name' => 'गुलिक काल',
            'desc' => 'अशुभ समय - महत्त्वपूर्ण निर्णय नलिने'
        ]
    ];
}

/**
 * Get festivals and holidays
 */
function getFestivals($month, $date) {
    $festivals = [
        // Baishakh (1)
        '1-1' => ['name' => 'नेपाली नयाँ वर्ष', 'type' => 'public', 'holiday' => true],
        '1-11' => ['name' => 'लोकतन्त्र दिवस', 'type' => 'public', 'holiday' => true],
        
        // Jestha (2)
        '2-15' => ['name' => 'गणतन्त्र दिवस', 'type' => 'public', 'holiday' => true],
        
        // Ashadh (3)
        '3-15' => ['name' => 'राष्ट्रिय धान दिवस', 'type' => 'agriculture', 'holiday' => false],
        
        // Shrawan (4)
        '4-1' => ['name' => 'साउने संक्रान्ति', 'type' => 'religious', 'holiday' => false],
        '4-15' => ['name' => 'नाग पञ्चमी', 'type' => 'religious', 'holiday' => false],
        
        // Bhadra (5)
        '5-15' => ['name' => 'जनै पूर्णिमा/गाईजात्रा', 'type' => 'religious', 'holiday' => true],
        '5-25' => ['name' => 'श्रीकृष्ण जन्माष्टमी', 'type' => 'religious', 'holiday' => true],
        
        // Ashwin (6)
        '6-1' => ['name' => 'हरितालिका तीज', 'type' => 'religious', 'holiday' => true],
        '6-15' => ['name' => 'इन्द्रजात्रा/ऋषिपञ्चमी', 'type' => 'religious', 'holiday' => true],
        '6-30' => ['name' => 'घटस्थापना (दशैं सुरु)', 'type' => 'religious', 'holiday' => true],
        
        // Kartik (7)
        '7-1' => ['name' => 'फूलपाती', 'type' => 'religious', 'holiday' => true],
        '7-7' => ['name' => 'महानवमी', 'type' => 'religious', 'holiday' => true],
        '7-8' => ['name' => 'विजयादशमी (दशैं)', 'type' => 'religious', 'holiday' => true],
        '7-15' => ['name' => 'कोजाग्रत पूर्णिमा', 'type' => 'religious', 'holiday' => false],
        
        // Mangsir (8)
        '8-1' => ['name' => 'लक्ष्मी पूजा/तिहार', 'type' => 'religious', 'holiday' => true],
        '8-3' => ['name' => 'भाइतिहार/गोवर्धन पूजा', 'type' => 'religious', 'holiday' => true],
        '8-15' => ['name' => 'चठ पर्व', 'type' => 'religious', 'holiday' => true],
        
        // Push (9)
        '9-1' => ['name' => 'उधौली/योमरी पूर्णिमा', 'type' => 'religious', 'holiday' => true],
        '9-15' => ['name' => 'तमु लhosar', 'type' => 'religious', 'holiday' => true],
        
        // Magh (10)
        '10-1' => ['name' => 'माघे संक्रान्ति', 'type' => 'religious', 'holiday' => true],
        '10-10' => ['name' => 'शहीद दिवस', 'type' => 'public', 'holiday' => true],
        '10-30' => ['name' => 'सोनाम लhosar', 'type' => 'religious', 'holiday' => true],
        
        // Falgun (11)
        '11-1' => ['name' => 'प्रजातन्त्र दिवस', 'type' => 'public', 'holiday' => true],
        '11-15' => ['name' => 'महाशिवरात्रि', 'type' => 'religious', 'holiday' => true],
        '11-20' => ['name' => 'फागु पूर्णिमा (होली)', 'type' => 'religious', 'holiday' => true],
        
        // Chaitra (12)
        '12-8' => ['name' => 'घोडे जात्रा', 'type' => 'religious', 'holiday' => false],
        '12-15' => ['name' => 'रामनवमी', 'type' => 'religious', 'holiday' => true],
        '12-30' => ['name' => 'चैते दशैं', 'type' => 'religious', 'holiday' => true],
    ];
    
    $key = $month . '-' . $date;
    return $festivals[$key] ?? null;
}

/**
 * Check special days
 */
function getSpecialDay($date, $month, $panchang) {
    $special = [];
    
    // Ekadashi
    if ($panchang['tithi'] == 'एकादशी') {
        $special[] = ['type' => 'ekadashi', 'name' => 'एकादशी व्रत', 'color' => '#f59e0b'];
    }
    
    // Purnima
    if ($date == 15 || $panchang['tithi'] == 'पूर्णिमा/अमावस्या') {
        if ($panchang['paksha'] == 'शुक्ल पक्ष') {
            $special[] = ['type' => 'purnima', 'name' => 'पूर्णिमा', 'color' => '#ec4899'];
        }
    }
    
    // Amavasya
    if ($date == 30 || ($date == 15 && $panchang['paksha'] == 'कृष्ण पक्ष')) {
        $special[] = ['type' => 'amavasya', 'name' => 'अमावस्या', 'color' => '#64748b'];
    }
    
    return $special;
}

// Calculate for selected date
$selectedPanchang = calculatePanchang($selectedDate, $currentMonth, $currentYear);
$selectedTimings = calculateTimings(date('l'), $selectedDate, $currentMonth, $currentYear);
$selectedFestival = getFestivals($currentMonth, $selectedDate);
$selectedSpecial = getSpecialDay($selectedDate, $currentMonth, $selectedPanchang);

?>

<!-- ═══ ENHANCED NEPALI CALENDAR ═══════════════════════════════════════════════ -->
<section class="px-4 py-4 max-w-4xl mx-auto">
  
  <!-- Header -->
  <div class="flex items-center gap-4 mb-6">
    <a href="/index.php" class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
      <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
    </a>
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center">
        <i data-lucide="calendar-days" class="w-6 h-6 text-amber-600"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-900 ne">नेपाली पात्रो</h1>
        <p class="text-sm text-gray-500">Complete with Jyotish Features</p>
      </div>
    </div>
  </div>

  <!-- Main Calendar Card -->
  <div class="bg-gradient-to-br from-teal-600 to-emerald-700 rounded-3xl p-6 text-white shadow-xl shadow-teal-200 mb-6">
    
    <!-- Month Navigation -->
    <div class="flex justify-between items-center mb-6">
      <a href="?month=<?= $currentMonth == 1 ? 12 : $currentMonth - 1 ?>&year=<?= $currentMonth == 1 ? $currentYear - 1 : $currentYear ?>&date=<?= $selectedDate ?>" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
        <i data-lucide="chevron-left" class="w-6 h-6"></i>
      </a>
      <div class="text-center">
        <div class="text-2xl font-bold ne"><?= $nepaliMonths[$currentMonth] ?> <?= $currentYear ?></div>
        <a href="?month=<?= $todayBS['month'] ?>&year=<?= $todayBS['year'] ?>&date=<?= $todayBS['day'] ?>" class="text-sm text-teal-200 hover:text-white underline">आज</a>
      </div>
      <a href="?month=<?= $currentMonth == 12 ? 1 : $currentMonth + 1 ?>&year=<?= $currentMonth == 12 ? $currentYear + 1 : $currentYear ?>&date=<?= $selectedDate ?>" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
        <i data-lucide="chevron-right" class="w-6 h-6"></i>
      </a>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white/10 rounded-2xl p-4 mb-6">
      <!-- Weekday Headers -->
      <div class="grid grid-cols-7 gap-1 mb-2">
        <?php foreach(['आइत','सोम','मंगल','बुध','बिहि','शुक्र','शनि'] as $day): ?>
          <div class="text-center text-xs font-semibold text-teal-100 py-2"><?= $day ?></div>
        <?php endforeach; ?>
      </div>
      
      <!-- Days Grid -->
      <div class="grid grid-cols-7 gap-1">
        <?php
        // Get days in month (simplified - Nepali months vary)
        $daysInMonth = [0,31,32,31,32,31,30,30,30,29,30,29,30,30][$currentMonth] ?? 30;
        
        // Start day offset (simplified)
        $startDay = ($currentMonth + $currentYear) % 7;
        
        // Empty cells before first day
        for($i = 0; $i < $startDay; $i++): ?>
          <div class="aspect-square"></div>
        <?php endfor;
        
        // Days
        for($day = 1; $day <= $daysInMonth; $day++):
          $isSelected = $day == $selectedDate;
          $isToday = $day == $todayBS['day'] && $currentMonth == $todayBS['month'] && $currentYear == $todayBS['year'];
          $dayPanchang = calculatePanchang($day, $currentMonth, $currentYear);
          $dayFestival = getFestivals($currentMonth, $day);
        ?>
          <a href="?month=<?= $currentMonth ?>&year=<?= $currentYear ?>&date=<?= $day ?>" 
             class="aspect-square rounded-lg flex flex-col items-center justify-center transition-all
                    <?= $isSelected ? 'bg-white text-teal-700 font-bold' : 'hover:bg-white/20' ?>
                    <?= $isToday ? 'ring-2 ring-amber-300' : '' ?>">
            <span class="text-sm"><?= $day ?></span>
            <?php if($dayFestival): ?>
              <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1"></span>
            <?php elseif($dayPanchang['tithi'] == 'एकादशी' || $dayPanchang['tithi'] == 'पूर्णिमा/अमावस्या'): ?>
              <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1"></span>
            <?php endif; ?>
          </a>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Selected Date Display -->
    <div class="text-center mb-4">
      <div class="text-5xl font-bold ne"><?= $selectedDate ?></div>
      <div class="text-lg text-teal-100 ne"><?= $weekDays[date('l')] ?></div>
      <div class="mt-2 inline-flex items-center gap-2 bg-white/20 px-4 py-2 rounded-full">
        <i data-lucide="calendar" class="w-4 h-4"></i>
        <span><?= date('d F Y') ?> AD</span>
      </div>
    </div>

    <!-- Sunrise/Sunset -->
    <div class="flex justify-center gap-4">
      <div class="flex items-center gap-2 bg-white/10 px-3 py-2 rounded-xl">
        <i data-lucide="sunrise" class="w-4 h-4 text-amber-300"></i>
        <div class="text-xs">
          <div class="text-teal-100 ne">सूर्योदय</div>
          <div class="font-semibold">5:08 AM</div>
        </div>
      </div>
      <div class="flex items-center gap-2 bg-white/10 px-3 py-2 rounded-xl">
        <i data-lucide="sunset" class="w-4 h-4 text-orange-300"></i>
        <div class="text-xs">
          <div class="text-teal-100 ne">सूर्यास्त</div>
          <div class="font-semibold">6:53 PM</div>
        </div>
      </div>
      <div class="flex items-center gap-2 bg-white/10 px-3 py-2 rounded-xl">
        <i data-lucide="moon" class="w-4 h-4 text-yellow-300"></i>
        <div class="text-xs">
          <div class="text-teal-100 ne">चन्द्रमा</div>
          <div class="font-semibold"><?= $selectedPanchang['paksha'] ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Special Day / Festival Alert -->
  <?php if ($selectedFestival || !empty($selectedSpecial)): ?>
  <div class="mb-6 space-y-3">
    <?php if ($selectedFestival): ?>
    <div class="bg-gradient-to-r from-rose-500 to-pink-600 rounded-2xl p-4 text-white shadow-lg">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
          <i data-lucide="sparkles" class="w-6 h-6"></i>
        </div>
        <div>
          <div class="text-sm opacity-90"><?= $selectedFestival['type'] == 'public' ? 'सार्वजनिक बिदा' : 'धार्मिक पर्व' ?></div>
          <div class="text-xl font-bold ne"><?= $selectedFestival['name'] ?></div>
          <?php if ($selectedFestival['holiday']): ?>
          <span class="inline-block mt-1 px-2 py-1 bg-white/20 rounded text-xs">बिदा</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <?php foreach ($selectedSpecial as $special): ?>
    <div class="rounded-xl p-4 text-white shadow-md" style="background: <?= $special['color'] ?>">
      <div class="flex items-center gap-2">
        <i data-lucide="star" class="w-5 h-5"></i>
        <span class="font-semibold ne"><?= $special['name'] ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Panchang Details -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="book-open" class="w-5 h-5 text-amber-600"></i>
      <span class="ne">पञ्चाङ्ग</span>
    </h3>
    
    <div class="grid grid-cols-2 gap-4">
      <div class="p-3 bg-amber-50 rounded-xl">
        <div class="text-xs text-amber-600 mb-1 ne">तिथि</div>
        <div class="font-semibold text-gray-900 ne"><?= $selectedPanchang['tithi'] ?></div>
      </div>
      <div class="p-3 bg-indigo-50 rounded-xl">
        <div class="text-xs text-indigo-600 mb-1 ne">नक्षत्र</div>
        <div class="font-semibold text-gray-900 ne"><?= $selectedPanchang['nakshatra'] ?></div>
      </div>
      <div class="p-3 bg-emerald-50 rounded-xl">
        <div class="text-xs text-emerald-600 mb-1 ne">योग</div>
        <div class="font-semibold text-gray-900 ne"><?= $selectedPanchang['yog'] ?></div>
      </div>
      <div class="p-3 bg-rose-50 rounded-xl">
        <div class="text-xs text-rose-600 mb-1 ne">करण</div>
        <div class="font-semibold text-gray-900 ne"><?= $selectedPanchang['karan'] ?></div>
      </div>
    </div>
    
    <div class="mt-4 p-3 bg-gray-50 rounded-xl">
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600 ne">चन्द्र पक्ष:</span>
        <span class="font-semibold text-gray-900 ne"><?= $selectedPanchang['paksha'] ?> (<?= $selectedPanchang['moon_phase'] ?>)</span>
      </div>
    </div>
  </div>

  <!-- Subh/Asubh Times -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="clock" class="w-5 h-5 text-emerald-600"></i>
      <span class="ne">शुभ/अशुभ समय</span>
    </h3>
    
    <!-- Abhijit Muhurt (Shubh) -->
    <div class="p-4 bg-emerald-50 rounded-xl mb-3 border-l-4 border-emerald-500">
      <div class="flex justify-between items-center">
        <div>
          <div class="font-bold text-emerald-800 ne"><?= $selectedTimings['abhijit_muhurt']['name'] ?></div>
          <div class="text-sm text-emerald-600"><?= $selectedTimings['abhijit_muhurt']['desc'] ?></div>
        </div>
        <div class="text-right">
          <div class="text-lg font-bold text-emerald-700"><?= $selectedTimings['abhijit_muhurt']['start'] ?> - <?= $selectedTimings['abhijit_muhurt']['end'] ?></div>
          <div class="text-xs text-emerald-600 ne">शुभ मुहूर्त</div>
        </div>
      </div>
    </div>
    
    <!-- Asubh Times -->
    <div class="space-y-2">
      <?php foreach (['rahu_kal', 'yamaghanta', 'gulik_kal'] as $timing): ?>
      <div class="p-3 bg-red-50 rounded-xl border-l-4 border-red-400">
        <div class="flex justify-between items-center">
          <div>
            <div class="font-semibold text-red-800 ne"><?= $selectedTimings[$timing]['name'] ?></div>
            <div class="text-xs text-red-600"><?= $selectedTimings[$timing]['desc'] ?></div>
          </div>
          <div class="text-right">
            <div class="font-semibold text-red-700"><?= $selectedTimings[$timing]['start'] ?> - <?= $selectedTimings[$timing]['end'] ?></div>
            <div class="text-xs text-red-600 ne">अशुभ</div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Daily Rashifal -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="sparkles" class="w-5 h-5 text-purple-600"></i>
      <span class="ne">आजको राशिफल</span>
      <span class="text-xs text-gray-500 ml-auto"><?= date('d M Y') ?></span>
    </h3>
    
    <div class="grid grid-cols-3 gap-3">
      <?php foreach (array_slice($rashis, 0, 6, true) as $num => $rashi): ?>
      <a href="/rashifal.php?rashi=<?= $num ?>" class="p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors text-center">
        <div class="text-2xl mb-1"><?= $rashi['icon'] ?></div>
        <div class="text-sm font-medium text-gray-700 ne"><?= $rashi['name'] ?></div>
        <div class="text-xs text-purple-600 mt-1">हेर्नुहोस् →</div>
      </a>
      <?php endforeach; ?>
    </div>
    
    <a href="/rashifal.php" class="mt-4 block text-center py-3 bg-purple-100 rounded-xl text-purple-700 font-medium hover:bg-purple-200 transition-colors ne">
      सबै १२ राशिको राशिफल हेर्नुहोस्
    </a>
  </div>

  <!-- Public Holidays Quick View -->
  <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl p-5 border border-orange-100">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="calendar-check" class="w-5 h-5 text-orange-600"></i>
      <span class="ne">आगामी बिदाहरू</span>
    </h3>
    
    <div class="space-y-2">
      <?php 
      $upcomingHolidays = [
        ['date' => '१५ असोज', 'name' => 'घटस्थापना', 'days' => '४ दिन'],
        ['date' => '२२ असोज', 'name' => 'फूलपाती', 'days' => '११ दिन'],
        ['date' => '२८ असोज', 'name' => 'महानवमी', 'days' => '१७ दिन'],
        ['date' => '२९ असोज', 'name' => 'विजयादशमी', 'days' => '१८ दिन'],
      ];
      foreach ($upcomingHolidays as $holiday):
      ?>
      <div class="flex justify-between items-center p-3 bg-white rounded-xl">
        <div>
          <div class="font-semibold text-gray-900 ne"><?= $holiday['name'] ?></div>
          <div class="text-sm text-gray-500"><?= $holiday['date'] ?></div>
        </div>
        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm ne"><?= $holiday['days'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
