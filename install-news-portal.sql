-- ============================================
-- а§Жа§Ха§Ња§ґа§µа§Ња§£а•А News Portal - Complete Database Setup
-- Version: 1.0
-- Date: 2026-07-09
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:45";
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================
-- DATABASE CREATION
-- ============================================
CREATE DATABASE IF NOT EXISTS `{DB_NAME}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `{DB_NAME}`;

-- ============================================
-- TABLE: aak_users
-- User accounts with role-based access
-- ============================================
DROP TABLE IF EXISTS `aak_users`;
CREATE TABLE `aak_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_active` (`is_active`),
    KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts for news portal';

-- Default admin user (password: admin123 - CHANGE IN PRODUCTION!)
INSERT INTO `aak_users` (`username`, `email`, `password_hash`, `display_name`, `role`, `bio`, `is_active`, `created_at`) VALUES
('admin', 'admin@aakashvani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Administrator', 'super_admin', 'System administrator with full access', 1, NOW()),
('editor1', 'editor@aakashvani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chief Editor', 'editor', 'Senior news editor', 1, NOW()),
('reporter1', 'reporter@aakashvani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'News Reporter', 'reporter', 'Field reporter covering politics and economy', 1, NOW());

-- ============================================
-- TABLE: aak_user_permissions
-- Granular permissions for users
-- ============================================
DROP TABLE IF EXISTS `aak_user_permissions`;
CREATE TABLE `aak_user_permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `permission` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_perm` (`user_id`, `permission`),
    KEY `idx_permission` (`permission`),
    CONSTRAINT `fk_user_perm_user` FOREIGN KEY (`user_id`) REFERENCES `aak_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User-specific permissions';

-- Admin permissions
INSERT INTO `aak_user_permissions` (`user_id`, `permission`) VALUES
(1, 'manage_users'),
(1, 'manage_settings'),
(1, 'manage_seo'),
(1, 'manage_ads'),
(1, 'publish_articles'),
(1, 'delete_articles'),
(1, 'manage_media'),
(1, 'view_analytics'),
(1, 'manage_comments');

-- Editor permissions
INSERT INTO `aak_user_permissions` (`user_id`, `permission`) VALUES
(2, 'publish_articles'),
(2, 'edit_articles'),
(2, 'manage_media'),
(2, 'manage_comments');

-- ============================================
-- TABLE: aak_categories
-- Hierarchical news categories
-- ============================================
DROP TABLE IF EXISTS `aak_categories`;
CREATE TABLE `aak_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `name_ne` VARCHAR(100) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `description_ne` TEXT DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#16a34a',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_home` TINYINT(1) NOT NULL DEFAULT 1,
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`),
    KEY `idx_menu` (`show_in_menu`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `aak_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='News categories with hierarchy';

-- Default categories
INSERT INTO `aak_categories` (`name`, `name_ne`, `slug`, `description`, `icon`, `color`, `sort_order`, `show_in_menu`, `show_in_home`) VALUES
('Politics', 'а§∞а§Ња§Ьа§®а•Аа§§а§њ', 'politics', 'Political news and updates from Nepal and around the world', 'рЯПЫпЄП', '#dc2626', 10, 1, 1),
('Economy', 'а§Еа§∞а•На§•а§§а§®а•На§§а•На§∞', 'economy', 'Business, finance, and economic news', 'рЯТ∞', '#059669', 20, 1, 1),
('Sports', 'а§Ца•За§≤а§Ха•Ба§¶', 'sports', 'Sports news including cricket, football, and more', 'вЪљ', '#f59e0b', 30, 1, 1),
('Technology', 'а§™а•На§∞а§µа§ња§Іа§њ', 'technology', 'Technology, gadgets, and digital trends', 'рЯТї', '#6366f1', 40, 1, 1),
('Entertainment', 'а§Ѓа§®а•Ла§∞а§Юа•На§Ьа§®', 'entertainment', 'Movies, music, celebrity news, and entertainment', 'рЯОђ', '#ec4899', 50, 1, 1),
('World', 'а§µа§ња§ґа•На§µ', 'world', 'International news and global affairs', 'рЯМН', '#0ea5e9', 60, 1, 1),
('Health', 'а§Єа•На§µа§Ња§Єа•На§•а•На§ѓ', 'health', 'Health tips, medical news, and wellness', 'рЯП•', '#10b981', 70, 1, 1),
('Education', 'а§ґа§ња§Ха•На§Ја§Њ', 'education', 'Education news, exams, and career guidance', 'рЯУЪ', '#8b5cf6', 80, 1, 1),
('Lifestyle', 'а§Ьа•Аа§µа§®а§ґа•Иа§≤а•А', 'lifestyle', 'Lifestyle, travel, food, and fashion', 'рЯМњ', '#84cc16', 90, 1, 1);

