<?php
/**
 * आकाशवाणी — IPO Tracker (LIVE API DATA)
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
// IPO data loaded via JavaScript API from /api/ipo-data.php
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('IPO ट्र्याकर', 'IPO Tracker') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        .page-header { background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--sp-12) 0; color: #fff; }
        .ipo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--sp-6); }
        .ipo-card { background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--dark-100); padding: var(--sp-6); transition: all var(--transition); }
        .ipo-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .ipo-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--sp-4); }
        .ipo-symbol { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .ipo-status { padding: var(--sp-1) var(--sp-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; }
        .ipo-status.open { background: var(--primary-50); color: var(--primary-700); }
        .ipo-status.upcoming { background: #fef3c7; color: #92400e; }
        .ipo-status.closed { background: var(--dark-100); color: var(--dark-500); }
        .ipo-company { font-size: 1rem; font-weight: 600; color: var(--dark-900); margin-bottom: var(--sp-4); }
        .ipo-details { display: flex; flex-direction: column; gap: var(--sp-2); margin-bottom: var(--sp-4); }
        .ipo-detail { display: flex; justify-content: space-between; padding: var(--sp-2) var(--sp-3); background: var(--dark-50); border-radius: var(--radius); }
        .ipo-detail-label { font-size: 0.875rem; color: var(--dark-500); }
        .ipo-detail-value { font-size: 0.875rem; font-weight: 600; color: var(--dark-900); }
        .section { padding: var(--sp-12) 0; }
        .ipo-section:nth-child(even) { background: var(--dark-50); }
        .loading-spinner { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--sp-12); }
        .spinner { width: 48px; height: 48px; border: 4px solid var(--dark-200); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Responsive */
        @media (max-width: 640px) {
            .page-title { font-size: 1.5rem; }
            .ipo-card { padding: var(--sp-4); }
            .ipo-symbol { font-size: 1.25rem; }
            .ipo-grid { grid-template-columns: 1fr; gap: var(--sp-4); }
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="tp-topbar">
        <div class="tp-container">
            <div class="tp-topbar-inner">
                <div class="tp-topbar-left">
                    <span class="tp-date"><?= date('l, j F Y') ?></span>
                    <span class="tp-topbar-links">
                        <a href="?">नेपाली</a>
                        <a href="?lang=en">English</a>
                    </span>
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
                <button class="tp-nav-toggle" id="navToggle" aria-label="Menu">
                    <i data-lucide="menu"></i>
                </button>
                <ul class="tp-nav-list" id="navList">
                    <li><a href="/"><?= $t('गृह', 'Home') ?></a></li>
                    <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                    <li><a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a></li>
                    <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                    <li><a href="/ipo-tracker.php" class="active"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    <li><a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a></li>
                    <li><a href="/gov-services.php"><?= $t('सरकारी', 'Gov') ?></a></li>
                    <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                    <li><a href="/cricket.php"><?= $t('क्रिकेट', 'Cricket') ?></a></li>
                    <li><a href="/tenders.php"><?= $t('टेन्डर', 'Tenders') ?></a></li>
                    <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                </ul>
                <div class="tp-nav-search">
                    <button class="tp-search-btn" id="searchToggle" aria-label="Search">
                        <i data-lucide="search"></i>
                    </button>
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

    <!-- PAGE BANNER -->
    <div class="tp-page-banner">
        <div class="tp-container">
            <div class="tp-page-banner-inner">
                <div>
                    <h1 class="tp-page-title"><i data-lucide="trending-up"></i> <?= $t('IPO ट्र्याकर', 'IPO Tracker') ?></h1>
                    <p class="tp-page-subtitle"><?= $t('नेपालका IPO र मर्जर आइपिओको विवरण', 'Details of IPOs and merger IPOs in Nepal') ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <section class="ipo-section">
        <div class="container">
            <!-- Loading State -->
            <div id="ipo-loading" class="loading-spinner">
                <div class="spinner"></div>
                <p style="margin-top:var(--sp-4);color:var(--dark-500)"><?= $t('IPO डाटा लोड हुँदै...', 'Loading IPO data...') ?></p>
            </div>
            
            <!-- Error State -->
            <div id="ipo-error" style="display:none;text-align:center;padding:var(--sp-8)">
                <p style="color:var(--error);margin-bottom:var(--sp-4)"><?= $t('IPO डाटा लोड हुन सकेन', 'Failed to load IPO data') ?></p>
                <button onclick="loadIPOs()" class="btn btn-primary"><?= $t('पुनः प्रयास गर्नुहोस्', 'Retry') ?></button>
            </div>
            
            <!-- Open IPOs -->
            <h2 class="text-xl font-bold mb-6" id="open-title" style="padding-top:var(--sp-6);display:none"><?= $t('खुला IPO', 'Open IPOs') ?></h2>
            <div class="ipo-grid" id="open-ipos" style="display:none"></div>
            
            <!-- Upcoming IPOs -->
            <h2 class="text-xl font-bold mb-6" id="upcoming-title" style="padding-top:var(--sp-8);display:none"><?= $t('आगामी IPO', 'Upcoming IPOs') ?></h2>
            <div class="ipo-grid" id="upcoming-ipos" style="display:none"></div>
            
            <!-- Last Updated -->
            <p id="ipo-updated" style="display:none;text-align:center;padding:var(--sp-6);font-size:0.75rem;color:var(--dark-400)"></p>
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
                    <p class="tp-footer-desc"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?></p>
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
                        <li><a href="/gov-services.php"><?= $t('सरकारी सेवा', 'Gov Services') ?></a></li>
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
                        <li><a href="/terms.php"><?= $t('सेवा सर्त', 'Terms') ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="tp-footer-bottom">
                <span>&copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?></span>
                <span><?= $t('हामी नेपालको सूचना खुला राख्छौं', 'We keep Nepal\'s information open') ?></span>
            </div>
        </div>
    </footer>

    <script>
    (function() {
        'use strict';

        async function loadMarket() {
            try {
                const resp = await fetch('/api/market-data.php?type=all');
                if (!resp.ok) return;
                const d = await resp.json();
                if (d.nepse) {
                    const n = d.nepse, v = document.getElementById('nepse-value'), c = document.getElementById('nepse-change');
                    if (v && n.index) v.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits:2});
                    if (c && n.change !== undefined) {
                        const up = n.change >= 0;
                        c.textContent = (up ? '+' : '') + n.change.toFixed(2) + ' (' + (up ? '+' : '') + n.changePercent.toFixed(2) + '%)';
                        c.className = 'tp-mkt-change ' + (up ? 'up' : 'down');
                    }
                }
                if (d.gold && d.gold.hallmarkPerTola) { const gv = document.getElementById('gold-value'); if (gv) gv.textContent = 'रु ' + Number(d.gold.hallmarkPerTola).toLocaleString('en-US'); }
                if (d.forex && d.forex.rates && d.forex.rates.length > 0) {
                    const usd = d.forex.rates.find(r => r.code === 'USD');
                    if (usd) { const fv = document.getElementById('forex-value'); if (fv) fv.textContent = 'रु ' + usd.sell.toFixed(2); }
                }
                if (d.petrol && d.petrol.petrol) { const pv = document.getElementById('petrol-value'); if (pv) pv.textContent = 'रु ' + d.petrol.petrol; }
            } catch(e) { console.warn('Market unavailable'); }
        }

        function initSearch() {
            const toggle = document.getElementById('searchToggle'), bar = document.getElementById('searchBar');
            if (toggle && bar) toggle.addEventListener('click', function() { bar.style.display = bar.style.display === 'none' ? 'block' : 'none'; });
            const navToggle = document.getElementById('navToggle'), navList = document.getElementById('navList');
            if (navToggle && navList) navToggle.addEventListener('click', function() { navList.classList.toggle('open'); });
        }

        async function loadIPOs() {
            var loading = document.getElementById('ipo-loading');
            var error = document.getElementById('ipo-error');
            var openGrid = document.getElementById('open-ipos');
            var upcomingGrid = document.getElementById('upcoming-ipos');
            var openTitle = document.getElementById('open-title');
            var upcomingTitle = document.getElementById('upcoming-title');
            var updated = document.getElementById('ipo-updated');

            if (!loading) return;
            loading.style.display = 'flex';
            if (error) error.style.display = 'none';

            try {
                var resp = await fetch('/api/ipo-data.php');
                var data = await resp.json();

                if (data.ok && data.ipos && data.ipos.length > 0) {
                    var now = new Date().toISOString().split('T')[0];
                    var openIPOs = data.ipos.filter(function(i) {
                        return i.status === 'Active' || (i.openDate && i.closeDate && i.openDate <= now && i.closeDate >= now);
                    });
                    var upcomingIPOs = data.ipos.filter(function(i) {
                        return i.status === 'Upcoming' || !i.openDate || i.openDate > now;
                    });

                    if (openIPOs.length > 0 && openGrid) {
                        openGrid.innerHTML = openIPOs.map(function(ipo) {
                            return '<div class="ipo-card"><div class="ipo-header"><span class="ipo-symbol">' + (ipo.symbol || '-') + '</span><span class="ipo-status open">OPEN</span></div><h3 class="ipo-company">' + (ipo.name || ipo.company || '-') + '</h3><div class="ipo-details"><div class="ipo-detail"><span class="ipo-detail-label">Price</span><span class="ipo-detail-value">' + (ipo.price || 'रु 0') + '</span></div><div class="ipo-detail"><span class="ipo-detail-label">Units</span><span class="ipo-detail-value">' + (ipo.shares || '-') + '</span></div><div class="ipo-detail"><span class="ipo-detail-label">Close</span><span class="ipo-detail-value">' + (ipo.closeDate || '-') + '</span></div></div></div>';
                        }).join('');
                        if (openTitle) openTitle.style.display = 'block';
                        openGrid.style.display = 'grid';
                    }

                    if (upcomingIPOs.length > 0 && upcomingGrid) {
                        upcomingGrid.innerHTML = upcomingIPOs.map(function(ipo) {
                            return '<div class="ipo-card"><div class="ipo-header"><span class="ipo-symbol">' + (ipo.symbol || '-') + '</span><span class="ipo-status upcoming">UPCOMING</span></div><h3 class="ipo-company">' + (ipo.name || ipo.company || '-') + '</h3><div class="ipo-details"><div class="ipo-detail"><span class="ipo-detail-label">Price</span><span class="ipo-detail-value">' + (ipo.price || 'TBD') + '</span></div><div class="ipo-detail"><span class="ipo-detail-label">Opening</span><span class="ipo-detail-value">' + (ipo.openDate || 'TBD') + '</span></div></div></div>';
                        }).join('');
                        if (upcomingTitle) upcomingTitle.style.display = 'block';
                        upcomingGrid.style.display = 'grid';
                    }

                    if (updated) { updated.style.display = 'block'; updated.textContent = '<?= $t('अपडेट: ', 'Updated: ') ?>' + new Date().toLocaleString('ne-NP'); }
                }
            } catch(e) {
                if (error) { error.style.display = 'block'; }
            } finally {
                if (loading) loading.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
            loadMarket();
            initSearch();
            loadIPOs();
            setInterval(loadMarket, 5 * 60 * 1000);
        });
    })();
    </script>

</body>
</html>
