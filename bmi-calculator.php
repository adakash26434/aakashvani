<?php
/**
 * BMI Calculator - Calculate Body Mass Index
 * Helps determine if weight is healthy
 */
$pageTitle = 'BMI Calculator | आकाशवाणी';
$pageDesc  = 'Calculate your Body Mass Index (BMI) to check if your weight is healthy.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-green-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="activity" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">BMI Calculator</h1>
      <p class="text-[11px] text-slate-500">Body Mass Index गणना गर्नुस्</p>
    </div>
  </div>
</section>

<!-- BMI Calculator Card -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    
    <!-- Unit Toggle -->
    <div class="flex gap-2 mb-4">
      <button onclick="setUnit('metric')" id="metric-btn" class="flex-1 py-2 rounded-lg text-[12px] font-semibold bg-green-100 text-green-700 border border-green-200">
        Metric (kg/cm)
      </button>
      <button onclick="setUnit('imperial')" id="imperial-btn" class="flex-1 py-2 rounded-lg text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
        Imperial (lb/ft)
      </button>
    </div>
    
    <!-- Metric Inputs -->
    <div id="metric-inputs">
      <div class="mb-3">
        <label class="text-[11px] text-slate-600 mb-1 block">Weight (kg)</label>
        <input type="number" id="weight-kg" value="70" min="1" step="0.1"
          class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>
      <div class="mb-3">
        <label class="text-[11px] text-slate-600 mb-1 block">Height (cm)</label>
        <input type="number" id="height-cm" value="170" min="1" step="0.1"
          class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>
    </div>
    
    <!-- Imperial Inputs -->
    <div id="imperial-inputs" class="hidden">
      <div class="mb-3">
        <label class="text-[11px] text-slate-600 mb-1 block">Weight (lb)</label>
        <input type="number" id="weight-lb" value="154" min="1" step="0.1"
          class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>
      <div class="mb-3">
        <label class="text-[11px] text-slate-600 mb-1 block">Height (ft)</label>
        <input type="number" id="height-ft" value="5" min="1" step="1"
          class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>
      <div class="mb-3">
        <label class="text-[11px] text-slate-600 mb-1 block">Height (in)</label>
        <input type="number" id="height-in" value="7" min="0" max="11" step="1"
          class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>
    </div>
    
    <!-- Calculate Button -->
    <button onclick="calculateBMI()" id="calculate-btn"
      class="w-full bg-green-600 text-white font-bold py-3 rounded-xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform">
      <i data-lucide="calculator" class="w-5 h-5"></i>
      Calculate BMI
    </button>
  </div>
</section>

<!-- BMI Result -->
<section class="px-4 mb-4" id="result-section" style="display:none">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <div class="text-center mb-4">
      <p class="text-[12px] text-slate-500 mb-1">Your BMI</p>
      <p class="text-[48px] font-black text-slate-900" id="bmi-value">—</p>
      <p class="text-[14px] font-bold" id="bmi-category">—</p>
    </div>
    
    <!-- BMI Scale -->
    <div class="relative h-8 bg-gradient-to-r from-blue-400 via-green-400 via-yellow-400 to-red-400 rounded-full mb-3">
      <div id="bmi-indicator" class="absolute w-4 h-4 bg-white border-2 border-slate-900 rounded-full top-1/2 transform -translate-y-1/2 shadow-lg" style="left: 50%"></div>
    </div>
    
    <div class="flex justify-between text-[10px] text-slate-500 mb-4">
      <span>Underweight<br>&lt;18.5</span>
      <span>Normal<br>18.5-24.9</span>
      <span>Overweight<br>25-29.9</span>
      <span>Obese<br>&gt;30</span>
    </div>
    
    <!-- Health Tips -->
    <div id="health-tips" class="bg-slate-50 rounded-xl p-3">
      <p class="text-[11px] text-slate-700" id="tip-text"></p>
    </div>
  </div>
</section>

