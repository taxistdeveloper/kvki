<?php
$adminTitle = 'Слайды';
$action = 'slides';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';

if (isset($_POST['delete']) && $editId) {
    $db->prepare('DELETE FROM hero_slides WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/slides');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');

    if ($title) {
        try {
            if ($editId) {
                $db->prepare('UPDATE hero_slides SET title=?, `text`=?, image_url=? WHERE id=?')
                    ->execute([$title, $text, $imageUrl, $editId]);
            } else {
                $db->prepare('INSERT INTO hero_slides (title, `text`, image_url) VALUES (?,?,?)')
                    ->execute([$title, $text, $imageUrl]);
            }
            header('Location: ' . ADMIN_URL . '/slides');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM hero_slides WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = $db->query('SELECT * FROM hero_slides ORDER BY sort_order, id')->fetchAll();
$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Слайды</h1>
                <p class="text-ink-500 text-sm mt-0.5">Добавление, редактирование и удаление баннеров на главной странице.</p>
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
    <?php $item = $item ?? ['title' => '', 'text' => '', 'image_url' => '']; ?>
    <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать слайд' : 'Новый слайд' ?></h2>
        </div>
        <div class="p-6">
            <?php if (!empty($formError)): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <p class="text-sm text-ink-600 mb-4">Для изображений: положите slide1.jpg, slide2.jpg и т.д. в <code class="px-1.5 py-0.5 bg-cream-200 rounded text-ink-600">assets/images/hero/</code> или укажите полный URL.</p>
            <form method="post">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Заголовок</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Заголовок слайда">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Текст</label>
                        <textarea name="text" rows="3" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"><?= htmlspecialchars($item['text']) ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">URL изображения (пусто = slide1, slide2...)</label>
                        <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url']) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="https://... или оставьте пустым">
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <a href="<?= ADMIN_URL ?>/slides" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
            <?php if ($editId): ?>
            <form method="post" class="mt-8 pt-6 border-t border-cream-200" onsubmit="return confirm('Удалить слайд?');">
                <input type="hidden" name="delete" value="1">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Удалить слайд
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список слайдов</span>
            <a href="<?= ADMIN_URL ?>/slides/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить слайд
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Заголовок</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="2" class="px-6 py-12 text-center text-ink-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p>Слайдов пока нет</p>
                                    <a href="<?= ADMIN_URL ?>/slides/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить слайд
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $s): ?>
                        <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <a href="<?= ADMIN_URL ?>/slides/<?= $s['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($s['title']) ?></a>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= ADMIN_URL ?>/slides/<?= $s['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                                <form method="post" action="<?= ADMIN_URL ?>/slides/<?= $s['id'] ?>" class="inline" onsubmit="return confirm('Удалить слайд?');">
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
