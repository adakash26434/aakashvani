<?php
/**
 * /api/get-podcasts.php — Fetch user podcasts + featured content
 * Returns: {success:true, data:[...], featured:[...]}
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db();
    $limit = (int)($_GET['limit'] ?? 50);
    $featured = isset($_GET['featured']) ? 1 : null;
    
    $sql = "SELECT id, title, description, slug, cover_image, audio_url, duration_seconds, 
                   category, source_name, featured, views, created_at
            FROM user_podcasts 
            WHERE status = 'published'";
    
    if ($featured !== null) {
        $sql .= " AND featured = 1";
    }
    
    $sql .= " ORDER BY featured DESC, created_at DESC LIMIT " . $limit;
    
    $podcasts = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    echo json_encode([
        'success' => true,
        'count' => count($podcasts),
        'data' => $podcasts,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