<!-- BMI Categories Info -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">BMI Categories</p>
    <div class="space-y-2">
      <div class="flex items-center justify-between p-2 bg-blue-50 rounded-lg">
        <span class="text-[12px] font-semibold text-blue-700">Underweight</span>
        <span class="text-[11px] text-blue-600">&lt; 18.5</span>
      </div>
      <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg">
        <span class="text-[12px] font-semibold text-green-700">Normal</span>
        <span class="text-[11px] text-green-600">18.5 - 24.9</span>
      </div>
      <div class="flex items-center justify-between p-2 bg-yellow-50 rounded-lg">
        <span class="text-[12px] font-semibold text-yellow-700">Overweight</span>
        <span class="text-[11px] text-yellow-600">25 - 29.9</span>
      </div>
      <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
        <span class="text-[12px] font-semibold text-red-700">Obese</span>
        <span class="text-[11px] text-red-600">&gt; 30</span>
      </div>
    </div>
  </div>
</section>

<!-- Info Note -->
<section class="px-4 mb-4">
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
      <div class="text-[11px] text-blue-800">
        <p class="font-semibold mb-1">About BMI</p>
        <p>BMI is a measure of body fat based on height and weight. It's a screening tool, not a diagnosis. Consult a healthcare provider for personalized advice.</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var currentUnit = 'metric';
  
  window.setUnit = function(unit){
    currentUnit = unit;
    
    var metricBtn = document.getElementById('metric-btn');
    var imperialBtn = document.getElementById('imperial-btn');
    var metricInputs = document.getElementById('metric-inputs');
    var imperialInputs = document.getElementById('imperial-inputs');
    
    if(unit === 'metric'){
      metricBtn.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
      metricBtn.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
      imperialBtn.classList.remove('bg-green-100', 'text-green-700', 'border-green-200');
      imperialBtn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
      metricInputs.classList.remove('hidden');
      imperialInputs.classList.add('hidden');
    } else {
      imperialBtn.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
      imperialBtn.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
      metricBtn.classList.remove('bg-green-100', 'text-green-700', 'border-green-200');
      metricBtn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
      imperialInputs.classList.remove('hidden');
      metricInputs.classList.add('hidden');
    }
  };
  
  window.calculateBMI = function(){
    var weight, height;
    
    if(currentUnit === 'metric'){
      weight = parseFloat(document.getElementById('weight-kg').value) || 0;
      height = parseFloat(document.getElementById('height-cm').value) || 0;
      height = height / 100; // Convert cm to meters
    } else {
      var weightLb = parseFloat(document.getElementById('weight-lb').value) || 0;
      var heightFt = parseFloat(document.getElementById('height-ft').value) || 0;
      var heightIn = parseFloat(document.getElementById('height-in').value) || 0;
      
      weight = weightLb * 0.453592; // Convert lb to kg
      height = ((heightFt * 12) + heightIn) * 0.0254; // Convert ft+in to meters
    }
    
    if(weight <= 0 || height <= 0){
      alert('Please enter valid weight and height');
      return;
    }
    
    var bmi = weight / (height * height);
    displayResult(bmi);
  };
  
  function displayResult(bmi){
    var resultSection = document.getElementById('result-section');
    var bmiValue = document.getElementById('bmi-value');
    var bmiCategory = document.getElementById('bmi-category');
    var indicator = document.getElementById('bmi-indicator');
    var tipText = document.getElementById('tip-text');
    
    bmiValue.textContent = bmi.toFixed(1);
    
    var category, color, tip, position;
    
    if(bmi < 18.5){
      category = 'Underweight';
      color = 'text-blue-600';
      tip = 'You are underweight. Consider eating nutrient-rich foods and consulting a dietitian.';
      position = 20;
    } else if(bmi < 25){
      category = 'Normal Weight';
      color = 'text-green-600';
      tip = 'Great! Your weight is healthy. Maintain with balanced diet and regular exercise.';
      position = 50;
    } else if(bmi < 30){
      category = 'Overweight';
      color = 'text-yellow-600';
      tip = 'You are slightly overweight. Consider increasing physical activity and reducing calorie intake.';
      position = 75;
    } else {
      category = 'Obese';
      color = 'text-red-600';
      tip = 'You are in the obese range. Please consult a healthcare provider for guidance.';
      position = 90;
    }
    
    bmiCategory.textContent = category;
    bmiCategory.className = 'text-[14px] font-bold ' + color;
    indicator.style.left = position + '%';
    tipText.textContent = tip;
    
    resultSection.style.display = 'block';
  }
  
  // Auto-calculate on input change
  var inputs = document.querySelectorAll('input[type="number"]');
  inputs.forEach(function(input){
    input.addEventListener('input', calculateBMI);
  });
  
  // Initial calculation
  calculateBMI();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
