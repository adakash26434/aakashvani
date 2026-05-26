-- ═══════════════════════════════════════════════════════════════════════════════
-- आकाशवाणी Database Fix Script v2 (MySQL/MariaDB Compatible)
-- Run this in phpMyAdmin or MySQL console to fix all issues
-- ═══════════════════════════════════════════════════════════════════════════════

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 1: CREATE MISSING TABLES (if they don't exist)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Create radio_stations table if not exists
CREATE TABLE IF NOT EXISTS radio_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    stream_url VARCHAR(500),
    stream_type VARCHAR(20) DEFAULT 'mp3',
    city VARCHAR(100),
    frequency VARCHAR(50),
    logo_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active',
    featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create radio_podcasts table if not exists
CREATE TABLE IF NOT EXISTS radio_podcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    audio_url VARCHAR(500),
    duration VARCHAR(20),
    publish_date DATE,
    status VARCHAR(20) DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES radio_stations(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create loksewa_notices table if not exists
CREATE TABLE IF NOT EXISTS loksewa_notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500),
    type VARCHAR(100),
    url VARCHAR(500),
    source VARCHAR(100),
    published_date DATE,
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create success_stories table if not exists
CREATE TABLE IF NOT EXISTS success_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) UNIQUE,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    hero_image VARCHAR(255),
    source_name VARCHAR(100),
    source_url VARCHAR(500),
    category VARCHAR(50),
    status VARCHAR(20) DEFAULT 'published',
    featured TINYINT(1) DEFAULT 0,
    published_at TIMESTAMP,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create tech_news table if not exists
CREATE TABLE IF NOT EXISTS tech_news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500),
    summary TEXT,
    content TEXT,
    image_url VARCHAR(500),
    category VARCHAR(50),
    source VARCHAR(100),
    external_url VARCHAR(500),
    published_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 2: FIX TABLE CHARSET TO UTF-8 (Prevents ?????? in Nepali text)
-- ═══════════════════════════════════════════════════════════════════════════════

-- Convert all tables to UTF-8
ALTER TABLE radio_stations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE radio_podcasts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE loksewa_notices CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE success_stories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tech_news CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 3: CLEAR OLD DATA
-- ═══════════════════════════════════════════════════════════════════════════════

-- Clear and reset loksewa data (will auto-refresh from API)
TRUNCATE TABLE loksewa_notices;

