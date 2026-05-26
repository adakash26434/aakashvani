<?php
/**
 * vehicle.php — सवारी साधन दर्ता जाँच (Vehicle Registration Check)
 * Uses existing api/gov-check.php DoTM Nepal integration
 */
$pageTitle = 'सवारी दर्ता जाँच | आकाशवाणी';
$pageDesc  = 'नम्बर प्लेट बाट सवारीको दर्ता स्थिति, कर म्याद, बीमा र मालिक विवरण जाँच्नुहोस्।';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/header.php';
?>
<main class="app-main">

<!-- ── Page header ── -->
<section class="px-4 pt-4 pb-2">
  <div class="flex items-center gap-3 mb-1">
    <span class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center flex-shrink-0">
      <i data-lucide="car-front" class="w-5 h-5"></i>
    </span>
    <div>
      <h1 class="text-[18px] font-bold text-slate-900 leading-tight ne">सवारी दर्ता जाँच</h1>
      <p class="text-[11px] text-slate-500">DoTM Nepal — Vehicle Registration Lookup</p>
    </div>
    <span class="ml-auto text-[10px] bg-orange-100 text-orange-700 font-semibold px-2 py-0.5 rounded-full">सरकारी</span>
  </div>
</section>

<!-- ── Search form ── -->
<section class="px-4 mb-3">
  <div class="bg-white rounded-2xl shadow-app p-4">
    <label class="text-[11px] font-semibold text-slate-600 block mb-1">
      <?= $tH('नम्बर प्लेट (जस्तै: BA 1 PA 1234)','Number Plate (e.g. BA 1 PA 1234)') ?>
    </label>
    <div class="flex gap-2">
      <input type="text" id="veh-input"
        placeholder="BA 1 PA 1234"
        class="flex-1 px-3 py-2.5 rounded-xl border border-slate-200 text-[14px] font-semibold uppercase focus:border-orange-500 focus:outline-none tracking-widest"
        style="letter-spacing:0.05em"
        autocomplete="off" autocorrect="off" spellcheck="false"/>
      <button id="veh-check-btn"
        class="bg-orange-500 text-white font-bold px-4 rounded-xl shadow-app text-[13px] flex-shrink-0">
        <i data-lucide="search" class="w-4 h-4"></i>
      </button>
    </div>
    <p class="text-[10px] text-slate-400 mt-2">
      <?= $tH('नम्बर प्लेट अक्षर र अंक सही राख्नुहोस् — जस्तै BA 1 PA 1234, KO 1 CHA 0001','Enter full plate number exactly — e.g. BA 1 PA 1234') ?>
    </p>
    <div id="veh-result" class="hidden mt-3"></div>
  </div>
</section>

<!-- ── Plate format guide ── -->
<section class="px-4 mb-3">
  <h2 class="text-[12px] font-bold text-slate-700 mb-2 flex items-center gap-1.5">
    <i data-lucide="info" class="w-3.5 h-3.5 text-slate-500"></i>
    <?= $tH('नम्बर प्लेट फर्म्याट','Plate Format Guide') ?>
  </h2>
  <div class="grid grid-cols-2 gap-2">
    <?php foreach([
      ['BA 1 PA 1234', 'बागमती प्रदेश — निजी'],
      ['KO 1 CHA 0001','कोशी — सरकारी'],
      ['GA 1 GA 5678', 'गण्डकी — निजी'],
      ['LU 1 PA 2222', 'लुम्बिनी — निजी'],
    ] as [$plate,$note]): ?>
    <div class="bg-slate-50 rounded-xl px-3 py-2">
      <div class="font-mono font-bold text-[13px] text-orange-700 tracking-wider"><?= $plate ?></div>
      <div class="text-[10px] text-slate-500 mt-0.5"><?= $note ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Quick links ── -->
<section class="px-4 mb-3">
  <h2 class="text-[12px] font-bold text-slate-700 mb-2 flex items-center gap-1.5">
    <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
    <?= $tH('आधिकारिक पोर्टलहरू','Official Portals') ?>
  </h2>
  <div class="space-y-2">
    <?php foreach([
      ['DoTM सवारी खोज', 'https://www.dotm.gov.np/en/vehicle/',     'गाडी दर्ता, मालिक, कर स्थिति',     'car-front', 'orange'],
      ['DOTM अनलाइन',    'https://dotmis.gov.np/',                   'लाइसेन्स, Bluebook सेवाहरू',        'file-text',  'amber'],
      ['कर भुक्तानी',    'https://tax.mofd.gov.np/',                 'सवारी कर online भुक्तान',           'receipt',    'emerald'],
      ['बीमा जाँच',      'https://www.ib.gov.np/',                   'बीमा समिति — बीमा स्थिति जाँच',     'shield',     'sky'],
    ] as [$name,$url,$desc,$ic,$cl]): ?>
    <a href="<?= $url ?>" target="_blank" rel="noopener"
      class="flex items-center gap-3 bg-white rounded-2xl p-3 shadow-app">
      <span class="w-9 h-9 rounded-xl bg-<?= $cl ?>-100 text-<?= $cl ?>-700 flex items-center justify-center flex-shrink-0">
        <i data-lucide="<?= $ic ?>" class="w-4 h-4"></i>
      </span>
      <div class="flex-1">
        <div class="text-[13px] font-semibold text-slate-800"><?= $name ?></div>
        <div class="text-[10px] text-slate-500"><?= $desc ?></div>
      </div>
      <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Renewal guide ── -->
