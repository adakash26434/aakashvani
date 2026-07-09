<?php
/**
 * आकाशवाणी — News Post Page (Article Detail)
 * Premium 2026 Design
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!$slug) {
    header('Location: /news.php');
    exit;
}

$news = getNewsBySlug($slug);
if (!$news) {
    http_response_code(404);
    $news = [
        'title' => $t('समाचार भेटिएन', 'News Not Found'),
        'content' => $t('यो समाचार अवस्थित छैन।', 'This news article does not exist.'),
        'published_at' => date('Y-m-d H:i:s'),
        'source_name' => 'आकाशवाणी',
    ];
}

// Related news
$related = getRelatedNews($news['id'] ?? 0, $news['category'] ?? '', 4);
?>
<!DOCTYPE html>
<html lang="<?= $isNepali ? 'ne' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title'] ?? '') ?> | <?= $t('आकाशवाणी', 'Aakashvani') ?></title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta property="og:title" content="<?= $t('आकाशवाणी - Nepal Information Portal', 'Aakashvani - Nepal Information Portal') ?>">
    <meta property="og:description" content="<?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted information platform.') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($news['summary'] ?? $news['content'] ?? ''), 0, 160)) ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    
    <style>
        .article-header {
            background: linear-gradient(135deg, var(--dark-900), var(--dark-800));
            padding: var(--sp-12) 0;
            color: #fff;
        }
        
        .article-badge {
            display: inline-block;
            padding: var(--sp-1) var(--sp-3);
            background: var(--primary);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: var(--radius-full);
            margin-bottom: var(--sp-4);
        }
        
        .article-title {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: 800;
            line-height: 1.2;
            color: #fff;
            margin-bottom: var(--sp-4);
            max-width: 900px;
        }
        
        .article-meta {
            display: flex;
            align-items: center;
            gap: var(--sp-4);
            font-size: 0.875rem;
            color: var(--dark-400);
        }
        
        .article-source {
            display: flex;
            align-items: center;
            gap: var(--sp-2);
            color: var(--primary);
            font-weight: 500;
        }
        
        .article-content {
            max-width: 720px;
            margin: 0 auto;
            padding: var(--sp-12) var(--sp-6);
        }
        
        .article-featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: var(--radius-xl);
            margin-bottom: var(--sp-8);
        }
        
        .article-body {
            font-size: 1.0625rem;
            line-height: 1.8;
            color: var(--dark-700);
        }
        
        .article-body p {
            margin-bottom: var(--sp-4);
        }
        
        .article-body h2, .article-body h3 {
            margin-top: var(--sp-8);
            margin-bottom: var(--sp-4);
        }
        
        .article-body blockquote {
            padding: var(--sp-4) var(--sp-6);
            border-left: 4px solid var(--primary);
            background: var(--dark-50);
            margin: var(--sp-6) 0;
            font-style: italic;
        }
        
        /* Share */
        .share-section {
            display: flex;
            align-items: center;
            gap: var(--sp-4);
            padding: var(--sp-6) 0;
            border-top: 1px solid var(--dark-200);
            border-bottom: 1px solid var(--dark-200);
            margin: var(--sp-8) 0;
        }
        
        .share-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark-600);
        }
        
        .share-buttons {
            display: flex;
            gap: var(--sp-2);
        }
        
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all var(--transition);
            cursor: pointer;
            border: none;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
        }
        
        .share-btn.facebook { background: #1877f2; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.whatsapp { background: #25d366; }
        
        /* Related */
        .related-section {
            padding: var(--sp-12) 0;
            background: var(--dark-50);
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--sp-6);
        }
        
        @media (max-width: 1024px) {
            .related-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 640px) {
            .related-grid { grid-template-columns: 1fr; }
        }
        
        .related-card {
            background: #fff;
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: all var(--transition);
        }
        
        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .related-image {
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        
        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .related-body {
            padding: var(--sp-4);
        }
        
        .related-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark-900);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
                    <span class="tp-topbar-links"><a href="?">नेपाली</a><a href="?lang=en">English</a></span>
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
                <button class="tp-nav-toggle" id="navToggle" aria-label="Menu"><i data-lucide="menu"></i></button>
                                <ul class="tp-nav-list" id="navList">
                    <li><a href="/"><?= $t('गृह', 'Home') ?></a></li>
                    <li class="has-dropdown">
                        <a href="/news.php" class="active"><?= $t('समाचार', 'News') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/news.php" class="active"><?= $t('सबै समाचार', 'All News') ?></a></li>
                            <li><a href="/news-post.php"><?= $t('समाचार विवरण', 'News Detail') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/ipo-tracker.php"><?= $t('बजार', 'Markets') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/ipo-tracker.php"><?= $t('NEPSE / IPO', 'NEPSE / IPO') ?></a></li>
                            <li><a href="/currency.php"><?= $t('मुद्रा विनिमय', 'Currency') ?></a></li>
                            <li><a href="/gold-price.php"><?= $t('सुन / चाँदी', 'Gold / Silver') ?></a></li>
                            <li><a href="/bank-interest-rates.php"><?= $t('ब्याज दर', 'Bank Interest') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/loksewa.php"><?= $t('करियर', 'Career') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/loksewa.php"><?= $t('लोकसेवा', 'Loksewa') ?></a></li>
                            <li><a href="/nokari.php"><?= $t('नोकरी', 'Jobs') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/gov-services.php"><?= $t('सरकारी', 'Gov') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/gov-services.php"><?= $t('सरकारी सेवाहरू', 'Gov Services') ?></a></li>
                            <li><a href="/tenders.php"><?= $t('टेन्डर', 'Tenders') ?></a></li>
                            <li><a href="/alerts.php"><?= $t('अलर्ट', 'Alerts') ?></a></li>
                        </ul>
                    </li>
                    <li class="has-dropdown">
                        <a href="/nepali-patro.php"><?= $t('दैनिक', 'Daily') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/nepali-patro.php"><?= $t('पात्रो', 'Calendar') ?></a></li>
                            <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                            <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                            <li><a href="/emergency.php"><?= $t('आपतकालीन', 'Emergency') ?></a></li>
                        </ul>
                    </li>
                    <li><a href="/cricket.php"><?= $t('क्रिकेट', 'Cricket') ?></a></li>
                    <li class="has-dropdown">
                        <a href="/tools.php"><?= $t('टूलहरू', 'Tools') ?></a>
                        <ul class="tp-nav-sub">
                            <li><a href="/tools.php"><?= $t('सबै टूलहरू', 'All Tools') ?></a></li>
                            <li><a href="/info-hub.php"><?= $t('सूचना केन्द्र', 'Info Hub') ?></a></li>
                        </ul>
                    </li>
                </ul>
