<?php
/**
 * आकाशवाणी — Gold & Silver Prices
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
    <meta property="og:title" content="<?= $t('सुनको मूल्य', 'Gold Price') ?> | आकाशवाणी">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('सुन र चाँदीको मूल्य', 'Gold & Silver Price') ?> | आकाशवाणी</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, #b45309, #d97706); padding: var(--space-8) 0; color: #fff; }
        .gold-card { background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: var(--radius-xl); padding: var(--space-8); text-align: center; margin-bottom: var(--space-6); }
        .gold-label { font-size: 0.875rem; color: #92400e; margin-bottom: var(--space-2); }
        .gold-price { font-size: 3rem; font-weight: 800; color: #92400e; }
        .gold-unit { font-size: 1rem; color: #b45309; }
        .price-card { background: #fff; border-radius: var(--radius-xl); padding: var(--space-6); box-shadow: var(--shadow); margin-bottom: var(--space-4); }
        .price-row { display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) 0; border-bottom: 1px solid var(--dark-100); }
        .price-row:last-child { border-bottom: none; }
        .price-type { font-weight: 600; color: var(--dark-900); }
        .price-value { font-size: 1.25rem; font-weight: 700; color: #d97706; }
        .source-tag { font-size: 0.75rem; color: var(--dark-400); text-align: center; margin-top: var(--space-4); }
        .section { padding: var(--space-8) 0; }
        .loading-spinner { display: flex; justify-content: center; padding: var(--space-12); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--dark-200); border-top-color: #d97706; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .gold-price { font-size: 2rem; } }
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
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <?= $t('सुन र चाँदीको मूल्य', 'Gold & Silver Price') ?>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल बजारको ताजा सुन र चाँदीको भाव', 'Latest gold and silver rates in Nepal market') ?></p>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width: 600px;">
            <div id="gold-loading" class="loading-spinner"><div class="spinner"></div></div>
            <div id="gold-content" style="display:none">
                <div class="gold-card">
                    <p class="gold-label"><?= $t('आजको सुन (प्रति तोला)', 'Todays Gold (per tola)') ?></p>
                    <p class="gold-price" id="gold-rate">-</p>
                    <p class="gold-unit"><?= $t('नेरु', 'NPR') ?> / <?= $t('तोला', 'tola') ?></p>
                </div>
                <div class="price-card">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-4)"><?= $t('सुनको प्रकार', 'Types of Gold') ?></h3>
                    <div id="gold-types"></div>
                </div>
                <div class="price-card">
                    <h3 style="font-size:1rem;font-weight:700;color:var(--dark-900);margin-bottom:var(--space-4)"><?= $t('चाँदीको मूल्य', 'Silver Price') ?></h3>
                    <div id="silver-price"></div>
                </div>
                <p class="source-tag"><?= $t('स्रोत: नेपाल सुन चाँदी व्यापार संघ', 'Source: Nepal Gold and Silver Dealers Association') ?></p>
            </div>
            <div id="gold-error" style="display:none;text-align:center;padding:var(--space-8);color:var(--error)">
                <?= $t('मूल्य लोड हुन सकेन', 'Failed to load prices') ?>
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container"><div class="footer-bottom" style="border:none;padding:0"><p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p></div></div>
    </footer>
    <script>
    async function loadGold() {
        try {
            const resp = await fetch('/api/market-data.php?type=gold');
            const data = await resp.json();
            if (data.ok && (data.gold || data.price)) {
                const goldData = data.gold || data;
                document.getElementById('gold-rate').textContent = 'रु ' + (goldData.goldTejabi || goldData.goldHallmark || goldData.price || goldData.rate || '---');
                const types = document.getElementById('gold-types');
                if (goldData.goldHallmark) types.innerHTML += '<div class="price-row"><span class="price-type"><?= $t("हल्मर सुन (९९.५%)", "Hallmark Gold (99.5%)") ?></span><span class="price-value">रु ' + goldData.goldHallmark + '</span></div>';
                if (goldData.goldTejabi) types.innerHTML += '<div class="price-row"><span class="price-type"><?= $t("तेजाबी सुन (९१.६%)", "Tejabi Gold (91.6%)") ?></span><span class="price-value">रु ' + goldData.goldTejabi + '</span></div>';
                if (goldData.silver) document.getElementById('silver-price').innerHTML = '<div class="price-row"><span class="price-type"><?= $t("चाँदी (प्रति केजी)", "Silver (per kg)") ?></span><span class="price-value">रु ' + goldData.silver + '</span></div>';
                document.getElementById('gold-loading').style.display = 'none';
                document.getElementById('gold-content').style.display = 'block';
            } else { throw new Error(); }
        } catch(e) {
            document.getElementById('gold-loading').style.display = 'none';
            document.getElementById('gold-error').style.display = 'block';
        }
    }
    document.addEventListener('DOMContentLoaded', loadGold);
    </script>
    <script src="/assets/js/app.js"></script>
</body>
</html>