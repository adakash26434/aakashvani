<?php
/**
 * आकाशवाणी — Nepali Patro v2
 * Premium 2026 Design
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/bs-date.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$todayBS = getTodayBS();
$year = isset($_GET['year']) ? (int)$_GET['year'] : $todayBS['year'];
$month = isset($_GET['month']) ? (int)$_GET['month'] : $todayBS['month'];

$nepaliMonths = [
    1 => 'बैशाख', 2 => 'जेठ', 3 => 'आषाढ़', 4 => 'श्रावण',
    5 => 'भाद्र', 6 => 'आश्विन', 7 => 'कार्तिक', 8 => 'मंसिर',
    9 => 'पुष', 10 => 'माघ', 11 => 'फाल्गुन', 12 => 'चैत्र'
];

$weekDays = ['आइत', 'सोम', 'मंगल', 'बुध', 'बिहि', 'शुक्र', 'शनि'];
$monthDays = [31, 31, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30];
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
    <title><?= $nepaliMonths[$month] ?> <?= $year ?> | <?= $t('पात्रो', 'Calendar') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--dark-900), var(--dark-800));
            padding: var(--sp-12) 0;
            color: #fff;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: var(--sp-1);
        }
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            transition: all var(--transition);
            cursor: pointer;
        }
        .calendar-day:hover { background: var(--primary); color: #fff; }
        .calendar-day.today { background: var(--primary); color: #fff; font-weight: 700; }
        .calendar-day.weekend { color: var(--error); }
        .calendar-day.empty { background: transparent; cursor: default; }
        .calendar-day.empty:hover { background: transparent; color: inherit; }
        .week-header {
            background: var(--dark-50);
            border-radius: var(--radius-lg);
            font-weight: 600;
            color: var(--dark-600);
        }
        .week-header.weekend { color: var(--error); }
        .day-number { font-size: 1rem; font-weight: 600; }
        .calendar-section { padding: var(--sp-12) 0; }
        .info-card {
            background: #fff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--dark-100);
            padding: var(--sp-6);
        }
        .info-card-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--dark-900);
            margin-bottom: var(--sp-4);
            padding-bottom: var(--sp-2);
            border-bottom: 2px solid var(--primary);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: var(--sp-2) 0;
            border-bottom: 1px solid var(--dark-100);
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--dark-500); font-size: 0.875rem; }
        .info-value { font-weight: 600; font-size: 0.875rem; }
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

    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= $t('नेपाली पात्रो', 'Nepali Calendar') ?>
            </h1>
            <p class="page-subtitle"><?= $nepaliMonths[$month] ?> <?= $year ?></p>
        </div>
    </section>
    
    <!-- Calendar -->
    <section class="calendar-section">
        <div class="container">
            <!-- Month Navigation -->
            <div class="flex items-center justify-between mb-6">
                <a href="?year=<?= $year ?>&month=<?= $month <= 1 ? 12 : $month - 1 ?>" class="btn btn-secondary">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <?= $t('अघिल्लो', 'Previous') ?>
                </a>
                <h2 class="text-xl font-bold"><?= $nepaliMonths[$month] ?> <?= $year ?></h2>
                <a href="?year=<?= $year ?>&month=<?= $month >= 12 ? 1 : $month + 1 ?>" class="btn btn-secondary">
                    <?= $t('अर्को', 'Next') ?>
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
            
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Calendar -->
                <div class="lg:col-span-2">
                    <div class="info-card">
                        <!-- Week Headers -->
                        <div class="calendar-grid mb-1">
                            <?php foreach ($weekDays as $i => $day): ?>
                            <div class="calendar-day week-header <?= $i == 0 || $i == 6 ? 'weekend' : '' ?>">
                                <span class="day-number"><?= $day ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Days -->
                        <div class="calendar-grid">
                            <?php
                            $startDay = ($year * 365 + array_sum(array_slice($monthDays, 0, $month - 1)) + 1) % 7;
                            for ($i = 0; $i < $startDay; $i++): ?>
                            <div class="calendar-day empty"></div>
                            <?php endfor; ?>
                            <?php for ($day = 1; $day <= $monthDays[$month - 1]; $day++): ?>
                            <?php $isToday = ($day == $todayBS['day'] && $month == $todayBS['month'] && $year == $todayBS['year']); ?>
                            <div class="calendar-day <?= $isToday ? 'today' : '' ?> <?= ($startDay + $day - 1) % 7 == 0 || ($startDay + $day - 1) % 7 == 6 ? 'weekend' : '' ?>">
                                <span class="day-number"><?= $day ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="space-y-4">
                    <div class="info-card">
                        <h3 class="info-card-title"><?= $t('आजको मिति', 'Today') ?></h3>
                        <div class="info-row">
                            <span class="info-label"><?= $t('बिक्रम सम्वत', 'Bikram Samwat') ?></span>
                            <span class="info-value"><?= $todayBS['day'] ?> <?= $nepaliMonths[$todayBS['month']] ?> <?= $todayBS['year'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?= $t('इस्वी सम्वत', 'Gregorian') ?></span>
                            <span class="info-value"><?= date('j F Y') ?></span>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <h3 class="info-card-title"><?= $t('पञ्चाङ्ग', 'Panchang') ?></h3>
                        <div id="panchang-loading" style="text-align:center;padding:8px"><span style="font-size:0.75rem;color:var(--dark-400)"><?= $t('लोड हुँदै...','Loading...') ?></span></div>
                        <div id="panchang-data" style="display:none">
                            <div class="info-row">
                                <span class="info-label"><?= $t('तिथि', 'Tithi') ?></span>
                                <span class="info-value" id="panchang-tithi">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?= $t('नक्षत्र', 'Nakshatra') ?></span>
                                <span class="info-value" id="panchang-nakshatra">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?= $t('योग', 'Yoga') ?></span>
                                <span class="info-value" id="panchang-yoga">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label"><?= $t('करण', 'Karan') ?></span>
                                <span class="info-value" id="panchang-karan">-</span>
                            </div>
                        </div>
                    </div>
                </div>
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
    async function loadPanchang() {
        try {
            const resp = await fetch('/api/panchang.php');
            const data = await resp.json();
            if (data.ok && data.panchang) {
                const p = data.panchang;
                document.getElementById('panchang-tithi').textContent = p.tithi_name + ' ' + p.tithi;
                document.getElementById('panchang-nakshatra').textContent = p.nakshatra;
                document.getElementById('panchang-yoga').textContent = p.yoga;
                document.getElementById('panchang-karan').textContent = p.karan;
                document.getElementById('panchang-loading').style.display = 'none';
                document.getElementById('panchang-data').style.display = 'block';
            }
        } catch(e) { console.error('Panchang error:', e); }
    }
    document.addEventListener('DOMContentLoaded', loadPanchang);
    </script>

    <!-- Mobile Bottom Nav -->
<script>document.addEventListener('DOMContentLoaded',function(){if(typeof lucide!=='undefined')lucide.createIcons()}
        (function() {
            var s=document.createElement('script');s.src='/assets/js/lucide.min.js';document.head.appendChild(s);
        })();
);</script>
</body>
</html>
