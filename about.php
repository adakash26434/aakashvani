<?php
/** about.php v13 — About */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-5 pt-5 text-center">
    <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-app mb-3">
      <i data-lucide="newspaper" class="w-10 h-10 text-white"></i>
    </div>
    <h1 class="text-[22px] font-extrabold text-slate-900"><?= defined('SITE_NAME')?SITE_NAME:'आकाशवाणी' ?></h1>
    <p class="text-[12px] text-slate-500"><?= $tH('Version 13.0 • App Redesign','Version 13.0 • App Redesign') ?></p>
  </section>

  <section class="px-5 mt-5 pb-4 space-y-3">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <h2 class="text-[14px] font-bold text-slate-900 mb-2"><?= $tH('हाम्रो उद्देश्य','Our Mission') ?></h2>
      <p class="text-[13px] text-slate-600 leading-relaxed">
        <?= $tH(
          'नेपालीहरूका लागि सबै आवश्यक सूचना — समाचार, पात्रो, बजार, सरकारी सेवा र AI सहायता — एकै ठाउँमा, सरल मोबाइल अनुभवमा उपलब्ध गराउनु।',
          'Bring all essential information for Nepalis — news, patro, market, government services, and AI assistance — into one simple mobile-first app experience.'
        ) ?>
      </p>
    </div>

    <div class="grid grid-cols-3 gap-2">
      <?php foreach([
        ['users','5K+','प्रयोगकर्ता'],
        ['newspaper','10K+','समाचार'],
        ['star','4.8','रेटिङ'],
      ] as $s): ?>
        <div class="bg-white rounded-2xl p-3 shadow-app text-center">
          <i data-lucide="<?= $s[0] ?>" class="w-4 h-4 mx-auto text-teal-600 mb-1"></i>
          <div class="text-[18px] font-extrabold text-slate-900"><?= $s[1] ?></div>
          <div class="text-[10px] text-slate-500"><?= $s[2] ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php foreach([
        ['shield','गोपनीयता नीति','/privacy.php'],
        ['file-text','नियम र शर्त','/terms.php'],
        ['mail','सम्पर्क','/contact.php'],
        ['help-circle','सहायता','/help.php'],
        ['github','Open Source','#'],
      ] as $r): ?>
        <a href="<?= $r[2] ?>" class="flex items-center gap-3 p-3">
          <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center"><i data-lucide="<?= $r[0] ?>" class="w-4 h-4"></i></div>
          <div class="flex-1 text-[13px] font-semibold text-slate-900"><?= htmlspecialchars($r[1]) ?></div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="text-center text-[11px] text-slate-400 pt-2">
      © <?= date('Y') ?> आकाशवाणी<br/>
      <?= $tH('नेपालमा बनेको','Made in Nepal') ?> 🇳🇵
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
