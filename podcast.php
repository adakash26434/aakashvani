<?php
/**
 * /podcast.php — Dedicated Podcast Hub
 * Features: User-uploaded podcasts + RSS import + offline download
 * Admin can manage podcast content via /admin/admin-entertainment.php
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

$pageTitle = 'पोडकास्ट | ' . SITE_NAME;
$pageDesc  = 'नेपाली पोडकास्ट, समाचार र कथाहरू। अफलाइन सुन्न डाउनलोड गर्नुहोस्।';

if (function_exists('renderHeader')) renderHeader($pageTitle, $pageDesc);
else echo "<!doctype html><html lang='ne'><head><meta charset='utf-8'><title>".htmlspecialchars($pageTitle)."</title><meta name='description' content='".htmlspecialchars($pageDesc)."'></head><body>";
?>
<div class="pc-wrap">
  <div class="pc-hero">
    <h1>🎙️ पोडकास्ट</h1>
    <p>नेपाली पोडकास्ट, समाचार, कथा र साक्षात्कार। सबै डिभाइसमा सुन्नुहोस्। अफलाइनको लागि डाउनलोड गर्नुहोस्।</p>
  </div>

  <!-- Sticky player -->
  <div class="pc-player" id="pcPlayer">
    <div class="pc-player-icon">🎙️</div>
    <div class="now">
      <b id="pcName">पोडकास्ट छान्नुहोस्</b>
      <span id="pcMeta">तल बाट कुनै पोडकास्ट सुरु गर्नुहोस्</span>
    </div>
    <div class="ctrl">
      <button onclick="pcToggle()" id="pcBtn">▶</button>
      <button onclick="pcStop()" title="Stop">⏹</button>
    </div>
    <audio id="pcAudio" preload="none" crossorigin="anonymous"></audio>
  </div>

  <div class="pc-offline-tip">💡 <b>Offline Tip:</b> "⬇ डाउनलोड" थिच्नुहोस्। इन्टरनेट बिना पनि सुन्न सकिन्छ।</div>

  <!-- Featured section -->
  <h2 class="pc-section-title">⭐ विशेष पोडकास्ट</h2>
  <div id="pcFeatured" class="pc-featured-grid">
    <p class="col-span-full text-sm text-slate-400 text-center">उपलब्ध हुँदैछ...</p>
  </div>

  <!-- All podcasts -->
  <h2 class="pc-section-title">📻 सबै पोडकास्टहरू</h2>
  <div id="pcGrid" class="pc-grid">
    <p class="col-span-full text-sm text-slate-400 text-center">लोड हुँदैछ...</p>
  </div>
</div>

<script>
const audio = document.getElementById('pcAudio');
const btn   = document.getElementById('pcBtn');
let currentUrl = null;
let allPodcasts = [];

// Fetch podcasts from API or fallback data
async function loadPodcasts() {
  try {
    // Try to fetch from admin API (if available)
    const resp = await fetch('/api/get-podcasts.php').then(r => r.json()).catch(() => ({ success: false }));
    if (resp.success && resp.data) {
      allPodcasts = resp.data;
    } else {
      // Fallback: Get from radio.php function
      allPodcasts = await fetch('/api/get-podcasts.php')
        .then(r => r.json())
        .then(d => d.data || [])
        .catch(() => []);
    }
  } catch (e) {
    console.warn('Podcasts failed to load:', e.message);
  }
  
  renderPodcasts();
}

function renderPodcasts() {
  const featured = allPodcasts.filter(p => p.featured);
  const all = allPodcasts;
  
  // Featured grid
  const featGrid = document.getElementById('pcFeatured');
  if (featured.length > 0) {
    featGrid.innerHTML = featured.map(p => `
      <div class="pc-card" onclick="pcPlay(${JSON.stringify(p).replace(/"/g, '&quot;')})">
        <div class="pc-img" style="${p.cover_image ? `background-image:url('${p.cover_image}')` : ''}">${!p.cover_image ? '🎙️' : ''}</div>
        <div class="pc-body">
          <h3>${htmlEsc(p.title)}</h3>
          <p>${htmlEsc((p.description || '').substring(0, 60))}</p>
          <div class="pc-meta">
            <span>${p.source_name || 'आकाशवाणी'}</span>
            <span>${p.views || 0} views</span>
          </div>
          <div class="pc-actions">
            <button onclick="event.stopPropagation(); pcPlay(${JSON.stringify(p).replace(/"/g, '&quot;')})">▶ सुन्नुहोस्</button>
            <button onclick="event.stopPropagation(); pcDownload('${p.audio_url}')">⬇ डाउनलोड</button>
          </div>
        </div>
      </div>
    `).join('');
  } else {
    featGrid.innerHTML = '<p style="grid-column:1/-1;color:#999;text-align:center;padding:30px">विशेष पोडकास्ट अहिले उपलब्ध छैन</p>';
  }
  
  // All podcasts
  const grid = document.getElementById('pcGrid');
  if (all.length > 0) {
    grid.innerHTML = all.map(p => `
      <div class="pc-card" onclick="pcPlay(${JSON.stringify(p).replace(/"/g, '&quot;')})">
        <div class="pc-img" style="${p.cover_image ? `background-image:url('${p.cover_image}')` : ''}">${!p.cover_image ? '🎙️' : ''}</div>
        <div class="pc-body">
          <h3>${htmlEsc(p.title)}</h3>
          <p>${htmlEsc((p.description || '').substring(0, 60))}</p>
          <div class="pc-meta">
            <span>${p.source_name || 'आकाशवाणी'}</span>
            <span>${p.duration_seconds ? formatDuration(p.duration_seconds) : 'N/A'}</span>
          </div>
          <div class="pc-actions">
            <button onclick="event.stopPropagation(); pcPlay(${JSON.stringify(p).replace(/"/g, '&quot;')})">▶ सुन्नुहोस्</button>
            <button onclick="event.stopPropagation(); pcDownload('${p.audio_url}')">⬇</button>
          </div>
        </div>
      </div>
    `).join('');
  } else {
    grid.innerHTML = '<p style="grid-column:1/-1;color:#999;text-align:center;padding:30px">अहिले कुनै पोडकास्ट उपलब्ध छैन</p>';
  }
}

function pcPlay(p) {
  document.getElementById('pcName').textContent = p.title;
  document.getElementById('pcMeta').textContent = (p.source_name || 'आकाशवाणी') + ' · ' + (p.duration_seconds ? formatDuration(p.duration_seconds) : '');
  
  document.querySelectorAll('.pc-card').forEach(c => c.classList.remove('playing'));
  event && event.currentTarget && event.currentTarget.classList && event.currentTarget.classList.add('playing');
  
  audio.src = p.audio_url;
  audio.play().then(() => { btn.textContent = '⏸'; currentUrl = p.audio_url; })
              .catch(e => { btn.textContent = '▶'; alert('बजाउन सकिएन: ' + e.message); });
}

function pcToggle() {
  if (!audio.src) return;
  if (audio.paused) { audio.play(); btn.textContent = '⏸'; }
  else { audio.pause(); btn.textContent = '▶'; }
}

function pcStop() {
  audio.pause(); audio.src=''; btn.textContent='▶';
  document.querySelectorAll('.pc-card').forEach(c => c.classList.remove('playing'));
}

async function pcDownload(url) {
  try {
    const cache = await caches.open('podcast-offline-v1');
    await cache.add(url);
    alert('✓ अफलाइन को लागि सेभ भयो। इन्टरनेट बिना पनि बजाउन मिल्छ।');
  } catch (e) { alert('डाउनलोड असफल: ' + e.message); }
}

function formatDuration(seconds) {
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  return h > 0 ? `${h}:${String(m).padStart(2,'0')}h` : `${m}m`;
}

function htmlEsc(s) {
  return (s || '').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));
}

audio.addEventListener('ended', () => { btn.textContent = '▶'; });

// Load podcasts on page load
loadPodcasts();
</script>

<?php if (function_exists('renderFooter')) renderFooter(); else echo "</body></html>"; ?>
