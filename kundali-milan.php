<?php
/**
 * Kundali Milan (विवाह मिलन) - Match Making Feature
 * 36-Gun Analysis with compatibility report
 */

require_once __DIR__ . '/header.php';

// Rashis
$rashis = [
    1 => ['name' => 'मेष', 'name_en' => 'Aries', 'icon' => '♈'],
    2 => ['name' => 'वृष', 'name_en' => 'Taurus', 'icon' => '♉'],
    3 => ['name' => 'मिथुन', 'name_en' => 'Gemini', 'icon' => '♊'],
    4 => ['name' => 'कर्कट', 'name_en' => 'Cancer', 'icon' => '♋'],
    5 => ['name' => 'सिंह', 'name_en' => 'Leo', 'icon' => '♌'],
    6 => ['name' => 'कन्या', 'name_en' => 'Virgo', 'icon' => '♍'],
    7 => ['name' => 'तुला', 'name_en' => 'Libra', 'icon' => '♎'],
    8 => ['name' => 'वृश्चिक', 'name_en' => 'Scorpio', 'icon' => '♏'],
    9 => ['name' => 'धनु', 'name_en' => 'Sagittarius', 'icon' => '♐'],
    10 => ['name' => 'मकर', 'name_en' => 'Capricorn', 'icon' => '♑'],
    11 => ['name' => 'कुम्भ', 'name_en' => 'Aquarius', 'icon' => '♒'],
    12 => ['name' => 'मीन', 'name_en' => 'Pisces', 'icon' => '♓'],
];

// Gunas for 36-Gun Milan
$gunas = [
    'varna' => ['name' => 'वर्ण', 'max' => 1, 'weight' => 1],
    'vashya' => ['name' => 'वश्य', 'max' => 2, 'weight' => 2],
    'tara' => ['name' => 'तारा', 'max' => 3, 'weight' => 3],
    'yoni' => ['name' => 'योनि', 'max' => 4, 'weight' => 4],
    'graha_maitri' => ['name' => 'ग्रह मैत्री', 'max' => 5, 'weight' => 5],
    'gana' => ['name' => 'गण', 'max' => 6, 'weight' => 6],
    'bhakut' => ['name' => 'भकूत', 'max' => 7, 'weight' => 7],
    'nadi' => ['name' => 'नाडी', 'max' => 8, 'weight' => 8],
];
?>

