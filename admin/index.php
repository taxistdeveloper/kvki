<?php
/**
 * Точка входа админ-панели
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/admin';
$base = preg_quote(BASE_URL . '/admin', '#');
$path = preg_replace("#^{$base}#", '', parse_url($requestUri, PHP_URL_PATH) ?: '');
$path = trim($path, '/') ?: 'dashboard';
$segments = explode('/', $path);
$action = $segments[0] ?? 'dashboard';
$id = $segments[1] ?? null;

// Выход
if ($action === 'logout') {
    adminLogout();
    header('Location: ' . ADMIN_URL . '/login');
    exit;
}

// Логин — без проверки авторизации
if ($action === 'login') {
    if (adminIsLoggedIn()) {
        header('Location: ' . ADMIN_URL);
        exit;
    }
    require __DIR__ . '/pages/login.php';
    exit;
}

// Остальные страницы — требуют авторизации
adminRequireAuth();

switch ($action) {
    case 'dashboard':
        require __DIR__ . '/pages/dashboard.php';
        break;
    case 'pages':
        require __DIR__ . '/pages/pages.php';
        break;
    case 'announcements':
        require __DIR__ . '/pages/announcements.php';
        break;
    case 'news':
        require __DIR__ . '/pages/news.php';
        break;
    case 'slides':
        require __DIR__ . '/pages/slides.php';
        break;
    case 'partners':
        require __DIR__ . '/pages/partners.php';
        break;
    case 'instagram':
        require __DIR__ . '/pages/instagram.php';
        break;
    case 'menu':
        require __DIR__ . '/pages/menu.php';
        break;
    case 'header':
        require __DIR__ . '/pages/header.php';
        break;
    case 'footer':
        require __DIR__ . '/pages/footer.php';
        break;
    case 'e-resource':
        require __DIR__ . '/pages/e-resource.php';
        break;
    case 'ajax':
        $ajaxFile = $segments[1] ?? '';
        if (preg_match('/^get-page-content/', $ajaxFile)) {
            require __DIR__ . '/ajax/get-page-content.php';
        } else {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not found']);
        }
        break;
    default:
        header('Location: ' . ADMIN_URL);
        exit;
}
