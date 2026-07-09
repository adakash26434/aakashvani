<?php
/**
 * आकाशवाणी — Register Page
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('दर्ता', 'Register') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--sp-8); }
        .auth-card { background: #fff; border-radius: var(--radius-2xl); padding: var(--sp-8); width: 100%; max-width: 420px; box-shadow: var(--shadow-xl); }
        .auth-logo { text-align: center; margin-bottom: var(--sp-6); }
        .auth-logo .brand-logo { width: 64px; height: 64px; background: var(--primary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 2rem; margin: 0 auto var(--sp-3); }
        .auth-title { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: var(--sp-6); color: var(--dark-900); }
        .form-group { margin-bottom: var(--sp-4); }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--dark-700); margin-bottom: var(--sp-2); }
        .input { width: 100%; padding: var(--sp-3) var(--sp-4); border: 1px solid var(--dark-200); border-radius: var(--radius-lg); font-size: 1rem; transition: all var(--transition); }
        .input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-50); }
        .btn-primary { width: 100%; padding: var(--sp-3) var(--sp-4); background: var(--primary); color: #fff; border: none; border-radius: var(--radius-lg); font-size: 1rem; font-weight: 600; cursor: pointer; transition: all var(--transition); }
        .btn-primary:hover { background: var(--primary-600); transform: translateY(-1px); }
        .auth-footer { text-align: center; margin-top: var(--sp-6); font-size: 0.875rem; color: var(--dark-500); }
        .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .flash-error { background: #fef2f2; color: #b91c1c; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; border: 1px solid #fecaca; }
        .flash-success { background: #f0fdf4; color: #15803d; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; border: 1px solid #bbf7d0; }
    </style>
    <script src="/assets/js/lucide.min.js"></script>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="brand-logo"><?= brandInitials() ?></div>
                <h1 style="font-size: 1.25rem; font-weight: 700; color: var(--dark-900);"><?= $t('आकाशवाणी', 'Aakashvani') ?></h1>
            </div>
            
            <h2 class="auth-title"><?= $t('नयाँ खाता बनाउनुहोस्', 'Create New Account') ?></h2>
            
            <?php if ($flash): ?>
                <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/auth/register.php">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= $t('पूरा नाम', 'Full Name') ?></label>
                    <input type="text" name="name" class="input" placeholder="<?= $t('तपाईंको नाम', 'Your name') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('इमेल', 'Email') ?></label>
                    <input type="email" name="email" class="input" placeholder="info@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('फोन (वैकल्पिक)', 'Phone (optional)') ?></label>
                    <input type="tel" name="phone" class="input" placeholder="98XXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('पासवर्ड', 'Password') ?></label>
                    <input type="password" name="password" class="input" placeholder="••••••••" required minlength="8" pattern="(?=.*[A-Z])(?=.*[0-9]).*">
                    <small style="color: var(--dark-400); font-size: 0.75rem;"><?= $t('कम्तीमा ८ अक्षर, एउटा ठूलो अक्षर र एउटा नम्बर', 'Min 8 chars, one uppercase, one number') ?></small>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('पासवर्ड पुष्टि', 'Confirm Password') ?></label>
                    <input type="password" name="password_confirm" class="input" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary"><?= $t('दर्ता गर्नुहोस्', 'Register') ?></button>
            </form>
            
            <p class="auth-footer">
                <?= $t('पहिले नै खाता छ?', 'Already have account?') ?> <a href="/login.php"><?= $t('लगइन', 'Login') ?></a>
            </p>
            
            <p style="text-align: center; margin-top: var(--sp-4);">
                <a href="/" style="color: var(--dark-400); font-size: 0.875rem;">← <?= $t('गृहपृष्ठ', 'Back to Home') ?></a>
            </p>
        </div>
    </div>
</body>
</html>