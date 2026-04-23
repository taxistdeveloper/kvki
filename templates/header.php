<?php
$menu = new Menu();
$menuItems = $menu->buildMenuWithUrls($menu->getMenu());
$router = $router ?? new Router($menu);
$currentPath = $router->getCurrentPath();
$hs = HeaderSettings::load();
$headerAboutLinks = HeaderSettings::getAboutLinks();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1f59b0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? SITE_DESCRIPTION) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: {
                            50: '#f5f5f5',
                            100: '#f5f5f5',
                            200: '#f5f5f5'
                        },
                        sage: {
                            400: '#1f59b0',
                            500: '#1f59b0',
                            600: '#1f59b0',
                            700: '#1f59b0',
                            800: '#1f59b0',
                            900: '#1f59b0'
                        },
                        edu: {
                            50: '#1f59b0',
                            100: '#1f59b0',
                            200: '#1f59b0',
                            400: '#1f59b0',
                            500: '#1f59b0',
                            600: '#1f59b0',
                            700: '#1f59b0',
                            800: '#1f59b0'
                        },
                        ink: {
                            600: '#516272',
                            700: '#3a4b5a',
                            800: '#263544'
                        }
                    },
                    fontFamily: {
                        sans: ['Montserrat', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(ROOT_PATH . '/assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
</head>

<body class="font-sans antialiased bg-cream-100 text-ink-800 min-h-screen flex flex-col has-mobile-nav">
    <header class="site-header sticky top-0 z-50 w-full shadow-sm" id="site-header">
        <!-- Верхняя панель: навигация, контакты, язык (скрывается при прокрутке) -->
        <?php if (($hs['top_bar_visible'] ?? '1') === '1'): ?>
            <div id="header-top-bar" class="header-top-bar bg-cream-100/90 border-b border-cream-200 transition-all duration-300 ease-out">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-center justify-between gap-4 py-2.5 text-sm text-ink-600">
                        <div class="flex items-center gap-6">
                            <?php if (!empty($headerAboutLinks)): ?>
                                <div class="relative group">
                                    <a href="<?= BASE_URL ?><?= htmlspecialchars($headerAboutLinks[0]['url']) ?>" class="inline-flex items-center gap-1 hover:text-sage-600 transition-colors">
                                        Колледж
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </a>
                                    <div class="absolute left-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                                        <div class="bg-white rounded-xl header-flyout border border-cream-200 py-2 min-w-[180px]">
                                            <?php foreach ($headerAboutLinks as $link): ?>
                                                <?php $linkUrl = (str_starts_with($link['url'], 'http') ? $link['url'] : (BASE_URL . (str_starts_with($link['url'], '/') ? $link['url'] : '/' . $link['url']))); ?>
                                                <a href="<?= htmlspecialchars($linkUrl) ?>" class="block px-4 py-2 text-ink-700 hover:bg-cream-100 hover:text-sage-600 transition-colors"><?= htmlspecialchars($link['title']) ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/e-resource" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sage-500/10 text-sage-700 font-semibold hover:bg-sage-500/20 hover:text-sage-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                e-Resource
                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="relative group">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-sage-600 transition-colors cursor-pointer bg-transparent border-0 text-inherit">
                                    Контакты
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute right-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                                    <div class="bg-white rounded-xl header-flyout border border-cream-200 py-2 min-w-[200px] text-right">
                                        <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $hs['phone_primary'] ?? '')) ?>" class="block px-4 py-2 text-ink-700 hover:bg-cream-100 hover:text-sage-600 transition-colors"><?= htmlspecialchars($hs['phone_primary'] ?? '+7 (747) 094 10 00') ?></a>
                                        <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $hs['phone_secondary'] ?? '')) ?>" class="block px-4 py-2 text-ink-700 hover:bg-cream-100 hover:text-sage-600 transition-colors"><?= htmlspecialchars($hs['phone_secondary'] ?? '+7 (700) 123 45 67') ?></a>
                                        <?php if (!empty($hs['phone_home'])): ?>
                                            <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $hs['phone_home'])) ?>" class="block px-4 py-2 text-ink-700 hover:bg-cream-100 hover:text-sage-600 transition-colors"><?= htmlspecialchars($hs['phone_home']) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($hs['whatsapp'])): ?>
                                <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D/', '', $hs['whatsapp'])) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-cream-200 transition-colors" aria-label="WhatsApp">
                                    <svg class="w-5 h-5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($hs['telegram'])): ?>
                                <a href="<?= htmlspecialchars($hs['telegram']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-cream-200 transition-colors" aria-label="Telegram">
                                    <svg class="w-5 h-5 text-[#0088cc]" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($hs['youtube'])): ?>
                                <a href="<?= htmlspecialchars($hs['youtube']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-cream-200 transition-colors" aria-label="YouTube">
                                    <svg class="w-5 h-5 text-[#FF0000]" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($hs['instagram'])): ?>
                                <a href="<?= htmlspecialchars($hs['instagram']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-cream-200 transition-colors" aria-label="Instagram">
                                    <svg class="w-5 h-5 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <div class="relative group">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-sage-600 transition-colors">
                                    Рус
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div class="absolute right-0 top-full pt-0.5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                                    <div class="bg-white rounded-xl header-flyout border border-cream-200 py-2 min-w-[100px]">

                                        <a href="#" class="block px-4 py-2 text-left text-ink-700 hover:bg-cream-100 hover:text-sage-600 transition-colors">Қаз</a>
                                        <a href="#" class="block px-4 py-2 text-left text-ink-700 hover:bg-cream-100 hover:text-sage-600 transition-colors">Eng</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Основной header -->
        <div class="header-main-wrap w-full pb-3 pt-2">
            <div class="header-main-panel w-full px-4 sm:px-6 lg:px-8 rounded-2xl bg-white shadow-lg border border-cream-200/70">
                <div class="relative flex items-center justify-center h-16 lg:h-20 w-full">
                    <div class="flex items-center justify-center gap-6 xl:gap-10">
                    <a href="<?= BASE_URL ?>/" class="flex items-center gap-3 group shrink-0">
                        <div class="flex items-center justify-center rounded-2xl overflow-hidden shadow-md group-hover:shadow-lg transition-all duration-300 ring-1 ring-cream-200/60 p-0.5 min-w-[48px] min-h-[48px] lg:min-w-[56px] lg:min-h-[56px]">
                            <?php $logoFile = file_exists(ROOT_PATH . '/assets/images/logo/logo-50.png') ? 'logo-50.png' : (file_exists(ROOT_PATH . '/assets/images/logo/logo-50.svg') ? 'logo-50.svg' : null); ?>
                            <?php if ($logoFile): ?>
                                <img src="<?= BASE_URL ?>/assets/images/logo/<?= $logoFile ?>" alt="<?= htmlspecialchars($hs['logo_title'] ?? 'Карагандинский высший колледж инжиниринга') ?>" class="h-12 lg:h-14 w-auto object-contain group-hover:scale-[1.02] transition-transform duration-300 rounded-xl">
                            <?php else: ?>
                                <span class="h-12 lg:h-14 flex items-center justify-center px-3 font-bold text-sage-700 text-lg lg:text-xl" aria-label="<?= htmlspecialchars($hs['logo_abbr'] ?? 'КВКИ') ?>"><?= htmlspecialchars($hs['logo_abbr'] ?? 'КВКИ') ?></span>
                            <?php endif; ?>
                        </div>
                        <?php $logo2File = file_exists(ROOT_PATH . '/assets/images/logo/logo.png') ? 'logo.png' : (file_exists(ROOT_PATH . '/assets/images/logo/logo.') ? 'logo.png' : null); ?>
                        <?php if ($logo2File): ?>
                            <div class="flex items-center justify-center rounded-2xl overflow-hidden shadow-md group-hover:shadow-lg transition-all duration-300 ring-1 ring-cream-200/60 p-0.5 min-w-[48px] min-h-[48px] lg:min-w-[56px] lg:min-h-[56px]">
                                <img src="<?= BASE_URL ?>/assets/images/logo/<?= $logo2File ?>" alt="МОН РК" class="h-12 lg:h-14 w-auto object-contain group-hover:scale-[1.02] transition-transform duration-300 rounded-xl">
                            </div>
                        <?php endif; ?>
                    </a>

                    <nav class="hidden xl:flex items-center gap-2">
                        <?php foreach ($menuItems as $item): ?>
                            <div class="relative group">
                                <a href="<?= htmlspecialchars($item['url']) ?>"
                                    class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-base font-medium text-ink-600 hover:bg-cream-200 hover:text-sage-700 transition-colors <?= str_starts_with($currentPath, '/' . $item['slug']) ? 'text-sage-700 bg-cream-200' : '' ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                    <?php if (!empty($item['children'])): ?><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg><?php endif; ?>
                                </a>
                                <?php if (!empty($item['children'])): ?>
                                    <div class="absolute left-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                        <div class="bg-white rounded-2xl header-flyout border border-cream-200 py-2 min-w-[260px]">
                                            <?php foreach ($item['children'] as $child): ?>
                                                <div class="relative submenu-item">
                                                    <a href="<?= htmlspecialchars($child['url']) ?>" class="flex items-center justify-between px-5 py-3 text-base text-ink-700 hover:bg-cream-200 hover:text-sage-700 transition-colors">
                                                        <?= htmlspecialchars($child['title']) ?>
                                                        <?php if (!empty($child['children'])): ?><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg><?php endif; ?>
                                                    </a>
                                                    <?php if (!empty($child['children'])): ?>
                                                        <div class="absolute left-full top-0 ml-1 hidden submenu-children bg-white rounded-2xl header-flyout border border-cream-200 py-2 min-w-[260px]">
                                                            <?php foreach ($child['children'] as $grandchild): ?>
                                                                <div class="relative submenu-item">
                                                                    <a href="<?= htmlspecialchars($grandchild['url']) ?>" class="flex items-center justify-between px-5 py-3 text-base text-ink-700 hover:bg-cream-200 hover:text-sage-700 transition-colors">
                                                                        <?= htmlspecialchars($grandchild['title']) ?>
                                                                        <?php if (!empty($grandchild['children'])): ?><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                                            </svg><?php endif; ?>
                                                                    </a>
                                                                    <?php if (!empty($grandchild['children'])): ?>
                                                                        <div class="absolute left-full top-0 ml-1 hidden submenu-children bg-white rounded-2xl header-flyout border border-cream-200 py-2 min-w-[260px]">
                                                                            <?php foreach ($grandchild['children'] as $ggc): ?>
                                                                                <a href="<?= htmlspecialchars($ggc['url']) ?>" class="block px-5 py-3 text-base text-ink-700 hover:bg-cream-200 hover:text-sage-700 transition-colors"><?= htmlspecialchars($ggc['title']) ?></a>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </nav>
                    </div>

                    <div class="absolute right-0 top-0 bottom-0 flex items-center xl:hidden">
                        <button type="button" id="mobile-menu-btn" class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-ink-600 hover:bg-cream-200 transition-colors" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobile-menu">
                            <svg class="w-6 h-6 menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg class="w-6 h-6 menu-icon-close hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="mobile-menu-overlay" class="mobile-menu-overlay xl:hidden" aria-hidden="true"></div>
    <nav id="mobile-menu" class="mobile-menu xl:hidden" aria-label="Мобильное меню">
        <div class="pt-14 pb-8 px-4">
            <?php
            function renderMobileMenu($items, $level = 0)
            {
                foreach ($items as $item) {
                    $hasChildren = !empty($item['children']);
                    $childId = $hasChildren ? 'nav-' . md5($item['title'] . $level) : '';
                    if ($hasChildren): ?>
                        <div class="border-b border-cream-200 last:border-0">
                            <button type="button" class="mobile-nav-item w-full text-left" data-accordion="<?= $childId ?>" aria-expanded="false" aria-controls="<?= $childId ?>">
                                <?= htmlspecialchars($item['title']) ?>
                                <svg class="w-5 h-5 text-ink-600 accordion-chevron transition-transform flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="<?= $childId ?>" class="mobile-nav-children" role="region">
                                <?php renderMobileMenu($item['children'], $level + 1); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($item['url']) ?>" class="mobile-nav-item mobile-nav-item--link block border-b border-cream-200 last:border-0" data-close-menu>
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
            <?php endif;
                }
            }
            renderMobileMenu($menuItems);
            ?>
        </div>
    </nav>