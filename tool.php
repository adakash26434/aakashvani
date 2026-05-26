<?php
/**
 * tool.php — Universal landing for tool tiles that don't yet have a dedicated page.
 * Routes via ?slug=emi|currency|date-convert|fd-sip|loan|unit|number|load-shedding|weather|speed-test
 * Loads inside the unified app shell (header.php / footer.php), so the look matches every other inner page.
 */
require_once __DIR__ . '/header.php';

$slug = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['slug'] ?? ''));

$tools = [
  'emi' => [
    'title'=>'EMI Calculator', 'titleNe'=>'EMI क्यालकुलेटर',
    'icon'=>'calculator','color'=>'sky',
    'descNe'=>'मासिक किस्ता गणना — ऋण रकम, ब्याजदर र अवधि हाल्नुहोस्।',
    'descEn'=>'Calculate monthly EMI from principal, rate and tenure.',
    'fields'=>[['p','मूल रकम (रु)',100000],['r','ब्याजदर वार्षिक %',12],['n','अवधि (महिना)',24]],
    'formula'=>'emi',
    'sources'=>[['Nepal Rastra Bank — Interest rate guidelines','https://www.nrb.org.np/']]
  ],
  'loan' => [
    'title'=>'Loan Calculator','titleNe'=>'ऋण क्यालकुलेटर',
    'icon'=>'landmark','color'=>'indigo',
    'descNe'=>'कुल भुक्तानी र ब्याज हेर्नुहोस्।',
    'descEn'=>'See total payment and interest.',
    'fields'=>[['p','ऋण रकम (रु)',500000],['r','ब्याजदर वार्षिक %',13],['n','अवधि (वर्ष)',5]],
    'formula'=>'loan',
    'sources'=>[['Nepal Rastra Bank','https://www.nrb.org.np/']]
  ],
  'fd-sip' => [
    'title'=>'FD / SIP Calculator','titleNe'=>'FD / SIP क्यालकुलेटर',
    'icon'=>'piggy-bank','color'=>'amber',
    'descNe'=>'सावधिक निक्षेपको परिपक्व रकम र SIP को भविष्यको मूल्य गणना।',
    'descEn'=>'Maturity value of fixed deposit & future value of SIP.',
    'fields'=>[['p','मासिक/एकमुष्ट लगानी (रु)',5000],['r','वार्षिक %',9],['n','वर्ष',5]],
    'formula'=>'sip',
    'sources'=>[['NRB Banking statistics','https://www.nrb.org.np/category/banking-supervision/']]
  ],
  'currency' => [
    'title'=>'Currency Converter','titleNe'=>'मुद्रा साटासाट',
    'icon'=>'dollar-sign','color'=>'green',
    'descNe'=>'लाइभ NRB विनिमय दरबाट साटासाट। तल "बजार" पृष्ठमा पूर्ण विवरण।',
    'descEn'=>'Live conversion via NRB exchange rates. Full table on Market page.',
    'cta'=>['/utilities.php#forex','NRB Forex हेर्नुहोस्'],
    'sources'=>[['Nepal Rastra Bank — Forex','https://www.nrb.org.np/forex/']]
  ],
  'date-convert' => [
    'title'=>'AD ↔ BS Date','titleNe'=>'मिति AD ↔ BS',
    'icon'=>'calendar','color'=>'violet',
    'descNe'=>'विक्रम सम्वत् र इस्वी सम्वत् बीच मिति बदल्नुहोस्।',
    'descEn'=>'Convert dates between Bikram Sambat and Gregorian.',
    'cta'=>['/nepali-patro.php','पात्रोमा खोल्नुहोस्'],
    'sources'=>[['Nepal Calendar — official','https://www.hamropatro.com/']]
  ],
  'unit' => [
    'title'=>'Unit Converter','titleNe'=>'एकाइ रूपान्तरण',
    'icon'=>'ruler','color'=>'rose',
    'descNe'=>'लम्बाई, क्षेत्रफल (रोपनी/आना/कट्ठा), तौल, तापक्रम।',
    'descEn'=>'Length, area (ropani/aana/kattha), weight, temperature.',
    'formula'=>'unit',
    'sources'=>[['Nepal Bureau of Standards & Metrology','https://nbsm.gov.np/']]
  ],
  'number' => [
    'title'=>'Number in Nepali','titleNe'=>'अंक नेपालीमा',
    'icon'=>'hash','color'=>'teal',
    'descNe'=>'अंक → नेपाली शब्द र देवनागरी अंक रूपान्तरण।',
    'descEn'=>'Convert digit → Nepali words & Devanagari numerals.',
    'formula'=>'number',
    'sources'=>[['Nepal Academy — official lexicon','https://nepalacademy.gov.np/']]
  ],
  'pdf-converter' => [
    'title'=>'PDF Converter','titleNe'=>'PDF रूपान्तरण',
    'icon'=>'file-type','color'=>'rose',
    'descNe'=>'Image/Document लाई PDF मा बदल्ने सुविधा। Server-side conversion library नभएसम्म browser/local tool वा Admin-approved processor जोड्न सकिन्छ।',
    'descEn'=>'Convert images/documents to PDF. Requires a browser/local processor or server-side PDF library.',
    'status'=>'UI तयार · processor जोड्न बाँकी',
    'cta'=>['/tools.php','सबै PDF टूल हेर्नुहोस्'],
    'sources'=>[['PDF Association — PDF standard','https://pdfa.org/']]
  ],
  'pdf-merge' => [
    'title'=>'PDF Merge','titleNe'=>'PDF जोड्ने',
    'icon'=>'files','color'=>'sky',
    'descNe'=>'धेरै PDF फाइल एउटै PDF मा जोड्ने सुविधा। Privacy का लागि client-side merge library जोड्दा राम्रो हुन्छ।',
    'descEn'=>'Merge multiple PDF files into one. A client-side merge library is recommended for privacy.',
    'status'=>'UI तयार · client-side merge जोड्न बाँकी',
    'cta'=>['/tools.php','सबै PDF टूल हेर्नुहोस्'],
    'sources'=>[['PDF Association — PDF standard','https://pdfa.org/']]
  ],
  'pdf-split' => [
    'title'=>'PDF Split','titleNe'=>'PDF छुट्याउने',
    'icon'=>'scissors','color'=>'amber',
    'descNe'=>'PDF बाट page range छुट्याउने सुविधा। Backend library नभएसम्म यो tool placeholder/details mode मा छ।',
    'descEn'=>'Split pages or ranges from PDF. Currently ready as a UI/detail placeholder until processing is connected.',
    'status'=>'UI तयार · split processor जोड्न बाँकी',
    'cta'=>['/tools.php','सबै PDF टूल हेर्नुहोस्'],
    'sources'=>[['PDF Association — PDF standard','https://pdfa.org/']]
  ],
  'pdf-compress' => [
    'title'=>'PDF Compress','titleNe'=>'PDF Compress',
    'icon'=>'archive','color'=>'emerald',
    'descNe'=>'PDF file size घटाउने सुविधा। Quality/privacy control सहित processor जोड्न बाँकी छ।',
    'descEn'=>'Compress PDF file size. Processor with quality/privacy controls can be added.',
    'status'=>'UI तयार · compression engine जोड्न बाँकी',
    'cta'=>['/tools.php','सबै PDF टूल हेर्नुहोस्'],
    'sources'=>[['PDF Association — PDF standard','https://pdfa.org/']]
  ],
  'load-shedding' => [
    'title'=>'Load Shedding','titleNe'=>'लोडसेडिङ तालिका',
    'icon'=>'zap-off','color'=>'yellow',
    'descNe'=>'NEA को आधिकारिक तालिका। आफ्नो समूह छान्नुहोस्।',
    'descEn'=>'Official NEA schedule. Select your group.',
    'cta'=>['https://nea.org.np/load-shedding','NEA साइटमा खोल्नुहोस्'],
    'sources'=>[['Nepal Electricity Authority','https://nea.org.np/'],['NEA Load-shed page','https://nea.org.np/load-shedding']]
  ],
  'weather' => [
    'title'=>'Weather','titleNe'=>'मौसम',
    'icon'=>'cloud-sun','color'=>'sky',
    'descNe'=>'जल तथा मौसम विज्ञान विभागको पूर्वानुमान।',
    'descEn'=>'Forecast from Dept. of Hydrology & Meteorology.',
    'cta'=>['https://www.mfd.gov.np/','DHM मा हेर्नुहोस्'],
    'sources'=>[['Department of Hydrology & Meteorology','https://www.dhm.gov.np/'],['MFD','https://www.mfd.gov.np/']]
  ],
  'speed-test' => [
    'title'=>'Internet Speed Test','titleNe'=>'इन्टरनेट स्पीड टेस्ट',
    'icon'=>'gauge','color'=>'indigo',
    'descNe'=>'तेस्रो-पक्ष स्पीड टेस्ट सेवामा खुल्छ — स्पष्ट डाटा प्रयोग।',
    'descEn'=>'Opens trusted third-party speed test — clear data use.',
    'cta'=>['https://www.speedtest.net/','Speedtest खोल्नुहोस्'],
    'sources'=>[['Speedtest by Ookla','https://www.speedtest.net/']]
  ],
];

