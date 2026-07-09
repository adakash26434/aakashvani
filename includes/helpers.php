<?php
/**
 * News Portal Helper Functions
 */

// CSRF Protection
function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitization
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Truncate text
function truncate(string $text, int $length = 100, string $suffix = '...'): string {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

// Pagination
function paginate(int $total, int $totalPages, int $currentPage, array $params = []): array {
    $items = [];
    $delta = 2;
    
    if ($currentPage > 1) {
        $items[] = ['type' => 'prev', 'page' => $currentPage - 1];
    }
    
    if ($currentPage - $delta > 1) {
        $items[] = ['type' => 'page', 'page' => 1, 'current' => false];
    }
    
    if ($currentPage - $delta > 2) {
        $items[] = ['type' => 'ellipsis'];
    }
    
    for ($i = max(1, $currentPage - $delta); $i <= min($totalPages, $currentPage + $delta); $i++) {
        $items[] = ['type' => 'page', 'page' => $i, 'current' => $i === $currentPage];
    }
    
    if ($currentPage + $delta < $totalPages - 1) {
        $items[] = ['type' => 'ellipsis'];
    }
    
    if ($currentPage + $delta < $totalPages) {
        $items[] = ['type' => 'page', 'page' => $totalPages, 'current' => false];
    }
    
    if ($currentPage < $totalPages) {
        $items[] = ['type' => 'next', 'page' => $currentPage + 1];
    }
    
    return $items;
}

// Activity Logging
function logActivity(int $userId, string $action, string $entityType, int $entityId, string $entityTitle): void {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sql = "INSERT INTO aak_activity_log (user_id, action, entity_type, entity_id, entity_title, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $pdo->prepare($sql)->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $entityTitle,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log('Activity Log Error: ' . $e->getMessage());
    }
}

// Date formatting
function formatDate(string $date, string $format = 'M j, Y'): string {
    return date($format, strtotime($date));
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M j', $time);
}

// Get homepage sections with their content
function getHomepageSections(): array {
    static $sections = null;
    
    if ($sections !== null) return $sections;
    
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sections = $pdo->query("SELECT hs.*, c.name as category_name 
                                  FROM aak_homepage_sections hs 
                                  LEFT JOIN aak_categories c ON hs.category_id = c.id 
                                  WHERE hs.is_active = 1 
                                  ORDER BY hs.sort_order ASC")->fetchAll();
        
        // Load articles for each section
        $news = new NewsArticle($pdo);
        
        foreach ($sections as &$section) {
            $section['articles'] = getSectionArticles($section, $news);
        }
        
    } catch (Exception $e) {
        error_log('Homepage Sections Error: ' . $e->getMessage());
        $sections = [];
    }
    
    return $sections ?? [];
}

function getSectionArticles(array $section, NewsArticle $news): array {
    $limit = $section['max_items'] ?? 10;
    $type = $section['type'] ?? 'latest';
    
    switch ($type) {
        case 'featured':
            return $news->getFeatured($limit);
        case 'breaking':
            return $news->getBreaking($limit);
        case 'trending':
            return $news->getTrending($limit);
        case 'most_viewed':
            return $news->getMostViewed($limit);
        case 'editors_pick':
            return $news->getEditorsPick($limit);
        case 'category':
            return $news->getLatest($limit, $section['category_id']);
        default:
            return $news->getLatest($limit);
    }
}

// Get breadcrumb
function getBreadcrumb(array $items): string {
    $html = '<nav class="flex items-center gap-2 text-sm text-gray-500 mb-4">';
    
    foreach ($items as $i => $item) {
        if ($i > 0) {
            $html .= '<span class="text-gray-400">/</span>';
        }
        
        if (isset($item['url']) && $i < count($items) - 1) {
            $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="hover:text-primary">' 
                   . htmlspecialchars($item['text']) . '</a>';
        } else {
            $html .= '<span class="text-gray-700 dark:text-gray-300">' 
                   . htmlspecialchars($item['text']) . '</span>';
        }
    }
    
    $html .= '</nav>';
    return $html;
}

