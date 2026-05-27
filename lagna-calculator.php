<?php
/**
 * Lagna Calculator - Calculate Lagna (Ascendant) and Udaya Rashi
 * Takes birth date, time, and place to calculate the rising sign
 */
$pageTitle = 'Lagna Calculator | आकाशवाणी';
$pageDesc  = 'Calculate your Lagna (Ascendant) and Udaya Rashi based on birth date, time, and place.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main lg-wrap">

<!-- Header -->
<section class="lg-hero">
  <h1><i data-lucide="sparkles" class="w-6 h-6"></i>Lagna Calculator</h1>
  <p>जन्म मिति र समय दिनुस् — अनुमानित लग्न / उदय राशि देखाउँछ</p>
</section>

<!-- Calculator Card -->
<section class="lg-card">
  
  <!-- Birth Date -->
  <div class="mb-4">
    <label class="block text-[12px] font-semibold text-slate-700 mb-2">जन्म मिति</label>
    <input type="date" id="birth-date" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400">
  </div>

  <!-- Birth Time -->
  <div class="mb-4">
    <label class="block text-[12px] font-semibold text-slate-700 mb-2">जन्म समय</label>
    <input type="time" id="birth-time" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400">
  </div>

  <!-- Birth Place -->
  <div class="mb-4">
    <label class="block text-[12px] font-semibold text-slate-700 mb-2">जन्म स्थान</label>
    <input type="text" id="birth-place" placeholder="उदाहरण: काठमाडौं, पोखरा" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-400">
  </div>

  <!-- Calculate Button -->
  <button onclick="calculateLagna()" class="lg-btn w-full">
    <i data-lucide="calculator" class="w-5 h-5"></i>
    लग्न गणना गर्नुस्
  </button>
</section>

<!-- Result Card -->
<section class="lg-card lg-result" id="result-section">
  <div class="text-center mb-4">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center mb-3">
      <i data-lucide="sparkles" class="w-8 h-8 text-white"></i>
    </div>
    <h2 class="text-[18px] font-bold text-slate-800">तपाईंको लग्न</h2>
  </div>

  <!-- Lagna Result -->
  <div class="lg-result-item">
    <span class="lg-label">लग्न</span>
    <span class="lg-value" id="lagna-result">-</span>
  </div>

  <!-- Udaya Rashi Result -->
  <div class="lg-result-item">
    <span class="lg-label">उदय राशि</span>
    <span class="lg-value" id="udaya-rashi-result">-</span>
  </div>

  <!-- Lagna Lord -->
  <div class="lg-result-item">
    <span class="lg-label">लग्नेश</span>
    <span class="lg-value" id="lagnesh-result">-</span>
  </div>

  <!-- Info Note -->
  <div class="mt-4 p-3 bg-violet-50 border border-violet-100 rounded-xl">
    <p class="text-[11px] text-violet-700 leading-relaxed">
      <i data-lucide="info" class="w-4 h-4 inline-block mr-1"></i>
      यो अनुमानित गणना हो। सटीक लग्न जान्नको लागि ज्योतिषीसँग सल्लाह लिनुहोस्।
    </p>
  </div>
</section>

<script>
// Lagna (Ascendant) calculation based on birth time
const rashis = [
  {name: 'मेष', en: 'Aries', symbol: '♈', lord: 'मंगल'},
  {name: 'वृष', en: 'Taurus', symbol: '♉', lord: 'शुक्र'},
  {name: 'मिथुन', en: 'Gemini', symbol: '♊', lord: 'बुध'},
  {name: 'कर्कट', en: 'Cancer', symbol: '♋', lord: 'चन्द्रमा'},
  {name: 'सिंह', en: 'Leo', symbol: '♌', lord: 'सूर्य'},
  {name: 'कन्या', en: 'Virgo', symbol: '♍', lord: 'बुध'},
  {name: 'तुला', en: 'Libra', symbol: '♎', lord: 'शुक्र'},
  {name: 'वृश्चिक', en: 'Scorpio', symbol: '♏', lord: 'मंगल'},
  {name: 'धनु', en: 'Sagittarius', symbol: '♐', lord: 'गुरु'},
  {name: 'मकर', en: 'Capricorn', symbol: '♑', lord: 'शनि'},
  {name: 'कुम्भ', en: 'Aquarius', symbol: '♒', lord: 'शनि'},
  {name: 'मीन', en: 'Pisces', symbol: '♓', lord: 'गुरु'}
];

function calculateLagna() {
  const dateInput = document.getElementById('birth-date').value;
  const timeInput = document.getElementById('birth-time').value;
  const placeInput = document.getElementById('birth-place').value;

  if (!dateInput || !timeInput) {
    alert('कृपया जन्म मिति र समय दिनुहोस्');
    return;
  }

  // Parse date and time
  const birthDate = new Date(dateInput + 'T' + timeInput);
  const hours = birthDate.getHours();
  const minutes = birthDate.getMinutes();
  const totalMinutes = hours * 60 + minutes;

  // Calculate Lagna based on time (simplified calculation)
  // Each rashi rises approximately every 2 hours
  const rashiIndex = Math.floor(totalMinutes / 120) % 12;
  const lagna = rashis[rashiIndex];

  // Display results
  document.getElementById('lagna-result').textContent = lagna.name + ' (' + lagna.en + ') ' + lagna.symbol;
  document.getElementById('udaya-rashi-result').textContent = lagna.name + ' (' + lagna.en + ')';
  document.getElementById('lagnesh-result').textContent = lagna.lord;

  // Show result section
  document.getElementById('result-section').classList.add('show');

  if(window.lucide) lucide.createIcons();
}

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('birth-date').value = today;
  
  // Set default time to current time
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  document.getElementById('birth-time').value = hours + ':' + minutes;
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
