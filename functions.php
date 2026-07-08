<?php
/**
 * आकाशवाणी - Functions
 * Database Query Functions
 */

// Get Published News
if (!function_exists('getPublishedNews')) {
    function getPublishedNews($category = null, $source = null, $limit = 20, $offset = 0) {
        $pdo = getDB();
        if (!$pdo) return [];
        
        $where = "WHERE status = 'published'";
        $params = [];
        
        if ($category) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        if ($source) {
            $where .= " AND source = ?";
            $params[] = $source;
        }
        
        $sql = "SELECT id, title, slug, summary, content, image, category, source, source_name, published_at, view_count 
                FROM news 
                $where 
                ORDER BY published_at DESC 
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Get News by Slug
if (!function_exists('getNewsBySlug')) {
    function getNewsBySlug($slug) {
        $pdo = getDB();
        if (!$pdo) return null;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM news WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}

// Get Related News
if (!function_exists('getRelatedNews')) {
    function getRelatedNews($id = 0, $category = '', $limit = 4) {
        $pdo = getDB();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->prepare("
                SELECT id, title, slug, image, category, published_at 
                FROM news 
                WHERE status = 'published' AND id != ? AND category = ?
                ORDER BY published_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$id, $category, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Get Categories
if (!function_exists('getCategories')) {
    function getCategories() {
        $pdo = getDB();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->query("SELECT DISTINCT category FROM news WHERE status = 'published' ORDER BY category");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Get Market Data (from cache)
if (!function_exists('getMarketData')) {
    function getMarketData() {
        $cacheFile = __DIR__ . '/data/cache/market.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
            return json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        return [];
    }
}

// Get IPO List (from cache)
if (!function_exists('getIPOList')) {
    function getIPOList() {
        $cacheFile = __DIR__ . '/data/cache/ipo-list.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
            return json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        return [];
    }
}

// Update View Count
if (!function_exists('incrementViewCount')) {
    function incrementViewCount($newsId) {
        $pdo = getDB();
        if (!$pdo) return false;
        
        try {
            $stmt = $pdo->prepare("UPDATE news SET view_count = view_count + 1 WHERE id = ?");
            return $stmt->execute([$newsId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Search News
if (!function_exists('searchNews')) {
    function searchNews($query, $limit = 20) {
        $pdo = getDB();
        if (!$pdo) return [];
        
        try {
            $stmt = $pdo->prepare("
                SELECT id, title, slug, image, category, published_at 
                FROM news 
                WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)
                ORDER BY published_at DESC 
                LIMIT ?
            ");
            $search = '%' . $query . '%';
            $stmt->execute([$search, $search, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

// Security Headers for APIs
if (!function_exists('sendSecurityHeaders')) {
    function sendSecurityHeaders(): void {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}

// Rate limiting check
if (!function_exists('checkRateLimit')) {
    function checkRateLimit(string $key, int $maxRequests = 60, int $window = 60): bool {
        $cacheDir = __DIR__ . '/data/cache/';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        $file = $cacheDir . 'rate_' . md5($key) . '.json';
        $now = time();
        
        $data = ['count' => 0, 'window' => $now];
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: $data;
        }
        
        if ($now - $data['window'] > $window) {
            $data = ['count' => 0, 'window' => $now];
        }
        
        $data['count']++;
        
        if ($data['count'] > $maxRequests) {
            return false;
        }
        
        file_put_contents($file, json_encode($data));
        return true;
    }
}

// Slugify — URL-safe Nepali/English slug generator
if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = preg_replace('/[\x{200c}\x{200d}]/u', '', $text);
        $replace = [
            'आ' => 'a', 'अ' => 'a', 'इ' => 'i', 'ई' => 'i', 'उ' => 'u', 'ऊ' => 'u',
            'ए' => 'e', 'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au', 'ऋ' => 'ri',
            'ा' => 'aa', 'ि' => 'i', 'ी' => 'i', 'ु' => 'u', 'ू' => 'u',
            'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au', 'ृ' => 'ri',
            '्' => '', 'ं' => '', 'ँ' => '',
            'क' => 'ka', 'ख' => 'kha', 'ग' => 'ga', 'घ' => 'gha',
            'च' => 'cha', 'छ' => 'chha', 'ज' => 'ja', 'झ' => 'jha',
            'ट' => 'ta', 'ठ' => 'tha', 'ड' => 'da', 'ढ' => 'dha',
            'ण' => 'na', 'त' => 'ta', 'थ' => 'tha', 'द' => 'da', 'ध' => 'dha',
            'न' => 'na', 'प' => 'pa', 'फ' => 'pha', 'ब' => 'ba', 'भ' => 'bha',
            'म' => 'ma', 'य' => 'ya', 'र' => 'ra', 'ल' => 'la', 'व' => 'wa',
            'श' => 'sha', 'ष' => 'shha', 'स' => 'sa', 'ह' => 'ha',
        ];
        foreach ($replace as $from => $to) {
            $text = str_replace($from, $to, $text);
        }
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
        $text = preg_replace('/\s+/', '-', trim($text));
        $text = preg_replace('/-+/', '-', $text);
        return strtolower(trim($text, '-')) ?: 'post';
    }
}
