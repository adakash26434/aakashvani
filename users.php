<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
requireAdmin();

$db = db();
$msg = getFlash();
$page = (int)($_GET['page'] ?? 1);
$limit = 25;
$offset = ($page - 1) * $limit;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_user') {
        $id = (int)$_POST['id'];
        if (deleteUser($id)) {
            flash('User deleted successfully.');
        } else {
            flash('Failed to delete user.', 'error');
        }
        header('Location: /admin/users.php'); exit;
    }
    
    if ($action === 'toggle_user') {
        $id = (int)$_POST['id'];
        $stmt = db()->prepare('SELECT is_active FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            updateUserStatus($id, 1 - $user['is_active']);
            flash('User status updated.');
        }
        header('Location: /admin/users.php'); exit;
    }
}

// Get data
$users = getAllUsers($limit, $offset);
$total = getUserCount();
$pages = ceil($total / $limit);

?><!DOCTYPE html>
<html lang="en" class="bg-[#fafaf9]" data-theme="nshdark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>User Management — आकाशवाणी Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.js"></script>
</head>
<body class="bg-[#fafaf9] text-[#0f172a]">
<?php include 'header.php'; ?>

<div class="pt-20 pb-20 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-[#0f172a] mb-2">User Management</h1>
            <p class="text-[#64748b]">View and manage registered users and signups</p>
        </div>

        <!-- Message -->
        <?php if ($msg): ?>
            <div class="mb-6 p-4 rounded-lg bg-<?= $msg['type'] === 'success' ? '[#10b981]/15' : '[#ef4444]/15' ?> border border-<?= $msg['type'] === 'success' ? '[#10b981]/40' : '[#ef4444]/40' ?> text-<?= $msg['type'] === 'success' ? '[#10b981]' : '[#ef4444]' ?>">
                <?= h($msg['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-lg p-4">
                <div class="text-[#64748b] text-sm">Total Users</div>
                <div class="text-2xl font-bold text-[#7c3aed]"><?= $total ?></div>
            </div>
            <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-lg p-4">
                <div class="text-[#64748b] text-sm">Active Users</div>
                <div class="text-2xl font-bold text-[#10b981]"><?= (int)db()->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn() ?></div>
            </div>
            <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-lg p-4">
                <div class="text-[#64748b] text-sm">Inactive Users</div>
                <div class="text-2xl font-bold text-[#eab308]"><?= (int)db()->query('SELECT COUNT(*) FROM users WHERE is_active = 0')->fetchColumn() ?></div>
            </div>
            <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-lg p-4">
                <div class="text-[#64748b] text-sm">Verified Users</div>
                <div class="text-2xl font-bold text-[#0ea5e9]"><?= (int)db()->query('SELECT COUNT(*) FROM users WHERE is_verified = 1')->fetchColumn() ?></div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#fafaf9] border-b border-[#e2e8f0]">
                        <tr>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">ID</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Name</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Email</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Phone</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Subject</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Registered</th>
                            <th class="px-4 py-3 text-left text-[#64748b] font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e2e8f0]">
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-[#f5f5f4] transition-colors">
                            <td class="px-4 py-3 text-[#0f172a]">#<?= $user['id'] ?></td>
                            <td class="px-4 py-3 text-[#0f172a] font-medium"><?= h($user['name']) ?></td>
                            <td class="px-4 py-3 text-[#64748b]"><?= h($user['email']) ?></td>
                            <td class="px-4 py-3 text-[#64748b]"><?= h($user['phone'] ?? '—') ?></td>
                            <td class="px-4 py-3 text-[#64748b] max-w-xs truncate"><?= h($user['subject'] ?? '—') ?></td>
                            <td class="px-4 py-3">
                                <?php if ($user['is_active']): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-semibold bg-[#10b981]/15 text-[#10b981] border border-[#10b981]/40">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-semibold bg-[#eab308]/15 text-[#eab308] border border-[#eab308]/40">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-[#64748b] text-xs"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_user">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="px-2 py-1 rounded text-xs font-semibold bg-[#7c3aed]/15 text-[#7c3aed] hover:bg-[#7c3aed]/25 border border-[#7c3aed]/40 transition-colors">
                                            <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete this user?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="px-2 py-1 rounded text-xs font-semibold bg-[#ef4444]/15 text-[#ef4444] hover:bg-[#ef4444]/25 border border-[#ef4444]/40 transition-colors">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="mt-6 flex items-center justify-center gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=1" class="px-3 py-1 rounded text-sm bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#0f172a] hover:border-[#7c3aed]">First</a>
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 rounded text-sm bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#0f172a] hover:border-[#7c3aed]">Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-3 py-1 rounded text-sm bg-[#7c3aed] text-[#fafaf9] font-semibold"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="px-3 py-1 rounded text-sm bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#0f172a] hover:border-[#7c3aed]"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $pages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 rounded text-sm bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#0f172a] hover:border-[#7c3aed]">Next</a>
                <a href="?page=<?= $pages ?>" class="px-3 py-1 rounded text-sm bg-[#f5f5f4] border border-[#e2e8f0] text-[#64748b] hover:text-[#0f172a] hover:border-[#7c3aed]">Last</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
