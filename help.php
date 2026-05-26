<?php
/** help.php v13 — FAQ + help center */
require_once __DIR__ . '/header.php';
$faqs = [
  ['खाता कसरी बनाउने?','Register page मा गएर नाम, इमेल र पासवर्ड हाल्नुहोस्। ३० सेकेन्डमा सकिन्छ।'],
  ['पासवर्ड बिर्सिएँ — के गर्ने?','Login page मा "Forgot?" क्लिक गर्नुहोस्। इमेलमा रिसेट लिङ्क आउँछ।'],
  ['Notification कसरी बन्द गर्ने?','Profile → अलर्ट सेटिङ → off गर्नुहोस्।'],
  ['भाषा कसरी बदल्ने?','तल "More" मा Nepali/EN switch बटन छ।'],
  ['Data कति safe छ?','सबै password encrypted, HTTPS मा मात्र data जान्छ। हामी कसैलाई बेच्दैनौं।'],
];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <h1 class="text-[20px] font-bold text-slate-900 mb-1"><?= $tH('सहायता केन्द्र','Help Center') ?></h1>
    <p class="text-[12px] text-slate-500 mb-3"><?= $tH('बारम्बार सोधिने प्रश्नहरू','Frequently asked questions') ?></p>

    <div class="flex items-center gap-2 bg-white rounded-full px-3 py-2 shadow-app mb-3">
      <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
      <input placeholder="<?= $tH('प्रश्न खोज्नुहोस्…','Search help…') ?>" class="flex-1 bg-transparent text-[13px] focus:outline-none"/>
    </div>

    <div class="space-y-2 pb-4">
      <?php foreach($faqs as $i=>$f): ?>
        <details class="bg-white rounded-2xl shadow-app group">
          <summary class="flex items-center justify-between gap-2 p-3.5 cursor-pointer list-none">
            <div class="flex items-center gap-2 flex-1">
              <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-[11px] font-bold shrink-0"><?= $i+1 ?></span>
              <span class="text-[13px] font-semibold text-slate-900"><?= htmlspecialchars($f[0]) ?></span>
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-open:rotate-180 transition"></i>
          </summary>
          <div class="px-4 pb-3.5 text-[12.5px] text-slate-600 leading-relaxed pl-12"><?= htmlspecialchars($f[1]) ?></div>
        </details>
      <?php endforeach; ?>
    </div>

    <a href="/contact.php" class="block bg-gradient-to-br from-teal-600 to-emerald-700 text-white rounded-2xl p-4 shadow-app text-center mb-4">
      <i data-lucide="message-circle-question" class="w-6 h-6 mx-auto mb-1"></i>
      <div class="text-[14px] font-bold"><?= $tH('अझै समस्या छ?','Still have questions?') ?></div>
      <div class="text-[11px] opacity-90"><?= $tH('हामीलाई सम्पर्क गर्नुहोस्','Contact our team') ?></div>
    </a>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
