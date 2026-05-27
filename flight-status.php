<?php
/**
 * Flight Status - Nepal Airport Flights
 * Real-time flight information for Nepal airports
 */
require_once __DIR__ . '/header.php';

$pageTitle = 'Flight Status | ' . SITE_NAME;
$pageDesc = 'नेपालका विमानस्थलहरूको वास्तविक समय उडान स्थिति।';
?>
<style>
.fs-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.fs-header {
  text-align: center;
  margin-bottom: 30px;
}
.fs-airport-select {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.fs-airport-btn {
  padding: 10px 20px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}
.fs-airport-btn.active {
  border-color: #0d9488;
  background: #f0fdfa;
  color: #0d9488;
}
.fs-type-toggle {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-bottom: 20px;
}
.fs-type-btn {
  padding: 8px 24px;
  border: none;
  border-radius: 20px;
  background: #e5e7eb;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}
.fs-type-btn.active {
  background: #0d9488;
  color: white;
}
.fs-flight-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s;
}
.fs-flight-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.fs-flight-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #f0fdfa, #cffafe);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d9488;
}
.fs-flight-info {
  flex: 1;
}
.fs-flight-number {
  font-weight: 700;
  color: #0b1220;
  font-size: 14px;
}
.fs-flight-airline {
  color: #64748b;
  font-size: 12px;
}
.fs-flight-route {
  color: #0d9488;
  font-weight: 600;
  font-size: 13px;
  margin-top: 4px;
}
.fs-flight-time {
  text-align: right;
}
.fs-flight-scheduled {
  font-weight: 700;
  color: #0b1220;
  font-size: 16px;
}
.fs-flight-status {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 6px;
  margin-top: 4px;
  display: inline-block;
}
.status-ontime { background: #dcfce7; color: #166534; }
.status-delayed { background: #fef9c3; color: #854d0e; }
.status-departed { background: #dbeafe; color: #1e40af; }
.status-landed { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.fs-loading {
  text-align: center;
  padding: 40px;
  color: #64748b;
}
.fs-empty {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}
</style>

<div class="fs-container">
  <div class="fs-header">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="plane" class="w-8 h-8 text-white"></i>
    </div>
    <div class="flex items-center justify-center gap-2 mb-2">
      <h1 class="text-2xl font-bold text-gray-900 ne">Flight Status</h1>
      <span class="flex items-center gap-1 text-[10px] bg-green-100 text-green-700 font-semibold px-2 py-1 rounded-full">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
        Live
      </span>
    </div>
    <p class="text-gray-500">नेपालका विमानस्थलहरूको वास्तविक समय उडान स्थिति</p>
  </div>

  <div class="fs-airport-select">
    <button class="fs-airport-btn active" data-airport="VNKT">काठमाडौं (KTM)</button>
    <button class="fs-airport-btn" data-airport="VNKL">पोखरा (PKR)</button>
    <button class="fs-airport-btn" data-airport="VNRC">भैरहवा (BWA)</button>
    <button class="fs-airport-btn" data-airport="VNSK">सिमरा (SIF)</button>
    <button class="fs-airport-btn" data-airport="VNDC">धनगढी (DHI)</button>
    <button class="fs-airport-btn" data-airport="VNSR">सुर्खेत (SKH)</button>
  </div>

  <div class="fs-type-toggle">
    <button class="fs-type-btn active" data-type="departures">Departures</button>
    <button class="fs-type-btn" data-type="arrivals">Arrivals</button>
  </div>

  <div class="fs-type-toggle">
    <button class="fs-type-btn active" data-route="all">All Flights</button>
    <button class="fs-type-btn" data-route="domestic">Domestic</button>
    <button class="fs-type-btn" data-route="international">International</button>
  </div>

  <div id="fs-flights">
    <div class="fs-loading">
      <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
      <p>Loading...</p>
    </div>
  </div>
</div>

<script>
let currentAirport = 'VNKT';
let currentType = 'departures';
let currentRoute = 'all';

function loadFlights() {
  const container = document.getElementById('fs-flights');
  container.innerHTML = '<div class="fs-loading"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i><p>Loading...</p></div>';
  
  fetch('/api/flight-status.php?airport=' + currentAirport + '&type=' + currentType)
    .then(r => r.json())
    .then(data => {
      if (!data.ok || !data.flights || data.flights.length === 0) {
        container.innerHTML = '<div class="fs-empty"><i data-lucide="plane" class="w-8 h-8 mx-auto mb-2"></i><p>No flights available</p></div>';
        lucide.createIcons();
        return;
      }
      
      // Filter by route type
      let flights = data.flights;
      if (currentRoute === 'domestic') {
        flights = flights.filter(f => isDomesticFlight(f.origin, f.destination));
      } else if (currentRoute === 'international') {
        flights = flights.filter(f => !isDomesticFlight(f.origin, f.destination));
      }
      
      if (flights.length === 0) {
        container.innerHTML = '<div class="fs-empty"><i data-lucide="plane" class="w-8 h-8 mx-auto mb-2"></i><p>No flights available for this filter</p></div>';
        lucide.createIcons();
        return;
      }
      
      let html = '';
      flights.forEach(f => {
        const statusClass = 'status-' + (f.status?.toLowerCase().replace(' ', '-') || 'ontime');
        const originName = getAirportName(f.origin);
        const destName = getAirportName(f.destination);
        const route = `${originName} → ${destName}`;
        const isDomestic = isDomesticFlight(f.origin, f.destination);
        
        html += `
          <div class="fs-flight-card">
            <div class="fs-flight-icon">
              <i data-lucide="plane" class="w-6 h-6"></i>
            </div>
            <div class="fs-flight-info">
              <div class="fs-flight-number">${f.flight}</div>
              <div class="fs-flight-airline">${f.airline}</div>
              <div class="fs-flight-route">${route} <span class="text-xs ${isDomestic ? 'text-green-600' : 'text-blue-600'}">(${isDomestic ? 'Domestic' : 'International'})</span></div>
            </div>
            <div class="fs-flight-time">
              <div class="fs-flight-scheduled">${f.scheduled}</div>
              <div class="fs-flight-status ${statusClass}">${f.status}</div>
            </div>
          </div>
        `;
      });
      
      container.innerHTML = html;
      lucide.createIcons();
    })
    .catch(err => {
      container.innerHTML = '<div class="fs-empty"><p>Error loading flights</p></div>';
    });
}

document.querySelectorAll('.fs-airport-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.fs-airport-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentAirport = btn.dataset.airport;
    loadFlights();
  });
});

document.querySelectorAll('.fs-type-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.fs-type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (btn.dataset.type) {
      currentType = btn.dataset.type;
    } else if (btn.dataset.route) {
      currentRoute = btn.dataset.route;
    }
    loadFlights();
  });
});

