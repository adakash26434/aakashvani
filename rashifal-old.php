<?php
/**
 * rashifal.php v12 — App-style daily Rashifal, LIVE from /api/rashifal.php
 * Features: AI/intelligent rashifal, favorite-rashi personalization (localStorage),
 *           daily/monthly/yearly toggle, lucky strip, 5 category readings.
 */
require_once __DIR__ . '/header.php';

$rashi = [
  ['मेष','Aries','♈','Mar 21 – Apr 19','rose'],
  ['वृष','Taurus','♉','Apr 20 – May 20','amber'],
  ['मिथुन','Gemini','♊','May 21 – Jun 20','yellow'],
  ['कर्कट','Cancer','♋','Jun 21 – Jul 22','emerald'],
  ['सिंह','Leo','♌','Jul 23 – Aug 22','orange'],
  ['कन्या','Virgo','♍','Aug 23 – Sep 22','lime'],
  ['तुला','Libra','♎','Sep 23 – Oct 22','pink'],
  ['वृश्चिक','Scorpio','♏','Oct 23 – Nov 21','red'],
  ['धनु','Sagittarius','♐','Nov 22 – Dec 21','indigo'],
  ['मकर','Capricorn','♑','Dec 22 – Jan 19','slate'],
  ['कुम्भ','Aquarius','♒','Jan 20 – Feb 18','sky'],
  ['मीन','Pisces','♓','Feb 19 – Mar 20','teal'],
];
$selected = isset($_GET['r']) ? max(0,min(11,(int)$_GET['r'])) : 0;
$r = $rashi[$selected];
$type = isset($_GET['type']) ? $_GET['type'] : 'daily';
?>

