<?php

/**
 * Блок ленты Instagram (карточки + модальное окно).
 * Ожидает из index.php: $instagramProfileUrl, $instagramPosts, $instagramDemoPosts, $instagramItemsCount
 *
 * Опционально:
 * - $instagramSectionShellClass — классы внешнего <section>
 * - $instagramSectionHeading — заголовок H2
 * - $instagramSectionIntro — HTML первого абзаца под заголовком (без обёртки <p>)
 * - $instagramIncludeModal — показывать ли модалку (по умолчанию true)
 */
$instagramSectionShellClass = $instagramSectionShellClass ?? 'py-20 lg:py-24 bg-cream-200/50';
$instagramSectionHeading = $instagramSectionHeading ?? 'Новости';
if (!isset($instagramSectionIntro)) {
    $instagramSectionIntro = 'Подписывайтесь на нас <a href="' . htmlspecialchars($instagramProfileUrl) . '" target="_blank" rel="noopener noreferrer" class="text-sage-600 font-semibold hover:underline">@kvki.kz</a>';
}
$instagramIncludeModal = $instagramIncludeModal ?? true;
?>
<section class="<?= htmlspecialchars($instagramSectionShellClass) ?>">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="kvki-title-wrap">
            <h2 class="kvki-title text-3xl sm:text-4xl mb-3"><?= htmlspecialchars($instagramSectionHeading) ?></h2>
            <p class="kvki-subtitle text-base sm:text-lg mb-12"><?= $instagramSectionIntro ?></p>
        </div>
        <?php if (!empty($instagramPosts)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 instagram-embed-grid">
                <?php foreach ($instagramPosts as $idx => $post): ?>
                    <a
                        href="<?= htmlspecialchars($post['url']) ?>"
                        class="js-instagram-open js-instagram-item group block rounded-3xl overflow-hidden bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all"
                        data-post-type="embed"
                        data-post-url="<?= htmlspecialchars($post['url']) ?>"
                        data-post-image="<?= htmlspecialchars($post['image'] ?? '') ?>"
                        data-post-caption="<?= htmlspecialchars($post['caption'] ?? '') ?>"
                        <?= $idx >= 6 ? ' style="display:none;"' : '' ?>>
                        <div class="aspect-square bg-gradient-to-br from-pink-50 to-purple-50 flex items-center justify-center">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>" alt="" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            <?php else: ?>
                                <svg class="w-14 h-14 text-pink-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z" />
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 font-semibold text-sm mb-2" style="color: #253f50;">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z" />
                                </svg>
                                @kvki.kz
                            </div>
                            <?php if (!empty($post['caption'])): ?>
                                <p class="text-ink-600 text-sm leading-relaxed line-clamp-3"><?= htmlspecialchars($post['caption']) ?></p>
                            <?php else: ?>
                                <p class="text-sage-700 text-sm font-medium">Открыть пост</p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 instagram-embed-grid">
                <?php foreach ($instagramDemoPosts as $idx => $post): ?>
                    <a
                        href="<?= htmlspecialchars($post['url']) ?>"
                        class="js-instagram-open js-instagram-item group block rounded-3xl overflow-hidden bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all"
                        data-post-type="demo"
                        data-post-url="<?= htmlspecialchars($post['url']) ?>"
                        data-post-image="<?= htmlspecialchars($post['image'] ?? '') ?>"
                        data-post-caption="<?= htmlspecialchars($post['caption'] ?? '') ?>"
                        <?= $idx >= 6 ? ' style="display:none;"' : '' ?>>
                        <div class="aspect-square bg-cream-100">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>" alt="Демо публикация Instagram" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300">
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 font-semibold text-sm mb-2" style="color: #253f50;">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z" />
                                </svg>
                                @kvki.kz
                            </div>
                            <p class="text-ink-600 text-sm leading-relaxed"><?= htmlspecialchars($post['caption']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="mt-10">
            <?php if ($instagramItemsCount > 6): ?>
                <button type="button" id="instagram-show-more" class="inline-flex items-center text-sage-700 font-semibold hover:text-sage-800">
                    Посмотреть еще
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <a id="instagram-view-all-link" href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sage-700 font-semibold hover:text-sage-800" style="display:none;">
                    Посмотреть все
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sage-700 font-semibold hover:text-sage-800">
                    Посмотреть все
                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if ($instagramIncludeModal): ?>
    <div id="instagram-post-modal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-black/70" data-instagram-modal-close></div>
        <div class="relative w-full h-full p-4 sm:p-6 lg:p-10 overflow-y-auto">
            <div class="max-w-4xl mx-auto bg-white rounded-3xl overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-black/10">
                    <h3 class="text-lg sm:text-xl font-bold text-ink-800">Публикации</h3>
                    <button type="button" class="w-10 h-10 rounded-xl border border-black/10 hover:bg-black/5 text-ink-600 flex items-center justify-center" data-instagram-modal-close aria-label="Закрыть">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="instagram-modal-content" class="p-5 sm:p-6"></div>
                <div class="px-5 sm:px-6 pb-6 flex justify-end">
                    <a id="instagram-modal-link" href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white" style="background-color: #253f50;">
                        Открыть в Instagram
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>