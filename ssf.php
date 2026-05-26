<?php
/**
 * ssf.php — सामाजिक सुरक्षा कोष (Social Security Fund)
 * SSF member info, contribution check, helplines, official links
 */
$pageTitle = 'सामाजिक सुरक्षा कोष | आकाशवाणी';
$pageDesc  = 'SSF सदस्यता, अंशदान स्थिति, बिदाइ कोष विवरण र आधिकारिक सम्पर्क — एकै ठाउँमा।';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/header.php';

$tabs = [
  ['check',   'shield-check',  'स्थिति जाँच',   'Status Check'],
  ['contrib', 'coins',         'अंशदान',         'Contribution'],
  ['withdraw','arrow-right-from-line','निकासी',  'Withdrawal'],
  ['contact', 'phone',         'सम्पर्क',         'Contact'],
];
?>
<main class="app-main">

<!-- ── Page header ── -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-3 mb-1">
    <span class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="shield" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight ne">सामाजिक सुरक्षा कोष</h1>
      <p class="text-[11px] text-slate-500">Social Security Fund Nepal — SSF</p>
    </div>
    <span class="ml-auto text-[10px] bg-teal-100 text-teal-700 font-semibold px-2 py-0.5 rounded-full">सरकारी</span>
  </div>
</section>

<!-- ── Tab bar ── -->
<nav class="px-4 mb-3">
  <div class="flex gap-1.5 bg-slate-100 rounded-xl p-1">
    <?php foreach($tabs as $i => [$key,$ic,$ne,$en]): ?>
    <button data-tab="<?= $key ?>"
      class="ssf-tab flex-1 text-[11px] font-semibold py-1.5 rounded-lg transition-colors <?= $i===0 ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-500' ?>">
      <i data-lucide="<?= $ic ?>" class="w-3.5 h-3.5 mx-auto mb-0.5"></i>
      <span class="block"><?= $tH($ne,$en) ?></span>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- ══ TAB: Status Check ══════════════════════════════════════════════════ -->
<div id="tab-check" class="ssf-panel px-4">

  <div class="bg-white rounded-2xl shadow-app p-4 mb-3">
    <h2 class="text-[13px] font-bold text-slate-800 mb-3 flex items-center gap-1.5">
      <i data-lucide="search" class="w-4 h-4 text-teal-600"></i>
      <?= $tH('SSF सदस्यता जाँच','SSF Member Lookup') ?>
    </h2>
    <div class="space-y-3">
      <div>
        <label class="text-[11px] font-semibold text-slate-600 block mb-1"><?= $tH('SSF सदस्य नम्बर वा मोबाइल','SSF Member No. or Mobile') ?></label>
        <input type="text" id="ssf-input" placeholder="जस्तै: 1234567890"
          class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-[14px] focus:border-teal-500 focus:outline-none"/>
      </div>
      <button id="ssf-check-btn" class="w-full bg-teal-600 text-white font-bold py-2.5 rounded-xl shadow-app text-[14px]">
        <?= $tH('जाँच गर्नुहोस्','Check Status') ?>
      </button>
    </div>
    <div id="ssf-result" class="hidden mt-3"></div>
  </div>

  <!-- Direct portal links -->
  <div class="bg-amber-50 border border-amber-100 rounded-2xl p-3 mb-3">
    <div class="text-[11px] font-bold text-amber-800 mb-2 flex items-center gap-1">
      <i data-lucide="info" class="w-3.5 h-3.5"></i>
      <?= $tH('आधिकारिक पोर्टलहरू','Official Portals') ?>
    </div>
    <div class="space-y-2">
      <?php foreach([
        ['SSF TIMS पोर्टल',   'https://ssftims.ssf.org.np/',      'सदस्यता, अंशदान, बिदाइ स्थिति'],
        ['SSF आधिकारिक साइट', 'https://www.ssf.org.np/',          'नियम, ऐन, परिपत्र'],
        ['Nagarik App',       'https://nagarikapp.gov.np/',        'SSF सेवा Nagarik App मार्फत'],
      ] as [$name,$url,$desc]): ?>
      <a href="<?= $url ?>" target="_blank" rel="noopener"
        class="flex items-center justify-between bg-white rounded-xl px-3 py-2.5 shadow-app">
        <div>
          <div class="text-[13px] font-semibold text-slate-800"><?= $name ?></div>
          <div class="text-[10px] text-slate-500"><?= $desc ?></div>
        </div>
        <i data-lucide="external-link" class="w-4 h-4 text-teal-600 flex-shrink-0"></i>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ══ TAB: Contribution ════════════════════════════════════════════════ -->
