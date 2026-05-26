<?php
/** profile.php v12 — User profile with settings */
require_once __DIR__ . '/header.php';
$user = $cu ?? ['name'=>'Guest User','email'=>'guest@aakashvani.com','phone'=>'','joined'=>'2026-05-01'];
$stats = [
  ['lucide'=>'newspaper','label'=>$tH('पढेका','Read'),'val'=>'124'],
  ['lucide'=>'bookmark','label'=>$tH('बुकमार्क','Saved'),'val'=>'18'],
  ['lucide'=>'bell','label'=>$tH('अलर्ट','Alerts'),'val'=>'5'],
];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="rounded-2xl p-5 text-white shadow-app bg-gradient-to-br from-teal-600 to-emerald-700 flex items-center gap-3">
      <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-[28px] font-extrabold"><?= htmlspecialchars(mb_substr($user['name'],0,1)) ?></div>
      <div class="flex-1 min-w-0">
        <div class="text-[16px] font-extrabold truncate"><?= htmlspecialchars($user['name']) ?></div>
        <div class="text-[11px] opacity-90 truncate"><?= htmlspecialchars($user['email']) ?></div>
        <?php if(!empty($user['phone'])): ?><div class="text-[11px] opacity-80"><?= htmlspecialchars($user['phone']) ?></div><?php endif; ?>
      </div>
      <a href="/profile-edit.php" class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
    </div>

    <div class="grid grid-cols-3 gap-2 mt-3">
      <?php foreach($stats as $s): ?>
        <div class="bg-white rounded-2xl p-2.5 shadow-app text-center">
          <i data-lucide="<?= $s['lucide'] ?>" class="w-4 h-4 mx-auto text-teal-600 mb-1"></i>
          <div class="text-[16px] font-extrabold text-slate-900"><?= $s['val'] ?></div>
          <div class="text-[10px] text-slate-500"><?= $s['label'] ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="px-4 mt-4 pb-4 space-y-2">
    <?php
    $sections = [
      $tH('खाता','Account') => [
        ['edit-2','प्रोफाइल सम्पादन','/profile-edit.php'],
        ['lock','पासवर्ड परिवर्तन','/password.php'],
        ['bell','अलर्ट सेटिङ','/alerts-settings.php'],
      ],
      $tH('प्राथमिकता','Preferences') => [
        ['languages','भाषा (ने/EN)','/language.php'],
        ['moon','डार्क मोड','#'],
        ['bookmark','बुकमार्कहरू','/bookmarks.php'],
      ],
      $tH('अन्य','More') => [
        ['info','बारेमा','/about.php'],
        ['shield','गोपनीयता नीति','/privacy.php'],
        ['help-circle','सहायता','/help.php'],
        ['log-out','लग-आउट','/auth/logout.php','rose'],
      ],
    ];
    foreach($sections as $title=>$rows): ?>
      <div>
        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wide px-1 mb-1.5"><?= $title ?></div>
        <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
          <?php foreach($rows as $r): $danger=($r[3]??'')==='rose'; ?>
            <a href="<?= $r[2] ?>" class="flex items-center gap-3 p-3">
              <div class="w-9 h-9 rounded-xl <?= $danger?'bg-rose-50 text-rose-600':'bg-slate-100 text-slate-600' ?> flex items-center justify-center"><i data-lucide="<?= $r[0] ?>" class="w-4 h-4"></i></div>
              <div class="flex-1 text-[13px] font-semibold <?= $danger?'text-rose-600':'text-slate-900' ?>"><?= htmlspecialchars($r[1]) ?></div>
              <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
