<?php
/**
 * /admin/content.php — Manual fallback content editor
 * For Traffic / Load-shedding / Water / Lok Sewa / Transport / Alert
 * Auth shares session with /admin/prices.php (PIN-based).
 */
session_start();
require_once __DIR__ . '/../includes/csrf.php';
$cacheDir = __DIR__ . '/../cache';
$pinFile  = $cacheDir . '/admin-pin.txt';
$ovFile   = $cacheDir . '/content-overrides.json';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$err = '';
// Login (reuses same PIN as prices) — rate-limited + CSRF-guarded
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['login_pin'])) {
    $rlKey = 'content_pin:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!function_exists('checkRateLimit') && is_file(__DIR__ . '/../functions.php')) {
        require_once __DIR__ . '/../functions.php';
    }
    if (function_exists('checkRateLimit') && !checkRateLimit($rlKey, 5, 60)) {
        $err = 'बढी प्रयास भयो। १ मिनेट पर्खनुहोस्।';
    } elseif (!csrfVerify()) {
        $err = 'Security check failed.';
    } else {
        $pin = trim($_POST['login_pin']);
        $hash = is_file($pinFile) ? file_get_contents($pinFile) : '';
        if ($hash && password_verify($pin, $hash)) { $_SESSION['nh_admin']=true; header('Location: /admin/content.php'); exit; }
        $err = 'गलत PIN';
    }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: /admin/content.php'); exit; }

if (empty($_SESSION['nh_admin'])) {
  ?><!doctype html><html><head><meta charset="utf-8"><title>Admin · Content</title><meta name="viewport" content="width=device-width,initial-scale=1"><script src="https://cdn.tailwindcss.com"></script></head>
  <body class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <form method="post" class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm">
      <h1 class="text-xl font-bold mb-1">आकाशवाणी Admin</h1>
      <p class="text-sm text-slate-500 mb-4">Content fallback panel</p>
      <?php if ($err): ?><div class="bg-rose-50 text-rose-700 text-sm p-2 rounded mb-3"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <input type="password" name="login_pin" placeholder="Admin PIN" required class="w-full border rounded-lg px-3 py-2 mb-3"/>
      <?= csrfField() ?>
      <button class="w-full bg-teal-600 text-white py-2 rounded-lg font-semibold">Login</button>
      <p class="text-xs text-slate-400 mt-3">PIN सेट गर्न <a href="/admin/prices.php" class="underline">/admin/prices.php</a> मा जानुहोस्।</p>
    </form>
  </body></html><?php exit;
}

$data = is_file($ovFile) ? (json_decode((string)file_get_contents($ovFile), true) ?: []) : [];

$SECTIONS = [
  'traffic'      => ['ट्राफिक सूचना', 'सडक बन्द, जाम, प्रतिबन्ध', 'ट्राफिक प्रहरी कार्यालय', 'https://traffic.nepalpolice.gov.np/'],
  'loadshedding' => ['लोडसेडिङ तालिका', 'समूह र समय', 'नेपाल विद्युत प्राधिकरण (NEA)', 'https://nea.org.np/'],
  'water'        => ['पानी आपूर्ति', 'क्षेत्र र समय', 'KUKL', 'https://kathmanduwater.org/'],
  'loksewa'      => ['लोक सेवा सूचना (Pinned)', 'महत्वपूर्ण विज्ञापन', 'PSC Nepal', 'https://psc.gov.np/'],
  'transport'    => ['यातायात नोटिस', 'फ्लाइट/बस अपडेट', 'CAA Nepal / DOTM', 'https://caanepal.gov.np/'],
  'alert'        => ['सामान्य अलर्ट प्रसारण', 'महत्वपूर्ण घोषणा', 'आकाशवाणी', ''],
];
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin · Content · आकाशवाणी</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-50 min-h-screen">
<header class="bg-teal-700 text-white p-4 flex items-center justify-between">
  <div><h1 class="font-bold text-lg">आकाशवाणी — Content Admin</h1><p class="text-xs opacity-90">Fallback content (when live API fails)</p></div>
  <div class="flex gap-2 text-sm">
    <a href="/admin/prices.php" class="bg-white/15 px-3 py-1 rounded">Prices</a>
    <a href="?logout=1" class="bg-white/15 px-3 py-1 rounded">Logout</a>
  </div>
