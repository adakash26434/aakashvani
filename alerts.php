<?php
/** alerts.php v14 — categorized: Alerts (disasters) | Notices (traffic/police) | Tools (quick access) */
require_once __DIR__ . '/header.php';
?>
<main class="app-main">
  <section class="px-4 pt-3">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h1 class="text-[20px] font-bold text-slate-900 flex items-center gap-2">
          <?= $tH('अलर्ट केन्द्र','Alert Center') ?>
          <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full text-[9.5px] font-bold border border-rose-200">
            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> LIVE
          </span>
        </h1>
        <p class="text-[11px] text-slate-500"><?= $tH('विपद् · ट्राफिक · प्रहरी सूचना · आपत्कालीन उपकरण','Disasters · Traffic · Police notice · Emergency tools') ?></p>
      </div>
      <button onclick="window.nshPushSubscribe&&window.nshPushSubscribe()" class="text-[11px] font-semibold text-white bg-teal-600 hover:bg-teal-700 px-3 py-1.5 rounded-full flex items-center gap-1.5">
        <i data-lucide="bell" class="w-3.5 h-3.5"></i> <?= $tH('Push','Push') ?>
      </button>
    </div>

    <!-- Top tabs: Alerts | Notices | Tools -->
    <div class="grid grid-cols-3 gap-1.5 bg-slate-100 p-1 rounded-2xl mb-3">
      <button data-tab="alerts"  class="tab-btn active flex flex-col items-center gap-0.5 py-2 rounded-xl bg-white shadow-sm">
        <i data-lucide="siren" class="w-4 h-4 text-rose-600"></i>
        <span class="text-[11.5px] font-bold text-slate-900"><?= $tH('अलर्ट','Alerts') ?></span>
        <span class="text-[9.5px] text-slate-500" id="cnt-alerts">…</span>
      </button>
      <button data-tab="notices" class="tab-btn flex flex-col items-center gap-0.5 py-2 rounded-xl text-slate-600">
        <i data-lucide="megaphone" class="w-4 h-4"></i>
        <span class="text-[11.5px] font-bold"><?= $tH('सूचना','Notices') ?></span>
        <span class="text-[9.5px]" id="cnt-notices">…</span>
      </button>
      <button data-tab="tools" class="tab-btn flex flex-col items-center gap-0.5 py-2 rounded-xl text-slate-600">
        <i data-lucide="wrench" class="w-4 h-4"></i>
        <span class="text-[11.5px] font-bold"><?= $tH('टुल्स','Tools') ?></span>
        <span class="text-[9.5px]" id="cnt-tools">6</span>
      </button>
    </div>

    <!-- Sub-filter chips (only for alerts + notices tabs) -->
    <div id="subchips" class="flex gap-1.5 mb-3 overflow-x-auto pb-1" style="scrollbar-width:none"></div>

    <!-- Panes -->
    <div id="pane-alerts" class="space-y-2.5 pb-4">
      <?php for($i=0;$i<3;$i++): ?>
        <div class="bg-white rounded-2xl p-3.5 shadow-app flex gap-3 animate-pulse">
          <div class="w-11 h-11 rounded-xl bg-slate-100"></div>
          <div class="flex-1 space-y-1.5"><div class="h-3 bg-slate-100 rounded w-2/3"></div><div class="h-2 bg-slate-100 rounded w-full"></div><div class="h-2 bg-slate-100 rounded w-1/3"></div></div>
        </div>
      <?php endfor; ?>
    </div>
    <div id="pane-notices" class="hidden space-y-2.5 pb-4"></div>
    <div id="pane-tools" class="hidden pb-4">
      <div class="grid grid-cols-3 gap-2.5">
        <a href="/emergency.php" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-11 h-11 mx-auto rounded-xl bg-red-100 text-red-700 flex items-center justify-center mb-1.5"><i data-lucide="phone-call" class="w-5 h-5"></i></div>
          <div class="text-[11px] font-bold text-slate-800"><?= $tH('आपत्कालीन','Emergency') ?></div>
          <div class="text-[10px] text-slate-500">100 · 101 · 102</div>
        </a>
        <a href="/transport.php" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-11 h-11 mx-auto rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-1.5"><i data-lucide="traffic-cone" class="w-5 h-5"></i></div>
          <div class="text-[11px] font-bold text-slate-800"><?= $tH('ट्राफिक','Traffic') ?></div>
          <div class="text-[10px] text-slate-500"><?= $tH('यातायात','Transport') ?></div>
        </a>
        <a href="/utilities.php" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-11 h-11 mx-auto rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center mb-1.5"><i data-lucide="cloud-rain" class="w-5 h-5"></i></div>
          <div class="text-[11px] font-bold text-slate-800"><?= $tH('मौसम','Weather') ?></div>
          <div class="text-[10px] text-slate-500">DHM</div>
        </a>
        <a href="/utilities.php#loadshedding" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-11 h-11 mx-auto rounded-xl bg-yellow-100 text-yellow-700 flex items-center justify-center mb-1.5"><i data-lucide="zap-off" class="w-5 h-5"></i></div>
          <div class="text-[11px] font-bold text-slate-800"><?= $tH('लोडसेडिङ','Load Shed') ?></div>
          <div class="text-[10px] text-slate-500">NEA</div>
        </a>
        <a href="https://bipadportal.gov.np/" target="_blank" rel="noopener" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-11 h-11 mx-auto rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center mb-1.5"><i data-lucide="mountain" class="w-5 h-5"></i></div>
          <div class="text-[11px] font-bold text-slate-800"><?= $tH('BIPAD','BIPAD') ?></div>
          <div class="text-[10px] text-slate-500"><?= $tH('विपद् पोर्टल','Disaster portal') ?></div>
        </a>
        <a href="https://traffic.nepalpolice.gov.np/" target="_blank" rel="noopener" class="bg-white rounded-2xl p-3 shadow-app text-center">
          <div class="w-11 h-11 mx-auto rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mb-1.5"><i data-lucide="shield" class="w-5 h-5"></i></div>
          <div class="text-[11px] font-bold text-slate-800"><?= $tH('प्रहरी','Police') ?></div>
          <div class="text-[10px] text-slate-500">nepalpolice.gov.np</div>
        </a>
      </div>
    </div>
  </section>