// Render article card
function renderArticleCard(array $article, string $style = 'grid'): string {
    $title = $article['title_ne'] ?? $article['title'] ?? 'Untitled';
    $excerpt = $article['excerpt_ne'] ?? $article['excerpt'] ?? '';
    $url = '/news-post.php?slug=' . ($article['slug'] ?? '');
    $image = $article['featured_image'] ?? '';
    $category = $article['category_name'] ?? '';
    $date = $article['published_at'] ? timeAgo($article['published_at']) : '';
    
    if ($style === 'list') {
        return '
            <div class="flex gap-4 p-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors">
                ' . ($image ? '<a href="' . $url . '"><img src="' . htmlspecialchars($image) . '" alt="" class="w-24 h-20 object-cover rounded-lg flex-shrink-0"></a>' : '') . '
                <div class="flex-1 min-w-0">
                    ' . ($category ? '<span class="text-xs text-primary font-medium">' . htmlspecialchars($category) . '</span>' : '') . '
                    <h3 class="font-medium text-gray-900 dark:text-white hover:text-primary">
                        <a href="' . $url . '">' . truncate(htmlspecialchars($title), 80) . '</a>
                    </h3>
                    ' . ($excerpt ? '<p class="text-sm text-gray-500 mt-1 line-clamp-2">' . truncate(htmlspecialchars(strip_tags($excerpt)), 100) . '</p>' : '') . '
                    ' . ($date ? '<span class="text-xs text-gray-400 mt-1">' . $date . '</span>' : '') . '
                </div>
            </div>';
    }
    
    // Grid style (default)
    return '
        <article class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
            ' . ($image ? '
                <a href="' . $url . '">
                    <div class="aspect-video overflow-hidden">
                        <img src="' . htmlspecialchars($image) . '" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                </a>
            ' : '') . '
            <div class="p-4">
                ' . ($category ? '<span class="text-xs text-primary font-medium">' . htmlspecialchars($category) . '</span>' : '') . '
                <h3 class="font-semibold text-gray-900 dark:text-white mt-1 hover:text-primary">
                    <a href="' . $url . '">' . truncate(htmlspecialchars($title), 70) . '</a>
                </h3>
                ' . ($excerpt ? '<p class="text-sm text-gray-500 mt-2 line-clamp-2">' . truncate(htmlspecialchars(strip_tags($excerpt)), 100) . '</p>' : '') . '
                <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                    ' . ($date ? '<span>' . $date . '</span>' : '<span></span>') . '
                    ' . (isset($article['view_count']) ? '<span>👁 ' . number_format($article['view_count']) . '</span>' : '') . '
                </div>
            </div>
        </article>';
}

// Render breaking news ticker
function renderBreakingNews(array $articles): string {
    if (empty($articles)) return '';
    
    $items = array_map(function($a) {
        return '<a href="/news-post.php?slug=' . ($a['slug'] ?? '') . '" class="hover:text-primary">' 
             . htmlspecialchars($a['title'] ?? '') . '</a>';
    }, $articles);
    
    return '
        <div class="bg-red-600 text-white py-2 overflow-hidden">
            <div class="flex items-center">
                <span class="px-4 py-1 bg-white text-red-600 font-bold text-sm flex-shrink-0">BREAKING</span>
                <div class="flex-1 overflow-hidden">
                    <div class="flex animate-marquee">
                        ' . implode('<span class="mx-4 text-red-200">|</span>', $items) . '
                        ' . implode('<span class="mx-4 text-red-200">|</span>', $items) . '
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
            .animate-marquee { animation: marquee 30s linear infinite; display: flex; }
        </style>';
}

// Render category list
function renderCategoryList(array $categories, int $parentId = 0, string $prefix = ''): string {
    $html = '';
    
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parentId) {
            $html .= '<li>';
            $html .= '<a href="/category.php?slug=' . ($cat['slug'] ?? '') . '" class="flex items-center justify-between px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg">';
            $html .= '<span>' . $prefix . htmlspecialchars($cat['name'] ?? '') . '</span>';
            if (!empty($cat['children'])) {
                $html .= '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
            }
            $html .= '</a>';
            
            if (!empty($cat['children'])) {
                $html .= '<ul class="pl-4 mt-1">' . renderCategoryList($categories, $cat['id'], $prefix . '— ') . '</ul>';
            }
            
            $html .= '</li>';
        }
    }
    
    return $html;
}

