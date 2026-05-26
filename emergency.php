<?php
/** emergency.php v13 — Comprehensive emergency directory */
require_once __DIR__ . '/header.php';
$em = [
  ['प्रहरी','Police','100','shield','red'],
  ['एम्बुलेन्स','Ambulance','102','ambulance','rose'],
  ['फायर ब्रिगेड','Fire','101','flame','orange'],
  ['ट्राफिक','Traffic','103','traffic-cone','amber'],
  ['बाल हेल्पलाइन','Child Helpline','1098','baby','pink'],
  ['महिला हिंसा','Women Helpline','1145','heart','rose'],
  ['विपद् व्यवस्थापन','Disaster Mgmt','1149','siren','red'],
  ['मानसिक स्वास्थ्य','Mental Health','1660-01-3434','brain','purple'],
  ['खानेपानी','Water Supply','1166','droplet','sky'],
  ['विद्युत प्राधिकरण','Electricity','1150','zap','yellow'],
  ['एन्टी करप्सन','Anti Corruption','1064','scale','indigo'],
  ['COVID हेल्पलाइन','COVID Helpline','1115','syringe','sky'],
];
$hospitals = [
  ['वीर अस्पताल','Bir Hospital','01-4221119','काठमाडौं','सरकारी'],
  ['टिचिङ अस्पताल','TUTH','01-4412404','महाराजगन्ज','सरकारी'],
  ['पाटन अस्पताल','Patan Hospital','01-5522295','ललितपुर','सरकारी'],
  ['ग्राण्डी अस्पताल','Grande Hospital','01-5159266','धापासी','निजी'],
  ['मनमोहन मेमोरियल','Manmohan Memorial','01-4371552','काठमाडौं','निजी'],
  ['नर्भिक अस्पताल','Norvic Hospital','01-4258554','थापाथली','निजी'],
];
$bloodBanks = [
  ['भृकुटी ब्लड बैंक','01-4270737','काठमाडौं (२४/७)'],
  ['नेपाल रेडक्रस ब्लड','01-4228694','काठमाडौं'],
  ['केन्द्रीय ब्लड बैंक','01-4271613','वीर अस्पताल'],
  ['पोखरा ब्लड बैंक','061-520066','पोखरा'],
];
$provinceHospitals = [
  ['कोशी अस्पताल','021-527000','विराटनगर','कोशी प्रदेश'],
  ['नारायणी अस्पताल','051-525518','बिरगञ्ज','मधेश प्रदेश'],
  ['भरतपुर अस्पताल','056-524840','भरतपुर','बागमती प्रदेश'],
  ['लुम्बिनी अस्पताल','071-540433','बुटवल','लुम्बिनी प्रदेश'],
  ['सेती अस्पताल','091-521144','धनगढी','सुदूरपश्चिम'],
  ['पोखरा अस्पताल','061-520066','पोखरा','गण्डकी प्रदेश'],
];
$embassies = [
  ['भारतीय दूतावास','01-4410900','🇮🇳'],
  ['अमेरिकी दूतावास','01-4234000','🇺🇸'],
  ['चिनियाँ दूतावास','01-4411740','🇨🇳'],
  ['बेलायत दूतावास','01-4237100','🇬🇧'],
];
$firstAid = [
  ['हृदयघात','Heart Attack','❤️','छाती थिच्नुस् (CPR) — ३०x छाती + २x श्वास। ambulance बोलाउनुस्।'],
  ['जल्नु','Burns','🔥','चिसो पानीले १०+ मिनेट चलाउनुस्। बरफ नगर्नुस्।'],
  ['ढल्नु','Choking','😮','पछाडिबाट ५ पटक ढाड थिच्नुस् (Heimlich)।'],
  ['साँप टोक्नु','Snake Bite','🐍','नखल्नुस्, नबाँध्नुस्। सुस्त राख्नुस् र अस्पताल जानुस्।'],
  ['बेहोस','Unconscious','🫁','श्वास जाँच्नुस् — छ भने पासो अवस्थामा राख्नुस्।'],
  ['रगत बग्नु','Bleeding','🩹','सफा कपडाले थिच्नुस्। उचाल्नुस्। ५–१० मिनेट दबाब।'],
];
?>
<main class="app-main">

<!-- ── Hero ── -->
<section class="px-4 pt-3 pb-2">
  <div class="rounded-2xl p-4 bg-gradient-to-br from-red-600 to-rose-700 text-white shadow-app relative overflow-hidden">
    <div class="absolute -right-3 -top-3 text-[90px] opacity-10 leading-none">🚨</div>
    <h1 class="text-[20px] font-extrabold mb-0.5"><?= $tH('आपतकालीन सहायता','Emergency Help') ?></h1>
    <p class="text-[11px] opacity-90"><?= $tH('ट्याप गर्नुस् — तुरुन्तै कल हुन्छ','Tap to call instantly') ?></p>
  </div>
</section>

<!-- ── National hotlines ── -->
<section class="px-4 mb-4">
  <div class="grid grid-cols-2 gap-2.5">
    <?php foreach($em as $e): ?>
      <a href="tel:<?= preg_replace('/[^0-9\-]/','',$e[2]) ?>"
         class="bg-white rounded-2xl p-3 shadow-app flex items-center gap-2.5 active:scale-95 transition-transform">
        <div class="w-11 h-11 rounded-xl bg-<?= $e[4] ?>-100 text-<?= $e[4] ?>-700 flex items-center justify-center shrink-0">
          <i data-lucide="<?= $e[3] ?>" class="w-5 h-5"></i>
        </div>
        <div class="min-w-0">
          <div class="text-[11.5px] font-semibold text-slate-600 truncate ne"><?= $tH($e[0],$e[1]) ?></div>
          <div class="text-[16px] font-extrabold text-slate-900 tracking-wide"><?= $e[2] ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── First aid tips ── -->
