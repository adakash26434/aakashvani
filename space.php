<?php
/**
 * Space Page - Content Collection like OnlineKhabar, Ratopati
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/class.news.php';
require_once __DIR__ . '/includes/helpers.php';

$space = new Space();

// Get space by slug
$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: /');
    exit;
}

$spaceData = $space->getBySlug($slug);
if (!$spaceData) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Increment view count
// $space->incrementViews($spaceData['id']);

// Get articles
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = $spaceData['articles_per_page'] ?? 12;
$result = $space->getSpaceArticlesWithAuto($spaceData['id'], $page, $perPage);
$articles = $result['data'];
$spaceData = $result['space']; // Get full space data

$lang = siteLang();
$isNepali = ($lang !== 'en');
$t = fn($ne, $en) => $isNepali ? $ne : $en;

$pageTitle = $spaceData['meta_title'] ?? ($spaceData['name'] . ' | ' . SITE_NAME);
$metaDesc = $spaceData['meta_description'] ?? $spaceData['description'] ?? '';

include __DIR__ . '/includes/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <?php
    echo getBreadcrumb([
        ['text' => $t('गृह', 'Home'), 'url' => '/'],
        ['text' => $spaceData['name']]
    ]);
    ?>
    
    <!-- Space Header -->
    <div class="relative mb-8 rounded-2xl overflow-hidden">
        <?php if ($spaceData['cover_image']): ?>
            <div class="absolute inset-0">
                <img src="<?= htmlspecialchars($spaceData['cover_image']) ?>" alt="" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
            </div>
        <?php else: ?>
            <div class="absolute inset-0 bg-gradient-to-r from-primary/20 to-primary/5"></div>
        <?php endif; ?>
        
        <div class="relative p-8 md:p-12">
            <div class="flex items-center gap-4 mb-4">
                <?php if ($spaceData['icon']): ?>
                    <span class="text-6xl"><?= $spaceData['icon'] ?></span>
                <?php endif; ?>
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                        <?= htmlspecialchars($spaceData['name']) ?>
                    </h1>
                    <?php if ($spaceData['name_ne']): ?>
                        <p class="text-xl text-white/80"><?= htmlspecialchars($spaceData['name_ne']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($spaceData['description']): ?>
                <p class="text-white/80 text-lg max-w-3xl mb-4">
                    <?= htmlspecialchars($spaceData['description']) ?>
                </p>
            <?php endif; ?>
            
            <div class="flex items-center gap-4 text-white/60 text-sm">
                <span><?= $result['total'] ?> <?= $t('वटा समाचार', 'articles') ?></span>
                <span>•</span>
                <span class="capitalize"><?= $spaceData['layout'] ?> <?= $t('लेआउट', 'layout') ?></span>
            </div>
        </div>
    </div>
    
    <!-- Articles Based on Layout -->
    <?php if (empty($articles)): ?>
        <div class="text-center py-16">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2"><?= $t('कुनै समाचार छैन', 'No articles yet') ?></h2>
            <p class="text-gray-500"><?= $t('यस कोणमा अहिलेसम्म कुनै समाचार प्रकाशित भएको छैन।', 'No articles have been published in this space yet.') ?></p>
        </div>
    <?php elseif ($spaceData['layout'] === 'featured'): ?>
        <!-- Featured Layout - First article big, others grid -->
        <div class="space-y-8">
            <?php if (!empty($articles)): ?>
                <?php $featured = array_shift($articles); ?>
                <article class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-lg border border-gray-200 dark:border-gray-700">
                    <?php if ($featured['featured_image']): ?>
                        <a href="/news-post.php?slug=<?= $featured['slug'] ?>">
                            <div class="aspect-video md:aspect-[2/1] overflow-hidden">
                                <img src="<?= htmlspecialchars($featured['featured_image']) ?>" alt="" 
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                            </div>
                        </a>
                    <?php endif; ?>
                    <div class="p-6 md:p-8">
                        <?php if ($featured['category_name']): ?>
                            <span class="inline-block px-3 py-1 text-sm font-medium rounded-full mb-3" 
                                  style="background: <?= $spaceData['color'] ?? '#16a34a' ?>20; color: <?= $spaceData['color'] ?? '#16a34a' ?>">
                                <?= htmlspecialchars($featured['category_name']) ?>
                            </span>
                        <?php endif; ?>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4 hover:text-primary">
                            <a href="/news-post.php?slug=<?= $featured['slug'] ?>">
                                <?= htmlspecialchars($featured['title_ne'] ?? $featured['title']) ?>
                            </a>
                        </h2>
                        <?php if ($featured['excerpt'] || $featured['excerpt_ne']): ?>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4 line-clamp-3">
                                <?= htmlspecialchars($featured['excerpt_ne'] ?? $featured['excerpt']) ?>
                            </p>
                        <?php endif; ?>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <?php if ($featured['author_name']): ?>
                                <span><?= htmlspecialchars($featured['author_name']) ?></span>
                                <span>•</span>
                            <?php endif; ?>
                            <span><?= timeAgo($featured['published_at']) ?></span>
                            <span>•</span>
                            <span><?= number_format($featured['view_count']) ?> views</span>
                        </div>
                    </div>
                </article>
            <?php endif; ?>
            
            <?php if (!empty($articles)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach (array_slice($articles, 0, 6) as $article): ?>
                        <?= renderArticleCard($article) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php elseif ($spaceData['layout'] === 'magazine'): ?>
        <!-- Magazine Layout - Mixed sizes -->
        <div class="grid grid-cols-12 gap-6">
            <?php $i = 0; foreach ($articles as $article): ?>
                <?php if ($i === 0): ?>
                    <!-- First article - large -->
                    <div class="col-span-12 md:col-span-8">
                        <article class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden h-full">
                            <?php if ($article['featured_image']): ?>
                                <a href="/news-post.php?slug=<?= $article['slug'] ?>">
                                    <div class="aspect-video overflow-hidden">
                                        <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="" class="w-full h-full object-cover">
                                    </div>
                                </a>
                            <?php endif; ?>
                            <div class="p-6">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                    <a href="/news-post.php?slug=<?= $article['slug'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                                </h2>
                                <p class="text-gray-500 mb-3"><?= timeAgo($article['published_at']) ?></p>
                            </div>
                        </article>
                    </div>
                <?php else: ?>
                    <div class="col-span-12 md:col-span-4">
                        <?= renderArticleCard($article, 'list') ?>
                    </div>
                <?php endif; ?>
                <?php $i++; if ($i >= 7) break; endforeach; ?>
        </div>
        
    <?php elseif ($spaceData['layout'] === 'list'): ?>
        <!-- List Layout -->
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
                                <span class="text-xs font-medium" style="color: <?= $spaceData['color'] ?? '#16a34a' ?>">
                                    <?= htmlspecialchars($article['category_name']) ?>
                                </span>
                            <?php endif; ?>
                            <h3 class="font-semibold text-gray-900 dark:text-white hover:text-primary mb-1">
                                <a href="/news-post.php?slug=<?= $article['slug'] ?>">
                                    <?= htmlspecialchars($article['title_ne'] ?? $article['title']) ?>
                                </a>
                            </h3>
                            <?php if ($article['excerpt']): ?>
                                <p class="text-sm text-gray-500 line-clamp-2 mb-2">
                                    <?= htmlspecialchars($article['excerpt_ne'] ?? $article['excerpt']) ?>
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span><?= timeAgo($article['published_at']) ?></span>
                                <span><?= number_format($article['view_count']) ?> views</span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
    <?php elseif ($spaceData['layout'] === 'masonry'): ?>
        <!-- Masonry Layout -->
        <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6">
            <?php foreach ($articles as $article): ?>
                <article class="break-inside-avoid bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <?php if ($article['featured_image']): ?>
                        <a href="/news-post.php?slug=<?= $article['slug'] ?>">
                            <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="" class="w-full">
                        </a>
                    <?php endif; ?>
                    <div class="p-4">
                        <?php if ($article['category_name']): ?>
                            <span class="text-xs font-medium" style="color: <?= $spaceData['color'] ?? '#16a34a' ?>">
                                <?= htmlspecialchars($article['category_name']) ?>
                            </span>
                        <?php endif; ?>
                        <h3 class="font-semibold text-gray-900 dark:text-white hover:text-primary">
                            <a href="/news-post.php?slug=<?= $article['slug'] ?>">
                                <?= htmlspecialchars(truncate($article['title_ne'] ?? $article['title'], 80)) ?>
                            </a>
                        </h3>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <!-- Default Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= $spaceData['columns'] ?? 3 ?> gap-6">
            <?php foreach ($articles as $article): ?>
                <?= renderArticleCard($article) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if ($result['total_pages'] > 1 && $spaceData['show_pagination']): ?>
        <div class="flex justify-center mt-12">
            <div class="flex gap-2">
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
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
