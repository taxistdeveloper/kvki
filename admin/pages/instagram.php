<?php
$adminTitle = 'Instagram';
$action = 'instagram';
require __DIR__ . '/../includes/header.php';

require_once dirname(__DIR__, 2) . '/classes/InstagramApi.php';

$db = Database::getInstance();
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';
$apiMessage = '';
$apiSettings = null;

// Загрузка настроек API
try {
    $apiSettings = $db->query('SELECT * FROM instagram_settings ORDER BY id DESC LIMIT 1')->fetch();
} catch (PDOException $e) {}

// Сохранение настроек API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_action'])) {
    if ($_POST['api_action'] === 'save_settings') {
        $token = trim($_POST['access_token'] ?? '');
        $igUserId = trim($_POST['ig_user_id'] ?? '');
        if ($token && $igUserId) {
            // Пробуем получить Page Access Token (для API media нужен именно он)
            $result = InstagramApi::getInstagramIdFromToken($token);
            $accessToken = ($result && !empty($result['page_access_token'])) ? $result['page_access_token'] : $token;
            $igId = ($result && !empty($result['ig_user_id'])) ? $result['ig_user_id'] : $igUserId;
            try {
                $db->exec('DELETE FROM instagram_settings');
                $db->prepare('INSERT INTO instagram_settings (access_token, ig_user_id) VALUES (?,?)')->execute([$accessToken, $igId]);
                $apiMessage = 'success:Настройки сохранены. Нажмите «Синхронизировать» для загрузки постов.';
                header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
                exit;
            } catch (PDOException $e) {
                $apiMessage = 'error:' . $e->getMessage();
            }
        } else {
            $apiMessage = 'error:Укажите токен и Instagram ID';
        }
    } elseif ($_POST['api_action'] === 'discover') {
        $token = trim($_POST['access_token'] ?? '');
        if ($token) {
            $result = InstagramApi::getInstagramIdFromToken($token);
            if ($result && !empty($result['page_access_token'])) {
                // Сохраняем Page Access Token (не User token!) — для API media нужен именно он
                try {
                    $db->exec('DELETE FROM instagram_settings');
                    $db->prepare('INSERT INTO instagram_settings (access_token, ig_user_id) VALUES (?,?)')
                        ->execute([$result['page_access_token'], $result['ig_user_id']]);
                    $apiMessage = 'success:Подключено @' . ($result['username'] ?? '') . '. Нажмите «Синхронизировать» для загрузки постов.';
                    header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
                    exit;
                } catch (PDOException $e) {
                    $apiMessage = 'error:' . $e->getMessage();
                    header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
                    exit;
                }
            } else {
                $err = InstagramApi::$lastError ?: 'Проверьте токен и что аккаунт Business/Creator подключён к Facebook Page.';
                $apiMessage = 'error:' . $err;
                header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
                exit;
            }
        }
    } elseif ($_POST['api_action'] === 'sync') {
        if ($apiSettings) {
            $posts = InstagramApi::fetchMedia($apiSettings['access_token'], $apiSettings['ig_user_id'], 12);
            if (empty($posts) && InstagramApi::$lastError) {
                $apiMessage = 'error:Синхронизация не удалась: ' . InstagramApi::$lastError;
                header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
                exit;
            }
            try {
                $db->prepare('DELETE FROM instagram_posts WHERE source = ?')->execute(['api']);
                $stmt = $db->prepare('INSERT INTO instagram_posts (post_url, caption, sort_order, source) VALUES (?,?,?,?)');
                foreach ($posts as $i => $p) {
                    $caption = $p['caption'] ? mb_substr($p['caption'], 0, 500) : null;
                    $stmt->execute([$p['post_url'], $caption, $i, 'api']);
                }
                $db->prepare('UPDATE instagram_settings SET last_sync_at = NOW() WHERE id = ?')->execute([$apiSettings['id']]);
                $apiMessage = 'success:Загружено ' . count($posts) . ' постов.';
            } catch (PDOException $e) {
                $apiMessage = 'error:' . $e->getMessage();
            }
            header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
            exit;
        } else {
            $apiMessage = 'error:Сначала сохраните настройки API';
            header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
            exit;
        }
    } elseif ($_POST['api_action'] === 'remove_api') {
        try {
            $db->exec('DELETE FROM instagram_settings');
            $db->prepare('DELETE FROM instagram_posts WHERE source = ?')->execute(['api']);
            $apiMessage = 'success:Автосинхронизация отключена.';
            header('Location: ' . ADMIN_URL . '/instagram?msg=' . urlencode($apiMessage));
            exit;
        } catch (PDOException $e) {}
    }
}

