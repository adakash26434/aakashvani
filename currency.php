<?php
/**
 * आकाशवाणी — Currency Exchange Rates
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
    <meta property="og:title" content="<?= $t('मुद्रा विनिमय दर', 'Currency Exchange') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('मुद्रा विनिमय दर', 'Currency Exchange') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .page-header { background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--sp-8) 0; color: #fff; }
        .rate-card { background: #fff; border-radius: var(--radius-xl); padding: var(--sp-6); box-shadow: var(--shadow); }
        .rate-row { display: flex; justify-content: space-between; align-items: center; padding: var(--sp-4) 0; border-bottom: 1px solid var(--dark-100); }
        .rate-row:last-child { border-bottom: none; }
        .rate-flag { font-size: 1.5rem; margin-right: var(--sp-3); }
        .rate-code { font-weight: 700; color: var(--dark-900); }
        .rate-name { font-size: 0.75rem; color: var(--dark-500); }
        .rate-value { font-size: 1.25rem; font-weight: 700; color: var(--primary); }
        .rate-change { font-size: 0.75rem; padding: 2px 8px; border-radius: var(--radius-full); }
        .rate-change.up { background: #dcfce7; color: #16a34a; }
        .rate-change.down { background: #fee2e2; color: #dc2626; }
        .section { padding: var(--sp-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--sp-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .rate-value { font-size: 1rem; } }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="tp-topbar">
        <div class="tp-container">
            <div class="tp-topbar-inner">
                <div class="tp-topbar-left">
                    <span class="tp-date"><?= date('l, j F Y') ?></span>
                    <span class="tp-topbar-links"><a href="?">नेपाली</a><a href="?lang=en">English</a></span>
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
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <?= $t('मुद्रा विनिमय दर', 'Currency Exchange Rates') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल र बिश्वको ताजा मुद्रा दर', 'Latest Nepal and World currency rates') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 800px;">
            <div id="currency-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="currency-content" style="display:none">
                <div class="rate-card" style="margin-bottom:var(--sp-6)">
                    <div class="flex justify-between items-center" style="margin-bottom:var(--sp-4)">
                        <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900)"><?= $t('अमेरिकी डलर (USD)', 'US Dollar (USD)') ?></h3>
                        <span id="usd-rate" class="rate-value">-</span>
                    </div>
                    <p style="font-size:0.75rem;color:var(--dark-500)"><?= $t('नोट: यो दर बैंक र मनी एक्सचेन्जमा फरक हुन सक्छ', 'Note: Rates may vary at banks and money exchanges') ?></p>
                </div>
                <div class="rate-card">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--sp-4)"><?= $t('अन्य मुद्राहरू', 'Other Currencies') ?></h3>
                    <div id="currency-list"></div>
                </div>
            </div>
            <div id="currency-error" style="display:none;text-align:center;padding:var(--sp-8);color:var(--error)">
                <?= $t('मुद्रा दर लोड हुन सकेन', 'Failed to load currency rates') ?>
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
    async function loadCurrency() {
        try {
            const resp = await fetch('/api/market-data.php?type=forex');
            const data = await resp.json();
            if (data.ok && data.forex) {
                const usd = data.forex.find(c => c.currency === 'USD' || c.code === 'USD');
                if (usd) {
                    document.getElementById('usd-rate').textContent = 'रु ' + (usd.buy || usd.rate || '---');
                }
                const list = document.getElementById('currency-list');
                data.forex.slice(0, 10).forEach(c => {
                    list.innerHTML += '<div class="rate-row"><div><span class="rate-code">' + (c.currency || c.code || '-') + '</span></div><div class="rate-value">रु ' + (c.buy || c.rate || '-') + '</div></div>';
                });
                document.getElementById('currency-loading').style.display = 'none';
                document.getElementById('currency-content').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('currency-loading').style.display = 'none';
            document.getElementById('currency-error').style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', loadCurrency);
    </script>
</body>
</html>