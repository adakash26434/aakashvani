<?php
require_once __DIR__ . '/config.php';
$lang=siteLang();
$isNepali=($lang!=='en');
$t=fn($ne,$en)=>$isNepali?$ne:$en;
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t('लगइन','Login')?> | <?=$t('आकाशवाणी','Aakashvani')?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--space-8); }
        .login-card { background: #fff; border-radius: var(--radius-2xl); padding: var(--space-8); width: 100%; max-width: 400px; box-shadow: var(--shadow-xl); }
        .login-logo { text-align: center; margin-bottom: var(--space-6); }
        .login-logo .brand-logo { width: 64px; height: 64px; background: var(--primary); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 2rem; margin: 0 auto var(--space-3); }
    </style>

    <style>
        /* Responsive */
        @media (max-width: 480px) {
            .login-page, .auth-page { padding: var(--space-4); }
            .login-card, .auth-card { padding: var(--space-6); }
            .login-logo .brand-logo { width: 48px; height: 48px; font-size: 1.5rem; }
        }
    </style>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-logo">
                <div class="brand-logo">आ</div>
                <h1 class="text-xl font-bold"><?=$t('आकाशवाणी','Aakashvani')?></h1>
                <p class="text-sm text-secondary"><?=$t('सूचनाको खुला आकाश','Your Gateway to Information')?></p>
            </div>
            
            <h2 class="text-lg font-bold mb-6 text-center"><?=$t('लगइन गर्नुहोस्','Sign In')?></h2>
            
            <form>
                <div class="form-group">
                    <label class="form-label"><?=$t('इमेल','Email')?></label>
                    <input type="email" class="input" placeholder="info@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label"><?=$t('पासवर्ड','Password')?></label>
                    <input type="password" class="input" placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary w-full"><?=$t('लगइन','Login')?></button>
            </form>
            
            <p class="text-center text-sm text-secondary mt-6">
                <?=$t('खाता छैन?','No account?')?> <a href="/register.php" class="text-primary font-semibold"><?=$t('दर्ता गर्नुहोस्','Register')?></a>
            </p>
            
            <p class="text-center mt-4">
                <a href="/" class="text-sm text-secondary">← <?=$t('गृहपृष्ठ','Back to Home')?></a>
            </p>
        </div>
    </div>
    <script src="/assets/js/app.js"></script>
</body>
</html>
