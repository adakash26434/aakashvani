-- ============================================
-- ADD: Old news table (for existing API compatibility)
-- This table is used by:
-- - api/news-unified.php
-- - api/market-data.php  
-- - functions.php
-- - includes/data-manager.php
-- ============================================

-- Create news table with unified schema
CREATE TABLE IF NOT EXISTS `news` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(500) NOT NULL,
    `title_ne`        VARCHAR(500) DEFAULT NULL,
    `slug`            VARCHAR(500) NOT NULL,
    `excerpt`         TEXT,
    `excerpt_ne`      TEXT,
    `summary`         TEXT,
    `content`         LONGTEXT,
    `content_ne`      LONGTEXT,
    `image`           VARCHAR(700),
    `image_url`       VARCHAR(700),
    `featured_image`  VARCHAR(700) DEFAULT NULL,
    `category`        VARCHAR(60) NOT NULL DEFAULT 'general',
    `category_id`     INT UNSIGNED DEFAULT NULL,
    `lang`            VARCHAR(5) NOT NULL DEFAULT 'ne',
    `source`          VARCHAR(100),
    `source_name`     VARCHAR(120),
    `source_url`      VARCHAR(700),
    `author`          VARCHAR(200),
    `author_name`     VARCHAR(200) DEFAULT NULL,
    `url_hash`        VARCHAR(64),
    `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
    `is_published`   TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_trending`     TINYINT(1) NOT NULL DEFAULT 0,
    `view_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `reading_time`    INT DEFAULT 0,
    `ai_processed`   TINYINT(1) NOT NULL DEFAULT 0,
    `content_status`  VARCHAR(20) NOT NULL DEFAULT 'unknown',
    `content_length`  INT NOT NULL DEFAULT 0,
    `scrape_status`   VARCHAR(20) NOT NULL DEFAULT 'pending',
    `scrape_error`    TEXT,
    `last_scraped_at` DATETIME DEFAULT NULL,
    `published_at`    DATETIME DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE  KEY `uq_slug`         (`slug`),
    UNIQUE  KEY `uq_url_hash`      (`url_hash`),
    UNIQUE  KEY `uq_source_guid`   (`source_name`, `source_url`),
    KEY     `idx_status_pub_date`  (`status`, `published_at`),
    KEY     `idx_published`        (`is_published`),
    KEY     `idx_news_category`    (`category`),
    KEY     `idx_news_source_date` (`source_name`, `created_at`),
    KEY     `idx_news_view_count`  (`view_count`),
    KEY     `idx_featured`        (`is_featured`),
    KEY     `idx_breaking`        (`is_breaking`),
    FULLTEXT KEY `ft_title_summary`(`title`, `summary`, `excerpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Create sync trigger to keep news table updated
-- When aak_articles is inserted/updated, sync to news
-- ============================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_news_sync_insert//
CREATE TRIGGER trg_news_sync_insert AFTER INSERT ON aak_articles
FOR EACH ROW
BEGIN
    INSERT INTO news (title, title_ne, slug, excerpt, excerpt_ne, summary, content, content_ne,
                     image, image_url, featured_image, category, category_id, lang,
                     source, source_name, source_url, author, author_name, url_hash,
                     status, is_published, is_featured, is_breaking, is_trending,
                     view_count, reading_time, published_at, created_at, updated_at)
    VALUES (NEW.title, NEW.title_ne, NEW.slug, NEW.excerpt, NEW.excerpt_ne, NEW.excerpt,
            NEW.content, NEW.content_ne, NEW.featured_image, NEW.featured_image, NEW.featured_image,
            (SELECT name FROM aak_categories WHERE id = NEW.category_id),
            NEW.category_id, NEW.language, 'Internal', 'Aakashvani', NULL,
            (SELECT display_name FROM aak_users WHERE id = NEW.author_id),
            (SELECT display_name FROM aak_users WHERE id = NEW.author_id),
            MD5(NEW.slug), NEW.status,
            CASE WHEN NEW.status = 'published' THEN 1 ELSE 0 END,
            NEW.is_featured, NEW.is_breaking, NEW.is_trending,
            NEW.view_count, NEW.reading_time, NEW.published_at, NEW.created_at, NEW.updated_at)
    ON DUPLICATE KEY UPDATE
        title = NEW.title, title_ne = NEW.title_ne,
        excerpt = NEW.excerpt, excerpt_ne = NEW.excerpt_ne,
        content = NEW.content, content_ne = NEW.content_ne,
        featured_image = NEW.featured_image,
        status = NEW.status, is_published = CASE WHEN NEW.status = 'published' THEN 1 ELSE 0 END,
        is_featured = NEW.is_featured, is_breaking = NEW.is_breaking, is_trending = NEW.is_trending,
        view_count = NEW.view_count, reading_time = NEW.reading_time,
        published_at = NEW.published_at, updated_at = NEW.updated_at;
