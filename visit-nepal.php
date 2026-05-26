<?php
/**
 * /visit-nepal.php — Nepal visit photography gallery
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

$district = $_GET['district'] ?? null;
$featured = getVisitPlaces(6, true);
$places   = getVisitPlaces(48, false, $district);

$districts = db()->query("SELECT DISTINCT district FROM visit_places WHERE district IS NOT NULL AND district!='' ORDER BY district")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'नेपाल घुम्ने ठाउँ | ' . SITE_NAME;
$pageDesc  = 'हिमाल, पहाड र तराईका मनमोहक पर्यटकीय स्थलहरूको फोटो ग्यालरी।';

if (function_exists('renderHeader')) renderHeader($pageTitle, $pageDesc);
else echo "<!doctype html><html lang='ne'><head><meta charset='utf-8'><title>".htmlspecialchars($pageTitle)."</title></head><body>";
?>
<div class="vn-wrap">
  <div class="vn-hero">
    <h1>📸 नेपाल घुम्ने ठाउँ</h1>
    <p>हिमाल, पहाड, तराई — आफ्नै देशको प्रकृति र संस्कृति। फोटोबाट यात्रा सुरु गर्नुहोस्।</p>
  </div>

  <?php if ($districts): ?>
  <div class="vn-filter">
    <a href="/visit-nepal.php" class="<?= !$district ? 'active' : '' ?>">सबै</a>
    <?php foreach ($districts as $d): ?>
      <a href="/visit-nepal.php?district=<?= urlencode($d) ?>" class="<?= $district === $d ? 'active' : '' ?>"><?= htmlspecialchars($d) ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($featured && !$district): ?>
    <h2 class="vn-section">⭐ विशेष गन्तव्य</h2>
    <div class="vn-feat">
      <?php foreach ($featured as $p): ?>
        <article class="vn-card">
          <a href="/visit-place.php?slug=<?= urlencode($p['slug']) ?>">
            <div class="vn-img" style="background-image:url('<?= htmlspecialchars($p['image_path']) ?>')">
              <span class="feat">FEATURED</span>
              <?php if ($p['district']): ?><span class="pin">📍 <?= htmlspecialchars($p['district']) ?></span><?php endif; ?>
            </div>
            <div class="vn-body">
              <h3><?= htmlspecialchars($p['title']) ?></h3>
              <p class="cap"><?= htmlspecialchars($p['short_caption'] ?? '') ?></p>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h2 class="vn-section">🗺️ सबै गन्तव्य</h2>
  <?php if (!$places): ?>
    <div class="vn-empty">अहिले कुनै पनि स्थान थपिएको छैन। प्रशासकले छिट्टै थप्नुहुनेछ।</div>
  <?php else: ?>
    <div class="vn-grid">
      <?php foreach ($places as $p): ?>
        <article class="vn-card">
          <a href="/visit-place.php?slug=<?= urlencode($p['slug']) ?>">
            <div class="vn-img" style="background-image:url('<?= htmlspecialchars($p['image_thumb'] ?: $p['image_path']) ?>')">
              <?php if ($p['district']): ?><span class="pin">📍 <?= htmlspecialchars($p['district']) ?></span><?php endif; ?>
            </div>
            <div class="vn-body">
              <h3><?= htmlspecialchars($p['title']) ?></h3>
              <p class="cap"><?= htmlspecialchars($p['short_caption'] ?? '') ?></p>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php if (function_exists('renderFooter')) renderFooter(); else echo "</body></html>"; ?>
