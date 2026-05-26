<?php
/** register.php v13 — Sign up */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-5 pt-6 pb-3 text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-app mb-3">
      <i data-lucide="user-plus" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-[22px] font-extrabold text-slate-900"><?= $tH('नयाँ खाता','Create account') ?></h1>
    <p class="text-[12px] text-slate-500 mt-1"><?= $tH('३० सेकेन्डमा दर्ता गर्नुहोस्','Sign up in 30 seconds') ?></p>
  </section>
  <section class="px-5">
    <form method="post" action="/auth/register.php" class="space-y-3">
      <?= csrfField() ?>
      <?php foreach([
        ['name','पूरा नाम','Full name','user','text'],
        ['email','इमेल','Email','mail','email'],
        ['phone','मोबाइल','Phone','phone','tel'],
        ['password','पासवर्ड','Password','lock','password'],
      ] as $f): ?>
        <div>
          <label class="text-[11px] font-semibold text-slate-600"><?= $tH($f[1],$f[2]) ?></label>
          <div class="mt-1 flex items-center gap-2 bg-white rounded-xl px-3 py-2.5 shadow-app">
            <i data-lucide="<?= $f[3] ?>" class="w-4 h-4 text-slate-400"></i>
            <input type="<?= $f[4] ?>" name="<?= $f[0] ?>" required class="flex-1 bg-transparent text-[14px] focus:outline-none"/>
          </div>
        </div>
      <?php endforeach; ?>
      <label class="flex items-start gap-2 text-[11px] text-slate-600">
        <input type="checkbox" required class="accent-teal-600 mt-0.5"/>
        <span><?= $tH('म नियम र शर्त स्वीकार गर्छु','I accept the Terms & Privacy') ?></span>
      </label>
      <button class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl shadow-app"><?= $tH('खाता खोल्नुहोस्','Create Account') ?></button>
    </form>
    <p class="text-center text-[12px] text-slate-500 mt-5 pb-4">
      <?= $tH('पहिले नै खाता छ?','Have an account?') ?> <a href="/login.php" class="text-teal-700 font-bold"><?= $tH('लग-इन','Sign in') ?></a>
    </p>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
