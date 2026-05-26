<?php
/** privacy.php v13 */
require_once __DIR__ . '/header.php';
$secs = [
  ['सूचना संकलन','Information We Collect','तपाईंले प्रदान गर्नुभएको नाम, इमेल, मोबाइल मात्र। हामी पासवर्ड हेर्दैनौं — encrypted form मा राख्छौं।'],
  ['प्रयोग','How We Use It','सेवा सुधार, अलर्ट पठाउन, र AI सिफारिसका लागि मात्र।'],
  ['तेस्रो पक्ष','Third Parties','हामी कसैलाई पनि तपाईंको डाटा बेच्दैनौं। Analytics अनामित (anonymous) मात्र।'],
  ['कुकीज','Cookies','भाषा र लग-इन याद राख्न प्रयोग गरिन्छ।'],
  ['तपाईंको अधिकार','Your Rights','कुनै पनि बेला खाता मेटाउन र डाटा download गर्न सक्नुहुन्छ।'],
];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-app"><i data-lucide="shield-check" class="w-6 h-6 text-white"></i></div>
      <div>
        <h1 class="text-[20px] font-bold text-slate-900"><?= $tH('गोपनीयता नीति','Privacy Policy') ?></h1>
        <p class="text-[11px] text-slate-500"><?= $tH('अद्यावधिक: १५ जेठ २०८३','Updated: May 2026') ?></p>
      </div>
    </div>

    <div class="space-y-2.5 pb-4">
      <?php foreach($secs as $i=>$s): ?>
        <div class="bg-white rounded-2xl p-4 shadow-app">
          <div class="flex items-center gap-2 mb-1.5">
            <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-[11px] font-bold"><?= $i+1 ?></span>
            <h2 class="text-[14px] font-bold text-slate-900"><?= $tH($s[0],$s[1]) ?></h2>
          </div>
          <p class="text-[12.5px] text-slate-600 leading-relaxed pl-8"><?= htmlspecialchars($s[2]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
