<?php
/**
 * Admin User Management
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$message = '';
$messageType = '';

// Get DB
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
                'username' => sanitize($_POST['username'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'display_name' => sanitize($_POST['display_name'] ?? ''),
                'role' => sanitize($_POST['role'] ?? 'reporter'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];
            
            if (!empty($_POST['password'])) {
                $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            if ($action === 'create') {
                $cols = implode(', ', array_keys($data));
                $placeholders = ':' . implode(', :', array_keys($data));
                $sql = "INSERT INTO aak_users ({$cols}) VALUES ({$placeholders})";
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $set = implode(' = ?, ', array_keys($data)) . ' = ?';
                $values = array_values($data);
                $values[] = $id;
                $sql = "UPDATE aak_users SET {$set} WHERE id = ?";
            }
            
            try {
                $pdo->prepare($sql)->execute(array_values($data));
                $message = $action === 'create' ? 'User created!' : 'User updated!';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id != $_SESSION['user_id']) {
                $pdo->prepare("DELETE FROM aak_users WHERE id = ?")->execute([$id]);
                $message = 'User deleted!';
                $messageType = 'success';
            }
            break;
    }
}

$users = $pdo->query("SELECT * FROM aak_users ORDER BY created_at DESC")->fetchAll();
$csrfToken = generateCSRF();

$roles = ['super_admin', 'admin', 'editor', 'reporter', 'content_manager'];
?>
<!DOCTYPE html>
<html lang="ne" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Admin</title>
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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">👥 User Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage users and roles</p>
                </div>
                <button onclick="openModal()" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg flex items-center gap-2">
                    Add User
                </button>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Role</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Last Login</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-medium">
                                            <?= strtoupper(substr($user['display_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($user['display_name']) ?></div>
                                            <div class="text-sm text-gray-500">@<?= htmlspecialchars($user['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= 
                                        $user['role'] === 'super_admin' ? 'bg-red-100 text-red-700' :
                                        ($user['role'] === 'admin' ? 'bg-orange-100 text-orange-700' :
                                        ($user['role'] === 'editor' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'))
                                    ?>">
                                        <?= ucwords(str_replace('_', ' ', $user['role'])) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?= $user['is_active'] ? 
                                        '<span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Active</span>' :
                                        '<span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">Inactive</span>'
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <?= $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never' ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button onclick='editUser(<?= json_encode($user) ?>)' class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <?php if ($user['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- User Modal -->
    <div id="userModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-gray-900 rounded-xl max-w-md w-full shadow-2xl">
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="text-xl font-bold" id="modalTitle">Add User</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">×</button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="userId">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Username</label>
                        <input type="text" name="username" id="username" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Display Name</label>
                        <input type="text" name="display_name" id="displayName" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="email" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Password <?= '<span class="text-red-500">(required for new user)</span>' ?></label>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Role</label>
                        <select name="role" id="role" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= ucwords(str_replace('_', ' ', $role)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="isActive" value="1" checked class="rounded">
                            <span class="text-sm">Active</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add User';
            document.getElementById('formAction').value = 'create';
            document.getElementById('userId').value = '';
            document.getElementById('username').value = '';
            document.getElementById('displayName').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('role').value = 'reporter';
            document.getElementById('isActive').checked = true;
        }
        
        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
        
        function editUser(user) {
            document.getElementById('userModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'update';
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('displayName').value = user.display_name;
            document.getElementById('email').value = user.email;
            document.getElementById('password').value = '';
            document.getElementById('role').value = user.role;
            document.getElementById('isActive').checked = user.is_active == 1;
        }
        
        <?php if ($message): ?>
        new Notyf().<?= $messageType === 'success' ? 'success' : 'error' ?>('<?= addslashes($message) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