<section class="px-4 mb-4">
  <h2 class="text-[12px] font-bold text-slate-700 mb-2 flex items-center gap-1.5">
    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-orange-500"></i>
    <?= $tH('कर नवीकरण प्रक्रिया (Step-by-Step)','Tax Renewal Process') ?>
  </h2>
  <div class="bg-white rounded-2xl shadow-app p-3 mb-3">
    <ol class="space-y-2">
      <?php foreach([
        ['Bluebook (नीलो किताब) तयार राख्नुस्','सवारीको मुख्य दस्तावेज'],
        ['बीमा नवीकरण गर्नुस्','कर तिर्नु अघि valid बीमा चाहिन्छ'],
        ['Lalpurja / नागरिकता फोटोकपी','मालिकको परिचय प्रमाण'],
        ['DoTM कार्यालयमा जानुस् वा Online','tax.mofd.gov.np बाट online तिर्न सकिन्छ'],
        ['Sticker / नवीकरण टाँस्नुस्','Windshield मा राम्रोसँग टाँस्नुस्'],
      ] as $i=>[$step,$note]): ?>
      <li class="flex gap-2.5 items-start">
        <span class="w-5 h-5 rounded-full bg-orange-100 text-orange-700 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5"><?= $i+1 ?></span>
        <div>
          <div class="text-[12px] font-semibold text-slate-800 ne"><?= htmlspecialchars($step) ?></div>
          <div class="text-[10.5px] text-slate-500 ne"><?= htmlspecialchars($note) ?></div>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>

  <!-- Tax rates -->
  <div class="text-[11px] font-bold text-slate-600 mb-1.5">🚗 वार्षिक सवारी कर (अनुमानित)</div>
  <div class="bg-white rounded-2xl shadow-app overflow-hidden mb-3">
    <table class="w-full text-[11px]">
      <thead class="bg-slate-50 border-b border-slate-100">
        <tr>
          <th class="text-left p-2 text-slate-500 font-bold">सवारी</th>
          <th class="text-right p-2 text-slate-500 font-bold">cc / प्रकार</th>
          <th class="text-right p-2 text-slate-500 font-bold">कर (वार्षिक)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach([
          ['मोटरसाइकल', '≤ १२५cc',   'रू ३,५०० – ५,०००'],
          ['मोटरसाइकल', '१२५–२५०cc', 'रू ५,५०० – ७,५००'],
          ['कार (पेट्रोल)','≤ १३००cc', 'रू १०,५०० – १५,०००'],
          ['कार (पेट्रोल)','१३०१–२०००cc','रू १५,५०० – २५,०००'],
          ['इलेक्ट्रिक','EV स्कुटर',  'रू २,५०० – ४,०००'],
          ['मिनीबस','मालवाहक / सवारी','रू ३०,०००+'],
        ] as [$v,$cc,$tax]): ?>
        <tr class="hover:bg-slate-50">
          <td class="p-2 font-semibold text-slate-700 ne"><?= $v ?></td>
          <td class="p-2 text-right text-slate-500 ne"><?= $cc ?></td>
          <td class="p-2 text-right font-bold text-orange-700 ne"><?= $tax ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="px-3 py-1.5 bg-slate-50 text-[9px] text-slate-400">* प्रदेश र वर्ष अनुसार फरक। आधिकारिक दर DoTM / MOFD बाट जाँच्नुस्।</div>
  </div>

  <!-- Insurance tips -->
  <div class="bg-sky-50 border border-sky-100 rounded-2xl p-3">
    <div class="text-[11px] font-bold text-sky-800 mb-1.5 flex items-center gap-1"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> बीमा जाँच र नवीकरण सुझाव</div>
    <ul class="space-y-1 text-[11px] text-sky-700">
      <li>🔹 <b>Third Party बीमा</b> — अनिवार्य। रू १,५०० – ३,००० प्रति वर्ष (सवारी अनुसार)</li>
      <li>🔹 <b>Comprehensive बीमा</b> — आफ्नो सवारी पनि Cover। महँगो तर सुरक्षित</li>
      <li>🔹 <b>बीमा जाँच</b> → <b>ib.gov.np</b> मा नम्बर राखेर online जाँच गर्नुस्</li>
      <li>🔹 म्याद सकिएको सवारी चलाउँदा <b>₹ ५,०००–२०,०००</b> जरिवाना हुन सक्छ</li>
    </ul>
  </div>
</section>

