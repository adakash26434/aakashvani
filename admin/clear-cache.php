<?php
/**
 * Cache Clear Utility — Admin Tool
 * Clears all cached API data and forces fresh fetches
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
requireAdmin();

$msg = '';
$cleared = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_all') {
    $cacheDirs = [
        __DIR__ . '/../data/cache/',
        __DIR__ . '/../cache/',
    ];
    
    foreach ($cacheDirs as $dir) {
        if (!is_dir($dir)) continue;
        
        $files = glob($dir . '*.{json,txt,cache}', GLOB_BRACE);
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                if (@unlink($file)) {
                    $cleared[] = $filename;
                }
            }
        }
        
        // Also clear subdirectories
        $subdirs = glob($dir . '*', GLOB_ONLYDIR);
        foreach ($subdirs as $subdir) {
            $files = glob($subdir . '/*.{json,txt,cache}', GLOB_BRACE);
            foreach ($files as $file) {
                if (is_file($file)) {
                    $filename = basename($file);
                    if (@unlink($file)) {
                        $cleared[] = $filename;
                    }
                }
            }
        }
    }
    
    // Clear specific cache files
    $specificFiles = [
        __DIR__ . '/../data/cache/ipo.json',
        __DIR__ . '/../data/cache/market.json',
        __DIR__ . '/../data/cache/alerts.json',
        __DIR__ . '/../data/cache/news-rss.json',
        __DIR__ . '/../data/cache/weather.json',
        __DIR__ . '/../data/cache/cricket.json',
        __DIR__ . '/../data/cache/rashifal.json',
        __DIR__ . '/../data/cache/morning-brief.json',
    ];
    
    foreach ($specificFiles as $file) {
        if (is_file($file) && @unlink($file)) {
            $cleared[] = basename($file);
        }
    }
    
    $msg = 'Cache cleared successfully! Removed ' . count($cleared) . ' files.';
}

// Get cache stats
$cacheStats = [];
$cacheDirs = [
    __DIR__ . '/../data/cache/' => 'Main Cache',
    __DIR__ . '/../cache/' => 'Article Cache',
];

foreach ($cacheDirs as $dir => $label) {
    if (!is_dir($dir)) {
        $cacheStats[$label] = ['files' => 0, 'size' => 0];
        continue;
    }
    
    $files = glob($dir . '*');
    $fileCount = 0;
    $totalSize = 0;
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileCount++;
            $totalSize += filesize($file);
        }
    }
    
    $cacheStats[$label] = [
        'files' => $fileCount,
        'size' => $totalSize,
        'size_formatted' => formatBytes($totalSize)
    ];
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

$pageTitle = 'Clear Cache | Admin';
require_once __DIR__ . '/admin-header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
  <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
      <i data-lucide="trash-2" class="w-5 h-5"></i>
    </div>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Clear Cache</h1>
      <p class="text-sm text-slate-500">Remove cached API data and force fresh fetches</p>
    </div>
  </div>

  <?php if ($msg): ?>
  <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
    <div>
      <p class="font-medium text-emerald-800"><?= htmlspecialchars($msg) ?></p>
      <?php if (!empty($cleared)): ?>
      <p class="text-sm text-emerald-600 mt-1">Files: <?= htmlspecialchars(implode(', ', array_slice($cleared, 0, 5))) ?><?= count($cleared) > 5 ? ' and ' . (count($cleared) - 5) . ' more...' : '' ?></p>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Cache Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <?php foreach ($cacheStats as $label => $stats): ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i data-lucide="folder" class="w-4 h-4 text-slate-400"></i>
          <span class="font-medium text-slate-700"><?= htmlspecialchars($label) ?></span>
        </div>
        <span class="text-sm font-semibold text-slate-900"><?= $stats['files'] ?> files</span>
      </div>
      <div class="mt-2 text-sm text-slate-500">
        Size: <?= $stats['size_formatted'] ?? '0 B' ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Cache Info -->
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <div class="flex items-start gap-3">
      <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
      <div>
        <p class="font-medium text-blue-800">What gets cleared?</p>
        <ul class="text-sm text-blue-600 mt-1 space-y-1">
          <li>• News RSS cache (15 min TTL)</li>
          <li>• Market data cache (Gold, Fuel, Forex, NEPSE)</li>
          <li>• Weather data cache</li>
          <li>• IPO/FPO data cache</li>
          <li>• Cricket scores cache</li>
          <li>• Alerts cache (BIPAD, USGS, Police)</li>
          <li>• Rashifal and Morning Brief cache</li>
          <li>• Article content cache</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Clear Button -->
  <form method="POST" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" onsubmit="return confirm('Are you sure? This will force all APIs to fetch fresh data.');">
    <input type="hidden" name="action" value="clear_all">
    
    <div class="flex items-center justify-between">
      <div>
        <h3 class="font-semibold text-slate-900">Clear All Cache</h3>
        <p class="text-sm text-slate-500 mt-1">This will remove all cached data and force APIs to fetch fresh content</p>
      </div>
      <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold px-6 py-2.5 rounded-lg flex items-center gap-2 transition-colors">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
        Clear Cache
      </button>
    </div>
  </form>

  <!-- Quick Links -->
  <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
    <a href="/admin/dashboard.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="layout-dashboard" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Dashboard</span>
    </a>
    <a href="/admin/data-manager.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="database" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Data Manager</span>
    </a>
    <a href="/admin/settings.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="settings" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Settings</span>
    </a>
    <a href="/admin/prices.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="dollar-sign" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Prices</span>
    </a>
  </div>
</div>

<script>
  // Initialize Lucide icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
