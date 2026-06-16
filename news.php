<?php
/**
 * आकाशवाणी — News Page v2
 * Premium 2026 Design
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

// Get news
$category = isset($_GET['category']) ? sanitize($_GET['category']) : null;
$news = getPublishedNews($category, null, 20, 0);

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
    <meta name="description" content="<?= $t('नेपाल र विश्वका ताजा समाचार।', 'Latest news from Nepal and around the world.') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, var(--dark-900), var(--dark-800));
            padding: var(--space-12) 0;
            color: #fff;
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: var(--space-2);
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
            gap: var(--space-2);
            padding: var(--space-4) 0;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .categories-list::-webkit-scrollbar { display: none; }
        .category-btn {
            padding: var(--space-2) var(--space-4);
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
            padding: var(--space-12) 0;
        }
        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-6);
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
            padding: var(--space-4);
        }
        .news-card-category {
            display: inline-block;
            padding: var(--space-1) var(--space-2);
            background: var(--primary-50);
            color: var(--primary-700);
            font-size: 0.625rem;
            font-weight: 700;
            border-radius: var(--radius-sm);
            text-transform: uppercase;
            margin-bottom: var(--space-2);
        }
        .news-card-title {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.4;
            color: var(--dark-900);
            margin-bottom: var(--space-2);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .news-card-meta {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-size: 0.75rem;
            color: var(--dark-400);
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                        <a href="/news.php" class="nav-link active"><?= $t('समाचार', 'News') ?></a>
                        <a href="/nepali-patro.php" class="nav-link"><?= $t('पात्रो', 'Calendar') ?></a>
                        <a href="/rashifal.php" class="nav-link"><?= $t('राशिफल', 'Horoscope') ?></a>
                        <a href="/ipo-tracker.php" class="nav-link"><?= $t('IPO', 'IPO') ?></a>
                    </nav>
                    <div class="header-actions">
                        <button class="btn btn-ghost btn-icon" aria-label="Search">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </button>
                        <button class="btn btn-ghost btn-icon mobile-menu-btn" aria-label="Menu">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 6h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">
                <svg class="icon" style="display:inline;vertical-align:middle;margin-right:8px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2-2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8z"/></svg>
                <?= $t('ताजा समाचार', 'Latest News') ?>
                <span class="live-badge" style="margin-left:12px;vertical-align:middle">
                    <span class="live-dot"></span>
                    LIVE
                </span>
            </h1>
            <p class="page-subtitle"><?= $t('नेपाल र विश्वका ताजा र विश्वसनीय समाचार', 'Trusted news from Nepal and around the world') ?></p>
        </div>
    </section>
    
    <!-- Categories -->
    <nav class="categories-nav">
        <div class="container">
            <div class="categories-list">
                <?php foreach ($categories as $cat => $label): ?>
                <a href="/news.php<?= $cat ? '?category='.$cat : '' ?>" 
                   class="category-btn <?= ($category ?? '') === $cat ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>
    
    <!-- News Grid -->
    <section class="news-section">
        <div class="container">
            <div class="news-grid">
                <?php foreach ($news as $item): ?>
                <a href="/news-post.php?slug=<?= urlencode($item['slug'] ?? '') ?>" class="news-card">
                    <div class="news-card-image">
                        <img src="<?= htmlspecialchars($item['image'] ?? '/assets/images/placeholder.jpg') ?>" alt="" loading="lazy">
                    </div>
                    <div class="news-card-body">
                        <span class="news-card-category"><?= htmlspecialchars($item['category'] ?? 'समाचार') ?></span>
                        <h3 class="news-card-title"><?= htmlspecialchars($item['title'] ?? '') ?></h3>
                        <div class="news-card-meta">
                            <span><?= htmlspecialchars($item['source_name'] ?? '') ?></span>
                            <span><?= timeAgo($item['published_at'] ?? '') ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="border:none;padding:0">
                <p class="footer-copyright">&copy; <?= date('Y') ?> <?= $t('आकाशवाणी', 'Aakashvani') ?></p>
                <div class="footer-legal">
                    <a href="/privacy.php"><?= $t('गोपनीयता', 'Privacy') ?></a>
                    <a href="/terms.php"><?= $t('सर्त', 'Terms') ?></a>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="/assets/js/app.js"></script>
</body>
</html>
