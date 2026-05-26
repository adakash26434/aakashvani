<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        $error = 'Security check failed. Please reload and try again.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        // timing-safe compare against plain ADMIN_PASS (config.php)
        if (defined('ADMIN_PASS') && $password !== '' && hash_equals((string)ADMIN_PASS, $password)) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['is_admin']        = true;
            $_SESSION['user_name']       = 'Admin';
            header('Location: /admin/dashboard.php');
            exit;
        }
        $error = 'Invalid password.';
    }
}

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/dashboard.php');
    exit;
}
$bn = brandName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Admin Login | <?= htmlspecialchars($bn) ?></title>
  <meta name="robots" content="noindex,nofollow" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root { --bg:#fafaf9; --card:#ffffff; --border:#e7e5e4; --ink:#0f172a; --muted:#64748b; --brand:#15803d; --brand-soft:#dcfce7; }
    body{background:var(--bg);color:var(--ink);font-family:'Inter','Hind Siliguri',sans-serif;}
    .panel{background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 1px 2px rgba(15,23,42,.04),0 8px 28px -12px rgba(15,23,42,.08);}
    .inp{background:#fff;border:1px solid var(--border);border-radius:10px;padding:12px 14px;font-size:14px;color:var(--ink);width:100%;outline:none;transition:.15s;}
    .inp:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(21,128,61,.12);}
    .btn{background:var(--brand);color:#fff;padding:12px 16px;border-radius:10px;font-weight:600;font-size:14px;width:100%;transition:.15s;display:inline-flex;align-items:center;justify-content:center;gap:8px;}
    .btn:hover{background:#166534;}
    .label{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:8px;}
    .brand-mark{width:44px;height:44px;background:var(--brand);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px;letter-spacing:-.5px;}
  </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10">
  <div class="w-full max-w-sm">
    <div class="flex flex-col items-center text-center mb-8">
      <?php $logo = brandLogoUrl(); if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="" style="width:48px;height:48px;object-fit:contain;border-radius:12px;background:#fff;border:1px solid var(--border);padding:4px;" />
      <?php else: ?>
        <div class="brand-mark"><?= htmlspecialchars(brandInitials()) ?></div>
      <?php endif; ?>
      <h1 class="mt-4 text-lg font-bold tracking-tight"><?= htmlspecialchars($bn) ?></h1>
      <p class="text-xs text-[color:var(--muted)] mt-1 inline-flex items-center gap-1.5">
        <i data-lucide="shield-check" style="width:13px;height:13px;"></i> Admin Console
      </p>
    </div>

    <div class="panel p-6">
      <?php if (!empty($error)): ?>
        <div class="mb-4 px-3 py-2.5 rounded-lg text-sm flex items-center gap-2" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <form method="POST" class="space-y-4">
        <?= csrfField() ?>
        <div>
          <label class="label">Admin Password</label>
          <input type="password" name="password" class="inp" required autofocus placeholder="••••••••" />
        </div>
        <button type="submit" class="btn">
          <i data-lucide="log-in" style="width:16px;height:16px;"></i> Sign in
        </button>
      </form>
    </div>

    <p class="text-center mt-6 text-xs text-[color:var(--muted)]">
      <a href="/" class="inline-flex items-center gap-1.5 hover:text-[color:var(--brand)] transition-colors">
        <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Back to site
      </a>
    </p>
  </div>
  <script>lucide.createIcons();</script>
</body>
</html>
