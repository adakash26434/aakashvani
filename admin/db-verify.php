<?php
/**
 * Database Verification Script
 * Checks all tables, columns, and data integrity
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';

// Don't require admin - this is a setup tool
$pageTitle = 'Database Verification | आकाशवाणी';
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Noto Sans Devanagari', sans-serif; background: #f8fafc; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; color: #0f172a; margin-bottom: 8px; }
        .header p { color: #64748b; font-size: 14px; }
        .card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card h2 { font-size: 16px; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .status-item { padding: 12px; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
        .status-item.ok { background: #ecfdf5; color: #059669; }
        .status-item.error { background: #fef2f2; color: #dc2626; }
        .status-item.warning { background: #fffbeb; color: #d97706; }
        .status-icon { width: 20px; height: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; }
        th { font-weight: 600; color: #475569; background: #f8fafc; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #0d9488; color: #fff; }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .fix-box { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Database Verification</h1>
            <p>Check all tables, columns, and data integrity</p>
        </div>

<?php
$checks = [];
$errors = [];
$warnings = [];

// Check 1: Database Connection
try {
    $pdo = db();
    $checks[] = ['Database Connection', 'ok', 'Connected successfully'];
} catch (Exception $e) {
    $errors[] = 'Database connection failed: ' . $e->getMessage();
    $checks[] = ['Database Connection', 'error', 'Failed'];
}

// Check 2: Required Tables
$requiredTables = [
    'tech_news' => 'News articles storage',
    'radio_stations' => 'Radio stations data',
    'success_stories' => 'Success stories',
    'loksewa_notices' => 'Loksewa notices',
    'rashifal_daily' => 'Daily horoscope',
    'subscriptions' => 'User subscriptions',
    'news_sync_log' => 'News sync log',
];

$tableStatus = [];
foreach ($requiredTables as $table => $description) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetch();
        if ($exists) {
            // Count rows
            $countStmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $countStmt->fetchColumn();
            $tableStatus[$table] = ['status' => 'ok', 'rows' => $count, 'desc' => $description];
        } else {
            $tableStatus[$table] = ['status' => 'error', 'rows' => 0, 'desc' => $description];
            $warnings[] = "Table '$table' does not exist";
        }
    } catch (Exception $e) {
        $tableStatus[$table] = ['status' => 'error', 'rows' => 0, 'desc' => $e->getMessage()];
        $warnings[] = "Error checking table '$table': " . $e->getMessage();
    }
}

// Check 3: UTF-8 Support
try {
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
    $charsets = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $utf8Ok = (strpos($charsets['character_set_database'] ?? '', 'utf8') !== false);
    $checks[] = ['UTF-8 Support', $utf8Ok ? 'ok' : 'warning', $charsets['character_set_database'] ?? 'unknown'];
    if (!$utf8Ok) {
        $warnings[] = "Database charset should be utf8mb4 for Nepali text";
    }
} catch (Exception $e) {
    $checks[] = ['UTF-8 Support', 'warning', 'Could not check'];
}

// Check 4: Directory Permissions
$dirs = [
    'data/' => is_writable(__DIR__ . '/../data/'),
    'data/cache/' => is_writable(__DIR__ . '/../data/cache/'),
    'data/logs/' => is_writable(__DIR__ . '/../data/logs/'),
    'cache/' => is_writable(__DIR__ . '/../cache/'),
    'assets/news-cache/' => is_writable(__DIR__ . '/../assets/news-cache/'),
];

foreach ($dirs as $dir => $writable) {
    $checks[] = ["Directory: $dir", $writable ? 'ok' : 'error', $writable ? 'Writable' : 'Not writable'];
    if (!$writable) {
        $errors[] = "Directory '$dir' is not writable";
    }
}

// Check 5: Config file
$configExists = file_exists(__DIR__ . '/../config.php');
$checks[] = ['Config File', $configExists ? 'ok' : 'error', $configExists ? 'Found' : 'Missing'];
if (!$configExists) {
    $errors[] = "config.php not found - copy from config.example.php";
}

// Display Results
?>

        <!-- Overall Status -->
        <div class="card">
            <h2><i data-lucide="activity" class="status-icon"></i> Overall Status</h2>
            <div class="status-grid">
                <?php foreach ($checks as $check): ?>
                <div class="status-item <?= $check[1] ?>">
                    <?php if ($check[1] === 'ok'): ?>
                        <i data-lucide="check-circle" class="status-icon"></i>
                    <?php elseif ($check[1] === 'error'): ?>
                        <i data-lucide="x-circle" class="status-icon"></i>
                    <?php else: ?>
                        <i data-lucide="alert-triangle" class="status-icon"></i>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($check[0]) ?></div>
                        <div style="font-size: 12px; opacity: 0.9;"><?= htmlspecialchars($check[2]) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tables Status -->
        <div class="card">
            <h2><i data-lucide="database" class="status-icon"></i> Database Tables</h2>
            <table>
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Rows</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tableStatus as $table => $info): ?>
                    <tr>
                        <td><code class="code"><?= htmlspecialchars($table) ?></code></td>
                        <td><?= htmlspecialchars($info['desc']) ?></td>
                        <td>
                            <?php if ($info['status'] === 'ok'): ?>
                                <span style="color: #059669;">✓ OK</span>
                            <?php else: ?>
                                <span style="color: #dc2626;">✗ Missing</span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($info['rows']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (isset($tableStatus['radio_stations']) && $tableStatus['radio_stations']['rows'] === 0): ?>
            <div class="fix-box">
                <strong>⚠️ Radio stations table is empty!</strong><br>
                Run the SQL from <code class="code">fix-database-issues.sql</code> to add stations.<br>
                <a href="/admin/clear-cache.php" class="btn btn-primary" style="margin-top: 8px;">
                    <i data-lucide="database" style="width: 14px;"></i> Go to Admin
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
        <div class="card" style="border-left: 4px solid #dc2626;">
            <h2 style="color: #dc2626;"><i data-lucide="alert-octagon" class="status-icon"></i> Errors (Must Fix)</h2>
            <ul style="list-style: none; margin-top: 12px;">
                <?php foreach ($errors as $error): ?>
                <li style="padding: 8px 0; border-bottom: 1px solid #fee2e2; color: #dc2626;">
                    ✗ <?= htmlspecialchars($error) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Warnings -->
        <?php if (!empty($warnings)): ?>
        <div class="card" style="border-left: 4px solid #d97706;">
            <h2 style="color: #d97706;"><i data-lucide="alert-triangle" class="status-icon"></i> Warnings</h2>
            <ul style="list-style: none; margin-top: 12px;">
                <?php foreach ($warnings as $warning): ?>
                <li style="padding: 8px 0; border-bottom: 1px solid #fef3c7; color: #92400e;">
                    ⚠ <?= htmlspecialchars($warning) ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="card">
            <h2><i data-lucide="tool" class="status-icon"></i> Quick Actions</h2>
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px;">
                <a href="/admin/dashboard.php" class="btn btn-primary">
                    <i data-lucide="layout-dashboard" style="width: 14px;"></i> Admin Dashboard
                </a>
                <a href="/admin/clear-cache.php" class="btn btn-secondary">
                    <i data-lucide="trash-2" style="width: 14px;"></i> Clear Cache
                </a>
                <a href="/admin/article-test.php" class="btn btn-secondary">
                    <i data-lucide="file-text" style="width: 14px;"></i> Test Articles
                </a>
                <a href="/" class="btn btn-secondary">
                    <i data-lucide="home" style="width: 14px;"></i> View Site
                </a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #64748b; font-size: 12px;">
            <p>आकाशवाणी Database Verification Tool</p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
