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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nepaliMonths[$month] ?> <?= $year ?> | <?= $t('पात्रो', 'Calendar') ?> | आकाशवाणी</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--dark-900), var(--dark-800));
            padding: var(--space-12) 0;
            color: #fff;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: var(--space-1);
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
        .calendar-section { padding: var(--space-12) 0; }
        .info-card {
            background: #fff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--dark-100);
            padding: var(--space-6);
        }
        .info-card-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--dark-900);
            margin-bottom: var(--space-4);
            padding-bottom: var(--space-2);
            border-bottom: 2px solid var(--primary);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: var(--space-2) 0;
            border-bottom: 1px solid var(--dark-100);
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--dark-500); font-size: 0.875rem; }
        .info-value { font-weight: 600; font-size: 0.875rem; }
    </style>

    <style>
        /* Responsive */
        @media (max-width: 768px) {
            .page-header { padding: var(--space-8) 0; }
            .page-header h1 { font-size: 1.75rem; }
        }
        
        @media (max-width: 480px) {
            .page-header h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <span class="brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                    </a>
                    <nav class="main-nav">
                        <div class="container">
                            <div class="nav-list">
                                <a href="/" class="nav-link"><?= $t('गृह', 'Home') ?></a>
                        <a href="/news.php" class="nav-link"><?= $t('समाचार', 'News') ?></a>
                        <a href="/nepali-patro.php" class="nav-link active"><?= $t('पात्रो', 'Calendar') ?></a>
                        <a href="/rashifal.php" class="nav-link"><?= $t('राशिफल', 'Horoscope') ?></a>
                        <a href="/ipo-tracker.php" class="nav-link"><?= $t('IPO', 'IPO') ?></a>
                    </nav>
                    <div class="header-actions">
                        <button class="btn btn-ghost btn-icon" aria-label="Search">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
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
                        <div class="info-row">
                            <span class="info-label"><?= $t('तिथि', 'Tithi') ?></span>
                            <span class="info-value"><?= $t('शुक्ल पक्ष', 'Shukla Paksha') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?= $t('नक्षत्र', 'Nakshatra') ?></span>
                            <span class="info-value"><?= $t('रोहिणी', 'Rohini') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><?= $t('योग', 'Yoga') ?></span>
                            <span class="info-value"><?= $t('शुभ', 'Shubha') ?></span>
                        </div>
                    </div>
                </div>
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