$tool = $tools[$slug] ?? null;
?>
<main class="app-main app-shell">
  <section class="px-4 pt-3">
    <?php if (!$tool): ?>
      <div class="bg-white rounded-2xl p-6 text-center shadow-app">
        <i data-lucide="alert-triangle" class="w-10 h-10 mx-auto text-amber-500 mb-3"></i>
        <h1 class="ne">यो टुल भेटिएन</h1>
        <p class="ne mt-2">सबै टुलहरूको सूचीमा जानुहोस्।</p>
        <a href="/tools.php" class="inline-block mt-3 px-4 py-2 rounded-xl bg-teal-600 text-white font-semibold text-sm">← टुलहरू</a>
      </div>
    <?php else: ?>
      <div class="flex items-center gap-3 mb-3">
        <a href="/tools.php" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center"><i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i></a>
        <div class="w-11 h-11 rounded-xl bg-<?= htmlspecialchars($tool['color']) ?>-100 text-<?= htmlspecialchars($tool['color']) ?>-700 flex items-center justify-center">
          <i data-lucide="<?= htmlspecialchars($tool['icon']) ?>" class="w-5 h-5"></i>
        </div>
        <div>
          <h1 class="ne"><?= htmlspecialchars($tool['titleNe']) ?></h1>
          <p class="text-[11.5px] text-slate-500"><?= htmlspecialchars($tool['title']) ?></p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
        <p class="ne text-slate-700"><?= htmlspecialchars($tool['descNe']) ?></p>
        <?php if (!empty($tool['status'])): ?>
          <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-amber-50 text-amber-800 border border-amber-100 px-2.5 py-1 text-[11px] font-bold ne">
            <i data-lucide="info" class="w-3.5 h-3.5"></i><?= htmlspecialchars($tool['status']) ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($tool['fields'])): ?>
      <div class="bg-white rounded-2xl p-4 shadow-app mb-3">
        <div class="space-y-3">
          <?php foreach($tool['fields'] as $f): ?>
            <label class="block">
              <span class="block text-[12.5px] font-semibold text-slate-700 mb-1 ne"><?= htmlspecialchars($f[1]) ?></span>
              <input type="number" id="fld_<?= $f[0] ?>" value="<?= $f[2] ?>" step="any"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"/>
            </label>
          <?php endforeach; ?>
          <button onclick="calcTool()" class="w-full py-2.5 rounded-xl bg-teal-600 text-white font-bold text-sm ne">गणना गर्नुहोस्</button>
          <div id="toolResult" class="hidden mt-2 p-3 rounded-xl bg-teal-50 border border-teal-100 text-teal-900 text-[13.5px] font-bold ne"></div>
        </div>
      </div>
      <script>
        var FORMULA = <?= json_encode($tool['formula'] ?? '') ?>;
        function calcTool(){
          var p = parseFloat(document.getElementById('fld_p')?.value || 0);
          var r = parseFloat(document.getElementById('fld_r')?.value || 0);
          var n = parseFloat(document.getElementById('fld_n')?.value || 0);
          var out = '—';
          if (FORMULA==='emi'){
            var i = r/12/100; var m = (p*i*Math.pow(1+i,n))/(Math.pow(1+i,n)-1);
            out = 'मासिक EMI: रु '+m.toFixed(2)+' · कुल भुक्तानी: रु '+(m*n).toFixed(0);
          } else if (FORMULA==='loan'){
            var i = r/12/100, mo = n*12;
            var m = (p*i*Math.pow(1+i,mo))/(Math.pow(1+i,mo)-1);
            out = 'मासिक: रु '+m.toFixed(2)+' · कुल ब्याज: रु '+((m*mo)-p).toFixed(0);
          } else if (FORMULA==='sip'){
            var i = r/12/100, mo = n*12;
            var fv = p * ((Math.pow(1+i,mo)-1)/i) * (1+i);
            out = 'परिपक्व रकम: रु '+fv.toFixed(0);
          } else {
            out = 'परिणाम: '+ (p||0);
          }
          var el = document.getElementById('toolResult');
          el.textContent = out; el.classList.remove('hidden');
        }
      </script>
      <?php endif; ?>

      <?php if (!empty($tool['cta'])): ?>
      <a href="<?= htmlspecialchars($tool['cta'][0]) ?>" <?= strpos($tool['cta'][0],'http')===0?'target="_blank" rel="noopener"':'' ?>
         class="block bg-gradient-to-br from-teal-600 to-emerald-700 text-white rounded-2xl p-4 shadow-app mb-3">
        <div class="flex items-center gap-2">
          <i data-lucide="external-link" class="w-4 h-4"></i>
          <span class="ne font-bold"><?= htmlspecialchars($tool['cta'][1]) ?></span>
        </div>
      </a>
      <?php endif; ?>

      <div class="bg-white rounded-2xl p-4 shadow-app">
        <div class="text-[11.5px] font-bold text-slate-500 uppercase tracking-wide mb-2 ne">आधिकारिक स्रोत</div>
        <div class="space-y-1.5">
          <?php foreach ($tool['sources'] as $s): ?>
            <a href="<?= htmlspecialchars($s[1]) ?>" target="_blank" rel="noopener" class="flex items-center gap-2 text-[13px] text-teal-700 hover:text-teal-800 ne">
              <i data-lucide="link" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($s[0]) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <p class="text-[11px] text-slate-400 mt-3 ne">कुनै पनि गणना सूचक मात्र हो। निर्णयअघि आधिकारिक स्रोत पुष्टि गर्नुहोस्।</p>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
