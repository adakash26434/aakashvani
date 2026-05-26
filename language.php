<?php
$pageTitle = 'भाषा सेटिङ';
if (isset($_GET['set'])) {
    $lang = $_GET['set'] === 'en' ? 'en' : 'ne';
    setcookie('site_lang', $lang, time() + 31536000, '/');
    header('Location: /profile.php');
    exit;
}
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-4 pb-6">
    <div class="bg-white rounded-2xl shadow-app p-5 space-y-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center"><i data-lucide="languages" class="w-5 h-5"></i></div>
        <div>
          <h1 class="text-[20px] font-extrabold text-slate-900">भाषा सेटिङ</h1>
          <p class="text-[12px] text-slate-500">Choose your preferred language</p>
        </div>
      </div>
      <div class="grid grid-cols-1 gap-3">
        <a href="/language.php?set=ne" class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between">
          <span class="font-bold text-slate-900">नेपाली</span><i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>
        <a href="/language.php?set=en" class="rounded-2xl border border-slate-200 p-4 flex items-center justify-between">
          <span class="font-bold text-slate-900">English</span><i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