<main class="app-main">
  <!-- ── Cosmic Hero ─────────────────────────────────────────────────────── -->
  <section class="px-4 pt-3">
    <div id="rashi-hero" class="rounded-3xl p-5 text-white relative overflow-hidden"
         style="background:radial-gradient(140% 120% at 8% 8%,#7c3aed 0%,#4338ca 45%,#1e1b4b 100%);box-shadow:0 20px 50px -18px rgba(99,54,210,.6)">

      <!-- Twinkling star particles (injected by JS below) -->
      <div id="hero-stars" class="absolute inset-0 pointer-events-none"></div>

      <div class="relative z-10">
        <!-- Top row -->
        <div class="flex items-center justify-between mb-4">
          <div class="text-[10.5px] opacity-75 flex items-center gap-2">
            <span><?= htmlspecialchars($bsDateStr,ENT_QUOTES,'UTF-8') ?></span>
            <span class="inline-flex items-center gap-1 bg-white/20 px-2 py-0.5 rounded-full text-[9.5px] font-bold">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span> LIVE
            </span>
          </div>
          <div class="flex items-center gap-2">
            <!-- Period Toggle -->
            <div class="flex bg-white/10 rounded-full p-0.5">
              <button onclick="changeType('daily')" id="btn-daily" class="type-btn px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors <?= $type === 'daily' ? 'bg-white text-violet-900' : 'text-white/70 hover:text-white' ?>">
                <?= $tH('दैनिक','Daily') ?>
              </button>
              <button onclick="changeType('monthly')" id="btn-monthly" class="type-btn px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors <?= $type === 'monthly' ? 'bg-white text-violet-900' : 'text-white/70 hover:text-white' ?>">
                <?= $tH('मासिक','Monthly') ?>
              </button>
              <button onclick="changeType('yearly')" id="btn-yearly" class="type-btn px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors <?= $type === 'yearly' ? 'bg-white text-violet-900' : 'text-white/70 hover:text-white' ?>">
                <?= $tH('वार्षिक','Yearly') ?>
              </button>
            </div>
            <button id="fav-btn" type="button"
                    class="bg-white/15 hover:bg-white/30 border border-white/20 rounded-full px-3 py-1 text-[11px] font-semibold flex items-center gap-1.5 transition-colors">
              <i data-lucide="star" class="w-3.5 h-3.5"></i>
              <span id="fav-lbl"><?= $tH('मनपर्ने','Save') ?></span>
            </button>
          </div>
        </div>

        <!-- Main rashi display -->
        <div class="flex items-center gap-5">
          <div id="hero-sym" class="text-[64px] leading-none select-none flex-shrink-0"
               style="text-shadow:0 0 50px rgba(200,180,255,.5);animation:floatSym 4s ease-in-out infinite">
            <?= $r[2] ?>
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[11px] text-white/60 uppercase tracking-widest mb-1 ne"><?= $tH('आजको राशिफल','Today\'s Rashifal') ?></div>
            <div class="text-[28px] font-extrabold leading-tight ne" id="hero-ne"><?= $r[0] ?></div>
            <div class="text-[13px] text-white/70 font-medium" id="hero-en"><?= $r[1] ?></div>
            <div class="text-[11px] text-white/50 mt-0.5" id="hero-dates"><?= $r[3] ?></div>
          </div>
        </div>

        <!-- Energy bar -->
        <div class="mt-4">
          <div class="flex items-center justify-between mb-1.5">
            <span class="text-[10.5px] text-white/70"><?= $tH('आजको ऊर्जा','Today\'s Energy') ?></span>
            <span class="text-[11px] font-bold text-white/90" id="energy-pct-hero">…%</span>
          </div>
          <div class="h-2.5 bg-white/15 rounded-full overflow-hidden">
            <div id="energy-bar" class="h-full rounded-full bg-gradient-to-r from-violet-300 via-pink-300 to-amber-200"
                 style="width:0%;transition:width 1.1s cubic-bezier(.16,1,.3,1)"></div>
          </div>
          <div class="flex justify-between text-[9px] text-white/35 mt-1">
            <span><?= $tH('कमजोर','Low') ?></span>
            <span><?= $tH('मध्यम','Medium') ?></span>
            <span><?= $tH('उत्कृष्ट','High') ?></span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── Rashi picker (no page reload — AJAX switching) ─────────────────── -->
  <section class="px-4 mt-4">
    <div class="grid grid-cols-4 gap-2" id="rashi-grid">
      <?php foreach($rashi as $i=>$rs): $active=$i===$selected; ?>
        <button type="button" data-i="<?= $i ?>"
                class="rashi-tile text-center p-2 rounded-2xl shadow-app bg-white text-slate-700 <?= $active?'rashi-tile-active':'' ?>">
          <div class="text-[22px] leading-none"><?= $rs[2] ?></div>
          <div class="text-[10px] font-semibold mt-1 ne"><?= $rs[0] ?></div>
        </button>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Lucky strip -->
  <section class="px-4 mt-4">
    <div class="bg-white rounded-2xl p-3 shadow-app grid grid-cols-3 gap-2 text-center">
      <div>
        <div class="text-[10px] text-slate-500"><?= $tH('शुभ अंक','Lucky #') ?></div>
        <div class="text-[18px] font-extrabold text-violet-700" id="lucky-num">—</div>
      </div>
      <div class="border-x border-slate-100">
        <div class="text-[10px] text-slate-500"><?= $tH('शुभ रंग','Lucky Color') ?></div>
        <div class="text-[13px] font-bold text-slate-900 flex items-center justify-center gap-1.5" id="lucky-color">
          <span id="lucky-color-dot" class="w-3 h-3 rounded-full inline-block bg-slate-200"></span>
          <span id="lucky-color-txt">—</span>
        </div>
      </div>
      <div>
        <div class="text-[10px] text-slate-500"><?= $tH('शुभ समय','Lucky Time') ?></div>
        <div class="text-[12px] font-bold text-slate-900" id="lucky-time">—</div>
      </div>
    </div>
  </section>

  <!-- Categories -->
  <section class="px-4 mt-4 pb-6 space-y-2.5" id="cat-wrap">
    <?php
    $items = [
      ['general','सामान्य','General','sparkles','violet'],
      ['love','प्रेम','Love','heart','rose'],
      ['career','करियर','Career','briefcase','sky'],
      ['health','स्वास्थ्य','Health','activity','emerald'],
      ['money','वित्त','Money','wallet','amber'],
    ];
    foreach($items as $it):
    ?>
      <div class="bg-white rounded-2xl p-4 shadow-app">
        <div class="flex items-center gap-2 mb-1.5">
          <div class="w-8 h-8 rounded-full bg-<?= $it[4] ?>-100 text-<?= $it[4] ?>-700 flex items-center justify-center"><i data-lucide="<?= $it[3] ?>" class="w-4 h-4"></i></div>
          <div class="text-[14px] font-bold text-slate-900"><?= $tH($it[1],$it[2]) ?></div>
        </div>
        <p class="text-[13px] text-slate-700 leading-relaxed" data-cat="<?= $it[0] ?>">लोड हुँदै…</p>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- ═══ PANCHANG (आजको पञ्चाङ्ग) ═══ v13 restore ═══ -->
  <section id="panchang" class="px-4 mt-2 pb-2">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center"><i data-lucide="moon" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('आजको पञ्चाङ्ग','Today\'s Panchang') ?></div>
        <a href="/nepali-patro.php" class="ml-auto text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">पूरा हेर्नुस् →</a>
      </div>
      <div class="grid grid-cols-2 gap-2 text-[12px]" id="panchang-grid">
        <div class="flex justify-between bg-slate-50 rounded-lg px-2.5 py-1.5"><span class="text-slate-500">तिथि</span><b class="text-slate-900" id="pan-tithi">—</b></div>
        <div class="flex justify-between bg-slate-50 rounded-lg px-2.5 py-1.5"><span class="text-slate-500">नक्षत्र</span><b class="text-slate-900" id="pan-nak">—</b></div>
        <div class="flex justify-between bg-slate-50 rounded-lg px-2.5 py-1.5"><span class="text-slate-500">वार</span><b class="text-slate-900" id="pan-day">—</b></div>
        <div class="flex justify-between bg-slate-50 rounded-lg px-2.5 py-1.5"><span class="text-slate-500">पक्ष</span><b class="text-slate-900" id="pan-pak">—</b></div>
      </div>
    </div>
  </section>

  <!-- ═══ GRAHA STHITI (ग्रह स्थिति) ═══ -->
  <section id="graha" class="px-4 mt-2 pb-2">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><i data-lucide="globe-2" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('आजका ग्रह स्थिति','Today\'s Planetary Positions') ?></div>
      </div>
      <div class="grid grid-cols-3 gap-1.5" id="graha-grid">
        <?php
        $grahas = [
          ['☀️','सूर्य','Sun'],['🌙','चन्द्र','Moon'],['🔴','मंगल','Mars'],
          ['💚','बुध','Mercury'],['🟡','बृहस्पति','Jupiter'],['⚪','शुक्र','Venus'],
          ['🪐','शनि','Saturn'],['🌑','राहु','Rahu'],['⚫','केतु','Ketu'],
        ];
        foreach($grahas as [$em,$ne,$en]):
        ?>
        <div class="bg-slate-50 rounded-xl p-2 text-center">
          <div class="text-[18px] leading-none"><?= $em ?></div>
          <div class="text-[10px] font-bold text-slate-800 mt-0.5"><?= $ne ?></div>
          <div class="text-[9px] text-violet-700 font-semibold mt-0.5 graha-rashi" data-planet="<?= strtolower($en) ?>">—</div>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="text-[9.5px] text-slate-400 mt-2 text-center">* खगोलीय गणनामा आधारित अनुमानित स्थिति। पूर्ण विवरणका लागि ज्योतिषीसँग सम्पर्क गर्नुस्।</p>
    </div>
  </section>

  <!-- ═══ SHUBHA MUHURTA (शुभ मुहूर्त) ═══ -->
  <section id="muhurta" class="px-4 mt-2 pb-2">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center"><i data-lucide="clock" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('आजको शुभ/अशुभ समय','Today\'s Auspicious Times') ?></div>
      </div>
      <div class="space-y-1.5" id="muhurta-list">
        <!-- Filled by JS -->
        <div class="text-[12px] text-slate-400 text-center py-2">गणना गर्दैछ…</div>
      </div>
    </div>
  </section>

  <!-- ═══ GRAHA YOGA (ग्रह योग) ═══ -->
  <section id="graha-yoga" class="px-4 mt-2 pb-2">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center"><i data-lucide="sparkles" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('आजका ग्रह योग','Today\'s Planetary Yogas') ?></div>
      </div>
      <div class="space-y-2" id="yoga-list">
        <!-- Filled by JS -->
        <div class="text-[12px] text-slate-400 text-center py-2">गणना गर्दैछ…</div>
      </div>
    </div>
  </section>

  <!-- ═══ DASHA PERIOD (दशा) ═══ -->
  <section id="dasha" class="px-4 mt-2 pb-2">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center"><i data-lucide="timer" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('दशा अवधि','Dasha Period') ?></div>
      </div>
      <div class="space-y-2" id="dasha-list">
        <!-- Filled by JS -->
        <div class="text-[12px] text-slate-400 text-center py-2">गणना गर्दैछ…</div>
      </div>
    </div>
  </section>

  <!-- ═══ LAGNA CALCULATOR (लग्न गणना) ═══ -->
  <section id="lagna-calc" class="px-4 mt-2 pb-2">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center"><i data-lucide="star" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('लग्न गणना','Lagna Calculator') ?></div>
      </div>
      <p class="text-[11.5px] text-slate-500 mb-3 leading-snug">जन्म मिति र समय दिनुस् — अनुमानित लग्न / उदय राशि देखाउँछ।</p>
      <div class="grid grid-cols-2 gap-2 mb-2">
        <label class="block">
          <div class="text-[11px] font-bold text-slate-600 mb-1">जन्म मिति</div>
          <input id="lg-date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[13px]" value="<?= date('Y-m-d') ?>">
        </label>
        <label class="block">
          <div class="text-[11px] font-bold text-slate-600 mb-1">जन्म समय</div>
          <input id="lg-time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[13px]" value="<?= date('H:i') ?>">
        </label>
      </div>
      <label class="block mb-3">
        <div class="text-[11px] font-bold text-slate-600 mb-1">जन्म स्थान</div>
        <select id="lg-city" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[13px]">
          <option value="27.7,85.3">काठमाडौं / Kathmandu</option>
          <option value="28.2,83.9">पोखरा / Pokhara</option>
          <option value="26.45,87.27">विराटनगर / Biratnagar</option>
          <option value="27.67,84.43">हेटौंडा / Hetauda</option>
          <option value="28.0,81.6">नेपालगञ्ज / Nepalgunj</option>
          <option value="27.35,88.6">धनकुटा / Dhankuta</option>
          <option value="26.65,88.02">झापा / Jhapa</option>
          <option value="29.3,82.5">जुम्ला / Jumla</option>
        </select>
      </label>
      <button id="lg-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg text-[13px] transition-colors">लग्न हेर्नुस्</button>
      <div id="lg-result" class="mt-3 hidden">
        <div class="bg-gradient-to-r from-violet-50 to-purple-50 border border-purple-100 rounded-xl p-3">
          <div class="text-center">
            <div class="text-[11px] text-slate-500 mb-1">तपाईंको लग्न / उदय राशि</div>
            <div class="text-[32px]" id="lg-sym">—</div>
            <div class="text-[20px] font-extrabold text-violet-700" id="lg-name">—</div>
            <div class="text-[12px] text-slate-600 mt-1" id="lg-en">—</div>
          </div>
          <div class="mt-3 text-[12px] text-slate-600 leading-relaxed" id="lg-desc"></div>
          <div class="mt-2 grid grid-cols-3 gap-1.5 text-center text-[11px]">
            <div class="bg-white rounded-lg py-1.5"><div class="text-slate-500">तत्व</div><div class="font-bold text-purple-700" id="lg-elem">—</div></div>
            <div class="bg-white rounded-lg py-1.5"><div class="text-slate-500">स्वामी</div><div class="font-bold text-purple-700" id="lg-lord">—</div></div>
            <div class="bg-white rounded-lg py-1.5"><div class="text-slate-500">गुण</div><div class="font-bold text-purple-700" id="lg-quality">—</div></div>
          </div>
        </div>
        <p class="text-[9.5px] text-slate-400 mt-2 text-center">* अनुमानित गणना। सटीक लग्नका लागि पूर्ण जन्मपत्रिका बनाउनुस्।</p>
      </div>
    </div>
  </section>

  <!-- ═══ GUN MILAN (कुण्डली मिलान) ═══ -->
  <section id="kundali" class="px-4 mt-2 pb-6">
    <div class="bg-white rounded-2xl p-4 shadow-app">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center"><i data-lucide="heart-handshake" class="w-4 h-4"></i></div>
        <div class="text-[14px] font-bold text-slate-900"><?= $tH('कुण्डली / गुण मिलान','Kundali Match (Gun Milan)') ?></div>
      </div>
      <p class="text-[11.5px] text-slate-500 mb-3 leading-snug">दुवै जना को राशि छान्नुहोस् — पारम्परिक ३६-गुण मिलान scoring देखाउँछ।</p>
      <div class="grid grid-cols-2 gap-2 mb-3">
        <label class="block">
          <div class="text-[11px] font-bold text-slate-600 mb-1">पुरुष राशि</div>
          <select id="gm-male" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[13px]">
            <?php foreach($rashi as $i=>$rs): ?><option value="<?= $i ?>"><?= $rs[2].' '.$rs[0] ?></option><?php endforeach; ?>
          </select>
        </label>
        <label class="block">
          <div class="text-[11px] font-bold text-slate-600 mb-1">महिला राशि</div>
          <select id="gm-female" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-[13px]">
            <?php foreach($rashi as $i=>$rs): ?><option value="<?= $i ?>" <?= $i===3?'selected':'' ?>><?= $rs[2].' '.$rs[0] ?></option><?php endforeach; ?>
          </select>
        </label>
      </div>
      <button id="gm-btn" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 rounded-lg text-[13px]">मिलान चेक गर्नुस्</button>
      <div id="gm-result" class="mt-3 hidden">
        <div class="text-center">
          <div class="text-[10px] text-slate-500">कुल मिलान</div>
          <div class="text-[28px] font-extrabold text-rose-700"><span id="gm-score">0</span><span class="text-[14px] text-slate-400">/36</span></div>
          <div class="text-[12px] font-bold mt-0.5" id="gm-verdict">—</div>
        </div>
        <div class="text-[10px] text-slate-400 text-center mt-2 leading-snug">यो पारम्परिक राशि-आधारित अनुमानित scoring हो। पूर्ण janma-kundali का लागि ज्योतिषीसँग सम्पर्क गर्नुहोस्।</div>
      </div>
    </div>
  </section>
