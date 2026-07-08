<?php
/**
 * आकाशवाणी — Live Alerts Hub (All Connected APIs)
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
    <title><?= $t('लाइभ अलर्ट', 'Live Alerts') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .alerts-hero { background: linear-gradient(135deg, #dc2626, #991b1b); padding: var(--space-12) 0; color: #fff; text-align: center; }
        .alerts-hero h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: var(--space-2); }
        .tabs-container { display: flex; gap: var(--space-2); justify-content: center; flex-wrap: wrap; margin-top: var(--space-6); }
        .alert-tab { padding: var(--space-2) var(--space-4); background: rgba(255,255,255,0.2); border: none; border-radius: var(--radius-full); color: #fff; cursor: pointer; font-size: 0.875rem; transition: all var(--transition); }
        .alert-tab:hover { background: rgba(255,255,255,0.3); }
        .alert-tab.active { background: #fff; color: #dc2626; }
        .section { padding: var(--space-12) 0; }
        .alert-section { display: none; }
        .alert-section.active { display: block; }
        .eq-list { display: grid; gap: var(--space-4); }
        .eq-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); border-left: 4px solid var(--error); }
        .eq-card.moderate { border-left-color: #f59e0b; }
        .eq-card.minor { border-left-color: #16a34a; }
        .eq-mag { font-size: 3rem; font-weight: 800; color: var(--error); line-height: 1; }
        .eq-mag.moderate { color: #f59e0b; }
        .eq-mag.minor { color: #16a34a; }
        .eq-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-4); margin-top: var(--space-4); }
        .eq-meta-item { text-align: center; }
        .eq-meta-label { font-size: 0.75rem; color: var(--dark-500); }
        .eq-meta-value { font-weight: 600; }
        .weather-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-8); box-shadow: var(--shadow); }
        .weather-main { display: flex; align-items: center; gap: var(--space-8); margin-bottom: var(--space-8); }
        .weather-icon-big { font-size: 6rem; }
        .weather-temp-big { font-size: 5rem; font-weight: 800; color: var(--dark-900); }
        .weather-info h2 { font-size: 1.5rem; font-weight: 600; margin-bottom: var(--space-2); }
        .weather-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); }
        .stat-box { background: var(--dark-50); border-radius: var(--radius-lg); padding: var(--space-4); text-align: center; }
        .stat-box-value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .stat-box-label { font-size: 0.75rem; color: var(--dark-500); }
        .police-list { display: grid; gap: var(--space-3); }
        .police-item { background: #fff; border-radius: var(--radius-lg); padding: var(--space-4); box-shadow: var(--shadow-sm); display: flex; align-items: flex-start; gap: var(--space-3); }
        .police-icon { font-size: 1.5rem; }
        .police-content { flex: 1; }
        .police-title { font-weight: 600; margin-bottom: var(--space-1); }
        .police-time { font-size: 0.75rem; color: var(--dark-500); }
        .jobs-list { display: grid; gap: var(--space-4); }
        .job-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); }
        .job-header { display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-4); margin-bottom: var(--space-4); }
        .job-title { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); }
        .job-org { font-size: 0.875rem; color: var(--dark-600); margin-top: var(--space-1); }
        .job-badge { padding: var(--space-1) var(--space-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; background: var(--primary-50); color: var(--primary-700); }
        .job-deadline { font-size: 0.875rem; color: var(--dark-500); }
        .job-deadline strong { color: var(--error); }
        .flight-list { display: grid; gap: var(--space-3); }
        .flight-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-4); box-shadow: var(--shadow); display: flex; align-items: center; gap: var(--space-4); }
        .flight-icon { font-size: 2rem; }
        .flight-route { font-weight: 600; flex: 1; }
        .flight-time { font-size: 0.875rem; color: var(--dark-500); }
        .flight-status { padding: var(--space-1) var(--space-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; }
        .status-on-time { background: #dcfce7; color: #166534; }
        .status-delayed { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        @media (max-width: 768px) {
            .weather-stats, .eq-meta { grid-template-columns: repeat(2, 1fr); }
            .weather-main { flex-direction: column; text-align: center; }
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


    <!-- Hero -->
    <section class="alerts-hero">
        <div class="container">
            <h1>🚨 <?= $t('लाइभ अलर्ट र जानकारी', 'Live Alerts & Information') ?></h1>
            <p><?= $t('नेपाल र विश्वको ताजा जानकारी', 'Latest information from Nepal and the world') ?></p>
            <div class="tabs-container">
                <button class="alert-tab active" onclick="showSection('earthquake')">🌍 <?= $t('भूकम्प', 'Earthquake') ?></button>
                <button class="alert-tab" onclick="showSection('weather')">🌤️ <?= $t('मौसम', 'Weather') ?></button>
                <button class="alert-tab" onclick="showSection('police')">🚨 <?= $t('प्रहरी', 'Police') ?></button>
                <button class="alert-tab" onclick="showSection('jobs')">💼 <?= $t('जobs', 'Jobs') ?></button>
                <button class="alert-tab" onclick="showSection('flights')">✈️ <?= $t('उडान', 'Flights') ?></button>
            </div>
        </div>
    </section>

    <!-- Earthquake Section -->
    <section class="section alert-section active" id="earthquake-section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6">🌍 <?= $t('हालका भूकम्पहरू (USGS Data)', 'Recent Earthquakes (USGS Data)') ?></h2>
            <p style="color: var(--dark-500); margin-bottom: var(--space-6);"><?= $t('यो USGS (United States Geological Survey) बाट Real-time डेटा हो। भूकम्प भविष्यवाणी गर्न सकिँदैन, तर हालका भूकम्पहरूको जानकारी प्राप्त गर्न सकिन्छ।', 'This is real-time data from USGS. Earthquakes cannot be predicted, but recent earthquake information is available.') ?></p>
            <div class="eq-list" id="eq-list">
                <div class="eq-card">
                    <div style="text-align: center; padding: var(--space-8);">
                        <div class="alert-loading"><?= $t('भूकम्प डेटा लोड हुँदै...', 'Loading earthquake data...') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Weather Section -->
    <section class="section alert-section" id="weather-section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6">🌤️ <?= $t('नेपालको मौसम', 'Weather in Nepal') ?></h2>
            <div class="weather-card" id="weather-card">
                <div class="weather-main">
                    <span class="weather-icon-big" id="weather-icon">☀️</span>
                    <div class="weather-info">
                        <h2 id="weather-city"><?= $t('काठमाडौं', 'Kathmandu') ?></h2>
                        <div class="weather-temp-big" id="weather-temp">--°</div>
                        <p id="weather-desc"><?= $t('लोड हुँदै...', 'Loading...') ?></p>
                    </div>
                </div>
                <div class="weather-stats">
                    <div class="stat-box">
                        <div class="stat-box-value" id="stat-humidity">--%</div>
                        <div class="stat-box-label"><?= $t('आर्द्रता', 'Humidity') ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-value" id="stat-wind">-- km/h</div>
                        <div class="stat-box-label"><?= $t('हावाको गति', 'Wind Speed') ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-value" id="stat-pressure">-- hPa</div>
                        <div class="stat-box-label"><?= $t('हावा दबाब', 'Pressure') ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-value" id="stat-uv">--</div>
                        <div class="stat-box-label"><?= $t('UV इन्डेक्स', 'UV Index') ?></div>
                    </div>
                </div>
            </div>
            <p style="margin-top: var(--space-4); color: var(--dark-500); font-size: 0.875rem;"><?= $t('Source: Open-Meteo API', 'Source: Open-Meteo API') ?></p>
        </div>
    </section>

    <!-- Police Section -->
    <section class="section alert-section" id="police-section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6">🚨 <?= $t('नेपाल प्रहरी सूचनाहरू', 'Nepal Police Updates') ?></h2>
            <p style="color: var(--dark-500); margin-bottom: var(--space-6);"><?= $t('नेपाल प्रहरीको सार्वजनिक सूचनाहरू - ट्राफिक, सडक अवरोध, र सार्वजनिक सूचनाहरू।', 'Nepal Police public notices - traffic, road closures, and public notices.') ?></p>
            <div class="police-list" id="police-list">
                <div class="police-item">
                    <div class="alert-loading" style="padding: var(--space-8); text-align: center; width: 100%;"><?= $t('प्रहरी सूचना लोड हुँदै...', 'Loading police updates...') ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Jobs Section -->
    <section class="section alert-section" id="jobs-section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6">💼 <?= $t('सरकारी जobs', 'Government Jobs') ?></h2>
            <div class="alert alert-info" style="margin-bottom: var(--space-6);">
                <strong>ℹ️ Source</strong>: <?= $t('Lok Sewa Aayog र विभिन्न नेपाली समाचार स्रोतहरूबाट।', 'From Lok Sewa Aayog and various Nepali news sources.') ?>
            </div>
            <div class="jobs-list" id="jobs-list">
                <div class="job-card">
                    <div class="job-header">
                        <div>
                            <div class="job-title"><?= $t('Lok Sewa Aayog - विभिन्न पदहरू', 'Lok Sewa Aayog - Various Positions') ?></div>
                            <div class="job-org"><?= $t('लोकसेवा आयोग, काठमाडौं', 'Lok Sewa Aayog, Kathmandu') ?></div>
                        </div>
                        <span class="job-badge"><?= $t('नयाँ', 'New') ?></span>
                    </div>
                    <p style="color: var(--dark-500); margin-top: var(--space-3);"><?= $t('Lok Sewa Aayog को आधिकारिक वेबसाइटमा जानुहोस्: loksewaaayog.gov.np', 'Visit official Lok Sewa Aayog website: loksewaaayog.gov.np') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Flights Section -->
    <section class="section alert-section" id="flights-section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6">✈️ <?= $t('हवाई उडान स्थिति', 'Flight Status') ?></h2>
            <div class="alert alert-info" style="margin-bottom: var(--space-6);">
                <strong>ℹ️ Source</strong>: <?= $t('AviationStack API बाट TIA (VNKT) को उडानहरू। API Key कुञ्फिगर गर्न AviationStack मा निःशुल्क साइनअप गर्नुहोस्।', 'Flight data from AviationStack API for TIA (VNKT). Configure API key by signing up at AviationStack.') ?>
                <br><small><a href="https://aviationstack.com" target="_blank">aviationstack.com</a> - <?= $t('निःशुल्क 100 रिक्वेस्ट/महिना', 'Free 100 requests/month') ?></small>
            </div>
            <div class="flight-list" id="flight-list">
                <div class="flight-card">
                    <span class="flight-icon">✈️</span>
                    <div class="flight-route">TIA → दिल्ली</div>
                    <div class="flight-time">10:30</div>
                    <span class="flight-status status-on-time"><?= $t('समयमा', 'On Time') ?></span>
                </div>
                <div class="flight-card">
                    <span class="flight-icon">✈️</span>
                    <div class="flight-route">TIA → दुबई</div>
                    <div class="flight-time">14:00</div>
                    <span class="flight-status status-delayed"><?= $t('विलम्बित', 'Delayed') ?></span>
                </div>
                <p style="margin-top: var(--space-4); color: var(--dark-500); font-size: 0.875rem;">
                    <?= $t('यात्रुहरूले TIA को आधिकारिक वेबसाइट: facskyportal.com.np मा जानकारी हेर्नुहोस्।', 'Passengers should check official TIA website: facskyportal.com.np') ?>
                </p>
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
    // Weather icons
    const weatherIcons = {
        'Clear': '☀️', 'Sunny': '☀️', 'Clouds': '☁️', 'Rain': '🌧️',
        'Snow': '❄️', 'Thunderstorm': '⛈️', 'Drizzle': '🌦️', 'Mist': '🌫️', 'default': '🌤️'
    };

    // Tab switching
    function showSection(section) {
        document.querySelectorAll('.alert-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.alert-section').forEach(s => s.classList.remove('active'));
        document.getElementById(section + '-section').classList.add('active');
        event.target.classList.add('active');
    }

    // Load Earthquake Data
    async function loadEarthquakes() {
        try {
            const resp = await fetch('/api/earthquake.php');
            const data = await resp.json();
            const container = document.getElementById('eq-list');
            
            if (data.earthquakes && data.earthquakes.length > 0) {
                container.innerHTML = data.earthquakes.map(eq => {
                    const magClass = eq.magnitude >= 5 ? 'moderate' : eq.magnitude >= 4 ? 'moderate' : 'minor';
                    return `
                        <div class="eq-card ${magClass}">
                            <div class="eq-mag ${magClass}">${eq.magnitude} <span style="font-size: 1rem;">M</span></div>
                            <h3 style="margin-top: var(--space-3);">${eq.place || 'नेपाल क्षेत्र'}</h3>
                            <div class="eq-meta">
                                <div class="eq-meta-item">
                                    <div class="eq-meta-label"><?= $t('गहिराइ', 'Depth') ?></div>
                                    <div class="eq-meta-value">${eq.depth} km</div>
                                </div>
                                <div class="eq-meta-item">
                                    <div class="eq-meta-label"><?= $t('समय', 'Time') ?></div>
                                    <div class="eq-meta-value">${eq.date || 'हाल'}</div>
                                </div>
                                <div class="eq-meta-item">
                                    <div class="eq-meta-label"><?= $t('महसुस', 'Felt') ?></div>
                                    <div class="eq-meta-value">${eq.felt || 0} <?= $t('जना', 'people') ?></div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<div class="eq-card"><p style="text-align:center; padding: var(--space-8);">✓ <?= $t('हाल कुनै भूकम्प भएको छैन', 'No earthquakes currently') ?></p></div>';
            }
        } catch (e) {
            document.getElementById('eq-list').innerHTML = '<div class="eq-card"><p style="text-align:center; padding: var(--space-8); color: var(--dark-500);"><?= $t('भूकम्प डेटा उपलब्ध छैन', 'Earthquake data unavailable') ?></p></div>';
        }
    }

    // Load Weather Data
    async function loadWeather() {
        try {
            const resp = await fetch('/api/weather-alerts.php?type=weather&city=Kathmandu');
            const data = await resp.json();
            if (data.current) {
                const c = data.current;
                document.getElementById('weather-temp').textContent = Math.round(c.temp) + '°';
                document.getElementById('weather-desc').textContent = c.description || '<?= $t('सामान्य', 'Normal') ?>';
                document.getElementById('weather-icon').textContent = weatherIcons[c.icon] || weatherIcons['default'];
                document.getElementById('stat-humidity').textContent = c.humidity + '%';
                document.getElementById('stat-wind').textContent = Math.round(c.wind_speed) + ' km/h';
                document.getElementById('stat-pressure').textContent = c.pressure + ' hPa';
                document.getElementById('stat-uv').textContent = c.uvi || '0';
            }
        } catch (e) {
            console.log('Weather API error');
        }
    }

    // Load Police Alerts
    async function loadPoliceAlerts() {
        try {
            const resp = await fetch('/api/alerts.php?type=police');
            const data = await resp.json();
            const container = document.getElementById('police-list');
            if (data.alerts && data.alerts.length > 0) {
                container.innerHTML = data.alerts.map(a => `
                    <div class="police-item">
                        <span class="police-icon">${a.type === 'traffic' ? '🚧' : '📢'}</span>
                        <div class="police-content">
                            <div class="police-title">${a.title || a.description || '<?= $t('सूचना', 'Notice') ?>'}</div>
                            <div class="police-time">${a.date || '<?= $t('हालै', 'Recent') ?>'}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="police-item"><p style="text-align:center; padding: var(--space-6); width:100%;">✓ <?= $t('कुनै सूचना छैन', 'No notices') ?></p></div>';
            }
        } catch (e) {
            document.getElementById('police-list').innerHTML = '<div class="police-item"><p style="text-align:center; padding: var(--space-6);"><?= $t('प्रहरी डेटा उपलब्ध छैन', 'Police data unavailable') ?></p></div>';
        }
    }

    // Load Jobs
    async function loadJobs() {
        try {
            const resp = await fetch('/api/loksewa.php');
            const data = await resp.json();
            const container = document.getElementById('jobs-list');
            if (data.jobs && data.jobs.length > 0) {
                container.innerHTML = data.jobs.slice(0, 10).map(job => `
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <div class="job-title">${job.title || '<?= $t('जobs', 'Job') ?>'}</div>
                                <div class="job-org">${job.organization || '<?= $t('सरकारी निकाय', 'Government Body') ?>'}</div>
                            </div>
                            <span class="job-badge">${job.type || '<?= $t('नयाँ', 'New') ?>'}</span>
                        </div>
                        <div class="job-deadline">
                            <?= $t('अन्तिम मिति:', 'Deadline:') ?> <strong>${job.deadline || '<?= $t('हेर्नुहोस्', 'See details') ?>'}</strong>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="job-card"><p style="text-align:center; padding: var(--space-6);"><?= $t('जobs उपलब्ध छैन', 'No jobs available') ?></p></div>';
            }
        } catch (e) {
            document.getElementById('jobs-list').innerHTML = '<div class="job-card"><p style="text-align:center; padding: var(--space-6);"><?= $t('जobs डेटा उपलब्ध छैन', 'Jobs data unavailable') ?></p></div>';
        }
    }

    // Load Flights
    async function loadFlights() {
        try {
            const resp = await fetch('/api/flight-status.php');
            const data = await resp.json();
            const container = document.getElementById('flight-list');
            if (data.flights && data.flights.length > 0) {
                container.innerHTML = data.flights.map(flight => `
                    <div class="flight-card">
                        <span class="flight-icon">✈️</span>
                        <div class="flight-route">${flight.from || 'TIA'} → ${flight.to || 'Destination'}</div>
                        <div class="flight-time">${flight.time || '--:--'}</div>
                        <span class="flight-status ${flight.status === 'On Time' ? 'status-on-time' : flight.status === 'Delayed' ? 'status-delayed' : 'status-cancelled'}">${flight.status || '<?= $t('जान्नुहोस्', 'Check') ?>'}</span>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="flight-card"><p style="text-align:center; padding: var(--space-6); width:100%;"><?= $t('उडान जानकारी उपलब्ध छैन', 'Flight info unavailable') ?></p></div>';
            }
        } catch (e) {
            document.getElementById('flight-list').innerHTML = '<div class="flight-card"><p style="text-align:center; padding: var(--space-6);"><?= $t('उडान डेटा उपलब्ध छैन', 'Flight data unavailable') ?></p></div>';
        }
    }

    // Init
    document.addEventListener('DOMContentLoaded', function() {
        loadEarthquakes();
        loadWeather();
        loadPoliceAlerts();
        loadJobs();
        loadFlights();
    });
    </script>
</body>
</html>