<?php
/**
 * downloads.php v2 — सरकारी फारम डाउनलोड
 * Proxy through api/download-proxy.php + official source fallback
 */
$pageTitle = 'डाउनलोडहरू | आकाशवाणी';
$pageDesc  = 'सरकारी फारम, SEE Routine, Scholarship, Font र अन्य उपयोगी फाइलहरू डाउनलोड गर्नुस्।';
require_once __DIR__ . '/header.php';

$cats = [
  'सरकारी फारम' => [
    ['नागरिकता आवेदन',     'PDF', 'MOHA',         '/api/download-proxy.php?id=citizenship',   'https://moha.gov.np/page/citizenship-forms',        'file-text',  'bg-brand-50 text-brand-700'],
    ['राहदानी / Passport',  'PDF', 'DoP',          '/api/download-proxy.php?id=passport',      'https://www.passport.gov.np/page/downloadForms',    'book-open',  'bg-sky-50 text-sky-700'],
    ['PAN दर्ता',           'PDF', 'IRD',          '/api/download-proxy.php?id=pan',           'https://ird.gov.np/page/pan-registration',          'receipt',    'bg-amber-50 text-amber-700'],
    ['VAT दर्ता',           'PDF', 'IRD',          '/api/download-proxy.php?id=vat',           'https://ird.gov.np/page/vat-registration',          'receipt',    'bg-orange-50 text-orange-700'],
    ['ड्राइभिङ लाइसेन्स',  'PDF', 'DOTM',         '/api/download-proxy.php?id=drivinglicense', 'https://www.dotm.gov.np/en/forms',                 'car',        'bg-emerald-50 text-emerald-700'],
    ['सवारी दर्ता',         'PDF', 'DOTM',         '/api/download-proxy.php?id=vehicle',       'https://www.dotm.gov.np/en/forms',                 'car-front',  'bg-green-50 text-green-700'],
    ['आयकर विवरण (ITR)',    'PDF', 'IRD',          '/api/download-proxy.php?id=itr',           'https://ird.gov.np',                               'landmark',   'bg-red-50 text-red-700'],
  ],
  'शिक्षा' => [
    ['SEE Routine 2081',    'PDF', 'DOE',          '/api/download-proxy.php?id=see',           'https://www.doe.gov.np',                           'graduation-cap','bg-purple-50 text-purple-700'],
    ['Scholarship Form',    'PDF', 'MoEST',        '/api/download-proxy.php?id=scholarship',   'https://moest.gov.np/page/scholarship',            'award',      'bg-indigo-50 text-indigo-700'],
  ],
  'क्यालेन्डर' => [
    ['नेपाली पात्रो 2082',  'PDF', 'nepalipatro.com','/api/download-proxy.php?id=patro2082',  'https://nepalipatro.com.np',                       'calendar',   'bg-rose-50 text-rose-700'],
  ],
  'फन्ट / उपयोगी' => [
    ['Preeti Nepali Font',  'TTF', 'fonts.org.np', '/api/download-proxy.php?id=preeti',       'https://fonts.org.np/nepali-fonts/',               'type',       'bg-teal-50 text-teal-700'],
    ['Mangal Unicode Font', 'TTF', 'fonts.org.np', '/api/download-proxy.php?id=mangal',       'https://fonts.org.np/nepali-fonts/',               'type',       'bg-cyan-50 text-cyan-700'],
  ],
];
?>
<main class="app-main">

<!-- header -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-2">
    <span class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center">
      <i data-lucide="download" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 ne">डाउनलोडहरू</h1>
      <p class="text-[11px] text-slate-500">सरकारी फारम · फन्ट · क्यालेन्डर</p>
    </div>
  </div>
</section>

<!-- notice bar -->
<section class="px-4 mb-3">
  <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2">
    <i data-lucide="info" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
    <p class="text-[11px] text-amber-800 leading-relaxed ne">
      फारमहरू आधिकारिक सरकारी सर्भरबाट fetch हुन्छन्। Download नभएमा
      <strong>स्रोत</strong> बटन थिचेर आधिकारिक साइटबाट डाउनलोड गर्नुस्।
    </p>
  </div>
</section>

<?php foreach($cats as $title => $items): ?>
<section class="px-4 mt-3">
  <h2 class="text-[12px] font-bold text-slate-500 uppercase tracking-wide mb-2 ne flex items-center gap-2">
    <span><?= htmlspecialchars($title) ?></span>
    <span class="flex-1 h-px bg-slate-200"></span>
    <span class="text-[10px] text-slate-400 normal-case"><?= count($items) ?></span>
  </h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
    <?php foreach($items as [$name,$ext,$src,$dlUrl,$srcUrl,$icon,$iconCls]): ?>
    <div class="bg-white rounded-2xl border border-slate-100 p-2.5 flex flex-col items-center text-center hover:border-brand-300 transition-colors">
      <div class="w-10 h-10 rounded-xl <?= $iconCls ?> flex items-center justify-center mb-1.5 border border-current border-opacity-20">
        <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i>
      </div>
      <div class="text-[11.5px] font-bold text-slate-900 ne leading-tight line-clamp-2 min-h-[28px]" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></div>
      <div class="text-[9px] text-slate-400 mt-0.5 truncate w-full"><?= $ext ?> · <?= htmlspecialchars($src) ?></div>
      <div class="flex items-center gap-1 mt-2 w-full">
        <a href="<?= htmlspecialchars($dlUrl) ?>" download
           class="flex-1 h-7 rounded-lg bg-brand-600 text-white flex items-center justify-center gap-1 text-[10px] font-bold hover:bg-brand-700 active:scale-95">
          <i data-lucide="download" class="w-3 h-3"></i><span class="ne">डाउनलोड</span>
        </a>
        <a href="<?= htmlspecialchars($srcUrl) ?>" target="_blank" rel="noopener"
           title="आधिकारिक स्रोत"
           class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200">
          <i data-lucide="external-link" class="w-3 h-3"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>


<!-- Loksewa CTA -->
<section class="px-4 mt-5">
  <a href="/loksewa.php"
     class="flex items-center gap-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-2xl p-4">
    <span class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
      <i data-lucide="briefcase" class="w-5 h-5"></i>
    </span>
    <div>
      <div class="text-[14px] font-bold ne">लोकसेवा / सरकारी जागिर</div>
      <div class="text-[11px] text-white/80">PSC सूचना · विज्ञापन · नतिजा · पाठ्यक्रम</div>
    </div>
    <i data-lucide="chevron-right" class="w-5 h-5 ml-auto opacity-70"></i>
  </a>
</section>

<div class="pb-6"></div>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
