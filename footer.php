<?php
/**
 * आकाशवाणी — footer.php v10 (APP REDESIGN)
 * Closes the center phone-column, renders the right info rail (desktop only),
 * closes <main>, and renders a compact app-style footer + PWA banners.
 */
$lang = function_exists('siteLang') ? siteLang() : 'ne';
$tF = fn($ne,$en) => $lang==='ne' ? $ne : $en;

/* ─── Sunrise / Sunset (Kathmandu) — PHP native, no external API ─── */
$_sunInfo = @date_sun_info(time(), 27.7172, 85.3240);
$ktmTz = new DateTimeZone('Asia/Kathmandu');
$_fmt = function($ts) use ($ktmTz){ if(!$ts) return '—'; $d=new DateTime('@'.$ts); $d->setTimezone($ktmTz); return $d->format('g:i A'); };
$sunriseStr = $_fmt($_sunInfo['sunrise'] ?? 0);
$sunsetStr  = $_fmt($_sunInfo['sunset']  ?? 0);

/* ─── Today's festival / tithi (simple BS lookup table) ─── */
$_festivals = [
  '1-1'=>'नयाँ वर्ष (Naya Barsha)', '1-11'=>'लोकतन्त्र दिवस',
  '2-15'=>'बुद्ध जयन्ती', '3-15'=>'गाईजात्रा',
  '4-1'=>'जनै पूर्णिमा', '4-3'=>'कृष्ण जन्माष्टमी', '4-17'=>'तीज',
  '5-15'=>'इन्द्रजात्रा', '6-10'=>'घटस्थापना', '6-17'=>'फूलपाती',
  '6-18'=>'महाअष्टमी', '6-19'=>'महानवमी', '6-20'=>'विजया दशमी',
  '7-13'=>'लक्ष्मी पूजा', '7-14'=>'गोवर्धन पूजा', '7-15'=>'भाइटीका',
  '7-19'=>'छठ पर्व', '9-1'=>'योमरी पुन्ही', '10-15'=>'माघे संक्रान्ति',
  '10-28'=>'सरस्वती पूजा', '11-14'=>'महाशिवरात्री', '11-30'=>'फागु पूर्णिमा (होली)',
  '12-15'=>'चैते दशैं',
];
$todayFest = isset($bsM,$bsD) ? ($_festivals[$bsM.'-'.$bsD] ?? '') : '';

