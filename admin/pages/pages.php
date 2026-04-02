<?php
$adminTitle = 'Страницы';
$action = 'pages';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$formError = '';
$subAction = $segments[1] ?? 'list';
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    if ($deleteId > 0) {
        try {
            $stmt = $db->prepare('SELECT slug FROM pages WHERE id = ?');
            $stmt->execute([$deleteId]);
            $row = $stmt->fetch();
            if ($row) {
                $slug = $row['slug'];
                $db->prepare('DELETE FROM pages WHERE id = ?')->execute([$deleteId]);
                $htmlFile = Page::getContentFilePath($slug);
                $phpFile = Page::getTemplateFilePath($slug);
                if (file_exists($htmlFile)) @unlink($htmlFile);
                if (file_exists($phpFile)) @unlink($phpFile);
            }
            header('Location: ' . ADMIN_URL . '/pages');
            exit;
        } catch (PDOException $e) {
            $formError = 'Ошибка удаления: ' . $e->getMessage();
        }
    }
}

// Обработка форм
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postSlug = trim($_POST['slug'] ?? '');
    $postTitle = trim($_POST['title'] ?? '');
    $postContent = $_POST['content'] ?? '';
    $postMeta = trim($_POST['meta_description'] ?? '');
    $postActive = isset($_POST['is_active']) ? 1 : 0;

    if ($postSlug && $postTitle && !isset($_POST['delete_id'])) {
        try {
            if ($editId) {
                $oldSlug = $db->prepare('SELECT slug FROM pages WHERE id = ?');
                $oldSlug->execute([$editId]);
                $oldSlug = $oldSlug->fetchColumn();
                $stmt = $db->prepare('UPDATE pages SET slug=?, title=?, content=?, meta_description=?, is_active=? WHERE id=?');
                $stmt->execute([$postSlug, $postTitle, $postContent, $postMeta, $postActive, $editId]);
                if ($oldSlug && $oldSlug !== $postSlug) {
                    @unlink(Page::getContentFilePath($oldSlug));
                }
            } else {
                $stmt = $db->prepare('INSERT INTO pages (slug, title, content, meta_description, is_active) VALUES (?,?,?,?,?)');
                $stmt->execute([$postSlug, $postTitle, $postContent, $postMeta, $postActive]);
            }
            Page::saveContentToFile($postSlug, $postContent);
            header('Location: ' . ADMIN_URL . '/pages');
            exit;
        } catch (PDOException $e) {
            $formError = 'Ошибка сохранения: ' . $e->getMessage();
        }
    }
}

$page = null;
$isNew = ($subAction === 'new');
$loadSlug = trim($_GET['load_slug'] ?? '');

if ($editId) {
    $stmt = $db->prepare('SELECT * FROM pages WHERE id = ?');
    $stmt->execute([$editId]);
    $page = $stmt->fetch();
    if ($page) {
        // Контент всегда берём из content/pages/{slug}.html (файл приоритетнее БД)
        $slug = trim($page['slug'] ?? '');
        $fileContent = $slug ? Page::getContentFromFile($slug) : null;
        if ($fileContent === null && $slug) {
            $altSlug = str_replace('_', '-', $slug);
            if ($altSlug !== $slug) $fileContent = Page::getContentFromFile($altSlug);
        }
        $page['content'] = ($fileContent !== null && $fileContent !== '') ? $fileContent : ($page['content'] ?? '');
    }
} elseif ($isNew && $loadSlug) {
    // Новая страница: загрузить контент из существующего файла content/pages
    $fileContent = Page::getContentFromFile($loadSlug);
    if ($fileContent !== null) {
        $page = [
            'slug' => $loadSlug,
            'title' => '',
            'content' => $fileContent,
            'meta_description' => '',
            'is_active' => 1
        ];
    } else {
        $formError = 'Файл content/pages/' . htmlspecialchars($loadSlug) . '.html не найден.';
    }
}

