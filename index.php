<?php
require_once __DIR__ . '/config/config.php';

try {
    $menu = new Menu();
    $router = new Router($menu);
    $page = new Page();

    $slug = $router->getPageSlug();

    // Проверка объявлений: ob-yavleniya или ob-yavleniya/{slug}
    $announcementItem = null;
    $isAnnouncementList = ($slug === 'ob-yavleniya');
    $isAnnouncementSingle = (str_starts_with($slug, 'ob-yavleniya/') && substr_count($slug, '/') >= 1);
    if ($db = Database::tryGetInstance()) {
        if ($isAnnouncementSingle) {
            $annSlug = substr($slug, strlen('ob-yavleniya/'));
            $stmt = $db->prepare('SELECT * FROM announcements WHERE url = ? OR url = ?');
            $stmt->execute(['/ob-yavleniya/' . $annSlug, 'ob-yavleniya/' . $annSlug]);
            $announcementItem = $stmt->fetch();
        }
        // Обратная совместимость: объявления с URL без префикса (например /priem-dokumentov)
        if (!$announcementItem && strpos($slug, '/') === false && $slug !== 'ob-yavleniya') {
            $stmt = $db->prepare('SELECT * FROM announcements WHERE url = ? OR url = ?');
            $stmt->execute(['/' . $slug, $slug]);
            $announcementItem = $stmt->fetch();
            if ($announcementItem) $isAnnouncementSingle = true;
        }
    }

    // Проверка новостей: novosti или novosti/{slug}
    $newsItem = null;
    $isNewsList = ($slug === 'novosti');
    $isNewsSingle = (str_starts_with($slug, 'novosti/') && substr_count($slug, '/') >= 1);
    if ($db = Database::tryGetInstance()) {
        try {
            if ($isNewsSingle) {
                $newsSlug = substr($slug, strlen('novosti/'));
                $stmt = $db->prepare('SELECT * FROM news WHERE slug = ? AND (is_active = 1 OR is_active IS NULL)');
                $stmt->execute([$newsSlug]);
                $newsItem = $stmt->fetch();
            }
        } catch (PDOException $e) {}
    }

    // Проверка существования страницы: в меню, файл контента, запись в БД, объявление или новость
    $pageExists = ($slug === 'index')
        || $menu->isPathInMenu($slug)
        || (Page::getContentFromFile($slug) !== null)
        || ($page->findBySlug($slug) !== null)
        || $isAnnouncementList
        || ($announcementItem !== null)
        || $isNewsList
        || ($newsItem !== null);

    if (!$pageExists) {
        http_response_code(404);
        $errorCode = 404;
        $errorTitle = 'Страница не найдена';
        $errorMessage = 'Запрашиваемая страница не существует или была перемещена.';
        $errorDescription = 'Проверьте адрес или вернитесь на главную.';
        require __DIR__ . '/templates/error.php';
        return;
    }

    $pageData = $page->findBySlug($slug);
    $breadcrumbTitles = $router->getBreadcrumbFromPath();
    $lastBreadcrumb = end($breadcrumbTitles);

    // Объявления: свой заголовок и контент
    if ($announcementItem) {
        $pageTitle = $announcementItem['title'] . ' — ' . SITE_NAME;
        $metaDescription = $announcementItem['excerpt'] ?: SITE_DESCRIPTION;
        $breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'Объявления', 'slug' => 'ob-yavleniya'], ['title' => $announcementItem['title'], 'slug' => $slug]];
    } elseif ($isAnnouncementList) {
        $pageTitle = 'Объявления — ' . SITE_NAME;
        $metaDescription = 'Важная информация для студентов и абитуриентов. ' . SITE_DESCRIPTION;
        $breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'Объявления', 'slug' => 'ob-yavleniya']];
    } elseif ($newsItem) {
        $pageTitle = $newsItem['title'] . ' — ' . SITE_NAME;
        $metaDescription = $newsItem['excerpt'] ?: SITE_DESCRIPTION;
        $breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'Новости', 'slug' => 'novosti'], ['title' => $newsItem['title'], 'slug' => $slug]];
    } elseif ($isNewsList) {
        $pageTitle = 'Новости — ' . SITE_NAME;
        $metaDescription = 'Новости колледжа. ' . SITE_DESCRIPTION;
        $breadcrumbTitles = [['title' => 'Главная', 'slug' => ''], ['title' => 'Новости', 'slug' => 'novosti']];
    } else {
        $pageTitle = ($pageData['title'] ?? (is_array($lastBreadcrumb) ? $lastBreadcrumb['title'] : $lastBreadcrumb)) . ' — ' . SITE_NAME;
        $metaDescription = $pageData['meta_description'] ?? SITE_DESCRIPTION;
    }

    // PHP-шаблон (content/pages/{slug}.php) — сливается с сайтом через page-layout
    if ($slug !== 'index' && Page::hasTemplateFile($slug) && !$announcementItem && !$isAnnouncementList && !$newsItem && !$isNewsList) {
        $templateFile = Page::getTemplateFilePath($slug);
        require $templateFile;
        return;
    }

    $pageContent = $page->getContent($slug);
    if ($announcementItem) {
        if ($db = Database::tryGetInstance()) {
            $db->prepare('UPDATE announcements SET views = COALESCE(views, 0) + 1 WHERE id = ?')->execute([$announcementItem['id']]);
        }
        $views = (int)($announcementItem['views'] ?? 0) + 1;
        $pageContent = '<article class="announcement-single">'
            . '<div class="flex flex-wrap items-center gap-4 mb-4"><time class="text-sm text-sage-600 font-medium">' . htmlspecialchars($announcementItem['date']) . '</time><span class="text-sm text-ink-500">' . $views . ' просмотров</span></div>'
            . '<h1 class="text-2xl font-bold text-ink-800 mb-6">' . htmlspecialchars($announcementItem['title']) . '</h1>'
            . ($announcementItem['excerpt'] ? '<div class="prose prose-lg text-ink-600">' . nl2br(htmlspecialchars($announcementItem['excerpt'])) . '</div>' : '')
            . '</article>';
    } elseif ($newsItem) {
        if ($db = Database::tryGetInstance()) {
            try {
                $db->prepare('UPDATE news SET views = COALESCE(views, 0) + 1 WHERE id = ?')->execute([$newsItem['id']]);
            } catch (PDOException $e) {}
        }
        $newsItem['views'] = (int)($newsItem['views'] ?? 0) + 1;
        $pageContent = ''; // Рендерится через news-article.php
    } elseif ($isNewsList) {
        $db = Database::tryGetInstance();
        $listRows = [];
        if ($db) {
            try {
                $listRows = $db->query('SELECT `date`, title, slug, excerpt, image_url, COALESCE(views, 0) AS views FROM news WHERE is_active = 1 OR is_active IS NULL ORDER BY created_at DESC, id DESC')->fetchAll();
            } catch (PDOException $e) {}
        }
        $pageContent = ''; // Рендерится через news-list.php
    } elseif ($isAnnouncementList) {
        $db = Database::tryGetInstance();
        $listRows = $db ? $db->query('SELECT `date`, title, url, excerpt, is_important, COALESCE(views, 0) AS views FROM announcements ORDER BY sort_order, id')->fetchAll() : [];
        $listHtml = '';
        foreach ($listRows as $r) {
            $url = (str_starts_with($r['url'], 'http') || str_starts_with($r['url'], BASE_URL)) ? $r['url'] : (BASE_URL . (str_starts_with($r['url'], '/') ? $r['url'] : '/' . $r['url']));
            $views = (int)($r['views'] ?? 0);
            $listHtml .= '<a href="' . htmlspecialchars($url) . '" class="flex flex-col sm:flex-row sm:items-center gap-4 p-6 rounded-2xl bg-cream-50 border border-cream-200 hover:border-sage-400 hover:shadow-lg transition-all group mb-4">'
                . '<div class="flex flex-col gap-1 shrink-0"><time class="text-sm text-sage-600 font-medium">' . htmlspecialchars($r['date']) . '</time><span class="text-xs text-ink-500">' . $views . ' просмотров</span></div>'
                . '<div class="flex-1 min-w-0"><h3 class="font-bold text-ink-800 mb-1 group-hover:text-sage-700 flex items-center gap-2">'
                . ($r['is_important'] ? '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-lg bg-sage-600 text-white">Важно</span>' : '')
                . htmlspecialchars($r['title']) . '</h3><p class="text-ink-600 text-sm">' . htmlspecialchars($r['excerpt']) . '</p></div>'
                . '<svg class="w-5 h-5 text-sage-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>';
        }
        $pageContent = '<div class="space-y-4"><p class="text-ink-600 mb-8">Важная информация для студентов и абитуриентов</p>' . ($listHtml ?: '<p class="text-ink-600">Объявлений пока нет.</p>') . '</div>';
    }

    // Посты Instagram из админки (ссылки на посты для embed)
    $instagramProfileUrl = 'https://www.instagram.com/ktsk.kz';
    $instagramPosts = [];
    if ($db = Database::tryGetInstance()) {
        try {
            $rows = $db->query('SELECT post_url, caption FROM instagram_posts ORDER BY sort_order, id')->fetchAll();
            if (!empty($rows)) {
                $instagramPosts = array_map(fn($r) => ['url' => $r['post_url'], 'caption' => $r['caption'] ?? ''], $rows);
            }
        } catch (PDOException $e) {}
    }
    $announcementsDefault = [
        ['date' => '12.03.2025', 'title' => 'Приём документов на 2025–2026 учебный год', 'url' => BASE_URL . '/abiturientam/pravila-priema', 'excerpt' => 'Документы принимаются с 1 июня по 25 августа. Список необходимых документов и порядок подачи.', 'important' => true, 'views' => 0],
        ['date' => '11.03.2025', 'title' => 'Изменение расписания на 13 марта', 'url' => BASE_URL . '/o-nas', 'excerpt' => 'В связи с проведением олимпиады занятия 13 марта переносятся. Уточняйте расписание у кураторов.', 'views' => 0],
        ['date' => '08.03.2025', 'title' => 'Выходной день 8 марта', 'url' => BASE_URL . '/o-nas', 'excerpt' => 'Колледж не работает 8 марта. Занятия возобновляются 10 марта.', 'views' => 0],
    ];
    $announcements = $announcementsDefault;
    if ($db = Database::tryGetInstance()) {
        try {
            $rows = $db->query('SELECT `date`, title, url, excerpt, is_important, COALESCE(views, 0) AS views FROM announcements ORDER BY sort_order, id')->fetchAll();
            if (!empty($rows)) {
                $announcements = array_map(fn($r) => [
                    'date' => $r['date'],
                    'title' => $r['title'],
                    'url' => (str_starts_with($r['url'], 'http') || str_starts_with($r['url'], BASE_URL)) ? $r['url'] : (BASE_URL . (str_starts_with($r['url'], '/') ? $r['url'] : '/' . $r['url'])),
                    'excerpt' => $r['excerpt'],
                    'important' => (bool)$r['is_important'],
                    'views' => (int)($r['views'] ?? 0),
                ], $rows);
            }
        } catch (PDOException $e) {
        }
    }
    $specialties = [
        ['title' => 'Гражданское строительство', 'url' => BASE_URL . '/zhas-maman/obrazovatelnye-programmy/stroitelnye-spetsialnosti/grazhdanskoe-stroitelstvo'],
        ['title' => 'Архитектура и дизайн', 'url' => BASE_URL . '/zhas-maman/obrazovatelnye-programmy/arkhitektura-i-dizayn'],
        ['title' => 'Информационные технологии', 'url' => BASE_URL . '/o-nas/tsmk/informatsionnykh-tekhnologiy'],
        ['title' => 'Электротехника', 'url' => BASE_URL . '/zhas-maman/obrazovatelnye-programmy/tekhnicheskie-spetsialnosti/elektrotekhnika'],
    ];
    $stats = [
        ['value' => '50+', 'label' => 'лет опыта'],
        ['value' => '2000+', 'label' => 'студентов'],
        ['value' => '15+', 'label' => 'специальностей'],
        ['value' => '100+', 'label' => 'преподавателей'],
    ];
    // Логотипы партнёров: положите partner1, partner2, ... (jpg/png/webp/svg) в assets/images/partners/
    $partnersPath = ROOT_PATH . '/assets/images/partners';
    $partnersUrl = BASE_URL . '/assets/images/partners';
    $partnersStock = [
        'https://logo.clearbit.com/unesco.org',
        'https://logo.clearbit.com/unicef.org',
        'https://logo.clearbit.com/microsoft.com',
        'https://logo.clearbit.com/google.com',
    ];
    $partnerImage = function ($n) use ($partnersPath, $partnersUrl, $partnersStock) {
        foreach (['.jpg', '.jpeg', '.png', '.webp', '.svg'] as $ext) {
            if (file_exists($partnersPath . "/partner{$n}{$ext}")) {
                return $partnersUrl . "/partner{$n}{$ext}";
            }
        }
        return $partnersStock[$n - 1] ?? '';
    };
    $partnersDefault = [
        ['name' => 'Партнёр 1', 'url' => '#', 'image' => $partnerImage(1)],
        ['name' => 'Партнёр 2', 'url' => '#', 'image' => $partnerImage(2)],
        ['name' => 'Партнёр 3', 'url' => '#', 'image' => $partnerImage(3)],
        ['name' => 'Партнёр 4', 'url' => '#', 'image' => $partnerImage(4)],
    ];
    $partners = $partnersDefault;
    if ($db = Database::tryGetInstance()) {
        try {
            $rows = $db->query('SELECT name, url, image_url FROM partners ORDER BY sort_order, id')->fetchAll();
            if (!empty($rows)) {
                $partners = [];
                foreach ($rows as $i => $r) {
                    $img = $r['image_url'] ?: $partnerImage($i + 1);
                    $partners[] = ['name' => $r['name'], 'url' => $r['url'] ?: '#', 'image' => $img];
                }
            }
        } catch (PDOException $e) {
        }
    }
    // Фото для слайдера: положите slide1, slide2, slide3 (jpg/png/webp) в assets/images/hero/
    // Пока нет своих — используются стоковые фото (Unsplash)
    $heroImagesPath = ROOT_PATH . '/assets/images/hero';
    $heroImagesUrl = BASE_URL . '/assets/images/hero';
    $heroStockImages = [
        'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1920&q=80', // здание
        'https://images.unsplash.com/photo-1523050854058-ddf90803c5d4?auto=format&fit=crop&w=1920&q=80', // студенты
        'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1920&q=80', // архитектура
    ];
    $heroImage = function ($n) use ($heroImagesPath, $heroImagesUrl, $heroStockImages) {
        foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
            if (file_exists($heroImagesPath . "/slide{$n}{$ext}")) {
                return $heroImagesUrl . "/slide{$n}{$ext}";
            }
        }
        return $heroStockImages[$n - 1] ?? '';
    };
    $heroSlidesDefault = [
        ['title' => 'Карагандинский высший колледж инжиниринга', 'text' => 'Современное образовательное учреждение. Готовим специалистов в строительстве, архитектуре и дизайне.', 'image' => $heroImage(1)],
        ['title' => 'Образование в строительстве и дизайне', 'text' => 'Более 50 лет опыта. Практико-ориентированное обучение и востребованные специальности.', 'image' => $heroImage(2)],
        ['title' => 'Приём 2025 — подайте заявку', 'text' => 'Узнайте о правилах приёма, специальностях и станьте студентом КВКИ.', 'image' => $heroImage(3)],
    ];
    $heroSlides = $heroSlidesDefault;
    if ($db = Database::tryGetInstance()) {
        try {
            $rows = $db->query('SELECT title, `text`, image_url FROM hero_slides ORDER BY sort_order, id')->fetchAll();
            if (!empty($rows)) {
                $heroSlides = [];
                foreach ($rows as $i => $r) {
                    $img = $r['image_url'] ?: $heroImage($i + 1);
                    $heroSlides[] = ['title' => $r['title'], 'text' => $r['text'], 'image' => $img];
                }
            }
        } catch (PDOException $e) {
        }
    }

    require __DIR__ . '/templates/header.php';
