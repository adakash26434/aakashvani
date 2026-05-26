<?php
/**
 * Government Tenders
 * Nepal Government Tender Notices
 */
require_once __DIR__ . '/header.php';

$pageTitle = 'सरकारी टेन्डर | ' . SITE_NAME;
$pageDesc = 'नेपाल सरकारका टेन्डर सूचनाहरू।';
?>
<style
.tender-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}
.tender-header {
  text-align: center;
  margin-bottom: 30px;
}
.tender-filter {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.tender-select {
  flex: 1;
  min-width: 150px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
}
.tender-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  transition: all 0.2s;
}
.tender-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.tender-card-title {
  font-weight: 700;
  color: #0b1220;
  font-size: 16px;
  margin-bottom: 4px;
}
.tender-card-subtitle {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 12px;
}
.tender-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  color: #64748b;
  font-size: 14px;
}
.tender-info-icon {
  width: 20px;
  height: 20px;
  color: #0d9488;
}
.tender-status {
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  display: inline-block;
  margin-top: 12px;
}
.status-open {
  background: #dcfce7;
  color: #166534;
}
.status-closed {
  background: #fee2e2;
  color: #991b1b;
}
.tender-link {
  display: inline-block;
  margin-top: 12px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #0d9488, #0891b2);
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}
.tender-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}
.tender-loading {
  text-align: center;
  padding: 40px;
  color: #64748b;
}
.tender-empty {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}
</style>

<div class="tender-container">
  <div class="tender-header">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="file-text" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 ne">सरकारी टेन्डर</h1>
    <p class="text-gray-500">नेपाल सरकारका टेन्डर सूचनाहरू</p>
  </div>

  <div class="tender-filter">
    <select id="tender-category" class="tender-select">
      <option value="">सबै कोटी</option>
    </select>
    <select id="tender-ministry" class="tender-select">
      <option value="">सबै मन्त्रालय</option>
    </select>
  </div>

  <div id="tender-list">
    <div class="tender-loading">
      <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
      <p>Loading...</p>
    </div>
  </div>
</div>

<script>
let currentCategory = '';
let currentMinistry = '';

function loadTenders() {
  const container = document.getElementById('tender-list');
  container.innerHTML = '<div class="tender-loading"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i><p>Loading...</p></div>';
  
  let url = '/api/government-tenders.php';
  if (currentCategory) url += '?category=' + encodeURIComponent(currentCategory);
  if (currentMinistry) url += (currentCategory ? '&' : '?') + 'ministry=' + encodeURIComponent(currentMinistry);
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      if (!data.ok || !data.tenders || data.tenders.length === 0) {
        container.innerHTML = '<div class="tender-empty"><i data-lucide="file-text" class="w-8 h-8 mx-auto mb-2"></i><p>No tenders found</p></div>';
        lucide.createIcons();
        return;
      }
      
      let html = '';
      data.tenders.forEach(t => {
        const statusClass = t.status === 'Open' ? 'status-open' : 'status-closed';
        html += `
          <div class="tender-card">
            <div class="tender-card-title">${t.title}</div>
            <div class="tender-card-subtitle ne">${t.title_ne}</div>
            <div class="tender-info-row">
              <i data-lucide="building" class="tender-info-icon"></i>
              <span>${t.ministry}</span>
            </div>
            <div class="tender-info-row">
              <i data-lucide="map-pin" class="tender-info-icon"></i>
              <span>${t.location}</span>
            </div>
            <div class="tender-info-row">
              <i data-lucide="calendar" class="tender-info-icon"></i>
              <span>Deadline: ${t.deadline_ne}</span>
            </div>
            <div class="tender-info-row">
              <i data-lucide="banknote" class="tender-info-icon"></i>
              <span>${t.estimated_cost}</span>
            </div>
            <div class="tender-status ${statusClass}">${t.status}</div>
            <a href="${t.link}" target="_blank" class="tender-link">
              <i data-lucide="external-link" class="w-4 h-4 inline mr-1"></i>
              View Details
            </a>
          </div>
        `;
      });
      
      container.innerHTML = html;
      lucide.createIcons();
    })
    .catch(err => {
      container.innerHTML = '<div class="tender-empty"><p>Error loading tenders</p></div>';
    });
}

function loadFilters() {
  fetch('/api/government-tenders.php')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const categorySelect = document.getElementById('tender-category');
        const ministrySelect = document.getElementById('tender-ministry');
        
        data.categories.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.name;
          opt.textContent = c.name + ' (' + c.name_ne + ')';
          categorySelect.appendChild(opt);
        });
        
        data.ministries.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.name;
          opt.textContent = m.name + ' (' + m.name_ne + ')';
          ministrySelect.appendChild(opt);
        });
      }
    });
}

document.getElementById('tender-category').addEventListener('change', (e) => {
  currentCategory = e.target.value;
  loadTenders();
});

document.getElementById('tender-ministry').addEventListener('change', (e) => {
  currentMinistry = e.target.value;
  loadTenders();
});

loadFilters();
loadTenders();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
