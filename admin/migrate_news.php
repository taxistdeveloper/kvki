<?php
/**
 * Миграция: создание таблицы news
 * Выполните один раз: http://ваш-сайт/admin/migrate_news.php
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) {
    die('Database not available');
}
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    `date` VARCHAR(20) NOT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    views INT UNSIGNED DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_date (date),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
try {
    $db->exec($sql);
    echo "Таблица 'news' создана успешно.";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
