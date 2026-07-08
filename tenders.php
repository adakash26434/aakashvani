<?php
/**
 * आकाशवाणी — Government Tenders Page
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
    <title><?= $t('सरकारी टेन्डर', 'Government Tenders') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        .tenders-hero { background: linear-gradient(135deg, #1e3a5f, #0c4a6e); padding: var(--sp-16) 0; color: #fff; text-align: center; }
        .tender-card { background: #fff; border-radius: var(--radius-xl); padding: var(--sp-6); box-shadow: var(--shadow); margin-bottom: var(--sp-4); transition: all var(--transition); border-left: 4px solid var(--primary); }
        .tender-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .tender-header { display: flex; justify-content: space-between; align-items: flex-start; gap: var(--sp-4); margin-bottom: var(--sp-4); }
        .tender-org { font-size: 0.75rem; font-weight: 600; color: var(--primary); text-transform: uppercase; margin-bottom: var(--sp-1); }
        .tender-title { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); line-height: 1.4; }
        .tender-number { font-size: 0.875rem; color: var(--dark-500); margin-top: var(--sp-1); }
        .tender-badge { padding: var(--sp-1) var(--sp-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
        .badge-new { background: var(--primary-50); color: var(--primary-700); }
        .badge-urgent { background: #fef2f2; color: #b91c1c; }
        .badge-closing { background: #fef3c7; color: #92400e; }
        .tender-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--sp-4); padding: var(--sp-4); background: var(--dark-50); border-radius: var(--radius-lg); margin-bottom: var(--sp-4); }
        .tender-detail { text-align: center; }
        .tender-detail-label { font-size: 0.75rem; color: var(--dark-500); margin-bottom: var(--sp-1); }
        .tender-detail-value { font-weight: 600; color: var(--dark-900); }
        .tender-footer { display: flex; justify-content: space-between; align-items: center; padding-top: var(--sp-4); border-top: 1px solid var(--dark-100); }
        .tender-date { font-size: 0.875rem; color: var(--dark-500); }
        .filter-section { background: #fff; border-radius: var(--radius-xl); padding: var(--sp-6); box-shadow: var(--shadow); margin-bottom: var(--sp-6); }
        .filter-grid { display: flex; flex-wrap: wrap; gap: var(--sp-4); align-items: center; }
        .section { padding: var(--sp-12) 0; }
        .category-tabs { display: flex; gap: var(--sp-2); flex-wrap: wrap; margin-bottom: var(--sp-6); }
        .category-tab { padding: var(--sp-2) var(--sp-4); background: var(--dark-100); border: none; border-radius: var(--radius-full); cursor: pointer; font-size: 0.875rem; transition: all var(--transition); }
        .category-tab:hover { background: var(--dark-200); }
        .category-tab.active { background: var(--primary); color: #fff; }
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
                    <li><a href="/tenders.php" class="active"><?= $t('टेन्डर', 'Tenders') ?></a></li>
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

    <!-- Tenders Hero -->
    <section class="tenders-hero">
        <div class="container">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: var(--sp-2);">
                📋 <?= $t('सरकारी टेन्डर', 'Government Tenders') ?>
            </h1>
            <p style="opacity: 0.8;"><?= $t('सरकारी निकायहरूबाट प्रकाशित टेन्डरहरू', 'Tenders published by government bodies') ?></p>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="section" style="padding-bottom: 0;">
        <div class="container">
            <div class="filter-section">
                <h3 class="font-semibold mb-4"><?= $t('श्रेणी', 'Categories') ?></h3>
                <div class="category-tabs">
                    <button class="category-tab active" onclick="filterTenders('all')"><?= $t('सबै', 'All') ?></button>
                    <button class="category-tab" onclick="filterTenders('construction')">🏗️ <?= $t('निर्माण', 'Construction') ?></button>
                    <button class="category-tab" onclick="filterTenders('it')">💻 <?= $t('सूचना प्रविधि', 'IT') ?></button>
                    <button class="category-tab" onclick="filterTenders('consultancy')">📊 <?= $t('परामर्श', 'Consultancy') ?></button>
                    <button class="category-tab" onclick="filterTenders('goods')">📦 <?= $t('सामान', 'Goods') ?></button>
                    <button class="category-tab" onclick="filterTenders('services')">🔧 <?= $t('सेवा', 'Services') ?></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Tenders List -->
    <section class="section" style="padding-top: var(--sp-6);">
        <div class="container" style="max-width: 900px;">
            <div id="tenders-list">
                <!-- Tenders will be loaded here -->
                <div class="tender-card">
                    <div class="tender-header">
                        <div>
                            <div class="tender-org"><?= $t('नेपाल सरकार - भौतिक पूर्वाधार मन्त्रालय', 'Gov of Nepal - Ministry of Physical Infrastructure') ?></div>
                            <div class="tender-title"><?= $t('सडक निर्माण तथा मर्मत सम्भार ठेक्का', 'Road Construction and Maintenance Contract') ?></div>
                            <div class="tender-number"><?= $t('टेन्डर नं.', 'Tender No.') ?>: GON-MOP-2026-001</div>
                        </div>
                        <span class="tender-badge badge-new"><?= $t('नयाँ', 'NEW') ?></span>
                    </div>
                    <div class="tender-details">
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('अनुमानित मूल्य', 'Est. Value') ?></div>
                            <div class="tender-detail-value">रु. 5,00,00,000</div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('धरौटी', 'Security') ?></div>
                            <div class="tender-detail-value">रु. 5,00,000</div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('बिड खुल्ने', 'Bid Opening') ?></div>
                            <div class="tender-detail-value"><?= date('d M Y', strtotime('+15 days')) ?></div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('बाँकी दिन', 'Days Left') ?></div>
                            <div class="tender-detail-value"><?= date('t') - date('j') ?> <?= $t('दिन', 'days') ?></div>
                        </div>
                    </div>
                    <div class="tender-footer">
                        <span class="tender-date"><?= $t('प्रकाशित', 'Published') ?>: <?= date('d M Y') ?></span>
                        <a href="#" class="btn btn-primary btn-sm"><?= $t('विवरण हेर्नुहोस्', 'View Details') ?></a>
                    </div>
                </div>

                <div class="tender-card">
                    <div class="tender-header">
                        <div>
                            <div class="tender-org"><?= $t('काठमाडौं महानगरपालिका', 'Kathmandu Metropolitan City') ?></div>
                            <div class="tender-title"><?= $t('स्मार्ट सिटी आईटी इन्फ्रास्ट्रक्चर', 'Smart City IT Infrastructure') ?></div>
                            <div class="tender-number"><?= $t('टेन्डर नं.', 'Tender No.') ?>: KMC-IT-2026-045</div>
                        </div>
                        <span class="tender-badge badge-urgent"><?= $t('अत्यावश्यक', 'URGENT') ?></span>
                    </div>
                    <div class="tender-details">
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('अनुमानित मूल्य', 'Est. Value') ?></div>
                            <div class="tender-detail-value">रु. 2,50,00,000</div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('धरौटी', 'Security') ?></div>
                            <div class="tender-detail-value">रु. 2,50,000</div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('बिड खुल्ने', 'Bid Opening') ?></div>
                            <div class="tender-detail-value"><?= date('d M Y', strtotime('+7 days')) ?></div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('बाँकी दिन', 'Days Left') ?></div>
                            <div class="tender-detail-value" style="color: var(--error);"><?= date('t') - date('j') ?> <?= $t('दिन', 'days') ?></div>
                        </div>
                    </div>
                    <div class="tender-footer">
                        <span class="tender-date"><?= $t('प्रकाशित', 'Published') ?>: <?= date('d M Y', strtotime('-3 days')) ?></span>
                        <a href="#" class="btn btn-primary btn-sm"><?= $t('विवरण हेर्नुहोस्', 'View Details') ?></a>
                    </div>
                </div>

                <div class="tender-card">
                    <div class="tender-header">
                        <div>
                            <div class="tender-org"><?= $t('शिक्षा मन्त्रालय', 'Ministry of Education') ?></div>
                            <div class="tender-title"><?= $t('विद्यालय भवन निर्माण ठेक्का', 'School Building Construction Contract') ?></div>
                            <div class="tender-number"><?= $t('टेन्डर नं.', 'Tender No.') ?>: MOE-BLDG-2026-012</div>
                        </div>
                        <span class="tender-badge badge-closing"><?= $t('बन्द हुँदै', 'CLOSING') ?></span>
                    </div>
                    <div class="tender-details">
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('अनुमानित मूल्य', 'Est. Value') ?></div>
                            <div class="tender-detail-value">रु. 1,80,00,000</div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('धरौटी', 'Security') ?></div>
                            <div class="tender-detail-value">रु. 1,80,000</div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('बिड खुल्ने', 'Bid Opening') ?></div>
                            <div class="tender-detail-value"><?= date('d M Y', strtotime('+3 days')) ?></div>
                        </div>
                        <div class="tender-detail">
                            <div class="tender-detail-label"><?= $t('बाँकी दिन', 'Days Left') ?></div>
                            <div class="tender-detail-value" style="color: var(--error);"><?= max(0, date('t') - date('j') - 10) ?> <?= $t('दिन', 'days') ?></div>
                        </div>
                    </div>
                    <div class="tender-footer">
                        <span class="tender-date"><?= $t('प्रकाशित', 'Published') ?>: <?= date('d M Y', strtotime('-20 days')) ?></span>
                        <a href="#" class="btn btn-primary btn-sm"><?= $t('विवरण हेर्नुहोस्', 'View Details') ?></a>
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
    // Filter tenders by category
    function filterTenders(category) {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');
        
        // In real implementation, this would fetch from API
        console.log('Filtering:', category);
    }
    </script>
    <script>
    // Load tenders from API
    async function loadTenders() {
        const container = document.getElementById('tenders-list');
        if (!container) return;
        
        container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--sp-8)"><div class="tender-title">लोड हुँदैछ...</div></div>';
        
        try {
            const resp = await fetch('/api/government-tenders.php?days=30');
            const data = await resp.json();
            
            if (data.ok && data.tenders && data.tenders.length > 0) {
                renderTenders(data.tenders);
            } else {
                container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--sp-8)"><div class="tender-title"><?= $t('अहिले कुनै टेन्डर छैन', 'No tenders available') ?></div></div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--sp-8)"><div class="tender-title"><?= $t('डाटा लोड हुन सकेन', 'Failed to load data') ?></div></div>';
        }
    }
    
    function renderTenders(tenders) {
        const container = document.getElementById('tenders-list');
        container.innerHTML = tenders.map(t => \`
            <div class="tender-card">
                <div class="tender-header">
                    <div>
                        <div class="tender-org">\${t.ministry || 'सरकारी निकाय'}</div>
                        <div class="tender-title">\${t.title || t.description || '<?= $t('टेन्डर', 'Tender') ?>'}</div>
                        <div class="tender-number"><?= $t('टेन्डर नं.', 'Tender No.') ?>: \${t.tender_number || t.tender_no || 'N/A'}</div>
                    </div>
                    <span class="tender-badge badge-\${getBadgeClass(t)}">\${getBadgeText(t)}</span>
                </div>
                <div class="tender-details">
                    <div class="tender-detail">
                        <div class="tender-detail-label"><?= $t('अनुमानित मूल्य', 'Est. Value') ?></div>
                        <div class="tender-detail-value">\${t.estimated_value ? 'रु ' + Number(t.estimated_value).toLocaleString() : 'N/A'}</div>
                    </div>
                    <div class="tender-detail">
                        <div class="tender-detail-label"><?= $t('म्याद', 'Deadline') ?></div>
                        <div class="tender-detail-value">\${t.deadline || 'N/A'}</div>
                    </div>
                    <div class="tender-detail">
                        <div class="tender-detail-label"><?= $t('कागजात', 'Documents') ?></div>
                        <div class="tender-detail-value">\${t.document_price ? 'रु ' + Number(t.document_price).toLocaleString() : '<?= $t('निःशुल्क', 'Free') ?>'}</div>
                    </div>
                </div>
                <div class="tender-footer">
                    <div class="tender-date"><?= $t('प्रकाशित', 'Published') ?>: \${t.published_date || t.created_at || '<?= date('Y-m-d') ?>'}</div>
                    <a href="\${t.source_url || 'https://bolpatra.gov.np'}" target="_blank" class="btn btn-primary btn-sm"><?= $t('विवरण', 'Details') ?></a>
                </div>
            </div>
        \`).join('');
    }
    
    function getBadgeClass(t) {
        if (t.is_new) return 'new';
        if (t.is_urgent) return 'urgent';
        if (t.closing_soon) return 'closing';
        return 'new';
    }
    
    function getBadgeText(t) {
        if (t.is_new) return '<?= $t('नयाँ', 'NEW') ?>';
        if (t.is_urgent) return '<?= $t('जरुरी', 'URGENT') ?>';
        if (t.closing_soon) return '<?= $t('बन्द हुँदै', 'CLOSING') ?>';
        return '<?= $t('नयाँ', 'NEW') ?>';
    }
    
    function filterTenders(category) {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');
        // Reload with category filter
        loadTendersWithCategory(category);
    }
    
    async function loadTendersWithCategory(category) {
        const container = document.getElementById('tenders-list');
        container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--sp-8)"><div class="tender-title">लोड हुँदैछ...</div></div>';
        
        try {
            const url = category === 'all' 
                ? '/api/government-tenders.php?days=30'
                : '/api/government-tenders.php?category=' + category + '&days=30';
            const resp = await fetch(url);
            const data = await resp.json();
            
            if (data.ok && data.tenders && data.tenders.length > 0) {
                renderTenders(data.tenders);
            } else {
                container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--sp-8)"><div class="tender-title"><?= $t('यस श्रेणीमा कुनै टेन्डर छैन', 'No tenders in this category') ?></div></div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--sp-8)"><div class="tender-title"><?= $t('डाटा लोड हुन सकेन', 'Failed to load data') ?></div></div>';
        }
    }
    
    // Load on page load
    document.addEventListener('DOMContentLoaded', loadTenders);
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

<script>
(function() {
    'use strict';
    async function loadMarket() {
        try {
            var resp = await fetch('/api/market-data.php?type=all');
            if (!resp.ok) return;
            var d = await resp.json();
            if (d.nepse) {
                var n = d.nepse, v = document.getElementById('nepse-value'), c = document.getElementById('nepse-change');
                if (v && n.index) v.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits:2});
                if (c && n.change !== undefined) {
                    var up = n.change >= 0;
                    c.textContent = (up ? '+' : '') + n.change.toFixed(2);
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
    }
    function initSearch() {
        var toggle = document.getElementById('searchToggle'), bar = document.getElementById('searchBar');
        if (toggle && bar) toggle.addEventListener('click', function() { bar.style.display = bar.style.display === 'none' ? 'block' : 'none'; });
        var nt = document.getElementById('navToggle'), nl = document.getElementById('navList');
        if (nt && nl) nt.addEventListener('click', function() { nl.classList.toggle('open'); });
    }
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        loadMarket();
        initSearch();
        setInterval(loadMarket, 5 * 60 * 1000);
    });
})();
</script>
</body>
</html>