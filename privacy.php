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
    <title><?=$t('गोपनीयता नीति','Privacy Policy')?> | <?=$t('आकाशवाणी','Aakashvani')?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-grid">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <div class="brand-text">
                            <h1><?=$t('आकाशवाणी','Aakashvani')?></h1>
                            <span><?=$t('सूचनाको खुला आकाश','Your Gateway to Information')?></span>
                        </div>
                    </a>
                    <nav class="main-nav">
                        <div class="nav-list">
                            <a href="/" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?=$t('गृह','Home')?></a>
                            <a href="/news.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg><?=$t('समाचार','News')?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    
    <section style="background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff">
        <div class="container">
            <h1 class="text-3xl font-bold"><?=$t('गोपनीयता नीति','Privacy Policy')?></h1>
        </div>
    </section>
    
    <section style="padding:var(--space-12) 0">
        <div class="container" style="max-width:800px">
            <div class="card card-body">
                <h2 style="margin-bottom:var(--space-4)"><?=$t('हाम्रो प्रतिबद्धता','Our Commitment')?></h2>
                <p style="color:var(--dark-600);line-height:1.8">
                    <?=$isNepali?'आकाशवाणीले तपाईंको गोपनीयतालाई गम्भीरता राख्छ। हामी तपाईंको व्यक्तिगत जानकारी सुरक्षित राख्छौं।':'Aakashvani takes your privacy seriously. We keep your personal information safe and secure.'?>
                </p>
                <h2 style="margin-top:var(--space-8);margin-bottom:var(--space-4)"><?=$t('संकलन गरिएको जानकारी','Information We Collect')?></h2>
                <ul style="color:var(--dark-600);line-height:1.8;padding-left:var(--space-6)">
                    <li><?=$isNepali?'प्रयोगकर्ताको IP ठेगाना':'IP address'?></li>
                    <li><?=$isNepali?'ब्राउजर प्रकार':'Browser type'?></li>
                    <li><?=$isNepali?'पृष्ठ भ्रमण इतिहास':'Page visit history'?></li>
                </ul>
            </div>
        </div>
    </section>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?=date('Y')?> <?=$t('आकाशवाणी','Aakashvani')?></p>
            </div>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
</body>
</html>