/* ─── Quote of the day (rotates by day of year) ─── */
$_quotes = [
  ['यदि तपाईँ सपना देख्न सक्नुहुन्छ भने, तपाईँ त्यो पूरा गर्न सक्नुहुन्छ।','—  वाल्ट डिज्नी'],
  ['सफलता अन्तिम होइन, असफलता घातक होइन — निरन्तर रहने साहस नै महत्त्वपूर्ण छ।','— विन्स्टन चर्चिल'],
  ['ज्ञान शक्ति हो; तर चरित्र शक्तिको प्रयोग गर्ने आधार हो।','— स्वामी विवेकानन्द'],
  ['कर्म नै पूजा हो।','— नेपाली उखान'],
  ['सानो शुरुवात ठूलो परिवर्तनको आधार हो।','— लाओ त्सु'],
  ['धैर्य तीतो हुन्छ, तर यसको फल मीठो हुन्छ।','— अरस्तु'],
  ['आफूले गरेको कामलाई माया गर्नुहोस्, सफलता आफै पछ्याउँछ।','— स्टीभ जब्स'],
];
$_qIdx = ((int)date('z')) % count($_quotes);
$todayQuote = $_quotes[$_qIdx];
?>
  </div><!-- /app-shell (center column) -->

  <!-- ═══ DETAIL PANE (desktop only) — fills the right side ════════════════ -->
  <aside id="detail-pane" aria-label="Detail view">
    <div class="dp-head">
      <button type="button" class="dp-btn" onclick="NSH_closePane && NSH_closePane()" aria-label="Close" title="Close (Esc)">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
      <div class="dp-title">आकाशवाणी</div>
      <button type="button" class="dp-btn" onclick="var f=document.querySelector('#detail-pane iframe'); if(f && f.src && f.src!=='about:blank'){ document.getElementById('detail-pane').classList.add('loading'); f.src=f.src; }" aria-label="Reload" title="Reload">
        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
      </button>
      <button type="button" class="dp-btn" onclick="var f=document.querySelector('#detail-pane iframe'); if(f && f.src && f.src!=='about:blank'){ var u=new URL(f.src); u.searchParams.delete('embed'); window.open(u.toString(),'_blank'); }" aria-label="Open in new tab" title="Open in new tab">
        <i data-lucide="external-link" class="w-4 h-4"></i>
      </button>
    </div>
    <div class="dp-body">
      <iframe src="about:blank" title="Detail content"></iframe>
      <div class="dp-loading"><span class="spin"></span><span><?= $tF('लोड हुँदै…','Loading…') ?></span></div>

      <!-- ═══ DEFAULT DASHBOARD (shown when nothing is clicked) ════════════════ -->
      <div class="dp-dashboard">
        <!-- Hero: greeting + today -->
        <div class="dpd-hero">
          <div class="h-ico"><i data-lucide="sun" class="w-6 h-6"></i></div>
          <div style="flex:1;min-width:0">
            <div class="h-date ne"><?= isset($bsDateStr) ? $bsDateStr : date('l, j F Y') ?></div>
            <div class="h-title ne"><?= $tF('नमस्कार — आजको खबर','Namaste — Today at a glance') ?></div>
            <?php if($todayFest): ?>
              <div class="ne" style="margin-top:4px;font-size:11.5px;font-weight:600;background:rgba(255,255,255,.22);display:inline-block;padding:3px 9px;border-radius:999px;border:1px solid rgba(255,255,255,.25)">🎉 <?= htmlspecialchars($todayFest,ENT_QUOTES,'UTF-8') ?></div>
            <?php endif; ?>
          </div>
          <a href="/morning-brief.php" class="h-ico" title="<?= $tF('बिहानी ब्रिफ','Morning Brief') ?>" style="background:rgba(255,255,255,.25)">
            <i data-lucide="sunrise" class="w-5 h-5"></i>
          </a>
        </div>

        <!-- Live market mini-cards (data from /api/market-data.php) -->
        <div class="dpd-grid2">
          <a href="/utilities.php#gold" class="dpd-card">
            <h4><i data-lucide="gem" class="w-3.5 h-3.5"></i> <?= $tF('सुन (हल्लमार्क)','Gold (Hallmark)') ?></h4>
            <div class="big" id="dpd-gold">—</div>
            <div class="sub ne" id="dpd-gold-sub"><?= $tF('प्रति तोला','per tola') ?></div>
          </a>
          <a href="/utilities.php#gold" class="dpd-card">
            <h4><i data-lucide="circle-dot" class="w-3.5 h-3.5"></i> <?= $tF('चाँदी','Silver') ?></h4>
            <div class="big" id="dpd-silver">—</div>
            <div class="sub ne"><?= $tF('प्रति तोला','per tola') ?></div>
          </a>
          <a href="/utilities.php#forex" class="dpd-card">
            <h4><i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i> USD · NPR</h4>
            <div class="big" id="dpd-usd">—</div>
            <div class="sub ne" id="dpd-usd-sub"><?= $tF('विदेशी मुद्रा','Forex rate') ?></div>
          </a>
          <a href="/nepali-patro.php" class="dpd-card">
            <h4><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i> <?= $tF('पात्रो','Patro') ?></h4>
            <div class="big ne" style="font-size:15px;line-height:1.3"><?= isset($bsShort)?$bsShort:'' ?></div>
            <div class="sub ne"><?= $tF('पर्व · तिथि · राशिफल','Festival · Tithi · Rashifal') ?></div>
          </a>
        </div>

        <!-- ═══ Sunrise / Sunset / Weather (Kathmandu) ═════════════════════ -->
        <div class="dpd-grid2">
          <div class="dpd-card" style="background:linear-gradient(135deg,#fff7ed,#fef3c7);border-color:#fcd34d">
            <h4 style="color:#92400e"><i data-lucide="sunrise" class="w-3.5 h-3.5"></i> <?= $tF('सूर्योदय / सूर्यास्त','Sunrise / Sunset') ?></h4>
            <div style="display:flex;gap:10px;align-items:baseline;margin-top:4px">
              <div><div class="big" style="font-size:15px;color:#b45309"><?= $sunriseStr ?></div><div class="sub">↑ <?= $tF('बिहान','sunrise') ?></div></div>
              <div><div class="big" style="font-size:15px;color:#9a3412"><?= $sunsetStr ?></div><div class="sub">↓ <?= $tF('बेलुका','sunset') ?></div></div>
            </div>
          </div>
          <div class="dpd-card" id="dpd-wx-card" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#93c5fd">
            <h4 style="color:#1e40af"><i data-lucide="cloud-sun" class="w-3.5 h-3.5"></i> <?= $tF('काठमाडौंको मौसम','Kathmandu Weather') ?></h4>
            <div class="big" id="dpd-wx-temp" style="color:#1d4ed8">—</div>
            <div class="sub ne" id="dpd-wx-sub"><?= $tF('लोड हुँदै…','Loading…') ?></div>
          </div>
        </div>

        <!-- ═══ 3-day Weather Forecast (Open-Meteo, live) ═══════════════════ -->
        <div class="dpd-section-t ne" style="margin-top:14px">
          <i data-lucide="cloud" class="w-4 h-4" style="color:#1d4ed8"></i>
          <?= $tF('३ दिनको मौसम पूर्वानुमान','3-Day Forecast') ?>
        </div>
        <div id="dpd-forecast" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
          <div class="dpd-card" style="text-align:center;padding:10px"><div class="sub ne">—</div></div>
          <div class="dpd-card" style="text-align:center;padding:10px"><div class="sub ne">—</div></div>
          <div class="dpd-card" style="text-align:center;padding:10px"><div class="sub ne">—</div></div>
        </div>

        <!-- ═══ LIVE ALERTS (BIPAD / Earthquake / Severe weather) ═══════════ -->
        <div class="dpd-section-t ne" style="margin-top:14px">
          <i data-lucide="bell-ring" class="w-4 h-4" style="color:#dc2626"></i>
          <?= $tF('सरकारी सूचना र चेतावनी','Live Alerts (Gov · Quake · Weather)') ?>
          <span id="dpd-alerts-count" style="margin-left:auto;font-size:10px;background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:999px;font-weight:700"></span>
        </div>
        <div id="dpd-alerts" style="display:flex;flex-direction:column;gap:8px">
          <div style="background:#fff;border:1px solid #e6eaf2;border-radius:12px;padding:10px;font-size:12px;color:#94a3b8" class="ne">लोड हुँदै…</div>
        </div>

        <!-- ═══ LIVE FUEL PRICE (NOC, from /api/market-data.php) ════════════ -->
        <div class="dpd-section-t ne" style="margin-top:14px">
          <i data-lucide="fuel" class="w-4 h-4" style="color:#ea580c"></i>
          <?= $tF('इन्धन मूल्य (NOC)','Fuel Price (NOC)') ?>
          <a href="/utilities.php#fuel" class="more ne"><?= $tF('विवरण →','Details →') ?></a>
        </div>
        <div class="dpd-grid2" id="dpd-fuel">
          <div class="dpd-card" style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border-color:#fdba74">
            <h4 style="color:#9a3412"><i data-lucide="droplet" class="w-3.5 h-3.5"></i> <?= $tF('पेट्रोल','Petrol') ?></h4>
            <div class="big" id="dpd-petrol" style="color:#c2410c">—</div>
            <div class="sub ne"><?= $tF('प्रति लिटर','per litre') ?></div>
          </div>
          <div class="dpd-card" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#86efac">
            <h4 style="color:#166534"><i data-lucide="droplets" class="w-3.5 h-3.5"></i> <?= $tF('डिजेल','Diesel') ?></h4>
            <div class="big" id="dpd-diesel" style="color:#15803d">—</div>
            <div class="sub ne"><?= $tF('प्रति लिटर','per litre') ?></div>
          </div>
          <div class="dpd-card" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border-color:#fca5a5">
            <h4 style="color:#991b1b"><i data-lucide="flame" class="w-3.5 h-3.5"></i> <?= $tF('LPG ग्यास','LPG Cylinder') ?></h4>
            <div class="big" id="dpd-lpg" style="color:#b91c1c">—</div>
            <div class="sub ne"><?= $tF('प्रति सिलिन्डर','per 14.2 kg') ?></div>
          </div>
          <div class="dpd-card" style="background:linear-gradient(135deg,#faf5ff,#f3e8ff);border-color:#d8b4fe">
            <h4 style="color:#6b21a8"><i data-lucide="plane" class="w-3.5 h-3.5"></i> <?= $tF('हवाई इन्धन','Aviation Fuel') ?></h4>
            <div class="big" id="dpd-avi" style="color:#7e22ce">—</div>
            <div class="sub ne"><?= $tF('प्रति लिटर','per litre') ?></div>
          </div>
        </div>

        <!-- Quick services -->
        <div class="dpd-section-t ne">
          <i data-lucide="zap" class="w-4 h-4 text-brand-600"></i>
          <?= $tF('द्रुत सेवा','Quick Services') ?>
          <a href="/gov-services.php" class="more ne"><?= $tF('सबै →','All →') ?></a>
        </div>
        <div class="dpd-tiles">
          <a href="/news.php" class="dpd-tile">
            <span class="ic bg-i2"><i data-lucide="newspaper" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('समाचार','News') ?></span>
          </a>
          <a href="/ipo-tracker.php" class="dpd-tile">
            <span class="ic bg-i1"><i data-lucide="trending-up" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne">IPO</span>
          </a>
          <a href="/rashifal.php" class="dpd-tile">
            <span class="ic bg-i5"><i data-lucide="sparkles" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('राशिफल','Rashifal') ?></span>
          </a>
          <a href="/gov-services.php" class="dpd-tile">
            <span class="ic bg-i6"><i data-lucide="landmark" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('सरकारी','Gov') ?></span>
          </a>
          <a href="/tools.php" class="dpd-tile">
            <span class="ic bg-i3"><i data-lucide="wrench" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('टूल','Tools') ?></span>
          </a>
          <a href="/tax-calculator.php" class="dpd-tile">
            <span class="ic bg-i8"><i data-lucide="receipt" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('कर','Tax') ?></span>
          </a>
          <a href="/emergency.php" class="dpd-tile">
            <span class="ic bg-i6"><i data-lucide="phone-call" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('आपतकाल','SOS') ?></span>
          </a>
          <a href="/downloads.php" class="dpd-tile">
            <span class="ic bg-i7"><i data-lucide="download" class="w-[18px] h-[18px]"></i></span>
            <span class="lbl ne"><?= $tF('डाउनलोड','Files') ?></span>
          </a>
        </div>

        <!-- ═══ News by Category (no broken API — direct links) ═════════════ -->
        <div class="dpd-section-t ne">
          <i data-lucide="flame" class="w-4 h-4" style="color:#dc2626"></i>
          <?= $tF('समाचार वर्ग','News Categories') ?>
          <a href="/news.php" class="more ne"><?= $tF('सबै →','All →') ?></a>
        </div>
        <div class="dpd-tiles" style="grid-template-columns:repeat(3,1fr)">
          <?php foreach([
            ['/news.php?cat=politics','🏛️',$tF('राजनीति','Politics')],
            ['/news.php?cat=economy','💰',$tF('अर्थ','Economy')],
            ['/news.php?cat=sports','⚽',$tF('खेलकुद','Sports')],
            ['/news.php?cat=entertainment','🎬',$tF('मनोरञ्जन','Entertainment')],
            ['/news.php?cat=world','🌏',$tF('विश्व','World')],
            ['/news.php?cat=technology','💻',$tF('प्रविधि','Tech')],
          ] as $c): ?>
            <a href="<?= $c[0] ?>" class="dpd-tile" style="padding:10px 4px">
              <span style="font-size:20px;line-height:1"><?= $c[1] ?></span>
              <span class="lbl ne"><?= $c[2] ?></span>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- ═══ LIVE Headlines (with photos, RSS-powered) ════════════════════ -->
        <div class="dpd-section-t ne" style="margin-top:14px">
          <i data-lucide="radio" class="w-4 h-4" style="color:#dc2626"></i>
          <?= $tF('ताजा समाचार · LIVE','Latest News · LIVE') ?>
          <a href="/news.php" class="more ne"><?= $tF('सबै →','All →') ?></a>
        </div>
        <div id="dpd-live-news" style="display:flex;flex-direction:column;gap:8px">
          <div style="background:#fff;border:1px solid #e6eaf2;border-radius:12px;padding:10px;font-size:12px;color:#94a3b8" class="ne">लोड हुँदै…</div>
        </div>

        <!-- Daily Essentials list (Sajha Sewa) -->
        <div class="dpd-section-t ne" style="margin-top:14px">
          <i data-lucide="link" class="w-4 h-4" style="color:#0f766e"></i>
          <?= $tF('सजिलो लिंक','Quick Links') ?>
        </div>
        <div class="dpd-grid2" style="grid-template-columns:repeat(3,1fr);gap:8px">
          <?php
          $quickLinks = [
            ['https://esewa.com.np',         'e',  '#60a832','#3d8a1a', 'eSewa',       $tF('बिल भुक्तानी','Bills'),       true],
            ['https://khalti.com',           'K',  '#5c2d91','#7a3fbc', 'Khalti',      $tF('डिजिटल वालेट','Wallet'),     true],
            ['https://nagarikapp.gov.np',    'N',  '#dc2626','#7c2d12', 'Nagarik',     $tF('सरकारी सेवा','Gov'),         true],
            ['https://psc.gov.np',           'लो', '#0c4a6e','#0369a1', $tF('लोकसेवा','Lok Sewa'), $tF('परीक्षा · नतिजा','Exams'), true],
            ['/utilities.php#fuel',          '⛽', '#ea580c','#c2410c', $tF('इन्धन','Fuel'),       $tF('NOC मूल्य','NOC Price'),    false],
            ['/utilities.php#gold',          '🪙', '#ca8a04','#a16207', $tF('सुन/चाँदी','Gold'),   $tF('हल्लमार्क','Hallmark'),    false],
          ];
          foreach($quickLinks as [$h,$ic,$c1,$c2,$t1,$t2,$ext]): ?>
            <a href="<?= htmlspecialchars($h,ENT_QUOTES) ?>"<?= $ext ? ' target="_blank" rel="noopener"' : '' ?> class="dpd-card" style="text-align:center;padding:10px 6px">
              <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,<?= $c1 ?>,<?= $c2 ?>);color:#fff;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 6px"><?= $ic ?></div>
              <div class="ne" style="font-size:11.5px;font-weight:700;color:#0f172a;line-height:1.2"><?= $t1 ?></div>
              <div class="ne" style="font-size:10px;color:#64748b;margin-top:2px;line-height:1.2"><?= $t2 ?></div>
            </a>
          <?php endforeach; ?>
        </div>


        <!-- ═══ Quote of the day (नेपाली) ═════════════════════════════════════ -->
        <div class="dpd-tip" style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border-color:#5eead4;margin-top:14px">
          <div class="ic" style="background:#0d9488"><i data-lucide="quote" class="w-4 h-4"></i></div>
          <div style="flex:1;min-width:0">
            <div class="ne" style="font-size:13px;color:#134e4a;font-weight:600;line-height:1.55;font-style:italic">"<?= htmlspecialchars($todayQuote[0],ENT_QUOTES,'UTF-8') ?>"</div>
            <div class="ne" style="font-size:11px;color:#0f766e;margin-top:5px;font-weight:600"><?= htmlspecialchars($todayQuote[1],ENT_QUOTES,'UTF-8') ?></div>
          </div>
        </div>

        <!-- Helpful tip -->
        <div class="dpd-tip">
          <div class="ic"><i data-lucide="mouse-pointer-click" class="w-4 h-4"></i></div>
          <div class="t ne"><?= $tF('बायाँतिर कुनै पनि item मा click गर्नुहोस् — यहाँ खुल्नेछ। फेरि बन्द गरे यो dashboard फर्किन्छ।','Click any item on the left — it opens here. Close it and this dashboard returns.') ?></div>
        </div>
      </div>

      <!-- Populate dashboard widgets from real LIVE APIs -->
      <script>
      (function(){
        if(!window.matchMedia('(min-width:1100px)').matches) return;
        function num(n){ return Number(n).toLocaleString('en-IN'); }

        // ── Live market data (real working endpoint) ──────────────────────
        // ── Live market data + admin overrides (merge: override wins) ─────
        Promise.all([
          fetch('/api/market-data.php',{credentials:'same-origin'}).then(r=>r.ok?r.json():null).catch(()=>null),
          fetch('/api/overrides.php',{credentials:'same-origin'}).then(r=>r.ok?r.json():{ok:false}).catch(()=>({ok:false}))
        ]).then(([live, ovRes])=>{
            var d = live || {};
            var ov = (ovRes && ovRes.overrides) || {};
            // Apply overrides where use:true
            ['gold','petrol','forex'].forEach(function(sec){
              if(ov[sec] && ov[sec].use){
                d[sec] = Object.assign({}, d[sec]||{}, ov[sec]);
                d[sec]._manual = true;
              }
            });
            if(!d) return;
            var manualBadge = function(el){ if(!el) return; var b=document.createElement('span'); b.textContent=' ✋'; b.title='Admin manual'; b.style.cssText='font-size:9px;opacity:.55;vertical-align:super'; el.appendChild(b); };
            if(d.gold && d.gold.hallmarkPerTola){
              var g=document.getElementById('dpd-gold'); if(g) g.textContent='रु '+num(d.gold.hallmarkPerTola);
              var s=document.getElementById('dpd-gold-sub');
              if(s) s.textContent = d.gold._manual ? 'Admin set · प्रति तोला' : (d.gold.updatedAt?'अपडेट: '+String(d.gold.updatedAt).split(' ')[1]:'प्रति तोला');
              if(d.gold._manual) manualBadge(g);
            }
            if(d.gold && d.gold.silverPerTola){
              var sv=document.getElementById('dpd-silver'); if(sv) sv.textContent='रु '+num(d.gold.silverPerTola);
              if(d.gold._manual) manualBadge(sv);
            }
            var usdRate = (d.forex && d.forex.usdNpr) || (d.gold && d.gold.usdNpr);
            if(usdRate){
              var u=document.getElementById('dpd-usd'); if(u) u.textContent='रु '+Number(usdRate).toFixed(2);
              var us=document.getElementById('dpd-usd-sub'); if(us) us.textContent='1 USD = '+Number(usdRate).toFixed(2)+' NPR';
              if(d.forex && d.forex._manual) manualBadge(u);
            }
            // Live NOC fuel prices
            if(d.petrol){
              var manual = !!d.petrol._manual;
              var setFuel=function(id,val){var el=document.getElementById(id); if(el && val){ el.textContent='रु '+num(val); if(manual) manualBadge(el); } };
              setFuel('dpd-petrol', d.petrol.petrol);
              setFuel('dpd-diesel', d.petrol.diesel);
              setFuel('dpd-lpg',    d.petrol.lpg_cylinder);
              setFuel('dpd-avi',    d.petrol.aviation_fuel);
            }
          });

        // ── Live weather + 3-day forecast (Open-Meteo — free, CORS-enabled) ──
        var WX_CODES={0:'सफा',1:'धमिलो',2:'आंशिक बादल',3:'बादल',45:'कुहिरो',48:'कुहिरो',51:'सिमसिम',53:'सिमसिम',55:'झरी',61:'पानी',63:'पानी',65:'भारी पानी',71:'हिउँ',73:'हिउँ',75:'भारी हिउँ',80:'पानी',81:'पानी',82:'भारी पानी',95:'चट्याङ',96:'चट्याङ',99:'चट्याङ'};
        var WX_EMOJI={0:'☀️',1:'🌤️',2:'⛅',3:'☁️',45:'🌫️',48:'🌫️',51:'🌦️',53:'🌦️',55:'🌧️',61:'🌧️',63:'🌧️',65:'🌧️',71:'🌨️',73:'🌨️',75:'❄️',80:'🌦️',81:'🌧️',82:'⛈️',95:'⛈️',96:'⛈️',99:'⛈️'};
        fetch('https://api.open-meteo.com/v1/forecast?latitude=27.7172&longitude=85.3240&current=temperature_2m,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=Asia%2FKathmandu&forecast_days=4')
          .then(r=>r.ok?r.json():null).then(d=>{
            if(!d) return;
            if(d.current){
              var t=Math.round(d.current.temperature_2m);
              var w=Math.round(d.current.wind_speed_10m);
              var desc=WX_CODES[d.current.weather_code]||'—';
              var tx=document.getElementById('dpd-wx-temp'); if(tx) tx.textContent=t+'°C';
              var sb=document.getElementById('dpd-wx-sub'); if(sb) sb.textContent=desc+' · हावा '+w+' km/h';
            }
            // 3-day forecast (skip today = index 0)
            if(d.daily && d.daily.time){
              var DAYS=['आइत','सोम','मंगल','बुध','बिही','शुक्र','शनि'];
              var fc=document.getElementById('dpd-forecast');
              if(fc){
                var html='';
                for(var i=1;i<=3 && i<d.daily.time.length;i++){
                  var dt=new Date(d.daily.time[i]); var dn=DAYS[dt.getDay()];
                  var c=d.daily.weather_code[i]; var hi=Math.round(d.daily.temperature_2m_max[i]); var lo=Math.round(d.daily.temperature_2m_min[i]);
                  var pp=d.daily.precipitation_probability_max?d.daily.precipitation_probability_max[i]:null;
                  html+='<div class="dpd-card" style="text-align:center;padding:10px 6px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#bfdbfe">'+
                    '<div class="ne" style="font-size:11px;font-weight:700;color:#1e3a8a">'+dn+'</div>'+
                    '<div style="font-size:22px;line-height:1.1">'+(WX_EMOJI[c]||'🌡️')+'</div>'+
                    '<div style="font-size:12px;font-weight:700;color:#1e40af">'+hi+'°/'+lo+'°</div>'+
                    (pp!==null?'<div class="sub" style="color:#2563eb">💧 '+pp+'%</div>':'')+
                  '</div>';
                }
                fc.innerHTML=html;
              }
            }
          }).catch(()=>{
            var sb=document.getElementById('dpd-wx-sub'); if(sb) sb.textContent='मौसम अनुपलब्ध';
          });

        // ── Live alerts (BIPAD gov + USGS earthquake + severe weather) ────
        fetch('/api/alerts.php',{credentials:'same-origin'})
          .then(r=>r.ok?r.json():null).then(d=>{
            var box=document.getElementById('dpd-alerts'); if(!box) return;
            var cnt=document.getElementById('dpd-alerts-count');
            if(!d || !d.ok || !d.items || !d.items.length){
              box.innerHTML='<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;border-radius:12px;padding:10px;font-size:12px;color:#166534" class="ne">✓ हाल कुनै सक्रिय चेतावनी छैन</div>';
              if(cnt) cnt.style.display='none';
              return;
            }
            if(cnt){ cnt.textContent=d.count+(d.count>12?'+':''); }
            var SEV={severe:{bg:'#fef2f2',bd:'#fca5a5',fg:'#991b1b',ic:'⚠️'},active:{bg:'#fff7ed',bd:'#fdba74',fg:'#9a3412',ic:'🔔'},moderate:{bg:'#fefce8',bd:'#fde047',fg:'#854d0e',ic:'📢'},minor:{bg:'#f0f9ff',bd:'#7dd3fc',fg:'#075985',ic:'ℹ️'},info:{bg:'#f8fafc',bd:'#cbd5e1',fg:'#475569',ic:'ℹ️'}};
            box.innerHTML=d.items.slice(0,5).map(function(a){
              var s=SEV[a.severity]||SEV.info;
              var when=a.startedOn?new Date(a.startedOn).toLocaleString('en-GB',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}):'';
              return '<a href="'+(a.link||'#')+'" target="_blank" rel="noopener" style="display:block;background:'+s.bg+';border:1px solid '+s.bd+';border-radius:12px;padding:9px 10px;text-decoration:none">'+
                '<div style="display:flex;gap:6px;align-items:center;font-size:10.5px;font-weight:700;color:'+s.fg+'" class="ne">'+s.ic+' '+(a.hazard||'')+' · '+(a.source||'')+'</div>'+
                '<div style="font-size:12.5px;font-weight:600;color:#0b1220;line-height:1.4;margin-top:3px" class="ne">'+(a.title||'')+'</div>'+
                (when?'<div style="font-size:10px;color:#64748b;margin-top:2px">🕐 '+when+'</div>':'')+
              '</a>';
            }).join('');
          }).catch(()=>{
            var box=document.getElementById('dpd-alerts');
            if(box) box.innerHTML='<div style="background:#fff;border:1px solid #e6eaf2;border-radius:12px;padding:10px;font-size:12px;color:#94a3b8" class="ne">चेतावनी load हुन सकेन</div>';
          });

        // ── Live RSS headlines (with photos — only renders when image exists) ──
        function esc(s){return String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
        fetch('/api/news-rss.php?limit=6',{credentials:'same-origin'})
          .then(r=>r.ok?r.json():null).then(d=>{
            var box=document.getElementById('dpd-live-news'); if(!box) return;
            if(!d || !d.ok || !d.items || !d.items.length){
              box.innerHTML='<div style="background:#fff;border:1px solid #e6eaf2;border-radius:12px;padding:10px;font-size:12px;color:#94a3b8" class="ne">समाचार उपलब्ध छैन</div>';
              return;
            }
            box.innerHTML = d.items.map(it=>{
              var thumb = it.image
                ? '<div style="width:60px;height:60px;flex-shrink:0;border-radius:10px;overflow:hidden;background:linear-gradient(135deg,#f0fdfa,#cffafe)"><img src="'+esc(it.image)+'" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover" onerror="this.parentNode.style.display=\'none\'"></div>'
                : '';
              var url = it.internalUrl || (it.slug ? '/news-detail.php?slug=' + encodeURIComponent(it.slug) : '/news-detail.php?url=' + encodeURIComponent(it.link || '') + '&src=' + encodeURIComponent(it.sourceLabel || ''));
              return '<a href="'+url+'" style="display:flex;gap:10px;background:#fff;border:1px solid #e6eaf2;border-radius:12px;padding:9px;text-decoration:none;color:inherit;align-items:flex-start">'+
                thumb +
                '<div style="flex:1;min-width:0">'+
                  '<div style="font-size:10.5px;color:#0f766e;font-weight:700" class="ne">'+esc(it.sourceLabel||'')+' · '+esc(it.ago||'')+'</div>'+
                  '<div style="font-size:12.5px;font-weight:600;color:#0b1220;line-height:1.4;margin-top:2px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden" class="ne">'+esc(it.title)+'</div>'+
                '</div>'+
              '</a>';
            }).join('');
          }).catch(()=>{
            var box=document.getElementById('dpd-live-news');
            if(box) box.innerHTML='<div style="background:#fff;border:1px solid #e6eaf2;border-radius:12px;padding:10px;font-size:12px;color:#94a3b8" class="ne">समाचार load हुन सकेन</div>';
          });
      })();
      </script>
    </div>
  </aside>
