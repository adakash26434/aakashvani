<?php
/**
 * Unit Converter - Convert between different units
 * Length, Weight, Temperature, Area, Volume, Speed
 */
$pageTitle = 'Unit Converter | आकाशवाणी';
$pageDesc  = 'Convert between different units - length, weight, temperature, area, volume, and speed.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="calculator" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Unit Converter</h1>
      <p class="text-[11px] text-slate-500">एकाइ परिवर्तन गर्नुस्</p>
    </div>
  </div>
</section>

<!-- Category Tabs -->
<section class="px-4 mb-4">
  <div class="flex gap-2 overflow-x-auto no-sb pb-2">
    <button onclick="switchCategory('length')" class="cat-btn active flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-blue-100 text-blue-700 border border-blue-200">
      Length
    </button>
    <button onclick="switchCategory('weight')" class="cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      Weight
    </button>
    <button onclick="switchCategory('temperature')" class="cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      Temperature
    </button>
    <button onclick="switchCategory('area')" class="cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      Area
    </button>
    <button onclick="switchCategory('volume')" class="cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      Volume
    </button>
    <button onclick="switchCategory('speed')" class="cat-btn flex-shrink-0 px-4 py-2 rounded-full text-[12px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
      Speed
    </button>
  </div>
</section>

<!-- Converter Card -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    
    <!-- From Unit -->
    <div class="mb-4">
      <label class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2 block">From</label>
      <div class="flex gap-2">
        <select id="from-unit" class="flex-1 text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 font-semibold">
        </select>
        <input type="number" id="from-value" value="1" min="0" step="any"
          class="w-32 text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 font-semibold text-right">
      </div>
    </div>

    <!-- Swap Button -->
    <div class="flex justify-center mb-4">
      <button onclick="swapUnits()" class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors">
        <i data-lucide="arrow-down-up" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- To Unit -->
    <div class="mb-4">
      <label class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2 block">To</label>
      <div class="flex gap-2">
        <select id="to-unit" class="flex-1 text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 font-semibold">
        </select>
        <input type="number" id="to-value" readonly
          class="w-32 text-[13px] bg-slate-100 border border-slate-200 rounded-xl px-3 py-2.5 font-semibold text-right text-blue-700">
      </div>
    </div>

    <!-- Convert Button -->
    <button onclick="convert()" id="convert-btn"
      class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform">
      <i data-lucide="refresh-cw" class="w-5 h-5"></i>
      Convert
    </button>
  </div>
</section>