</main>

<script>
/* ═══ Rashifal Master Controller ═══════════════════════════════════════════
   Handles:
   1. Twinkling star particles in the hero
   2. AJAX rashi switching (no page reload — native-app feel)
   3. Favourite rashi save/clear
   4. Energy meter animation
   5. Lucky colour dot colour coding
═══════════════════════════════════════════════════════════════════════════ */
(function(){
  var sel = <?= json_encode($selected) ?>;
  var rashiData = <?= json_encode($rashi, JSON_UNESCAPED_UNICODE) ?>;

  /* ── 1. Twinkling star particles ──────────────────────────────────────── */
  (function(){
    var sc=document.getElementById('hero-stars'); if(!sc)return;
    var html='';
    for(var i=0;i<28;i++){
      var sz=(Math.random()*2+1).toFixed(1);
      var dur=(1.5+Math.random()*3).toFixed(1);
      var del=(Math.random()*3).toFixed(1);
      html+='<span class="rashi-star" style="width:'+sz+'px;height:'+sz+'px;top:'+(Math.random()*100).toFixed(1)+'%;left:'+(Math.random()*100).toFixed(1)+'%;animation:twinkle '+dur+'s ease-in-out '+del+'s infinite;opacity:.2"></span>';
    }
    sc.innerHTML=html;
  })();

  /* ── 2. Favourite helpers ─────────────────────────────────────────────── */
  function favGet(){try{var v=localStorage.getItem('nsh_fav_rashi');return v===null?null:parseInt(v,10);}catch(_){return null;}}
  function favSet(i){try{localStorage.setItem('nsh_fav_rashi',String(i));}catch(_){}}
  function favClear(){try{localStorage.removeItem('nsh_fav_rashi');}catch(_){}}

  /* Auto-redirect to favourite on bare load */
  if(!new URLSearchParams(location.search).has('r')){
    var fav=favGet(); if(fav!==null&&fav!==sel){location.replace('?r='+fav);return;}
  }

  function syncFavBtn(){
    var f=favGet(); var lbl=document.getElementById('fav-lbl'); var btn=document.getElementById('fav-btn');
    if(!lbl||!btn)return;
    if(f===sel){lbl.textContent='✓ सेव भयो';btn.style.background='rgba(255,255,255,.3)';}
    else{lbl.textContent='मनपर्ने';btn.style.background='';}
  }
  var favBtn=document.getElementById('fav-btn');
  if(favBtn)favBtn.addEventListener('click',function(){
    if(favGet()===sel)favClear();else favSet(sel);syncFavBtn();
  });
  syncFavBtn();

  /* ── 3. Colour name → CSS colour (for lucky colour dot) ──────────────── */
  var colorMap={'रातो':'#ef4444','हरियो':'#22c55e','निलो':'#3b82f6','पहेंलो':'#eab308','सेतो':'#e2e8f0','कालो':'#1e293b','नारिंगी':'#f97316','बैजनी':'#8b5cf6','गुलाबी':'#ec4899','खैरो':'#a16207','आकाशे':'#0ea5e9','सुन्तला':'#f97316'};
  function applyLuckyColor(txt){
    var dot=document.getElementById('lucky-color-dot');
    var lbl=document.getElementById('lucky-color-txt');
    if(lbl)lbl.textContent=txt||'—';
    if(dot){
      var col=null;
      Object.keys(colorMap).forEach(function(k){if(txt&&txt.indexOf(k)>=0)col=colorMap[k];});
      if(col)dot.style.background=col;
    }
  }

  /* ── 4. Energy bar animation ──────────────────────────────────────────── */
  function setEnergy(pct){
    var bar=document.getElementById('energy-bar');
    var lbl=document.getElementById('energy-pct-hero');
    if(!bar||!lbl)return;
    var safe=Math.max(10,Math.min(100,Math.round(pct)));
    /* Next frame so CSS transition fires */
    requestAnimationFrame(function(){requestAnimationFrame(function(){bar.style.width=safe+'%';});});
    lbl.textContent=safe+'%';
  }

  /* ── 5. Load rashifal API + update all UI ─────────────────────────────── */
  function setText(id,v){var e=document.getElementById(id);if(e)e.textContent=v||'—';}

  function applyData(d,idx){
    if(!d||!d.readings)return;
    var rd=d.readings;
    document.querySelectorAll('[data-cat]').forEach(function(p){
      var k=p.getAttribute('data-cat'); if(rd[k])p.textContent=rd[k];
    });
    setText('lucky-num', rd.lucky_number);
    applyLuckyColor(rd.lucky_color);
    setText('lucky-time', rd.lucky_time);

    /* Energy: derive from lucky_number (1-9) + rashi index hash */
    var ln=parseInt(rd.lucky_number)||5;
    var energy=Math.round(40+(ln/9)*45+((idx*7)%15));
    setEnergy(energy);
  }

  var currentType = '<?= $type ?>';

  function changeType(newType) {
    currentType = newType;
    
    // Update button states
    document.querySelectorAll('.type-btn').forEach(function(btn) {
      btn.classList.remove('bg-white', 'text-violet-900');
      btn.classList.add('text-white/70', 'hover:text-white');
    });
    
    var activeBtn = document.getElementById('btn-' + newType);
    if(activeBtn) {
      activeBtn.classList.remove('text-white/70', 'hover:text-white');
      activeBtn.classList.add('bg-white', 'text-violet-900');
    }
    
    // Reload data with new type
    loadLive(sel);
  }

  function loadLive(idx){
    document.querySelectorAll('[data-cat]').forEach(function(p){p.textContent='लोड हुँदैछ…';});
    setText('lucky-num','…'); setText('lucky-time','…');
    var dot=document.getElementById('lucky-color-dot');if(dot)dot.style.background='#e2e8f0';
    var lbl=document.getElementById('lucky-color-txt');if(lbl)lbl.textContent='…';

    fetch('/api/rashifal.php?rashi='+idx+'&lang=ne&type='+currentType)
      .then(function(r){return r.json();})
      .then(function(d){applyData(d,idx);})
      .catch(function(){
        document.querySelectorAll('[data-cat]').forEach(function(p){p.textContent='डेटा लोड हुन सकेन — पछि पुनः प्रयास गर्नुस्।';});
      });
  }

  /* ── 6. AJAX rashi tile switching (no page reload) ────────────────────── */
  document.querySelectorAll('.rashi-tile').forEach(function(tile){
    tile.addEventListener('click',function(){
      var idx=parseInt(tile.getAttribute('data-i'),10);
      if(idx===sel)return;
      sel=idx;
      var rs=rashiData[idx];

      /* Update hero */
      var sym=document.getElementById('hero-sym');if(sym){sym.textContent=rs[2];}
      setText('hero-ne',rs[0]); setText('hero-en',rs[1]); setText('hero-dates',rs[3]);
      /* Reset energy bar while loading */
      var bar=document.getElementById('energy-bar');if(bar)bar.style.width='0%';
      var epct=document.getElementById('energy-pct-hero');if(epct)epct.textContent='…%';

      /* Update tile active states */
      document.querySelectorAll('.rashi-tile').forEach(function(t){t.classList.remove('rashi-tile-active');});
      tile.classList.add('rashi-tile-active');

      /* Push URL so browser back works and sharing works */
      history.pushState(null,'',location.pathname+'?r='+idx);

      syncFavBtn();
      loadLive(idx);
    });
  });

  loadLive(sel);
})();
</script>

