<?php
/**
 * आकाशवाणी — Nokari (Jobs)
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
    <meta property="og:title" content="<?= $t('नोकरी', 'Jobs') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('नोकरी - रोजगार सुचना', 'Jobs - Employment Information') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <style>
        .page-header { background: linear-gradient(135deg, #059669, #10b981); padding: var(--space-8) 0; color: #fff; }
        .job-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); border-left: 4px solid #059669; }
        .job-title { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); margin-bottom: var(--space-2); }
        .job-company { font-size: 0.875rem; color: #059669; font-weight: 600; margin-bottom: var(--space-2); }
        .job-meta { display: flex; flex-wrap: wrap; gap: var(--space-3); font-size: 0.75rem; color: var(--dark-500); margin-bottom: var(--space-3); }
        .job-meta span { display: flex; align-items: center; gap: 4px; }
        .job-desc { font-size: 0.875rem; color: var(--dark-600); margin-bottom: var(--space-3); line-height: 1.6; }
        .job-salary { font-size: 1rem; font-weight: 700; color: #059669; }
        .job-badge { display: inline-block; padding: 2px 10px; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 600; }
        .badge-new { background: #dcfce7; color: #16a34a; }
        .badge-hot { background: #fee2e2; color: #dc2626; }
        .section { padding: var(--space-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--space-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: #059669; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .job-meta { gap: var(--space-2); } }
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

    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#fff"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <?= $t('नोकरी - रोजगार सुचना', 'Jobs - Employment Information') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल र बिश्वको ताजा रोजगारी सुचना', 'Latest job vacancies in Nepal and abroad') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 900px;">
            <div id="jobs-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="jobs-list" style="display:none"></div>
            <div id="jobs-empty" style="display:none;text-align:center;padding:var(--space-8);color:var(--dark-500)">
                <?= $t('हाल कुनै नोकरी छैन', 'No jobs available at this time') ?>
            </div>
            <div id="jobs-error" style="display:none;text-align:center;padding:var(--space-8);color:var(--error)">
                <?= $t('डाटा लोड हुन सकेन', 'Failed to load data') ?>
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
    async function loadJobs() {
        try {
            const resp = await fetch('/api/nokari.php?limit=30');
            const data = await resp.json();
            if (data.ok && data.jobs) {
                renderJobs(data.jobs);
                document.getElementById('jobs-loading').style.display = 'none';
                document.getElementById('jobs-list').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('jobs-loading').style.display = 'none';
            document.getElementById('jobs-error').style.display = 'block';
        }
    }
    function renderJobs(jobs) {
        const list = document.getElementById('jobs-list');
        if (!jobs || jobs.length === 0) {
            list.style.display = 'none';
            document.getElementById('jobs-empty').style.display = 'block';
            return;
        }
        list.innerHTML = jobs.map(job => {
            const badge = job.is_new ? '<span class="job-badge badge-new"><?= $t("नयाँ", "New") ?></span>' : '';
            return '<div class="job-card"><div class="flex justify-between items-start gap-4" style="margin-bottom:var(--space-2)"><h3 class="job-title">' + (job.title || job.position || '---') + '</h3>' + badge + '</div><p class="job-company">' + (job.company || job.employer || '---') + '</p><div class="job-meta"><span>📍 ' + (job.location || '---') + '</span>' + (job.salary ? '<span>💰 ' + job.salary + '</span>' : '') + (job.deadline ? '<span>⏰ ' + job.deadline + '</span>' : '') + '</div><p class="job-desc">' + (job.description || job.desc || '') + '</p>' + (job.link ? '<a href="' + job.link + '" target="_blank" style="display:inline-block;padding:var(--space-2) var(--space-4);background:#059669;color:#fff;border-radius:var(--radius-lg);font-size:0.875rem;text-decoration:none"><?= $t("आवेदन दिनुहोस्", "Apply Now") ?> →</a>' : '') + '</div>';
        }).join('');
    }
    document.addEventListener('DOMContentLoaded', loadJobs);
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>