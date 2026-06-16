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
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal's most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('सरकारी टेन्डर', 'Government Tenders') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .tenders-hero { background: linear-gradient(135deg, #1e3a5f, #0c4a6e); padding: var(--space-16) 0; color: #fff; text-align: center; }
        .tender-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); transition: all var(--transition); border-left: 4px solid var(--primary); }
        .tender-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .tender-header { display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-4); margin-bottom: var(--space-4); }
        .tender-org { font-size: 0.75rem; font-weight: 600; color: var(--primary); text-transform: uppercase; margin-bottom: var(--space-1); }
        .tender-title { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); line-height: 1.4; }
        .tender-number { font-size: 0.875rem; color: var(--dark-500); margin-top: var(--space-1); }
        .tender-badge { padding: var(--space-1) var(--space-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
        .badge-new { background: var(--primary-50); color: var(--primary-700); }
        .badge-urgent { background: #fef2f2; color: #b91c1c; }
        .badge-closing { background: #fef3c7; color: #92400e; }
        .tender-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--space-4); padding: var(--space-4); background: var(--dark-50); border-radius: var(--radius-lg); margin-bottom: var(--space-4); }
        .tender-detail { text-align: center; }
        .tender-detail-label { font-size: 0.75rem; color: var(--dark-500); margin-bottom: var(--space-1); }
        .tender-detail-value { font-weight: 600; color: var(--dark-900); }
        .tender-footer { display: flex; justify-content: space-between; align-items: center; padding-top: var(--space-4); border-top: 1px solid var(--dark-100); }
        .tender-date { font-size: 0.875rem; color: var(--dark-500); }
        .filter-section { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-6); }
        .filter-grid { display: flex; flex-wrap: wrap; gap: var(--space-4); align-items: center; }
        .section { padding: var(--space-12) 0; }
        .category-tabs { display: flex; gap: var(--space-2); flex-wrap: wrap; margin-bottom: var(--space-6); }
        .category-tab { padding: var(--space-2) var(--space-4); background: var(--dark-100); border: none; border-radius: var(--radius-full); cursor: pointer; font-size: 0.875rem; transition: all var(--transition); }
        .category-tab:hover { background: var(--dark-200); }
        .category-tab.active { background: var(--primary); color: #fff; }
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
                            <a href="/gov-services.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg><?= $t('सरकारी सेवा', 'Gov Services') ?></a>
                            <a href="/tenders.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg><?= $t('टेन्डर', 'Tenders') ?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Tenders Hero -->
    <section class="tenders-hero">
        <div class="container">
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: var(--space-2);">
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
    <section class="section" style="padding-top: var(--space-6);">
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

    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p>
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
        
        container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--space-8)"><div class="tender-title">लोड हुँदैछ...</div></div>';
        
        try {
            const resp = await fetch('/api/government-tenders.php?days=30');
            const data = await resp.json();
            
            if (data.ok && data.tenders && data.tenders.length > 0) {
                renderTenders(data.tenders);
            } else {
                container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--space-8)"><div class="tender-title"><?= $t('अहिले कुनै टेन्डर छैन', 'No tenders available') ?></div></div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--space-8)"><div class="tender-title"><?= $t('डाटा लोड हुन सकेन', 'Failed to load data') ?></div></div>';
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
        container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--space-8)"><div class="tender-title">लोड हुँदैछ...</div></div>';
        
        try {
            const url = category === 'all' 
                ? '/api/government-tenders.php?days=30'
                : '/api/government-tenders.php?category=' + category + '&days=30';
            const resp = await fetch(url);
            const data = await resp.json();
            
            if (data.ok && data.tenders && data.tenders.length > 0) {
                renderTenders(data.tenders);
            } else {
                container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--space-8)"><div class="tender-title"><?= $t('यस श्रेणीमा कुनै टेन्डर छैन', 'No tenders in this category') ?></div></div>';
            }
        } catch (e) {
            container.innerHTML = '<div class="tender-card" style="text-align:center;padding:var(--space-8)"><div class="tender-title"><?= $t('डाटा लोड हुन सकेन', 'Failed to load data') ?></div></div>';
        }
    }
    
    // Load on page load
    document.addEventListener('DOMContentLoaded', loadTenders);
    </script>
</body>
</html>