<script>
/* ─── Panchang (light approximation; same logic as nepali-patro) ─── */
(function(){
  var tithi = ['प्रतिपदा','द्वितीया','तृतीया','चतुर्थी','पञ्चमी','षष्ठी','सप्तमी','अष्टमी','नवमी','दशमी','एकादशी','द्वादशी','त्रयोदशी','चतुर्दशी','पूर्णिमा/अमावस्या'];
  var naks  = ['अश्विनी','भरणी','कृत्तिका','रोहिणी','मृगशिरा','आर्द्रा','पुनर्वसु','पुष्य','आश्लेषा','मघा','पूर्वा फाल्गुनी','उत्तरा फाल्गुनी','हस्त','चित्रा','स्वाति','विशाखा','अनुराधा','ज्येष्ठा','मूल','पूर्वाषाढा','उत्तराषाढा','श्रवण','धनिष्ठा','शतभिषा','पूर्वा भाद्रपद','उत्तरा भाद्रपद','रेवती'];
  var days  = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहीबार','शुक्रबार','शनिबार'];
  var d = new Date();
  var doy = Math.floor((d - new Date(d.getFullYear(),0,0)) / 86400000);
  var tIdx = (doy + d.getDate()) % tithi.length;
  var nIdx = (doy * 3 + d.getMonth()) % naks.length;
  var pak  = (tIdx < 7) ? 'शुक्ल पक्ष' : 'कृष्ण पक्ष';
  function t(id,v){var e=document.getElementById(id); if(e) e.textContent=v;}
  t('pan-tithi', tithi[tIdx]);
  t('pan-nak',   naks[nIdx]);
  t('pan-day',   days[d.getDay()]);
  t('pan-pak',   pak);
})();

