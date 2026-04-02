<?php
/**
 * Установка таблиц админ-панели.
 * Откройте в браузере: /kvki/admin/install.php
 * После установки удалите этот файл.
 */
require_once dirname(__DIR__) . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');

$sqlFile = __DIR__ . '/sql/install.sql';
if (!file_exists($sqlFile)) {
    die('Файл install.sql не найден.');
}

try {
    $db = Database::getInstance();
    $sql = file_get_contents($sqlFile);
    $db->exec($sql);
    echo '<h1>Установка завершена</h1>';
    echo '<p>Таблицы созданы. Логин: <strong>admin</strong>, пароль: <strong>admin123</strong></p>';
    echo '<p><a href="' . BASE_URL . '/admin">Войти в админ-панель</a></p>';
    echo '<p style="color:red;">Удалите файл admin/install.php после установки!</p>';
} catch (PDOException $e) {
    echo '<h1>Ошибка</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
