<?php
/**
 * Admin Homepage Sections Manager
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$message = '';
$messageType = '';

// Get DB connection
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database error');
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
        case 'update':
            $data = [
                'section_key' => sanitize($_POST['section_key'] ?? ''),
                'title' => sanitize($_POST['title'] ?? ''),
                'title_ne' => sanitize($_POST['title_ne'] ?? ''),
                'subtitle' => sanitize($_POST['subtitle'] ?? ''),
                'type' => sanitize($_POST['type'] ?? 'latest'),
                'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
                'max_items' => (int)($_POST['max_items'] ?? 10),
                'style' => sanitize($_POST['style'] ?? 'grid'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0),
                'cols_md' => (int)($_POST['cols_md'] ?? 4),
                'cols_sm' => (int)($_POST['cols_sm'] ?? 2),
                'show_title' => isset($_POST['show_title']) ? 1 : 0,
                'show_excerpt' => isset($_POST['show_excerpt']) ? 1 : 0,
                'show_image' => isset($_POST['show_image']) ? 1 : 1,
            ];
            
            $articleIds = array_filter(array_map('intval', $_POST['article_ids'] ?? []));
            $data['article_ids'] = implode(',', $articleIds);
            
            if ($action === 'create') {
                $cols = implode(', ', array_keys($data));
                $placeholders = ':' . implode(', :', array_keys($data));
                $sql = "INSERT INTO aak_homepage_sections ({$cols}) VALUES ({$placeholders})";
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $set = implode(' = ?, ', array_keys($data)) . ' = ?';
                $values = array_values($data);
                $values[] = $id;
                $sql = "UPDATE aak_homepage_sections SET {$set} WHERE id = ?";
            }
            
            try {
                $pdo->prepare($sql)->execute(array_values($data));
                $message = 'Section saved successfully!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("DELETE FROM aak_homepage_sections WHERE id = ?")->execute([$id]);
            $message = 'Section deleted successfully!';
            $messageType = 'success';
            break;
            
        case 'reorder':
            $orders = $_POST['order'] ?? [];
            foreach ($orders as $id => $order) {
                $pdo->prepare("UPDATE aak_homepage_sections SET sort_order = ? WHERE id = ?")->execute([(int)$order, (int)$id]);
            }
            $message = 'Sections reordered!';
            $messageType = 'success';
            break;
    }
}

// Get sections
$sections = $pdo->query("SELECT hs.*, c.name as category_name 
                         FROM aak_homepage_sections hs 
                         LEFT JOIN aak_categories c ON hs.category_id = c.id 
                         ORDER BY hs.sort_order ASC")->fetchAll();

// Get categories for dropdown
$categories = $pdo->query("SELECT id, name FROM aak_categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

$csrfToken = generateCSRF();
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Sections | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <?php include __DIR__ . '/includes/admin-header.php'; ?>
    
    <div class="flex">
        <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🏠 Homepage Sections</h1>
                    <p class="text-gray-600 dark:text-gray-400">Configure homepage content sections</p>
                </div>
                <button onclick="openModal()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2">
                    Add Section
                </button>
            </div>
            
            <!-- Available Section Types -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-6 border border-gray-200 dark:border-gray-700">
                <h3 class="font-medium mb-3">Available Section Types:</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">latest - Latest News</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">featured - Featured News</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">trending - Trending News</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">most_viewed - Most Viewed</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">category - Category News</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">breaking - Breaking News</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">editors_pick - Editor's Choice</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">gallery - Photo Gallery</span>
                    <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">video - Video Section</span>
                </div>
            </div>
            
            <!-- Sections List -->
            <div class="space-y-4" id="sectionsList">
                <?php foreach ($sections as $section): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700" data-id="<?= $section['id'] ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded <?= $section['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $section['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                                        <?= $section['type'] ?>
                                    </span>
                                    <?php if ($section['category_name']): ?>
                                        <span class="text-sm text-gray-500">→ <?= htmlspecialchars($section['category_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="font-semibold text-lg"><?= htmlspecialchars($section['title']) ?>
                                    <?php if ($section['title_ne']): ?>
                                        <span class="text-gray-500 font-normal">/ <?= htmlspecialchars($section['title_ne']) ?></span>
                                    <?php endif; ?>
                                </h3>
                                <div class="text-sm text-gray-500 mt-1">
                                    Style: <?= $section['style'] ?> | 
                                    Items: <?= $section['max_items'] ?> |
                                    Columns: <?= $section['cols_md'] ?> |
                                    Order: <?= $section['sort_order'] ?>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick='editSection(<?= json_encode($section) ?>)' 
                                        class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this section?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $section['id'] ?>">
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($sections)): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No sections configured</h3>
                        <p class="text-gray-500">Add homepage sections to display news content.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Section Modal -->
    <div id="sectionModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-900">
                <h2 class="text-xl font-bold" id="modalTitle">Add Section</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">×</button>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="sectionId">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section Key</label>
                        <input type="text" name="section_key" id="sectionKey" required placeholder="e.g., latest-news-1"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select name="type" id="sectionType" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="latest">Latest News</option>
                            <option value="featured">Featured</option>
                            <option value="trending">Trending</option>
                            <option value="most_viewed">Most Viewed</option>
                            <option value="category">Category Based</option>
                            <option value="breaking">Breaking News</option>
                            <option value="editors_pick">Editor's Pick</option>
                            <option value="gallery">Photo Gallery</option>
                            <option value="video">Video Section</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title (English)</label>
                        <input type="text" name="title" id="sectionTitle" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title (नेपाली)</label>
                        <input type="text" name="title_ne" id="sectionTitleNe"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtitle</label>
                    <input type="text" name="subtitle" id="sectionSubtitle"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category_id" id="sectionCategory"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Items</label>
                        <input type="number" name="max_items" id="maxItems" value="10" min="1" max="50"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Style</label>
                        <select name="style" id="sectionStyle"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                            <option value="grid">Grid</option>
                            <option value="list">List</option>
                            <option value="carousel">Carousel</option>
                            <option value="big_featured">Big Featured</option>
                            <option value="compact">Compact</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Columns (MD)</label>
                        <input type="number" name="cols_md" id="colsMd" value="4" min="1" max="6"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Columns (SM)</label>
                        <input type="number" name="cols_sm" id="colsSm" value="2" min="1" max="4"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" id="sortOrder" value="0" min="0"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div class="flex flex-col justify-end">
                        <label class="flex items-center gap-2 mb-1">
                            <input type="checkbox" name="is_active" id="sectionActive" value="1" checked class="rounded">
                            <span class="text-sm">Active</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_title" id="showTitle" value="1" checked class="rounded">
                        <span class="text-sm">Show Title</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_excerpt" id="showExcerpt" value="1" class="rounded">
                        <span class="text-sm">Show Excerpt</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_image" id="showImage" value="1" checked class="rounded">
                        <span class="text-sm">Show Image</span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('sectionModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add Section';
            document.getElementById('formAction').value = 'create';
            document.getElementById('sectionId').value = '';
            document.getElementById('sectionKey').value = '';
            document.getElementById('sectionType').value = 'latest';
            document.getElementById('sectionTitle').value = '';
            document.getElementById('sectionTitleNe').value = '';
            document.getElementById('sectionSubtitle').value = '';
            document.getElementById('sectionCategory').value = '';
            document.getElementById('maxItems').value = '10';
            document.getElementById('sectionStyle').value = 'grid';
            document.getElementById('colsMd').value = '4';
            document.getElementById('colsSm').value = '2';
            document.getElementById('sortOrder').value = '0';
            document.getElementById('sectionActive').checked = true;
            document.getElementById('showTitle').checked = true;
            document.getElementById('showExcerpt').checked = false;
            document.getElementById('showImage').checked = true;
        }
        
        function closeModal() {
            document.getElementById('sectionModal').classList.add('hidden');
        }
        
        function editSection(s) {
            document.getElementById('sectionModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit Section';
            document.getElementById('formAction').value = 'update';
            document.getElementById('sectionId').value = s.id;
            document.getElementById('sectionKey').value = s.section_key || '';
            document.getElementById('sectionType').value = s.type || 'latest';
            document.getElementById('sectionTitle').value = s.title || '';
            document.getElementById('sectionTitleNe').value = s.title_ne || '';
            document.getElementById('sectionSubtitle').value = s.subtitle || '';
            document.getElementById('sectionCategory').value = s.category_id || '';
            document.getElementById('maxItems').value = s.max_items || 10;
            document.getElementById('sectionStyle').value = s.style || 'grid';
            document.getElementById('colsMd').value = s.cols_md || 4;
            document.getElementById('colsSm').value = s.cols_sm || 2;
            document.getElementById('sortOrder').value = s.sort_order || 0;
            document.getElementById('sectionActive').checked = s.is_active == 1;
            document.getElementById('showTitle').checked = s.show_title == 1;
            document.getElementById('showExcerpt').checked = s.show_excerpt == 1;
            document.getElementById('showImage').checked = s.show_image == 1;
        }
        
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