<!-- Common Conversions -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Common Conversions</p>
    <div class="space-y-2" id="common-conversions">
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var currentCategory = 'length';
  
  var units = {
    length: {
      'Meter': 1,
      'Kilometer': 1000,
      'Centimeter': 0.01,
      'Millimeter': 0.001,
      'Inch': 0.0254,
      'Foot': 0.3048,
      'Yard': 0.9144,
      'Mile': 1609.34,
      'Nepali Haat': 0.4572
    },
    weight: {
      'Kilogram': 1,
      'Gram': 0.001,
      'Milligram': 0.000001,
      'Pound': 0.453592,
      'Ounce': 0.0283495,
      'Tola': 0.0116638,
      'Quintal': 100,
      'Metric Ton': 1000
    },
    temperature: {
      'Celsius': 'c',
      'Fahrenheit': 'f',
      'Kelvin': 'k'
    },
    area: {
      'Square Meter': 1,
      'Square Kilometer': 1000000,
      'Square Foot': 0.092903,
      'Square Yard': 0.836127,
      'Acre': 4046.86,
      'Hectare': 10000,
      'Ropani': 508.737,
      'Aana': 31.796
    },
    volume: {
      'Liter': 1,
      'Milliliter': 0.001,
      'Cubic Meter': 1000,
      'Gallon': 3.78541,
      'Quart': 0.946353,
      'Cup': 0.236588,
      'Tablespoon': 0.0147868,
      'Teaspoon': 0.00492892
    },
    speed: {
      'Meter/Second': 1,
      'Kilometer/Hour': 0.277778,
      'Mile/Hour': 0.44704,
      'Knot': 0.514444,
      'Foot/Second': 0.3048
    }
  };
  
  var commonConversions = {
    length: [
      ['Meter', 'Foot', 1],
      ['Kilometer', 'Mile', 1],
      ['Inch', 'Centimeter', 1]
    ],
    weight: [
      ['Kilogram', 'Pound', 1],
      ['Kilogram', 'Tola', 1],
      ['Gram', 'Ounce', 1]
    ],
    temperature: [
      ['Celsius', 'Fahrenheit', 0],
      ['Celsius', 'Kelvin', 0]
    ],
    area: [
      ['Square Meter', 'Square Foot', 1],
      ['Acre', 'Ropani', 1],
      ['Hectare', 'Acre', 1]
    ],
    volume: [
      ['Liter', 'Gallon', 1],
      ['Cup', 'Milliliter', 1],
      ['Liter', 'Cubic Meter', 1]
    ],
    speed: [
      ['Kilometer/Hour', 'Mile/Hour', 1],
      ['Meter/Second', 'Kilometer/Hour', 1],
      ['Knot', 'Kilometer/Hour', 1]
    ]
  };
  
  window.switchCategory = function(category){
    currentCategory = category;
    
    // Update buttons
    document.querySelectorAll('.cat-btn').forEach(function(btn){
      btn.classList.remove('active', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
      btn.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
    });
    event.target.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
    event.target.classList.add('active', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
    
    // Populate unit selects
    var fromSelect = document.getElementById('from-unit');
    var toSelect = document.getElementById('to-unit');
    fromSelect.innerHTML = '';
    toSelect.innerHTML = '';
    
    var unitList = Object.keys(units[category]);
    unitList.forEach(function(unit, index){
      fromSelect.innerHTML += '<option value="' + unit + '">' + unit + '</option>';
      toSelect.innerHTML += '<option value="' + unit + '">' + unit + '</option>';
    });
    
    // Set default selections
    if(unitList.length > 1){
      fromSelect.value = unitList[0];
      toSelect.value = unitList[1];
    }
    
    // Update common conversions
    updateCommonConversions();
    
    // Convert
    convert();
  };
  
  window.convert = function(){
    var fromUnit = document.getElementById('from-unit').value;
    var toUnit = document.getElementById('to-unit').value;
    var fromValue = parseFloat(document.getElementById('from-value').value) || 0;
    
    var result;
    
    if(currentCategory === 'temperature'){
      result = convertTemperature(fromValue, fromUnit, toUnit);
    } else {
      var fromFactor = units[currentCategory][fromUnit];
      var toFactor = units[currentCategory][toUnit];
      result = (fromValue * fromFactor) / toFactor;
    }
    
    document.getElementById('to-value').value = result.toFixed(4);
  };
  
  function convertTemperature(value, from, to){
    var celsius;
    
    // Convert to Celsius first
    if(from === 'Celsius'){
      celsius = value;
    } else if(from === 'Fahrenheit'){
      celsius = (value - 32) * 5/9;
    } else if(from === 'Kelvin'){
      celsius = value - 273.15;
    }
    
    // Convert from Celsius to target
    if(to === 'Celsius'){
      return celsius;
    } else if(to === 'Fahrenheit'){
      return (celsius * 9/5) + 32;
    } else if(to === 'Kelvin'){
      return celsius + 273.15;
    }
  }
  
  window.swapUnits = function(){
    var fromSelect = document.getElementById('from-unit');
    var toSelect = document.getElementById('to-unit');
    var fromValue = document.getElementById('from-value');
    var toValue = document.getElementById('to-value');
    
    var temp = fromSelect.value;
    fromSelect.value = toSelect.value;
    toSelect.value = temp;
    
    fromValue.value = toValue.value;
    convert();
  };
  
  function updateCommonConversions(){
    var container = document.getElementById('common-conversions');
    var conversions = commonConversions[currentCategory];
    
    container.innerHTML = conversions.map(function(conv){
      var from = conv[0];
      var to = conv[1];
      var value = conv[2];
      var result;
      
      if(currentCategory === 'temperature'){
        result = convertTemperature(value, from, to);
      } else {
        var fromFactor = units[currentCategory][from];
        var toFactor = units[currentCategory][to];
        result = (value * fromFactor) / toFactor;
      }
      
      return '<div onclick="quickConvert(\'' + from + '\',\'' + to + '\',' + value + ')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">' +
        '<span class="text-[13px] font-semibold text-slate-700">' + value + ' ' + from + ' → ' + to + '</span>' +
        '<span class="text-[12px] text-blue-600 font-bold">' + result.toFixed(4) + '</span>' +
      '</div>';
    }).join('');
  }
  
  window.quickConvert = function(from, to, value){
    document.getElementById('from-unit').value = from;
    document.getElementById('to-unit').value = to;
    document.getElementById('from-value').value = value;
    convert();
  };
  
  // Auto-convert on input change
  document.getElementById('from-value').addEventListener('input', convert);
  document.getElementById('from-unit').addEventListener('change', convert);
  document.getElementById('to-unit').addEventListener('change', convert);
  
  // Initialize
  switchCategory('length');
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
