<?php
/**
 * आकाशवाणी — Premium Homepage 2026
 * Clean Architecture | Real API Data | Premium UI
 */

require_once __DIR__ . '/includes/autoload.php';

// Language helper
$lang = $_SESSION['lang'] ?? 'ne';
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Page config
$pageTitle = $t('आकाशवाणी — सूचनाको खुला आकाश', 'Aakashvani — Your Gateway to Information');

// Fetch homepage news server-side via dataManager (not HTTP)
$homepageNews = [];
try {
    $dm = dataManager();
    $homepageNews = array_slice($dm->getNews('general', null, 4, 0), 0, 4);
} catch (Throwable $e) {
    error_log('index.php homepageNews fetch failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    
    <!-- SEO -->
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा।', 'Nepal\'s most trusted information platform.') ?>">
    
    <!-- PWA -->
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aakashvani">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <!-- Premium CSS -->
    <link rel="stylesheet" href="/assets/css/premium.css">
</head>
<body>
    <!-- TOP BAR -->
    <div class="topbar">
        <div class="container">
            <div class="topbar-inner">
                <div class="topbar-left">
                    <span class="topbar-date"><?= date('l, j F Y') ?></span>
                    <span class="topbar-divider">|</span>
                    <span class="topbar-greeting"><?= $t('शुभ प्रभात', 'Good Morning') ?></span>
                </div>
                <div class="topbar-right">
                    <a href="?lang=en" class="lang-btn">EN / ने</a>
                    <a href="/login.php" class="login-btn"><?= $t('लगइन', 'Login') ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="brand">
                    <div class="brand-logo"><span>आ</span></div>
                    <div class="brand-text">
                        <h1>आकाशवाणी</h1>
                        <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                    </div>
                </a>
                <div class="header-search">
                    <div class="search-wrapper">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="search" class="search-input" placeholder="<?= $t('समाचार, जानकारी खोज्नुहोस्...', 'Search news, info...') ?>" id="headerSearch">
                        <kbd class="search-kbd">⌘K</kbd>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="action-btn mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- NAV -->
        <nav class="main-nav">
            <div class="container">
                <ul class="nav-list">
                    <?php
                    $navItems = [
                        ['path' => '/', 'label' => 'गृह', 'label_en' => 'Home'],
                        ['path' => '/news.php', 'label' => 'समाचार', 'label_en' => 'News'],
                        ['path' => '/nepali-patro.php', 'label' => 'पात्रो', 'label_en' => 'Calendar'],
                        ['path' => '/rashifal.php', 'label' => 'राशिफल', 'label_en' => 'Horoscope'],
                        ['path' => '/ipo-tracker.php', 'label' => 'NEPSE/IPO', 'label_en' => 'NEPSE/IPO'],
                        ['path' => '/tools.php', 'label' => 'टूलहरू', 'label_en' => 'Tools'],
                        ['path' => '/gov-services.php', 'label' => 'सरकारी', 'label_en' => 'Gov'],
                        ['path' => '/weather.php', 'label' => 'मौसम', 'label_en' => 'Weather'],
                        ['path' => '/cricket.php', 'label' => 'क्रिकेट', 'label_en' => 'Cricket'],
                        ['path' => '/tenders.php', 'label' => 'टेन्डर', 'label_en' => 'Tenders'],
                        ['path' => '/emergency.php', 'label' => 'आपतकालीन', 'label_en' => 'Emergency'],
                    ];
                    $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                    foreach ($navItems as $item):
                        $isActive = ($currentPath === $item['path']) ? 'active' : '';
                    ?>
                    <li>
                        <a href="<?= $item['path'] ?>" class="nav-link <?= $isActive ?>">
                            <?= $t($item['label'], $item['label_en']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- LIVE BANNER -->
    <div class="live-banner">
        <div class="container">
            <div class="banner-inner">
                <span class="live-pill">
                    <span class="live-pill-dot"></span>
                    LIVE
                </span>
                <span class="banner-text">
                    <?= $t('स्वागत छ! आकाशवाणी - नेपालको छिटो सूचना प्लेटफर्म', 'Welcome to Aakashvani - Nepal\'s fastest information platform') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- MARKET STRIP -->
    <section class="market-strip" id="marketSection">
        <div class="container">
            <div class="market-grid">
                <div class="market-card" data-type="nepse">
                    <div class="market-icon" data-lucide="trending-up"></div>
                    <div class="market-label">NEPSE</div>
                    <div class="market-value" id="nepse-value">...</div>
                    <div class="market-change" id="nepse-change">...</div>
                </div>
                <div class="market-card" data-type="gold">
                    <div class="market-icon" data-lucide="gem"></div>
                    <div class="market-label"><?= $t('सुन (10g)', 'Gold (10g)') ?></div>
                    <div class="market-value" id="gold-value">...</div>
                    <div class="market-meta" id="gold-meta">...</div>
                </div>
                <div class="market-card" data-type="forex">
                    <div class="market-icon" data-lucide="dollar-sign"></div>
                    <div class="market-label">USD</div>
                    <div class="market-value" id="forex-value">...</div>
                    <div class="market-meta" id="forex-meta">...</div>
                </div>
                <div class="market-card" data-type="petrol">
                    <div class="market-icon" data-lucide="fuel"></div>
                    <div class="market-label"><?= $t('पेट्रोल', 'Petrol') ?></div>
                    <div class="market-value" id="petrol-value">...</div>
                    <div class="market-meta" id="petrol-meta">...</div>
                </div>
                <div class="market-card" data-type="electricity">
                    <div class="market-icon" data-lucide="zap"></div>
                    <div class="market-label"><?= $t('बिजुली', 'Electricity') ?></div>
                    <div class="market-value" id="electricity-value">...</div>
                    <div class="market-meta" id="electricity-meta">...</div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <main class="main-content page-enter">
        <div class="container">

            <!-- Featured News -->
            <a href="/news-post.php" class="featured-card" id="featuredNews">
                <div class="featured-img-wrap">
                    <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1200&h=600&fit=crop" alt="Featured" class="featured-img" loading="eager">
                    <div class="featured-overlay"></div>
                </div>
                <div class="featured-content">
                    <span class="featured-badge">
                        <span class="featured-badge-dot"></span>
                        <?= $t('समाचार', 'News') ?>
                    </span>
                    <h2 class="featured-title" id="featured-title">
                        <?= $t('नेपालको आर्थिक विकास: नयाँ अवसर र चुनौतीहरू', 'Nepal Economic Development: New Opportunities and Challenges') ?>
                    </h2>
                    <div class="featured-meta">
                        <span><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                        <span class="featured-meta-dot">•</span>
                        <span id="featured-time"><?= $t('2 घण्टा अघि', '2 hours ago') ?></span>
                    </div>
                    <div class="featured-cta">
                        <span><?= $t('थप पढ्नुहोस्', 'Read More') ?></span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </div>
                </div>
            </a>

            <!-- News Section -->
            <div class="news-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="section-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                        </span>
                        <?= $t('ताजा समाचार', 'Latest News') ?>
                    </h2>
                    <a href="/news.php" class="section-link">
                        <?= $t('सबै हेर्नुहोस्', 'View All') ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="news-grid" id="newsGrid">
<?php if (!empty($homepageNews)): ?>
    <?php foreach ($homepageNews as $item): ?>
                    <a href="<?= htmlspecialchars($item['internalUrl'] ?? $item['link'] ?? '#') ?>" class="news-card anim-fade-up">
                        <div class="card-img-wrap">
                            <img src="<?= htmlspecialchars($item['image'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&h=250&fit=crop') ?>" alt="<?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 60, 'UTF-8')) ?>" class="card-img" loading="lazy">
                            <span class="card-cat-badge"><?= htmlspecialchars(ucfirst($item['cat'] ?? 'general')) ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 100, 'UTF-8')) ?></h3>
                            <div class="card-meta">
                                <span class="card-source"><?= htmlspecialchars($item['sourceLabel'] ?? 'Aakashvani') ?></span>
                                <span class="card-time"><?= htmlspecialchars($item['ago'] ?? '') ?></span>
                            </div>
                        </div>
                    </a>
    <?php endforeach; ?>
