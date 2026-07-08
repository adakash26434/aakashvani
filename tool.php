<?php
/**
 * आकाशवाणी — Tool Detail Page
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$toolId = $_GET['id'] ?? 'tax-calculator';
$tools = [
    'tax-calculator' => ['name' => 'कर क्यालकुलेटर', 'en' => 'Tax Calculator', 'desc' => 'आयकर गणना'],
    'unit-converter' => ['name' => 'इकाई रूपान्तरक', 'en' => 'Unit Converter', 'desc' => 'इकाई रूपान्तरण'],
    'currency-converter' => ['name' => 'मुद्रा रूपान्तरक', 'en' => 'Currency Converter', 'desc' => 'मुद्रा रूपान्तरण'],
    'bmi-calculator' => ['name' => 'BMI क्यालकुलेटर', 'en' => 'BMI Calculator', 'desc' => 'BMI गणना'],
];
$tool = $tools[$toolId] ?? ['name' => 'टूल', 'en' => 'Tool', 'desc' => ''];
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isNepali ? $tool['name'] : $tool['en'] ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .tool-page { padding: var(--sp-8) 0; }
        .tool-card { background: #fff; border-radius: var(--radius-xl); padding: var(--sp-8); box-shadow: var(--shadow); }
        .tool-result { background: var(--primary-50); border-radius: var(--radius-lg); padding: var(--sp-6); text-align: center; margin-top: var(--sp-6); }
        .tool-result-value { font-size: 2rem; font-weight: 800; color: var(--primary); }
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
            <h1 class="page-title"><?= $isNepali ? $tool['name'] : $tool['en'] ?></h1>
            <p class="page-subtitle"><?= $isNepali ? $tool['desc'] : $tool['desc'] ?></p>
        </div>
    </section>

    <section class="tool-page">
        <div class="container" style="max-width: 600px;">
            <div class="tool-card">
                <?php if ($toolId === 'tax-calculator'): ?>
                <div class="form-group">
                    <label class="form-label"><?= $t('वार्षिक आय (रु.)', 'Annual Income (Rs.)') ?></label>
                    <input type="number" class="input input-lg" id="income" placeholder="500000" oninput="calculateTax()">
                </div>
                <div class="tool-result">
                    <p style="color: var(--dark-500); margin-bottom: var(--sp-2);"><?= $t('अनुमानित कर', 'Estimated Tax') ?></p>
                    <div class="tool-result-value" id="tax-result">रु. 0</div>
                </div>
                <script>
                function calculateTax() {
                    const income = parseFloat(document.getElementById('income').value) || 0;
                    let tax = 0;
                    if (income > 500000) tax = (income - 500000) * 0.01;
                    if (income > 700000) tax = 2000 + (income - 700000) * 0.1;
                    if (income > 1000000) tax = 32000 + (income - 1000000) * 0.2;
                    if (income > 2000000) tax = 92000 + (income - 2000000) * 0.3;
                    if (income > 5000000) tax = 192000 + (income - 5000000) * 0.36;
                    document.getElementById('tax-result').textContent = 'रु. ' + Math.round(tax).toLocaleString();
                }
                </script>
                <?php elseif ($toolId === 'bmi-calculator'): ?>
                <div class="form-group">
                    <label class="form-label"><?= $t('तौल (kg)', 'Weight (kg)') ?></label>
                    <input type="number" class="input input-lg" id="weight" placeholder="70" oninput="calculateBMI()">
                </div>
                <div class="form-group">
                    <label class="form-label"><?= $t('उचाई (cm)', 'Height (cm)') ?></label>
                    <input type="number" class="input input-lg" id="height" placeholder="170" oninput="calculateBMI()">
                </div>
                <div class="tool-result">
                    <p style="color: var(--dark-500); margin-bottom: var(--sp-2);"><?= $t('तपाईंको BMI', 'Your BMI') ?></p>
                    <div class="tool-result-value" id="bmi-result">0</div>
                </div>
                <script>
                function calculateBMI() {
                    const weight = parseFloat(document.getElementById('weight').value) || 0;
                    const height = parseFloat(document.getElementById('height').value) || 0;
                    if (weight > 0 && height > 0) {
                        const bmi = weight / Math.pow(height / 100, 2);
                        document.getElementById('bmi-result').textContent = bmi.toFixed(1);
                    }
                }
                </script>
                <?php else: ?>
                <div class="alert alert-info">
                    <?= $t('यो टूल छिट्टै आउँदैछ।', 'This tool is coming soon.') ?>
                </div>
                <?php endif; ?>
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

</body>
</html>