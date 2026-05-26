-- =====================================================
-- USER PODCASTS TABLE (New)
-- =====================================================
-- Add this to database.sql or run separately

CREATE TABLE IF NOT EXISTS user_podcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    slug VARCHAR(280) UNIQUE NOT NULL,
    cover_image VARCHAR(500),
    audio_url VARCHAR(500) NOT NULL,
    duration_seconds INT,
    category VARCHAR(50),
    source_name VARCHAR(200) DEFAULT 'आकाशवाणी',
    source_url VARCHAR(500),
    featured TINYINT(1) DEFAULT 0,
    status ENUM('draft','published','archived') DEFAULT 'published',
    views INT DEFAULT 0,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_featured (featured),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
