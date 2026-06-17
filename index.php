<?php
/**
 * आकाशवाणी — Premium Homepage 2026
 * Clean Architecture | Real API Data | Premium UI
 */

require_once __DIR__ . '/config.php';

// Language helper
$lang = $_SESSION['lang'] ?? 'ne';
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Page config
$pageTitle = $t('आकाशवाणी — सूचनाको खुला आकाश', 'Aakashvani — Your Gateway to Information');
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
    
    <!-- Premium CSS -->
    <link rel="stylesheet" href="/assets/css/premium.css">
</head>
<body>
    <!-- ═══════════════════════════════════════════════════════════════
         PREMIUM TOP BAR
         ═══════════════════════════════════════════════════════════════ -->
    <div class="premium-topbar">
        <div class="container">
            <div class="topbar-content">
                <div class="topbar-left">
                    <span class="topbar-badge">✨</span>
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

    <!-- ═══════════════════════════════════════════════════════════════
         PREMIUM HEADER
         ═══════════════════════════════════════════════════════════════ -->
    <header class="premium-header">
        <div class="container">
            <div class="header-grid">
                <!-- Brand -->
                <a href="/" class="brand">
                    <div class="brand-logo">
                        <span>आ</span>
                    </div>
                    <div class="brand-text">
                        <h1>आकाशवाणी</h1>
                        <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                    </div>
                </a>
                
                <!-- Search -->
                <div class="header-search">
                    <div class="search-wrapper">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                        <input type="search" class="search-input" placeholder="<?= $t('समाचार, जानकारी खोज्नुहोस्...', 'Search news, info...') ?>" id="headerSearch">
                        <kbd class="search-kbd">⌘K</kbd>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="header-actions">
                    <button class="action-btn" id="themeToggle" title="<?= $t('Dark Mode', 'Dark Mode') ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <button class="action-btn mobile-menu-toggle" id="mobileMenuToggle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 12h16M4 6h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="premium-nav">
            <div class="container">
                <ul class="nav-list">
                    <?php
                    $navItems = [
                        ['path' => '/', 'label' => 'गृह', 'label_en' => 'Home', 'icon' => 'home'],
                        ['path' => '/news.php', 'label' => 'समाचार', 'label_en' => 'News', 'icon' => 'newspaper'],
                        ['path' => '/nepali-patro.php', 'label' => 'पात्रो', 'label_en' => 'Calendar', 'icon' => 'calendar'],
                        ['path' => '/rashifal.php', 'label' => 'राशिफल', 'label_en' => 'Horoscope', 'icon' => 'star'],
                        ['path' => '/ipo-tracker.php', 'label' => 'NEPSE/IPO', 'label_en' => 'NEPSE/IPO', 'icon' => 'chart'],
                        ['path' => '/tools.php', 'label' => 'टूलहरू', 'label_en' => 'Tools', 'icon' => 'tool'],
                        ['path' => '/gov-services.php', 'label' => 'सरकारी', 'label_en' => 'Gov', 'icon' => 'building'],
                        ['path' => '/weather.php', 'label' => 'मौसम', 'label_en' => 'Weather', 'icon' => 'cloud'],
                        ['path' => '/cricket.php', 'label' => 'क्रिकेट', 'label_en' => 'Cricket', 'icon' => 'circle'],
                        ['path' => '/tenders.php', 'label' => 'टेन्डर', 'label_en' => 'Tenders', 'icon' => 'file'],
                        ['path' => '/emergency.php', 'label' => 'आपतकालीन', 'label_en' => 'Emergency', 'icon' => 'phone'],
                    ];
                    
                    $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                    foreach ($navItems as $item):
                        $isActive = ($currentPath === $item['path']) ? 'active' : '';
                        $label = $t($item['label'], $item['label_en']);
                    ?>
                    <li>
                        <a href="<?= $item['path'] ?>" class="nav-link <?= $isActive ?>">
                            <?= $item['label'] ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- ═══════════════════════════════════════════════════════════════
         LIVE BANNER
         ═══════════════════════════════════════════════════════════════ -->
    <div class="live-banner">
        <div class="container">
            <div class="banner-content">
                <span class="live-badge">
                    <span class="live-dot"></span>
                    LIVE
                </span>
                <span class="banner-text">
                    <?= $t('स्वागत छ! आकाशवाणी - नेपालको छिटो सूचना प्लेटफर्म', 'Welcome to Aakashbani - Nepal\'s fastest information platform') ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         PREMIUM MARKET SECTION
         ═══════════════════════════════════════════════════════════════ -->
    <section class="premium-market" id="marketSection">
        <div class="container">
            <div class="market-grid">
                <!-- NEPSE -->
                <div class="market-card" data-type="nepse">
                    <div class="card-icon">📈</div>
                    <div class="card-label">NEPSE</div>
                    <div class="card-value" id="nepse-value">...</div>
                    <div class="card-change" id="nepse-change">...</div>
                </div>
                
                <!-- Gold -->
                <div class="market-card" data-type="gold">
                    <div class="card-icon">🥇</div>
                    <div class="card-label"><?= $t('सुन (10g)', 'Gold (10g)') ?></div>
                    <div class="card-value" id="gold-value">...</div>
                    <div class="card-meta" id="gold-meta">...</div>
                </div>
                
                <!-- USD -->
                <div class="market-card" data-type="forex">
                    <div class="card-icon">💵</div>
                    <div class="card-label">USD</div>
                    <div class="card-value" id="forex-value">...</div>
                    <div class="card-meta" id="forex-meta">...</div>
                </div>
                
                <!-- Petrol -->
                <div class="market-card" data-type="petrol">
                    <div class="card-icon">⛽</div>
                    <div class="card-label"><?= $t('पेट्रोल', 'Petrol') ?></div>
                    <div class="card-value" id="petrol-value">...</div>
                    <div class="card-meta" id="petrol-meta">...</div>
                </div>
                
                <!-- Electricity -->
                <div class="market-card" data-type="electricity">
                    <div class="card-icon">⚡</div>
                    <div class="card-label"><?= $t('बिजुली', 'Electricity') ?></div>
                    <div class="card-value" id="electricity-value">...</div>
                    <div class="card-meta" id="electricity-meta">...</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         MAIN CONTENT
         ═══════════════════════════════════════════════════════════════ -->
    <main class="main-content">
        <div class="container">
            <!-- Featured News -->
            <a href="/news-post.php" class="featured-card" id="featuredNews">
                <div class="featured-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1200&h=600&fit=crop" alt="Featured" class="featured-image" loading="eager">
                    <div class="featured-overlay"></div>
                </div>
                <div class="featured-content">
                    <span class="featured-badge">
                        <span class="badge-dot"></span>
                        <?= $t('समाचार', 'News') ?>
                    </span>
                    <h2 class="featured-title" id="featured-title">
                        <?= $t('नेपालको आर्थिक विकास: नयाँ अवसर र चुनौतीहरू', 'Nepal Economic Development: New Opportunities and Challenges') ?>
                    </h2>
                    <div class="featured-meta">
                        <span class="meta-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                        <span class="meta-divider">•</span>
                        <span class="meta-time" id="featured-time"><?= $t('2 घण्टा अघि', '2 hours ago') ?></span>
                    </div>
                    <div class="featured-cta">
                        <span class="cta-text"><?= $t('थप पढ्नुहोस्', 'Read More') ?></span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

            <!-- News Section -->
            <div class="news-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="section-title-icon">📰</span>
                        <?= $t('ताजा समाचार', 'Latest News') ?>
                    </h2>
                    <a href="/news.php" class="section-link">
                        <?= $t('सबै हेर्नुहोस्', 'View All') ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                
                <div class="news-grid" id="newsGrid">
                    <!-- News Card 1 -->
                    <a href="/news-post.php" class="news-card">
                        <div class="card-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=250&fit=crop" alt="News" class="card-image" loading="lazy">
                            <span class="card-category-badge"><?= $t('अर्थ', 'Economy') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $t('NEPSE ले नयाँ रेकर्ड बनायो', 'NEPSE Sets New Record High') ?></h3>
                            <div class="card-meta">
                                <span class="meta-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span class="meta-time"><?= $t('30 मिनेट अघि', '30 min ago') ?></span>
                            </div>
                        </div>
                    </a>
                    
                    <!-- News Card 2 -->
                    <a href="/news-post.php" class="news-card">
                        <div class="card-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?w=400&h=250&fit=crop" alt="News" class="card-image" loading="lazy">
                            <span class="card-category-badge"><?= $t('प्रविधि', 'Technology') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $t('सुनको मूल्यमा वृद्धि', 'Gold Prices Surge') ?></h3>
                            <div class="card-meta">
                                <span class="meta-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span class="meta-time"><?= $t('1 घण्टा अघि', '1 hour ago') ?></span>
                            </div>
                        </div>
                    </a>
                    
                    <!-- News Card 3 -->
                    <a href="/news-post.php" class="news-card">
                        <div class="card-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=400&h=250&fit=crop" alt="News" class="card-image" loading="lazy">
                            <span class="card-category-badge"><?= $t('खेलकुद', 'Sports') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= $t('IPO आवेदन खुला', 'IPO Applications Open') ?></h3>
                            <div class="card-meta">
                                <span class="meta-source"><?= $t('आकाशवाणी', 'Aakashvani') ?></span>
                                <span class="meta-time"><?= $t('2 घण्टा अघि', '2 hours ago') ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Tools Section -->
            <div class="tools-section mt-8 mb-8">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="section-title-icon">🛠️</span>
                        <?= $t('छिटो टूलहरू', 'Quick Tools') ?>
                    </h2>
                </div>
                
                <div class="tools-grid">
                    <?php
                    $quickTools = [
                        ['icon' => '📅', 'label' => $t('पात्रो', 'Calendar'), 'href' => '/nepali-patro.php'],
                        ['icon' => '♈', 'label' => $t('राशिफल', 'Horoscope'), 'href' => '/rashifal.php'],
                        ['icon' => '📊', 'label' => $t('NEPSE', 'NEPSE'), 'href' => '/ipo-tracker.php'],
                        ['icon' => '🏥', 'label' => $t('अस्पताल', 'Hospitals'), 'href' => '/gov-services.php'],
                        ['icon' => '⚡', 'label' => $t('मौसम', 'Weather'), 'href' => '/weather.php'],
                        ['icon' => '🔔', 'label' => $t('सरकारी सेवा', 'Gov Services'), 'href' => '/gov-services.php'],
                    ];
                    foreach ($quickTools as $tool):
                    ?>
                    <a href="<?= $tool['href'] ?>" class="tool-card">
                        <span class="tool-icon"><?= $tool['icon'] ?></span>
                        <span class="tool-label"><?= $tool['label'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- ═══════════════════════════════════════════════════════════════
         PREMIUM FOOTER
         ═══════════════════════════════════════════════════════════════ -->
    <footer class="premium-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Column -->
                <div>
                    <div class="footer-brand">
                        <div class="footer-logo">आ</div>
                        <div class="footer-brand-text">
                            <h3>आकाशवाणी</h3>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway to Information') ?></span>
                        </div>
                    </div>
                    <p class="footer-description">
                        <?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। समाचार, बजार डाटा, सरकारी सेवा, र थप जानकारी एकैठाउँमा।', 'Nepal\'s most trusted information platform. News, market data, government services, and more in one place.') ?>
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link">📘</a>
                        <a href="#" class="social-link">🐦</a>
                        <a href="#" class="social-link">📸</a>
                        <a href="#" class="social-link">📺</a>
                    </div>
                </div>
                
                <!-- Links Column -->
                <div>
                    <h4 class="footer-title"><?= $t('लिंकहरू', 'Links') ?></h4>
                    <ul class="footer-links">
                        <li><a href="/"><?= $t('गृहपृष्ठ', 'Home') ?></a></li>
                        <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                        <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                        <li><a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a></li>
                        <li><a href="/gov-services.php"><?= $t('सरकारी सेवा', 'Gov Services') ?></a></li>
                    </ul>
                </div>
                
                <!-- Resources Column -->
                <div>
                    <h4 class="footer-title"><?= $t('स्रोतहरू', 'Resources') ?></h4>
                    <ul class="footer-links">
                        <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                        <li><a href="/nepali-patro.php"><?= $t('नेपाली पात्रो', 'Nepali Calendar') ?></a></li>
                        <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                        <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                    </ul>
                </div>
                
                <!-- Contact Column -->
                <div>
                    <h4 class="footer-title"><?= $t('सम्पर्क', 'Contact') ?></h4>
                    <ul class="footer-links">
                        <li><a href="/about.php"><?= $t('हाम्रो बारेमा', 'About Us') ?></a></li>
                        <li><a href="/contact.php"><?= $t('सम्पर्क गर्नुहोस्', 'Contact') ?></a></li>
                        <li><a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a></li>
                        <li><a href="/terms.php"><?= $t('सेवा सर्त', 'Terms') ?></a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p class="footer-copyright">
                    &copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?>
                </p>
                <div class="footer-legal">
                    <a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a>
                    <a href="/terms.php"><?= $t('सर्तहरू', 'Terms') ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ═══════════════════════════════════════════════════════════════
         SCRIPTS
         ═══════════════════════════════════════════════════════════════ -->
    <script>
    // Premium Market Data Loader
    (function() {
        'use strict';
        
        const MarketLoader = {
            apiUrl: '/api/market-data.php',
            init() {
                this.loadMarketData();
                setInterval(() => this.loadMarketData(), 5 * 60 * 1000); // 5 min refresh
            },
            
            async loadMarketData() {
                try {
                    const resp = await fetch(this.apiUrl + '?type=all');
                    if (!resp.ok) throw new Error('API Error');
                    
                    const data = await resp.json();
                    this.updateUI(data);
                    this.animateCards();
                } catch (err) {
                    console.warn('Market data unavailable:', err.message);
                }
            },
            
            updateUI(data) {
                // NEPSE
                if (data.nepse) {
                    const n = data.nepse;
                    const valueEl = document.getElementById('nepse-value');
                    const changeEl = document.getElementById('nepse-change');
                    
                    if (valueEl) valueEl.textContent = n.index ? n.index.toLocaleString('en-US', {maximumFractionDigits: 2}) : '—';
                    
                    if (changeEl && n.change !== undefined) {
                        const isUp = n.change >= 0;
                        changeEl.innerHTML = `<span class="change-value ${isUp ? 'up' : 'down'}">${isUp ? '+' : ''}${n.change.toFixed(2)} (${isUp ? '+' : ''}${n.changePercent.toFixed(2)}%)</span>`;
                        changeEl.className = `card-change ${isUp ? 'up' : 'down'}`;
                    }
                }
                
                // Gold
                if (data.gold && data.gold.hallmarkPerTola) {
                    const goldEl = document.getElementById('gold-value');
                    if (goldEl) goldEl.textContent = 'रु ' + Number(data.gold.hallmarkPerTola).toLocaleString('en-US');
                    
                    const metaEl = document.getElementById('gold-meta');
                    if (metaEl && data.gold.source) {
                        metaEl.innerHTML = `<span class="meta-source">${data.gold.source}</span>`;
                    }
                }
                
                // Forex
                if (data.forex && data.forex.length > 0) {
                    const usd = data.forex.find(r => r.code === 'USD');
                    if (usd) {
                        const forexEl = document.getElementById('forex-value');
                        const metaEl = document.getElementById('forex-meta');
                        if (forexEl) forexEl.textContent = 'रु ' + usd.sell.toFixed(2);
                        if (metaEl) metaEl.innerHTML = `<span class="meta-buy">Buy: रु ${usd.buy.toFixed(2)}</span>`;
                    }
                }
                
                // Petrol
                if (data.petrol && data.petrol.petrol) {
                    const petrolEl = document.getElementById('petrol-value');
                    if (petrolEl) petrolEl.textContent = 'रु ' + data.petrol.petrol;
                }
            },
            
            animateCards() {
                document.querySelectorAll('.market-card').forEach((card, i) => {
                    setTimeout(() => card.classList.add('loaded'), i * 100);
                });
            }
        };
        
        // News Loader
        const NewsLoader = {
            apiUrl: '/api/news-unified.php',
            
            async loadNews() {
                try {
                    const resp = await fetch(this.apiUrl + '?limit=6');
                    if (!resp.ok) throw new Error('News API Error');
                    
                    const data = await resp.json();
                    if (data.news && data.news.length > 0) {
                        this.updateFeatured(data.news[0]);
                        this.updateGrid(data.news.slice(1, 4));
                    }
                } catch (err) {
                    console.warn('News data load failed:', err.message);
                }
            },
            
            updateFeatured(news) {
                const titleEl = document.getElementById('featured-title');
                const timeEl = document.getElementById('featured-time');
                if (titleEl && news.title) titleEl.textContent = news.title;
                if (timeEl && news.published_at) timeEl.textContent = this.timeAgo(news.published_at);
            },
            
            updateGrid(newsItems) {
                // Optional: Update news grid dynamically
            },
            
            timeAgo(dateStr) {
                if (!dateStr) return '<?= $t('अहिले', 'Just now') ?>';
                const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
                if (diff < 60) return diff + 's <?= $t('अघि', 'ago') ?>';
                if (diff < 3600) return Math.floor(diff / 60) + 'm <?= $t('अघि', 'ago') ?>';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h <?= $t('अघि', 'ago') ?>';
                return Math.floor(diff / 86400) + 'd <?= $t('अघि', 'ago') ?>';
            }
        };
        
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            MarketLoader.init();
            NewsLoader.loadNews();
        });
    })();
    </script>
    
    <!-- Service Worker -->
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