// Popular tags
function renderPopularTags(int $limit = 20): string {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
        $tags = $pdo->query("SELECT * FROM aak_tags WHERE is_active = 1 ORDER BY use_count DESC LIMIT {$limit}")->fetchAll();
        
        $html = '<div class="flex flex-wrap gap-2">';
        foreach ($tags as $tag) {
            $html .= '<a href="/tag.php?slug=' . ($tag['slug'] ?? '') . '" 
                      class="px-3 py-1 text-sm rounded-full hover:opacity-80 transition-opacity"
                      style="background: ' . ($tag['color'] ?? '#6366f1') . '20; color: ' . ($tag['color'] ?? '#6366f1') . '">'
                   . '#' . htmlspecialchars($tag['name'] ?? '') . '</a>';
        }
        $html .= '</div>';
        
        return $html;
    } catch (Exception $e) {
        return '';
    }
}

// Reading time badge
function getReadingTime(string $content): int {
    $text = strip_tags($content);
    $words = str_word_count($text);
    return max(1, ceil($words / 200));
}

// SEO Meta Tags
function renderSEOTags(array $data): string {
    $html = '';
    
    $title = $data['meta_title'] ?? $data['title'] ?? '';
    $description = $data['meta_description'] ?? $data['excerpt'] ?? '';
    $image = $data['og_image'] ?? $data['featured_image'] ?? '';
    $url = $data['url'] ?? '';
    $type = $data['type'] ?? 'article';
    
    if ($title) {
        $html .= '<title>' . htmlspecialchars($title) . '</title>';
        $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">';
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">';
    }
    
    if ($description) {
        $html .= '<meta name="description" content="' . htmlspecialchars(truncate($description, 160)) . '">';
        $html .= '<meta property="og:description" content="' . htmlspecialchars(truncate($description, 160)) . '">';
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars(truncate($description, 160)) . '">';
    }
    
    if ($image) {
        $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">';
        $html .= '<meta name="twitter:image" content="' . htmlspecialchars($image) . '">';
        $html .= '<meta name="twitter:card" content="summary_large_image">';
    }
    
    if ($url) {
        $html .= '<meta property="og:url" content="' . htmlspecialchars($url) . '">';
    }
    
    $html .= '<meta property="og:type" content="' . $type . '">';
    $html .= '<meta property="og:site_name" content="' . htmlspecialchars(SITE_NAME ?? 'Aakashvani') . '">';
    
    return $html;
}

// Admin sidebar active state
function isActive(string $path): string {
    return strpos($_SERVER['REQUEST_URI'] ?? '', $path) !== false ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700';
}

// Mobile menu item
function isActiveMobile(string $path): string {
    return strpos($_SERVER['REQUEST_URI'] ?? '', $path) !== false ? 'text-primary' : 'text-gray-500';
}

// ============================================
// SPACE & HOMEPAGE SECTION RENDERERS
// ============================================

/**
 * Render homepage sections from database
 */
function renderHomepageSections(): string {
    $html = '';
    
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $news = new NewsArticle($pdo);
        $space = new Space($pdo);
        
        // Get active spaces for homepage
        $spaces = $space->getHomepageSpaces();
        
        foreach ($spaces as $spaceData) {
            $articles = $space->getSpaceArticlesWithAuto($spaceData['id'], 1, $spaceData['max_articles'] ?? 12);
            $articles = $articles['data'] ?? [];
            
            if (empty($articles) && $spaceData['layout'] !== 'popular_tags') continue;
            
            $html .= renderSpaceSection($spaceData, $articles);
        }
        
    } catch (Exception $e) {
        error_log('Homepage Sections Error: ' . $e->getMessage());
    }
    
    return $html;
}

/**
 * Render a single space/section
 */