<div id="tab-contrib" class="ssf-panel hidden px-4">
  <div class="bg-white rounded-2xl shadow-app p-4 mb-3">
    <h2 class="text-[13px] font-bold text-slate-800 mb-3"><?= $tH('अंशदान दर (२०८१/८२)','Contribution Rates FY 2081/82') ?></h2>
    <div class="space-y-2">
      <?php foreach([
        ['कर्मचारी अंशदान',    'Employee',    '11%',  'मासिक तलब बाट'],
        ['रोजगारदाता अंशदान',  'Employer',    '20%',  'रोजगारदाताले थप्ने'],
        ['जम्मा अंशदान',       'Total',       '31%',  'SSF खातामा जम्मा'],
        ['औद्योगिक दुर्घटना',  'Accident',    '1.4%', 'दुर्घटना बिमाका लागि'],
      ] as [$ne,$en,$rate,$note]): ?>
      <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
        <div>
          <div class="text-[13px] font-semibold text-slate-800 ne"><?= $ne ?></div>
          <div class="text-[10px] text-slate-500"><?= $note ?></div>
        </div>
        <span class="text-[18px] font-extrabold text-teal-700"><?= $rate ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-[10px] text-slate-400 mt-3">* दरहरू परिवर्तन हुन सक्छन् — आधिकारिक जानकारीका लागि <a href="https://www.ssf.org.np/" target="_blank" class="text-teal-600 underline">ssf.org.np</a> हेर्नुहोस्।</p>
  </div>

  <div class="bg-white rounded-2xl shadow-app p-4 mb-3">
    <h2 class="text-[13px] font-bold text-slate-800 mb-3"><?= $tH('योजनाहरू','SSF Schemes') ?></h2>
    <div class="space-y-2">
      <?php foreach([
        ['shield','indigo', 'वृद्धभत्ता',       'Old Age Pension',    'उमेर ६० पुगेपछि'],
        ['heart', 'rose',   'अस्पताल खर्च',     'Medical Benefit',    'बिरामी हुँदा ७०% खर्च'],
        ['baby',  'pink',   'प्रसूति सुविधा',   'Maternity',          '९८ दिन पारिश्रमिक'],
        ['home',  'amber',  'आवास कर्जा',        'Housing Loan',       'घर बनाउन सहुलियत'],
        ['briefcase','teal','बेरोजगारी',         'Unemployment',       'जागिर गुमाएपछि'],
      ] as [$ic,$cl,$ne,$en,$note]): ?>
      <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
        <span class="w-8 h-8 rounded-xl bg-<?= $cl ?>-100 text-<?= $cl ?>-700 flex items-center justify-center flex-shrink-0">
          <i data-lucide="<?= $ic ?>" class="w-4 h-4"></i>
        </span>
        <div class="flex-1">
          <div class="text-[13px] font-semibold text-slate-800 ne"><?= $ne ?></div>
          <div class="text-[10px] text-slate-500"><?= $note ?></div>
        </div>
        <a href="https://www.ssf.org.np/" target="_blank" rel="noopener"
          class="text-[10px] text-teal-600 font-semibold">विवरण ↗</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══ TAB: Withdrawal ══════════════════════════════════════════════════ -->