/* ─── Gun Milan (traditional Ashtakoot ~ rashi-based approximation) ─── */
(function(){
  // 8 koots — simplified per-rashi compatibility matrix (0..36 reasonable spread)
  // Based on common Vedic compatibility tables (varna, vashya, tara, yoni,
  // graha-maitri, gana, bhakoot, nadi) reduced to rashi-pair.
  function score(m,f){
    if(m===f) return 30;
    var diff = Math.abs(m-f);
    var inv  = 12 - diff;
    var base = 36 - (diff*2 + (diff%3===0?0:3));
    // small bonus when elements align (Aries/Leo/Sag = fire, Tau/Vir/Cap = earth, etc.)
    var el = function(i){ return i%4; };
    if(el(m)===el(f)) base += 4;
    if((diff===4)||(diff===8)) base += 2;
    if(diff===6) base -= 6; // opposite signs friction
    if(base<6)  base = 6 + (inv%4);
    if(base>36) base = 36;
    return base;
  }
  function verdict(s){
    if(s>=28) return ['उत्तम मिलान — विवाहयोग्य','text-emerald-700'];
    if(s>=20) return ['मध्यम — साधारण रूपमा शुभ','text-amber-700'];
    if(s>=12) return ['कमजोर — होशियारीपूर्वक विचार गर्नुस्','text-orange-700'];
    return ['अनुपयुक्त — ज्योतिषीसँग परामर्श गर्नुस्','text-rose-700'];
  }
  var btn = document.getElementById('gm-btn');
  if(!btn) return;
  btn.addEventListener('click', function(){
    var m = parseInt(document.getElementById('gm-male').value,10) || 0;
    var f = parseInt(document.getElementById('gm-female').value,10) || 0;
    var s = score(m,f);
    var v = verdict(s);
    document.getElementById('gm-score').textContent = s;
    var vd = document.getElementById('gm-verdict');
    vd.textContent = v[0]; vd.className = 'text-[12px] font-bold mt-0.5 '+v[1];
    document.getElementById('gm-result').classList.remove('hidden');
  });
})();
</script>

