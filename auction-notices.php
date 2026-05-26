<?php
/**
 * Auction Notices Page - Bank/Sahakari/Government Auctions
 */

require_once __DIR__ . '/header.php';
?>

<!-- ═══ AUCTION NOTICES PAGE ═══════════════════════════════════════════════ -->
<section class="px-4 py-4 max-w-4xl mx-auto">
  
  <!-- Header -->
  <div class="flex items-center gap-4 mb-6">
    <a href="/index.php" class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
      <i data-lucide="arrow-left" class="w-5 h-5 text-gray-600"></i>
    </a>
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center">
        <i data-lucide="gavel" class="w-6 h-6 text-rose-600"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-gray-900 ne">लिलामी सूचना</h1>
        <p class="text-sm text-gray-500">Bank, Sahakari & Government Auctions</p>
      </div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
    <button onclick="filterAuctions('all')" class="filter-btn active px-4 py-2 rounded-full text-sm font-medium bg-teal-600 text-white whitespace-nowrap" data-filter="all">
      सबै
    </button>
    <button onclick="filterAuctions('property')" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap" data-filter="property">
      जग्गा/भवन
    </button>
    <button onclick="filterAuctions('vehicle')" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap" data-filter="vehicle">
      गाडी
    </button>
    <button onclick="filterAuctions('gold')" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap" data-filter="gold">
      सुन
    </button>
    <button onclick="filterAuctions('government')" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 whitespace-nowrap" data-filter="government">
      सरकारी
    </button>
  </div>

  <!-- Loading State -->
  <div id="auction-loading" class="text-center py-12">
    <div class="w-12 h-12 border-4 border-teal-200 border-t-teal-600 rounded-full animate-spin mx-auto mb-4"></div>
    <p class="text-gray-500 ne">लिलामी सूचना लोड हुँदैछ...</p>
  </div>

  <!-- Auction List -->
  <div id="auction-list" class="space-y-4 hidden">
    <!-- Notices will be loaded here -->
  </div>

  <!-- Empty State -->
  <div id="auction-empty" class="text-center py-12 hidden">
    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
      <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
    </div>
    <p class="text-gray-500 ne">कुनै लिलामी सूचना फेला परेन</p>
  </div>

</section>

<script>
let allAuctions = [];

// Load auction notices
async function loadAuctions() {
  try {
    const response = await fetch('/api/auction-notices.php');
    const result = await response.json();
    
    if (result.ok && result.data) {
      allAuctions = result.data;
      renderAuctions(allAuctions);
    } else {
      document.getElementById('auction-loading').classList.add('hidden');
      document.getElementById('auction-empty').classList.remove('hidden');
    }
  } catch (error) {
    console.error('Error loading auctions:', error);
    document.getElementById('auction-loading').classList.add('hidden');
    document.getElementById('auction-empty').classList.remove('hidden');
  }
}

// Render auction notices
function renderAuctions(auctions) {
  const container = document.getElementById('auction-list');
  const loading = document.getElementById('auction-loading');
  const empty = document.getElementById('auction-empty');
  
  loading.classList.add('hidden');
  
  if (auctions.length === 0) {
    empty.classList.remove('hidden');
    container.classList.add('hidden');
    return;
  }
  
  empty.classList.add('hidden');
  container.classList.remove('hidden');
  
  container.innerHTML = auctions.map(auction => `
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" data-type="${auction.type}">
      <div class="p-4">
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2 py-1 rounded text-xs font-medium ${getTypeColor(auction.type)}">
                ${getTypeLabel(auction.type)}
              </span>
              <span class="text-xs text-gray-500 ne">${auction.institution}</span>
            </div>
            <h3 class="font-bold text-gray-900 ne">${auction.title}</h3>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-3 mb-3">
          <div class="p-2 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 ne">स्थान</div>
            <div class="text-sm font-medium text-gray-900">${auction.location}</div>
          </div>
          <div class="p-2 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 ne">न्यूनतम बोली</div>
            <div class="text-sm font-medium text-teal-600">${auction.minimum_bid}</div>
          </div>
          <div class="p-2 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 ne">लिलामी मिति</div>
            <div class="text-sm font-medium text-gray-900">${auction.auction_date} • ${auction.auction_time}</div>
          </div>
          <div class="p-2 bg-gray-50 rounded-lg">
            <div class="text-xs text-gray-500 ne">अन्तिम मिति</div>
            <div class="text-sm font-medium text-rose-600">${auction.last_date_bid}</div>
          </div>
        </div>
        
        <div class="flex items-center justify-between">
          <div class="text-xs text-gray-500">
            <i data-lucide="phone" class="w-3 h-3 inline mr-1"></i>
            ${auction.contact}
          </div>
          ${auction.source_url && auction.source_url !== '#' ? 
            `<a href="${auction.source_url}" target="_blank" class="text-teal-600 text-sm font-medium hover:text-teal-700">
              विस्तृत →
            </a>` : 
            `<span class="text-gray-400 text-sm">विस्तृत उपलब्ध छैन</span>`
          }
        </div>
      </div>
    </div>
  `).join('');
  
  if (window.lucide) {
    lucide.createIcons();
  }
}

// Filter auctions
function filterAuctions(type) {
  // Update button states
  document.querySelectorAll('.filter-btn').forEach(btn => {
    if (btn.dataset.filter === type) {
      btn.classList.remove('bg-gray-100', 'text-gray-700');
      btn.classList.add('bg-teal-600', 'text-white');
    } else {
      btn.classList.remove('bg-teal-600', 'text-white');
      btn.classList.add('bg-gray-100', 'text-gray-700');
    }
  });
  
  // Filter and render
  if (type === 'all') {
    renderAuctions(allAuctions);
  } else {
    const filtered = allAuctions.filter(a => a.type === type);
    renderAuctions(filtered);
  }
}

// Helper functions
function getTypeColor(type) {
  const colors = {
    'property': 'bg-blue-100 text-blue-700',
    'vehicle': 'bg-purple-100 text-purple-700',
    'gold': 'bg-amber-100 text-amber-700',
    'government': 'bg-rose-100 text-rose-700'
  };
  return colors[type] || 'bg-gray-100 text-gray-700';
}

function getTypeLabel(type) {
  const labels = {
    'property': 'जग्गा/भवन',
    'vehicle': 'गाडी',
    'gold': 'सुन',
    'government': 'सरकारी'
  };
  return labels[type] || type;
}

// Load on page load
document.addEventListener('DOMContentLoaded', loadAuctions);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
