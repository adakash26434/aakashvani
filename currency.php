<?php
/**
 * आकाशवाणी — Currency Exchange Rates
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
    <meta property="og:title" content="<?= $t('मुद्रा विनिमय दर', 'Currency Exchange') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('मुद्रा विनिमय दर', 'Currency Exchange') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--space-8) 0; color: #fff; }
        .rate-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); }
        .rate-row { display: flex; justify-content: space-between; align-items: center; padding: var(--space-4) 0; border-bottom: 1px solid var(--dark-100); }
        .rate-row:last-child { border-bottom: none; }
        .rate-flag { font-size: 1.5rem; margin-right: var(--space-3); }
        .rate-code { font-weight: 700; color: var(--dark-900); }
        .rate-name { font-size: 0.75rem; color: var(--dark-500); }
        .rate-value { font-size: 1.25rem; font-weight: 700; color: var(--primary); }
        .rate-change { font-size: 0.75rem; padding: 2px 8px; border-radius: var(--radius-full); }
        .rate-change.up { background: #dcfce7; color: #16a34a; }
        .rate-change.down { background: #fee2e2; color: #dc2626; }
        .section { padding: var(--space-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--space-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .rate-value { font-size: 1rem; } }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-main">
            <div class="container">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="header-brand"><div class="brand-logo">आ</div><span class="brand-name"><?= $t('आकाशवाणी', 'Aakashvani') ?></span></a>
                    <nav class="main-nav">
                        <div class="container"><div class="nav-list">
                            <a href="/" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg><?= $t('गृह', 'Home') ?></a>
                            <a href="/news.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg><?= $t('समाचार', 'News') ?></a>
                            <a href="/ipo-tracker.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a>
                            <a href="/weather.php" class="nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg><?= $t('मौसम', 'Weather') ?></a>
                        </div></div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <?= $t('मुद्रा विनिमय दर', 'Currency Exchange Rates') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल र बिश्वको ताजा मुद्रा दर', 'Latest Nepal and World currency rates') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 800px;">
            <div id="currency-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="currency-content" style="display:none">
                <div class="rate-card" style="margin-bottom:var(--space-6)">
                    <div class="flex justify-between items-center" style="margin-bottom:var(--space-4)">
                        <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900)"><?= $t('अमेरिकी डलर (USD)', 'US Dollar (USD)') ?></h3>
                        <span id="usd-rate" class="rate-value">-</span>
                    </div>
                    <p style="font-size:0.75rem;color:var(--dark-500)"><?= $t('नोट: यो दर बैंक र मनी एक्सचेन्जमा फरक हुन सक्छ', 'Note: Rates may vary at banks and money exchanges') ?></p>
                </div>
                <div class="rate-card">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-4)"><?= $t('अन्य मुद्राहरू', 'Other Currencies') ?></h3>
                    <div id="currency-list"></div>
                </div>
            </div>
            <div id="currency-error" style="display:none;text-align:center;padding:var(--space-8);color:var(--error)">
                <?= $t('मुद्रा दर लोड हुन सकेन', 'Failed to load currency rates') ?>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container"><div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p></div></div>
    </footer>
    <script>
    async function loadCurrency() {
        try {
            const resp = await fetch('/api/market-data.php?type=forex');
            const data = await resp.json();
            if (data.ok && data.forex) {
                const usd = data.forex.find(c => c.currency === 'USD' || c.code === 'USD');
                if (usd) {
                    document.getElementById('usd-rate').textContent = 'रु ' + (usd.buy || usd.rate || '---');
                }
                const list = document.getElementById('currency-list');
                data.forex.slice(0, 10).forEach(c => {
                    list.innerHTML += '<div class="rate-row"><div><span class="rate-code">' + (c.currency || c.code || '-') + '</span></div><div class="rate-value">रु ' + (c.buy || c.rate || '-') + '</div></div>';
                });
                document.getElementById('currency-loading').style.display = 'none';
                document.getElementById('currency-content').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('currency-loading').style.display = 'none';
            document.getElementById('currency-error').style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', loadCurrency);
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>