<?php
// auth/register.php — sign-up handler
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /register.php'); exit;
}
if (!csrfVerify()) {
    flash('Security check failed. Please reload and try again.', 'error');
    header('Location: /register.php'); exit;
}

$name            = trim($_POST['name'] ?? '');
$email           = trim($_POST['email'] ?? '');
$phone           = trim($_POST['phone'] ?? '');
$password        = (string)($_POST['password'] ?? '');
$passwordConfirm = (string)($_POST['password_confirm'] ?? '');

if ($name === '' || $email === '') {
    flash('नाम र इमेल आवश्यक।', 'error');
    header('Location: /register.php'); exit;
}

if (strlen($password) < 6) {
    flash('पासवर्ड कम्तीमा ६ अक्षर हुनुपर्छ।', 'error');
    header('Location: /register.php'); exit;
}

if ($password !== $passwordConfirm) {
    flash('पासवर्ड मिलेन।', 'error');
    header('Location: /register.php'); exit;
}

try {
    if (function_exists('registerUser')) {
        $res = registerUser($email, $password, $name, $phone ?: null);
        if (!empty($res['success'])) {
            $_SESSION['user_id']    = $res['user_id'] ?? null;
            $_SESSION['user_name']  = $name;
            $_SESSION['auth_user_id'] = $res['user_id'] ?? null;
            flash('दर्ता सफल भयो, स्वागत छ ' . htmlspecialchars($name));
            header('Location: /profile.php'); exit;
        }
        flash($res['error'] ?? $res['message'] ?? 'दर्ता असफल।', 'error');
        header('Location: /register.php'); exit;
    }
    flash('User registration अहिले उपलब्ध छैन।', 'error');
    header('Location: /register.php'); exit;
} catch (Throwable $e) {
    error_log('[auth/register] ' . $e->getMessage());
    flash('दर्ता गर्न सकिएन। फेरि प्रयास गर्नुहोस्।', 'error');
    header('Location: /register.php'); exit;
}