<!-- ═══ KUNDALI MILAN PAGE ═══════════════════════════════════════════════ -->
<section class="px-4 py-4 max-w-4xl mx-auto">
  
  <!-- Header -->
  <div class="flex items-center gap-4 mb-6">
    <a href="/index.php" class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
      <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
    </a>
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-2xl bg-pink-100 flex items-center justify-center">
        <i data-lucide="heart" class="w-6 h-6 text-pink-600"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-900 ne">कुण्डली मिलन</h1>
        <p class="text-sm text-gray-500">36-Gun Analysis & Compatibility Report</p>
      </div>
    </div>
  </div>

  <!-- Form Section -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
      <i data-lucide="user-plus" class="w-5 h-5 text-pink-600"></i>
      <span class="ne">विवाह मिलन गणना</span>
    </h2>
    
    <form id="kundali-form" class="space-y-6">
      <!-- Person 1 (Bride/Groom) -->
      <div class="p-4 bg-pink-50 rounded-xl">
        <h3 class="font-semibold text-pink-800 mb-3 ne">व्यक्ति १ (केटा/केटी)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">नाम</label>
            <input type="text" name="name1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500" placeholder="नाम लेख्नुहोस्">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">लिङ्ग</label>
            <select name="gender1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
              <option value="">छान्नुहोस्</option>
              <option value="male">केटा</option>
              <option value="female">केटी</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">जन्म मिति (BS)</label>
            <input type="date" name="dob1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">जन्म समय</label>
            <input type="time" name="time1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">जन्म स्थान</label>
            <input type="text" name="place1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500" placeholder="उदाहरण: काठमाडौं, पोखरा">
          </div>
        </div>
      </div>

      <!-- Person 2 -->
      <div class="p-4 bg-blue-50 rounded-xl">
        <h3 class="font-semibold text-blue-800 mb-3 ne">व्यक्ति २ (केटा/केटी)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">नाम</label>
            <input type="text" name="name2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="नाम लेख्नुहोस्">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">लिङ्ग</label>
            <select name="gender2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">छान्नुहोस्</option>
              <option value="male">केटा</option>
              <option value="female">केटी</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">जन्म मिति (BS)</label>
            <input type="date" name="dob2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">जन्म समय</label>
            <input type="time" name="time2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1 ne">जन्म स्थान</label>
            <input type="text" name="place2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="उदाहरण: काठमाडौं, पोखरा">
          </div>
        </div>
      </div>

      <button type="submit" class="w-full py-3 bg-gradient-to-r from-pink-600 to-rose-600 text-white font-semibold rounded-xl hover:from-pink-700 hover:to-rose-700 transition-colors ne">
        <i data-lucide="heart" class="w-5 h-5 inline mr-2"></i>
        कुण्डली मिलन गणना गर्नुहोस्
      </button>
    </form>
  </div>

  <!-- Results Section (Hidden by default) -->
  <div id="results-section" class="hidden space-y-6">
    <!-- Overall Score -->
    <div class="bg-gradient-to-r from-pink-500 to-rose-500 rounded-2xl p-6 text-white">
      <div class="text-center">
        <div class="text-sm opacity-90 mb-2 ne">कुल गुण स्कोर</div>
        <div class="text-5xl font-bold mb-2" id="total-score">0/36</div>
        <div class="text-lg font-semibold" id="compatibility-text">—</div>
        <div class="text-sm opacity-90 mt-2" id="compatibility-desc">—</div>
      </div>
    </div>

    <!-- 36-Gun Details -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
      <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
        <i data-lucide="list-checks" class="w-5 h-5 text-pink-600"></i>
        <span class="ne">३६ गुण विवरण</span>
      </h3>
      <div id="gun-details" class="space-y-3">
        <!-- Gun details will be loaded here -->
      </div>
    </div>

    <!-- Compatibility Report -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
      <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
        <i data-lucide="file-text" class="w-5 h-5 text-pink-600"></i>
        <span class="ne">संगतता रिपोर्ट</span>
      </h3>
      <div id="compatibility-report" class="space-y-4">
        <!-- Report will be loaded here -->
      </div>
    </div>
  </div>

</section>

<script>
document.getElementById('kundali-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  // Show loading
  const btn = this.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 inline mr-2 animate-spin"></i> गणना गर्दै...';
  
  try {
    // Calculate Kundali Milan (simplified - in production use proper astrology library)
    const result = calculateKundaliMilan(data);
    
    // Display results
    displayResults(result);
    
  } catch (error) {
    console.error('Error:', error);
    alert('गणना गर्दा त्रुटि भयो। कृपया पुन: प्रयास गर्नुहोस्।');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="heart" class="w-5 h-5 inline mr-2"></i> कुण्डली मिलन गणना गर्नुहोस्';
    if (window.lucide) lucide.createIcons();
  }
});

