<?php
$adminTitle = 'Новости';
$action = 'news';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';

function slugFromTitle(string $title): string {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z',
        'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
        'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
        'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
    ];
    $slug = mb_strtolower(trim($title));
    $result = '';
    $len = mb_strlen($slug);
    for ($i = 0; $i < $len; $i++) {
        $c = mb_substr($slug, $i, 1);
        $result .= $map[$c] ?? $c;
    }
    $result = preg_replace('/[^a-z0-9\s-]/', '', $result);
    $result = preg_replace('/\s+/', '-', $result);
    $result = preg_replace('/-+/', '-', $result);
    return trim($result, '-');
}

// Удаление
if (isset($_POST['delete']) && $editId) {
    $db->prepare('DELETE FROM news WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/news');
    exit;
}

// Сохранение
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $date = trim($_POST['date'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug) && !empty($title)) {
        $slug = slugFromTitle($title);
        $slug = $slug !== '' ? $slug : 'news-' . time();
    }
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $imageUrl = trim($_POST['image_url'] ?? '');

    if ($date && $title && $slug) {
        try {
            if ($editId) {
                $db->prepare('UPDATE news SET `date`=?, title=?, slug=?, excerpt=?, content=?, image_url=?, is_active=? WHERE id=?')
                    ->execute([$date, $title, $slug, $excerpt, $content, $imageUrl ?: null, $isActive, $editId]);
            } else {
                $db->prepare('INSERT INTO news (`date`, title, slug, excerpt, content, image_url, is_active) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$date, $title, $slug, $excerpt, $content, $imageUrl ?: null, $isActive]);
            }
            $savedId = $editId ?: $db->lastInsertId();

            // Загрузка картинки
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $uploadDir = dirname(__DIR__, 2) . '/assets/images/news';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $filename = 'news-' . $savedId . '.' . $ext;
                    $filepath = $uploadDir . '/' . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                        $imageUrl = BASE_URL . '/assets/images/news/' . $filename;
                        $db->prepare('UPDATE news SET image_url=? WHERE id=?')->execute([$imageUrl, $savedId]);
                    }
                }
            }

            header('Location: ' . ADMIN_URL . '/news');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    } else {
        $formError = 'Заполните дату, заголовок и slug.';
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM news WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = [];
try {
    $items = $db->query('SELECT * FROM news ORDER BY created_at DESC, id DESC')->fetchAll();
} catch (PDOException $e) {
    $formError = 'Таблица news не найдена. Выполните миграцию: admin/sql/add_news.sql';
}

$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
?>

<div class="max-w-5xl">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Новости</h1>
                <p class="text-ink-500 text-sm mt-0.5">Добавление, редактирование и удаление новостей. Счётчик просмотров обновляется при просмотре на сайте.</p>
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
        <?php $item = $item ?? ['date' => date('d.m.Y'), 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'image_url' => '', 'is_active' => 1]; ?>
        <div class="mb-8 rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
            <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
                <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать новость' : 'Новая новость' ?></h2>
            </div>
            <div class="p-6">
                <?php if ($formError): ?>
                    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="space-y-5">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1.5">Дата</label>
                                <input type="text" name="date" value="<?= htmlspecialchars($item['date']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="12.03.2025">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink-700 mb-1.5">Slug (URL)</label>
                                <input type="text" name="slug" id="news-slug" value="<?= htmlspecialchars($item['slug']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Автоматически из заголовка">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Заголовок</label>
                            <input type="text" name="title" id="news-title" value="<?= htmlspecialchars($item['title']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Заголовок новости">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Картинка</label>
                            <div class="flex flex-col sm:flex-row gap-5">
                                <div class="flex-1">
                                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100 file:cursor-pointer transition-colors">
                                    <p class="text-xs text-ink-500 mt-1.5">JPG, PNG, GIF, WebP. Или укажите URL ниже.</p>
                                </div>
                                <div class="sm:w-36 shrink-0">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="" class="w-full h-24 object-cover rounded-xl border border-cream-200 bg-white shadow-inner">
                                    <?php else: ?>
                                        <div class="w-full h-24 rounded-xl border-2 border-dashed border-cream-200 bg-cream-50 flex items-center justify-center text-ink-400 text-xs">Нет картинки</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <input type="text" name="image_url" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>" class="mt-3 w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm transition-all outline-none" placeholder="URL картинки (или загрузите файл выше)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Краткое описание</label>
                            <textarea name="excerpt" rows="2" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"><?= htmlspecialchars($item['excerpt']) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Содержание</label>
                            <textarea name="content" rows="8" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"><?= htmlspecialchars($item['content']) ?></textarea>
                            <p class="text-xs text-ink-500 mt-1.5">HTML поддерживается</p>
                        </div>
                        <div>
                            <label class="flex items-center gap-2.5 cursor-pointer px-4 py-2.5 rounded-xl border border-cream-200 bg-white hover:bg-cream-50 transition-colors">
                                <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 rounded text-sage-600 focus:ring-sage-500 focus:ring-2">
                                <span class="text-sm font-medium text-ink-700">Опубликован</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Сохранить
                        </button>
                        <?php if ($editId): ?>
                            <button type="submit" name="delete" value="1" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all" onclick="return confirm('Удалить новость?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Удалить
                            </button>
                        <?php endif; ?>
                        <a href="<?= ADMIN_URL ?>/news" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список новостей</span>
            <a href="<?= ADMIN_URL ?>/news/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить новость
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Дата</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Заголовок</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700 w-16">Картинка</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Просмотры"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Просмотры
                            </span>
                        </th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-ink-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    <p>Новостей пока нет</p>
                                    <a href="<?= ADMIN_URL ?>/news/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить новость
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $n): ?>
                        <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                            <td class="px-6 py-3 text-sm text-ink-600"><?= htmlspecialchars($n['date']) ?></td>
                            <td class="px-6 py-3">
                                <span class="font-medium text-ink-800"><?= htmlspecialchars($n['title']) ?></span>
                                <?php if (!($n['is_active'] ?? 1)): ?><span class="ml-2 px-2 py-0.5 text-xs bg-ink-100 text-ink-600 rounded">Черновик</span><?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <?php if (!empty($n['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($n['image_url']) ?>" alt="" class="w-12 h-12 object-cover rounded-lg border border-cream-200">
                                <?php else: ?>
                                    <span class="w-12 h-12 inline-flex items-center justify-center rounded-lg bg-cream-100 text-ink-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sage-50 text-sage-700 text-sm font-medium" title="Просмотров">
                                    <svg class="w-4 h-4 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <?= (int)($n['views'] ?? 0) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= ADMIN_URL ?>/news/<?= $n['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                                <form method="post" action="<?= ADMIN_URL ?>/news/<?= $n['id'] ?>" class="inline" onsubmit="return confirm('Удалить новость?')">
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
        <a href="<?= BASE_URL ?>/novosti" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Посмотреть на сайте
        </a>
    </div>
</div>

<script>
(function() {
    const titleInput = document.getElementById('news-title');
    const slugInput = document.getElementById('news-slug');
    const cyrillicToLatin = {
        'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'e','ж':'zh','з':'z',
        'и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r',
        'с':'s','т':'t','у':'u','ф':'f','х':'kh','ц':'ts','ч':'ch','ш':'sh','щ':'sch',
        'ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya'
    };
    function slugify(text) {
        var s = String(text).toLowerCase().trim().split('').map(function(c) {
            return cyrillicToLatin[c] || c;
        }).join('').replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
        return s || 'news-' + Date.now();
    }
    let slugManuallyEdited = false;
    if (titleInput && slugInput) {
        slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
        titleInput.addEventListener('input', function() {
            if (!slugManuallyEdited) slugInput.value = slugify(titleInput.value);
        });
        if (!slugInput.value && titleInput.value) slugInput.value = slugify(titleInput.value);
    }
})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
