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
<style>
  .vn-wrap{max-width:1280px;margin:0 auto;padding:24px 16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif}
  .vn-hero{background:linear-gradient(135deg,#dc2626,#f59e0b);color:#fff;border-radius:16px;padding:28px;margin-bottom:24px}
  .vn-hero h1{margin:0 0 8px;font-size:30px}
  .vn-hero p{margin:0;opacity:.95}
  .vn-filter{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0 24px}
  .vn-filter a{padding:6px 14px;background:#f1f5f9;color:#0f172a;border-radius:20px;text-decoration:none;font-size:13px;border:1px solid #e2e8f0}
  .vn-filter a.active{background:#0f766e;color:#fff;border-color:#0f766e}
  .vn-feat{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:32px}
  .vn-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:14px}
  .vn-card{background:#fff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;transition:transform .25s,box-shadow .25s}
  .vn-card:hover{transform:translateY(-5px);box-shadow:0 14px 28px -10px rgba(220,38,38,.25)}
  .vn-card a{color:inherit;text-decoration:none;display:block}
  .vn-img{aspect-ratio:4/3;background:#f1f5f9 center/cover no-repeat;position:relative}
  .vn-img .pin{position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,.6);color:#fff;padding:4px 10px;border-radius:6px;font-size:11px;backdrop-filter:blur(6px)}
  .vn-img .feat{position:absolute;top:10px;right:10px;background:#f59e0b;color:#fff;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700}
  .vn-body{padding:12px}
  .vn-body h3{margin:0 0 4px;font-size:15px;color:#0f172a}
  .vn-body .cap{margin:0;font-size:12px;color:#64748b;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .vn-section{font-size:20px;margin:0 0 14px;color:#0f172a}
  .vn-feat .vn-img{aspect-ratio:16/10}
  .vn-empty{text-align:center;padding:60px 20px;color:#64748b;background:#f8fafc;border-radius:12px}
</style>
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
