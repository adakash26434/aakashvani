<?php
/**
 * आकाशवाणी — News Page v3 (TechPana-style)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Get news
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$news = getPublishedNews($category, null, 20, 0);

// Try to fetch from news API when DB is empty
if (empty($news)) {
    $cacheFile = __DIR__ . '/data/cache/news-home.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (!empty($cached['news'])) {
            $news = $cached['news'];
        }
    }
    
    // Final fallback with real RSS feed
    if (empty($news)) {
        $news = fetchNewsFromRSS($category);
    }
}

// Helper function to fetch from RSS
function fetchNewsFromRSS($category = null) {
    $cacheFile = __DIR__ . '/data/cache/news-rss-' . ($category ?: 'all') . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
        return json_decode(file_get_contents($cacheFile), true) ?: [];
    }
    
    $cat = $category ?: 'all';
    $url = '/api/news-rss.php?cat=' . $cat . '&limit=12';
    
    $context = stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true]
    ]);
    
    $resp = @file_get_contents('http://' . $_SERVER['HTTP_HOST'] . $url, false, $context);
    if ($resp) {
        $data = json_decode($resp, true);
        if (!empty($data['news'])) {
            file_put_contents($cacheFile, $resp);
            return $data['news'];
        }
    }
    
    return [];
}

$categories = [
    '' => $t('सबै', 'All'),
    'politics' => $t('राजनीति', 'Politics'),
    'economy' => $t('अर्थ', 'Economy'),
    'sports' => $t('खेलकुद', 'Sports'),
    'technology' => $t('प्रविधि', 'Technology'),
    'entertainment' => $t('मनोरञ्जन', 'Entertainment'),
    'international' => $t('विश्व', 'International'),
];

$pageTitle = $t('समाचार | आकाशवाणी', 'News | Aakashvani');
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
    <meta name="description" content="<?= $t('नेपाल र विश्वका ताजा समाचार।', 'Latest news from Nepal and around the world.') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--dark-900), var(--dark-800));
            padding: var(--sp-12) 0;
            color: #fff;
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: var(--sp-2);
        }
        .page-subtitle {
            color: var(--dark-400);
            font-size: 1.125rem;
        }
        .categories-nav {
            background: #fff;
            border-bottom: 1px solid var(--dark-100);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .categories-list {
            display: flex;
            gap: var(--sp-2);
            padding: var(--sp-4) 0;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .categories-list::-webkit-scrollbar { display: none; }
        .category-btn {
            padding: var(--sp-2) var(--sp-4);
            background: var(--dark-50);
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-600);
            white-space: nowrap;
            transition: all var(--transition);
        }
        .category-btn:hover {
            background: var(--dark-100);
            color: var(--dark-900);
        }
        .category-btn.active {
            background: var(--primary);
            color: #fff;
        }
        .news-section {
            padding: var(--sp-12) 0;
        }
        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--sp-6);
        }
        @media (max-width: 1024px) { .news-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .news-grid { grid-template-columns: 1fr; } }
        .news-card {
            background: #fff;
            border-radius: var(--radius-xl);
            overflow: hidden;
            border: 1px solid var(--dark-100);
            transition: all var(--transition);
        }
        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .news-card-image {
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        .news-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .news-card-body {
            padding: var(--sp-4);
        }
        .news-card-category {
            display: inline-block;
            padding: var(--sp-1) var(--sp-2);
            background: var(--primary-50);
            color: var(--primary-700);
            font-size: 0.625rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
            text-transform: uppercase;
            margin-bottom: var(--sp-2);
        }
        .news-card-title {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
            color: var(--dark-900);
            margin-bottom: var(--sp-2);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .news-card-meta {
            display: flex;
            align-items: center;
            gap: var(--sp-3);
            font-size: 0.75rem;
            color: var(--dark-400);
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
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
                    <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                    <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- MID HEADER -->
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
                <div class="tp-header-ads"></div>
            </div>
        </div>
    </div>

    <!-- STICKY NAV -->
    <nav class="tp-nav" id="tpNav">
        <div class="tp-container">
            <div class="tp-nav-inner">
                <button class="tp-nav-toggle" id="navToggle" aria-label="Menu">
                    <i data-lucide="menu"></i>
                </button>
                <ul class="tp-nav-list" id="navList">
                    <li><a href="/"><?= $t('गृह', 'Home') ?></a></li>
                    <li><a href="/news.php" class="active"><?= $t('समाचार', 'News') ?></a></li>
                    <li><a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a></li>
                    <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                    <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    <li><a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a></li>
                    <li><a href="/gov-services.php"><?= $t('सरकारी', 'Gov') ?></a></li>
                    <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                    <li><a href="/cricket.php"><?= $t('क्रिकेट', 'Cricket') ?></a></li>
                    <li><a href="/tenders.php"><?= $t('टेन्डर', 'Tenders') ?></a></li>
                    <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                </ul>
                <div class="tp-nav-search">
                    <button class="tp-search-btn" id="searchToggle" aria-label="Search">
                        <i data-lucide="search"></i>
                    </button>
                </div>
            </div>
            <div class="tp-search-bar" id="searchBar" style="display:none">
                <input type="search" placeholder="<?= $t('समाचार खोज्नुहोस्...', 'Search news...') ?>" id="searchInput">
            </div>
        </div>
    </nav>

    <!-- MARKET TICKER -->
    <div class="tp-market-bar">
        <div class="tp-container">
            <div class="tp-market-inner">
                <span class="tp-market-item"><i data-lucide="trending-up"></i><span class="tp-mkt-label">NEPSE</span><span class="tp-mkt-value" id="nepse-value">...</span><span class="tp-mkt-change" id="nepse-change">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="gem"></i><span class="tp-mkt-label"><?= $t('सुन', 'Gold') ?></span><span class="tp-mkt-value" id="gold-value">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="dollar-sign"></i><span class="tp-mkt-label">USD</span><span class="tp-mkt-value" id="forex-value">...</span></span>
                <span class="tp-market-divider">|</span>
                <span class="tp-market-item"><i data-lucide="fuel"></i><span class="tp-mkt-label"><?= $t('पेट्रोल', 'Petrol') ?></span><span class="tp-mkt-value" id="petrol-value">...</span></span>
            </div>
        </div>
    </div>

    <!-- PAGE BANNER -->
    <div class="tp-page-banner">
        <div class="tp-container">
            <div class="tp-page-banner-inner">
                <div>
                    <h1 class="tp-page-title"><i data-lucide="newspaper"></i> <?= $catLabel ?></h1>
                    <p class="tp-page-subtitle"><?= $t('नेपाल र विश्वका ताजा र विश्वसनीय समाचार', 'Trusted news from Nepal and around the world') ?></p>
                </div>
                <span class="tp-live-badge"><span class="tp-live-dot"></span>LIVE</span>
            </div>
        </div>
    </div>

    <!-- CATEGORY STRIP -->
    <div class="tp-cat-strip">
        <div class="tp-container">
            <div class="tp-cat-strip-inner">
                <?php foreach ($categories as $cat => $label): ?>
                <a href="/news.php<?= $cat ? '?category='.$cat : '' ?>" class="tp-cat-pill <?= ($category ?? '') === $cat ? 'active' : '' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- NEWS MAIN -->
    <main class="tp-news-main">
        <div class="tp-container">
            <div class="tp-content-layout">

                <!-- PRIMARY GRID -->
                <div class="tp-primary">
                    <div class="tp-news-grid" id="news-list">
                        <?php if (!empty($news)): foreach ($news as $item): ?>
                        <a href="<?= htmlspecialchars($item['internalUrl'] ?? '/news-post.php?slug='.urlencode($item['slug'] ?? '')) ?>" class="tp-news-card anim-fade-up">
                            <div class="tp-card-img-wrap">
                                <img src="<?= htmlspecialchars($item['image'] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&h=240&fit=crop') ?>" alt="<?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 60, 'UTF-8')) ?>" class="tp-card-img" loading="lazy">
                                <span class="tp-card-cat"><?= htmlspecialchars(ucfirst($item['cat'] ?? $item['category'] ?? 'समाचार')) ?></span>
                            </div>
                            <div class="tp-card-body">
                                <h3 class="tp-card-title"><?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 90, 'UTF-8')) ?></h3>
                                <div class="tp-card-meta">
                                    <span class="tp-card-source"><?= htmlspecialchars($item['sourceLabel'] ?? $item['source_name'] ?? 'Aakashvani') ?></span>
                                    <span class="tp-card-time"><?= timeAgo($item['published_at'] ?? '') ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; else: for ($i = 0; $i < 6; $i++): ?>
                        <div class="tp-news-card tp-skeleton">
                            <div class="tp-card-img-wrap"><div class="tp-skeleton-img"></div></div>
                            <div class="tp-card-body">
                                <div class="tp-skeleton-line" style="width:60%"></div>
                                <div class="tp-skeleton-line" style="width:90%;height:14px;margin-top:8px"></div>
                                <div class="tp-skeleton-line" style="width:75%;height:14px"></div>
                                <div class="tp-skeleton-line" style="width:40%;height:12px;margin-top:8px"></div>
                            </div>
                        </div>
                        <?php endfor; endif; ?>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="tp-sidebar">
                    <div class="tp-side-widget">
                        <h3 class="tp-widget-title"><?= $t('छिटो टूलहरू', 'Quick Tools') ?></h3>
                        <div class="tp-quick-grid">
                            <a href="/nepali-patro.php" class="tp-quick-item"><i data-lucide="calendar-days"></i><span><?= $t('पात्रो', 'Calendar') ?></span></a>
                            <a href="/rashifal.php" class="tp-quick-item"><i data-lucide="sparkles"></i><span><?= $t('राशिफल', 'Horoscope') ?></span></a>
                            <a href="/ipo-tracker.php" class="tp-quick-item"><i data-lucide="trending-up"></i><span><?= $t('NEPSE', 'NEPSE') ?></span></a>
                            <a href="/weather.php" class="tp-quick-item"><i data-lucide="cloud-sun"></i><span><?= $t('मौसम', 'Weather') ?></span></a>
                            <a href="/gov-services.php" class="tp-quick-item"><i data-lucide="building-2"></i><span><?= $t('सरकारी', 'Gov') ?></span></a>
                            <a href="/emergency.php" class="tp-quick-item"><i data-lucide="phone"></i><span><?= $t('आपतकालीन', 'Emergency') ?></span></a>
                        </div>
                    </div>
                    <div class="tp-side-widget">
                        <h3 class="tp-widget-title"><?= $t('ताजा लिंकहरू', 'Trending') ?></h3>
                        <ul class="tp-trending-list">
                            <li><a href="/ipo-tracker.php"><i data-lucide="trending-up"></i><?= $t('IPO खुला छ', 'Open IPOs') ?></a></li>
                            <li><a href="/weather.php"><i data-lucide="cloud-sun"></i><?= $t('आजको मौसम', 'Weather') ?></a></li>
                            <li><a href="/rashifal.php"><i data-lucide="sparkles"></i><?= $t('आजको राशिफल', 'Horoscope') ?></a></li>
                            <li><a href="/nepali-patro.php"><i data-lucide="calendar-days"></i><?= $t('नेपाली पात्रो', 'Calendar') ?></a></li>
                            <li><a href="/tenders.php"><i data-lucide="file-text"></i><?= $t('नयाँ टेन्डर', 'New Tenders') ?></a></li>
                            <li><a href="/cricket.php"><i data-lucide="circle-dot"></i><?= $t('क्रिकेट स्कोर', 'Cricket') ?></a></li>
                        </ul>
                    </div>
                    <div class="tp-side-widget tp-newsletter">
                        <h3 class="tp-widget-title"><?= $t('न्यूजलेटर', 'Newsletter') ?></h3>
                        <p class="tp-newsletter-desc"><?= $t('दैनिक समाचार इमेलमा पाउनुहोस्', 'Get daily news in email') ?></p>
                        <form class="tp-newsletter-form" onsubmit="handleNewsletterSubmit(event, this)">
                            <input type="email" placeholder="<?= $t('इमेल', 'Email') ?>" required class="tp-input">
                            <button type="submit" class="tp-btn-submit"><?= $t('सब्सक्राइब', 'Subscribe') ?></button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    
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
                    <p class="tp-footer-desc"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?></p>
                    <div class="tp-footer-social">
                        <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i data-lucide="youtube"></i></a>
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

        function timeAgo(d) {
            if (!d) return '<?= $t('भर्खरै', 'Just now') ?>';
            const s = Math.floor((Date.now() - new Date(d * 1000).getTime()) / 1000);
            if (s < 60) return s + 's <?= $t('अघि', 'ago') ?>';
            if (s < 3600) return Math.floor(s/60) + 'm <?= $t('अघि', 'ago') ?>';
            if (s < 86400) return Math.floor(s/3600) + 'h <?= $t('अघि', 'ago') ?>';
            return Math.floor(s/86400) + 'd <?= $t('अघि', 'ago') ?>';
        }

        async function loadMarket() {
            try {
                const resp = await fetch('/api/market-data.php?type=all');
                if (!resp.ok) return;
                const d = await resp.json();
                if (d.nepse) {
                    const n = d.nepse, v = document.getElementById('nepse-value'), c = document.getElementById('nepse-change');
                    if (v && n.index) v.textContent = n.index.toLocaleString('en-US', {maximumFractionDigits:2});
                    if (c && n.change !== undefined) {
                        const up = n.change >= 0;
                        c.textContent = (up ? '+' : '') + n.change.toFixed(2) + ' (' + (up ? '+' : '') + n.changePercent.toFixed(2) + '%)';
                        c.className = 'tp-mkt-change ' + (up ? 'up' : 'down');
                    }
                }
                if (d.gold && d.gold.hallmarkPerTola) { const gv = document.getElementById('gold-value'); if (gv) gv.textContent = 'रु ' + Number(d.gold.hallmarkPerTola).toLocaleString('en-US'); }
                if (d.forex && d.forex.rates && d.forex.rates.length > 0) {
                    const usd = d.forex.rates.find(r => r.code === 'USD');
                    if (usd) { const fv = document.getElementById('forex-value'); if (fv) fv.textContent = 'रु ' + usd.sell.toFixed(2); }
                }
                if (d.petrol && d.petrol.petrol) { const pv = document.getElementById('petrol-value'); if (pv) pv.textContent = 'रु ' + d.petrol.petrol; }
            } catch(e) { console.warn('Market unavailable'); }
        }

        async function loadNews() {
            const params = new URLSearchParams(window.location.search);
            const cat = params.get('category') || 'all';
            try {
                const resp = await fetch('/api/news-unified.php?cat=' + cat + '&limit=20');
                if (!resp.ok) return;
                const data = await resp.json();
                const items = data.items || data.news || [];
                if (items.length > 0) {
                    const grid = document.getElementById('news-list');
                    if (grid && grid.querySelector('.tp-skeleton')) {
                        grid.innerHTML = items.slice(0, 12).map(function(item, i) {
                            return '<a href="' + (item.link || '#') + '" class="tp-news-card anim-fade-up delay-' + ((i % 3) + 1) + '">' +
                                '<div class="tp-card-img-wrap"><img src="' + (item.image || 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=400&h=240&fit=crop') + '" alt="" class="tp-card-img" loading="lazy"><span class="tp-card-cat">' + (item.cat || item.category || 'समाचार') + '</span></div>' +
                                '<div class="tp-card-body"><h3 class="tp-card-title">' + (item.title || '') + '</h3>' +
                                '<div class="tp-card-meta"><span class="tp-card-source">' + (item.sourceLabel || 'Aakashvani') + '</span><span class="tp-card-time">' + timeAgo(item.pubDate || item.published_at) + '</span></div></div></a>';
                        }).join('');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }
            } catch(e) { console.warn('News API unavailable'); }
        }

        function initSearch() {
            const toggle = document.getElementById('searchToggle'), bar = document.getElementById('searchBar');
            if (toggle && bar) toggle.addEventListener('click', function() { bar.style.display = bar.style.display === 'none' ? 'block' : 'none'; if (bar.style.display === 'block') bar.querySelector('input').focus(); });
            const navToggle = document.getElementById('navToggle'), navList = document.getElementById('navList');
            if (navToggle && navList) navToggle.addEventListener('click', function() { navList.classList.toggle('open'); });
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
            loadMarket();
            loadNews();
            initSearch();
            setInterval(loadMarket, 5 * 60 * 1000);
        });
    })();
    function handleNewsletterSubmit(e, form) {
        e.preventDefault();
        form.innerHTML = '<p style="color:var(--primary);text-align:center;padding:var(--sp-4)">&#10003; ' + 
            (window.__t ? window.__t('Subscribed!') : 'Subscribed!') + '</p>';
    }
    </script>

</body>
</html>
