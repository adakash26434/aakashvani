<?php
/**
 * Admin Data Manager UI
 * For managing data when auto-fetch APIs fail
 */
requireAdmin();
$pageTitle = 'Data Manager · आकाशवाणी';
@include __DIR__ . '/../header.php';
?>

<style>
.admin-wrap { padding: 20px; max-width: 1200px; margin: 0 auto; }
.admin-title { font-size: 24px; font-weight: 700; margin-bottom: 20px; color: #0f172a; }

/* API Status Grid */
.api-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 30px;
}
.status-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #e2e8f0;
    transition: all 0.2s;
}
.status-card.ok { border-color: #10b981; background: #ecfdf5; }
.status-card.error { border-color: #ef4444; background: #fef2f2; }
.status-card h3 { font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #64748b; }
.status-card .code { font-size: 12px; color: #94a3b8; }
.status-card .time { font-size: 11px; color: #64748b; margin-top: 4px; }

/* Data Sections */
.data-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}
.data-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.data-card h3 {
    font-size: 16px; font-weight: 700;
    margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.data-meta {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.data-actions {
    display: flex; gap: 8px;
    flex-wrap: wrap;
}
.btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.btn-primary { background: #0d9488; color: #fff; }
.btn-primary:hover { background: #0f766e; }
.btn-secondary { background: #f1f5f9; color: #475569; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-danger { background: #fef2f2; color: #dc2626; }
.btn-danger:hover { background: #fee2e2; }

/* Editor Modal */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-overlay.active { display: flex; }
.modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h2 { font-size: 18px; font-weight: 700; }
.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}
.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: monospace;
}
.form-group textarea { min-height: 200px; resize: vertical; }

/* Auto refresh toggle */
.auto-refresh {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 12px;
}
.toggle-switch {
    position: relative;
    width: 48px;
    height: 24px;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: #cbd5e1;
    border-radius: 24px;
    transition: 0.3s;
}
.slider:before {
    content: '';
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: 0.3s;
}
input:checked + .slider { background: #0d9488; }
input:checked + .slider:before { transform: translateX(24px); }
</style>

<div class="admin-wrap">
    <h1 class="admin-title ne">📊 डाटा व्यवस्थापन (Data Manager)</h1>
    
    <p class="ne" style="color:#64748b;margin-bottom:20px;">
        जब auto-fetch API हरुले काम गर्दैनन्, यहाँबाट म्यानुअली डाटा व्यवस्थापन गर्न सकिन्छ।
    </p>

    <!-- API Status -->
    <h2 class="ne" style="font-size:16px;font-weight:700;margin-bottom:12px;">🔌 API स्थिति (API Status)</h2>
    <div class="api-status-grid" id="apiStatus">
        <div class="status-card" style="text-align:center;padding:30px;">
            <div class="skeleton" style="width:100%;height:20px;margin-bottom:10px;"></div>
            <div class="skeleton" style="width:60%;height:15px;margin:0 auto;"></div>
        </div>
    </div>

    <!-- Auto Refresh Toggle -->
    <div class="auto-refresh">
        <label class="toggle-switch">
            <input type="checkbox" id="autoRefresh" checked>
            <span class="slider"></span>
        </label>
        <span class="ne" style="font-size:14px;">स्वचालित रिफ्रेश (30 सेकेन्ड)</span>
        <button class="btn btn-secondary" onclick="loadData()" style="margin-left:auto;">
            <span class="ne">अहिले रिफ्रेश गर्नुहोस्</span>
        </button>
    </div>

    <!-- Data Sections -->
    <h2 class="ne" style="font-size:16px;font-weight:700;margin-bottom:12px;">📁 डाटा सेक्सनहरू (Data Sections)</h2>
    <div class="data-sections" id="dataSections">
        <div class="data-card">
            <div class="skeleton" style="width:60%;height:20px;margin-bottom:15px;"></div>
            <div class="skeleton" style="width:80%;height:15px;margin-bottom:10px;"></div>
            <div class="skeleton" style="width:40%;height:35px;"></div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="ne" id="modalTitle">डाटा सम्पादन</h2>
            <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="ne">डाटा (JSON format)</label>
                <textarea id="dataEditor" placeholder='{"key": "value"}'></textarea>
            </div>
            <div class="form-group">
                <label class="ne">Source / स्रोत</label>
                <input type="text" id="dataSource" placeholder="e.g., NRB, NOC, Admin">
            </div>
            <div class="form-group">
                <label class="ne">Note / टिप्पणी</label>
                <input type="text" id="dataNote" placeholder="Any notes about this data">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">
                <span class="ne">रद्द गर्नुहोस्</span>
            </button>
            <button class="btn btn-primary" onclick="saveData()">
                <span class="ne">सेव गर्नुहोस्</span>
            </button>
        </div>
    </div>
</div>

<script>
let currentSection = '';
let refreshInterval = null;

// Load API status
async function loadApiStatus() {
    try {
        const res = await fetch('/api/admin-data-manager.php?action=api-status');
        const data = await res.json();
        
        if (!data.ok) return;
        
        const container = document.getElementById('apiStatus');
        container.innerHTML = Object.entries(data.apis).map(([name, status]) => `
            <div class="status-card ${status.status}">
                <h3>${name.toUpperCase()}</h3>
                <div style="font-size:20px;font-weight:700;color:${status.status === 'ok' ? '#10b981' : '#ef4444'};">
                    ${status.status === 'ok' ? '✓ Online' : '✗ Error'}
                </div>
                <div class="code">HTTP ${status.code}</div>
                <div class="time">${status.response_time_ms}ms</div>
            </div>
        `).join('');
    } catch (e) {
        console.error('API status load failed:', e);
    }
}

// Load data sections
async function loadData() {
    try {
        const res = await fetch('/api/admin-data-manager.php?action=list');
        const data = await res.json();
        
        if (!data.ok) return;
        
        const container = document.getElementById('dataSections');
        container.innerHTML = Object.entries(data.sections).map(([key, section]) => `
            <div class="data-card">
                <h3>
                    <i data-lucide="${section.is_auto ? 'refresh-cw' : 'edit-3'}" class="w-5 h-5" 
                        style="color:${section.is_auto ? '#10b981' : '#f59e0b'};"></i>
                    <span class="ne">${section.name}</span>
                    ${!section.is_auto ? '<span style="font-size:11px;background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:20px;margin-left:auto;">म्यानुअल</span>' : ''}
                </h3>
                <div class="data-meta ne">
                    <div>Last Updated: ${section.last_updated || 'Never'}</div>
                    <div>Source: ${section.source || 'Auto'}</div>
                    <div>Status: ${section.has_data ? '✓ Data available' : '✗ No data'}</div>
                </div>
                <div class="data-actions">
                    <button class="btn btn-primary" onclick="editData('${key}')">
                        <span class="ne">सम्पादन गर्नुहोस्</span>
                    </button>
                    <button class="btn btn-secondary" onclick="viewData('${key}')">
                        <span class="ne">हेर्नुहोस्</span>
                    </button>
                    <button class="btn btn-danger" onclick="clearData('${key}')">
                        <span class="ne">खाली गर्नुहोस्</span>
                    </button>
                </div>
            </div>
        `).join('');
        
        if (window.lucide) lucide.createIcons();
    } catch (e) {
        console.error('Data load failed:', e);
    }
}

// Edit data
async function editData(section) {
    currentSection = section;
    try {
        const res = await fetch(`/api/admin-data-manager.php?action=get&section=${section}`);
        const data = await res.json();
        
        document.getElementById('dataEditor').value = JSON.stringify(data.data || {}, null, 2);
        document.getElementById('dataSource').value = data.data?.source || '';
        document.getElementById('dataNote').value = data.data?.note || '';
        document.getElementById('modalTitle').textContent = `${section} - डाटा सम्पादन`;
        document.getElementById('editModal').classList.add('active');
    } catch (e) {
        alert('डाटा लोड गर्न समस्या भयो');
    }
}

// Save data
async function saveData() {
    try {
        const jsonData = JSON.parse(document.getElementById('dataEditor').value);
        jsonData.source = document.getElementById('dataSource').value;
        jsonData.note = document.getElementById('dataNote').value;
        
        const res = await fetch(`/api/admin-data-manager.php?action=save&section=${currentSection}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(jsonData)
        });
        
        const result = await res.json();
        if (result.ok) {
            closeModal();
            loadData();
            alert('डाटा सेव भयो!');
        } else {
            alert('सेव गर्न समस्या: ' + result.error);
        }
    } catch (e) {
        alert('JSON format मिलेन। कृपया सही format राख्नुहोस्।');
    }
}

// View data
async function viewData(section) {
    window.open(`/api/admin-data-manager.php?action=get&section=${section}`, '_blank');
}

// Clear data
async function clearData(section) {
    if (!confirm('के तपाईं यो सेक्सनको डाटा खाली गर्न निश्चित हुनुहुन्छ?')) return;
    
    try {
        const res = await fetch(`/api/admin-data-manager.php?action=clear&section=${section}`);
        const result = await res.json();
        if (result.ok) {
            loadData();
            alert('डाटा खाली गरियो!');
        }
    } catch (e) {
        alert('खाली गर्न समस्या भयो');
    }
}

// Close modal
function closeModal() {
    document.getElementById('editModal').classList.remove('active');
    currentSection = '';
}

// Auto refresh toggle
document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        loadApiStatus();
        loadData();
    }, 30000);
}

function stopAutoRefresh() {
    clearInterval(refreshInterval);
}

// Initial load
loadApiStatus();
loadData();
startAutoRefresh();

if (window.lucide) lucide.createIcons();
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
