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

$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$phone    = trim($_POST['phone']    ?? '');
$password = (string)($_POST['password'] ?? '');

if ($name === '' || $email === '' || strlen($password) < 6) {
    flash('नाम, इमेल र पासवर्ड (कम्तीमा ६ अक्षर) आवश्यक।', 'error');
    header('Location: /register.php'); exit;
}

try {
    if (function_exists('registerUser')) {
        $res = registerUser($email, $password, $name, $phone);
        if (!empty($res['success'])) {
            $_SESSION['user_id']   = $res['user_id'] ?? null;
            $_SESSION['user_name'] = $name;
            flash('दर्ता सफल भयो, स्वागत छ ' . htmlspecialchars($name));
            header('Location: /profile.php'); exit;
        }
        flash($res['message'] ?? 'दर्ता असफल।', 'error');
        header('Location: /register.php'); exit;
    }
    flash('User registration अहिले उपलब्ध छैन।', 'error');
    header('Location: /register.php'); exit;
} catch (Throwable $e) {
    error_log('[auth/register] ' . $e->getMessage());
    flash('दर्ता गर्न सकिएन। फेरि प्रयास गर्नुहोस्।', 'error');
    header('Location: /register.php'); exit;
}