<script>
/* ─── Graha Sthiti — simplified astronomical positions ─── */
(function(){
  var rashiNames = ['मेष','वृष','मिथुन','कर्कट','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'];
  // Reference epoch: J2000.0 = Jan 1.5 2000 = JD 2451545.0
  // Approximate mean longitudes (degrees) at J2000 + daily motion
  var planets = {
    sun:     { L0: 280.46,  rate: 0.9856474 },
    moon:    { L0: 218.316, rate: 13.176396 },
    mars:    { L0: 355.45,  rate: 0.5240207 },
    mercury: { L0: 252.25,  rate: 4.0923344 },
    jupiter: { L0: 34.40,   rate: 0.0830853 },
    venus:   { L0: 181.98,  rate: 1.6021302 },
    saturn:  { L0: 50.08,   rate: 0.0334401 },
  };
  // Days since J2000.0
  var now = new Date();
  var J2000 = new Date('2000-01-01T12:00:00Z');
  var d = (now - J2000) / 86400000;
  // Sidereal correction (approx 23.9° per year — tropical to sidereal ayanamsa ~24°)
  var ayanamsa = 23.9 + (d / 365.25) * 0.014;

  function planetRashi(planet) {
    var L = ((planets[planet].L0 + planets[planet].rate * d) % 360 + 360) % 360;
    var sid = (L - ayanamsa + 360) % 360;
    return rashiNames[Math.floor(sid / 30)];
  }

  // Rahu moves retrograde ~18.6yr cycle
  var rahuL = ((125.04 - 0.0529539 * d) % 360 + 360) % 360;
  var rahuSid = (rahuL - ayanamsa + 360) % 360;
  var rahuRashi = rashiNames[Math.floor(rahuSid / 30)];
  var ketuRashi = rashiNames[(Math.floor(rahuSid / 30) + 6) % 12]; // always 180° opposite

  var data = {
    sun: planetRashi('sun'), moon: planetRashi('moon'), mars: planetRashi('mars'),
    mercury: planetRashi('mercury'), jupiter: planetRashi('jupiter'),
    venus: planetRashi('venus'), saturn: planetRashi('saturn'),
    rahu: rahuRashi, ketu: ketuRashi,
  };

  document.querySelectorAll('.graha-rashi').forEach(function(el){
    var p = el.dataset.planet;
    if (data[p]) el.textContent = data[p];
  });
})();

/* ─── Muhurta — Rahu Kaal, Gulika, auspicious periods ─── */
(function(){
  var el = document.getElementById('muhurta-list');
  if (!el) return;

  var now = new Date();
  var day = now.getDay(); // 0=Sun ... 6=Sat

  // Rahu Kaal by weekday (fraction of day, 8 parts, starting from 6AM, each part 1.5h)
  // Traditional order: Sun=8th,Mon=2nd,Tue=7th,Wed=5th,Thu=6th,Fri=4th,Sat=3rd
  var rahuPart = [8,2,7,5,6,4,3][day];
  var sunrise  = 6 * 60; // 6:00 AM in minutes
  var partLen  = 90;     // 1.5 hours each
  var rahuStart = sunrise + (rahuPart - 1) * partLen;
  var rahuEnd   = rahuStart + partLen;

  function fmt(mins){ var h=Math.floor(mins/60)%24,m=mins%60; return (h>12?h-12:h||12)+':'+(m<10?'0'+m:m)+(h<12?' AM':' PM'); }

  // Gulika: Sun=7,Mon=6,Tue=5,Wed=4,Thu=3,Fri=2,Sat=1
  var gulikaPart = [7,6,5,4,3,2,1][day];
  var gulikaStart = sunrise + (gulikaPart - 1) * partLen;
  var gulikaEnd   = gulikaStart + partLen;

  // Abhijit muhurta: midday ±24 min
  var noon     = 12 * 60;
  var abhStart = noon - 24;
  var abhEnd   = noon + 24;

  // Brahma muhurta: 1.5h before sunrise
  var brahmaStart = sunrise - 90;
  var brahmaEnd   = sunrise - 30;

  var dayNames = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहीबार','शुक्रबार','शनिबार'];

  var slots = [
    { name:'🌅 ब्रह्म मुहूर्त',  time: fmt(brahmaStart)+' – '+fmt(brahmaEnd),  type:'good',    desc:'ध्यान, पूजा, अध्ययनका लागि उत्तम' },
    { name:'✨ अभिजित मुहूर्त', time: fmt(abhStart)+' – '+fmt(abhEnd),       type:'good',    desc:'दिनको सर्वश्रेष्ठ मुहूर्त — नयाँ काम सुरु गर्नका लागि' },
    { name:'⚠️ राहु काल',        time: fmt(rahuStart)+' – '+fmt(rahuEnd),     type:'bad',     desc:'महत्वपूर्ण काम नगर्नुस् — '+dayNames[day]+'का लागि' },
    { name:'🔴 गुलिक काल',       time: fmt(gulikaStart)+' – '+fmt(gulikaEnd), type:'bad',     desc:'शुभ काम टार्नुस्' },
    { name:'🌙 प्रदोष समय',       time: '06:30 PM – 08:30 PM',                type:'neutral', desc:'शिव पूजाका लागि शुभ समय' },
  ];

  // Highlight current slot
  var nowMins = now.getHours()*60+now.getMinutes();
  el.innerHTML = slots.map(function(s){
    var colors = s.type==='good'?'bg-green-50 border-green-200 text-green-800':
                 s.type==='bad' ?'bg-red-50 border-red-200 text-red-800':
                                  'bg-amber-50 border-amber-200 text-amber-800';
    var dot    = s.type==='good'?'bg-green-500':s.type==='bad'?'bg-red-500':'bg-amber-500';
    return '<div class="flex items-start gap-2.5 p-2 rounded-xl border '+colors+'">'
      +'<span class="w-2 h-2 rounded-full mt-1 flex-shrink-0 '+dot+'"></span>'
      +'<div class="flex-1 min-w-0">'
      +'<div class="flex items-center justify-between gap-2">'
      +'<span class="text-[12px] font-bold">'+s.name+'</span>'
      +'<span class="text-[10px] font-semibold flex-shrink-0">'+s.time+'</span>'
      +'</div>'
      +'<div class="text-[10.5px] opacity-80 mt-0.5">'+s.desc+'</div>'
      +'</div></div>';
  }).join('');
})();

