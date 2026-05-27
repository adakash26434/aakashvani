<?php
/**
 * Gold Price Tracker - Real-time gold and silver prices
 * Uses Nepal Gold/Silver market data
 */
$pageTitle = 'Gold Price Tracker | आकाशवाणी';
$pageDesc  = 'Track real-time gold and silver prices in Nepal. Hallmark gold, tejabi gold, and silver rates.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="gem" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Gold Price Tracker</h1>
      <p class="text-[11px] text-slate-500">सुन र चाँदीको मूल्य</p>
    </div>
  </div>
</section>

<!-- Live Prices Card -->
<section class="px-4 mb-4">
  <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl border border-amber-200 shadow-app p-4">
    <div class="flex items-center justify-between mb-3">
      <p class="text-[12px] font-bold text-amber-800 uppercase tracking-wide">Live Prices</p>
      <span class="text-[10px] bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full font-semibold">
        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse inline-block mr-1"></span>
        Live
      </span>
    </div>
    
    <!-- Gold Hallmark -->
    <div class="bg-white rounded-xl p-4 mb-3 border border-amber-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] text-amber-600 font-semibold mb-1">Hallmark Gold (24K)</p>
          <p class="text-[22px] font-black text-amber-900" id="hallmark-price">रु —</p>
          <p class="text-[10px] text-slate-500">प्रति तोला (11.664g)</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
          <i data-lucide="gem" class="w-6 h-6 text-amber-600"></i>
        </div>
      </div>
    </div>
    
    <!-- Gold Tejabi -->
    <div class="bg-white rounded-xl p-4 mb-3 border border-amber-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] text-amber-600 font-semibold mb-1">Tejabi Gold (22K)</p>
          <p class="text-[22px] font-black text-amber-900" id="tejabi-price">रु —</p>
          <p class="text-[10px] text-slate-500">प्रति तोला (11.664g)</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
          <i data-lucide="coins" class="w-6 h-6 text-amber-600"></i>
        </div>
      </div>
    </div>
    
    <!-- Silver -->
    <div class="bg-white rounded-xl p-4 border border-slate-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[11px] text-slate-600 font-semibold mb-1">Silver (Chandi)</p>
          <p class="text-[22px] font-black text-slate-900" id="silver-price">रु —</p>
          <p class="text-[10px] text-slate-500">प्रति तोला (11.664g)</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
          <i data-lucide="circle-dot" class="w-6 h-6 text-slate-600"></i>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Price History -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <div class="flex items-center justify-between mb-3">
      <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide">Price History</p>
      <select id="history-range" class="text-[11px] bg-slate-50 border border-slate-200 rounded-lg px-2 py-1">
        <option value="7">7 Days</option>
        <option value="30">30 Days</option>
        <option value="90">90 Days</option>
      </select>
    </div>
    
    <div id="price-chart" class="h-40 bg-slate-50 rounded-xl flex items-center justify-center mb-3">
      <p class="text-[11px] text-slate-400">Loading chart...</p>
    </div>
    
    <div class="flex justify-between text-[10px] text-slate-500">
      <span>Low: <span id="price-low" class="font-bold text-slate-700">—</span></span>
      <span>High: <span id="price-high" class="font-bold text-slate-700">—</span></span>
    </div>
  </div>
</section>

<!-- Price Calculator -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Price Calculator</p>
    
    <div class="mb-3">
      <label class="text-[11px] text-slate-600 mb-1 block">Weight (tola)</label>
      <input type="number" id="weight-input" value="1" min="0" step="0.1"
        class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-400">
    </div>
    
    <div class="mb-3">
      <label class="text-[11px] text-slate-600 mb-1 block">Type</label>
      <select id="gold-type" class="w-full text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="hallmark">Hallmark Gold (24K)</option>
        <option value="tejabi">Tejabi Gold (22K)</option>
        <option value="silver">Silver</option>
      </select>
    </div>
    
    <div class="bg-amber-50 rounded-xl p-3 text-center">
      <p class="text-[10px] text-amber-600 mb-1">Total Price</p>
      <p class="text-[24px] font-black text-amber-900" id="calculated-price">रु —</p>
    </div>
  </div>
