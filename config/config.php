<?php

/**
 * КГКП Карагандинский высший колледж инжиниринга
 * Главная конфигурация
 */

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', '/kvki_redesign');
define('SITE_NAME', 'КГКП Карагандинский высший колледж инжиниринга');
define('SITE_DESCRIPTION', 'Образовательное учреждение технического профиля');

// Ключ для cron-синхронизации Instagram (опционально)
// define('CRON_INSTAGRAM_KEY', 'EAAM8J4FcQQ8BQ4M5YRP2805h1UWob8UeEyt2QzGsTzBdNlyeu8kow9tOMlmEvH9EGPZCs3ZBJRUKJRSsqDa8kECvaYzhjJkpbZBnRVpUlZAqdWjHjxSPCID5mhjkFwPRTVCZA2AU5WZAZAeZA71L3GqWrUp7RUF7PP3rAfFmo1c1jljSZCAYjzFwg58YWHVml0AseEOxJuqs0whaQ29l7BGRSSlcZAJZBcAVaIpXNN8ZAqPKG9VNTFkYnZCD8uZB2O8T6ftBm9RdPJGzozIOFtXOgCJRDvCtCQ5AZDZD');

// OpenAI API ключ для ИИ-ассистента КВКИ (опционально; без ключа — простые ответы по ключевым словам)
// define('OPENAI_API_KEY', 'sk-...');

// Режим отладки
define('DEBUG', true);
error_reporting(DEBUG ? E_ALL : 0);
ini_set('display_errors', DEBUG ? 1 : 0);

// Автозагрузка классов
spl_autoload_register(function ($class) {
    $file = ROOT_PATH . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

require_once ROOT_PATH . '/config/database.php';
