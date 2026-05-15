<?php
/**
 * Медиацентр — видео колледжа
 */
$pageTitle = 'Видео — ' . SITE_NAME;
$metaDescription = 'Видеоматериалы КВКИ: мероприятия, учебные будни, студенческие проекты и яркие моменты жизни колледжа.';
$breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'Медиацентр', 'slug' => ''], ['title' => 'Видео', 'slug' => 'video']];

require_once ROOT_PATH . '/classes/VideoEmbed.php';

$videos = [];
$categories = [];
$usedCategories = [];
if ($db = Database::tryGetInstance()) {
    try {
        $rows = $db->query('SELECT title, excerpt, video_url, thumbnail_url, `date`, category, duration FROM videos WHERE is_active = 1 ORDER BY sort_order, `date` DESC, id DESC')->fetchAll();
        foreach ($rows as $row) {
            $thumb = $row['thumbnail_url'] ?: VideoEmbed::thumbnailUrl($row['video_url']);
            $embed = VideoEmbed::embedUrl($row['video_url']);
            $category = trim((string)($row['category'] ?? ''));
            if ($category !== '' && !in_array($category, $usedCategories, true)) {
                $usedCategories[] = $category;
            }
            $videos[] = [
                'title' => $row['title'],
                'excerpt' => $row['excerpt'] ?? '',
                'video_url' => $row['video_url'],
                'embed_url' => $embed,
                'thumbnail' => $thumb,
                'date' => $row['date'],
                'category' => $category,
                'duration' => trim((string)($row['duration'] ?? '')),
            ];
        }
        $catalogCategories = $db->query('SELECT name FROM video_categories ORDER BY sort_order, name')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($catalogCategories as $name) {
            if ($name !== '' && in_array($name, $usedCategories, true) && !in_array($name, $categories, true)) {
                $categories[] = $name;
            }
        }
        foreach ($usedCategories as $name) {
            if (!in_array($name, $categories, true)) {
                $categories[] = $name;
            }
        }
    } catch (PDOException $e) {
    }
}

require ROOT_PATH . '/templates/header.php';
?>