<div id="tab-withdraw" class="ssf-panel hidden px-4">
  <div class="bg-white rounded-2xl shadow-app p-4 mb-3">
    <h2 class="text-[13px] font-bold text-slate-800 mb-3"><?= $tH('निकासी प्रक्रिया','Withdrawal Process') ?></h2>
    <div class="space-y-3">
      <?php foreach([
        ['1','file-text',  'TIMS पोर्टलमा आवेदन',  'ssftims.ssf.org.np मा लगिन गर्नुहोस् र Withdrawal Application भर्नुहोस्।'],
        ['2','upload',     'कागजात अपलोड',          'नागरिकता, बैंक पासबुक, रोजगार समाप्ति पत्र अपलोड गर्नुहोस्।'],
        ['3','clock',      'प्रक्रिया समय',          'आवेदन परेको ३०–४५ दिन भित्र रकम बैंक खातामा जम्मा हुन्छ।'],
        ['4','landmark',   'SSF कार्यालय',           'समस्या भएमा नजिकको SSF कार्यालयमा सम्पर्क गर्नुहोस्।'],
      ] as [$num,$ic,$ne,$desc]): ?>
      <div class="flex gap-3">
        <span class="w-7 h-7 rounded-full bg-teal-100 text-teal-700 font-bold text-[12px] flex items-center justify-center flex-shrink-0"><?= $num ?></span>
        <div>
          <div class="text-[13px] font-semibold text-slate-800 ne flex items-center gap-1.5">
            <i data-lucide="<?= $ic ?>" class="w-3.5 h-3.5 text-teal-600"></i><?= $ne ?>
          </div>
          <p class="text-[11px] text-slate-500 mt-0.5"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-3 mb-3">
    <div class="text-[11px] font-bold text-emerald-800 mb-2"><?= $tH('आवश्यक कागजात','Required Documents') ?></div>
    <ul class="space-y-1.5">
      <?php foreach([
        'नागरिकता प्रमाणपत्रको प्रतिलिपि',
        'बैंक खाता पासबुकको प्रतिलिपि',
        'रोजगार समाप्ति पत्र (Resignation/Termination)',
        'SSF सदस्यता कार्ड वा नम्बर',
        'पासपोर्ट साइज फोटो (२ प्रति)',
      ] as $doc): ?>
      <li class="flex items-center gap-2 text-[12px] text-emerald-900">
        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0"></i><?= $doc ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- ══ TAB: Contact ══════════════════════════════════════════════════════ -->
<div id="tab-contact" class="ssf-panel hidden px-4">
  <div class="bg-white rounded-2xl shadow-app p-4 mb-3">
    <h2 class="text-[13px] font-bold text-slate-800 mb-3"><?= $tH('SSF सम्पर्क','SSF Contact') ?></h2>
    <div class="space-y-2">
      <?php foreach([
        ['phone',       'हेल्पलाइन',        '1660-01-22200',       'tel:16600122200'],
        ['phone',       'काठमाडौं कार्यालय', '01-5970611',          'tel:015970611'],
        ['mail',        'इमेल',             'info@ssf.org.np',      'mailto:info@ssf.org.np'],
        ['globe',       'वेबसाइट',          'www.ssf.org.np',       'https://www.ssf.org.np/'],
        ['map-pin',     'ठेगाना',            'बबरमहल, काठमाडौं',   null],
      ] as [$ic,$label,$val,$href]): ?>
      <div class="flex items-center gap-3 p-2.5 <?= $href ? 'bg-slate-50 rounded-xl cursor-pointer hover:bg-teal-50' : 'bg-slate-50 rounded-xl' ?>">
        <span class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center flex-shrink-0">
          <i data-lucide="<?= $ic ?>" class="w-4 h-4"></i>
        </span>
        <div class="flex-1">
          <div class="text-[10px] text-slate-500"><?= $label ?></div>
          <?php if($href): ?>
          <a href="<?= $href ?>" class="text-[13px] font-semibold text-teal-700"><?= $val ?></a>
          <?php else: ?>
          <div class="text-[13px] font-semibold text-slate-800 ne"><?= $val ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-app p-4 mb-6">
    <h2 class="text-[13px] font-bold text-slate-800 mb-2"><?= $tH('प्रदेश कार्यालयहरू','Provincial Offices') ?></h2>
    <div class="grid grid-cols-2 gap-2">
      <?php foreach([
        ['काठमाडौं', '01-5970611'],
        ['पोखरा',    '061-570990'],
        ['विराटनगर', '021-524090'],
        ['बुटवल',    '071-540440'],
        ['बिरगञ्ज',  '051-533770'],
        ['धनगढी',    '091-526610'],
        ['हेटौंडा',  '057-524880'],
        ['सुर्खेत',  '083-520077'],
      ] as [$city,$phone]): ?>
      <a href="tel:<?= preg_replace('/[^0-9]/','',$phone) ?>"
        class="flex items-center gap-2 bg-slate-50 rounded-xl p-2.5 text-[12px]">
        <i data-lucide="phone" class="w-3.5 h-3.5 text-teal-600 flex-shrink-0"></i>
        <div>
          <div class="font-semibold text-slate-800 ne"><?= $city ?></div>
          <div class="text-slate-500"><?= $phone ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

