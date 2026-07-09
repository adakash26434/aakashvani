-- ============================================================
-- आकाशवाणी — MASTER DATABASE INSTALL SCRIPT v4
-- Run this ONCE in phpMyAdmin → Import
-- Safe to re-run (uses IF NOT EXISTS everywhere)
-- Includes: auth_users, user_data, newsletter_subscribers tables
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET foreign_key_checks = 0;

-- ===== CORE TABLES =====

CREATE TABLE IF NOT EXISTS `users` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `name`         VARCHAR(200) NOT NULL,
    `email`        VARCHAR(200) NOT NULL UNIQUE,
    `phone`        VARCHAR(20),
    `password`     VARCHAR(255) DEFAULT NULL,
    `role`         ENUM('user','admin','moderator') NOT NULL DEFAULT 'user',
    `avatar`       VARCHAR(500) DEFAULT NULL,
    `is_verified`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
    `last_login`   DATETIME DEFAULT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email`   (`email`),
    INDEX `idx_users_role`    (`role`),
    INDEX `idx_users_active`  (`is_active`),
    INDEX `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(300) NOT NULL,
    `email`      VARCHAR(300) NOT NULL,
    `subject`    VARCHAR(500),
    `message`    TEXT NOT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_contact_read`    (`is_read`),
    INDEX `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== MARKET DATA =====

