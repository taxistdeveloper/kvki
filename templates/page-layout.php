<?php
/**
 * Шаблон внутренней страницы — объединяет контент с сайтом (header, footer, стили).
 * Используйте в content/pages/{slug}.php:
 *
 *   <?php $content = '<h1>Заголовок</h1><p class="page-date">17.03.2026</p><p>Текст...</p>'; ?>
 *   <?php require ROOT_PATH . '/templates/page-layout.php'; ?>
 */
$content = $content ?? '';
require ROOT_PATH . '/templates/header.php';
?>
<main class="flex-1 bg-[#f5f5f5]">
    <div class="max-w-screen-2xl mx-auto px-5 sm:px-8 lg:px-10 py-16 lg:py-24 min-w-0">
        <div class="min-w-0">
            <div class="page-article-card p-6 sm:p-8 lg:p-12 rounded-3xl bg-cream-50 border border-cream-200 mb-8 shadow-[0_4px_24px_rgba(41,46,52,0.08)] max-w-full min-w-0">
                <div class="page-article__content prose prose-lg max-w-none"><?= $content ?></div>
            </div>
        </div>
    </div>
</main>
<?php require ROOT_PATH . '/templates/footer.php'; ?>
