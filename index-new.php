<?php
/**
 * आकाशवाणी — index.php (World-Class Homepage)
 * Premium Nepal News & Live Information Platform
 * 
 * Design Philosophy:
 * - Clean, minimal, professional
 * - Content-first hierarchy
 * - Instant loading experience
 * - World-class UI/UX
 */

// Clean URL router
$__path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$__clean = rtrim($__path, '/') ?: '/';
$__routes = [
    '/news' => '/news.php', '/loksewa' => '/loksewa.php',
    '/rashifal' => '/rashifal.php', '/tools' => '/tools.php',
    '/gov-services' => '/gov-services.php', '/nepali-patro' => '/nepali-patro.php',
    '/contact' => '/contact.php', '/search' => '/search.php',
    '/emergency' => '/emergency.php', '/ipo-tracker' => '/ipo-tracker.php',
    '/login' => '/login.php', '/register' => '/register.php',
    '/about' => '/about.php',
];
if ($__clean !== '/' && isset($__routes[$__clean])) {
    header('Location: ' . $__routes[$__clean], true, 302);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/market.php';

// Get data
$market = getMarket(true);
$gold = $market['gold'];
$forex = $market['forex'];
$nepse = $market['nepse'];
$fuel = $market['fuel'];

$lang = siteLang();
$isNepali = ($lang !== 'en');

$latestNews = function_exists('getPublishedNews') ? getPublishedNews(null, null, 12, 0, null, null) : [];
$featured = !empty($latestNews) ? array_shift($latestNews) : null;
$recentNews = array_slice($latestNews, 0, 8);

try { maybeRefreshNews(); } catch(\Exception $e) {}

// Market values
$nepseIdx = (float)($nepse['index'] ?? 0);
$nepseChg = $nepse['change'] ?? null;
$nepseUp = $nepseChg !== null && (float)$nepseChg >= 0;
$goldFine = (float)($gold['fine'] ?? 0);
$petrolP = (float)($fuel['petrol'] ?? 0);
$usdRate = (float)($forex['USD'] ?? 0);

// SEO
$pageTitle = 'आकाशवाणी — सूचनाको खुला आकाश';
$pageDesc = 'नेपालको सबैभन्दा विश्वसनीय सूचना प्लेटफर्म। AI समाचार, NEPSE, IPO, पात्रो, र सरकारी सेवा सबै एकै ठाउँमा।';
$pageUrl = defined('SITE_URL') ? SITE_URL : '';
$pageImg = defined('OG_IMAGE') ? OG_IMAGE : $pageUrl . '/assets/images/og-image.jpg';

include __DIR__ . '/header-new-design.php';
?>

<!-- ═══════════════════════════════════════════════════════════════
     WORLD-CLASS HOMEPAGE
     ═══════════════════════════════════════════════════════════════ -->

<!-- Live Market Bar -->
<section class="market-bar">
    <div class="container">
        <div class="market-inner">
            <div class="market-item">
                <span class="market-label">NEPSE</span>
                <span class="market-value <?= $nepseUp ? 'up' : 'down' ?>"><?= number_format($nepseIdx, 2) ?></span>
                <?php if ($nepseChg !== null): ?>
                <span class="market-change <?= $nepseUp ? 'up' : 'down' ?>">
                    <i data-lucide="<?= $nepseUp ? 'trending-up' : 'trending-down' ?>" class="icon-xs"></i>
                    <?= ($nepseUp ? '+' : '') . number_format((float)$nepseChg, 2) ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="market-divider"></div>
            <div class="market-item">
                <span class="market-label"><?= $isNepali ? 'सुन (10g)' : 'Gold (10g)' ?></span>
                <span class="market-value">रु <?= number_format($goldFine, 0) ?></span>
            </div>
            <div class="market-divider"></div>
            <div class="market-item">
                <span class="market-label">USD</span>
                <span class="market-value">रु <?= number_format($usdRate, 2) ?></span>
            </div>
            <div class="market-divider"></div>
            <div class="market-item">
                <span class="market-label"><?= $isNepali ? 'पेट्रोल' : 'Petrol' ?></span>
                <span class="market-value">रु <?= number_format($petrolP, 0) ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="home-main">
    <div class="container">
        <div class="home-grid">
            
            <!-- Primary Content -->
            <div class="content-primary">
                
                <!-- Featured News -->
                <?php if ($featured): ?>
                <article class="featured-card">
                    <a href="/news-post.php?id=<?= $featured['id'] ?>" class="featured-image">
                        <img src="<?= htmlspecialchars($featured['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                             alt="<?= htmlspecialchars($featured['title']) ?>"
                             loading="eager"
                             class="featured-img">
                        <?php if (!empty($featured['is_live'])): ?>
                        <span class="live-badge">LIVE</span>
                        <?php endif; ?>
                    </a>
                    <div class="featured-content">
                        <div class="featured-meta">
                            <span class="category-tag"><?= htmlspecialchars($featured['category'] ?? 'समाचार') ?></span>
                            <span class="time-ago"><?= timeAgo($featured['published_at'] ?? $featured['created_at'] ?? '') ?></span>
                        </div>
                        <h2 class="featured-title">
                            <a href="/news-post.php?id=<?= $featured['id'] ?>"><?= htmlspecialchars($featured['title']) ?></a>
                        </h2>
                        <p class="featured-excerpt"><?= htmlspecialchars(mb_substr($featured['summary'] ?? $featured['content'] ?? '', 0, 200)) ?>...</p>
                        <div class="featured-footer">
                            <span class="source"><?= htmlspecialchars($featured['source_name'] ?? 'आकाशवाणी') ?></span>
                            <a href="/news-post.php?id=<?= $featured['id'] ?>" class="read-more">
                                <?= $isNepali ? 'पढ्नुहोस्' : 'Read more' ?>
                                <i data-lucide="arrow-right" class="icon-sm"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endif; ?>
                
                <!-- Latest News -->
                <section class="news-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i data-lucide="clock" class="icon-md"></i>
                            <?= $isNepali ? 'ताजा समाचार' : 'Latest News' ?>
                        </h3>
                        <a href="/news.php" class="view-all">
                            <?= $isNepali ? 'सबै हेर्नुहोस्' : 'View all' ?>
                            <i data-lucide="chevron-right" class="icon-sm"></i>
                        </a>
                    </div>
                    
                    <div class="news-grid">
                        <?php foreach ($recentNews as $news): ?>
                        <article class="news-card">
                            <a href="/news-post.php?id=<?= $news['id'] ?>" class="news-image">
                                <img src="<?= htmlspecialchars($news['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                                     alt="<?= htmlspecialchars($news['title']) ?>"
                                     loading="lazy"
                                     class="news-img">
                            </a>
                            <div class="news-content">
                                <div class="news-meta">
                                    <span class="category-tag-sm"><?= htmlspecialchars($news['category'] ?? '') ?></span>
                                </div>
                                <h4 class="news-title">
                                    <a href="/news-post.php?id=<?= $news['id'] ?>"><?= htmlspecialchars($news['title']) ?></a>
                                </h4>
                                <div class="news-footer">
                                    <span class="time-ago-sm"><?= timeAgo($news['published_at'] ?? $news['created_at'] ?? '') ?></span>
                                    <span class="news-source"><?= htmlspecialchars($news['source_name'] ?? '') ?></span>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </section>
                
            </div>
            
            <!-- Sidebar -->
            <aside class="content-sidebar">
                
                <!-- Quick Services -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">
                        <i data-lucide="layout-grid" class="icon-md"></i>
                        <?= $isNepali ? 'छिटो सेवा' : 'Quick Services' ?>
                    </h3>
                    <div class="service-grid">
                        <a href="/news.php" class="service-item">
                            <i data-lucide="newspaper" class="service-icon"></i>
                            <span><?= $isNepali ? 'समाचार' : 'News' ?></span>
                        </a>
                        <a href="/ipo-tracker.php" class="service-item">
                            <i data-lucide="trending-up" class="service-icon"></i>
                            <span>NEPSE</span>
                        </a>
                        <a href="/nepali-patro.php" class="service-item">
                            <i data-lucide="calendar" class="service-icon"></i>
                            <span><?= $isNepali ? 'पात्रो' : 'Calendar' ?></span>
                        </a>
                        <a href="/rashifal.php" class="service-item">
                            <i data-lucide="sparkles" class="service-icon"></i>
                            <span><?= $isNepali ? 'राशिफल' : 'Rashifal' ?></span>
                        </a>
                        <a href="/gov-services.php" class="service-item">
                            <i data-lucide="landmark" class="service-icon"></i>
                            <span><?= $isNepali ? 'सरकारी' : 'Government' ?></span>
                        </a>
                        <a href="/emergency.php" class="service-item">
                            <i data-lucide="phone" class="service-icon"></i>
                            <span><?= $isNepali ? 'आपतकालीन' : 'Emergency' ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- Categories -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">
                        <i data-lucide="grid-3x3" class="icon-md"></i>
                        <?= $isNepali ? 'वर्गीकरण' : 'Categories' ?>
                    </h3>
                    <div class="category-list">
                        <a href="/news.php?category=politics" class="category-item">
                            <span class="cat-dot" style="background:#ef4444"></span>
                            <span><?= $isNepali ? 'राजनीति' : 'Politics' ?></span>
                            <i data-lucide="chevron-right" class="icon-sm"></i>
                        </a>
                        <a href="/news.php?category=economy" class="category-item">
                            <span class="cat-dot" style="background:#10b981"></span>
                            <span><?= $isNepali ? 'अर्थ' : 'Economy' ?></span>
                            <i data-lucide="chevron-right" class="icon-sm"></i>
                        </a>
                        <a href="/news.php?category=sports" class="category-item">
                            <span class="cat-dot" style="background:#f59e0b"></span>
                            <span><?= $isNepali ? 'खेलकुद' : 'Sports' ?></span>
                            <i data-lucide="chevron-right" class="icon-sm"></i>
                        </a>
                        <a href="/news.php?category=technology" class="category-item">
                            <span class="cat-dot" style="background:#3b82f6"></span>
                            <span><?= $isNepali ? 'प्रविधि' : 'Technology' ?></span>
                            <i data-lucide="chevron-right" class="icon-sm"></i>
                        </a>
                        <a href="/news.php?category=international" class="category-item">
                            <span class="cat-dot" style="background:#8b5cf6"></span>
                            <span><?= $isNepali ? 'विश्व' : 'International' ?></span>
                            <i data-lucide="chevron-right" class="icon-sm"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Tools -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">
                        <i data-lucide="wrench" class="icon-md"></i>
                        <?= $isNepali ? 'उपयोगी टूल' : 'Useful Tools' ?>
                    </h3>
                    <div class="tool-list">
                        <a href="/gold-price.php" class="tool-item">
                            <i data-lucide="gem" class="tool-icon"></i>
                            <div>
                                <span class="tool-name"><?= $isNepali ? 'सुनको मूल्य' : 'Gold Price' ?></span>
                                <span class="tool-value">रु <?= number_format($goldFine, 0) ?></span>
                            </div>
                        </a>
                        <a href="/currency-converter.php" class="tool-item">
                            <i data-lucide="coins" class="tool-icon"></i>
                            <div>
                                <span class="tool-name"><?= $isNepali ? 'मुद्रा विनिमय' : 'Currency' ?></span>
                                <span class="tool-value">1 USD = रु <?= number_format($usdRate, 2) ?></span>
                            </div>
                        </a>
                        <a href="/weather.php" class="tool-item">
                            <i data-lucide="cloud-sun" class="tool-icon"></i>
                            <div>
                                <span class="tool-name"><?= $isNepali ? 'मौसम' : 'Weather' ?></span>
                                <span class="tool-value"><?= $isNepali ? 'हेर्नुहोस्' : 'Check now' ?></span>
                            </div>
                        </a>
                    </div>
                </div>
                
            </aside>
        </div>
    </div>
</main>

<style>
/* ═══════════════════════════════════════════════════════════════
   WORLD-CLASS HOMEPAGE STYLES
   ═══════════════════════════════════════════════════════════════ */

/* Market Bar */
.market-bar {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: 12px 0;
    position: sticky;
    top: 0;
    z-index: 90;
}

.market-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 32px;
    flex-wrap: wrap;
}