<section class="px-4 mb-4">
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="heart-pulse" class="w-4 h-4 text-rose-500"></i>
    <?= $tH('प्राथमिक उपचार','First Aid Quick Tips') ?>
  </h2>
  <div class="space-y-2">
    <?php foreach($firstAid as $fa): ?>
    <div class="bg-white rounded-xl shadow-app p-3 flex gap-3 items-start">
      <span class="text-[22px] flex-shrink-0 leading-none mt-0.5"><?= $fa[2] ?></span>
      <div>
        <div class="text-[12px] font-bold text-slate-800 ne"><?= $tH($fa[0],$fa[1]) ?></div>
        <div class="text-[11px] text-slate-500 mt-0.5 leading-snug ne"><?= htmlspecialchars($fa[3]) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Kathmandu hospitals ── -->
<section class="px-4 mb-4">
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="hospital" class="w-4 h-4 text-emerald-600"></i>
    <?= $tH('प्रमुख अस्पताल — काठमाडौं','Major Hospitals — Kathmandu') ?>
  </h2>
  <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
    <?php foreach($hospitals as $h): ?>
      <a href="tel:<?= preg_replace('/[^0-9]/','',$h[2]) ?>" class="flex items-center gap-3 p-3 active:bg-slate-50">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
          <i data-lucide="hospital" class="w-4 h-4"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[13px] font-bold text-slate-900 ne"><?= $tH($h[0],$h[1]) ?></div>
          <div class="text-[11px] text-slate-500"><?= htmlspecialchars($h[3]) ?> ·
            <span class="<?= $h[4]==='सरकारी'?'text-teal-600':'text-orange-500' ?> font-semibold"><?= $h[4] ?></span>
          </div>
        </div>
        <div class="text-right">
          <div class="text-[12px] font-bold text-slate-800"><?= $h[2] ?></div>
          <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600 ml-auto"></i>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Province hospitals + Blood banks side by side ── -->
<section class="px-4 mb-4 grid grid-cols-1 gap-4">

  <!-- Blood banks -->
  <div>
    <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
      <i data-lucide="droplets" class="w-4 h-4 text-red-500"></i>
      <?= $tH('रक्त बैंक','Blood Banks') ?>
    </h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php foreach($bloodBanks as $b): ?>
      <a href="tel:<?= preg_replace('/[^0-9]/','',$b[1]) ?>" class="flex items-center gap-3 p-3 active:bg-slate-50">
        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
          <i data-lucide="droplets" class="w-3.5 h-3.5"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[12px] font-bold text-slate-900 ne"><?= htmlspecialchars($b[0]) ?></div>
          <div class="text-[10px] text-slate-500 ne"><?= htmlspecialchars($b[2]) ?></div>
        </div>
        <div class="text-[12px] font-bold text-red-600"><?= $b[1] ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Province hospitals -->
  <div>
    <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
      <i data-lucide="map-pin" class="w-4 h-4 text-blue-500"></i>
      <?= $tH('प्रदेशवार अस्पताल','Province Hospitals') ?>
    </h2>
    <div class="bg-white rounded-2xl shadow-app divide-y divide-slate-100">
      <?php foreach($provinceHospitals as $ph): ?>
      <a href="tel:<?= preg_replace('/[^0-9]/','',$ph[1]) ?>" class="flex items-center gap-3 p-3 active:bg-slate-50">
        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
          <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[12px] font-bold text-slate-900 ne"><?= htmlspecialchars($ph[0]) ?></div>
          <div class="text-[10px] text-slate-500 ne"><?= htmlspecialchars($ph[2]) ?> · <?= htmlspecialchars($ph[3]) ?></div>
        </div>
        <div class="text-[11.5px] font-bold text-blue-600"><?= $ph[1] ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

</section>

<!-- ── Embassy contacts ── -->
<section class="px-4 mb-6">
  <h2 class="text-[13px] font-bold text-slate-800 mb-2 flex items-center gap-1.5">
    <i data-lucide="landmark" class="w-4 h-4 text-indigo-500"></i>
    <?= $tH('दूतावास','Embassies in Nepal') ?>
  </h2>
  <div class="grid grid-cols-2 gap-2">
    <?php foreach($embassies as $emb): ?>
    <a href="tel:<?= preg_replace('/[^0-9]/','',$emb[1]) ?>"
       class="bg-white rounded-xl shadow-app p-3 flex items-center gap-2 active:bg-slate-50">
      <span class="text-[22px]"><?= $emb[2] ?></span>
      <div>
        <div class="text-[11.5px] font-bold text-slate-800 ne"><?= htmlspecialchars($emb[0]) ?></div>
        <div class="text-[11px] font-mono text-indigo-600"><?= $emb[1] ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="mt-2 bg-amber-50 border border-amber-200 rounded-xl p-3 text-[11px] text-amber-800">
    <b>⚠ नोट:</b> विदेशमा रहँदा स्थानीय आपतकालीन नम्बर (112 / 911) प्रयोग गर्नुहोस्।
  </div>
</section>

</main>
<?php require_once __DIR__ . '/footer.php'; ?>
