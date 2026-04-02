<?php
/**
 * Cron: синхронизация Instagram постов
 * Вызовите по расписанию: */30 * * * * curl -s "https://ваш-сайт/kvki/admin/cron_instagram_sync.php?key=ВАШ_СЕКРЕТ"
 * Или добавьте ключ в config: define('CRON_INSTAGRAM_KEY', 'ваш-секрет');
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/classes/InstagramApi.php';

$key = $_GET['key'] ?? '';
$expectedKey = defined('CRON_INSTAGRAM_KEY') ? CRON_INSTAGRAM_KEY : null;
if (!$expectedKey || $key !== $expectedKey) {
    http_response_code(403);
    exit('Forbidden');
}

$db = Database::tryGetInstance();
if (!$db) exit('DB error');

$settings = $db->query('SELECT * FROM instagram_settings ORDER BY id DESC LIMIT 1')->fetch();
if (!$settings) exit('No API settings');

$posts = InstagramApi::fetchMedia($settings['access_token'], $settings['ig_user_id'], 12);
$db->prepare('DELETE FROM instagram_posts WHERE source = ?')->execute(['api']);
$stmt = $db->prepare('INSERT INTO instagram_posts (post_url, caption, sort_order, source) VALUES (?,?,?,?)');
foreach ($posts as $i => $p) {
    $caption = $p['caption'] ? mb_substr($p['caption'], 0, 500) : null;
    $stmt->execute([$p['post_url'], $caption, $i, 'api']);
}
$db->prepare('UPDATE instagram_settings SET last_sync_at = NOW() WHERE id = ?')->execute([$settings['id']]);

echo 'OK:' . count($posts);