function renderSpaceSection(array $space, array $articles): string {
    $title = $space['name'] ?? '';
    $titleNe = $space['name_ne'] ?? '';
    $icon = $space['icon'] ?? '';
    $color = $space['color'] ?? '#16a34a';
    $layout = $space['layout'] ?? 'grid';
    $columns = $space['columns'] ?? 3;
    $slug = $space['slug'] ?? '';
    $showTitle = $space['show_title'] ?? true;
    $showExcerpt = $space['show_excerpt'] ?? false;
    $showDate = $space['show_date'] ?? true;
    $showViews = $space['show_views'] ?? false;
    $showCategory = $space['show_category'] ?? true;
    
    $isNepali = (siteLang() !== 'en');
    $displayTitle = $isNepali && $titleNe ? $titleNe : $title;
    
    $html = '<section class="py-8">';
    $html .= '<div class="container mx-auto px-4">';
    
    // Section Header
    if ($showTitle) {
        $html .= '<div class="flex items-center justify-between mb-6">';
        $html .= '<div class="flex items-center gap-3">';
        if ($icon) $html .= '<span class="text-2xl">' . $icon . '</span>';
        $html .= '<h2 class="text-2xl font-bold text-gray-900 dark:text-white">' . htmlspecialchars($displayTitle) . '</h2>';
        $html .= '</div>';
        $html .= '<a href="/space.php?slug=' . htmlspecialchars($slug) . '" class="text-primary hover:underline text-sm font-medium">';
        $html .= $isNepali ? 'सबै हेर्नुहोस् →' : 'View All →';
        $html .= '</a>';
        $html .= '</div>';
    }
    
    // Content based on layout
    switch ($layout) {
        case 'featured':
            $html .= renderFeaturedLayout($articles, $color, $showExcerpt, $showDate, $showViews, $showCategory);
            break;
        case 'magazine':
            $html .= renderMagazineLayout($articles, $color, $showExcerpt, $showDate, $showCategory);
            break;
        case 'list':
            $html .= renderListLayout($articles, $color, $showDate, $showViews, $showCategory);
            break;
        case 'carousel':
            $html .= renderCarouselLayout($articles, $color, $showExcerpt, $showDate, $showCategory);
            break;
        case 'popular_tags':
            $html .= renderPopularTagsSection();
            break;
        default: // grid
            $html .= renderGridLayout($articles, $columns, $color, $showExcerpt, $showDate, $showViews, $showCategory);
    }
    
    $html .= '</div>';
    $html .= '</section>';
    
    return $html;
}

/**
 * Grid layout
 */
