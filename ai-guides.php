<?php
/** ai-guides.php v12 — AI chat / how-to guides */
require_once __DIR__ . '/header.php';
$suggestions = [
  ['कर कति लाग्छ?','calculator'],
  ['IPO कसरी apply गर्ने?','rocket'],
  ['नागरिकता बनाउने तरिका','user-check'],
  ['NEPSE आज कस्तो छ?','trending-up'],
  ['राहदानी process','book-open'],
  ['EMI calculator','wallet'],
];
$guides = [
  ['नागरिकता आवेदन ५ Step','5 steps','user-check','sky'],
  ['Online PAN दर्ता','3 steps','file-text','emerald'],
  ['Driving License नवीकरण','4 steps','car','amber'],
  ['Passport MRP application','6 steps','book-open','indigo'],
];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="rounded-2xl p-5 text-white shadow-app bg-gradient-to-br from-violet-600 via-fuchsia-600 to-purple-700 relative overflow-hidden">
      <i data-lucide="sparkles" class="absolute -right-3 -top-3 w-24 h-24 opacity-15"></i>
      <div class="text-[11px] opacity-80">AI ASSISTANT</div>
      <div class="text-[22px] font-extrabold leading-tight"><?= $tH('नेपालीमा सोध्नुहोस्','Ask in Nepali') ?></div>
      <div class="text-[12px] opacity-90 mt-1"><?= $tH('जुनै पनि कुरामा तुरुन्तै जवाफ','Instant answers on anything') ?></div>
    </div>

    <!-- Input -->
    <form action="/ai-chat.php" method="get" class="mt-3 flex items-center gap-2 bg-white rounded-full p-1.5 shadow-app">
      <input name="q" placeholder="<?= $tH('सोध्नुहोस्…','Ask anything…') ?>" class="flex-1 bg-transparent px-3 py-2 text-[14px] focus:outline-none"/>
      <button class="w-10 h-10 rounded-full bg-violet-600 text-white flex items-center justify-center shrink-0"><i data-lucide="send" class="w-4 h-4"></i></button>
    </form>
  </section>

  <section class="px-4 mt-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('द्रुत प्रश्न','Quick Prompts') ?></h2>
    <div class="flex flex-wrap gap-2">
      <?php foreach($suggestions as $s): ?>
        <a href="/ai-chat.php?q=<?= urlencode($s[0]) ?>" class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-full text-[12px] font-semibold text-slate-700 shadow-app">
          <i data-lucide="<?= $s[1] ?>" class="w-3.5 h-3.5 text-violet-600"></i><?= htmlspecialchars($s[0]) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="px-4 mt-4 pb-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('Step-by-Step गाइड','Step-by-Step Guides') ?></h2>
    <div class="grid grid-cols-2 gap-2.5">
      <?php foreach($guides as $g): ?>
        <a href="#" class="bg-white rounded-2xl p-3 shadow-app">
          <div class="w-10 h-10 rounded-xl bg-<?= $g[3] ?>-100 text-<?= $g[3] ?>-700 flex items-center justify-center mb-2"><i data-lucide="<?= $g[2] ?>" class="w-5 h-5"></i></div>
          <div class="text-[12px] font-bold text-slate-900 leading-snug"><?= htmlspecialchars($g[0]) ?></div>
          <div class="text-[10px] text-slate-500 mt-0.5"><?= htmlspecialchars($g[1]) ?> • <?= $tH('पढ्नुहोस्','Read') ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
