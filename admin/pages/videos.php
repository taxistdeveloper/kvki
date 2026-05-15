<?php
$adminTitle = 'Видео';
$action = 'videos';
require __DIR__ . '/../includes/header.php';

require_once dirname(__DIR__, 2) . '/classes/VideoEmbed.php';

$db = Database::getInstance();
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';
$categoryMessage = '';

function ensureVideoCategory(PDO $db, string $name): void
{
    $name = trim($name);
    if ($name === '') {
        return;
    }
    $db->prepare('INSERT IGNORE INTO video_categories (name) VALUES (?)')->execute([$name]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $newCategory = trim($_POST['new_category'] ?? '');
    if ($newCategory !== '') {
        try {
            ensureVideoCategory($db, $newCategory);
            header('Location: ' . ADMIN_URL . '/videos');
            exit;
        } catch (PDOException $e) {
            $categoryMessage = $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category_id'])) {
    $categoryId = (int)$_POST['delete_category_id'];
    if ($categoryId > 0) {
        try {
            $db->prepare('DELETE FROM video_categories WHERE id = ?')->execute([$categoryId]);
            header('Location: ' . ADMIN_URL . '/videos');
            exit;
        } catch (PDOException $e) {
            $categoryMessage = $e->getMessage();
        }
    }
}

if (isset($_POST['delete']) && $editId) {
    $db->prepare('DELETE FROM videos WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/videos');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete']) && !isset($_POST['add_category']) && !isset($_POST['delete_category_id'])) {
    $date = trim($_POST['date'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');

    if ($date && $title && $videoUrl) {
        try {
            if ($category !== '') {
                ensureVideoCategory($db, $category);
            }
            if ($editId) {
                $db->prepare('UPDATE videos SET `date`=?, title=?, excerpt=?, video_url=?, thumbnail_url=?, category=?, duration=?, sort_order=?, is_active=? WHERE id=?')
                    ->execute([$date, $title, $excerpt, $videoUrl, $thumbnailUrl ?: null, $category ?: null, $duration ?: null, $sortOrder, $isActive, $editId]);
            } else {
                $db->prepare('INSERT INTO videos (`date`, title, excerpt, video_url, thumbnail_url, category, duration, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?)')
                    ->execute([$date, $title, $excerpt, $videoUrl, $thumbnailUrl ?: null, $category ?: null, $duration ?: null, $sortOrder, $isActive]);
            }
            $savedId = $editId ?: (int)$db->lastInsertId();

            if (!empty($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $uploadDir = dirname(__DIR__, 2) . '/assets/images/videos';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $filename = 'video-' . $savedId . '.' . $ext;
                    $filepath = $uploadDir . '/' . $filename;
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $filepath)) {
                        $thumbnailUrl = BASE_URL . '/assets/images/videos/' . $filename;
                        $db->prepare('UPDATE videos SET thumbnail_url=? WHERE id=?')->execute([$thumbnailUrl, $savedId]);
                    }
                }
            } elseif ($thumbnailUrl === '' && $videoUrl !== '') {
                $autoThumb = VideoEmbed::thumbnailUrl($videoUrl);
                if ($autoThumb !== '') {
                    $db->prepare('UPDATE videos SET thumbnail_url=? WHERE id=?')->execute([$autoThumb, $savedId]);
                }
            }

            header('Location: ' . ADMIN_URL . '/videos');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    } else {
        $formError = 'Заполните дату, заголовок и ссылку на видео.';
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM videos WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = [];
try {
    $items = $db->query('SELECT * FROM videos ORDER BY sort_order, `date` DESC, id DESC')->fetchAll();
} catch (PDOException $e) {
    $formError = 'Таблица videos не найдена. Выполните миграцию: admin/migrate_videos.php';
}

$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
$categoryOptions = [];
try {
    $categoryOptions = $db->query('SELECT id, name FROM video_categories ORDER BY sort_order, name')->fetchAll();
} catch (PDOException $e) {
    if (!$formError) {
        $formError = 'Таблица video_categories не найдена. Выполните миграцию: admin/migrate_videos.php';
    }
}
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Видео</h1>
                <p class="text-ink-500 text-sm mt-0.5">Медиацентр: добавление и редактирование роликов для страницы «Видео».</p>
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
    <?php $item = $item ?? ['date' => date('d.m.Y'), 'title' => '', 'excerpt' => '', 'video_url' => '', 'thumbnail_url' => '', 'category' => '', 'duration' => '', 'sort_order' => 0, 'is_active' => 1]; ?>
    <div class="mb-8 rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать видео' : 'Новое видео' ?></h2>
        </div>
        <div class="p-6">
            <?php if (!empty($formError)): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <p class="text-sm text-ink-600 mb-5">Ссылка на YouTube, Rutube или VK. Для YouTube превью подставится автоматически, если не загрузить своё.</p>
            <form method="post" enctype="multipart/form-data">
                <div class="space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Дата</label>
                            <input type="text" name="date" value="<?= htmlspecialchars($item['date']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="12.03.2025">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Категория</label>
                            <input type="text" name="category" id="video-category-input" list="video-categories" value="<?= htmlspecialchars($item['category'] ?? '') ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Введите или выберите категорию">
                            <datalist id="video-categories">
                                <?php foreach ($categoryOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option['name']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <?php if (!empty($categoryOptions)): ?>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <?php foreach ($categoryOptions as $option): ?>
                                <button type="button" class="px-3 py-1.5 rounded-full border border-cream-200 text-xs font-medium text-ink-600 hover:border-sage-300 hover:text-sage-700 transition-colors" data-video-category-pick="<?= htmlspecialchars($option['name'], ENT_QUOTES) ?>"><?= htmlspecialchars($option['name']) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <p class="text-xs text-ink-500 mt-1.5">Новая категория сохранится в справочник при сохранении видео.</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Заголовок</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Название ролика">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Ссылка на видео</label>
                        <input type="url" name="video_url" value="<?= htmlspecialchars($item['video_url']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Длительность</label>
                            <input type="text" name="duration" value="<?= htmlspecialchars($item['duration'] ?? '') ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="04:21">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Порядок</label>
                            <input type="number" name="sort_order" value="<?= (int)($item['sort_order'] ?? 0) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Превью</label>
                        <div class="flex flex-col sm:flex-row gap-5">
                            <div class="flex-1">
                                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100 file:cursor-pointer transition-colors">
                                <p class="text-xs text-ink-500 mt-1.5">JPG, PNG, GIF, WebP. Или укажите URL ниже.</p>
                            </div>
                            <div class="sm:w-40 shrink-0">
                                <?php $previewThumb = $item['thumbnail_url'] ?: ($item['video_url'] ? VideoEmbed::thumbnailUrl($item['video_url']) : ''); ?>
                                <?php if ($previewThumb): ?>
                                    <img src="<?= htmlspecialchars($previewThumb) ?>" alt="" class="w-full h-24 object-cover rounded-xl border border-cream-200 bg-white shadow-inner">
                                <?php else: ?>
                                    <div class="w-full h-24 rounded-xl border-2 border-dashed border-cream-200 bg-cream-50 flex items-center justify-center text-ink-400 text-xs">Нет превью</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <input type="text" name="thumbnail_url" value="<?= htmlspecialchars($item['thumbnail_url'] ?? '') ?>" class="mt-3 w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm transition-all outline-none" placeholder="URL превью (или загрузите файл выше)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Краткое описание</label>
                        <textarea name="excerpt" rows="3" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"><?= htmlspecialchars($item['excerpt'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2.5 cursor-pointer px-4 py-2.5 rounded-xl border border-cream-200 bg-white hover:bg-cream-50 transition-colors">
                            <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded text-sage-600 focus:ring-sage-500 focus:ring-2">
                            <span class="text-sm font-medium text-ink-700">Опубликовано</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <a href="<?= ADMIN_URL ?>/videos" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
            <?php if ($editId): ?>
            <form method="post" class="mt-8 pt-6 border-t border-cream-200" onsubmit="return confirm('Удалить видео?');">
                <input type="hidden" name="delete" value="1">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Удалить видео
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="mb-8 rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800">Категории</h2>
        </div>
        <div class="p-6">
            <?php if ($categoryMessage): ?>
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm"><?= htmlspecialchars($categoryMessage) ?></div>
            <?php endif; ?>
            <p class="text-sm text-ink-600 mb-4">Введите название и нажмите «Добавить». Категория появится в подсказках и в фильтрах на сайте.</p>
            <form method="post" class="flex flex-col sm:flex-row gap-3 mb-5">
                <input type="text" name="new_category" class="flex-1 px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Например: Мероприятия" required>
                <button type="submit" name="add_category" value="1" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                    Добавить
                </button>
            </form>
            <?php if (!empty($categoryOptions)): ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($categoryOptions as $option): ?>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-cream-200 bg-cream-50 text-sm text-ink-700">
                    <span><?= htmlspecialchars($option['name']) ?></span>
                    <form method="post" class="inline" onsubmit="return confirm('Удалить категорию из справочника?');">
                        <input type="hidden" name="delete_category_id" value="<?= (int)$option['id'] ?>">
                        <button type="submit" class="text-ink-400 hover:text-red-600 transition-colors" aria-label="Удалить категорию">×</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-sm text-ink-500">Категорий пока нет.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список видео</span>
            <a href="<?= ADMIN_URL ?>/videos/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить видео
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700 w-24">Превью</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Заголовок</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Категория</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Дата</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Статус</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-ink-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <p>Видео пока нет</p>
                                    <a href="<?= ADMIN_URL ?>/videos/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить видео
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $video): ?>
                        <?php $thumb = $video['thumbnail_url'] ?: VideoEmbed::thumbnailUrl($video['video_url']); ?>
                        <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <?php if ($thumb): ?>
                                    <img src="<?= htmlspecialchars($thumb) ?>" alt="" class="h-12 w-20 object-cover rounded-lg border border-cream-200">
                                <?php else: ?>
                                    <span class="text-ink-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <a href="<?= ADMIN_URL ?>/videos/<?= $video['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($video['title']) ?></a>
                            </td>
                            <td class="px-6 py-3 text-ink-600"><?= htmlspecialchars($video['category'] ?: '—') ?></td>
                            <td class="px-6 py-3 text-ink-600"><?= htmlspecialchars($video['date']) ?></td>
                            <td class="px-6 py-3">
                                <?php if ((int)$video['is_active']): ?>
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-sage-100 text-sage-700">Опубликовано</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-cream-200 text-ink-500">Скрыто</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= ADMIN_URL ?>/videos/<?= $video['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                                <form method="post" action="<?= ADMIN_URL ?>/videos/<?= $video['id'] ?>" class="inline" onsubmit="return confirm('Удалить видео?');">
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
        <a href="<?= BASE_URL ?>/video" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Посмотреть на сайте
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('video-category-input');
    if (!input) return;
    document.querySelectorAll('[data-video-category-pick]').forEach(function (button) {
        button.addEventListener('click', function () {
            input.value = button.getAttribute('data-video-category-pick') || '';
            input.focus();
        });
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
