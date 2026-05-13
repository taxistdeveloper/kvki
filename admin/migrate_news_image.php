<?php
/**
 * Миграция: добавить поле image_url в таблицу news
 * Выполните один раз, если таблица news уже создана без картинки
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) {
    die('Database not available');
}
try {
    $db->exec('ALTER TABLE news ADD COLUMN image_url VARCHAR(500) DEFAULT NULL');
    echo "Поле 'image_url' добавлено в таблицу news.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Поле 'image_url' уже существует.";
    } else {
        echo "Ошибка: " . $e->getMessage();
    }
}
