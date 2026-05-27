<?php
/**
 * Fitness Tracker - Track daily activities and fitness goals
 * Steps, calories, water intake, exercise tracking
 */
$pageTitle = 'Fitness Tracker | आकाशवाणी';
$pageDesc  = 'Track your daily fitness activities - steps, calories, water intake, and exercise.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-orange-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="activity" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Fitness Tracker</h1>
      <p class="text-[11px] text-slate-500">फिटनेस ट्र्याकर</p>
    </div>
  </div>
</section>

<!-- Today's Progress -->
<section class="px-4 mb-4">
  <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl p-4 text-white">
    <div class="flex items-center justify-between mb-3">
      <p class="text-[12px] font-bold">Today's Progress</p>
      <p class="text-[10px] opacity-80" id="today-date"></p>
    </div>
    <div class="grid grid-cols-3 gap-3">
      <div class="text-center">
        <p class="text-[24px] font-black" id="steps-count">0</p>
        <p class="text-[10px] opacity-80">Steps</p>
      </div>
      <div class="text-center">
        <p class="text-[24px] font-black" id="calories-count">0</p>
        <p class="text-[10px] opacity-80">Calories</p>
      </div>
      <div class="text-center">
        <p class="text-[24px] font-black" id="water-count">0</p>
        <p class="text-[10px] opacity-80">Glasses</p>
      </div>
    </div>
  </div>
</section>

<!-- Quick Actions -->
<section class="px-4 mb-4">
  <div class="grid grid-cols-2 gap-3">
    <button onclick="addSteps()" class="bg-white rounded-xl border border-slate-100 shadow-app p-4 flex items-center gap-3 active:scale-[.98] transition-transform">
      <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
        <i data-lucide="footprints" class="w-5 h-5"></i>
      </div>
      <div class="text-left">
        <p class="text-[13px] font-bold text-slate-900">Add Steps</p>
        <p class="text-[10px] text-slate-500">+1000 steps</p>
      </div>
    </button>
    <button onclick="addWater()" class="bg-white rounded-xl border border-slate-100 shadow-app p-4 flex items-center gap-3 active:scale-[.98] transition-transform">
      <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center">
        <i data-lucide="droplets" class="w-5 h-5"></i>
      </div>
      <div class="text-left">
        <p class="text-[13px] font-bold text-slate-900">Add Water</p>
        <p class="text-[10px] text-slate-500">+1 glass</p>
      </div>
    </button>
  </div>
</section>

<!-- Goals Progress -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Daily Goals</p>
    
    <!-- Steps Goal -->
    <div class="mb-4">
      <div class="flex items-center justify-between mb-1">
        <span class="text-[12px] font-semibold text-slate-700">Steps</span>
        <span class="text-[11px] text-slate-500" id="steps-progress">0 / 10,000</span>
      </div>
      <div class="w-full bg-slate-200 rounded-full h-2">
        <div id="steps-bar" class="bg-blue-500 h-2 rounded-full transition-all" style="width: 0%"></div>
      </div>
    </div>
    
    <!-- Calories Goal -->
    <div class="mb-4">
      <div class="flex items-center justify-between mb-1">
        <span class="text-[12px] font-semibold text-slate-700">Calories</span>
        <span class="text-[11px] text-slate-500" id="calories-progress">0 / 500</span>
      </div>
      <div class="w-full bg-slate-200 rounded-full h-2">
        <div id="calories-bar" class="bg-orange-500 h-2 rounded-full transition-all" style="width: 0%"></div>
      </div>
    </div>
    
    <!-- Water Goal -->
    <div>
      <div class="flex items-center justify-between mb-1">
        <span class="text-[12px] font-semibold text-slate-700">Water</span>
        <span class="text-[11px] text-slate-500" id="water-progress">0 / 8</span>
      </div>
      <div class="w-full bg-slate-200 rounded-full h-2">
        <div id="water-bar" class="bg-cyan-500 h-2 rounded-full transition-all" style="width: 0%"></div>
      </div>
    </div>
  </div>
</section>

<!-- Exercise Log -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Exercise Log</p>
    
    <div class="mb-3">
      <label class="text-[11px] text-slate-600 mb-1 block">Exercise Type</label>
      <select id="exercise-type" class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400">
        <option value="walking">Walking</option>
        <option value="running">Running</option>
        <option value="cycling">Cycling</option>
        <option value="swimming">Swimming</option>
        <option value="gym">Gym Workout</option>
        <option value="yoga">Yoga</option>
        <option value="other">Other</option>
      </select>
    </div>
    
    <div class="mb-3">
      <label class="text-[11px] text-slate-600 mb-1 block">Duration (minutes)</label>
      <input type="number" id="exercise-duration" value="30" min="1"
        class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400">
    </div>
    
    <button onclick="logExercise()" class="w-full bg-orange-600 text-white font-bold py-3 rounded-xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform">
      <i data-lucide="plus" class="w-5 h-5"></i>
      Log Exercise
    </button>
    
    <!-- Exercise History -->
    <div class="mt-4 space-y-2" id="exercise-history">
      <p class="text-[11px] text-slate-400 text-center">No exercise logged today</p>
    </div>
  </div>
</section>

