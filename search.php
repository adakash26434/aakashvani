<?php
/**
 * Search Page - Public search for news articles
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/class.news.php';
require_once __DIR__ . '/includes/helpers.php';

$news = new NewsArticle();
$category = new Category();

$query = sanitize($_GET['q'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0) ?: null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

if ($query) {
    $filters = [
        'search' => $query,
        'status' => 'published'
    ];
    if ($categoryFilter) {
        $filters['category_id'] = $categoryFilter;
    }
    $result = $news->getArticles($filters, $page, $perPage);
    $articles = $result['data'];
} else {
    $articles = [];
    $result = ['total' => 0, 'total_pages' => 0, 'page' => 1];
}

$categories = $category->getAll();
$pageTitle = $query ? "Search: {$query}" : 'Search';

include __DIR__ . '/includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <?php
    echo getBreadcrumb([
        ['text' => $t('गृह', 'Home'), 'url' => '/'],
        ['text' => $t('खोज', 'Search')]
    ]);
    ?>
    
    <!-- Search Form -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 mb-8 border border-gray-200 dark:border-gray-700">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                       placeholder="<?= $t('के खोज्नुहुन्छ?', 'What are you looking for?') ?>"
                       class="w-full px-5 py-3 pl-12 text-lg border-2 border-gray-200 dark:border-gray-600 rounded-xl dark:bg-gray-700 focus:border-primary focus:outline-none"
                       autofocus>
                <svg class="w-6 h-6 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <select name="category" class="px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl dark:bg-gray-700">
                <option value=""><?= $t('सबै कोटीहरू', 'All Categories') ?></option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-xl transition-colors">
                <?= $t('खोज्नुहोस्', 'Search') ?>
            </button>
        </form>
    </div>
    
    <?php if ($query): ?>
        <!-- Results Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?= $t('खोज परिणामहरू', 'Search Results') ?>
                </h1>
                <p class="text-gray-500 mt-1">
                    <?= number_format($result['total']) ?> <?= $t('वटा परिणाम भेटियो', 'results found') ?>
                    "<?= htmlspecialchars($query) ?>"
                </p>
            </div>
        </div>
        
        <?php if (empty($articles)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center border border-gray-200 dark:border-gray-700">
                <div class="text-6xl mb-4">🔍</div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    <?= $t('केही भेटिएन', 'Nothing found') ?>
                </h2>
                <p class="text-gray-500 max-w-md mx-auto">
                    <?= $t('तपाईंको खोजसँग मिल्ने कुनै समाचार भेटिएन। अर्को शब्द प्रयास गर्नुहोस्।', 'No articles match your search. Try different keywords.') ?>
                </p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($articles as $article): ?>
                    <article class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                        <div class="flex gap-4">
                            <?php if ($article['featured_image']): ?>
                                <a href="/news-post.php?slug=<?= $article['slug'] ?>" class="flex-shrink-0">
                                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="" 
                                         class="w-32 h-24 object-cover rounded-lg">
                                </a>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <?php if ($article['category_name']): ?>
                                    <span class="text-xs font-medium text-primary"><?= htmlspecialchars($article['category_name']) ?></span>
                                <?php endif; ?>
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-white hover:text-primary mb-1">
                                    <a href="/news-post.php?slug=<?= $article['slug'] ?>">
                                        <?= htmlspecialchars($article['title_ne'] ?? $article['title']) ?>
                                    </a>
                                </h3>
                                <?php if ($article['excerpt']): ?>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 mb-2">
                                        <?= htmlspecialchars(strip_tags($article['excerpt_ne'] ?? $article['excerpt'])) ?>
                                    </p>
                                <?php endif; ?>
                                <div class="flex items-center gap-3 text-xs text-gray-400">
                                    <span><?= timeAgo($article['published_at']) ?></span>
                                    <span>•</span>
                                    <span><?= number_format($article['view_count']) ?> views</span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <?php if ($result['total_pages'] > 1): ?>
                <div class="flex justify-center gap-2 mt-8">
                    <?php foreach (paginate($result['total'], $result['total_pages'], $page) as $item): ?>
                        <?php if ($item['type'] === 'ellipsis'): ?>
                            <span class="px-3 py-2 text-gray-400">...</span>
                        <?php elseif ($item['type'] === 'page'): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                               class="px-4 py-2 rounded-lg <?= $item['current'] ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800' ?>">
                                <?= $item['page'] ?>
                            </a>
                        <?php elseif ($item['type'] === 'prev'): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                               class="px-4 py-2 bg-white dark:bg-gray-800 rounded-lg">←</a>
                        <?php elseif ($item['type'] === 'next'): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $item['page']])) ?>"
                               class="px-4 py-2 bg-white dark:bg-gray-800 rounded-lg">→</a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Search Tips -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><?= $t('खोज सुझावहरू', 'Search Tips') ?></h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="text-center p-4">
                    <div class="text-3xl mb-2">💡</div>
                    <h3 class="font-medium mb-1"><?= $t('कीवर्ड प्रयोग गर्नुहोस्', 'Use Keywords') ?></h3>
                    <p class="text-sm text-gray-500"><?= $t('स्पष्ट र छोटो कीवर्ड प्रयोग गर्नुहोस्', 'Use clear and short keywords') ?></p>
                </div>
                <div class="text-center p-4">
                    <div class="text-3xl mb-2">📂</div>
                    <h3 class="font-medium mb-1"><?= $t('कोटी छान्नुहोस्', 'Select Category') ?></h3>
                    <p class="text-sm text-gray-500"><?= $t('निश्चित कोटीमा खोज्नुहोस्', 'Search in specific category') ?></p>
                </div>
                <div class="text-center p-4">
                    <div class="text-3xl mb-2">🔄</div>
                    <h3 class="font-medium mb-1"><?= $t('अर्को शब्द', 'Try Again') ?></h3>
                    <p class="text-sm text-gray-500"><?= $t('नमिलेको खण्डमा अर्को शब्द प्रयोग गर्नुहोस्', 'Try different words if not found') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