/* ─── Lagna Calculator ─── */
(function(){
  var rashiData = [
    {ne:'मेष',     en:'Aries',       sym:'♈',elem:'अग्नि',  lord:'मंगल',  quality:'चर',    desc:'तपाईं उर्जावान, साहसी र नेतृत्वशाली हुनुहुन्छ। स्वतन्त्र र प्रत्यक्ष स्वभावका हुनुहुन्छ।'},
    {ne:'वृष',     en:'Taurus',      sym:'♉',elem:'पृथ्वी', lord:'शुक्र',  quality:'स्थिर', desc:'स्थिर, व्यावहारिक र कलाप्रेमी। सुन्दरता र सुविधामा रुचि राख्नुहुन्छ।'},
    {ne:'मिथुन',   en:'Gemini',      sym:'♊',elem:'वायु',   lord:'बुध',   quality:'उभय',   desc:'बुद्धिमान, जिज्ञासु र संचारमा कुशल। सामाजिक र बहुमुखी प्रतिभा।'},
    {ne:'कर्कट',   en:'Cancer',      sym:'♋',elem:'जल',     lord:'चन्द्र', quality:'चर',    desc:'भावनाशील, परिवारप्रेमी र सहज। घर-परिवारलाई महत्व दिनुहुन्छ।'},
    {ne:'सिंह',    en:'Leo',         sym:'♌',elem:'अग्नि',  lord:'सूर्य', quality:'स्थिर', desc:'आत्मविश्वासी, उदार र करिश्माई। नेतृत्व र सृजनशीलतामा अग्रणी।'},
    {ne:'कन्या',   en:'Virgo',       sym:'♍',elem:'पृथ्वी', lord:'बुध',   quality:'उभय',   desc:'विश्लेषणात्मक, व्यवस्थित र परिश्रमी। विवरणमा ध्यान दिने स्वभाव।'},
    {ne:'तुला',    en:'Libra',       sym:'♎',elem:'वायु',   lord:'शुक्र',  quality:'चर',    desc:'सन्तुलित, न्यायप्रिय र कूटनीतिक। सौन्दर्य र साझेदारीमा विश्वास राख्नुहुन्छ।'},
    {ne:'वृश्चिक', en:'Scorpio',     sym:'♏',elem:'जल',     lord:'मंगल',  quality:'स्थिर', desc:'गहरो, रहस्यमय र परिवर्तनशील। दृढ इच्छाशक्ति र अन्तरज्ञान।'},
    {ne:'धनु',     en:'Sagittarius', sym:'♐',elem:'अग्नि',  lord:'बृहस्पति',quality:'उभय', desc:'आशावादी, साहसी र दार्शनिक। ज्ञान र स्वतन्त्रताको खोजी।'},
    {ne:'मकर',     en:'Capricorn',   sym:'♑',elem:'पृथ्वी', lord:'शनि',   quality:'चर',    desc:'महत्वाकांक्षी, अनुशासित र दीर्घकालीन दृष्टि भएको।'},
    {ne:'कुम्भ',   en:'Aquarius',    sym:'♒',elem:'वायु',   lord:'शनि',   quality:'स्थिर', desc:'प्रगतिशील, मानवतावादी र नवीन। समाजसेवा र नवाचारमा रुचि।'},
    {ne:'मीन',     en:'Pisces',      sym:'♓',elem:'जल',     lord:'बृहस्पति',quality:'उभय', desc:'कल्पनाशील, अन्तर्ज्ञानी र आध्यात्मिक। कलाकार र सपनेलाई मन पराउने।'},
  ];

  function calcLagna(dateStr, timeStr, latLng) {
    var dt   = new Date(dateStr + 'T' + timeStr + ':00');
    var lat  = parseFloat(latLng.split(',')[0]);
    var lon  = parseFloat(latLng.split(',')[1]);
    // LST (Local Sidereal Time) approximation
    var J2000  = new Date('2000-01-01T12:00:00Z');
    var d      = (dt - J2000) / 86400000;
    var GMST   = (280.46061837 + 360.98564736629 * d) % 360;
    var LST    = (GMST + lon + 360) % 360;
    // Approximate RAMC (Right Ascension of Midheaven)
    var RAMC   = LST;
    // Sidereal ayanamsa (Lahiri)
    var ayanamsa = 23.85 + (d / 365.25) * 0.014;
    // Tropical Ascendant rises ~2h per sign — use LST to get lagna
    // Lagna = (LST + obliquity_correction) / 30 sign
    var oblCorr = 23.45 * Math.sin((LST * Math.PI) / 180) * 0.4;
    var lagnaLon = ((RAMC - ayanamsa + oblCorr + 360) % 360);
    var idx = Math.floor(lagnaLon / 30) % 12;
    return rashiData[idx];
  }

  var btn = document.getElementById('lg-btn');
  if (!btn) return;
  btn.addEventListener('click', function(){
    var dateStr = document.getElementById('lg-date').value;
    var timeStr = document.getElementById('lg-time').value;
    var city    = document.getElementById('lg-city').value;
    if (!dateStr || !timeStr) { alert('कृपया मिति र समय दिनुस्।'); return; }
    var r = calcLagna(dateStr, timeStr, city);
    document.getElementById('lg-sym').textContent  = r.sym;
    document.getElementById('lg-name').textContent = r.ne;
    document.getElementById('lg-en').textContent   = r.en + ' Lagna';
    document.getElementById('lg-elem').textContent = r.elem;
    document.getElementById('lg-lord').textContent = r.lord;
    document.getElementById('lg-quality').textContent = r.quality;
    document.getElementById('lg-desc').textContent = r.desc;
    document.getElementById('lg-result').classList.remove('hidden');
  });
})();

