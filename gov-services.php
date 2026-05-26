<?php
/**
 * gov-services.php v11 — App-style government services directory
 */
require_once __DIR__ . '/header.php';

// v12 fix: पहिले tiles ले /gov/*.php मा link गर्थे जुन files exist नै गरेका थिएनन्
// (सबै 404 दिन्थे)। अब each tile ले या त live status check (api/gov-check.php)
// खोल्छ, या api/gov-services.php को rich info modal trigger गर्छ।
// data-svc = api/gov-services.php को key; data-check = gov-check.php को type.
$svc = [
  ['नागरिकता','Citizenship','citizenship','user-check','sky',null],
  ['राहदानी','Passport','passport','book-open','indigo','passport'],
  ['सवारी इजाजत','License','driving_license','car','emerald','license'],
  ['PAN/VAT','PAN/VAT','pan_vat','file-text','amber','pan'],
  ['सम्पत्ति कर','Property Tax','property_tax','home','rose',null],
  ['विद्यालय','Education','education','graduation-cap','violet',null],
  ['स्वास्थ्य बीमा','Health Ins.','health_insurance','heart-pulse','red',null],
  ['राष्ट्रिय परिचयपत्र','National ID','national_id','id-card','teal','nid'],
  ['सवारी दर्ता','Vehicle','vehicle','car-front','orange','vehicle'],
  ['सामाजिक सुरक्षा','Social Sec.','social_security','shield','teal',null],
];

$contacts = [
  ['प्रहरी','Police','100','phone','red'],
  ['एम्बुलेन्स','Ambulance','102','ambulance','rose'],
  ['फायर','Fire','101','flame','orange'],
  ['बाल हेल्पलाइन','Child','1098','baby','pink'],
];

