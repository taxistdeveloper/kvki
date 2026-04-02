<?php
/**
 * Конфигурация админ-панели
 */
session_start();

define('ADMIN_ROOT', dirname(__DIR__));
require_once ADMIN_ROOT . '/config/config.php';

define('ADMIN_URL', BASE_URL . '/admin');
define('ADMIN_ASSETS', BASE_URL . '/admin/assets');