function renderGridLayout(array $articles, int $columns, string $color, bool $showExcerpt, bool $showDate, bool $showViews, bool $showCategory): string {
    $colsClass = match($columns) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
    };
    
    $html = '<div class="grid ' . $colsClass . ' gap-6">';
    
    foreach (array_slice($articles, 0, $columns * 4) as $article) {
        $html .= renderArticleCardGrid($article, $color, $showExcerpt, $showDate, $showViews, $showCategory);
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Featured layout - big first article
 */
function renderFeaturedLayout(array $articles, string $color, bool $showExcerpt, bool $showDate, bool $showViews, bool $showCategory): string {
    if (empty($articles)) return '';
    
    $html = '<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">';
    
    // Featured article (large)
    $featured = array_shift($articles);
    $html .= '<div class="lg:col-span-2">';
    $html .= renderFeaturedArticle($featured, $color, $showExcerpt, $showDate, $showViews);
    $html .= '</div>';
    
    // Side articles
    $html .= '<div class="space-y-4">';
    foreach (array_slice($articles, 0, 3) as $article) {
        $html .= renderCompactArticle($article, $color, $showDate);
    }
    $html .= '</div>';
    
    $html .= '</div>';
    return $html;
}

/**
 * Magazine layout - mixed sizes
 */
function renderMagazineLayout(array $articles, string $color, bool $showExcerpt, bool $showDate, bool $showCategory): string {
    $html = '<div class="grid grid-cols-12 gap-6">';
    
    $i = 0;
    foreach (array_slice($articles, 0, 7) as $article) {
        if ($i === 0) {
            $html .= '<div class="col-span-12 md:col-span-8">';
            $html .= renderFeaturedArticle($article, $color, $showExcerpt, $showDate, false);
            $html .= '</div>';
        } else {
            $html .= '<div class="col-span-12 md:col-span-4">';
            $html .= renderCompactArticle($article, $color, $showDate);
            $html .= '</div>';
        }
        $i++;
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * List layout - compact horizontal cards
 */
function renderListLayout(array $articles, string $color, bool $showDate, bool $showViews, bool $showCategory): string {
    $html = '<div class="space-y-4">';
    
    foreach (array_slice($articles, 0, 10) as $article) {
        $html .= renderListArticle($article, $color, $showDate, $showViews, $showCategory);
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Carousel layout
 */
function renderCarouselLayout(array $articles, string $color, bool $showExcerpt, bool $showDate, bool $showCategory): string {
    if (empty($articles)) return '';
    
    $html = '<div class="relative overflow-hidden">';
    $html .= '<div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide" id="carousel-' . uniqid() . '">';
    
    foreach ($articles as $article) {
        $html .= '<div class="flex-shrink-0 w-72">';
        $html .= renderArticleCardGrid($article, $color, $showExcerpt, $showDate, false, $showCategory);
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * Featured article (large card)
 */
function renderFeaturedArticle(array $article, string $color, bool $showExcerpt, bool $showDate, bool $showViews): string {
    $title = $article['title_ne'] ?? $article['title'] ?? '';
    $excerpt = $article['excerpt_ne'] ?? $article['excerpt'] ?? '';
    $slug = $article['slug'] ?? '';
    $image = $article['featured_image'] ?? '';
    $category = $article['category_name'] ?? '';
    
    $html = '<article class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700 h-full">';
    
    if ($image) {
        $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '">';
        $html .= '<div class="aspect-video overflow-hidden">';
        $html .= '<img src="' . htmlspecialchars($image) . '" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">';
        $html .= '</div>';
        $html .= '</a>';
    }
    
    $html .= '<div class="p-6">';
    
    if ($category) {
        $html .= '<span class="text-xs font-medium" style="color: ' . htmlspecialchars($color) . '">' . htmlspecialchars($category) . '</span>';
    }
    
    $html .= '<h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mt-2 mb-3 hover:text-primary">';
    $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '">' . htmlspecialchars(truncate($title, 80)) . '</a>';
    $html .= '</h3>';
    
    if ($showExcerpt && $excerpt) {
        $html .= '<p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">' . htmlspecialchars(strip_tags($excerpt)) . '</p>';
    }
    
    $html .= '<div class="flex items-center gap-3 text-xs text-gray-400">';
    if ($showDate && !empty($article['published_at'])) {
        $html .= '<span>' . timeAgo($article['published_at']) . '</span>';
    }
    if ($showViews && !empty($article['view_count'])) {
        $html .= '<span>👁 ' . number_format($article['view_count']) . '</span>';
    }
    $html .= '</div>';
    
    $html .= '</div></article>';
    
    return $html;
}

/**
 * Compact article (small horizontal card)
 */
function renderCompactArticle(array $article, string $color, bool $showDate): string {
    $title = $article['title_ne'] ?? $article['title'] ?? '';
    $slug = $article['slug'] ?? '';
    $image = $article['featured_image'] ?? '';
    $category = $article['category_name'] ?? '';
    
    $html = '<article class="flex gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">';
    
    if ($image) {
        $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '" class="flex-shrink-0">';
        $html .= '<img src="' . htmlspecialchars($image) . '" alt="" class="w-20 h-16 object-cover rounded-lg">';
        $html .= '</a>';
    }
    
    $html .= '<div class="flex-1 min-w-0">';
    
    if ($category) {
        $html .= '<span class="text-xs font-medium" style="color: ' . htmlspecialchars($color) . '">' . htmlspecialchars($category) . '</span>';
    }
    
    $html .= '<h4 class="font-medium text-sm text-gray-900 dark:text-white hover:text-primary line-clamp-2 mt-1">';
    $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '">' . htmlspecialchars(truncate($title, 60)) . '</a>';
    $html .= '</h4>';
    
    if ($showDate && !empty($article['published_at'])) {
        $html .= '<span class="text-xs text-gray-400 mt-1 block">' . timeAgo($article['published_at']) . '</span>';
    }
    
    $html .= '</div></article>';
    
    return $html;
}

/**
 * List article (horizontal card)
 */
function renderListArticle(array $article, string $color, bool $showDate, bool $showViews, bool $showCategory): string {
    $title = $article['title_ne'] ?? $article['title'] ?? '';
    $excerpt = $article['excerpt_ne'] ?? $article['excerpt'] ?? '';
    $slug = $article['slug'] ?? '';
    $image = $article['featured_image'] ?? '';
    $category = $article['category_name'] ?? '';
    
    $html = '<article class="flex gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">';
    
    if ($image) {
        $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '" class="flex-shrink-0">';
        $html .= '<img src="' . htmlspecialchars($image) . '" alt="" class="w-32 h-24 object-cover rounded-lg">';
        $html .= '</a>';
    }
    
    $html .= '<div class="flex-1 min-w-0">';
    
    $html .= '<div class="flex items-center gap-2 mb-1">';
    if ($showCategory && $category) {
        $html .= '<span class="text-xs font-medium px-2 py-0.5 rounded" style="background: ' . htmlspecialchars($color) . '20; color: ' . htmlspecialchars($color) . '">' . htmlspecialchars($category) . '</span>';
    }
    if ($showDate && !empty($article['published_at'])) {
        $html .= '<span class="text-xs text-gray-400">' . timeAgo($article['published_at']) . '</span>';
    }
    $html .= '</div>';
    
    $html .= '<h3 class="font-semibold text-gray-900 dark:text-white hover:text-primary mb-2">';
    $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '">' . htmlspecialchars(truncate($title, 70)) . '</a>';
    $html .= '</h3>';
    
    if ($excerpt) {
        $html .= '<p class="text-sm text-gray-500 line-clamp-2">' . htmlspecialchars(strip_tags($excerpt)) . '</p>';
    }
    
    $html .= '</div></article>';
    
    return $html;
}

/**
 * Grid article card
 */
function renderArticleCardGrid(array $article, string $color, bool $showExcerpt, bool $showDate, bool $showViews, bool $showCategory): string {
    $title = $article['title_ne'] ?? $article['title'] ?? '';
    $excerpt = $article['excerpt_ne'] ?? $article['excerpt'] ?? '';
    $slug = $article['slug'] ?? '';
    $image = $article['featured_image'] ?? '';
    $category = $article['category_name'] ?? '';
    
    $html = '<article class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow h-full flex flex-col">';
    
    if ($image) {
        $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '" class="block">';
        $html .= '<div class="aspect-video overflow-hidden">';
        $html .= '<img src="' . htmlspecialchars($image) . '" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">';
        $html .= '</div>';
        $html .= '</a>';
    }
    
    $html .= '<div class="p-4 flex-1 flex flex-col">';
    
    if ($showCategory && $category) {
        $html .= '<span class="text-xs font-medium mb-2" style="color: ' . htmlspecialchars($color) . '">' . htmlspecialchars($category) . '</span>';
    }
    
    $html .= '<h3 class="font-semibold text-gray-900 dark:text-white hover:text-primary flex-1">';
    $html .= '<a href="/news-post.php?slug=' . htmlspecialchars($slug) . '">' . htmlspecialchars(truncate($title, 60)) . '</a>';
    $html .= '</h3>';
    
    if ($showExcerpt && $excerpt) {
        $html .= '<p class="text-sm text-gray-500 mt-2 line-clamp-2">' . htmlspecialchars(strip_tags($excerpt)) . '</p>';
    }
    
    $html .= '<div class="flex items-center gap-2 text-xs text-gray-400 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">';
    if ($showDate && !empty($article['published_at'])) {
        $html .= '<span>' . timeAgo($article['published_at']) . '</span>';
    }
    if ($showViews && !empty($article['view_count'])) {
        $html .= '<span>👁 ' . number_format($article['view_count']) . '</span>';
    }
    $html .= '</div>';
    
    $html .= '</div></article>';
    
    return $html;
}

/**
 * Popular tags section
 */
function renderPopularTagsSection(): string {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
        $tags = $pdo->query("SELECT * FROM aak_tags WHERE is_active = 1 ORDER BY use_count DESC LIMIT 30")->fetchAll();
        
        if (empty($tags)) return '';
        
        $html = '<div class="flex flex-wrap gap-2">';
        foreach ($tags as $tag) {
            $html .= '<a href="/tag.php?slug=' . htmlspecialchars($tag['slug']) . '" 
                      class="px-4 py-2 rounded-full text-sm font-medium transition-opacity hover:opacity-80"
                      style="background: ' . htmlspecialchars($tag['color'] ?? '#6366f1') . '20; color: ' . htmlspecialchars($tag['color'] ?? '#6366f1') . '">';
            $html .= '#' . htmlspecialchars($tag['name']);
            $html .= '</a>';
        }
        $html .= '</div>';
        
        return $html;
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Get spaces for navigation menu
 */
function getNavSpaces(): array {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
        return $pdo->query("SELECT name, name_ne, slug, icon FROM aak_spaces WHERE is_active = 1 AND show_in_menu = 1 ORDER BY sort_order ASC LIMIT 10")->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
