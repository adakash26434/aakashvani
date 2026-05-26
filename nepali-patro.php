<?php
/**
 * nepali-patro.php v12 — Nepali Patro with daily Panchang & Choghadiya times
 * (Kaal / Amrit / Shubha / Rog / Labha / Udveg / Chal samaya)
 * Times are computed from local sunrise/sunset for Kathmandu (lat 27.7172, lon 85.3240).
 */
require_once __DIR__ . '/header.php';

/* ────────── Build current BS month grid ────────── */
$curY=$bsY; $curM=$bsM; $curD=$bsD;
$dim       = $_bsData[$curY][$curM] ?? 30;
$firstDow  = ($adDow - (($curD-1) % 7) + 7) % 7;

$tithi = ['प्रतिपदा','द्वितीया','तृतीया','चतुर्थी','पञ्चमी','षष्ठी','सप्तमी','अष्टमी','नवमी','दशमी','एकादशी','द्वादशी','त्रयोदशी','चतुर्दशी','पूर्णिमा/अमावस्या'];
$nakshatra = ['अश्विनी','भरणी','कृत्तिका','रोहिणी','मृगशिरा','आर्द्रा','पुनर्वसु','पुष्य','आश्लेषा','मघा','पूर्वा फाल्गुनी','उत्तरा फाल्गुनी','हस्त','चित्रा','स्वाति','विशाखा','अनुराधा','ज्येष्ठा','मूल','पूर्वाषाढा','उत्तराषाढा','श्रवण','धनिष्ठा','शतभिषा','पूर्वा भाद्रपद','उत्तरा भाद्रपद','रेवती'];
$yoga = ['विष्कम्भ','प्रीति','आयुष्मान्','सौभाग्य','शोभन','अतिगण्ड','सुकर्मा','धृति','शूल','गण्ड','वृद्धि','ध्रुव','व्याघात','हर्षण','वज्र','सिद्धि','व्यतीपात','वरीयान','परिघ','शिव','सिद्ध','साध्य','शुभ','शुक्ल','ब्रह्मा','इन्द्र','वैधृति'];
$karana = ['बव','बालव','कौलव','तैतिल','गर','वणिज','विष्टि (भद्रा)','शकुनि','चतुष्पाद','नाग','किंस्तुघ्न'];

/* ────────── Sunrise / Sunset for Kathmandu (today) ────────── */
$lat = 27.7172; $lon = 85.3240; $tz = 5.75; // NPT = UTC+5:45
$todayTs = strtotime($now->format('Y-m-d'));
$sunInfo = date_sun_info($todayTs, $lat, $lon);
$sunrise = $sunInfo['sunrise'] ?: ($todayTs + 6*3600);
$sunset  = $sunInfo['sunset']  ?: ($todayTs + 18*3600);
$dayLen  = max(1, $sunset - $sunrise);
$nightLen= max(1, ($sunrise + 86400) - $sunset); // next sunrise - today's sunset

/* ────────── Choghadiya (8 slots in day, 8 in night) ────────── */
// Order depends on weekday. 0=Sun, 1=Mon, ... 6=Sat
$choghOrder = [
    0 => ['उद्वेग','चल','लाभ','अमृत','काल','शुभ','रोग','उद्वेग'],
    1 => ['अमृत','काल','शुभ','रोग','उद्वेग','चल','लाभ','अमृत'],
    2 => ['रोग','उद्वेग','चल','लाभ','अमृत','काल','शुभ','रोग'],
    3 => ['लाभ','अमृत','काल','शुभ','रोग','उद्वेग','चल','लाभ'],
    4 => ['शुभ','रोग','उद्वेग','चल','लाभ','अमृत','काल','शुभ'],
    5 => ['चल','लाभ','अमृत','काल','शुभ','रोग','उद्वेग','चल'],
    6 => ['काल','शुभ','रोग','उद्वेग','चल','लाभ','अमृत','काल'],
];
$nightChoghStart = [0=>4,1=>5,2=>6,3=>0,4=>1,5=>2,6=>3]; // first night slot = day slot index
$dayList   = $choghOrder[$adDow];
$nStart    = $nightChoghStart[$adDow];
$nightList = []; for($i=0;$i<8;$i++){ $nightList[] = $choghOrder[$adDow][($nStart+$i)%8]; }
$slotD = $dayLen/8; $slotN=$nightLen/8;

