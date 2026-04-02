<?php
/**
 * Миграция: создание таблицы header_settings
 * Запустите один раз: /kvki/admin/migrate_header.php
 */
require_once dirname(__DIR__) . '/config/config.php';
header('Content-Type: text/html; charset=utf-8');

$sqlFile = __DIR__ . '/sql/add_header_settings.sql';
if (!file_exists($sqlFile)) {
    die('Файл add_header_settings.sql не найден.');
}

try {
    $db = Database::getInstance();
    $sql = file_get_contents($sqlFile);
    $db->exec($sql);
    $extrasFile = __DIR__ . '/sql/add_header_extras.sql';
    if (file_exists($extrasFile)) {
        $db->exec(file_get_contents($extrasFile));
    }
    echo '<h1>Миграция header_settings выполнена</h1>';
    echo '<p><a href="' . BASE_URL . '/admin/header">Перейти к настройкам header</a></p>';
} catch (PDOException $e) {
    echo '<h1>Ошибка</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
