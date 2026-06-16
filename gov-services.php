<?php
require_once __DIR__ . '/config.php';
$lang=siteLang();
$isNepali=($lang!=='en');
$t=fn($ne,$en)=>$isNepali?$ne:$en;
$services=[
    ['name'=>'नागरिकता','en'=>'Citizenship','icon'=>'id-card','desc'=>'नागरिकता प्रमाणपत्र'],
    ['name'=>'राहदानी','en'=>'Passport','icon'=>'book-open','desc'=>'राहदानी (Passport)'],
    ['name'=>'स्थानीय तह','en'=>'Local Body','icon'=>'map-pin','desc'=>'नगरपालिका/गाउँपालिका'],
    ['name'=>'कर','en'=>'Tax','icon'=>'calculator','desc'=>'आयकर र मूल्याङ्कन'],
    ['name'=>'जग्गा','en'=>'Land','icon'=>'home','desc'=>'जग्गा रजिष्ट्रेशन'],
    ['name'=>'शिक्षा','en'=>'Education','icon'=>'graduation-cap','desc'=>'शैक्षिक प्रमाणपत्र'],
];
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t('सरकारी सेवा','Government Services')?> | आकाशवाणी<meta property="og:title" content="<?= $t('आकाशवाणी', 'Aakashvani') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    </title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header{background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff}
        .services-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:var(--space-6)}
        .service-card{background:#fff;border-radius:var(--radius-xl);border:1px solid var(--dark-100);padding:var(--space-8);text-align:center;transition:all var(--transition);text-decoration:none;display:block}
        .service-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:var(--primary)}
        .service-icon{width:72px;height:72px;background:var(--primary-50);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-4);color:var(--primary)}
        .service-name{font-size:1.125rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-2)}
        .service-desc{font-size:0.875rem;color:var(--dark-500)}
        .section{padding:var(--space-12) 0}
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
                            <a href="/gov-services.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg><?=$t('सरकारी सेवा','Gov Services')?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                <?=$t('सरकारी सेवाहरू','Government Services')?>
            </h1>
            <p class="page-subtitle"><?=$t('नेपाल सरकारका महत्वपूर्ण सेवाहरू','Important services of Government of Nepal')?></p>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="services-grid">
                <?php foreach($services as $svc):?>
                <a href="#" class="service-card">
                    <div class="service-icon">
                        <svg class="icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </div>
                    <h3 class="service-name"><?=$isNepali?$svc['name']:$svc['en']?></h3>
                    <p class="service-desc"><?=$svc['desc']?></p>
                </a>
                <?php endforeach;?>
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