</main>

<!-- ═══ APP FOOTER (compact, lives inside phone column) ════════════════════════ -->
<footer class="app-shell" style="padding-top:8px;padding-bottom:0">
  <div class="app-card" style="padding:18px 16px">
    <div class="flex items-center gap-2 mb-3">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-700 to-brand-500 text-white flex items-center justify-center font-extrabold">आ</div>
      <div>
        <div class="text-[14px] font-bold text-ink ne">आकाशवाणी<span class="text-slate-400 font-normal text-[11px]"> by आकाश अधिकारी</span></div>
        <div class="text-[11px] text-slate-500 ne"><?= $tF('सूचनाको खुला आकाश','सूचनाको खुला आकाश') ?></div>
      </div>
    </div>

    <!-- Icon link grid removed — bottom navigation already exposes these shortcuts -->


    <div class="mt-4 pt-3 border-t border-line flex items-center gap-2 text-[11px] text-slate-400">
      © <?= date('Y') ?> <?= htmlspecialchars(defined('SITE_NAME')?SITE_NAME:'आकाशवाणी',ENT_QUOTES,'UTF-8') ?>
      <a href="?lang=<?= $lang==='ne'?'en':'ne' ?>" class="ml-auto text-brand-700 font-semibold"><?= $lang==='ne'?'EN':'नेपा' ?></a>
    </div>

    <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-500 flex-wrap">
      <a href="/sources.php" class="hover:text-brand-700 ne flex items-center gap-1"><i data-lucide="book-marked" class="w-3 h-3"></i> Sources &amp; Attribution</a>
      <span class="opacity-40">·</span>
      <a href="/sources.php#legal" class="hover:text-brand-700 ne flex items-center gap-1"><i data-lucide="scale" class="w-3 h-3"></i> कानूनी नीति</a>
    </div>

    <!-- AI Sync badge (admin only) -->
    <div id="footer-sync" class="hidden mt-3">
      <div class="inline-flex items-center gap-2 bg-brand-50 border border-brand-200 rounded-lg px-3 py-1.5 text-[11px] text-brand-700">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
        <span id="footer-sync-txt">AI Sync active</span>
      </div>
    </div>
  </div>
