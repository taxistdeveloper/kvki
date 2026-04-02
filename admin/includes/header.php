<?php
$adminTitle = $adminTitle ?? 'Админ-панель';
$currentAction = $action ?? 'dashboard';

// Навигация с иконками и группировкой
$navGroups = [
    'Главная' => [
        ['action' => 'dashboard', 'label' => 'Дашборд', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ],
    'Контент' => [
        ['action' => 'pages', 'label' => 'Страницы', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['action' => 'announcements', 'label' => 'Объявления', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.712-10.758a1.76 1.76 0 013.417-.592V12m0 0h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['action' => 'news', 'label' => 'Новости', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
        ['action' => 'slides', 'label' => 'Слайды', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['action' => 'partners', 'label' => 'Партнёры', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['action' => 'instagram', 'label' => 'Instagram', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14'],
    ],
    'Настройки' => [
        ['action' => 'menu', 'label' => 'Меню', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
        ['action' => 'header', 'label' => 'Header', 'icon' => 'M4 6h16M4 10h16M4 14h16'],
        ['action' => 'footer', 'label' => 'Footer', 'icon' => 'M4 6h16M4 12h16M4 18h7'],
        ['action' => 'e-resource', 'label' => 'e-Resource', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($adminTitle) ?> — КВКИ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DM Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        cream: { 50: '#fefdfb', 100: '#faf8f5', 200: '#f5f1eb', 300: '#ebe5dc' },
                        sage: { 50: '#f2f7f2', 100: '#e8f0e8', 200: '#c5d9c5', 300: '#8fa98f', 400: '#6b8e6b', 500: '#5a7d5a', 600: '#4a6d4a', 700: '#3d5c3d', 800: '#2d4a2d', 900: '#1e3520' },
                        ink: { 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a' }
                    },
                    boxShadow: {
                        'admin': '0 1px 3px 0 rgb(0 0 0 / 0.06), 0 1px 2px -1px rgb(0 0 0 / 0.06)',
                        'admin-lg': '0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05)',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.35s ease-out forwards; }
        .admin-sidebar-link { transition: all 0.15s ease; }
        .admin-sidebar-link:hover { background: rgba(255,255,255,0.08); }
        .admin-sidebar-link.active { background: rgba(255,255,255,0.15); }
        .admin-input { @apply w-full px-3 py-2.5 text-sm border border-cream-200 rounded-xl bg-white focus:ring-2 focus:ring-sage-400/40 focus:border-sage-400 transition-all outline-none; }
        .admin-btn-primary { @apply inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-sage-600 rounded-xl hover:bg-sage-700 active:scale-[0.98] transition-all shadow-sm; }
        .admin-btn-secondary { @apply inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-ink-600 bg-white border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-cream-300 transition-all; }
        .admin-btn-danger { @apply inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-700 bg-red-50 rounded-xl hover:bg-red-100 transition-all; }
        .admin-card { @apply bg-white rounded-2xl border border-cream-200 shadow-admin overflow-hidden; }
        .admin-table th { @apply text-left px-5 py-3.5 text-xs font-semibold text-ink-500 uppercase tracking-wider bg-cream-50; }
        .admin-table td { @apply px-5 py-3.5 text-sm text-ink-700 border-t border-cream-100; }
        .admin-table tbody tr { transition: background 0.15s ease; }
        .admin-table tbody tr:hover td { @apply bg-sage-50/40; }
    </style>
</head>
<body class="bg-cream-100 text-ink-800 min-h-screen antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-sage-800 flex-shrink-0 flex flex-col transform transition-transform duration-200 ease-out lg:transform-none -translate-x-full lg:translate-x-0">
            <div class="flex items-center justify-between h-16 px-5 border-b border-sage-700/80">
                <a href="<?= ADMIN_URL ?>" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-sage-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="font-bold text-white text-lg">КВКИ Админ</span>
                </a>
                <button type="button" id="admin-sidebar-close" class="lg:hidden p-2 rounded-lg text-sage-300 hover:bg-sage-700 hover:text-white" aria-label="Закрыть меню">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
                <?php foreach ($navGroups as $groupLabel => $items): ?>
                <div>
                    <p class="px-3 mb-2 text-xs font-semibold text-sage-400 uppercase tracking-wider"><?= htmlspecialchars($groupLabel) ?></p>
                    <div class="space-y-0.5">
                        <?php foreach ($items as $item): ?>
                        <a href="<?= ADMIN_URL ?><?= $item['action'] === 'dashboard' ? '' : '/' . $item['action'] ?>" class="admin-sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sage-100 <?= $currentAction === $item['action'] ? 'active' : '' ?>">
                            <svg class="w-5 h-5 flex-shrink-0 text-sage-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/></svg>
                            <span class="font-medium"><?= htmlspecialchars($item['label']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </nav>
            <div class="p-3 border-t border-sage-700/80 space-y-0.5">
                <a href="<?= BASE_URL ?>/" target="_blank" class="admin-sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sage-300 text-sm hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    На сайт
                </a>
                <a href="<?= ADMIN_URL ?>/logout" class="admin-sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sage-300 text-sm hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Выход
                </a>
            </div>
        </aside>

        <!-- Overlay для мобильного меню -->
        <div id="admin-sidebar-overlay" class="fixed inset-0 bg-ink-900/50 z-30 opacity-0 pointer-events-none transition-opacity duration-200 lg:hidden" aria-hidden="true"></div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="sticky top-0 z-20 flex items-center h-14 px-4 lg:px-8 bg-white/95 backdrop-blur-sm border-b border-cream-200 shadow-admin">
                <button type="button" id="admin-sidebar-toggle" class="lg:hidden p-2 -ml-2 rounded-lg text-ink-600 hover:bg-cream-100" aria-label="Открыть меню">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 min-w-0 ml-2">
                    <h1 class="text-lg font-semibold text-ink-800 truncate"><?= htmlspecialchars($adminTitle) ?></h1>
                    <p class="text-xs text-ink-500 hidden sm:block">Панель управления КВКИ</p>
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-8 overflow-auto">
