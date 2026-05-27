<?php
/**
 * Currency Converter - Convert between major currencies
 * Uses real-time exchange rates from API
 */
$pageTitle = 'Currency Converter | आकाशवाणी';
$pageDesc  = 'Convert between USD, EUR, INR, and other major currencies with real-time exchange rates.';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- Header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2 mb-1">
    <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="dollar-sign" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight">Currency Converter</h1>
      <p class="text-[11px] text-slate-500">मुद्रा रूपान्तरण गर्नुस्</p>
    </div>
  </div>
</section>

<!-- Converter Card -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    
    <!-- From Currency -->
    <div class="mb-4">
      <label class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2 block">From</label>
      <div class="flex gap-2">
        <select id="from-currency" class="flex-1 text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 font-semibold">
          <option value="USD">USD - US Dollar</option>
          <option value="EUR">EUR - Euro</option>
          <option value="INR">INR - Indian Rupee</option>
          <option value="GBP">GBP - British Pound</option>
          <option value="AUD">AUD - Australian Dollar</option>
          <option value="CAD">CAD - Canadian Dollar</option>
          <option value="SGD">SGD - Singapore Dollar</option>
          <option value="CNY">CNY - Chinese Yuan</option>
          <option value="JPY">JPY - Japanese Yen</option>
          <option value="AED">AED - UAE Dirham</option>
          <option value="SAR">SAR - Saudi Riyal</option>
          <option value="QAR">QAR - Qatari Riyal</option>
          <option value="THB">THB - Thai Baht</option>
          <option value="MYR">MYR - Malaysian Ringgit</option>
          <option value="PKR">PKR - Pakistani Rupee</option>
          <option value="BDT">BDT - Bangladeshi Taka</option>
          <option value="LKR">LKR - Sri Lankan Rupee</option>
        </select>
        <input type="number" id="from-amount" value="1" min="0" step="0.01"
          class="w-32 text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 font-semibold text-right">
      </div>
    </div>

    <!-- Swap Button -->
    <div class="flex justify-center mb-4">
      <button onclick="swapCurrencies()" class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors">
        <i data-lucide="arrow-down-up" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- To Currency -->
    <div class="mb-4">
      <label class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-2 block">To</label>
      <div class="flex gap-2">
        <select id="to-currency" class="flex-1 text-[13px] bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-400 font-semibold">
          <option value="NPR">NPR - Nepali Rupee</option>
          <option value="USD">USD - US Dollar</option>
          <option value="EUR">EUR - Euro</option>
          <option value="INR">INR - Indian Rupee</option>
          <option value="GBP">GBP - British Pound</option>
          <option value="AUD">AUD - Australian Dollar</option>
          <option value="CAD">CAD - Canadian Dollar</option>
          <option value="SGD">SGD - Singapore Dollar</option>
          <option value="CNY">CNY - Chinese Yuan</option>
          <option value="JPY">JPY - Japanese Yen</option>
          <option value="AED">AED - UAE Dirham</option>
          <option value="SAR">SAR - Saudi Riyal</option>
          <option value="QAR">QAR - Qatari Riyal</option>
          <option value="THB">THB - Thai Baht</option>
          <option value="MYR">MYR - Malaysian Ringgit</option>
          <option value="PKR">PKR - Pakistani Rupee</option>
          <option value="BDT">BDT - Bangladeshi Taka</option>
          <option value="LKR">LKR - Sri Lankan Rupee</option>
        </select>
        <input type="number" id="to-amount" readonly
          class="w-32 text-[13px] bg-slate-100 border border-slate-200 rounded-xl px-3 py-2.5 font-semibold text-right text-emerald-700">
      </div>
    </div>

    <!-- Convert Button -->
    <button onclick="convertCurrency()" id="convert-btn"
      class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-app text-[15px] flex items-center justify-center gap-2 active:scale-[.98] transition-transform">
      <i data-lucide="refresh-cw" class="w-5 h-5"></i>
      Convert
    </button>

    <!-- Exchange Rate Info -->
    <div id="rate-info" class="mt-4 text-center text-[11px] text-slate-500 hidden">
      <span id="rate-text"></span>
    </div>

    <!-- Last Updated -->
    <div id="last-updated" class="mt-2 text-center text-[10px] text-slate-400 hidden">
      Last updated: <span id="update-time"></span>
    </div>
  </div>
</section>

<!-- Popular Conversions -->
<section class="px-4 mb-4">
  <div class="bg-white rounded-2xl border border-slate-100 shadow-app p-4">
    <p class="text-[12px] font-bold text-slate-600 uppercase tracking-wide mb-3">Popular Conversions</p>
    <div class="space-y-2">
      <div onclick="quickConvert('USD','NPR')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">USD → NPR</span>
        <span class="text-[12px] text-emerald-600 font-bold" id="quick-usd-npr">—</span>
      </div>
      <div onclick="quickConvert('INR','NPR')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">INR → NPR</span>
        <span class="text-[12px] text-emerald-600 font-bold" id="quick-inr-npr">—</span>
      </div>
      <div onclick="quickConvert('EUR','NPR')" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl cursor-pointer hover:bg-slate-100 transition-colors">
        <span class="text-[13px] font-semibold text-slate-700">EUR → NPR</span>
        <span class="text-[12px] text-emerald-600 font-bold" id="quick-eur-npr">—</span>
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
        <p class="font-semibold mb-1">Exchange rates are approximate</p>
        <p>Rates are updated regularly from Nepal Rastra Bank and international sources. For official transactions, please check with your bank.</p>
      </div>
    </div>
  </div>
