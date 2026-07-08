<?php
require_once __DIR__ . '/config.php';
$lang=siteLang();
$isNepali=($lang!=='en');
$t=fn($ne,$en)=>$isNepali?$ne:$en;
// Services loaded via API (JS fetch)
?>
<!DOCTYPE html>
<html lang="<?=$isNepali?'ne':'en'?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$t('सरकारी सेवा','Government Services')?> | आकाशवाणी</title>
    <meta property="og:title" content="<?= $t('आकाशवाणी', 'Aakashvani') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    </title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        .page-header{background:linear-gradient(135deg,var(--dark-900),var(--dark-800));padding:var(--space-12) 0;color:#fff}
        .services-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:var(--space-6)}
        .service-card{background:#fff;border-radius:var(--radius-xl);border:1px solid var(--dark-100);padding:var(--space-8);text-align:center;transition:all var(--transition);text-decoration:none;display:block}
        .service-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:var(--primary)}
        .service-icon{width:72px;height:72px;background:var(--primary-50);border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-4);color:var(--primary)}
        .service-name{font-size:1.125rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-2)}
        .service-desc{font-size:0.875rem;color:var(--dark-500)}
        .section{padding:var(--space-12) 0}
        /* Responsive */
        @media (max-width: 768px) {
            .page-header { padding: var(--space-8) 0; }
            .page-header h1 { font-size: 1.75rem; }
            .content-section { padding: var(--space-6) 0; }
        }
        
        @media (max-width: 480px) {
            .page-header h1 { font-size: 1.5rem; }
            .btn { padding: var(--space-2) var(--space-4); font-size: 0.875rem; }
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

    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                <?=$t('सरकारी सेवाहरू','Government Services')?>
            </h1>
            <p class="page-subtitle"><?=$t('नेपाल सरकारका महत्वपूर्ण सेवाहरू','Important services of Government of Nepal')?></p>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <!-- Category Filter -->
            <div class="flex gap-4 mb-6" style="flex-wrap:wrap;gap:var(--space-3);margin-bottom:var(--space-6)">
                <button class="btn-cat active" data-cat="all" style="padding:var(--space-2) var(--space-4);border:1px solid var(--dark-200);border-radius:var(--radius-full);background:var(--primary);color:#fff;cursor:pointer"><?=$t('सबै','All')?></button>
                <button class="btn-cat" data-cat="citizenship" style="padding:var(--space-2) var(--space-4);border:1px solid var(--dark-200);border-radius:var(--radius-full);background:#fff;cursor:pointer"><?=$t('नागरिकता','Citizenship')?></button>
                <button class="btn-cat" data-cat="passport" style="padding:var(--space-2) var(--space-4);border:1px solid var(--dark-200);border-radius:var(--radius-full);background:#fff;cursor:pointer"><?=$t('राहदानी','Passport')?></button>
                <button class="btn-cat" data-cat="tax" style="padding:var(--space-2) var(--space-4);border:1px solid var(--dark-200);border-radius:var(--radius-full);background:#fff;cursor:pointer"><?=$t('कर','Tax')?></button>
                <button class="btn-cat" data-cat="land" style="padding:var(--space-2) var(--space-4);border:1px solid var(--dark-200);border-radius:var(--radius-full);background:#fff;cursor:pointer"><?=$t('जग्गा','Land')?></button>
                <button class="btn-cat" data-cat="education" style="padding:var(--space-2) var(--space-4);border:1px solid var(--dark-200);border-radius:var(--radius-full);background:#fff;cursor:pointer"><?=$t('शिक्षा','Education')?></button>
            </div>
            <div id="services-loading" style="text-align:center;padding:var(--space-8)"><div class="spinner" style="width:40px;height:40px;border:3px solid var(--dark-200);border-top-color:var(--primary);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto"></div></div>
            <div id="services-grid" class="services-grid" style="display:none"></div>
            <div id="services-error" style="display:none;text-align:center;padding:var(--space-8);color:var(--error)"><?=$t('सेवा लोड हुन सकेन','Failed to load services')?></div>
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
let allServices = {};
async function loadServices() {
    const grid = document.getElementById('services-grid');
    const loading = document.getElementById('services-loading');
    const error = document.getElementById('services-error');
    try {
        const resp = await fetch('/api/gov-services.php', { cache: 'no-store' });
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        const data = await resp.json();
        if (data && data.services) {
            allServices = data.services;
            renderServices('all');
            loading.style.display = 'none';
            grid.style.display = 'grid';
        } else { 
            console.log('API response:', data);
            throw new Error('Invalid data format'); 
        }
    } catch(e) { 
        console.error('Services load error:', e);
        loading.style.display = 'none'; 
        error.style.display = 'block';
        error.innerHTML = '<p style="color:var(--error);text-align:center;padding:var(--space-4)"><?= $t("सेवा लोड हुन सकेन। कृपया पुनः प्रयास गर्नुहोस्।","Services failed to load. Please try again.") ?></p><button onclick="loadServices()" class="btn btn-primary"><?= $t("पुनः लोड गर्नुहोस्","Reload") ?></button>';
    }
}
function renderServices(cat) {
    const grid = document.getElementById('services-grid');
    const cats = cat === 'all' ? Object.keys(allServices) : [cat];
    let html = '';
    cats.forEach(c => {
        if (allServices[c]) {
            allServices[c].forEach(svc => {
                html += '<div class="service-card" style="background:#fff;border-radius:var(--radius-xl);border:1px solid var(--dark-100);padding:var(--space-6);text-align:left">';
                html += '<h3 style="font-size:1.125rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-2)">' + svc.name + '</h3>';
                html += '<p style="font-size:0.875rem;color:var(--dark-500);margin-bottom:var(--space-3)">' + svc.desc + '</p>';
                html += '<div style="font-size:0.75rem;color:var(--dark-400);margin-bottom:var(--space-2)"><strong><?= $t("आवश्यक कागजात","Required Docs") ?>:</strong> ' + (svc.docs ? svc.docs.join(', ') : '-') + '</div>';
                html += '<div style="font-size:0.75rem;color:var(--dark-400);margin-bottom:var(--space-2)"><strong><?= $t("शुल्क","Fee") ?>:</strong> ' + svc.fee + '</div>';
                html += '<div style="font-size:0.75rem;color:var(--dark-400);margin-bottom:var(--space-3)"><strong><?= $t("समय","Time") ?>:</strong> ' + svc.time + '</div>';
                if (svc.url) html += '<a href="' + svc.url + '" target="_blank" style="display:inline-block;padding:var(--space-2) var(--space-4);background:var(--primary);color:#fff;border-radius:var(--radius-lg);font-size:0.875rem;text-decoration:none"><?= $t("वेबसाइट","Website") ?> →</a>';
                html += '</div>';
            });
        }
    });
    grid.innerHTML = html;
    // Update button states
    document.querySelectorAll('.btn-cat').forEach(btn => {
        btn.style.background = btn.dataset.cat === cat ? 'var(--primary)' : '#fff';
        btn.style.color = btn.dataset.cat === cat ? '#fff' : 'inherit';
    });
}
// Category buttons
document.addEventListener('DOMContentLoaded', () => {
    loadServices();
    document.querySelectorAll('.btn-cat').forEach(btn => {
        btn.addEventListener('click', () => renderServices(btn.dataset.cat));
    }
        (function() {
            var s=document.createElement('script');s.src='https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';document.head.appendChild(s);
        })();
);
});
</script>
<script>document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
        (function() {
            var s=document.createElement('script');s.src='https://unpkg.com/lucide@latest/dist/umd/lucide.min.js';document.head.appendChild(s);
        })();
);</script>

    <!-- Mobile Bottom Nav -->
</body>
</html>
