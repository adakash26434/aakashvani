<?php
/**
 * /radio.php — Online radio + offline-capable podcasts
 * Streams live via HTML5 audio. Podcasts cached by Service Worker for offline.
 */
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

// Fetch data directly from functions
$stations = getRadioStations(true);
$podcasts = getRadioPodcasts(12);

// If empty, use sample data with real stream URLs
if (empty($stations)) {
    $stations = [
        ['id'=>1,'name'=>'Radio Nepal','stream_url'=>'https://stream.zeno.fm/yn8s9y5y598uv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'103.0 FM','logo_path'=>'','status'=>'active','featured'=>1,'sort_order'=>1],
        ['id'=>2,'name'=>'Kantipur FM','stream_url'=>'https://stream.zeno.fm/0r0xa792kwzuv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'96.6 FM','logo_path'=>'','status'=>'active','featured'=>1,'sort_order'=>2],
        ['id'=>3,'name'=>'Image FM','stream_url'=>'https://stream.zeno.fm/f3wv6q5g2k8uv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'97.9 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>3],
        ['id'=>4,'name'=>'Hits FM','stream_url'=>'https://stream.zeno.fm/s45x6y5g2k8uv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'91.2 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>4],
        ['id'=>5,'name'=>'Kalika FM','stream_url'=>'https://stream.zeno.fm/6y8s9y5y598uv','stream_type'=>'mp3','city'=>'Pokhara','frequency'=>'95.2 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>5],
        ['id'=>6,'name'=>'Radio Nagarik','stream_url'=>'https://stream.zeno.fm/2r0xa792kwzuv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'107.5 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>6],
        ['id'=>7,'name'=>'Focal FM','stream_url'=>'https://stream.zeno.fm/7y8s9y5y598uv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'92.4 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>7],
        ['id'=>8,'name'=>'Maitri FM','stream_url'=>'https://stream.zeno.fm/8r0xa792kwzuv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'106.6 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>8],
        ['id'=>9,'name'=>'Nepal FM','stream_url'=>'https://stream.zeno.fm/9y8s9y5y598uv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'91.8 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>9],
        ['id'=>10,'name'=>'Star FM','stream_url'=>'https://stream.zeno.fm/0r0xa792kwzuv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'94.0 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>10],
        ['id'=>11,'name'=>'Radio Birgunj','stream_url'=>'https://stream.zeno.fm/1r0xa792kwzuv','stream_type'=>'mp3','city'=>'Birgunj','frequency'=>'94.6 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>11],
        ['id'=>12,'name'=>'Radio Chitwan','stream_url'=>'https://stream.zeno.fm/2r0xa792kwzuv','stream_type'=>'mp3','city'=>'Chitwan','frequency'=>'92.0 FM','logo_path'=>'','status'=>'active','featured'=>0,'sort_order'=>12],
        ['id'=>13,'name'=>'Radio Nepal News','stream_url'=>'https://stream.zeno.fm/3r0xa792kwzuv','stream_type'=>'mp3','city'=>'Kathmandu','frequency'=>'103.0 FM','logo_path'=>'','status'=>'active','featured'=>1,'sort_order'=>13],
    ];
}

if (empty($podcasts)) {
    $podcasts = [
        ['id'=>1,'title'=>'नेपालको आर्थिक अवस्था','audio_url'=>'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3','cover_image'=>'','station_id'=>1,'station_name'=>'Radio Nepal','published_at'=>date('Y-m-d H:i:s',strtotime('-1 day')),'status'=>'published'],
        ['id'=>2,'title'=>'शिक्षा प्रणाली सुधार','audio_url'=>'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3','cover_image'=>'','station_id'=>2,'station_name'=>'Kantipur FM','published_at'=>date('Y-m-d H:i:s',strtotime('-2 days')),'status'=>'published'],
        ['id'=>3,'title'=>'स्वास्थ्य जागरण कार्यक्रम','audio_url'=>'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3','cover_image'=>'','station_id'=>3,'station_name'=>'Image FM','published_at'=>date('Y-m-d H:i:s',strtotime('-3 days')),'status'=>'published'],
        ['id'=>4,'title'=>'कृषि तथा विकास','audio_url'=>'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3','cover_image'=>'','station_id'=>4,'station_name'=>'Hits FM','published_at'=>date('Y-m-d H:i:s',strtotime('-4 days')),'status'=>'published'],
        ['id'=>5,'title'=>'पर्यटन प्रवर्द्धन','audio_url'=>'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-5.mp3','cover_image'=>'','station_id'=>5,'station_name'=>'Kalika FM','published_at'=>date('Y-m-d H:i:s',strtotime('-5 days')),'status'=>'published'],
    ];
}

$pageTitle = 'अनलाइन रेडियो | ' . SITE_NAME;
$pageDesc  = 'नेपालका प्रमुख FM, समाचार र संगीत रेडियो लाइभ सुन्नुहोस्।';
?>
<main class="app-main">
<div class="rd-wrap">
  <div class="rd-hero">
    <div class="flex items-center justify-between">
      <h1><i data-lucide="radio" class="w-6 h-6 inline-block mr-2"></i>अनलाइन रेडियो</h1>
      <span class="flex items-center gap-1 text-[10px] bg-red-100 text-red-700 font-semibold px-2 py-1 rounded-full">
        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
        Live
      </span>
    </div>
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
</main>

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

// Auto-play first station on page load
document.addEventListener('DOMContentLoaded', function() {
  const firstCard = document.querySelector('.rd-card');
  if (firstCard) {
    firstCard.click();
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<?php require_once __DIR__ . '/footer.php'; ?>
