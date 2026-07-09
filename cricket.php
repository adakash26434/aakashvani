<?php
/**
 * आकाशवाणी — Cricket Page (Live API)
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
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('क्रिकेट', 'Cricket') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        .cricket-hero { background: linear-gradient(135deg, #1a472a, #2d5a3d); padding: var(--sp-16) 0; color: #fff; text-align: center; }
        .cricket-tabs { display: flex; gap: var(--sp-2); justify-content: center; margin-top: var(--sp-6); }
        .cricket-tab { padding: var(--sp-2) var(--sp-4); background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: var(--radius-full); cursor: pointer; font-size: 0.875rem; transition: all var(--transition); }
        .cricket-tab:hover { background: rgba(255,255,255,0.2); }
        .cricket-tab.active { background: #fff; color: var(--dark-900); }
        .section { padding: var(--sp-12) 0; }
        .match-card { background: #fff; border-radius: var(--radius-xl); padding: var(--sp-6); box-shadow: var(--shadow); margin-bottom: var(--sp-4); transition: all var(--transition); }
        .match-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .match-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--sp-4); }
        .match-type { font-size: 0.75rem; font-weight: 600; color: var(--primary); text-transform: uppercase; }
        .match-status { font-size: 0.75rem; padding: var(--sp-1) var(--sp-3); border-radius: var(--radius-full); }
        .status-live { background: var(--error); color: #fff; animation: pulse 2s infinite; }
        .status-upcoming { background: var(--dark-100); color: var(--dark-600); }
        .status-completed { background: var(--dark-50); color: var(--dark-500); }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        .team-row { display: flex; align-items: center; gap: var(--sp-4); padding: var(--sp-3) 0; border-bottom: 1px solid var(--dark-100); }
        .team-row:last-child { border-bottom: none; }
        .team-name { font-weight: 600; flex: 1; }
        .team-score { font-size: 1.25rem; font-weight: 700; color: var(--dark-900); }
        .team-overs { font-size: 0.875rem; color: var(--dark-500); }
        .match-footer { display: flex; justify-content: space-between; margin-top: var(--sp-4); padding-top: var(--sp-4); border-top: 1px solid var(--dark-100); font-size: 0.875rem; color: var(--dark-500); }
        .nepal-badge { display: inline-flex; align-items: center; gap: var(--sp-2); padding: var(--sp-1) var(--sp-3); background: #dc2626; color: #fff; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; }
        /* Responsive */
        @media (max-width: 640px) {
            .match-card { padding: var(--sp-4); }
            .team-name { font-size: 0.875rem; }
            .team-score { font-size: 1.5rem; }
            .cricket-tabs { flex-wrap: wrap; }
            .cricket-tabs .tab-btn { flex: 1; min-width: 100px; font-size: 0.875rem; padding: var(--sp-2) var(--sp-3); }
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
                    <li class="has-dropdown">
                        <a href="/news.php"><?= $t('समाचार', 'News') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/news.php"><?= $t('सबै समाचार', 'All News') ?></a></li>
                            <li><a href="/news-post.php"><?= $t('समाचार विवरण', 'News Detail') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/ipo-tracker.php"><?= $t('बजार', 'Markets') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/ipo-tracker.php"><?= $t('NEPSE / IPO', 'NEPSE / IPO') ?></a></li>
                            <li><a href="/currency.php"><?= $t('मुद्रा विनिमय', 'Currency') ?></a></li>
                            <li><a href="/gold-price.php"><?= $t('सुन / चाँदी', 'Gold / Silver') ?></a></li>
                            <li><a href="/bank-interest-rates.php"><?= $t('ब्याज दर', 'Bank Interest') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/loksewa.php"><?= $t('करियर', 'Career') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/loksewa.php"><?= $t('लोकसेवा', 'Loksewa') ?></a></li>
                            <li><a href="/nokari.php"><?= $t('नोकरी', 'Jobs') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/gov-services.php"><?= $t('सरकारी', 'Gov') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/gov-services.php"><?= $t('सरकारी सेवाहरू', 'Gov Services') ?></a></li>
                            <li><a href="/tenders.php"><?= $t('टेन्डर', 'Tenders') ?></a></li>
                            <li><a href="/alerts.php"><?= $t('अलर्ट', 'Alerts') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/nepali-patro.php"><?= $t('दैनिक', 'Daily') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a></li>
                            <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                            <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                            <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                        </ul>
                    </li>
                    <li><a href="/cricket.php" class="active"><?= $t('क्रिकेट', 'Cricket') ?></a></li>
                    <li class="has-dropdown">
                        <a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/tools.php"><?= $t('सबै टूलहरू', 'All Tools') ?></a></li>
                            <li><a href="/info-hub.php"><?= $t('सूचना केन्द्र', 'Info Hub') ?></a></li>
                        </ul>
                    </li>
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


    <!-- Cricket Hero -->
    <section class="cricket-hero">
        <div class="container">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: var(--sp-2);">
                🏏 <?= $t('क्रिकेट स्कोर', 'Cricket Scores') ?>
            </h1>
            <p style="opacity: 0.8;"><?= $t('लाइभ क्रिकेट नतिजा र तालिका', 'Live cricket results and schedule') ?></p>
            
            <div class="cricket-tabs">
                <button class="cricket-tab active" onclick="showTab('live')"><?= $t('लाइभ', 'Live') ?></button>
                <button class="cricket-tab" onclick="showTab('upcoming')"><?= $t('आगामी', 'Upcoming') ?></button>
                <button class="cricket-tab" onclick="showTab('results')"><?= $t('नतिजा', 'Results') ?></button>
                <button class="cricket-tab" onclick="showTab('nepal')"><?= $t('नेपाल', 'Nepal') ?></button>
            </div>
        </div>
    </section>

    <!-- Live Matches -->
    <section class="section" id="live-section">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6">
                <span class="live-badge"><span class="live-dot"></span>LIVE</span>
                <?= $t('हाल भइरहेको', 'Currently Live') ?>
            </h2>
            <div id="live-matches">
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-type"><?= $t('अभौतिक क्रिकेट', 'Virtual Cricket') ?></span>
                        <span class="match-status status-live"><?= $t('लाइभ', 'LIVE') ?></span>
                    </div>
                    <div class="team-row">
                        <span class="team-name">🏏 <?= $t('नेपाल राष्ट्रिय टोली', 'Nepal National Team') ?></span>
                        <span class="team-score">--/--</span>
                        <span class="team-overs">(-- ov)</span>
                    </div>
                    <div class="match-footer">
                        <span><?= $t('टस', 'Toss') ?>: --</span>
                        <span><?= $t('अपडेट', 'Update') ?>: --:--</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Matches -->
    <section class="section" id="upcoming-section" style="display: none;">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6"><?= $t('आगामी खेलहरू', 'Upcoming Matches') ?></h2>
            <div id="upcoming-matches">
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-type">T20 International</span>
                        <span class="match-status status-upcoming"><?= $t('आगामी', 'Upcoming') ?></span>
                    </div>
                    <div class="team-row">
                        <span class="team-name">🇳🇵 नेपाल</span>
                        <span style="color: var(--dark-400);">vs</span>
                        <span class="team-name">🏴󠁧󠁢󠁥󠁮󠁧󠁿 इंग्ल्याण्ड</span>
                    </div>
                    <div class="match-footer">
                        <span><?= $t('मिति', 'Date') ?>: <?= date('d M Y') ?></span>
                        <span><?= $t('स्थान', 'Venue') ?>: --</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Results -->
    <section class="section" id="results-section" style="display: none;">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6"><?= $t('नतिजा', 'Results') ?></h2>
            <div id="results-matches">
                <div class="match-card">
                    <div class="match-header">
                        <span class="match-type"><?= $t('अभौतिक क्रिकेट', 'Virtual Cricket') ?></span>
                        <span class="match-status status-completed"><?= $t('सम्पन्न', 'Completed') ?></span>
                    </div>
                    <div class="team-row">
                        <span class="team-name">🏏 <?= $t('नेपाल राष्ट्रिय टोली', 'Nepal National Team') ?></span>
                        <span class="team-score" style="color: var(--success);">✓</span>
                        <span class="team-overs"><?= $t('विजयी', 'Winner') ?></span>
                    </div>
                    <div class="match-footer">
                        <span><?= $t('मिति', 'Date') ?>: <?= date('d M Y', strtotime('-1 day')) ?></span>
                        <span><?= $t('प्रतियोगिता', 'Tournament') ?>: ACC Cup</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nepal Cricket -->
    <section class="section" id="nepal-section" style="display: none; background: var(--dark-50);">
        <div class="container" style="max-width: 800px;">
            <h2 class="text-xl font-bold mb-6">
                <span class="nepal-badge">🇳🇵</span>
                <?= $t('नेपाल क्रिकेट', 'Nepal Cricket') ?>
            </h2>
            <div id="nepal-matches">
                <div class="alert alert-info"><?= $t('नेपाल क्रिकेटको जानकारी लोड हुँदै...', 'Loading Nepal cricket info...') ?></div>
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
                <span><?= $t('हामी नेपालको सूचना खुला राख्छौं', 'We keep Nepal\'s information open') ?></span>
            </div>
        </div>
    </footer>


    <script>
    // Tab switching
    function showTab(tab) {
        document.querySelectorAll('.cricket-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('[id$="-section"]').forEach(s => s.style.display = 'none');
        
        document.querySelector(`.cricket-tab:nth-child(${tab === 'live' ? 1 : tab === 'upcoming' ? 2 : tab === 'results' ? 3 : 4})`).classList.add('active');
        document.getElementById(tab + '-section').style.display = 'block';
        
        loadCricketData(tab);
    }

    // Load cricket data
    async function loadCricketData(mode) {
        const containers = {
            'live': 'live-matches',
            'upcoming': 'upcoming-matches',
            'results': 'results-matches',
            'nepal': 'nepal-matches'
        };
        
        try {
            const resp = await fetch('/api/cricket.php?mode=' + mode);
            const data = await resp.json();
            
            if (data.matches && data.matches.length > 0) {
                const container = document.getElementById(containers[mode]);
                container.innerHTML = data.matches.map(match => `
                    <div class="match-card">
                        <div class="match-header">
                            <span class="match-type">${match.type || 'T20'}</span>
                            <span class="match-status ${match.status === 'live' ? 'status-live' : match.status === 'upcoming' ? 'status-upcoming' : 'status-completed'}">
                                ${match.status === 'live' ? '🔴 LIVE' : match.status === 'upcoming' ? '⏰ ' + match.status : '✓ ' + match.status}
                            </span>
                        </div>
                        ${match.teams ? match.teams.map(team => `
                            <div class="team-row">
                                <span class="team-name">${team.flag || '🏏'} ${team.name}</span>
                                <span class="team-score">${team.score || '-'}</span>
                                <span class="team-overs">${team.overs || ''}</span>
                            </div>
                        `).join('') : ''}
                        <div class="match-footer">
                            <span>${match.venue || 'स्थान निश्चित भएको छैन'}</span>
                            <span>${match.time || match.date || ''}</span>
                        </div>
                    </div>
                `).join('');
            }
        } catch (e) {
            console.log('Cricket API error:', e);
        }
    }

    // Load live matches on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadCricketData('live');
    }
        (function() {
            var s=document.createElement('script');s.src='/assets/js/lucide.min.js';document.head.appendChild(s);
        })();
);
    </script>

    <!-- Mobile Bottom Nav -->
<script>document.addEventListener('DOMContentLoaded',function(){if(typeof lucide!=='undefined')lucide.createIcons()}
        (function() {
            var s=document.createElement('script');s.src='/assets/js/lucide.min.js';document.head.appendChild(s);
        })();
);
            // Mobile dropdown: clicking has-dropdown links toggles submenu
            navList?.querySelectorAll('.has-dropdown > a').forEach(link => {
                link.addEventListener('click', (e) => {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        link.parentElement.classList.toggle('open');
                    }
                });
            });</script>
</body>
</html>