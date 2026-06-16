<?php
/**
 * आकाशवाणी — Emergency v2
 * Premium 2026 Design
 */
require_once __DIR__ . '/config.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$emergency = [
    ['name' => 'प्रहरी', 'number' => '100', 'icon' => 'shield', 'color' => '#3b82f6'],
    ['name' => 'एम्बुलेन्स', 'number' => '102', 'icon' => 'heart-pulse', 'color' => '#ef4444'],
    ['name' => 'दमकल', 'number' => '101', 'icon' => 'flame', 'color' => '#f59e0b'],
    ['name' => 'गृह मन्त्रालय', 'number' => '01-4200100', 'icon' => 'landmark', 'color' => '#10b981'],
];
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('आपतकालीन नम्बर', 'Emergency Numbers') ?> | आकाशवाणी</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .emergency-header { background: linear-gradient(135deg, #dc2626, #991b1b); padding: var(--space-16) 0; color: #fff; text-align: center; }
        .emergency-icon { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); }
        .emergency-section { padding: var(--space-12) 0; }
        .quick-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-4); }
        .quick-card { display: flex; align-items: center; gap: var(--space-4); padding: var(--space-6); background: #fff; border-radius: var(--radius-xl); box-shadow: var(--shadow); text-decoration: none; transition: all var(--transition); }
        .quick-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .quick-icon { width: 56px; height: 56px; border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
        .quick-info { flex: 1; }
        .quick-name { font-size: 0.875rem; color: var(--dark-500); margin-bottom: var(--space-1); }
        .quick-number { font-size: 1.5rem; font-weight: 800; color: var(--dark-900); }
        .section-title { font-size: 1.25rem; font-weight: 700; margin-bottom: var(--space-6); }
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
                    </nav>
                    <div class="header-actions">
                        <a href="/" class="btn btn-ghost btn-icon">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <section class="emergency-header">
        <div class="container">
            <div class="emergency-icon">
                <svg class="icon-xl" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px;height:40px"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <h1 class="text-3xl font-bold mb-2"><?= $t('आपतकालीन नम्बरहरू', 'Emergency Numbers') ?></h1>
            <p class="text-lg opacity-80"><?= $t('नेपालका महत्वपूर्ण आपतकालीन सम्पर्क नम्बरहरू', 'Important emergency contact numbers of Nepal') ?></p>
        </div>
    </section>
    
    <section class="emergency-section">
        <div class="container">
            <h2 class="section-title"><?= $t('छिटो डायल', 'Quick Dial') ?></h2>
            <div class="quick-grid">
                <?php foreach ($emergency as $item): ?>
                <a href="tel:<?= $item['number'] ?>" class="quick-card">
                    <div class="quick-icon" style="background:<?= $item['color'] ?>">
                        <svg class="icon-lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div class="quick-info">
                        <span class="quick-name"><?= $item['name'] ?></span>
                        <span class="quick-number"><?= $item['number'] ?></span>
                    </div>
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
