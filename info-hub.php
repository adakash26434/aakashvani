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
            ['name' => $t('मुद्रा', 'Currency'), 'href' => '/currency.php', 'new' => true],
            ['name' => $t('सुनको मूल्य', 'Gold Price'), 'href' => '/gold-price.php', 'new' => true],
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
            ['name' => $t('नोकरी', 'Jobs'), 'href' => '/nokari.php', 'new' => true],
            ['name' => $t('लोकसेवा', 'Gov Jobs'), 'href' => '/loksewa.php', 'new' => true],
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
    <title><?= $t('सबै जानकारी', 'All Information') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?><meta property="og:title" content="<?= $t('आकाशवाणी', 'Aakashvani') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    </title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
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
    <!-- TOP BAR -->
    <div class="tp-topbar">
        <div class="tp-container">
            <div class="tp-topbar-inner">
                <div class="tp-topbar-left">
                    <span class="tp-date"><?= date('l, j F Y') ?></span>
                    <span class="tp-topbar-links"><a href="/unicode">Unicode</a><a href="?lang=en">English</a></span>
                </div>
                <div class="tp-topbar-right">
                    <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- MID HEADER -->
    <div class="tp-header-mid">
        <div class="tp-container">
            <div class="tp-header-mid-inner">
                <a href="/" class="tp-logo">
                    <img src="/favicon.svg" alt="Aakashvani" width="48" height="48">
                    <div class="tp-logo-text">
                        <span class="tp-logo-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                        <span class="tp-logo-tagline"><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                    </div>
                </a>
                <div class="tp-header-ads"></div>
            </div>
        </div>
    </div>

    <!-- STICKY NAV -->
    <nav class="tp-nav" id="tpNav">
        <div class="tp-container">
            <div class="tp-nav-inner">
                <button class="tp-nav-toggle" id="navToggle" aria-label="Menu"><i data-lucide="menu"></i></button>
                <ul class="tp-nav-list" id="navList">
                    <li><a href="/"><?= $t('गृह', 'Home') ?></a></li>
                    <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                    <li><a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a></li>
                    <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                    <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    <li><a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a></li>
                    <li><a href="/gov-services.php"><?= $t('सरकारी', 'Gov') ?></a></li>
                    <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                    <li><a href="/cricket.php"><?= $t('क्रिकेट', 'Cricket') ?></a></li>
                    <li><a href="/tenders.php"><?= $t('टेन्डर', 'Tenders') ?></a></li>
                    <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                </ul>
                <div class="tp-nav-search">
                    <button class="tp-search-btn" id="searchToggle" aria-label="Search"><i data-lucide="search"></i></button>
                </div>
            </div>
            <div class="tp-search-bar" id="searchBar" style="display:none">
                <input type="search" placeholder="<?= $t('खोज्नुहोस्...', 'Search...') ?>" id="searchInput">
            </div>
        </div>
    </nav>

    <!-- MARKET TICKER -->
    <div class="tp-market-bar">
        <div class="tp-container">
            <div class="tp-market-inner">
                <span class="tp-market-item"><i data-lucide="trending-up"></i><span class="tp-mkt-label">NEPSE</span><span class="tp-mkt-value" id="nepse-value">...</span><span class="tp-mkt-change" id="nepse-change">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="gem"></i><span class="tp-mkt-label"><?= $t('सुन', 'Gold') ?></span><span class="tp-mkt-value" id="gold-value">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="dollar-sign"></i><span class="tp-mkt-label">USD</span><span class="tp-mkt-value" id="forex-value">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="fuel"></i><span class="tp-mkt-label"><?= $t('पेट्रोल', 'Petrol') ?></span><span class="tp-mkt-value" id="petrol-value">...</span></span>
            </div>
        </div>
    </div>

    
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
    <!-- FOOTER -->
    <footer class="tp-footer">
        <div class="tp-container">
            <div class="tp-footer-grid">
                <div class="tp-footer-brand">
                    <a href="/" class="tp-footer-logo">
                        <img src="/favicon.svg" alt="Aakashvani" width="40" height="40">
                        <div>
                            <span class="tp-footer-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                            <span class="tp-footer-tagline"><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </a>
                    <p class="tp-footer-desc"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal's most trusted information platform.') ?></p>
                    <div class="tp-footer-social">
                        <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h4><?= $t('लिंकहरू', 'Links') ?></h4>
                    <ul>
                        <li><a href="/"><?= $t('गृहपृष्ठ', 'Home') ?></a></li>
                        <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                        <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4><?= $t('स्रोतहरू', 'Resources') ?></h4>
                    <ul>
                        <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                        <li><a href="/nepali-patro.php"><?= $t('नेपाली पात्रो', 'Calendar') ?></a></li>
                        <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                        <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4><?= $t('कम्पनी', 'Company') ?></h4>
                    <ul>
                        <li><a href="/about.php"><?= $t('हाम्रो बारेमा', 'About') ?></a></li>
                        <li><a href="/contact.php"><?= $t('सम्पर्क', 'Contact') ?></a></li>
                        <li><a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="tp-footer-bottom">
                <span>&copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?></span>
                <span><?= $t('हामी नेपालको सूचना खुला राख्छौं', 'We keep Nepal's information open') ?></span>
            </div>
        </div>
    </footer>

    
    <script src="/assets/js/app.js"></script>
</body>
</html>