if (isset($_POST['delete']) && $editId) {
    $db->prepare('DELETE FROM instagram_posts WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/instagram');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete']) && !isset($_POST['api_action'])) {
    $postUrl = trim($_POST['post_url'] ?? '');
    $caption = trim($_POST['caption'] ?? '');

    if ($postUrl && (str_contains($postUrl, 'instagram.com/p/') || str_contains($postUrl, 'instagram.com/reel/') || str_contains($postUrl, 'instagram.com/reels/'))) {
        try {
            if ($editId) {
                $db->prepare('UPDATE instagram_posts SET post_url=?, caption=? WHERE id=?')
                    ->execute([$postUrl, $caption ?: null, $editId]);
            } else {
                $maxOrder = $db->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM instagram_posts')->fetchColumn();
                $db->prepare('INSERT INTO instagram_posts (post_url, caption, sort_order, source) VALUES (?,?,?,?)')
                    ->execute([$postUrl, $caption ?: null, (int)$maxOrder, 'manual']);
            }
            header('Location: ' . ADMIN_URL . '/instagram');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    } else {
        $formError = 'Укажите ссылку на пост или Reels (например: instagram.com/p/ABC123/ или instagram.com/reels/ABC123/)';
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM instagram_posts WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = [];
try {
    $items = $db->query('SELECT * FROM instagram_posts ORDER BY sort_order, id')->fetchAll();
} catch (PDOException $e) {
    $formError = 'Таблица instagram_posts не найдена. Выполните: admin/migrate_instagram.php';
}

$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
$instagramProfileUrl = 'https://www.instagram.com/ktsk.kz';

$msg = $_GET['msg'] ?? '';
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center shadow-lg shadow-pink-500/20">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Instagram</h1>
                <p class="text-ink-500 text-sm mt-0.5">Добавление, редактирование постов с <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" class="text-sage-600 hover:underline">@ktsk.kz</a>. Они отобразятся на главной странице.</p>
            </div>
        </div>
    </div>

    <?php if ($msg): ?>
        <?php
        $msgParts = explode(':', $msg, 2);
        $msgType = $msgParts[0] ?? 'info';
        $msgText = $msgParts[1] ?? $msg;
        ?>
        <div class="mb-5 py-3 px-4 rounded-xl text-sm flex items-center gap-3 shadow-sm <?= $msgType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-100 text-red-700' ?>">
            <?php if ($msgType === 'success'): ?>
            <svg class="w-5 h-5 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <?php else: ?>
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php endif; ?>
            <?= htmlspecialchars($msgText) ?>
        </div>
    <?php endif; ?>

    <?php if ($formError && !$showForm): ?>
        <div class="mb-5 py-3 px-4 bg-red-50 border border-red-100 text-red-700 rounded-xl flex items-center gap-3 text-sm shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= htmlspecialchars($formError) ?>
        </div>
    <?php endif; ?>

    <!-- Автоматическая синхронизация -->
    <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-purple-50 to-pink-50">
            <h2 class="text-lg font-semibold text-ink-800">Автоматическая синхронизация</h2>
            <p class="text-sm text-ink-500 mt-1">Требуется Instagram Business/Creator + Facebook Page</p>
        </div>
        <div class="p-6">
            <?php if ($apiSettings): ?>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="text-sm text-ink-600">
                        <span class="font-medium text-green-600">✓ Настроено</span>
                        <?php if ($apiSettings['last_sync_at']): ?>
                            · Последняя синхронизация: <?= date('d.m.Y H:i', strtotime($apiSettings['last_sync_at'])) ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="post" class="inline">
                            <input type="hidden" name="api_action" value="sync">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">Синхронизировать</button>
                        </form>
                        <form method="post" class="inline" onsubmit="return confirm('Отключить автосинхронизацию? Посты из API будут удалены.')">
                            <input type="hidden" name="api_action" value="remove_api">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 text-sm rounded-xl hover:bg-cream-100 font-medium transition-colors">Отключить</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="api_action" value="discover">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1">User Access Token</label>
                        <input type="password" name="access_token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>" placeholder="EAAx..." required
                            class="w-full px-4 py-2.5 border border-cream-200 rounded-xl text-sm" autocomplete="off">
                        <p class="text-xs text-ink-500 mt-1">Graph API Explorer → «Пользователь или Страница»: выберите <strong>вашу Facebook-страницу</strong> (не «Маркер пользователя»). Права: instagram_basic, pages_show_list. Нажмите «Generate Access Token».</p>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">Подключить</button>
                </form>
                <p class="mt-4 text-xs text-ink-500">
                    <strong>Инструкция:</strong> 1) Создайте приложение на <a href="https://developers.facebook.com/" target="_blank" class="text-sage-600">developers.facebook.com</a>. 2) Добавьте продукт «Instagram Graph API». 3) Подключите Instagram Business к Facebook Page. 4) В Graph API Explorer получите токен с правами instagram_basic, pages_show_list. 5) Вставьте токен и нажмите «Подключить».
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($showForm): ?>
        <?php $item = $item ?? ['post_url' => '', 'caption' => '']; ?>
        <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
            <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
                <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать пост' : 'Добавить пост' ?></h2>
            </div>
            <div class="p-6">
                <?php if ($formError): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
                <form method="post">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Ссылка на пост</label>
                            <input type="url" name="post_url" value="<?= htmlspecialchars($item['post_url']) ?>" required
                                class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"
                                placeholder="https://www.instagram.com/p/ABC123/">
                            <p class="text-xs text-ink-500 mt-1.5">Откройте пост в Instagram → ⋮ → «Копировать ссылку»</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Подпись (необязательно)</label>
                            <input type="text" name="caption" value="<?= htmlspecialchars($item['caption'] ?? '') ?>"
                                class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"
                                placeholder="Краткое описание">
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Сохранить
                        </button>
                        <?php if ($editId): ?>
                        <button type="submit" name="delete" value="1" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all" onclick="return confirm('Удалить пост?');">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Удалить
                        </button>
                        <?php endif; ?>
                        <a href="<?= ADMIN_URL ?>/instagram" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Посты для главной</span>
            <a href="<?= ADMIN_URL ?>/instagram/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить пост
            </a>
        </div>
        <div class="divide-y divide-cream-200">
            <?php if (empty($items)): ?>
                <div class="p-12 text-center text-ink-500">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                        <p>Постов пока нет</p>
                        <a href="<?= ADMIN_URL ?>/instagram/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Добавить пост
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($items as $i => $p): ?>
                <div class="p-4 flex items-center justify-between hover:bg-cream-50">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="<?= htmlspecialchars($p['post_url']) ?>" target="_blank" class="text-sage-600 hover:underline text-sm truncate"><?= htmlspecialchars($p['post_url']) ?></a>
                            <?php if (($p['source'] ?? '') === 'api'): ?><span class="shrink-0 px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded">API</span><?php endif; ?>
                        </div>
                        <?php if (!empty($p['caption'])): ?><p class="text-ink-600 text-sm mt-1 line-clamp-2"><?= htmlspecialchars($p['caption']) ?></p><?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <?php if (($p['source'] ?? '') !== 'api'): ?><a href="<?= ADMIN_URL ?>/instagram/<?= $p['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a><?php endif; ?>
                        <form method="post" action="<?= ADMIN_URL ?>/instagram/<?= $p['id'] ?>" class="inline" onsubmit="return confirm('Удалить пост?');">
                            <input type="hidden" name="delete" value="1">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">Удалить</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-4 items-center">
        <a href="<?= BASE_URL ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Посмотреть на сайте
        </a>
        <span class="text-sm text-ink-600">
            <strong>Как добавить пост:</strong> откройте <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" class="text-sage-600 hover:underline">instagram.com/ktsk.kz</a>, выберите пост → ⋮ → «Копировать ссылку».
        </span>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
