<?php
/**
 * आकाशवाणी — nepali-patro.php (World-Class Nepali Calendar)
 * Premium Nepali calendar with clean, modern design
 */

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/includes/bs-date.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');

// Get current Nepali date
$todayBS = getTodayBS();
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : $todayBS['year'];
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : $todayBS['month'];
$selectedDate = isset($_GET['date']) ? intval($_GET['date']) : $todayBS['day'];

// Nepali months
$nepaliMonths = [
    1 => ['name' => 'बैशाख', 'days' => 31, 'en' => 'Baisakh'],
    2 => ['name' => 'जेठ', 'days' => 31, 'en' => 'Jeth'],
    3 => ['name' => 'आषाढ़', 'days' => 31, 'en' => 'Ashadh'],
    4 => ['name' => 'श्रावण', 'days' => 32, 'en' => 'Shrawan'],
    5 => ['name' => 'भाद्र', 'days' => 31, 'en' => 'Bhadra'],
    6 => ['name' => 'आश्विन', 'days' => 30, 'en' => 'Ashwin'],
    7 => ['name' => 'कार्तिक', 'days' => 30, 'en' => 'Kartik'],
    8 => ['name' => 'मंसिर', 'days' => 29, 'en' => 'Mangsir'],
    9 => ['name' => 'पुष', 'days' => 29, 'en' => 'Poush'],
    10 => ['name' => 'माघ', 'days' => 30, 'en' => 'Magh'],
    11 => ['name' => 'फाल्गुन', 'days' => 30, 'en' => 'Falgun'],
    12 => ['name' => 'चैत्र', 'days' => 31, 'en' => 'Chaitra']
];

// Week days
$weekDays = [
    ['short' => 'आइत', 'en' => 'Sun'],
    ['short' => 'सोम', 'en' => 'Mon'],
    ['short' => 'मंगल', 'en' => 'Tue'],
    ['short' => 'बुध', 'en' => 'Wed'],
    ['short' => 'बिहि', 'en' => 'Thu'],
    ['short' => 'शुक्र', 'en' => 'Fri'],
    ['short' => 'शनि', 'en' => 'Sat']
];

// Festivals (sample)
$festivals = [
    1 => ['name' => 'नयाँ वर्ष', 'color' => '#10b981'],
    11 => ['name' => 'लोकतन्त्र दिवस', 'color' => '#3b82f6'],
];

// Page title
$pageTitle = $nepaliMonths[$currentMonth]['name'] . ' ' . $currentYear . ' | पात्रो | आकाशवाणी';
?>

<!-- Page Header -->
<section class="patro-header">
    <div class="container">
        <div class="header-content">
            <div class="header-title">
                <i data-lucide="calendar" class="icon-lg"></i>
                <h1><?= $isNepali ? 'नेपाली पात्रो' : 'Nepali Calendar' ?></h1>
            </div>
            
            <!-- Month Navigation -->
            <div class="month-nav">
                <a href="?year=<?= $currentYear ?>&month=<?= $currentMonth <= 1 ? 12 : $currentMonth - 1 ?>&date=1" class="nav-btn">
                    <i data-lucide="chevron-left" class="nav-icon"></i>
                </a>
                <div class="current-month">
                    <span class="month-name"><?= $nepaliMonths[$currentMonth]['name'] ?></span>
                    <span class="year-name"><?= $currentYear ?></span>
                </div>
                <a href="?year=<?= $currentYear ?>&month=<?= $currentMonth >= 12 ? 1 : $currentMonth + 1 ?>&date=1" class="nav-btn">
                    <i data-lucide="chevron-right" class="nav-icon"></i>
                </a>
            </div>
            
            <!-- Quick Jump -->
            <select class="month-select" onchange="window.location.href=this.value">
                <?php foreach ($nepaliMonths as $num => $m): ?>
                <option value="?year=<?= $currentYear ?>&month=<?= $num ?>&date=1" <?= $currentMonth == $num ? 'selected' : '' ?>>
                    <?= $m['name'] ?> <?= $currentYear ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</section>