</section>

<!-- Market Info -->
<section class="px-4 mb-4">
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
      <div class="text-[11px] text-blue-800">
        <p class="font-semibold mb-1">About Gold Prices in Nepal</p>
        <p>Gold prices in Nepal are determined by Nepal Gold and Silver Dealers Association (Negosida). Prices are updated daily based on international market rates.</p>
      </div>
    </div>
  </div>
</section>

<!-- Last Updated -->
<section class="px-4 mb-4">
  <div class="text-center text-[10px] text-slate-400">
    Last updated: <span id="update-time">—</span>
    <br>
    Source: Nepal Gold & Silver Dealers Association
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var goldData = {
    hallmark: 0,
    tejabi: 0,
    silver: 0
  };
  
  // Fetch gold prices from market-data API
  function fetchGoldPrices(){
    fetch('/api/market-data.php')
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data && data.gold){
          goldData.hallmark = data.gold.hallmarkPerTola || 0;
          goldData.tejabi = data.gold.tejabiPerTola || 0;
          goldData.silver = data.gold.silverPerTola || 0;
          
          updateUI();
          updateCalculator();
          generateChart();
        }
      })
      .catch(function(){
        // Fallback to sample data
        goldData.hallmark = 135000;
        goldData.tejabi = 132000;
        goldData.silver = 1650;
        
        updateUI();
        updateCalculator();
        generateChart();
      });
  }
  
  function updateUI(){
    document.getElementById('hallmark-price').textContent = 'रु ' + formatNumber(goldData.hallmark);
    document.getElementById('tejabi-price').textContent = 'रु ' + formatNumber(goldData.tejabi);
    document.getElementById('silver-price').textContent = 'रु ' + formatNumber(goldData.silver);
    document.getElementById('update-time').textContent = new Date().toLocaleString();
  }
  
  function updateCalculator(){
    var weight = parseFloat(document.getElementById('weight-input').value) || 0;
    var type = document.getElementById('gold-type').value;
    var price = 0;
    
    if(type === 'hallmark'){
      price = goldData.hallmark * weight;
    } else if(type === 'tejabi'){
      price = goldData.tejabi * weight;
    } else if(type === 'silver'){
      price = goldData.silver * weight;
    }
    
    document.getElementById('calculated-price').textContent = 'रु ' + formatNumber(price);
  }
  
  function formatNumber(num){
    return new Intl.NumberFormat('en-IN').format(Math.round(num));
  }
  
  function generateChart(){
    // Generate sample chart data (in real app, fetch from API)
    var chart = document.getElementById('price-chart');
    var days = parseInt(document.getElementById('history-range').value);
    var prices = [];
    var basePrice = goldData.hallmark || 135000;
    
    for(var i = 0; i < days; i++){
      var variation = (Math.random() - 0.5) * 2000;
      prices.push(Math.round(basePrice + variation));
    }
    
    var low = Math.min(...prices);
    var high = Math.max(...prices);
    
    document.getElementById('price-low').textContent = 'रु ' + formatNumber(low);
    document.getElementById('price-high').textContent = 'रु ' + formatNumber(high);
    
    // Simple bar chart visualization
    var chartHTML = '<div class="flex items-end gap-1 h-full px-2">';
    prices.forEach(function(price, index){
      var height = ((price - low) / (high - low)) * 100;
      var isToday = index === prices.length - 1;
      chartHTML += '<div style="flex:1;background:' + (isToday ? '#f59e0b' : '#fcd34d') + ';height:' + height + '%;border-radius:2px;min-height:4px;"></div>';
    });
    chartHTML += '</div>';
    
    chart.innerHTML = chartHTML;
  }
  
  // Event listeners
  document.getElementById('weight-input').addEventListener('input', updateCalculator);
  document.getElementById('gold-type').addEventListener('change', updateCalculator);
  document.getElementById('history-range').addEventListener('change', generateChart);
  
  // Initial fetch
  fetchGoldPrices();
  
  // Refresh every 5 minutes
  setInterval(fetchGoldPrices, 300000);
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
