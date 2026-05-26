<?php
/**
 * NEA Electricity Bill Info
 * Nepal Electricity Authority bill information and rates
 */
require_once __DIR__ . '/header.php';

$pageTitle = 'बिजुली बिल | ' . SITE_NAME;
$pageDesc = 'नेपाल विद्युत प्राधिकरणको बिल जानकारी र दरहरू।';
?>
<style>
.nea-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.nea-header {
  text-align: center;
  margin-bottom: 30px;
}
.nea-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}
.nea-card-title {
  font-weight: 700;
  color: #0b1220;
  font-size: 16px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.nea-rate-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f1f5f9;
}
.nea-rate-row:last-child {
  border-bottom: none;
}
.nea-rate-label {
  color: #64748b;
  font-size: 14px;
}
.nea-rate-value {
  font-weight: 600;
  color: #0b1220;
  font-size: 14px;
}
.nea-input-group {
  margin-bottom: 16px;
}
.nea-input-label {
  display: block;
  font-weight: 600;
  color: #0b1220;
  font-size: 14px;
  margin-bottom: 8px;
}
.nea-input {
  width: 100%;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
}
.nea-btn {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #0d9488, #0891b2);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.nea-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}
.nea-payment-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 8px;
}
.nea-payment-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: linear-gradient(135deg, #f0fdfa, #cffafe);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d9488;
}
.nea-payment-name {
  font-weight: 600;
  color: #0b1220;
  font-size: 14px;
}
.nea-payment-type {
  color: #64748b;
  font-size: 12px;
}
.nea-bill-result {
  background: #f0fdfa;
  border: 1px solid #0d9488;
  border-radius: 12px;
  padding: 20px;
  margin-top: 20px;
  display: none;
}
.nea-bill-result.show {
  display: block;
}
.nea-info-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #cffafe;
}
.nea-info-item:last-child {
  border-bottom: none;
}
.nea-info-label {
  color: #64748b;
  font-size: 14px;
}
.nea-info-value {
  font-weight: 600;
  color: #0b1220;
  font-size: 14px;
}
.nea-total {
  background: #0d9488;
  color: white;
  padding: 12px;
  border-radius: 8px;
  text-align: center;
  font-weight: 700;
  font-size: 18px;
  margin-top: 16px;
}
</style>

<div class="nea-container">
  <div class="nea-header">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="zap" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 ne">बिजुली बिल</h1>
    <p class="text-gray-500">नेपाल विद्युत प्राधिकरण - बिल जानकारी र दरहरू</p>
  </div>

  <!-- Bill Check -->
  <div class="nea-card">
    <div class="nea-card-title">
      <i data-lucide="file-text" class="w-5 h-5 text-yellow-600"></i>
      <span class="ne">बिल जाँच्नुहोस्</span>
    </div>
    <div class="nea-input-group">
      <label class="nea-input-label ne">SC Number</label>
      <input type="text" id="nea-sc" class="nea-input" placeholder="उदाहरण: 12345678">
    </div>
    <button class="nea-btn" onclick="checkBill()">
      <i data-lucide="search" class="w-4 h-4 inline mr-2"></i>
      बिल हेर्नुहोस्
    </button>
    <div id="nea-bill-result" class="nea-bill-result"></div>
  </div>

  <!-- Rates -->
  <div class="nea-card">
    <div class="nea-card-title">
      <i data-lucide="calculator" class="w-5 h-5 text-yellow-600"></i>
      <span class="ne">विद्युत दरहरू</span>
    </div>
    <div id="nea-rates">
      <div class="nea-loading text-center py-4">
        <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto"></i>
      </div>
    </div>
  </div>

  <!-- Payment Methods -->
  <div class="nea-card">
    <div class="nea-card-title">
      <i data-lucide="credit-card" class="w-5 h-5 text-yellow-600"></i>
      <span class="ne">तिर्ने तरिकाहरू</span>
    </div>
    <div id="nea-payments">
      <div class="nea-loading text-center py-4">
        <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto"></i>
      </div>
    </div>
  </div>

  <!-- Contact Info -->
  <div class="nea-card">
    <div class="nea-card-title">
      <i data-lucide="phone" class="w-5 h-5 text-yellow-600"></i>
      <span class="ne">सम्पर्क</span>
    </div>
    <div id="nea-info">
      <div class="nea-loading text-center py-4">
        <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto"></i>
      </div>
    </div>
  </div>
