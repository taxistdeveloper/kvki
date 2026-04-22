<?php
$hs = $hs ?? HeaderSettings::load();
$fs = FooterSettings::load();
$footerPhone = $hs['phone_primary'] ?? $hs['phone_home'] ?? '+7 (7212) 44-12-65';
$footerEmail = $fs['footer_email'] ?? 'info@kvki.kz';
$footerAddress = $fs['footer_address'] ?? 'ул. Кирпичная 8, г. Караганда, Казахстан';
$footerAboutLinks = FooterSettings::getLinks('footer_about_links');
$footerAdmissionLinks = FooterSettings::getLinks('footer_admission_links');
$footerAnticorTitle = $fs['footer_anticor_title'] ?? 'Антикоррупционный комплекс';
$footerAnticorLinks = FooterSettings::getLinks('footer_anticor_links');
$footerHotline = $fs['footer_hotline'] ?? '1424';
$footerMapUrl = $fs['footer_map_embed_url'] ?? '';
$footer2gisUrl = $fs['footer_2gis_url'] ?? 'https://2gis.kz/karaganda/search/Кирпичная%208';
$footerYandexUrl = $fs['footer_yandex_url'] ?? 'https://yandex.ru/maps/?pt=73.0876,49.8027&z=17&l=map';
$footerBottomLinks = FooterSettings::getLinks('footer_bottom_links');
$footerLinkClass = 'group inline-flex items-center gap-2 py-1.5 text-base text-cream-100/90 hover:text-cream-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-cream-100/30 focus:ring-offset-2 focus:ring-offset-sage-800 rounded';
$footerChevron = '<svg class="w-3.5 h-3.5 text-cream-100/50 group-hover:text-cream-100/80 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
$footerCardLinkClass = 'group inline-flex items-center gap-2 py-1.5 text-base text-black hover:text-black/80 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-black/20 focus:ring-offset-2 focus:ring-offset-white rounded';
$footerCardChevron = '<svg class="w-3.5 h-3.5 text-black/50 group-hover:text-black/70 group-hover:translate-x-0.5 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
?>
    <footer class="mt-auto text-cream-100" style="background-color: #253f50;" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-12 lg:gap-10">
                <!-- Колонка 1: Логотип и контакты -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <a href="<?= BASE_URL ?>/" class="inline-flex items-center gap-4 mb-6 group">
                        <div class="flex items-center justify-center rounded-2xl overflow-hidden bg-cream-100/10 p-1.5 shadow-inner group-hover:bg-cream-100/15 transition-colors duration-300 min-w-[48px] min-h-[48px]">
                            <?php $footerLogoFile = file_exists(ROOT_PATH . '/assets/images/logo/logo-50.png') ? 'logo-50.png' : (file_exists(ROOT_PATH . '/assets/images/logo/logo-50.svg') ? 'logo-50.svg' : null); ?>
                            <?php if ($footerLogoFile): ?>
                                <img src="<?= BASE_URL ?>/assets/images/logo/<?= $footerLogoFile ?>" alt="<?= htmlspecialchars($hs['logo_title'] ?? 'Карагандинский высший колледж инжиниринга') ?>" class="h-12 w-auto object-contain group-hover:scale-[1.02] transition-transform duration-300">
                            <?php else: ?>
                                <span class="font-bold text-cream-100 text-xl"><?= htmlspecialchars($hs['logo_abbr'] ?? 'КВКИ') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="max-w-[200px]">
                            <span class="font-semibold text-cream-100 text-base leading-tight block group-hover:text-cream-50 transition-colors"><?= htmlspecialchars(SITE_NAME) ?></span>
                        </div>
                    </a>
                    <ul class="space-y-4 text-base">
                        <li class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-lg bg-cream-100/10 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-cream-100/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <span class="text-cream-100/90 leading-relaxed"><?= htmlspecialchars($footerAddress) ?></span>
                        </li>
                        <li>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $footerPhone)) ?>" class="<?= $footerLinkClass ?>">
                                <span class="w-9 h-9 rounded-lg bg-cream-100/10 flex items-center justify-center shrink-0 group-hover:bg-cream-100/15 transition-colors">
                                    <svg class="w-4 h-4 text-cream-100/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </span>
                                <?= htmlspecialchars($footerPhone) ?>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:<?= htmlspecialchars($footerEmail) ?>" class="<?= $footerLinkClass ?>">
                                <span class="w-9 h-9 rounded-lg bg-cream-100/10 flex items-center justify-center shrink-0 group-hover:bg-cream-100/15 transition-colors">
                                    <svg class="w-4 h-4 text-cream-100/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <?= htmlspecialchars($footerEmail) ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Колонка 2: О колледже -->
                <nav aria-label="О колледже">
                    <h4 class="text-base font-semibold text-cream-100 uppercase tracking-wider mb-5">О колледже</h4>
                    <ul class="space-y-2">
                        <?php foreach ($footerAboutLinks as $link): ?>
                        <li><a href="<?= BASE_URL ?><?= htmlspecialchars(strpos($link['url'], '/') === 0 ? $link['url'] : '/' . $link['url']) ?>" class="<?= $footerLinkClass ?>"><?= $footerChevron ?><?= htmlspecialchars($link['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <!-- Колонка 3: Поступление -->
                <nav aria-label="Поступление">
                    <h4 class="text-base font-semibold text-cream-100 uppercase tracking-wider mb-5">Поступление</h4>
                    <ul class="space-y-2">
                        <?php foreach ($footerAdmissionLinks as $link): ?>
                        <li><a href="<?= BASE_URL ?><?= htmlspecialchars(strpos($link['url'], '/') === 0 ? $link['url'] : '/' . $link['url']) ?>" class="<?= $footerLinkClass ?>"><?= $footerChevron ?><?= htmlspecialchars($link['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <!-- Колонка 4: Антикоррупционный комплекс -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl p-7 lg:p-8 shadow-soft border border-sage-200 hover:border-sage-300 transition-colors duration-300 w-full min-w-0">
                        <div class="flex justify-center mb-4">
                            <div class="w-16 h-16 rounded-2xl bg-sage-600/10 flex items-center justify-center">
                                <svg class="w-9 h-9 text-sage-600/85" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                        </div>
                        <h4 class="font-semibold text-black text-center mb-5 text-lg"><?= htmlspecialchars($footerAnticorTitle) ?></h4>
                        <ul class="space-y-2 mb-5">
                            <?php foreach ($footerAnticorLinks as $link): ?>
                            <li><a href="<?= BASE_URL ?><?= htmlspecialchars(strpos($link['url'], '/') === 0 ? $link['url'] : '/' . $link['url']) ?>" class="<?= $footerCardLinkClass ?>"><?= $footerCardChevron ?><?= htmlspecialchars($link['title']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="text-center pt-5 border-t border-sage-200">
                            <p class="text-sm text-black/80 mb-2">Горячая линия по борьбе с коррупцией</p>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $footerHotline)) ?>" class="inline-block text-2xl font-bold text-red-500 hover:text-red-400 transition-colors focus:outline-none focus:ring-2 focus:ring-red-400/50 focus:ring-offset-2 focus:ring-offset-white rounded px-2 py-1"><?= htmlspecialchars($footerHotline) ?></a>
                            <p class="text-sm text-black/70 mt-2">Звонок по всему Казахстану бесплатный</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Карта и соцсети -->
            <div class="mt-14 pt-12 border-t border-cream-100/10">
                <div class="rounded-2xl overflow-hidden bg-sage-900/50 mb-8 h-48 sm:h-56 lg:h-64 relative" id="footer-map" role="img" aria-label="КВКИ — г. Караганда, ул. Кирпичная 8"></div>
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <div class="flex gap-3">
                        <?php if (!empty($hs['telegram'])): ?>
                <a href="<?= htmlspecialchars($hs['telegram']) ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-xl bg-cream-100/20 flex items-center justify-center hover:bg-cream-100/30 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-cream-100/30 focus:ring-offset-2 focus:ring-offset-sage-800 transition-all duration-200" aria-label="Telegram">
                    <img src="https://cdn.simpleicons.org/telegram/FFFFFF" alt="" class="w-6 h-6 object-contain" width="24" height="24">
                </a>
                <?php endif; ?>
                <?php if (!empty($hs['instagram'])): ?>
                <a href="<?= htmlspecialchars($hs['instagram']) ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-xl bg-cream-100/20 flex items-center justify-center hover:bg-cream-100/30 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-cream-100/30 focus:ring-offset-2 focus:ring-offset-sage-800 transition-all duration-200" aria-label="Instagram">
                    <img src="https://cdn.simpleicons.org/instagram/FFFFFF" alt="" class="w-6 h-6 object-contain" width="24" height="24">
                </a>
                <?php endif; ?>
                <?php if (!empty($hs['youtube'])): ?>
                <a href="<?= htmlspecialchars($hs['youtube']) ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-xl bg-cream-100/20 flex items-center justify-center hover:bg-cream-100/30 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-cream-100/30 focus:ring-offset-2 focus:ring-offset-sage-800 transition-all duration-200" aria-label="YouTube">
                    <img src="https://cdn.simpleicons.org/youtube/FFFFFF" alt="" class="w-6 h-6 object-contain" width="24" height="24">
                </a>
                <?php endif; ?>
                    </div>
                    <?php if (!empty($footer2gisUrl) || !empty($footerYandexUrl)): ?>
                    <div class="flex gap-2">
                        <?php if (!empty($footer2gisUrl)): ?><a href="<?= htmlspecialchars($footer2gisUrl) ?>" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-cream-100/10 text-cream-100/90 text-base font-medium hover:bg-cream-100/20 hover:text-cream-100 transition-colors">2ГИС</a><?php endif; ?>
                        <?php if (!empty($footerYandexUrl)): ?><a href="<?= htmlspecialchars($footerYandexUrl) ?>" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-cream-100/10 text-cream-100/90 text-base font-medium hover:bg-cream-100/20 hover:text-cream-100 transition-colors">Яндекс</a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Нижняя полоса -->
            <div class="mt-10 pt-8 border-t border-cream-100/10">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-5 text-base text-cream-100/70">
                    <p class="text-center sm:text-left order-2 sm:order-1">© <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?>. Все права защищены.</p>
                    <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 order-1 sm:order-2" aria-label="Юридическая информация">
                        <?php foreach ($footerBottomLinks as $i => $link): ?>
                        <?php if ($i > 0): ?><span class="text-cream-100/30" aria-hidden="true">·</span><?php endif; ?>
                        <a href="<?= BASE_URL ?><?= htmlspecialchars(strpos($link['url'], '/') === 0 ? $link['url'] : '/' . $link['url']) ?>" class="hover:text-cream-100 transition-colors focus:outline-none focus:ring-2 focus:ring-cream-100/30 focus:ring-offset-2 focus:ring-offset-sage-800 rounded"><?= htmlspecialchars($link['title']) ?></a>
                        <?php endforeach; ?>
                        <span class="text-cream-100/30" aria-hidden="true">·</span>
                        <a href="<?= BASE_URL ?>/admin" class="text-cream-100/50 hover:text-cream-100 transition-colors focus:outline-none focus:ring-2 focus:ring-cream-100/30 focus:ring-offset-2 focus:ring-offset-sage-800 rounded">Админ</a>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <!-- Нижняя навигация (мобильное приложение) — видна только на экранах < 1280px -->
    <?php $navSlug = trim($slug ?? '', '/') ?: 'index'; ?>
    <nav class="mobile-bottom-nav xl:hidden" aria-label="Главное меню">
        <a href="<?= BASE_URL ?>/" class="mobile-bottom-nav__item<?= ($navSlug === 'index') ? ' mobile-bottom-nav__item--active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Главная</span>
        </a>
        <a href="<?= BASE_URL ?>/e-resource" class="mobile-bottom-nav__item<?= ($navSlug === 'e-resource') ? ' mobile-bottom-nav__item--active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>e-Resource</span>
        </a>
        <a href="<?= BASE_URL ?>/novosti" class="mobile-bottom-nav__item<?= ($navSlug === 'novosti' || str_starts_with($navSlug, 'novosti/')) ? ' mobile-bottom-nav__item--active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <span>Новости</span>
        </a>
        <a href="<?= BASE_URL ?>/ob-yavleniya" class="mobile-bottom-nav__item<?= ($navSlug === 'ob-yavleniya' || str_starts_with($navSlug, 'ob-yavleniya/')) ? ' mobile-bottom-nav__item--active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <span>Объявления</span>
        </a>
        <a href="<?= BASE_URL ?>/abiturientam" class="mobile-bottom-nav__item<?= str_starts_with($navSlug, 'abiturientam') || in_array($navSlug, ['pravila-priema', 'spetsialnosti', 'kak-postupit-v-kolledzh-abiturientu'], true) ? ' mobile-bottom-nav__item--active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span>Абитуриентам</span>
        </a>
        <a href="tel:<?= htmlspecialchars(preg_replace('/\D/', '', $footerPhone)) ?>" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>Позвонить</span>
        </a>
    </nav>

    <!-- Кнопка «Наверх» -->
    <button type="button" id="scroll-to-top" class="scroll-to-top" aria-label="Прокрутить наверх" title="Наверх">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <!-- ИИ-ассистент КВКИ -->
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/assistant.css?v=<?= file_exists(ROOT_PATH . '/assets/css/assistant.css') ? filemtime(ROOT_PATH . '/assets/css/assistant.css') : time() ?>">
    <script src="<?= BASE_URL ?>/assets/js/assistant.js?v=<?= file_exists(ROOT_PATH . '/assets/js/assistant.js') ? filemtime(ROOT_PATH . '/assets/js/assistant.js') : time() ?>"></script>

    <?php
    $footerHost = $_SERVER['HTTP_HOST'] ?? '';
    $footerIsLocalhost = ($footerHost === 'localhost' || $footerHost === '127.0.0.1' || str_starts_with($footerHost, 'localhost:') || str_starts_with($footerHost, '127.0.0.1:'));
    if (($slug ?? '') === 'index' && !empty($instagramPosts ?? []) && !$footerIsLocalhost): ?>
    <script async src="//www.instagram.com/embed.js"></script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function() {
            const heroSwiperEl = document.querySelector('.hero-swiper');
            if (heroSwiperEl) {
                new Swiper('.hero-swiper', {
                    loop: true,
                    autoplay: { delay: 5000, disableOnInteraction: false },
                    pagination: { el: '.hero-pagination', clickable: true },
                    navigation: {
                        nextEl: '.hero-next',
                        prevEl: '.hero-prev',
                    },
                    effect: 'fade',
                    fadeEffect: { crossFade: true },
                });
            }
        })();
        (function() {
            const partnersSwiperEl = document.querySelector('.partners-swiper');
            if (partnersSwiperEl) {
                const slidesCount = partnersSwiperEl.querySelectorAll('.swiper-slide').length;
                new Swiper('.partners-swiper', {
                    loop: slidesCount > 4,
                    speed: 7000,
                    allowTouchMove: false,
                    autoplay: {
                        delay: 1,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: false,
                    },
                    watchOverflow: true,
                    spaceBetween: 20,
                    slidesPerView: 1.2,
                    breakpoints: {
                        640: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 20 },
                        1280: { slidesPerView: 4, spaceBetween: 20 },
                    },
                });
            }
        })();
        (function() {
            const menuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('mobile-menu-overlay');
            const menuOpenIcon = document.querySelector('.menu-icon-open');
            const menuCloseIcon = document.querySelector('.menu-icon-close');

            function openMenu() {
                mobileMenu?.classList.add('is-open');
                overlay?.classList.add('is-visible');
                menuOpenIcon?.classList.add('hidden');
                menuCloseIcon?.classList.remove('hidden');
                menuBtn?.setAttribute('aria-label', 'Закрыть меню');
                menuBtn?.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }
            function closeMenu() {
                mobileMenu?.classList.remove('is-open');
                overlay?.classList.remove('is-visible');
                menuOpenIcon?.classList.remove('hidden');
                menuCloseIcon?.classList.add('hidden');
                menuBtn?.setAttribute('aria-label', 'Открыть меню');
                menuBtn?.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                document.querySelectorAll('.mobile-nav-children.is-expanded').forEach(el => el.classList.remove('is-expanded'));
                document.querySelectorAll('[data-accordion]').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
            }

            menuBtn?.addEventListener('click', function() {
                mobileMenu?.classList.contains('is-open') ? closeMenu() : openMenu();
            });
            overlay?.addEventListener('click', closeMenu);
            document.querySelectorAll('[data-close-menu]').forEach(el => el.addEventListener('click', closeMenu));

            document.querySelectorAll('[data-accordion]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-accordion');
                    const panel = document.getElementById(id);
                    if (!panel) return;
                    const isExpanded = panel.classList.toggle('is-expanded');
                    this.setAttribute('aria-expanded', isExpanded);
                    this.querySelector('.accordion-chevron')?.classList.toggle('rotate-180', isExpanded);
                });
            });

            document.querySelectorAll('.submenu-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.querySelector('.submenu-children')?.classList.remove('hidden');
                });
                item.addEventListener('mouseleave', function() {
                    this.querySelector('.submenu-children')?.classList.add('hidden');
                });
            });
        })();
        (function() {
            const header = document.getElementById('site-header');
            if (!header) return;
            const thresholdDown = 80;
            const thresholdUp = 20;
            function onScroll() {
                const scrollY = window.scrollY || document.documentElement.scrollTop;
                if (header.classList.contains('scrolled')) {
                    if (scrollY < thresholdUp) header.classList.remove('scrolled');
                } else {
                    if (scrollY > thresholdDown) header.classList.add('scrolled');
                }
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
        (function() {
            const btn = document.getElementById('scroll-to-top');
            if (!btn) return;
            const threshold = 300;
            function toggle() {
                const y = window.scrollY || document.documentElement.scrollTop;
                btn.classList.toggle('scroll-to-top--visible', y > threshold);
            }
            btn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            window.addEventListener('scroll', toggle, { passive: true });
            toggle();
        })();
        (function() {
            const mapEl = document.getElementById('footer-map');
            if (!mapEl || typeof L === 'undefined') return;
            const kvkiLat = 49.8027, kvkiLng = 73.0876;
            const map = L.map('footer-map', { zoomControl: false }).setView([kvkiLat, kvkiLng], 17);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(map);
            L.control.zoom({ position: 'bottomright' }).addTo(map);
            const markerHtml = '<div class="footer-map-marker"><div class="footer-map-marker-pulse"></div><div class="footer-map-marker-pin"></div></div>';
            const marker = L.marker([kvkiLat, kvkiLng], {
                icon: L.divIcon({
                    className: 'footer-map-marker-container',
                    html: markerHtml,
                    iconSize: [48, 48],
                    iconAnchor: [24, 48]
                })
            }).addTo(map);
            marker.bindPopup('<strong>КВКИ</strong><br>ул. Кирпичная 8, г. Караганда');
        })();
        (function() {
            const showMoreBtn = document.getElementById('instagram-show-more');
            if (!showMoreBtn) return;
            const hiddenItems = document.querySelectorAll('.js-instagram-item[style*="display:none"]');
            if (!hiddenItems.length) return;
            const viewAllLink = document.getElementById('instagram-view-all-link');

            showMoreBtn.addEventListener('click', function() {
                hiddenItems.forEach(item => {
                    item.style.display = '';
                });
                showMoreBtn.style.display = 'none';
                if (viewAllLink) {
                    viewAllLink.style.display = 'inline-flex';
                }
            }, { once: true });
        })();
        (function() {
            const modal = document.getElementById('instagram-post-modal');
            if (!modal) return;
            const modalContent = document.getElementById('instagram-modal-content');
            const modalLink = document.getElementById('instagram-modal-link');
            const openCards = document.querySelectorAll('.js-instagram-open');
            const closeBtns = modal.querySelectorAll('[data-instagram-modal-close]');
            const body = document.body;

            function buildEmbedUrl(postUrl) {
                try {
                    const url = new URL(postUrl);
                    const pathname = url.pathname.endsWith('/') ? url.pathname : `${url.pathname}/`;
                    return `${url.origin}${pathname}embed/captioned/`;
                } catch (e) {
                    return '';
                }
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                modalContent.innerHTML = '';
                body.style.overflow = '';
            }

            function openModal(payload) {
                const type = payload.postType || 'embed';
                const caption = payload.postCaption || '';
                const postUrl = payload.postUrl || '';
                const postImage = payload.postImage || '';

                if (type === 'demo') {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'space-y-4';

                    if (postImage) {
                        const image = document.createElement('img');
                        image.src = postImage;
                        image.alt = 'Пост Instagram';
                        image.className = 'w-full rounded-2xl object-cover max-h-[70vh]';
                        wrapper.appendChild(image);
                    }

                    if (caption) {
                        const text = document.createElement('p');
                        text.className = 'text-ink-700 leading-relaxed whitespace-pre-line';
                        text.textContent = caption;
                        wrapper.appendChild(text);
                    }
                    modalContent.innerHTML = '';
                    modalContent.appendChild(wrapper);
                } else {
                    const embedUrl = buildEmbedUrl(postUrl);
                    modalContent.innerHTML = '';
                    if (embedUrl) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'w-full';
                        const iframe = document.createElement('iframe');
                        iframe.src = embedUrl;
                        iframe.className = 'w-full rounded-2xl border border-black/10';
                        iframe.style.minHeight = '720px';
                        iframe.loading = 'lazy';
                        iframe.setAttribute('allowfullscreen', '');
                        wrapper.appendChild(iframe);

                        if (caption) {
                            const text = document.createElement('p');
                            text.className = 'text-ink-600 text-sm mt-3';
                            text.textContent = caption;
                            wrapper.appendChild(text);
                        }
                        modalContent.appendChild(wrapper);
                    } else {
                        const text = document.createElement('p');
                        text.className = 'text-ink-600';
                        text.textContent = 'Не удалось открыть публикацию в модальном окне.';
                        modalContent.appendChild(text);
                    }
                }

                if (modalLink) {
                    modalLink.href = postUrl || modalLink.href;
                }
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                body.style.overflow = 'hidden';
            }

            openCards.forEach(card => {
                card.addEventListener('click', function(event) {
                    event.preventDefault();
                    openModal(this.dataset);
                });
            });

            closeBtns.forEach(btn => {
                btn.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        })();
    </script>