<?php else: ?>
                    <a href="/news-post.php" class="news-card anim-fade-up delay-1">
                        <div class="card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=250&fit=crop" alt="News" class="card-img" loading="lazy">
                            <span class="card-cat-badge"><?= $t('अर्थ', 'Economy') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $t('NEPSE ले नयाँ रेकर्ड बनायो', 'NEPSE Sets New Record High') ?></h3>
                            <div class="card-meta">
                                <span class="card-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span class="card-time"><?= $t('30 मिनेट अघि', '30 min ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="/news-post.php" class="news-card anim-fade-up delay-2">
                        <div class="card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=400&h=250&fit=crop" alt="News" class="card-img" loading="lazy">
                            <span class="card-cat-badge"><?= $t('प्रविधि', 'Technology') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $t('सुनको मूल्यमा वृद्धि', 'Gold Prices Surge') ?></h3>
                            <div class="card-meta">
                                <span class="card-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span class="card-time"><?= $t('1 घण्टा अघि', '1 hour ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="/news-post.php" class="news-card anim-fade-up delay-3">
                        <div class="card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=400&h=250&fit=crop" alt="News" class="card-img" loading="lazy">
                            <span class="card-cat-badge"><?= $t('खेलकुद', 'Sports') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $t('IPO आवेदन खुला', 'IPO Applications Open') ?></h3>
                            <div class="card-meta">
                                <span class="card-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span class="card-time"><?= $t('2 घण्टा अघि', '2 hours ago') ?></span>
                            </div>
                        </div>
                    </a>