$pages = $db->query('SELECT * FROM pages ORDER BY slug')->fetchAll();
$showForm = $page || $isNew || !empty($formError);
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Страницы</h1>
                <p class="text-ink-500 text-sm mt-0.5">Добавление, редактирование и удаление страниц. Контент сохраняется в HTML-файлы.</p>
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
    <?php $page = $page ?? ['slug' => $postSlug ?? $loadSlug ?? '', 'title' => $postTitle ?? '', 'content' => $postContent ?? '', 'meta_description' => $postMeta ?? '', 'is_active' => $postActive ?? 1]; ?>
    <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать страницу' : 'Новая страница' ?></h2>
        </div>
        <div class="p-6">
            <?php if (!empty($formError)): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <form method="post">
                <div class="space-y-6">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Заголовок</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($page['title']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="О колледже">
                        </div>
                        <div class="flex flex-col">
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">URL (slug)</label>
                            <input type="text" name="slug" value="<?= htmlspecialchars($page['slug']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 font-mono text-sm transition-all outline-none" placeholder="o-kolledzhe">
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-medium text-ink-700">Контент</label>
                            <div class="flex rounded-xl border border-cream-200 overflow-hidden shadow-sm bg-white">
                                <button type="button" id="btn-mode-visual" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-sage-100 text-sage-700 border-r border-cream-200 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Визуальный
                                </button>
                                <button type="button" id="btn-mode-code" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-ink-500 hover:bg-cream-100 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    Код (HTML)
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-ink-500 mb-2">content/pages/<span class="font-mono text-sage-600 bg-sage-50 px-1.5 py-0.5 rounded" id="content-file-slug"><?= htmlspecialchars($page['slug'] ?? 'slug') ?>.html</span> — контент из файла
                        <button type="button" id="btn-load-from-file" class="ml-2 inline-flex items-center gap-1.5 px-2.5 py-1 text-sage-600 hover:text-sage-700 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Загрузить из файла
                        </button>
                        </p>
                        <?php if ($isNew): ?>
                        <div class="mb-4 p-3 bg-cream-50 rounded-xl border border-cream-200 flex flex-wrap items-center gap-3">
                            <span class="text-sm text-ink-600">Загрузить из файла:</span>
                            <form method="get" action="<?= ADMIN_URL ?>/pages/new" class="inline-flex items-center gap-2">
                                <input type="text" name="load_slug" value="<?= htmlspecialchars($loadSlug ?? '') ?>" placeholder="o-kolledzhe" class="px-3 py-1.5 border border-cream-200 rounded-lg text-sm font-mono w-48 focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 outline-none">
                                <button type="submit" class="px-3 py-1.5 bg-sage-100 text-sage-700 text-sm font-medium rounded-lg hover:bg-sage-200 transition-colors">Загрузить</button>
                            </form>
                            <span class="text-xs text-ink-500">content/pages/<span class="font-mono">slug</span>.html</span>
                        </div>
                        <?php endif; ?>
                        <div id="editor-visual-wrap" class="border border-cream-200 rounded-xl overflow-hidden shadow-inner bg-white [&_.ql-toolbar]:border-cream-200 [&_.ql-toolbar]:bg-cream-50 [&_.ql-container]:border-cream-200 [&_.ql-editor]:rounded-b-xl">
                            <div id="editor-container" class="min-h-[360px] [&_.ql-editor]:min-h-[300px] [&_.ql-editor]:text-ink-800 [&_.ql-editor]:text-base"></div>
                        </div>
                        <div id="editor-code-wrap" class="hidden rounded-xl overflow-hidden border border-ink-300/40 shadow-lg">
                            <div class="px-4 py-2.5 flex items-center gap-2 border-b border-white/10 bg-[#1e1e1e]">
                                <svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                <span class="text-xs font-medium text-[#94a3b8]">HTML</span>
                            </div>
                            <textarea id="page-content-code"><?= htmlspecialchars($page['content']) ?></textarea>
                        </div>
                        <textarea id="page-content" name="content" class="hidden"><?= htmlspecialchars($page['content']) ?></textarea>
                    </div>
                    <div class="flex flex-wrap items-end gap-6 pt-2">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Meta description</label>
                            <input type="text" name="meta_description" value="<?= htmlspecialchars($page['meta_description']) ?>" class="w-full max-w-md px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Для поисковиков">
                        </div>
                        <label class="flex items-center gap-2.5 cursor-pointer px-4 py-2.5 rounded-xl border border-cream-200 bg-white hover:bg-cream-50 transition-colors">
                            <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded text-sage-600 focus:ring-sage-500 focus:ring-2" <?= ($page['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span class="text-sm font-medium text-ink-700">Опубликовано</span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <a href="<?= ADMIN_URL ?>/pages" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
            <?php if ($editId): ?>
            <form method="post" class="mt-8 pt-6 border-t border-cream-200" onsubmit="return confirm(<?= json_encode('Удалить страницу «' . ($page['title'] ?? '') . '»?', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>);">
                <input type="hidden" name="delete_id" value="<?= $editId ?>">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Удалить страницу
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список страниц</span>
            <a href="<?= ADMIN_URL ?>/pages/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить страницу
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Заголовок</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700 hidden sm:table-cell">URL</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Статус</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pages)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-ink-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p>Страниц пока нет</p>
                                    <a href="<?= ADMIN_URL ?>/pages/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить страницу
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pages as $p): ?>
                        <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <a href="<?= ADMIN_URL ?>/pages/<?= $p['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($p['title']) ?></a>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-ink-500 hidden sm:table-cell">/<?= htmlspecialchars($p['slug']) ?></td>
                            <td class="px-6 py-3">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg <?= $p['is_active'] ? 'bg-sage-100 text-sage-700 ring-1 ring-sage-200/50' : 'bg-cream-200 text-ink-500' ?>"><?= $p['is_active'] ? 'Опубликовано' : 'Черновик' ?></span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= ADMIN_URL ?>/pages/<?= $p['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                                <form method="post" class="inline" onsubmit="return confirm(<?= json_encode('Удалить «' . $p['title'] . '»?', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>);">
                                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
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

<?php if (!empty($showForm)): ?>
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/lib/codemirror.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/theme/darcula.css" rel="stylesheet">
<style>
#editor-container .ql-toolbar { border-radius: 12px 12px 0 0; }
#editor-container .ql-container { border-radius: 0 0 12px 12px; }
#editor-container .ql-editor { font-family: 'DM Sans', system-ui, sans-serif; min-height: 300px; }
#editor-container .ql-editor h1 { font-size: 1.5rem; font-weight: 800; margin: 1rem 0; color: #1e293b; }
#editor-container .ql-editor h2 { font-size: 1.25rem; font-weight: 700; margin: 0.75rem 0; color: #334155; }
#editor-container .ql-editor h3 { font-size: 1.125rem; font-weight: 700; margin: 0.5rem 0; color: #475569; }
#editor-container .ql-editor p { margin-bottom: 0.75rem; line-height: 1.65; }
#editor-container .ql-editor ul, #editor-container .ql-editor ol { margin: 0.5rem 0 0.75rem 1.5rem; }
#editor-container .ql-editor a { color: #4a6d4a; text-decoration: underline; }
#editor-container .ql-editor a:hover { color: #3d5c3d; }
#editor-visual-wrap, #editor-code-wrap { transition: opacity 0.2s ease; }
#btn-mode-visual, #btn-mode-code { transition: background-color 0.15s, color 0.15s; }
#editor-code-wrap { min-height: 360px; }
#editor-code-wrap .CodeMirror { height: 360px !important; min-height: 360px; border-radius: 0 0 12px 12px; font-size: 14px; line-height: 1.6; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; border: none; background: #2b2b2b !important; }
#editor-code-wrap .CodeMirror-cursor { border-left-color: #fff !important; }
#editor-code-wrap .CodeMirror-gutters { background: #252526; border-right: 1px solid #333; }
#editor-code-wrap .CodeMirror-linenumber { color: #858585; padding: 0 8px 0 4px; min-width: 2.5em; }
#editor-code-wrap .CodeMirror-scroll { background: #2b2b2b; }
#editor-code-wrap .CodeMirror-vscrollbar::-webkit-scrollbar { width: 10px; }
#editor-code-wrap .CodeMirror-vscrollbar::-webkit-scrollbar-track { background: #252526; }
#editor-code-wrap .CodeMirror-vscrollbar::-webkit-scrollbar-thumb { background: #555; border-radius: 5px; }
#editor-code-wrap .CodeMirror-vscrollbar::-webkit-scrollbar-thumb:hover { background: #666; }
#editor-code-wrap .CodeMirror-hscrollbar::-webkit-scrollbar { height: 8px; }
#editor-code-wrap .CodeMirror-hscrollbar::-webkit-scrollbar-track { background: #252526; }
#editor-code-wrap .CodeMirror-hscrollbar::-webkit-scrollbar-thumb { background: #555; border-radius: 4px; }
#editor-code-wrap .CodeMirror, #editor-code-wrap .CodeMirror-scroll { scrollbar-color: #555 #252526; }
#editor-code-wrap .CodeMirror-gutters { border-right-color: #333 !important; }
#editor-code-wrap .CodeMirror-vscrollbar, #editor-code-wrap .CodeMirror-hscrollbar { background: #252526 !important; }
</style>
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/lib/codemirror.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/mode/xml/xml.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/mode/javascript/javascript.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/mode/css/css.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.16/mode/htmlmixed/htmlmixed.js"></script>
<script>
(function() {
    const form = document.querySelector('form');
    const textarea = document.getElementById('page-content');
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.querySelector('input[name="slug"]');
    if (!textarea || !form) return;

    // Автогенерация slug из заголовка (транслитерация кириллицы)
    const cyrillicToLatin = {
        'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'e','ж':'zh','з':'z',
        'и':'i','й':'y','к':'k','л':'l','м':'m','н':'n','о':'o','п':'p','р':'r',
        'с':'s','т':'t','у':'u','ф':'f','х':'kh','ц':'ts','ч':'ch','ш':'sh','щ':'sch',
        'ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya'
    };
    function slugify(text) {
        return String(text).toLowerCase().trim().split('').map(function(c) {
            return cyrillicToLatin[c] || c;
        }).join('').replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
    let slugManuallyEdited = false;
    if (titleInput && slugInput) {
        slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
        titleInput.addEventListener('input', function() {
            if (!slugManuallyEdited) slugInput.value = slugify(titleInput.value);
        });
    }

    const quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Введите текст...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                [{ 'color': [] }, { 'background': [] }],
                ['clean']
            ]
        }
    });
    quill.root.innerHTML = textarea.value;
    quill.root.style.fontFamily = "'DM Sans', system-ui, sans-serif";
    quill.root.style.fontSize = '16px';
    quill.root.style.lineHeight = '1.6';

    const visualWrap = document.getElementById('editor-visual-wrap');
    const codeWrap = document.getElementById('editor-code-wrap');
    const codeTextarea = document.getElementById('page-content-code');
    const btnVisual = document.getElementById('btn-mode-visual');
    const btnCode = document.getElementById('btn-mode-code');

    let codeMirror = null;
    if (codeTextarea && typeof CodeMirror !== 'undefined') {
        codeMirror = CodeMirror.fromTextArea(codeTextarea, {
            mode: 'htmlmixed',
            theme: 'darcula',
            lineNumbers: true,
            indentUnit: 4,
            indentWithTabs: false,
            tabSize: 4,
            lineWrapping: true,
            matchBrackets: true,
            extraKeys: {
                'Tab': function(cm) { cm.replaceSelection('    ', 'end'); }
            }
        });
    }

    function setMode(mode) {
        const isVisual = mode === 'visual';
        visualWrap.classList.toggle('hidden', !isVisual);
        codeWrap.classList.toggle('hidden', isVisual);
        btnVisual.classList.toggle('bg-sage-100', isVisual);
        btnVisual.classList.toggle('text-sage-700', isVisual);
        btnVisual.classList.toggle('bg-white', !isVisual);
        btnVisual.classList.toggle('text-ink-500', !isVisual);
        btnCode.classList.toggle('bg-sage-100', !isVisual);
        btnCode.classList.toggle('text-sage-700', !isVisual);
        btnCode.classList.toggle('bg-white', isVisual);
        btnCode.classList.toggle('text-ink-500', isVisual);
        if (codeMirror && !isVisual) {
            setTimeout(function() { codeMirror.refresh(); }, 50);
        }
    }
    setMode('visual');

    btnVisual.addEventListener('click', function() {
        var codeVal = codeMirror ? codeMirror.getValue() : (codeTextarea ? codeTextarea.value : '');
        quill.root.innerHTML = codeVal;
        setMode('visual');
    });
    btnCode.addEventListener('click', function() {
        var html = quill.root.innerHTML;
        var raw = textarea.value;
        var cmVal = codeMirror ? codeMirror.getValue() : (codeTextarea ? codeTextarea.value : '');
        var best = [raw, html, cmVal].reduce(function(a, b) { return a.length > b.length ? a : b; });
        if (codeMirror) codeMirror.setValue(best);
        else if (codeTextarea) codeTextarea.value = html;
        setMode('code');
        if (codeMirror) setTimeout(function() { codeMirror.refresh(); }, 50);
    });

    form.addEventListener('submit', function() {
        var codeVal = codeMirror ? codeMirror.getValue() : (codeTextarea ? codeTextarea.value : '');
        textarea.value = visualWrap.classList.contains('hidden') ? codeVal : quill.root.innerHTML;
    });

    var contentFileSlug = document.getElementById('content-file-slug');
    var btnLoadFromFile = document.getElementById('btn-load-from-file');
    if (slugInput && contentFileSlug) slugInput.addEventListener('input', function() { contentFileSlug.textContent = slugInput.value ? slugInput.value + '.html' : 'slug.html'; });
    if (btnLoadFromFile && slugInput) {
        btnLoadFromFile.addEventListener('click', function() {
            var slug = slugInput.value.trim();
            if (!slug) { alert('Введите URL (slug) страницы'); return; }
            btnLoadFromFile.disabled = true;
            btnLoadFromFile.textContent = 'Загрузка...';
            fetch('<?= ADMIN_URL ?>/ajax/get-page-content.php?slug=' + encodeURIComponent(slug))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) { alert(data.error + (data.path ? '\nПуть: ' + data.path : '')); return; }
                    var c = data.content || '';
                    textarea.value = c;
                    if (codeMirror) { codeMirror.setValue(c); codeMirror.refresh(); } else if (codeTextarea) codeTextarea.value = c;
                    quill.root.innerHTML = c;
                    setMode('code');
                })
                .catch(function() { alert('Ошибка загрузки'); })
                .finally(function() { btnLoadFromFile.disabled = false; btnLoadFromFile.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> Загрузить из файла'; });
        });
    }
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
