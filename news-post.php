<?php
/**
 * आकाशवाणी — news-post.php (World-Class Article Page)
 * Premium reading experience with clean, professional design
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: /news.php');
    exit;
}

$news = getNewsBySlug($slug);
if (!$news) {
    http_response_code(404);
    header('Location: /news.php');
    exit;
}

$related = getRelatedNews($news['id'], $news['category'] ?? '', 6);
$latest = getPublishedNews(null, null, 6, 0);
$latest = array_filter($latest, fn($n) => ($n['id'] ?? 0) !== ($news['id'] ?? 0));
$latest = array_slice(array_values($latest), 0, 6);

$lang = siteLang();
$isNepali = ($lang !== 'en');

// SEO
$pageTitle = ($news['title'] ?? '') . ' | आकाशवाणी';
$pageDesc = mb_substr(strip_tags($news['summary'] ?? $news['content'] ?? ''), 0, 160);
$pageUrl = defined('SITE_URL') ? SITE_URL : '';

// Reading time
$wordCount = str_word_count(strip_tags(($news['content'] ?? '') . ' ' . ($news['summary'] ?? '')));
$readMins = max(1, (int)ceil($wordCount / 200));

include __DIR__ . '/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb">
    <div class="container">
        <ol class="breadcrumb-list">
            <li><a href="/"><i data-lucide="home" class="bc-icon"></i></a></li>
            <li><span class="bc-sep">›</span></li>
            <li><a href="/news.php"><?= $isNepali ? 'समाचार' : 'News' ?></a></li>
            <li><span class="bc-sep">›</span></li>
            <li><span class="bc-current"><?= htmlspecialchars(mb_substr($news['title'] ?? '', 0, 40)) ?>...</span></li>
        </ol>
    </div>
</nav>

<!-- Article -->
<main class="article-main">
    <div class="container">
        <div class="article-layout">
            
            <!-- Main Content -->
            <article class="article-content">
                
                <!-- Article Header -->
                <header class="article-header">
                    <div class="article-meta-top">
                        <span class="article-category"><?= htmlspecialchars($news['category'] ?? 'समाचार') ?></span>
                        <?php if (!empty($news['is_live'])): ?>
                        <span class="live-badge">LIVE</span>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="article-title"><?= htmlspecialchars($news['title'] ?? '') ?></h1>
                    
                    <div class="article-meta">
                        <div class="meta-left">
                            <span class="meta-source">
                                <i data-lucide="external-link" class="meta-icon"></i>
                                <?= htmlspecialchars($news['source_name'] ?? 'आकाशवाणी') ?>
                            </span>
                            <span class="meta-sep">•</span>
                            <span class="meta-time">
                                <i data-lucide="clock" class="meta-icon"></i>
                                <?= timeAgo($news['published_at'] ?? '') ?>
                            </span>
                            <span class="meta-sep">•</span>
                            <span class="meta-read">
                                <i data-lucide="book-open" class="meta-icon"></i>
                                <?= $readMins ?> <?= $isNepali ? 'मिनेट पढ्नुहोस्' : 'min read' ?>
                            </span>
                        </div>
                    </div>
                </header>
                
                <!-- Featured Image -->
                <?php if (!empty($news['image'])): ?>
                <figure class="article-image-wrap">
                    <img src="<?= htmlspecialchars($news['image']) ?>" 
                         alt="<?= htmlspecialchars($news['title'] ?? '') ?>"
                         class="article-featured-img"
                         loading="eager">
                    <?php if (!empty($news['image_caption'])): ?>
                    <figcaption class="image-caption"><?= htmlspecialchars($news['image_caption']) ?></figcaption>
                    <?php endif; ?>
                </figure>
                <?php endif; ?>
                
                <!-- Article Body -->
                <div class="article-body">
                    <?php if (!empty($news['summary'])): ?>
                    <p class="article-lead"><?= nl2br(htmlspecialchars($news['summary'])) ?></p>
                    <?php endif; ?>
                    
                    <div class="article-text">
                        <?= $news['content'] ?? '' ?>
                    </div>
                </div>
                
                <!-- Share Section -->
                <div class="share-section">
                    <span class="share-label"><?= $isNepali ? 'सेयर गर्नुहोस्' : 'Share' ?></span>
                    <div class="share-buttons">
                        <button class="share-btn" data-platform="facebook" title="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                        </button>
                        <button class="share-btn" data-platform="twitter" title="Twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                        </button>
                        <button class="share-btn" data-platform="whatsapp" title="WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        </button>
                        <button class="share-btn" data-platform="copy" title="Copy Link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        </button>
                    </div>
                </div>
                
                <!-- Related News -->
                <?php if (!empty($related)): ?>
                <section class="related-section">
                    <h3 class="related-title">
                        <i data-lucide="layers" class="section-icon"></i>
                        <?= $isNepali ? 'सम्बन्धित समाचार' : 'Related News' ?>
                    </h3>
                    <div class="related-grid">
                        <?php foreach (array_slice($related, 0, 4) as $item): ?>
                        <article class="related-card">
                            <a href="/news-post.php?slug=<?= urlencode($item['slug'] ?? '') ?>" class="related-image">
                                <img src="<?= htmlspecialchars($item['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                                     alt="<?= htmlspecialchars($item['title'] ?? '') ?>"
                                     loading="lazy"
                                     class="related-img">
                            </a>
                            <div class="related-content">
                                <span class="related-category"><?= htmlspecialchars($item['category'] ?? '') ?></span>
                                <h4 class="related-item-title">
                                    <a href="/news-post.php?slug=<?= urlencode($item['slug'] ?? '') ?>">
                                        <?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 80)) ?>...
                                    </a>
                                </h4>
                                <span class="related-time"><?= timeAgo($item['published_at'] ?? '') ?></span>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
                
            </article>
            
            <!-- Sidebar -->
            <aside class="article-sidebar">
                
                <!-- Latest News -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i data-lucide="clock" class="widget-icon"></i>
                        <?= $isNepali ? 'थप समाचार' : 'More News' ?>
                    </h3>
                    <div class="sidebar-news">
                        <?php foreach ($latest as $item): ?>
                        <a href="/news-post.php?slug=<?= urlencode($item['slug'] ?? '') ?>" class="sidebar-news-item">
                            <img src="<?= htmlspecialchars($item['image'] ?? '/assets/images/placeholder.jpg') ?>" 
                                 alt=""
                                 class="sidebar-news-img"
                                 loading="lazy">
                            <div class="sidebar-news-content">
                                <span class="sidebar-news-title"><?= htmlspecialchars(mb_substr($item['title'] ?? '', 0, 70)) ?>...</span>
                                <span class="sidebar-news-time"><?= timeAgo($item['published_at'] ?? '') ?></span>
                            </div>
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
   ARTICLE PAGE STYLES
   ═══════════════════════════════════════════════════════════════ */

