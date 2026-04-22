<?php
$adminTitle = 'Дашборд';
$action = 'dashboard';
require __DIR__ . '/../includes/header.php';

$db = Database::getInstance();
$counts = ['pages' => 0, 'announcements' => 0, 'news' => 0, 'slides' => 0, 'partners' => 0, 'instagram' => 0, 'menu' => 0, 'e_resource' => 0];
try {
    $counts['pages'] = $db->query('SELECT COUNT(*) FROM pages')->fetchColumn();
    $counts['announcements'] = $db->query('SELECT COUNT(*) FROM announcements')->fetchColumn();
    try { $counts['news'] = $db->query('SELECT COUNT(*) FROM news')->fetchColumn(); } catch (PDOException $e) {}
    $counts['slides'] = $db->query('SELECT COUNT(*) FROM hero_slides')->fetchColumn();
    $counts['partners'] = $db->query('SELECT COUNT(*) FROM partners')->fetchColumn();
    try { $counts['instagram'] = $db->query('SELECT COUNT(*) FROM instagram_posts')->fetchColumn(); } catch (PDOException $e) {}
    try { $counts['e_resource'] = $db->query('SELECT COUNT(*) FROM e_resource_apps')->fetchColumn(); } catch (PDOException $e) {}
    $menuItems = (new Menu())->getMenu();
    $counts['menu'] = count($menuItems);
} catch (PDOException $e) {
    $countsError = 'Выполните установку: <a href="' . ADMIN_URL . '/install.php" class="text-sage-600 underline hover:text-sage-700">admin/install.php</a>';
}

$dashboardCards = [
    ['key' => 'pages', 'label' => 'Страницы', 'href' => 'pages', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'sage'],
    ['key' => 'announcements', 'label' => 'Объявления', 'href' => 'announcements', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.712-10.758a1.76 1.76 0 013.417-.592V12m0 0h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'sage'],
    ['key' => 'news', 'label' => 'Новости', 'href' => 'news', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', 'color' => 'sage'],
    ['key' => 'slides', 'label' => 'Слайды', 'href' => 'slides', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'sage'],
    ['key' => 'partners', 'label' => 'Партнёры', 'href' => 'partners', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'sage'],
    ['key' => 'instagram', 'label' => 'Instagram', 'href' => 'instagram', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14', 'color' => 'sage'],
    ['key' => 'menu', 'label' => 'Пунктов меню', 'href' => 'menu', 'icon' => 'M4 6h16M4 12h16M4 18h16', 'color' => 'sage'],
    ['key' => 'e_resource', 'label' => 'e-Resource', 'href' => 'e-resource', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'sage'],
];
?>

<div class="max-w-6xl">
    <?php if (!empty($countsError)): ?>
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <?= $countsError ?>
    </div>
    <?php endif; ?>

    <div class="mb-8">
        <h2 class="text-xl font-semibold text-ink-800">Добро пожаловать</h2>
        <p class="text-ink-500 text-sm mt-1">Обзор контента и быстрый доступ к разделам</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($dashboardCards as $card): ?>
        <a href="<?= ADMIN_URL ?>/<?= $card['href'] ?>" class="group block min-h-[132px] p-6 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-4xl leading-none font-bold text-ink-800"><?= (int)$counts[$card['key']] ?></p>
                    <p class="text-lg font-semibold text-ink-700 mt-2"><?= htmlspecialchars($card['label']) ?></p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center flex-shrink-0 group-hover:bg-sage-600/15 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $card['icon'] ?>"/></svg>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
