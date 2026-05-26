<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Seed default guides if empty
try { seedDefaultGuides(); } catch(Exception $e) {}

// Handle single guide view
$slug = trim($_GET['slug'] ?? '');
if ($slug) {
    $guide = getGuideBySlug($slug);
    if (!$guide) { header('Location: /ai-guide.php'); exit; }

    $pageTitle = h($guide['title']) . ' | AI Guide | ' . SITE_NAME;
    $pageDesc  = h($guide['excerpt']);
    $pageUrl   = SITE_URL . '/ai-guide.php?slug=' . urlencode($slug);
    include __DIR__ . '/header.php';
?>

<section class="border-b border-[#e2e8f0] py-8 bg-[#ffffff]">
  <div class="max-w-4xl mx-auto px-4">
    <a href="/ai-guide.php" class="inline-flex items-center gap-1.5 text-sm text-[#64748b] hover:text-[#14b8a6] font-mono mb-4 transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
      सबै Guides
    </a>
    <div class="flex items-center gap-3 mb-4">
      <span class="text-3xl"><?= h($guide['icon']) ?></span>
      <div>
        <span class="text-xs font-mono text-[#64748b]"><?= h($guide['category']) ?></span>
        <span class="ml-2 <?= $guide['level']==='Beginner'?'badge-green':($guide['level']==='Intermediate'?'badge-orange':'badge-blue') ?> px-1.5 py-0.5 rounded text-[10px] font-mono"><?= h($guide['level']) ?></span>
      </div>
    </div>
    <h1 class="text-2xl md:text-3xl font-bold text-[#0f172a]"><?= h($guide['title']) ?></h1>
    <p class="text-[#64748b] font-mono text-sm mt-2"><?= h($guide['excerpt']) ?></p>
    <div class="flex items-center gap-4 mt-4 text-xs text-[#64748b] font-mono">
      <span><?= date('M j, Y', strtotime($guide['created_at'])) ?></span>
      <span>👁 <?= number_format($guide['views']) ?> views</span>
    </div>
  </div>
</section>

<div class="max-w-4xl mx-auto px-4 py-12">
  <div class="prose-custom bg-[#ffffff] border border-[#e2e8f0] rounded p-6 md:p-10">
    <?= $guide['content'] ?>
  </div>

  <div class="mt-10 p-5 bg-[#ffffff] border border-[#e2e8f0] rounded flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <p class="text-sm font-semibold text-[#0f172a]">यो Guide उपयोगी लाग्यो?</p>
      <p class="text-xs text-[#64748b] font-mono">अरू हरूसँग share गर्नुस्।</p>
    </div>
    <div class="flex gap-3">
      <a href="<?= fbShare(SITE_URL . '/ai-guide.php?slug=' . urlencode($slug)) ?>" target="_blank"
         class="btn-primary px-4 py-2 rounded text-xs font-semibold">Facebook Share</a>
      <a href="<?= waShare($guide['title'] . ' — ' . SITE_URL . '/ai-guide.php?slug=' . urlencode($slug)) ?>" target="_blank"
         class="btn-outline px-4 py-2 rounded text-xs font-semibold">WhatsApp Share</a>
    </div>
  </div>

  <div class="mt-6">
    <a href="/ai-guide.php" class="inline-flex items-center gap-1.5 text-sm text-[#14b8a6] hover:underline font-mono">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
      सबै Guides हेर्नुस्
    </a>
  </div>
</div>

<style>
.prose-custom h1,.prose-custom h2,.prose-custom h3{color:#0f172a;margin-top:1.5em;margin-bottom:.75em;font-weight:700}
.prose-custom h2{font-size:1.25rem;border-bottom:1px solid #e2e8f0;padding-bottom:.5em}
.prose-custom h3{font-size:1.05rem;color:#14b8a6}
.prose-custom p{color:#64748b;line-height:1.8;margin-bottom:1em}
.prose-custom ul,.prose-custom ol{color:#64748b;line-height:1.8;padding-left:1.5em;margin-bottom:1em}
.prose-custom li{margin-bottom:.4em}
.prose-custom strong{color:#0f172a}
.prose-custom blockquote{border-left:3px solid #0f766e;padding-left:1em;color:#64748b;font-style:italic;background:rgba(35,134,54,0.06);padding:1em;border-radius:0 .25rem .25rem 0;margin:1em 0}
.prose-custom code{background:#f5f5f4;color:#14b8a6;padding:.15em .4em;border-radius:.25rem;font-size:.875em}
.prose-custom pre{background:#f5f5f4;border:1px solid #e2e8f0;padding:1em;border-radius:.5rem;overflow-x:auto;margin-bottom:1em}
</style>

<?php
    include __DIR__ . '/footer.php';
    exit;
}

// ── LISTING PAGE ──────────────────────────────────────────────────────────────
$category   = trim($_GET['cat'] ?? '');
$guides     = getPublishedGuides($category ?: null, 50, 0);
$categories = getGuideCategories();

$pageTitle = 'AI Guide — Nepali मा सिक्नुस् | ' . SITE_NAME;
$pageDesc  = 'ChatGPT, Gemini, Canva AI र अरू AI Tools को complete guide Nepali मा। Beginners देखि Advanced सम्म।';
$pageUrl   = SITE_URL . '/ai-guide.php';

include __DIR__ . '/header.php';
?>

<!-- Page Header -->
<section class="border-b border-[#e2e8f0] py-12 bg-[#ffffff]">
  <div class="max-w-7xl mx-auto px-4">
    <div class="inline-flex items-center gap-2 badge-blue px-2.5 py-1 rounded text-xs font-mono mb-4">🤖 AI Learning</div>
    <h1 class="text-3xl md:text-4xl font-bold text-[#0f172a] tracking-tight mb-2">AI Guide — Nepali मा सिक्नुस्</h1>
    <p class="text-[#64748b] font-mono">ChatGPT, Gemini, Canva AI र अरू tools को guide — Nepali भाषामा</p>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">
  <!-- Category Filter -->
  <div class="flex flex-wrap gap-2 mb-8">
    <a href="/ai-guide.php" class="px-3 py-1.5 rounded text-xs font-mono transition-colors <?= !$category ? 'badge-green' : 'bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#14b8a6] hover:border-[#0f766e]' ?>">
      सबै Guides
    </a>
    <?php foreach ($categories as $c): ?>
    <a href="/ai-guide.php?cat=<?= urlencode($c['category']) ?>"
       class="px-3 py-1.5 rounded text-xs font-mono transition-colors <?= $category===$c['category'] ? 'badge-green' : 'bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#14b8a6] hover:border-[#0f766e]' ?>">
      <?= h($c['category']) ?> <span class="opacity-60">(<?= (int)$c['c'] ?>)</span>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($guides)): ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($guides as $g): ?>
    <a href="/ai-guide.php?slug=<?= urlencode($g['slug']) ?>" class="card-hover bg-[#ffffff] rounded p-5 group block">
      <div class="text-3xl mb-3"><?= h($g['icon']) ?></div>
      <div class="flex items-center gap-2 mb-3">
        <span class="text-xs font-mono text-[#64748b]"><?= h($g['category']) ?></span>
        <span class="<?= $g['level']==='Beginner'?'badge-green':($g['level']==='Intermediate'?'badge-orange':'badge-blue') ?> px-1.5 py-0.5 rounded text-[10px] font-mono">
          <?= h($g['level']) ?>
        </span>
      </div>
      <h2 class="font-bold text-[#0f172a] group-hover:text-[#0ea5e9] transition-colors mb-2 leading-snug">
        <?= h($g['title']) ?>
      </h2>
      <p class="text-sm text-[#64748b] font-mono leading-relaxed line-clamp-3"><?= h($g['excerpt']) ?></p>
      <div class="flex items-center justify-between mt-4 pt-4 border-t border-[#e2e8f0] text-xs font-mono text-[#64748b]">
        <span><?= date('M j, Y', strtotime($g['created_at'])) ?></span>
        <span class="text-[#14b8a6] group-hover:text-[#10b981] transition-colors">पढ्नुस् →</span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="text-center py-20 text-[#64748b] font-mono border border-dashed border-[#e2e8f0] rounded">
    <p class="text-5xl mb-4">🤖</p>
    <p>Guides छिट्टै add हुनेछन्।</p>
  </div>
  <?php endif; ?>

  <!-- Guide request CTA -->
  <div class="mt-12 p-6 bg-[#ffffff] border border-[#e2e8f0] rounded flex flex-col md:flex-row items-center justify-between gap-4">
    <div>
      <h3 class="font-bold text-[#0f172a]">कुनै AI Tool को Guide चाहियो?</h3>
      <p class="text-sm text-[#64748b] font-mono mt-1">WhatsApp मा message गर्नुस् — हामी Nepali मा guide बनाउनेछौं।</p>
    </div>
    <a href="<?= waLink('Namaste! Yo AI tool ko guide Nepali ma chahiyo: ') ?>" target="_blank"
       class="btn-primary px-6 py-2.5 rounded font-semibold text-sm shrink-0">
      Guide Request गर्नुस्
    </a>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