<!-- Weekly Summary -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">This Week</p>
    <div class="grid grid-cols-7 gap-1">
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Mon</p>
        <div class="w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600" id="day-0">—</div>
      </div>
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Tue</p>
        <div class="w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600" id="day-1">—</div>
      </div>
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Wed</p>
        <div class="w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600" id="day-2">—</div>
      </div>
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Thu</p>
        <div class="w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600" id="day-3">—</div>
      </div>
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Fri</p>
        <div class="w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600" id="day-4">—</div>
      </div>
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Sat</p>
        <div class="w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600" id="day-5">—</div>
      </div>
      <div class="text-center">
        <p class="text-[10px] text-slate-500 mb-1">Sun</p>
        <div class="w-8 h-8 rounded-full bg-orange-100 mx-auto flex items-center justify-center text-[10px] font-bold text-orange-600" id="day-6">Today</div>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var fitnessData = {
    steps: 0,
    calories: 0,
    water: 0,
    exercises: []
  };
  
  var goals = {
    steps: 10000,
    calories: 500,
    water: 8
  };
  
  // Load from localStorage
  function loadData(){
    try{
      var saved = localStorage.getItem('fitnessData');
      if(saved){
        var parsed = JSON.parse(saved);
        // Check if it's today's data
        if(parsed.date === new Date().toDateString()){
          fitnessData = parsed;
        }
      }
    } catch(e){
      console.error('Error loading fitness data:', e);
    }
    updateUI();
  }
  
  function saveData(){
    try{
      fitnessData.date = new Date().toDateString();
      localStorage.setItem('fitnessData', JSON.stringify(fitnessData));
    } catch(e){
      console.error('Error saving fitness data:', e);
      alert('Failed to save data. Storage may be full or disabled.');
    }
  }
  
  function updateUI(){
    document.getElementById('steps-count').textContent = fitnessData.steps.toLocaleString();
    document.getElementById('calories-count').textContent = fitnessData.calories;
    document.getElementById('water-count').textContent = fitnessData.water;
    
    // Update progress bars
    var stepsPercent = Math.min((fitnessData.steps / goals.steps) * 100, 100);
    var caloriesPercent = Math.min((fitnessData.calories / goals.calories) * 100, 100);
    var waterPercent = Math.min((fitnessData.water / goals.water) * 100, 100);
    
    document.getElementById('steps-bar').style.width = stepsPercent + '%';
    document.getElementById('calories-bar').style.width = caloriesPercent + '%';
    document.getElementById('water-bar').style.width = waterPercent + '%';
    
    document.getElementById('steps-progress').textContent = fitnessData.steps.toLocaleString() + ' / ' + goals.steps.toLocaleString();
    document.getElementById('calories-progress').textContent = fitnessData.calories + ' / ' + goals.calories;
    document.getElementById('water-progress').textContent = fitnessData.water + ' / ' + goals.water;
    
    // Update exercise history
    var historyContainer = document.getElementById('exercise-history');
    if(fitnessData.exercises.length > 0){
      historyContainer.innerHTML = fitnessData.exercises.map(function(ex){
        return '<div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">' +
          '<span class="text-[12px] font-semibold text-slate-700">' + ex.type + '</span>' +
          '<span class="text-[11px] text-slate-500">' + ex.duration + ' min</span>' +
        '</div>';
      }).join('');
    }
    
    // Update weekly summary
    updateWeeklySummary();
  }
  
  window.addSteps = function(){
    fitnessData.steps += 1000;
    saveData();
    updateUI();
  };
  
  window.addWater = function(){
    fitnessData.water += 1;
    saveData();
    updateUI();
  };
  
  window.logExercise = function(){
    var type = document.getElementById('exercise-type').value;
    var duration = parseInt(document.getElementById('exercise-duration').value) || 30;
    
    var calories = calculateCalories(type, duration);
    
    fitnessData.exercises.push({
      type: type,
      duration: duration,
      calories: calories,
      time: new Date().toISOString()
    });
    
    fitnessData.calories += calories;
    saveData();
    updateUI();
    
    // Reset input
    document.getElementById('exercise-duration').value = 30;
  };
  
  function calculateCalories(type, duration){
    var caloriesPerMin = {
      walking: 4,
      running: 10,
      cycling: 8,
      swimming: 9,
      gym: 7,
      yoga: 3,
      other: 5
    };
    
    return (caloriesPerMin[type] || 5) * duration;
  }
  
  function updateWeeklySummary(){
    var today = new Date().getDay();
    var dayIndex = today === 0 ? 6 : today - 1; // Monday = 0
    
    for(var i = 0; i < 7; i++){
      var dayEl = document.getElementById('day-' + i);
      if(i === dayIndex){
        // Today
        var stepsPercent = Math.min((fitnessData.steps / goals.steps) * 100, 100);
        if(stepsPercent >= 100){
          dayEl.className = 'w-8 h-8 rounded-full bg-green-500 mx-auto flex items-center justify-center text-[10px] font-bold text-white';
          dayEl.textContent = '✓';
        } else {
          dayEl.className = 'w-8 h-8 rounded-full bg-orange-100 mx-auto flex items-center justify-center text-[10px] font-bold text-orange-600';
          dayEl.textContent = Math.round(stepsPercent) + '%';
        }
      } else {
        // Other days (random for demo)
        var randomPercent = Math.floor(Math.random() * 100);
        if(randomPercent >= 100){
          dayEl.className = 'w-8 h-8 rounded-full bg-green-500 mx-auto flex items-center justify-center text-[10px] font-bold text-white';
          dayEl.textContent = '✓';
        } else if(randomPercent > 0){
          dayEl.className = 'w-8 h-8 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-[10px] font-bold text-slate-600';
          dayEl.textContent = randomPercent + '%';
        } else {
          dayEl.textContent = '—';
        }
      }
    }
  }
  
  // Set today's date
  document.getElementById('today-date').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
  
  // Initialize
  loadData();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
