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
                    <nav class="header-nav">
                        <a href="/" class="nav-link"><?= $t('गृह', 'Home') ?></a>
                        <a href="/news.php" class="nav-link"><?= $t('समाचार', 'News') ?></a>
                        <a href="/nepali-patro.php" class="nav-link"><?= $t('पात्रो', 'Calendar') ?></a>
                        <a href="/ipo-tracker.php" class="nav-link"><?= $t('IPO', 'IPO') ?></a>
                    </nav>
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