// Quick-action tiles (same card style as Emergency, but action-based) — v12
$quick = [
  ['password',  'पासवर्ड रिसेट',     'Password Reset',  'key-round',  'amber',  'सरकारी पोर्टल पासवर्ड भुलियो? Live रिसेट step'],
  ['nid',       'राष्ट्रिय परिचयपत्र','National ID',     'id-card',    'teal',   'NID Application status — Live चेक'],
  ['pan',       'PAN चेक',           'PAN Lookup',      'file-text',  'sky',    'IRD पोर्टलमा PAN status'],
  ['passport',  'राहदानी status',    'Passport Status', 'book-open',  'indigo', 'राहदानी application status'],
];
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <h1 class="text-[20px] font-bold text-slate-900 mb-1"><?= $tH('सरकारी सेवा','Government Services') ?></h1>
    <p class="text-[12px] text-slate-500 mb-3"><?= $tH('सबै सरकारी सेवा एकै ठाउँमा','All gov services in one place') ?></p>
  </section>

  <!-- Emergency strip -->
  <section class="px-4">
    <div class="bg-rose-50 border border-rose-100 rounded-2xl p-3">
      <div class="text-[11px] font-bold text-rose-700 mb-2 flex items-center gap-1"><i data-lucide="phone-call" class="w-3.5 h-3.5"></i><?= $tH('आपतकालीन सम्पर्क','Emergency Contacts') ?></div>
      <div class="grid grid-cols-4 gap-2">
        <?php foreach($contacts as $c): ?>
          <a href="tel:<?= $c[2] ?>" class="bg-white rounded-xl p-2 text-center shadow-app">
            <div class="w-8 h-8 mx-auto rounded-full bg-<?= $c[4] ?>-100 text-<?= $c[4] ?>-700 flex items-center justify-center mb-1"><i data-lucide="<?= $c[3] ?>" class="w-4 h-4"></i></div>
            <div class="text-[14px] font-extrabold text-slate-900 leading-none"><?= $c[2] ?></div>
            <div class="text-[9px] text-slate-500 mt-0.5"><?= $tH($c[0],$c[1]) ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Quick action strip (same card style as Emergency) — v12 -->
  <section class="px-4 mt-3">
    <div class="bg-teal-50 border border-teal-100 rounded-2xl p-3">
      <div class="text-[11px] font-bold text-teal-700 mb-2 flex items-center gap-1"><i data-lucide="zap" class="w-3.5 h-3.5"></i><?= $tH('द्रुत कार्य','Quick Actions') ?></div>
      <div class="grid grid-cols-4 gap-2">
        <?php foreach($quick as $q): ?>
          <button type="button" data-check="<?= htmlspecialchars($q[0], ENT_QUOTES) ?>" data-svc="<?= htmlspecialchars($q[0]==='password'?'national_id':($q[0]==='nid'?'national_id':($q[0]==='pan'?'pan_vat':'passport')), ENT_QUOTES) ?>" class="svc-tile bg-white rounded-xl p-2 text-center shadow-app w-full">
            <div class="w-8 h-8 mx-auto rounded-full bg-<?= $q[4] ?>-100 text-<?= $q[4] ?>-700 flex items-center justify-center mb-1"><i data-lucide="<?= $q[3] ?>" class="w-4 h-4"></i></div>
            <div class="text-[10.5px] font-bold text-slate-900 leading-tight"><?= $tH($q[1],$q[2]) ?></div>
          </button>
        <?php endforeach; ?>
      </div>
      <div class="text-[10px] text-slate-500 mt-2 leading-snug">पासवर्ड भुलियो भने सम्बन्धित सरकारी पोर्टलको "Forgot Password" link माथिको कुनै कार्ड बाट खुल्छ।</div>
    </div>
  </section>

  <!-- Services grid -->
  <section class="px-4 mt-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('सेवाहरू','Services') ?></h2>
    <div class="grid grid-cols-2 gap-2.5">
      <?php foreach($svc as $s): ?>
        <button type="button" data-svc="<?= htmlspecialchars($s[2], ENT_QUOTES) ?>" data-check="<?= htmlspecialchars((string)($s[5] ?? ''), ENT_QUOTES) ?>" class="svc-tile bg-white rounded-2xl p-3.5 shadow-app flex items-center gap-3 text-left w-full">
          <div class="w-11 h-11 rounded-xl bg-<?= $s[4] ?>-100 text-<?= $s[4] ?>-700 flex items-center justify-center shrink-0"><i data-lucide="<?= $s[3] ?>" class="w-5 h-5"></i></div>
          <div class="min-w-0 flex-1">
            <div class="text-[13px] font-bold text-slate-900 truncate"><?= $tH($s[0],$s[1]) ?></div>
            <div class="text-[10px] text-slate-500"><?= $s[5] ? '✓ Live status चेक' : 'विवरण हेर्नुहोस्' ?></div>
          </div>
        </button>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Quick links -->
  <section class="px-4 mt-4 pb-4">
    <h2 class="text-[13px] font-bold text-slate-700 mb-2"><?= $tH('द्रुत लिङ्क','Quick Links') ?></h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php foreach([
        ['nagarikapp.gov.np','नागरिक एप पोर्टल','external-link'],
        ['ird.gov.np','आन्तरिक राजस्व विभाग','external-link'],
        ['nepalpolice.gov.np','नेपाल प्रहरी','external-link'],
        ['election.gov.np','निर्वाचन आयोग','external-link'],
      ] as $l): ?>
        <a href="https://<?= $l[0] ?>" target="_blank" rel="noopener" class="flex items-center gap-3 p-3">
          <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center"><i data-lucide="landmark" class="w-4 h-4"></i></div>
          <div class="flex-1 min-w-0">
            <div class="text-[13px] font-semibold text-slate-900 truncate"><?= htmlspecialchars($l[1]) ?></div>
            <div class="text-[11px] text-slate-500 truncate"><?= $l[0] ?></div>
          </div>
          <i data-lucide="<?= $l[2] ?>" class="w-4 h-4 text-slate-400"></i>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</main>


  <!-- Service detail modal (info + live check) — v12 -->
  <div id="svc-modal" class="fixed inset-0 z-50 bg-black/50 hidden items-end sm:items-center justify-center" onclick="if(event.target===this)closeSvc()">
    <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl max-h-[90vh] overflow-hidden flex flex-col">
      <div class="p-4 border-b border-slate-100 flex items-center gap-2">
        <div class="flex-1 min-w-0">
          <div id="svc-title" class="text-[15px] font-bold text-slate-900 truncate">—</div>
          <div id="svc-office" class="text-[11px] text-slate-500 truncate">—</div>
        </div>
        <button onclick="closeSvc()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center"><i data-lucide="x" class="w-4 h-4"></i></button>
      </div>
      <div id="svc-body" class="p-4 overflow-y-auto text-[12.5px] text-slate-700 space-y-3">लोड हुँदै…</div>
    </div>
  </div>

