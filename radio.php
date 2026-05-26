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
<div class="rd-wrap">
  <div class="rd-hero">
    <h1><i data-lucide="radio" class="w-6 h-6 inline-block mr-2"></i>अनलाइन रेडियो</h1>
    <p>नेपालका लाइभ FM र समाचार रेडियो। पोडकास्टहरू अफलाइन सुन्न डाउनलोड गर्न सकिन्छ।</p>
  </div>

  <div class="rd-player" id="rdPlayer">
    <div class="logo" id="rdLogo"><i data-lucide="radio" class="w-8 h-8"></i></div>
    <div class="now">
      <b id="rdName">कुनै रेडियो छानेर सुरु गर्नुहोस्</b>
      <span id="rdMeta">तल बाट कुनै स्टेशन छान्नुहोस्</span>
    </div>
    <div class="ctrl">
      <button onclick="rdToggle()" id="rdBtn"><i data-lucide="play" class="w-5 h-5"></i></button>
      <button onclick="rdStop()" title="Stop"><i data-lucide="square" class="w-5 h-5"></i></button>
    </div>
    <audio id="rdAudio" preload="none" crossorigin="anonymous"></audio>
  </div>

  <h2 class="rd-section"><span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>लाइभ स्टेशन</span></h2>
  <?php if (!$stations): ?>
    <p class="text-slate-500">कुनै स्टेशन सेटअप गरिएको छैन।</p>
  <?php else: ?>
  <div class="rd-grid">
    <?php foreach ($stations as $s): ?>
      <div class="rd-card" onclick='rdPlay(<?= json_encode([
        "name" => $s["name"], "url" => $s["stream_url"], "type" => $s["stream_type"],
        "logo" => $s["logo_path"] ?: "", "meta" => trim(($s["city"] ?? "") . " · " . ($s["frequency"] ?? ""), " ·"),
      ], JSON_UNESCAPED_UNICODE) ?>)'>
        <div class="logo" <?= $s['logo_path'] ? "style='background-image:url(\"".htmlspecialchars($s['logo_path'])."\")'" : "" ?>>
          <?= $s['logo_path'] ? '' : '<i data-lucide="radio" class="w-8 h-8"></i>' ?>
        </div>
        <h4><?= htmlspecialchars($s['name']) ?></h4>
        <div class="meta"><?= htmlspecialchars(trim(($s['city'] ?? '') . ' · ' . ($s['frequency'] ?? ''), ' ·')) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="rd-offline-tip"><i data-lucide="lightbulb" class="w-4 h-4 inline-block mr-1"></i><b>Tip:</b> पोडकास्टको <i data-lucide="download" class="w-3 h-3 inline-block"></i> डाउनलोड थिच्नुहोस् — अफलाइन ब्राउजरमा पनि सुन्न सकिन्छ।</div>

  <h2 class="rd-section"><i data-lucide="mic" class="w-4 h-4 inline-block mr-1"></i>पोडकास्ट (Offline-ready)</h2>
  <?php if (!$podcasts): ?>
    <p style="color:#64748b">अहिले कुनै पोडकास्ट उपलब्ध छैन।</p>
  <?php else: foreach ($podcasts as $p): ?>
    <div class="rd-podcast">
      <div class="pic" <?= $p['cover_image'] ? "style='background-image:url(\"".htmlspecialchars($p['cover_image'])."\")'" : "" ?>></div>
      <div class="pi">
        <h4><?= htmlspecialchars($p['title']) ?></h4>
        <div class="pm"><?= htmlspecialchars($p['station_name'] ?? $p['source_name'] ?? '') ?> · <?= $p['published_at'] ? date('M d', strtotime($p['published_at'])) : '' ?></div>
      </div>
      <button onclick='rdPlay(<?= json_encode(["name"=>$p["title"],"url"=>$p["audio_url"],"type"=>"mp3","logo"=>$p["cover_image"]??"","meta"=>$p["station_name"]??""], JSON_UNESCAPED_UNICODE) ?>)'><i data-lucide="play" class="w-4 h-4 inline-block mr-1"></i>बजाउनुहोस्</button>
      <button class="dl" onclick='rdDownload("<?= htmlspecialchars($p["audio_url"]) ?>")' title="Download"><i data-lucide="download" class="w-4 h-4"></i></button>
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
