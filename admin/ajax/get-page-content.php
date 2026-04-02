<?php
/**
 * API: загрузить контент из content/pages/{slug}.html
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

adminRequireAuth();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    http_response_code(400);
    echo json_encode(['error' => 'slug required']);
    exit;
}

$content = Page::getContentFromFile($slug);
$filePath = Page::getContentFilePath($slug);

if ($content === null) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Файл не найден',
        'path' => $filePath
    ]);
    exit;
}

echo json_encode([
    'content' => $content,
    'path' => $filePath
], JSON_UNESCAPED_UNICODE);
