<?php
/** onboarding.php v13 — First-time intro slides */
require_once __DIR__ . '/header.php';
$slides = [
  ['newspaper','सबै समाचार एकै ठाउँमा','AI द्वारा वर्गीकृत, नेपाली र अंग्रेजीमा','teal','emerald'],
  ['calendar-days','नेपाली पात्रो र पञ्चाङ्ग','तिथि, पर्व, र शुभ साइत','violet','purple'],
  ['trending-up','बजार र IPO ट्र्याकिङ','NEPSE लाइभ, सुन, इन्धन र विदेशी मुद्रा','amber','orange'],
  ['landmark','सरकारी सेवा डिजिटल','नागरिकता देखि कर सम्म सबै','sky','blue'],
];
?>
<main class="app-main">
  <section class="px-5 pt-6 pb-4">
    <div class="flex justify-end mb-3">
      <a href="/" class="text-[12px] font-semibold text-slate-500"><?= $tH('स्किप','Skip') ?> →</a>
    </div>

    <div class="space-y-5">
      <?php foreach($slides as $i=>$s): ?>
        <div class="bg-white rounded-3xl p-5 shadow-app flex items-center gap-4">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-<?= $s[3] ?>-500 to-<?= $s[4] ?>-600 flex items-center justify-center shrink-0">
            <i data-lucide="<?= $s[0] ?>" class="w-8 h-8 text-white"></i>
          </div>
          <div class="flex-1">
            <div class="text-[15px] font-extrabold text-slate-900"><?= $s[1] ?></div>
            <div class="text-[12px] text-slate-500 mt-0.5"><?= $s[2] ?></div>
          </div>
          <span class="text-[18px] font-extrabold text-slate-200"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <a href="/register.php" class="block mt-6 bg-teal-600 text-white text-center font-bold py-3.5 rounded-2xl shadow-app">
      <?= $tH('सुरु गरौँ','Get Started') ?> →
    </a>
    <a href="/login.php" class="block mt-2 text-center text-[13px] font-semibold text-slate-600 py-2">
      <?= $tH('पहिले नै खाता छ? लग-इन','Already have an account? Sign in') ?>
    </a>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
