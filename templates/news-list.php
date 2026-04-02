<?php
/**
 * Шаблон списка новостей — карточки с превью
 */
$eyeSvg = '<svg class="w-4 h-4 text-sage-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
?>
<div class="news-list">
    <!-- Заголовок секции -->
    <div class="mb-12">
        <h1 class="text-3xl lg:text-4xl font-extrabold text-ink-800 mb-3">Новости</h1>
        <p class="text-lg text-ink-600 max-w-2xl">Актуальные события и важная информация из жизни колледжа</p>
    </div>

    <?php if (empty($listRows)): ?>
        <div class="py-20 text-center rounded-3xl bg-cream-50 border-2 border-dashed border-cream-200">
            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-cream-200 flex items-center justify-center">
                <svg class="w-10 h-10 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <p class="text-ink-600 font-medium">Новостей пока нет</p>
            <p class="text-ink-500 text-sm mt-1">Следите за обновлениями</p>
        </div>
    <?php else: ?>
        <div class="grid gap-6 sm:gap-8">
            <?php foreach ($listRows as $r): ?>
                <?php
                $url = BASE_URL . '/novosti/' . $r['slug'];
                $views = (int)($r['views'] ?? 0);
                $imgUrl = '';
                if (!empty($r['image_url'])) {
                    $imgUrl = $r['image_url'];
                    if (!str_starts_with($imgUrl, 'http') && !str_starts_with($imgUrl, '//') && !str_starts_with($imgUrl, '/')) {
                        $imgUrl = BASE_URL . '/' . ltrim($imgUrl, '/');
                    }
                }
                ?>
                <a href="<?= htmlspecialchars($url) ?>" class="group block rounded-2xl bg-white border border-cream-200 overflow-hidden hover:border-sage-400 hover:shadow-xl hover:shadow-sage-500/10 transition-all duration-300">
                    <div class="flex flex-col sm:flex-row">
                        <?php if ($imgUrl): ?>
                        <div class="sm:w-72 shrink-0 aspect-video sm:aspect-square overflow-hidden bg-cream-100">
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <?php endif; ?>
                        <div class="flex-1 p-6 lg:p-8 flex flex-col justify-center min-w-0">
                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                <time class="text-sm font-medium text-sage-600"><?= htmlspecialchars($r['date']) ?></time>
                                <span class="inline-flex items-center gap-1.5 text-sm text-ink-500"><?= $eyeSvg ?><span><?= $views ?></span></span>
                            </div>
                            <h2 class="text-xl font-bold text-ink-800 mb-2 group-hover:text-sage-700 transition-colors line-clamp-2"><?= htmlspecialchars($r['title']) ?></h2>
                            <?php if (!empty($r['excerpt'])): ?>
                            <p class="text-ink-600 text-sm leading-relaxed line-clamp-2"><?= htmlspecialchars($r['excerpt']) ?></p>
                            <?php endif; ?>
                            <span class="mt-4 inline-flex items-center gap-2 text-sage-600 font-semibold text-sm group-hover:gap-3 transition-all">
                                Читать далее
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
