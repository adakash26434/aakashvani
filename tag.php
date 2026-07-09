<?php
/**
 * Tag Page - News by Tag
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/class.news.php';
require_once __DIR__ . '/includes/helpers.php';

$tag = new Tag();
$news = new NewsArticle();

// Get tag by slug
$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: /');
    exit;
}

$tagData = $tag->getBySlug($slug);
if (!$tagData) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Get articles with this tag
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$filters = [
    'tag_id' => $tagData['id'],
    'status' => 'published'
];

$result = $news->getArticles($filters, $page, $perPage);
$articles = $result['data'];

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$pageTitle = '#' . $tagData['name'] . ' | ' . SITE_NAME;

include __DIR__ . '/includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <?php
    echo getBreadcrumb([
        ['text' => $t('गृह', 'Home'), 'url' => '/'],
        ['text' => '#' . $tagData['name']]
    ]);
    ?>
    
    <!-- Tag Header -->
    <div class="bg-gradient-to-r from-indigo-500/10 to-indigo-500/5 rounded-2xl p-6 mb-8">
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-lg" style="background: <?= $tagData['color'] ?? '#6366f1' ?>20; color: <?= $tagData['color'] ?? '#6366f1' ?>">
                #<?= htmlspecialchars($tagData['name']) ?>
            </span>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    #<?= htmlspecialchars($tagData['name']) ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    <?= $result['total'] ?> <?= $t('वटा समाचार', 'articles') ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Articles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($articles as $article): ?>
            <?= renderArticleCard($article) ?>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($articles)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500"><?= $t('यस ट्यागमा कुनै समाचार छैन।', 'No articles with this tag yet.') ?></p>
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
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