-- Clear radio stations (we'll insert fresh data)
TRUNCATE TABLE radio_stations;

-- Clear podcasts
TRUNCATE TABLE radio_podcasts;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 4: INSERT SAMPLE DATA
-- ═══════════════════════════════════════════════════════════════════════════════

-- Insert Sample Radio Stations
INSERT INTO radio_stations 
    (name, stream_url, stream_type, city, frequency, logo_path, status, featured, sort_order, created_at) 
VALUES
-- Kathmandu Popular Stations
('Radio Kantipur', 'https://streaming.softnep.net:8002/stream', 'mp3', 'Kathmandu', '96.1 FM', NULL, 'active', 1, 1, NOW()),
('Ujyalo Radio Network', 'https://stream.ujyalo.com/live', 'mp3', 'Kathmandu', '90.4 FM', NULL, 'active', 1, 2, NOW()),
('Hits FM', 'https://streaming.softnep.net:8052/stream', 'mp3', 'Kathmandu', '91.2 FM', NULL, 'active', 1, 3, NOW()),
('Radio Audio', 'https://streaming.softnep.net:8062/stream', 'mp3', 'Kathmandu', '106.3 FM', NULL, 'active', 0, 4, NOW()),
('Radio Sagarmatha', 'https://streaming.softnep.net:8036/stream', 'mp3', 'Kathmandu', '102.4 FM', NULL, 'active', 0, 5, NOW()),
('Kalinchowk FM', 'https://streaming.softnep.net:8092/stream', 'mp3', 'Kathmandu', '106.0 FM', NULL, 'active', 0, 6, NOW()),
('Radio Nepal', 'https://stream.radionepal.gov.np/live', 'mp3', 'Kathmandu', 'AM 765', NULL, 'active', 0, 7, NOW()),

-- Other Cities
('Radio Lumbini', 'https://streaming.softnep.net:8024/stream', 'mp3', 'Butwal', '96.8 FM', NULL, 'active', 0, 8, NOW()),
('Radio Pokhara', 'https://streaming.softnep.net:8028/stream', 'mp3', 'Pokhara', '95.6 FM', NULL, 'active', 0, 9, NOW()),
('Radio Birgunj', 'https://streaming.softnep.net:8090/stream', 'mp3', 'Birgunj', '105.6 FM', NULL, 'active', 0, 10, NOW()),
('Radio Janakpur', 'https://streaming.softnep.net:8044/stream', 'mp3', 'Janakpur', '97.0 FM', NULL, 'active', 0, 11, NOW()),

-- International / Online
('BBC Nepali', 'https://stream.live.vc.bbcmedia.co.uk/bbc_nepali_radio', 'mp3', 'London', 'Online', NULL, 'active', 1, 12, NOW()),
('Voice of America Nepali', 'https://voa-53.akacast.akamaistream.net/7/55/322395/v1/ibb.akacast.akamaistream.net/voa-53', 'mp3', 'USA', 'Online', NULL, 'active', 0, 13, NOW());

-- Insert Sample Success Stories
INSERT INTO success_stories 
    (slug, title, summary, hero_image, source_name, source_url, category, status, featured, published_at, views, created_at) 
VALUES
('nepali-student-harvard', 'नेपाली विद्यार्थी हार्वर्डमा', 'एक नेपाली विद्यार्थीले हार्वर्ड विश्वविद्यालयमा पूर्ण छात्रवृत्ति पाएका छन्।', NULL, 'BBC Nepali', 'https://www.bbc.com/nepali', 'education', 'published', 1, NOW(), 0, NOW()),
('nepali-startup-unicorn', 'नेपाली स्टार्टअप युनिकोर्न बन्यो', 'काठमाडौंमा स्थापित एक टेक स्टार्टअपले १ अर्ब डलर मूल्यांकन पुर्याएको छ।', NULL, 'TechPana', 'https://techpana.com', 'technology', 'published', 1, NOW(), 0, NOW()),
('women-entrepreneur-award', 'नेपाली महिला उद्यमीले अन्तर्राष्ट्रिय पुरस्कार जितिन्', 'काठमाडौंकी एक महिला उद्यमीले युएन महिला उद्यमिता पुरस्कार जितेकी छन्।', NULL, 'Kantipur', 'https://ekantipur.com', 'business', 'published', 0, NOW(), 0, NOW());

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 5: CREATE INDEXES
-- ═══════════════════════════════════════════════════════════════════════════════

-- Drop existing indexes if they exist (to avoid duplicate errors)
DROP INDEX IF EXISTS idx_loksewa_type ON loksewa_notices;
DROP INDEX IF EXISTS idx_loksewa_fetched ON loksewa_notices;
DROP INDEX IF EXISTS idx_news_category ON tech_news;
DROP INDEX IF EXISTS idx_news_created ON tech_news;
DROP INDEX IF EXISTS idx_stories_featured ON success_stories;
DROP INDEX IF EXISTS idx_radio_status ON radio_stations;

-- Create indexes
CREATE INDEX idx_loksewa_type ON loksewa_notices(type);
CREATE INDEX idx_loksewa_fetched ON loksewa_notices(fetched_at);
CREATE INDEX idx_news_category ON tech_news(category);
CREATE INDEX idx_news_created ON tech_news(created_at);
CREATE INDEX idx_stories_featured ON success_stories(featured);
CREATE INDEX idx_radio_status ON radio_stations(status);

-- ═══════════════════════════════════════════════════════════════════════════════
-- DONE!
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT '✅ Database fixes applied successfully!' AS result;