function chogh_color($n){
    $good = ['अमृत'=>'emerald','शुभ'=>'green','लाभ'=>'sky'];
    $bad  = ['काल'=>'rose','रोग'=>'red','उद्वेग'=>'orange'];
    if(isset($good[$n])) return $good[$n];
    if(isset($bad[$n]))  return $bad[$n];
    return 'slate';
}
function fmt_t($ts){ return date('g:i A', $ts); }

/* Build all 16 slots */
$slots = [];
for($i=0;$i<8;$i++){
    $s = $sunrise + $i*$slotD; $e = $s + $slotD;
    $slots[] = ['period'=>'दिन','name'=>$dayList[$i],'s'=>$s,'e'=>$e];
}
for($i=0;$i<8;$i++){
    $s = $sunset + $i*$slotN; $e = $s + $slotN;
    $slots[] = ['period'=>'रात','name'=>$nightList[$i],'s'=>$s,'e'=>$e];
}

/* Find current slot */
$nowTs = $now->getTimestamp();
$currentSlot = null;
foreach($slots as $sl){ if($nowTs>=$sl['s'] && $nowTs<$sl['e']){ $currentSlot=$sl; break; } }

/* ────────── Rahu / Yamghanta / Gulika kaal (day only) ────────── */
$rahuOrder = [0=>7, 1=>1, 2=>6, 3=>4, 4=>5, 5=>3, 6=>2]; // Sun..Sat → 1-indexed period of day (8 parts)
$yamOrder  = [0=>4, 1=>3, 2=>2, 3=>1, 4=>7, 5=>6, 6=>5];
$gulOrder  = [0=>6, 1=>5, 2=>4, 3=>3, 4=>2, 5=>1, 6=>7];
$periodLen = $dayLen/8;
function periodTime($idx, $sunrise, $periodLen){
    $s = $sunrise + ($idx-1)*$periodLen; $e = $s + $periodLen;
    return [$s,$e];
}
[$rS,$rE] = periodTime($rahuOrder[$adDow], $sunrise,$periodLen);
[$yS,$yE] = periodTime($yamOrder[$adDow],  $sunrise,$periodLen);
[$gS,$gE] = periodTime($gulOrder[$adDow],  $sunrise,$periodLen);

/* ────────── Tithi/Nakshatra rough index (display only) ────────── */
$tIdx = ($curD - 1) % count($tithi);
$nIdx = ($curD * 3 + $curM) % count($nakshatra);
$yIdx = ($curD + $curM) % count($yoga);
$kIdx = ($curD) % count($karana);

