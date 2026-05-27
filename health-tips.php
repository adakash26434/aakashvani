<?php
/**
 * Health Tips - Daily health advice and wellness tips
 * Covers nutrition, exercise, mental health, and more
 */
$pageTitle = 'Health Tips | आकाशवाणी';
$pageDesc  = 'Daily health tips and wellness advice for a healthy lifestyle.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-teal-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="heart-pulse" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Health Tips</h1>
      <p class="text-[11px] text-slate-500">स्वास्थ्य सुझावहरू</p>
    </div>
  </div>
</section>

<!-- Daily Tip Card -->
<section class="px-4 mb-4">
  <div class="bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl p-4 text-white">
    <div class="flex items-center gap-2 mb-2">
      <i data-lucide="sun" class="w-5 h-5"></i>
      <p class="text-[12px] font-bold">Today's Tip</p>
    </div>
    <p class="text-[16px] font-bold leading-relaxed" id="daily-tip">Loading...</p>
  </div>
</section>

<!-- Category Tabs -->
<section class="px-4 mb-4">
  <div class="flex gap-2 overflow-x-auto no-sb pb-2">
    <button onclick="filterTips('all')" class="tip-cat-btn active flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-teal-100 text-teal-700 border border-teal-200">
      सबै
    </button>
    <button onclick="filterTips('nutrition')" class="tip-cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      पोषण
    </button>
    <button onclick="filterTips('exercise')" class="tip-cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      व्यायाम
    </button>
    <button onclick="filterTips('mental')" class="tip-cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      मानसिक
    </button>
    <button onclick="filterTips('sleep')" class="tip-cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      निद्रा
    </button>
  </div>
</section>

<!-- Tips List -->
<section class="px-4 mb-4">
  <div class="space-y-3" id="tips-list">
  </div>
</section>

