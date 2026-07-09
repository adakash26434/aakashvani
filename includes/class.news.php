<?php
/**
 * News Portal - Core Classes
 * Enterprise-grade news management for आकाशवाणी
 */

if (!defined('AAK_INIT')) die('Direct access not permitted');

/**
 * NewsArticle Class - Handles all article operations
 */
class NewsArticle {
    private $db;
    private $table = 'aak_articles';
    
    public function __construct($db = null) {
        $this->db = $db ?? $this->getDB();
    }
    
    private function getDB() {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
            } catch (PDOException $e) {
                error_log('DB Connection Error: ' . $e->getMessage());
                return null;
            }
        }
        return $pdo;
    }
    
    /**
     * Generate unique slug
     */
    public function generateSlug(string $title, int $id = 0): string {
        $slug = $this->sanitizeSlug($title);
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $id)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    private function sanitizeSlug(string $text): string {
        // Nepali and Unicode support
        $text = preg_replace('/[\s]+/u', '-', trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $text);
        $text = strtolower($text);
        $text = preg_replace('/-+/u', '-', $text);
        $text = trim($text, '-');
        return substr($text, 0, 250);
    }
    
    private function slugExists(string $slug, int $excludeId = 0): bool {
        $sql = "SELECT id FROM {$this->table} WHERE slug = ? AND id != ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug, $excludeId]);
        return (bool)$stmt->fetch();
    }
    
    /**
     * Calculate reading time
     */
    public function calculateReadingTime(string $content): int {
        $text = strip_tags($content);
        $words = str_word_count($text);
        return max(1, ceil($words / 200)); // 200 words per minute
    }
    
    /**
     * Create new article
     */
    public function create(array $data): int|false {
        $data['slug'] = $this->generateSlug($data['title']);
        $data['reading_time'] = $this->calculateReadingTime($data['content'] ?? '');
        
        $columns = [
            'title', 'title_ne', 'slug', 'excerpt', 'excerpt_ne', 'content', 'content_ne',
            'featured_image', 'featured_image_caption', 'featured_image_alt',
            'category_id', 'author_id', 'status', 'scheduled_at', 'published_at',
            'is_featured', 'is_breaking', 'is_trending', 'is_editors_pick',
            'language', 'meta_title', 'meta_description', 'meta_keywords', 'og_image'
        ];
        
        $insertData = [];
        foreach ($columns as $col) {
            $insertData[$col] = $data[$col] ?? null;
        }
        
        if ($insertData['status'] === 'published' && empty($insertData['published_at'])) {
            $insertData['published_at'] = date('Y-m-d H:i:s');
        }
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', array_keys($insertData)) . ") 
                VALUES (:" . implode(', :', array_keys($insertData)) . ")";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($insertData);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Article Create Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update article
     */
    public function update(int $id, array $data): bool {
        if (isset($data['title']) && $data['title'] !== $this->getById($id)['title'] ?? '') {
            $data['slug'] = $this->generateSlug($data['title'], $id);
        }
        
        if (isset($data['content'])) {
            $data['reading_time'] = $this->calculateReadingTime($data['content']);
        }
        
        if (isset($data['status'])) {
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';
        $values = array_values($data);
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log('Article Update Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get article by ID
     */
    public function getById(int $id, bool $includeUnpublished = false): ?array {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, 
                       u.display_name as author_name
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                LEFT JOIN aak_users u ON a.author_id = u.id
                WHERE a.id = ?" . (!$includeUnpublished ? " AND a.status = 'published'" : "");
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if ($article) {
            $article['tags'] = $this->getArticleTags($id);
            $article['images'] = $this->getArticleImages($id);
        }
        
        return $article ?: null;
    }
    
    /**
     * Get article by slug
     */
    public function getBySlug(string $slug): ?array {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug,
                       u.display_name as author_name
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                LEFT JOIN aak_users u ON a.author_id = u.id
                WHERE a.slug = ? AND a.status = 'published'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        $article = $stmt->fetch();
        
        if ($article) {
            $article['tags'] = $this->getArticleTags($article['id']);
            $article['images'] = $this->getArticleImages($article['id']);
        }
        
        return $article ?: null;
    }
    
    /**
     * Increment view count
     */
    public function incrementViews(int $id): void {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?";
        $this->db->prepare($sql)->execute([$id]);
    }
    
    /**
     * Get articles with filters
     */
    public function getArticles(array $filters = [], int $page = 1, int $perPage = 20): array {
        $where = ["a.deleted_at IS NULL"];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category_id'])) {
            $where[] = "a.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['author_id'])) {
            $where[] = "a.author_id = ?";
            $params[] = $filters['author_id'];
        }
        
        if (!empty($filters['is_featured'])) {
            $where[] = "a.is_featured = 1";
        }
        
        if (!empty($filters['is_breaking'])) {
            $where[] = "a.is_breaking = 1";
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(a.title LIKE ? OR a.content LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['tag_id'])) {
            $where[] = "a.id IN (SELECT article_id FROM aak_article_tags WHERE tag_id = ?)";
            $params[] = $filters['tag_id'];
        }
        
        if (!empty($filters['language'])) {
            $where[] = "(a.language = ? OR a.language = 'both')";
            $params[] = $filters['language'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Count total
        $countSql = "SELECT COUNT(*) FROM {$this->table} a WHERE {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();
        
        // Get articles
        $offset = ($page - 1) * $perPage;
        $orderBy = $filters['order_by'] ?? 'a.published_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        
        $sql = "SELECT a.id, a.title, a.title_ne, a.slug, a.excerpt, a.excerpt_ne, 
                       a.featured_image, a.category_id, a.status, a.published_at,
                       a.is_featured, a.is_breaking, a.is_trending, a.view_count,
                       c.name as category_name, c.slug as category_slug,
                       u.display_name as author_name
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                LEFT JOIN aak_users u ON a.author_id = u.id
                WHERE {$whereClause}
                ORDER BY {$orderBy} {$orderDir}
                LIMIT {$perPage} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll();
        
        // Add tags to each article
        foreach ($articles as &$article) {
            $article['tags'] = $this->getArticleTags($article['id']);
        }
        
        return [
            'data' => $articles,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Get latest articles
     */
    public function getLatest(int $limit = 10, ?int $categoryId = null): array {
        $sql = "SELECT a.id, a.title, a.title_ne, a.slug, a.excerpt, a.excerpt_ne,
                       a.featured_image, a.published_at, a.view_count, a.reading_time,
                       c.name as category_name, c.slug as category_slug,
                       u.display_name as author_name
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                LEFT JOIN aak_users u ON a.author_id = u.id
                WHERE a.status = 'published' AND a.deleted_at IS NULL";
        
        $params = [];
        if ($categoryId) {
            $sql .= " AND a.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY a.published_at DESC LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured articles
     */
    public function getFeatured(int $limit = 5): array {
        $sql = "SELECT a.id, a.title, a.title_ne, a.slug, a.excerpt, a.excerpt_ne,
                       a.featured_image, a.published_at, a.view_count,
                       c.name as category_name, c.slug as category_slug
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                WHERE a.status = 'published' AND a.is_featured = 1 AND a.deleted_at IS NULL
                ORDER BY a.published_at DESC LIMIT {$limit}";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get breaking news
     */
    public function getBreaking(int $limit = 5): array {
        $sql = "SELECT id, title, slug, excerpt, featured_image, published_at
                FROM {$this->table}
                WHERE status = 'published' AND is_breaking = 1 AND deleted_at IS NULL
                ORDER BY published_at DESC LIMIT {$limit}";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get trending articles
     */
    public function getTrending(int $limit = 10): array {
        $sql = "SELECT id, title, title_ne, slug, excerpt, featured_image, 
                       view_count, published_at, category_id
                FROM {$this->table}
                WHERE status = 'published' AND is_trending = 1 AND deleted_at IS NULL
                ORDER BY view_count DESC, published_at DESC
                LIMIT {$limit}";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get most viewed
     */
    public function getMostViewed(int $limit = 10, int $days = 7): array {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = "SELECT id, title, title_ne, slug, excerpt, featured_image, 
                       view_count, published_at, category_id
                FROM {$this->table}
                WHERE status = 'published' AND deleted_at IS NULL AND published_at >= ?
                ORDER BY view_count DESC LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$since]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get editor's picks
     */
    public function getEditorsPick(int $limit = 10): array {
        $sql = "SELECT a.id, a.title, a.title_ne, a.slug, a.excerpt, a.excerpt_ne,
                       a.featured_image, a.published_at, a.view_count,
                       c.name as category_name, c.slug as category_slug
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                WHERE a.status = 'published' AND a.is_editors_pick = 1 AND a.deleted_at IS NULL
                ORDER BY a.published_at DESC LIMIT {$limit}";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get related articles
     */
    public function getRelated(int $articleId, int $limit = 5): array {
        $article = $this->getById($articleId);
        if (!$article) return [];
        
        // Get by same category first, then by tags
        $sql = "SELECT a.id, a.title, a.title_ne, a.slug, a.excerpt, a.featured_image,
                       a.published_at, c.name as category_name
                FROM {$this->table} a
                LEFT JOIN aak_categories c ON a.category_id = c.id
                WHERE a.status = 'published' AND a.id != ? AND a.deleted_at IS NULL
                ORDER BY a.category_id = ? DESC, a.published_at DESC
                LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$articleId, $article['category_id'] ?? 0]);
        return $stmt->fetchAll();
    }
    
    /**
     * Set article tags
     */
    public function setTags(int $articleId, array $tagIds): bool {
        $this->db->beginTransaction();
        
        try {
            // Remove existing
            $this->db->prepare("DELETE FROM aak_article_tags WHERE article_id = ?")->execute([$articleId]);
            
            // Add new
            if (!empty($tagIds)) {
                $insert = "INSERT INTO aak_article_tags (article_id, tag_id) VALUES (?, ?)";
                $stmt = $this->db->prepare($insert);
                foreach ($tagIds as $tagId) {
                    $stmt->execute([$articleId, $tagId]);
                }
                
                // Update tag use counts
                $this->db->prepare("UPDATE aak_tags SET use_count = use_count + 1 WHERE id IN (" . implode(',', array_fill(0, count($tagIds), '?')) . ")")->execute($tagIds);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Set Tags Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get article tags
     */
    public function getArticleTags(int $articleId): array {
        $sql = "SELECT t.id, t.name, t.slug, t.color
                FROM aak_tags t
                JOIN aak_article_tags at ON t.id = at.tag_id
                WHERE at.article_id = ?";
        
        return $this->db->prepare($sql)->execute([$articleId])->fetchAll();
    }
    
    /**
     * Get article gallery images
     */
    public function getArticleImages(int $articleId): array {
        $sql = "SELECT id, image_path, caption, alt_text, sort_order
                FROM aak_article_images
                WHERE article_id = ?
                ORDER BY sort_order ASC";
        
        return $this->db->prepare($sql)->execute([$articleId])->fetchAll();
    }
    
    /**
     * Add article image
     */
    public function addImage(int $articleId, string $imagePath, ?string $caption = null, ?string $alt = null, int $sort = 0): bool {
        $sql = "INSERT INTO aak_article_images (article_id, image_path, caption, alt_text, sort_order)
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->prepare($sql)->execute([$articleId, $imagePath, $caption, $alt, $sort]);
    }
    
    /**
     * Delete article (soft delete)
     */
    public function delete(int $id): bool {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?";
        return $this->db->prepare($sql)->execute([$id]);
    }
    
    /**
     * Permanent delete
     */
    public function forceDelete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->prepare($sql)->execute([$id]);
    }
    
    /**
     * Duplicate article
     */
    public function duplicate(int $id): int|false {
        $original = $this->getById($id, true);
        if (!$original) return false;
        
        unset($original['id'], $original['slug'], $original['published_at'], 
              $original['view_count'], $original['created_at'], $original['updated_at']);
        
        $original['title'] = $original['title'] . ' (Copy)';
        $original['status'] = 'draft';
        $original['is_featured'] = 0;
        $original['is_breaking'] = 0;
        $original['is_trending'] = 0;
        $original['is_editors_pick'] = 0;
        
        return $this->create($original);
    }
    
    /**
     * Get statistics
     */
    public function getStats(): array {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived,
                    SUM(view_count) as total_views,
                    SUM(CASE WHEN published_at >= CURDATE() THEN 1 ELSE 0 END) as today_published
                FROM {$this->table} WHERE deleted_at IS NULL";
        
        return $this->db->query($sql)->fetch();
    }
    
    /**
     * Process scheduled articles
     */
    public function processScheduled(): int {
        $sql = "UPDATE {$this->table} 
                SET status = 'published', published_at = NOW() 
                WHERE status = 'scheduled' AND scheduled_at <= NOW() AND deleted_at IS NULL";
        
        return $this->db->exec($sql);
    }
}

/**
 * Category Class
 */
class Category {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db ?? $this->getDB();
    }
    
    private function getDB() {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
            } catch (PDOException $e) {
                error_log('DB Error: ' . $e->getMessage());
                return null;
            }
        }
        return $pdo;
    }
    
    public function create(array $data): int|false {
        $data['slug'] = $this->generateSlug($data['name']);
        
        $sql = "INSERT INTO aak_categories (parent_id, name, name_ne, slug, description, image, icon, color, sort_order, is_active, show_in_menu, show_in_home, meta_title, meta_description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['parent_id'] ?? null,
                $data['name'],
                $data['name_ne'] ?? null,
                $data['slug'],
                $data['description'] ?? null,
                $data['image'] ?? null,
                $data['icon'] ?? null,
                $data['color'] ?? '#16a34a',
                $data['sort_order'] ?? 0,
                $data['is_active'] ?? 1,
                $data['show_in_menu'] ?? 1,
                $data['show_in_home'] ?? 1,
                $data['meta_title'] ?? null,
                $data['meta_description'] ?? null
            ]);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Category Create Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function update(int $id, array $data): bool {
        if (isset($data['name']) && $data['name'] !== $this->getById($id)['name'] ?? '') {
            $data['slug'] = $this->generateSlug($data['name'], $id);
        }
        
        $fields = ['parent_id', 'name', 'name_ne', 'slug', 'description', 'image', 'icon', 'color', 'sort_order', 'is_active', 'show_in_menu', 'show_in_home', 'meta_title', 'meta_description'];
        $setClause = [];
        $values = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $setClause[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($setClause)) return false;
        
        $values[] = $id;
        $sql = "UPDATE aak_categories SET " . implode(', ', $setClause) . " WHERE id = ?";
        
        try {
            return $this->db->prepare($sql)->execute($values);
        } catch (PDOException $e) {
            error_log('Category Update Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM aak_categories WHERE id = ?";
        return $this->db->prepare($sql)->execute([$id])->fetch() ?: null;
    }
    
    public function getBySlug(string $slug): ?array {
        $sql = "SELECT * FROM aak_categories WHERE slug = ? AND is_active = 1";
        return $this->db->prepare($sql)->execute([$slug])->fetch() ?: null;
    }
    
    public function getAll(bool $activeOnly = true): array {
        $sql = "SELECT c.*, 
                       p.name as parent_name,
                       (SELECT COUNT(*) FROM aak_articles a WHERE a.category_id = c.id AND a.status = 'published') as article_count
                FROM aak_categories c
                LEFT JOIN aak_categories p ON c.parent_id = p.id";
        
        if ($activeOnly) {
            $sql .= " WHERE c.is_active = 1";
        }
        
        $sql .= " ORDER BY c.sort_order ASC, c.name ASC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    public function getTree(bool $activeOnly = true): array {
        $categories = $this->getAll($activeOnly);
        return $this->buildTree($categories);
    }
    
    private function buildTree(array $categories, int $parentId = 0): array {
        $tree = [];
        foreach ($categories as $cat) {
            if ($cat['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $cat['id']);
                if ($children) {
                    $cat['children'] = $children;
                }
                $tree[] = $cat;
            }
        }
        return $tree;
    }
    
    public function getMenuCategories(): array {
        $sql = "SELECT * FROM aak_categories WHERE is_active = 1 AND show_in_menu = 1 ORDER BY sort_order ASC";
        return $this->getTreeFromFlat($this->db->query($sql)->fetchAll());
    }
    
    private function getTreeFromFlat(array $categories, int $parentId = 0): array {
        $tree = [];
        foreach ($categories as $cat) {
            if ($cat['parent_id'] == $parentId) {
                $children = $this->getTreeFromFlat($categories, $cat['id']);
                if ($children) {
                    $cat['children'] = $children;
                }
                $tree[] = $cat;
            }
        }
        return $tree;
    }
    
    public function delete(int $id): bool {
        // Move children to parent
        $cat = $this->getById($id);
        if ($cat && $cat['parent_id']) {
            $this->db->prepare("UPDATE aak_categories SET parent_id = ? WHERE parent_id = ?")->execute([$cat['parent_id'], $id]);
        }
        
        return (bool)$this->db->prepare("DELETE FROM aak_categories WHERE id = ?")->execute([$id]);
    }
    
    private function generateSlug(string $name, int $excludeId = 0): string {
        $slug = preg_replace('/[\s]+/u', '-', strtolower(trim($name)));
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug);
        
        $sql = "SELECT id FROM aak_categories WHERE slug = ? AND id != ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug, $excludeId]);
        
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }
}

/**
 * Tag Class
 */
class Tag {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db ?? $this->getDB();
    }
    
    private function getDB() {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
            } catch (PDOException $e) {
                return null;
            }
        }
        return $pdo;
    }
    
    public function create(string $name, ?string $color = null): int|false {
        $slug = $this->generateSlug($name);
        
        $sql = "INSERT INTO aak_tags (name, slug, color) VALUES (?, ?, ?)";
        try {
            $this->db->prepare($sql)->execute([$name, $slug, $color ?? '#6366f1']);
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            // Check for duplicate
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return $this->getBySlug($slug)['id'] ?? false;
            }
            return false;
        }
    }
    
    public function getAll(bool $activeOnly = true): array {
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM aak_article_tags at WHERE at.tag_id = t.id) as article_count
                FROM aak_tags t";
        
        if ($activeOnly) {
            $sql .= " WHERE t.is_active = 1";
        }
        
        $sql .= " ORDER BY t.use_count DESC, t.name ASC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    public function getPopular(int $limit = 20): array {
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM aak_article_tags at WHERE at.tag_id = t.id) as article_count
                FROM aak_tags t
                WHERE t.is_active = 1
                ORDER BY t.use_count DESC
                LIMIT {$limit}";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    public function getBySlug(string $slug): ?array {
        return $this->db->prepare("SELECT * FROM aak_tags WHERE slug = ?")->execute([$slug])->fetch() ?: null;
    }
    
    public function findOrCreate(string $name): int {
        $slug = $this->generateSlug($name);
        $existing = $this->getBySlug($slug);
        if ($existing) {
            return $existing['id'];
        }
        return $this->create($name) ?? 0;
    }
    
    private function generateSlug(string $name): string {
        $slug = preg_replace('/[\s]+/u', '-', strtolower(trim($name)));
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug);
        return substr($slug, 0, 50);
    }
    
    public function update(int $id, array $data): bool {
        $fields = [];
        $values = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }
        if (isset($data['color'])) {
            $fields[] = "color = ?";
            $values[] = $data['color'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $values[] = $data['is_active'];
        }
        
        if (empty($fields)) return false;
        
        $values[] = $id;
        $sql = "UPDATE aak_tags SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->prepare($sql)->execute($values);
    }
    
    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM aak_tags WHERE id = ?")->execute([$id]);
    }
}

/**
 * Media Library Class
 */
class MediaLibrary {
    private $db;
    private $uploadDir;
    
    public function __construct($db = null) {
        $this->db = $db ?? $this->getDB();
        $this->uploadDir = __DIR__ . '/../uploads/';
        
        if (!is_dir($this->uploadDir)) {
            @mkdir($this->uploadDir, 0755, true);
        }
    }
    
    private function getDB() {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
            } catch (PDOException $e) {
                return null;
            }
        }
        return $pdo;
    }
    
    public function upload(array $file, ?int $userId = null): int|false {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf'];
        if (!in_array($file['type'], $allowedMimes)) {
            return false;
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $ext;
        $path = 'uploads/' . date('Y/m/') . $filename;
        $fullPath = __DIR__ . '/../' . $path;
        
        // Create directory if needed
        @mkdir(dirname($fullPath), 0755, true);
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return false;
        }
        
        // Get image dimensions
        $width = null;
        $height = null;
        if (in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            $info = @getimagesize($fullPath);
            if ($info) {
                $width = $info[0];
                $height = $info[1];
            }
        }
        
        // Generate thumbnail
        $thumbnail = $this->generateThumbnail($fullPath, $path);
        
        $sql = "INSERT INTO aak_media (user_id, filename, original_name, mime_type, file_size, width, height, path, url, thumbnail)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->prepare($sql)->execute([
                $userId,
                $filename,
                $file['name'],
                $file['type'],
                $file['size'],
                $width,
                $height,
                $path,
                '/' . $path,
                $thumbnail ? '/' . $thumbnail : null,
                $this->uploadDir . $filename
            ]);
            
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            @unlink($fullPath);
            return false;
        }
    }
    
    private function generateThumbnail(string $imagePath, string $imageUrl): ?string {
        $thumbPath = preg_replace('/\/([^\/]+)$/', '/thumbs/$1', $imagePath);
        $thumbUrl = preg_replace('/\/([^\/]+)$/', '/thumbs/$1', $imageUrl);
        
        @mkdir(dirname($thumbPath), 0755, true);
        
        // Simple resize - in production use Imagine or GD
        $info = @getimagesize($imagePath);
        if (!$info) return null;
        
        $srcWidth = $info[0];
        $srcHeight = $info[1];
        $thumbWidth = 300;
        $thumbHeight = (int)(($thumbWidth / $srcWidth) * $srcHeight);
        
        switch ($info['mime']) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($imagePath);
                $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
                imagejpeg($thumb, $thumbPath, 80);
                break;
            case 'image/png':
                $src = imagecreatefrompng($imagePath);
                $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
                imagepng($thumb, $thumbPath, 8);
                break;
            default:
                return null;
        }
        
        imagedestroy($src);
        imagedestroy($thumb);
        
        return $thumbUrl;
    }
    
    public function getAll(array $filters = [], int $page = 1, int $perPage = 30): array {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['mime'])) {
            $where[] = "mime_type LIKE ?";
            $params[] = $filters['mime'] . '%';
        }
        
        if (!empty($filters['folder'])) {
            $where[] = "folder = ?";
            $params[] = $filters['folder'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(original_name LIKE ? OR filename LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $countSql = "SELECT COUNT(*) FROM aak_media WHERE {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();
        
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM aak_media WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    public function getById(int $id): ?array {
        return $this->db->prepare("SELECT * FROM aak_media WHERE id = ?")->execute([$id])->fetch() ?: null;
    }
    
    public function delete(int $id): bool {
        $media = $this->getById($id);
        if (!$media) return false;
        
        @unlink(__DIR__ . '/../' . $media['path']);
        if ($media['thumbnail']) {
            @unlink(__DIR__ . '/../' . $media['thumbnail']);
        }
        
        return $this->db->prepare("DELETE FROM aak_media WHERE id = ?")->execute([$id]);
    }
    
    public function updateCaption(int $id, string $caption, ?string $altText = null): bool {
        $sql = "UPDATE aak_media SET caption = ?, alt_text = ? WHERE id = ?";
        return $this->db->prepare($sql)->execute([$caption, $altText, $id]);
    }
    
    public function incrementUseCount(int $id): void {
        $this->db->prepare("UPDATE aak_media SET use_count = use_count + 1 WHERE id = ?")->execute([$id]);
    }
}
