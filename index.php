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
            $listHtml .= '<a href="' . htmlspecialchars($url) . '" class="group block bg-white rounded-[28px] shadow-soft border border-black/5 hover:shadow-card hover:border-sage-600/40 transition-all px-6 py-5 mb-4">'
                . '<div class="flex items-center gap-5">'
                . '<div class="shrink-0 w-24"><time class="text-xs text-ink-600 font-semibold">' . htmlspecialchars($r['date']) . '</time><div class="text-[11px] text-ink-500 mt-1">' . $views . ' просмотров</div></div>'
                . '<div class="flex-1 min-w-0">'
                . '<div class="flex items-center gap-2 min-w-0">'
                . ($r['is_important'] ? '<span class="inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full bg-sage-600 text-white">Важно</span>' : '')
                . '<h3 class="font-semibold text-ink-800 leading-snug truncate group-hover:text-sage-600 transition-colors">' . htmlspecialchars($r['title']) . '</h3></div>'
                . '<p class="text-ink-600 text-sm mt-1 truncate">' . htmlspecialchars($r['excerpt']) . '</p></div>'
                . '<svg class="w-5 h-5 text-ink-400 shrink-0 group-hover:text-sage-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>'
                . '</div></a>';
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
    // Демо-карточки Instagram на случай, когда посты из админки ещё не добавлены
    $instagramImagesPath = ROOT_PATH . '/assets/images/instagram';
    $instagramImagesUrl = BASE_URL . '/assets/images/instagram';
    $instagramStockImages = [
        'https://images.unsplash.com/photo-1523050854058-ddf90803c5d4?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1503676382389-4809596d5290?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1497486751825-1233686d5d80?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1472289065668-ce650ac443d2?auto=format&fit=crop&w=800&q=80',
    ];
    $instagramImage = function ($n) use ($instagramImagesPath, $instagramImagesUrl, $instagramStockImages) {
        foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
            if (file_exists($instagramImagesPath . "/instagram{$n}{$ext}")) {
                return $instagramImagesUrl . "/instagram{$n}{$ext}";
            }
        }
        return $instagramStockImages[$n - 1] ?? '';
    };
    $instagramDemoPosts = [
        ['url' => $instagramProfileUrl, 'caption' => 'Учебные будни и практические занятия студентов КВКИ.', 'image' => $instagramImage(1)],
        ['url' => $instagramProfileUrl, 'caption' => 'Мероприятия, конкурсы и проекты колледжа.', 'image' => $instagramImage(2)],
        ['url' => $instagramProfileUrl, 'caption' => 'Новости, достижения и жизнь колледжа каждый день.', 'image' => $instagramImage(3)],
        ['url' => $instagramProfileUrl, 'caption' => 'Производственная практика и реальные кейсы в обучении.', 'image' => $instagramImage(4)],
        ['url' => $instagramProfileUrl, 'caption' => 'Творческие работы студентов архитектуры и дизайна.', 'image' => $instagramImage(5)],
        ['url' => $instagramProfileUrl, 'caption' => 'Победы в конкурсах, олимпиадах и спортивных мероприятиях.', 'image' => $instagramImage(6)],
        ['url' => $instagramProfileUrl, 'caption' => 'День открытых дверей и встречи с абитуриентами.', 'image' => $instagramImage(7)],
        ['url' => $instagramProfileUrl, 'caption' => 'Современные аудитории, лаборатории и мастерские колледжа.', 'image' => $instagramImage(8)],
        ['url' => $instagramProfileUrl, 'caption' => 'Командная работа, развитие навыков и студенческая активность.', 'image' => $instagramImage(9)],
    ];
    $instagramItemsCount = !empty($instagramPosts) ? count($instagramPosts) : count($instagramDemoPosts);
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
                                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.1] mb-8 <?= !empty($slide['image']) ? 'text-white' : 'text-ink-800' ?>">
                                                <?= htmlspecialchars($slide['title']) ?>
                                            </h1>
                                            <p class="text-xl leading-relaxed mb-10 <?= !empty($slide['image']) ? 'text-cream-100' : 'text-ink-600' ?>">
                                                <?= htmlspecialchars($slide['text']) ?>
                                            </p>
                                            <div class="flex flex-wrap gap-4">
                                                <a href="<?= BASE_URL ?>/abiturientam" class="inline-flex items-center justify-center px-8 py-4 bg-edu-600 text-white font-semibold rounded-2xl hover:bg-edu-700 transition-colors shadow-sm hover:shadow">
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($stats as $stat): ?>
                            <div class="bg-white rounded-[28px] shadow-soft border border-black/5 px-6 py-5 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-sage-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-3xl font-bold text-ink-800 leading-none"><?= htmlspecialchars($stat['value']) ?></div>
                                    <div class="text-sm text-ink-600 mt-1 truncate"><?= htmlspecialchars($stat['label']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- НАПРАВЛЕНИЯ -->
            <section class="py-20 lg:py-24">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="kvki-title-wrap">
                        <h2 class="kvki-title text-3xl sm:text-4xl mb-3">Направления</h2>
                        <p class="kvki-subtitle text-base sm:text-lg mb-12">Выберите направление подготовки</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <a href="<?= BASE_URL ?>/zhas-maman/obrazovatelnye-programmy/stroitelnye-spetsialnosti" class="group p-6 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center mb-5 group-hover:bg-sage-600/15 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Строительство</h3>
                            <p class="text-ink-600 text-sm">Гражданское, промышленное, дорожное строительство</p>
                        </a>
                        <a href="<?= BASE_URL ?>/zhas-maman/obrazovatelnye-programmy/arkhitektura-i-dizayn" class="group p-6 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center mb-5 group-hover:bg-sage-600/15 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6 6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Архитектура и дизайн</h3>
                            <p class="text-ink-600 text-sm">Жилой, коммерческий, ландшафтный дизайн</p>
                        </a>
                        <a href="<?= BASE_URL ?>/zhas-maman/obrazovatelnye-programmy/tekhnicheskie-spetsialnosti" class="group p-6 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center mb-5 group-hover:bg-sage-600/15 transition-colors">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink-800 mb-2 text-lg">Технические специальности</h3>
                            <p class="text-ink-600 text-sm">Электротехника, механика, автоматизация</p>
                        </a>
                        <a href="<?= BASE_URL ?>/studentam" class="group p-6 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center mb-5 group-hover:bg-sage-600/15 transition-colors">
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
                    <div class="kvki-title-wrap">
                        <h2 class="kvki-title text-3xl sm:text-4xl mb-3">Объявления</h2>
                        <p class="kvki-subtitle text-base sm:text-lg mb-12">Важная информация для студентов и абитуриентов</p>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($announcements as $item): ?>
                            <a href="<?= htmlspecialchars($item['url']) ?>" class="group block bg-white rounded-[28px] shadow-soft border border-black/5 hover:shadow-card hover:border-sage-600/40 transition-all px-6 py-5">
                                <div class="flex items-center gap-5">
                                    <div class="shrink-0 w-24">
                                        <time class="text-xs text-ink-600 font-semibold"><?= htmlspecialchars($item['date']) ?></time>
                                        <div class="text-[11px] text-ink-500 mt-1"><?= (int)($item['views'] ?? 0) ?> просмотров</div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <?php if (!empty($item['important'])): ?><span class="inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full bg-sage-600 text-white">Важно</span><?php endif; ?>
                                            <h3 class="font-bold text-ink-800 leading-snug truncate group-hover:text-sage-600 transition-colors">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </h3>
                                        </div>
                                        <p class="text-ink-600 text-sm mt-1 truncate"><?= htmlspecialchars($item['excerpt']) ?></p>
                                    </div>
                                    <svg class="w-5 h-5 text-ink-400 shrink-0 group-hover:text-sage-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
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
                    <div class="kvki-title-wrap">
                        <h2 class="kvki-title text-3xl sm:text-4xl mb-3">Instagram</h2>
                        <p class="kvki-subtitle text-base sm:text-lg mb-12">Подписывайтесь на нас <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-sage-600 font-semibold hover:underline">@ktsk.kz</a></p>
                    </div>
                    <?php if (!empty($instagramPosts)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 instagram-embed-grid">
                            <?php foreach ($instagramPosts as $idx => $post): ?>
                                <div class="instagram-embed-wrapper js-instagram-item flex justify-center"<?= $idx >= 6 ? ' style="display:none;"' : '' ?>>
                                    <a
                                        href="<?= htmlspecialchars($post['url']) ?>"
                                        class="js-instagram-open block w-full max-w-sm p-6 rounded-2xl bg-gradient-to-br from-pink-50 to-purple-50 border border-cream-200 hover:border-pink-300 hover:shadow-lg transition-all text-center"
                                        data-post-type="embed"
                                        data-post-url="<?= htmlspecialchars($post['url']) ?>"
                                        data-post-caption="<?= htmlspecialchars($post['caption'] ?? '') ?>"
                                    >
                                        <svg class="w-12 h-12 mx-auto mb-3 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                                        <span class="text-sage-700 font-semibold">Открыть пост</span>
                                        <?php if (!empty($post['caption'])): ?><p class="text-ink-500 text-sm mt-2 line-clamp-2"><?= htmlspecialchars(mb_substr($post['caption'], 0, 80)) ?><?= mb_strlen($post['caption']) > 80 ? '…' : '' ?></p><?php endif; ?>
                                    </a>
                                </div>
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
                                    <?= $idx >= 6 ? ' style="display:none;"' : '' ?>
                                >
                                    <div class="aspect-square bg-cream-100">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="<?= htmlspecialchars($post['image']) ?>" alt="Демо публикация Instagram" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300">
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-5">
                                        <div class="flex items-center gap-2 font-semibold text-sm mb-2" style="color: #253f50;">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                                            @ktsk.kz
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
            <div id="instagram-post-modal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-black/70" data-instagram-modal-close></div>
                <div class="relative w-full h-full p-4 sm:p-6 lg:p-10 overflow-y-auto">
                    <div class="max-w-4xl mx-auto bg-white rounded-3xl overflow-hidden shadow-2xl">
                        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-black/10">
                            <h3 class="text-lg sm:text-xl font-bold text-ink-800">Публикация Instagram</h3>
                            <button type="button" class="w-10 h-10 rounded-xl border border-black/10 hover:bg-black/5 text-ink-600 flex items-center justify-center" data-instagram-modal-close aria-label="Закрыть">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div id="instagram-modal-content" class="p-5 sm:p-6"></div>
                        <div class="px-5 sm:px-6 pb-6 flex justify-end">
                            <a id="instagram-modal-link" href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white" style="background-color: #253f50;">
                                Открыть в Instagram
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Наши партнеры -->
            <section class="py-20 lg:py-24 bg-gradient-to-b from-sage-50/60 to-cream-100">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-14">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-sage-500/15 text-sage-700 text-sm font-semibold mb-4">Партнёрство</span>
                        <div class="kvki-title-wrap mx-auto">
                            <h2 class="kvki-title text-3xl lg:text-4xl mb-3">Наши партнёры</h2>
                            <p class="kvki-subtitle text-base sm:text-lg max-w-2xl mx-auto">Компании и организации, с которыми мы сотрудничаем</p>
                        </div>
                    </div>
                    <div class="swiper partners-swiper">
                        <div class="swiper-wrapper">
                            <?php
                            $partnerSlides = $partners;
                            if (count($partnerSlides) > 1 && count($partnerSlides) < 8) {
                                $partnerSlides = array_merge($partnerSlides, $partnerSlides);
                            }
                            ?>
                            <?php foreach ($partnerSlides as $partner): ?>
                                <div class="swiper-slide h-auto">
                                    <a href="<?= htmlspecialchars($partner['url']) ?>" target="_blank" rel="noopener noreferrer" class="group block h-full">
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
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
            <!-- CTA -->
            <section class="py-20 lg:py-24 bg-cream-200/50">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                        <div class="lg:col-span-5">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-sage-500/20 text-sage-700 text-sm font-semibold mb-6">Абитуриентам</span>
                            <h2 class="kvki-title text-3xl lg:text-4xl leading-tight mb-4">Поступить в колледж</h2>
                            <p class="kvki-subtitle text-base sm:text-lg leading-relaxed">Узнайте о правилах приёма, сроках подачи документов и станьте студентом КВКИ.</p>
                        </div>
                        <div class="lg:col-span-7 grid sm:grid-cols-2 gap-4">
                            <a href="<?= BASE_URL ?>/abiturientam/pravila-priema" class="group flex flex-col p-7 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center mb-5 group-hover:bg-sage-600/15 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="font-bold text-sage-600 text-xl mb-2 transition-colors">Правила приёма</h3>
                                <p class="text-ink-600 text-sm mb-4 flex-1">Список документов, сроки и порядок подачи</p>
                                <span class="inline-flex items-center gap-2 text-sage-600 font-semibold text-sm group-hover:gap-3 transition-all">
                                    Подробнее
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                            <a href="<?= BASE_URL ?>/abiturientam/informatsiya" class="group flex flex-col p-7 rounded-[28px] bg-white border border-black/5 shadow-soft hover:shadow-card hover:border-sage-600/40 transition-all duration-300">
                                <div class="w-12 h-12 rounded-2xl bg-sage-600/10 text-sage-600 flex items-center justify-center mb-5 group-hover:bg-sage-600/15 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h3 class="font-bold text-ink-800 text-lg mb-1 group-hover:text-sage-700 transition-colors">Информация</h3>
                                <p class="text-ink-600 text-sm mb-4 flex-1">Полезные сведения для абитуриентов</p>
                                <span class="inline-flex items-center gap-2 text-sage-600 font-semibold text-sm group-hover:gap-3 transition-all">
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
            <!-- Внутренние страницы (без сайдбара — полная ширина контента) -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
                <div class="min-w-0">
                    <div class="p-8 lg:p-12 rounded-3xl bg-cream-50 border border-cream-200 max-w-full">
                        <div class="prose prose-lg max-w-none"><?= str_replace('{{BASE_URL}}', BASE_URL, $pageContent ?? '') ?></div>
                    </div>
                    <?php if (in_array($slug, ['stroitelnoe-tekhnicheskoe-otdelenie', 'arkhitektury-dizayna-i-dekorativno-prikladnogo-iskusstva', 'professionalno-tekhnicheskie'], true)): ?>
                        <?php
                        $departmentInstagramItems = !empty($instagramPosts)
                            ? $instagramPosts
                            : $instagramDemoPosts;
                        $departmentInstagramItemsCount = count($departmentInstagramItems);
                        $buildInstagramEmbedUrl = static function ($postUrl) {
                            $url = trim((string)$postUrl);
                            if ($url === '') return '';
                            $parts = parse_url($url);
                            if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) return '';
                            $path = rtrim($parts['path'], '/') . '/';
                            return $parts['scheme'] . '://' . $parts['host'] . $path . 'embed/captioned/';
                        };
                        ?>
                        <section id="instagram-feed" class="mt-8 rounded-3xl border border-cream-200 bg-gradient-to-br from-white to-cream-50 shadow-[0_12px_30px_rgba(15,23,42,0.10)] p-6 sm:p-8 lg:p-10">
                            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 mb-7">
                                <div class="max-w-3xl">
                                    <p class="text-sm lg:text-base font-semibold uppercase tracking-[0.12em] text-sage-700 mb-3">Приоритет UX</p>
                                    <h2 class="text-3xl lg:text-5xl font-black text-ink-800 tracking-tight leading-tight mb-3">Instagram отделения</h2>
                                    <p class="text-lg lg:text-2xl text-ink-600 leading-relaxed">
                                        Крупная типографика, четкая структура и усиленные зоны нажатия для комфортного чтения на любых экранах.
                                    </p>
                                </div>
                                <a href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 min-h-[54px] px-7 py-3 rounded-xl bg-sage-600 text-white text-base lg:text-lg font-semibold hover:bg-sage-700 transition-colors">
                                    Все публикации
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                <?php foreach ($departmentInstagramItems as $idx => $item): ?>
                                    <?php
                                    $postUrl = $item['url'] ?? '';
                                    $caption = $item['caption'] ?? '';
                                    $image = $item['image'] ?? '';
                                    $embedUrl = $buildInstagramEmbedUrl($postUrl);
                                    $postType = !empty($image) ? 'demo' : 'embed';
                                    ?>
                                    <article class="js-dept-instagram-item rounded-2xl border border-cream-200 bg-white overflow-hidden shadow-sm hover:shadow-md transition"<?= $idx >= 6 ? ' style="display:none;"' : '' ?>>
                                        <button
                                            type="button"
                                            class="js-instagram-open w-full text-left"
                                            data-post-type="<?= htmlspecialchars($postType) ?>"
                                            data-post-url="<?= htmlspecialchars($postUrl ?: $instagramProfileUrl) ?>"
                                            data-post-image="<?= htmlspecialchars($image) ?>"
                                            data-post-caption="<?= htmlspecialchars($caption) ?>"
                                        >
                                            <?php if (!empty($image)): ?>
                                                <div class="aspect-square bg-cream-100">
                                                    <img src="<?= htmlspecialchars($image) ?>" alt="Публикация Instagram" class="w-full h-full object-cover">
                                                </div>
                                            <?php else: ?>
                                                <div class="aspect-square bg-gradient-to-br from-pink-50 to-purple-100 flex items-center justify-center">
                                                    <div class="text-center px-6">
                                                        <svg class="w-14 h-14 mx-auto mb-3 text-pink-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                                                        <p class="text-ink-700 text-base font-semibold">Смотреть пост</p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </button>
                                        <div class="p-4 lg:p-5 bg-white">
                                            <?php if (!empty($caption)): ?>
                                                <p class="text-base text-ink-600 leading-relaxed mb-4 min-h-[72px]"><?= htmlspecialchars(mb_substr($caption, 0, 150)) ?><?= mb_strlen($caption) > 150 ? '…' : '' ?></p>
                                            <?php endif; ?>
                                            <button
                                                type="button"
                                                class="js-instagram-open inline-flex items-center justify-center gap-2 min-h-[48px] px-5 py-2.5 rounded-xl border border-cream-200 bg-cream-50 text-sage-700 text-base font-semibold hover:border-sage-300 hover:bg-sage-50 transition-colors"
                                                data-post-type="<?= htmlspecialchars($postType) ?>"
                                                data-post-url="<?= htmlspecialchars($postUrl ?: $instagramProfileUrl) ?>"
                                                data-post-image="<?= htmlspecialchars($image) ?>"
                                                data-post-caption="<?= htmlspecialchars($caption) ?>"
                                            >
                                                Просмотр в окне
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($departmentInstagramItemsCount > 6): ?>
                                <div class="mt-6 flex justify-center">
                                    <button
                                        type="button"
                                        id="dept-instagram-toggle"
                                        data-state="collapsed"
                                        class="inline-flex items-center justify-center gap-2 min-h-[52px] px-6 py-3 rounded-xl border border-cream-200 bg-white text-sage-700 text-base font-semibold hover:border-sage-300 hover:bg-sage-50 transition-colors"
                                    >
                                        <span>Показать больше</span>
                                        <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <div id="instagram-post-modal" class="fixed inset-0 z-[100] hidden" aria-hidden="true">
                                <div class="absolute inset-0 bg-black/70" data-instagram-modal-close></div>
                                <div class="relative w-full h-full p-4 sm:p-6 lg:p-10 overflow-y-auto">
                                    <div class="max-w-4xl mx-auto bg-white rounded-3xl overflow-hidden shadow-2xl">
                                        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-black/10">
                                            <h3 class="text-lg sm:text-xl font-bold text-ink-800">Публикация Instagram</h3>
                                            <button type="button" class="w-10 h-10 rounded-xl border border-black/10 hover:bg-black/5 text-ink-600 flex items-center justify-center" data-instagram-modal-close aria-label="Закрыть">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div id="instagram-modal-content" class="p-5 sm:p-6"></div>
                                        <div class="px-5 sm:px-6 pb-6 flex justify-end">
                                            <a id="instagram-modal-link" href="<?= htmlspecialchars($instagramProfileUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-white" style="background-color: #253f50;">
                                                Открыть в Instagram
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($departmentInstagramItemsCount > 6): ?>
                                <script>
                                    (function() {
                                        const toggleBtn = document.getElementById('dept-instagram-toggle');
                                        if (!toggleBtn) return;
                                        const items = document.querySelectorAll('.js-dept-instagram-item');
                                        if (!items.length) return;

                                        const toggleLabel = toggleBtn.querySelector('span');
                                        const toggleIcon = toggleBtn.querySelector('svg');

                                        function expand() {
                                            items.forEach((item) => {
                                                item.style.display = '';
                                            });
                                            toggleBtn.dataset.state = 'expanded';
                                            if (toggleLabel) toggleLabel.textContent = 'Показать меньше';
                                            if (toggleIcon) toggleIcon.classList.add('rotate-180');
                                        }

                                        function collapse() {
                                            items.forEach((item, index) => {
                                                item.style.display = index >= 6 ? 'none' : '';
                                            });
                                            toggleBtn.dataset.state = 'collapsed';
                                            if (toggleLabel) toggleLabel.textContent = 'Показать больше';
                                            if (toggleIcon) toggleIcon.classList.remove('rotate-180');
                                        }

                                        toggleBtn.addEventListener('click', function() {
                                            if (toggleBtn.dataset.state === 'collapsed') {
                                                expand();
                                            } else {
                                                collapse();
                                            }
                                        });
                                    })();
                                </script>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
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