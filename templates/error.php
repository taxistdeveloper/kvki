<?php
/**
 * Шаблон страниц ошибок (404, 500)
 * $errorCode, $errorTitle, $errorMessage, $errorDescription
 */
$errorCode = $errorCode ?? 404;
$errorTitle = $errorTitle ?? 'Страница не найдена';
$errorMessage = $errorMessage ?? 'Запрашиваемая страница не существует или была перемещена.';
$errorDescription = $errorDescription ?? 'Проверьте адрес или вернитесь на главную.';
$is500 = ($errorCode === 500);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1f59b0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?= (int)$errorCode ?> — <?= htmlspecialchars($errorTitle) ?> — <?= SITE_NAME ?? 'КВКИ' ?></title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: { 50: '#f5f5f5', 100: '#f5f5f5', 200: '#f5f5f5' },
                        sage: { 400: '#1f59b0', 500: '#1f59b0', 600: '#1f59b0', 700: '#1f59b0', 800: '#1f59b0', 900: '#1f59b0' },
                        edu: { 50: '#1f59b0', 100: '#1f59b0', 200: '#1f59b0', 400: '#1f59b0', 500: '#1f59b0', 600: '#1f59b0', 700: '#1f59b0', 800: '#1f59b0' },
                        ink: { 600: '#516272', 700: '#3a4b5a', 800: '#263544' }
                    },
                    fontFamily: { sans: ['Montserrat', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?? '/kvki' ?>/assets/css/style.css?v=<?= defined('ROOT_PATH') && file_exists(ROOT_PATH . '/assets/css/style.css') ? filemtime(ROOT_PATH . '/assets/css/style.css') : time() ?>">
    <style>
        .error-code { font-size: clamp(6rem, 18vw, 12rem); line-height: 1; letter-spacing: -0.04em; }
        .error-illustration { filter: drop-shadow(0 4px 24px rgba(31, 89, 176, 0.18)); }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .animate-float { animation: float 4s ease-in-out infinite; }
        @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.85; } }
        .animate-pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }
    </style>
</head>
<body class="font-sans antialiased bg-cream-100 text-ink-800 min-h-screen flex flex-col has-mobile-nav">
    <div class="flex-1 flex items-center justify-center px-4 py-16 relative">
        <div class="max-w-2xl w-full text-center relative">
            <!-- Декоративный фон -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10" aria-hidden="true">
                <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-sage-500/5 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-sage-500/5 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full border border-sage-400/10"></div>
            </div>

            <div class="relative">
                <!-- Иконка/иллюстрация -->
                <div class="mb-8 flex justify-center">
                    <?php if ($is500): ?>
                    <div class="error-illustration animate-pulse-soft w-28 h-28 rounded-3xl bg-cream-50 border-2 border-sage-400/30 flex items-center justify-center shadow-lg">
                        <svg class="w-14 h-14 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <?php else: ?>
                    <div class="error-illustration animate-float w-28 h-28 rounded-3xl bg-cream-50 border-2 border-sage-400/30 flex items-center justify-center shadow-lg">
                        <svg class="w-14 h-14 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Код ошибки -->
                <div class="error-code font-extrabold text-sage-600/90 mb-4"><?= (int)$errorCode ?></div>

                <!-- Заголовок -->
                <h1 class="text-2xl sm:text-3xl font-bold text-ink-800 mb-4"><?= htmlspecialchars($errorTitle) ?></h1>
                <p class="text-ink-600 text-lg mb-2"><?= htmlspecialchars($errorMessage) ?></p>
                <p class="text-ink-600/80 text-sm mb-10"><?= htmlspecialchars($errorDescription) ?></p>

                <!-- Кнопки -->
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="<?= BASE_URL ?? '/' ?>/" class="inline-flex items-center justify-center px-8 py-4 bg-sage-600 text-white font-semibold rounded-2xl hover:bg-sage-700 transition-colors shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        На главную
                    </a>
                    <a href="javascript:history.back()" class="inline-flex items-center justify-center px-8 py-4 bg-cream-50 border-2 border-sage-400/40 text-sage-700 font-semibold rounded-2xl hover:bg-cream-200 hover:border-sage-500/50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Назад
                    </a>
                </div>

                <!-- Логотип -->
                <div class="mt-16">
                    <a href="<?= BASE_URL ?? '/' ?>/" class="inline-flex items-center gap-3 group">
                        <div class="w-14 h-14 rounded-2xl bg-sage-600 flex items-center justify-center text-white font-bold text-sm group-hover:bg-sage-700 transition-colors">
                            КВКИ
                        </div>
                        <span class="text-ink-600 text-sm group-hover:text-sage-700 transition-colors">Карагандинский высший колледж инжиниринга</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $base = BASE_URL ?? '/kvki';
    $phone = '+77212441265';
    if (class_exists('HeaderSettings')) {
        $hs = HeaderSettings::load();
        $phone = preg_replace('/\D/', '', $hs['phone_primary'] ?? $hs['phone_home'] ?? $phone);
    }
    ?>
    <nav class="mobile-bottom-nav xl:hidden" aria-label="Главное меню">
        <a href="<?= $base ?>/" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Главная</span>
        </a>
        <a href="<?= $base ?>/novosti" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <span>Новости</span>
        </a>
        <a href="<?= $base ?>/ob-yavleniya" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <span>Объявления</span>
        </a>
        <a href="<?= $base ?>/abiturientam" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span>Абитуриентам</span>
        </a>
        <a href="tel:<?= $phone ?>" class="mobile-bottom-nav__item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>Позвонить</span>
        </a>
    </nav>
</body>
</html>
