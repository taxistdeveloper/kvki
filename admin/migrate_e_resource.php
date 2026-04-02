<?php
/**
 * Миграция: создание таблицы e_resource_apps
 * Запустите один раз: /kvki/admin/migrate_e_resource.php
 */
require_once dirname(__DIR__) . '/config/config.php';
header('Content-Type: text/html; charset=utf-8');

$sqlFile = __DIR__ . '/sql/add_e_resource_apps.sql';
if (!file_exists($sqlFile)) {
    die('Файл add_e_resource_apps.sql не найден.');
}

try {
    $db = Database::getInstance();
    $sql = file_get_contents($sqlFile);
    $db->exec($sql);
    echo '<h1>Миграция e_resource_apps выполнена</h1>';
    echo '<p><a href="' . BASE_URL . '/admin/e-resource">Перейти к управлению e-Resource</a></p>';
} catch (PDOException $e) {
    echo '<h1>Ошибка</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
