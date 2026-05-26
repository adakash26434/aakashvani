<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
requireAdmin();

$configPath = __DIR__ . '/../config.php';
$msg   = getFlash();
$error = '';

// ─── Read current values from config.php via regex ────────────────────────────
function readConst(string $name, string $default = ''): string {
    $value = defined($name) ? constant($name) : $default;
    return $value;
}

// ─── Handle Settings Save ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Upload Logo ─────────────────────────────────────────────────────────────
    if ($action === 'upload_logo') {
        $assetsDir = __DIR__ . '/../assets';
        if (!is_dir($assetsDir)) @mkdir($assetsDir, 0755, true);

        if (!empty($_FILES['logo_file']['tmp_name']) && is_uploaded_file($_FILES['logo_file']['tmp_name'])) {
            $tmp  = $_FILES['logo_file']['tmp_name'];
            $info = @getimagesize($tmp);
            $allowed = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
            if (!$info || !isset($allowed[$info[2]])) {
                $error = 'Invalid image. Use PNG, JPG, GIF, or WEBP.';
            } elseif ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
                $error = 'Logo too large (max 2 MB).';
            } else {
                // remove existing logo files
                foreach (['png','jpg','gif','webp'] as $ext) @unlink($assetsDir . '/logo.' . $ext);
                $ext  = $allowed[$info[2]];
                $dest = $assetsDir . '/logo.' . $ext;
                if (move_uploaded_file($tmp, $dest)) {
                    @chmod($dest, 0644);
                    // update SITE_LOGO in config.php
                    $newLogo = '/assets/logo.' . $ext;
                    $cfg = file_get_contents($configPath);
                    if (preg_match("/define\\(\\s*'SITE_LOGO'/", $cfg)) {
                        $cfg = preg_replace("/define\\(\\s*'SITE_LOGO'\\s*,\\s*'[^']*'\\s*\\);/", "define('SITE_LOGO', " . var_export($newLogo, true) . ");", $cfg);
                    } else {
                        $cfg = preg_replace("/(define\\(\\s*'SESSION_NAME'[^\\n]*\\n)/", "$1define('SITE_LOGO', " . var_export($newLogo, true) . ");\n", $cfg);
                    }
                    file_put_contents($configPath, $cfg);
                    flash('Logo uploaded successfully.');
                    header('Location: /admin/settings.php'); exit;
                } else {
                    $error = 'Failed to save uploaded logo. Check assets/ folder permissions.';
                }
            }
        } elseif (!empty($_POST['remove_logo'])) {
            foreach (['png','jpg','gif','webp'] as $ext) @unlink(__DIR__ . '/../assets/logo.' . $ext);
            $cfg = file_get_contents($configPath);
            $cfg = preg_replace("/define\\(\\s*'SITE_LOGO'\\s*,\\s*'[^']*'\\s*\\);/", "define('SITE_LOGO', '');", $cfg);
            file_put_contents($configPath, $cfg);
            flash('Logo removed. Default icon will be shown.');
            header('Location: /admin/settings.php'); exit;
        } else {
            $error = 'No file selected.';
        }
    }

    // ── Save Database & Admin Settings ──────────────────────────────────────────
    if ($action === 'save_settings') {
        $dbHost    = trim($_POST['db_host'] ?? 'localhost');
        $dbName    = trim($_POST['db_name'] ?? '');
        $dbUser    = trim($_POST['db_user'] ?? '');
        $dbPass    = $_POST['db_pass'] ?? '';
        $siteUrl   = rtrim(trim($_POST['site_url'] ?? ''), '/');
        $siteEmail = trim($_POST['site_email'] ?? '');
        $whatsapp  = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
        $siteName  = trim($_POST['site_name'] ?? 'Aakash Person Tech');
        $tagline   = trim($_POST['site_tagline'] ?? '');
        $footerAbout = trim($_POST['footer_about'] ?? '');

        // Change admin password (only if new password provided)
        $newPass     = $_POST['new_pass'] ?? '';
        $confirmPass = $_POST['confirm_pass'] ?? '';
        $currentPass = $_POST['current_pass'] ?? '';

        if (!empty($newPass)) {
            if ($currentPass !== ADMIN_PASS) {
                $error = 'Current admin password is incorrect. Password not changed.';
            } elseif ($newPass !== $confirmPass) {
                $error = 'New passwords do not match. Password not changed.';
            } elseif (strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                $adminPass = $newPass;
            }
        }

        if (!$error) {
            $adminPass = $adminPass ?? ADMIN_PASS;

            // Build new config.php content
            $newConfig = '<?php' . "\n";
            $newConfig .= '// ─── Database Configuration ──────────────────────────────────────────────────' . "\n";
            $newConfig .= 'define(\'DB_HOST\',    ' . var_export($dbHost, true) . ');' . "\n";
            $newConfig .= 'define(\'DB_NAME\',    ' . var_export($dbName, true) . ');' . "\n";
            $newConfig .= 'define(\'DB_USER\',    ' . var_export($dbUser, true) . ');' . "\n";
            $newConfig .= 'define(\'DB_PASS\',    ' . var_export($dbPass, true) . ');' . "\n";
            $newConfig .= 'define(\'DB_CHARSET\', \'utf8mb4\');' . "\n\n";
            $newConfig .= '// ─── Site Configuration ───────────────────────────────────────────────────────' . "\n";
            $newConfig .= 'define(\'SITE_NAME\',    ' . var_export($siteName, true) . ');' . "\n";
            $newConfig .= 'define(\'SITE_TAGLINE\', ' . var_export($tagline, true) . ');' . "\n";
            $newConfig .= 'define(\'SITE_URL\',     ' . var_export($siteUrl, true) . ');  // no trailing slash' . "\n";
            $newConfig .= 'define(\'SITE_EMAIL\',   ' . var_export($siteEmail, true) . ');' . "\n";
            $newConfig .= 'define(\'WHATSAPP_NO\',  ' . var_export($whatsapp, true) . ');' . "\n";
            $newConfig .= 'define(\'ADMIN_PASS\',   ' . var_export($adminPass, true) . ');' . "\n";
            $newConfig .= 'define(\'SESSION_NAME\', \'aakashtech_admin\');' . "\n";
            $newConfig .= 'define(\'SITE_LOGO\',    ' . var_export(defined('SITE_LOGO') ? SITE_LOGO : '', true) . ');' . "\n";
            $newConfig .= 'define(\'FOOTER_ABOUT\', ' . var_export($footerAbout, true) . ');' . "\n\n";
            $newConfig .= '// ─── OG / Social Sharing ─────────────────────────────────────────────────────' . "\n";
            $newConfig .= 'define(\'OG_IMAGE\', SITE_URL . \'/assets/og-image.jpg\');' . "\n\n";
            $newConfig .= '// ─── Timezone ─────────────────────────────────────────────────────────────────' . "\n";
            $newConfig .= "date_default_timezone_set('Asia/Kathmandu');\n\n";
            $newConfig .= '// Start session' . "\n";
            $newConfig .= "if (session_status() === PHP_SESSION_NONE) {\n";
            $newConfig .= "    session_name(SESSION_NAME);\n";
            $newConfig .= "    session_start();\n";
            $newConfig .= "}\n";

            if (file_put_contents($configPath, $newConfig) !== false) {
                // If password changed, force re-login
                if ($adminPass !== ADMIN_PASS) {
                    session_destroy();
                    flash('Settings saved. Please log in with your new password.');
                    header('Location: /admin/index.php'); exit;
                }
                flash('Settings saved successfully.');
                header('Location: /admin/settings.php'); exit;
            } else {
                $error = 'Could not write to config.php. Check file permissions (chmod 644).';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="bg-[#fafaf9]" data-theme="nshdark">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Settings | आकाशवाणी Admin</title>
  <meta name="robots" content="noindex,nofollow" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.js"></script>
  <style>
    body { background:#fafaf9; color:#0f172a; font-family:'Inter',sans-serif; }
    input, select, textarea { background:#ffffff; border:1px solid #e2e8f0; padding:8px 12px; width:100%; color:#0f172a; outline:none; border-radius:6px; }
    input:focus, select:focus, textarea:focus { border-color:#7c3aed; box-shadow:0 0 0 3px rgba(167,139,250,.15); }
    input::placeholder, select::placeholder, textarea::placeholder { color:#64748b; }
    label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#64748b; margin-bottom:4px; }
    .hint { font-size:11px; color:#64748b; margin-top:4px; font-family:monospace; }
    .section { background:#ffffff; border:1px solid #e2e8f0; padding:24px; margin-bottom:24px; border-radius:10px; }
    .section-title { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:#7c3aed; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid #e2e8f0; }
    .btn-primary { background:#7c3aed; color:#fafaf9; padding:10px 16px; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition:all .15s; }
    .btn-primary:hover { background:#9370ff; }
    .error-box { border:1px solid #ef4444; background:#ef4444/10; color:#ef4444; padding:12px 16px; border-radius:6px; margin-bottom:20px; }
    .success-box { border:1px solid #10b981; background:#10b981/10; color:#10b981; padding:12px 16px; border-radius:6px; margin-bottom:20px; }
  </style>
</head>
<body class="min-h-screen flex flex-col bg-[#fafaf9]">

<!-- Header -->
<header class="border-b border-[#e2e8f0] bg-[#ffffff] sticky top-0 z-50">
  <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <svg class="h-5 w-5 text-[#7c3aed]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
      <span class="font-bold text-sm tracking-widest uppercase text-[#0f172a]">Admin<span class="text-[#7c3aed]">Terminal</span></span>
    </div>
    <div class="flex items-center gap-4">
      <a href="/admin/dashboard.php" class="text-xs text-[#64748b] hover:text-[#7c3aed]">&larr; Dashboard</a>
      <a href="/admin/logout.php" class="text-xs text-[#64748b] hover:text-[#ef4444] font-mono uppercase tracking-wider">Logout</a>
    </div>
  </div>
</header>

<div class="max-w-4xl mx-auto px-4 py-8 w-full">

  <div class="flex items-center gap-3 mb-8">
    <h1 class="text-2xl font-bold uppercase tracking-wider text-[#0f172a]">Site Settings</h1>
    <span class="text-xs font-mono text-[#64748b] border border-[#e2e8f0] px-2 py-1 rounded">config.php</span>
  </div>

  <?php if (!empty($error)): ?>
    <div class="error-box">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php
  $flash = getFlash();
  if ($flash): ?>
    <div class="success-box">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- Warning box -->
  <div class="border border-yellow-500/30 bg-yellow-500/10 px-4 py-3 text-xs text-yellow-400 font-mono mb-8 flex items-start gap-3">
    <span class="shrink-0 font-bold text-yellow-400 text-base">!</span>
    <div>
      <p class="font-bold uppercase tracking-wider mb-1">Important</p>
      <p>These settings are saved directly to <code class="text-yellow-300">config.php</code> on the server. Changing the database credentials will take effect immediately — make sure the new credentials are correct before saving, or the site will stop working.</p>
    </div>
  </div>

  <form method="POST" onsubmit="return confirmSave()">
    <input type="hidden" name="action" value="save_settings" />

    <!-- ── Database Settings ──────────────────────────────────────────────────── -->
    <div class="section">
      <div class="section-title">Database Configuration (MySQL)</div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label>Database Host</label>
          <input type="text" name="db_host" value="<?= htmlspecialchars(DB_HOST) ?>" placeholder="localhost" required />
          <p class="hint">Usually <code>localhost</code> on cPanel hosting.</p>
        </div>
        <div>
          <label>Database Name</label>
          <input type="text" name="db_name" value="<?= htmlspecialchars(DB_NAME) ?>" placeholder="your_cpanel_username_dbname" required />
          <p class="hint">In cPanel: yourusername_yourdbname</p>
        </div>
        <div>
          <label>Database Username</label>
          <input type="text" name="db_user" value="<?= htmlspecialchars(DB_USER) ?>" placeholder="your_cpanel_username_user" required />
          <p class="hint">The MySQL user created in cPanel.</p>
        </div>
        <div>
          <label>Database Password</label>
          <input type="password" name="db_pass" value="<?= htmlspecialchars(DB_PASS) ?>" placeholder="Database password" />
          <p class="hint">Leave as-is to keep the current password, or type a new one.</p>
        </div>
      </div>

      <div class="mt-5 border-t border-[#e5e7eb] pt-5">
        <button type="button" onclick="testDbConnection()"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider border border-[#e5e7eb] text-[#64748b] hover:border-[#16a34a] hover:text-[#16a34a] transition-colors">
          Test Connection
        </button>
        <span id="db-test-result" class="text-xs font-mono ml-3"></span>
      </div>
    </div>

    <!-- ── Site Settings ───────────────────────────────────────────────────────── -->
    <div class="section">
      <div class="section-title">Site Configuration</div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label>Site Name</label>
          <input type="text" name="site_name" value="<?= htmlspecialchars(SITE_NAME) ?>" placeholder="Aakash Person Tech" required />
        </div>
        <div>
          <label>Site Tagline</label>
          <input type="text" name="site_tagline" value="<?= htmlspecialchars(SITE_TAGLINE) ?>" placeholder="Global Tech. Local Prices." />
        </div>
        <div>
          <label>Site URL</label>
          <input type="url" name="site_url" value="<?= htmlspecialchars(SITE_URL) ?>" placeholder="https://yourdomain.com" required />
          <p class="hint">No trailing slash. Used for OG tags and links.</p>
        </div>
        <div>
          <label>Contact Email</label>
          <input type="email" name="site_email" value="<?= htmlspecialchars(SITE_EMAIL) ?>" placeholder="aakashpame@gmail.com" />
        </div>
        <div>
          <label>WhatsApp Number</label>
          <input type="text" name="whatsapp" value="<?= htmlspecialchars(WHATSAPP_NO) ?>" placeholder="9779851059598" />
          <p class="hint">Digits only — country code + number. No +, spaces, or dashes.</p>
        </div>
      </div>
      <div class="mt-5">
        <label>Footer About Text</label>
        <textarea name="footer_about" rows="3" style="width:100%;background:#f8fafc;border:1px solid #e5e7eb;padding:8px 12px;font-family:monospace;font-size:14px;color:#0f172a;" placeholder="Short description shown in the website footer."><?= htmlspecialchars(defined('FOOTER_ABOUT') ? FOOTER_ABOUT : '') ?></textarea>
        <p class="hint">Leave blank to use the default bilingual description.</p>
      </div>
    </div>

    <!-- ── Admin Password ─────────────────────────────────────────────────────── -->
    <div class="section">
      <div class="section-title">Change Admin Password</div>
      <p class="text-xs text-[#64748b] font-mono mb-5">Leave all three fields blank to keep the current password unchanged.</p>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
          <label>Current Password</label>
          <input type="password" name="current_pass" placeholder="Enter current password" autocomplete="current-password" />
        </div>
        <div>
          <label>New Password</label>
          <input type="password" name="new_pass" placeholder="Min. 6 characters" autocomplete="new-password" id="new_pass" />
        </div>
        <div>
          <label>Confirm New Password</label>
          <input type="password" name="confirm_pass" placeholder="Repeat new password" autocomplete="new-password" id="confirm_pass" />
          <p id="pw-match" class="hint hidden"></p>
        </div>
      </div>

      <!-- Password strength indicator -->
      <div class="mt-4">
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-mono text-[#64748b] uppercase tracking-wider">Password strength:</span>
          <span id="pw-strength-label" class="text-xs font-bold font-mono"></span>
        </div>
        <div class="h-1 bg-[#e5e7eb] w-full max-w-sm">
          <div id="pw-strength-bar" class="h-1 transition-all duration-300" style="width:0"></div>
        </div>
      </div>
    </div>

    <!-- ── Save Button ────────────────────────────────────────────────────────── -->
    <div class="flex items-center gap-4">
      <button type="submit"
              class="px-8 py-3 font-bold uppercase tracking-widest bg-[#16a34a] text-[#f8fafc] btn-glow text-sm">
        Save Settings
      </button>
      <a href="/admin/dashboard.php"
         class="px-8 py-3 font-bold uppercase tracking-wider border border-[#e5e7eb] text-[#64748b] hover:text-[#0f172a] text-sm text-center">
        Cancel
      </a>
      <span class="text-xs text-[#64748b] font-mono ml-2">Saves to: <code class="text-[#16a34a]">config.php</code></span>
    </div>

  </form>

  <!-- ── Logo Upload ────────────────────────────────────────────────────────── -->
  <?php
    $currentLogo = defined('SITE_LOGO') ? SITE_LOGO : '';
    $logoExists  = $currentLogo && file_exists(__DIR__ . '/..' . $currentLogo);
  ?>
  <form method="POST" enctype="multipart/form-data" class="section">
    <div class="section-title">Site Logo</div>
    <input type="hidden" name="action" value="upload_logo" />

    <div class="flex items-center gap-6 mb-5">
      <div class="h-20 w-20 border border-[#e5e7eb] bg-[#f8fafc] flex items-center justify-center overflow-hidden">
        <?php if ($logoExists): ?>
          <img src="<?= htmlspecialchars($currentLogo) ?>?v=<?= @filemtime(__DIR__ . '/..' . $currentLogo) ?>" alt="Logo" class="max-h-full max-w-full object-contain" />
        <?php else: ?>
          <span class="text-xs text-[#64748b] font-mono">no logo</span>
        <?php endif; ?>
      </div>
      <div class="flex-1">
        <p class="text-sm text-[#0f172a] font-mono mb-1">
          Current: <code class="text-[#16a34a]"><?= $logoExists ? htmlspecialchars($currentLogo) : '— (default icon)' ?></code>
        </p>
        <p class="hint">Upload a new logo (PNG, JPG, GIF, or WEBP, max 2 MB). Saved to <code>/assets/logo.*</code>.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <label>Choose Logo File</label>
        <input type="file" name="logo_file" accept="image/png,image/jpeg,image/gif,image/webp" />
      </div>
      <div class="flex items-end gap-3">
        <button type="submit" class="px-6 py-2 font-bold uppercase tracking-widest bg-[#16a34a] text-white btn-soft text-xs">
          Upload Logo
        </button>
        <?php if ($logoExists): ?>
          <button type="submit" name="remove_logo" value="1"
                  onclick="return confirm('Remove current logo and revert to the default icon?')"
                  class="px-4 py-2 text-xs font-bold uppercase tracking-wider border border-[#e5e7eb] text-[#64748b] hover:border-red-400 hover:text-red-500">
            Remove
          </button>
        <?php endif; ?>
      </div>
    </div>
  </form>

  <!-- ── Current Config Preview ─────────────────────────────────────────────── -->
  <div class="section mt-10">
    <div class="section-title">Current config.php Preview</div>
    <p class="text-xs text-[#64748b] font-mono mb-4">Read-only view of the active configuration. Passwords are masked.</p>
    <pre class="text-xs font-mono text-[#64748b] leading-relaxed overflow-x-auto whitespace-pre-wrap bg-[#f8fafc] border border-[#e5e7eb] p-4"><?php
      $lines = [
          "DB_HOST     = " . DB_HOST,
          "DB_NAME     = " . DB_NAME,
          "DB_USER     = " . DB_USER,
          "DB_PASS     = " . str_repeat('*', min(strlen(DB_PASS), 12)),
          "",
          "SITE_NAME   = " . SITE_NAME,
          "SITE_URL    = " . SITE_URL,
          "SITE_EMAIL  = " . SITE_EMAIL,
          "WHATSAPP_NO = " . WHATSAPP_NO,
          "ADMIN_PASS  = " . str_repeat('*', min(strlen(ADMIN_PASS), 12)),
          "SITE_LOGO   = " . (defined('SITE_LOGO') ? SITE_LOGO : '(unset)'),
          "",
          "Config file: " . realpath($configPath),
          "Last saved:  " . date('Y-m-d H:i:s', filemtime($configPath)),
      ];
      echo htmlspecialchars(implode("\n", $lines));
    ?></pre>
  </div>

  <!-- ── Permissions Check ──────────────────────────────────────────────────── -->
  <div class="section">
    <div class="section-title">File Permissions</div>
    <?php $writable = is_writable($configPath); ?>
    <div class="flex items-center gap-3">
      <div class="h-3 w-3 rounded-full <?= $writable ? 'bg-[#16a34a]' : 'bg-red-500' ?>"></div>
      <span class="text-sm font-mono">
        <?= $writable
          ? 'config.php is writable — settings can be saved.'
          : 'config.php is NOT writable. Run: <code class="text-yellow-300">chmod 644 config.php</code> via SSH or cPanel File Manager.' ?>
      </span>
    </div>
    <p class="text-xs text-[#64748b] font-mono mt-3">
      Path: <code class="text-[#16a34a]"><?= htmlspecialchars(realpath($configPath)) ?></code>
    </p>
  </div>

</div><!-- /max-w-4xl -->

<script>
function confirmSave() {
  return confirm('Save these settings to config.php? The site will use the new values immediately.');
}

// ── Password strength meter ───────────────────────────────────────────────────
const newPass = document.getElementById('new_pass');
const confirmPass = document.getElementById('confirm_pass');
const strengthBar = document.getElementById('pw-strength-bar');
const strengthLabel = document.getElementById('pw-strength-label');
const matchHint = document.getElementById('pw-match');

newPass.addEventListener('input', () => {
  const val = newPass.value;
  let score = 0;
  if (val.length >= 6) score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { pct: '0%', color: '#e5e7eb', label: '' },
    { pct: '25%', color: '#ef4444', label: 'Weak' },
    { pct: '50%', color: '#f97316', label: 'Fair' },
    { pct: '75%', color: '#eab308', label: 'Good' },
    { pct: '100%', color: '#16a34a', label: 'Strong' },
  ];
  const level = levels[Math.min(score, 4)];
  strengthBar.style.width = val.length ? level.pct : '0%';
  strengthBar.style.background = level.color;
  strengthLabel.textContent = val.length ? level.label : '';
  strengthLabel.style.color = level.color;
  checkMatch();
});

confirmPass.addEventListener('input', checkMatch);

function checkMatch() {
  if (!confirmPass.value) { matchHint.classList.add('hidden'); return; }
  matchHint.classList.remove('hidden');
  if (newPass.value === confirmPass.value) {
    matchHint.textContent = 'Passwords match';
    matchHint.style.color = '#16a34a';
  } else {
    matchHint.textContent = 'Passwords do not match';
    matchHint.style.color = '#ef4444';
  }
}

// ── Test DB Connection ────────────────────────────────────────────────────────
function testDbConnection() {
  const result = document.getElementById('db-test-result');
  result.textContent = 'Testing...';
  result.style.color = '#64748b';

  const form = document.querySelector('form');
  const data = new FormData();
  data.append('action', 'test_db');
  data.append('db_host', form.db_host.value);
  data.append('db_name', form.db_name.value);
  data.append('db_user', form.db_user.value);
  data.append('db_pass', form.db_pass.value);

  fetch('/admin/test-db.php', { method: 'POST', body: data })
    .then(r => r.json())
    .then(d => {
      result.textContent = d.ok ? '✓ Connection successful!' : '✗ ' + d.error;
      result.style.color = d.ok ? '#16a34a' : '#ef4444';
    })
    .catch(() => {
      result.textContent = '✗ Request failed';
      result.style.color = '#ef4444';
    });
}
</script>
</body>
</html>
