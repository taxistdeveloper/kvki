<?php
$adminTitle = 'Меню';
$action = 'menu';
require __DIR__ . '/../includes/header.php';

$menuObj = new Menu();
$menu = $menuObj->getMenu();

// Список страниц для выбора (как в WordPress)
$pages = [];
try {
    $db = Database::getInstance();
    $pages = $db->query('SELECT id, slug, title FROM pages WHERE is_active = 1 ORDER BY slug')->fetchAll();
} catch (PDOException $e) {}
// Системные страницы (не из БД): Главная, Новости, Объявления
$systemPages = [
    ['id' => 0, 'slug' => 'index', 'title' => 'Главная'],
    ['id' => 0, 'slug' => 'novosti', 'title' => 'Новости'],
    ['id' => 0, 'slug' => 'ob-yavleniya', 'title' => 'Объявления'],
];
$pages = array_merge($systemPages, $pages);

/**
 * Получить элемент по пути (например "0-1-2")
 */
function getItemByPath(array &$items, string $path): ?array
{
    $parts = array_filter(explode('-', $path), fn($p) => $p !== '');
    if (empty($parts)) return null;
    $idx = (int)array_shift($parts);
    if (!isset($items[$idx])) return null;
    $item = &$items[$idx];
    foreach ($parts as $p) {
        $idx = (int)$p;
        $item['children'] = $item['children'] ?? [];
        if (!isset($item['children'][$idx])) return null;
        $item = &$item['children'][$idx];
    }
    return $item;
}

/**
 * Добавить дочерний элемент по пути
 */
function addChildByPath(array &$items, string $path): void
{
    $parts = array_filter(explode('-', $path), fn($p) => $p !== '');
    if (empty($parts)) {
        $items[] = ['title' => 'Новый пункт'];
        return;
    }
    $ref = &$items;
    foreach ($parts as $p) {
        $idx = (int)$p;
        $ref[$idx]['children'] = $ref[$idx]['children'] ?? [];
        $ref = &$ref[$idx]['children'];
    }
    $ref[] = ['title' => 'Новый пункт'];
}

/**
 * Удалить элемент по пути
 */
function deleteByPath(array &$items, string $path): bool
{
    $parts = array_filter(explode('-', $path), fn($p) => $p !== '');
    if (empty($parts)) return false;
    $idx = (int)array_pop($parts);
    if (empty($parts)) {
        array_splice($items, $idx, 1);
        return true;
    }
    $ref = &$items;
    foreach ($parts as $p) {
        $i = (int)$p;
        $ref[$i]['children'] = $ref[$i]['children'] ?? [];
        $ref = &$ref[$i]['children'];
    }
    array_splice($ref, $idx, 1);
    return true;
}

/**
 * Переместить элемент вверх/вниз
 */
function moveByPath(array &$items, string $path, int $dir): bool
{
    $parts = array_filter(explode('-', $path), fn($p) => $p !== '');
    if (empty($parts)) return false;
    $idx = (int)array_pop($parts);
    $ref = empty($parts) ? $items : null;
    if (!empty($parts)) {
        $r = &$items;
        foreach ($parts as $p) {
            $i = (int)$p;
            $r[$i]['children'] = $r[$i]['children'] ?? [];
            $r = &$r[$i]['children'];
        }
        $ref = &$r;
    }
    $arr = &$ref;
    $newIdx = $idx + $dir;
    if ($newIdx < 0 || $newIdx >= count($arr)) return false;
    $tmp = $arr[$idx];
    $arr[$idx] = $arr[$newIdx];
    $arr[$newIdx] = $tmp;
    return true;
}

// Обработка GET-действий
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isset($_GET['add'])) {
        addChildByPath($menu, $_GET['add']);
        Menu::saveMenu($menu);
        header('Location: ' . ADMIN_URL . '/menu');
        exit;
    }
    if (isset($_GET['delete'])) {
        deleteByPath($menu, $_GET['delete']);
        Menu::saveMenu($menu);
        header('Location: ' . ADMIN_URL . '/menu');
        exit;
    }
    if (isset($_GET['move_up'])) {
        moveByPath($menu, $_GET['move_up'], -1);
        Menu::saveMenu($menu);
        header('Location: ' . ADMIN_URL . '/menu');
        exit;
    }
    if (isset($_GET['move_down'])) {
        moveByPath($menu, $_GET['move_down'], 1);
        Menu::saveMenu($menu);
        header('Location: ' . ADMIN_URL . '/menu');
        exit;
    }
}

