<?php
/** forgot.php v13 — Password reset */
require_once __DIR__ . '/header.php';
$sent = isset($_GET['sent']);
?>
<main class="app-main">
  <section class="px-5 pt-8 text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-app mb-3">
      <i data-lucide="key-round" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-[22px] font-extrabold text-slate-800"><?= $tH('पासवर्ड बिर्सनुभयो?','Forgot password?') ?></h1>
    <p class="text-[12px] text-slate-500 mt-1 px-4"><?= $tH('इमेलमा रिसेट लिङ्क पठाइनेछ','We\'ll email you a reset link') ?></p>
  </section>
  <section class="px-5 mt-5">
    <?php if($sent): ?>
      <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center">
        <i data-lucide="check-circle" class="w-10 h-10 text-emerald-600 mx-auto mb-2"></i>
        <div class="text-[14px] font-bold text-emerald-800"><?= $tH('लिङ्क पठाइयो!','Link sent!') ?></div>
        <div class="text-[12px] text-emerald-700 mt-1"><?= $tH('इमेल चेक गर्नुहोस्','Check your inbox') ?></div>
      </div>
    <?php else: ?>
      <form method="post" action="?sent=1" class="space-y-3">
        <?= csrfField() ?>
        <div class="flex items-center gap-2 bg-white rounded-xl px-3 py-2.5 shadow-app">
          <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
          <input type="email" name="email" required class="flex-1 bg-transparent text-[14px] focus:outline-none" placeholder="you@email.com"/>
        </div>
        <button class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl shadow-app"><?= $tH('रिसेट लिङ्क पठाउनुहोस्','Send Reset Link') ?></button>
      </form>
    <?php endif; ?>
    <p class="text-center text-[12px] text-slate-500 mt-5">
      <a href="/login.php" class="text-teal-700 font-bold inline-flex items-center gap-1"><i data-lucide="arrow-left" class="w-3 h-3"></i> <?= $tH('लग-इनमा फर्क','Back to login') ?></a>
    </p>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