.market-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.market-label {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
}

.market-value {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
}

.market-value.up { color: #22c55e; }
.market-value.down { color: #ef4444; }

.market-change {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
}

.market-change.up { color: #22c55e; }
.market-change.down { color: #ef4444; }

.market-divider {
    width: 1px;
    height: 20px;
    background: #334155;
}

.icon-xs { width: 12px; height: 12px; }
.icon-sm { width: 14px; height: 14px; }
.icon-md { width: 18px; height: 18px; }

/* Main Content */
.home-main {
    padding: 32px 0;
    background: #f8fafc;
}

.home-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 32px;
}

@media (max-width: 1024px) {
    .home-grid {
        grid-template-columns: 1fr;
    }
    .content-sidebar {
        display: none;
    }
}

/* Featured Card */
.featured-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 32px;
}

.featured-image {
    position: relative;
    display: block;
}

.featured-img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}

@media (max-width: 768px) {
    .featured-img { height: 240px; }
}

.live-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    padding: 6px 12px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-radius: 4px;
}

.featured-content {
    padding: 24px;
}

.featured-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.category-tag {
    padding: 4px 12px;
    background: #10b981;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
}

.time-ago {
    font-size: 12px;
    color: #94a3b8;
}

.featured-title {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 12px;
}

@media (max-width: 768px) {
    .featured-title { font-size: 20px; }
}

