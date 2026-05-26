<?php
/** login.php v12 — App-style auth screen */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-5 pt-6 pb-4 text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-app mb-3">
      <i data-lucide="user-round" class="w-8 h-8 text-white"></i>
    </div>
    <h1 class="text-[22px] font-extrabold text-slate-900"><?= $tH('स्वागत छ','Welcome back') ?></h1>
    <p class="text-[12px] text-slate-500 mt-1"><?= $tH('आफ्नो खातामा लग-इन गर्नुहोस्','Sign in to continue') ?></p>
  </section>

  <section class="px-5">
    <form method="post" action="/auth/login.php" class="space-y-3">
      <?= csrfField() ?>
      <div>
        <label class="text-[11px] font-semibold text-slate-600"><?= $tH('इमेल वा मोबाइल','Email or Phone') ?></label>
        <div class="mt-1 flex items-center gap-2 bg-white rounded-xl px-3 py-2.5 shadow-app">
          <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
          <input name="login" required class="flex-1 bg-transparent text-[14px] focus:outline-none" placeholder="you@email.com"/>
        </div>
      </div>
      <div>
        <label class="text-[11px] font-semibold text-slate-600"><?= $tH('पासवर्ड','Password') ?></label>
        <div class="mt-1 flex items-center gap-2 bg-white rounded-xl px-3 py-2.5 shadow-app">
          <i data-lucide="lock" class="w-4 h-4 text-slate-400"></i>
          <input type="password" name="password" required class="flex-1 bg-transparent text-[14px] focus:outline-none" placeholder="••••••••"/>
        </div>
      </div>
      <div class="flex justify-between items-center text-[12px]">
        <label class="flex items-center gap-1.5 text-slate-600"><input type="checkbox" name="remember" class="accent-teal-600"/> <?= $tH('याद राख्नु','Remember me') ?></label>
        <a href="/forgot.php" class="text-teal-700 font-semibold"><?= $tH('बिर्सनुभयो?','Forgot?') ?></a>
      </div>
      <button class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl shadow-app"><?= $tH('लग-इन','Sign In') ?></button>
    </form>

    <div class="flex items-center gap-3 my-4 text-[11px] text-slate-400">
      <div class="flex-1 h-px bg-slate-200"></div><?= $tH('वा','OR') ?><div class="flex-1 h-px bg-slate-200"></div>
    </div>
    <div class="grid grid-cols-2 gap-2">
      <a href="/auth/google.php" class="bg-white rounded-xl py-2.5 shadow-app flex items-center justify-center gap-2 text-[13px] font-semibold text-slate-700"><i data-lucide="chrome" class="w-4 h-4"></i> Google</a>
      <a href="/auth/facebook.php" class="bg-white rounded-xl py-2.5 shadow-app flex items-center justify-center gap-2 text-[13px] font-semibold text-slate-700"><i data-lucide="facebook" class="w-4 h-4"></i> Facebook</a>
    </div>

    <p class="text-center text-[12px] text-slate-500 mt-5 pb-4">
      <?= $tH('खाता छैन?','No account?') ?> <a href="/register.php" class="text-teal-700 font-bold"><?= $tH('दर्ता गर्नुहोस्','Sign up') ?></a>
    </p>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