/* Events placeholder */
$events = $events ?? [
    5  => ['कृष्ण जन्माष्टमी','सार्वजनिक बिदा'],
    11 => ['तीज पर्व'],
    15 => ['पूर्णिमा'],
    22 => ['मासिक शिवरात्री'],
];
?>
<main class="app-main pb-8">

  <!-- Hero -->
  <section class="px-4 pt-3">
    <div class="bg-gradient-to-br from-teal-600 to-emerald-700 rounded-2xl p-5 text-white shadow-app">
      <div class="text-[12px] opacity-80"><?= $tH('आज','Today') ?></div>
      <div class="text-[28px] font-extrabold leading-tight"><?= $curD ?> <?= $_bsMonths[$curM] ?></div>
      <div class="text-[14px] opacity-90"><?= $_bsDays[$adDow] ?> • <?= $curY ?> BS</div>
      <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px]">
        <span class="inline-flex items-center gap-1 bg-white/15 px-2.5 py-1 rounded-full">
          <i data-lucide="calendar" class="w-3 h-3"></i><?= $now->format('j M Y') ?> AD
        </span>
        <span class="inline-flex items-center gap-1 bg-white/15 px-2.5 py-1 rounded-full">
          <i data-lucide="sunrise" class="w-3 h-3"></i><?= fmt_t($sunrise) ?>
        </span>
        <span class="inline-flex items-center gap-1 bg-white/15 px-2.5 py-1 rounded-full">
          <i data-lucide="sunset" class="w-3 h-3"></i><?= fmt_t($sunset) ?>
        </span>
        <?php if($currentSlot): ?>
          <span class="inline-flex items-center gap-1 bg-white text-teal-800 font-bold px-2.5 py-1 rounded-full">
            <i data-lucide="clock" class="w-3 h-3"></i> अहिले: <?= $currentSlot['name'] ?>
          </span>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Month grid -->
  <section class="px-4 mt-4">
    <div class="flex items-center justify-between mb-2">
      <button class="w-9 h-9 rounded-full bg-white shadow-app flex items-center justify-center text-slate-600">
        <i data-lucide="chevron-left" class="w-4 h-4"></i>
      </button>
      <div class="font-bold text-slate-900"><?= $_bsMonths[$curM] ?> <?= $curY ?></div>
      <button class="w-9 h-9 rounded-full bg-white shadow-app flex items-center justify-center text-slate-600">
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
      </button>
    </div>
    <div class="bg-white rounded-2xl shadow-app p-3">
      <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-slate-500 mb-1">
        <?php foreach(['आइत','सोम','मंगल','बुध','बिहि','शुक्र','शनि'] as $i=>$d): ?>
          <div class="<?= $i===6?'text-rose-500':'' ?>"><?= $d ?></div>
        <?php endforeach; ?>
      </div>
      <div class="grid grid-cols-7 gap-1">
        <?php for($i=0;$i<$firstDow;$i++): ?><div></div><?php endfor; ?>
        <?php for($d=1;$d<=$dim;$d++):
          $cellDow = ($firstDow + $d - 1) % 7;
          $isToday = ($d===$curD);
          $isSat = ($cellDow===6);
          $hasEvent = isset($events[$d]);
        ?>
          <div class="aspect-square flex flex-col items-center justify-center rounded-xl text-[13px] relative
            <?= $isToday?'bg-teal-600 text-white font-bold':($isSat?'text-rose-500':'text-slate-800') ?>">
            <?= $d ?>
            <?php if($hasEvent && !$isToday): ?>
              <span class="w-1 h-1 rounded-full bg-rose-500 absolute bottom-1.5"></span>
            <?php endif; ?>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- Panchang -->
  <section class="px-4 mt-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-2">आजको पञ्चाङ्ग</h2>
    <div class="grid grid-cols-2 gap-2">
      <?php
      $panch = [
        ['तिथि',     $tithi[$tIdx],            'moon'],
        ['वार',      $_bsDays[$adDow],         'calendar'],
        ['नक्षत्र',  $nakshatra[$nIdx],        'sparkles'],
        ['योग',      $yoga[$yIdx],             'star'],
        ['करण',      $karana[$kIdx],           'circle-dot'],
        ['पक्ष',     $curD<=15?'शुक्ल पक्ष':'कृष्ण पक्ष','sun'],
      ];
      foreach($panch as $p): ?>
        <div class="bg-white rounded-xl p-3 shadow-app">
          <div class="flex items-center gap-1.5 text-[11px] text-slate-500"><i data-lucide="<?= $p[2] ?>" class="w-3 h-3"></i><?= $p[0] ?></div>
          <div class="text-[14px] font-bold text-slate-900 mt-0.5"><?= $p[1] ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Inauspicious times (Rahu / Yam / Gulika) -->
  <section class="px-4 mt-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-2">अशुभ समय (आज)</h2>
    <div class="grid grid-cols-3 gap-2">
      <?php foreach([
        ['राहु काल','rose',$rS,$rE],
        ['यम घण्ट','orange',$yS,$yE],
        ['गुलिक काल','amber',$gS,$gE],
      ] as $r): ?>
        <div class="bg-white rounded-xl p-3 shadow-app border-l-4 border-<?= $r[1] ?>-500">
          <div class="text-[11px] text-slate-500 font-semibold"><?= $r[0] ?></div>
          <div class="text-[12px] font-bold text-slate-900 mt-0.5"><?= fmt_t($r[2]) ?> – <?= fmt_t($r[3]) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Choghadiya: काल / अमृत / शुभ etc -->
  <section class="px-4 mt-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-2">चौघडिया (शुभ–अशुभ समय)</h2>
    <div class="bg-white rounded-2xl shadow-app overflow-hidden">
      <div class="px-3 py-2 bg-slate-50 text-[11px] font-bold text-slate-600 uppercase">दिन (सूर्योदय → सूर्यास्त)</div>
      <div class="divide-y divide-slate-100">
        <?php for($i=0;$i<8;$i++):
          $sl=$slots[$i]; $c=chogh_color($sl['name']);
          $active=$currentSlot && $sl["s"]===$currentSlot["s"];
        ?>
          <div class="flex items-center justify-between px-3 py-2 <?= $active?'bg-teal-50':'' ?>">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-<?= $c ?>-500"></span>
              <span class="text-[13px] font-semibold text-slate-900"><?= $sl['name'] ?></span>
              <?php if($active): ?><span class="text-[10px] bg-teal-600 text-white px-1.5 py-0.5 rounded">अहिले</span><?php endif; ?>
            </div>
            <div class="text-[12px] text-slate-600 font-mono"><?= fmt_t($sl['s']) ?> – <?= fmt_t($sl['e']) ?></div>
          </div>
        <?php endfor; ?>
      </div>
      <div class="px-3 py-2 bg-slate-50 text-[11px] font-bold text-slate-600 uppercase">रात (सूर्यास्त → सूर्योदय)</div>
      <div class="divide-y divide-slate-100">
        <?php for($i=8;$i<16;$i++):
          $sl=$slots[$i]; $c=chogh_color($sl['name']);
          $active=$currentSlot && $sl["s"]===$currentSlot["s"];
        ?>
          <div class="flex items-center justify-between px-3 py-2 <?= $active?'bg-teal-50':'' ?>">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-<?= $c ?>-500"></span>
              <span class="text-[13px] font-semibold text-slate-900"><?= $sl['name'] ?></span>
              <?php if($active): ?><span class="text-[10px] bg-teal-600 text-white px-1.5 py-0.5 rounded">अहिले</span><?php endif; ?>
            </div>
            <div class="text-[12px] text-slate-600 font-mono"><?= fmt_t($sl['s']) ?> – <?= fmt_t($sl['e']) ?></div>
          </div>
        <?php endfor; ?>
      </div>
      <div class="px-3 py-2 text-[11px] text-slate-500 bg-slate-50 border-t border-slate-100">
        अमृत · शुभ · लाभ = शुभ • काल · रोग · उद्वेग = अशुभ • चल = सामान्य
      </div>
    </div>
  </section>

  <!-- Events list -->
  <section class="px-4 mt-4">
    <h2 class="text-[15px] font-bold text-slate-900 mb-2">यो महिनाका पर्व</h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php if(!$events): ?>
        <div class="p-4 text-center text-sm text-slate-500">कुनै पर्व छैन</div>
      <?php else: foreach($events as $d=>$list): ?>
        <div class="flex items-start gap-3 p-3">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex flex-col items-center justify-center shrink-0">
            <div class="text-[14px] font-extrabold leading-none"><?= $d ?></div>
            <div class="text-[9px] uppercase"><?= mb_substr($_bsMonths[$curM],0,3) ?></div>
          </div>
          <div class="flex-1">
            <?php foreach($list as $e): ?>
              <div class="text-[13px] font-semibold text-slate-900"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </section>

  <p class="text-center text-[10px] text-slate-400 mt-3 px-4">
    गणना अनुमानित (Kathmandu lat 27.72, lon 85.32)। पञ्चाङ्गको सटीक तिथि/नक्षत्रका लागि आधिकारिक पञ्चाङ्ग हेर्नुस्।
  </p>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
