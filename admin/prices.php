<?php
/**
 * /admin/prices.php — Manual price override admin panel
 *
 * Workflow:
 *  • First visit: shows "set PIN" form (creates /cache/admin-pin.txt)
 *  • Returning  : shows login form (PIN check)
 *  • Logged in  : edit form for Gold / Silver / Petrol / Diesel / LPG / etc.
 *
 * Saves via fetch POST → /api/overrides.php (which we own, with session check)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
session_start();

$cacheDir = __DIR__ . '/../cache';
$pinFile  = $cacheDir . '/admin-pin.txt';
$ovFile   = $cacheDir . '/overrides.json';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);

$err = '';
$msg = '';

// Rate-limit PIN attempts: 5 per minute per IP
if ($_SERVER['REQUEST_METHOD']==='POST' && (isset($_POST['set_pin']) || isset($_POST['login_pin']))) {
    $rlKey = 'prices_pin:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!checkRateLimit($rlKey, 5, 60)) {
        $err = 'बढी प्रयास भयो। १ मिनेट पर्खनुहोस्।';
    } elseif (!csrfVerify()) {
        $err = 'Security check failed. Reload and try again.';
    }
}

// ── Set PIN (first run only) ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['set_pin']) && !is_file($pinFile) && empty($err)) {
  $pin = trim($_POST['set_pin']);
  if (strlen($pin) < 4) $err = 'PIN कम्तीमा ४ अक्षरको हुनुपर्छ';
  else {
    file_put_contents($pinFile, password_hash($pin, PASSWORD_DEFAULT));
    @chmod($pinFile, 0600);
    $_SESSION['nh_admin'] = true;
    header('Location: /admin/prices.php'); exit;
  }
}

// ── Login ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['login_pin']) && empty($err)) {
  $pin = trim($_POST['login_pin']);
  $hash = is_file($pinFile) ? file_get_contents($pinFile) : '';
  if ($hash && password_verify($pin, $hash)) {
    $_SESSION['nh_admin'] = true;
    header('Location: /admin/prices.php'); exit;
  } else { $err = 'गलत PIN'; }
}

// ── Logout ───────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) { unset($_SESSION['nh_admin']); header('Location: /admin/prices.php'); exit; }

$isLogged = !empty($_SESSION['nh_admin']);
$pinSet   = is_file($pinFile);
$cur = is_file($ovFile) ? json_decode(file_get_contents($ovFile), true) : [];
$g = $cur['gold']   ?? ['use'=>false];
$p = $cur['petrol'] ?? ['use'=>false];
$f = $cur['forex']  ?? ['use'=>false];
$v = fn($a,$k,$d='')=> htmlspecialchars((string)($a[$k] ?? $d), ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="ne">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin · Price Overrides — Aakashvani</title>
<style>
:root{--brand:#7c2d12;--bg:#f8fafc;--ink:#0f172a;--card:#fff;--bd:#e2e8f0;--ok:#16a34a;--err:#dc2626;--mute:#64748b}
*{box-sizing:border-box}body{margin:0;font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--ink);padding:20px}
.wrap{max-width:780px;margin:0 auto}
.head{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.head .logo{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#7c2d12,#dc2626);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px}
.head h1{font-size:18px;margin:0}.head p{margin:2px 0 0;font-size:12px;color:var(--mute)}
.card{background:var(--card);border:1px solid var(--bd);border-radius:14px;padding:18px;margin-bottom:14px;box-shadow:0 1px 2px rgba(0,0,0,.03)}
.card h2{font-size:14px;margin:0 0 12px;display:flex;align-items:center;gap:8px;color:var(--brand)}
.row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}
label{display:block;font-size:11px;font-weight:700;color:var(--mute);margin-bottom:4px;text-transform:uppercase;letter-spacing:.04em}
input[type=number],input[type=text],input[type=password]{width:100%;padding:10px 12px;border:1px solid var(--bd);border-radius:10px;font-size:14px;font-family:inherit}
input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(124,45,18,.08)}
.toggle{display:flex;align-items:center;gap:8px;padding:8px 10px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:12px;cursor:pointer;font-size:13px;font-weight:600;color:#991b1b}
.toggle input{width:18px;height:18px;cursor:pointer}
.toggle.on{background:#dcfce7;border-color:#86efac;color:#166534}
.btn{display:inline-flex;align-items:center;gap:6px;padding:11px 18px;background:var(--brand);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none}
.btn:hover{background:#5b1d0c}.btn.sec{background:#fff;color:var(--ink);border:1px solid var(--bd)}
.alert{padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:14px}
.alert.err{background:#fef2f2;color:var(--err);border:1px solid #fecaca}
.alert.ok{background:#dcfce7;color:var(--ok);border:1px solid #86efac}
.hint{font-size:11px;color:var(--mute);margin-top:6px;line-height:1.5}
.foot{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:18px}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div class="logo">न</div>
    <div><h1>Aakashvani · Admin Panel</h1><p>हाते मूल्य व्यवस्थापन (API fallback)</p></div>
    <?php if ($isLogged): ?><a href="?logout=1" style="margin-left:auto;font-size:12px;color:var(--mute);text-decoration:none">Logout →</a><?php endif; ?>
  </div>

  <?php if ($err): ?><div class="alert err">⚠ <?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert ok">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <?php if (!$pinSet): ?>
    <div class="card">
      <h2>🔐 पहिलो पटक — Admin PIN बनाउनुहोस्</h2>
      <form method="post">
        <label>नयाँ PIN (कम्तीमा ४ अक्षर)</label>
        <input type="password" name="set_pin" required minlength="4" autofocus>
        <?= csrfField() ?>
        <div class="hint">यो PIN <code>/cache/admin-pin.txt</code> मा hash गरेर save हुन्छ। बिर्सिएमा यो file delete गरे फेरि बनाउन सकिन्छ।</div>
        <div style="margin-top:14px"><button class="btn" type="submit">Set PIN र Login</button></div>
      </form>
    </div>
  <?php elseif (!$isLogged): ?>
    <div class="card">
      <h2>🔐 Admin Login</h2>
      <form method="post">
        <label>PIN</label>
        <input type="password" name="login_pin" required autofocus>
        <?= csrfField() ?>
        <div style="margin-top:14px"><button class="btn" type="submit">Login</button></div>
      </form>
    </div>
  <?php else: ?>
    <div id="saveAlert"></div>

    <form id="ovForm">
      <!-- ─── Gold / Silver ─── -->
      <div class="card">
        <h2>🥇 सुन / चाँदी (per तोला, NPR)</h2>
        <label class="toggle <?= !empty($g['use']) ? 'on' : '' ?>">
          <input type="checkbox" name="gold_use" <?= !empty($g['use']) ? 'checked' : '' ?>>
          <span>Manual override सक्रिय गर्ने (नभए live API बाट आउँछ)</span>
        </label>
        <div class="row">
          <div><label>हल्लमार्क (Hallmark)</label><input type="number" name="gold_hallmarkPerTola" step="0.01" value="<?= $v($g,'hallmarkPerTola') ?>" placeholder="300000"></div>
          <div><label>तेजाबी (Tajbi)</label><input type="number" name="gold_tajbiPerTola" step="0.01" value="<?= $v($g,'tajbiPerTola') ?>" placeholder="295000"></div>
        </div>
        <div class="row">
          <div><label>चाँदी (Silver)</label><input type="number" name="gold_silverPerTola" step="0.01" value="<?= $v($g,'silverPerTola') ?>" placeholder="1800"></div>
          <div></div>
        </div>
        <div class="hint">Source reference: <a href="https://www.fenegosida.org" target="_blank" rel="noopener">FENEGOSIDA</a> को आधिकारिक दर हेरेर भर्नुहोस्।</div>
      </div>

      <!-- ─── Fuel / NOC ─── -->
      <div class="card">
        <h2>⛽ इन्धन मूल्य — NOC Nepal (NPR)</h2>
        <label class="toggle <?= !empty($p['use']) ? 'on' : '' ?>">
          <input type="checkbox" name="petrol_use" <?= !empty($p['use']) ? 'checked' : '' ?>>
          <span>Manual override सक्रिय (NOC live data नआएमा यो देखिन्छ)</span>
        </label>
        <div class="row">
          <div><label>पेट्रोल (per litre)</label><input type="number" name="petrol_petrol" step="0.01" value="<?= $v($p,'petrol') ?>" placeholder="214"></div>
          <div><label>डिजेल (per litre)</label><input type="number" name="petrol_diesel" step="0.01" value="<?= $v($p,'diesel') ?>" placeholder="222"></div>
        </div>
        <div class="row">
          <div><label>मट्टितेल (per litre)</label><input type="number" name="petrol_kerosene" step="0.01" value="<?= $v($p,'kerosene') ?>" placeholder="222"></div>
          <div><label>हवाई इन्धन (per litre)</label><input type="number" name="petrol_aviation_fuel" step="0.01" value="<?= $v($p,'aviation_fuel') ?>" placeholder="145"></div>
        </div>
        <div class="row">
          <div><label>LPG सिलिन्डर (14.2 kg)</label><input type="number" name="petrol_lpg_cylinder" step="0.01" value="<?= $v($p,'lpg_cylinder') ?>" placeholder="1900"></div>
          <div><label>LPG प्रति kg</label><input type="number" name="petrol_lpg_per_kg" step="0.01" value="<?= $v($p,'lpg_per_kg') ?>" placeholder="133.8"></div>
        </div>
        <div class="hint">Source: <a href="https://www.nepaloil.com.np" target="_blank" rel="noopener">nepaloil.com.np</a></div>
      </div>

      <!-- ─── Forex ─── -->
      <div class="card">
        <h2>💵 USD · NPR विदेशी मुद्रा</h2>
        <label class="toggle <?= !empty($f['use']) ? 'on' : '' ?>">
          <input type="checkbox" name="forex_use" <?= !empty($f['use']) ? 'checked' : '' ?>>
          <span>Manual override सक्रिय</span>
        </label>
        <div class="row">
          <div><label>1 USD = ? NPR</label><input type="number" name="forex_usdNpr" step="0.01" value="<?= $v($f,'usdNpr') ?>" placeholder="135.5"></div>
          <div></div>
        </div>
        <div class="hint">Source: <a href="https://www.nrb.org.np/forex/" target="_blank" rel="noopener">Nepal Rastra Bank</a></div>
      </div>

      <!-- ─── Note ─── -->
      <div class="card">
        <h2>📝 Note (वैकल्पिक)</h2>
        <input type="text" name="note" value="<?= $v($cur,'note') ?>" placeholder="e.g. NOC ले आज मूल्य घटाएको">
      </div>

      <div class="foot">
        <a class="btn sec" href="/">← Home</a>
        <button type="submit" class="btn">💾 Save Overrides</button>
      </div>
    </form>

    <p class="hint" style="text-align:center;margin-top:14px">
      अन्तिम update: <?= htmlspecialchars($cur['updatedAt'] ?? 'never') ?>
    </p>

    <script>
    document.getElementById('ovForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.target);
      const payload = {
        gold:   {use:fd.get('gold_use')==='on'},
        petrol: {use:fd.get('petrol_use')==='on'},
        forex:  {use:fd.get('forex_use')==='on'},
        note:   fd.get('note') || ''
      };
      for (const [k,v] of fd.entries()) {
        if (v==='' || v==='on') continue;
        const [sec, ...rest] = k.split('_'); const key = rest.join('_');
        if (payload[sec] && key) payload[sec][key] = parseFloat(v);
      }
      const r = await fetch('/api/overrides.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload),credentials:'same-origin'});
      const j = await r.json();
      const a = document.getElementById('saveAlert');
      if (j.ok) { a.innerHTML='<div class="alert ok">✓ Save भयो — dashboard ले अब यी values देखाउनेछ</div>'; setTimeout(()=>a.innerHTML='',4000); }
      else     { a.innerHTML='<div class="alert err">⚠ '+(j.error||'Error')+'</div>'; }
      window.scrollTo({top:0,behavior:'smooth'});
    });
    // Toggle visual feedback
    document.querySelectorAll('.toggle input').forEach(cb=>{
      cb.addEventListener('change',()=>cb.closest('.toggle').classList.toggle('on',cb.checked));
    });
    </script>
  <?php endif; ?>
</div>
</body>
</html>
