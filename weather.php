<?php
/**
 * आकाशवाणी — Weather Page (Live API)
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
$cities = [
    ['name' => 'काठमाडौं', 'en' => 'Kathmandu', 'temp' => '...'],
    ['name' => 'पोखरा', 'en' => 'Pokhara', 'temp' => '...'],
    ['name' => 'बुटवल', 'en' => 'Butwal', 'temp' => '...'],
    ['name' => 'विराटनगर', 'en' => 'Biratnagar', 'temp' => '...'],
    ['name' => 'नेपालगञ्ज', 'en' => 'Nepalgunj', 'temp' => '...'],
    ['name' => 'धनगढी', 'en' => 'Dhangadhi', 'temp' => '...'],
    ['name' => 'इटहरी', 'en' => 'Ithari', 'temp' => '...'],
    ['name' => 'जनकपुर', 'en' => 'Janakpur', 'temp' => '...'],
];
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
    <title><?= $t('मौसम', 'Weather') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        .weather-hero { background: linear-gradient(135deg, #1e3a5f, #2563eb); padding: var(--space-16) 0; color: #fff; text-align: center; }
        .weather-main { display: flex; align-items: center; justify-content: center; gap: var(--space-6); margin-bottom: var(--space-4); }
        .weather-icon { font-size: 5rem; }
        .weather-temp { font-size: 5rem; font-weight: 800; line-height: 1; }
        .weather-unit { font-size: 2rem; font-weight: 400; opacity: 0.8; }
        .weather-desc { font-size: 1.5rem; opacity: 0.9; }
        .weather-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-4); margin-top: var(--space-8); }
        .city-card { background: rgba(255,255,255,0.1); border-radius: var(--radius-xl); padding: var(--space-6); backdrop-filter: blur(10px); transition: all var(--transition); cursor: pointer; }
        .city-card:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        .city-name { font-size: 1rem; font-weight: 600; margin-bottom: var(--space-2); }
        .city-temp { font-size: 2rem; font-weight: 800; }
        .section { padding: var(--space-12) 0; }
        .weather-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); }
        .weather-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); margin-top: var(--space-6); }
        .stat-item { text-align: center; padding: var(--space-4); background: var(--dark-50); border-radius: var(--radius-lg); }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
        .stat-label { font-size: 0.75rem; color: var(--dark-500); margin-top: var(--space-1); }
    </style>

    <style>
        /* Responsive */
        @media (max-width: 768px) {
            .emergency-hero, .weather-hero, .tenders-hero { padding: var(--space-8) 0; }
            .emergency-hero h1, .weather-hero h1, .tenders-hero h1 { font-size: 1.75rem; }
            .emergency-grid, .weather-grid, .tender-card { padding: var(--space-4); }
        }
        
        @media (max-width: 480px) {
            .emergency-hero h1, .weather-hero h1, .tenders-hero h1 { font-size: 1.5rem; }
            .emergency-number { font-size: 1.25rem; }
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
                    <li><a href="/weather.php" class="active"><?= $t('मौसम', 'Weather') ?></a></li>
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

    <!-- Weather Hero -->
    <section class="weather-hero">
        <div class="container">
            <h1 style="font-size: 1.25rem; opacity: 0.8; margin-bottom: var(--space-4);"><?= $t('नेपालको मौसम', 'Weather in Nepal') ?></h1>
            <div class="weather-main">
                <span class="weather-icon" id="weather-icon">☀️</span>
                <span class="weather-temp" id="weather-temp">--°</span>
            </div>
            <p class="weather-desc" id="weather-desc"><?= $t('लोड हुँदै...', 'Loading...') ?></p>
            <p style="opacity: 0.7; margin-top: var(--space-2);" id="weather-city"><?= $t('काठमाडौं', 'Kathmandu') ?></p>
        </div>
    </section>

    <!-- Weather Stats -->
    <section class="section" style="margin-top: -var(--space-8);">
        <div class="container">
            <div class="weather-card" style="margin-top: -var(--space-16);">
                <div class="weather-stats" id="weather-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="stat-humidity">--%</div>
                        <div class="stat-label"><?= $t('आर्द्रता', 'Humidity') ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="stat-wind">-- km/h</div>
                        <div class="stat-label"><?= $t('हावा', 'Wind') ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="stat-uv">--</div>
                        <div class="stat-label"><?= $t('UV इन्डेक्स', 'UV Index') ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="stat-pressure">-- hPa</div>
                        <div class="stat-label"><?= $t('हावा दबाब', 'Pressure') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- City Grid -->
    <section class="section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6"><?= $t('अन्य शहरहरू', 'Other Cities') ?></h2>
            <div class="weather-grid" id="city-grid">
                <?php foreach ($cities as $city): ?>
                <div class="city-card" onclick="loadCityWeather('<?= $city['en'] ?>')">
                    <div class="city-name"><?= $isNepali ? $city['name'] : $city['en'] ?></div>
                    <div class="city-temp" id="city-temp-<?= strtolower(str_replace(' ', '-', $city['en'])) ?>">--°</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Earthquake Alerts -->
    <section class="section" style="background: var(--dark-50);">
        <div class="container">
            <h2 class="text-xl font-bold mb-6">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--error); width: 1.5rem; height: 1.5rem; vertical-align: middle; margin-right: 8px;"><path d="m2 22 1-1h3l9-9"/><path d="M3 21v-3l9-9"/><path d="m15 6 3.4-3.4a2.1 2.1 0 1 1 3 3L18 9l.4.4a2.1 2.1 0 1 1-3 3l-3.8-3.8a2.1 2.1 0 1 1-3-3l.4-.4Z"/></svg>
                <?= $t('भूकम्प अलर्ट', 'Earthquake Alerts') ?>
            </h2>
            <div id="earthquake-list">
                <div class="alert alert-info"><?= $t('भूकम्पको जानकारी लोड हुँदै...', 'Loading earthquake data...') ?></div>
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
    // Weather icons map
    const weatherIcons = {
        'Clear': '☀️', 'Sunny': '☀️', 'Clouds': '☁️', 'Rain': '🌧️',
        'Snow': '❄️', 'Thunderstorm': '⛈️', 'Drizzle': '🌦️', 'Mist': '🌫️',
        'Haze': '🌫️', 'Fog': '🌫️', 'default': '🌤️'
    };

    // Load weather data
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
                document.getElementById('stat-uv').textContent = c.uvi || '0';
                document.getElementById('stat-pressure').textContent = c.pressure + ' hPa';
            }
        } catch (e) {
            document.getElementById('weather-temp').textContent = '25°';
            document.getElementById('weather-desc').textContent = '<?= $t('आंशिक बादल', 'Partly Cloudy') ?>';
        }
    }

    // Load city weather
    async function loadCityWeather(city) {
        try {
            const resp = await fetch('/api/weather-alerts.php?type=weather&city=' + city);
            const data = await resp.json();
            if (data.current) {
                const id = 'city-temp-' + city.toLowerCase().replace(' ', '-');
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = Math.round(data.current.temp) + '°';
                }
                // Update main display
                document.getElementById('weather-temp').textContent = Math.round(data.current.temp) + '°';
                document.getElementById('weather-desc').textContent = data.current.description || '';
                document.getElementById('weather-icon').textContent = weatherIcons[data.current.icon] || weatherIcons['default'];
                document.getElementById('weather-city').textContent = city;
            }
        } catch (e) {
            console.log('Weather API error');
        }
    }

    // Load earthquake data
    async function loadEarthquakes() {
        try {
            const resp = await fetch('/api/earthquake.php');
            const data = await resp.json();
            const container = document.getElementById('earthquake-list');
            
            if (data.earthquakes && data.earthquakes.length > 0) {
                container.innerHTML = data.earthquakes.slice(0, 3).map(eq => `
                    <div class="alert ${eq.magnitude > 5 ? 'alert-error' : 'alert-warning'}" style="margin-bottom: var(--space-3);">
                        <strong>${eq.magnitude} ${eq.magnitude_type || 'M'}</strong> - ${eq.location || 'नेपाल'}
                        <br><small>${eq.time || 'हाल'} | ${eq.depth || '?'} km गहिराइ</small>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="alert alert-success">✓ <?= $t('हाल कुनै ठूलो भूकम्प छैन', 'No major earthquake currently') ?></div>';
            }
        } catch (e) {
            document.getElementById('earthquake-list').innerHTML = '<div class="alert alert-info"><?= $t('भूकम्प डेटा उपलब्ध छैन', 'Earthquake data unavailable') ?></div>';
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        loadWeather();
        loadEarthquakes();
        (async function() {
            try {
                var resp = await fetch('/api/market-data.php?type=all');
                if (!resp.ok) return;
                var d = await resp.json();
                if (d.nepse) {
                    var n = d.nepse, v = document.getElementById('nepse-value'), c = document.getElementById('nepse-change');
                    if (v && n.index) v.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits:2});
                    if (c && n.change !== undefined) {
                        var up = n.change >= 0;
                        c.textContent = (up ? '+' : '') + n.change.toFixed(2) + ' (' + (up ? '+' : '') + n.changePercent.toFixed(2) + '%)';
                        c.className = 'tp-mkt-change ' + (up ? 'up' : 'down');
                    }
                }
                if (d.gold && d.gold.hallmarkPerTola) { var gv = document.getElementById('gold-value'); if (gv) gv.textContent = 'रु ' + Number(d.gold.hallmarkPerTola).toLocaleString('en-US'); }
                if (d.forex && d.forex.rates && d.forex.rates.length > 0) {
                    var usd = d.forex.rates.find(function(r) { return r.code === 'USD'; });
                    if (usd) { var fv = document.getElementById('forex-value'); if (fv) fv.textContent = 'रु ' + usd.sell.toFixed(2); }
                }
                if (d.petrol && d.petrol.petrol) { var pv = document.getElementById('petrol-value'); if (pv) pv.textContent = 'रु ' + d.petrol.petrol; }
            } catch(e) {}
        })();
        var toggle = document.getElementById('searchToggle'), bar = document.getElementById('searchBar');
        if (toggle && bar) toggle.addEventListener('click', function() { bar.style.display = bar.style.display === 'none' ? 'block' : 'none'; });
        var navToggle = document.getElementById('navToggle'), navList = document.getElementById('navList');
        if (navToggle && navList) navToggle.addEventListener('click', function() { navList.classList.toggle('open'); });
    });
    </script>

</body>
</html>