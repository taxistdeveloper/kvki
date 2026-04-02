<?php
/**
 * Миграция: создание таблицы footer_settings
 * Запустите один раз: /kvki/admin/migrate_footer.php
 */
require_once dirname(__DIR__) . '/config/config.php';
header('Content-Type: text/html; charset=utf-8');

$sqlFile = __DIR__ . '/sql/add_footer_settings.sql';
if (!file_exists($sqlFile)) {
    die('Файл add_footer_settings.sql не найден.');
}

try {
    $db = Database::getInstance();
    $sql = file_get_contents($sqlFile);
    $db->exec($sql);
    echo '<h1>Миграция footer_settings выполнена</h1>';
    echo '<p><a href="' . BASE_URL . '/admin/footer">Перейти к настройкам footer</a></p>';
} catch (PDOException $e) {
    echo '<h1>Ошибка</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
