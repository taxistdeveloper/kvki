<?php
/**
 * Минимальная страница 500 — используется при фатальных ошибках PHP,
 * когда config или index.php не загружаются (ErrorDocument 500)
 */
http_response_code(500);
$baseUrl = defined('BASE_URL') ? BASE_URL : '/kvki';
$siteName = defined('SITE_NAME') ? SITE_NAME : 'КВКИ';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Ошибка сервера — <?= htmlspecialchars($siteName) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: { 100: '#faf8f5', 50: '#fefdfb' },
                        sage: { 500: '#5a7d5a', 600: '#4a6d4a', 700: '#3d5c3d' },
                        ink: { 600: '#4a5568', 800: '#1a202c' }
                    },
                    fontFamily: { sans: ['Nunito', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-cream-100 text-ink-800 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <div class="w-24 h-24 mx-auto mb-6 rounded-3xl bg-cream-50 border-2 border-sage-500/30 flex items-center justify-center">
            <svg class="w-12 h-12 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="text-7xl font-extrabold text-sage-600/90 mb-4">500</div>
        <h1 class="text-2xl font-bold text-ink-800 mb-3">Ошибка сервера</h1>
        <p class="text-ink-600 mb-8">Временная неполадка. Мы уже работаем над исправлением.</p>
        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="inline-flex items-center px-8 py-4 bg-sage-600 text-white font-semibold rounded-2xl hover:bg-sage-700 transition-colors">
            На главную
        </a>
    </div>
</body>
</html>
