<?php
/**
 * Admin Tag Manager
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$tag = new Tag();
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
                $name = sanitize($_POST['name'] ?? '');
                if ($name) {
                    $id = $tag->create($name, sanitize($_POST['color'] ?? null));
                    if ($id) {
                        $message = 'Tag created successfully!';
                        $messageType = 'success';
                    }
                }
                break;
                
            case 'update':
                $id = (int)($_POST['id'] ?? 0);
                if ($id) {
                    $tag->update($id, [
                        'name' => sanitize($_POST['name'] ?? ''),
                        'color' => sanitize($_POST['color'] ?? null),
                        'is_active' => isset($_POST['is_active']) ? 1 : 0
                    ]);
                    $message = 'Tag updated successfully!';
                    $messageType = 'success';
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id && $tag->delete($id)) {
                    $message = 'Tag deleted successfully!';
                    $messageType = 'success';
                }
                break;
        }
    }
}

$tags = $tag->getAll(false);
$csrfToken = generateCSRF();
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag Manager | Admin</title>
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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🏷️ Tag Manager</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage article tags and labels</p>
                </div>
                <button onclick="openModal()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2">
                    Add Tag
                </button>
            </div>
            
            <!-- Tags Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($tags as $t): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full text-white" style="background: <?= $t['color'] ?>">
                                <?= htmlspecialchars($t['name']) ?>
                            </span>
                            <div class="flex gap-1">
                                <button onclick='editTag(<?= json_encode($t) ?>)' class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('Delete this tag?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            <?= $t['article_count'] ?? 0 ?> articles
                            <?php if (!$t['is_active']): ?>
                                <span class="text-red-500 ml-2">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($tags)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No tags yet</h3>
                    <p class="text-gray-500">Tags help organize and discover related content.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Tag Modal -->
    <div id="tagModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full shadow-2xl">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold" id="modalTitle">Add Tag</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">×</button>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="tagId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tag Name</label>
                    <input type="text" name="name" id="tagName" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                    <input type="color" name="color" id="tagColor" value="#6366f1"
                           class="w-full h-10 rounded-lg cursor-pointer">
                </div>
                
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="tagActive" value="1" checked class="rounded">
                    <span class="text-sm">Active</span>
                </label>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('tagModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add Tag';
            document.getElementById('formAction').value = 'create';
            document.getElementById('tagId').value = '';
            document.getElementById('tagName').value = '';
            document.getElementById('tagColor').value = '#6366f1';
            document.getElementById('tagActive').checked = true;
        }
        
        function closeModal() {
            document.getElementById('tagModal').classList.add('hidden');
        }
        
        function editTag(t) {
            document.getElementById('tagModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit Tag';
            document.getElementById('formAction').value = 'update';
            document.getElementById('tagId').value = t.id;
            document.getElementById('tagName').value = t.name;
            document.getElementById('tagColor').value = t.color || '#6366f1';
            document.getElementById('tagActive').checked = t.is_active == 1;
        }
        
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
