<?php
require_once __DIR__ . '/functions.php';
if (!isLoggedIn()) { header('Location: /login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['alert_settings'] = [
        'news' => !empty($_POST['news']),
        'market' => !empty($_POST['market']),
        'gov' => !empty($_POST['gov']),
    ];
    flash('अलर्ट सेटिङ सुरक्षित भयो।');
    header('Location: /profile.php');
    exit;
}
$settings = $_SESSION['alert_settings'] ?? ['news'=>true,'market'=>true,'gov'=>true];
$pageTitle = 'अलर्ट सेटिङ';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-4 pb-6">
    <form method="post" class="bg-white rounded-2xl shadow-app p-5 space-y-4">
      <div>
        <h1 class="text-[20px] font-extrabold text-slate-900">अलर्ट सेटिङ</h1>
        <p class="text-[12px] text-slate-500">Choose notifications you want to receive</p>
      </div>
      <?php foreach ([['news','समाचार अलर्ट'],['market','बजार/IPO अलर्ट'],['gov','सरकारी सेवा अलर्ट']] as $row): ?>
        <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-4">
          <span class="font-bold text-slate-900"><?= htmlspecialchars($row[1]) ?></span>
          <input type="checkbox" name="<?= $row[0] ?>" class="accent-teal-600" <?= !empty($settings[$row[0]]) ? 'checked' : '' ?>>
        </label>
      <?php endforeach; ?>
      <div class="flex gap-2">
        <a href="/profile.php" class="flex-1 text-center rounded-xl bg-slate-100 text-slate-700 font-bold py-3">रद्द</a>
        <button class="flex-1 rounded-xl bg-teal-600 text-white font-bold py-3">Save</button>
      </div>
    </form>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
