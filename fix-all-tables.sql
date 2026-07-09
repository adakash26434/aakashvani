-- ============================================
-- आकाशवाणी - COMPLETE DATABASE FIX
-- Creates ALL missing tables at once
-- Run AFTER install-news-portal.sql
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================
-- 1. NEWS TABLE (for news-unified API)
-- ============================================
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(500) NOT NULL,
    `title_ne`        VARCHAR(500) DEFAULT NULL,
    `slug`            VARCHAR(500) NOT NULL,
    `excerpt`         TEXT,
    `excerpt_ne`      TEXT,
    `summary`         TEXT,
    `content`         LONGTEXT,
    `content_ne`      LONGTEXT,
    `image`           VARCHAR(700) DEFAULT NULL,
    `image_url`       VARCHAR(700) DEFAULT NULL,
    `featured_image`  VARCHAR(700) DEFAULT NULL,
    `category`        VARCHAR(60) NOT NULL DEFAULT 'general',
    `category_id`     INT UNSIGNED DEFAULT NULL,
    `lang`            VARCHAR(5) NOT NULL DEFAULT 'ne',
    `source`          VARCHAR(100) DEFAULT NULL,
    `source_name`     VARCHAR(120) DEFAULT NULL,
    `source_url`      VARCHAR(700) DEFAULT NULL,
    `author`          VARCHAR(200) DEFAULT NULL,
    `author_name`     VARCHAR(200) DEFAULT NULL,
    `url_hash`        VARCHAR(64) DEFAULT NULL,
    `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
    `is_published`   TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_trending`     TINYINT(1) NOT NULL DEFAULT 0,
    `view_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `reading_time`     INT DEFAULT 0,
    `ai_processed`   TINYINT(1) NOT NULL DEFAULT 0,
    `content_status`  VARCHAR(20) NOT NULL DEFAULT 'unknown',
    `content_length`  INT NOT NULL DEFAULT 0,
    `scrape_status`   VARCHAR(20) NOT NULL DEFAULT 'pending',
    `scrape_error`    TEXT DEFAULT NULL,
    `last_scraped_at` DATETIME DEFAULT NULL,
    `published_at`    DATETIME DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug`         (`slug`),
    UNIQUE KEY `uq_url_hash`      (`url_hash`),
    UNIQUE KEY `uq_source_guid`    (`source_name`, `source_url`),
    KEY `idx_status_pub_date`  (`status`, `published_at`),
    KEY `idx_published`        (`is_published`),
    KEY `idx_news_category`    (`category`),
    KEY `idx_news_source_date` (`source_name`, `created_at`),
    KEY `idx_news_view_count`  (`view_count`),
    KEY `idx_featured`        (`is_featured`),
    KEY `idx_breaking`        (`is_breaking`),
    FULLTEXT KEY `ft_title_summary`(`title`, `summary`, `excerpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TECH_NEWS TABLE (for RSS sync)
-- ============================================
DROP TABLE IF EXISTS `tech_news`;
CREATE TABLE `tech_news` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(500) NOT NULL,
    `title_ne`        VARCHAR(500) DEFAULT NULL,
    `slug`            VARCHAR(500) NOT NULL,
    `excerpt`         TEXT,
    `content`         LONGTEXT,
    `category`        VARCHAR(60) NOT NULL DEFAULT 'general',
    `source_name`     VARCHAR(120) DEFAULT NULL,
    `original_url`     VARCHAR(700) DEFAULT NULL,
    `url_hash`        VARCHAR(64) DEFAULT NULL,
    `image_url`       VARCHAR(700) DEFAULT NULL,
    `is_published`   TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking`     TINYINT(1) NOT NULL DEFAULT 0,
    `lang`            VARCHAR(5) NOT NULL DEFAULT 'ne',
    `scope`            VARCHAR(20) NOT NULL DEFAULT 'national',
    `ai_processed`   TINYINT(1) NOT NULL DEFAULT 0,
    `view_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `published_at`    DATETIME DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_tn_slug`       (`slug`),
    UNIQUE KEY `uq_tn_url_hash`    (`url_hash`),
    KEY `idx_tn_source`      (`source_name`),
    KEY `idx_tn_published`    (`is_published`),
    KEY `idx_tn_category`     (`category`),
    KEY `idx_tn_breaking`     (`is_breaking`),
    KEY `idx_tn_pub_date`     (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. RASHIFAL_DAILY TABLE
-- ============================================
DROP TABLE IF EXISTS `rashifal_daily`;
CREATE TABLE `rashifal_daily` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `sign`            VARCHAR(30) NOT NULL,
    `date`            DATE NOT NULL,
    `prediction`       TEXT,
    `prediction_ne`    TEXT,
    `lucky_number`    VARCHAR(10) DEFAULT NULL,
    `lucky_color`     VARCHAR(20) DEFAULT NULL,
    `mood`            VARCHAR(30) DEFAULT NULL,
    `compatibility`    VARCHAR(30) DEFAULT NULL,
    `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_sign_date` (`sign`, `date`),
    KEY `idx_rashifal_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. NEWSLETTER_SUBSCRIBERS TABLE
-- ============================================
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email`           VARCHAR(200) NOT NULL UNIQUE,
    `name`            VARCHAR(100) DEFAULT NULL,
    `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
    `unsubscribed_at`  DATETIME DEFAULT NULL,
    `subscribed_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `verify_token`     VARCHAR(64) DEFAULT NULL,
    `verified_at`      DATETIME DEFAULT NULL,
    KEY `idx_email`        (`email`),
    KEY `idx_active`        (`is_active`),
    KEY `idx_verify_token`  (`verify_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. NEWS_SYNC_LOG TABLE
-- ============================================
DROP TABLE IF EXISTS `news_sync_log`;
CREATE TABLE `news_sync_log` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `source`              VARCHAR(50) NOT NULL,
    `status`              VARCHAR(20) NOT NULL,
    `articles_processed`  INT DEFAULT 0,
    `articles_added`      INT DEFAULT 0,
    `articles_updated`    INT DEFAULT 0,
    `articles_failed`     INT DEFAULT 0,
    `error_message`       TEXT,
    `run_at`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_sync_source`  (`source`),
    KEY `idx_sync_run_at`  (`run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. AUCTION_NOTICES TABLE
-- ============================================
DROP TABLE IF EXISTS `auction_notices`;
CREATE TABLE `auction_notices` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(500) NOT NULL,
    `slug`             VARCHAR(500) NOT NULL,
    `description`      TEXT,
    `organization`     VARCHAR(200) DEFAULT NULL,
    `category`         VARCHAR(50) DEFAULT NULL,
    `location`         VARCHAR(200) DEFAULT NULL,
    `published_date`   DATE DEFAULT NULL,
    `deadline_date`    DATE DEFAULT NULL,
    `image_url`        VARCHAR(500) DEFAULT NULL,
    `source_url`       VARCHAR(500) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_auction_slug`   (`slug`),
    KEY `idx_auction_pub_date`  (`published_date`),
    KEY `idx_auction_deadline`    (`deadline_date`),
    KEY `idx_auction_category`     (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. CABINET_DECISIONS TABLE
-- ============================================
DROP TABLE IF EXISTS `cabinet_decisions`;
CREATE TABLE `cabinet_decisions` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `decision_number`  VARCHAR(50) DEFAULT NULL,
    `title`            VARCHAR(500) NOT NULL,
    `slug`             VARCHAR(500) NOT NULL,
    `summary`          TEXT,
    `decision_date`    DATE DEFAULT NULL,
    `meeting_date`     DATE DEFAULT NULL,
    `source_url`       VARCHAR(500) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_cabinet_slug`   (`slug`),
    KEY `idx_cabinet_decision_date` (`decision_date`),
    KEY `idx_cabinet_meeting_date`  (`meeting_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. CONTACT_DIRECTORY TABLE
-- ============================================
DROP TABLE IF EXISTS `contact_directory`;
CREATE TABLE `contact_directory` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`             VARCHAR(200) NOT NULL,
    `designation`       VARCHAR(100) DEFAULT NULL,
    `ministry`         VARCHAR(200) DEFAULT NULL,
    `department`        VARCHAR(200) DEFAULT NULL,
    `office`            VARCHAR(200) DEFAULT NULL,
    `phone`            VARCHAR(100) DEFAULT NULL,
    `mobile`           VARCHAR(100) DEFAULT NULL,
    `email`            VARCHAR(200) DEFAULT NULL,
    `address`          VARCHAR(300) DEFAULT NULL,
    `category`         VARCHAR(50) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_contact_email`   (`email`),
    KEY `idx_contact_ministry`  (`ministry`),
    KEY `idx_contact_category`   (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. DIRECTORY TABLE
-- ============================================
DROP TABLE IF EXISTS `directory`;
CREATE TABLE `directory` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(300) NOT NULL,
    `slug`             VARCHAR(300) NOT NULL,
    `category`         VARCHAR(100) DEFAULT NULL,
    `subcategory`       VARCHAR(100) DEFAULT NULL,
    `description`      TEXT,
    `phone`            VARCHAR(100) DEFAULT NULL,
    `address`          VARCHAR(300) DEFAULT NULL,
    `website`          VARCHAR(300) DEFAULT NULL,
    `email`            VARCHAR(200) DEFAULT NULL,
    `image_url`        VARCHAR(500) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_directory_slug`  (`slug`),
    KEY `idx_directory_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. GOVERNMENT_TENDERS TABLE
-- ============================================
DROP TABLE IF EXISTS `government_tenders`;
CREATE TABLE `government_tenders` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(500) NOT NULL,
    `slug`             VARCHAR(500) NOT NULL,
    `description`      TEXT,
    `organization`     VARCHAR(200) DEFAULT NULL,
    `category`         VARCHAR(50) DEFAULT NULL,
    `tender_type`       VARCHAR(50) DEFAULT NULL,
    `estimated_value`   DECIMAL(15,2) DEFAULT NULL,
    `location`         VARCHAR(200) DEFAULT NULL,
    `published_date`   DATE DEFAULT NULL,
    `deadline_date`    DATE DEFAULT NULL,
    `document_fee`     DECIMAL(10,2) DEFAULT NULL,
    `source_url`       VARCHAR(500) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_tender_slug`   (`slug`),
    KEY `idx_tender_pub_date`  (`published_date`),
    KEY `idx_tender_deadline`    (`deadline_date`),
    KEY `idx_tender_org`        (`organization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. LOKSEWA_NOTICES TABLE
-- ============================================
DROP TABLE IF EXISTS `loksewa_notices`;
CREATE TABLE `loksewa_notices` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(500) NOT NULL,
    `slug`             VARCHAR(500) NOT NULL,
    `exam_name`        VARCHAR(200) DEFAULT NULL,
    `organization`     VARCHAR(200) DEFAULT NULL,
    `level`            VARCHAR(50) DEFAULT NULL,
    `position`         VARCHAR(200) DEFAULT NULL,
    `vacancy_count`    INT DEFAULT NULL,
    `description`      TEXT,
    `requirement`      TEXT,
    `fee`              DECIMAL(10,2) DEFAULT NULL,
    `application_date`  DATE DEFAULT NULL,
    `exam_date`        DATE DEFAULT NULL,
    `deadline_date`    DATE DEFAULT NULL,
    `source_url`       VARCHAR(500) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `is_breaking`       TINYINT(1) DEFAULT 0,
    `image_url`        VARCHAR(500) DEFAULT NULL,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_loksewa_slug`   (`slug`),
    KEY `idx_loksewa_deadline`   (`deadline_date`),
    KEY `idx_loksewa_exam_date`   (`exam_date`),
    KEY `idx_loksewa_org`        (`organization`),
    KEY `idx_loksewa_level`      (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. USER_PODCASTS TABLE
-- ============================================
DROP TABLE IF EXISTS `user_podcasts`;
CREATE TABLE `user_podcasts` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`          INT UNSIGNED DEFAULT NULL,
    `title`            VARCHAR(300) NOT NULL,
    `slug`             VARCHAR(300) NOT NULL,
    `description`      TEXT,
    `audio_url`        VARCHAR(500) NOT NULL,
    `duration`         INT DEFAULT NULL COMMENT 'Duration in seconds',
    `category`         VARCHAR(50) DEFAULT NULL,
    `tags`             VARCHAR(200) DEFAULT NULL,
    `thumbnail_url`    VARCHAR(500) DEFAULT NULL,
    `is_published`    TINYINT(1) DEFAULT 1,
    `play_count`       INT DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_podcast_slug`   (`slug`),
    KEY `idx_podcast_user`      (`user_id`),
    KEY `idx_podcast_category`  (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. USER_DATA TABLE
-- ============================================
DROP TABLE IF EXISTS `user_data`;
CREATE TABLE `user_data` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email`           VARCHAR(200) NOT NULL UNIQUE,
    `name`             VARCHAR(200) DEFAULT NULL,
    `phone`            VARCHAR(50) DEFAULT NULL,
    `password_hash`    VARCHAR(255) DEFAULT NULL,
    `role`            VARCHAR(20) DEFAULT 'user',
    `is_active`       TINYINT(1) DEFAULT 1,
    `last_login`       DATETIME DEFAULT NULL,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_email`   (`email`),
    KEY `idx_user_role`       (`role`),
    KEY `idx_user_active`     (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. OFFERS TABLE
-- ============================================
DROP TABLE IF EXISTS `offers`;
CREATE TABLE `offers` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(300) NOT NULL,
    `slug`             VARCHAR(300) NOT NULL,
    `description`      TEXT,
    `discount_percent` DECIMAL(5,2) DEFAULT NULL,
    `coupon_code`      VARCHAR(50) DEFAULT NULL,
    `valid_from`       DATE DEFAULT NULL,
    `valid_until`      DATE DEFAULT NULL,
    `image_url`        VARCHAR(500) DEFAULT NULL,
    `source_url`       VARCHAR(500) DEFAULT NULL,
    `is_featured`      TINYINT(1) DEFAULT 0,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_offer_slug`   (`slug`),
    KEY `idx_offer_valid`    (`valid_from`, `valid_until`),
    KEY `idx_offer_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. STORIES TABLE
-- ============================================
DROP TABLE IF EXISTS `stories`;
CREATE TABLE `stories` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`            VARCHAR(300) NOT NULL,
    `slug`             VARCHAR(300) NOT NULL,
    `content`          TEXT,
    `image_url`        VARCHAR(500) DEFAULT NULL,
    `author`           VARCHAR(100) DEFAULT NULL,
    `category`         VARCHAR(50) DEFAULT NULL,
    `tags`             VARCHAR(200) DEFAULT NULL,
    `is_published`    TINYINT(1) DEFAULT 1,
    `view_count`       INT DEFAULT 0,
    `published_at`     DATETIME DEFAULT NULL,
    `created_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_story_slug`   (`slug`),
    KEY `idx_story_category`  (`category`),
    KEY `idx_story_published` (`is_published`),
    FULLTEXT KEY `ft_story_content`(`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. SYNC TRIGGERS (aak_articles -> news, tech_news)
-- ============================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_sync_to_news//
CREATE TRIGGER trg_sync_to_news AFTER INSERT ON aak_articles
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO news (title, title_ne, slug, excerpt, excerpt_ne, summary, content, content_ne,
                           featured_image, image_url, category, category_id, lang,
                           source_name, source_url, author_name, url_hash,
                           status, is_published, is_featured, is_breaking, is_trending,
                           view_count, reading_time, published_at, created_at)
    VALUES (NEW.title, NEW.title_ne, NEW.slug, NEW.excerpt, NEW.excerpt_ne, NEW.excerpt,
            NEW.content, NEW.content_ne, NEW.featured_image, NEW.featured_image,
            (SELECT name FROM aak_categories WHERE id = NEW.category_id),
            NEW.category_id, NEW.language, 'Aakashvani', NULL,
            (SELECT display_name FROM aak_users WHERE id = NEW.author_id),
            MD5(NEW.slug), NEW.status,
            CASE WHEN NEW.status = 'published' THEN 1 ELSE 0 END,
            NEW.is_featured, NEW.is_breaking, NEW.is_trending,
            NEW.view_count, NEW.reading_time, NEW.published_at, NEW.created_at);
END//

DROP TRIGGER IF EXISTS trg_sync_to_tech_news//
CREATE TRIGGER trg_sync_to_tech_news AFTER INSERT ON aak_articles
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO tech_news (title, title_ne, slug, excerpt, content,
                                 category, source_name, original_url, url_hash, image_url,
                                 is_published, is_featured, is_breaking,
                                 lang, scope, ai_processed, view_count, published_at, created_at)
    VALUES (NEW.title, NEW.title_ne, NEW.slug, NEW.excerpt, NEW.content,
            (SELECT name FROM aak_categories WHERE id = NEW.category_id),
            'Aakashvani', NULL, MD5(NEW.slug), NEW.featured_image,
            CASE WHEN NEW.status = 'published' THEN 1 ELSE 0 END,
            NEW.is_featured, NEW.is_breaking,
            NEW.language, 'national', 1, NEW.view_count, NEW.published_at, NEW.created_at);
END//

DELIMITER ;

-- ============================================
-- 17. SAMPLE DATA
-- ============================================

-- Sample news
INSERT INTO news (title, slug, excerpt, category, status, is_published, is_featured, is_breaking, view_count, published_at) VALUES
('नेपालमा ताजा समाचार: महत्वपूर्ण घटनाहरू', 'nepal-taja-samachar-mahattvik', 'नेपालको ताजा समाचार र अपडेटहरू।', 'general', 'published', 1, 1, 1, 150, NOW()),
('अर्थतन्त्र: शेयर बजारमा तेजी', 'arthatantra-share-bazaar-teji', 'नेपाली शेयर बजारमा आज तेजी देखिएको छ।', 'economy', 'published', 1, 0, 0, 89, NOW()),
('खेलकुद: क्रिकेट विश्वकपको तयारी', 'khelkud-cricket-vishwakop', 'आगामी क्रिकेट विश्वकपको तयारी सुरु भएको छ।', 'sports', 'published', 1, 0, 0, 200, NOW()),
('प्रविधि: नयाँ स्मार्टफोन विश्लेषण', 'prabidhii-naya-smartphone', 'नयाँ स्मार्टफोनको विश्लेषण र समीक्षा।', 'technology', 'published', 1, 0, 0, 75, NOW()),
('राजनीति: संसदको अधिवेशन सुरु', 'rajniti-sansad-adhiveshan', 'संसदको नयाँ अधिवेशन आजदेखि सुरु भएको छ।', 'politics', 'published', 1, 0, 0, 120, NOW())
ON DUPLICATE KEY UPDATE title=title;

-- Sample tech_news
INSERT INTO tech_news (title, slug, excerpt, content, category, source_name, url_hash, is_published, is_breaking, published_at) VALUES
('नेपाली खेलकुद: राष्ट्रिय खेलकुद समाचार', 'nepali-khelkud-rashtriya', 'नेपालको राष्ट्रिय खेलकुद समाचार', 'नेपाली खेलकुदको नवीनतम अपडेटहरू।', 'sports', 'RSS', MD5('nepal-sports-1'), 1, 0, NOW()),
('प्रविधि समाचार: डिजिटल प्रविधिको विकास', 'prabidhii-samachar-digital', 'डिजिटल प्रविधिको विकास र प्रगति', 'डिजिटल प्रविधिको नवीनतम विकास र प्रगति।', 'technology', 'RSS', MD5('tech-news-1'), 1, 0, NOW())
ON DUPLICATE KEY UPDATE title=title;

-- Sample rashifal
INSERT INTO rashifal_daily (sign, date, prediction, prediction_ne, lucky_number, lucky_color, mood) VALUES
('mesha', CURDATE(), 'Today is favorable for new beginnings.', 'आज नयाँ सुरुवातको लागि अनुकूल छ।', '7', 'Red', 'Energetic'),
('vrishabha', CURDATE(), 'Focus on financial matters.', 'वित्तीय विषयमा ध्यान दिनुहोस्।', '3', 'Green', 'Calm'),
('mithuna', CURDATE(), 'Communication is key today.', 'आज सञ्चार महत्वपूर्ण छ।', '5', 'Yellow', 'Curious'),
('karka', CURDATE(), 'Time for family matters.', 'परिवारको विषयमा समय बिताउनुहोस्।', '2', 'White', 'Nurturing'),
('simha', CURDATE(), 'Leadership opportunities arise.', 'नेतृत्वको अवसरहरू आइपुग्छन्।', '1', 'Orange', 'Confident'),
('kanya', CURDATE(), 'Focus on health and wellness.', 'स्वास्थ्य र कल्याणमा ध्यान दिनुहोस्।', '9', 'Blue', 'Analytical'),
('tula', CURDATE(), 'Relationships take center stage.', 'सम्बन्धहरू महत्वपूर्ण हुनेछन्।', '6', 'Pink', 'Harmonious'),
('vrishchika', CURDATE(), 'Transformation is highlighted.', 'परिवर्तन उजागर हुनेछ।', '4', 'Black', 'Intense'),
('dhanu', CURDATE(), 'Adventure calls you.', 'यात्रा र अविष्कारले बोलाउँछ।', '8', 'Purple', 'Optimistic'),
('makara', CURDATE(), 'Career advancements possible.', 'कार्यक्षेत्रमा प्रगति सम्भव छ।', '10', 'Brown', 'Disciplined'),
('kumbha', CURDATE(), 'Innovation leads the way.', 'नवाचारले बाटो देखाउँछ।', '11', 'Electric', 'Inventive'),
('meena', CURDATE(), 'Spiritual growth is favored.', 'आध्यात्मिक विकास अनुकूल छ।', '12', 'Aqua', 'Compassionate')
ON DUPLICATE KEY UPDATE prediction=prediction;

-- ============================================
-- COMPLETE!
-- ============================================
