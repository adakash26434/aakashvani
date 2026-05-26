-- ═══════════════════════════════════════════════════════════════════════════════
-- आकाशवाणी Complete Database Setup Script
-- Run this in phpMyAdmin to create ALL missing tables and fix ALL issues
-- ═══════════════════════════════════════════════════════════════════════════════

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 1: CREATE ALL MISSING TABLES
-- ═══════════════════════════════════════════════════════════════════════════════

-- 1. Radio Stations Table
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

-- 2. Radio Podcasts Table
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

-- 3. Loksewa Notices Table
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

-- 4. Success Stories Table
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

-- 5. Tech News Table
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

-- 6. Rashifal Daily Table
CREATE TABLE IF NOT EXISTS rashifal_daily (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rashi_number INT NOT NULL,
    rashi_name VARCHAR(50),
    date DATE NOT NULL,
    prediction TEXT,
    lucky_number VARCHAR(20),
    lucky_color VARCHAR(50),
    lucky_time VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rashi_date (rashi_number, date)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 7. Weather Cache Table
CREATE TABLE IF NOT EXISTS weather_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100),
    temperature VARCHAR(20),
    condition_text VARCHAR(100),
    humidity VARCHAR(20),
    wind_speed VARCHAR(20),
    data JSON,
    cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 8. Market Data Cache Table
CREATE TABLE IF NOT EXISTS market_data_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50),
    symbol VARCHAR(50),
    value VARCHAR(50),
    change_value VARCHAR(50),
    change_percent VARCHAR(50),
    data JSON,
    cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 9. Alerts Table
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500),
    description TEXT,
    severity VARCHAR(20) DEFAULT 'info',
    category VARCHAR(50),
    source VARCHAR(100),
    source_url VARCHAR(500),
    status VARCHAR(20) DEFAULT 'active',
    alert_time TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 10. Job Listings Table
CREATE TABLE IF NOT EXISTS job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    company VARCHAR(255),
    location VARCHAR(100),
    job_type VARCHAR(50),
    salary VARCHAR(100),
    description TEXT,
    requirements TEXT,
    application_url VARCHAR(500),
    source VARCHAR(100),
    posted_date DATE,
    deadline DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 11. Cricket Matches Table
CREATE TABLE IF NOT EXISTS cricket_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id VARCHAR(100) UNIQUE,
    team1 VARCHAR(100),
    team2 VARCHAR(100),
    team1_score VARCHAR(50),
    team2_score VARCHAR(50),
    match_status VARCHAR(50),
    match_type VARCHAR(50),
    venue VARCHAR(200),
    start_time TIMESTAMP,
    data JSON,
    cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 12. Bus Routes Table
CREATE TABLE IF NOT EXISTS bus_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_number VARCHAR(50),
    from_city VARCHAR(100),
    to_city VARCHAR(100),
    via_cities TEXT,
    distance_km INT,
    duration VARCHAR(50),
    fare VARCHAR(50),
    operator VARCHAR(100),
    schedule JSON,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 2: FIX UTF-8 CHARSET ON ALL TABLES
-- ═══════════════════════════════════════════════════════════════════════════════

ALTER TABLE radio_stations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE radio_podcasts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE loksewa_notices CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE success_stories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tech_news CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE rashifal_daily CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE weather_cache CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE market_data_cache CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE alerts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE job_listings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE cricket_matches CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE bus_routes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 3: INSERT SAMPLE DATA
-- ═══════════════════════════════════════════════════════════════════════════════

