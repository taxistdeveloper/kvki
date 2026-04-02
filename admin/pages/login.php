<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password && adminLogin($username, $password)) {
        header('Location: ' . ADMIN_URL);
        exit;
    }
    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — КВКИ Админ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DM Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        cream: { 50: '#fefdfb', 100: '#faf8f5', 200: '#f5f1eb' },
                        sage: { 500: '#5a7d5a', 600: '#4a6d4a', 700: '#3d5c3d', 800: '#2d4a2d' },
                        ink: { 600: '#475569', 700: '#334155', 800: '#1e293b' }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-cream-100 via-cream-50 to-sage-50 flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-xl shadow-ink-900/5 border border-cream-200 overflow-hidden">
            <div class="p-8 sm:p-10">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-sage-600 flex items-center justify-center shadow-lg shadow-sage-600/25">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-ink-800">КВКИ Админ</h1>
                        <p class="text-ink-500 text-sm">Панель управления</p>
                    </div>
                </div>

                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl text-red-700 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="post" class="space-y-5">
                    <div>
                        <label for="username" class="block text-sm font-medium text-ink-700 mb-1.5">Логин</label>
                        <input type="text" id="username" name="username" required autofocus
                               class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/40 focus:border-sage-400 transition-all outline-none"
                               placeholder="Введите логин"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-ink-700 mb-1.5">Пароль</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:ring-2 focus:ring-sage-400/40 focus:border-sage-400 transition-all outline-none"
                               placeholder="Введите пароль">
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-sage-600 text-white font-semibold rounded-xl hover:bg-sage-700 active:scale-[0.99] transition-all shadow-md shadow-sage-600/20">
                        Войти
                    </button>
                </form>
            </div>
            <div class="px-8 py-4 bg-cream-50 border-t border-cream-200">
                <p class="text-center text-xs text-ink-500">По умолчанию: <code class="px-1.5 py-0.5 bg-cream-200 rounded text-ink-600">admin</code> / <code class="px-1.5 py-0.5 bg-cream-200 rounded text-ink-600">admin123</code></p>
            </div>
        </div>
    </div>
</body>
</html>