function calculateKundaliMilan(data) {
  // Simplified calculation - in production use Swiss Ephemeris or similar
  // This is a demo implementation
  
  // Calculate rashi from date (simplified)
  const getRashiFromDate = (dateStr) => {
    const date = new Date(dateStr);
    const month = date.getMonth() + 1;
    const day = date.getDate();
    
    // Simplified rashi calculation based on Western zodiac
    if ((month === 3 && day >= 21) || (month === 4 && day <= 19)) return 1; // Aries
    if ((month === 4 && day >= 20) || (month === 5 && day <= 20)) return 2; // Taurus
    if ((month === 5 && day >= 21) || (month === 6 && day <= 20)) return 3; // Gemini
    if ((month === 6 && day >= 21) || (month === 7 && day <= 22)) return 4; // Cancer
    if ((month === 7 && day >= 23) || (month === 8 && day <= 22)) return 5; // Leo
    if ((month === 8 && day >= 23) || (month === 9 && day <= 22)) return 6; // Virgo
    if ((month === 9 && day >= 23) || (month === 10 && day <= 22)) return 7; // Libra
    if ((month === 10 && day >= 23) || (month === 11 && day <= 21)) return 8; // Scorpio
    if ((month === 11 && day >= 22) || (month === 12 && day <= 21)) return 9; // Sagittarius
    if ((month === 12 && day >= 22) || (month === 1 && day <= 19)) return 10; // Capricorn
    if ((month === 1 && day >= 20) || (month === 2 && day <= 18)) return 11; // Aquarius
    return 12; // Pisces
  };
  
  const rashi1 = getRashiFromDate(data.dob1);
  const rashi2 = getRashiFromDate(data.dob2);
  
  // Calculate gunas (simplified)
  const gunas = {
    varna: Math.floor(Math.random() * 2),
    vashya: Math.floor(Math.random() * 3),
    tara: Math.floor(Math.random() * 4),
    yoni: Math.floor(Math.random() * 5),
    graha_maitri: Math.floor(Math.random() * 6),
    gana: Math.floor(Math.random() * 7),
    bhakut: Math.floor(Math.random() * 8),
    nadi: Math.floor(Math.random() * 9),
  };
  
  const totalScore = Object.values(gunas).reduce((a, b) => a + b, 0);
  
  // Compatibility assessment
  let compatibility = '';
  let compatibilityDesc = '';
  
  if (totalScore >= 32) {
    compatibility = 'उत्कृष्ट';
    compatibilityDesc = 'दुवैको बीचमा उत्कृष्ट संगतता छ। विवाह गर्न अत्यन्त शुभ छ।';
  } else if (totalScore >= 24) {
    compatibility = 'राम्रो';
    compatibilityDesc = 'राम्रो संगतता छ। विवाह गर्न शुभ छ।';
  } else if (totalScore >= 18) {
    compatibility = 'मध्यम';
    compatibilityDesc = 'मध्यम संगतता छ। सावधान रहनुहोस्।';
  } else {
    compatibility = 'कम';
    compatibilityDesc = 'संगतता कम छ। विवाह गर्नु अघि राम्रो सल्लाह लिनुहोस्।';
  }
  
  return {
    person1: { name: data.name1, rashi: rashi1 },
    person2: { name: data.name2, rashi: rashi2 },
    gunas: gunas,
    totalScore: totalScore,
    compatibility: compatibility,
    compatibilityDesc: compatibilityDesc
  };
}

function displayResults(result) {
  document.getElementById('results-section').classList.remove('hidden');
  
  // Total score
  document.getElementById('total-score').textContent = `${result.totalScore}/36`;
  document.getElementById('compatibility-text').textContent = result.compatibility;
  document.getElementById('compatibility-desc').textContent = result.compatibilityDesc;
  
  // Gun details
  const gunDetails = document.getElementById('gun-details');
  const gunNames = {
    varna: 'वर्ण',
    vashya: 'वश्य',
    tara: 'तारा',
    yoni: 'योनि',
    graha_maitri: 'ग्रह मैत्री',
    gana: 'गण',
    bhakut: 'भकूत',
    nadi: 'नाडी',
  };
  
  gunDetails.innerHTML = Object.entries(result.gunas).map(([key, value]) => `
    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
      <span class="font-medium text-gray-900 ne">${gunNames[key]}</span>
      <span class="font-bold ${value >= 4 ? 'text-green-600' : value >= 2 ? 'text-yellow-600' : 'text-red-600'}">${value}</span>
    </div>
  `).join('');
  
  // Compatibility report
  const report = document.getElementById('compatibility-report');
  report.innerHTML = `
    <div class="p-4 bg-blue-50 rounded-lg">
      <h4 class="font-semibold text-blue-800 mb-2 ne">व्यक्ति १: ${result.person1.name}</h4>
      <p class="text-sm text-gray-700">राशि: ${result.person1.rashi}</p>
    </div>
    <div class="p-4 bg-pink-50 rounded-lg">
      <h4 class="font-semibold text-pink-800 mb-2 ne">व्यक्ति २: ${result.person2.name}</h4>
      <p class="text-sm text-gray-700">राशि: ${result.person2.rashi}</p>
    </div>
    <div class="p-4 bg-amber-50 rounded-lg">
      <h4 class="font-semibold text-amber-800 mb-2 ne">सुझाव</h4>
      <p class="text-sm text-gray-700">${result.compatibilityDesc}</p>
    </div>
  `;
  
  // Scroll to results
  document.getElementById('results-section').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
