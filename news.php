<?php
/**
 * आकाशवाणी — News Page (Premium)
 */
require_once __DIR__ . '/config.php';

$lang = $_SESSION['lang'] ?? 'ne';
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;

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
    <meta name="description" content="<?= $t('नेपाल र विश्वका ताजा समाचार।', 'Latest news from Nepal and world.') ?>">
    <meta name="theme-color" content="#10b981">
    <link rel="manifest" href="/manifest.json">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/premium.css">
    
    <style>
        .page-hero {
            background: linear-gradient(135deg, var(--dark-900), var(--dark-800));
            padding: var(--space-12) 0;
            text-align: center;
        }
        .page-title-main {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
        }
        .page-subtitle {
            color: var(--dark-400);
            font-size: 1.125rem;
            margin-top: var(--space-2);
        }
        .categories-bar {
            background: #fff;
            border-bottom: 1px solid var(--dark-100);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: var(--space-4) 0;
        }
        .categories-scroll {
            display: flex;
            gap: var(--space-3);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .categories-scroll::-webkit-scrollbar { display: none; }
        .cat-btn {
            padding: var(--space-2) var(--space-5);
            background: var(--dark-50);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark-600);
            white-space: nowrap;
            transition: all var(--transition);
            border: 1px solid transparent;
        }
        .cat-btn:hover {
            background: var(--dark-100);
            color: var(--dark-900);
        }
        .cat-btn.active {
            background: var(--gradient-primary);
            color: #fff;
            box-shadow: var(--glow-sm);
        }
        .content-area {
            padding: var(--space-10) 0;
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="premium-topbar">
        <div class="container">
            <div class="topbar-content">
                <div class="topbar-left">
                    <span class="topbar-badge">✨</span>
                    <span class="topbar-date"><?= date('l, j F Y') ?></span>
                </div>
                <div class="topbar-right">
                    <a href="?lang=en" class="lang-btn">EN / ने</a>
                    <a href="/login.php" class="login-btn"><?= $t('लगइन', 'Login') ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="premium-header">
        <div class="container">
            <div class="header-grid">
                <a href="/" class="brand">
                    <div class="brand-logo"><span>आ</span></div>
                    <div class="brand-text">
                        <h1>आकाशवाणी</h1>
                        <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway') ?></span>
                    </div>
                </a>
                
                <div class="header-search">
                    <div class="search-wrapper">
                        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <input type="search" class="search-input" placeholder="<?= $t('खोज्नुहोस्...', 'Search...') ?>">
                    </div>
                </div>
                
                <div class="header-actions">
                    <button class="action-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <nav class="premium-nav">
            <div class="container">
                <ul class="nav-list">
                    <?php
                    $navItems = [
                        ['/' => 'गृह'], ['/news.php' => 'समाचार'], ['/nepali-patro.php' => 'पात्रो'],
                        ['/rashifal.php' => 'राशिफल'], ['/ipo-tracker.php' => 'NEPSE/IPO'],
                        ['/tools.php' => 'टूलहरू'], ['/gov-services.php' => 'सरकारी'], ['/weather.php' => 'मौसम'],
                    ];
                    $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
                    foreach ($navItems as $item):
                        $path = array_key_first($item);
                        $label = $item[$path];
                        $active = $currentPath === $path ? 'active' : '';
                    ?>
                    <li><a href="<?= $path ?>" class="nav-link <?= $active ?>"><?= $label ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- PAGE HERO -->
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title-main">📰 <?= $t('ताजा समाचार', 'Latest News') ?></h1>
            <p class="page-subtitle"><?= $t('नेपाल र विश्वका ताजा समाचार', 'Latest from Nepal & World') ?></p>
        </div>
    </section>

    <!-- CATEGORIES -->
    <div class="categories-bar">
        <div class="container">
            <div class="categories-scroll">
                <?php foreach ($categories as $cat => $label): ?>
                <a href="/news.php<?= $cat ? '?category='.$cat : '' ?>" 
                   class="cat-btn <?= ($category ?? '') === $cat ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- NEWS GRID -->
    <section class="content-area">
        <div class="container">
            <div class="news-grid" id="news-list">
                <?php for($i=0; $i<6; $i++): ?>
                <div class="news-card skeleton-card">
                    <div class="card-image-wrapper"></div>
                    <div class="card-body">
                        <span class="card-category-badge"></span>
                        <h3 class="card-title"></h3>
                        <div class="card-meta"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- PREMIUM FOOTER -->
    <footer class="premium-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <div class="footer-logo">आ</div>
                        <div class="footer-brand-text">
                            <h3>आकाशवाणी</h3>
                            <span><?= $t('सूचनाको खुला आकाश', 'Your Gateway') ?></span>
                        </div>
                    </div>
                    <p class="footer-description"><?= $t('नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म।', 'Nepal\'s most trusted platform.') ?></p>
                </div>
                <div>
                    <h4 class="footer-title"><?= $t('लिंकहरू', 'Links') ?></h4>
                    <ul class="footer-links">
                        <li><a href="/"><?= $t('गृहपृष्ठ', 'Home') ?></a></li>
                        <li><a href="/news.php"><?= $t('समाचार', 'News') ?></a></li>
                        <li><a href="/ipo-tracker.php"><?= $t('NEPSE/IPO', 'NEPSE/IPO') ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title"><?= $t('स्रोतहरू', 'Resources') ?></h4>
                    <ul class="footer-links">
                        <li><a href="/rashifal.php"><?= $t('राशिफल', 'Horoscope') ?></a></li>
                        <li><a href="/weather.php"><?= $t('मौसम', 'Weather') ?></a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title"><?= $t('कानूनी', 'Legal') ?></h4>
                    <ul class="footer-links">
                        <li><a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a></li>
                        <li><a href="/terms.php"><?= $t('सर्तहरू', 'Terms') ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी। सर्वाधिकार सुरक्षित।', 'Aakashvani. All rights reserved.') ?></p>
            </div>
        </div>
    </footer>

    <script>
    async function loadNews() {
        const params = new URLSearchParams(window.location.search);
        const cat = params.get('category') || 'all';
        
        try {
            const resp = await fetch('/api/news-unified.php?cat=' + cat + '&limit=12');
            const data = await resp.json();
            
            if (data.news && data.news.length > 0) {
                const grid = document.getElementById('news-list');
                grid.innerHTML = data.news.map(news => `
                    <a href="/news-post.php?slug=${news.slug || news.id}" class="news-card">
                        <div class="card-image-wrapper">
                            <img src="${news.image || '/assets/images/placeholder.svg'}" alt="" loading="lazy">
                            <span class="card-category-badge">${news.category || 'समाचार'}</span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title">${news.title || ''}</h3>
                            <div class="card-meta">
                                <span class="meta-source">${news.source || 'आकाशवाणी'}</span>
                                <span class="meta-time">${timeAgo(news.published_at)}</span>
                            </div>
                        </div>
                    </a>
                `).join('');
            }
        } catch (e) {
            console.warn('News load failed');
        }
    }
    
    function timeAgo(dateStr) {
        if (!dateStr) return '<?= $t('अहिले', 'Just now') ?>';
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60) return diff + 's ago';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }
    
    document.addEventListener('DOMContentLoaded', loadNews);
    </script>
</body>
</html>
