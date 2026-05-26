<?php
/**
 * /story.php?slug=... — single success story view
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

$slug = $_GET['slug'] ?? '';
$story = $slug ? getSuccessStoryBySlug($slug) : null;

if (!$story) {
    http_response_code(404);
    echo "<h1>कथा फेला परेन</h1><p><a href='/success-stories.php'>← सबै कथा</a></p>";
    exit;
}

$pageTitle = $story['title'] . ' | ' . SITE_NAME;
if (function_exists('renderHeader')) renderHeader($pageTitle, mb_substr($story['summary'] ?? '', 0, 160, 'UTF-8'));
else echo "<!doctype html><html lang='ne'><head><meta charset='utf-8'><title>".htmlspecialchars($pageTitle)."</title></head><body>";
?>
<style>
  .story-wrap{max-width:780px;margin:0 auto;padding:24px 16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif;color:#0f172a}
  .story-wrap .back{color:#0f766e;text-decoration:none;font-size:14px}
  .story-wrap h1{font-size:32px;line-height:1.3;margin:16px 0 10px}
  .story-meta{color:#64748b;font-size:13px;margin-bottom:18px}
  .story-hero{width:100%;border-radius:14px;margin-bottom:20px}
  .story-body{font-size:16px;line-height:1.75;color:#1e293b}
  .story-source{margin-top:30px;padding:16px;background:#f1f5f9;border-radius:10px;font-size:14px}
  .story-source a{color:#0f766e;font-weight:600}
</style>
<div class="story-wrap">
  <a href="/success-stories.php" class="back">← सबै कथा</a>
  <h1><?= htmlspecialchars($story['title']) ?></h1>
  <div class="story-meta">
    <?= htmlspecialchars($story['source_name'] ?? 'आकाशवाणी') ?> ·
    <?= $story['published_at'] ? date('F d, Y', strtotime($story['published_at'])) : '' ?> ·
    👁 <?= (int) $story['views'] ?>
  </div>
  <?php if (!empty($story['hero_image'])): ?>
    <img class="story-hero" src="<?= htmlspecialchars($story['hero_image']) ?>" alt="<?= htmlspecialchars($story['title']) ?>">
  <?php endif; ?>
  <div class="story-body">
    <?php if (!empty($story['body'])): ?>
      <?= nl2br(htmlspecialchars($story['body'])) ?>
    <?php else: ?>
      <p><?= nl2br(htmlspecialchars($story['summary'] ?? '')) ?></p>
    <?php endif; ?>
  </div>
  <?php if (!empty($story['source_url'])): ?>
    <div class="story-source">
      मूल स्रोत: <a href="<?= htmlspecialchars($story['source_url']) ?>" target="_blank" rel="noopener">
        <?= htmlspecialchars($story['source_name'] ?? 'पढ्नुहोस्') ?> →
      </a>
    </div>
  <?php endif; ?>
</div>
<?php if (function_exists('renderFooter')) renderFooter(); else echo "</body></html>"; ?>
