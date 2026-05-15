<?php
$adminTitle = 'Антикоррупционный комплекс';
$action = 'anticor';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';
$uploadDir = dirname(__DIR__, 2) . '/storage/anticor';

function anticorPublicUrl(string $filePath): string
{
    $filePath = trim($filePath);
    if ($filePath === '') {
        return '';
    }
    if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
        return $filePath;
    }

    return BASE_URL . '/' . ltrim(str_replace('\\', '/', $filePath), '/');
}

function anticorDeleteStoredFile(string $filePath, string $uploadDir): void
{
    $filePath = trim($filePath);
    if ($filePath === '' || str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
        return;
    }

    $normalized = str_replace('\\', '/', ltrim($filePath, '/'));
    if (!str_starts_with($normalized, 'storage/anticor/')) {
        return;
    }

    $absolutePath = dirname(__DIR__, 2) . '/' . $normalized;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

if (isset($_POST['delete']) && $editId) {
    $stmt = $db->prepare('SELECT file_path FROM anticor_documents WHERE id = ?');
    $stmt->execute([$editId]);
    $existing = $stmt->fetch();
    if ($existing) {
        anticorDeleteStoredFile((string)($existing['file_path'] ?? ''), $uploadDir);
    }
    $db->prepare('DELETE FROM anticor_documents WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/anticor');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $title = trim($_POST['title'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $filePath = trim($_POST['file_path'] ?? '');

    if ($editId) {
        $stmt = $db->prepare('SELECT file_path FROM anticor_documents WHERE id = ?');
        $stmt->execute([$editId]);
        $existing = $stmt->fetch();
        if ($existing) {
            $filePath = (string)($existing['file_path'] ?? '');
        }
    }

    if ($title === '') {
        $formError = 'Укажите название документа.';
    } else {
        try {
            if ($editId) {
                $db->prepare('UPDATE anticor_documents SET title=?, file_path=?, sort_order=?, is_active=? WHERE id=?')
                    ->execute([$title, $filePath, $sortOrder, $isActive, $editId]);
            } else {
                $db->prepare('INSERT INTO anticor_documents (title, file_path, sort_order, is_active) VALUES (?,?,?,?)')
                    ->execute([$title, '', $sortOrder, $isActive]);
            }

            $savedId = $editId ?: (int)$db->lastInsertId();

            if (!empty($_FILES['document']['name']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    throw new RuntimeException('Допустим только формат PDF.');
                }

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if ($editId && $filePath !== '') {
                    anticorDeleteStoredFile($filePath, $uploadDir);
                }

                $filename = 'anticor-' . $savedId . '.pdf';
                $filepath = $uploadDir . '/' . $filename;
                if (!move_uploaded_file($_FILES['document']['tmp_name'], $filepath)) {
                    throw new RuntimeException('Не удалось сохранить загруженный файл.');
                }

                $filePath = 'storage/anticor/' . $filename;
                $db->prepare('UPDATE anticor_documents SET file_path=? WHERE id=?')->execute([$filePath, $savedId]);
            } elseif (!$editId) {
                $db->prepare('DELETE FROM anticor_documents WHERE id = ?')->execute([$savedId]);
                throw new RuntimeException('Загрузите PDF-файл документа.');
            }

            header('Location: ' . ADMIN_URL . '/anticor');
            exit;
        } catch (Throwable $e) {
            $formError = $e->getMessage();
        }
    }
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM anticor_documents WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = [];
try {
    $items = $db->query('SELECT * FROM anticor_documents ORDER BY sort_order, id')->fetchAll();
} catch (PDOException $e) {
    $formError = $formError ?: 'Таблица anticor_documents не найдена. Выполните миграцию admin/migrate_anticor.php.';
}

$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;
?>

<div class="max-w-[1536px] animate-fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Антикоррупционный комплекс</h1>
                <p class="text-ink-500 text-sm mt-0.5">Документы для страницы <span class="font-mono text-sage-700">/antikorruptsionnyy-kompleks</span>.</p>
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
    <?php $item = $item ?? ['title' => '', 'file_path' => '', 'sort_order' => 0, 'is_active' => 1]; ?>
    <div class="mb-8 rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать документ' : 'Новый документ' ?></h2>
        </div>
        <div class="p-6">
            <?php if (!empty($formError)): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <p class="text-sm text-ink-600 mb-5">Загрузите PDF-файл и укажите название для публичной страницы.</p>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="file_path" value="<?= htmlspecialchars($item['file_path'] ?? '') ?>">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Название</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Название документа">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Порядок</label>
                            <input type="number" name="sort_order" value="<?= (int)($item['sort_order'] ?? 0) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none">
                        </div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-ink-700">
                                <input type="checkbox" name="is_active" value="1" <?= !isset($item['is_active']) || (int)$item['is_active'] === 1 ? 'checked' : '' ?> class="rounded border-cream-300 text-sage-600 focus:ring-sage-400">
                                Показывать на сайте
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">PDF-файл</label>
                        <input type="file" name="document" accept="application/pdf,.pdf" <?= $editId ? '' : 'required' ?> class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sage-50 file:text-sage-700 hover:file:bg-sage-100 file:cursor-pointer transition-colors">
                        <?php if (!empty($item['file_path'])): ?>
                        <p class="text-xs text-ink-500 mt-2">
                            Текущий файл:
                            <a href="<?= htmlspecialchars(anticorPublicUrl((string)$item['file_path'])) ?>" target="_blank" rel="noopener noreferrer" class="text-sage-700 hover:underline"><?= htmlspecialchars($item['file_path']) ?></a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <a href="<?= ADMIN_URL ?>/anticor" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
            <?php if ($editId): ?>
            <form method="post" class="mt-8 pt-6 border-t border-cream-200" onsubmit="return confirm('Удалить документ?');">
                <input type="hidden" name="delete" value="1">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Удалить документ
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список документов</span>
            <a href="<?= ADMIN_URL ?>/anticor/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить документ
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700 w-20">№</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Название</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Файл</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Статус</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-ink-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p>Документов пока нет</p>
                                    <a href="<?= ADMIN_URL ?>/anticor/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Добавить документ
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $doc): ?>
                        <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                            <td class="px-6 py-3 text-ink-500"><?= (int)$doc['sort_order'] ?></td>
                            <td class="px-6 py-3">
                                <a href="<?= ADMIN_URL ?>/anticor/<?= $doc['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($doc['title']) ?></a>
                            </td>
                            <td class="px-6 py-3 text-sm text-ink-600">
                                <?php if (!empty($doc['file_path'])): ?>
                                <a href="<?= htmlspecialchars(anticorPublicUrl((string)$doc['file_path'])) ?>" target="_blank" rel="noopener noreferrer" class="text-sage-700 hover:underline">Открыть PDF</a>
                                <?php else: ?>
                                <span class="text-ink-400">Файл не загружен</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3">
                                <?php if ((int)($doc['is_active'] ?? 0) === 1): ?>
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">Активен</span>
                                <?php else: ?>
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-cream-200 text-ink-500">Скрыт</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="<?= ADMIN_URL ?>/anticor/<?= $doc['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                                <form method="post" action="<?= ADMIN_URL ?>/anticor/<?= $doc['id'] ?>" class="inline" onsubmit="return confirm('Удалить документ?');">
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
        <a href="<?= BASE_URL ?>/antikorruptsionnyy-kompleks" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Посмотреть на сайте
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