<script>
(function(){
  const modal = document.getElementById('svc-modal');
  const body  = document.getElementById('svc-body');
  const title = document.getElementById('svc-title');
  const office= document.getElementById('svc-office');
  window.closeSvc = () => modal.classList.add('hidden') || modal.classList.remove('flex');

  function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c])); }

  function renderInfo(d){
    const docs = (d.documents||[]).map(x=>`<li>${escapeHtml(x.ne||x.en||'')}</li>`).join('');
    const steps= (d.process  ||[]).map(x=>`<li><b>${x.step}.</b> ${escapeHtml(x.ne||x.en||'')}</li>`).join('');
    const locs = (d.locations||[]).map(x=>`<div class="text-[11px] text-slate-600">• ${escapeHtml(x.name)} — ${escapeHtml(x.address||'')} <a href="tel:${escapeHtml(x.phone||'')}" class="text-teal-700">${escapeHtml(x.phone||'')}</a></div>`).join('');
    const cats = (d.categories||[]).map(x=>`<div class="flex justify-between text-[12px]"><span>[${escapeHtml(x.code)}] ${escapeHtml(x.ne||x.en)}</span><b>रु ${x.fee||0}</b></div>`).join('');
    return `
      ${d.website ? `<a href="${escapeHtml(d.website)}" target="_blank" rel="noopener" class="block bg-teal-50 text-teal-800 rounded-xl p-2.5 text-[12px] font-semibold">🌐 आधिकारिक पोर्टल: ${escapeHtml(d.website)} ↗</a>` : ''}
      ${d.helpline ? `<a href="tel:${escapeHtml(d.helpline)}" class="block bg-rose-50 text-rose-800 rounded-xl p-2.5 text-[12px] font-semibold">📞 हेल्पलाइन: ${escapeHtml(d.helpline)}</a>` : ''}
      ${docs ? `<div><div class="text-[11px] font-bold text-slate-500 mb-1">📋 चाहिने कागजात</div><ul class="list-disc ml-5 space-y-0.5">${docs}</ul></div>` : ''}
      ${steps? `<div><div class="text-[11px] font-bold text-slate-500 mb-1">🔄 प्रक्रिया</div><ol class="space-y-0.5">${steps}</ol></div>` : ''}
      ${cats ? `<div><div class="text-[11px] font-bold text-slate-500 mb-1">🏷️ श्रेणीहरू</div>${cats}</div>` : ''}
      ${locs ? `<div><div class="text-[11px] font-bold text-slate-500 mb-1">📍 कार्यालयहरू</div>${locs}</div>` : ''}
    `;
  }

  function renderCheckForm(checkType, svcKey){
    const labels = {
      pan:    {n:'PAN नम्बर',         p:'७-९ अंक',         dob:false, src:'IRD Taxpayer Portal', url:'https://taxpayerportal.ird.gov.np/taxpayer/PanSearch'},
      license:{n:'License नम्बर',     p:'जस्तै: 03-06-12345678', dob:true,  src:'DoTM License Status', url:'https://www.dotm.gov.np/en/print-status/'},
      vehicle:{n:'Vehicle नम्बर',     p:'जस्तै: BA 1 PA 1234',  dob:false, src:'DoTM Vehicle Portal', url:'https://www.dotm.gov.np/'},
      nid:    {n:'NID Registration नम्बर', p:'',           dob:true,  src:'NID Management Centre', url:'https://nidmc.gov.np/status/'},
      passport:{n:'Passport Application नम्बर', p:'',      dob:true,  src:'Department of Passport', url:'https://nepalpassport.gov.np/'},
    };
    const L = labels[checkType]; if(!L) return '';
    return `
      <div class="mt-3 pt-3 border-t border-slate-100">
        <div class="text-[12px] font-bold text-slate-800 mb-2">🔎 Live Status Check</div>
        <form id="chk-form" class="space-y-2">
          <input type="hidden" name="type" value="${checkType}">
          <input name="number" required placeholder="${escapeHtml(L.n)} ${L.p?'('+escapeHtml(L.p)+')':''}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-[13px]">
          ${L.dob ? `<input name="dob" required placeholder="जन्म मिति (YYYY-MM-DD, BS)" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-[13px]">` : ''}
          <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-teal-600 text-white text-[13px] font-bold rounded-xl py-2.5">चेक गर्नुहोस्</button>
            <a href="${escapeHtml(L.url)}" target="_blank" rel="noopener" class="bg-slate-100 text-slate-700 text-[13px] font-semibold rounded-xl py-2.5 px-3">${escapeHtml(L.src)} ↗</a>
          </div>
        </form>
        <div id="chk-result" class="mt-2 hidden text-[12px]"></div>
      </div>
    `;
  }

  async function loadSvc(svcKey, checkType){
    modal.classList.remove('hidden'); modal.classList.add('flex');
    body.innerHTML = '<div class="text-slate-400 text-center py-8">लोड हुँदै…</div>';
    title.textContent = svcKey;
    try {
      const r = await fetch('/api/gov-services.php?service=' + encodeURIComponent(svcKey));
      const j = await r.json();
      const d = (j && (j.data || j)) || {};
      title.textContent = d.name_ne || d.name_en || svcKey;
      office.textContent= d.office_ne || d.office || '';
      body.innerHTML = renderInfo(d) + (checkType ? renderCheckForm(checkType, svcKey) : '');
      if (window.lucide) lucide.createIcons();
      const form = document.getElementById('chk-form');
      if (form) form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(form);
        const result = document.getElementById('chk-result');
        result.classList.remove('hidden');
        result.innerHTML = '<div class="text-slate-400">चेक हुँदै…</div>';
        try {
          const res = await fetch('/api/gov-check.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify(Object.fromEntries(fd.entries()))
          });
          const out = await res.json();
          if (out.success) {
            const rows = ['name','address','status','vat_registered','registration_date','type','owner','make','color','tax_paid_until','insurance_until','category','expiry']
              .filter(k => out[k] !== undefined && out[k] !== null && out[k] !== '')
              .map(k => `<div class="flex justify-between gap-2 py-0.5 border-b border-slate-50"><span class="text-slate-500">${k.replace(/_/g,' ')}</span><b class="text-slate-900 text-right">${escapeHtml(String(out[k]))}</b></div>`)
              .join('');
            result.innerHTML = `
              <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-2.5">
                <div class="font-bold text-emerald-800 mb-1">✓ फेला पर्‍यो — स्रोत: ${escapeHtml(out.source||'official')}</div>
                ${rows || '<div class="text-slate-600">डेटा प्राप्त भयो।</div>'}
                ${out.official_url ? `<a href="${escapeHtml(out.official_url)}" target="_blank" rel="noopener" class="block mt-2 text-teal-700 font-semibold text-[11px]">पुरा विवरणका लागि official source मा हेर्नुहोस् ↗</a>` : ''}
              </div>`;
          } else {
            const steps = (out.steps||[]).map(s=>`<li>${s}</li>`).join('');
            result.innerHTML = `
              <div class="bg-amber-50 border border-amber-200 rounded-xl p-2.5">
                <div class="font-bold text-amber-800 mb-1">⚠ ${escapeHtml(out.message || 'Live डेटा पाउन सकिएन')}</div>
                ${steps ? `<ol class="list-decimal ml-5 mt-1 space-y-0.5 text-[11.5px]">${steps}</ol>` : ''}
                ${out.official_url ? `<div class="mt-2 flex gap-2"><button type="button" onclick="navigator.clipboard.writeText('${escapeHtml(out.entered_number||'')}')" class="bg-white border border-slate-200 rounded-lg px-2 py-1 text-[11px]">📋 Copy</button><a href="${escapeHtml(out.official_url)}" target="_blank" rel="noopener" class="bg-teal-600 text-white rounded-lg px-2 py-1 text-[11px] font-semibold">${escapeHtml(out.official_label||'Open Official')} ↗</a></div>` : ''}
              </div>`;
          }
        } catch(err){
          result.innerHTML = '<div class="text-rose-600">अनुरोध असफल भयो।</div>';
        }
      });
    } catch(e){
      body.innerHTML = '<div class="text-rose-600">लोड गर्न सकिएन।</div>';
    }
  }

  document.querySelectorAll('.svc-tile').forEach(btn => {
    btn.addEventListener('click', () => {
      if (btn.dataset.check === 'password') return openPasswordHelp();
      loadSvc(btn.dataset.svc, btn.dataset.check || '');
    });
  });

  function openPasswordHelp(){
    title.textContent  = 'पासवर्ड रिसेट सहायता';
    office.textContent = 'सरकारी पोर्टलहरूका Forgot Password लिङ्क';
    body.innerHTML = `
      <div class="text-[12px] text-slate-700">तपाईंले प्रयोग गर्ने पोर्टल छान्नुहोस् — "Forgot Password" page directly खुल्छ:</div>
      <div class="space-y-1.5">
        ${[
          ['Nagarik App',      'https://nagarikapp.gov.np/'],
          ['IRD (PAN/VAT)',    'https://taxpayerportal.ird.gov.np/'],
          ['NID पोर्टल',        'https://nidmc.gov.np/'],
          ['DoTM (License)',   'https://www.dotm.gov.np/'],
          ['राहदानी पोर्टल',    'https://nepalpassport.gov.np/'],
          ['Lok Sewa (PSC)',   'https://psconline.psc.gov.np/'],
          ['NEB Result Portal','https://neb.gov.np/'],
          ['eSewa',            'https://esewa.com.np/#/forgot-password'],
          ['Khalti',           'https://khalti.com/account/forgot-password/'],
        ].map(p => `<a href="${escapeHtml(p[1])}" target="_blank" rel="noopener" class="flex items-center justify-between gap-2 bg-slate-50 hover:bg-teal-50 border border-slate-200 rounded-xl px-3 py-2"><span class="font-semibold text-slate-800">${escapeHtml(p[0])}</span><span class="text-teal-700 text-[11px] font-bold">Open ↗</span></a>`).join('')}
      </div>
      <div class="text-[10px] text-slate-500 mt-2">सुरक्षा: पासवर्ड कहिल्यै SMS/Email मा कसैलाई नदिनुहोस्। आधिकारिक पोर्टल मात्र प्रयोग गर्नुहोस्।</div>`;
    modal.classList.remove('hidden'); modal.classList.add('flex');
    if (window.lucide && lucide.createIcons) lucide.createIcons();
  }
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
