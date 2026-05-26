<?php
require_once __DIR__ . '/functions.php';
if (!isLoggedIn()) { header('Location: /login.php'); exit; }
$user = getCurrentUser();
if (!$user) { header('Location: /login.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '') {
        $error = 'नाम आवश्यक छ।';
    } else {
        try {
            db()->prepare('UPDATE users SET name=?, phone=? WHERE id=?')->execute([$name, $phone ?: null, (int)$user['id']]);
            flash('प्रोफाइल अपडेट भयो।');
            header('Location: /profile.php');
            exit;
        } catch (Throwable $e) {
            $error = 'प्रोफाइल अपडेट गर्न सकिएन।';
        }
    }
}
$pageTitle = 'प्रोफाइल सम्पादन';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-4 pb-6">
    <form method="post" class="bg-white rounded-2xl shadow-app p-5 space-y-4">
      <div>
        <h1 class="text-[20px] font-extrabold text-slate-900">प्रोफाइल सम्पादन</h1>
        <p class="text-[12px] text-slate-500">Update your account details</p>
      </div>
      <?php if($error): ?><div class="rounded-xl bg-rose-50 text-rose-700 text-sm p-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <div>
        <label class="text-[11px] font-semibold text-slate-600">नाम</label>
        <input name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $user['name'] ?? '') ?>" class="mt-1 w-full bg-slate-50 rounded-xl px-3 py-3 text-sm outline-none">
      </div>
      <div>
        <label class="text-[11px] font-semibold text-slate-600">इमेल</label>
        <input value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled class="mt-1 w-full bg-slate-100 rounded-xl px-3 py-3 text-sm text-slate-500 outline-none">
      </div>
      <div>
        <label class="text-[11px] font-semibold text-slate-600">मोबाइल</label>
        <input name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone'] ?? '') ?>" class="mt-1 w-full bg-slate-50 rounded-xl px-3 py-3 text-sm outline-none">
      </div>
      <div class="flex gap-2">
        <a href="/profile.php" class="flex-1 text-center rounded-xl bg-slate-100 text-slate-700 font-bold py-3">रद्द</a>
        <button class="flex-1 rounded-xl bg-teal-600 text-white font-bold py-3">Save</button>
      </div>
    </form>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
