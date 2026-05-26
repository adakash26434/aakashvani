<?php
/**
 * /visit-place.php?slug=... — single place detail
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

$slug = $_GET['slug'] ?? '';
$p = $slug ? getVisitPlaceBySlug($slug) : null;
if (!$p) { http_response_code(404); echo "<h1>ठाउँ फेला परेन</h1><p><a href='/visit-nepal.php'>← सबै</a></p>"; exit; }

$pageTitle = $p['title'] . ' | नेपाल घुम्ने ठाउँ';
if (function_exists('renderHeader')) renderHeader($pageTitle, mb_substr($p['description'], 0, 160, 'UTF-8'));
else echo "<!doctype html><html lang='ne'><head><meta charset='utf-8'><title>".htmlspecialchars($pageTitle)."</title></head><body>";
?>
<style>
  .vp-wrap{max-width:900px;margin:0 auto;padding:24px 16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif}
  .vp-wrap .back{color:#dc2626;text-decoration:none;font-size:14px}
  .vp-wrap h1{font-size:32px;margin:12px 0 4px;color:#0f172a}
  .vp-sub{color:#64748b;margin-bottom:18px}
  .vp-hero{width:100%;border-radius:14px;margin-bottom:20px}
  .vp-info{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px}
  .vp-info div{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px}
  .vp-info b{display:block;font-size:11px;color:#64748b;margin-bottom:2px}
  .vp-desc{font-size:16px;line-height:1.75;color:#1e293b}
  .vp-map{margin-top:20px;border-radius:12px;overflow:hidden;height:300px;background:#f1f5f9}
</style>
<div class="vp-wrap">
  <a href="/visit-nepal.php" class="back">← नेपाल घुम्ने ठाउँ</a>
  <h1><?= htmlspecialchars($p['title']) ?></h1>
  <div class="vp-sub">
    <?php if ($p['district']): ?>📍 <?= htmlspecialchars($p['district']) ?><?php endif; ?>
    <?php if ($p['province']): ?> · <?= htmlspecialchars($p['province']) ?><?php endif; ?>
    · 👁 <?= (int) $p['views'] ?>
  </div>
  <img class="vp-hero" src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">

  <div class="vp-info">
    <?php if ($p['altitude_m']): ?><div><b>उचाइ</b><?= (int)$p['altitude_m'] ?> मीटर</div><?php endif; ?>
    <?php if ($p['best_season']): ?><div><b>उत्तम समय</b><?= htmlspecialchars($p['best_season']) ?></div><?php endif; ?>
    <?php if ($p['region']!=='unknown'): ?><div><b>भू-भाग</b><?= htmlspecialchars(ucfirst($p['region'])) ?></div><?php endif; ?>
    <?php if ($p['category']): ?><div><b>श्रेणी</b><?= htmlspecialchars($p['category']) ?></div><?php endif; ?>
  </div>

  <div class="vp-desc"><?= nl2br(htmlspecialchars($p['description'])) ?></div>

  <?php if (!empty($p['how_to_reach'])): ?>
    <h3>🛣️ कसरी पुग्ने?</h3>
    <p style="line-height:1.7"><?= nl2br(htmlspecialchars($p['how_to_reach'])) ?></p>
  <?php endif; ?>

  <?php if ($p['latitude'] && $p['longitude']): ?>
    <iframe class="vp-map" style="width:100%;border:0"
      src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $p['longitude']-0.02 ?>%2C<?= $p['latitude']-0.02 ?>%2C<?= $p['longitude']+0.02 ?>%2C<?= $p['latitude']+0.02 ?>&layer=mapnik&marker=<?= $p['latitude'] ?>%2C<?= $p['longitude'] ?>"></iframe>
  <?php endif; ?>
</div>
<?php if (function_exists('renderFooter')) renderFooter(); else echo "</body></html>"; ?>
