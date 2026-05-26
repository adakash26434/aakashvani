<?php
/**
 * /radio.php — Online radio + offline-capable podcasts
 * Streams live via HTML5 audio. Podcasts cached by Service Worker for offline.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

$stations = getRadioStations(true);
$podcasts = getRadioPodcasts(12);

$pageTitle = 'अनलाइन रेडियो | ' . SITE_NAME;
$pageDesc  = 'नेपालका प्रमुख FM, समाचार र संगीत रेडियो लाइभ सुन्नुहोस्।';

if (function_exists('renderHeader')) renderHeader($pageTitle, $pageDesc);
else echo "<!doctype html><html lang='ne'><head><meta charset='utf-8'><title>".htmlspecialchars($pageTitle)."</title></head><body>";
?>
<style>
  .rd-wrap{max-width:1100px;margin:0 auto;padding:24px 16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif}
  .rd-hero{background:linear-gradient(135deg,#7c3aed,#ec4899);color:#fff;border-radius:16px;padding:28px;margin-bottom:24px}
  .rd-hero h1{margin:0 0 8px;font-size:28px}
  .rd-hero p{margin:0;opacity:.95}
  .rd-player{position:sticky;top:8px;background:#0f172a;color:#fff;border-radius:14px;padding:16px;display:flex;align-items:center;gap:14px;z-index:10;box-shadow:0 10px 30px -10px rgba(15,23,42,.5);margin-bottom:24px}
  .rd-player .now{flex:1;min-width:0}
  .rd-player .now b{display:block;font-size:15px;margin-bottom:2px}
  .rd-player .now span{font-size:12px;opacity:.7}
  .rd-player audio{display:none}
  .rd-player .ctrl{display:flex;gap:8px}
  .rd-player button{background:#7c3aed;color:#fff;border:none;width:42px;height:42px;border-radius:50%;cursor:pointer;font-size:18px}
  .rd-player button:hover{background:#6d28d9}
  .rd-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;margin-bottom:30px}
  .rd-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px;cursor:pointer;transition:all .2s;text-align:center}
  .rd-card:hover{border-color:#7c3aed;transform:translateY(-3px);box-shadow:0 8px 20px -8px rgba(124,58,237,.3)}
  .rd-card.playing{background:#7c3aed;color:#fff;border-color:#7c3aed}
  .rd-card .logo{width:64px;height:64px;border-radius:12px;margin:0 auto 10px;background:#f1f5f9 center/cover no-repeat;display:flex;align-items:center;justify-content:center;font-size:28px}
  .rd-card h4{margin:0 0 4px;font-size:14px}
  .rd-card .meta{font-size:11px;opacity:.7}
  .rd-section{font-size:18px;margin:20px 0 12px;color:#0f172a}
  .rd-podcast{display:flex;gap:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-bottom:10px;align-items:center}
  .rd-podcast .pic{width:56px;height:56px;border-radius:8px;background:#f1f5f9 center/cover no-repeat;flex-shrink:0}
  .rd-podcast .pi{flex:1;min-width:0}
  .rd-podcast h4{margin:0 0 3px;font-size:14px;color:#0f172a}
  .rd-podcast .pm{font-size:11px;color:#64748b}
  .rd-podcast button{background:#0f766e;color:#fff;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;font-size:12px}
  .rd-podcast .dl{background:#f1f5f9;color:#0f172a;margin-left:6px}
  .rd-offline-tip{background:#fef3c7;border:1px solid #fde68a;color:#78350f;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:16px}
</style>
<div class="rd-wrap">
  <div class="rd-hero">
    <h1>📻 अनलाइन रेडियो</h1>
    <p>नेपालका लाइभ FM र समाचार रेडियो। पोडकास्टहरू अफलाइन सुन्न डाउनलोड गर्न सकिन्छ।</p>
  </div>

  <div class="rd-player" id="rdPlayer">
    <div class="logo" id="rdLogo" style="width:42px;height:42px;border-radius:8px;background:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:18px">📻</div>
    <div class="now">
      <b id="rdName">कुनै रेडियो छानेर सुरु गर्नुहोस्</b>
      <span id="rdMeta">तल बाट कुनै स्टेशन छान्नुहोस्</span>
    </div>
    <div class="ctrl">
      <button onclick="rdToggle()" id="rdBtn">▶</button>
      <button onclick="rdStop()" title="Stop">⏹</button>
    </div>
    <audio id="rdAudio" preload="none" crossorigin="anonymous"></audio>
  </div>

  <h2 class="rd-section">🔴 लाइभ स्टेशन</h2>
  <?php if (!$stations): ?>
    <p style="color:#64748b">कुनै स्टेशन सेटअप गरिएको छैन।</p>
  <?php else: ?>
  <div class="rd-grid">
    <?php foreach ($stations as $s): ?>
      <div class="rd-card" onclick='rdPlay(<?= json_encode([
        "name" => $s["name"], "url" => $s["stream_url"], "type" => $s["stream_type"],
        "logo" => $s["logo_path"] ?: "", "meta" => trim(($s["city"] ?? "") . " · " . ($s["frequency"] ?? ""), " ·"),
      ], JSON_UNESCAPED_UNICODE) ?>)'>
        <div class="logo" <?= $s['logo_path'] ? "style='background-image:url(\"".htmlspecialchars($s['logo_path'])."\")'" : "" ?>>
          <?= $s['logo_path'] ? '' : '📻' ?>
        </div>
        <h4><?= htmlspecialchars($s['name']) ?></h4>
        <div class="meta"><?= htmlspecialchars(trim(($s['city'] ?? '') . ' · ' . ($s['frequency'] ?? ''), ' ·')) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="rd-offline-tip">💡 <b>Tip:</b> पोडकास्टको "⬇ डाउनलोड" थिच्नुहोस् — अफलाइन ब्राउजरमा पनि सुन्न सकिन्छ।</div>

  <h2 class="rd-section">🎙️ पोडकास्ट (Offline-ready)</h2>
  <?php if (!$podcasts): ?>
    <p style="color:#64748b">अहिले कुनै पोडकास्ट उपलब्ध छैन।</p>
  <?php else: foreach ($podcasts as $p): ?>
    <div class="rd-podcast">
      <div class="pic" <?= $p['cover_image'] ? "style='background-image:url(\"".htmlspecialchars($p['cover_image'])."\")'" : "" ?>></div>
      <div class="pi">
        <h4><?= htmlspecialchars($p['title']) ?></h4>
        <div class="pm"><?= htmlspecialchars($p['station_name'] ?? $p['source_name'] ?? '') ?> · <?= $p['published_at'] ? date('M d', strtotime($p['published_at'])) : '' ?></div>
      </div>
      <button onclick='rdPlay(<?= json_encode(["name"=>$p["title"],"url"=>$p["audio_url"],"type"=>"mp3","logo"=>$p["cover_image"]??"","meta"=>$p["station_name"]??""], JSON_UNESCAPED_UNICODE) ?>)'>▶ बजाउनुहोस्</button>
      <button class="dl" onclick='rdDownload("<?= htmlspecialchars($p["audio_url"]) ?>")'>⬇</button>
    </div>
  <?php endforeach; endif; ?>
</div>

<script>
const audio = document.getElementById('rdAudio');
const btn   = document.getElementById('rdBtn');
let currentUrl = null;

function rdPlay(s) {
  document.getElementById('rdName').textContent = s.name;
  document.getElementById('rdMeta').textContent = s.meta || '';
  if (s.logo) {
    document.getElementById('rdLogo').style.backgroundImage = 'url("' + s.logo + '")';
    document.getElementById('rdLogo').textContent = '';
  }
  document.querySelectorAll('.rd-card').forEach(c => c.classList.remove('playing'));
  event && event.currentTarget && event.currentTarget.classList && event.currentTarget.classList.add('playing');

  if (s.type === 'hls' && window.Hls && Hls.isSupported()) {
    if (window._hls) window._hls.destroy();
    window._hls = new Hls();
    window._hls.loadSource(s.url);
    window._hls.attachMedia(audio);
  } else {
    audio.src = s.url;
  }
  audio.play().then(() => { btn.textContent = '⏸'; currentUrl = s.url; })
              .catch(e => { btn.textContent = '▶'; alert('बजाउन सकिएन: ' + e.message); });
}
function rdToggle() {
  if (!audio.src) return;
  if (audio.paused) { audio.play(); btn.textContent = '⏸'; }
  else { audio.pause(); btn.textContent = '▶'; }
}
function rdStop() {
  audio.pause(); audio.src=''; btn.textContent='▶';
  document.querySelectorAll('.rd-card').forEach(c => c.classList.remove('playing'));
}
async function rdDownload(url) {
  try {
    const cache = await caches.open('radio-offline-v1');
    await cache.add(url);
    alert('✓ अफलाइन को लागि सेभ भयो। इन्टरनेट बिना पनि बजाउन मिल्छ।');
  } catch (e) { alert('डाउनलोड असफल: ' + e.message); }
}
audio.addEventListener('ended', () => { btn.textContent = '▶'; });
</script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<?php if (function_exists('renderFooter')) renderFooter(); else echo "</body></html>"; ?>
