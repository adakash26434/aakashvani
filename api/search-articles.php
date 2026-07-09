<?php
/**
 * Search Articles API - For Admin Space Manager
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/class.news.php';

header('Content-Type: application/json');

// Require admin authentication
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$news = new NewsArticle();

$query = sanitize($_GET['q'] ?? '');
$spaceId = (int)($_GET['space_id'] ?? 0);
$limit = min(20, (int)($_GET['limit'] ?? 20));

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT a.id, a.title, a.title_ne, a.slug, a.excerpt, a.excerpt_ne, 
                   a.featured_image, a.published_at, a.view_count, a.status,
                   c.name as category_name, c.slug as category_slug
            FROM aak_articles a
            LEFT JOIN aak_categories c ON a.category_id = c.id
            WHERE a.status = 'published' AND a.deleted_at IS NULL
            AND (a.title LIKE ? OR a.title_ne LIKE ? OR a.excerpt LIKE ? OR a.excerpt_ne LIKE ?)";
    
    $params = ['%' . $query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%'];
    
    if ($spaceId) {
        // Exclude articles already in this space
        $sql .= " AND a.id NOT IN (
                    SELECT article_id FROM aak_space_articles WHERE space_id = ?
                  )";
        $params[] = $spaceId;
    }
    
    $sql .= " ORDER BY a.published_at DESC LIMIT {$limit}";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
    
    echo json_encode($articles);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