CREATE TABLE IF NOT EXISTS `nepse_live` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `market_index`    DECIMAL(8,2),
    `index_change`    DECIMAL(6,2),
    `percent_change`  DECIMAL(5,2),
    `turnover`        VARCHAR(50),
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `market_data_cache` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `data_type`   ENUM('gold','silver','petrol','diesel','nepse','forex') NOT NULL,
    `data_json`   JSON,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_type` (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== IPO TRACKER =====

CREATE TABLE IF NOT EXISTS `ipo_tracker` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `company_name`     VARCHAR(255) NOT NULL,
    `share_type`       ENUM('IPO','FPO','Right Share','Debenture') DEFAULT 'IPO',
    `opening_date`     DATE,
    `closing_date`     DATE,
    `allotment_date`   DATE DEFAULT NULL,
    `status`           ENUM('Upcoming','Open','Closed','Allotted') DEFAULT 'Upcoming',
    `units`            VARCHAR(50),
    `min_units`        INT DEFAULT 10,
    `price_per_unit`   DECIMAL(10,2),
    `kitta_applied`    INT DEFAULT NULL,
    `result_url`       VARCHAR(500) DEFAULT NULL,
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ipo_status` (`status`),
    INDEX `idx_ipo_closing` (`closing_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== VEHICLE TAX =====

CREATE TABLE IF NOT EXISTS `vehicle_tax_rates` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `province`         VARCHAR(50) NOT NULL,
    `vehicle_type`     ENUM('2-wheeler','4-wheeler') NOT NULL,
    `cc_range_from`    INT NOT NULL,
    `cc_range_to`      INT NOT NULL,
    `annual_tax_rate`  DECIMAL(10,2) NOT NULL,
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_vehicle_province` (`province`, `vehicle_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `vehicle_tax_rates` (`province`,`vehicle_type`,`cc_range_from`,`cc_range_to`,`annual_tax_rate`) VALUES
('Bagmati','2-wheeler',0,125,2500),('Bagmati','2-wheeler',126,150,4000),
('Bagmati','2-wheeler',151,225,6000),('Bagmati','2-wheeler',226,400,10000),
('Bagmati','2-wheeler',401,9999,15000),('Bagmati','4-wheeler',0,1000,12000),
('Bagmati','4-wheeler',1001,1500,18000),('Bagmati','4-wheeler',1501,2000,35000),
('Bagmati','4-wheeler',2001,2500,50000),('Bagmati','4-wheeler',2501,3000,70000),
('Bagmati','4-wheeler',3001,9999,100000);

-- ===== EMERGENCY DIRECTORY =====

CREATE TABLE IF NOT EXISTS `emergency_directory` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `district`          VARCHAR(50) NOT NULL,
    `category`          ENUM('Hospital','Ambulance','Police','Fire Brigade','Blood Bank') NOT NULL,
    `organization_name` VARCHAR(150) NOT NULL,
    `phone_number`      VARCHAR(100) NOT NULL,
    `location`          VARCHAR(150),
    `is_active`         TINYINT(1) DEFAULT 1,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_emergency_district` (`district`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `emergency_directory` (`district`,`category`,`organization_name`,`phone_number`,`location`) VALUES
('National','Police','Nepal Police Emergency','100','All Nepal'),
('National','Ambulance','Ambulance Service','102','All Nepal'),
('National','Fire Brigade','Fire Brigade','101','All Nepal'),
('Kathmandu','Hospital','Bir Hospital','01-4221119','Mahaboudha'),
('Kathmandu','Hospital','Teaching Hospital (TUTH)','01-4412303','Maharajgunj'),
('Kathmandu','Hospital','Patan Hospital','01-5522266','Lagankhel'),
('Kathmandu','Blood Bank','Central Blood Bank','01-4225344','Exhibition Road'),
('Kaski','Hospital','Western Regional Hospital','061-520066','Pokhara'),
('Morang','Hospital','Koshi Hospital','021-525200','Biratnagar'),
('Chitwan','Hospital','Bharatpur Hospital','056-527012','Bharatpur');

-- ===== NEWS CACHE =====

CREATE TABLE IF NOT EXISTS `news_cache` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `source`      VARCHAR(100) NOT NULL,
    `news_json`   LONGTEXT,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== NEWS ARTICLES =====

CREATE TABLE IF NOT EXISTS `news` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(500) NOT NULL,
    `slug`            VARCHAR(500) NOT NULL,
    `excerpt`         TEXT,
    `summary`         TEXT,
    `content`         LONGTEXT,
    `image`           VARCHAR(700),
    `image_url`       VARCHAR(700),
    `category`        VARCHAR(60) NOT NULL DEFAULT 'general',
    `lang`            VARCHAR(5)  NOT NULL DEFAULT 'ne',
    `source`          VARCHAR(100),
    `source_name`     VARCHAR(120),
    `source_url`      VARCHAR(700),
    `author`          VARCHAR(200),
    `url_hash`        VARCHAR(64),
    `status`          ENUM('draft','published','archived') NOT NULL DEFAULT 'published',
    `is_published`   TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `is_breaking`     TINYINT(1) NOT NULL DEFAULT 0,
    `view_count`      INT UNSIGNED NOT NULL DEFAULT 0,
    `ai_processed`    TINYINT(1) NOT NULL DEFAULT 0,
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
    FULLTEXT KEY `ft_title_summary`(`title`, `summary`, `excerpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== SUCCESS STORIES =====

CREATE TABLE IF NOT EXISTS `success_stories` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`         VARCHAR(220) NOT NULL,
    `title`        VARCHAR(500) NOT NULL,
    `summary`      TEXT NULL,
    `body`         MEDIUMTEXT NULL,
    `hero_image`   VARCHAR(500) NULL,
    `person_name`  VARCHAR(200) NULL,
    `category`     VARCHAR(60) NOT NULL DEFAULT 'general',
    `source_type`  ENUM('rss','manual','admin') NOT NULL DEFAULT 'rss',
    `source_name`  VARCHAR(120) NULL,
    `source_url`   VARCHAR(700) NULL,
    `source_guid`  VARCHAR(500) NULL,
    `published_at` DATETIME NULL,
    `views`        INT UNSIGNED NOT NULL DEFAULT 0,
    `featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `status`       ENUM('published','draft','hidden') NOT NULL DEFAULT 'published',
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    UNIQUE KEY `uq_source_guid` (`source_guid`),
    KEY `idx_featured_pub` (`featured`,`published_at`),
    KEY `idx_status_pub`   (`status`,`published_at`),
    KEY `idx_category`     (`category`),
    FULLTEXT KEY `ft_title_summary` (`title`,`summary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== VISIT PLACES =====

CREATE TABLE IF NOT EXISTS `visit_places` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`          VARCHAR(220) NOT NULL,
    `title`         VARCHAR(300) NOT NULL,
    `title_en`      VARCHAR(300) NULL,
    `description`   MEDIUMTEXT NOT NULL,
    `short_caption` VARCHAR(500) NULL,
    `district`      VARCHAR(80) NULL,
    `province`      VARCHAR(80) NULL,
    `region`        ENUM('himal','pahad','tarai','unknown') NOT NULL DEFAULT 'unknown',
    `altitude_m`    INT NULL,
    `latitude`      DECIMAL(10,7) NULL,
    `longitude`     DECIMAL(10,7) NULL,
    `image_path`    VARCHAR(500) NOT NULL,
    `image_thumb`   VARCHAR(500) NULL,
    `image_credit`  VARCHAR(200) NULL,
    `gallery_json`  JSON NULL,
    `best_season`   VARCHAR(120) NULL,
    `how_to_reach`  TEXT NULL,
    `nearby_json`   JSON NULL,
    `tags`          VARCHAR(500) NULL,
    `category`      VARCHAR(60) NOT NULL DEFAULT 'general',
    `featured`      TINYINT(1) NOT NULL DEFAULT 0,
    `views`         INT UNSIGNED NOT NULL DEFAULT 0,
    `status`        ENUM('published','draft','hidden') NOT NULL DEFAULT 'published',
    `sort_order`    INT NOT NULL DEFAULT 0,
    `created_by`    VARCHAR(120) NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_featured_pub` (`featured`,`status`),
    KEY `idx_region`       (`region`),
    KEY `idx_category`     (`category`),
    FULLTEXT KEY `ft_title_desc` (`title`,`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== RADIO STATIONS =====

CREATE TABLE IF NOT EXISTS `radio_stations` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`         VARCHAR(120) NOT NULL,
    `name`         VARCHAR(200) NOT NULL,
    `name_en`      VARCHAR(200) NULL,
    `frequency`    VARCHAR(30) NULL,
    `district`     VARCHAR(80) NULL,
    `province`     VARCHAR(80) NULL,
    `stream_url`   VARCHAR(700) NOT NULL,
    `website`      VARCHAR(500) NULL,
    `logo_url`     VARCHAR(500) NULL,
    `genre`        VARCHAR(120) NULL DEFAULT 'general',
    `language`     VARCHAR(60) NULL DEFAULT 'Nepali',
    `is_live`      TINYINT(1) NOT NULL DEFAULT 1,
    `featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order`   INT NOT NULL DEFAULT 0,
    `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status_sort`  (`status`,`sort_order`),
    KEY `idx_featured`     (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample radio stations
INSERT IGNORE INTO `radio_stations` (`slug`,`name`,`frequency`,`district`,`stream_url`,`genre`,`featured`,`sort_order`) VALUES
('radio-nepal','Radio Nepal','100 MHz','Kathmandu','https://stream.radioparadise.com/mp3-192','general',1,1),
('kantipur-fm','Kantipur FM','96.1 MHz','Kathmandu','https://stream.radioparadise.com/mp3-192','news',1,2),
('image-fm','Image FM','97.9 MHz','Kathmandu','https://stream.radioparadise.com/mp3-192','music',0,3);

-- ===== APP NOTICES =====

CREATE TABLE IF NOT EXISTS `app_notices` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`          VARCHAR(300) NOT NULL,
    `body`           MEDIUMTEXT NOT NULL,
    `type`           ENUM('info','success','warning','urgent','janachetana') NOT NULL DEFAULT 'info',
    `document_path`  VARCHAR(500) NULL,
    `document_name`  VARCHAR(200) NULL,
    `document_size`  INT UNSIGNED NULL,
    `document_mime`  VARCHAR(120) NULL,
    `cta_label`      VARCHAR(80) NULL,
    `cta_url`        VARCHAR(500) NULL,
    `display_mode`   ENUM('modal','banner','both') NOT NULL DEFAULT 'modal',
    `dismissible`    TINYINT(1) NOT NULL DEFAULT 1,
    `pin_top`        TINYINT(1) NOT NULL DEFAULT 0,
    `priority`       INT NOT NULL DEFAULT 0,
    `active`         TINYINT(1) NOT NULL DEFAULT 1,
    `show_from`      DATETIME NULL,
    `show_until`     DATETIME NULL,
    `pages_only`     VARCHAR(500) NULL,
    `views`          INT UNSIGNED NOT NULL DEFAULT 0,
    `clicks`         INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_active_priority` (`active`,`priority`),
    KEY `idx_schedule`        (`show_from`,`show_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== PODCASTS =====

CREATE TABLE IF NOT EXISTS `podcasts` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`         VARCHAR(220) NOT NULL,
    `title`        VARCHAR(500) NOT NULL,
    `description`  TEXT NULL,
    `audio_url`    VARCHAR(700) NOT NULL,
    `duration`     VARCHAR(20) NULL,
    `thumbnail`    VARCHAR(500) NULL,
    `category`     VARCHAR(80) NOT NULL DEFAULT 'general',
    `source_name`  VARCHAR(120) NULL,
    `source_url`   VARCHAR(700) NULL,
    `published_at` DATETIME NULL,
    `plays`        INT UNSIGNED NOT NULL DEFAULT 0,
    `featured`     TINYINT(1) NOT NULL DEFAULT 0,
    `status`       ENUM('published','draft','hidden') NOT NULL DEFAULT 'published',
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_slug` (`slug`),
    KEY `idx_status_pub`  (`status`,`published_at`),
    KEY `idx_featured`    (`featured`),
    KEY `idx_category`    (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== USER BOOKMARKS =====

CREATE TABLE IF NOT EXISTS `user_bookmarks` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT NOT NULL,
    `item_type`    ENUM('news','story','place','ipo','notice') NOT NULL,
    `item_id`      VARCHAR(200) NOT NULL,
    `item_title`   VARCHAR(500) NULL,
    `item_url`     VARCHAR(700) NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_item` (`user_id`,`item_type`,`item_id`),
    KEY `idx_user_id`   (`user_id`),
    KEY `idx_item_type` (`item_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== PUSH SUBSCRIPTIONS =====

CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT NULL,
    `endpoint`     TEXT NOT NULL,
    `p256dh`       VARCHAR(500) NOT NULL,
    `auth`         VARCHAR(200) NOT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== NEWSLETTER SUBSCRIBERS =====

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email`           VARCHAR(255) NOT NULL UNIQUE,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `subscribed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` DATETIME DEFAULT NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== AUTH USERS (Unified Auth) =====

CREATE TABLE IF NOT EXISTS `auth_users` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email`          VARCHAR(255) NOT NULL UNIQUE,
    `password_hash`  VARCHAR(255) NOT NULL,
    `full_name`      VARCHAR(200) NOT NULL,
    `phone`          VARCHAR(20) DEFAULT NULL,
    `language`       VARCHAR(5) NOT NULL DEFAULT 'ne',
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `is_verified`    TINYINT(1) NOT NULL DEFAULT 0,
    `verify_token`   VARCHAR(64) DEFAULT NULL,
    `reset_token`    VARCHAR(64) DEFAULT NULL,
    `reset_expires`  DATETIME DEFAULT NULL,
    `last_login`     DATETIME DEFAULT NULL,
    `login_count`    INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_auth_email`    (`email`),
    INDEX `idx_auth_active`   (`is_active`),
    INDEX `idx_auth_verify`   (`verify_token`),
    INDEX `idx_auth_reset`    (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== USER DATA (Preferences/Settings) =====

CREATE TABLE IF NOT EXISTS `user_data` (
    `user_id`     INT PRIMARY KEY,
    `data_json`   LONGTEXT NOT NULL DEFAULT '{}',
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- ============================================================
-- DONE! All tables created. Run this once, then restart site.
-- ============================================================
