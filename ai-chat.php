<?php
/** ai-chat.php v13 — AI chat interface */
require_once __DIR__ . '/header.php';
$q = trim((string)($_GET['q'] ?? ''));
$messages = $messages ?? [];
if($q && !$messages){
  $messages = [
    ['role'=>'user','text'=>$q],
    ['role'=>'ai','text'=>'नमस्कार! तपाईंको प्रश्न: "'.$q.'"। म तपाईंलाई सहयोग गर्न तयार छु। कृपया तल थप विवरण पठाउनुहोस्।'],
  ];
}
?>
<main class="app-main pb-2">
  <section class="px-4 pt-3">
    <div class="flex items-center gap-2 bg-gradient-to-br from-violet-600 to-purple-700 rounded-2xl p-3 text-white shadow-app">
      <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center"><i data-lucide="bot" class="w-5 h-5"></i></div>
      <div class="flex-1">
        <div class="text-[13px] font-bold">Nepali AI</div>
        <div class="text-[10px] opacity-80 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span><?= $tH('अनलाइन','Online') ?></div>
      </div>
    </div>
  </section>

  <section class="px-4 mt-3 space-y-2" id="chatLog">
    <?php if(!$messages): ?>
      <div class="text-center py-10">
        <i data-lucide="message-circle" class="w-12 h-12 text-slate-300 mx-auto mb-2"></i>
        <p class="text-[13px] text-slate-500"><?= $tH('कुनै पनि प्रश्न नेपालीमा सोध्नुहोस्','Ask anything in Nepali') ?></p>
      </div>
    <?php else: foreach($messages as $m): $isU=$m['role']==='user'; ?>
      <div class="flex <?= $isU?'justify-end':'justify-start' ?>">
        <div class="max-w-[80%] rounded-2xl px-3.5 py-2.5 text-[13.5px] leading-relaxed shadow-app
          <?= $isU?'bg-violet-600 text-white rounded-br-sm':'bg-white text-slate-800 rounded-bl-sm' ?>">
          <?= nl2br(htmlspecialchars($m['text'])) ?>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </section>
</main>

<!-- Input bar floats above bottom tabs -->
<div class="fixed bottom-[88px] left-1/2 -translate-x-1/2 w-full max-w-[460px] px-3 z-30">
  <form action="/ai-chat.php" method="get" class="flex items-center gap-2 bg-white rounded-full p-1.5 shadow-lg border border-slate-100">
    <button type="button" class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center shrink-0"><i data-lucide="plus" class="w-4 h-4"></i></button>
    <input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="<?= $tH('सोध्नुहोस्…','Message…') ?>" class="flex-1 bg-transparent text-[14px] px-1 focus:outline-none"/>
    <button class="w-9 h-9 rounded-full bg-violet-600 text-white flex items-center justify-center shrink-0"><i data-lucide="send" class="w-4 h-4"></i></button>
  </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
