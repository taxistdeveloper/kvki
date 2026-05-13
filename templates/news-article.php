<?php
/**
 * Шаблон статьи новости — красивый UI/UX
 */
$imgUrl = '';
if (!empty($newsItem['image_url'])) {
    $imgUrl = $newsItem['image_url'];
    if (!str_starts_with($imgUrl, 'http') && !str_starts_with($imgUrl, '//') && !str_starts_with($imgUrl, '/')) {
        $imgUrl = BASE_URL . '/' . ltrim($imgUrl, '/');
    }
}
$contentHtml = $newsItem['content'] ?: ($newsItem['excerpt'] ? nl2br(htmlspecialchars($newsItem['excerpt'])) : '');
$views = (int)($newsItem['views'] ?? 0);
?>
<div class="news-article">
    <!-- Кнопка назад -->
    <a href="<?= BASE_URL ?>/novosti" class="inline-flex items-center gap-2 text-sm font-medium text-sage-600 hover:text-sage-700 mb-8 group transition-colors">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        К списку новостей
    </a>

    <?php if ($imgUrl): ?>
    <!-- Hero-изображение -->
    <div class="relative rounded-3xl overflow-hidden mb-10 shadow-xl">
        <div class="aspect-video min-h-[220px] max-h-[440px] bg-cream-200">
            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($newsItem['title']) ?>" class="w-full h-full object-cover">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-ink-900/50 via-transparent to-transparent pointer-events-none"></div>
    </div>
    <?php endif; ?>

    <!-- Контент статьи -->
    <article class="max-w-3xl">
        <!-- Мета: дата и просмотры -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <time class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sage-50 text-sage-700 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= htmlspecialchars($newsItem['date']) ?>
            </time>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cream-200/80 text-ink-600 text-sm font-medium">
                <svg class="w-4 h-4 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <?= $views ?> просмотров
            </span>
        </div>

        <!-- Заголовок -->
        <h1 class="text-3xl lg:text-4xl font-extrabold text-ink-800 leading-tight mb-8 tracking-tight"><?= htmlspecialchars($newsItem['title']) ?></h1>

        <!-- Текст -->
        <div class="news-article__content prose prose-lg max-w-none text-ink-600">
            <?= $contentHtml ?>
        </div>

        <!-- Кнопка внизу -->
        <div class="mt-12 pt-8 border-t border-cream-200">
            <a href="<?= BASE_URL ?>/novosti" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sage-50 text-sage-700 font-semibold hover:bg-sage-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                Все новости
            </a>
        </div>
    </article>
</div>