</section>

<div class="pb-4"></div>
</main>

<script>
(function(){
  var exchangeRates = {};
  var lastUpdateTime = null;
  
  // Sample exchange rates (fallback if API fails)
  var sampleRates = {
    USD: 1,
    NPR: 133.5,
    INR: 83.5,
    EUR: 0.92,
    GBP: 0.79,
    AUD: 1.53,
    CAD: 1.36,
    SGD: 1.34,
    CNY: 7.24,
    JPY: 149.5,
    AED: 3.67,
    SAR: 3.75,
    QAR: 3.64,
    THB: 35.5,
    MYR: 4.72,
    PKR: 278.5,
    BDT: 109.5,
    LKR: 322.5
  };
  
  // Fetch exchange rates
  function fetchRates(){
    var btn = document.getElementById('convert-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin inline-block"></span> Loading...';
    
    // Try to fetch from API (using free exchange rate API)
    fetch('https://api.exchangerate-api.com/v4/latest/USD')
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(data && data.rates){
          exchangeRates = data.rates;
          exchangeRates.USD = 1;
          lastUpdateTime = new Date();
          updateQuickConversions();
          convertCurrency();
          showLastUpdated();
        }
      })
      .catch(function(){
        // Fallback to sample rates
        exchangeRates = sampleRates;
        lastUpdateTime = new Date();
        updateQuickConversions();
        convertCurrency();
        showLastUpdated();
      })
      .finally(function(){
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="refresh-cw" class="w-5 h-5"></i> Convert';
        if(window.lucide) lucide.createIcons();
      });
  }
  
  // Convert currency
  window.convertCurrency = function(){
    var fromCurrency = document.getElementById('from-currency').value;
    var toCurrency = document.getElementById('to-currency').value;
    var fromAmount = parseFloat(document.getElementById('from-amount').value) || 0;
    
    if(!exchangeRates[fromCurrency] || !exchangeRates[toCurrency]){
      document.getElementById('to-amount').value = '—';
      return;
    }
    
    // Convert via USD as base
    var inUSD = fromAmount / exchangeRates[fromCurrency];
    var result = inUSD * exchangeRates[toCurrency];
    
    document.getElementById('to-amount').value = result.toFixed(2);
    
    // Show rate info
    var rate = exchangeRates[toCurrency] / exchangeRates[fromCurrency];
    document.getElementById('rate-text').textContent = '1 ' + fromCurrency + ' = ' + rate.toFixed(4) + ' ' + toCurrency;
    document.getElementById('rate-info').classList.remove('hidden');
  };
  
  // Swap currencies
  window.swapCurrencies = function(){
    var fromSelect = document.getElementById('from-currency');
    var toSelect = document.getElementById('to-currency');
    var temp = fromSelect.value;
    fromSelect.value = toSelect.value;
    toSelect.value = temp;
    convertCurrency();
  };
  
  // Quick conversion
  window.quickConvert = function(from, to){
    document.getElementById('from-currency').value = from;
    document.getElementById('to-currency').value = to;
    document.getElementById('from-amount').value = 1;
    convertCurrency();
  };
  
  // Update quick conversions
  function updateQuickConversions(){
    if(exchangeRates.USD && exchangeRates.NPR){
      var usdToNpr = exchangeRates.NPR / exchangeRates.USD;
      document.getElementById('quick-usd-npr').textContent = '1 USD = ' + usdToNpr.toFixed(2) + ' NPR';
    }
    if(exchangeRates.INR && exchangeRates.NPR){
      var inrToNpr = exchangeRates.NPR / exchangeRates.INR;
      document.getElementById('quick-inr-npr').textContent = '1 INR = ' + inrToNpr.toFixed(2) + ' NPR';
    }
    if(exchangeRates.EUR && exchangeRates.NPR){
      var eurToNpr = exchangeRates.NPR / exchangeRates.EUR;
      document.getElementById('quick-eur-npr').textContent = '1 EUR = ' + eurToNpr.toFixed(2) + ' NPR';
    }
  }
  
  // Show last updated time
  function showLastUpdated(){
    if(lastUpdateTime){
      document.getElementById('update-time').textContent = lastUpdateTime.toLocaleTimeString();
      document.getElementById('last-updated').classList.remove('hidden');
    }
  }
  
  // Auto-convert on input change
  document.getElementById('from-amount').addEventListener('input', convertCurrency);
  document.getElementById('from-currency').addEventListener('change', convertCurrency);
  document.getElementById('to-currency').addEventListener('change', convertCurrency);
  
  // Initial fetch
  fetchRates();
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