</main>

<script>
(function(){
  // Tab switching
  document.querySelectorAll('.ssf-tab').forEach(btn => {
    btn.addEventListener('click', function(){
      document.querySelectorAll('.ssf-tab').forEach(b => {
        b.classList.remove('bg-white','text-teal-700','shadow-sm');
        b.classList.add('text-slate-500');
      });
      this.classList.add('bg-white','text-teal-700','shadow-sm');
      this.classList.remove('text-slate-500');
      document.querySelectorAll('.ssf-panel').forEach(p => p.classList.add('hidden'));
      document.getElementById('tab-' + this.dataset.tab).classList.remove('hidden');
    });
  });

  // SSF check button — tries TIMS API, graceful fallback
  document.getElementById('ssf-check-btn').addEventListener('click', async function(){
    const val = document.getElementById('ssf-input').value.trim();
    const res = document.getElementById('ssf-result');
    if (!val) { res.classList.remove('hidden'); res.innerHTML = '<div class="text-rose-600 text-[12px]">सदस्य नम्बर वा मोबाइल नम्बर राख्नुहोस्।</div>'; return; }
    res.classList.remove('hidden');
    res.innerHTML = '<div class="text-slate-400 text-[12px] py-2">जाँच हुँदै...</div>';
    try {
      const r = await fetch('/api/gov-check.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'ssf', member_id: val })
      });
      const d = await r.json();
      if (d && d.success) {
        const rows = Object.entries(d)
          .filter(([k]) => !['success','source'].includes(k) && d[k])
          .map(([k,v]) => `<div class="flex justify-between py-0.5 border-b border-slate-50 text-[11px]"><span class="text-slate-500">${k.replace(/_/g,' ')}</span><b class="text-slate-900">${v}</b></div>`)
          .join('');
        res.innerHTML = `<div class="bg-emerald-50 border border-emerald-200 rounded-xl p-2.5"><div class="font-bold text-emerald-800 mb-1 text-[12px]">✓ फेला पर्‍यो — ${d.source||'SSF'}</div>${rows}</div>`;
      } else {
        res.innerHTML = `<div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
          <div class="font-bold text-amber-800 mb-1 text-[12px]">⚠ स्वचालित जाँच हुन सकेन</div>
          <p class="text-[11px] text-slate-700 mb-2">SSF TIMS पोर्टलमा सिधै हेर्नुहोस्:</p>
          <div class="flex gap-2">
            <button onclick="navigator.clipboard.writeText('${val.replace(/'/g,"\\'")}');this.textContent='✓ Copied!'" class="bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-[11px] font-semibold">📋 Copy No.</button>
            <a href="https://ssftims.ssf.org.np/" target="_blank" rel="noopener" class="bg-teal-600 text-white rounded-lg px-3 py-1.5 text-[11px] font-semibold">SSF TIMS पोर्टल ↗</a>
          </div>
        </div>`;
      }
    } catch(e) {
      res.innerHTML = `<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-[11px]">
        <div class="font-bold text-amber-800 mb-2">⚠ जडान भएन — Official पोर्टल प्रयोग गर्नुहोस्</div>
        <a href="https://ssftims.ssf.org.np/" target="_blank" rel="noopener" class="bg-teal-600 text-white rounded-lg px-3 py-2 text-[12px] font-bold inline-block">SSF TIMS ↗</a>
      </div>`;
    }
  });
  // Enter key support
  document.getElementById('ssf-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') document.getElementById('ssf-check-btn').click();
  });
})();
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
