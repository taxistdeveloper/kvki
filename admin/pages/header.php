<?php
$adminTitle = 'Header';
$action = 'header';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$formError = '';
$formSuccess = '';

// Проверка существования таблицы
$tableExists = false;
try {
    $db->query('SELECT 1 FROM header_settings LIMIT 1');
    $tableExists = true;
} catch (PDOException $e) {}

if (!$tableExists) {
    echo '<div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 mb-6">';
    echo 'Таблица header_settings не найдена. Выполните миграцию: <a href="' . BASE_URL . '/admin/migrate_header.php" class="underline">admin/migrate_header.php</a>';
    echo '</div>';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $keys = [
            'logo_abbr', 'logo_title', 'logo_subtitle',
            'phone_primary', 'phone_secondary', 'phone_home', 'whatsapp', 'telegram', 'youtube', 'instagram',
            'about_o_kolledzhe', 'about_rukovodstvo', 'about_istoriya',
            'baza_znaniy_url', 'top_bar_visible'
        ];
        try {
            $stmt = $db->prepare('INSERT INTO header_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
            foreach ($keys as $key) {
                $value = trim($_POST[$key] ?? '');
                if ($key === 'top_bar_visible') {
                    $value = isset($_POST['top_bar_visible']) ? '1' : '0';
                }
                $stmt->execute([$key, $value]);
            }
            // header_about_links — JSON из массива
            $aboutLinks = [];
            if (!empty($_POST['about_links'])) {
                foreach ((array)$_POST['about_links'] as $row) {
                    $title = trim($row['title'] ?? '');
                    $url = trim($row['url'] ?? '');
                    if ($title && $url) {
                        $aboutLinks[] = ['title' => $title, 'url' => $url];
                    }
                }
            }
            $stmt->execute(['header_about_links', json_encode($aboutLinks, JSON_UNESCAPED_UNICODE)]);
            $formSuccess = 'Настройки сохранены.';
        } catch (PDOException $e) {
            $formError = $e->getMessage();
        }
    }

    $settings = [];
    if ($tableExists) {
        $stmt = $db->query('SELECT `key`, `value` FROM header_settings');
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
    $defaults = [
        'logo_abbr' => 'КВКИ',
        'logo_title' => 'Карагандинский высший колледж',
        'logo_subtitle' => 'инжиниринга',
        'phone_primary' => '+7 (747) 094 10 00',
        'phone_secondary' => '+7 (700) 123 45 67',
        'phone_home' => '',
        'whatsapp' => '77470941000',
        'telegram' => 'https://t.me/kvki_college',
        'youtube' => '',
        'instagram' => '',
        'about_o_kolledzhe' => '/o-nas',
        'about_rukovodstvo' => '/o-nas/rukovodstvo',
        'about_istoriya' => '/o-nas/istoriya',
        'baza_znaniy_url' => '/baza-znaniy',
        'top_bar_visible' => '1',
        'header_about_links' => '[{"title":"О колледже","url":"/o-nas"},{"title":"Руководство","url":"/o-nas/rukovodstvo"},{"title":"История","url":"/o-nas/istoriya"}]',
    ];
    $s = array_merge($defaults, $settings);
    $aboutLinks = json_decode($s['header_about_links'] ?? '[]', true) ?: [];
    if (empty($aboutLinks)) {
        $aboutLinks = [
            ['title' => 'О колледже', 'url' => $s['about_o_kolledzhe'] ?? '/o-nas'],
            ['title' => 'Руководство', 'url' => $s['about_rukovodstvo'] ?? '/o-nas/rukovodstvo'],
            ['title' => 'История', 'url' => $s['about_istoriya'] ?? '/o-nas/istoriya'],
        ];
    }
    $pages = [];
    try {
        $pages = $db->query('SELECT id, slug, title FROM pages WHERE is_active = 1 ORDER BY title')->fetchAll();
    } catch (PDOException $e) {}
}
?>

<h1 class="text-2xl font-bold text-ink-800 mb-8">Настройки верхнего header</h1>

<?php if ($formSuccess): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800"><?= htmlspecialchars($formSuccess) ?></div>
<?php endif; ?>
<?php if ($formError): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"><?= htmlspecialchars($formError) ?></div>
<?php endif; ?>

<?php if ($tableExists): ?>
<form method="post" class="space-y-8">
    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Логотип</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Аббревиатура (в блоке)</label>
                <input type="text" name="logo_abbr" value="<?= htmlspecialchars($s['logo_abbr']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="КВКИ">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Название</label>
                <input type="text" name="logo_title" value="<?= htmlspecialchars($s['logo_title']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="Карагандинский высший колледж">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Подзаголовок</label>
                <input type="text" name="logo_subtitle" value="<?= htmlspecialchars($s['logo_subtitle']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="инжиниринга">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Контакты (верхняя панель)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Телефон основной</label>
                <input type="text" name="phone_primary" value="<?= htmlspecialchars($s['phone_primary']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="+7 (747) 094 10 00">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Телефон дополнительный</label>
                <input type="text" name="phone_secondary" value="<?= htmlspecialchars($s['phone_secondary']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="+7 (700) 123 45 67">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Домашний телефон</label>
                <input type="text" name="phone_home" value="<?= htmlspecialchars($s['phone_home']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="+7 (7212) 12-34-56">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">WhatsApp (номер без +)</label>
                <input type="text" name="whatsapp" value="<?= htmlspecialchars($s['whatsapp']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="77470941000">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Telegram (URL)</label>
                <input type="text" name="telegram" value="<?= htmlspecialchars($s['telegram']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="https://t.me/kvki_college">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">YouTube (URL)</label>
                <input type="text" name="youtube" value="<?= htmlspecialchars($s['youtube']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="https://youtube.com/@channel">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Instagram (URL)</label>
                <input type="text" name="instagram" value="<?= htmlspecialchars($s['instagram']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="https://instagram.com/username">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Ссылки «О компании» — выбор страниц</h2>
        <p class="text-sm text-ink-600 mb-4">Добавьте ссылки в выпадающее меню «О компании». Можно выбрать страницу из списка или ввести свой URL.</p>
        <div id="about-links-container" class="space-y-3">
            <?php foreach ($aboutLinks as $i => $link): ?>
            <div class="about-link-row flex gap-2 items-start">
                <input type="text" name="about_links[<?= $i ?>][title]" value="<?= htmlspecialchars($link['title']) ?>" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <select class="about-page-select px-4 py-2 border rounded-lg w-48" data-target="about_links_<?= $i ?>_url" title="Выбрать страницу">
                    <option value="">— Выбрать страницу —</option>
                    <?php foreach ($pages as $p): ?>
                    <option value="/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="about_links[<?= $i ?>][url]" value="<?= htmlspecialchars($link['url']) ?>" id="about_links_<?= $i ?>_url" placeholder="/o-nas или URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">
                <button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add-about-link" class="mt-3 px-4 py-2 border border-dashed border-cream-300 rounded-lg text-ink-600 hover:bg-cream-50">+ Добавить ссылку</button>
    </div>

    <div class="bg-white rounded-2xl border border-cream-200 p-8">
        <h2 class="text-lg font-semibold mb-6">Прочее</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">База знаний (URL)</label>
                <input type="text" name="baza_znaniy_url" value="<?= htmlspecialchars($s['baza_znaniy_url']) ?>" class="w-full px-4 py-2 border rounded-lg" placeholder="/baza-znaniy">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="top_bar_visible" id="top_bar_visible" value="1" <?= ($s['top_bar_visible'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded">
                <label for="top_bar_visible" class="text-sm font-medium">Показывать верхнюю панель (контакты, соцсети)</label>
            </div>
        </div>
    </div>

    <div>
        <button type="submit" class="px-6 py-2 bg-sage-600 text-white rounded-lg hover:bg-sage-700">Сохранить</button>
        <a href="<?= BASE_URL ?>/" target="_blank" class="ml-4 px-6 py-2 border rounded-lg hover:bg-cream-100 inline-block">Посмотреть на сайте</a>
    </div>
</form>
<script>
(function() {
    const container = document.getElementById('about-links-container');
    const addBtn = document.getElementById('add-about-link');
    if (!container || !addBtn) return;
    const pages = <?= json_encode(array_map(fn($p) => ['url' => '/' . $p['slug'], 'title' => $p['title']], $pages)) ?>;
    let nextIndex = container.querySelectorAll('.about-link-row').length;
    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'about-link-row flex gap-2 items-start';
        const pageOpts = pages.map(p => '<option value="' + p.url.replace(/"/g, '&quot;') + '">' + (p.title || '').replace(/</g, '&lt;') + '</option>').join('');
        row.innerHTML = '<input type="text" name="about_links[' + nextIndex + '][title]" placeholder="Название" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">' +
            '<select class="about-page-select px-4 py-2 border rounded-lg w-48" data-target="about_links_' + nextIndex + '_url" title="Выбрать страницу">' +
            '<option value="">— Выбрать страницу —</option>' + pageOpts + '</select>' +
            '<input type="text" name="about_links[' + nextIndex + '][url]" id="about_links_' + nextIndex + '_url" placeholder="/o-nas или URL" class="flex-1 min-w-0 px-4 py-2 border rounded-lg">' +
            '<button type="button" class="remove-link px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg" title="Удалить">✕</button>';
        container.appendChild(row);
        nextIndex++;
        bindRowEvents(row);
    });
    function bindRowEvents(row) {
        row.querySelector('.about-page-select')?.addEventListener('change', function() {
            const v = this.value;
            if (v) row.querySelector('input[name*="[url]"]').value = v;
        });
        row.querySelector('.remove-link')?.addEventListener('click', function() { row.remove(); });
    }
    container.querySelectorAll('.about-link-row').forEach(bindRowEvents);
    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('about-page-select') && e.target.value) {
            const target = document.getElementById(e.target.dataset.target);
            if (target) target.value = e.target.value;
        }
    });
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-link')) e.target.closest('.about-link-row')?.remove();
    });
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