<?php endif; ?>
                </div>
            </div>

            <!-- Quick Tools -->
            <div class="tools-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="section-title-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        </span>
                        <?= $t('छिटो टूलहरू', 'Quick Tools') ?>
                    </h2>
                </div>
                <div class="tools-grid">
                    <?php
                    $quickTools = [
                        ['icon' => 'calendar', 'label' => $t('पात्रो', 'Calendar'), 'href' => '/nepali-patro.php'],
                        ['icon' => 'star', 'label' => $t('राशिफल', 'Horoscope'), 'href' => '/rashifal.php'],
                        ['icon' => 'trending-up', 'label' => $t('NEPSE', 'NEPSE'), 'href' => '/ipo-tracker.php'],
                        ['icon' => 'building', 'label' => $t('अस्पताल', 'Hospitals'), 'href' => '/gov-services.php'],
                        ['icon' => 'cloud-sun', 'label' => $t('मौसम', 'Weather'), 'href' => '/weather.php'],
                        ['icon' => 'megaphone', 'label' => $t('एसएमएस', 'SMS Alert'), 'href' => '/alerts.php'],
                    ];
                    foreach ($quickTools as $tool): ?>
                    <a href="<?= $tool['href'] ?>" class="tool-card">
                        <div class="tool-card-icon">
                            <i data-lucide="<?= $tool['icon'] ?>"></i>
                        </div>
                        <span class="tool-label"><?= $tool['label'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </main>

    <!-- MOBILE BOTTOM NAV -->
    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <div class="bottom-nav-inner">
            <a href="/" class="bottom-nav-item <?= ($currentPath === '/') ? 'active' : '' ?>">
                <i data-lucide="home"></i>
                <span>गृह</span>
            </a>
            <a href="/news.php" class="bottom-nav-item <?= (strpos($currentPath, 'news') !== false) ? 'active' : '' ?>">
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

    <script>
    // Initialize Lucide icons after DOM
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
    </script>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div>
                    <div class="footer-brand">
                        <div class="footer-logo">आ</div>
                        <div>
                            <div class="footer-name">आकाशवाणी</div>
                            <div class="footer-tagline"><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></div>
                        </div>
                    </div>
                    <p class="footer-desc">
                        <?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, बजार डाटा, सरकारी सेवा, र थप जानकारी एकैठाउँमा।', 'Nepal\'s most trusted information platform. News, market data, government services, and more in one place.') ?>
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-btn" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                        <a href="#" class="social-btn" aria-label="Twitter/X"><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117Z"/></svg></a>
                        <a href="#" class="social-btn" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                    </div>
                </div>
                <!-- Links -->
                <div>
                    <div class="footer-col-title"><?= $t('लिंकहरू', 'Links') ?></div>
                    <ul class="footer-links">
                        <li><a href="/"><?= $t('गृहपृष्ठ', 'Home') ?></a></li>
                        <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                        <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                        <li><a href="/gov-services.php"><?= $t('सरकारी सेवा', 'Gov Services') ?></a></li>
                    </ul>
                </div>
                <!-- Resources -->
                <div>
                    <div class="footer-col-title"><?= $t('स्रोतहरू', 'Resources') ?></div>
                    <ul class="footer-links">
                        <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                        <li><a href="/nepali-patro.php"><?= $t('नेपाली पात्रो', 'Calendar') ?></a></li>
                        <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                        <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                    </ul>
                </div>
                <!-- Company -->
                <div>
                    <div class="footer-col-title"><?= $t('कम्पनी', 'Company') ?></div>
                    <ul class="footer-links">
                        <li><a href="/about.php"><?= $t('हाम्रो बारेमा', 'About Us') ?></a></li>
                        <li><a href="/contact.php"><?= $t('सम्पर्क', 'Contact') ?></a></li>
                        <li><a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a></li>
                        <li><a href="/terms.php"><?= $t('सेवा सर्त', 'Terms') ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span class="footer-copy">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?></span>
                <div class="footer-legal">
                    <a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a>
                    <a href="/terms.php"><?= $t('सर्तहरू', 'Terms') ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- MARKET + NEWS LOADER -->
    <script>
    (function() {
        'use strict';

        async function loadMarket() {
            try {
                const resp = await fetch('/api/market-data.php?type=all');
                if (!resp.ok) return;
                const d = await resp.json();

                // NEPSE
                if (d.nepse) {
                    const n = d.nepse;
                    const v = document.getElementById('nepse-value');
                    const c = document.getElementById('nepse-change');
                    if (v && n.index) v.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits:2});
                    if (c && n.change !== undefined) {
                        const up = n.change >= 0;
                        c.textContent = (up ? '+' : '') + n.change.toFixed(2) + ' (' + (up ? '+' : '') + n.changePercent.toFixed(2) + '%)';
                        c.className = 'market-change ' + (up ? 'up' : 'down');
                    }
                }

                // Gold
                if (d.gold && d.gold.hallmarkPerTola) {
                    const gv = document.getElementById('gold-value');
                    const gm = document.getElementById('gold-meta');
                    if (gv) gv.textContent = 'रु ' + Number(d.gold.hallmarkPerTola).toLocaleString('en-US');
                    if (gm && d.gold.source) gm.textContent = d.gold.source;
                }

                // Forex (forex.rates is the array, not forex itself)
                if (d.forex && d.forex.rates && d.forex.rates.length > 0) {
                    const usd = d.forex.rates.find(r => r.code === 'USD');
                    if (usd) {
                        const fv = document.getElementById('forex-value');
                        const fm = document.getElementById('forex-meta');
                        if (fv) fv.textContent = 'रु ' + usd.sell.toFixed(2);
                        if (fm) fm.textContent = 'Buy: रु ' + usd.buy.toFixed(2);
                    }
                }

                // Petrol
                if (d.petrol && d.petrol.petrol) {
                    const pv = document.getElementById('petrol-value');
                    if (pv) pv.textContent = 'रु ' + d.petrol.petrol;
                }

                // Animate cards in
                document.querySelectorAll('.market-card').forEach((c, i) => {
                    setTimeout(() => c.classList.add('loaded'), i * 100);
                });
            } catch(e) { console.warn('Market data unavailable'); }
        }

        async function loadNews() {
            try {
                const resp = await fetch('/api/news-unified.php?limit=5');
                if (!resp.ok) return;
                const data = await resp.json();
                const items = data.items || data.news || [];

                // Update featured
                if (items[0]) {
                    const t = document.getElementById('featured-title');
                    const ti = document.getElementById('featured-time');
                    if (t && items[0].title) t.textContent = items[0].title;
                    if (ti && items[0].published_at) ti.textContent = timeAgo(items[0].published_at);
                }

                // Update news grid
                const grid = document.getElementById('newsGrid');
                if (grid && items.length > 1) {
                    grid.innerHTML = items.slice(1, 4).map(item => `
                        <a href="${item.source_url || '#'}" class="news-card anim-fade-up">
                            <div class="card-img-wrap">
                                <img src="${item.image_url || 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&h=250&fit=crop'}"
                                     alt="${(item.title || '').substring(0, 60)}"
                                     class="card-img" loading="lazy">
                                <span class="card-cat-badge">${item.category || 'general'}</span>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title">${item.title || ''}</h3>
                                <div class="card-meta">
                                    <span class="card-source">${item.source || 'Aakashvani'}</span>
                                    <span class="card-time">${timeAgo(item.published_at)}</span>
                                </div>
                            </div>
                        </a>
                    `).join('');
                }
            } catch(e) { console.warn('News load failed:', e.message); }
        }

        function timeAgo(d) {
            if (!d) return '';
            const s = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
            if (s < 60) return s + 's ago';
            if (s < 3600) return Math.floor(s/60) + 'm ago';
            if (s < 86400) return Math.floor(s/3600) + 'h ago';
            return Math.floor(s/86400) + 'd ago';
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadMarket();
            loadNews();
            // Refresh every 5 minutes
            setInterval(loadMarket, 5 * 60 * 1000);
            setInterval(loadNews, 10 * 60 * 1000);
        });
    })();
    </script>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('SW registered'))
                .catch(err => console.log('SW registration failed'));
        });
    }
    </script>
</body>
</html>
