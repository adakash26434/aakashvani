<?php
/**
 * आकाशवाणी — SEO Helper
 * Provides getSeoSetting(), saveSeoSetting(), getPageSeo(), ensureSeoTables()
 */

function ensureSeoTables(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $pdo = db();
    if (isMysql()) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS seo_settings (
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS seo_pages (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            page_path VARCHAR(200) NOT NULL,
            meta_title VARCHAR(200),
            meta_description VARCHAR(500),
            meta_keywords VARCHAR(500),
            og_image VARCHAR(500),
            is_noindex TINYINT(1) DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_path (page_path)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS seo_settings (
            setting_key TEXT NOT NULL PRIMARY KEY,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS seo_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            page_path TEXT NOT NULL UNIQUE,
            meta_title TEXT,
            meta_description TEXT,
            meta_keywords TEXT,
            og_image TEXT,
            is_noindex INTEGER DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

function _seoCache(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        ensureSeoTables();
        $rows = db()->query("SELECT setting_key, setting_value FROM seo_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $cache = $rows ?: [];
    } catch (Exception $e) {
        $cache = [];
    }
    return $cache;
}

function getSeoSetting(string $key, string $default = ''): string {
    $cache = _seoCache();
    return isset($cache[$key]) && $cache[$key] !== '' ? (string)$cache[$key] : $default;
}

function saveSeoSetting(string $key, string $value): void {
    ensureSeoTables();
    $pdo = db();
    if (isMysql()) {
        $pdo->prepare("INSERT INTO seo_settings (setting_key, setting_value) VALUES (?,?)
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=CURRENT_TIMESTAMP")
            ->execute([$key, $value]);
    } else {
        $pdo->prepare("INSERT OR REPLACE INTO seo_settings (setting_key, setting_value) VALUES (?,?)")
            ->execute([$key, $value]);
    }
}

function saveSeoSettings(array $map): void {
    foreach ($map as $k => $v) saveSeoSetting($k, (string)$v);
}

function getPageSeo(string $path): array {
    try {
        ensureSeoTables();
        $stmt = db()->prepare("SELECT * FROM seo_pages WHERE page_path = ?");
        $stmt->execute([$path]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) { return []; }
}

function savePageSeo(string $path, array $data): void {
    ensureSeoTables();
    $pdo = db();
    $title = $data['meta_title'] ?? '';
    $desc  = $data['meta_description'] ?? '';
    $kw    = $data['meta_keywords'] ?? '';
    $img   = $data['og_image'] ?? '';
    $ni    = (int)($data['is_noindex'] ?? 0);
    if (isMysql()) {
        $pdo->prepare("INSERT INTO seo_pages (page_path,meta_title,meta_description,meta_keywords,og_image,is_noindex)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE meta_title=VALUES(meta_title),meta_description=VALUES(meta_description),
            meta_keywords=VALUES(meta_keywords),og_image=VALUES(og_image),is_noindex=VALUES(is_noindex),
            updated_at=CURRENT_TIMESTAMP")
            ->execute([$path,$title,$desc,$kw,$img,$ni]);
    } else {
        $pdo->prepare("INSERT OR REPLACE INTO seo_pages (page_path,meta_title,meta_description,meta_keywords,og_image,is_noindex)
            VALUES (?,?,?,?,?,?)")
            ->execute([$path,$title,$desc,$kw,$img,$ni]);
    }
}

function getAllPageSeo(): array {
    try {
        ensureSeoTables();
        return db()->query("SELECT * FROM seo_pages ORDER BY page_path")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { return []; }
}