/* Breadcrumb */
.breadcrumb {
    background: #f8fafc;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    flex-wrap: wrap;
}

.breadcrumb-list li {
    display: flex;
    align-items: center;
}

.breadcrumb-list a {
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb-list a:hover { color: #10b981; }

.bc-icon { width: 14px; height: 14px; }
.bc-sep { color: #cbd5e1; }
.bc-current { color: #0f172a; font-weight: 500; }

/* Article Main */
.article-main {
    padding: 32px 0 48px;
    background: #f8fafc;
}

.article-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 32px;
}

@media (max-width: 1024px) {
    .article-layout {
        grid-template-columns: 1fr;
    }
    .article-sidebar {
        display: none;
    }
}

/* Article Content */
.article-content {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

@media (max-width: 640px) {
    .article-content {
        padding: 20px;
        border-radius: 12px;
    }
}

/* Article Header */
.article-header {
    margin-bottom: 24px;
}

.article-meta-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.article-category {
    padding: 6px 14px;
    background: #10b981;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
}

.live-badge {
    padding: 6px 14px;
    background: #ef4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 6px;
    animation: livePulse 1.5s ease-in-out infinite;
}

@keyframes livePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.article-title {
    font-size: 32px;
    font-weight: 800;
    line-height: 1.3;
    color: #0f172a;
    margin-bottom: 16px;
}

@media (max-width: 768px) {
    .article-title {
        font-size: 24px;
    }
}

.article-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2e8f0;
}

.meta-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.meta-source,
.meta-time,
.meta-read {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
}

.meta-icon { width: 14px; height: 14px; }
.meta-sep { color: #cbd5e1; }

/* Featured Image */
.article-image-wrap {
    margin: 0 -32px 24px;
}

@media (max-width: 640px) {
    .article-image-wrap {
        margin: 0 -20px 20px;
    }
}

.article-featured-img {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
}

.image-caption {
    padding: 12px 32px;
    font-size: 13px;
    color: #64748b;
    font-style: italic;
    background: #f8fafc;
}

@media (max-width: 640px) {
    .image-caption {
        padding: 10px 20px;
    }
}

/* Article Body */
.article-body {
    max-width: 720px;
}

.article-lead {
    font-size: 18px;
    line-height: 1.7;
    color: #334155;
    margin-bottom: 24px;
    font-weight: 500;
}

.article-text {
    font-size: 16px;
    line-height: 1.8;
    color: #475569;
}

.article-text p {
    margin-bottom: 16px;
}

.article-text h2,
.article-text h3 {
    margin: 32px 0 16px;
    color: #0f172a;
}

.article-text img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 24px 0;
}

.article-text blockquote {
    margin: 24px 0;
    padding: 20px 24px;
    background: #f8fafc;
    border-left: 4px solid #10b981;
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: #334155;
}

/* Share Section */
.share-section {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px 0;
    margin-top: 24px;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}

.share-label {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
}

.share-buttons {
    display: flex;
    gap: 8px;
}

.share-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #f1f5f9;
    border: none;
    border-radius: 10px;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.share-btn:hover {
    background: #10b981;
    color: #fff;
    transform: translateY(-2px);
}

/* Related Section */
.related-section {
    margin-top: 32px;
}

.related-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #10b981;
}

.section-icon { color: #10b981; width: 20px; height: 20px; }

.related-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 640px) {
    .related-grid {
        grid-template-columns: 1fr;
    }
}

.related-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    transition: background 0.2s;
}

.related-card:hover { background: #f8fafc; }

.related-image {
    flex-shrink: 0;
    width: 100px;
    height: 70px;
    border-radius: 8px;
    overflow: hidden;
}

.related-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-content { flex: 1; }

.related-category {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #10b981;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.related-item-title {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 6px;
}

.related-item-title a {
    color: #0f172a;
    text-decoration: none;
}

.related-item-title a:hover { color: #10b981; }

.related-time {
    font-size: 11px;
    color: #94a3b8;
}

/* Sidebar */
.article-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-widget {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

.sidebar-news {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sidebar-news-item {
    display: flex;
    gap: 12px;
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
}

.sidebar-news-item:hover { background: #f8fafc; }

.sidebar-news-img {
    flex-shrink: 0;
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.sidebar-news-content { flex: 1; }

.sidebar-news-title {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    line-height: 1.4;
    margin-bottom: 4px;
}

.sidebar-news-time {
    font-size: 11px;
    color: #94a3b8;
}
</style>

<script>
// Share functionality
document.querySelectorAll('.share-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const platform = this.dataset.platform;
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        
        let shareUrl = '';
        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'whatsapp':
                shareUrl = `https://wa.me/?text=${title}%20${url}`;
                break;
            case 'copy':
                navigator.clipboard.writeText(window.location.href);
                alert('<?= $isNepali ? "लिंक कपी भयो!" : "Link copied!" ?>');
                return;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
