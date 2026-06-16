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
    <title><?=$t('सम्पर्क','Contact')?> | आकाशवाणी</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between">
                    <a href="/" class="header-brand"><div class="brand-logo">आ</div><span class="brand-name"><?=$t('आकाशवाणी','Aakashvani')?></span></a>
                    <nav class="header-nav">
                        <a href="/" class="nav-link"><?=$t('गृह','Home')?></a>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <section class="page-header" style="background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff">
        <div class="container">
            <h1 class="page-title"><?=$t('सम्पर्क','Contact Us')?></h1>
        </div>
    </section>
    <section class="section" style="padding:var(--space-12) 0">
        <div class="container" style="max-width:600px">
            <div class="card card-body">
                <div class="form-group">
                    <label class="form-label"><?=$t('इमेल','Email')?></label>
                    <input type="email" class="input" placeholder="info@aakashvani.com">
                </div>
                <div class="form-group">
                    <label class="form-label"><?=$t('सन्देश','Message')?></label>
                    <textarea class="input" rows="5" placeholder="<?=$t('तपाईंको सन्देश...','Your message...')?>"></textarea>
                </div>
                <button class="btn btn-primary"><?=$t('पठाउनुहोस्','Send')?></button>
            </div>
        </div>
    </section>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?=date('Y')?> <?=$t('आकाशवाणी','Aakashvani')?></p></div>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
</body>
</html>
