<?php
/**
 * api/news-unified.php — UNIFIED NEWS ENDPOINT
 * 
 * This endpoint provides consistent news data from all sources.
 * It uses the DataManager to ensure:
 * - No duplicates (MD5 fingerprint checking)
 * - Consistent data structure
 * - Centralized caching
 * - UTF-8 compliance for Nepali text
 * 
 * Query params:
 *   ?cat=politics|economy|sports|entertainment|world|technology|general   (default: general)
 *   ?limit=50                                                   (default 20, max 50)
 *   ?search=query                                               (optional)
 *   ?offset=0                                                   (pagination)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: *');
sendSecurityHeaders();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/data-manager.php';

try {
    $cat    = isset($_GET['cat'])    ? strtolower(trim($_GET['cat']))    : 'general';
    $limit  = isset($_GET['limit'])  ? max(1, min(50, (int)$_GET['limit'])) : 20;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset'])      : 0;
    $search = isset($_GET['search']) ? trim($_GET['search'])             : null;
    
    // Validate category
    $validCats = ['general', 'politics', 'economy', 'sports', 'technology', 'world', 'entertainment', 'all'];
    if (!in_array($cat, $validCats)) {
        $cat = 'general';
    }
    
    // Get news from unified manager
    $dm = dataManager();
    $news = $dm->getNews(
        $cat === 'all' ? null : $cat,
        $search,
        $limit,
        $offset
    );
    
    // Ensure all articles have required fields and are properly formatted
    $items = [];
    foreach ($news as $article) {
        $items[] = [
            'id'         => $article['id'] ?? '',
            'title'      => $article['title'] ?? '',
            'excerpt'    => truncateText($article['excerpt'] ?? $article['summary'] ?? '', 250),
            'category'   => $article['category'] ?? 'general',
            'source'     => $article['source'] ?? $article['source_name'] ?? 'Unknown',
            'source_url' => $article['source_url'] ?? $article['link'] ?? '',
            'image_url'  => $article['image_url'] ?? '',
            'published_at' => $article['published_at'] ?? $article['pubDate'] ?? time(),
            'language'   => $article['language'] ?? 'ne',
        ];
    }
    
    http_response_code(200);
    echo json_encode([
        'ok'      => true,
        'status'  => 'success',
        'count'   => count($items),
        'category' => $cat,
        'items'   => $items,
        'timestamp' => time(),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'     => false,
        'status' => 'error',
        'error'  => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
?>
