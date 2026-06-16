<?php
/**
 * आकाशवाणी — news.php (World-Class News Page)
 * Premium news listing with clean, modern design
 */

$pageTitle = 'समाचार | आकाशवाणी';
$pageDesc = 'नेपाल र विश्वका ताजा समाचार। OnlineKhabar, Setopati, Kantipur लगायत २०+ स्रोतबाट।';

include __DIR__ . '/header.php';

$lang = siteLang();
$isNepali = ($lang !== 'en');

// Categories
$categories = [
    ['all', $isNepali ? 'सबै' : 'All', 'newspaper'],
    ['politics', $isNepali ? 'राजनीति' : 'Politics', 'landmark'],
    ['economy', $isNepali ? 'अर्थ' : 'Economy', 'trending-up'],
    ['sports', $isNepali ? 'खेलकुद' : 'Sports', 'trophy'],
    ['technology', $isNepali ? 'प्रविधि' : 'Technology', 'cpu'],
    ['international', $isNepali ? 'विश्व' : 'International', 'globe'],
    ['entertainment', $isNepali ? 'मनोरञ्जन' : 'Entertainment', 'film'],
];

$activeCat = isset($_GET['cat']) ? strtolower(trim($_GET['cat'])) : 'all';

// Get news (simulated for demo - use actual API in production)
$newsItems = [];
if (function_exists('getPublishedNews')) {
    $newsItems = getPublishedNews(null, null, 20, 0);
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="header-content">
            <div class="header-title">
                <i data-lucide="newspaper" class="icon-lg"></i>
                <h1><?= $isNepali ? 'ताजा समाचार' : 'Latest News' ?></h1>
                <span class="live-indicator">
                    <span class="live-dot"></span>
                    LIVE
                </span>
            </div>
            
            <!-- Search -->
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="search" id="newsSearch" placeholder="<?= $isNepali ? 'समाचार खोज्नुहोस्...' : 'Search news...' ?>" class="search-input">
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories-section">
    <div class="container">
        <div class="categories-scroll">
            <?php foreach ($categories as $cat): ?>
            <a href="?cat=<?= $cat[0] ?>" 
               class="category-pill <?= $activeCat === $cat[0] ? 'active' : '' ?>">
                <i data-lucide="<?= $cat[2] ?>" class="cat-icon"></i>
                <span><?= htmlspecialchars($cat[1]) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- News Content -->
<main class="news-main">
    <div class="container">
        <div class="news-layout">
            
            <!-- News Grid -->
            <div class="news-content">
                
                <!-- Featured Article -->
                <?php if (!empty($newsItems)): ?>
                <article class="featured-article">
                    <a href="/news-post.php?slug=<?= urlencode($newsItems[0]['slug'] ?? '') ?>" class="article-image">
                        <img src="<?= htmlspecialchars($newsItems[0]['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                             alt="<?= htmlspecialchars($newsItems[0]['title'] ?? '') ?>"
                             loading="eager"
                             class="article-img">
                        <?php if (!empty($newsItems[0]['is_live'])): ?>
                        <span class="article-live">LIVE</span>
                        <?php endif; ?>
                    </a>
                    <div class="article-content">
                        <div class="article-meta">
                            <span class="article-category"><?= htmlspecialchars($newsItems[0]['category'] ?? 'समाचार') ?></span>
                            <span class="article-time"><?= timeAgo($newsItems[0]['published_at'] ?? '') ?></span>
                        </div>
                        <h2 class="article-title">
                            <a href="/news-post.php?slug=<?= urlencode($newsItems[0]['slug'] ?? '') ?>">
                                <?= htmlspecialchars($newsItems[0]['title'] ?? '') ?>
                            </a>
                        </h2>
                        <p class="article-excerpt">
                            <?= htmlspecialchars(mb_substr($newsItems[0]['summary'] ?? '', 0, 180)) ?>...
                        </p>
                        <div class="article-footer">
                            <span class="article-source">
                                <i data-lucide="external-link" class="source-icon"></i>
                                <?= htmlspecialchars($newsItems[0]['source_name'] ?? 'आकाशवाणी') ?>
                            </span>
                            <a href="/news-post.php?slug=<?= urlencode($newsItems[0]['slug'] ?? '') ?>" class="read-btn">
                                <?= $isNepali ? 'पढ्नुहोस्' : 'Read more' ?>
                                <i data-lucide="arrow-right" class="btn-icon"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endif; ?>
                
                <!-- News List -->
                <section class="news-list-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i data-lucide="clock" class="section-icon"></i>
                            <?= $isNepali ? 'थप समाचार' : 'More News' ?>
                        </h3>
                    </div>
                    
                    <div class="news-grid">
                        <?php 
                        $displayNews = array_slice($newsItems, 1, 12);
                        foreach ($displayNews as $news): 
                        ?>
                        <article class="news-card-compact">
                            <a href="/news-post.php?slug=<?= urlencode($news['slug'] ?? '') ?>" class="compact-image">
                                <img src="<?= htmlspecialchars($news['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                                     alt="<?= htmlspecialchars($news['title'] ?? '') ?>"
                                     loading="lazy"
                                     class="compact-img">
                                <?php if (!empty($news['is_breaking'])): ?>
                                <span class="breaking-badge">BREAKING</span>
                                <?php endif; ?>
                            </a>
                            <div class="compact-content">
                                <span class="compact-category"><?= htmlspecialchars($news['category'] ?? '') ?></span>
                                <h4 class="compact-title">
                                    <a href="/news-post.php?slug=<?= urlencode($news['slug'] ?? '') ?>">
                                        <?= htmlspecialchars($news['title'] ?? '') ?>
                                    </a>
                                </h4>
                                <div class="compact-meta">
                                    <span class="compact-time"><?= timeAgo($news['published_at'] ?? '') ?></span>
                                    <span class="compact-source"><?= htmlspecialchars($news['source_name'] ?? '') ?></span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Load More -->
                    <div class="load-more-wrap">
                        <button class="load-more-btn" id="loadMoreBtn">
                            <i data-lucide="refresh-cw" class="btn-icon"></i>
                            <?= $isNepali ? 'थप लोड गर्नुहोस्' : 'Load More' ?>
                        </button>
                    </div>
                </section>
            </div>
            
            <!-- Sidebar -->
            <aside class="news-sidebar">
                
                <!-- Trending -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i data-lucide="trending-up" class="widget-icon"></i>
                        <?= $isNepali ? 'ट्रेन्डिङ' : 'Trending' ?>
                    </h3>
                    <div class="trending-list">
                        <?php foreach (array_slice($newsItems, 0, 5) as $i => $item): ?>
                        <a href="/news-post.php?slug=<?= urlencode($item['slug'] ?? '') ?>" class="trending-item">
                            <span class="trending-rank"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                            <div class="trending-content">
                                <span class="trending-title"><?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 80)) ?>...</span>
                                <span class="trending-time"><?= timeAgo($item['published_at'] ?? '') ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Categories -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i data-lucide="grid-3x3" class="widget-icon"></i>
                        <?= $isNepali ? 'वर्गीकरण' : 'Categories' ?>
                    </h3>
                    <div class="category-links">
                        <?php foreach (array_slice($categories, 1) as $cat): ?>
                        <a href="?cat=<?= $cat[0] ?>" class="category-link">
                            <span class="cat-icon-wrap">
                                <i data-lucide="<?= $cat[2] ?>" class="link-icon"></i>
                            </span>
                            <span class="cat-name"><?= htmlspecialchars($cat[1]) ?></span>
                            <i data-lucide="chevron-right" class="arrow-icon"></i>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            </aside>
        </div>
    </div>