.featured-title a {
    color: #0f172a;
    text-decoration: none;
}

.featured-title a:hover {
    color: #10b981;
}

.featured-excerpt {
    font-size: 15px;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 16px;
}

.featured-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.source {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
}

.read-more {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #10b981;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.read-more:hover {
    gap: 10px;
}

/* News Section */
.news-section {
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

.section-title i { color: #10b981; }

.view-all {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #10b981;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}

.view-all:hover {
    gap: 8px;
}

/* News Grid */
.news-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .news-grid { grid-template-columns: 1fr; }
}

.news-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    transition: background 0.2s;
}

.news-card:hover {
    background: #f8fafc;
}

.news-image {
    flex-shrink: 0;
    width: 120px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
}

.news-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.news-content {
    flex: 1;
    min-width: 0;
}

.news-meta {
    margin-bottom: 6px;
}

.category-tag-sm {
    font-size: 10px;
    font-weight: 600;
    color: #10b981;
    text-transform: uppercase;
}

.news-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 8px;
}

.news-title a {
    color: #0f172a;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-title a:hover {
    color: #10b981;
}

.news-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #94a3b8;
}

.news-source {
    font-weight: 500;
}

.time-ago-sm {
    color: #94a3b8;
}

/* Sidebar Cards */
.sidebar-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.sidebar-title {
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

.sidebar-title i { color: #10b981; }

/* Service Grid */
.service-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}

.service-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 8px;
    background: #f8fafc;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
}

.service-item:hover {
    background: #10b981;
    transform: translateY(-2px);
}

.service-item:hover .service-icon,
.service-item:hover span {
    color: #fff;
}

.service-icon {
    width: 24px;
    height: 24px;
    color: #10b981;
}

.service-item span {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
}

/* Category List */
.category-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
}

.category-item:hover {
    background: #f8fafc;
}

.cat-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.category-item span {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.category-item i { color: #cbd5e1; }
.category-item:hover i { color: #10b981; }

/* Tool List */
.tool-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tool-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.15s;
}

.tool-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.tool-icon {
    width: 20px;
    height: 20px;
    color: #10b981;
}

.tool-item div {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.tool-name {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.tool-value {
    font-size: 12px;
    font-weight: 600;
    color: #0f172a;
}
</style>

<?php include __DIR__ . '/footer-new-design.php'; ?>
