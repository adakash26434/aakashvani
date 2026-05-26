<?php
/**
 * Hospital/Health Centers Directory
 * Nepal hospitals and health centers directory
 */
require_once __DIR__ . '/header.php';

$pageTitle = 'अस्पताल | ' . SITE_NAME;
$pageDesc = 'नेपालका अस्पताल र स्वास्थ्य केन्द्रहरूको निर्देशिका।';
?>
<style>
.hosp-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.hosp-header {
  text-align: center;
  margin-bottom: 30px;
}
.hosp-filter {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.hosp-select {
  flex: 1;
  min-width: 150px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
}
.hosp-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  transition: all 0.2s;
}
.hosp-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.hosp-card-title {
  font-weight: 700;
  color: #0b1220;
  font-size: 16px;
  margin-bottom: 4px;
}
.hosp-card-subtitle {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 12px;
}
.hosp-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  color: #64748b;
  font-size: 14px;
}
.hosp-info-icon {
  width: 20px;
  height: 20px;
  color: #0d9488;
}
.hosp-emergency {
  background: #fee2e2;
  color: #991b1b;
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  display: inline-block;
  margin-top: 12px;
}
.hosp-loading {
  text-align: center;
  padding: 40px;
  color: #64748b;
}
.hosp-empty {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}
</style>

<div class="hosp-container">
  <div class="hosp-header">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="heart-pulse" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 ne">अस्पताल</h1>
    <p class="text-gray-500">नेपालका अस्पताल र स्वास्थ्य केन्द्रहरू</p>
  </div>

  <div class="hosp-filter">
    <select id="hosp-city" class="hosp-select">
      <option value="">सबै शहर</option>
    </select>
    <select id="hosp-type" class="hosp-select">
      <option value="">सबै प्रकार</option>
    </select>
  </div>

  <div id="hosp-list">
    <div class="hosp-loading">
      <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
      <p>Loading...</p>
    </div>
  </div>
</div>

<script>
let currentCity = '';
let currentType = '';

function loadHospitals() {
  const container = document.getElementById('hosp-list');
  container.innerHTML = '<div class="hosp-loading"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i><p>Loading...</p></div>';
  
  let url = '/api/hospitals.php';
  if (currentCity) url += '?city=' + encodeURIComponent(currentCity);
  if (currentType) url += (currentCity ? '&' : '?') + 'type=' + encodeURIComponent(currentType);
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      if (!data.ok || !data.hospitals || data.hospitals.length === 0) {
        container.innerHTML = '<div class="hosp-empty"><i data-lucide="heart-pulse" class="w-8 h-8 mx-auto mb-2"></i><p>No hospitals found</p></div>';
        lucide.createIcons();
        return;
      }
      
      let html = '';
      data.hospitals.forEach(h => {
        html += `
          <div class="hosp-card">
            <div class="hosp-card-title">${h.name}</div>
            <div class="hosp-card-subtitle ne">${h.name_ne}</div>
            <div class="hosp-info-row">
              <i data-lucide="map-pin" class="hosp-info-icon"></i>
              <span>${h.city} (${h.city_ne})</span>
            </div>
            <div class="hosp-info-row">
              <i data-lucide="phone" class="hosp-info-icon"></i>
              <span>${h.phone}</span>
            </div>
            <div class="hosp-info-row">
              <i data-lucide="home" class="hosp-info-icon"></i>
              <span>${h.address}</span>
            </div>
            <div class="hosp-emergency">
              <i data-lucide="alert-circle" class="w-4 h-4 inline mr-1"></i>
              Emergency: ${h.emergency}
            </div>
          </div>
        `;
      });
      
      container.innerHTML = html;
      lucide.createIcons();
    })
    .catch(err => {
      container.innerHTML = '<div class="hosp-empty"><p>Error loading hospitals</p></div>';
    });
}

function loadFilters() {
  fetch('/api/hospitals.php')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const citySelect = document.getElementById('hosp-city');
        const typeSelect = document.getElementById('hosp-type');
        
        data.cities.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.name;
          opt.textContent = c.name + ' (' + c.name_ne + ')';
          citySelect.appendChild(opt);
        });
        
        data.types.forEach(t => {
          const opt = document.createElement('option');
          opt.value = t.name;
          opt.textContent = t.name + ' (' + t.name_ne + ')';
          typeSelect.appendChild(opt);
        });
      }
    });
}

document.getElementById('hosp-city').addEventListener('change', (e) => {
  currentCity = e.target.value;
  loadHospitals();
});

document.getElementById('hosp-type').addEventListener('change', (e) => {
  currentType = e.target.value;
  loadHospitals();
});

loadFilters();
loadHospitals();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
