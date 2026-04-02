<?php
$adminTitle = 'Объявления';
$action = 'announcements';
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
    $db->prepare('DELETE FROM announcements WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/announcements');
    exit;
}

// Сохранение
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $date = trim($_POST['date'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    if (empty($url) && !empty($title)) {
        $slug = slugFromTitle($title);
        $url = '/ob-yavleniya/' . ($slug !== '' ? $slug : 'announcement-' . time());
    }
    $excerpt = trim($_POST['excerpt'] ?? '');
    $important = isset($_POST['is_important']) ? 1 : 0;

    if ($date && $title && $url) {
        try {
            if ($editId) {
                $db->prepare('UPDATE announcements SET `date`=?, title=?, url=?, excerpt=?, is_important=? WHERE id=?')
                    ->execute([$date, $title, $url, $excerpt, $important, $editId]);
            } else {
                $db->prepare('INSERT INTO announcements (`date`, title, url, excerpt, is_important) VALUES (?,?,?,?,?)')
                    ->execute([$date, $title, $url, $excerpt, $important]);
            }
            header('Location: ' . ADMIN_URL . '/announcements');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM announcements WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = $db->query('SELECT * FROM announcements ORDER BY sort_order, id')->fetchAll();
$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Объявления</h1>
                <p class="text-ink-500 text-sm mt-0.5">Добавление, редактирование и удаление объявлений.</p>
            </div>
        </div>
    </div>

<?php if ($showForm): ?>
    <?php $item = $item ?? ['date' => date('d.m.Y'), 'title' => '', 'url' => '', 'excerpt' => '', 'is_important' => 0]; ?>
    <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать объявление' : 'Новое объявление' ?></h2>
        </div>
        <div class="p-6">
            <?php if ($formError): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <form method="post">
                <div class="space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Дата</label>
                            <input type="text" name="date" value="<?= htmlspecialchars($item['date']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="12.03.2025">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">URL</label>
                            <input type="text" name="url" id="announcement-url" value="<?= htmlspecialchars($item['url']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Автоматически из заголовка">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Заголовок</label>
                        <input type="text" name="title" id="announcement-title" value="<?= htmlspecialchars($item['title']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Заголовок объявления">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Краткое описание</label>
                        <textarea name="excerpt" rows="3" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none"><?= htmlspecialchars($item['excerpt']) ?></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2.5 cursor-pointer px-4 py-2.5 rounded-xl border border-cream-200 bg-white hover:bg-cream-50 transition-colors">
                            <input type="checkbox" name="is_important" value="1" <?= $item['is_important'] ? 'checked' : '' ?> class="w-4 h-4 rounded text-sage-600 focus:ring-sage-500 focus:ring-2">
                            <span class="text-sm font-medium text-ink-700">Важное</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <?php if ($editId): ?>
                    <button type="submit" name="delete" value="1" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all" onclick="return confirm('Удалить объявление?');">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Удалить
                    </button>
                    <?php endif; ?>
                    <a href="<?= ADMIN_URL ?>/announcements" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script>
    (function() {
        const titleInput = document.getElementById('announcement-title');
        const urlInput = document.getElementById('announcement-url');
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
            return s || 'announcement';
        }
        let urlManuallyEdited = false;
        if (titleInput && urlInput) {
            urlInput.addEventListener('input', function() { urlManuallyEdited = true; });
            titleInput.addEventListener('input', function() {
                if (!urlManuallyEdited) urlInput.value = '/ob-yavleniya/' + slugify(titleInput.value);
            });
            if (!urlInput.value && titleInput.value) urlInput.value = '/ob-yavleniya/' + slugify(titleInput.value);
        }
    })();
    </script>
<?php endif; ?>

    <div class="rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список объявлений</span>
            <a href="<?= ADMIN_URL ?>/announcements/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить объявление
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Дата</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Заголовок</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $a): ?>
                    <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                        <td class="px-6 py-3 text-sm text-ink-600"><?= htmlspecialchars($a['date']) ?></td>
                        <td class="px-6 py-3">
                            <a href="<?= ADMIN_URL ?>/announcements/<?= $a['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($a['title']) ?></a>
                            <?php if ($a['is_important']): ?><span class="ml-2 px-2.5 py-1 text-xs font-semibold rounded-lg bg-sage-100 text-sage-700 ring-1 ring-sage-200/50">Важно</span><?php endif; ?>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="<?= ADMIN_URL ?>/announcements/<?= $a['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                            <form method="post" action="<?= ADMIN_URL ?>/announcements/<?= $a['id'] ?>" class="inline" onsubmit="return confirm('Удалить объявление?');">
                                <input type="hidden" name="delete" value="1">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
