<?php
$adminTitle = 'e-Resource';
$action = 'e-resource';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();

$migrationError = '';
try {
    $db->query('SELECT 1 FROM e_resource_apps LIMIT 1');
} catch (PDOException $e) {
    $migrationError = 'Выполните миграцию: <a href="' . BASE_URL . '/admin/migrate_e_resource.php" class="text-sage-600 underline font-semibold hover:text-sage-700">admin/migrate_e_resource.php</a>';
}

$editId = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$formError = '';

if (isset($_POST['delete']) && $editId) {
    $db->prepare('DELETE FROM e_resource_apps WHERE id = ?')->execute([$editId]);
    header('Location: ' . ADMIN_URL . '/e-resource');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $tag = trim($_POST['tag'] ?? '');
    $status = $_POST['status'] ?? 'active';
    if (!in_array($status, ['active', 'dev', 'disabled'], true)) $status = 'active';

    if ($name) {
        try {
            if ($editId) {
                $db->prepare('UPDATE e_resource_apps SET name=?, `description`=?, url=?, tag=?, status=? WHERE id=?')
                    ->execute([$name, $description, $url ?: null, $tag ?: null, $status, $editId]);
            } else {
                $maxOrder = $db->query('SELECT COALESCE(MAX(sort_order), 0) FROM e_resource_apps')->fetchColumn();
                $db->prepare('INSERT INTO e_resource_apps (name, `description`, url, tag, status, sort_order) VALUES (?,?,?,?,?,?)')
                    ->execute([$name, $description, $url ?: null, $tag ?: null, $status, (int)$maxOrder + 1]);
            }
            header('Location: ' . ADMIN_URL . '/e-resource');
            exit;
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    } else {
        $formError = 'Название обязательно';
    }
}

// Сортировка (кнопки вверх/вниз)
if (isset($_POST['move_up']) && $editId) {
    $stmt = $db->prepare('SELECT sort_order FROM e_resource_apps WHERE id = ?');
    $stmt->execute([$editId]);
    $order = (int)$stmt->fetchColumn();
    $prev = $db->prepare('SELECT id, sort_order FROM e_resource_apps WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1');
    $prev->execute([$order]);
    $prevRow = $prev->fetch();
    if ($prevRow) {
        $db->prepare('UPDATE e_resource_apps SET sort_order = ? WHERE id = ?')->execute([$prevRow['sort_order'], $editId]);
        $db->prepare('UPDATE e_resource_apps SET sort_order = ? WHERE id = ?')->execute([$order, $prevRow['id']]);
    }
    header('Location: ' . ADMIN_URL . '/e-resource');
    exit;
}
if (isset($_POST['move_down']) && $editId) {
    $stmt = $db->prepare('SELECT sort_order FROM e_resource_apps WHERE id = ?');
    $stmt->execute([$editId]);
    $order = (int)$stmt->fetchColumn();
    $next = $db->prepare('SELECT id, sort_order FROM e_resource_apps WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1');
    $next->execute([$order]);
    $nextRow = $next->fetch();
    if ($nextRow) {
        $db->prepare('UPDATE e_resource_apps SET sort_order = ? WHERE id = ?')->execute([$nextRow['sort_order'], $editId]);
        $db->prepare('UPDATE e_resource_apps SET sort_order = ? WHERE id = ?')->execute([$order, $nextRow['id']]);
    }
    header('Location: ' . ADMIN_URL . '/e-resource');
    exit;
}

$item = null;
if ($editId) {
    $stmt = $db->prepare('SELECT * FROM e_resource_apps WHERE id = ?');
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
}

$items = [];
try {
    $items = $db->query('SELECT * FROM e_resource_apps ORDER BY sort_order, id')->fetchAll();
} catch (PDOException $e) {}

$showForm = $item || ($segments[1] ?? '') === 'new' || $formError;

$statusLabels = ['active' => 'Активно', 'dev' => 'В разработке', 'disabled' => 'Отключено'];
?>

<div class="max-w-[1536px] animate-fade-in">
    <?php if ($migrationError): ?>
    <div class="mb-5 py-3 px-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex items-center gap-3 text-sm shadow-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <?= $migrationError ?>
    </div>
    <?php endif; ?>
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">e-Resource</h1>
                <p class="text-ink-500 text-sm mt-0.5">Приложения на странице <a href="<?= BASE_URL ?>/e-resource" target="_blank" class="text-sage-600 hover:underline"><?= BASE_URL ?>/e-resource</a></p>
            </div>
        </div>
    </div>

<?php if ($showForm): ?>
    <?php $item = $item ?? ['name' => '', 'description' => '', 'url' => '', 'tag' => '', 'status' => 'active']; ?>
    <div class="mb-8 rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white">
            <h2 class="text-lg font-semibold text-ink-800"><?= $editId ? 'Редактировать приложение' : 'Новое приложение' ?></h2>
        </div>
        <div class="p-6">
            <?php if ($formError): ?><div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-3"><svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><?= htmlspecialchars($formError) ?></div><?php endif; ?>
            <form method="post">
                <div class="space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Название</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Besaspap.app">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Статус</label>
                            <select name="status" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none">
                                <?php foreach ($statusLabels as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($item['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700 mb-1.5">Описание</label>
                        <input type="text" name="description" value="<?= htmlspecialchars($item['description']) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="Система входа для Besasap">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">URL (пусто или # = «Скоро»)</label>
                            <input type="text" name="url" value="<?= htmlspecialchars($item['url']) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="https://... или #">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink-700 mb-1.5">Тег (версия или статус)</label>
                            <input type="text" name="tag" value="<?= htmlspecialchars($item['tag']) ?>" class="w-full px-4 py-2.5 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-all outline-none" placeholder="1.0 или В разработке">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Сохранить
                    </button>
                    <?php if ($editId): ?>
                    <button type="submit" name="move_up" value="1" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 font-medium transition-colors">↑ Вверх</button>
                    <button type="submit" name="move_down" value="1" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 font-medium transition-colors">↓ Вниз</button>
                    <button type="submit" name="delete" value="1" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-100 text-red-700 font-medium rounded-xl hover:bg-red-200 transition-all" onclick="return confirm('Удалить приложение?');">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Удалить
                    </button>
                    <?php endif; ?>
                    <a href="<?= ADMIN_URL ?>/e-resource" class="inline-flex items-center gap-2 px-5 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors font-medium">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

    <div class="rounded-2xl border border-cream-200 bg-white shadow-lg shadow-ink-900/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
            <span class="font-semibold text-ink-800">Список приложений</span>
            <a href="<?= ADMIN_URL ?>/e-resource/new" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить приложение
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-cream-50">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700 w-12">#</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Название</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Описание</th>
                        <th class="text-left px-6 py-3 font-semibold text-ink-700">Статус</th>
                        <th class="text-right px-6 py-3 font-semibold text-ink-700">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-ink-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-cream-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <p>Нет приложений</p>
                                <a href="<?= ADMIN_URL ?>/e-resource/new" class="inline-flex items-center gap-2 px-4 py-2 bg-sage-600 text-white rounded-xl hover:bg-sage-700 text-sm font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Добавить приложение
                                </a>
                                <p class="text-xs text-ink-500">Или <a href="<?= BASE_URL ?>/admin/migrate_e_resource.php" class="text-sage-600 hover:underline">выполните миграцию</a> для добавления по умолчанию.</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($items as $i => $a): ?>
                    <tr class="border-t border-cream-200 hover:bg-cream-50/50 transition-colors">
                        <td class="px-6 py-3 text-ink-500 text-sm"><?= (int)($a['sort_order'] ?? $i + 1) ?></td>
                        <td class="px-6 py-3">
                            <a href="<?= ADMIN_URL ?>/e-resource/<?= $a['id'] ?>" class="font-medium text-ink-800 hover:text-sage-600 transition-colors"><?= htmlspecialchars($a['name']) ?></a>
                        </td>
                        <td class="px-6 py-3 text-ink-600 text-sm"><?= htmlspecialchars($a['description']) ?></td>
                        <td class="px-6 py-3">
                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg <?= $a['status'] === 'active' ? 'bg-sage-100 text-sage-700 ring-1 ring-sage-200/50' : ($a['status'] === 'disabled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800') ?>"><?= $statusLabels[$a['status']] ?? $a['status'] ?></span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="<?= ADMIN_URL ?>/e-resource/<?= $a['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-2 text-sage-600 hover:bg-sage-50 rounded-lg text-sm font-medium transition-colors">Редактировать</a>
                            <form method="post" action="<?= ADMIN_URL ?>/e-resource/<?= $a['id'] ?>" class="inline" onsubmit="return confirm('Удалить приложение?');">
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
        <a href="<?= BASE_URL ?>/e-resource" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 transition-colors text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Посмотреть на сайте
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