<!-- Calendar -->
<main class="patro-main">
    <div class="container">
        <div class="patro-layout">
            
            <!-- Calendar Grid -->
            <div class="calendar-section">
                
                <!-- Week Days Header -->
                <div class="calendar-grid-header">
                    <?php foreach ($weekDays as $day): ?>
                    <div class="week-day <?= $day['short'] === 'आइत' || $day['short'] === 'शनि' ? 'weekend' : '' ?>">
                        <span class="day-ne"><?= $day['short'] ?></span>
                        <span class="day-en"><?= $day['en'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Calendar Days -->
                <div class="calendar-grid">
                    <?php
                    // Calculate starting day (simplified)
                    $startDay = ($currentYear * 365 + array_sum(array_column($nepaliMonths, 'days')) + $currentMonth * 30 + $selectedDate) % 7;
                    
                    // Empty cells before first day
                    for ($i = 0; $i < $startDay; $i++): ?>
                    <div class="calendar-day empty"></div>
                    <?php endfor; ?>
                    
                    <?php for ($day = 1; $day <= $nepaliMonths[$currentMonth]['days']; $day++): 
                        $isToday = ($day == $selectedDate);
                        $isWeekend = (($startDay + $day - 1) % 7 == 0 || ($startDay + $day - 1) % 7 == 6);
                        $festival = $festivals[$day] ?? null;
                    ?>
                    <a href="?year=<?= $currentYear ?>&month=<?= $currentMonth ?>&date=<?= $day ?>" 
                       class="calendar-day <?= $isToday ? 'today' : '' ?> <?= $isWeekend ? 'weekend' : '' ?> <?= $festival ? 'has-festival' : '' ?>">
                        <span class="day-number"><?= $day ?></span>
                        <?php if ($festival): ?>
                        <span class="festival-name"><?= $festival['name'] ?></span>
                        <?php endif; ?>
                        <?php if ($isToday): ?>
                        <span class="today-indicator"></span>
                        <?php endif; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                
            </div>
            
            <!-- Today Info -->
            <aside class="today-section">
                
                <div class="today-card">
                    <div class="today-header">
                        <span class="today-label"><?= $isNepali ? 'आज' : 'Today' ?></span>
                        <span class="today-date"><?= $selectedDate ?> <?= $nepaliMonths[$currentMonth]['name'] ?> <?= $currentYear ?></span>
                    </div>
                    
                    <div class="today-info">
                        <div class="info-item">
                            <i data-lucide="sun" class="info-icon"></i>
                            <div>
                                <span class="info-label"><?= $isNepali ? 'सूर्योदय' : 'Sunrise' ?></span>
                                <span class="info-value"><?= $isNepali ? 'बिहानी' : 'Morning' ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i data-lucide="sunset" class="info-icon"></i>
                            <div>
                                <span class="info-label"><?= $isNepali ? 'सूर्यास्त' : 'Sunset' ?></span>
                                <span class="info-value"><?= $isNepali ? 'साँझ' : 'Evening' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Panchang -->
                <div class="panchang-card">
                    <h3 class="card-title">
                        <i data-lucide="star" class="title-icon"></i>
                        <?= $isNepali ? 'पञ्चाङ्ग' : 'Panchang' ?>
                    </h3>
                    <div class="panchang-list">
                        <div class="panchang-item">
                            <span class="panchang-label"><?= $isNepali ? 'तिथि' : 'Tithi' ?></span>
                            <span class="panchang-value"><?= $isNepali ? 'शुक्ल पक्ष' : 'Shukla Paksha' ?></span>
                        </div>
                        <div class="panchang-item">
                            <span class="panchang-label"><?= $isNepali ? 'नक्षत्र' : 'Nakshatra' ?></span>
                            <span class="panchang-value"><?= $isNepali ? 'रोहिणी' : 'Rohini' ?></span>
                        </div>
                        <div class="panchang-item">
                            <span class="panchang-label"><?= $isNepali ? 'योग' : 'Yoga' ?></span>
                            <span class="panchang-value"><?= $isNepali ? 'शुभ' : 'Shubha' ?></span>
                        </div>
                        <div class="panchang-item">
                            <span class="panchang-label"><?= $isNepali ? 'करण' : 'Karan' ?></span>
                            <span class="panchang-value"><?= $isNepali ? 'बालव' : 'Balav' ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming -->
                <div class="upcoming-card">
                    <h3 class="card-title">
                        <i data-lucide="calendar-check" class="title-icon"></i>
                        <?= $isNepali ? 'आगामी चाडपर्व' : 'Upcoming Festivals' ?>
                    </h3>
                    <div class="upcoming-list">
                        <?php foreach (array_slice($festivals, 0, 3) as $day => $fest): ?>
                        <div class="upcoming-item">
                            <span class="upcoming-day"><?= $day ?></span>
                            <span class="upcoming-name"><?= $fest['name'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            </aside>
        </div>
    </div>
</main>

<style>
/* ═══════════════════════════════════════════════════════════════
   PATRO (CALENDAR) PAGE STYLES
   ═══════════════════════════════════════════════════════════════ */

/* Header */
.patro-header {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: 32px 0;
    color: #fff;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title i { color: #10b981; }

.header-title h1 {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
}

/* Month Navigation */
.month-nav {
    display: flex;
    align-items: center;
    gap: 16px;
}

.nav-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #fff;
    transition: all 0.2s;
}

.nav-btn:hover {
    background: #10b981;
}

.nav-icon { width: 20px; height: 20px; }

.current-month {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.month-name {
    font-size: 20px;
    font-weight: 700;
}

.year-name {
    font-size: 14px;
    color: #94a3b8;
}

/* Month Select */
.month-select {
    padding: 10px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
}

.month-select option { color: #0f172a; }

/* Main */
.patro-main {
    padding: 32px 0;
    background: #f8fafc;
}

.patro-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 32px;
}

@media (max-width: 1024px) {
    .patro-layout {
        grid-template-columns: 1fr;
    }
    .today-section {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}

@media (max-width: 640px) {
    .today-section {
        grid-template-columns: 1fr;
    }
}

/* Calendar Section */
.calendar-section {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Week Days Header */
.calendar-grid-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
    margin-bottom: 8px;
}

.week-day {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
}

.week-day.weekend {
    background: #fef2f2;
}

.day-ne {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}

.day-en {
    font-size: 11px;
    color: #94a3b8;
}

/* Calendar Grid */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.calendar-day {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
    padding: 8px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
    position: relative;
}

.calendar-day:hover {
    background: #f1f5f9;
}

.calendar-day.empty {
    background: transparent;
}

.calendar-day.today {
    background: #10b981;
    color: #fff;
}

.calendar-day.today:hover {
    background: #059669;
}

.calendar-day.weekend .day-number {
    color: #ef4444;
}

.calendar-day.today.weekend .day-number {
    color: #fff;
}

.day-number {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
}

.festival-name {
    font-size: 9px;
    color: #10b981;
    text-align: center;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.calendar-day.today .festival-name {
    color: rgba(255,255,255,0.8);
}

.today-indicator {
    position: absolute;
    bottom: 6px;
    width: 4px;
    height: 4px;
    background: #fff;
    border-radius: 50%;
}

/* Today Section */
.today-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.today-card,
.panchang-card,
.upcoming-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.today-header {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.today-label {
    font-size: 12px;
    font-weight: 600;
    color: #10b981;
    text-transform: uppercase;
}

.today-date {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
}

.today-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-icon {
    width: 20px;
    height: 20px;
    color: #10b981;
}

.info-item div {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 11px;
    color: #94a3b8;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}

/* Card Title */
.card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.title-icon {
    width: 18px;
    height: 18px;
    color: #10b981;
}

/* Panchang */
.panchang-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.panchang-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: #f8fafc;
    border-radius: 8px;
}

.panchang-label {
    font-size: 12px;
    color: #64748b;
}

.panchang-value {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

/* Upcoming */
.upcoming-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.upcoming-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #f8fafc;
    border-radius: 8px;
}

.upcoming-day {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #10b981;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    border-radius: 6px;
}

.upcoming-name {
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
}

/* Responsive */
@media (max-width: 640px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-title h1 { font-size: 22px; }
    .calendar-day {
        padding: 4px;
    }
    .day-number { font-size: 14px; }
    .festival-name { display: none; }
}
</style>

<?php include __DIR__ . '/footer.php'; ?>
