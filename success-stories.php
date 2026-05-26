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
<style>
  .ss-wrap{max-width:1200px;margin:0 auto;padding:24px 16px;font-family:system-ui,-apple-system,'Noto Sans Devanagari',sans-serif}
  .ss-hero{background:linear-gradient(135deg,#0f766e,#0891b2);color:#fff;border-radius:16px;padding:28px;margin-bottom:24px}
  .ss-hero h1{margin:0 0 8px;font-size:28px;display:flex;align-items:center;gap:10px}
  .ss-hero p{margin:0;opacity:.92}
  .ss-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
  .ss-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;transition:transform .2s,box-shadow .2s}
  .ss-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px -10px rgba(15,118,110,.25)}
  .ss-card a{color:inherit;text-decoration:none;display:block}
  .ss-img{aspect-ratio:16/10;background:#f1f5f9 center/cover no-repeat;position:relative}
  .ss-img .badge{position:absolute;top:10px;left:10px;background:#f59e0b;color:#fff;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}
  .ss-body{padding:14px}
  .ss-body h3{margin:0 0 8px;font-size:16px;line-height:1.4;color:#0f172a}
  .ss-body p{margin:0 0 10px;font-size:13px;color:#475569;line-height:1.5;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
  .ss-meta{display:flex;justify-content:space-between;font-size:11px;color:#64748b}
  .ss-section-title{font-size:18px;margin:24px 0 14px;color:#0f172a;display:flex;align-items:center;gap:8px}
  .ss-empty{text-align:center;padding:40px;color:#64748b}
</style>
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
