<?php
/**
 * Exam Results
 * Nepal exam board results
 */
require_once __DIR__ . '/header.php';

$pageTitle = 'परीक्षा परिणाम | ' . SITE_NAME;
$pageDesc = 'नेपालका परीक्षा बोर्डका परिणामहरू।';
?>
<style
.exam-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.exam-header {
  text-align: center;
  margin-bottom: 30px;
}
.exam-filter {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.exam-select {
  flex: 1;
  min-width: 150px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
}
.exam-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  transition: all 0.2s;
}
.exam-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}
.exam-card-title {
  font-weight: 700;
  color: #0b1220;
  font-size: 16px;
  margin-bottom: 4px;
}
.exam-card-subtitle {
  color: #64748b;
  font-size: 14px;
  margin-bottom: 12px;
}
.exam-info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  color: #64748b;
  font-size: 14px;
}
.exam-info-icon {
  width: 20px;
  height: 20px;
  color: #0d9488;
}
.exam-status {
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
  display: inline-block;
  margin-top: 12px;
}
.status-published {
  background: #dcfce7;
  color: #166534;
}
.status-pending {
  background: #fef9c3;
  color: #854d0e;
}
.exam-link {
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
.exam-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
}
.exam-loading {
  text-align: center;
  padding: 40px;
  color: #64748b;
}
.exam-empty {
  text-align: center;
  padding: 40px;
  color: #94a3b8;
}
</style>

<div class="exam-container">
  <div class="exam-header">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 ne">परीक्षा परिणाम</h1>
    <p class="text-gray-500">नेपालका परीक्षा बोर्डका परिणामहरू</p>
  </div>

  <div class="exam-filter">
    <select id="exam-board" class="exam-select">
      <option value="">सबै बोर्ड</option>
    </select>
    <select id="exam-year" class="exam-select">
      <option value="">सबै वर्ष</option>
    </select>
  </div>

  <div id="exam-list">
    <div class="exam-loading">
      <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
      <p>Loading...</p>
    </div>
  </div>
</div>

<script>
let currentBoard = '';
let currentYear = '';

function loadExamResults() {
  const container = document.getElementById('exam-list');
  container.innerHTML = '<div class="exam-loading"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i><p>Loading...</p></div>';
  
  let url = '/api/exam-results.php';
  if (currentBoard) url += '?board=' + encodeURIComponent(currentBoard);
  if (currentYear) url += (currentBoard ? '&' : '?') + 'year=' + encodeURIComponent(currentYear);
  
  fetch(url)
    .then(r => r.json())
    .then(data => {
      if (!data.ok || !data.results || data.results.length === 0) {
        container.innerHTML = '<div class="exam-empty"><i data-lucide="graduation-cap" class="w-8 h-8 mx-auto mb-2"></i><p>No results found</p></div>';
        lucide.createIcons();
        return;
      }
      
      let html = '';
      data.results.forEach(r => {
        const statusClass = r.status === 'Published' ? 'status-published' : 'status-pending';
        html += `
          <div class="exam-card">
            <div class="exam-card-title">${r.exam}</div>
            <div class="exam-card-subtitle ne">${r.exam_ne}</div>
            <div class="exam-info-row">
              <i data-lucide="building" class="exam-info-icon"></i>
              <span>${r.board} (${r.board_ne})</span>
            </div>
            <div class="exam-info-row">
              <i data-lucide="calendar" class="exam-info-icon"></i>
              <span>Year: ${r.year}</span>
            </div>
            <div class="exam-info-row">
              <i data-lucide="clock" class="exam-info-icon"></i>
              <span>Result Date: ${r.result_date}</span>
            </div>
            <div class="exam-status ${statusClass}">${r.status}</div>
            <a href="${r.link}" target="_blank" class="exam-link">
              <i data-lucide="external-link" class="w-4 h-4 inline mr-1"></i>
              View Result
            </a>
          </div>
        `;
      });
      
      container.innerHTML = html;
      lucide.createIcons();
    })
    .catch(err => {
      container.innerHTML = '<div class="exam-empty"><p>Error loading results</p></div>';
    });
}

function loadFilters() {
  fetch('/api/exam-results.php')
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const boardSelect = document.getElementById('exam-board');
        const yearSelect = document.getElementById('exam-year');
        
        data.boards.forEach(b => {
          const opt = document.createElement('option');
          opt.value = b.name;
          opt.textContent = b.name + ' (' + b.name_ne + ') - ' + b.full_name;
          boardSelect.appendChild(opt);
        });
        
        data.years.forEach(y => {
          const opt = document.createElement('option');
          opt.value = y.value;
          opt.textContent = y.label;
          yearSelect.appendChild(opt);
        });
      }
    });
}

document.getElementById('exam-board').addEventListener('change', (e) => {
  currentBoard = e.target.value;
  loadExamResults();
});

document.getElementById('exam-year').addEventListener('change', (e) => {
  currentYear = e.target.value;
  loadExamResults();
});

loadFilters();
loadExamResults();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