<main class="flex-1">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
        <div class="relative overflow-hidden rounded-2xl bg-white border border-cream-200 shadow-[0_10px_24px_rgba(15,23,42,0.08)] p-8 lg:p-10 mb-10">
            <div class="relative z-10">
                <h1 class="text-3xl lg:text-4xl font-extrabold text-ink-800 tracking-tight mb-3">Видео колледжа</h1>
                <p class="text-ink-600 text-lg max-w-2xl leading-relaxed">
                    Погрузитесь в жизнь нашего колледжа: мероприятия, учебные будни, студенческие проекты и яркие моменты.
                </p>
            </div>
            <div class="absolute top-0 right-0 w-72 h-72 bg-sage-400/5 rounded-full -translate-y-1/2 translate-x-1/2" aria-hidden="true"></div>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="flex flex-wrap gap-3 mb-8" data-video-filters>
            <button type="button" data-filter="all" class="px-5 py-2.5 bg-ink-800 text-white rounded-2xl text-sm font-medium">Все видео</button>
            <?php foreach ($categories as $category): ?>
            <button type="button" data-filter="<?= htmlspecialchars($category, ENT_QUOTES) ?>" class="px-5 py-2.5 border border-cream-200 hover:border-sage-300 rounded-2xl text-sm font-medium transition-colors"><?= htmlspecialchars($category) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <section class="mb-16">
            <div class="flex items-end justify-between mb-8">
                <h2 class="text-2xl lg:text-3xl font-bold text-ink-800 tracking-tight">Все видео</h2>
                <span class="text-sm text-ink-500"><?= count($videos) ?> <?= count($videos) === 1 ? 'ролик' : (count($videos) >= 2 && count($videos) <= 4 ? 'ролика' : 'роликов') ?></span>
            </div>

            <?php if (empty($videos)): ?>
            <div class="rounded-2xl border border-dashed border-cream-200 bg-white px-6 py-16 text-center">
                <p class="text-ink-600">Видеоматериалы скоро появятся.</p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-video-grid>
                <?php foreach ($videos as $video): ?>
                <article class="group bg-white border border-cream-200 rounded-2xl overflow-hidden hover:shadow-[0_20px_40px_rgba(15,23,42,0.1)] transition-all duration-300" data-category="<?= htmlspecialchars($video['category'], ENT_QUOTES) ?>">
                    <button type="button"
                            class="block w-full text-left"
                            data-video-open
                            data-video-title="<?= htmlspecialchars($video['title'], ENT_QUOTES) ?>"
                            data-video-embed="<?= htmlspecialchars($video['embed_url'] ?: $video['video_url'], ENT_QUOTES) ?>"
                            data-video-external="<?= $video['embed_url'] ? '0' : '1' ?>">
                        <div class="relative aspect-video bg-zinc-900">
                            <?php if ($video['thumbnail']): ?>
                            <img src="<?= htmlspecialchars($video['thumbnail']) ?>"
                                 alt="<?= htmlspecialchars($video['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-sage-700 to-ink-900"></div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-16 h-16 bg-white/95 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-xl ring-4 ring-white/30 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-ink-800 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 4.01V8" />
                                    </svg>
                                </div>
                            </div>
                            <?php if ($video['duration']): ?>
                            <div class="absolute bottom-3 right-3 bg-black/70 text-white text-xs font-mono px-2 py-1 rounded-lg"><?= htmlspecialchars($video['duration']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <?php if ($video['category'] || $video['date']): ?>
                            <div class="flex items-center gap-2 text-xs text-sage-600 mb-2">
                                <?php if ($video['category']): ?><span><?= htmlspecialchars($video['category']) ?></span><?php endif; ?>
                                <?php if ($video['category'] && $video['date']): ?><span class="w-1 h-1 bg-sage-400 rounded-full"></span><?php endif; ?>
                                <?php if ($video['date']): ?><span><?= htmlspecialchars($video['date']) ?></span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <h3 class="font-semibold text-lg leading-tight text-ink-800 mb-2 line-clamp-2"><?= htmlspecialchars($video['title']) ?></h3>
                            <?php if ($video['excerpt']): ?>
                            <p class="text-ink-600 text-sm line-clamp-2"><?= htmlspecialchars($video['excerpt']) ?></p>
                            <?php endif; ?>
                        </div>
                    </button>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<div id="video-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6" aria-hidden="true">
    <div class="absolute inset-0 bg-ink-900/70 backdrop-blur-sm" data-video-close></div>
    <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-cream-200">
            <h2 id="video-modal-title" class="text-lg font-semibold text-ink-800"></h2>
            <button type="button" class="p-2 rounded-xl text-ink-500 hover:bg-cream-100 hover:text-ink-800 transition-colors" data-video-close aria-label="Закрыть">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="aspect-video bg-black">
            <iframe id="video-modal-frame" class="w-full h-full" src="" title="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('video-modal');
    const frame = document.getElementById('video-modal-frame');
    const title = document.getElementById('video-modal-title');
    const filters = document.querySelector('[data-video-filters]');
    const cards = document.querySelectorAll('[data-video-grid] [data-category]');

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        frame.removeAttribute('src');
        frame.removeAttribute('title');
        title.textContent = '';
    }

    document.querySelectorAll('[data-video-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            const embed = button.getAttribute('data-video-embed') || '';
            const external = button.getAttribute('data-video-external') === '1';
            const videoTitle = button.getAttribute('data-video-title') || 'Видео';
            if (external) {
                window.open(embed, '_blank', 'noopener,noreferrer');
                return;
            }
            title.textContent = videoTitle;
            frame.setAttribute('title', videoTitle);
            frame.setAttribute('src', embed);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    document.querySelectorAll('[data-video-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    if (filters) {
        filters.addEventListener('click', function (event) {
            const button = event.target.closest('[data-filter]');
            if (!button) return;
            const value = button.getAttribute('data-filter');
            filters.querySelectorAll('[data-filter]').forEach(function (item) {
                const active = item === button;
                item.classList.toggle('bg-ink-800', active);
                item.classList.toggle('text-white', active);
                item.classList.toggle('border', !active);
                item.classList.toggle('border-cream-200', !active);
                item.classList.toggle('hover:border-sage-300', !active);
            });
            cards.forEach(function (card) {
                const category = card.getAttribute('data-category') || '';
                const visible = value === 'all' || category === value;
                card.classList.toggle('hidden', !visible);
            });
        });
    }
});
</script>

<?php require ROOT_PATH . '/templates/footer.php'; ?>