<div class="tp-nav-search">
                    <button class="tp-search-btn" id="searchToggle" aria-label="Search"><i data-lucide="search"></i></button>
                </div>
            </div>
            <div class="tp-search-bar" id="searchBar" style="display:none">
                <input type="search" placeholder="<?= $t('खोज्नुहोस्...', 'Search...') ?>" id="searchInput">
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

    
    <!-- Article Header -->
    <section class="article-header">
        <div class="container">
            <span class="article-badge"><?= htmlspecialchars($news['category'] ?? 'समाचार') ?></span>
            <h1 class="article-title"><?= htmlspecialchars($news['title'] ?? '') ?></h1>
            <div class="article-meta">
                <span class="article-source">
                    <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    <?= htmlspecialchars($news['source_name'] ?? 'आकाशवाणी') ?>
                </span>
                <span><?= timeAgo($news['published_at'] ?? '') ?></span>
            </div>
        </div>
    </section>
    
    <!-- Article Content -->
    <article class="article-content">
        <?php if (!empty($news['image'])): ?>
        <img src="<?= htmlspecialchars($news['image']) ?>" alt="" class="article-featured-image">
        <?php endif; ?>
        
        <?php if (!empty($news['summary'])): ?>
        <p style="font-size:1.125rem;font-weight:500;color:var(--dark-700);margin-bottom:var(--sp-6)">
            <?= nl2br(htmlspecialchars($news['summary'])) ?>
        </p>
        <?php endif; ?>
        
        <div class="article-body">
            <?= $news['content'] ?? '' ?>
        </div>
        
        <!-- Share -->
        <div class="share-section">
            <span class="share-label"><?= $t('सेयर गर्नुहोस्', 'Share') ?>:</span>
            <div class="share-buttons">
                <button class="share-btn facebook" onclick="shareContent('facebook')">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </button>
                <button class="share-btn twitter" onclick="shareContent('twitter')">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                </button>
                <button class="share-btn whatsapp" onclick="shareContent('whatsapp')">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                </button>
            </div>
        </div>
    </article>
    
    <!-- Related News -->
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <div class="container">
            <h2 class="text-xl font-bold mb-6" style="border-bottom:2px solid var(--primary);padding-bottom:var(--sp-3)">
                <?= $t('सम्बन्धित समाचार', 'Related News') ?>
            </h2>
            <div class="related-grid">
                <?php foreach ($related as $item): ?>
                <a href="/news-post.php?slug=<?= urlencode($item['slug'] ?? '') ?>" class="related-card">
                    <div class="related-image">
                        <img src="<?= htmlspecialchars($item['image'] ?? '/assets/images/placeholder.svg') ?>" alt="" loading="lazy">
                    </div>
                    <div class="related-body">
                        <h3 class="related-title"><?= htmlspecialchars($item['title'] ?? '') ?></h3>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Footer -->
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
        function shareContent(platform) {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            const shareUrls = {
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
                twitter: `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
                whatsapp: `https://wa.me/?text=${title}%20${url}`
            };
            if (shareUrls[platform]) {
                window.open(shareUrls[platform], '_blank', 'width=600,height=400');
            }
        }
    
            // Mobile dropdown: clicking has-dropdown links toggles submenu
            navList?.querySelectorAll('.has-dropdown > a').forEach(link => {
                link.addEventListener('click', (e) => {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        link.parentElement.classList.toggle('open');
                    }
                });
            });</script>
</body>
</html>