// Обработка POST (сохранение)
$formError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['menu'] ?? [];
    $menu = buildMenuFromPost($posted);
    if (Menu::saveMenu($menu)) {
        header('Location: ' . ADMIN_URL . '/menu');
        exit;
    }
    $formError = 'Ошибка сохранения файла';
}

function buildMenuFromPost(array $data): array
{
    $result = [];
    ksort($data, SORT_NUMERIC);
    foreach ($data as $item) {
        if (!is_array($item)) continue;
        $title = trim($item['title'] ?? '');
        if ($title === '') continue;
        $node = ['title' => $title];
        $linkType = $item['link_type'] ?? 'auto';
        $slugVal = trim($item['slug'] ?? '') ?: trim($item['slug_manual'] ?? '');
        if ($linkType === 'page' && $slugVal !== '') {
            $node['slug'] = $slugVal;
        } elseif ($linkType === 'custom' && !empty(trim($item['url'] ?? ''))) {
            $node['url'] = trim($item['url']);
        }
        if (!empty($item['children']) && is_array($item['children'])) {
            $node['children'] = buildMenuFromPost($item['children']);
        }
        $result[] = $node;
    }
    return $result;
}

function renderMenuTreeRecursive(array $items, string $pathPrefix, int $level, array $pages): string
{
    $html = '';
    foreach ($items as $i => $item) {
        $path = $pathPrefix === '' ? (string)$i : $pathPrefix . '-' . $i;
        $title = htmlspecialchars($item['title'] ?? '');
        $children = $item['children'] ?? [];
        $hasSlug = !empty($item['slug']);
        $hasUrl = !empty($item['url']);
        $linkType = $hasSlug ? 'page' : ($hasUrl ? 'custom' : 'auto');
        $slug = htmlspecialchars($item['slug'] ?? '');
        $url = htmlspecialchars($item['url'] ?? '');
        $pathBase = str_replace('-', '][children][', $path);
        $slugInPages = in_array($slug, ['index', 'novosti', 'ob-yavleniya']) || in_array($slug, array_column($pages, 'slug'));

        $indent = $level * 20;
        $hasChildren = !empty($children);
        $html .= '<div class="menu-node group rounded-xl border border-cream-200/80 bg-white shadow-sm hover:shadow-md hover:border-sage-300/70 transition-all duration-200" style="margin-left: ' . $indent . 'px" data-path="' . htmlspecialchars($path) . '">';
        $html .= '<div class="p-3">';
        // Строка 1: drag handle + сворачивание + номер + название + действия
        $html .= '<div class="flex items-center gap-3">';
        $html .= '<span class="menu-drag-handle flex-shrink-0 w-7 h-7 rounded-lg bg-cream-100 text-ink-400 flex items-center justify-center cursor-grab active:cursor-grabbing hover:bg-cream-200 hover:text-ink-600 transition-colors" title="Перетащить">';
        $html .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8V6zm6 0h2v2h-2V6zm-6 6h2v2H8v-2zm6 0h2v2h-2v-2zm-6 6h2v2H8v-2zm6 0h2v2h-2v-2z"/></svg>';
        $html .= '</span>';
        if ($hasChildren) {
            $html .= '<button type="button" class="menu-toggle flex-shrink-0 w-7 h-7 rounded-lg bg-sage-100 text-sage-600 flex items-center justify-center hover:bg-sage-200 transition-colors" title="Свернуть/развернуть подпункты" aria-expanded="true">';
            $html .= '<svg class="menu-chevron w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            $html .= '</button>';
        } else {
            $html .= '<span class="flex-shrink-0 w-7 h-7 rounded-lg bg-cream-100 flex items-center justify-center text-xs text-ink-400">—</span>';
        }
        $html .= '<span class="menu-ordinal flex-shrink-0 w-7 h-7 rounded-lg bg-gradient-to-br from-sage-100 to-sage-50 text-sage-600 flex items-center justify-center text-xs font-bold shadow-sm">' . ($i + 1) . '</span>';
        $html .= '<input type="text" name="menu[' . $pathBase . '][title]" value="' . $title . '" placeholder="Название пункта меню (как будет отображаться)" class="flex-1 min-w-0 px-3 py-2 border border-cream-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 transition-shadow">';
        $html .= '<div class="flex items-center gap-0.5 shrink-0" role="toolbar" aria-label="Действия с пунктом">';
        $html .= '<a href="' . ADMIN_URL . '/menu?add=' . urlencode($path) . '" class="p-2 rounded-lg text-sage-600 hover:bg-sage-50 hover:text-sage-700 transition-colors" title="Добавить подпункт (вложенное меню)"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></a>';
        $html .= '<a href="' . ADMIN_URL . '/menu?move_up=' . urlencode($path) . '" class="p-2 rounded-lg text-ink-400 hover:bg-cream-100 hover:text-ink-600 transition-colors" title="Поднять пункт выше"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></a>';
        $html .= '<a href="' . ADMIN_URL . '/menu?move_down=' . urlencode($path) . '" class="p-2 rounded-lg text-ink-400 hover:bg-cream-100 hover:text-ink-600 transition-colors" title="Опустить пункт ниже"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></a>';
        $html .= '<a href="' . ADMIN_URL . '/menu?delete=' . urlencode($path) . '" class="p-2 rounded-lg text-red-400/80 hover:bg-red-50 hover:text-red-600 transition-colors" title="Удалить пункт" onclick="return confirm(\'Удалить пункт?\')"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></a>';
        $html .= '</div>';
        $html .= '</div>';
        // Строка 2: тип ссылки + поле
        $html .= '<div class="flex flex-wrap items-center gap-3 mt-2.5 ml-10 text-sm">';
        $html .= '<span class="text-ink-500 font-medium shrink-0">Ссылка:</span>';
        $html .= '<div class="flex items-center gap-3">';
        $html .= '<label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="menu[' . $pathBase . '][link_type]" value="auto" ' . ($linkType === 'auto' ? 'checked' : '') . ' class="text-sage-600 focus:ring-sage-500"> <span class="text-ink-600">Авто</span></label>';
        $html .= '<label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="menu[' . $pathBase . '][link_type]" value="page" ' . ($linkType === 'page' ? 'checked' : '') . ' class="text-sage-600 focus:ring-sage-500"> <span class="text-ink-600">Страница</span></label>';
        $html .= '<label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="menu[' . $pathBase . '][link_type]" value="custom" ' . ($linkType === 'custom' ? 'checked' : '') . ' class="text-sage-600 focus:ring-sage-500"> <span class="text-ink-600">URL</span></label>';
        $html .= '</div>';
        $html .= '<select name="menu[' . $pathBase . '][slug]" class="menu-page-select px-3 py-2 border border-cream-200 rounded-lg text-sm focus:ring-2 focus:ring-sage-400/30 focus:border-sage-400 min-w-[180px] transition-shadow" data-path="' . htmlspecialchars($path) . '" title="Выберите страницу из списка или «Вручную» для ввода slug">';
        $html .= '<option value="">— Выберите страницу —</option>';
        foreach ($pages as $p) {
            $sel = ($slug === $p['slug']) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($p['slug']) . '"' . $sel . '>' . htmlspecialchars($p['title']) . '</option>';
        }
        $html .= '<option value="__manual__"' . ($linkType === 'page' && $slug !== '' && !$slugInPages ? ' selected' : '') . '>— Вручную —</option>';
        $html .= '</select>';
        $html .= '<input type="text" name="menu[' . $pathBase . '][slug_manual]" value="' . ($slugInPages ? '' : $slug) . '" placeholder="например: o-kolledzhe" class="menu-slug-manual px-3 py-2 border border-cream-200 rounded-lg text-sm focus:ring-2 focus:ring-sage-400/30 w-40" style="display:' . ($linkType === 'page' && $slug !== '' && !$slugInPages ? 'inline-block' : 'none') . '" data-path="' . htmlspecialchars($path) . '" title="Введите slug страницы (латиница, дефисы)">';
        $html .= '<input type="text" name="menu[' . $pathBase . '][url]" value="' . $url . '" placeholder="https://... или /путь" class="menu-url-input px-3 py-2 border border-cream-200 rounded-lg text-sm focus:ring-2 focus:ring-sage-400/30 min-w-[200px] transition-shadow" data-path="' . htmlspecialchars($path) . '" title="Внешняя ссылка или внутренний путь">';
        $html .= '</div>';
        $html .= '</div>';
        if (!empty($children)) {
            $html .= '<div class="menu-children border-t border-cream-100 pt-2 pb-1 px-3 ml-10 space-y-2">';
            $html .= renderMenuTreeRecursive($children, $path, $level + 1, $pages);
            $html .= '</div>';
        } else {
            $html .= renderMenuTreeRecursive($children, $path, $level + 1, $pages);
        }
        $html .= '</div>';
    }
    return $html;
}

