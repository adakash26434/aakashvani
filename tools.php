<?php
/**
 * आकाशवाणी — Tools v2
 */
require_once __DIR__ . '/config.php';
$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$tools = [
    ['name' => 'कर क्यालकुलेटर', 'en' => 'Tax Calculator', 'icon' => 'calculator', 'desc' => 'आयकर गणना'],
    ['name' => 'इकाई रूपान्तरक', 'en' => 'Unit Converter', 'icon' => 'arrow-right-left', 'desc' => 'इकाई रूपान्तरण'],
    ['name' => 'मुद्रा रूपान्तरक', 'en' => 'Currency Converter', 'icon' => 'dollar-sign', 'desc' => 'मुद्रा रूपान्तरण'],
    ['name' => 'BMI क्यालकुलेटर', 'en' => 'BMI Calculator', 'icon' => 'activity', 'desc' => 'BMI गणना'],
    ['name' => 'भाग्य क्यालकुलेटर', 'en' => 'Lagna Calculator', 'icon' => 'star', 'desc' => 'भाग्य गणना'],
    ['name' => 'PDF टूल', 'en' => 'PDF Tools', 'icon' => 'file-text', 'desc' => 'PDF रूपान्तरण'],
];
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('टूलहरू', 'Tools') ?> | आकाशवाणी</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header { background: linear-gradient(135deg, var(--dark-900), var(--dark-800)); padding: var(--space-12) 0; color: #fff; }
        .tools-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--space-6); }
        .tool-card { background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--dark-100); padding: var(--space-6); text-align: center; transition: all var(--transition); text-decoration: none; display: block; }
        .tool-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--primary); }
        .tool-icon { width: 64px; height: 64px; background: var(--primary-50); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); color: var(--primary); }
        .tool-name { font-size: 1.125rem; font-weight: 700; color: var(--dark-900); margin-bottom: var(--space-2); }
        .tool-desc { font-size: 0.875rem; color: var(--dark-500); }
        .section { padding: var(--space-12) 0; }
        .section:nth-child(even) { background: var(--dark-50); }
    </style>

    <style>
        /* Responsive */
        @media (max-width: 768px) {
            .page-header { padding: var(--space-8) 0; }
            .page-header h1 { font-size: 1.75rem; }
        }
        
        @media (max-width: 480px) {
            .page-header h1 { font-size: 1.5rem; }
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
        <!-- Navigation -->
        <nav class="main-nav">
            <div class="container">
                <div class="nav-list">
                    <a href="/" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                        <?= $t('गृह', 'Home') ?>
                    </a>
                    <a href="/news.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                        <?= $t('समाचार', 'News') ?>
                    </a>
                    <a href="/nepali-patro.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?= $t('पात्रो', 'Calendar') ?>
                    </a>
                    <a href="/rashifal.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                        <?= $t('राशिफल', 'Horoscope') ?>
                    </a>
                    <a href="/ipo-tracker.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        <?= $t('NEPSE/IPO', 'NEPSE/IPO') ?>
                    </a>
                    <a href="/weather.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/></svg>
                        <?= $t('मौसम', 'Weather') ?>
                    </a>
                    <a href="/cricket.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                        <?= $t('क्रिकेट', 'Cricket') ?>
                    </a>
                    <a href="/tenders.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                        <?= $t('टेन्डर', 'Tenders') ?>
                    </a>
                    <a href="/emergency.php" class="nav-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?= $t('आपतकालीन', 'Emergency') ?>
                    </a>
                </div>
            </div>
        </nav>
    </header>
                </div>
            </div>
        </div>
    </header>
    
    <section class="page-header">
        <div class="container">
            <h1 class="page-title" style="display:flex;align-items:center;gap:12px">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary)"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                <?= $t('उपयोगी टूलहरू', 'Useful Tools') ?>
            </h1>
            <p class="page-subtitle"><?= $t('गणना र रूपान्तरणका लागि उपयोगी टूलहरू', 'Useful tools for calculations and conversions') ?></p>
        </div>
    </section>
    
    <section class="section">
        <div class="container">
            <div class="tools-grid">
                <?php foreach ($tools as $tool): ?>
                <a href="/tool.php?id=<?= strtolower(str_replace(' ', '-', $tool['en'])) ?>" class="tool-card">
                    <div class="tool-icon">
                        <svg class="icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    </div>
                    <h3 class="tool-name"><?= $isNepali ? $tool['name'] : $tool['en'] ?></h3>
                    <p class="tool-desc"><?= $tool['desc'] ?></p>
                </a>
                <?php endforeach; ?>
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
    <script src="/assets/js/app.js"></script>
</body>
</html>
