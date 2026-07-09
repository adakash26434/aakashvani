<?php
/**
 * Nepali Stories API
 * Endpoint: /api/stories.php
 * Method: GET
 * 
 * Parameters:
 * - category: Filter by category (optional)
 * - limit: Number of stories to return (default: 20)
 * - offset: Pagination offset (default: 0)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

$category = $_GET['category'] ?? null;
$limit = (int)($_GET['limit'] ?? 20);
$offset = (int)($_GET['offset'] ?? 0);

function getStories(?string $category = null, int $limit = 20, int $offset = 0): array {
    $db = db();
    if (!$db) return [];
    ensureStoriesTable();

    $sql = 'SELECT * FROM stories WHERE is_published = 1';
    $params = [];

    if ($category) {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }

    $sql .= ' ORDER BY views DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $stories = $stmt->fetchAll();

        foreach ($stories as &$s) {
            $s['tags'] = json_decode($s['tags'] ?? '[]', true) ?: [];
            $s['tags_en'] = json_decode($s['tags_en'] ?? '[]', true) ?: [];
        }

        return $stories;
    } catch (Throwable $e) {
        error_log('[stories] getStories: ' . $e->getMessage());
        return [];
    }
}


function getStoryCategories(): array {
    return [
        ['name' => 'moral', 'name_ne' => 'नैतिक कथा'],
        ['name' => 'mystery', 'name_ne' => 'रहस्य'],
        ['name' => 'educational', 'name_ne' => 'शैक्षिक'],
        ['name' => 'adventure', 'name_ne' => 'साहसिक'],
        ['name' => 'historical', 'name_ne' => 'ऐतिहासिक'],
    ];
}

// Route handler
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'categories':
        echo json_encode(['categories' => getStoryCategories()], JSON_UNESCAPED_UNICODE);
        break;
    case 'list':
    default:
        $stories = getStories($category, $limit, $offset);
        $stories[] = [
            'source' => 'Sample Nepali Stories',
            'source_url' => 'https://www.hamropatro.com',
            'note' => 'नेपाली कथाहरू नमूना डाटा। Admin द्वारा थपिएको कथाहरू पनि देखाइनेछ।',
        ];
        echo json_encode(['stories' => $stories, 'total' => count($stories)], JSON_UNESCAPED_UNICODE);
        break;
}
