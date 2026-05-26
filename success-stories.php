<?php
/**
 * /success-stories.php — सफलताका कथा (auto-synced + admin)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/functions.entertainment.php';

maybeRefreshSuccessStories();

$featured = getSuccessStories(3, true);
$stories  = getSuccessStories(24, false);

$pageTitle = 'सफलताका कथा | ' . SITE_NAME;
$pageDesc  = 'नेपाली युवा, उद्यमी, खेलाडी र साधारण मानिसहरूका प्रेरणादायी सफलताका कथा।';

if (function_exists('renderHeader')) renderHeader($pageTitle, $pageDesc);
else { echo "<!doctype html><html lang='ne'><head><meta charset='utf-8'><title>".htmlspecialchars($pageTitle)."</title><meta name='description' content='".htmlspecialchars($pageDesc)."'></head><body>"; }
?>
<div class="ss-wrap">
  <div class="ss-hero">
    <h1>🏆 सफलताका कथा</h1>
    <p>नेपाली युवा, उद्यमी, खेलाडी र साधारण मानिसहरूका प्रेरणादायी यात्रा — दैनिक अटो-सिंक।</p>
  </div>

  <?php if ($featured): ?>
  <h2 class="ss-section-title">⭐ विशेष कथा</h2>
  <div class="ss-grid">
    <?php foreach ($featured as $s): ?>
      <article class="ss-card">
        <a href="/story.php?slug=<?= urlencode($s['slug']) ?>">
          <div class="ss-img" style="background-image:url('<?= htmlspecialchars($s['hero_image'] ?: '/assets/images/story-default.jpg') ?>')">
            <span class="badge">विशेष</span>
          </div>
          <div class="ss-body">
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <p><?= htmlspecialchars(mb_substr($s['summary'] ?? '', 0, 150, 'UTF-8')) ?></p>
            <div class="ss-meta">
              <span><?= htmlspecialchars($s['source_name'] ?? 'आकाशवाणी') ?></span>
              <span><?= $s['published_at'] ? date('M d, Y', strtotime($s['published_at'])) : '' ?></span>
            </div>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <h2 class="ss-section-title">📰 ताजा कथाहरू</h2>
  <?php if (!$stories): ?>
    <div class="ss-empty">अहिले कुनै कथा छैन। स्वतः सिंक छिट्टै हुनेछ।</div>
  <?php else: ?>
  <div class="ss-grid">
    <?php foreach ($stories as $s): ?>
      <article class="ss-card">
        <a href="/story.php?slug=<?= urlencode($s['slug']) ?>">
          <div class="ss-img" style="background-image:url('<?= htmlspecialchars($s['hero_image'] ?: '/assets/images/story-default.jpg') ?>')"></div>
          <div class="ss-body">
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <p><?= htmlspecialchars(mb_substr($s['summary'] ?? '', 0, 130, 'UTF-8')) ?></p>
            <div class="ss-meta">
              <span><?= htmlspecialchars($s['source_name'] ?? '') ?></span>
              <span><?= $s['published_at'] ? date('M d', strtotime($s['published_at'])) : '' ?></span>
            </div>
          </div>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php if (function_exists('renderFooter')) renderFooter(); else echo "</body></html>"; ?>
