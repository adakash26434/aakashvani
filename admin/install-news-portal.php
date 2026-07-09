<?php
/**
 * News Portal Database Installer
 * Run this script once to install all news portal tables
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/news-schema.php';

if (!defined('AAK_INIT')) define('AAK_INIT', true);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'INSTALL') {
        $message = 'Please type INSTALL to confirm.';
        $messageType = 'error';
    } else {
        $results = installNewsPortalTables();
        
        if (empty($results['errors'])) {
            $message = 'News Portal tables installed successfully! ' . count($results['success']) . ' tables created.';
            $messageType = 'success';
        } else {
            $message = 'Some errors occurred: ' . implode(', ', $results['errors']);
            $messageType = 'error';
        }
    }
}

$tables = [
    'aak_users' => 'User accounts & roles',
    'aak_user_permissions' => 'User-specific permissions',
    'aak_categories' => 'News categories (with hierarchy)',
    'aak_tags' => 'Article tags',
    'aak_articles' => 'News articles (full CMS)',
    'aak_article_tags' => 'Article-tag relationships',
    'aak_article_images' => 'Article gallery images',
    'aak_media' => 'Media library',
    'aak_homepage_sections' => 'Homepage section configuration',
    'aak_seo_settings' => 'SEO metadata',
    'aak_activity_log' => 'Admin activity tracking',
    'aak_comments' => 'Article comments',
    'aak_advertisements' => 'Advertisement management'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Portal Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">📰 News Portal Installer</h1>
            <p class="text-gray-600 dark:text-gray-400">Enterprise-grade news management system for आकाशवाणी</p>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="mb-8">
            <h2 class="font-semibold mb-4 text-gray-900 dark:text-white">Tables to be created:</h2>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($tables as $table => $desc): ?>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-primary">✓</span>
                        <div>
                            <div class="font-mono text-sm font-medium"><?= $table ?></div>
                            <div class="text-xs text-gray-500"><?= $desc ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Type <strong>INSTALL</strong> to confirm:
                </label>
                <input type="text" name="confirm" placeholder="Type INSTALL"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
            </div>
            
            <button type="submit" 
                    class="w-full px-4 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-lg transition-colors">
                Install News Portal Tables
            </button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="font-medium mb-2 text-gray-900 dark:text-white">After installation:</h3>
            <ol class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li>1. Access <a href="/admin/news-manager.php" class="text-primary hover:underline">News Manager</a> to create articles</li>
                <li>2. Configure <a href="/admin/category-manager.php" class="text-primary hover:underline">Categories</a></li>
                <li>3. Set up <a href="/admin/homepage-sections.php" class="text-primary hover:underline">Homepage Sections</a></li>
                <li>4. Manage <a href="/admin/tag-manager.php" class="text-primary hover:underline">Tags</a></li>
            </ol>
        </div>
    </div>
</body>
</html>
