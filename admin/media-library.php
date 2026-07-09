<?php
/**
 * Admin Media Library
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$media = new MediaLibrary();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'upload':
            if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $userId = $_SESSION['user_id'] ?? null;
                $id = $media->upload($_FILES['file'], $userId);
                if ($id) {
                    $message = 'File uploaded successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Upload failed. Check file type and size.';
                    $messageType = 'error';
                }
            }
            break;
            
        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $media->updateCaption($id, $_POST['caption'] ?? '', $_POST['alt_text'] ?? '');
                $message = 'Media updated!';
                $messageType = 'success';
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $media->delete($id)) {
                $message = 'Media deleted!';
                $messageType = 'success';
            }
            break;
    }
}

// Get media items
$filters = [
    'mime' => $_GET['type'] ?? '',
    'folder' => $_GET['folder'] ?? '',
    'search' => sanitize($_GET['search'] ?? '')
];
$page = max(1, (int)($_GET['page'] ?? 1));
$result = $media->getAll($filters, $page, 30);

$csrfToken = generateCSRF();
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <style>
        .media-grid { @apply grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4; }
        .media-item { @apply relative group cursor-pointer rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors; }
        .media-overlay { @apply absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2; }
        .modal-overlay { @apply fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4; }
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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">🖼️ Media Library</h1>
                    <p class="text-gray-600 dark:text-gray-400">Upload and manage images, files, and media</p>
                </div>
                <label class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload File
                    <form method="POST" enctype="multipart/form-data" id="uploadForm" class="hidden">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="upload">
                        <input type="file" name="file" accept="image/*,.pdf,.doc,.docx" onchange="document.getElementById('uploadForm').submit(); this.parentElement.classList.add('hidden')">
                    </form>
                </label>
            </div>
            
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 mb-6">
                <form method="GET" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" 
                               placeholder="Search files..." 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                    </div>
                    <select name="type" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700">
                        <option value="">All Types</option>
                        <option value="image" <?= $filters['mime'] === 'image' ? 'selected' : '' ?>>Images</option>
                        <option value="application/pdf" <?= $filters['mime'] === 'application/pdf' ? 'selected' : '' ?>>PDF</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Filter</button>
                    <a href="?" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">Clear</a>
                </form>
            </div>
            
            <!-- Media Grid -->
            <?php if (empty($result['data'])): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
                    <div class="text-6xl mb-4">📁</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No media found</h3>
                    <p class="text-gray-500">Upload your first file to get started.</p>
                </div>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($result['data'] as $item): ?>
                        <div class="media-item" onclick='viewMedia(<?= json_encode($item) ?>)'>
                            <?php if (strpos($item['mime_type'], 'image') !== false): ?>
                                <img src="<?= htmlspecialchars($item['thumbnail'] ?? $item['url']) ?>" 
                                     alt="<?= htmlspecialchars($item['alt_text'] ?? '') ?>" 
                                     class="w-full aspect-square object-cover">
                            <?php else: ?>
                                <div class="w-full aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <span class="text-4xl">📄</span>
                                </div>
                            <?php endif; ?>
                            <div class="p-2">
                                <div class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">
                                    <?= htmlspecialchars($item['original_name']) ?>
                                </div>
                            </div>
                            <div class="media-overlay">
                                <button class="p-2 bg-white rounded-lg text-blue-600 hover:bg-blue-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($result['total_pages'] > 1): ?>
                    <div class="flex justify-center gap-2 mt-8">
                        <?php foreach (paginate($result['total'], $result['total_pages'], $page) as $item): ?>
                            <?php if ($item['type'] === 'ellipsis'): ?>
                                <span class="px-3 py-2 text-gray-400">...</span>
                            <?php elseif ($item['type'] === 'page'): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                                   class="px-4 py-2 rounded-lg <?= $item['current'] ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800' ?>">
                                    <?= $item['page'] ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Media Modal -->
    <div id="mediaModal" class="modal-overlay hidden">
        <div class="bg-white dark:bg-gray-900 rounded-xl max-w-xl w-full shadow-2xl">
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="text-xl font-bold">Media Details</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">×</button>
            </div>
            <div class="p-6">
                <div id="previewImage" class="bg-gray-100 dark:bg-gray-800 rounded-lg aspect-video flex items-center justify-center mb-4">
                    <span class="text-6xl">📄</span>
                </div>
                <div class="space-y-3">
                    <input type="text" id="filename" readonly class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm">
                    <input type="text" id="caption" placeholder="Caption" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    <input type="text" id="altText" placeholder="Alt text (SEO)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    <input type="text" id="mediaUrl" readonly class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm">
                    <div class="flex gap-2 pt-2">
                        <button onclick="copyUrl()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">Copy URL</button>
                        <button onclick="saveMedia()" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg">Save</button>
                    </div>
                    <form method="POST" id="deleteForm" class="pt-4 border-t">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" onclick="return confirm('Delete?')" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentMedia = null;
        
        function viewMedia(media) {
            currentMedia = media;
            document.getElementById('mediaModal').classList.remove('hidden');
            document.getElementById('filename').value = media.original_name;
            document.getElementById('caption').value = media.caption || '';
            document.getElementById('altText').value = media.alt_text || '';
            document.getElementById('mediaUrl').value = media.url;
            document.getElementById('deleteId').value = media.id;
            
            const preview = document.getElementById('previewImage');
            if (media.mime_type && media.mime_type.startsWith('image')) {
                preview.innerHTML = `<img src="${media.url}" class="max-w-full max-h-48 rounded object-contain">`;
            }
        }
        
        function closeModal() {
            document.getElementById('mediaModal').classList.add('hidden');
        }
        
        function copyUrl() {
            navigator.clipboard.writeText(document.getElementById('mediaUrl').value);
            new Notyf().success('URL copied!');
        }
        
        function saveMedia() {
            const formData = new FormData();
            formData.append('csrf_token', '<?= $csrfToken ?>');
            formData.append('action', 'update');
            formData.append('id', currentMedia.id);
            formData.append('caption', document.getElementById('caption').value);
            formData.append('alt_text', document.getElementById('altText').value);
            
            fetch('/api/media.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    new Notyf().success('Saved!');
                    setTimeout(() => location.reload(), 1000);
                });
        }
        
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
