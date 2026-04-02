<?php
/**
 * Шаблон внутренней страницы — объединяет контент с сайтом (header, footer, стили).
 * Используйте в content/pages/{slug}.php:
 *
 *   <?php $content = '<h1>Заголовок</h1><p class="page-date">17.03.2026</p><p>Текст...</p>'; ?>
 *   <?php require ROOT_PATH . '/templates/page-layout.php'; ?>
 */
$content = $content ?? '';
$slug = $router->getPageSlug();
$sidebarData = $menu->getSidebarItems($slug);
require ROOT_PATH . '/templates/header.php';
?>
<main class="flex-1 bg-[#f5f5f5]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
            <?php if ($sidebarData): ?>
                <?php require ROOT_PATH . '/templates/sidebar.php'; ?>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <div class="page-article-card p-8 rounded-xl bg-white mb-8 shadow-[0_4px_24px_rgba(41,46,52,0.18)]">
                    <div class="page-article__content prose prose-lg max-w-none"><?= $content ?></div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require ROOT_PATH . '/templates/footer.php'; ?>
