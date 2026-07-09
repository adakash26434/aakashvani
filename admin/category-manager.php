<?php
/**
 * Admin Category Manager
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$category = new Category();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
            case 'update':
                $data = [
                    'name' => sanitize($_POST['name'] ?? ''),
                    'name_ne' => sanitize($_POST['name_ne'] ?? ''),
                    'description' => sanitize($_POST['description'] ?? ''),
                    'parent_id' => (int)($_POST['parent_id'] ?? 0) ?: null,
                    'icon' => sanitize($_POST['icon'] ?? ''),
                    'color' => sanitize($_POST['color'] ?? '#16a34a'),
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'show_in_menu' => isset($_POST['show_in_menu']) ? 1 : 0,
                    'show_in_home' => isset($_POST['show_in_home']) ? 1 : 0,
                    'meta_title' => sanitize($_POST['meta_title'] ?? ''),
                    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
                ];
                
                if ($action === 'create') {
                    $id = $category->create($data);
                    if ($id) {
                        $message = 'Category created successfully!';
                        $messageType = 'success';
                    }
                } else {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($category->update($id, $data)) {
                        $message = 'Category updated successfully!';
                        $messageType = 'success';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($category->delete($id)) {
                    $message = 'Category deleted successfully!';
                    $messageType = 'success';
                }
                break;
        }
    }
}

$categories = $category->getAll(false);
$csrfToken = generateCSRF();
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Manager | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <style>
        .modal-overlay { @apply fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4; }
        .modal-content { @apply bg-white dark:bg-gray-900 rounded-xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-2xl; }
        .category-color { @apply w-6 h-6 rounded-full; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <?php include __DIR__ . '/includes/admin-header.php'; ?>
    
    <div class="flex">
        <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📁 Category Manager</h1>
                    <p class="text-gray-600 dark:text-gray-400">Organize your content with categories</p>
                </div>
                <button onclick="openModal()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Category
                </button>
            </div>
            
            <!-- Categories Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Category</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Slug</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Parent</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Articles</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Menu</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Home</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">Active</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="category-color" style="background: <?= $cat['color'] ?>"></span>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($cat['name']) ?></div>
                                            <?php if ($cat['name_ne']): ?>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($cat['name_ne']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?= htmlspecialchars($cat['slug']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?= $cat['parent_name'] ? htmlspecialchars($cat['parent_name']) : '—' ?>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-500">
                                    <?= $cat['article_count'] ?? 0 ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?= $cat['show_in_menu'] ? '✅' : '❌' ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?= $cat['show_in_home'] ? '✅' : '❌' ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?= $cat['is_active'] ? '✅' : '❌' ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button onclick='editCategory(<?= json_encode($cat) ?>)' 
                                                class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg">
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
                
                <?php if (empty($categories)): ?>
                    <div class="p-12 text-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No categories yet</h3>
                        <p class="text-gray-500">Create your first category to organize content.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Category Modal -->
    <div id="categoryModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold" id="modalTitle">Add Category</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="categoryId">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name (English)</label>
                        <input type="text" name="name" id="name" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name (नेपाली)</label>
                        <input type="text" name="name_ne" id="name_ne"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parent Category</label>
                    <select name="parent_id" id="parent_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                        <option value="">None (Top Level)</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"></textarea>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                        <input type="color" name="color" id="color" value="#16a34a"
                               class="w-full h-10 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon (emoji)</label>
                        <input type="text" name="icon" id="icon" placeholder="📰"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="0"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded">
                        <span class="text-sm">Active</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_in_menu" id="show_in_menu" value="1" checked class="rounded">
                        <span class="text-sm">Show in Menu</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="show_in_home" id="show_in_home" value="1" checked class="rounded">
                        <span class="text-sm">Show in Home</span>
                    </label>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="font-medium mb-3">SEO</h4>
                    <div class="space-y-3">
                        <input type="text" name="meta_title" id="meta_title" placeholder="Meta Title"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                        <textarea name="meta_description" id="meta_description" rows="2" placeholder="Meta Description"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('categoryModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value = 'create';
            document.getElementById('categoryId').value = '';
            document.getElementById('name').value = '';
            document.getElementById('name_ne').value = '';
            document.getElementById('description').value = '';
            document.getElementById('parent_id').value = '';
            document.getElementById('color').value = '#16a34a';
            document.getElementById('icon').value = '';
            document.getElementById('sort_order').value = '0';
            document.getElementById('is_active').checked = true;
            document.getElementById('show_in_menu').checked = true;
            document.getElementById('show_in_home').checked = true;
        }
        
        function closeModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }
        
        function editCategory(cat) {
            document.getElementById('categoryModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value = 'update';
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('name').value = cat.name;
            document.getElementById('name_ne').value = cat.name_ne || '';
            document.getElementById('description').value = cat.description || '';
            document.getElementById('parent_id').value = cat.parent_id || '';
            document.getElementById('color').value = cat.color || '#16a34a';
            document.getElementById('icon').value = cat.icon || '';
            document.getElementById('sort_order').value = cat.sort_order || 0;
            document.getElementById('is_active').checked = cat.is_active == 1;
            document.getElementById('show_in_menu').checked = cat.show_in_menu == 1;
            document.getElementById('show_in_home').checked = cat.show_in_home == 1;
        }
        
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