</footer>

<?php
// Skip floating AI assistant inside detail-pane iframe (?embed=1) to avoid duplicate fabs
$__embedFooter = isset($_GET['embed']) && $_GET['embed']==='1';
if (!$__embedFooter): ?>
<?php @include __DIR__ . '/includes/ai-assistant.php'; ?>
<?php endif; ?>

<!-- Re-render lucide icons for any dynamically added content -->
<script>
// Lucide is deferred — wait for it then render remaining icons
(function(){
  function tryRender(){if(window.lucide&&lucide.createIcons)try{lucide.createIcons({nameAttr:'data-lucide'});}catch(e){}}
  if(window.lucide){tryRender();}else{window.addEventListener('load',tryRender,{once:true});}
})();
</script>

<!-- Non-critical scripts: run during browser idle time so they never block page paint -->
<script>
(function(){
  var ric = window.requestIdleCallback || function(fn){setTimeout(fn,200);};

  // ── User data sync (logged-in only) ──────────────────────────────────────
  var loggedIn=<?php echo (!empty($_SESSION['auth_user_id']))?'true':'false'; ?>;
  if(loggedIn){
    ric(function(){
      var PFX='nsh:';
      fetch('/api/user-data.php',{credentials:'same-origin'})
        .then(function(r){return r.json();}).then(function(res){
          if(!res||!res.ok||!res.data)return;
          Object.keys(res.data).forEach(function(k){try{localStorage.setItem(k,typeof res.data[k]==='string'?res.data[k]:JSON.stringify(res.data[k]));}catch(e){}});
          window.dispatchEvent(new CustomEvent('nsh:user-data-ready'));
        }).catch(function(){});
      var pending={},timer=null;
      function push(k){pending[k]=true;if(timer)return;timer=setTimeout(function(){var ks=Object.keys(pending);pending={};timer=null;ks.forEach(function(k){var v=localStorage.getItem(k);var b=v===null?{key:k,delete:true}:{key:k,value:(function(){try{return JSON.parse(v);}catch(e){return v;}})()};fetch('/api/user-data.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify(b)}).catch(function(){});});},800);}
      var _si=localStorage.setItem.bind(localStorage);var _rm=localStorage.removeItem.bind(localStorage);
      localStorage.setItem=function(k,v){_si(k,v);if(typeof k==='string'&&k.startsWith(PFX))push(k);};
      localStorage.removeItem=function(k){_rm(k);if(typeof k==='string'&&k.startsWith(PFX))push(k);};
    });
  }

  // ── AI sync badge (admin only) ────────────────────────────────────────────
  ric(function(){
    var badge=document.getElementById('footer-sync');var txt=document.getElementById('footer-sync-txt');
    if(!badge||!txt)return;
    var isAdmin=document.cookie.indexOf('nsh_admin')!==-1||document.body.dataset.admin==='1';
    if(!isAdmin)return;
    fetch('/api/sync-status.php').then(function(r){return r.json();}).then(function(d){
      if(!d||!d.success)return;
      badge.classList.remove('hidden');
      var ago=d.last_run?(Math.round((Date.now()-new Date(d.last_run))/60000))+'m ago':'never';
      txt.textContent='AI Sync: '+(d.today_count||0)+' articles · last '+ago;
    }).catch(function(){});
  });
})();
</script>

<!-- Page transitions + swipe navigation (loaded async) -->
<script src="/assets/js/swipe-nav.js" defer></script>

<!-- PWA helpers (needed early for install prompt — not deferred) -->
<script>
function pwaInstall(){
  if(window.nshPwa&&window.nshPwa.install&&window.nshPwa.install())return;
  var b=document.getElementById('pwa-install');if(b)b.style.display='flex';
}
function pwaReload(){
  if(navigator.serviceWorker){navigator.serviceWorker.getRegistration().then(function(r){if(r&&r.waiting)r.waiting.postMessage({type:'SKIP_WAITING'});});}
  setTimeout(function(){location.reload();},200);
}
document.addEventListener('nsh:pwa-installable',function(){var b=document.getElementById('pwa-install');if(b)b.style.display='flex';});
document.addEventListener('nsh:pwa-installed',function(){var b=document.getElementById('pwa-install');if(b)b.style.display='none';});
</script>

<!-- PWA Install banner -->
<?php
$pwaName      = defined('PWA_NAME')       ? PWA_NAME       : (defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी');
$pwaShortName = defined('PWA_SHORT_NAME') ? PWA_SHORT_NAME : 'नेपाली Hub';
?>
<div id="pwa-install" style="display:none;position:fixed;left:12px;right:12px;bottom:calc(96px + env(safe-area-inset-bottom,0px));z-index:9998;
  background:#fff;border:1px solid #e6eaf2;border-radius:18px;box-shadow:0 12px 40px -10px rgba(11,18,32,.25);
  padding:12px 14px;align-items:center;gap:12px;max-width:520px;margin:0 auto;animation:pwaUp .3s ease">
  <style>@keyframes pwaUp{from{transform:translateY(20px);opacity:0}to{transform:none;opacity:1}}</style>
  <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;flex-shrink:0">न</div>
  <div style="flex:1;min-width:0">
    <div style="font-weight:700;font-size:.9rem;color:#0b1220" class="ne"><?= htmlspecialchars($pwaName,ENT_QUOTES,'UTF-8') ?></div>
    <div style="font-size:.78rem;color:#64748b;margin-top:1px" class="ne"><?= $tF('Home Screen मा App राख्नुहोस् — offline पनि चल्छ!','Add to Home Screen — works offline!') ?></div>
  </div>
  <div style="display:flex;gap:6px;flex-shrink:0">
    <button onclick="pwaInstall()" style="padding:8px 14px;background:#0d9488;color:#fff;border:none;border-radius:12px;font-size:.82rem;font-weight:700;cursor:pointer">Install</button>
    <button onclick="document.getElementById('pwa-install').style.display='none'" style="padding:8px 10px;background:#f1f5f9;color:#64748b;border:none;border-radius:12px;font-size:.82rem;cursor:pointer">✕</button>
  </div>
</div>

<!-- PWA Update banner -->
<div id="pwa-update" style="display:none;position:fixed;top:calc(80px + env(safe-area-inset-top,0px));left:12px;right:12px;z-index:9999;
  background:#fff;border:1px solid #99f6e4;border-radius:16px;box-shadow:0 8px 30px -10px rgba(13,148,136,.3);padding:12px 14px;max-width:420px;margin:0 auto">
  <div style="display:flex;align-items:flex-start;gap:10px">
    <i data-lucide="refresh-cw" style="width:20px;height:20px;color:#0f766e;flex-shrink:0;margin-top:2px"></i>
    <div style="flex:1">
      <div style="font-weight:700;font-size:.86rem;color:#0b1220" class="ne"><?= $tF('नयाँ अपडेट उपलब्ध छ','New update available') ?></div>
      <div style="display:flex;gap:8px;margin-top:8px">
        <button onclick="pwaReload()" style="padding:6px 14px;background:#0d9488;color:#fff;border:none;border-radius:10px;font-size:.78rem;font-weight:700;cursor:pointer">Refresh</button>
        <button onclick="document.getElementById('pwa-update').style.display='none'" style="padding:6px 10px;background:#f1f5f9;color:#64748b;border:none;border-radius:10px;font-size:.78rem;cursor:pointer"><?= $tF('पछि','Later') ?></button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
