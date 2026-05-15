<?php
/**
 * Миграция: таблица videos для медиацентра
 * Выполните один раз: http://ваш-сайт/kvki/admin/migrate_videos.php
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) {
    die('Database not available');
}
$sql = file_get_contents(__DIR__ . '/sql/add_videos.sql');
$categorySql = file_get_contents(__DIR__ . '/sql/add_video_categories.sql');
try {
    $db->exec($sql);
    $db->exec($categorySql);
    echo "Таблицы 'videos' и 'video_categories' созданы успешно.";
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
}
