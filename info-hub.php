<?php
/**
 * आकाशवाणी — Info Hub (All Information)
 * Premium 2026 Design
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$sections = [
    [
        'title' => $t('समाचार', 'News'),
        'icon' => 'newspaper',
        'color' => '#10b981',
        'items' => [
            ['name' => $t('राजनीति', 'Politics'), 'href' => '/news.php?category=politics'],
            ['name' => $t('अर्थ', 'Economy'), 'href' => '/news.php?category=economy'],
            ['name' => $t('खेलकुद', 'Sports'), 'href' => '/news.php?category=sports'],
            ['name' => $t('प्रविधि', 'Technology'), 'href' => '/news.php?category=technology'],
        ]
    ],
    [
        'title' => $t('जीवनशैली', 'Lifestyle'),
        'icon' => 'sparkles',
        'color' => '#f59e0b',
        'items' => [
            ['name' => $t('पात्रो', 'Calendar'), 'href' => '/nepali-patro.php'],
            ['name' => $t('राशिफल', 'Horoscope'), 'href' => '/rashifal.php'],
            ['name' => $t('मौसम', 'Weather'), 'href' => '/weather.php'],
        ]
    ],
    [
        'title' => $t('नेतृत्व', 'Finance'),
        'icon' => 'trending-up',
        'color' => '#3b82f6',
        'items' => [
            ['name' => $t('IPO ट्र्याकर', 'IPO Tracker'), 'href' => '/ipo-tracker.php'],
            ['name' => $t('मुद्रा', 'Currency'), 'href' => '/currency.php'],
            ['name' => $t('सुनको मूल्य', 'Gold Price'), 'href' => '/gold-price.php'],
        ]
    ],
    [
        'title' => $t('सरकारी', 'Government'),
        'icon' => 'landmark',
        'color' => '#8b5cf6',
        'items' => [
            ['name' => $t('सरकारी सेवा', 'Gov Services'), 'href' => '/gov-services.php'],
            ['name' => $t('नागरिकता', 'Citizenship'), 'href' => '/gov-services.php#citizenship'],
            ['name' => $t('राहदानी', 'Passport'), 'href' => '/gov-services.php#passport'],
        ]
    ],
    [
        'title' => $t('उपयोगी', 'Utilities'),
        'icon' => 'wrench',
        'color' => '#ef4444',
        'items' => [
            ['name' => $t('कर क्यालकुलेटर', 'Tax Calculator'), 'href' => '/tools.php#tax'],
            ['name' => $t('इकाई रूपान्तरक', 'Unit Converter'), 'href' => '/tools.php#unit'],
            ['name' => $t('आपतकालीन', 'Emergency'), 'href' => '/emergency.php'],
        ]
    ],
    [
        'title' => $t('शिक्षा', 'Education'),
        'icon' => 'graduation-cap',
        'color' => '#06b6d4',
        'items' => [
            ['name' => $t('नोकरी', 'Jobs'), 'href' => '/nokari.php'],
            ['name' => $t('लोकसेवा', 'Gov Jobs'), 'href' => '/loksewa.php'],
            ['name' => $t('क्रिकेट', 'Cricket'), 'href' => '/cricket.php'],
        ]
    ],
];
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('सबै जानकारी', 'All Information') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .hub-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-6); }
        @media (max-width: 1024px) { .hub-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .hub-grid { grid-template-columns: 1fr; } }
        .hub-card { background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--dark-100); overflow: hidden; transition: all var(--transition); }
        .hub-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .hub-header { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-4); border-bottom: 1px solid var(--dark-100); }
        .hub-icon { width: 40px; height: 40px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: #fff; }
        .hub-title { font-size: 1rem; font-weight: 700; color: var(--dark-900); }
        .hub-items { padding: var(--space-2); }
        .hub-item { display: flex; align-items: center; justify-content: space-between; padding: var(--space-3); border-radius: var(--radius); transition: background var(--transition); }
        .hub-item:hover { background: var(--dark-50); }
        .hub-item span { font-size: 0.875rem; color: var(--dark-700); }
        .hub-item svg { color: var(--dark-400); width: 16px; height: 16px; }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-grid">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <div class="brand-text">
                            <h1><?= $t('आकाशवाणी', 'Aakashvani') ?></h1>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </a>
                    <nav class="main-nav">
                        <div class="nav-list">
                            <a href="/" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?= $t('गृह', 'Home') ?></a>
                            <a href="/news.php" class="nav-item-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg><?= $t('समाचार', 'News') ?></a>
                            <a href="/info-hub.php" class="nav-item-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><?= $t('जानकारी', 'Info Hub') ?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    
    <section class="page-header" style="background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px;color:#fff">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <?= $t('सबै जानकारी', 'All Information') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपालको सबै महत्वपूर्ण जानकारी एकै ठाउँमा', 'All important information of Nepal in one place') ?></p>
        </div>
    </section>
    
    <section style="padding:var(--space-12) 0">
        <div class="container">
            <div class="hub-grid">
                <?php foreach ($sections as $section): ?>
                <div class="hub-card">
                    <div class="hub-header">
                        <div class="hub-icon" style="background:<?= $section['color'] ?>">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        </div>
                        <h2 class="hub-title"><?= $section['title'] ?></h2>
                    </div>
                    <div class="hub-items">
                        <?php foreach ($section['items'] as $item): ?>
                        <a href="<?= $item['href'] ?>" class="hub-item">
                            <span><?= $item['name'] ?></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p>
            </div>
        </div>
    </footer>
    
    <script src="/assets/js/app.js"></script>
</body>
</html>