-- Insert Radio Stations
INSERT INTO radio_stations (name, stream_url, stream_type, city, frequency, status, featured, sort_order) VALUES
('Radio Kantipur', 'https://streaming.softnep.net:8002/stream', 'mp3', 'Kathmandu', '96.1 FM', 'active', 1, 1),
('Ujyalo Radio Network', 'https://stream.ujyalo.com/live', 'mp3', 'Kathmandu', '90.4 FM', 'active', 1, 2),
('Hits FM', 'https://streaming.softnep.net:8052/stream', 'mp3', 'Kathmandu', '91.2 FM', 'active', 1, 3),
('Radio Audio', 'https://streaming.softnep.net:8062/stream', 'mp3', 'Kathmandu', '106.3 FM', 'active', 0, 4),
('Radio Sagarmatha', 'https://streaming.softnep.net:8036/stream', 'mp3', 'Kathmandu', '102.4 FM', 'active', 0, 5),
('Kalinchowk FM', 'https://streaming.softnep.net:8092/stream', 'mp3', 'Kathmandu', '106.0 FM', 'active', 0, 6),
('Radio Nepal', 'https://stream.radionepal.gov.np/live', 'mp3', 'Kathmandu', 'AM 765', 'active', 0, 7),
('Radio Lumbini', 'https://streaming.softnep.net:8024/stream', 'mp3', 'Butwal', '96.8 FM', 'active', 0, 8),
('Radio Pokhara', 'https://streaming.softnep.net:8028/stream', 'mp3', 'Pokhara', '95.6 FM', 'active', 0, 9),
('Radio Birgunj', 'https://streaming.softnep.net:8090/stream', 'mp3', 'Birgunj', '105.6 FM', 'active', 0, 10),
('Radio Janakpur', 'https://streaming.softnep.net:8044/stream', 'mp3', 'Janakpur', '97.0 FM', 'active', 0, 11),
('BBC Nepali', 'https://stream.live.vc.bbcmedia.co.uk/bbc_nepali_radio', 'mp3', 'London', 'Online', 'active', 1, 12),
('Voice of America Nepali', 'https://voa-53.akacast.akamaistream.net/7/55/322395/v1/ibb.akacast.akamaistream.net/voa-53', 'mp3', 'USA', 'Online', 'active', 0, 13);

-- Insert Success Stories
INSERT INTO success_stories (slug, title, summary, source_name, source_url, category, status, featured, published_at, views) VALUES
('nepali-student-harvard', 'नेपाली विद्यार्थी हार्वर्डमा', 'एक नेपाली विद्यार्थीले हार्वर्ड विश्वविद्यालयमा पूर्ण छात्रवृत्ति पाएका छन्।', 'BBC Nepali', 'https://www.bbc.com/nepali', 'education', 'published', 1, NOW(), 0),
('nepali-startup-unicorn', 'नेपाली स्टार्टअप युनिकोर्न बन्यो', 'काठमाडौंमा स्थापित एक टेक स्टार्टअपले १ अर्ब डलर मूल्यांकन पुर्याएको छ।', 'TechPana', 'https://techpana.com', 'technology', 'published', 1, NOW(), 0),
('women-entrepreneur-award', 'नेपाली महिला उद्यमीले अन्तर्राष्ट्रिय पुरस्कार जितिन्', 'काठमाडौंकी एक महिला उद्यमीले युएन महिला उद्यमिता पुरस्कार जितेकी छन्।', 'Kantipur', 'https://ekantipur.com', 'business', 'published', 0, NOW(), 0);

-- Insert Sample Tech News
INSERT INTO tech_news (title, summary, category, source, external_url, published_date) VALUES
('OpenAI ले GPT-5 घोषणा गर्यो', 'OpenAI ले नयाँ AI मोडल GPT-5 लन्च गरेको छ जसले अझ बुद्धिमानी काम गर्न सक्छ।', 'artificial-intelligence', 'TechCrunch', 'https://techcrunch.com', CURDATE()),
('SpaceX को नयाँ रकेट सफल', 'SpaceX ले नयाँ Starship रकेट सफलतापूर्वक प्रक्षेपण गरेको छ।', 'space', 'BBC Technology', 'https://bbc.com/technology', CURDATE()),
('Apple को नयाँ iPhone आउँदै', 'Apple ले आगामी महिना नयाँ iPhone 16 सिरिज लन्च गर्दैछ।', 'mobile', 'The Verge', 'https://theverge.com', CURDATE());

