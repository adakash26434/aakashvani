<?php
/**
 * आकाशवाणी — IPO Tracker (LIVE API DATA)
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
// IPO data loaded via JavaScript API from /api/ipo-data.php
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
    <title><?= $t('IPO ट्र्याकर', 'IPO Tracker') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--space-12) 0; color: #fff; }
        .ipo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: var(--space-6); }
        .ipo-card { background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--dark-100); padding: var(--space-6); transition: all var(--transition); }
        .ipo-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .ipo-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4); }
        .ipo-symbol { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .ipo-status { padding: var(--space-1) var(--space-3); border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; }
        .ipo-status.open { background: var(--primary-50); color: var(--primary-700); }
        .ipo-status.upcoming { background: #fef3c7; color: #92400e; }
        .ipo-status.closed { background: var(--dark-100); color: var(--dark-500); }
        .ipo-company { font-size: 1rem; font-weight: 600; color: var(--dark-900); margin-bottom: var(--space-4); }
        .ipo-details { display: flex; flex-direction: column; gap: var(--space-2); margin-bottom: var(--space-4); }
        .ipo-detail { display: flex; justify-content: space-between; padding: var(--space-2) var(--space-3); background: var(--dark-50); border-radius: var(--radius); }
        .ipo-detail-label { font-size: 0.875rem; color: var(--dark-500); }
        .ipo-detail-value { font-size: 0.875rem; font-weight: 600; color: var(--dark-900); }
        .section { padding: var(--space-12) 0; }
        .ipo-section:nth-child(even) { background: var(--dark-50); }
        .loading-spinner { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--space-12); }
        .spinner { width: 48px; height: 48px; border: 4px solid var(--dark-200); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Responsive */
        @media (max-width: 640px) {
            .page-title { font-size: 1.5rem; }
            .ipo-card { padding: var(--space-4); }
            .ipo-symbol { font-size: 1.25rem; }
            .ipo-grid { grid-template-columns: 1fr; gap: var(--space-4); }
        }
    </style>
