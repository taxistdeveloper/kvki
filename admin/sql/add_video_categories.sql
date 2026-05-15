-- Справочник категорий для видео
CREATE TABLE IF NOT EXISTS video_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_name (name),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO video_categories (name, sort_order) VALUES
('Мероприятия', 0),
('Учеба', 1),
('Студенты', 2),
('Студенческая жизнь', 3);

INSERT IGNORE INTO video_categories (name, sort_order)
SELECT DISTINCT TRIM(category), 100
FROM videos
WHERE category IS NOT NULL AND TRIM(category) <> '';
