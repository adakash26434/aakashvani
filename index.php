<?php
/**
 * आकाशवाणी — Homepage 2026 (TechPana-inspired redesign)
 */
require_once __DIR__ . '/includes/autoload.php';

$lang = $_SESSION['lang'] ?? 'ne';
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;
$pageTitle = $t('आकाशवाणी — सूचनाको खुला आकाश', 'Aakashvani — Your Gateway to Information');

// Server-side news fetch (graceful fallback)
$homepageNews = [];
try {
    $dm = dataManager();
    $homepageNews = array_slice($dm->getNews('general', null, 6, 0), 0, 6);
} catch (Throwable $e) {
    error_log('index.php news fetch: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा।', 'Nepal\'s most trusted information platform.') ?>">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Aakashvani">
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="/assets/js/lucide.min.js"></script>
    <link rel="stylesheet" href="/assets/css/premium.css">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     TOP BAR — date, social links, language
     ═══════════════════════════════════════════════════════ -->
<div class="tp-topbar">
    <div class="tp-container">
        <div class="tp-topbar-inner">
            <div class="tp-topbar-left">
                <span class="tp-date"><?= date('l, j F Y') ?></span>
                <span class="tp-topbar-links">
                    <a href="?">नेपाली</a>
                    <a href="?lang=en">English</a>
                </span>
            </div>
            <div class="tp-topbar-right">
                <a href="https://www.facebook.com" target="_blank" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                <a href="https://twitter.com" target="_blank" aria-label="X/Twitter"><i data-lucide="twitter"></i></a>
                <a href="https://www.youtube.com" target="_blank" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                <a href="/login.php" class="tp-login-btn"><?= $t('लगइन', 'Login') ?></a>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MIDDLE HEADER — logo + tagline
     ═══════════════════════════════════════════════════════ -->
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
            <div class="tp-header-ads">
                <!-- Ad space available -->
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     STICKY NAV — category navigation
     ═══════════════════════════════════════════════════════ -->
<nav class="tp-nav" id="tpNav">
    <div class="tp-container">
        <div class="tp-nav-inner">
            <button class="tp-nav-toggle" id="navToggle" aria-label="Menu">
                <i data-lucide="menu"></i>
            </button>
            <div class="tp-nav-sticky-logo">
                <a href="/"><img src="/favicon.svg" alt="Aakashvani" width="32" height="32"></a>
            </div>
            <ul class="tp-nav-list" id="navList">
                <?php
                $navItems = [
                    ['path' => '/', 'label' => $t('गृह', 'Home')],
                    ['path' => '/news.php', 'label' => $t('समाचार', 'News')],
                    ['path' => '/nepali-patro.php', 'label' => $t('पात्रो', 'Calendar')],
                    ['path' => '/rashifal.php', 'label' => $t('राशिफल', 'Horoscope')],
                    ['path' => '/ipo-tracker.php', 'label' => $t('NEPSE/IPO', 'NEPSE/IPO')],
                    ['path' => '/tools.php', 'label' => $t('टूलहरू', 'Tools')],
                    ['path' => '/gov-services.php', 'label' => $t('सरकारी', 'Gov')],
                    ['path' => '/weather.php', 'label' => $t('मौसम', 'Weather')],
                    ['path' => '/cricket.php', 'label' => $t('क्रिकेट', 'Cricket')],
                    ['path' => '/tenders.php', 'label' => $t('टेन्डर', 'Tenders')],
                    ['path' => '/emergency.php', 'label' => $t('आपतकालीन', 'Emergency')],
                ];
                $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                foreach ($navItems as $item):
                    $isActive = ($currentPath === $item['path']) ? ' class="active"' : '';
                ?>
                <li><a href="<?= $item['path'] ?>"<?= $isActive ?>><?= $item['label'] ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="tp-nav-search">
                <button class="tp-search-btn" aria-label="Search" id="searchToggle">
                    <i data-lucide="search"></i>
                </button>
            </div>
        </div>
        <!-- Search bar -->
        <div class="tp-search-bar" id="searchBar" style="display:none">
            <input type="search" placeholder="<?= $t('समाचार खोज्नुहोस्...', 'Search news...') ?>" id="searchInput">
        </div>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════
     MARKET TICKER
     ═══════════════════════════════════════════════════════ -->
<div class="tp-market-bar">
    <div class="tp-container">
        <div class="tp-market-inner">
            <span class="tp-market-item" id="nepse-card">
                <i data-lucide="trending-up"></i>
                <span class="tp-mkt-label">NEPSE</span>
                <span class="tp-mkt-value" id="nepse-value">...</span>
                <span class="tp-mkt-change" id="nepse-change">...</span>
            </span>
            <span class="tp-market-divider">|</span>
            <span class="tp-market-item">
                <i data-lucide="gem"></i>
                <span class="tp-mkt-label"><?= $t('सुन (तोला)', 'Gold (Tola)') ?></span>
                <span class="tp-mkt-value" id="gold-value">...</span>
            </span>
            <span class="tp-market-divider">|</span>
            <span class="tp-market-item">
                <i data-lucide="dollar-sign"></i>
                <span class="tp-mkt-label">USD</span>
                <span class="tp-mkt-value" id="forex-value">...</span>
            </span>
            <span class="tp-market-divider">|</span>
            <span class="tp-market-item">
                <i data-lucide="fuel"></i>
                <span class="tp-mkt-label"><?= $t('पेट्रोल', 'Petrol') ?></span>
                <span class="tp-mkt-value" id="petrol-value">...</span>
            </span>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MAIN CONTENT — 2/3 content + 1/3 sidebar
     ═══════════════════════════════════════════════════════ -->
<main class="tp-main">
    <div class="tp-container">
        <div class="tp-content-layout">

            <!-- ═══ PRIMARY CONTENT ═══ -->
            <div class="tp-primary">

                <!-- Featured / Hero News -->
                <article class="tp-hero" id="featuredNews">
                    <a href="#" class="tp-hero-link" id="featuredLink">
                        <div class="tp-hero-img-wrap">
                            <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=900&h=480&fit=crop" alt="" id="featuredImg" class="tp-hero-img" loading="eager">
                            <span class="tp-hero-cat" id="featuredCat"><?= $t('समाचार', 'News') ?></span>
                        </div>
                        <div class="tp-hero-body">
                            <h1 class="tp-hero-title" id="featured-title">
                                <?= $t('नेपालको आर्थिक विकास: नयाँ अवसर र चुनौतीहरू', 'Nepal Economic Development: Opportunities & Challenges') ?>
                            </h1>
                            <p class="tp-hero-excerpt" id="featured-excerpt">
                                <?= $t('नेपालको आर्थिक विकासमा नयाँ अवसरहरू देखा परेका छन्। सरकारले लिएका नीतिहरू र विकासका लागि आवश्यक पूर्वाधारमा सुधार हुँदैछ।', 'New opportunities are emerging in Nepal\'s economic development. Government policies and infrastructure improvements continue to evolve.') ?>
                            </p>
                            <div class="tp-hero-meta">
                                <span id="featured-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span>•</span>
                                <span id="featured-time"><?= $t('भर्खरै', 'Just now') ?></span>
                            </div>
                        </div>
                    </a>
                </article>

                <!-- Category filter tabs -->
                <div class="tp-cat-tabs">
                    <button class="tp-cat-tab active" data-cat="all"><?= $t('सबै', 'All') ?></button>
                    <button class="tp-cat-tab" data-cat="politics"><?= $t('राजनीति', 'Politics') ?></button>
                    <button class="tp-cat-tab" data-cat="economy"><?= $t('अर्थ', 'Economy') ?></button>
                    <button class="tp-cat-tab" data-cat="technology"><?= $t('प्रविधि', 'Technology') ?></button>
                    <button class="tp-cat-tab" data-cat="sports"><?= $t('खेलकुद', 'Sports') ?></button>
                    <button class="tp-cat-tab" data-cat="world"><?= $t('विश्व', 'World') ?></button>
                </div>

                <!-- News grid -->
                <div class="tp-news-grid" id="newsGrid">
<?php if (!empty($homepageNews)): foreach (array_slice($homepageNews, 0, 6) as $i => $item): ?>
                    <a href="<?= htmlspecialchars($item['internalUrl'] ?? $item['link'] ?? '#') ?>" class="tp-news-card anim-fade-up delay-<?= ($i % 3) + 1 ?>">
                        <div class="tp-card-img-wrap">
                            <img src="<?= htmlspecialchars($item['image'] ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=240&fit=crop') ?>"
                                 alt="<?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 60, 'UTF-8')) ?>"
                                 class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= htmlspecialchars(ucfirst($item['cat'] ?? 'general')) ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 90, 'UTF-8')) ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= htmlspecialchars($item['sourceLabel'] ?? 'Aakashvani') ?></span>
                                <span class="tp-card-time"><?= htmlspecialchars($item['ago'] ?? '') ?></span>
                            </div>
                        </div>
                    </a>
