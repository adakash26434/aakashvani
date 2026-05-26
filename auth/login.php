<?php
// auth/login.php — handler that processes POST or shows login form
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify()) {
        flash('Security check failed. Please reload and try again.', 'error');
        header('Location: /login.php'); exit;
    }
    $login    = trim($_POST['login']    ?? '');
    $password = (string)($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if ($login === '' || $password === '') {
        flash('इमेल/मोबाइल र पासवर्ड दुवै आवश्यक।', 'error');
        header('Location: /login.php'); exit;
    }

    // Try DB-based user if users table exists; otherwise fallback to admin only.
    $ok = false; $userId = null; $userName = $login; $isAdmin = false;
    try {
        $pdo = function_exists('db') ? db() : null;
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id, name, email, phone, password_hash, is_admin
                                   FROM users WHERE email=? OR phone=? LIMIT 1");
            $stmt->execute([$login, $login]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($u && password_verify($password, (string)$u['password_hash'])) {
                $ok = true; $userId = (int)$u['id']; $userName = $u['name'] ?: $login;
                $isAdmin = !empty($u['is_admin']);
            }
        }
    } catch (Throwable $e) { /* users table may not exist yet */ }

    // Admin fallback (config-defined ADMIN_PASS) — timing-safe compare
    if (!$ok && defined('ADMIN_PASS') && $password !== '' &&
        hash_equals((string)ADMIN_PASS, $password) &&
        in_array(strtolower($login), ['admin', strtolower((string)(defined('SITE_EMAIL')?SITE_EMAIL:''))], true)) {
        $ok = true; $isAdmin = true; $userName = 'Admin';
    }

    if (!$ok) {
        flash('गलत इमेल वा पासवर्ड।', 'error');
        header('Location: /login.php'); exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['is_admin']  = $isAdmin;
    if ($isAdmin) $_SESSION['admin_logged_in'] = true;
    if ($remember) {
        setcookie('nsh_remember', '1', time()+60*60*24*30, '/', '', true, true);
    }
    flash('स्वागत छ, ' . htmlspecialchars($userName));
    header('Location: ' . ($isAdmin ? '/admin/dashboard.php' : '/profile.php'));
    exit;
}

// GET → show login form (delegate to existing UI)
require __DIR__ . '/../login.php';
