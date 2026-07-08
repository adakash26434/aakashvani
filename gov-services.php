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
    <link rel="stylesheet" href="/assets/css/app.css">
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
    <!-- Header -->
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-grid">
                    <a href="/" class="header-brand">
                        <div class="brand-logo">आ</div>
                        <div class="brand-text">
                            <h1><?=$t('आकाशवाणी','Aakashvani')?></h1>
                            <span><?=$t('सूचनाको खुला आकाश','Your Gateway to Information')?></span>
                        </div>
                    </a>
                    <nav class="main-nav">
                        <div class="nav-list">
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?=$t('गृह','Home')?></a>
                            <a href="/news.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg><?=$t('समाचार','News')?></a>
                            <a href="/gov-services.php" class="nav-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:1rem;height:1rem"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg><?=$t('सरकारी सेवा','Gov Services')?></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
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
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?=date('Y')?> <?=$t('आकाशवाणी','Aakashvani')?></p></div>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
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
    });
});
</script>
<script>document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });</script>

    <!-- Mobile Bottom Nav -->
    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <div class="bottom-nav-inner">
            <a href="/" class="bottom-nav-item">
                <i data-lucide="home"></i>
                <span>गृह</span>
            </a>
            <a href="/news.php" class="bottom-nav-item">
                <i data-lucide="newspaper"></i>
                <span>समाचार</span>
            </a>
            <a href="/ipo-tracker.php" class="bottom-nav-item">
                <i data-lucide="trending-up"></i>
                <span>NEPSE</span>
            </a>
            <a href="/nepali-patro.php" class="bottom-nav-item">
                <i data-lucide="calendar-days"></i>
                <span>पात्रो</span>
            </a>
            <a href="/rashifal.php" class="bottom-nav-item">
                <i data-lucide="sparkles"></i>
                <span>राशिफल</span>
            </a>
        </div>
    </nav>

</body>
</html>
