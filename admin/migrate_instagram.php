<?php
/**
 * Миграция: создание таблицы instagram_posts
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) die('Database not available');
$sql = "CREATE TABLE IF NOT EXISTS instagram_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_url VARCHAR(500) NOT NULL,
    caption VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
try {
    $db->exec($sql);
    echo "Таблица instagram_posts создана.";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
