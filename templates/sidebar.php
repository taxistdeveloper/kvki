<?php
/**
 * Сайдбар-меню раздела (как на страницах «О нас» и т.п.)
 * Ожидает: $sidebarData = ['sectionTitle' => string, 'sectionUrl' => string, 'items' => [{title, url, isActive}]]
 */
if (empty($sidebarData) || empty($sidebarData['items'])) {
    return;
}
$sectionTitle = $sidebarData['sectionTitle'];
$sectionUrl = $sidebarData['sectionUrl'] ?? '#';
$items = $sidebarData['items'];
?>
<aside class="sidebar-menu shrink-0 w-full lg:w-64" aria-label="Меню раздела">
    <nav class="rounded-2xl bg-white border border-cream-200 shadow-sm overflow-hidden">
        <a href="<?= htmlspecialchars($sectionUrl) ?>" class="block px-5 py-4 font-bold text-ink-800 text-base hover:text-sage-700 transition-colors">
            <?= htmlspecialchars($sectionTitle) ?>
        </a>
        <div class="border-t border-cream-200"></div>
        <ul class="py-2" role="list">
            <?php foreach ($items as $item): ?>
                <li>
                    <a href="<?= htmlspecialchars($item['url']) ?>"
                       class="flex items-center justify-between px-5 py-3 text-sm font-medium transition-colors <?= !empty($item['isActive'])
                           ? 'bg-sage-600 text-white hover:bg-sage-700'
                           : 'text-ink-700 hover:bg-cream-100 hover:text-sage-700' ?>">
                        <span><?= htmlspecialchars($item['title']) ?></span>
                        <svg class="w-4 h-4 shrink-0 <?= !empty($item['isActive']) ? 'text-white' : 'text-ink-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
