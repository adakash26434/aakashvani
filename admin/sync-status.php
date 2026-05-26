<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
require_once dirname(__DIR__) . '/includes/sync-functions.php';
requireAdmin();

$pageTitle = 'Sync Status | Admin';
include dirname(__DIR__) . '/admin/header.php';
?>

<div class="max-w-6xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-6">Auto-Sync Status</h1>

  <div class="bg-gradient-to-r from-slate-800 to-slate-700 border border-slate-600/40 rounded-xl p-4 mb-6 flex items-center gap-4">
    <div class="text-3xl flex-shrink-0">🔄</div>
    <div class="flex-1 min-w-0">
      <div class="text-white font-black text-sm">Live API Data Sync</div>
      <div class="text-white/70 text-xs mt-0.5">Sync data from live APIs to cache</div>
    </div>
    <div class="flex gap-2 flex-shrink-0">
      <form method="POST" class="inline">
        <input type="hidden" name="action" value="sync_all"/>
        <button type="submit" class="bg-emerald-600 text-white font-bold text-xs px-4 py-2.5 rounded-lg hover:bg-emerald-500 transition-all">
          ↺ Sync Now
        </button>
      </form>
    </div>
  </div>

  <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'sync_all'): ?>
    <?php
    $results = syncAll();
    $success = count(array_filter($results));
    $total = count($results);
    ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
      <div class="text-emerald-800 font-bold">Sync Complete: <?= $success ?>/<?= $total ?> sources synced</div>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <?php $syncStatus = getAllSyncStatus(); ?>
    <?php foreach ([
      ['nepse','NEPSE','📈','https://nepalstock.com.np'],
      ['gold','Gold/Silver','🥇','https://www.nrb.org.np'],
      ['forex','Forex','💱','https://www.nrb.org.np'],
      ['petrol','Petrol','⛽','https://noc.gov.np'],
      ['ipo','IPO','📊','https://merolagani.com'],
      ['rashifal','Rashifal','🌟','Web Scraping'],
      ['tenders','Tenders','📄','PPMO Scraping'],
    ] as [$key,$label,$icon,$source]): ?>
    <div class="bg-white border border-slate-200 rounded-lg p-4">
      <div class="flex items-center gap-2 mb-2">
        <span class="text-2xl"><?= $icon ?></span>
        <div class="flex-1">
          <div class="text-sm font-bold text-slate-900"><?= $label ?></div>
          <div class="text-xs text-slate-500"><?= $source ?></div>
        </div>
      </div>
      <?php if ($syncStatus[$key]): ?>
        <div class="text-xs <?= $syncStatus[$key]['success'] ? 'text-emerald-600' : 'text-red-600' ?>">
          <?= $syncStatus[$key]['success'] ? '✓ Success' : '✗ Failed' ?>
          <br>
          <?= date('Y-m-d H:i:s', strtotime($syncStatus[$key]['synced_at'])) ?>
        </div>
      <?php else: ?>
        <div class="text-xs text-slate-400">Never synced</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-6 bg-slate-50 border border-slate-200 rounded-lg p-4">
    <h3 class="font-bold text-sm mb-2">Cron Job Setup</h3>
    <p class="text-xs text-slate-600 mb-2">Add to crontab for automatic sync every 30 minutes:</p>
    <code class="text-xs bg-slate-800 text-green-400 px-2 py-1 rounded block">
      0 *\/30 * * * * /usr/bin/php <?= dirname(__DIR__) ?>/cron-sync.php >> <?= dirname(__DIR__) ?>/sync.log 2>&1
    </code>
  </div>
</div>

<?php include dirname(__DIR__) . '/admin/footer.php'; ?>