<?php endforeach; else: ?>
                    <!-- Fallback cards -->
                    <a href="#" class="tp-news-card anim-fade-up delay-1">
                        <div class="tp-card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=240&fit=crop" alt="" class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= $t('अर्थ', 'Economy') ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= $t('NEPSE ले नयाँ रेकर्ड बनायो', 'NEPSE Sets New Record High') ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= $t('ShareSansar', 'ShareSansar') ?></span>
                                <span class="tp-card-time"><?= $t('30 मिनेट अघि', '30 min ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="tp-news-card anim-fade-up delay-2">
                        <div class="tp-card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&h=240&fit=crop" alt="" class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= $t('प्रविधि', 'Tech') ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= $t('नेपालमा 5G सेवा सुरु', '5G Service Launched in Nepal') ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= $t('TechPana', 'TechPana') ?></span>
                                <span class="tp-card-time"><?= $t('1 घण्टा अघि', '1 hour ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="tp-news-card anim-fade-up delay-3">
                        <div class="tp-card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1530533718754-001d2668365a?w=400&h=240&fit=crop" alt="" class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= $t('खेलकुद', 'Sports') ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= $t('नेपाल क्रिकेट टोलीको नयाँ जित', 'Nepal Cricket Team New Victory') ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= $t('GoalNepal', 'GoalNepal') ?></span>
                                <span class="tp-card-time"><?= $t('2 घण्टा अघि', '2 hours ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="tp-news-card anim-fade-up delay-1">
                        <div class="tp-card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=400&h=240&fit=crop" alt="" class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= $t('अर्थ', 'Economy') ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= $t('बैंकहरूको ब्याज दरमा परिवर्तन', 'Bank Interest Rates Changed') ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= $t('MeroLagani', 'MeroLagani') ?></span>
                                <span class="tp-card-time"><?= $t('3 घण्टा अघि', '3 hours ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="tp-news-card anim-fade-up delay-2">
                        <div class="tp-card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&h=240&fit=crop" alt="" class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= $t('विश्व', 'World') ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= $t('अमेरिकामा नयाँ नीति', 'New Policy in United States') ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= $t('BBC नेपाली', 'BBC Nepali') ?></span>
                                <span class="tp-card-time"><?= $t('4 घण्टा अघि', '4 hours ago') ?></span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="tp-news-card anim-fade-up delay-3">
                        <div class="tp-card-img-wrap">
                            <img src="https://images.unsplash.com/photo-1473116763249-2faaef81ccda?w=400&h=240&fit=crop" alt="" class="tp-card-img" loading="lazy">
                            <span class="tp-card-cat"><?= $t('प्रविधि', 'Tech') ?></span>
                        </div>
                        <div class="tp-card-body">
                            <h3 class="tp-card-title"><?= $t('गुगलको नयाँ AI टुल', 'Google New AI Tool') ?></h3>
                            <div class="tp-card-meta">
                                <span class="tp-card-source"><?= $t('TechSansar', 'TechSansar') ?></span>
                                <span class="tp-card-time"><?= $t('5 घण्टा अघि', '5 hours ago') ?></span>
                            </div>
                        </div>
                    </a>
<?php endif; ?>
                </div>

                <div class="tp-load-more">
                    <a href="/news.php" class="tp-btn-primary">
                        <?= $t('थप समाचार हेर्नुहोस्', 'View More News') ?>
                        <i data-lucide="arrow-right"></i>
                    </a>
                </div>

            </div><!-- /tp-primary -->

            <!-- ═══ SIDEBAR ═══ -->
            <aside class="tp-sidebar">

                <!-- Quick Tools -->
                <div class="tp-side-widget">
                    <h3 class="tp-widget-title"><?= $t('छिटो टूलहरू', 'Quick Tools') ?></h3>
                    <div class="tp-quick-grid">
                        <?php
                        $tools = [
                            ['icon' => 'calendar-days', 'label' => $t('पात्रो', 'Calendar'), 'href' => '/nepali-patro.php'],
                            ['icon' => 'sparkles', 'label' => $t('राशिफल', 'Horoscope'), 'href' => '/rashifal.php'],
                            ['icon' => 'trending-up', 'label' => $t('NEPSE', 'NEPSE'), 'href' => '/ipo-tracker.php'],
                            ['icon' => 'cloud-sun', 'label' => $t('मौसम', 'Weather'), 'href' => '/weather.php'],
                            ['icon' => 'building-2', 'label' => $t('सरकारी', 'Gov Services'), 'href' => '/gov-services.php'],
                            ['icon' => 'phone', 'label' => $t('आपतकालीन', 'Emergency'), 'href' => '/emergency.php'],
                        ];
                        foreach ($tools as $tool): ?>
                        <a href="<?= $tool['href'] ?>" class="tp-quick-item">
                            <i data-lucide="<?= $tool['icon'] ?>"></i>
                            <span><?= $tool['label'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Trending -->
                <div class="tp-side-widget">
                    <h3 class="tp-widget-title"><?= $t('ताजा लिंकहरू', 'Trending Now') ?></h3>
                    <ul class="tp-trending-list" id="trendingList">
                        <li><a href="/ipo-tracker.php"><i data-lucide="trending-up"></i><?= $t('IPO खुला छ', 'Open IPOs') ?></a></li>
                        <li><a href="/weather.php"><i data-lucide="cloud-sun"></i><?= $t('आजको मौसम', 'Today\'s Weather') ?></a></li>
                        <li><a href="/rashifal.php"><i data-lucide="sparkles"></i><?= $t('आजको राशिफल', 'Today\'s Horoscope') ?></a></li>
                        <li><a href="/nepali-patro.php"><i data-lucide="calendar-days"></i><?= $t('नेपाली पात्रो', 'Nepali Calendar') ?></a></li>
                        <li><a href="/tenders.php"><i data-lucide="file-text"></i><?= $t('नयाँ टेन्डर', 'New Tenders') ?></a></li>
                        <li><a href="/cricket.php"><i data-lucide="circle-dot"></i><?= $t('क्रिकेट स्कोर', 'Cricket Score') ?></a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="tp-side-widget tp-newsletter">
                    <h3 class="tp-widget-title"><?= $t('न्यूजलेटर', 'Newsletter') ?></h3>
                    <p class="tp-newsletter-desc"><?= $t('दैनिक समाचार इमेलमा पाउनुहोस्', 'Get daily news in your email') ?></p>
                    <form class="tp-newsletter-form" onsubmit="handleNewsletterSubmit(event, this)">
                        <input type="email" placeholder="<?= $t('इमेल', 'Email') ?>" required class="tp-input">
                        <button type="submit" class="tp-btn-submit"><?= $t('सब्सक्राइब', 'Subscribe') ?></button>
                    </form>
                </div>

            </aside><!-- /tp-sidebar -->

        </div>
    </div>
</main>

    <!-- MOBILE BOTTOM NAV -->
    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <div class="bottom-nav-inner">
            <a href="/" class="bottom-nav-item"><i data-lucide="home"></i><span>गृह</span></a>
            <a href="/news.php" class="bottom-nav-item"><i data-lucide="newspaper"></i><span>समाचार</span></a>
            <a href="/ipo-tracker.php" class="bottom-nav-item"><i data-lucide="trending-up"></i><span>NEPSE</span></a>
            <a href="/nepali-patro.php" class="bottom-nav-item"><i data-lucide="calendar-days"></i><span>पात्रो</span></a>
            <a href="/rashifal.php" class="bottom-nav-item"><i data-lucide="sparkles"></i><span>राशिफल</span></a>
        </div>
    </nav>

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
                    <p class="tp-footer-desc"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा एकैठाउँमा।', 'Nepal\'s most trusted information platform.') ?></p>
                    <div class="tp-footer-social">
                        <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                        <a href="#" aria-label="Instagram"><i data-lucide="instagram"></i></a>
                    </div>
                </div>
                <div>
                    <h4><?= $t('लिंकहरू', 'Links') ?></h4>
                    <ul>
                        <li><a href="/"><?= $t('गृहपृष्ठ', 'Home') ?></a></li>
                        <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                        <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                        <li><a href="/gov-services.php"><?= $t('सरकारी सेवा', 'Gov Services') ?></a></li>
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
                        <li><a href="/terms.php"><?= $t('सेवा सर्त', 'Terms') ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="tp-footer-bottom">
                <span>&copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?></span>
                <span><?= $t('हामी नेपालको सूचना खुला राख्छौं', 'We keep Nepal\'s information open') ?></span>
            </div>
        </div>
    </footer>

    <script>
    (function() {
        'use strict';

        async function loadMarket() {
            try {
                const resp = await fetch('/api/market-data.php?type=all');
                if (!resp.ok) return;
                const d = await resp.json();
                if (d.nepse) {
                    const n = d.nepse;
                    const v = document.getElementById('nepse-value');
                    const c = document.getElementById('nepse-change');
                    if (v && n.index) v.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits:2});
                    if (c && n.change !== undefined) {
                        const up = n.change >= 0;
                        c.textContent = (up ? '+' : '') + n.change.toFixed(2) + ' (' + (up ? '+' : '') + n.changePercent.toFixed(2) + '%)';
                        c.className = 'tp-mkt-change ' + (up ? 'up' : 'down');
                    }
                }
                if (d.gold && d.gold.hallmarkPerTola) {
                    const gv = document.getElementById('gold-value');
                    if (gv) gv.textContent = 'रु ' + Number(d.gold.hallmarkPerTola).toLocaleString('en-US');
                }
                if (d.forex && d.forex.rates && d.forex.rates.length > 0) {
                    const usd = d.forex.rates.find(r => r.code === 'USD');
                    if (usd) {
                        const fv = document.getElementById('forex-value');
                        if (fv) fv.textContent = 'रु ' + usd.sell.toFixed(2);
                    }
                }
                if (d.petrol && d.petrol.petrol) {
                    const pv = document.getElementById('petrol-value');
                    if (pv) pv.textContent = 'रु ' + d.petrol.petrol;
                }
            } catch(e) { console.warn('Market data unavailable'); }
        }

        async function loadNews() {
            try {
                const resp = await fetch('/api/news-unified.php?limit=7');
                if (!resp.ok) return;
                const data = await resp.json();
                const items = data.items || data.news || [];
                if (items[0]) {
                    const t = document.getElementById('featured-title');
                    const ti = document.getElementById('featured-time');
                    const ts = document.getElementById('featured-source');
                    const lnk = document.getElementById('featuredLink');
                    if (t && items[0].title) t.textContent = items[0].title;
                    if (ti && items[0].pubDate) ti.textContent = timeAgo(items[0].pubDate);
                    if (ts && items[0].sourceLabel) ts.textContent = items[0].sourceLabel;
                    if (lnk && items[0].link) lnk.href = items[0].link;
                }
                const grid = document.getElementById('newsGrid');
                if (grid && items.length > 1) {
                    grid.innerHTML = items.slice(1, 7).map((item, i) => `
                        <a href="${item.link || '#'}" class="tp-news-card anim-fade-up delay-${(i % 3) + 1}">
                            <div class="tp-card-img-wrap">
                                <img src="${item.image || 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&h=240&fit=crop'}"
                                     alt="${(item.title || '').substring(0, 60)}" class="tp-card-img" loading="lazy">
                                <span class="tp-card-cat">${item.cat || 'general'}</span>
                            </div>
                            <div class="tp-card-body">
                                <h3 class="tp-card-title">${item.title || ''}</h3>
                                <div class="tp-card-meta">
                                    <span class="tp-card-source">${item.sourceLabel || 'Aakashvani'}</span>
                                    <span class="tp-card-time">${timeAgo(item.pubDate || item.published_at)}</span>
                                </div>
                            </div>
                        </a>
                    `).join('');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            } catch(e) { console.warn('News load failed:', e.message); }
        }

        function timeAgo(d) {
            if (!d) return '<?= $t('भर्खरै', 'Just now') ?>';
            const s = Math.floor((Date.now() - new Date(d * 1000).getTime()) / 1000);
            if (s < 60) return s + 's <?= $t('अघि', 'ago') ?>';
            if (s < 3600) return Math.floor(s/60) + 'm <?= $t('अघि', 'ago') ?>';
            if (s < 86400) return Math.floor(s/3600) + 'h <?= $t('अघि', 'ago') ?>';
            return Math.floor(s/86400) + 'd <?= $t('अघि', 'ago') ?>';
        }

        function initSearch() {
            const toggle = document.getElementById('searchToggle');
            const bar = document.getElementById('searchBar');
            if (toggle && bar) {
                toggle.addEventListener('click', () => {
                    bar.style.display = bar.style.display === 'none' ? 'block' : 'none';
                    if (bar.style.display === 'block') bar.querySelector('input').focus();
                });
            }
            const navToggle = document.getElementById('navToggle');
            const navList = document.getElementById('navList');
            if (navToggle && navList) {
                navToggle.addEventListener('click', () => navList.classList.toggle('open'));
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadMarket();
            loadNews();
            initSearch();
            setInterval(loadMarket, 5 * 60 * 1000);
            setInterval(loadNews, 10 * 60 * 1000);
        });
    })();

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('SW registered'))
                .catch(err => console.log('SW registration failed'));
        });
    }
    function handleNewsletterSubmit(e, form) {
        e.preventDefault();
        form.innerHTML = '<p style="color:var(--primary);text-align:center;padding:var(--sp-4)">&#10003; ' + 
            (window.__t ? window.__t('Subscribed!') : 'Subscribed!') + '</p>';
    }
    </script>
</body>
</html>

