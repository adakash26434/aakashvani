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
    // Normalize to field names that frontend JS expects
    $items = [];
    foreach ($news as $article) {
        // Determine the best link to use
        $link = $article['link'] ?? $article['source_url'] ?? $article['original_url'] ?? '';
        $internalUrl = $article['internalUrl'] ?? '';
        
        // If no internalUrl but we have a slug, generate one
        if (empty($internalUrl) && !empty($article['slug'])) {
            $srcLabel = $article['sourceLabel'] ?? $article['source_name'] ?? 'Aakashvani';
            $internalUrl = '/news-post.php?slug=' . rawurlencode($article['slug']);
        }
        
        // Determine image - prefer image_url, fallback to image
        $imageUrl = $article['image_url'] ?? $article['image'] ?? '';
        
        // published_at can be various formats
        $pubDate = $article['published_at'] ?? $article['pubDate'] ?? time();
        if (is_string($pubDate)) {
            $pubDate = strtotime($pubDate) ?: time();
        }
        
        $items[] = [
            'id'          => $article['id'] ?? '',
            'title'       => $article['title'] ?? '',
            'excerpt'     => truncateText($article['excerpt'] ?? $article['summary'] ?? '', 250),
            'summary'     => truncateText($article['summary'] ?? $article['excerpt'] ?? '', 250),
            // Frontend JS expects: cat, image, sourceLabel
            'cat'         => strtolower($article['category'] ?? $article['cat'] ?? 'general'),
            'category'    => $article['category'] ?? 'general',
            'sourceLabel' => $article['sourceLabel'] ?? $article['source_name'] ?? $article['source'] ?? 'Aakashvani',
            'source'      => $article['source'] ?? $article['source_name'] ?? 'Aakashvani',
            // Link fields
            'link'        => $link,
            'internalUrl' => $internalUrl,
            'source_url'  => $article['source_url'] ?? '',
            // Image - normalize to 'image' for JS compatibility
            'image'       => $imageUrl,
            'image_url'   => $imageUrl,
            // Time fields
            'pubDate'     => is_numeric($pubDate) ? $pubDate : strtotime($pubDate),
            'published_at' => is_numeric($pubDate) ? $pubDate : strtotime($pubDate),
            'ago'         => $article['ago'] ?? '',
            // Meta
            'language'    => $article['language'] ?? 'ne',
            'slug'        => $article['slug'] ?? '',
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