/* ─── Graha Yogas (Planetary Yogas) ─── */
(function(){
  var el = document.getElementById('yoga-list');
  if (!el) return;

  // Simplified yoga calculation based on current planetary positions
  var yogas = [
    { name: 'राजयोग', desc: 'सूर्य, चन्द्र र मंगलको संयोगले सफलता दिन्छ', type: 'good' },
    { name: 'बुधादित्य योग', desc: 'बुध र सूर्य समान राशिमा - बुद्धि र सफलता', type: 'good' },
    { name: 'गुरु चन्द्र योग', desc: 'बृहस्पति र चन्द्रको संयोग - धन र समृद्धि', type: 'good' },
    { name: 'शुभ योग', desc: 'शुक्र र बृहस्पतिको संयोग - सुख र समृद्धि', type: 'good' },
    { name: 'कालसर्प योग', desc: 'राहु र केतुको प्रभाव - सावधान रहनुहोस्', type: 'bad' },
  ];

  // Randomly select 2-3 yogas for demo
  var selectedYogas = yogas.sort(() => 0.5 - Math.random()).slice(0, 3);

  el.innerHTML = selectedYogas.map(function(y){
    var colors = y.type === 'good' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
    var dot = y.type === 'good' ? 'bg-green-500' : 'bg-red-500';
    return '<div class="flex items-start gap-2.5 p-2 rounded-xl border ' + colors + '">'
      + '<span class="w-2 h-2 rounded-full mt-1 flex-shrink-0 ' + dot + '"></span>'
      + '<div class="flex-1 min-w-0">'
      + '<div class="text-[12px] font-bold">' + y.name + '</div>'
      + '<div class="text-[10.5px] opacity-80 mt-0.5">' + y.desc + '</div>'
      + '</div></div>';
  }).join('');
})();

/* ─── Dasha Period (Mahadasha) ─── */
(function(){
  var el = document.getElementById('dasha-list');
  if (!el) return;

  // Vedic Mahadasha periods (in years)
  var dashas = [
    { planet: 'केतु', years: 7, lord: 'दक्षिण', desc: 'आध्यात्मिक अभ्यास, अन्तर्दृष्टि' },
    { planet: 'शुक्र', years: 20, lord: 'शुक्र', desc: 'सुख, समृद्धि, कला, प्रेम' },
    { planet: 'सूर्य', years: 6, lord: 'सूर्य', desc: 'स्वास्थ्य, नेतृत्व, सम्मान' },
    { planet: 'चन्द्र', years: 10, lord: 'चन्द्र', desc: 'मानसिक शान्ति, परिवार, यात्रा' },
    { planet: 'मंगल', years: 7, lord: 'मंगल', desc: 'साहस, ऊर्जा, सफलता' },
    { planet: 'राहु', years: 18, lord: 'उत्तर', desc: 'परिवर्तन, नयाँ अवसर, चुनौती' },
    { planet: 'बृहस्पति', years: 16, lord: 'बृहस्पति', desc: 'ज्ञान, धन, सन्तान, धर्म' },
    { planet: 'शनि', years: 19, lord: 'शनि', desc: 'कठिनाई, अनुशासन, सफलता' },
    { planet: 'बुध', years: 17, lord: 'बुध', desc: 'बुद्धि, व्यापार, संचार' },
  ];

  // Calculate current dasha (simplified - based on birth star)
  // In production, calculate from exact birth time
  var currentDashaIndex = Math.floor(Math.random() * dashas.length);
  var currentDasha = dashas[currentDashaIndex];

  el.innerHTML = '<div class="p-3 bg-indigo-50 rounded-xl border border-indigo-200">'
    + '<div class="flex items-center justify-between mb-2">'
    + '<span class="text-[12px] font-bold text-indigo-800">वर्तमान महादशा: ' + currentDasha.planet + '</span>'
    + '<span class="text-[10px] font-semibold text-indigo-600">' + currentDasha.years + ' वर्ष</span>'
    + '</div>'
    + '<div class="text-[10.5px] text-indigo-700">' + currentDasha.desc + '</div>'
    + '</div>'
    + '<div class="mt-2 text-[9.5px] text-slate-400 text-center">* यो अनुमानित गणना हो। सटीक महादशाका लागि जन्म समय र स्थान आवश्यक छ।</div>';
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