-- Insert Sample Rashifal
INSERT INTO rashifal_daily (rashi_number, rashi_name, date, prediction, lucky_number, lucky_color) VALUES
(0, 'मेष', CURDATE(), 'आज तपाईंको दिन राम्रो रहनेछ। नयाँ अवसरहरू प्राप्त हुनेछन्।', '5, 12', 'रातो'),
(1, 'वृष', CURDATE(), 'आर्थिक मामिलामा सफलता मिल्नेछ। लगानी फलदायी हुनेछ।', '3, 9', 'हरियो'),
(2, 'मिथुन', CURDATE(), 'पारिवारिक सुख मिल्नेछ। यात्रा गर्ने योग छ।', '7, 15', 'पहेंलो'),
(3, 'कर्कट', CURDATE(), 'स्वास्थ्य राम्रो रहनेछ। काममा प्रशंसा मिल्नेछ।', '1, 8', 'सेतो'),
(4, 'सिंह', CURDATE(), 'आज नयाँ काम सुरु गर्न शुभ दिन। सफलता मिल्नेछ।', '4, 11', 'सुनौलो'),
(5, 'कन्या', CURDATE(), 'आर्थिक लाभ हुने योग छ। धन प्राप्ति हुनेछ।', '6, 13', 'हरियो'),
(6, 'तुला', CURDATE(), 'सामाजिक सम्बन्ध मजबुत हुनेछ। मित्रहरूको सहयोग मिल्नेछ।', '2, 10', 'नीलो'),
(7, 'वृश्चिक', CURDATE(), 'आज नयाँ ज्ञान प्राप्ति हुनेछ। पढाइमा राम्रो हुनेछ।', '8, 16', 'कालो'),
(8, 'धनु', CURDATE(), 'दीर्घ यात्राको योग छ। विदेश सम्बन्धी काम बन्नेछ।', '9, 18', 'पहेंलो'),
(9, 'मकर', CURDATE(), 'काममा प्रगति हुनेछ। पदोन्नति मिल्ने सम्भावना छ।', '5, 14', 'खैरो'),
(10, 'कुम्भ', CURDATE(), 'सामाजिक कार्यमा सहभागी हुनुहोस्। प्रतिष्ठा बढ्नेछ।', '3, 12', 'नीलो'),
(11, 'कुम्भ', CURDATE(), 'कलात्मक कार्यमा सफलता मिल्नेछ। सिर्जनशीलता बढ्नेछ।', '7, 21', 'बैजनी');

-- Insert Sample Job Listings
INSERT INTO job_listings (title, company, location, job_type, salary, description, source, posted_date, status) VALUES
('Software Engineer', 'F1Soft International', 'Kathmandu', 'Full-time', ' negotiable', ' experienced software developer needed', 'MeroJob', CURDATE(), 'active'),
('Marketing Manager', 'CG Corp', 'Kathmandu', 'Full-time', ' negotiable', ' experienced marketing professional', 'Kantipur Job', CURDATE(), 'active'),
('Civil Engineer', 'Shanghai Construction', 'Kathmandu', 'Full-time', ' negotiable', ' civil engineer for infrastructure project', 'RamroJob', CURDATE(), 'active');

-- Insert Sample Alerts
INSERT INTO alerts (title, description, severity, category, source, source_url, alert_time, status) VALUES
('काठमाडौंमा Heavy Rain Alert', 'अर्को २४ घण्टामा काठमाडौं उपत्यकामा भारी वर्षाको सम्भावना', 'warning', 'weather', 'DHM', 'https://www.dhm.gov.np', NOW(), 'active'),
('Load Shedding Schedule', 'आजबाट नयाँ लोडसेडिङ तालिका लागू हुनेछ', 'info', 'power', 'NEA', 'https://www.nea.org.np', NOW(), 'active');

-- ═══════════════════════════════════════════════════════════════════════════════
-- SECTION 4: CREATE INDEXES
-- ═══════════════════════════════════════════════════════════════════════════════

CREATE INDEX idx_radio_status ON radio_stations(status);
CREATE INDEX idx_radio_featured ON radio_stations(featured);
CREATE INDEX idx_podcasts_station ON radio_podcasts(station_id);
CREATE INDEX idx_loksewa_type ON loksewa_notices(type);
CREATE INDEX idx_loksewa_fetched ON loksewa_notices(fetched_at);
CREATE INDEX idx_stories_featured ON success_stories(featured);
CREATE INDEX idx_stories_category ON success_stories(category);
CREATE INDEX idx_news_category ON tech_news(category);
CREATE INDEX idx_news_created ON tech_news(created_at);
CREATE INDEX idx_rashifal_date ON rashifal_daily(rashi_number, date);
CREATE INDEX idx_weather_city ON weather_cache(city);
CREATE INDEX idx_market_symbol ON market_data_cache(symbol);
CREATE INDEX idx_alerts_severity ON alerts(severity);
CREATE INDEX idx_alerts_status ON alerts(status);
CREATE INDEX idx_jobs_status ON job_listings(status);
CREATE INDEX idx_jobs_date ON job_listings(posted_date);
CREATE INDEX idx_cricket_match ON cricket_matches(match_id);
CREATE INDEX idx_bus_routes ON bus_routes(from_city, to_city);

-- ═══════════════════════════════════════════════════════════════════════════════
-- DONE! All tables created with UTF-8 support and sample data inserted
-- ═══════════════════════════════════════════════════════════════════════════════
SELECT '✅ Complete database setup finished successfully!' AS result,
       'Created 12 tables with UTF-8 charset' AS details;
