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
    <title><?=$t('हाम्रो बारेमा','About Us')?> | आकाशवाणी</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header{background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-16) 0;color:#fff;text-align:center}
        .section{padding:var(--space-16) 0}
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between">
                    <a href="/" class="header-brand"><div class="brand-logo">आ</div><span class="brand-name"><?=$t('आकाशवाणी','Aakashvani')?></span></a>
                    <nav class="header-nav">
                        <a href="/" class="nav-link"><?=$t('गृह','Home')?></a>
                        <a href="/news.php" class="nav-link"><?=$t('समाचार','News')?></a>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <section class="page-header">
        <div class="container">
            <h1 class="page-title"><?=$t('हाम्रो बारेमा','About Us')?></h1>
        </div>
    </section>
    <section class="section">
        <div class="container" style="max-width:800px">
            <div class="card card-body">
                <h2 style="margin-bottom:var(--space-4)"><?=$t('आकाशवाणी के हो?','What is Aakashvani?')?></h2>
                <p style="color:var(--dark-600);line-height:1.8">
                    <?=$isNepali?'आकाशवाणी नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म हो। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा। हाम्रो लक्ष्य नेपाली जनतालाई सही र छिटो सूचना प्रदान गर्नु हो।':'Aakashvani is Nepal\'s most trusted information platform. News, NEPSE, IPO, Calendar, and Government services all in one place. Our mission is to provide accurate and fast information to the people of Nepal.'?>
                </p>
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
