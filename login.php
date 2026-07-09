<?php
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
    <title><?= $t('लगइन', 'Login') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--sp-8); }
        .login-card { background: #fff; border-radius: var(--radius-2xl); padding: var(--sp-8); width: 100%; max-width: 400px; box-shadow: var(--shadow-xl); }
        .login-logo { text-align: center; margin-bottom: var(--sp-6); }
        .login-logo .brand-logo { width: 64px; height: 64px; background: var(--primary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 2rem; margin: 0 auto var(--sp-3); }
        .flash-error { background: #fef2f2; color: #b91c1c; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; border: 1px solid #fecaca; }
        .flash-success { background: #f0fdf4; color: #15803d; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; border: 1px solid #bbf7d0; }
    </style>
    <script src="/assets/js/lucide.min.js"></script>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">
                <div class="brand-logo"><?= brandInitials() ?></div>
                <h1 class="text-xl font-bold"><?= $t('आकाशवाणी', 'Aakashvani') ?></h1>
                <p class="text-sm text-secondary"><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></p>
            </div>
            
            <h2 class="text-lg font-bold mb-6 text-center"><?= $t('लगइन गर्नुहोस्', 'Sign In') ?></h2>
            
            <?php if ($flash): ?>
                <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-success' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="/auth/login.php">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label"><?= $t('इमेल', 'Email') ?></label>
                    <input type="email" name="login" class="input" placeholder="info@example.com" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('पासवर्ड', 'Password') ?></label>
                    <input type="password" name="password" class="input" placeholder="••••••••" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="remember" id="remember" style="width: auto;">
                    <label for="remember" style="font-size: 0.875rem; margin-bottom: 0;"><?= $t('मोहर राख्नुहोस्', 'Remember me') ?></label>
                </div>
                <button type="submit" class="btn btn-primary w-full"><?= $t('लगइन', 'Login') ?></button>
            </form>
            
            <p class="text-center text-sm text-secondary mt-6">
                <?= $t('खाता छैन?', 'No account?') ?> <a href="/register.php" class="text-primary font-semibold"><?= $t('दर्ता गर्नुहोस्', 'Register') ?></a>
            </p>
            
            <p class="text-center mt-4">
                <a href="/" class="text-sm text-secondary">← <?= $t('गृहपृष्ठ', 'Back to Home') ?></a>
            </p>
        </div>
    </div>
</body>
</html>