</main>

<style>
.tab-btn.active{background:#fff;color:#0f172a;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.tab-btn{transition:all .15s}
.sub-chip{flex-shrink:0;padding:5px 11px;border-radius:999px;font-size:11px;font-weight:600;background:#fff;color:#475569;border:1px solid #e2e8f0;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:4px}
.sub-chip.active{background:#0f172a;color:#fff;border-color:#0f172a}
</style>

<script>
(function(){
  // Categorization config
  var ALERT_TYPES  = ['earthquake','disaster','weather','flood','landslide','fire'];
  var NOTICE_TYPES = ['traffic','police','alert','transport','loadshedding','water'];

  var TYPE_META = {
    earthquake:  {ic:'activity',      cl:'rose',   label:'भूकम्प'},
    disaster:    {ic:'siren',         cl:'amber',  label:'विपद्'},
    weather:     {ic:'cloud-rain',    cl:'sky',    label:'मौसम'},
    flood:       {ic:'droplets',      cl:'blue',   label:'बाढी'},
    landslide:   {ic:'mountain',      cl:'orange', label:'पहिरो'},
    fire:        {ic:'flame',         cl:'red',    label:'आगलागी'},
    traffic:     {ic:'traffic-cone',  cl:'amber',  label:'ट्राफिक सूचना'},
    police:      {ic:'shield',        cl:'blue',   label:'प्रहरी सूचना'},
    alert:       {ic:'megaphone',     cl:'teal',   label:'सामान्य सूचना'},
    transport:   {ic:'bus',           cl:'indigo', label:'यातायात'},
    loadshedding:{ic:'zap-off',       cl:'yellow', label:'लोडसेडिङ'},
    water:       {ic:'droplet',       cl:'cyan',   label:'खानेपानी'}
  };

  var items = [];
  var currentTab = 'alerts';
  var subFilter = 'all';

  function esc(s){return String(s||'').replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function ago(ts){
    if(!ts) return '';
    var t = typeof ts === 'string' ? Date.parse(ts)/1000 : ts;
    var d = Date.now()/1000 - t;
    if(d<60) return 'भर्खरै';
    if(d<3600) return Math.floor(d/60)+' मिनेट अघि';
    if(d<86400) return Math.floor(d/3600)+' घण्टा अघि';
    return Math.floor(d/86400)+' दिन अघि';
  }

  function normalize(raw){
    var out = []; var src = (raw && (raw.items || raw.alerts)) || [];
    src.forEach(function(a){
      out.push({
        type: a.type || 'alert',
        title: a.title || a.titleEn || a.hazard || 'Alert',
        msg: a.msg || a.description || (a.magnitude?('M '+a.magnitude+' · '+(a.place||'Nepal')):'') || '',
        time: a.startedOn || a.time || a.occurredOn || a.cached_at,
        source: a.source || '', source_url: a.source_url || a.link || ''
      });
    });
    return out;
  }

  function listFor(tab){
    var types = tab === 'alerts' ? ALERT_TYPES : (tab === 'notices' ? NOTICE_TYPES : []);
    var list = items.filter(function(x){ return types.indexOf(x.type) !== -1; });
    if (subFilter !== 'all') list = list.filter(function(x){ return x.type === subFilter; });
    return list;
  }

  function renderChips(){
    var bar = document.getElementById('subchips');
    if (currentTab === 'tools') { bar.innerHTML = ''; return; }
    var types = currentTab === 'alerts' ? ALERT_TYPES : NOTICE_TYPES;
    var present = {};
    items.forEach(function(x){ if (types.indexOf(x.type)!==-1) present[x.type] = (present[x.type]||0)+1; });
    var html = '<button class="sub-chip '+(subFilter==='all'?'active':'')+'" data-f="all">सबै</button>';
    types.forEach(function(t){
      if (!present[t]) return;
      var m = TYPE_META[t]; if (!m) return;
      html += '<button class="sub-chip '+(subFilter===t?'active':'')+'" data-f="'+t+'">'+
        '<i data-lucide="'+m.ic+'" class="w-3 h-3"></i> '+esc(m.label)+
        ' <span style="opacity:.6">('+present[t]+')</span></button>';
    });
    bar.innerHTML = html;
    bar.querySelectorAll('.sub-chip').forEach(function(b){
      b.addEventListener('click', function(){ subFilter = this.dataset.f; renderChips(); renderPane(); });
    });
    if(window.lucide) lucide.createIcons();
  }

  function renderPane(){
    var paneId = 'pane-' + currentTab;
    ['alerts','notices','tools'].forEach(function(t){
      document.getElementById('pane-'+t).classList.toggle('hidden', t !== currentTab);
    });
    if (currentTab === 'tools') { if(window.lucide) lucide.createIcons(); return; }
    var el = document.getElementById(paneId);
    var list = listFor(currentTab);
    if (!list.length) {
      el.innerHTML = '<div class="bg-white rounded-2xl p-6 text-center text-slate-500 shadow-app"><i data-lucide="shield-check" class="w-10 h-10 mx-auto text-emerald-500 mb-2"></i><div class="font-semibold">'+
        (currentTab==='alerts'?'कुनै सक्रिय अलर्ट छैन':'कुनै सूचना छैन')+
        '</div><div class="text-[11px] mt-1">सबै ठीकठाक छ</div></div>';
      if(window.lucide) lucide.createIcons(); return;
    }
    el.innerHTML = list.map(function(a){
      var m = TYPE_META[a.type] || {ic:'bell',cl:'slate',label:a.type};
      var srcBadge = a.source ? '<a href="'+esc(a.source_url||'#')+'" target="_blank" rel="noopener" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded-md text-[9.5px] font-bold uppercase tracking-wide">'+esc(a.source)+(a.source_url?' <i data-lucide="external-link" class="w-2.5 h-2.5"></i>':'')+'</a>' : '';
      return '<div class="bg-white rounded-2xl p-3.5 shadow-app flex gap-3">'+
        '<div class="w-11 h-11 rounded-xl bg-'+m.cl+'-100 text-'+m.cl+'-700 flex items-center justify-center shrink-0"><i data-lucide="'+m.ic+'" class="w-5 h-5"></i></div>'+
        '<div class="flex-1 min-w-0">'+
          '<div class="flex items-start justify-between gap-2"><div class="text-[13px] font-bold text-slate-900">'+esc(a.title)+'</div>'+srcBadge+'</div>'+
          (a.msg?'<div class="text-[12px] text-slate-600 mt-0.5">'+esc(a.msg)+'</div>':'')+
          '<div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1"><span class="px-1.5 py-0.5 rounded bg-'+m.cl+'-50 text-'+m.cl+'-700 font-semibold">'+esc(m.label)+'</span> · '+esc(ago(a.time))+'</div>'+
        '</div>'+
      '</div>';
    }).join('');
    if(window.lucide) lucide.createIcons();
  }

  function updateCounts(){
    document.getElementById('cnt-alerts').textContent  = items.filter(function(x){return ALERT_TYPES.indexOf(x.type)!==-1;}).length;
    document.getElementById('cnt-notices').textContent = items.filter(function(x){return NOTICE_TYPES.indexOf(x.type)!==-1;}).length;
  }

  document.querySelectorAll('.tab-btn').forEach(function(b){
    b.addEventListener('click', function(){
      document.querySelectorAll('.tab-btn').forEach(function(x){ x.classList.remove('active','bg-white','text-slate-900'); x.classList.add('text-slate-600'); });
      this.classList.add('active','bg-white','text-slate-900'); this.classList.remove('text-slate-600');
      currentTab = this.dataset.tab; subFilter = 'all';
      renderChips(); renderPane();
    });
  });

  Promise.all([
    fetch('/api/alerts.php').then(r=>r.json()).catch(()=>({alerts:[]})),
    fetch('/api/weather-alerts.php?type=all').then(r=>r.json()).catch(()=>null),
    fetch('/api/content-overrides.php').then(r=>r.json()).catch(()=>null)
  ]).then(function(res){
    items = items.concat(normalize(res[0] || {}));
    var wx = res[1];
    if (wx && wx.earthquakes) {
      wx.earthquakes.slice(0,10).forEach(function(eq){
        items.push({ type:'earthquake', title:'भूकम्प M'+(eq.magnitude||eq.mag||'?'), msg:(eq.place||eq.location||''), time:eq.time||eq.occurredOn, source:'USGS', source_url:'https://earthquake.usgs.gov/' });
      });
    }
    if (wx && wx.alerts) {
      wx.alerts.slice(0,10).forEach(function(a){
        items.push({ type:a.type||'weather', title:a.title||a.event||'Weather alert', msg:a.description||a.msg||'', time:a.time||a.startedOn, source:a.source||'DHM', source_url:a.source_url||'https://mfd.gov.np/' });
      });
    }
    var ov = res[2] && res[2].overrides;
    if (ov) {
      ['traffic','alert','loadshedding','water','transport','police'].forEach(function(k){
        var sec = ov[k]; if (!sec || !sec.enabled || !sec.items) return;
        sec.items.forEach(function(it){
          items.push({ type:k, title:it.title||k, msg:it.detail||'', time:sec.updatedAt, source:sec.source||'आकाशवाणी Admin', source_url:sec.source_url||it.url||'' });
        });
      });
    }
    var seen={}; items = items.filter(function(x){ var k=(x.title||'')+'|'+(x.time||''); if(seen[k])return false; seen[k]=1; return true; });
    items.sort(function(a,b){ return (Date.parse(b.time||0)||0) - (Date.parse(a.time||0)||0); });
    updateCounts(); renderChips(); renderPane();
  });
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