-- Subcategories for Politics
INSERT INTO `aak_categories` (`parent_id`, `name`, `name_ne`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
(1, 'Parliament', 'а§Єа§Ва§Єа§¶', 'parliament', 'Parliament and legislative news', 'рЯПЫпЄП', '#dc2626', 1),
(1, 'Elections', 'а§®а§ња§∞а•На§µа§Ња§Ъа§®', 'elections', 'Election news and updates', 'рЯЧ≥пЄП', '#dc2626', 2),
(1, 'Government', 'а§Єа§∞а§Ха§Ња§∞', 'government', 'Government policies and decisions', 'вЪЦпЄП', '#dc2626', 3);

-- Subcategories for Economy
INSERT INTO `aak_categories` (`parent_id`, `name`, `name_ne`, `slug`, `description`, `icon`, `color`, `sort_order`) VALUES
(2, 'Stock Market', 'а§ґа•За§ѓа§∞ а§ђа§Ьа§Ња§∞', 'stock-market', 'NEPSE and stock market updates', 'рЯУИ', '#059669', 1),
(2, 'Banking', 'а§ђа•Иа§Ва§Ха§ња§Щ', 'banking', 'Banking and financial news', 'рЯП¶', '#059669', 2),
(2, 'Agriculture', 'а§Ха•Га§Ја§њ', 'agriculture', 'Agriculture and rural economy', 'рЯМЊ', '#059669', 3);

-- ============================================
-- TABLE: aak_tags
-- Article tags for categorization
-- ============================================
DROP TABLE IF EXISTS `aak_tags`;
CREATE TABLE `aak_tags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `use_count` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_active` (`is_active`),
    KEY `idx_use_count` (`use_count` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Article tags';

-- Default tags
INSERT INTO `aak_tags` (`name`, `slug`, `description`, `color`, `use_count`) VALUES
('Breaking News', 'breaking-news', 'Urgent and breaking news', '#ef4444', 0),
('Featured', 'featured', 'Featured articles', '#8b5cf6', 0),
('Exclusive', 'exclusive', 'Exclusive coverage', '#f59e0b', 0),
('Analysis', 'analysis', 'In-depth analysis', '#06b6d4', 0),
('Opinion', 'opinion', 'Opinion pieces', '#84cc16', 0),
('Interview', 'interview', 'Interview articles', '#ec4899', 0),
('NEPSE', 'nepse', 'Stock market news', '#10b981', 0),
('IPO', 'ipo', 'IPO and public issues', '#14b8a6', 0),
('Budget', 'budget', 'Budget and finance', '#f97316', 0),
('Election', 'election', 'Election coverage', '#a855f7', 0),
('Monsoon', 'monsoon', 'Weather and monsoon', '#0ea5e9', 0),
('Tourism', 'tourism', 'Travel and tourism', '#22c55e', 0);

-- ============================================
-- TABLE: aak_articles
-- Main news articles table
-- ============================================
DROP TABLE IF EXISTS `aak_articles`;
CREATE TABLE `aak_articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
    `archived_at` DATETIME DEFAULT NULL,
    
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking` TINYINT(1) NOT NULL DEFAULT 0,
    `is_trending` TINYINT(1) NOT NULL DEFAULT 0,
    `is_editors_pick` TINYINT(1) NOT NULL DEFAULT 0,
    
    `view_count` INT NOT NULL DEFAULT 0,
    `reading_time` INT NOT NULL DEFAULT 0,
    `share_count` INT NOT NULL DEFAULT 0,
    
    `language` ENUM('ne','en','both') NOT NULL DEFAULT 'both',
    
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `meta_robots` VARCHAR(100) DEFAULT 'index, follow',
    `og_image` VARCHAR(255) DEFAULT NULL,
    `canonical_url` VARCHAR(255) DEFAULT NULL,
    `schema_type` VARCHAR(50) DEFAULT 'Article',
    
    `source_name` VARCHAR(100) DEFAULT NULL,
    `source_url` VARCHAR(255) DEFAULT NULL,
    
    `created_by` INT UNSIGNED DEFAULT NULL,
    `updated_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `idx_category` (`category_id`),
    KEY `idx_author` (`author_id`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_breaking` (`is_breaking`),
    KEY `idx_trending` (`is_trending`),
    KEY `idx_editors_pick` (`is_editors_pick`),
    KEY `idx_published` (`published_at`),
    KEY `idx_view_count` (`view_count` DESC),
    KEY `idx_scheduled` (`scheduled_at`),
    KEY `idx_deleted` (`deleted_at`),
    KEY `idx_language` (`language`),
    KEY `idx_created` (`created_at` DESC),
    FULLTEXT KEY `ft_search` (`title`, `title_ne`, `excerpt`, `excerpt_ne`, `content`),
    CONSTRAINT `fk_article_category` FOREIGN KEY (`category_id`) REFERENCES `aak_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_article_author` FOREIGN KEY (`author_id`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_article_created_by` FOREIGN KEY (`created_by`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_article_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='News articles';

-- ============================================
-- TABLE: aak_article_tags
-- Many-to-many relationship between articles and tags
-- ============================================
DROP TABLE IF EXISTS `aak_article_tags`;
CREATE TABLE `aak_article_tags` (
    `article_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`article_id`, `tag_id`),
    KEY `idx_tag` (`tag_id`),
    CONSTRAINT `fk_at_article` FOREIGN KEY (`article_id`) REFERENCES `aak_articles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_at_tag` FOREIGN KEY (`tag_id`) REFERENCES `aak_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Article-tag relationships';

-- ============================================
-- TABLE: aak_article_images
-- Gallery images for articles
-- ============================================
DROP TABLE IF EXISTS `aak_article_images`;
CREATE TABLE `aak_article_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_article` (`article_id`),
    KEY `idx_sort` (`sort_order`),
    CONSTRAINT `fk_ai_article` FOREIGN KEY (`article_id`) REFERENCES `aak_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Article gallery images';

-- ============================================
-- TABLE: aak_media
-- Central media library
-- ============================================
DROP TABLE IF EXISTS `aak_media`;
CREATE TABLE `aak_media` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `folder_id` INT UNSIGNED DEFAULT NULL,
    
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` INT UNSIGNED NOT NULL,
    
    `width` INT UNSIGNED DEFAULT NULL,
    `height` INT UNSIGNED DEFAULT NULL,
    `duration` INT UNSIGNED DEFAULT NULL COMMENT 'For audio/video',
    
    `path` VARCHAR(255) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `preview` VARCHAR(255) DEFAULT NULL,
    
    `caption` VARCHAR(255) DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    
    `folder` VARCHAR(100) DEFAULT NULL,
    `tags` VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated tags',
    
    `use_count` INT NOT NULL DEFAULT 0,
    `download_count` INT NOT NULL DEFAULT 0,
    
    `is_public` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_folder` (`folder`),
    KEY `idx_mime` (`mime_type`),
    KEY `idx_created` (`created_at` DESC),
    KEY `idx_active` (`is_active`),
    CONSTRAINT `fk_media_user` FOREIGN KEY (`user_id`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Media library';

-- ============================================
-- TABLE: aak_homepage_sections
-- Configurable homepage sections
-- ============================================
DROP TABLE IF EXISTS `aak_homepage_sections`;
CREATE TABLE `aak_homepage_sections` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_key` VARCHAR(50) NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `title_ne` VARCHAR(100) DEFAULT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    
    `type` ENUM('latest','featured','trending','most_viewed','category','custom','breaking','editors_pick','gallery','video','related','popular_tags') NOT NULL DEFAULT 'latest',
    `category_id` INT UNSIGNED DEFAULT NULL,
    `article_ids` TEXT DEFAULT NULL COMMENT 'JSON array of specific article IDs',
    
    `max_items` INT NOT NULL DEFAULT 10,
    `min_items` INT NOT NULL DEFAULT 1,
    `style` ENUM('grid','list','carousel','big_featured','compact','magazine','sidebar') NOT NULL DEFAULT 'grid',
    
    `layout` VARCHAR(50) DEFAULT 'default',
    `cols_md` INT NOT NULL DEFAULT 4,
    `cols_sm` INT NOT NULL DEFAULT 2,
    `cols_xs` INT NOT NULL DEFAULT 1,
    `gap` INT NOT NULL DEFAULT 4,
    
    `show_title` TINYINT(1) NOT NULL DEFAULT 1,
    `show_subtitle` TINYINT(1) NOT NULL DEFAULT 0,
    `show_excerpt` TINYINT(1) NOT NULL DEFAULT 0,
    `show_image` TINYINT(1) NOT NULL DEFAULT 1,
    `show_category` TINYINT(1) NOT NULL DEFAULT 1,
    `show_author` TINYINT(1) NOT NULL DEFAULT 0,
    `show_date` TINYINT(1) NOT NULL DEFAULT 1,
    `show_views` TINYINT(1) NOT NULL DEFAULT 0,
    `show_read_more` TINYINT(1) NOT NULL DEFAULT 0,
    
    `show_pagination` TINYINT(1) NOT NULL DEFAULT 0,
    `show_navigation` TINYINT(1) NOT NULL DEFAULT 0,
    
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_cacheable` TINYINT(1) NOT NULL DEFAULT 1,
    `cache_ttl` INT NOT NULL DEFAULT 300,
    
    `sort_order` INT NOT NULL DEFAULT 0,
    `container` ENUM('full','boxed','compact') NOT NULL DEFAULT 'boxed',
    
    `background_color` VARCHAR(7) DEFAULT NULL,
    `background_image` VARCHAR(255) DEFAULT NULL,
    `custom_css` TEXT DEFAULT NULL,
    
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `seo_description` VARCHAR(300) DEFAULT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_section_key` (`section_key`),
    KEY `idx_active` (`is_active`),
    KEY `idx_sort` (`sort_order`),
    KEY `idx_type` (`type`),
    KEY `idx_category` (`category_id`),
    CONSTRAINT `fk_hs_category` FOREIGN KEY (`category_id`) REFERENCES `aak_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Homepage section configuration';

-- Default homepage sections
INSERT INTO `aak_homepage_sections` (`section_key`, `title`, `title_ne`, `subtitle`, `type`, `max_items`, `style`, `cols_md`, `show_title`, `show_excerpt`, `show_image`, `sort_order`, `is_active`) VALUES
('breaking-news', 'Breaking News', 'а§≠а§ѓа•Л а§Ха•З ?', 'Latest breaking news updates', 'breaking', 8, 'list', 1, 1, 0, 1, 1, 1),
('featured-news', 'Featured Stories', 'а§µа§ња§ґа•За§Ј а§Ха§•а§Ња§єа§∞а•В', 'Top featured stories of the day', 'featured', 4, 'big_featured', 2, 1, 1, 1, 2, 1),
('latest-news', 'Latest News', 'а§§а§Ња§Ьа§Њ а§Єа§Ѓа§Ња§Ъа§Ња§∞', 'Recently published articles', 'latest', 12, 'grid', 4, 1, 1, 1, 3, 1),
('trending-news', 'Trending Now', 'а§Яа•На§∞а•За§®а•На§°а§ња§Щ', 'Most popular articles today', 'trending', 8, 'compact', 4, 1, 0, 1, 4, 1),
('politics-news', 'Politics', 'а§∞а§Ња§Ьа§®а•Аа§§а§њ', 'Latest from politics', 'category', 6, 'grid', 3, 1, 1, 1, 5, 1, 1),
('economy-news', 'Economy & Finance', 'а§Еа§∞а•На§• а§∞ а§µа§ња§§а•На§§', 'Business and financial news', 'category', 6, 'grid', 3, 1, 1, 1, 6, 1, 2),
('sports-news', 'Sports', 'а§Ца•За§≤а§Ха•Ба§¶', 'Sports updates', 'category', 6, 'grid', 3, 1, 1, 1, 7, 1, 3),
('editors-choice', 'Editor''s Choice', 'а§Єа§Ѓа•На§™а§Ња§¶а§Ха§Ха•Л а§Ыа§®а•Ла§Я', 'Handpicked stories by our editors', 'editors_pick', 6, 'magazine', 3, 1, 1, 1, 8, 1),
('most-read', 'Most Read', 'а§Іа•За§∞а•И а§™а§Ґа§ња§Па§Ха§Њ', 'Articles with highest views', 'most_viewed', 10, 'list', 1, 1, 0, 1, 9, 1),
('popular-tags', 'Popular Tags', 'а§≤а•Ла§Ха§™а•На§∞а§ња§ѓ а§Яа•На§ѓа§Ња§Ча§єа§∞а•В', 'Browse by popular topics', 'popular_tags', 20, 'compact', 6, 1, 0, 0, 10, 1);

-- ============================================
-- TABLE: aak_seo_settings
-- Page-specific SEO configuration
-- ============================================
DROP TABLE IF EXISTS `aak_seo_settings`;
CREATE TABLE `aak_seo_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_type` VARCHAR(50) NOT NULL COMMENT 'home, category, tag, article, page, etc',
    `reference_id` INT UNSIGNED DEFAULT NULL COMMENT 'Category/Tag/Article ID if applicable',
    `reference_slug` VARCHAR(100) DEFAULT NULL,
    
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `meta_robots` VARCHAR(100) DEFAULT 'index, follow',
    
    `og_title` VARCHAR(150) DEFAULT NULL,
    `og_description` VARCHAR(300) DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    `og_type` VARCHAR(50) DEFAULT 'website',
    
    `twitter_card` VARCHAR(50) DEFAULT 'summary_large_image',
    `twitter_title` VARCHAR(150) DEFAULT NULL,
    `twitter_description` VARCHAR(300) DEFAULT NULL,
    `twitter_image` VARCHAR(255) DEFAULT NULL,
    
    `canonical_url` VARCHAR(255) DEFAULT NULL,
    `redirect_url` VARCHAR(255) DEFAULT NULL COMMENT '301 redirect target',
    
    `schema_markup` TEXT DEFAULT NULL COMMENT 'Custom JSON-LD schema',
    
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_page_ref` (`page_type`, `reference_id`),
    UNIQUE KEY `uq_page_slug` (`page_type`, `reference_slug`),
    KEY `idx_page_type` (`page_type`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SEO settings per page';

-- Default SEO settings
INSERT INTO `aak_seo_settings` (`page_type`, `reference_slug`, `meta_title`, `meta_description`, `meta_robots`) VALUES
('home', 'home', 'а§Жа§Ха§Ња§ґа§µа§Ња§£а•А - а§Єа•Ва§Ъа§®а§Ња§Ха•Л а§Ца•Ба§≤а§Њ а§Жа§Ха§Ња§ґ', 'а§®а•За§™а§Ња§≤а§Ха•Л а§®а§µа•Аа§®а§§а§Ѓ а§Єа§Ѓа§Ња§Ъа§Ња§∞, а§ђа§Ьа§Ња§∞, а§Ца•За§≤, а§Ѓа§®а•Ла§∞а§Юа•На§Ьа§® а§∞ а§•а§™а•§ а§Жа§Ха§Ња§ґа§µа§Ња§£а•А - а§§а§™а§Ња§Иа§Ва§Ха•Л а§Єа•Ва§Ъа§®а§Њ а§Ча•За§Яа§µа•За•§', 'index, follow'),
('category', 'politics', 'а§∞а§Ња§Ьа§®а•Аа§§а§њ а§Єа§Ѓа§Ња§Ъа§Ња§∞ | а§Жа§Ха§Ња§ґа§µа§Ња§£а•А', 'а§®а•За§™а§Ња§≤ а§∞ а§µа§ња§ґа•На§µ а§∞а§Ња§Ьа§®а•Аа§§а§ња§Ха§Њ а§§а§Ња§Ьа§Њ а§Єа§Ѓа§Ња§Ъа§Ња§∞ а§∞ а§µа§ња§ґа•На§≤а•За§Ја§£а•§', 'index, follow'),
('category', 'economy', 'а§Еа§∞а•На§•а§§а§®а•На§§а•На§∞ а§∞ а§ђа§Ьа§Ња§∞ а§Єа§Ѓа§Ња§Ъа§Ња§∞ | а§Жа§Ха§Ња§ґа§µа§Ња§£а•А', 'а§ґа•За§ѓа§∞ а§ђа§Ьа§Ња§∞, а§ђа•Иа§Ва§Ха§ња§Щ, а§∞ а§®а•За§™а§Ња§≤а•А а§Еа§∞а•На§•а§§а§®а•На§§а•На§∞а§Ха§Њ а§§а§Ња§Ьа§Њ а§Єа§Ѓа§Ња§Ъа§Ња§∞а•§', 'index, follow'),
('category', 'sports', 'а§Ца•За§≤а§Ха•Ба§¶ а§Єа§Ѓа§Ња§Ъа§Ња§∞ | а§Жа§Ха§Ња§ґа§µа§Ња§£а•А', 'а§Ха•На§∞а§ња§Ха•За§Я, а§Ђа•Ба§Яа§ђа§≤ а§∞ а§®а•За§™а§Ња§≤а•А а§Ца•За§≤а§Ха•Ба§¶а§Ха§Њ а§§а§Ња§Ьа§Њ а§Єа§Ѓа§Ња§Ъа§Ња§∞а•§', 'index, follow'),
('category', 'technology', 'а§™а•На§∞а§µа§ња§Іа§њ а§Єа§Ѓа§Ња§Ъа§Ња§∞ | а§Жа§Ха§Ња§ґа§µа§Ња§£а•А', 'а§Яа•За§Ха•На§®а•Ла§≤а•Ла§Ьа•А, а§Ча•На§ѓа§Ња§Ьа•За§Яа•На§Є а§∞ а§°а§ња§Ьа§ња§Яа§≤ а§Яа•На§∞а•За§Ва§°а§єа§∞а•Ва§Ха•Л а§Єа§Ѓа§Ња§Ъа§Ња§∞а•§', 'index, follow');

-- ============================================
-- TABLE: aak_activity_log
-- Admin activity tracking
-- ============================================
DROP TABLE IF EXISTS `aak_activity_log`;
CREATE TABLE `aak_activity_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL COMMENT 'create, update, delete, login, logout, publish, etc',
    `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'article, category, tag, user, media, settings',
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `entity_title` VARCHAR(255) DEFAULT NULL COMMENT 'Title or name of affected entity',
    `details` JSON DEFAULT NULL COMMENT 'Additional action details',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_entity` (`entity_type`, `entity_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created` (`created_at` DESC),
    CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin activity log';

-- ============================================
-- TABLE: aak_comments
-- Article comments
-- ============================================
DROP TABLE IF EXISTS `aak_comments`;
CREATE TABLE `aak_comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL COMMENT 'Registered user (if any)',
    
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `depth` INT NOT NULL DEFAULT 0,
    
    `name` VARCHAR(100) DEFAULT NULL COMMENT 'Guest name',
    `email` VARCHAR(100) DEFAULT NULL COMMENT 'Guest email (not displayed)',
    `website` VARCHAR(255) DEFAULT NULL,
    
    `content` TEXT NOT NULL,
    `content_html` TEXT DEFAULT NULL,
    
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
    `is_spam` TINYINT(1) NOT NULL DEFAULT 0,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    
    `upvotes` INT NOT NULL DEFAULT 0,
    `downvotes` INT NOT NULL DEFAULT 0,
    
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `approved_at` DATETIME DEFAULT NULL,
    
    PRIMARY KEY (`id`),
    KEY `idx_article` (`article_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_parent` (`parent_id`),
    KEY `idx_approved` (`is_approved`),
    KEY `idx_spam` (`is_spam`),
    KEY `idx_created` (`created_at` DESC),
    CONSTRAINT `fk_comment_article` FOREIGN KEY (`article_id`) REFERENCES `aak_articles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_id`) REFERENCES `aak_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Article comments';

-- ============================================
-- TABLE: aak_advertisements
-- Advertisement slots management
-- ============================================
DROP TABLE IF EXISTS `aak_advertisements`;
CREATE TABLE `aak_advertisements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `position` VARCHAR(50) NOT NULL COMMENT 'header, sidebar, between_articles, footer, popup',
    `slot_id` VARCHAR(50) DEFAULT NULL COMMENT 'e.g., leaderboard, mrec, skyscraper',
    
    `type` ENUM('image','code','google_ads','native','video') NOT NULL DEFAULT 'image',
    `content` TEXT DEFAULT NULL COMMENT 'Ad code or HTML',
    
    `url` VARCHAR(255) DEFAULT NULL COMMENT 'Click-through URL',
    `image_path` VARCHAR(255) DEFAULT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `width` INT DEFAULT NULL,
    `height` INT DEFAULT NULL,
    
    `target` ENUM('_blank','_self','_parent','_top') NOT NULL DEFAULT '_blank',
    `nofollow` TINYINT(1) NOT NULL DEFAULT 1,
    
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    
    `impression_limit` INT DEFAULT NULL COMMENT 'Max impressions',
    `click_limit` INT DEFAULT NULL COMMENT 'Max clicks',
    
    `impression_count` INT NOT NULL DEFAULT 0,
    `click_count` INT NOT NULL DEFAULT 0,
    
    `priority` INT NOT NULL DEFAULT 0 COMMENT 'Higher = shown first',
    `weight` INT NOT NULL DEFAULT 100 COMMENT 'For rotation (percentage)',
    
    `geo_targeting` VARCHAR(255) DEFAULT NULL COMMENT 'Country/city codes',
    `device_targeting` ENUM('all','desktop','mobile','tablet') NOT NULL DEFAULT 'all',
    
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_position` (`position`),
    KEY `idx_active` (`is_active`),
    KEY `idx_dates` (`start_date`, `end_date`),
    KEY `idx_priority` (`priority` DESC),
    CONSTRAINT `fk_ad_created_by` FOREIGN KEY (`created_by`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Advertisement slots';

-- Default ad positions
INSERT INTO `aak_advertisements` (`name`, `position`, `slot_id`, `type`, `is_active`, `priority`) VALUES
('Header Banner', 'header', 'leaderboard', 'image', 1, 10),
('Sidebar Top', 'sidebar', 'mrec', 'image', 1, 10),
('Between Articles 1', 'between_articles', 'mrec', 'image', 1, 5),
('Footer Banner', 'footer', 'leaderboard', 'image', 1, 10);

-- ============================================
-- TABLE: aak_spaces
-- Content collections/spaces like OnlineKhabar, Ratopati
-- ============================================
DROP TABLE IF EXISTS `aak_spaces`;
CREATE TABLE `aak_spaces` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `name_ne` VARCHAR(100) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `description_ne` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#16a34a',
    `image` VARCHAR(255) DEFAULT NULL,
    `cover_image` VARCHAR(255) DEFAULT NULL,
    
    `layout` ENUM('grid','list','magazine','featured','masonry','carousel','timeline') NOT NULL DEFAULT 'grid',
    `template` VARCHAR(50) DEFAULT 'default',
    
    `category_id` INT UNSIGNED DEFAULT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    
    `sort_by` ENUM('latest','popular','custom','alphabetical') NOT NULL DEFAULT 'latest',
    `max_articles` INT NOT NULL DEFAULT 20,
    `articles_per_page` INT NOT NULL DEFAULT 12,
    
    `show_title` TINYINT(1) NOT NULL DEFAULT 1,
    `show_description` TINYINT(1) NOT NULL DEFAULT 1,
    `show_cover` TINYINT(1) NOT NULL DEFAULT 1,
    `show_excerpt` TINYINT(1) NOT NULL DEFAULT 1,
    `show_author` TINYINT(1) NOT NULL DEFAULT 0,
    `show_date` TINYINT(1) NOT NULL DEFAULT 1,
    `show_views` TINYINT(1) NOT NULL DEFAULT 0,
    `show_thumbnail` TINYINT(1) NOT NULL DEFAULT 1,
    `show_category` TINYINT(1) NOT NULL DEFAULT 1,
    `show_read_time` TINYINT(1) NOT NULL DEFAULT 0,
    `show_pagination` TINYINT(1) NOT NULL DEFAULT 1,
    
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_menu` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_home` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0,
    `is_public` TINYINT(1) NOT NULL DEFAULT 1,
    `requires_subscription` TINYINT(1) NOT NULL DEFAULT 0,
    
    `meta_title` VARCHAR(150) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `meta_keywords` VARCHAR(255) DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    
    `featured_article_id` INT UNSIGNED DEFAULT NULL,
    `custom_css` TEXT DEFAULT NULL,
    
    `view_count` INT NOT NULL DEFAULT 0,
    `subscriber_count` INT NOT NULL DEFAULT 0,
    
    `sort_order` INT NOT NULL DEFAULT 0,
    `columns` INT NOT NULL DEFAULT 3,
    
    `created_by` INT UNSIGNED DEFAULT NULL,
    `updated_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_active` (`is_active`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_menu` (`show_in_menu`),
    KEY `idx_home` (`show_in_home`),
    KEY `idx_sort` (`sort_order`),
    KEY `idx_category` (`category_id`),
    CONSTRAINT `fk_space_category` FOREIGN KEY (`category_id`) REFERENCES `aak_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_space_featured` FOREIGN KEY (`featured_article_id`) REFERENCES `aak_articles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_space_created` FOREIGN KEY (`created_by`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_space_updated` FOREIGN KEY (`updated_by`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Content collections/spaces';

-- Default spaces
INSERT INTO `aak_spaces` (`name`, `name_ne`, `slug`, `description`, `description_ne`, `icon`, `color`, `layout`, `sort_by`, `max_articles`, `show_in_menu`, `show_in_home`, `sort_order`) VALUES
('Breaking News', 'भयो के ?', 'breaking-news', 'Latest breaking news and urgent updates from Nepal and around the world', 'नेपाल र विश्वका ताजा समाचार र अर्जेन्ट अपडेटहरू', '🔥', '#ef4444', 'featured', 'latest', 10, 1, 1, 1),
('Featured Stories', 'विशेष कथाहरू', 'featured', 'Handpicked featured stories by our editors', 'हाम्रा सम्पादकहरूद्वारा चुनिएका विशेष कथाहरू', '⭐', '#8b5cf6', 'magazine', 'popular', 12, 1, 1, 2),
('Politics', 'राजनीति', 'politics-space', 'Comprehensive coverage of Nepal politics, parliament, and government', 'नेपाली राजनीति, संसद र सरकारको व्यापक कभरेज', '🏛️', '#dc2626', 'grid', 'latest', 20, 1, 1, 3),
('Economy & Markets', 'अर्थ र बजार', 'economy-markets', 'Stock market, banking, business news and economic analysis', 'शेयर बजार, बैंकिङ, व्यापार समाचार र आर्थिक विश्लेषण', '💰', '#059669', 'grid', 'latest', 20, 1, 1, 4),
('Sports', 'खेलकुद', 'sports-space', 'Cricket, football, and all sports coverage', 'क्रिकेट, फुटबल र सबै खेलकुद कभरेज', '⚽', '#f59e0b', 'grid', 'latest', 20, 1, 1, 5),
('Technology', 'प्रविधि', 'technology-space', 'Tech news, gadgets, startups and digital trends', 'प्रविधि समाचार, ग्याजेट्स, स्टार्टअप र डिजिटल ट्रेंडहरू', '💻', '#6366f1', 'grid', 'latest', 20, 1, 1, 6),
('Entertainment', 'मनोरञ्जन', 'entertainment-space', 'Movies, music, celebrities and entertainment news', 'चलचित्र, संगीत, सेलिब्रेटी र मनोरञ्जन समाचार', '🎬', '#ec4899', 'grid', 'latest', 20, 1, 1, 7),
('Opinion & Analysis', 'राय र विश्लेषण', 'opinion-analysis', 'Expert opinions, editorial and in-depth analysis', 'विशेषज्ञ राय, सम्पादकीय र गहिरो विश्लेषण', '💡', '#0ea5e9', 'list', 'latest', 15, 1, 1, 8),
('Interviews', 'अन्तर्वार्ता', 'interviews', 'Exclusive interviews with newsmakers', 'समाचारकर्ताहरूसँगको एक्सक्लुसिभ अन्तर्वार्ता', '🎤', '#14b8a6', 'featured', 'latest', 10, 1, 0, 9),
('Photo Stories', 'फोटो कथा', 'photo-stories', 'Visual stories and photo galleries', 'भिजुअल स्टोरी र फोटो ग्यालेरी', '📷', '#84cc16', 'masonry', 'latest', 16, 1, 0, 10),
('Videos', 'भिडियो', 'video-stories', 'Video stories and news clips', 'भिडियो स्टोरी र समाचार क्लिपहरू', '🎥', '#f97316', 'carousel', 'latest', 12, 1, 1, 11),
('Lifestyle', 'जीवनशैली', 'lifestyle-space', 'Travel, food, fashion and lifestyle', 'यात्रा, खाना, फेसन र लाइफस्टाइल', '🌿', '#22c55e', 'grid', 'latest', 20, 1, 1, 12);

-- ============================================
-- TABLE: aak_space_articles
-- Many-to-many relationship for space articles
-- ============================================
DROP TABLE IF EXISTS `aak_space_articles`;
CREATE TABLE `aak_space_articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `space_id` INT UNSIGNED NOT NULL,
    `article_id` INT UNSIGNED NOT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `custom_title` VARCHAR(255) DEFAULT NULL,
    `custom_excerpt` TEXT DEFAULT NULL,
    `added_by` INT UNSIGNED DEFAULT NULL,
    `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_space_article` (`space_id`, `article_id`),
    KEY `idx_space` (`space_id`),
    KEY `idx_article` (`article_id`),
    KEY `idx_sort` (`sort_order`),
    KEY `idx_featured` (`is_featured`),
    KEY `idx_pinned` (`is_pinned`),
    CONSTRAINT `fk_sa_space` FOREIGN KEY (`space_id`) REFERENCES `aak_spaces` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sa_article` FOREIGN KEY (`article_id`) REFERENCES `aak_articles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sa_added` FOREIGN KEY (`added_by`) REFERENCES `aak_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Space-article relationships';

-- ============================================
-- TABLE: aak_page_views
-- Analytics page views
-- ============================================
DROP TABLE IF EXISTS `aak_page_views`;
CREATE TABLE `aak_page_views` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INT UNSIGNED DEFAULT NULL,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    
    `session_id` VARCHAR(100) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    
    `referrer` VARCHAR(255) DEFAULT NULL,
    `referrer_domain` VARCHAR(100) DEFAULT NULL,
    
    `country` VARCHAR(2) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `device` ENUM('desktop','mobile','tablet','bot') DEFAULT 'desktop',
    `browser` VARCHAR(50) DEFAULT NULL,
    `os` VARCHAR(50) DEFAULT NULL,
    
    `utm_source` VARCHAR(100) DEFAULT NULL,
    `utm_medium` VARCHAR(100) DEFAULT NULL,
    `utm_campaign` VARCHAR(100) DEFAULT NULL,
    
    `view_date` DATE NOT NULL,
    `view_hour` TINYINT NOT NULL,
    `view_minute` TINYINT NOT NULL,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_article` (`article_id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_date` (`view_date`),
    KEY `idx_article_date` (`article_id`, `view_date`),
    KEY `idx_hour` (`view_hour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Page view analytics';

-- ============================================
-- TABLE: aak_notifications
-- User notifications
-- ============================================
DROP TABLE IF EXISTS `aak_notifications`;
CREATE TABLE `aak_notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    
    `type` VARCHAR(50) NOT NULL COMMENT 'article_published, comment_reply, mention, etc',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT DEFAULT NULL,
    
    `link` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `action_url` VARCHAR(255) DEFAULT NULL,
    
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    
    `priority` ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_read` (`is_read`),
    KEY `idx_created` (`created_at` DESC),
    KEY `idx_user_read` (`user_id`, `is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `aak_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User notifications';

-- ============================================
-- TABLE: aak_api_keys
-- External API access
-- ============================================
DROP TABLE IF EXISTS `aak_api_keys`;
CREATE TABLE `aak_api_keys` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `api_key` VARCHAR(64) NOT NULL,
    `secret_hash` VARCHAR(255) DEFAULT NULL,
    
    `permissions` JSON DEFAULT NULL COMMENT 'Allowed endpoints',
    `rate_limit` INT DEFAULT 1000 COMMENT 'Requests per hour',
    
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `expires_at` DATETIME DEFAULT NULL,
    
    `last_used_at` DATETIME DEFAULT NULL,
    `use_count` INT NOT NULL DEFAULT 0,
    
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_api_key` (`api_key`),
    KEY `idx_user` (`user_id`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API access keys';

-- ============================================
-- TRIGGER: Update tag use_count
-- ============================================
DELIMITER //
CREATE TRIGGER `trg_after_article_tag_insert` AFTER INSERT ON `aak_article_tags` FOR EACH ROW
BEGIN
    UPDATE aak_tags SET use_count = use_count + 1 WHERE id = NEW.tag_id;
END//
DELIMITER ;

-- ============================================
-- TRIGGER: Decrement tag use_count
-- ============================================
DELIMITER //
CREATE TRIGGER `trg_after_article_tag_delete` AFTER DELETE ON `aak_article_tags` FOR EACH ROW
BEGIN
    UPDATE aak_tags SET use_count = GREATEST(0, use_count - 1) WHERE id = OLD.tag_id;
END//
DELIMITER ;

-- ============================================
-- TRIGGER: Update article view count
-- ============================================
DELIMITER //
CREATE TRIGGER `trg_after_page_view_insert` AFTER INSERT ON `aak_page_views` FOR EACH ROW
BEGIN
    IF NEW.article_id IS NOT NULL THEN
        UPDATE aak_articles SET view_count = view_count + 1 WHERE id = NEW.article_id;
    END IF;
END//
DELIMITER ;

-- ============================================
-- SCHEDULED EVENT: Process scheduled articles
-- ============================================
DELIMITER //
CREATE EVENT IF NOT EXISTS `evt_publish_scheduled_articles`
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    UPDATE aak_articles 
    SET status = 'published', 
        published_at = NOW(),
        updated_at = NOW()
    WHERE status = 'scheduled' 
      AND scheduled_at <= NOW()
      AND deleted_at IS NULL;
END//
DELIMITER ;

-- ============================================
-- SCHEDULED EVENT: Cleanup old logs
-- ============================================
DELIMITER //
CREATE EVENT IF NOT EXISTS `evt_cleanup_activity_logs`
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    DELETE FROM aak_activity_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    DELETE FROM aak_page_views WHERE view_date < DATE_SUB(CURDATE(), INTERVAL 365 DAY);
END//
DELIMITER ;

-- ============================================
-- FINAL: Set timezone and optimization
-- ============================================
SET time_zone = "+05:45";

-- Optimize tables
OPTIMIZE TABLE `aak_users`;
OPTIMIZE TABLE `aak_articles`;
OPTIMIZE TABLE `aak_categories`;
OPTIMIZE TABLE `aak_tags`;
OPTIMIZE TABLE `aak_media`;
OPTIMIZE TABLE `aak_page_views`;

-- ============================================
-- INSTALLATION COMPLETE
-- ============================================
