<?php
/**
 * Миграция: Instagram API (настройки + колонка source)
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) die('Database not available');

$ok = [];
$err = [];

try {
    $db->exec("CREATE TABLE IF NOT EXISTS instagram_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        access_token VARCHAR(500) NOT NULL,
        ig_user_id VARCHAR(50) NOT NULL,
        last_sync_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $ok[] = 'Таблица instagram_settings создана';
} catch (PDOException $e) {
    $err[] = $e->getMessage();
}

try {
    $db->exec("ALTER TABLE instagram_posts ADD COLUMN source VARCHAR(20) DEFAULT 'manual'");
    $ok[] = 'Колонка source добавлена в instagram_posts';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        $ok[] = 'Колонка source уже существует';
    } else {
        $err[] = $e->getMessage();
    }
}

foreach ($ok as $m) echo $m . "<br>";
foreach ($err as $m) echo "Ошибка: " . $m . "<br>";
