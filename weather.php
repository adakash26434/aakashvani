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
    <link rel="stylesheet" href="/assets/css/app.css">
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
    <!-- Header -->
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
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?= $t('गृह', 'Home') ?></a>
                            <a href="/weather.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg><?= $t('मौसम', 'Weather') ?></a>
                            <a href="/cricket.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><circle cx="12" cy="12" r="10"/></svg><?= $t('क्रिकेट', 'Cricket') ?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

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

    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p>
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
        loadWeather();
        loadEarthquakes();
    });
    </script>

    <!-- Mobile Bottom Nav -->
    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <div class="bottom-nav-inner">
            <a href="/" class="bottom-nav-item"><i data-lucide="home"></i><span>गृह</span></a>
            <a href="/news.php" class="bottom-nav-item"><i data-lucide="newspaper"></i><span>समाचार</span></a>
            <a href="/ipo-tracker.php" class="bottom-nav-item"><i data-lucide="trending-up"></i><span>NEPSE</span></a>
            <a href="/nepali-patro.php" class="bottom-nav-item"><i data-lucide="calendar-days"></i><span>पात्रो</span></a>
            <a href="/rashifal.php" class="bottom-nav-item"><i data-lucide="sparkles"></i><span>राशिफल</span></a>
        </div>
    </nav>

<script>document.addEventListener('DOMContentLoaded',function(){if(typeof lucide!=='undefined')lucide.createIcons()});</script>
</body>
</html>