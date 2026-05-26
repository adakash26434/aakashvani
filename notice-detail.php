<?php
$pageTitle = 'सूचना विवरण | आकाशवाणी';
$pageDesc  = 'आकाशवाणी सूचना विवरण';
require_once __DIR__ . '/header.php';

$type = trim($_GET['type'] ?? 'notice');
$title = trim($_GET['title'] ?? 'सूचना');
$source = trim($_GET['source'] ?? 'Official source');
$url = trim($_GET['url'] ?? '');
$allowed = $url && preg_match('~^https?://~i', $url);
?>
<main class="app-main">
  <section class="px-4 pt-4 pb-6">
    <div class="bg-white rounded-2xl shadow-app overflow-hidden border border-slate-100">
      <div class="p-4 border-b border-slate-100">
        <div class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 text-teal-700 px-2.5 py-1 text-[11px] font-bold mb-3">
          <i data-lucide="megaphone" class="w-3.5 h-3.5"></i><?= htmlspecialchars(ucfirst($type), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <h1 class="text-[18px] font-extrabold text-slate-900 leading-snug ne"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="mt-2 text-[12px] text-slate-500 flex items-center gap-1.5">
          <i data-lucide="radio" class="w-3.5 h-3.5"></i>
          स्रोत: <?= htmlspecialchars($source, ENT_QUOTES, 'UTF-8') ?>
        </div>
      </div>
      <div class="p-4">
        <div class="rounded-xl bg-amber-50 border border-amber-100 p-3 text-[12px] text-amber-800 leading-relaxed ne">
          यो सूचना आधिकारिक स्रोतबाट संकलन गरिएको हो। विवरण/फारम भर्ने/डाउनलोड जस्ता संवेदनशील काम गर्दा आधिकारिक साइटमा अन्तिम पुष्टि गर्नुहोस्।
        </div>
        <?php if ($allowed): ?>
          <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 text-white font-bold py-3 text-[13px]">
            <i data-lucide="external-link" class="w-4 h-4"></i> आधिकारिक स्रोत खोल्नुहोस्
          </a>
        <?php endif; ?>
        <a href="/loksewa.php" class="mt-2 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 text-slate-700 font-bold py-3 text-[13px]">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> लोकसेवा सूचीमा फर्कनुहोस्
        </a>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
