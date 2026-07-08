<?php
/**
 * आकाशवाणी — Gold & Silver Prices
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $t('सुनको मूल्य', 'Gold Price') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('सुन र चाँदीको मूल्य', 'Gold & Silver Price') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .page-header { background: linear-gradient(135deg, #b45309, #d97706); padding: var(--space-8) 0; color: #fff; }
        .gold-card { background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: var(--radius-xl); padding: var(--space-8); text-align: center; margin-bottom: var(--space-6); }
        .gold-label { font-size: 0.875rem; color: #92400e; margin-bottom: var(--space-2); }
        .gold-price { font-size: 3rem; font-weight: 800; color: #92400e; }
        .gold-unit { font-size: 1rem; color: #b45309; }
        .price-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); }
        .price-row { display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) 0; border-bottom: 1px solid var(--dark-100); }
        .price-row:last-child { border-bottom: none; }
        .price-type { font-weight: 600; color: var(--dark-900); }
        .price-value { font-size: 1.25rem; font-weight: 700; color: #d97706; }
        .source-tag { font-size: 0.75rem; color: var(--dark-400); text-align: center; margin-top: var(--space-4); }
        .section { padding: var(--space-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--space-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: #d97706; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .gold-price { font-size: 2rem; } }
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

    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <?= $t('सुन र चाँदीको मूल्य', 'Gold & Silver Price') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल बजारको ताजा सुन र चाँदीको भाव', 'Latest gold and silver rates in Nepal market') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 600px;">
            <div id="gold-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="gold-content" style="display:none">
                <div class="gold-card">
                    <p class="gold-label"><?= $t('आजको सुन (प्रति तोला)', 'Todays Gold (per tola)') ?></p>
                    <p class="gold-price" id="gold-rate">-</p>
                    <p class="gold-unit"><?= $t('नेरु', 'NPR') ?> / <?= $t('तोला', 'tola') ?></p>
                </div>
                <div class="price-card">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-4)"><?= $t('सुनको प्रकार', 'Types of Gold') ?></h3>
                    <div id="gold-types"></div>
                </div>
                <div class="price-card">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-4)"><?= $t('चाँदीको मूल्य', 'Silver Price') ?></h3>
                    <div id="silver-price"></div>
                </div>
                <p class="source-tag"><?= $t('स्रोत: नेपाल सुन चाँदी व्यापार संघ', 'Source: Nepal Gold and Silver Dealers Association') ?></p>
            </div>
            <div id="gold-error" style="display:none;text-align:center;padding:var(--space-8);color:var(--error)">
                <?= $t('मूल्य लोड हुन सकेन', 'Failed to load prices') ?>
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

    <script>
    async function loadGold() {
        try {
            const resp = await fetch('/api/market-data.php?type=gold');
            const data = await resp.json();
            if (data.ok && (data.gold || data.price)) {
                const goldData = data.gold || data;
                document.getElementById('gold-rate').textContent = 'रु ' + (goldData.goldTejabi || goldData.goldHallmark || goldData.price || goldData.rate || '---');
                const types = document.getElementById('gold-types');
                if (goldData.goldHallmark) types.innerHTML += '<div class="price-row"><span class="price-type"><?= $t("हल्मर सुन (९९.५%)", "Hallmark Gold (99.5%)") ?></span><span class="price-value">रु ' + goldData.goldHallmark + '</span></div>';
                if (goldData.goldTejabi) types.innerHTML += '<div class="price-row"><span class="price-type"><?= $t("तेजाबी सुन (९१.६%)", "Tejabi Gold (91.6%)") ?></span><span class="price-value">रु ' + goldData.goldTejabi + '</span></div>';
                if (goldData.silver) document.getElementById('silver-price').innerHTML = '<div class="price-row"><span class="price-type"><?= $t("चाँदी (प्रति केजी)", "Silver (per kg)") ?></span><span class="price-value">रु ' + goldData.silver + '</span></div>';
                document.getElementById('gold-loading').style.display = 'none';
                document.getElementById('gold-content').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('gold-loading').style.display = 'none';
            document.getElementById('gold-error').style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', loadGold);
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>