<!-- Quick Health Check -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Quick Health Check</p>
    <div class="space-y-2">
      <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
        <span class="text-[13px] font-semibold text-slate-700">Water Intake</span>
        <span class="text-[11px] text-teal-600 font-bold">8 glasses/day</span>
      </div>
      <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
        <span class="text-[13px] font-semibold text-slate-700">Sleep</span>
        <span class="text-[11px] text-teal-600 font-bold">7-9 hours</span>
      </div>
      <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
        <span class="text-[13px] font-semibold text-slate-700">Steps</span>
        <span class="text-[11px] text-teal-600 font-bold">10,000/day</span>
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
        <p class="font-semibold mb-1">Disclaimer</p>
        <p>These tips are for general information only. Please consult a healthcare professional for medical advice.</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var healthTips = [
    { category: 'nutrition', title: 'Drink More Water', desc: 'Stay hydrated by drinking at least 8 glasses of water daily. Water helps maintain body temperature, lubricate joints, and transport nutrients.', icon: 'droplets', color: 'bg-blue-100 text-blue-700' },
    { category: 'nutrition', title: 'Eat More Vegetables', desc: 'Include a variety of colorful vegetables in your diet. They provide essential vitamins, minerals, and fiber.', icon: 'carrot', color: 'bg-orange-100 text-orange-700' },
    { category: 'nutrition', title: 'Reduce Sugar Intake', desc: 'Limit added sugars in your diet. Excess sugar can lead to weight gain, diabetes, and heart disease.', icon: 'candy-off', color: 'bg-pink-100 text-pink-700' },
    { category: 'nutrition', title: 'Eat Protein at Every Meal', desc: 'Protein helps build and repair tissues. Include lean meats, eggs, dairy, beans, or nuts in each meal.', icon: 'beef', color: 'bg-red-100 text-red-700' },
    { category: 'exercise', title: 'Walk 30 Minutes Daily', desc: 'Regular walking improves cardiovascular health, strengthens bones, and boosts mood.', icon: 'footprints', color: 'bg-green-100 text-green-700' },
    { category: 'exercise', title: 'Strength Training', desc: 'Include resistance exercises twice a week to build muscle mass and increase metabolism.', icon: 'dumbbell', color: 'bg-purple-100 text-purple-700' },
    { category: 'exercise', title: 'Stretch Regularly', desc: 'Stretching improves flexibility, reduces muscle tension, and prevents injuries.', icon: 'stretch-horizontal', color: 'bg-teal-100 text-teal-700' },
    { category: 'exercise', title: 'Take Breaks from Sitting', desc: 'Stand up and move every 30 minutes to reduce the risk of chronic diseases.', icon: 'armchair', color: 'bg-amber-100 text-amber-700' },
    { category: 'mental', title: 'Practice Mindfulness', desc: 'Spend 10 minutes daily in meditation or deep breathing to reduce stress and improve focus.', icon: 'brain', color: 'bg-violet-100 text-violet-700' },
    { category: 'mental', title: 'Connect with Others', desc: 'Social connections improve mental health. Spend time with friends and family regularly.', icon: 'users', color: 'bg-pink-100 text-pink-700' },
    { category: 'mental', title: 'Limit Screen Time', desc: 'Reduce time on electronic devices, especially before bed, to improve sleep and mental clarity.', icon: 'smartphone', color: 'bg-slate-100 text-slate-700' },
    { category: 'mental', title: 'Practice Gratitude', desc: 'Write down three things you are grateful for each day to boost happiness and reduce stress.', icon: 'heart', color: 'bg-rose-100 text-rose-700' },
    { category: 'sleep', title: 'Stick to a Sleep Schedule', desc: 'Go to bed and wake up at the same time every day, even on weekends, to regulate your body clock.', icon: 'clock', color: 'bg-indigo-100 text-indigo-700' },
    { category: 'sleep', title: 'Create a Bedtime Routine', desc: 'Develop relaxing pre-sleep activities like reading or taking a warm bath to signal your body it is time to sleep.', icon: 'moon', color: 'bg-blue-100 text-blue-700' },
    { category: 'sleep', title: 'Avoid Caffeine Before Bed', desc: 'Stop consuming caffeine at least 6 hours before bedtime to improve sleep quality.', icon: 'coffee', color: 'bg-amber-100 text-amber-700' },
    { category: 'sleep', title: 'Keep Your Room Cool', desc: 'Maintain a bedroom temperature between 60-67°F (15-19°C) for optimal sleep.', icon: 'thermometer', color: 'bg-cyan-100 text-cyan-700' },
  ];
  
  // Show random daily tip
  function showDailyTip(){
    var randomTip = healthTips[Math.floor(Math.random() * healthTips.length)];
    document.getElementById('daily-tip').textContent = randomTip.title + ': ' + randomTip.desc;
  }
  
  window.filterTips = function(category){
    var buttons = document.querySelectorAll('.tip-cat-btn');
    buttons.forEach(function(btn){
      btn.classList.remove('active', 'bg-teal-100', 'text-teal-700', 'border-teal-200');
      btn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    });
    event.target.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
    event.target.classList.add('active', 'bg-teal-100', 'text-teal-700', 'border-teal-200');
    
    var filtered = category === 'all' ? healthTips : healthTips.filter(function(t){ return t.category === category; });
    renderTips(filtered);
  };
  
  function renderTips(tips){
    var container = document.getElementById('tips-list');
    container.innerHTML = tips.map(function(tip){
      return '<div class="bg-white rounded-xl border border-slate-100 shadow-app p-4">' +
        '<div class="flex items-start gap-3">' +
          '<div class="w-10 h-10 rounded-xl ' + tip.color + ' flex items-center justify-center flex-shrink-0">' +
            '<i data-lucide="' + tip.icon + '" class="w-5 h-5"></i>' +
          '</div>' +
          '<div class="flex-1">' +
            '<p class="text-[13px] font-bold text-slate-900 mb-1">' + tip.title + '</p>' +
            '<p class="text-[11px] text-slate-600 leading-relaxed">' + tip.desc + '</p>' +
          '</div>' +
        '</div>' +
      '</div>';
    }).join('');
    
    if(window.lucide) lucide.createIcons();
  }
  
  // Initialize
  showDailyTip();
  renderTips(healthTips);
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
