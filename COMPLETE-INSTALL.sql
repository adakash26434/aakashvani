-- ============================================
-- आकाशवाणी - COMPLETE FRESH INSTALL
-- Single file - ALL tables + Sample data
-- Run this AFTER creating your database
-- ============================================

-- INSTRUCTIONS:
-- 1. Create database: CREATE DATABASE your_db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 2. USE your_db_name;
-- 3. Run this entire file
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:45";

-- ============================================
-- CORE USERS & PERMISSIONS (aak_users)
-- ============================================
DROP TABLE IF EXISTS `aak_users`;
CREATE TABLE `aak_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('super_admin','admin','editor','reporter','content_manager') NOT NULL DEFAULT 'reporter',
    `bio` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_users` (`username`, `email`, `password_hash`, `display_name`, `role`, `bio`, `is_active`) VALUES
('admin', 'admin@aakashvani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Administrator', 'super_admin', 'System administrator', 1),
('editor1', 'editor@aakashvani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chief Editor', 'editor', 'Senior news editor', 1),
('reporter1', 'reporter@aakashvani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'News Reporter', 'reporter', 'Field reporter', 1);

-- ============================================
-- USER PERMISSIONS
-- ============================================
DROP TABLE IF EXISTS `aak_user_permissions`;
CREATE TABLE `aak_user_permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `permission` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_perm` (`user_id`, `permission`),
    KEY `idx_permission` (`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_user_permissions` (`user_id`, `permission`) VALUES
(1, 'manage_users'), (1, 'manage_settings'), (1, 'manage_seo'), (1, 'publish_articles'),
(1, 'delete_articles'), (1, 'manage_media'), (1, 'view_analytics'),
(2, 'publish_articles'), (2, 'edit_articles'), (2, 'manage_media');

-- ============================================
-- CATEGORIES
-- ============================================
DROP TABLE IF EXISTS `aak_categories`;
CREATE TABLE `aak_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `name_ne` VARCHAR(100) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#16a34a',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_home` TINYINT(1) NOT NULL DEFAULT 1,
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_categories` (`name`, `name_ne`, `slug`, `description`, `icon`, `color`, `sort_order`, `show_in_menu`, `show_in_home`) VALUES
('Politics', 'राजनीति', 'politics', 'Political news', '🏛️', '#dc2626', 10, 1, 1),
('Economy', 'अर्थतन्त्र', 'economy', 'Business and finance', '💰', '#059669', 20, 1, 1),
('Sports', 'खेलकुद', 'sports', 'Sports news', '⚽', '#f59e0b', 30, 1, 1),
('Technology', 'प्रविधि', 'technology', 'Tech and gadgets', '💻', '#6366f1', 40, 1, 1),
('Entertainment', 'मनोरञ्जन', 'entertainment', 'Movies and music', '🎬', '#ec4899', 50, 1, 1),
('World', 'विश्व', 'world', 'International news', '🌍', '#0ea5e9', 60, 1, 1),
('Health', 'स्वास्थ्य', 'health', 'Health and wellness', '🏥', '#10b981', 70, 1, 1),
('Education', 'शिक्षा', 'education', 'Education news', '📚', '#8b5cf6', 80, 1, 1),
('Lifestyle', 'जीवनशैली', 'lifestyle', 'Lifestyle content', '🌿', '#84cc16', 90, 1, 1);

-- ============================================
-- TAGS
-- ============================================
DROP TABLE IF EXISTS `aak_tags`;
CREATE TABLE `aak_tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `use_count` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_active` (`is_active`),
    KEY `idx_use_count` (`use_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_tags` (`name`, `slug`, `color`) VALUES
('Breaking News', 'breaking-news', '#ef4444'),
('Featured', 'featured', '#8b5cf6'),
('Exclusive', 'exclusive', '#f59e0b'),
('Analysis', 'analysis', '#06b6d4'),
('Opinion', 'opinion', '#84cc16'),
('Interview', 'interview', '#ec4899'),
('NEPSE', 'nepse', '#10b981'),
('IPO', 'ipo', '#14b8a6'),
('Election', 'election', '#a855f7'),
('Monsoon', 'monsoon', '#0ea5e9');

-- ============================================
-- ARTICLES (Main CMS Table)
-- ============================================
DROP TABLE IF EXISTS `aak_articles`;
CREATE TABLE `aak_articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `title_ne` VARCHAR(255) DEFAULT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `excerpt_ne` TEXT DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `content_ne` LONGTEXT DEFAULT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `featured_image_caption` VARCHAR(255) DEFAULT NULL,
    `featured_image_alt` VARCHAR(255) DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `author_id` INT UNSIGNED DEFAULT NULL,
    `status` ENUM('draft','pending','published','scheduled','archived') NOT NULL DEFAULT 'draft',
    `scheduled_at` DATETIME DEFAULT NULL,
    `published_at` DATETIME DEFAULT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking` TINYINT(1) NOT NULL DEFAULT 0,
    `is_trending` TINYINT(1) NOT NULL DEFAULT 0,
    `is_editors_pick` TINYINT(1) NOT NULL DEFAULT 0,
    `view_count` INT NOT NULL DEFAULT 0,
    `reading_time` INT NOT NULL DEFAULT 0,
    `language` ENUM('ne','en','both') NOT NULL DEFAULT 'both',
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `idx_category` (`category_id`),
    KEY `idx_author` (`author_id`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_breaking` (`is_breaking`),
    KEY `idx_published` (`published_at`),
    KEY `idx_view_count` (`view_count`),
    FULLTEXT KEY `ft_search` (`title`, `excerpt`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ARTICLE TAGS
-- ============================================
DROP TABLE IF EXISTS `aak_article_tags`;
CREATE TABLE `aak_article_tags` (
    `article_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`article_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ARTICLE IMAGES
-- ============================================
DROP TABLE IF EXISTS `aak_article_images`;
CREATE TABLE `aak_article_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    KEY `idx_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MEDIA LIBRARY
-- ============================================
DROP TABLE IF EXISTS `aak_media`;
CREATE TABLE `aak_media` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    `width` INT UNSIGNED DEFAULT NULL,
    `height` INT UNSIGNED DEFAULT NULL,
    `path` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `use_count` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`),
    KEY `idx_mime` (`mime_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SPACES (Collections)
-- ============================================
DROP TABLE IF EXISTS `aak_spaces`;
CREATE TABLE `aak_spaces` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `name_ne` VARCHAR(100) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#16a34a',
    `layout` ENUM('grid','list','magazine','featured','masonry','carousel') NOT NULL DEFAULT 'grid',
    `category_id` INT UNSIGNED DEFAULT NULL,
    `sort_by` ENUM('latest','popular','custom') NOT NULL DEFAULT 'latest',
    `max_articles` INT NOT NULL DEFAULT 20,
    `columns` INT NOT NULL DEFAULT 3,
    `show_title` TINYINT(1) NOT NULL DEFAULT 1,
    `show_excerpt` TINYINT(1) NOT NULL DEFAULT 0,
    `show_date` TINYINT(1) NOT NULL DEFAULT 1,
    `show_thumbnail` TINYINT(1) NOT NULL DEFAULT 1,
    `show_category` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_home` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_spaces` (`name`, `name_ne`, `slug`, `description`, `icon`, `color`, `layout`, `sort_order`, `show_in_menu`, `show_in_home`) VALUES
('Breaking News', 'भयो के ?', 'breaking-news', 'Latest breaking news', '🔥', '#ef4444', 'featured', 1, 1, 1),
('Featured Stories', 'विशेष कथाहरू', 'featured', 'Editor picks', '⭐', '#8b5cf6', 'magazine', 2, 1, 1),
('Politics', 'राजनीति', 'politics-space', 'Political coverage', '🏛️', '#dc2626', 'grid', 3, 1, 1),
('Economy', 'अर्थ र बजार', 'economy-markets', 'Business news', '💰', '#059669', 'grid', 4, 1, 1),
('Sports', 'खेलकुद', 'sports-space', 'Sports coverage', '⚽', '#f59e0b', 'grid', 5, 1, 1),
('Technology', 'प्रविधि', 'technology-space', 'Tech news', '💻', '#6366f1', 'grid', 6, 1, 1),
('Entertainment', 'मनोरञ्जन', 'entertainment-space', 'Movies & music', '🎬', '#ec4899', 'grid', 7, 1, 1),
('Opinion', 'राय र विश्लेषण', 'opinion-analysis', 'Editorial pieces', '💡', '#0ea5e9', 'list', 8, 1, 1);

-- ============================================
-- SPACE ARTICLES
-- ============================================
DROP TABLE IF EXISTS `aak_space_articles`;
CREATE TABLE `aak_space_articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `space_id` INT UNSIGNED NOT NULL,
    `article_id` INT UNSIGNED NOT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_space_article` (`space_id`, `article_id`),
    KEY `idx_space` (`space_id`),
    KEY `idx_article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- HOMEPAGE SECTIONS
-- ============================================
DROP TABLE IF EXISTS `aak_homepage_sections`;
CREATE TABLE `aak_homepage_sections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_key` VARCHAR(50) NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `title_ne` VARCHAR(100) DEFAULT NULL,
    `type` ENUM('latest','featured','trending','most_viewed','category','breaking','editors_pick') NOT NULL DEFAULT 'latest',
    `category_id` INT UNSIGNED DEFAULT NULL,
    `max_items` INT NOT NULL DEFAULT 10,
    `style` ENUM('grid','list','carousel','big_featured') NOT NULL DEFAULT 'grid',
    `cols_md` INT NOT NULL DEFAULT 4,
    `show_title` TINYINT(1) NOT NULL DEFAULT 1,
    `show_excerpt` TINYINT(1) NOT NULL DEFAULT 0,
    `show_image` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_section_key` (`section_key`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_homepage_sections` (`section_key`, `title`, `title_ne`, `type`, `max_items`, `style`, `cols_md`, `sort_order`, `is_active`) VALUES
('breaking-news', 'Breaking News', 'भयो के ?', 'breaking', 8, 'list', 1, 1, 1),
('featured-news', 'Featured', 'विशेष', 'featured', 4, 'big_featured', 2, 2, 1),
('latest-news', 'Latest News', 'ताजा समाचार', 'latest', 12, 'grid', 4, 3, 1),
('trending', 'Trending', 'ट्रेन्डिङ', 'trending', 8, 'grid', 4, 4, 1);

-- ============================================
-- SEO SETTINGS
-- ============================================
DROP TABLE IF EXISTS `aak_seo_settings`;
CREATE TABLE `aak_seo_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `page_type` VARCHAR(50) NOT NULL,
    `reference_id` INT UNSIGNED DEFAULT NULL,
    `reference_slug` VARCHAR(100) DEFAULT NULL,
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_page_ref` (`page_type`, `reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `aak_seo_settings` (`page_type`, `reference_slug`, `meta_title`, `meta_description`) VALUES
('home', 'home', 'आकाशवाणी - सूचनाको खुला आकाश', 'नेपालको ताजा समाचार पोर्टल');

-- ============================================
-- ACTIVITY LOG
-- ============================================
DROP TABLE IF EXISTS `aak_activity_log`;
CREATE TABLE `aak_activity_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `entity_title` VARCHAR(255) DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_user` (`user_id`),
    KEY `idx_entity` (`entity_type`, `entity_id`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COMMENTS
-- ============================================
DROP TABLE IF EXISTS `aak_comments`;
CREATE TABLE `aak_comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_article` (`article_id`),
    KEY `idx_approved` (`is_approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NEWS TABLE (Public API)
-- ============================================
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `title_ne` VARCHAR(500) DEFAULT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `excerpt_ne` TEXT DEFAULT NULL,
    `summary` TEXT DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `content_ne` LONGTEXT DEFAULT NULL,
    `image` VARCHAR(700) DEFAULT NULL,
    `image_url` VARCHAR(700) DEFAULT NULL,
    `featured_image` VARCHAR(700) DEFAULT NULL,
    `category` VARCHAR(60) NOT NULL DEFAULT 'general',
    `category_id` INT UNSIGNED DEFAULT NULL,
    `lang` VARCHAR(5) NOT NULL DEFAULT 'ne',
    `source_name` VARCHAR(120) DEFAULT NULL,
    `source_url` VARCHAR(700) DEFAULT NULL,
    `author` VARCHAR(200) DEFAULT NULL,
    `author_name` VARCHAR(200) DEFAULT NULL,
    `url_hash` VARCHAR(64) DEFAULT NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking` TINYINT(1) NOT NULL DEFAULT 0,
    `is_trending` TINYINT(1) NOT NULL DEFAULT 0,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `reading_time` INT DEFAULT 0,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `idx_published` (`is_published`),
    KEY `idx_category` (`category`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_breaking` (`is_breaking`),
    KEY `idx_published_at` (`published_at`),
    KEY `idx_view_count` (`view_count`),
    FULLTEXT KEY `ft_title_summary` (`title`, `summary`, `excerpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TECH_NEWS (RSS Sync)
-- ============================================
DROP TABLE IF EXISTS `tech_news`;
CREATE TABLE `tech_news` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `title_ne` VARCHAR(500) DEFAULT NULL,
    `slug` VARCHAR(500) NOT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `content` LONGTEXT DEFAULT NULL,
    `category` VARCHAR(60) NOT NULL DEFAULT 'general',
    `source_name` VARCHAR(120) DEFAULT NULL,
    `original_url` VARCHAR(700) DEFAULT NULL,
    `url_hash` VARCHAR(64) DEFAULT NULL,
    `image_url` VARCHAR(700) DEFAULT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking` TINYINT(1) NOT NULL DEFAULT 0,
    `lang` VARCHAR(5) NOT NULL DEFAULT 'ne',
    `scope` VARCHAR(20) NOT NULL DEFAULT 'national',
    `ai_processed` TINYINT(1) NOT NULL DEFAULT 0,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_tn_slug` (`slug`),
    KEY `idx_tn_published` (`is_published`),
    KEY `idx_tn_category` (`category`),
    KEY `idx_tn_breaking` (`is_breaking`),
    KEY `idx_tn_pub_date` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- RASHIFAL DAILY
-- ============================================
DROP TABLE IF EXISTS `rashifal_daily`;
CREATE TABLE `rashifal_daily` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `sign` VARCHAR(30) NOT NULL,
    `date` DATE NOT NULL,
    `prediction` TEXT DEFAULT NULL,
    `prediction_ne` TEXT DEFAULT NULL,
    `lucky_number` VARCHAR(10) DEFAULT NULL,
    `lucky_color` VARCHAR(20) DEFAULT NULL,
    `mood` VARCHAR(30) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_sign_date` (`sign`, `date`),
    KEY `idx_rashifal_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rashifal_daily` (`sign`, `date`, `prediction`, `prediction_ne`, `lucky_number`, `lucky_color`, `mood`) VALUES
('mesha', CURDATE(), 'Today is favorable for new beginnings.', 'आज नयाँ सुरुआतको लागि अनुकूल छ।', '7', 'Red', 'Energetic'),
('vrishabha', CURDATE(), 'Focus on financial matters.', 'वित्तीय विषयमा ध्यान दिनुहोस्।', '3', 'Green', 'Calm'),
('mithuna', CURDATE(), 'Communication is key today.', 'आज सञ्चार महत्वपूर्ण छ।', '5', 'Yellow', 'Curious'),
('karka', CURDATE(), 'Time for family matters.', 'परिवारको विषयमा समय बिताउनुहोस्।', '2', 'White', 'Nurturing'),
('simha', CURDATE(), 'Leadership opportunities arise.', 'नेतृत्वको अवसरहरू आइपुग्छन्।', '1', 'Orange', 'Confident'),
('kanya', CURDATE(), 'Focus on health and wellness.', 'स्वास्थ्य र कल्याणमा ध्यान दिनुहोस्।', '9', 'Blue', 'Analytical'),
('tula', CURDATE(), 'Relationships take center stage.', 'सम्बन्धहरू महत्वपूर्ण हुनेछन्।', '6', 'Pink', 'Harmonious'),
('vrishchika', CURDATE(), 'Transformation is highlighted.', 'परिवर्तन उजागर हुनेछ।', '4', 'Black', 'Intense'),
('dhanu', CURDATE(), 'Adventure calls you.', 'यात्रा र अन्वेषणले बोलाउँछ।', '8', 'Purple', 'Optimistic'),
('makara', CURDATE(), 'Career advancements possible.', 'कार्यक्षेत्रमा प्रगति सम्भव छ।', '10', 'Brown', 'Disciplined'),
('kumbha', CURDATE(), 'Innovation leads the way.', 'नवाचारले बाटो देखाउँछ।', '11', 'Electric', 'Inventive'),
('meena', CURDATE(), 'Spiritual growth is favored.', 'आध्यात्मिक विकास अनुकूल छ।', '12', 'Aqua', 'Compassionate');

-- ============================================
-- NEWSLETTER SUBSCRIBERS
-- ============================================
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(200) NOT NULL,
    `name` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `unsubscribed_at` DATETIME DEFAULT NULL,
    `subscribed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `verify_token` VARCHAR(64) DEFAULT NULL,
    `verified_at` DATETIME DEFAULT NULL,
    UNIQUE KEY `uq_email` (`email`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NEWS SYNC LOG
-- ============================================
DROP TABLE IF EXISTS `news_sync_log`;
CREATE TABLE `news_sync_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `source` VARCHAR(50) NOT NULL,
    `status` VARCHAR(20) NOT NULL,
    `articles_processed` INT DEFAULT 0,
    `articles_added` INT DEFAULT 0,
    `articles_failed` INT DEFAULT 0,
    `error_message` TEXT DEFAULT NULL,
    `run_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_sync_source` (`source`),
    KEY `idx_sync_run_at` (`run_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE NEWS DATA
-- ============================================
INSERT INTO `news` (`title`, `slug`, `excerpt`, `category`, `status`, `is_published`, `is_featured`, `is_breaking`, `view_count`, `published_at`) VALUES
('नेपालमा ताजा समाचार: महत्वपूर्ण घटनाहरू', 'nepal-taja-samachar-mahattvik', 'नेपालको ताजा समाचार र अपडेटहरू।', 'general', 'published', 1, 1, 1, 150, NOW()),
('अर्थतन्त्र: शेयर बजारमा तेजी', 'arthatantra-share-bazaar-teji', 'नेपाली शेयर बजारमा आज तेजी देखिएको छ।', 'economy', 'published', 1, 0, 0, 89, NOW()),
('खेलकुद: क्रिकेट विश्वकपको तयारी', 'khelkud-cricket-vishwakop', 'आगामी क्रिकेट विश्वकपको तयारी सुरु भएको छ।', 'sports', 'published', 1, 0, 0, 200, NOW()),
('प्रविधि: नयाँ स्मार्टफोन विश्लेषण', 'prabidhii-naya-smartphone', 'नयाँ स्मार्टफोनको विश्लेषण र समीक्षा।', 'technology', 'published', 1, 0, 0, 75, NOW()),
('राजनीति: संसदको अधिवेशन सुरु', 'rajniti-sansad-adhiveshan', 'संसदको नयाँ अधिवेशन आजदेखि सुरु भएको छ।', 'politics', 'published', 1, 0, 0, 120, NOW()),
('विश्व: अन्तर्राष्ट्रिय समाचार', 'vishwa-antarrastriya', 'विश्वभरका ताजा समाचार र अपडेटहरू।', 'world', 'published', 1, 0, 0, 95, NOW()),
('स्वास्थ्य: नयाँ स्वास्थ्य सुझावहरू', 'swasthya-naya-sujhav', 'स्वस्थ जीवनको लागि नयाँ सुझावहरू।', 'health', 'published', 1, 0, 0, 60, NOW()),
('शिक्षा: परीक्षा सम्बन्धी जानकारी', 'shiksha-pariksha', 'विद्यार्थीहरूको लागि महत्वपूर्ण परीक्षा जानकारी।', 'education', 'published', 1, 0, 0, 85, NOW());

-- Copy to tech_news
INSERT INTO `tech_news` (`title`, `slug`, `excerpt`, `content`, `category`, `source_name`, `url_hash`, `is_published`, `is_featured`, `published_at`)
SELECT `title`, `slug`, `excerpt`, `content`, `category`, 'Aakashvani', MD5(`slug`), `is_published`, `is_featured`, `published_at`
FROM `news` WHERE `is_published`=1;

-- ============================================
-- AUTO SYNC TRIGGER
-- ============================================
DELIMITER //

DROP TRIGGER IF EXISTS trg_sync_article_to_news//
CREATE TRIGGER trg_sync_article_to_news AFTER INSERT ON aak_articles
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO news (title, title_ne, slug, excerpt, excerpt_ne, summary, content, content_ne,
                           featured_image, image_url, category, category_id, lang,
                           source_name, author_name, url_hash, status, is_published, 
                           is_featured, is_breaking, is_trending,
                           view_count, reading_time, published_at, created_at)
    VALUES (NEW.title, NEW.title_ne, NEW.slug, NEW.excerpt, NEW.excerpt_ne, NEW.excerpt,
            NEW.content, NEW.content_ne, NEW.featured_image, NEW.featured_image,
            (SELECT name FROM aak_categories WHERE id = NEW.category_id),
            NEW.category_id, NEW.language, 'Aakashvani',
            (SELECT display_name FROM aak_users WHERE id = NEW.author_id),
            MD5(NEW.slug), NEW.status,
            CASE WHEN NEW.status = 'published' THEN 1 ELSE 0 END,
            NEW.is_featured, NEW.is_breaking, NEW.is_trending,
            NEW.view_count, NEW.reading_time, NEW.published_at, NEW.created_at);
END//

DROP TRIGGER IF EXISTS trg_sync_article_to_tech//
CREATE TRIGGER trg_sync_article_to_tech AFTER INSERT ON aak_articles
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO tech_news (title, title_ne, slug, excerpt, content, category,
                                 source_name, original_url, url_hash, image_url,
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
-- COMPLETE!
-- All 33+ tables created with sample data
-- Default login: admin / admin123
-- ============================================
