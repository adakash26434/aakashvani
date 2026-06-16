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
        /* Responsive */
        @media (max-width: 768px) {
            .page-header { padding: var(--space-8) 0; }
            .page-header h1 { font-size: 1.75rem; }
            .content-section { padding: var(--space-6) 0; }
        }
        
        @media (max-width: 480px) {
            .page-header h1 { font-size: 1.5rem; }
            .btn { padding: var(--space-2) var(--space-4); font-size: 0.875rem; }
        }
    </style>
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
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?=$t('गृह','Home')?></a>
                            <a href="/news.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg><?=$t('समाचार','News')?></a>
                            <a href="/nepali-patro.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg><?=$t('पात्रो','Calendar')?></a>
                            <a href="/rashifal.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg><?=$t('राशिफल','Horoscope')?></a>
                        </div>
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
