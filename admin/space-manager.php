<?php
/**
 * Admin Space Manager - Content Collections like OnlineKhabar, Ratopati
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$space = new Space();
$category = new Category();
$news = new NewsArticle();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
        case 'update':
            $data = [
                'name' => sanitize($_POST['name'] ?? ''),
                'name_ne' => sanitize($_POST['name_ne'] ?? ''),
                'description' => sanitize($_POST['description'] ?? ''),
                'description_ne' => sanitize($_POST['description_ne'] ?? ''),
                'icon' => sanitize($_POST['icon'] ?? ''),
                'color' => sanitize($_POST['color'] ?? '#16a34a'),
                'image' => sanitize($_POST['image'] ?? ''),
                'cover_image' => sanitize($_POST['cover_image'] ?? ''),
                'layout' => sanitize($_POST['layout'] ?? 'grid'),
                'template' => sanitize($_POST['template'] ?? 'default'),
                'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
                'parent_id' => (int)($_POST['parent_id'] ?? 0) ?: null,
                'sort_by' => sanitize($_POST['sort_by'] ?? 'latest'),
                'max_articles' => (int)($_POST['max_articles'] ?? 20),
                'articles_per_page' => (int)($_POST['articles_per_page'] ?? 12),
                'columns' => (int)($_POST['columns'] ?? 3),
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'show_in_menu' => isset($_POST['show_in_menu']) ? 1 : 0,
                'show_in_home' => isset($_POST['show_in_home']) ? 1 : 0,
                'show_in_footer' => isset($_POST['show_in_footer']) ? 1 : 0,
                'show_title' => isset($_POST['show_title']) ? 1 : 0,
                'show_description' => isset($_POST['show_description']) ? 1 : 0,
                'show_cover' => isset($_POST['show_cover']) ? 1 : 0,
                'show_excerpt' => isset($_POST['show_excerpt']) ? 1 : 0,
                'show_author' => isset($_POST['show_author']) ? 1 : 0,
                'show_date' => isset($_POST['show_date']) ? 1 : 0,
                'show_views' => isset($_POST['show_views']) ? 1 : 0,
                'show_thumbnail' => isset($_POST['show_thumbnail']) ? 1 : 0,
                'show_category' => isset($_POST['show_category']) ? 1 : 0,
                'show_read_time' => isset($_POST['show_read_time']) ? 1 : 0,
                'show_pagination' => isset($_POST['show_pagination']) ? 1 : 0,
                'meta_title' => sanitize($_POST['meta_title'] ?? ''),
                'meta_description' => sanitize($_POST['meta_description'] ?? ''),
                'meta_keywords' => sanitize($_POST['meta_keywords'] ?? ''),
                'updated_by' => $_SESSION['user_id'] ?? null,
            ];
            
            if ($action === 'create') {
                $data['created_by'] = $_SESSION['user_id'] ?? null;
                $id = $space->create($data);
                if ($id) {
                    $message = 'Space created successfully!';
                    $messageType = 'success';
                }
            } else {
                $id = (int)($_POST['id'] ?? 0);
                if ($space->update($id, $data)) {
                    $message = 'Space updated successfully!';
                    $messageType = 'success';
                }
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($space->delete($id)) {
                $message = 'Space deleted successfully!';
                $messageType = 'success';
            }
            break;
            
        case 'add_article':
            $spaceId = (int)($_POST['space_id'] ?? 0);
            $articleId = (int)($_POST['article_id'] ?? 0);
            if ($spaceId && $articleId) {
                $space->addArticle($spaceId, $articleId, isset($_POST['is_featured']), isset($_POST['is_pinned']));
                $message = 'Article added to space!';
                $messageType = 'success';
            }
            break;
            
        case 'remove_article':
            $spaceId = (int)($_POST['space_id'] ?? 0);
            $articleId = (int)($_POST['article_id'] ?? 0);
            if ($spaceId && $articleId) {
                $space->removeArticle($spaceId, $articleId);
                $message = 'Article removed from space!';
                $messageType = 'success';
            }
            break;
            
        case 'update_article':
            $spaceId = (int)($_POST['space_id'] ?? 0);
            $articleId = (int)($_POST['article_id'] ?? 0);
            if ($spaceId && $articleId) {
                $space->updateSpaceArticle($spaceId, $articleId, [
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                ]);
                $message = 'Article updated!';
                $messageType = 'success';
            }
            break;
    }
}

$spaces = $space->getAll(false);
$categories = $category->getAll();
$csrfToken = generateCSRF();
$stats = $space->getStats();

// Handle space selection for management
$selectedSpaceId = (int)($_GET['space_id'] ?? 0);
$selectedSpace = $selectedSpaceId ? $space->getById($selectedSpaceId) : null;
$spaceArticles = $selectedSpaceId ? $space->getSpaceArticles($selectedSpaceId, 1, 50) : ['data' => []];
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Space Manager | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .space-card { @apply bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow; }
        .modal-overlay { @apply fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4; }
        .modal-content { @apply bg-white dark:bg-gray-900 rounded-xl w-full max-h-[90vh] overflow-hidden shadow-2xl; }
        .article-item { @apply flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors; }
        .article-item.featured { @apply bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500; }
        .article-item.pinned { @apply bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <?php include __DIR__ . '/includes/admin-header.php'; ?>
    
    <div class="flex">
        <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📚 Space Manager</h1>
                    <p class="text-gray-600 dark:text-gray-400">Content collections like OnlineKhabar, Ratopati, TechPana</p>
                </div>
                <button onclick="openModal()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Space
                </button>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_spaces'] ?></div>
                    <div class="text-sm text-gray-500">Total Spaces</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-green-600"><?= $stats['active_spaces'] ?></div>
                    <div class="text-sm text-gray-500">Active</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-blue-600"><?= $stats['menu_spaces'] ?></div>
                    <div class="text-sm text-gray-500">In Menu</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-purple-600"><?= $stats['homepage_spaces'] ?></div>
                    <div class="text-sm text-gray-500">On Homepage</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-orange-600"><?= number_format($stats['total_articles']) ?></div>
                    <div class="text-sm text-gray-500">Total Articles</div>
                </div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Spaces List -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="font-semibold text-gray-900 dark:text-white">All Spaces</h2>
                        </div>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
                            <?php foreach ($spaces as $s): ?>
                                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors <?= $selectedSpaceId == $s['id'] ? 'bg-primary/5 border-l-4 border-primary' : '' ?>">
                                    <div class="flex items-start justify-between">
                                        <a href="?space_id=<?= $s['id'] ?>" class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <?php if ($s['icon']): ?>
                                                    <span class="text-lg"><?= $s['icon'] ?></span>
                                                <?php endif; ?>
                                                <span class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($s['name']) ?></span>
                                            </div>
                                            <?php if ($s['name_ne']): ?>
                                                <div class="text-sm text-gray-500 mb-1"><?= htmlspecialchars($s['name_ne']) ?></div>
                                            <?php endif; ?>
                                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                                <span><?= $s['article_count'] ?? 0 ?> articles</span>
                                                <span>•</span>
                                                <span class="px-1.5 py-0.5 rounded <?= $s['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                                    <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </div>
                                        </a>
                                        <div class="flex gap-1 ml-2">
                                            <button onclick='editSpace(<?= json_encode($s) ?>)' class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Space Management -->
                <div class="lg:col-span-2">
                    <?php if ($selectedSpace): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <?php if ($selectedSpace['icon']): ?>
                                            <span class="text-xl"><?= $selectedSpace['icon'] ?></span>
                                        <?php endif; ?>
                                        Manage: <?= htmlspecialchars($selectedSpace['name']) ?>
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        <?= $selectedSpace['layout'] ?> layout • 
                                        <?= $selectedSpace['sort_by'] ?> sort • 
                                        Max <?= $selectedSpace['max_articles'] ?> articles
                                    </p>
                                </div>
                                <a href="/space.php?slug=<?= $selectedSpace['slug'] ?>" target="_blank"
                                   class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                                    View Public →
                                </a>
                            </div>
                            
                            <!-- Current Articles -->
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                        Articles in this Space (<?= count($spaceArticles['data']) ?>)
                                    </h3>
                                    <button onclick="openAddArticleModal()" 
                                            class="px-3 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-primary/90">
                                        + Add Article
                                    </button>
                                </div>
                                
                                <div class="space-y-2 max-h-[400px] overflow-y-auto">
                                    <?php if (empty($spaceArticles['data'])): ?>
                                        <div class="text-center py-8 text-gray-500">
                                            <p>No articles in this space yet.</p>
                                            <p class="text-sm">Click "Add Article" to add articles manually.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($spaceArticles['data'] as $article): ?>
                                            <div class="article-item <?= $article['space_featured'] ? 'featured' : '' ?> <?= $article['is_pinned'] ? 'pinned' : '' ?>">
                                                <?php if ($article['featured_image']): ?>
                                                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="" 
                                                         class="w-16 h-12 object-cover rounded-lg flex-shrink-0">
                                                <?php endif; ?>
                                                <div class="flex-1 min-w-0">
                                                    <a href="/news-post.php?slug=<?= $article['slug'] ?>" target="_blank"
                                                       class="font-medium text-gray-900 dark:text-white hover:text-primary line-clamp-1">
                                                        <?= htmlspecialchars($article['title']) ?>
                                                    </a>
                                                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-1">
                                                        <?php if ($article['category_name']): ?>
                                                            <span class="text-primary"><?= htmlspecialchars($article['category_name']) ?></span>
                                                        <?php endif; ?>
                                                        <span><?= number_format($article['view_count']) ?> views</span>
                                                        <?php if ($article['source'] === 'manual'): ?>
                                                            <span class="px-1 py-0.5 bg-blue-100 text-blue-700 rounded">Manual</span>
                                                        <?php else: ?>
                                                            <span class="px-1 py-0.5 bg-gray-100 text-gray-600 rounded">Auto</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <?php if ($article['source'] === 'manual'): ?>
                                                        <form method="POST" class="flex items-center gap-1">
                                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                            <input type="hidden" name="action" value="update_article">
                                                            <input type="hidden" name="space_id" value="<?= $selectedSpaceId ?>">
                                                            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                                            <label class="flex items-center gap-1 text-xs cursor-pointer">
                                                                <input type="checkbox" name="is_featured" value="1" <?= $article['space_featured'] ? 'checked' : '' ?>
                                                                       onchange="this.form.submit()" class="rounded text-purple-600">
                                                                ⭐
                                                            </label>
                                                            <label class="flex items-center gap-1 text-xs cursor-pointer">
                                                                <input type="checkbox" name="is_pinned" value="1" <?= $article['is_pinned'] ? 'checked' : '' ?>
                                                                       onchange="this.form.submit()" class="rounded text-yellow-600">
                                                                📌
                                                            </label>
                                                        </form>
                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                            <input type="hidden" name="action" value="remove_article">
                                                            <input type="hidden" name="space_id" value="<?= $selectedSpaceId ?>">
                                                            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                                            <button type="submit" class="p-1 text-red-600 hover:bg-red-100 rounded">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <div class="text-6xl mb-4">📚</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Select a Space</h3>
                            <p class="text-gray-500">Choose a space from the list to manage its articles and settings.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Space Form Modal -->
    <div id="spaceModal" class="modal-overlay hidden">
        <div class="modal-content max-w-2xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold" id="modalTitle">New Space</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" class="p-6 space-y-6 max-h-[calc(90vh-140px)] overflow-y-auto">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="spaceId">
                
                <!-- Basic Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name (English) *</label>
                        <input type="text" name="name" id="spaceName" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name (नेपाली)</label>
                        <input type="text" name="name_ne" id="spaceNameNe"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" id="spaceDescription" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"></textarea>
                </div>
                
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon (emoji)</label>
                        <input type="text" name="icon" id="spaceIcon" placeholder="📚"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                        <input type="color" name="color" id="spaceColor" value="#16a34a"
                               class="w-full h-10 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Layout</label>
                        <select name="layout" id="spaceLayout"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="grid">Grid</option>
                            <option value="list">List</option>
                            <option value="magazine">Magazine</option>
                            <option value="featured">Featured</option>
                            <option value="masonry">Masonry</option>
                            <option value="carousel">Carousel</option>
                            <option value="timeline">Timeline</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Columns</label>
                        <input type="number" name="columns" id="spaceColumns" value="3" min="1" max="6"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Link</label>
                        <select name="category_id" id="spaceCategory"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="">No category link</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort By</label>
                        <select name="sort_by" id="spaceSortBy"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="latest">Latest</option>
                            <option value="popular">Popular</option>
                            <option value="custom">Custom Order</option>
                            <option value="alphabetical">Alphabetical</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Articles</label>
                        <input type="number" name="max_articles" id="spaceMaxArticles" value="20" min="1" max="100"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Per Page</label>
                        <input type="number" name="articles_per_page" id="spaceArticlesPerPage" value="12" min="1" max="50"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" id="spaceSortOrder" value="0" min="0"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <!-- Display Options -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="font-medium mb-3 text-gray-900 dark:text-white">Display Options</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="spaceIsActive" value="1" checked class="rounded">
                            <span class="text-sm">Active</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" id="spaceIsFeatured" value="1" class="rounded">
                            <span class="text-sm">Featured</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_in_menu" id="spaceShowMenu" value="1" checked class="rounded">
                            <span class="text-sm">Show in Menu</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_in_home" id="spaceShowHome" value="1" checked class="rounded">
                            <span class="text-sm">Show in Home</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_in_footer" id="spaceShowFooter" value="1" class="rounded">
                            <span class="text-sm">Show in Footer</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_pagination" id="spaceShowPagination" value="1" checked class="rounded">
                            <span class="text-sm">Pagination</span>
                        </label>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="font-medium mb-3 text-gray-900 dark:text-white">Article Elements</h4>
                    <div class="grid grid-cols-4 gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_title" value="1" checked class="rounded">
                            <span class="text-sm">Title</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_excerpt" value="1" checked class="rounded">
                            <span class="text-sm">Excerpt</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_thumbnail" value="1" checked class="rounded">
                            <span class="text-sm">Thumbnail</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_category" value="1" checked class="rounded">
                            <span class="text-sm">Category</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_author" value="1" class="rounded">
                            <span class="text-sm">Author</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_date" value="1" checked class="rounded">
                            <span class="text-sm">Date</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_views" value="1" class="rounded">
                            <span class="text-sm">Views</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="show_read_time" value="1" class="rounded">
                            <span class="text-sm">Read Time</span>
                        </label>
                    </div>
                </div>
                
                <!-- SEO -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="font-medium mb-3 text-gray-900 dark:text-white">SEO</h4>
                    <div class="space-y-3">
                        <input type="text" name="meta_title" id="spaceMetaTitle" placeholder="Meta Title"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                        <textarea name="meta_description" id="spaceMetaDescription" rows="2" placeholder="Meta Description"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"></textarea>
                        <input type="text" name="meta_keywords" id="spaceMetaKeywords" placeholder="Meta Keywords (comma separated)"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        Save Space
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Article Modal -->
    <div id="addArticleModal" class="modal-overlay hidden">
        <div class="modal-content max-w-2xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold">Add Article to Space</h2>
                <button onclick="closeAddArticleModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">×</button>
            </div>
            
            <div class="p-4">
                <input type="text" id="articleSearch" placeholder="Search articles..."
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 mb-4"
                       oninput="searchArticles(this.value)">
                
                <div id="articleResults" class="space-y-2 max-h-[400px] overflow-y-auto">
                    <p class="text-gray-500 text-center py-4">Type to search articles...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function openModal(spaceId = null) {
            document.getElementById('spaceModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = spaceId ? 'Edit Space' : 'New Space';
            document.getElementById('formAction').value = spaceId ? 'update' : 'create';
            
            if (!spaceId) {
                // Reset form for new
                document.getElementById('spaceId').value = '';
                document.getElementById('spaceName').value = '';
                document.getElementById('spaceNameNe').value = '';
                document.getElementById('spaceDescription').value = '';
                document.getElementById('spaceIcon').value = '';
                document.getElementById('spaceColor').value = '#16a34a';
                document.getElementById('spaceLayout').value = 'grid';
                document.getElementById('spaceColumns').value = '3';
                document.getElementById('spaceMaxArticles').value = '20';
                document.getElementById('spaceArticlesPerPage').value = '12';
                document.getElementById('spaceSortOrder').value = '0';
                document.getElementById('spaceIsActive').checked = true;
                document.getElementById('spaceIsFeatured').checked = false;
                document.getElementById('spaceShowMenu').checked = true;
                document.getElementById('spaceShowHome').checked = true;
            }
        }
        
        function closeModal() {
            document.getElementById('spaceModal').classList.add('hidden');
        }
        
        function editSpace(space) {
            openModal(space.id);
            document.getElementById('spaceId').value = space.id;
            document.getElementById('spaceName').value = space.name || '';
            document.getElementById('spaceNameNe').value = space.name_ne || '';
            document.getElementById('spaceDescription').value = space.description || '';
            document.getElementById('spaceIcon').value = space.icon || '';
            document.getElementById('spaceColor').value = space.color || '#16a34a';
            document.getElementById('spaceLayout').value = space.layout || 'grid';
            document.getElementById('spaceColumns').value = space.columns || 3;
            document.getElementById('spaceMaxArticles').value = space.max_articles || 20;
            document.getElementById('spaceArticlesPerPage').value = space.articles_per_page || 12;
            document.getElementById('spaceSortOrder').value = space.sort_order || 0;
            document.getElementById('spaceCategory').value = space.category_id || '';
            document.getElementById('spaceSortBy').value = space.sort_by || 'latest';
            document.getElementById('spaceIsActive').checked = space.is_active == 1;
            document.getElementById('spaceIsFeatured').checked = space.is_featured == 1;
            document.getElementById('spaceShowMenu').checked = space.show_in_menu == 1;
            document.getElementById('spaceShowHome').checked = space.show_in_home == 1;
            document.getElementById('spaceShowFooter').checked = space.show_in_footer == 1;
            document.getElementById('spaceMetaTitle').value = space.meta_title || '';
            document.getElementById('spaceMetaDescription').value = space.meta_description || '';
        }
        
        let searchTimeout;
        function searchArticles(query) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                fetch(`/api/search-articles.php?q=${encodeURIComponent(query)}&space_id=<?= $selectedSpaceId ?>&limit=20`)
                    .then(r => r.json())
                    .then(data => {
                        const container = document.getElementById('articleResults');
                        if (data.length === 0) {
                            container.innerHTML = '<p class="text-gray-500 text-center py-4">No articles found</p>';
                            return;
                        }
                        container.innerHTML = data.map(a => `
                            <div class="article-item">
                                ${a.featured_image ? `<img src="${a.featured_image}" class="w-16 h-12 object-cover rounded-lg">` : ''}
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-white line-clamp-1">${a.title}</div>
                                    <div class="text-xs text-gray-400">${a.category_name || 'Uncategorized'} • ${a.view_count} views</div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="action" value="add_article">
                                    <input type="hidden" name="space_id" value="<?= $selectedSpaceId ?>">
                                    <input type="hidden" name="article_id" value="${a.id}">
                                    <button type="submit" class="px-3 py-1.5 text-sm bg-primary text-white rounded-lg hover:bg-primary/90">
                                        Add
                                    </button>
                                </form>
                            </div>
                        `).join('');
                    });
            }, 300);
        }
        
        function openAddArticleModal() {
            document.getElementById('addArticleModal').classList.remove('hidden');
            document.getElementById('articleSearch').focus();
        }
        
        function closeAddArticleModal() {
            document.getElementById('addArticleModal').classList.add('hidden');
        }
        
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
