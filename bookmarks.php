<?php
/** bookmarks.php v13 — Saved items */
require_once __DIR__ . '/header.php';
$items = $items ?? [
  ['type'=>'news','title'=>'नेप्से १०० अंकले बढ्यो','meta'=>'बजार • २ घण्टा अघि','icon'=>'newspaper','color'=>'emerald','url'=>'/news.php'],
  ['type'=>'guide','title'=>'नागरिकता आवेदन ५ Step','meta'=>'AI Guide','icon'=>'user-check','color'=>'sky','url'=>'/ai-guides.php'],
  ['type'=>'tool','title'=>'आयकर Calculator','meta'=>'टूल','icon'=>'calculator','color'=>'amber','url'=>'/tax-calculator.php'],
];
$tabs = [$tH('सबै','All'),'समाचार','गाइड','टूल'];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <h1 class="text-[20px] font-bold text-slate-900 mb-3"><?= $tH('बुकमार्कहरू','Bookmarks') ?></h1>
    <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide">
      <?php foreach($tabs as $i=>$t): ?>
        <button class="shrink-0 px-3.5 py-1.5 rounded-full text-[12px] font-semibold whitespace-nowrap <?= $i===0?'bg-slate-900 text-white':'bg-white text-slate-600 border border-slate-200' ?>"><?= htmlspecialchars($t) ?></button>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="px-4 mt-2 pb-4 space-y-2.5">
    <?php if(!$items): ?>
      <div class="bg-white rounded-2xl p-10 shadow-app text-center">
        <i data-lucide="bookmark-x" class="w-12 h-12 mx-auto text-slate-300 mb-2"></i>
        <div class="text-[14px] font-bold text-slate-700"><?= $tH('कुनै बुकमार्क छैन','No bookmarks yet') ?></div>
        <div class="text-[12px] text-slate-500 mt-1"><?= $tH('समाचार वा गाइड सेभ गर्नुहोस्','Save news or guides') ?></div>
      </div>
    <?php else: foreach($items as $b): ?>
      <a href="<?= $b['url'] ?>" class="bg-white rounded-2xl p-3 shadow-app flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-<?= $b['color'] ?>-100 text-<?= $b['color'] ?>-700 flex items-center justify-center shrink-0"><i data-lucide="<?= $b['icon'] ?>" class="w-5 h-5"></i></div>
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-bold text-slate-900 truncate"><?= htmlspecialchars($b['title']) ?></div>
          <div class="text-[10px] text-slate-500"><?= htmlspecialchars($b['meta']) ?></div>
        </div>
        <button class="w-8 h-8 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center"><i data-lucide="bookmark-minus" class="w-4 h-4"></i></button>
      </a>
    <?php endforeach; endif; ?>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