function renderMenuTree(array $items, array $pages): string
{
    return renderMenuTreeRecursive($items, '', 0, $pages);
}

?>

<div class="max-w-5xl">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-500 to-sage-600 flex items-center justify-center shadow-lg shadow-sage-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-ink-800 tracking-tight">Меню</h1>
                <p class="text-ink-500 text-sm mt-0.5">Настройка пунктов навигации. Не забудьте нажать «Сохранить».</p>
            </div>
        </div>
    </div>

    <?php if ($formError): ?>
        <div class="mb-5 py-3 px-4 bg-red-50 border border-red-100 text-red-700 rounded-xl flex items-center gap-3 text-sm shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= htmlspecialchars($formError) ?>
        </div>
    <?php endif; ?>

    <div class="mb-5 py-4 px-5 rounded-xl bg-gradient-to-r from-sage-50 to-sage-50/50 border border-sage-200/60 shadow-sm">
        <p class="text-sm font-semibold text-sage-800 mb-3">Как выбрать тип ссылки:</p>
        <div class="grid sm:grid-cols-3 gap-3 text-sm text-ink-600">
            <div class="flex items-start gap-2">
                <span class="w-2 h-2 rounded-full bg-sage-400 mt-1.5 shrink-0"></span>
                <div><strong class="text-sage-700">Авто</strong> — ссылка строится автоматически из структуры меню (для вложенных пунктов)</div>
            </div>
            <div class="flex items-start gap-2">
                <span class="w-2 h-2 rounded-full bg-sage-400 mt-1.5 shrink-0"></span>
                <div><strong class="text-sage-700">Страница</strong> — привязать к странице из раздела «Страницы» или ввести slug вручную</div>
            </div>
            <div class="flex items-start gap-2">
                <span class="w-2 h-2 rounded-full bg-sage-400 mt-1.5 shrink-0"></span>
                <div><strong class="text-sage-700">URL</strong> — любая ссылка: внешний сайт или внутренний путь (например /kontakty)</div>
            </div>
        </div>
    </div>

    <form method="post">
        <div class="rounded-[28px] border border-black/5 bg-white shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300 overflow-hidden">
            <div class="px-5 py-4 border-b border-cream-200 bg-gradient-to-r from-cream-50 to-white flex items-center justify-between flex-wrap gap-2">
                <div>
                    <span class="font-semibold text-ink-800 block">Пункты меню</span>
                    <span class="text-xs text-ink-500">⋮⋮ — перетащить, ▼ — свернуть/развернуть, стрелки — порядок, + — подпункт</span>
                </div>
                <a href="<?= ADMIN_URL ?>/menu?add=" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sage-600 text-white text-sm font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 hover:shadow-lg hover:shadow-sage-600/30 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Добавить пункт
                </a>
            </div>
            <div id="menu-sortable-root" class="p-4 space-y-3 bg-cream-50/30">
                <?= renderMenuTree($menu, $pages) ?>
                <?php if (empty($menu)): ?>
                    <div class="text-center py-16 px-6 rounded-xl border-2 border-dashed border-cream-200 bg-white">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-cream-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </div>
                        <p class="text-ink-500 font-medium mb-1">Меню пусто</p>
                        <p class="text-ink-400 text-sm mb-5">Добавьте первый пункт меню</p>
                        <a href="<?= ADMIN_URL ?>/menu?add=" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Добавить пункт
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sage-600 text-white font-medium rounded-xl hover:bg-sage-700 shadow-md shadow-sage-600/25 hover:shadow-lg transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Сохранить изменения
            </button>
            <a href="<?= BASE_URL ?>/" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 border border-cream-200 text-ink-600 rounded-xl hover:bg-cream-100 hover:border-cream-300 transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Посмотреть на сайте
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function() {
    // Drag and drop — переиндексация полей формы после перетаскивания
    function pathToBase(path) {
        return path ? path.replace(/-/g, '][children][') : '';
    }
    function updateNodePaths(container, pathPrefix) {
        var nodes = container.querySelectorAll(':scope > .menu-node');
        nodes.forEach(function(node, idx) {
            var newPath = pathPrefix === '' ? String(idx) : pathPrefix + '-' + idx;
            var oldPath = node.getAttribute('data-path');
            node.setAttribute('data-path', newPath);
            var ord = node.querySelector('.menu-ordinal');
            if (ord) ord.textContent = idx + 1;
            var oldBase = pathToBase(oldPath);
            var newBase = pathToBase(newPath);
            var oldPrefix = 'menu[' + oldBase + ']';
            var newPrefix = 'menu[' + newBase + ']';
            node.querySelectorAll('input, select, textarea').forEach(function(el) {
                if (el.name && el.name.indexOf(oldPrefix) === 0) {
                    el.name = newPrefix + el.name.slice(oldPrefix.length);
                }
            });
            var children = node.querySelector(':scope > .menu-children');
            if (children) {
                updateNodePaths(children, newPath);
            }
        });
    }
    function initSortable(el) {
        if (!el || el._sortable) return;
        new Sortable(el, {
            animation: 150,
            handle: '.menu-drag-handle',
            ghostClass: 'opacity-50',
            dragClass: 'ring-2 ring-sage-400',
            group: 'menu',
            onEnd: function(evt) {
                var containers = evt.from === evt.to ? [evt.from] : [evt.from, evt.to];
                containers.forEach(function(container) {
                    var parent = container.closest('.menu-node');
                    var pathPrefix = parent ? parent.getAttribute('data-path') : '';
                    updateNodePaths(container, pathPrefix);
                    container.querySelectorAll('.menu-children').forEach(initSortable);
                });
            }
        });
        el._sortable = true;
    }
    if (typeof Sortable !== 'undefined') {
        var root = document.getElementById('menu-sortable-root');
        if (root) {
            initSortable(root);
            root.querySelectorAll('.menu-children').forEach(initSortable);
        }
    }

    // Сворачивание/разворачивание подпунктов
    document.querySelectorAll('.menu-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var node = btn.closest('.menu-node');
            var children = node.querySelector('.menu-children');
            if (!children) return;
            var isExpanded = btn.getAttribute('aria-expanded') === 'true';
            if (isExpanded) {
                children.style.display = 'none';
                btn.querySelector('.menu-chevron').style.transform = 'rotate(-90deg)';
                btn.setAttribute('aria-expanded', 'false');
            } else {
                children.style.display = '';
                btn.querySelector('.menu-chevron').style.transform = '';
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    function updateLinkFields() {
        document.querySelectorAll('.menu-node').forEach(function(node) {
            const checked = node.querySelector('input[type="radio"]:checked');
            const linkType = checked ? checked.value : 'auto';
            const pageSelect = node.querySelector('.menu-page-select');
            const slugManual = node.querySelector('.menu-slug-manual');
            const urlInput = node.querySelector('.menu-url-input');
            if (!pageSelect || !slugManual || !urlInput) return;
            const isPage = linkType === 'page';
            const isCustom = linkType === 'custom';
            pageSelect.disabled = !isPage;
            pageSelect.classList.toggle('opacity-50', !isPage);
            pageSelect.classList.toggle('pointer-events-none', !isPage);
            slugManual.style.display = (isPage && pageSelect.value === '__manual__') ? 'inline-block' : 'none';
            slugManual.disabled = !isPage || pageSelect.value !== '__manual__';
            urlInput.disabled = !isCustom;
            urlInput.classList.toggle('opacity-50', !isCustom);
            urlInput.classList.toggle('pointer-events-none', !isCustom);
        });
    }
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('change', function(e) {
            if (e.target.type === 'radio' || e.target.classList.contains('menu-page-select')) {
                updateLinkFields();
            }
        });
        updateLinkFields();
    }
})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