// Nepal airport codes for domestic flights
const nepalAirports = ['VNKT', 'VNKL', 'VNRC', 'VNBP', 'VNSK', 'VNPK', 'VNJP', 'VNTJ', 'VNPL', 'VNSR', 'VNDC', 'VNBK', 'VNMT'];

// Airport code to city name mapping
const airportNames = {
  'VNKT': 'काठमाडौं',
  'VNKL': 'पोखरा',
  'VNRC': 'भैरहवा',
  'VNBP': 'भैरहवा',
  'VNSK': 'सिमरा',
  'VNPK': 'पोखरा',
  'VNJP': 'जनकपुर',
  'VNTJ': 'ताप्लेजुङ',
  'VNPL': 'पाल्पा',
  'VNSR': 'सुर्खेत',
  'VNDC': 'धनगढी',
  'VNBK': 'बझाङ',
  'VNMT': 'माउन्टेन',
  'DEL': 'दिल्ली',
  'BOM': 'मुम्बई',
  'DXB': 'दुबई',
  'SIN': 'सिंगापुर',
  'BKK': 'बैंकक',
  'KUL': 'कुआलालम्पुर',
  'HKG': 'हङकङ',
  'DOH': 'दोहा',
  'ISB': 'इस्लामाबाद',
  'DAC': 'ढाका',
  'CMB': 'कोलम्बो',
  'KTM': 'काठमाडौं'
};

function getAirportName(code) {
  return airportNames[code] || code;
}

function isDomesticFlight(origin, destination) {
  return nepalAirports.includes(origin) && nepalAirports.includes(destination);
}

// Auto-refresh every 2 minutes
let autoRefreshInterval;

function startAutoRefresh() {
  if (autoRefreshInterval) clearInterval(autoRefreshInterval);
  autoRefreshInterval = setInterval(() => {
    loadFlights();
  }, 120000); // 2 minutes
}

function stopAutoRefresh() {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
    autoRefreshInterval = null;
  }
}

// Initial load and start auto-refresh
loadFlights();
startAutoRefresh();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
