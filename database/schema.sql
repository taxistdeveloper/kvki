-- КГКП Карагандинский высший колледж инжиниринга
-- Схема базы данных

CREATE DATABASE IF NOT EXISTS kvki CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kvki;

CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    meta_description VARCHAR(500),
    parent_id INT UNSIGNED DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Пример главной страницы
INSERT INTO pages (slug, title, content, meta_description) VALUES
('index', 'Главная', '<h1>Добро пожаловать в КГКП Карагандинский высший колледж инжиниринга</h1><p>Современное образовательное учреждение технического профиля.</p>', 'Карагандинский высший колледж инжиниринга - образование в области строительства и дизайна')
ON DUPLICATE KEY UPDATE title=VALUES(title);
