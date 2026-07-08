<?php
/**
 * DATA MANAGER - Single source of truth for all application data
 * 
 * This file provides unified access to all data across the application:
 * - News articles (RSS + database)
 * - Market data (stocks, forex, crypto)
 * - User data
 * - Search and filtering
 * 
 * All pages should consume data through this manager, not directly from APIs.
 */

require_once __DIR__ . '/data-schema.php';
require_once __DIR__ . '/../config.php';

class DataManager {
    
    private static $instance = null;
    private $cache = [];
    private $cacheDir = __DIR__ . '/../cache';
    
    private function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public static function getInstance(): self {
        return self::$instance ??= new self();
    }

    /**
     * Atomic cache write — prevents stampede/corruption from concurrent writes
     * Uses temp file + rename (atomic on POSIX) + flock for safety
     */
    private function writeCache(string $file, string $data): bool {
        $dir = dirname($file);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $tmp = $file . '.tmp.' . getmypid();
        $fp = @fopen($tmp, 'c');
        if (!$fp) return false;
        if (!@flock($fp, LOCK_EX)) { fclose($fp); @unlink($tmp); return false; }
        ftruncate($fp, 0);
        fwrite($fp, $data);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        // Atomic rename on POSIX — no gap where file doesn't exist
        if (!@rename($tmp, $file)) { @unlink($tmp); return false; }
        return true;
    }
    
    /**
     * GET NEWS - Unified news access (RSS + database)
     * Returns consistent data structure regardless of source
     */
    public function getNews(
        ?string $category = null,
        ?string $search = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        $cacheKey = md5("news_{$category}_{$search}_{$limit}_{$offset}");
        $cacheFile = $this->cacheDir . "/news_{$cacheKey}.json";
        
        // Check cache (30-minute TTL)
        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 1800) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $cached;
            }
        }
        
        $articles = [];
        
        // 1. Fetch from RSS API
        try {
            $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://news.bandanasigdel.com.np';
            $rssData = @json_decode(@file_get_contents(
                $baseUrl . '/api/news-rss.php?cat=' . urlencode($category ?? 'general')
            ), true);
            if ($rssData && is_array($rssData['items'] ?? null)) {
                $articles = array_merge($articles, $rssData['items']);
            }
        } catch (Throwable $e) {
            error_log("RSS fetch failed: " . $e->getMessage());
        }
        
        // 2. Fetch from database if available
        try {
            $pdo = function_exists('db') ? db() : null;
            if ($pdo) {
                $query = "SELECT id, title, excerpt, content, category, 
                                 image_url, author, published_at, language
                          FROM news WHERE 1=1";
                $params = [];
                
                if ($category && $category !== 'general') {
                    $query .= " AND category = ?";
                    $params[] = $category;
                }
                if ($search) {
                    $query .= " AND (title LIKE ? OR excerpt LIKE ?)";
                    $searchTerm = "%{$search}%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $query .= " ORDER BY published_at DESC LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $dbArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (is_array($dbArticles)) {
                    $articles = array_merge($articles, $dbArticles);
                }
            }
        } catch (Throwable $e) {
            error_log("Database news fetch failed: " . $e->getMessage());
        }
        
        // 3. Deduplicate using fingerprint (title + URL)
        $seen = [];
        $unique = [];
        foreach ($articles as $item) {
            $title = $item['title'] ?? '';
            $url = $item['source_url'] ?? $item['link'] ?? '';
            $fingerprint = md5(mb_strtolower(preg_replace('/\s+/u', '', $title)) . '|' . $url);
            
            if (isset($seen[$fingerprint])) {
                continue; // Skip duplicate
            }
            $seen[$fingerprint] = 1;
            $unique[] = $item;
        }
        
        // 4. Sort by date and paginate
        usort($unique, fn($a, $b) => 
            ($b['published_at'] ?? $b['pubDate'] ?? 0) <=> 
            ($a['published_at'] ?? $a['pubDate'] ?? 0)
        );
        
        $result = array_slice($unique, $offset, $limit);
        
        // Cache result
        $this->writeCache($cacheFile, json_encode($result, JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * GET MARKET DATA - Unified market access
     */
    public function getMarketData(
        ?string $category = null,
        int $limit = 50
    ): array {
        $cacheKey = md5("market_{$category}_{$limit}");
        $cacheFile = $this->cacheDir . "/market_{$cacheKey}.json";
        
        // Check cache (5-minute TTL for real-time data)
        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 300) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $cached;
            }
        }
        
        $data = [];
        
        try {
            // Fetch from market API
            $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://news.bandanasigdel.com.np';
            $marketData = @json_decode(@file_get_contents(
                $baseUrl . '/api/market-data.php?type=' . urlencode($category ?? 'stock')
            ), true);
            if ($marketData && is_array($marketData['data'] ?? null)) {
                $data = array_slice($marketData['data'], 0, $limit);
            }
        } catch (Throwable $e) {
            error_log("Market data fetch failed: " . $e->getMessage());
        }
        
        // Cache result
        $this->writeCache($cacheFile, json_encode($data, JSON_UNESCAPED_UNICODE));
        
        return $data;
    }
    
    /**
     * SEARCH NEWS - Global search across all sources
     */
    public function searchNews(string $query, int $limit = 30): array {
        if (mb_strlen(trim($query), 'UTF-8') < 2) {
            return [];
        }
        
        return $this->getNews(null, $query, $limit, 0);
    }
    
    /**
     * GET TRENDING - Most read/shared articles
     */
    public function getTrending(int $limit = 10): array {
        $cacheFile = $this->cacheDir . '/trending.json';
        
        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < 3600) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $cached;
            }
        }
        
        try {
            $pdo = function_exists('db') ? db() : null;
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT * FROM news 
                    WHERE published_at > ? 
                    ORDER BY views DESC, published_at DESC 
                    LIMIT ?");
                $stmt->execute([time() - 86400*7, $limit]);
                $trending = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (is_array($trending)) {
                    $this->writeCache($cacheFile, json_encode($trending, JSON_UNESCAPED_UNICODE));
                    return $trending;
                }
            }
        } catch (Throwable $e) {
            error_log("Trending fetch failed: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * GET CATEGORIES - List all available news categories
     */
    public function getCategories(): array {
        return [
            'general'       => 'सामान्य',
            'politics'      => 'राजनीति',
            'economy'       => 'अर्थ',
            'sports'        => 'खेल',
            'technology'    => 'प्रविधि',
            'world'         => 'विश्व',
            'entertainment' => 'मनोरञ्जन',
        ];
    }
    
    /**
     * CLEAR CACHE - Force refresh of data
     */
    public function clearCache(?string $type = null): bool {
        try {
            if ($type) {
                // Clear specific type
                $files = glob($this->cacheDir . "/{$type}_*.json");
                foreach ($files as $file) {
                    @unlink($file);
                }
            } else {
                // Clear all
                array_map('unlink', glob($this->cacheDir . '/*.json'));
            }
            return true;
        } catch (Throwable $e) {
            error_log("Cache clear failed: " . $e->getMessage());
            return false;
        }
    }
}

// Global helper function
if (!function_exists('dataManager')) {
    function dataManager(): DataManager {
        return DataManager::getInstance();
    }
}
?>
