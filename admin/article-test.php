<?php
/**
 * Article Content Test Utility
 * Test if full article fetching is working correctly
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
requireAdmin();

$testResults = [];
$testUrl = $_POST['test_url'] ?? '';
$fetchArticleId = $_POST['fetch_id'] ?? '';

// Test single article fetch
if ($testUrl && filter_var($testUrl, FILTER_VALIDATE_URL)) {
    require_once __DIR__ . '/../includes/article-fetch.php';
    $start = microtime(true);
    $result = aakFetchArticle($testUrl, 60); // 1 minute cache for testing
    $time = round((microtime(true) - $start) * 1000, 2);
    
    $testResults = [
        'url' => $testUrl,
        'time_ms' => $time,
        'paragraphs' => count($result['paragraphs'] ?? []),
        'chars' => mb_strlen($result['plain'] ?? ''),
        'content' => $result['plain'] ?? '',
        'source' => $result['source'] ?? 'none',
        'success' => !empty($result['plain']) && mb_strlen($result['plain']) > 200
    ];
}

// Manual fetch for article by ID
if ($fetchArticleId && is_numeric($fetchArticleId)) {
    try {
        $stmt = db()->prepare("SELECT * FROM tech_news WHERE id = ? LIMIT 1");
        $stmt->execute([$fetchArticleId]);
        $article = $stmt->fetch();
        
        if ($article) {
            require_once __DIR__ . '/../includes/article-fetch.php';
            $fetched = aakFetchArticle($article['original_url'], 60);
            $scraped = trim($fetched['plain'] ?? implode("\n\n", $fetched['paragraphs'] ?? []));
            
            if (mb_strlen($scraped) > 300) {
                $up = db()->prepare("UPDATE tech_news SET content = ?, ai_processed = 1, updated_at = NOW() WHERE id = ?");
                $up->execute([$scraped, $fetchArticleId]);
                $message = "Article #{$fetchArticleId} updated with " . mb_strlen($scraped) . " characters!";
            } else {
                $error = "Could not fetch sufficient content (only " . mb_strlen($scraped) . " chars)";
            }
        } else {
            $error = "Article not found";
        }
    } catch (Throwable $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get recent articles with short content
$shortArticles = [];
try {
    $stmt = db()->prepare("SELECT id, title, source_name, original_url, LENGTH(content) as content_len, created_at 
                          FROM tech_news 
                          WHERE is_published = 1 
                          ORDER BY created_at DESC 
                          LIMIT 20");
    $stmt->execute();
    $shortArticles = $stmt->fetchAll();
} catch (Throwable $e) {}

$pageTitle = 'Article Content Test | Admin';
require_once __DIR__ . '/admin-header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-8">
  <div class="flex items-center gap-3 mb-6">
    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
      <i data-lucide="file-text" class="w-5 h-5"></i>
    </div>
    <div>
      <h1 class="text-xl font-bold text-slate-900">Article Content Test</h1>
      <p class="text-sm text-slate-500">Test full article fetching and fix short content</p>
    </div>
  </div>

  <?php if (!empty($message)): ?>
  <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
    <p class="text-emerald-800"><?= htmlspecialchars($message) ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
  <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
    <p class="text-red-800"><?= htmlspecialchars($error) ?></p>
  </div>
  <?php endif; ?>

  <!-- Test Single URL -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i data-lucide="test-tube" class="w-4 h-4 text-slate-400"></i>
      Test Article Fetch
    </h2>
    <form method="POST" class="flex gap-3">
      <input type="url" name="test_url" placeholder="https://www.onlinekhabar.com/..." 
             class="flex-1 px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-blue-500"
             value="<?= htmlspecialchars($testUrl) ?>" required>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg">
        Test Fetch
      </button>
    </form>

    <?php if ($testResults): ?>
    <div class="mt-4 p-4 rounded-lg <?= $testResults['success'] ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' ?>">
      <div class="flex items-center gap-2 mb-2">
        <?php if ($testResults['success']): ?>
          <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
          <span class="font-semibold text-emerald-800">Success!</span>
        <?php else: ?>
          <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600"></i>
          <span class="font-semibold text-amber-800">Limited Content</span>
        <?php endif; ?>
      </div>
      <div class="grid grid-cols-3 gap-4 text-sm mb-3">
        <div class="bg-white rounded p-2">
          <div class="text-slate-500">Paragraphs</div>
          <div class="font-semibold text-slate-900"><?= $testResults['paragraphs'] ?></div>
        </div>
        <div class="bg-white rounded p-2">
          <div class="text-slate-500">Characters</div>
          <div class="font-semibold text-slate-900"><?= number_format($testResults['chars']) ?></div>
        </div>
        <div class="bg-white rounded p-2">
          <div class="text-slate-500">Time</div>
          <div class="font-semibold text-slate-900"><?= $testResults['time_ms'] ?>ms</div>
        </div>
      </div>
      <?php if ($testResults['content']): ?>
      <div class="bg-white rounded p-3 max-h-48 overflow-y-auto">
        <div class="text-xs text-slate-500 mb-1">Preview (first 500 chars):</div>
        <div class="text-sm text-slate-800">
          <?= htmlspecialchars(mb_substr($testResults['content'], 0, 500)) ?><?= mb_strlen($testResults['content']) > 500 ? '...' : '' ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Recent Articles -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <h2 class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
      Recent Articles (Fix Short Content)
    </h2>
    
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th class="text-left px-3 py-2 font-medium text-slate-700">ID</th>
            <th class="text-left px-3 py-2 font-medium text-slate-700">Title</th>
            <th class="text-left px-3 py-2 font-medium text-slate-700">Source</th>
            <th class="text-left px-3 py-2 font-medium text-slate-700">Content Size</th>
            <th class="text-left px-3 py-2 font-medium text-slate-700">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($shortArticles as $article): 
            $contentLen = $article['content_len'] ?? 0;
            $isShort = $contentLen < 500;
          ?>
          <tr class="hover:bg-slate-50">
            <td class="px-3 py-3 font-mono text-slate-500"><?= $article['id'] ?></td>
            <td class="px-3 py-3">
              <div class="max-w-xs truncate font-medium text-slate-900">
                <?= htmlspecialchars(mb_substr($article['title'], 0, 60)) ?>
              </div>
            </td>
            <td class="px-3 py-3 text-slate-600"><?= htmlspecialchars($article['source_name'] ?? 'Unknown') ?></td>
            <td class="px-3 py-3">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $isShort ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' ?>">
                <?= $contentLen > 0 ? number_format($contentLen) . ' chars' : 'Empty' ?>
              </span>
            </td>
            <td class="px-3 py-3">
              <?php if ($isShort): ?>
              <form method="POST" class="inline">
                <input type="hidden" name="fetch_id" value="<?= $article['id'] ?>">
                <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium text-xs flex items-center gap-1">
                  <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                  Fetch Full
                </button>
              </form>
              <?php else: ?>
              <span class="text-slate-400 text-xs">✓ Good</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Quick Links -->
  <div class="mt-6 flex gap-3">
    <a href="/admin/dashboard.php" class="text-sm text-slate-600 hover:text-slate-900 flex items-center gap-1">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
      Back to Dashboard
    </a>
    <a href="/admin/clear-cache.php" class="text-sm text-slate-600 hover:text-slate-900 flex items-center gap-1 ml-4">
      <i data-lucide="trash-2" class="w-4 h-4"></i>
      Clear Cache
    </a>
  </div>
</div>

<script>
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>

<?php require_once __DIR__ . '/admin-footer.php'; ?>
