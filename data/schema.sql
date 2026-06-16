-- आकाशवाणी Database Schema
-- Run this on your MySQL server

CREATE DATABASE IF NOT EXISTS your_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE your_database;

-- News Table
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE,
    summary TEXT,
    content LONGTEXT,
    image VARCHAR(500),
    category VARCHAR(100),
    source VARCHAR(50) DEFAULT 'manual',
    source_name VARCHAR(200),
    status ENUM('draft','published','archived') DEFAULT 'draft',
    published_at DATETIME,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_published (published_at)
) ENGINE=InnoDB;

-- Insert sample news (run after creating table)
INSERT INTO news (title, slug, summary, content, image, category, source, source_name, status, published_at) VALUES
('प्रधानमन्त्रीले नयाँ आर्थिक नीति घोषणा गरे', 'pm-announces-new-economic-policy', 'प्रधानमन्त्रीले आज संसदमा नयाँ आर्थिक नीति घोषणा गरेका छन्।', '<p>प्रधानमन्त्रीले आज संसदमा नयाँ आर्थिक नीति घोषणा गरेका छन्। यस नीतिले देशको आर्थिक विकासमा महत्त्वपूर्ण योगदान पुर्‍याउने अपेक्षा गरिएको छ।</p>', 'https://picsum.photos/800/400?random=1', 'politics', 'manual', 'आकाशवाणी', 'published', NOW()),
('नेपाली राष्ट्रिय फुटबल टोली विश्वकप छनोटमा', 'national-football-team-world-cup-qualifier', 'नेपाली राष्ट्रिय फुटबल टोली विश्वकप छनोट खेल्ने भएको छ।', '<p>नेपाली राष्ट्रिय फुटबल टोली आगामी विश्वकप छनोटमा सहभागी हुने भएको छ।</p>', 'https://picsum.photos/800/400?random=2', 'sports', 'manual', 'आकाशवाणी', 'published', NOW()),
('शेयर बजारमा उत्साहजनक वृद्धि', 'stock-market-surge', 'नेपाली शेयर बजारमा आज उत्साहजनक वृद्धि भएको छ।', '<p>नेपाली शेयर बजारमा आज उत्साहजनक वृद्धि भएको छ।</p>', 'https://picsum.photos/800/400?random=3', 'business', 'manual', 'आकाशवाणी', 'published', NOW()),
('मौसम विभागको आजको पूर्वानुमान', 'weather-forecast-today', 'मौसम विभागले आजको मौसम पूर्वानुमान जारी गरेको छ।', '<p>आज काठमाडौंमा मध्यम वर्षाको सम्भावना छ।</p>', 'https://picsum.photos/800/400?random=4', 'weather', 'manual', 'आकाशवाणी', 'published', NOW()),
('सरकारी सेवा सुधारमा नयाँ पहल', 'government-service-improvement', 'सरकारले सेवा सुधारमा नयाँ पहल गरेको छ।', '<p>सरकारले नागरिकलाई सहज सेवा प्रदान गर्न नयाँ पहल गरेको छ।</p>', 'https://picsum.photos/800/400?random=5', 'government', 'manual', 'आकाशवाणी', 'published', NOW());