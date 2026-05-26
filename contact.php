<?php
/** contact.php v13 */
require_once __DIR__ . '/header.php';
$sent = isset($_GET['sent']);
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <h1 class="text-[20px] font-bold text-slate-900 mb-1"><?= $tH('सम्पर्क','Contact Us') ?></h1>
    <p class="text-[12px] text-slate-500 mb-3"><?= $tH('हामीसँग कुनै पनि कुरामा सम्पर्क गर्नुहोस्','We\'d love to hear from you') ?></p>

    <div class="grid grid-cols-3 gap-2 mb-3">
      <?php foreach([
        ['phone','कल','+977-1','tel:+9771','sky'],
        ['mail','इमेल','Email','mailto:hello@aakashvani.com','emerald'],
        ['message-circle','WhatsApp','Chat','https://wa.me/9779800000000','green'],
      ] as $c): ?>
        <a href="<?= $c[3] ?>" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-9 h-9 mx-auto rounded-full bg-<?= $c[4] ?>-100 text-<?= $c[4] ?>-700 flex items-center justify-center mb-1"><i data-lucide="<?= $c[0] ?>" class="w-4 h-4"></i></div>
          <div class="text-[11px] font-bold text-slate-900"><?= $c[1] ?></div>
          <div class="text-[10px] text-slate-500"><?= $c[2] ?></div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if($sent): ?>
      <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center mb-3">
        <i data-lucide="send" class="w-8 h-8 text-emerald-600 mx-auto mb-1"></i>
        <div class="text-[14px] font-bold text-emerald-800"><?= $tH('सन्देश पठाइयो!','Message sent!') ?></div>
      </div>
    <?php endif; ?>

    <form method="post" action="?sent=1" class="space-y-3 pb-4">
      <?= csrfField() ?>
      <div class="bg-white rounded-xl px-3 py-2.5 shadow-app flex items-center gap-2">
        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
        <input name="name" required placeholder="<?= $tH('नाम','Name') ?>" class="flex-1 bg-transparent text-[14px] focus:outline-none"/>
      </div>
      <div class="bg-white rounded-xl px-3 py-2.5 shadow-app flex items-center gap-2">
        <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
        <input type="email" name="email" required placeholder="<?= $tH('इमेल','Email') ?>" class="flex-1 bg-transparent text-[14px] focus:outline-none"/>
      </div>
      <div class="bg-white rounded-xl px-3 py-2.5 shadow-app">
        <textarea name="msg" rows="4" required placeholder="<?= $tH('तपाईंको सन्देश','Your message') ?>" class="w-full bg-transparent text-[14px] focus:outline-none resize-none"></textarea>
      </div>
      <button class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl shadow-app"><?= $tH('पठाउनुहोस्','Send') ?></button>
    </form>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