</main>

<style>
/* ═══════════════════════════════════════════════════════════════
   NEWS PAGE STYLES
   ═══════════════════════════════════════════════════════════════ */

/* Page Header */
.page-header {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: 32px 0;
    color: #fff;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title i { color: #10b981; }

.header-title h1 {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
}

.live-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 4px;
}

.live-dot {
    width: 6px;
    height: 6px;
    background: #fff;
    border-radius: 50%;
    animation: livePulse 1.5s ease-in-out infinite;
}

@keyframes livePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Search Box */
.search-box {
    position: relative;
    width: 100%;
    max-width: 400px;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    color: #94a3b8;
}

.search-input {
    width: 100%;
    padding: 12px 16px 12px 48px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    transition: all 0.2s;
}

.search-input::placeholder { color: #94a3b8; }

.search-input:focus {
    outline: none;
    background: rgba(255,255,255,0.15);
    border-color: #10b981;
}

/* Categories */
.categories-section {
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 50;
}

.categories-scroll {
    display: flex;
    gap: 8px;
    padding: 16px 0;
    overflow-x: auto;
    scrollbar-width: none;
}

.categories-scroll::-webkit-scrollbar { display: none; }

.category-pill {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #f8fafc;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    white-space: nowrap;
    transition: all 0.2s;
    text-decoration: none;
}

.category-pill:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.category-pill.active {
    background: #10b981;
    color: #fff;
}

.cat-icon { width: 14px; height: 14px; }

/* News Layout */
.news-main {
    padding: 32px 0;
    background: #f8fafc;
}

.news-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 32px;
}