</head>
<body>
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
                                <a href="/nepali-patro.php" class="nav-link"><?= $t('पात्रो', 'Calendar') ?></a>
                                <a href="/rashifal.php" class="nav-link"><?= $t('राशिफल', 'Horoscope') ?></a>
                                <a href="/ipo-tracker.php" class="nav-link active"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a>
                                <a href="/weather.php" class="nav-link"><?= $t('मौसम', 'Weather') ?></a>
                                <a href="/cricket.php" class="nav-link"><?= $t('क्रिकेट', 'Cricket') ?></a>
                                <a href="/tenders.php" class="nav-link"><?= $t('टेन्डर', 'Tenders') ?></a>
                                <a href="/emergency.php" class="nav-link"><?= $t('आपतकालीन', 'Emergency') ?></a>
                            </div>
                        </div>
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
    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                <?= $t('IPO ट्र्याकर', 'IPO Tracker') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपालका IPO र मर्जर आइपिओको विवरण', 'Details of IPOs and merger IPOs in Nepal') ?></p>
        </div>
    </section>
    
    <section class="ipo-section">
        <div class="container">
            <!-- Loading State -->
            <div id="ipo-loading" class="loading-spinner">
                <div class="spinner"></div>
                <p style="margin-top:var(--space-4);color:var(--dark-500)"><?= $t('IPO डाटा लोड हुँदै...', 'Loading IPO data...') ?></p>
            </div>
            
            <!-- Error State -->
            <div id="ipo-error" style="display:none;text-align:center;padding:var(--space-8)">
                <p style="color:var(--error);margin-bottom:var(--space-4)"><?= $t('IPO डाटा लोड हुन सकेन', 'Failed to load IPO data') ?></p>
                <button onclick="loadIPOs()" class="btn btn-primary"><?= $t('पुनः प्रयास गर्नुहोस्', 'Retry') ?></button>
            </div>
            
            <!-- Open IPOs -->
            <h2 class="text-xl font-bold mb-6" id="open-title" style="padding-top:var(--space-6);display:none"><?= $t('खुला IPO', 'Open IPOs') ?></h2>
            <div class="ipo-grid" id="open-ipos" style="display:none"></div>
            
            <!-- Upcoming IPOs -->
            <h2 class="text-xl font-bold mb-6" id="upcoming-title" style="padding-top:var(--space-8);display:none"><?= $t('आगामी IPO', 'Upcoming IPOs') ?></h2>
            <div class="ipo-grid" id="upcoming-ipos" style="display:none"></div>
            
            <!-- Last Updated -->
            <p id="ipo-updated" style="display:none;text-align:center;padding:var(--space-6);font-size:0.75rem;color:var(--dark-400)"></p>
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
<script>
async function loadIPOs() {
    const loading = document.getElementById('ipo-loading');
    const error = document.getElementById('ipo-error');
    const openGrid = document.getElementById('open-ipos');
    const upcomingGrid = document.getElementById('upcoming-ipos');
    const openTitle = document.getElementById('open-title');
    const upcomingTitle = document.getElementById('upcoming-title');
    const updated = document.getElementById('ipo-updated');
    
    loading.style.display = 'flex';
    error.style.display = 'none';
    
    try {
        const resp = await fetch('/api/ipo-data.php');
        const data = await resp.json();
        
        if (data.ok && data.ipos && data.ipos.length > 0) {
            const now = new Date().toISOString().split('T')[0];
            const openIPOs = data.ipos.filter(i => i.status === 'Active' || (i.openDate && i.closeDate && i.openDate <= now && i.closeDate >= now));
            const upcomingIPOs = data.ipos.filter(i => i.status === 'Upcoming' || !i.openDate || i.openDate > now);
            
            if (openIPOs.length > 0) {
                openGrid.innerHTML = openIPOs.map(ipo => `
                    <div class="ipo-card">
                        <div class="ipo-header">
                            <span class="ipo-symbol">${ipo.symbol || '-'}</span>
                            <span class="ipo-status open"><?= $t('खुला', 'OPEN') ?></span>
                        </div>
                        <h3 class="ipo-company">${ipo.name || ipo.company || '-'}</h3>
                        <div class="ipo-details">
                            <div class="ipo-detail">
                                <span class="ipo-detail-label"><?= $t('मूल्य', 'Price') ?></span>
                                <span class="ipo-detail-value">${ipo.price || 'रु 0'}</span>
                            </div>
                            <div class="ipo-detail">
                                <span class="ipo-detail-label"><?= $t('कुल युनिट', 'Total Units') ?></span>
                                <span class="ipo-detail-value">${ipo.shares || '-'}</span>
                            </div>
                            <div class="ipo-detail">
                                <span class="ipo-detail-label"><?= $t('बन्द हुने', 'Close Date') ?></span>
                                <span class="ipo-detail-value">${ipo.closeDate || '-'}</span>
                            </div>
                        </div>
                        <button class="btn btn-primary w-full"><?= $t('थप विवरण', 'More Details') ?></button>
                    </div>
                `).join('');
                openTitle.style.display = 'block';
                openGrid.style.display = 'grid';
            }
            
            if (upcomingIPOs.length > 0) {
                upcomingGrid.innerHTML = upcomingIPOs.map(ipo => `
                    <div class="ipo-card">
                        <div class="ipo-header">
                            <span class="ipo-symbol">${ipo.symbol || '-'}</span>
                            <span class="ipo-status upcoming"><?= $t('आगामी', 'UPCOMING') ?></span>
                        </div>
                        <h3 class="ipo-company">${ipo.name || ipo.company || '-'}</h3>
                        <div class="ipo-details">
                            <div class="ipo-detail">
                                <span class="ipo-detail-label"><?= $t('मूल्य', 'Price') ?></span>
                                <span class="ipo-detail-value">${ipo.price || 'रु 0'}</span>
                            </div>
                            <div class="ipo-detail">
                                <span class="ipo-detail-label"><?= $t('कुल युनिट', 'Total Units') ?></span>
                                <span class="ipo-detail-value">${ipo.shares || '-'}</span>
                            </div>
                            <div class="ipo-detail">
                                <span class="ipo-detail-label"><?= $t('खुला हुने', 'Open Date') ?></span>
                                <span class="ipo-detail-value">${ipo.openDate || '-'}</span>
                            </div>
                        </div>
                        <button class="btn btn-secondary w-full"><?= $t('अलर्ट सेट गर्नुहोस्', 'Set Alert') ?></button>
                    </div>
                `).join('');
                upcomingTitle.style.display = 'block';
                upcomingGrid.style.display = 'grid';
            }
            
            if (data.fetched_at) {
                updated.textContent = '<?= $t("अन्तिम अपडेट:", "Last updated:") ?> ' + data.fetched_at;
                updated.style.display = 'block';
            }
            
            loading.style.display = 'none';
        } else {
            throw new Error('No IPO data');
        }
    } catch(e) {
        loading.style.display = 'none';
        error.style.display = 'block';
        console.error('IPO Error:', e);
    }
}

document.addEventListener('DOMContentLoaded', loadIPOs);
</script>
</body>
</html>
