-- ═══════════════════════════════════════════════════════════════════════════════
-- आकाशवाणी Database Fix Script
-- Run this in phpMyAdmin or MySQL console to fix all issues
-- ═══════════════════════════════════════════════════════════════════════════════

-- 1. Fix Database Charset (Prevents ?????? in Nepali text)
-- Make sure to replace 'your_database_name' with actual DB name
-- ALTER DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Fix Tables - Convert to UTF-8
ALTER TABLE IF EXISTS loksewa_notices 
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE IF EXISTS success_stories 
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE IF EXISTS radio_stations 
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE IF EXISTS radio_podcasts 
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE IF EXISTS tech_news 
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Clear corrupted data from Loksewa (will auto-refresh from API)
TRUNCATE TABLE IF EXISTS loksewa_notices;

-- 4. Insert Sample Radio Stations (So radio page shows something)
-- Popular FM stations from Kathmandu and other cities
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

-- 5. Insert Sample Success Stories (For testing)
INSERT INTO success_stories 
    (slug, title, summary, hero_image, source_name, source_url, category, status, featured, published_at, views, created_at) 
VALUES
('nepali-student-harvard', 'नेपाली विद्यार्थी हार्वर्डमा', 'एक नेपाली विद्यार्थीले हार्वर्ड विश्वविद्यालयमा पूर्ण छात्रवृत्ति पाएका छन्।', NULL, 'BBC Nepali', 'https://www.bbc.com/nepali', 'education', 'published', 1, NOW(), 0, NOW()),
('nepali-startup-unicorn', 'नेपाली स्टार्टअप युनिकोर्न बन्यो', 'काठमाडौंमा स्थापित एक टेक स्टार्टअपले १ अर्ब डलर मूल्यांकन पुर्याएको छ।', NULL, 'TechPana', 'https://techpana.com', 'technology', 'published', 1, NOW(), 0, NOW()),
('women-entrepreneur-award', 'नेपाली महिला उद्यमीले अन्तर्राष्ट्रिय पुरस्कार जितिन्', 'काठमाडौंकी एक महिला उद्यमीले युएन महिला उद्यमिता पुरस्कार जितेकी छन्।', NULL, 'Kantipur', 'https://ekantipur.com', 'business', 'published', 0, NOW(), 0, NOW());

-- 6. Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_loksewa_type ON loksewa_notices(type);
CREATE INDEX IF NOT EXISTS idx_loksewa_fetched ON loksewa_notices(fetched_at);
CREATE INDEX IF NOT EXISTS idx_news_category ON tech_news(category);
CREATE INDEX IF NOT EXISTS idx_news_created ON tech_news(created_at);
CREATE INDEX IF NOT EXISTS idx_stories_featured ON success_stories(featured);
CREATE INDEX IF NOT EXISTS idx_radio_status ON radio_stations(status);

-- 7. Clear old caches (forces fresh data fetch)
-- This will be done automatically when you delete cache files

SELECT 'Database fixes applied successfully!' AS result;
