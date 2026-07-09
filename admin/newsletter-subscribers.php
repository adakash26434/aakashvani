<?php
/**
 * Newsletter Subscribers - Admin Tool
 * View, manage, and export newsletter subscriber list
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
requireAdmin();

$msg = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'export') {
        // Export as CSV
        try {
            $pdo = db();
            if ($pdo) {
                $stmt = $pdo->query("SELECT email, subscribed_at, is_active FROM newsletter_subscribers ORDER BY subscribed_at DESC");
                $subscribers = $stmt->fetchAll();
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="newsletter-subscribers-' . date('Y-m-d') . '.csv"');
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Email', 'Subscribed At', 'Status']);
                foreach ($subscribers as $sub) {
                    fputcsv($output, [$sub['email'], $sub['subscribed_at'], $sub['is_active'] ? 'Active' : 'Unsubscribed']);
                }
                fclose($output);
                exit;
            }
        } catch (Throwable $e) {
            $error = 'Export failed: ' . $e->getMessage();
        }
    }
    
    if ($action === 'delete') {
        $email = $_POST['email'] ?? '';
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $pdo = db();
                if ($pdo) {
                    $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE email = ?");
                    $stmt->execute([$email]);
                    $msg = "Subscriber deleted successfully.";
                }
            } catch (Throwable $e) {
                $error = 'Delete failed: ' . $e->getMessage();
            }
        }
    }
    
    if ($action === 'toggle') {
        $email = $_POST['email'] ?? '';
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $pdo = db();
                if ($pdo) {
                    $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = NOT is_active WHERE email = ?");
                    $stmt->execute([$email]);
                    $msg = "Subscription status updated.";
                }
            } catch (Throwable $e) {
                $error = 'Update failed: ' . $e->getMessage();
            }
        }
    }
}

// Get subscribers
$subscribers = [];
$stats = ['total' => 0, 'active' => 0, 'inactive' => 0];

try {
    $pdo = db();
    if ($pdo) {
        // Ensure table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at DATETIME DEFAULT NULL,
            INDEX idx_email (email),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Get stats
        $statsStmt = $pdo->query("SELECT 
            COUNT(*) as total,
            SUM(is_active = 1) as active,
            SUM(is_active = 0) as inactive
            FROM newsletter_subscribers");
        $statsRow = $statsStmt->fetch();
        $stats = [
            'total' => (int)($statsRow['total'] ?? 0),
            'active' => (int)($statsRow['active'] ?? 0),
            'inactive' => (int)($statsRow['inactive'] ?? 0)
        ];
        
        // Get subscribers
        $search = trim($_GET['search'] ?? '');
        if ($search) {
            $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE email LIKE ? ORDER BY subscribed_at DESC LIMIT 100");
            $stmt->execute(['%' . $search . '%']);
        } else {
            $stmt = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC LIMIT 100");
        }
        $subscribers = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $error = 'Database error: ' . $e->getMessage();
}

$pageTitle = 'Newsletter Subscribers | Admin';
require_once __DIR__ . '/admin-header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center">
      <i data-lucide="mail" class="w-5 h-5"></i>
    </div>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Newsletter Subscribers</h1>
      <p class="text-sm text-slate-500">Manage your newsletter email list</p>
    </div>
  </div>

  <?php if ($msg): ?>
  <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
    <div>
      <p class="font-medium text-emerald-800"><?= htmlspecialchars($msg) ?></p>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
    <div>
      <p class="font-medium text-red-800"><?= htmlspecialchars($error) ?></p>
    </div>
  </div>
  <?php endif; ?>

  <!-- Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
          <span class="font-medium text-slate-700">Total Subscribers</span>
        </div>
        <span class="text-lg font-bold text-slate-900"><?= $stats['total'] ?></span>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
          <span class="font-medium text-slate-700">Active</span>
        </div>
        <span class="text-lg font-bold text-emerald-600"><?= $stats['active'] ?></span>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i data-lucide="user-x" class="w-4 h-4 text-slate-400"></i>
          <span class="font-medium text-slate-700">Unsubscribed</span>
        </div>
        <span class="text-lg font-bold text-slate-400"><?= $stats['inactive'] ?></span>
      </div>
    </div>
  </div>

  <!-- Search & Export -->
  <div class="flex flex-wrap gap-4 mb-6">
    <form method="GET" class="flex-1 min-w-[200px]">
      <div class="relative">
        <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
               placeholder="Search by email..." 
               class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
      </div>
    </form>
    <form method="POST" class="inline">
      <input type="hidden" name="action" value="export">
      <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
        <i data-lucide="download" class="w-4 h-4"></i>
        Export CSV
      </button>
    </form>
  </div>

  <!-- Subscribers Table -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <?php if (empty($subscribers)): ?>
    <div class="p-8 text-center text-slate-500">
      <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
      <p>No subscribers found<?= isset($_GET['search']) ? ' for "' . htmlspecialchars($_GET['search']) . '"' : '' ?>.</p>
    </div>
    <?php else: ?>
    <table class="w-full">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Email</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Subscribed</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
          <th class="text-right px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($subscribers as $sub): ?>
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3">
            <span class="font-medium text-slate-900"><?= htmlspecialchars($sub['email']) ?></span>
          </td>
          <td class="px-4 py-3 text-sm text-slate-600">
            <?= date('M j, Y g:i A', strtotime($sub['subscribed_at'])) ?>
          </td>
          <td class="px-4 py-3">
            <?php if ($sub['is_active']): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Active</span>
            <?php else: ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Unsubscribed</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-right">
            <form method="POST" class="inline">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="email" value="<?= htmlspecialchars($sub['email']) ?>">
              <button type="submit" class="text-slate-500 hover:text-slate-700 text-sm mr-2" title="<?= $sub['is_active'] ? 'Unsubscribe' : 'Resubscribe' ?>">
                <?= $sub['is_active'] ? 'Unsubscribe' : 'Resubscribe' ?>
              </button>
            </form>
            <form method="POST" class="inline" onsubmit="return confirm('Delete this subscriber?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="email" value="<?= htmlspecialchars($sub['email']) ?>">
              <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if (count($subscribers) >= 100): ?>
    <div class="p-4 text-center text-sm text-slate-500 border-t border-slate-200">
      Showing first 100 subscribers. Use search to narrow results.
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Quick Links -->
  <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
    <a href="/admin/dashboard.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="layout-dashboard" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Dashboard</span>
    </a>
    <a href="/admin/clear-cache.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="trash-2" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Clear Cache</span>
    </a>
    <a href="/admin/settings.php" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="settings" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">Settings</span>
    </a>
    <a href="/" target="_blank" class="bg-white border border-slate-200 rounded-lg p-3 text-center hover:border-slate-300 transition-colors">
      <i data-lucide="external-link" class="w-5 h-5 text-slate-400 mx-auto mb-1"></i>
      <span class="text-sm font-medium text-slate-600">View Site</span>
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