END//

DROP TRIGGER IF EXISTS trg_news_sync_update//
CREATE TRIGGER trg_news_sync_update AFTER UPDATE ON aak_articles
FOR EACH ROW
BEGIN
    UPDATE news SET
        title = NEW.title, title_ne = NEW.title_ne,
        excerpt = NEW.excerpt, excerpt_ne = NEW.excerpt_ne,
        content = NEW.content, content_ne = NEW.content_ne,
        featured_image = NEW.featured_image,
        category = (SELECT name FROM aak_categories WHERE id = NEW.category_id),
        status = NEW.status, is_published = CASE WHEN NEW.status = 'published' THEN 1 ELSE 0 END,
        is_featured = NEW.is_featured, is_breaking = NEW.is_breaking, is_trending = NEW.is_trending,
        view_count = NEW.view_count, reading_time = NEW.reading_time,
        published_at = NEW.published_at, updated_at = NEW.updated_at
    WHERE slug = NEW.slug;
END//

DELIMITER ;

-- ============================================
-- Also create missing tables that are referenced
-- ============================================

-- Rashifal table
CREATE TABLE IF NOT EXISTS rashifal_daily (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sign VARCHAR(30) NOT NULL,
    date DATE NOT NULL,
    prediction TEXT,
    prediction_ne TEXT,
    lucky_number VARCHAR(10),
    lucky_color VARCHAR(20),
    mood VARCHAR(30),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sign_date (sign, date),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter subscribers
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 1,
    unsubscribed_at DATETIME DEFAULT NULL,
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News sync log
CREATE TABLE IF NOT EXISTS news_sync_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL,
    articles_processed INT DEFAULT 0,
    articles_added INT DEFAULT 0,
    articles_updated INT DEFAULT 0,
    articles_failed INT DEFAULT 0,
    error_message TEXT,
    run_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source),
    INDEX idx_run_at (run_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert sample news data for testing
-- ============================================
INSERT INTO news (title, slug, excerpt, category, status, is_published, is_featured, is_breaking, view_count, published_at) VALUES
('नेपालमा ताजा समाचार: महत्वपूर्ण घटनाहरू', 'nepal-ma-taja-samachar', 'नेपालको ताजा समाचार र अपडेटहरू।', 'general', 'published', 1, 1, 1, 150, NOW()),
('अर्थतन्त्र: शेयर बजारमा तेजी', 'arthatantra-share-bazaar', 'नेपाली शेयर बजारमा आज तेजी देखिएको छ।', 'economy', 'published', 1, 0, 0, 89, NOW()),
('खेलकुद: क्रिकेट विश्वकपको तयारी', 'khelkud-cricket', 'आगामी क्रिकेट विश्वकपको तयारी सुरु भएको छ।', 'sports', 'published', 1, 0, 0, 200, NOW()),
('प्रविधि: नयाँ स्मार्टफोन विश्लेषण', 'prabidhii-smartphone', 'नयाँ स्मार्टफोनको विश्लेषण र समीक्षा।', 'technology', 'published', 1, 0, 0, 75, NOW()),
('राजनीति: संसदको अधिवेशन सुरु', 'rajniti-sansad', 'संसदको नयाँ अधिवेशन आजदेखि सुरु भएको छ।', 'politics', 'published', 1, 0, 0, 120, NOW())
ON DUPLICATE KEY UPDATE title=title;
