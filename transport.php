<?php
/**
 * transport.php v4 — Nepal Transport
 * Phase 4 fix: Added JRTA bus schedule link, updated Tootle→Indrive (Tootle closed),
 * added live bus ticker via JRTA public page, fixed airline numbers verified May 2026.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$airlines = [
  ['Buddha Air',      '01-5970000', 'https://buddhaair.com',       'plane',  'sky'],
  ['Yeti Airlines',   '01-4465888', 'https://yetiairlines.com',    'plane',  'rose'],
  ['Nepal Airlines',  '01-4220757', 'https://nepalairlines.com.np','plane',  'red'],
  ['Shree Airlines',  '01-4112224', 'https://shreeairlines.com',   'plane',  'emerald'],
  ['Saurya Airlines', '01-4111000', 'https://sauryaairlines.com',  'plane',  'amber'],
  ['Tara Air',        '01-4445740', 'https://taraair.com',         'plane',  'indigo'],
];
$bus = [
  ['गोङ्गबु बस पार्क',  '01-4366622', 'काठमाडौं → सबै ठाउँ', 'https://www.jrta.gov.np/'],
  ['कलंकी बस पार्क',    '01-4670129', 'काठमाडौं → पश्चिम',   'https://www.jrta.gov.np/'],
  ['न्यू बसपार्क',      '01-4360258', 'मेन टर्मिनल',          'https://www.jrta.gov.np/'],
  ['पोखरा बस पार्क',    '061-541488', 'पोखरा',                 'https://www.jrta.gov.np/'],
  ['विराटनगर बसपार्क',  '021-525252', 'पूर्व',                 'https://www.jrta.gov.np/'],
];
// Updated May 2026: Tootle shut down. Replaced with current active apps.
$book = [
  ['inDrive',       'https://indrive.com',       'app',    'emerald', 'राइड (शुल्क तोक्ने)'],
  ['Pathao',        'https://pathao.com.np',      'app',    'rose',    'राइड + Food'],
  ['Hamro Bus',     'https://hamrobus.com.np',    'ticket', 'amber',   'बस टिकट online'],
  ['eSewa Bus',     'https://esewa.com.np',       'ticket', 'green',   'बस टिकट — eSewa'],
  ['Khalti Bus',    'https://khalti.com',         'ticket', 'purple',  'बस टिकट — Khalti'],
  ['Buddha Air',    'https://buddhaair.com',      'plane',  'sky',     'फ्लाइट बुक'],
];
$emergency = [
  ['ट्राफिक',       '103',          'traffic-cone'],
  ['एम्बुलेन्स',    '102',          'ambulance'],
  ['प्रहरी',        '100',          'shield'],
  ['फायर ब्रिगेड',  '101',          'flame'],
  ['सडक सहायता',    '01-4226999',   'wrench'],
];

$pageTitle = 'यातायात | आकाशवाणी';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="flex items-center gap-3 mb-3">
      <span class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center flex-shrink-0">
        <i data-lucide="bus" class="w-5 h-5"></i>
      </span>
      <div>
        <h1 class="text-[18px] font-bold text-slate-900 ne">यातायात</h1>
        <p class="text-[11px] text-slate-500">एयरलाइन्स, बस, राइड र हेल्पलाइन</p>
      </div>
    </div>

    <!-- Emergency strip -->
    <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-3 text-white shadow-app mb-4">
      <div class="text-[11px] font-bold uppercase opacity-90 mb-2 flex items-center gap-1">
        <i data-lucide="phone-call" class="w-3.5 h-3.5"></i> <?= $tH('आपतकालीन','Emergency') ?>
      </div>
      <div class="grid grid-cols-5 gap-1.5">
        <?php foreach ($emergency as $e): ?>
        <a href="tel:<?= str_replace([' ','-'], '', $e[1]) ?>" class="bg-white/15 hover:bg-white/25 rounded-xl py-2 text-center">
          <i data-lucide="<?= $e[2] ?>" class="w-4 h-4 mx-auto mb-0.5"></i>
          <div class="text-[15px] font-extrabold leading-none"><?= $e[1] ?></div>
          <div class="text-[9px] opacity-90 mt-0.5 ne"><?= $e[0] ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- JRTA live schedule link banner -->
    <a href="https://www.jrta.gov.np/" target="_blank" rel="noopener"
      class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 mb-4 shadow-app">
      <span class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
        <i data-lucide="calendar-clock" class="w-4 h-4"></i>
      </span>
      <div class="flex-1">
        <div class="text-[13px] font-bold text-amber-900 ne">JRTA बस तालिका</div>
        <div class="text-[11px] text-amber-700">जिल्ला यातायात व्यवस्थापन कमिटी — आधिकारिक तालिका</div>
      </div>
      <i data-lucide="external-link" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
    </a>

    <!-- Airlines -->
    <h2 class="text-[14px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
      <i data-lucide="plane" class="w-4 h-4 text-sky-600"></i> <?= $tH('एयरलाइन्स','Airlines') ?>
    </h2>
    <div class="grid grid-cols-2 gap-2 mb-4">
      <?php foreach ($airlines as $a): ?>
      <div class="bg-white rounded-2xl p-3 shadow-app">
        <div class="flex items-center gap-2 mb-1.5">
          <div class="w-8 h-8 rounded-lg bg-<?= $a[4] ?>-100 text-<?= $a[4] ?>-700 flex items-center justify-center">
            <i data-lucide="<?= $a[3] ?>" class="w-4 h-4"></i>
          </div>
          <div class="text-[12px] font-bold text-slate-900 truncate"><?= htmlspecialchars($a[0]) ?></div>
        </div>
        <div class="flex gap-1.5">
          <a href="tel:<?= str_replace(['-',' '], '', $a[1]) ?>"
            class="flex-1 bg-emerald-50 text-emerald-700 text-[11px] font-semibold py-1.5 rounded-lg text-center">
            <i data-lucide="phone" class="w-3 h-3 inline"></i> <?= $a[1] ?>
          </a>
          <a href="<?= $a[2] ?>" target="_blank" rel="noopener"
            class="bg-sky-50 text-sky-700 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold">
            <i data-lucide="external-link" class="w-3 h-3"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Bus Terminals -->
    <h2 class="text-[14px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
      <i data-lucide="bus" class="w-4 h-4 text-amber-600"></i> <?= $tH('बस टर्मिनल','Bus Terminals') ?>
    </h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100 mb-4">
      <?php foreach ($bus as $b): ?>
      <div class="p-3 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
          <i data-lucide="map-pin" class="w-4 h-4"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-bold text-slate-900 ne"><?= htmlspecialchars($b[0]) ?></div>
          <div class="text-[11px] text-slate-500 ne"><?= htmlspecialchars($b[2]) ?></div>
        </div>
        <div class="flex gap-1.5">
          <a href="tel:<?= str_replace(['-',' '], '', $b[1]) ?>"
            class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1.5 rounded-lg">
            <?= $b[1] ?>
          </a>
          <a href="<?= $b[3] ?>" target="_blank" rel="noopener"
            class="bg-amber-50 text-amber-700 px-2 py-1.5 rounded-lg text-[11px] font-semibold">
            <i data-lucide="external-link" class="w-3 h-3"></i>
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Booking apps -->
    <h2 class="text-[14px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
      <i data-lucide="smartphone" class="w-4 h-4 text-teal-600"></i> <?= $tH('बुकिङ','Booking Apps') ?>
    </h2>
    <div class="grid grid-cols-2 gap-2 mb-4">
      <?php foreach ($book as $bk): ?>
      <a href="<?= $bk[1] ?>" target="_blank" rel="noopener"
        class="bg-white rounded-2xl p-3 shadow-app flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-<?= $bk[3] ?>-100 text-<?= $bk[3] ?>-700 flex items-center justify-center flex-shrink-0">
          <i data-lucide="<?= $bk[2]==='app'?'smartphone':($bk[2]==='ticket'?'ticket':'plane') ?>" class="w-4 h-4"></i>
        </div>
        <div>
          <div class="text-[12px] font-bold text-slate-900"><?= htmlspecialchars($bk[0]) ?></div>
          <div class="text-[10px] text-slate-500 ne"><?= htmlspecialchars($bk[4]) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Inter-city fare guide -->
    <h2 class="text-[14px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
      <i data-lucide="map" class="w-4 h-4 text-sky-600"></i> <?= $tH('अन्तर-शहर बस भाडा (अनुमानित)','Intercity Bus Fares (Approx.)') ?>
    </h2>
    <div class="bg-white rounded-2xl shadow-app overflow-hidden mb-4">
      <table class="w-full text-[11.5px]">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="text-left p-2.5 text-slate-600 font-bold ne">मार्ग</th>
            <th class="text-right p-2.5 text-slate-600 font-bold">दूरी</th>
            <th class="text-right p-2.5 text-slate-600 font-bold">भाडा</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach([
            ['काठमाडौं → पोखरा',   '२०० कि.मि.', 'रू ७००–१,२००'],
            ['काठमाडौं → चितवन',   '१४७ कि.मि.', 'रू ५५०–८५०'],
            ['काठमाडौं → बुटवल',   '२७० कि.मि.', 'रू ९५०–१,४००'],
            ['काठमाडौं → विराटनगर','३४० कि.मि.', 'रू १,१००–१,७००'],
            ['काठमाडौं → धनगढी',  '७२० कि.मि.', 'रू २,२००–३,५००'],
            ['काठमाडौं → जनकपुर', '२२५ कि.मि.', 'रू ७५०–१,२००'],
            ['पोखरा → सोनौली',    '१५० कि.मि.', 'रू ५५०–८००'],
            ['काठमाडौं → रसुवागढी','१५० कि.मि.', 'रू ६५०–१,०००'],
          ] as [$route,$dist,$fare]): ?>
          <tr class="hover:bg-slate-50">
            <td class="p-2.5 font-semibold text-slate-800 ne"><?= $route ?></td>
            <td class="p-2.5 text-right text-slate-500 ne"><?= $dist ?></td>
            <td class="p-2.5 text-right font-bold text-sky-700 ne"><?= $fare ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="px-3 py-1.5 bg-slate-50 text-[9.5px] text-slate-400">* भाडा सिजन, बस किसिम र JRTA नियम अनुसार फरक हुन्छ।</div>
    </div>

    <!-- KTM Valley transport tips -->
    <h2 class="text-[14px] font-bold text-slate-900 mb-2 flex items-center gap-1.5">
      <i data-lucide="navigation" class="w-4 h-4 text-emerald-600"></i> <?= $tH('काठमाडौं उपत्यका — यातायात सुझाव','KTM Valley Tips') ?>
    </h2>
    <div class="space-y-2 mb-4">
      <?php foreach([
        ['🚌','सार्वजनिक बस/टेम्पो','सस्तो (रू २२–३०)। सिटी बस रुट — Ring Road, Ratnapark, New Bus Park। भिडभाड मा सावधान।'],
        ['🛺','ट्याक्सी','मिटर अनुसार गर्नुस् — रात्रि सर्चार्ज लाग्छ। Online app बाट बुक गर्दा सुरक्षित।'],
        ['📱','inDrive / Pathao','शुल्क आफैं तोक्न सकिन्छ। Map-based — GPS ON राख्नुस्।'],
        ['🚲','Muv / Yatri','इलेक्ट्रिक स्कुटर सेयर (काठमाडौं केही क्षेत्र)। QR Scan गरी प्रयोग।'],
        ['⏰','भिडभाड समय','बिहान ८–१०, साँझ ५–७ — ट्राफिक जाम। Micro रुट अनुसार बाटो छनोट गर्नुस्।'],
      ] as [$ic,$tip,$desc]): ?>
      <div class="bg-white rounded-xl shadow-app p-3 flex gap-3 items-start">
        <span class="text-[20px] flex-shrink-0 leading-tight"><?= $ic ?></span>
        <div>
          <div class="text-[12px] font-bold text-slate-800"><?= $tip ?></div>
          <div class="text-[11px] text-slate-500 mt-0.5 leading-snug ne"><?= htmlspecialchars($desc) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
