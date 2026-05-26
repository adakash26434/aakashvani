<?php
/**
 * Error Log Viewer
 * View and manage application error logs
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
require_once dirname(__DIR__) . '/includes/error-logger.php';
requireAdmin();

$action = $_GET['action'] ?? '';
$message = '';

// Clear logs
if ($action === 'clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dir = LOG_DIR;
    $files = glob($dir . '/error-*.log');
    foreach ($files as $file) {
        @unlink($file);
    }
    $message = 'All error logs cleared successfully!';
}

// Get error stats
$stats = getErrorStats(7);
$recentErrors = getRecentErrors(100);

$pageTitle = 'Error Logs | Admin';
require_once __DIR__ . '/admin-header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-slate-900">Error Logs</h1>
        <p class="text-sm text-slate-500">View and monitor application errors</p>
      </div>
    </div>
    <form method="POST" action="?action=clear" onsubmit="return confirm('Clear all error logs?');">
      <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg flex items-center gap-2">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
        Clear Logs
      </button>
    </form>
  </div>

  <?php if ($message): ?>
  <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
    <p class="text-emerald-800"><?= htmlspecialchars($message) ?></p>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php 
    $today = $stats[date('Y-m-d')] ?? ['total' => 0, 'errors' => 0, 'warnings' => 0, 'api_errors' => 0];
    $weekTotal = array_sum(array_column($stats, 'total'));
    ?>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="text-sm text-slate-500">Today's Errors</div>
      <div class="text-2xl font-bold <?= $today['errors'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
        <?= $today['errors'] ?>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="text-sm text-slate-500">Today's Warnings</div>
      <div class="text-2xl font-bold <?= $today['warnings'] > 0 ? 'text-amber-600' : 'text-emerald-600' ?>">
        <?= $today['warnings'] ?>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="text-sm text-slate-500">API Errors</div>
      <div class="text-2xl font-bold <?= $today['api_errors'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>">
        <?= $today['api_errors'] ?>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="text-sm text-slate-500">7-Day Total</div>
      <div class="text-2xl font-bold text-slate-800"><?= $weekTotal ?></div>
    </div>
  </div>

  <!-- Daily Chart -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <h2 class="font-semibold text-slate-900 mb-4">7-Day Error History</h2>
    <div class="space-y-2">
      <?php foreach ($stats as $date => $day): ?>
      <div class="flex items-center gap-4">
        <div class="w-24 text-sm text-slate-600"><?= $date ?></div>
        <div class="flex-1 h-6 bg-slate-100 rounded-full overflow-hidden flex">
          <?php if ($day['errors'] > 0): ?>
          <div class="h-full bg-red-500" style="width: <?= min(100, ($day['errors'] / max($day['total'], 1)) * 100) ?>"></div>
          <?php endif; ?>
          <?php if ($day['warnings'] > 0): ?>
          <div class="h-full bg-amber-400" style="width: <?= min(100, ($day['warnings'] / max($day['total'], 1)) * 100) ?>"></div>
          <?php endif; ?>
        </div>
        <div class="w-16 text-right text-sm font-medium text-slate-700"><?= $day['total'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="flex gap-4 mt-4 text-xs">
      <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded"></span> Errors</span>
      <span class="flex items-center gap-1"><span class="w-3 h-3 bg-amber-400 rounded"></span> Warnings</span>
    </div>
  </div>

  <!-- Recent Errors -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
      <h2 class="font-semibold text-slate-900">Recent Errors (Last 100)</h2>
      <span class="text-sm text-slate-500"><?= count($recentErrors) ?> entries</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-slate-700">Time</th>
            <th class="text-left px-4 py-3 font-medium text-slate-700">Level</th>
            <th class="text-left px-4 py-3 font-medium text-slate-700">Source</th>
            <th class="text-left px-4 py-3 font-medium text-slate-700">Message</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($recentErrors as $line): 
            // Parse log line: [datetime] [LEVEL] [IP] [METHOD URI] [context] message
            preg_match('/\[([^\]]+)\]\s*\[([^\]]+)\]\s*\[([^\]]+)\]\s*\[([^\]]+)\](?:\s*\[([^\]]*)\])?\s*(.+)/', $line, $matches);
            $datetime = $matches[1] ?? '';
            $level = $matches[2] ?? '';
            $ip = $matches[3] ?? '';
            $request = $matches[4] ?? '';
            $context = $matches[5] ?? '';
            $msg = $matches[6] ?? $line;
            
            $levelColor = match($level) {
              'ERROR', 'EXCEPTION', 'FATAL' => 'bg-red-100 text-red-700',
              'WARNING' => 'bg-amber-100 text-amber-700',
              'API_ERROR' => 'bg-purple-100 text-purple-700',
              default => 'bg-slate-100 text-slate-700'
            };
          ?>
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-slate-600 font-mono text-xs"><?= htmlspecialchars(substr($datetime, 11, 8)) ?></td>
            <td class="px-4 py-3">
              <span class="inline-block px-2 py-1 rounded-full text-xs font-medium <?= $levelColor ?>">
                <?= htmlspecialchars($level) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-slate-600 text-xs"><?= htmlspecialchars($context ?: '-') ?></td>
            <td class="px-4 py-3 text-slate-800 max-w-md truncate" title="<?= htmlspecialchars($msg) ?>">
              <?= htmlspecialchars($msg) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Log File Path -->
  <div class="mt-6 p-4 bg-slate-50 rounded-lg text-sm text-slate-600">
    <i data-lucide="folder" class="w-4 h-4 inline-block mr-1"></i>
    Log directory: <code class="bg-slate-200 px-2 py-1 rounded"><?= LOG_DIR ?></code>
  </div>
</div>

<script>
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
