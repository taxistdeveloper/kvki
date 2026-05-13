<?php
/**
 * Шаблон «Страница в разработке»
 * Показывается когда страница отключена в админке (is_active = 0)
 */
$ucTitle = trim((string)($pageData['title'] ?? $pageTitle ?? 'Страница'));
$base = BASE_URL ?? '/kvki';
$siteName = SITE_NAME ?? 'КВКИ';
$announcementsSlug = defined('ANNOUNCEMENTS_SLUG') ? ANNOUNCEMENTS_SLUG : 'obyavleniya';
$phone = '+77212441265';
if (class_exists('HeaderSettings')) {
    $hs = HeaderSettings::load();
    $phone = preg_replace('/\D/', '', $hs['phone_primary'] ?? $hs['phone_home'] ?? $phone);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1f59b0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="robots" content="noindex, nofollow">
    <title>Страница в разработке — <?= htmlspecialchars($siteName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: { 50: '#f5f5f5', 100: '#f5f5f5', 200: '#f5f5f5' },
                        sage: { 400: '#1f59b0', 500: '#1f59b0', 600: '#1f59b0', 700: '#1f59b0', 800: '#1f59b0' },
                        ink: { 500: '#64748b', 600: '#516272', 700: '#3a4b5a', 800: '#263544' }
                    },
                    fontFamily: { sans: ['Montserrat', 'system-ui', 'sans-serif'] },
                    boxShadow: {
                        soft: '0 10px 24px rgba(15, 23, 42, 0.08)',
                        card: '0 18px 40px rgba(15, 23, 42, 0.12)'
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css?v=<?= defined('ROOT_PATH') && file_exists(ROOT_PATH . '/assets/css/style.css') ? filemtime(ROOT_PATH . '/assets/css/style.css') : time() ?>">
    <style>
        .uc-scene {
            background:
                radial-gradient(circle at 12% 18%, rgba(31, 89, 176, 0.14), transparent 34%),
                radial-gradient(circle at 88% 12%, rgba(31, 89, 176, 0.1), transparent 30%),
                radial-gradient(circle at 50% 100%, rgba(31, 89, 176, 0.08), transparent 42%),
                linear-gradient(180deg, #f8fafc 0%, #f5f5f5 48%, #eef2f7 100%);
        }
        .uc-grid {
            background-image:
                linear-gradient(rgba(31, 89, 176, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(31, 89, 176, 0.05) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(circle at center, #000 38%, transparent 88%);
        }
        .uc-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .uc-illustration {
            filter: drop-shadow(0 18px 36px rgba(31, 89, 176, 0.16));
        }
        .uc-progress-track {
            background: linear-gradient(90deg, rgba(31, 89, 176, 0.08), rgba(31, 89, 176, 0.16), rgba(31, 89, 176, 0.08));
            background-size: 220% 100%;
        }
        @keyframes uc-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes uc-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        @keyframes uc-pulse-ring {
            0% { transform: scale(0.92); opacity: 0.55; }
            70% { transform: scale(1.18); opacity: 0; }
            100% { transform: scale(1.18); opacity: 0; }
        }
        .uc-float { animation: uc-float 5s ease-in-out infinite; }
        .uc-shimmer { animation: uc-shimmer 2.8s linear infinite; }
        .uc-pulse-ring { animation: uc-pulse-ring 2.4s ease-out infinite; }
        @media (prefers-reduced-motion: reduce) {
            .uc-float,
            .uc-shimmer,
            .uc-pulse-ring { animation: none; }
        }
    </style>
</head>
<body class="font-sans antialiased text-ink-800 min-h-screen flex flex-col has-mobile-nav uc-scene">
    <div class="pointer-events-none absolute inset-0 uc-grid" aria-hidden="true"></div>

    <header class="relative z-10 px-4 sm:px-6 pt-6">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-4">
            <a href="<?= $base ?>/" class="inline-flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-sage-600 text-white font-bold text-xs flex items-center justify-center shadow-soft group-hover:bg-sage-700 transition-colors">
                    КВКИ
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-sm font-semibold text-ink-800 leading-tight">Карагандинский высший</div>
                    <div class="text-xs text-ink-500">колледж инжиниринга</div>
                </div>
            </a>
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 border border-sage-400/20 text-xs font-semibold text-sage-700 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="uc-pulse-ring absolute inline-flex h-full w-full rounded-full bg-sage-500/40"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-sage-600"></span>
                </span>
                Раздел в работе
            </span>
        </div>
    </header>

    <main class="relative z-10 flex-1 flex items-center justify-center px-4 sm:px-6 py-10 sm:py-14">
        <div class="w-full max-w-3xl">
            <div class="uc-card uc-illustration rounded-[28px] border border-white/70 shadow-card overflow-hidden">
                <div class="h-1.5 uc-progress-track uc-shimmer" aria-hidden="true"></div>

                <div class="px-6 sm:px-10 lg:px-12 py-10 sm:py-12 text-center">
                    <div class="uc-float mx-auto mb-8 relative w-36 h-36">
                        <div class="absolute inset-0 rounded-[2rem] bg-gradient-to-br from-sage-500/10 to-sage-600/5 border border-sage-400/15"></div>
                        <div class="absolute inset-3 rounded-[1.6rem] bg-white border border-sage-400/10 flex items-center justify-center">
                            <svg class="w-16 h-16 text-sage-600" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                <rect x="10" y="14" width="44" height="36" rx="6" stroke="currentColor" stroke-width="2.2" opacity="0.35"/>
                                <path d="M18 24H46M18 32H38M18 40H42" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" opacity="0.55"/>
                                <path d="M32 8L44 18H38V26H26V18H20L32 8Z" fill="currentColor" opacity="0.9"/>
                                <circle cx="48" cy="46" r="9" stroke="currentColor" stroke-width="2.2"/>
                                <path d="M48 42V46L51 49" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.18em] text-sage-700 mb-3">Скоро откроем</p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-ink-800 tracking-tight leading-tight mb-4">
                        Страница в <span class="text-sage-600">разработке</span>
                    </h1>

                    <?php if ($ucTitle !== ''): ?>
                    <div class="inline-flex items-center gap-2 max-w-full px-4 py-2 rounded-2xl bg-cream-100 border border-cream-200 text-sm text-ink-600 mb-4">
                        <svg class="w-4 h-4 text-sage-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="truncate">«<?= htmlspecialchars($ucTitle) ?>»</span>
                    </div>
                    <?php endif; ?>

                    <p class="text-ink-600 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto mb-8">
                        Мы готовим материалы для этого раздела. Загляните позже или перейдите в другие разделы сайта.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-2xl mx-auto mb-10 text-left">
                        <div class="rounded-2xl border border-cream-200 bg-white/70 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-wide text-ink-500 mb-1">Контент</div>
                            <div class="h-1.5 rounded-full bg-sage-600/80"></div>
                        </div>
                        <div class="rounded-2xl border border-sage-400/25 bg-sage-600/5 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-wide text-sage-700 mb-1 font-semibold">Верстка</div>
                            <div class="h-1.5 rounded-full uc-progress-track uc-shimmer"></div>
                        </div>
                        <div class="rounded-2xl border border-cream-200 bg-white/70 px-4 py-3">
                            <div class="text-[11px] uppercase tracking-wide text-ink-500 mb-1">Публикация</div>
                            <div class="h-1.5 rounded-full bg-cream-200"></div>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                        <a href="<?= $base ?>/" class="inline-flex items-center justify-center gap-2 min-h-[52px] px-7 py-3 rounded-2xl bg-sage-600 text-white font-semibold shadow-soft hover:bg-sage-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            На главную
                        </a>
                        <a href="javascript:history.back()" class="inline-flex items-center justify-center gap-2 min-h-[52px] px-7 py-3 rounded-2xl bg-white border border-sage-400/25 text-sage-700 font-semibold hover:bg-cream-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Назад
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap justify-center gap-3 text-sm">
                <a href="<?= $base ?>/<?= htmlspecialchars($announcementsSlug) ?>" data-announcements-nav class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/75 border border-cream-200 text-ink-600 hover:text-sage-700 hover:border-sage-400/30 transition-colors">
                    Объявления
                </a>
                <a href="<?= $base ?>/abiturientam" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/75 border border-cream-200 text-ink-600 hover:text-sage-700 hover:border-sage-400/30 transition-colors">
                    Абитуриентам
                </a>
                <a href="<?= $base ?>/o-nas" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/75 border border-cream-200 text-ink-600 hover:text-sage-700 hover:border-sage-400/30 transition-colors">
                    О колледже
                </a>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/announcement-modal.php'; ?>

    <nav class="mobile-bottom-nav xl:hidden relative z-20" aria-label="Главное меню">
        <a href="<?= $base ?>/" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Главная</span>
        </a>
        <a href="<?= $base ?>/o-nas" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>О колледже</span>
        </a>
        <a href="<?= $base ?>/<?= htmlspecialchars($announcementsSlug) ?>" data-announcements-nav class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <span>Объявления</span>
        </a>
        <a href="<?= $base ?>/postuplenie" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Поступление</span>
        </a>
        <a href="tel:<?= htmlspecialchars($phone) ?>" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>Позвонить</span>
        </a>
    </nav>
    <script>window.BASE_URL = <?= json_encode($base) ?>; window.KVKI_ANNOUNCEMENTS_SLUG = <?= json_encode($announcementsSlug) ?>;</script>
    <script src="<?= htmlspecialchars($base) ?>/assets/js/announcements.js?v=<?= file_exists(ROOT_PATH . '/assets/js/announcements.js') ? filemtime(ROOT_PATH . '/assets/js/announcements.js') : time() ?>"></script>
</body>
</html>