<!-- ── DoTM offices ── -->
<section class="px-4 mb-6">
  <h2 class="text-[12px] font-bold text-slate-700 mb-2 flex items-center gap-1.5">
    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-500"></i>
    <?= $tH('DoTM कार्यालय सम्पर्क','DoTM Office Contacts') ?>
  </h2>
  <div class="grid grid-cols-2 gap-2">
    <?php foreach([
      ['काठमाडौं', '01-4416614'],
      ['पोखरा',    '061-540220'],
      ['विराटनगर', '021-470077'],
      ['बुटवल',    '071-548020'],
      ['बिरगञ्ज',  '051-521040'],
      ['धनगढी',    '091-521180'],
    ] as [$city,$phone]): ?>
    <a href="tel:<?= preg_replace('/[^0-9]/','',$phone) ?>"
      class="flex items-center gap-2 bg-white rounded-xl p-2.5 shadow-app">
      <i data-lucide="phone" class="w-3.5 h-3.5 text-orange-500 flex-shrink-0"></i>
      <div>
        <div class="text-[12px] font-semibold text-slate-800 ne"><?= $city ?></div>
        <div class="text-[11px] text-slate-500"><?= $phone ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

</main>

<script>
(function(){
  function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  async function doCheck(){
    const val = document.getElementById('veh-input').value.trim().toUpperCase();
    const res = document.getElementById('veh-result');
    if (!val) {
      res.classList.remove('hidden');
      res.innerHTML = '<div class="text-rose-600 text-[12px]">नम्बर प्लेट राख्नुहोस्।</div>';
      return;
    }
    res.classList.remove('hidden');
    res.innerHTML = '<div class="text-slate-400 text-[12px] py-2 text-center"><i data-lucide="loader" class="w-4 h-4 animate-spin inline-block mr-1"></i> जाँच हुँदै...</div>';
    if (window.lucide) lucide.createIcons();
    try {
      const r = await fetch('/api/gov-check.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'vehicle', vehicle_no: val })
      });
      const d = await r.json();
      if (d && d.success) {
        const fields = {
          'vehicle_no':       'नम्बर प्लेट',
          'owner':            'गाडी धनी',
          'type':             'किसिम',
          'make':             'ब्र्यान्ड / मोडेल',
          'color':            'रङ',
          'tax_paid_until':   'कर म्याद',
          'insurance_until':  'बीमा म्याद',
          'status':           'स्थिति',
        };
        const rows = Object.entries(fields)
          .filter(([k]) => d[k] !== undefined && d[k] !== null && d[k] !== '')
          .map(([k,label]) => {
            const val = escapeHtml(String(d[k]));
            const isExpired = (k === 'tax_paid_until' || k === 'insurance_until') && new Date(d[k]) < new Date();
            return `<div class="flex justify-between gap-2 py-1.5 border-b border-slate-100 text-[12px]">
              <span class="text-slate-500">${label}</span>
              <b class="${isExpired ? 'text-rose-600' : 'text-slate-900'}">${val}${isExpired ? ' ⚠' : ''}</b>
            </div>`;
          }).join('');
        res.innerHTML = `<div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3">
          <div class="font-bold text-emerald-800 mb-2 text-[12px]">✓ फेला पर्‍यो — स्रोत: ${escapeHtml(d.source||'DoTM Nepal')}</div>
          ${rows}
          <a href="https://www.dotm.gov.np/en/vehicle/" target="_blank" rel="noopener"
            class="block mt-2 text-teal-700 font-semibold text-[11px]">DoTM पोर्टलमा पुरा विवरण हेर्नुहोस् ↗</a>
        </div>`;
      } else {
        const steps = (d.steps||[]).map(s=>`<li class="text-[11px]">${s}</li>`).join('');
        res.innerHTML = `<div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
          <div class="font-bold text-amber-800 mb-1 text-[12px]">⚠ ${escapeHtml(d.message||'Live डेटा पाउन सकिएन')}</div>
          ${steps ? `<ol class="list-decimal ml-5 mt-1 space-y-1">${steps}</ol>` : ''}
          <div class="flex gap-2 mt-2">
            <button onclick="navigator.clipboard.writeText('${val.replace(/'/g,"\\'")}');this.textContent='✓ Copied!'"
              class="bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-[11px] font-semibold">📋 Copy</button>
            <a href="https://www.dotm.gov.np/en/vehicle/" target="_blank" rel="noopener"
              class="bg-orange-500 text-white rounded-lg px-3 py-1.5 text-[11px] font-bold">DoTM पोर्टल ↗</a>
          </div>
        </div>`;
      }
    } catch(e) {
      res.innerHTML = `<div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
        <div class="font-bold text-amber-800 mb-2 text-[12px]">⚠ जडान भएन — Official पोर्टल प्रयोग गर्नुहोस्</div>
        <a href="https://www.dotm.gov.np/en/vehicle/" target="_blank" rel="noopener"
          class="bg-orange-500 text-white rounded-lg px-3 py-2 text-[12px] font-bold inline-block">DoTM Vehicle Portal ↗</a>
      </div>`;
    }
  }

  document.getElementById('veh-check-btn').addEventListener('click', doCheck);
  document.getElementById('veh-input').addEventListener('keydown', e => { if(e.key==='Enter') doCheck(); });
  // Auto uppercase
  document.getElementById('veh-input').addEventListener('input', function(){ this.value = this.value.toUpperCase(); });
})();
</script>
<?php require_once __DIR__ . '/footer.php'; ?>
