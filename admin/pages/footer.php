<?php
$adminTitle = 'Footer';
$action = 'footer';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$formError = '';
$formSuccess = '';

// Проверка существования таблицы
$tableExists = false;
try {
    $db->query('SELECT 1 FROM footer_settings LIMIT 1');
    $tableExists = true;
} catch (PDOException $e) {}

if (!$tableExists) {
    echo '<div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 mb-6">';
    echo 'Таблица footer_settings не найдена. Выполните миграцию: <a href="' . BASE_URL . '/admin/migrate_footer.php" class="underline">admin/migrate_footer.php</a>';
    echo '</div>';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'footer_address', 'footer_email', 'footer_anticor_title', 'footer_hotline',
            'footer_map_embed_url', 'footer_2gis_url', 'footer_yandex_url'
        ];
        try {
            $stmt = $db->prepare('INSERT INTO footer_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
            foreach ($keys as $key) {
                $value = trim($_POST[$key] ?? '');
                $stmt->execute([$key, $value]);
            }
            // JSON-ссылки
            $aboutLinks = [];
            foreach ((array)($_POST['about_links'] ?? []) as $row) {
                $title = trim($row['title'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($title && $url) $aboutLinks[] = ['title' => $title, 'url' => $url];
            }
            $stmt->execute(['footer_about_links', json_encode($aboutLinks, JSON_UNESCAPED_UNICODE)]);

            $admissionLinks = [];
            foreach ((array)($_POST['admission_links'] ?? []) as $row) {
                $title = trim($row['title'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($title && $url) $admissionLinks[] = ['title' => $title, 'url' => $url];
            }
            $stmt->execute(['footer_admission_links', json_encode($admissionLinks, JSON_UNESCAPED_UNICODE)]);

            $anticorLinks = [];
            foreach ((array)($_POST['anticor_links'] ?? []) as $row) {
                $title = trim($row['title'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($title && $url) $anticorLinks[] = ['title' => $title, 'url' => $url];
            }
            $stmt->execute(['footer_anticor_links', json_encode($anticorLinks, JSON_UNESCAPED_UNICODE)]);

            $bottomLinks = [];
            foreach ((array)($_POST['bottom_links'] ?? []) as $row) {
                $title = trim($row['title'] ?? '');
                $url = trim($row['url'] ?? '');
                if ($title && $url) $bottomLinks[] = ['title' => $title, 'url' => $url];
            }
            $stmt->execute(['footer_bottom_links', json_encode($bottomLinks, JSON_UNESCAPED_UNICODE)]);

            $formSuccess = 'Настройки сохранены.';
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    }

    $settings = [];
    if ($tableExists) {
        $stmt = $db->query('SELECT `key`, `value` FROM footer_settings');
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    $defaults = [
        'footer_address' => 'ул. Кирпичная 8, г. Караганда, Казахстан',
        'footer_email' => 'info@kvki.kz',
        'footer_about_links' => '[]',
        'footer_admission_links' => '[]',
        'footer_anticor_title' => 'Антикоррупционный комплекс',
        'footer_anticor_links' => '[]',
        'footer_hotline' => '1424',
        'footer_map_embed_url' => '',
        'footer_2gis_url' => 'https://2gis.kz/karaganda/search/Кирпичная%208',
        'footer_yandex_url' => 'https://yandex.ru/maps/?pt=73.0876,49.8027&z=17&l=map',
        'footer_bottom_links' => '[]',
    ];
    $s = array_merge($defaults, $settings);
    $aboutLinks = json_decode($s['footer_about_links'] ?? '[]', true) ?: [];
    $admissionLinks = json_decode($s['footer_admission_links'] ?? '[]', true) ?: [];
    $anticorLinks = json_decode($s['footer_anticor_links'] ?? '[]', true) ?: [];
    $bottomLinks = json_decode($s['footer_bottom_links'] ?? '[]', true) ?: [];
    if (empty($aboutLinks)) {
        $aboutLinks = [['title' => 'О нас', 'url' => '/o-nas'], ['title' => 'История колледжа', 'url' => '/istoriya-kolledzha'], ['title' => 'Вакансии', 'url' => '/trudoustroystva'], ['title' => 'Новости', 'url' => '/novosti'], ['title' => 'Галерея', 'url' => '/o-nas']];
    }
    if (empty($admissionLinks)) {
        $admissionLinks = [['title' => 'Список документов', 'url' => '/kak-podat-dokumenty-na-postuplenie-v-kolledzh-onlayn'], ['title' => 'Специальности', 'url' => '/spetsialnosti'], ['title' => 'Правила приёма', 'url' => '/pravila-priema'], ['title' => 'Обратная связь', 'url' => '/o-nas']];
    }
    if (empty($anticorLinks)) {
        $anticorLinks = [['title' => 'Картограмма коррупции', 'url' => '/kartogramma-korruptsii'], ['title' => 'Контакты антикора', 'url' => '/antikorruptsionnyy-kompleks']];
    }
    if (empty($bottomLinks)) {
        $bottomLinks = [['title' => 'Условия использования', 'url' => '/o-nas'], ['title' => 'Политика конфиденциальности', 'url' => '/o-nas'], ['title' => 'Государственные символы', 'url' => '/o-nas/gos-uslugi/gosudarstvennye-simvoly']];
    }
    $pages = [];
    try {
        $pages = $db->query('SELECT id, slug, title FROM pages WHERE is_active = 1 ORDER BY title')->fetchAll();
    } catch (PDOException $e) {}
}
?>

<h1 class="text-2xl font-bold text-ink-800 mb-8">Настройки footer</h1>

<?php if ($formSuccess): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800"><?= htmlspecialchars($formSuccess) ?></div>
<?php endif; ?>
<?php if ($formError): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"><?= htmlspecialchars($formError) ?></div>
<?php endif; ?>

<?php if ($tableExists): ?>
<form method="post" class="space-y-8">
    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Контакты (верхняя колонка)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Адрес</label>
                <input type="text" name="footer_address" value="<?= htmlspecialchars($s['footer_address']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="ул. Кирпичная 8, г. Караганда, Казахстан">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="footer_email" value="<?= htmlspecialchars($s['footer_email']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="info@kvki.kz">
            </div>
            <p class="text-sm text-ink-500 md:col-span-2">Телефон и соцсети берутся из настроек Header.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Ссылки «О колледже»</h2>
        <div id="about-links-container" class="space-y-3">
            <?php foreach ($aboutLinks as $i => $link): ?>
            <div class="link-row flex gap-2 items-start">
                <input type="text" name="about_links[<?= $i ?>][title]" value="<?= htmlspecialchars($link['title']) ?>" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <select class="page-select px-4 py-2 border rounded-lg w-48" data-target="about_links_<?= $i ?>_url">
                    <option value="">— Выбрать страницу —</option>
                    <?php foreach ($pages as $p): ?>
                    <option value="/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="about_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link['url']) ?>" id="about_links_<?= $i ?>_url" placeholder="URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-about-link" class="mt-3 px-4 py-2 border border-dashed border-cream-300 rounded-lg text-ink-600 hover:bg-cream-50">+ Добавить ссылку</button>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Ссылки «Поступление»</h2>
        <div id="admission-links-container" class="space-y-3">
            <?php foreach ($admissionLinks as $i => $link): ?>
            <div class="link-row flex gap-2 items-start">
                <input type="text" name="admission_links[<?= $i ?>][title]" value="<?= htmlspecialchars($link['title']) ?>" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <select class="page-select px-4 py-2 border rounded-lg w-48" data-target="admission_links_<?= $i ?>_url">
                    <option value="">— Выбрать страницу —</option>
                    <?php foreach ($pages as $p): ?>
                    <option value="/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="admission_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link['url']) ?>" id="admission_links_<?= $i ?>_url" placeholder="URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-admission-link" class="mt-3 px-4 py-2 border border-dashed border-cream-300 rounded-lg text-ink-600 hover:bg-cream-50">+ Добавить ссылку</button>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Блок «Антикоррупционный комплекс»</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">Заголовок блока</label>
                <input type="text" name="footer_anticor_title" value="<?= htmlspecialchars($s['footer_anticor_title']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="Антикоррупционный комплекс">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Горячая линия (номер)</label>
                <input type="text" name="footer_hotline" value="<?= htmlspecialchars($s['footer_hotline']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="1424">
            </div>
        </div>
        <div id="anticor-links-container" class="space-y-3">
            <?php foreach ($anticorLinks as $i => $link): ?>
            <div class="link-row flex gap-2 items-start">
                <input type="text" name="anticor_links[<?= $i ?>][title]" value="<?= htmlspecialchars($link['title']) ?>" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <select class="page-select px-4 py-2 border rounded-lg w-48" data-target="anticor_links_<?= $i ?>_url">
                    <option value="">— Выбрать страницу —</option>
                    <?php foreach ($pages as $p): ?>
                    <option value="/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="anticor_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link['url']) ?>" id="anticor_links_<?= $i ?>_url" placeholder="URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-anticor-link" class="mt-3 px-4 py-2 border border-dashed border-cream-300 rounded-lg text-ink-600 hover:bg-cream-50">+ Добавить ссылку</button>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Карта и навигация</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">URL карты (iframe embed)</label>
                <input type="url" name="footer_map_embed_url" value="<?= htmlspecialchars($s['footer_map_embed_url']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="https://www.openstreetmap.org/export/embed.html?...">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ссылка 2ГИС</label>
                <input type="url" name="footer_2gis_url" value="<?= htmlspecialchars($s['footer_2gis_url']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="https://2gis.kz/...">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Ссылка Яндекс.Карты</label>
                <input type="url" name="footer_yandex_url" value="<?= htmlspecialchars($s['footer_yandex_url']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="https://yandex.ru/maps/...">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Нижняя полоса (юридические ссылки)</h2>
        <div id="bottom-links-container" class="space-y-3">
            <?php foreach ($bottomLinks as $i => $link): ?>
            <div class="link-row flex gap-2 items-start">
                <input type="text" name="bottom_links[<?= $i ?>][title]" value="<?= htmlspecialchars($link['title']) ?>" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <select class="page-select px-4 py-2 border rounded-lg w-48" data-target="bottom_links_<?= $i ?>_url">
                    <option value="">— Выбрать страницу —</option>
                    <?php foreach ($pages as $p): ?>
                    <option value="/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="bottom_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link['url']) ?>" id="bottom_links_<?= $i ?>_url" placeholder="URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-bottom-link" class="mt-3 px-4 py-2 border border-dashed border-cream-300 rounded-lg text-ink-600 hover:bg-cream-50">+ Добавить ссылку</button>
    </div>

    <div>
        <button type="submit" class="px-6 py-2 bg-sage-600 text-white rounded-lg hover:bg-sage-700">Сохранить</button>
        <a href="<?= BASE_URL ?>/" target="_blank" class="ml-4 px-6 py-2 border rounded-lg hover:bg-cream-100 inline-block">Посмотреть на сайте</a>
    </div>
</form>

<script>
(function() {
    const pages = <?= json_encode(array_map(fn($p) => ['url' => '/' . $p['slug'], 'title' => $p['title']], $pages)) ?>;

    function addLinkRow(containerId, addBtnId, namePrefix) {
        const container = document.getElementById(containerId);
        const addBtn = document.getElementById(addBtnId);
        if (!container || !addBtn) return;
        addBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'link-row flex gap-2 items-start';
            const pageOpts = pages.map(p => '<option value="' + (p.url || '').replace(/"/g, '&quot;') + '">' + (p.title || '').replace(/</g, '&lt;') + '</option>').join('');
            const n = container.querySelectorAll('.link-row').length;
            row.innerHTML = '<input type="text" name="' + namePrefix + '[' + n + '][title]" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">' +
                '<select class="page-select px-4 py-2 border rounded-lg w-48"><option value="">— Выбрать страницу —</option>' + pageOpts + '</select>' +
                '<input type="text" name="' + namePrefix + '[' + n + '][url]" placeholder="URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">' +
                '<button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>';
            container.appendChild(row);
            bindRowEvents(row);
        });
        function bindRowEvents(row) {
            const sel = row.querySelector('.page-select');
            const urlInput = row.querySelector('input[name*="[url]"]');
            sel?.addEventListener('change', function() {
                if (this.value && urlInput) urlInput.value = this.value;
            });
            row.querySelector('.remove-link')?.addEventListener('click', function() { row.remove(); });
        }
        container.querySelectorAll('.link-row').forEach(bindRowEvents);
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('page-select') && e.target.value) {
                const urlInput = e.target.closest('.link-row')?.querySelector('input[name*="[url]"]');
                if (urlInput) urlInput.value = e.target.value;
            }
        });
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-link')) e.target.closest('.link-row')?.remove();
        });
    }

    addLinkRow('about-links-container', 'add-about-link', 'about_links');
    addLinkRow('admission-links-container', 'add-admission-link', 'admission_links');
    addLinkRow('anticor-links-container', 'add-anticor-link', 'anticor_links');
    addLinkRow('bottom-links-container', 'add-bottom-link', 'bottom_links');
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
