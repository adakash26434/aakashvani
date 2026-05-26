<?php
require_once __DIR__ . '/functions.php';
if (!isLoggedIn()) { header('Location: /login.php'); exit; }
$user = getCurrentUser();
if (!$user) { header('Location: /login.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    try {
        $stmt = db()->prepare('SELECT password_hash FROM users WHERE id=? LIMIT 1');
        $stmt->execute([(int)$user['id']]);
        $hash = (string)$stmt->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            $error = 'हालको पासवर्ड मिलेन।';
        } elseif (strlen($new) < 6) {
            $error = 'नयाँ पासवर्ड कम्तीमा ६ अक्षरको हुनुपर्छ।';
        } elseif ($new !== $confirm) {
            $error = 'नयाँ पासवर्ड र पुष्टि मिलेन।';
        } else {
            db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), (int)$user['id']]);
            flash('पासवर्ड परिवर्तन भयो।');
            header('Location: /profile.php');
            exit;
        }
    } catch (Throwable $e) {
        $error = 'पासवर्ड परिवर्तन गर्न सकिएन।';
    }
}
$pageTitle = 'पासवर्ड परिवर्तन';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-4 pb-6">
    <form method="post" class="bg-white rounded-2xl shadow-app p-5 space-y-4">
      <div>
        <h1 class="text-[20px] font-extrabold text-slate-900">पासवर्ड परिवर्तन</h1>
        <p class="text-[12px] text-slate-500">Keep your account secure</p>
      </div>
      <?php if($error): ?><div class="rounded-xl bg-rose-50 text-rose-700 text-sm p-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <input type="password" name="current_password" required placeholder="हालको पासवर्ड" class="w-full bg-slate-50 rounded-xl px-3 py-3 text-sm outline-none">
      <input type="password" name="new_password" required placeholder="नयाँ पासवर्ड" class="w-full bg-slate-50 rounded-xl px-3 py-3 text-sm outline-none">
      <input type="password" name="confirm_password" required placeholder="नयाँ पासवर्ड पुष्टि" class="w-full bg-slate-50 rounded-xl px-3 py-3 text-sm outline-none">
      <div class="flex gap-2">
        <a href="/profile.php" class="flex-1 text-center rounded-xl bg-slate-100 text-slate-700 font-bold py-3">रद्द</a>
        <button class="flex-1 rounded-xl bg-teal-600 text-white font-bold py-3">Update</button>
      </div>
    </form>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
