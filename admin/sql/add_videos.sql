-- Видео медиацентра
CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    video_url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500) DEFAULT NULL,
  `date` VARCHAR(20) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    duration VARCHAR(20) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_sort (sort_order),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
