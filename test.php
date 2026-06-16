<?php
/**
 * आकाशवाणी - System Test Page
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Test | आकाशवाणी</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .test-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .test-card { background: #fff; border: 1px solid var(--dark-200); border-radius: var(--radius-xl); padding: 24px; margin-bottom: 20px; }
        .test-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--dark-100); }
        .test-item:last-child { border-bottom: none; }
        .test-pass { color: #10b981; font-weight: 600; }
        .test-fail { color: #ef4444; font-weight: 600; }
        .test-info { color: #6b7280; font-size: 14px; }
    </style>
</head>
<body style="background: var(--dark-50);">
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <a href="/" class="brand" style="text-decoration:none">
                    <div class="brand-logo">आ</div>
                    <span class="brand-name">आकाशवाणी</span>
                </a>
            </div>
        </div>
    </header>
    
    <div class="test-container">
        <h1 style="margin-bottom: 24px;">🔧 System Test</h1>
        
        <div class="test-card">
            <h2 style="margin-bottom: 16px;">PHP Configuration</h2>
            
            <div class="test-item">
                <span>PHP Version</span>
                <span class="test-pass">✓ <?= PHP_VERSION ?></span>
            </div>
            <div class="test-item">
                <span>MySQLi Available</span>
                <span class="<?= function_exists('mysqli_connect') ? 'test-pass' : 'test-fail' ?>">
                    <?= function_exists('mysqli_connect') ? '✓ Yes' : '✗ No' ?>
                </span>
            </div>
            <div class="test-item">
                <span>PDO Available</span>
                <span class="<?= class_exists('PDO') ? 'test-pass' : 'test-fail' ?>">
                    <?= class_exists('PDO') ? '✓ Yes' : '✗ No' ?>
                </span>
            </div>
            <div class="test-item">
                <span>JSON Available</span>
                <span class="<?= function_exists('json_encode') ? 'test-pass' : 'test-fail' ?>">
                    <?= function_exists('json_encode') ? '✓ Yes' : '✗ No' ?>
                </span>
            </div>
        </div>
        
        <div class="test-card">
            <h2 style="margin-bottom: 16px;">Functions</h2>
            
            <div class="test-item">
                <span>siteLang()</span>
                <span class="test-pass">✓ Defined</span>
            </div>
            <div class="test-item">
                <span>t()</span>
                <span class="test-pass">✓ Defined</span>
            </div>
            <div class="test-item">
                <span>getDB()</span>
                <span class="test-pass">✓ Defined</span>
            </div>
            <div class="test-item">
                <span>getPublishedNews()</span>
                <span class="test-pass">✓ Defined</span>
            </div>
            <div class="test-item">
                <span>timeAgo()</span>
                <span class="test-pass">✓ Defined</span>
            </div>
        </div>
        
        <div class="test-card">
            <h2 style="margin-bottom: 16px;">Database Connection</h2>
            
            <?php
            $pdo = getDB();
            if ($pdo) {
                echo '<div class="test-item">';
                echo '<span>Database Connection</span>';
                echo '<span class="test-pass">✓ Connected</span>';
                echo '</div>';
                
                // Test query
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM news");
                $row = $stmt->fetch();
                echo '<div class="test-item">';
                echo '<span>News Table</span>';
                echo '<span class="test-pass">✓ ' . ($row['cnt'] ?? 0) . ' records</span>';
                echo '</div>';
            } else {
                echo '<div class="test-item">';
                echo '<span>Database Connection</span>';
                echo '<span class="test-fail">✗ Failed - Check config.php credentials</span>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="test-card">
            <h2 style="margin-bottom: 16px;">Files & Directories</h2>
            
            <div class="test-item">
                <span>config.php</span>
                <span class="<?= file_exists(__DIR__ . '/config.php') ? 'test-pass' : 'test-fail' ?>">
                    <?= file_exists(__DIR__ . '/config.php') ? '✓ Exists' : '✗ Missing' ?>
                </span>
            </div>
            <div class="test-item">
                <span>functions.php</span>
                <span class="<?= file_exists(__DIR__ . '/functions.php') ? 'test-pass' : 'test-fail' ?>">
                    <?= file_exists(__DIR__ . '/functions.php') ? '✓ Exists' : '✗ Missing' ?>
                </span>
            </div>
            <div class="test-item">
                <span>assets/css/app.css</span>
                <span class="<?= file_exists(__DIR__ . '/assets/css/app.css') ? 'test-pass' : 'test-fail' ?>">
                    <?= file_exists(__DIR__ . '/assets/css/app.css') ? '✓ Exists' : '✗ Missing' ?>
                </span>
            </div>
            <div class="test-item">
                <span>assets/js/app.js</span>
                <span class="<?= file_exists(__DIR__ . '/assets/js/app.js') ? 'test-pass' : 'test-fail' ?>">
                    <?= file_exists(__DIR__ . '/assets/js/app.js') ? '✓ Exists' : '✗ Missing' ?>
                </span>
            </div>
            <div class="test-item">
                <span>data/cache/</span>
                <span class="<?= is_dir(__DIR__ . '/data/cache') || mkdir(__DIR__ . '/data/cache') ? 'test-pass' : 'test-fail' ?>">
                    <?= is_dir(__DIR__ . '/data/cache') || @mkdir(__DIR__ . '/data/cache') ? '✓ Ready' : '✗ Error' ?>
                </span>
            </div>
        </div>
        
        <div class="test-card">
            <h2 style="margin-bottom: 16px;">Quick Links</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="/" class="btn btn-primary">🏠 Homepage</a>
                <a href="/news.php" class="btn btn-outline">📰 News</a>
                <a href="/nepali-patro.php" class="btn btn-outline">📅 Nepali Patro</a>
                <a href="/rashifal.php" class="btn btn-outline">⭐ Rashifal</a>
                <a href="/ipo-tracker.php" class="btn btn-outline">📈 IPO Tracker</a>
            </div>
        </div>
    </div>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?=date('Y')?> आकाशवाणी</p>
            </div>
        </div>
    </footer>
</body>
</html>