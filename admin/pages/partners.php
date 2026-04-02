<?php
$adminTitle = 'Партнёры';
$action = 'partners';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';

if (isset($_POST['delete']) && $editId) {
    $db->prepare('DELETE FROM partners WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/partners');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $name = trim($_POST['name'] ?? '');
    $url = trim($_POST['url'] ?? '#');
    $imageUrl = trim($_POST['image_url'] ?? '');

    if ($name) {
        try {
            if ($editId) {
                $db->prepare('UPDATE partners SET name=?, url=?, image_url=? WHERE id=?')
                    ->execute([$name, $url ?: '#', $imageUrl, $editId]);
            } else {
                $db->prepare('INSERT INTO partners (name, url, image_url) VALUES (?,?,?)')
                    ->execute([$name, $url ?: '#', $imageUrl]);
            }
            $savedId = $editId ?: $db->lastInsertId();

            // Загрузка картинки
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $uploadDir = dirname(__DIR__, 2) . '/assets/images/partners';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $filename = 'partner-' . $savedId . '.' . $ext;
                    $filepath = $uploadDir . '/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                        $imageUrl = BASE_URL . '/assets/images/partners/' . $filename;
                        $db->prepare('UPDATE partners SET image_url=? WHERE id=?')->execute([$imageUrl, $savedId]);
                    }
                }
            }

            header('Location: ' . ADMIN_URL . '/partners');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM partners WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = $db->query('SELECT * FROM partners ORDER BY sort_order, id')->fetchAll();
$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Партнёры</h1>
                <p class="text-ink-500 text-sm mt-0.5">Добавление, редактирование и удаление логотипов партнёров.</p>
            </div>
        </div>
    </div>

    <?php if ($formError && !$showForm): ?>
    <div class="mb-5 py-3 px-4 bg-red-50 border border-red-100 text-red-700 rounded-xl flex items-center gap-3 text-sm shadow-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($formError) ?>
    </div>
    <?php endif; ?>

<?php if ($showForm): ?>
    <?php $item = $item ?? ['name' => '', 'url' => '#', 'image_url' => '']; ?>
    <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать партнёра' : 'Новый партнёр' ?></h2>
        </div>
        <div class="p-6">
            <?php if (!empty($formError)): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <p class="text-sm text-ink-600 mb-5">Загрузите логотип или укажите URL. JPG, PNG, WebP, SVG.</p>
            <form method="post" enctype="multipart/form-data">
                <div class="space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Название</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Название партнёра">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">URL сайта</label>
                            <input type="text" name="url" value="<?= htmlspecialchars($item['url']) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="https://...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Логотип</label>
                        <div class="flex flex-col sm:flex-row gap-5">
                            <div class="flex-1">
                                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100 file:cursor-pointer transition-colors">
                                <p class="text-xs text-ink-500 mt-1.5">Загрузите файл или укажите URL ниже</p>
                            </div>
                            <div class="sm:w-36 shrink-0">
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" class="w-full h-24 object-contain rounded-xl border border-cream-200 bg-white p-2 shadow-inner">
                                <?php else: ?>
                                    <div class="w-full h-24 rounded-xl border-2 border-dashed border-cream-200 bg-cream-50 flex items-center justify-center text-ink-400 text-xs">Нет логотипа</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url']) ?>" class="mt-3 w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm transition-all outline-none" placeholder="URL логотипа (или загрузите файл выше)">
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <a href="<?= ADMIN_URL ?>/partners" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
            <?php if ($editId): ?>
            <form method="post" class="mt-8 pt-6 border-t border-cream-200" onsubmit="return confirm('Удалить партнёра?');">
                <input type="hidden" name="delete" value="1">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Удалить партнёра
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список партнёров</span>
            <a href="<?= ADMIN_URL ?>/partners/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить партнёра
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700 w-20">Лого</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Название</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-ink-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <p>Партнёров пока нет</p>
                                    <a href="<?= ADMIN_URL ?>/partners/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить партнёра
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $p): ?>
                        <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" class="h-10 w-auto max-w-[80px] object-contain">
                                <?php else: ?>
                                    <span class="text-ink-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <a href="<?= ADMIN_URL ?>/partners/<?= $p['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($p['name']) ?></a>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= ADMIN_URL ?>/partners/<?= $p['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                                <form method="post" action="<?= ADMIN_URL ?>/partners/<?= $p['id'] ?>" class="inline" onsubmit="return confirm('Удалить партнёра?');">
                                    <input type="hidden" name="delete" value="1">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="<?= BASE_URL ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Посмотреть на сайте
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
