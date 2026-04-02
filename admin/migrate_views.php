<?php
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) {
    die('Database not available');
}
try {
    $db->exec('ALTER TABLE announcements ADD COLUMN views INT UNSIGNED DEFAULT 0');
    echo "Column 'views' added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'views' already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
