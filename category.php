<?php
/**
 * Category Page - News by Category
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/class.news.php';
require_once __DIR__ . '/includes/helpers.php';

$category = new Category();
$news = new NewsArticle();

// Get category by slug
$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: /');
    exit;
}

$categoryData = $category->getBySlug($slug);
if (!$categoryData) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Get articles
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$filters = [
    'category_id' => $categoryData['id'],
    'status' => 'published'
];

$result = $news->getArticles($filters, $page, $perPage);
$articles = $result['data'];

// Get subcategories
$subcategories = array_filter($category->getAll(), function($c) use ($categoryData) {
    return $c['parent_id'] == $categoryData['id'];
});

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$pageTitle = $categoryData['name'] . ' | ' . SITE_NAME;
$metaDesc = $categoryData['meta_description'] ?? $categoryData['description'] ?? '';

include __DIR__ . '/includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <?php
    // Breadcrumb
    echo getBreadcrumb([
        ['text' => $t('गृह', 'Home'), 'url' => '/'],
        ['text' => $categoryData['name']]
    ]);
    ?>
    
    <!-- Category Header -->
    <div class="bg-gradient-to-r from-primary/10 to-primary/5 rounded-2xl p-6 mb-8">
        <div class="flex items-center gap-4">
            <?php if ($categoryData['icon']): ?>
                <span class="text-4xl"><?= $categoryData['icon'] ?></span>
            <?php endif; ?>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?= htmlspecialchars($categoryData['name']) ?>
                </h1>
                <?php if ($categoryData['name_ne']): ?>
                    <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($categoryData['name_ne']) ?></p>
                <?php endif; ?>
                <?php if ($categoryData['description']): ?>
                    <p class="text-gray-600 dark:text-gray-400 mt-2"><?= htmlspecialchars($categoryData['description']) ?></p>
                <?php endif; ?>
                <p class="text-sm text-gray-500 mt-2"><?= $result['total'] ?> <?= $t('वटा समाचार', 'articles') ?></p>
            </div>
        </div>
    </div>
    
    <!-- Subcategories -->
    <?php if (!empty($subcategories)): ?>
        <div class="flex flex-wrap gap-2 mb-8">
            <?php foreach ($subcategories as $sub): ?>
                <a href="/category.php?slug=<?= $sub['slug'] ?>" 
                   class="px-4 py-2 bg-white dark:bg-gray-800 rounded-full border border-gray-200 dark:border-gray-700 hover:border-primary transition-colors">
                    <?= htmlspecialchars($sub['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Articles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($articles as $article): ?>
            <?= renderArticleCard($article) ?>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($articles)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500"><?= $t('यस कोटीमा कुनै समाचार छैन।', 'No articles in this category yet.') ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if ($result['total_pages'] > 1): ?>
        <div class="flex justify-center gap-2 mt-8">
            <?php foreach (paginate($result['total'], $result['total_pages'], $page) as $item): ?>
                <?php if ($item['type'] === 'ellipsis'): ?>
                    <span class="px-3 py-2 text-gray-400">...</span>
                <?php elseif ($item['type'] === 'page'): ?>
                    <a href="?slug=<?= $slug ?>&page=<?= $item['page'] ?>"
                       class="px-4 py-2 rounded-lg <?= $item['current'] ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                        <?= $item['page'] ?>
                    </a>
                <?php elseif ($item['type'] === 'prev'): ?>
                    <a href="?slug=<?= $slug ?>&page=<?= $item['page'] ?>"
                       class="px-4 py-2 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">←</a>
                <?php elseif ($item['type'] === 'next'): ?>
                    <a href="?slug=<?= $slug ?>&page=<?= $item['page'] ?>"
                       class="px-4 py-2 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">→</a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Sidebar -->
    <aside class="mt-12">
        <h3 class="text-xl font-bold mb-4"><?= $t('लोकप्रिय ट्यागहरू', 'Popular Tags') ?></h3>
        <?= renderPopularTags() ?>
    </aside>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
