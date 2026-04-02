<?php
/**
 * Добавляет колонку source в instagram_posts (если её нет)
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) die('Database not available');

try {
    $db->exec("ALTER TABLE instagram_posts ADD COLUMN source VARCHAR(20) DEFAULT 'manual'");
    echo 'OK: Колонка source добавлена.';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo 'Колонка source уже существует.';
    } else {
        echo 'Ошибка: ' . $e->getMessage();
    }
}
