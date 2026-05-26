<?php
/**
 * tax-calculator.php v13 — Nepal Income Tax Calculator
 * FY-AWARE: Auto-detects current Nepali fiscal year from Bikram Sambat date.
 * Tax slabs are stored in data/tax-slabs.json (admin-editable, no code change needed).
 * Falls back to built-in slabs if cache file missing.
 *
 * Phase 4 fix: Replaced hardcoded "Demo FY 2081/82" with real FY-aware logic.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// ── Determine current Nepali fiscal year (Shrawan 1 = new FY) ──────────────
// Nepal FY starts Shrawan (month 4 in BS). BS year = AD year + 56/57.
function currentNepFY(): string {
    $adYear  = (int)date('Y');
    $adMonth = (int)date('n');
    // Approx: BS month 4 (Shrawan) starts mid-July
    $bsYear  = $adMonth >= 7 ? $adYear + 57 : $adYear + 56;
    $bsPrev  = $bsYear - 1;
    return "{$bsPrev}/{$bsYear}"; // e.g. "2081/2082"
}

// ── Load tax slabs from JSON cache (admin can update without code deploy) ──
function loadTaxSlabs(string $fy, string $type): array {
    $file = __DIR__ . '/data/tax-slabs.json';
    if (file_exists($file)) {
        $json = json_decode(file_get_contents($file), true);
        $key  = $fy . '_' . $type;
        if (!empty($json[$key])) return $json[$key];
        // fallback to any available FY in file
        foreach ($json as $k => $v) {
            if (str_ends_with($k, '_' . $type)) return $v;
        }
    }
    // ── Built-in fallback: FY 2081/82 (IRO Nepal confirmed slabs) ──────────
    return $type === 'couple'
        ? [[600000,0.01],[200000,0.10],[300000,0.20],[900000,0.30],[2000000,0.36],[PHP_INT_MAX,0.39]]
        : [[500000,0.01],[200000,0.10],[300000,0.20],[900000,0.30],[2000000,0.36],[PHP_INT_MAX,0.39]];
}

// ── Inputs ─────────────────────────────────────────────────────────────────
$income = isset($_GET['amt'])  ? max(0, (float)$_GET['amt'])  : 0;
$type   = in_array($_GET['type'] ?? '', ['single','couple']) ? $_GET['type'] : 'single';
$fy     = currentNepFY();
$slabs  = loadTaxSlabs($fy, $type);

// ── Calculate ──────────────────────────────────────────────────────────────
$rem  = $income;
$tax  = 0.0;
$rows = [];
foreach ($slabs as $s) {
    $take = min($rem, $s[0]);
    if ($take <= 0) break;
    $t     = $take * $s[1];
    $rows[] = [$take, $s[1], $t];
    $tax   += $t;
    $rem   -= $take;
    if ($rem <= 0) break;
}
$net        = $income - $tax;
$effectiveR = $income > 0 ? $tax / $income * 100 : 0;

// ── Allowed deductions (FY 2081/82) ───────────────────────────────────────
$deductionsInfo = [
    ['CIT अंशदान (कर्मचारी)',   'रू १०% सम्म',     'CIT contribution'],
    ['जीवन बीमा',               'रू ४०,०००',        'Life insurance premium'],
    ['स्वास्थ्य बीमा',          'रू २०,०००',        'Health insurance'],
    ['गृह ऋण ब्याज',            'रू ३,००,०००',      'Home loan interest'],
    ['SSF अंशदान',              'तलबको ३१%',        'SSF contribution'],
    ['अपाङ्गता भत्ता',          'रू ५०,०००',        'Disability allowance'],
];

$pageTitle = 'आयकर क्याल्कुलेटर | आकाशवाणी';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-3">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-3">
      <span class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center flex-shrink-0">
        <i data-lucide="receipt" class="w-5 h-5"></i>
      </span>
      <div>
        <h1 class="text-[18px] font-bold text-slate-900 ne">आयकर क्याल्कुलेटर</h1>
        <p class="text-[11px] text-slate-500">
          आ.व. <?= htmlspecialchars($fy) ?> &nbsp;·&nbsp;
          <a href="https://ird.gov.np/" target="_blank" rel="noopener" class="text-teal-600 underline">IRD Nepal</a>
        </p>
      </div>
      <span class="ml-auto text-[10px] bg-teal-100 text-teal-700 font-semibold px-2 py-0.5 rounded-full">
        FY <?= htmlspecialchars($fy) ?>
      </span>
    </div>

    <!-- Form -->
    <form method="get" class="bg-white rounded-2xl p-4 shadow-app space-y-3 mb-4">
      <div>
        <label class="text-[11px] font-semibold text-slate-600"><?= $tH('वार्षिक कर योग्य आय (रू)','Annual Taxable Income (NPR)') ?></label>
        <input type="number" name="amt" value="<?= htmlspecialchars((string)$income) ?>"
          min="0" step="1000"
          class="w-full mt-1 px-3 py-2.5 rounded-xl border border-slate-200 text-[15px] font-semibold focus:border-teal-500 focus:outline-none"
          placeholder="उदा. 800000"/>
      </div>
      <div>
        <label class="text-[11px] font-semibold text-slate-600"><?= $tH('करदाताको प्रकार','Taxpayer Type') ?></label>
        <div class="grid grid-cols-2 gap-2 mt-1">
          <?php foreach(['single' => ['एकल','Single'], 'couple' => ['दम्पती','Couple']] as $v => [$ne,$en]): ?>
          <label class="flex items-center justify-center gap-1.5 py-2 rounded-xl border-2
            <?= $type===$v ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-slate-200 text-slate-600' ?>
            text-[13px] font-semibold cursor-pointer">
            <input type="radio" name="type" value="<?= $v ?>" <?= $type===$v?'checked':'' ?> class="hidden"/>
            <?= $tH($ne,$en) ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl shadow-app">
        <?= $tH('गणना गर्नुहोस्','Calculate') ?>
      </button>
    </form>
  </section>

  <?php if ($income > 0): ?>
  <!-- Result summary -->
  <section class="px-4 mb-4">
    <div class="rounded-2xl p-4 text-white shadow-app bg-gradient-to-br from-teal-600 to-emerald-700">
      <div class="text-[11px] opacity-80 mb-0.5"><?= $tH('कुल कर','Total Tax') ?></div>
      <div class="text-[30px] font-extrabold leading-none">रू <?= number_format($tax, 2) ?></div>
      <div class="grid grid-cols-3 gap-2 mt-3 text-[12px]">
        <div class="bg-white/15 rounded-xl p-2">
          <div class="opacity-80 text-[10px]"><?= $tH('खुद आय','Net Income') ?></div>
          <div class="font-bold text-[13px]">रू <?= number_format($net, 0) ?></div>
        </div>
        <div class="bg-white/15 rounded-xl p-2">
          <div class="opacity-80 text-[10px]"><?= $tH('प्रभावी दर','Effective') ?></div>
          <div class="font-bold text-[13px]"><?= number_format($effectiveR, 2) ?>%</div>
        </div>
        <div class="bg-white/15 rounded-xl p-2">
          <div class="opacity-80 text-[10px]"><?= $tH('मासिक कर','Monthly Tax') ?></div>
          <div class="font-bold text-[13px]">रू <?= number_format($tax / 12, 0) ?></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Slab breakdown -->
  <section class="px-4 mb-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('स्ल्याब विवरण','Slab Breakdown') ?></h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php foreach ($rows as $i => $r): ?>
      <div class="flex justify-between items-center p-3 text-[12px]">
        <div>
          <div class="font-semibold text-slate-900">रू <?= number_format($r[0]) ?></div>
          <div class="text-[10px] text-slate-500"><?= ($r[1]*100) ?>% दर</div>
        </div>
        <div class="font-bold text-teal-700">रू <?= number_format($r[2], 2) ?></div>
      </div>
      <?php endforeach; ?>
      <div class="flex justify-between items-center p-3 text-[12px] bg-slate-50">
        <div class="font-bold text-slate-900"><?= $tH('जम्मा','Total') ?></div>
        <div class="font-extrabold text-teal-700 text-[14px]">रू <?= number_format($tax, 2) ?></div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Tax slab reference -->
  <section class="px-4 mb-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2">
      <?= $tH("आ.व. {$fy} कर स्ल्याब (सन्दर्भ)", "FY {$fy} Tax Slabs (Reference)") ?>
    </h2>
    <div class="bg-white rounded-2xl shadow-app overflow-hidden">
      <div class="grid grid-cols-2 divide-x divide-slate-100">
        <?php foreach(['single'=>['एकल','Single'],'couple'=>['दम्पती','Couple']] as $t2=>[$ne2,$en2]): ?>
        <div>
          <div class="text-[11px] font-bold text-slate-600 px-3 py-2 bg-slate-50 border-b border-slate-100"><?= $tH($ne2,$en2) ?></div>
          <?php
          $slabs2 = loadTaxSlabs($fy, $t2);
          $cum = 0;
          foreach ($slabs2 as $s2):
            if ($s2[0] === PHP_INT_MAX) { $range = "रू ".number_format($cum)." माथि"; }
            else { $range = "रू ".number_format($cum+1)."–".number_format($cum+$s2[0]); }
            $cum += $s2[0];
          ?>
          <div class="flex justify-between px-3 py-1.5 border-b border-slate-50 text-[11px]">
            <span class="text-slate-600"><?= $range ?></span>
            <span class="font-bold text-slate-900"><?= ($s2[1]*100) ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <p class="text-[10px] text-slate-400 mt-1.5">
      * यो अनुमान मात्र हो। आधिकारिक जानकारीका लागि
      <a href="https://ird.gov.np/" target="_blank" rel="noopener" class="text-teal-600 underline">ird.gov.np</a> हेर्नुहोस्।
    </p>
  </section>

  <!-- Deductions info -->
  <section class="px-4 mb-6">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('मुख्य कर छुट सुविधाहरू','Key Deductions Available') ?></h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php foreach ($deductionsInfo as [$ne,$limit,$en]): ?>
      <div class="flex items-center justify-between px-3 py-2.5 text-[12px]">
        <div class="ne text-slate-800"><?= $ne ?></div>
        <div class="font-semibold text-teal-700 text-right text-[11px]"><?= $limit ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-[10px] text-slate-400 mt-1.5">
      * कट्टीका लागि IRD आधिकारिक निर्देशन हेर्नुहोस्।
      <a href="https://ird.gov.np/" target="_blank" rel="noopener" class="text-teal-600 underline">ird.gov.np ↗</a>
    </p>
  </section>

</main>
<?php require_once __DIR__ . '/footer.php'; ?>
