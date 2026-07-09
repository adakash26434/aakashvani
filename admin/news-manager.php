<?php
/**
 * Admin News Manager - Enterprise News Portal CMS
 * आकाशवाणी News Management System
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$news = new NewsArticle();
$category = new Category();
$tag = new Tag();

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please refresh and try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
            case 'update':
                $data = [
                    'title' => sanitize($_POST['title'] ?? ''),
                    'title_ne' => sanitize($_POST['title_ne'] ?? ''),
                    'excerpt' => sanitize($_POST['excerpt'] ?? ''),
                    'excerpt_ne' => sanitize($_POST['excerpt_ne'] ?? ''),
                    'content' => $_POST['content'] ?? '',
                    'content_ne' => $_POST['content_ne'] ?? '',
                    'featured_image' => sanitize($_POST['featured_image'] ?? ''),
                    'featured_image_caption' => sanitize($_POST['featured_image_caption'] ?? ''),
                    'featured_image_alt' => sanitize($_POST['featured_image_alt'] ?? ''),
                    'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
                    'author_id' => $_SESSION['user_id'] ?? null,
                    'status' => sanitize($_POST['status'] ?? 'draft'),
                    'scheduled_at' => !empty($_POST['scheduled_at']) ? date('Y-m-d H:i:s', strtotime($_POST['scheduled_at'])) : null,
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_breaking' => isset($_POST['is_breaking']) ? 1 : 0,
                    'is_trending' => isset($_POST['is_trending']) ? 1 : 0,
                    'is_editors_pick' => isset($_POST['is_editors_pick']) ? 1 : 0,
                    'language' => sanitize($_POST['language'] ?? 'both'),
                    'meta_title' => sanitize($_POST['meta_title'] ?? ''),
                    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
                    'meta_keywords' => sanitize($_POST['meta_keywords'] ?? ''),
                ];
                
                // Handle tags
                $tagIds = [];
                if (!empty($_POST['tags'])) {
                    foreach ($_POST['tags'] as $tagName) {
                        $tagName = trim($tagName);
                        if ($tagName) {
                            $tagIds[] = $tag->findOrCreate($tagName);
                        }
                    }
                }
                
                if ($action === 'create') {
                    $id = $news->create($data);
                    if ($id) {
                        $news->setTags($id, $tagIds);
                        logActivity($_SESSION['user_id'] ?? 0, 'create', 'article', $id, $data['title']);
                        $message = 'Article created successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to create article.';
                        $messageType = 'error';
                    }
                } else {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($news->update($id, $data)) {
                        $news->setTags($id, $tagIds);
                        logActivity($_SESSION['user_id'] ?? 0, 'update', 'article', $id, $data['title']);
                        $message = 'Article updated successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update article.';
                        $messageType = 'error';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                $article = $news->getById($id, true);
                if ($news->delete($id)) {
                    logActivity($_SESSION['user_id'] ?? 0, 'delete', 'article', $id, $article['title'] ?? '');
                    $message = 'Article deleted successfully!';
                    $messageType = 'success';
                }
                break;
                
            case 'duplicate':
                $id = (int)($_POST['id'] ?? 0);
                $newId = $news->duplicate($id);
                if ($newId) {
                    $message = 'Article duplicated successfully!';
                    $messageType = 'success';
                }
                break;
                
            case 'publish':
            case 'unpublish':
            case 'archive':
                $id = (int)($_POST['id'] ?? 0);
                $status = $action === 'publish' ? 'published' : ($action === 'unpublish' ? 'draft' : 'archived');
                if ($news->update($id, ['status' => $status])) {
                    $message = 'Article ' . $status . '!';
                    $messageType = 'success';
                }
                break;
        }
    }
}

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'category_id' => (int)($_GET['category_id'] ?? 0) ?: null,
    'search' => sanitize($_GET['search'] ?? ''),
    'order_by' => $_GET['order_by'] ?? 'a.published_at',
    'order_dir' => $_GET['order_dir'] ?? 'DESC'
];

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

// Get articles
$result = $news->getArticles($filters, $page, $perPage);
$articles = $result['data'];
$categories = $category->getAll();
$stats = $news->getStats();

$csrfToken = generateCSRF();
$paginator = paginate($result['total'], $result['total_pages'], $page, [
    'status' => $filters['status'],
    'category_id' => $filters['category_id'],
    'search' => $filters['search']
]);
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Manager | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.5.0/pro-standard.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#16a34a',
                        secondary: '#6366f1'
                    }
                }
            }
        }
    </script>
    <style>
        .article-status-draft { @apply bg-gray-100 text-gray-600; }
        .article-status-pending { @apply bg-yellow-100 text-yellow-700; }
        .article-status-published { @apply bg-green-100 text-green-700; }
        .article-status-scheduled { @apply bg-blue-100 text-blue-700; }
        .article-status-archived { @apply bg-red-100 text-red-700; }
        
        .table-actions { @apply opacity-0 group-hover:opacity-100 transition-opacity; }
        
        .editor-toolbar {
            @apply flex flex-wrap gap-1 p-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700;
        }
        
        .editor-btn {
            @apply p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors;
        }
        
        .editor-btn.active {
            @apply bg-primary text-white;
        }
        
        .tag-input {
            @apply flex flex-wrap gap-2 p-2 border border-gray-300 dark:border-gray-600 rounded-lg min-h-[42px];
        }
        
        .tag-item {
            @apply flex items-center gap-1 px-2 py-1 bg-primary/10 text-primary rounded-full text-sm;
        }
        
        .modal-overlay {
            @apply fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4;
        }
        
        .modal-content {
            @apply bg-white dark:bg-gray-900 rounded-xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl;
        }
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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📰 News Manager</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage articles, breaking news, and featured content</p>
                </div>
                <button onclick="openEditor()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Article
                </button>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total'] ?></div>
                    <div class="text-sm text-gray-500">Total</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-gray-600"><?= $stats['draft'] ?></div>
                    <div class="text-sm text-gray-500">Drafts</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-yellow-600"><?= $stats['pending'] ?></div>
                    <div class="text-sm text-gray-500">Pending</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-green-600"><?= $stats['published'] ?></div>
                    <div class="text-sm text-gray-500">Published</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-blue-600"><?= $stats['scheduled'] ?></div>
                    <div class="text-sm text-gray-500">Scheduled</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-primary"><?= number_format($stats['total_views']) ?></div>
                    <div class="text-sm text-gray-500">Views</div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" 
                               placeholder="Search articles..." 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                        <option value="">All Status</option>
                        <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="scheduled" <?= $filters['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                    <select name="category_id" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        Filter
                    </button>
                    <a href="?" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        Clear
                    </a>
                </form>
            </div>
            
            <!-- Articles Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Article</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Flags</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Views</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Date</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($articles as $article): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 group">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <?php if ($article['featured_image']): ?>
                                                <img src="<?= htmlspecialchars($article['featured_image']) ?>" 
                                                     alt="" class="w-12 h-12 object-cover rounded-lg">
                                            <?php endif; ?>
                                            <div>
                                                <a href="?action=edit&id=<?= $article['id'] ?>" 
                                                   class="font-medium text-gray-900 dark:text-white hover:text-primary">
                                                    <?= htmlspecialchars(truncate($article['title'], 60)) ?>
                                                </a>
                                                <?php if ($article['author_name']): ?>
                                                    <div class="text-sm text-gray-500">by <?= htmlspecialchars($article['author_name']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        <?= $article['category_name'] ? htmlspecialchars($article['category_name']) : '<span class="text-gray-400">Uncategorized</span>' ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full article-status-<?= $article['status'] ?>">
                                            <?= ucfirst($article['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            <?php if ($article['is_featured']): ?>
                                                <span class="px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700 rounded">⭐ Featured</span>
                                            <?php endif; ?>
                                            <?php if ($article['is_breaking']): ?>
                                                <span class="px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">🔥 Breaking</span>
                                            <?php endif; ?>
                                            <?php if ($article['is_trending']): ?>
                                                <span class="px-1.5 py-0.5 text-xs bg-orange-100 text-orange-700 rounded">📈 Trending</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        <?= number_format($article['view_count']) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        <?= $article['published_at'] ? date('M j, Y', strtotime($article['published_at'])) : '—' ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2 table-actions">
                                            <a href="?action=edit&id=<?= $article['id'] ?>" 
                                               class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <a href="/news-post.php?slug=<?= $article['slug'] ?>" target="_blank"
                                               class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg" title="Preview">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <form method="POST" class="inline" onsubmit="return confirm('Duplicate this article?')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="action" value="duplicate">
                                                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                                <button type="submit" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded-lg" title="Duplicate">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            <?php if ($article['status'] !== 'published'): ?>
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="action" value="publish">
                                                    <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                                    <button type="submit" class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg" title="Publish">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this article?')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg" title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($articles)): ?>
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No articles found</h3>
                        <p class="text-gray-500 mb-4">Create your first article to get started.</p>
                        <button onclick="openEditor()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                            Create Article
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($result['total_pages'] > 1): ?>
                <div class="mt-6 flex justify-center">
                    <div class="flex gap-2">
                        <?php foreach ($paginator as $item): ?>
                            <?php if ($item['type'] === 'ellipsis'): ?>
                                <span class="px-3 py-2 text-gray-400">...</span>
                            <?php elseif ($item['type'] === 'page'): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                                   class="px-3 py-2 rounded-lg <?= $item['current'] ? 'bg-primary text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-800' ?>">
                                    <?= $item['page'] ?>
                                </a>
                            <?php elseif ($item['type'] === 'prev'): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                                   class="px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">←</a>
                            <?php elseif ($item['type'] === 'next'): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                                   class="px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">→</a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Editor Modal -->
    <div id="editorModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold" id="editorTitle">New Article</h2>
                <button onclick="closeEditor()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" class="p-6 space-y-6 max-h-[calc(90vh-140px)] overflow-y-auto" id="articleForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="articleId">
                
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Title (English) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 focus:ring-2 focus:ring-primary"
                           placeholder="Enter article title">
                </div>
                
                <!-- Nepali Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Title (नेपाली)
                    </label>
                    <input type="text" name="title_ne" id="title_ne"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                           placeholder="नेपालीमा शीर्षक">
                </div>
                
                <!-- Excerpt -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Excerpt (English)
                        </label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                                  placeholder="Brief summary..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Excerpt (नेपाली)
                        </label>
                        <textarea name="excerpt_ne" id="excerpt_ne" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                                  placeholder="छोटो सारांश..."></textarea>
                    </div>
                </div>
                
                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Content (English)
                    </label>
                    <div class="editor-toolbar">
                        <button type="button" onclick="execCmd('bold')" class="editor-btn" title="Bold"><b>B</b></button>
                        <button type="button" onclick="execCmd('italic')" class="editor-btn" title="Italic"><i>I</i></button>
                        <button type="button" onclick="execCmd('underline')" class="editor-btn" title="Underline"><u>U</u></button>
                        <button type="button" onclick="execCmd('formatBlock|P')" class="editor-btn" title="Paragraph">P</button>
                        <button type="button" onclick="execCmd('formatBlock|H2')" class="editor-btn" title="Heading 2">H2</button>
                        <button type="button" onclick="execCmd('formatBlock|H3')" class="editor-btn" title="Heading 3">H3</button>
                        <button type="button" onclick="insertLink()" class="editor-btn" title="Link">🔗</button>
                        <button type="button" onclick="insertImage()" class="editor-btn" title="Image">🖼️</button>
                        <button type="button" onclick="execCmd('insertUnorderedList')" class="editor-btn" title="List">• List</button>
                        <button type="button" onclick="execCmd('formatBlock|BLOCKQUOTE')" class="editor-btn" title="Quote">❝</button>
                        <button type="button" onclick="insertTable()" class="editor-btn" title="Table">⊞</button>
                    </div>
                    <textarea name="content" id="content" rows="15"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 font-mono"
                              placeholder="Article content..."></textarea>
                </div>
                
                <!-- Nepali Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Content (नेपाली)
                    </label>
                    <textarea name="content_ne" id="content_ne" rows="10"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 font-mono"
                              placeholder="लेखको सामग्री..."></textarea>
                </div>
                
                <!-- Featured Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Featured Image
                    </label>
                    <div class="flex gap-4">
                        <input type="text" name="featured_image" id="featured_image"
                               class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                               placeholder="Image URL or upload">
                        <button type="button" onclick="openMediaPicker('featured_image')" 
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            Browse
                        </button>
                    </div>
                    <div class="mt-2">
                        <input type="text" name="featured_image_caption" id="featured_image_caption"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-sm"
                               placeholder="Image caption">
                    </div>
                    <div class="mt-2">
                        <input type="text" name="featured_image_alt" id="featured_image_alt"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 text-sm"
                               placeholder="Alt text (for SEO)">
                    </div>
                </div>
                
                <!-- Category & Tags -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Category
                        </label>
                        <select name="category_id" id="category_id"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tags (comma separated)
                        </label>
                        <input type="text" id="tagInput" placeholder="Add tags..."
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                               onkeydown="if(event.key==='Enter'||event.key===','){event.preventDefault();addTag();}">
                        <div id="tagList" class="flex flex-wrap gap-2 mt-2"></div>
                        <input type="hidden" name="tags" id="tagsField">
                    </div>
                </div>
                
                <!-- Status & Flags -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select name="status" id="status"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="draft">Draft</option>
                            <option value="pending">Pending Review</option>
                            <option value="published">Published</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Schedule For
                        </label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Language
                        </label>
                        <select name="language" id="language"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="both">Both</option>
                            <option value="ne">Nepali Only</option>
                            <option value="en">English Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Options
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1" class="rounded">
                                <span class="text-sm">⭐ Featured</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_breaking" id="is_breaking" value="1" class="rounded">
                                <span class="text-sm">🔥 Breaking</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_trending" id="is_trending" value="1" class="rounded">
                                <span class="text-sm">📈 Trending</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- SEO -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold mb-4">🔍 SEO Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Title
                            </label>
                            <input type="text" name="meta_title" id="meta_title" maxlength="150"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <div class="text-xs text-gray-500 mt-1"><span id="metaTitleCount">0</span>/150</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Keywords
                            </label>
                            <input type="text" name="meta_keywords" id="meta_keywords"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"
                                   placeholder="keyword1, keyword2, keyword3">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Description
                            </label>
                            <textarea name="meta_description" id="meta_description" rows="2" maxlength="300"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"></textarea>
                            <div class="text-xs text-gray-500 mt-1"><span id="metaDescCount">0</span>/300</div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="flex justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeEditor()" 
                            class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        Save Article
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Media Picker Modal -->
    <div id="mediaModal" class="modal-overlay hidden">
        <div class="modal-content max-w-6xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold">Media Library</h2>
                <button onclick="closeMediaPicker()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-4 md:grid-cols-6 gap-4" id="mediaGrid">
                    <!-- Media items loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedTags = [];
        let mediaCallback = null;
        
        function openEditor(articleId = null) {
            document.getElementById('editorModal').classList.remove('hidden');
            document.getElementById('editorTitle').textContent = articleId ? 'Edit Article' : 'New Article';
            document.getElementById('formAction').value = articleId ? 'update' : 'create';
            document.getElementById('articleId').value = articleId || '';
            
            if (articleId) {
                fetch('?ajax=1&action=get&id=' + articleId)
                    .then(r => r.json())
                    .then(data => populateForm(data));
            }
        }
        
        function closeEditor() {
            document.getElementById('editorModal').classList.add('hidden');
        }
        
        function populateForm(data) {
            document.getElementById('title').value = data.title || '';
            document.getElementById('title_ne').value = data.title_ne || '';
            document.getElementById('excerpt').value = data.excerpt || '';
            document.getElementById('excerpt_ne').value = data.excerpt_ne || '';
            document.getElementById('content').value = data.content || '';
            document.getElementById('content_ne').value = data.content_ne || '';
            document.getElementById('featured_image').value = data.featured_image || '';
            document.getElementById('featured_image_caption').value = data.featured_image_caption || '';
            document.getElementById('featured_image_alt').value = data.featured_image_alt || '';
            document.getElementById('category_id').value = data.category_id || '';
            document.getElementById('status').value = data.status || 'draft';
            document.getElementById('language').value = data.language || 'both';
            document.getElementById('is_featured').checked = data.is_featured == 1;
            document.getElementById('is_breaking').checked = data.is_breaking == 1;
            document.getElementById('is_trending').checked = data.is_trending == 1;
            document.getElementById('meta_title').value = data.meta_title || '';
            document.getElementById('meta_description').value = data.meta_description || '';
            document.getElementById('meta_keywords').value = data.meta_keywords || '';
            
            if (data.scheduled_at) {
                document.getElementById('scheduled_at').value = data.scheduled_at.slice(0, 16);
            }
            
            selectedTags = data.tags || [];
            renderTags();
        }
        
        function addTag() {
            const input = document.getElementById('tagInput');
            const tag = input.value.trim().replace(',', '');
            if (tag && !selectedTags.includes(tag)) {
                selectedTags.push(tag);
                renderTags();
            }
            input.value = '';
        }
        
        function removeTag(tag) {
            selectedTags = selectedTags.filter(t => t !== tag);
            renderTags();
        }
        
        function renderTags() {
            const container = document.getElementById('tagList');
            container.innerHTML = selectedTags.map(tag => 
                `<span class="tag-item">${tag}<button type="button" onclick="removeTag('${tag}')" class="ml-1 hover:text-red-500">×</button></span>`
            ).join('');
            document.getElementById('tagsField').value = JSON.stringify(selectedTags);
        }
        
        function openMediaPicker(callback) {
            mediaCallback = callback;
            document.getElementById('mediaModal').classList.remove('hidden');
            loadMedia();
        }
        
        function closeMediaPicker() {
            document.getElementById('mediaModal').classList.add('hidden');
            mediaCallback = null;
        }
        
        function loadMedia(page = 1) {
            fetch(`/api/media.php?page=${page}`)
                .then(r => r.json())
                .then(data => {
                    const grid = document.getElementById('mediaGrid');
                    grid.innerHTML = data.data.map(item => `
                        <div class="cursor-pointer hover:ring-2 ring-primary rounded-lg overflow-hidden" 
                             onclick="selectMedia('${item.url}')">
                            <img src="${item.thumbnail || item.url}" alt="${item.caption || ''}" class="w-full h-24 object-cover">
                        </div>
                    `).join('');
                });
        }
        
        function selectMedia(url) {
            if (mediaCallback) {
                document.getElementById(mediaCallback).value = url;
            }
            closeMediaPicker();
        }
        
        function execCmd(cmd, value = null) {
            document.execCommand(cmd, false, value);
        }
        
        function insertLink() {
            const url = prompt('Enter URL:');
            if (url) execCmd('createLink', url);
        }
        
        function insertImage() {
            const url = prompt('Enter image URL:');
            if (url) execCmd('insertImage', url);
        }
        
        function insertTable() {
            const html = '<table border="1" cellpadding="5" cellspacing="0"><tr><td>Cell 1</td><td>Cell 2</td></tr><tr><td>Cell 3</td><td>Cell 4</td></tr></table>';
            execCmd('insertHTML', html);
        }
        
        // SEO character counters
        document.getElementById('meta_title')?.addEventListener('input', function() {
            document.getElementById('metaTitleCount').textContent = this.value.length;
        });
        document.getElementById('meta_description')?.addEventListener('input', function() {
            document.getElementById('metaDescCount').textContent = this.value.length;
        });
        
        // Show notification
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
        
        // Check for edit action
        <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])): ?>
        openEditor(<?= (int)$_GET['id'] ?>);
        <?php endif; ?>
    </script>
</body>
</html>