</header>

<main class="max-w-3xl mx-auto p-4 space-y-4">
  <p class="bg-amber-50 border border-amber-200 text-amber-800 text-sm p-3 rounded-lg">
    यहाँ राख्नुभएको डाटा तब मात्र देखाइनेछ जब live API/source बाट डाटा आउँदैन — अथवा "Enabled" मा रहे आधिकारिक रूपमा देखाइन्छ। हरेक entry मा source सहित save गर्नुहोस्।
  </p>

  <?php foreach ($SECTIONS as $key=>$meta): $cur = $data[$key] ?? []; $items = $cur['items'] ?? []; ?>
  <section class="bg-white rounded-2xl shadow p-4">
    <div class="flex items-start justify-between mb-2">
      <div>
        <h2 class="font-bold text-slate-900"><?= htmlspecialchars($meta[0]) ?></h2>
        <p class="text-xs text-slate-500"><?= htmlspecialchars($meta[1]) ?></p>
      </div>
      <span class="text-[10px] <?= !empty($cur['enabled'])?'bg-emerald-100 text-emerald-700':'bg-slate-100 text-slate-500' ?> px-2 py-0.5 rounded-full font-bold">
        <?= !empty($cur['enabled']) ? 'ON' : 'OFF' ?>
      </span>
    </div>
    <form class="space-y-2 cf" data-key="<?= $key ?>">
      <textarea name="items" rows="4" placeholder='Each line = one entry. Format: title | detail | optional-url' class="w-full border rounded-lg p-2 text-sm font-mono"><?php
        foreach ($items as $it) {
          echo htmlspecialchars(($it['title']??'').' | '.($it['detail']??'').($it['url']?(' | '.$it['url']):''))."\n";
        } ?></textarea>
      <div class="grid grid-cols-2 gap-2">
        <input name="source" placeholder="Source name" value="<?= htmlspecialchars($cur['source'] ?? $meta[2]) ?>" class="border rounded-lg p-2 text-sm"/>
        <input name="source_url" placeholder="Source URL" value="<?= htmlspecialchars($cur['source_url'] ?? $meta[3]) ?>" class="border rounded-lg p-2 text-sm"/>
      </div>
      <input name="note" placeholder="Note (optional)" value="<?= htmlspecialchars($cur['note'] ?? '') ?>" class="w-full border rounded-lg p-2 text-sm"/>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="enabled" <?= !empty($cur['enabled'])?'checked':'' ?>/> Enable / show on site</label>
      <div class="flex justify-between items-center">
        <span class="text-xs text-slate-400"><?= !empty($cur['updatedAt']) ? 'Last: '.htmlspecialchars($cur['updatedAt']) : '' ?></span>
        <button class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-4 py-1.5 rounded-lg text-sm">Save</button>
      </div>
      <div class="msg text-xs"></div>
    </form>
  </section>
  <?php endforeach; ?>
</main>

<script>
document.querySelectorAll('form.cf').forEach(function(f){
  f.addEventListener('submit', function(e){
    e.preventDefault();
    var key = f.dataset.key;
    var lines = (f.items.value||'').split(/\n/).map(s=>s.trim()).filter(Boolean);
    var items = lines.map(function(ln){
      var p = ln.split('|').map(s=>s.trim());
      return { title:p[0]||'', detail:p[1]||'', url:p[2]||'' };
    });
    var body = { key:key, items:items, source:f.source.value, source_url:f.source_url.value, note:f.note.value, enabled:f.enabled.checked };
    var m = f.querySelector('.msg'); m.textContent='Saving…'; m.className='msg text-xs text-slate-500';
    fetch('/api/content-overrides.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)})
      .then(r=>r.json()).then(d=>{
        if(d && d.ok){ m.textContent='✓ Saved'; m.className='msg text-xs text-emerald-600'; }
        else { m.textContent='✗ '+ (d&&d.error||'Failed'); m.className='msg text-xs text-rose-600'; }
      }).catch(()=>{ m.textContent='✗ Network error'; m.className='msg text-xs text-rose-600'; });
  });
});
</script>
</body></html>
