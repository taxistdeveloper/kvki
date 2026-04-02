-- Настройки для автоматической синхронизации Instagram
CREATE TABLE IF NOT EXISTS instagram_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token VARCHAR(500) NOT NULL,
    ig_user_id VARCHAR(50) NOT NULL,
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Источник поста: manual = вручную, api = из Instagram API
ALTER TABLE instagram_posts ADD COLUMN source VARCHAR(20) DEFAULT 'manual';
