<?php
/**
 * आकाशवाणी — User Dashboard (root /dashboard.php)
 * v8: was incorrectly a copy of admin/dashboard.php (requireAdmin).
 *     Now a real logged-in-user dashboard.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/auth.php';

startAuthSession();

// Not logged in → send to login with redirect-back
if (!isLoggedIn()) {
    header('Location: /login.php?redirect=/dashboard.php');
    exit;
}

$user = getCurrentUser();
if (!$user) {
    logoutUser();
    header('Location: /login.php');
    exit;
}

$db = db();

// ── Personal stats (best-effort, table may not exist) ────────────────────────
function tryCount(PDO $db, string $sql, array $args = []): int {
    try { $s = $db->prepare($sql); $s->execute($args); return (int)$s->fetchColumn(); }
    catch (\Throwable $e) { return 0; }
}

$uid          = (int)$user['id'];
$savedNews    = tryCount($db, "SELECT COUNT(*) FROM user_bookmarks WHERE user_id=? AND kind='news'", [$uid]);
$savedNotices = tryCount($db, "SELECT COUNT(*) FROM user_bookmarks WHERE user_id=? AND kind='notice'", [$uid]);
$savedAlerts  = tryCount($db, "SELECT COUNT(*) FROM user_bookmarks WHERE user_id=? AND kind='alert'", [$uid]);

// Latest content
$latestNews = [];
try {
    $s = $db->query("SELECT id, slug, title, category, image_url, created_at
                     FROM tech_news WHERE is_published=1 ORDER BY id DESC LIMIT 4");
    $latestNews = $s ? $s->fetchAll() : [];
} catch (\Throwable $e) {}

$latestNotices = [];
try {
    $s = $db->query("SELECT id, title, category, importance, created_at
                     FROM notices WHERE is_published=1 ORDER BY id DESC LIMIT 4");
    $latestNotices = $s ? $s->fetchAll() : [];
} catch (\Throwable $e) {}

$displayName = $user['full_name'] ?: explode('@', $user['email'])[0];
$initials    = strtoupper(substr($displayName, 0, 2));

$pageTitle = $displayName . ' — Dashboard | आकाशवाणी';
$pageDesc  = 'Your personal आकाशवाणी dashboard — saved items, latest news, and quick actions.';
include __DIR__ . '/header.php';
?>

<section class="min-h-screen bg-stone-50 pb-24 lg:pb-12">
  <div class="max-w-6xl mx-auto px-4 py-6 lg:py-10 space-y-6">

    <!-- Greeting Card -->
    <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-5 lg:p-8 shadow-lg">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 lg:w-16 lg:h-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center text-xl lg:text-2xl font-bold">
          <?= h($initials) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-emerald-100 text-xs lg:text-sm"><?= t('स्वागत छ', 'Welcome back') ?></p>
          <h1 class="text-xl lg:text-3xl font-bold truncate"><?= h($displayName) ?></h1>
          <p class="text-emerald-50 text-xs lg:text-sm truncate"><?= h($user['email']) ?></p>
        </div>
        <a href="/profile.php"
           class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-sm font-medium transition">
          <i data-lucide="settings" class="w-4 h-4"></i>
          <?= t('सेटिङ', 'Settings') ?>
        </a>
      </div>
    </div>

    <!-- Stat Strip -->
    <div class="grid grid-cols-3 gap-3 lg:gap-4">
      <?php foreach ([
        ['bookmark',  $savedNews,    t('समाचार', 'News'),     'bg-blue-50 text-blue-700'],
        ['bell',      $savedAlerts,  t('अलर्ट', 'Alerts'),    'bg-rose-50 text-rose-700'],
        ['file-text', $savedNotices, t('सूचना', 'Notices'),   'bg-amber-50 text-amber-700'],
      ] as [$icon, $n, $label, $cls]): ?>
        <div class="rounded-xl bg-white border border-stone-200 p-3 lg:p-5">
          <div class="w-9 h-9 lg:w-11 lg:h-11 rounded-lg <?= $cls ?> flex items-center justify-center mb-2">
            <i data-lucide="<?= $icon ?>" class="w-4 h-4 lg:w-5 lg:h-5"></i>
          </div>
          <div class="text-xl lg:text-2xl font-bold text-slate-900"><?= (int)$n ?></div>
          <div class="text-[11px] lg:text-xs text-slate-500"><?= h($label) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Quick Actions Grid (Hamro Patro style tile grid) -->
    <div class="rounded-2xl bg-white border border-stone-200 p-4 lg:p-6">
      <h2 class="text-sm lg:text-base font-semibold text-slate-800 mb-3 lg:mb-4">
        <?= t('द्रुत कार्यहरू', 'Quick Actions') ?>
      </h2>
      <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-3 lg:gap-4">
        <?php
        $tiles = [
          ['calendar',     '/nepali-patro.php',  t('पात्रो', 'Patro')],
          ['newspaper',    '/news.php',          t('समाचार', 'News')],
          ['bell',         '/alerts.php',        t('अलर्ट', 'Alerts')],
          ['file-text',    '/notices.php',       t('सूचना', 'Notices')],
          ['landmark',     '/gov-services.php',  t('सरकारी', 'Gov')],
          ['trending-up',  '/ipo-tracker.php',   t('IPO', 'IPO')],
          ['calculator',   '/tax-calculator.php',t('कर', 'Tax')],
          ['sparkles',     '/ai-guides.php',     t('AI', 'AI')],
        ];
        foreach ($tiles as [$icon, $href, $label]): ?>
          <a href="<?= h($href) ?>"
             class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-emerald-50 transition group">
            <div class="w-11 h-11 lg:w-12 lg:h-12 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center text-emerald-700">
              <i data-lucide="<?= $icon ?>" class="w-5 h-5"></i>
            </div>
            <span class="text-[11px] lg:text-xs font-medium text-slate-700 text-center leading-tight"><?= h($label) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Two-column on desktop -->
    <div class="grid lg:grid-cols-2 gap-4 lg:gap-6">

      <!-- Latest News -->
      <div class="rounded-2xl bg-white border border-stone-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 lg:px-6 py-3 lg:py-4 border-b border-stone-100">
          <h2 class="text-sm lg:text-base font-semibold text-slate-800"><?= t('ताजा समाचार', 'Latest News') ?></h2>
          <a href="/news.php" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
            <?= t('सबै हेर्नुहोस्', 'View all') ?> →
          </a>
        </div>
        <?php if (empty($latestNews)): ?>
          <div class="p-6 text-center text-sm text-slate-400"><?= t('समाचार छैन', 'No news yet') ?></div>
        <?php else: ?>
          <ul class="divide-y divide-stone-100">
            <?php foreach ($latestNews as $n): ?>
              <li>
                <a href="/news-post.php?slug=<?= urlencode($n['slug'] ?? $n['id']) ?>"
                   class="flex gap-3 p-3 lg:p-4 hover:bg-stone-50 transition">
                  <?php if (!empty($n['image_url'])): ?>
                    <img src="<?= h($n['image_url']) ?>" alt="" loading="lazy"
                         class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                  <?php endif; ?>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 line-clamp-2"><?= h($n['title']) ?></p>
                    <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500">
                      <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium"><?= h($n['category'] ?: 'General') ?></span>
                      <span><?= h(date('M j', strtotime($n['created_at'] ?? 'now'))) ?></span>
                    </div>
                  </div>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Notices -->
      <div class="rounded-2xl bg-white border border-stone-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 lg:px-6 py-3 lg:py-4 border-b border-stone-100">
          <h2 class="text-sm lg:text-base font-semibold text-slate-800"><?= t('सरकारी सूचना', 'Notices') ?></h2>
          <a href="/notices.php" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
            <?= t('सबै हेर्नुहोस्', 'View all') ?> →
          </a>
        </div>
        <?php if (empty($latestNotices)): ?>
          <div class="p-6 text-center text-sm text-slate-400"><?= t('सूचना छैन', 'No notices yet') ?></div>
        <?php else: ?>
          <ul class="divide-y divide-stone-100">
            <?php foreach ($latestNotices as $no): ?>
              <li class="p-3 lg:p-4 hover:bg-stone-50 transition">
                <p class="text-sm font-semibold text-slate-800 line-clamp-2"><?= h($no['title']) ?></p>
                <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500">
                  <?php if (($no['importance'] ?? '') === 'urgent'): ?>
                    <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-medium">URGENT</span>
                  <?php endif; ?>
                  <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium"><?= h($no['category'] ?: 'General') ?></span>
                  <span><?= h(date('M j', strtotime($no['created_at'] ?? 'now'))) ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <!-- Account row -->
    <div class="rounded-2xl bg-white border border-stone-200 p-4 lg:p-6 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="text-sm font-semibold text-slate-800"><?= t('खाता', 'Account') ?></h3>
        <p class="text-xs text-slate-500 mt-1"><?= t('प्रोफाइल अपडेट गर्न वा लगआउट गर्न', 'Update profile or log out') ?></p>
      </div>
      <div class="flex gap-2">
        <a href="/profile.php" class="px-4 py-2 rounded-lg bg-stone-100 hover:bg-stone-200 text-sm font-medium text-slate-700">
          <?= t('सेटिङ', 'Settings') ?>
        </a>
        <a href="/logout.php" class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-sm font-medium text-rose-700">
          <?= t('लगआउट', 'Log out') ?>
        </a>
      </div>
    </div>

  </div>
</section>

<script>
  if (window.lucide) window.lucide.createIcons();
</script>

<?php include __DIR__ . '/footer.php'; ?>
