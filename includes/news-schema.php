<?php
/**
 * News Portal Database Schema
 * Enterprise-grade news management system for आकाशवाणी
 */

if (!defined('AAK_INIT')) die('Direct access not permitted');

/**
 * Get all SQL statements needed for news portal
 */
function getNewsPortalSQL(): array {
    return [
        // Users table with roles
        'CREATE TABLE IF NOT EXISTS aak_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            role ENUM("super_admin","admin","editor","reporter","content_manager") NOT NULL DEFAULT "reporter",
            is_active TINYINT(1) DEFAULT 1,
            last_login DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_role (role),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // User permissions
        'CREATE TABLE IF NOT EXISTS aak_user_permissions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            permission VARCHAR(100) NOT NULL,
            FOREIGN KEY (user_id) REFERENCES aak_users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_perm (user_id, permission),
            INDEX idx_permission (permission)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Categories
        'CREATE TABLE IF NOT EXISTS aak_categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            parent_id INT UNSIGNED DEFAULT NULL,
            name VARCHAR(100) NOT NULL,
            name_ne VARCHAR(100) DEFAULT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            image VARCHAR(255) DEFAULT NULL,
            icon VARCHAR(50) DEFAULT NULL,
            color VARCHAR(7) DEFAULT "#16a34a",
            sort_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            show_in_menu TINYINT(1) DEFAULT 1,
            show_in_home TINYINT(1) DEFAULT 1,
            meta_title VARCHAR(150) DEFAULT NULL,
            meta_description VARCHAR(300) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES aak_categories(id) ON DELETE SET NULL,
            INDEX idx_parent (parent_id),
            INDEX idx_slug (slug),
            INDEX idx_active (is_active),
            INDEX idx_sort (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Tags
        'CREATE TABLE IF NOT EXISTS aak_tags (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            slug VARCHAR(50) NOT NULL UNIQUE,
            color VARCHAR(7) DEFAULT "#6366f1",
            is_active TINYINT(1) DEFAULT 1,
            use_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Articles
        'CREATE TABLE IF NOT EXISTS aak_articles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            title_ne VARCHAR(255) DEFAULT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT,
            excerpt_ne TEXT,
            content LONGTEXT,
            content_ne LONGTEXT,
            featured_image VARCHAR(255) DEFAULT NULL,
            featured_image_caption VARCHAR(255) DEFAULT NULL,
            featured_image_alt VARCHAR(255) DEFAULT NULL,
            
            category_id INT UNSIGNED DEFAULT NULL,
            author_id INT UNSIGNED DEFAULT NULL,
            
            status ENUM("draft","pending","published","scheduled","archived") DEFAULT "draft",
            scheduled_at DATETIME DEFAULT NULL,
            published_at DATETIME DEFAULT NULL,
            
            is_featured TINYINT(1) DEFAULT 0,
            is_breaking TINYINT(1) DEFAULT 0,
            is_trending TINYINT(1) DEFAULT 0,
            is_editors_pick TINYINT(1) DEFAULT 0,
            
            view_count INT DEFAULT 0,
            reading_time INT DEFAULT 0,
            
            language ENUM("ne","en","both") DEFAULT "both",
            
            meta_title VARCHAR(150) DEFAULT NULL,
            meta_description VARCHAR(300) DEFAULT NULL,
            meta_keywords VARCHAR(255) DEFAULT NULL,
            og_image VARCHAR(255) DEFAULT NULL,
            
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            
            FOREIGN KEY (category_id) REFERENCES aak_categories(id) ON DELETE SET NULL,
            FOREIGN KEY (author_id) REFERENCES aak_users(id) ON DELETE SET NULL,
            
            INDEX idx_slug (slug),
            INDEX idx_status (status),
            INDEX idx_category (category_id),
            INDEX idx_author (author_id),
            INDEX idx_featured (is_featured),
            INDEX idx_breaking (is_breaking),
            INDEX idx_trending (is_trending),
            INDEX idx_published (published_at),
            INDEX idx_view_count (view_count),
            FULLTEXT idx_search (title, excerpt, content)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Article Tags (pivot)
        'CREATE TABLE IF NOT EXISTS aak_article_tags (
            article_id INT UNSIGNED NOT NULL,
            tag_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (article_id, tag_id),
            FOREIGN KEY (article_id) REFERENCES aak_articles(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES aak_tags(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Article Images (gallery)
        'CREATE TABLE IF NOT EXISTS aak_article_images (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            article_id INT UNSIGNED NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            caption VARCHAR(255) DEFAULT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            sort_order INT DEFAULT 0,
            FOREIGN KEY (article_id) REFERENCES aak_articles(id) ON DELETE CASCADE,
            INDEX idx_article (article_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Media Library
        'CREATE TABLE IF NOT EXISTS aak_media (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_size INT UNSIGNED NOT NULL,
            width INT UNSIGNED DEFAULT NULL,
            height INT UNSIGNED DEFAULT NULL,
            path VARCHAR(255) NOT NULL,
            url VARCHAR(255) NOT NULL,
            thumbnail VARCHAR(255) DEFAULT NULL,
            caption VARCHAR(255) DEFAULT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            folder VARCHAR(100) DEFAULT NULL,
            use_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES aak_users(id) ON DELETE SET NULL,
            INDEX idx_mime (mime_type),
            INDEX idx_folder (folder),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Homepage Sections
        'CREATE TABLE IF NOT EXISTS aak_homepage_sections (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            section_key VARCHAR(50) NOT NULL UNIQUE,
            title VARCHAR(100) NOT NULL,
            title_ne VARCHAR(100) DEFAULT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            type ENUM("latest","featured","trending","most_viewed","category","custom","breaking","editors_pick","gallery","video") NOT NULL,
            category_id INT UNSIGNED DEFAULT NULL,
            article_ids TEXT DEFAULT NULL,
            max_items INT DEFAULT 10,
            style ENUM("grid","list","carousel","big_featured","compact") DEFAULT "grid",
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            cols_md INT DEFAULT 4,
            cols_sm INT DEFAULT 2,
            show_title TINYINT(1) DEFAULT 1,
            show_excerpt TINYINT(1) DEFAULT 0,
            show_image TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES aak_categories(id) ON DELETE SET NULL,
            INDEX idx_active (is_active),
            INDEX idx_sort (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // SEO Settings
        'CREATE TABLE IF NOT EXISTS aak_seo_settings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page_type VARCHAR(50) NOT NULL,
            reference_id INT UNSIGNED DEFAULT NULL,
            meta_title VARCHAR(150) DEFAULT NULL,
            meta_description VARCHAR(300) DEFAULT NULL,
            meta_keywords VARCHAR(255) DEFAULT NULL,
            og_image VARCHAR(255) DEFAULT NULL,
            canonical_url VARCHAR(255) DEFAULT NULL,
            robots VARCHAR(100) DEFAULT "index, follow",
            schema_type VARCHAR(50) DEFAULT "Article",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_page (page_type, reference_id),
            INDEX idx_page_type (page_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Activity Log
        'CREATE TABLE IF NOT EXISTS aak_activity_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED DEFAULT NULL,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) DEFAULT NULL,
            entity_id INT UNSIGNED DEFAULT NULL,
            entity_title VARCHAR(255) DEFAULT NULL,
            details TEXT,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES aak_users(id) ON DELETE SET NULL,
            INDEX idx_user (user_id),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Comments (if needed)
        'CREATE TABLE IF NOT EXISTS aak_comments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            article_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED DEFAULT NULL,
            parent_id INT UNSIGNED DEFAULT NULL,
            name VARCHAR(100) DEFAULT NULL,
            email VARCHAR(100) DEFAULT NULL,
            content TEXT NOT NULL,
            is_approved TINYINT(1) DEFAULT 0,
            is_spam TINYINT(1) DEFAULT 0,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (article_id) REFERENCES aak_articles(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES aak_users(id) ON DELETE SET NULL,
            FOREIGN KEY (parent_id) REFERENCES aak_comments(id) ON DELETE CASCADE,
            INDEX idx_article (article_id),
            INDEX idx_approved (is_approved)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',

        // Advertisements
        'CREATE TABLE IF NOT EXISTS aak_advertisements (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            position VARCHAR(50) NOT NULL,
            type ENUM("image","code","google_ads") DEFAULT "image",
            content TEXT,
            url VARCHAR(255) DEFAULT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            width INT DEFAULT NULL,
            height INT DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            start_date DATE DEFAULT NULL,
            end_date DATE DEFAULT NULL,
            click_count INT DEFAULT 0,
            view_count INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_position (position),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    ];
}

/**
 * Install news portal tables
 */
function installNewsPortalTables(): array {
    $results = ['success' => [], 'errors' => []];
    
    // Try to connect
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Select database
        $pdo->exec('USE ' . DB_NAME);
        
        foreach (getNewsPortalSQL() as $sql) {
            try {
                $pdo->exec($sql);
                preg_match('/CREATE TABLE IF NOT EXISTS (aak_\w+)/', $sql, $m);
                $results['success'][] = $m[1] ?? 'table';
            } catch (PDOException $e) {
                $results['errors'][] = $sql . ': ' . $e->getMessage();
            }
        }
    } catch (PDOException $e) {
        $results['errors'][] = 'Connection failed: ' . $e->getMessage();
    }
    
    return $results;
}