@media (max-width: 1024px) {
    .news-layout {
        grid-template-columns: 1fr;
    }
    .news-sidebar {
        display: none;
    }
}

/* Featured Article */
.featured-article {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 32px;
}

.article-image {
    position: relative;
    display: block;
}

.article-img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

@media (max-width: 768px) {
    .article-img { height: 240px; }
}

.article-live {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 6px 12px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 4px;
}

.article-content {
    padding: 24px;
}

.article-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.article-category {
    padding: 4px 12px;
    background: #10b981;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
}

.article-time {
    font-size: 12px;
    color: #94a3b8;
}

.article-title {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 12px;
}

.article-title a {
    color: #0f172a;
    text-decoration: none;
}

.article-title a:hover { color: #10b981; }

.article-excerpt {
    font-size: 15px;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 16px;
}

.article-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.article-source {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #94a3b8;
}

.source-icon { width: 14px; height: 14px; }

.read-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    background: #10b981;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.read-btn:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-icon { width: 14px; height: 14px; }

/* News List Section */
.news-list-section {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #10b981;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
}

.section-icon { color: #10b981; width: 20px; height: 20px; }

/* News Grid */
.news-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .news-grid { grid-template-columns: 1fr; }
}

.news-card-compact {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    transition: background 0.2s;
}

.news-card-compact:hover { background: #f8fafc; }

.compact-image {
    flex-shrink: 0;
    width: 120px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.compact-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.breaking-badge {
    position: absolute;
    top: 4px;
    left: 4px;
    padding: 2px 6px;
    background: #ef4444;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    border-radius: 3px;
}

.compact-content { flex: 1; min-width: 0; }

.compact-category {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #10b981;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.compact-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 8px;
}

.compact-title a {
    color: #0f172a;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.compact-title a:hover { color: #10b981; }

.compact-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #94a3b8;
}

.compact-source { font-weight: 500; }

/* Load More */
.load-more-wrap {
    display: flex;
    justify-content: center;
    margin-top: 24px;
}

.load-more-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 32px;
    background: #f1f5f9;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.load-more-btn:hover {
    background: #10b981;
    color: #fff;
}

/* Sidebar Widget */
.sidebar-widget {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.widget-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.widget-icon { color: #10b981; width: 18px; height: 18px; }

/* Trending List */
.trending-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.trending-item {
    display: flex;
    gap: 12px;
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
}

.trending-item:hover { background: #f8fafc; }

.trending-rank {
    font-size: 20px;
    font-weight: 800;
    color: #e2e8f0;
    width: 28px;
}

.trending-content { flex: 1; }

.trending-title {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    line-height: 1.4;
    margin-bottom: 4px;
}

.trending-time {
    font-size: 11px;
    color: #94a3b8;
}

/* Category Links */
.category-links {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.category-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
}

.category-link:hover {
    background: #f8fafc;
}

.cat-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    border-radius: 8px;
}

.link-icon { width: 16px; height: 16px; color: #10b981; }

.cat-name {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.arrow-icon { width: 14px; height: 14px; color: #cbd5e1; }
.category-link:hover .arrow-icon { color: #10b981; }

/* Responsive */
@media (max-width: 640px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .search-box { max-width: 100%; }
    .header-title h1 { font-size: 22px; }
}
</style>

<?php include __DIR__ . '/footer.php'; ?>
