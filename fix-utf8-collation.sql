-- ============================================================================
-- AAKASHVANI — ONE-TIME UTF-8 COLLATION FIX
-- ============================================================================
-- Yo file phpMyAdmin ma run garnus (Database select garera → SQL tab).
-- Yesle existing tables haru lai utf8mb4 ma convert garcha.
-- ?????? aaune Nepali text issue solve garcha.
-- ============================================================================

-- 1. Database default fix
ALTER DATABASE `tankaadh_admin` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Sabai tables lai utf8mb4 ma convert (auto-generates ALTER statements)
-- phpMyAdmin ma yo query run garera output mai click garera execute garnus:
SELECT CONCAT('ALTER TABLE `', TABLE_NAME, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;') AS sql_to_run
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE';

-- 3. Common tables manually (yo direct run garna sakincha):
ALTER TABLE tech_news CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contact_messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE rashifal_daily CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Aru tables jati cha sabai ma yei tarika garnus (table missing bhayema error ignore garnus)