</div>

<script>
function loadNEAData() {
  fetch('/api/nea-bill.php')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        renderRates(data.rates);
        renderPayments(data.payment_methods);
        renderInfo(data.info);
      }
    });
}

function renderRates(rates) {
  let html = '';
  rates.forEach(rate => {
    html += '<div class="mb-4">';
    html += '<div class="font-bold text-gray-900 mb-2 ne">' + rate.name + '</div>';
    html += '<div class="nea-rate-row"><span class="nea-rate-label">Service Charge</span><span class="nea-rate-value">Rs. ' + rate.service_charge + '</span></div>';
    html += '<div class="nea-rate-row"><span class="nea-rate-label">Demand Charge</span><span class="nea-rate-value">Rs. ' + rate.demand_charge + '</span></div>';
    html += '<div class="mt-2 text-sm text-gray-500">Unit Rate:</div>';
    rate.unit_rate.forEach(ur => {
      const max = ur.max ? ur.max : '∞';
      html += '<div class="nea-rate-row"><span class="nea-rate-label">' + ur.min + ' - ' + max + ' units</span><span class="nea-rate-value">Rs. ' + ur.rate + '</span></div>';
    });
    html += '</div>';
  });
  document.getElementById('nea-rates').innerHTML = html;
}

function renderPayments(payments) {
  let html = '';
  payments.forEach(p => {
    html += '<div class="nea-payment-item">';
    html += '<div class="nea-payment-icon"><i data-lucide="credit-card" class="w-5 h-5"></i></div>';
    html += '<div><div class="nea-payment-name">' + p.name + '</div><div class="nea-payment-type">' + p.type + '</div></div>';
    html += '</div>';
  });
  document.getElementById('nea-payments').innerHTML = html;
  lucide.createIcons();
}

function renderInfo(info) {
  let html = '';
  html += '<div class="nea-rate-row"><span class="nea-rate-label">Name</span><span class="nea-rate-value">' + info.name + '</span></div>';
  html += '<div class="nea-rate-row"><span class="nea-rate-label">Hotline</span><span class="nea-rate-value">' + info.hotline + '</span></div>';
  html += '<div class="nea-rate-row"><span class="nea-rate-label">Email</span><span class="nea-rate-value">' + info.email + '</span></div>';
  html += '<div class="nea-rate-row"><span class="nea-rate-label">Website</span><span class="nea-rate-value"><a href="' + info.website + '" target="_blank" class="text-teal-600">' + info.website + '</a></span></div>';
  document.getElementById('nea-info').innerHTML = html;
}

function checkBill() {
  const sc = document.getElementById('nea-sc').value;
  if (!sc) {
    alert('SC Number लेख्नुहोस्');
    return;
  }
  
  const resultDiv = document.getElementById('nea-bill-result');
  resultDiv.innerHTML = '<div class="text-center py-4"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto"></i></div>';
  resultDiv.classList.add('show');
  lucide.createIcons();
  
  fetch('/api/nea-bill.php?sc=' + encodeURIComponent(sc))
    .then(r => r.json())
    .then(data => {
      if (data.ok && data.customer_bill) {
        const bill = data.customer_bill;
        let html = '<div class="nea-card-title mb-3">बिल विवरण</div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">SC Number</span><span class="nea-info-value">' + bill.sc_number + '</span></div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">Customer</span><span class="nea-info-value">' + bill.customer_name + '</span></div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">Billing Month</span><span class="nea-info-value">' + bill.billing_month + '</span></div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">Units Consumed</span><span class="nea-info-value">' + bill.units_consumed + '</span></div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">Energy Charge</span><span class="nea-info-value">Rs. ' + bill.energy_charge.toFixed(2) + '</span></div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">Service Charge</span><span class="nea-info-value">Rs. ' + bill.service_charge + '</span></div>';
        html += '<div class="nea-info-item"><span class="nea-info-label">Demand Charge</span><span class="nea-info-value">Rs. ' + bill.demand_charge + '</span></div>';
        html += '<div class="nea-total">Total: Rs. ' + bill.total_amount.toFixed(2) + '</div>';
        html += '<div class="text-center mt-3 text-sm text-gray-500">' + bill.note + '</div>';
        resultDiv.innerHTML = html;
      } else {
        resultDiv.innerHTML = '<div class="text-center text-red-600">बिल भेटिएन</div>';
      }
    });
}

loadNEAData();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