?>

    <main class="flex-1">
        <?php if ($slug === 'index'): ?>
            <!-- HERO SLIDER -->
            <section class="hero-slider relative overflow-hidden">
                <div class="swiper hero-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($heroSlides as $slide): ?>
                            <div class="swiper-slide">
                                <div class="hero-slide-inner relative py-20 lg:py-28 min-h-[420px] lg:min-h-[480px] flex items-center <?= !empty($slide['image']) ? '' : 'bg-gradient-to-br from-cream-200 to-sage-500/20' ?>"
                                    <?php if (!empty($slide['image'])): ?>style="background-image: url('<?= htmlspecialchars($slide['image']) ?>'); background-size: cover; background-position: center;" <?php endif; ?>>
                                    <?php if (!empty($slide['image'])): ?><div class="absolute inset-0 bg-ink-800/50"></div><?php endif; ?>
                                    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full relative z-10">
                                        <div class="max-w-2xl">
                                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.1] mb-8 <?= !empty($slide['image']) ? 'text-white' : 'text-ink-800' ?>">
                                                <?= htmlspecialchars($slide['title']) ?>
                                            </h1>
                                            <p class="text-xl leading-relaxed mb-10 <?= !empty($slide['image']) ? 'text-cream-100' : 'text-ink-600' ?>">
                                                <?= htmlspecialchars($slide['text']) ?>
                                            </p>
                                            <div class="flex flex-wrap gap-4">
                                                <a href="<?= BASE_URL ?>/abiturientam" class="inline-flex items-center justify-center px-8 py-4 bg-sage-600 text-white font-semibold rounded-2xl hover:bg-sage-700 transition-colors">
                                                    Абитуриентам
                                                </a>
                                                <a href="<?= BASE_URL ?>/o-nas" class="inline-flex items-center justify-center px-8 py-4 <?= !empty($slide['image']) ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-cream-200 text-sage-700 hover:bg-cream-200/80' ?> font-semibold rounded-2xl transition-colors">
                                                    О колледже
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination hero-pagination"></div>
                    <div class="swiper-button-prev hero-prev"></div>
                    <div class="swiper-button-next hero-next"></div>
                </div>
            </section>

            <!-- СТАТИСТИКА -->
            <section class="py-16 bg-cream-200/50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-10">
                        <?php foreach ($stats as $stat): ?>
                            <div>
                                <div class="text-4xl font-extrabold text-sage-700 mb-1"><?= htmlspecialchars($stat['value']) ?></div>
                                <div class="text-ink-600"><?= htmlspecialchars($stat['label']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- НАПРАВЛЕНИЯ -->
            <section class="py-20 lg:py-24">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-ink-800 mb-4">Направления</h2>
                    <p class="text-ink-600 mb-12 max-w-xl">Выберите направление подготовки</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <a href="<?= BASE_URL ?>/zhas-maman/obrazovatelnye-programmy/stroitelnye-spetsialnosti" class="group p-6 rounded-2xl bg-cream-50 border border-cream-200 hover:border-sage-400 hover:shadow-lg transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-500/20 text-sage-700 flex items-center justify-center mb-5 group-hover:bg-sage-500/30 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Строительство</h3>
                            <p class="text-ink-600 text-sm">Гражданское, промышленное, дорожное строительство</p>
                        </a>
                        <a href="<?= BASE_URL ?>/zhas-maman/obrazovatelnye-programmy/arkhitektura-i-dizayn" class="group p-6 rounded-2xl bg-cream-50 border border-cream-200 hover:border-sage-400 hover:shadow-lg transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-500/20 text-sage-700 flex items-center justify-center mb-5 group-hover:bg-sage-500/30 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Архитектура и дизайн</h3>
                            <p class="text-ink-600 text-sm">Жилой, коммерческий, ландшафтный дизайн</p>
                        </a>
                        <a href="<?= BASE_URL ?>/zhas-maman/obrazovatelnye-programmy/tekhnicheskie-spetsialnosti" class="group p-6 rounded-2xl bg-cream-50 border border-cream-200 hover:border-sage-400 hover:shadow-lg transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-500/20 text-sage-700 flex items-center justify-center mb-5 group-hover:bg-sage-500/30 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Технические специальности</h3>
                            <p class="text-ink-600 text-sm">Электротехника, механика, автоматизация</p>
                        </a>
                        <a href="<?= BASE_URL ?>/studentam" class="group p-6 rounded-2xl bg-cream-50 border border-cream-200 hover:border-sage-400 hover:shadow-lg transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-500/20 text-sage-700 flex items-center justify-center mb-5 group-hover:bg-sage-500/30 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Студентам</h3>
                            <p class="text-ink-600 text-sm">SKILLS паспорт, кружки, факультативы</p>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ОБЪЯВЛЕНИЯ -->
            <section class="py-20 lg:py-24 bg-cream-200/50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-ink-800 mb-4">Объявления</h2>
                    <p class="text-ink-600 mb-12">Важная информация для студентов и абитуриентов</p>
                    <div class="space-y-4">
                        <?php foreach ($announcements as $item): ?>
                            <a href="<?= htmlspecialchars($item['url']) ?>" class="flex flex-col sm:flex-row sm:items-center gap-4 p-6 rounded-2xl bg-cream-50 border border-cream-200 hover:border-sage-400 hover:shadow-lg transition-all group">
                                <div class="flex flex-col gap-1 shrink-0">
                                    <time class="text-sm text-sage-600 font-medium"><?= htmlspecialchars($item['date']) ?></time>
                                    <span class="text-xs text-ink-500"><?= (int)($item['views'] ?? 0) ?> просмотров</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-ink-800 mb-1 group-hover:text-sage-700 transition-colors flex items-center gap-2">
                                        <?php if (!empty($item['important'])): ?><span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-lg bg-sage-600 text-white">Важно</span><?php endif; ?>
                                        <?= htmlspecialchars($item['title']) ?>
                                    </h3>
                                    <p class="text-ink-600 text-sm"><?= htmlspecialchars($item['excerpt']) ?></p>
                                </div>
                                <svg class="w-5 h-5 text-sage-500 shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-10">
                        <a href="<?= BASE_URL ?>/ob-yavleniya" class="inline-flex items-center text-sage-700 font-semibold hover:text-sage-800">
                            Все объявления
                            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </section>


            <!-- INSTAGRAM -->
            <section class="py-20 lg:py-24 bg-cream-200/50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-3xl font-bold text-ink-800 mb-4">Instagram</h2>
                    <p class="text-ink-600 mb-12">Подписывайтесь на нас <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-sage-600 font-semibold hover:underline">@ktsk.kz</a></p>
                    <?php if (!empty($instagramPosts)): ?>
                        <?php
                        $host = $_SERVER['HTTP_HOST'] ?? '';
                        $isLocalhost = ($host === 'localhost' || $host === '127.0.0.1' || str_starts_with($host, 'localhost:') || str_starts_with($host, '127.0.0.1:'));
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 instagram-embed-grid">
                            <?php foreach ($instagramPosts as $post): ?>
                                <div class="instagram-embed-wrapper flex justify-center">
                                    <?php if ($isLocalhost): ?>
                                        <a href="<?= htmlspecialchars($post['url']) ?>" target="_blank" rel="noopener noreferrer" class="block w-full max-w-sm p-6 rounded-2xl bg-gradient-to-br from-pink-50 to-purple-50 border border-cream-200 hover:border-pink-300 hover:shadow-lg transition-all text-center">
                                            <svg class="w-12 h-12 mx-auto mb-3 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                                            <span class="text-sage-700 font-semibold">Смотреть в Instagram</span>
                                            <?php if (!empty($post['caption'])): ?><p class="text-ink-500 text-sm mt-2 line-clamp-2"><?= htmlspecialchars(mb_substr($post['caption'], 0, 80)) ?><?= mb_strlen($post['caption']) > 80 ? '…' : '' ?></p><?php endif; ?>
                                        </a>
                                    <?php else: ?>
                                        <blockquote class="instagram-media" data-instgrm-permalink="<?= htmlspecialchars($post['url']) ?>" data-instgrm-version="14"></blockquote>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="py-16 text-center rounded-2xl bg-cream-50 border-2 border-dashed border-cream-200">
                            <p class="text-ink-600 mb-6">Добавьте посты в разделе <strong>Админ → Instagram</strong></p>
                            <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white font-semibold rounded-xl hover:opacity-90 transition-opacity">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                                Подписаться @ktsk.kz
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="mt-10">
                        <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sage-700 font-semibold hover:text-sage-800">
                            Подписаться в Instagram
                            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Наши партнеры -->
            <section class="py-20 lg:py-24 bg-gradient-to-b from-sage-50/60 to-cream-100">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-14">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-sage-500/15 text-sage-700 text-sm font-semibold mb-4">Партнёрство</span>
                        <h2 class="text-3xl lg:text-4xl font-extrabold text-ink-800 mb-3">Наши партнёры</h2>
                        <p class="text-ink-600 max-w-2xl mx-auto">Компании и организации, с которыми мы сотрудничаем</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <?php foreach ($partners as $partner): ?>
                            <a href="<?= htmlspecialchars($partner['url']) ?>" target="_blank" rel="noopener noreferrer" class="group block">
                                <div class="h-full flex flex-col items-center p-8 rounded-2xl bg-white/80 backdrop-blur-sm border border-sage-200/60 shadow-sm hover:shadow-xl hover:border-sage-400/80 hover:bg-white transition-all duration-300">
                                    <div class="flex items-center justify-center h-32 mb-4 flex-1">
                                        <img src="<?= htmlspecialchars($partner['image']) ?>" alt="<?= htmlspecialchars($partner['name']) ?>" class="max-h-28 w-auto object-contain grayscale opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-300">
                                    </div>
                                    <h3 class="font-bold text-ink-800 text-center text-sm lg:text-base group-hover:text-sage-700 transition-colors"><?= htmlspecialchars($partner['name']) ?></h3>
                                    <span class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-sage-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Перейти на сайт
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-20 lg:py-24 bg-cream-200/50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-5">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-sage-500/20 text-sage-700 text-sm font-semibold mb-6">Абитуриентам</span>
                            <h2 class="text-3xl lg:text-4xl font-extrabold text-ink-800 leading-tight mb-4">Поступить в колледж</h2>
                            <p class="text-ink-600 text-lg leading-relaxed">Узнайте о правилах приёма, сроках подачи документов и станьте студентом КВКИ.</p>
                        </div>
                        <div class="lg:col-span-7 grid sm:grid-cols-2 gap-4">
                            <a href="<?= BASE_URL ?>/abiturientam/pravila-priema" class="group flex flex-col p-6 rounded-2xl bg-cream-50 border-2 border-cream-200 hover:border-sage-400 hover:shadow-xl hover:shadow-sage-500/10 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl bg-sage-500/20 text-sage-700 flex items-center justify-center mb-4 group-hover:bg-sage-500/30 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="font-bold text-ink-800 text-lg mb-1 group-hover:text-sage-700 transition-colors">Правила приёма</h3>
                                <p class="text-ink-600 text-sm mb-4 flex-1">Список документов, сроки и порядок подачи</p>
                                <span class="inline-flex items-center gap-1 text-sage-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                    Подробнее
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                            <a href="<?= BASE_URL ?>/abiturientam/informatsiya" class="group flex flex-col p-6 rounded-2xl bg-cream-50 border-2 border-cream-200 hover:border-sage-400 hover:shadow-xl hover:shadow-sage-500/10 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl bg-sage-500/20 text-sage-700 flex items-center justify-center mb-4 group-hover:bg-sage-500/30 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h3 class="font-bold text-ink-800 text-lg mb-1 group-hover:text-sage-700 transition-colors">Информация</h3>
                                <p class="text-ink-600 text-sm mb-4 flex-1">Полезные сведения для абитуриентов</p>
                                <span class="inline-flex items-center gap-1 text-sage-600 font-semibold text-sm group-hover:gap-2 transition-all">
                                    Подробнее
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        <?php elseif ($newsItem): ?>
            <!-- Статья новости -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
                <?php require __DIR__ . '/templates/news-article.php'; ?>
            </div>
        <?php elseif ($isNewsList): ?>
            <!-- Список новостей -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
                <?php require __DIR__ . '/templates/news-list.php'; ?>
            </div>
        <?php else: ?>
            <!-- Внутренние страницы -->
            <?php $sidebarData = $menu->getSidebarItems($slug); ?>
            <?php $wideSlugs = ['blog-direktora', 'istoriya-kolledzha', 'metodicheskaya-rabota', 'antikorruptsionnyy-kompleks', 'tsel-i-napravlenie', 'sotsialnye-partnery', 'professionalno-tekhnicheskie', 'perechen-gosudarstvennykh-uslug', 'gosudarstvennye-simvoly']; $isWide = count(array_filter($wideSlugs, fn($s) => str_contains($slug, $s))) > 0; ?>
            <div class="<?= $isWide ? 'max-w-screen-2xl' : 'max-w-7xl' ?> mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
                <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
                    <?php if ($sidebarData): ?>
                        <?php require __DIR__ . '/templates/sidebar.php'; ?>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <div class="p-8 lg:p-12 rounded-3xl bg-cream-50 border border-cream-200 <?= $isWide ? 'max-w-full' : '' ?>">
                            <div class="prose prose-lg max-w-none"><?= str_replace('{{BASE_URL}}', BASE_URL, $pageContent ?? '') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/templates/footer.php'; ?>
<?php } catch (Throwable $e) {
    if (DEBUG) {
        throw $e;
    }
    http_response_code(500);
    $errorCode = 500;
    $errorTitle = 'Ошибка сервера';
    $errorMessage = 'Временная неполадка. Мы уже работаем над исправлением.';
    $errorDescription = 'Попробуйте обновить страницу или зайти позже.';
    require __DIR__ . '/templates/error.php';
} ?>