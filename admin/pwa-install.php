<?php
/**
 * Admin PWA Install Page
 * /admin/pwa-install.php
 *
 * Shows install instructions for all platforms + quick install button.
 * Admin can copy the install link and share with users.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();

$pwaName      = defined('PWA_NAME')       ? PWA_NAME       : (defined('SITE_NAME') ? SITE_NAME : 'आकाशवाणी');
$pwaShortName = defined('PWA_SHORT_NAME') ? PWA_SHORT_NAME : 'आकाशवाणी';
$siteUrl      = defined('SITE_URL')       ? SITE_URL       : '';
?>
<!DOCTYPE html>
<html lang="ne">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>PWA Install | <?= htmlspecialchars($pwaName) ?> Admin</title>
  <meta name="robots" content="noindex,nofollow"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="manifest" href="/api/pwa-manifest.php"/>
  <script src="/assets/js/pwa-install.js" defer></script>
  <style>
    body{background:#f8fafc;font-family:'Inter',sans-serif;color:#0f172a;}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;}
    .platform-tab{cursor:pointer;padding:10px 18px;border-radius:8px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;border:1px solid #e2e8f0;color:#64748b;transition:all .15s;}
    .platform-tab.active,.platform-tab:hover{background:#0f766e;color:#fff;border-color:#0f766e;}
    .platform-step{display:none;}
    .platform-step.active{display:block;}
    .step-num{width:24px;height:24px;border-radius:50%;background:#0f766e;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;}
    .install-btn{background:linear-gradient(135deg,#0f766e,#059669);color:#fff;padding:14px 28px;border-radius:12px;font-weight:800;font-size:15px;border:none;cursor:pointer;box-shadow:0 4px 18px rgba(15,118,110,.35);transition:all .2s;}
    .install-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(15,118,110,.4);}
    .install-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}
  </style>
</head>
<body class="min-h-screen">

<!-- Header -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
  <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="/admin/dashboard.php" class="text-slate-400 hover:text-slate-700 text-sm">← Dashboard</a>
      <span class="text-slate-200">|</span>
      <span class="font-bold text-sm text-slate-900">📲 PWA Install Manager</span>
    </div>
    <a href="/admin/settings.php" class="text-xs text-teal-600 font-bold hover:underline">⚙ PWA Settings →</a>
  </div>
</header>

<div class="max-w-3xl mx-auto px-4 py-8">

  <!-- Hero -->
  <div class="card mb-6" style="background:linear-gradient(135deg,#0f4c45,#0f766e);border-color:transparent;color:#fff;">
    <div class="flex items-start gap-4">
      <div class="text-4xl">📱</div>
      <div>
        <h1 class="text-xl font-black mb-1"><?= htmlspecialchars($pwaName) ?></h1>
        <p class="text-white/70 text-sm mb-3">यो एप यूजरका मोबाइल/ल्यापटप/ट्याब/डेस्कटपमा सिधा install हुनसक्छ — App Store/Play Store बिना।</p>
        <div class="flex gap-3 flex-wrap">
          <!-- Main install button (shown only if browser supports it) -->
          <button id="mainInstallBtn" onclick="doInstall()" class="install-btn" hidden data-pwa-install>
            📲 यो डिभाइसमा Install गर्नुस्
          </button>
          <!-- Already installed indicator -->
          <div id="installedBadge" class="hidden items-center gap-2 bg-white/15 px-4 py-3 rounded-xl text-sm font-bold" data-pwa-installed style="display:none">
            ✅ App already installed छ
          </div>
          <!-- iOS instructions toggle -->
          <button onclick="showPlatform('ios')" class="bg-white/15 border border-white/20 text-white text-sm font-bold px-4 py-3 rounded-xl hover:bg-white/25">
            🍎 iOS Guide
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- App Name display -->
  <div class="card mb-6 flex items-center justify-between">
    <div>
      <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">App को नाम</div>
      <div class="text-lg font-black text-slate-900"><?= htmlspecialchars($pwaName) ?></div>
      <div class="text-sm text-slate-500">Short name (icon): <strong><?= htmlspecialchars($pwaShortName) ?></strong></div>
    </div>
    <a href="/admin/settings.php#pwa" class="text-sm font-bold text-teal-600 border border-teal-200 px-4 py-2 rounded-lg hover:bg-teal-50">
      ✏ नाम बदल्नुस्
    </a>
  </div>

  <!-- Install Link to Share -->
  <div class="card mb-6">
    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-3">🔗 Install Link (यूजरलाई पठाउनुस्)</h2>
    <p class="text-xs text-slate-500 mb-3">यो link यूजरले browser मा open गरे install prompt आउँछ।</p>
    <div class="flex gap-2">
      <input type="text" id="installLink" value="<?= htmlspecialchars($siteUrl) ?>/?install=1"
             class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-sm font-mono text-slate-700" readonly/>
      <button onclick="copyLink()" class="bg-teal-600 text-white font-bold text-sm px-4 py-2.5 rounded-lg hover:bg-teal-700">
        📋 Copy
      </button>
    </div>
    <p id="copyMsg" class="text-xs text-emerald-600 font-bold mt-2 hidden">✅ Copied!</p>
  </div>

  <!-- Platform Guide -->
  <div class="card mb-6">
    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4">📋 Device-wise Install Guide</h2>

    <!-- Tabs -->
    <div class="flex gap-2 flex-wrap mb-5" id="platformTabs">
      <button class="platform-tab active" onclick="showPlatform('android')">🤖 Android</button>
      <button class="platform-tab" onclick="showPlatform('ios')">🍎 iPhone/iPad</button>
      <button class="platform-tab" onclick="showPlatform('chrome')">🖥 Desktop (Chrome)</button>
      <button class="platform-tab" onclick="showPlatform('edge')">🪟 Desktop (Edge)</button>
      <button class="platform-tab" onclick="showPlatform('safari')">🍏 Mac (Safari)</button>
    </div>

    <!-- Android -->
    <div class="platform-step active" id="step-android">
      <div class="bg-green-50 border border-green-100 rounded-xl p-4 mb-3">
        <p class="text-xs text-green-800 font-bold mb-2">⚡ Chrome/Samsung Browser मा automatic prompt आउँछ</p>
        <p class="text-xs text-green-700">यूजरले website visit गरेपछि browser मा "Install App" वा "Add to Home Screen" popup आउँछ।</p>
      </div>
      <?php foreach ([
        ['गर्नुस् '.$siteUrl.' मा जानुस्', 'Chrome browser बाट'],
        ['Browser को ⋮ menu खोल्नुस्'],
        ['"Add to Home Screen" वा "Install App" मा click गर्नुस्'],
        ['"Install" confirm गर्नुस् — Home Screen मा icon आउँछ!'],
      ] as $i=>$row): $step=$row[0]; $hint=$row[1]??''; ?>
      <div class="flex items-start gap-3 mb-3">
        <span class="step-num"><?= $i+1 ?></span>
        <div><p class="text-sm font-semibold text-slate-800"><?= $step ?></p>
        <?php if($hint): ?><p class="text-xs text-slate-400"><?= $hint ?></p><?php endif; ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- iOS -->
    <div class="platform-step" id="step-ios">
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-3">
        <p class="text-xs text-blue-800 font-bold mb-2">ℹ️ iOS मा Safari मात्र काम गर्छ</p>
        <p class="text-xs text-blue-700">iPhone/iPad मा Chrome/Firefox बाट install हुँदैन। Safari बाट मात्र Add to Home Screen हुन्छ।</p>
      </div>
      <?php foreach ([
        ['Safari browser मा '.$siteUrl.' खोल्नुस्', 'Chrome/Firefox ले काम गर्दैन'],
        ['तलको Toolbar मा Share button (□↑) थिच्नुस्'],
        ['"Add to Home Screen" option खोज्नुस् र tap गर्नुस्'],
        ['"Add" button थिच्नुस् — Home Screen मा "'.$pwaShortName.'" icon आउँछ!'],
      ] as $i=>$row): $step=$row[0]; $hint=$row[1]??''; ?>
      <div class="flex items-start gap-3 mb-3">
        <span class="step-num"><?= $i+1 ?></span>
        <div><p class="text-sm font-semibold text-slate-800"><?= $step ?></p>
        <?php if($hint): ?><p class="text-xs text-slate-400"><?= $hint ?></p><?php endif; ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Chrome Desktop -->
    <div class="platform-step" id="step-chrome">
      <?php foreach ([
        ['Chrome मा '.$siteUrl.' खोल्नुस्'],
        ['Address bar को दाहिने छेउमा Install icon (⊕) देखिन्छ — click गर्नुस्'],
        ['"Install" confirm गर्नुस् — Desktop/Taskbar मा icon आउँछ!'],
        ['वा: Chrome menu (⋮) → "Install '.$pwaShortName.'"'],
      ] as $i=>$row): $step=$row[0]; $hint=$row[1]??''; ?>
      <div class="flex items-start gap-3 mb-3">
        <span class="step-num"><?= $i+1 ?></span>
        <div><p class="text-sm font-semibold text-slate-800"><?= $step ?></p>
        <?php if($hint): ?><p class="text-xs text-slate-400"><?= $hint ?></p><?php endif; ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Edge Desktop -->
    <div class="platform-step" id="step-edge">
      <?php foreach ([
        ['Microsoft Edge मा '.$siteUrl.' खोल्नुस्'],
        ['Address bar को दाहिने छेउमा Install icon देखिन्छ — click गर्नुस्'],
        ['"Install" confirm — Start Menu/Taskbar/Desktop मा icon आउँछ'],
        ['वा: Edge menu (···) → Apps → Install this site as an app'],
      ] as $i=>$row): $step=$row[0]; $hint=$row[1]??''; ?>
      <div class="flex items-start gap-3 mb-3">
        <span class="step-num"><?= $i+1 ?></span>
        <div><p class="text-sm font-semibold text-slate-800"><?= $step ?></p>
        <?php if($hint): ?><p class="text-xs text-slate-400"><?= $hint ?></p><?php endif; ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Safari Mac -->
    <div class="platform-step" id="step-safari">
      <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-3">
        <p class="text-xs text-amber-800 font-bold">macOS Ventura (13)+ र Safari 17+ मा मात्र PWA काम गर्छ।</p>
      </div>
      <?php foreach ([
        ['Safari मा '.$siteUrl.' खोल्नुस्'],
        ['File menu → "Add to Dock..." click गर्नुस्'],
        ['"Add" confirm — Dock मा "'.$pwaShortName.'" icon आउँछ!'],
      ] as $i=>$row): $step=$row[0]; $hint=$row[1]??''; ?>
      <div class="flex items-start gap-3 mb-3">
        <span class="step-num"><?= $i+1 ?></span>
        <div><p class="text-sm font-semibold text-slate-800"><?= $step ?></p>
        <?php if($hint): ?><p class="text-xs text-slate-400"><?= $hint ?></p><?php endif; ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Manifest Status -->
  <div class="card">
    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-3">🔍 PWA Status Check</h2>
    <div class="grid grid-cols-2 gap-3">
      <?php $checks = [
        ['Service Worker', file_exists(dirname(__DIR__).'/sw.js')  ? '✅ sw.js found' : '❌ sw.js missing'],
        ['Manifest',       '✅ /api/pwa-manifest.php (dynamic)'],
        ['HTTPS',          isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on' ? '✅ HTTPS active' : '⚠️ HTTP (install won\'t work)'],
        ['Icons',          file_exists(dirname(__DIR__).'/assets/icons/icon-192.png') ? '✅ Icons folder found' : '⚠️ /assets/icons/ missing'],
      ]; foreach($checks as [$label,$status]): ?>
      <div class="bg-slate-50 border border-slate-100 rounded-lg px-3 py-2.5">
        <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold"><?= $label ?></div>
        <div class="text-xs font-semibold text-slate-700 mt-0.5"><?= $status ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-slate-400 mt-3">⚠️ PWA install गर्न HTTPS अनिवार्य छ। cPanel मा Free SSL (Let's Encrypt) enable गर्नुस्।</p>
  </div>

</div>

<script>
function showPlatform(p){
  document.querySelectorAll('.platform-step').forEach(function(el){el.classList.remove('active');});
  document.querySelectorAll('.platform-tab').forEach(function(el){el.classList.remove('active');});
  var el=document.getElementById('step-'+p);
  if(el) el.classList.add('active');
  document.querySelectorAll('.platform-tab').forEach(function(btn){
    if(btn.textContent.toLowerCase().includes(p)||
       (p==='android'&&btn.textContent.includes('Android'))||
       (p==='ios'&&btn.textContent.includes('iOS'))||
       (p==='chrome'&&btn.textContent.includes('Chrome'))||
       (p==='edge'&&btn.textContent.includes('Edge'))||
       (p==='safari'&&btn.textContent.includes('Safari'))) btn.classList.add('active');
  });
}

function doInstall(){
  if(window.nshPwa && window.nshPwa.install()){
    // prompt shown
  } else {
    // Fallback — show device-specific guide
    var p = window.nshPwa ? window.nshPwa.platform() : 'android';
    showPlatform(p==='ios'?'ios':p==='windows'||p==='linux'?'chrome':p==='mac'?'safari':'android');
    document.querySelector('.platform-step.active').scrollIntoView({behavior:'smooth'});
  }
}

function copyLink(){
  var val=document.getElementById('installLink').value;
  navigator.clipboard.writeText(val).then(function(){
    document.getElementById('copyMsg').classList.remove('hidden');
    setTimeout(function(){document.getElementById('copyMsg').classList.add('hidden');},2000);
  });
}

// Auto-detect platform and show tab
document.addEventListener('DOMContentLoaded',function(){
  if(window.nshPwa){
    var p=window.nshPwa.platform();
    if(p==='ios') showPlatform('ios');
    else if(p==='android') showPlatform('android');
    else if(p==='mac') showPlatform('safari');
    else if(p==='windows'||p==='linux') showPlatform('chrome');
    if(window.nshPwa.isInstalled()){
      document.getElementById('mainInstallBtn').style.display='none';
      document.getElementById('installedBadge').style.display='flex';
    }
  }
});

document.addEventListener('nsh:pwa-installable',function(){
  var btn=document.getElementById('mainInstallBtn');
  btn.removeAttribute('hidden'); btn.style.display='';
});
</script>
</body>
</html>
