<?php
/**
 * Класс для работы с меню навигации
 */
class Menu
{
    private array $menu = [];

    public function __construct()
    {
        $this->loadMenu();
    }

    private function loadMenu(): void
    {
        $menuFile = ROOT_PATH . '/data/menu.json';
        if (file_exists($menuFile)) {
            $json = file_get_contents($menuFile);
            $data = json_decode($json, true);
            $this->menu = $data['menu'] ?? [];
        } else {
            $this->menu = $this->getDefaultMenu();
        }
    }

    private function getDefaultMenu(): array
    {
        return [
            [
                'title' => 'О нас',
                'children' => [
                    ['title' => 'Блог директора'],
                    ['title' => 'Администрация'],
                    ['title' => 'История колледжа'],
                    ['title' => 'Методическая работа'],
                    [
                        'title' => 'Воспитательная работа',
                        'children' => [
                            ['title' => 'Цель и направление'],
                            ['title' => 'Поликлиника'],
                            ['title' => 'СПП'],
                            ['title' => 'Социально‑психологическая служба'],
                            ['title' => 'Антикоррупционный комплекс'],
                            ['title' => 'План воспитательной работы'],
                            ['title' => 'План воспитательной работы общежития'],
                            ['title' => 'План работы по профилактике булинга'],
                        ],
                    ],
                    [
                        'title' => 'Производственная работа',
                        'children' => [
                            ['title' => 'Трудоустройства'],
                            ['title' => 'Социальные партнеры'],
                        ],
                    ],
                    [
                        'title' => 'Отделения',
                        'children' => [
                            ['title' => 'Строительное техническое отделение'],
                            ['title' => 'Архитектуры, дизайна и декоративно‑прикладного искусства'],
                            ['title' => 'Профессионально‑техническим'],
                        ],
                    ],
                    [
                        'title' => 'ЦМК',
                        'children' => [
                            ['title' => 'Общеобразовательных и социально‑гуманитарных дисциплин'],
                            ['title' => 'Истории и социально‑гуманитарных дисциплин'],
                            ['title' => 'Информационных технологий'],
                            ['title' => 'Языковой подготовки и социально‑гуманитарных дисциплин'],
                            ['title' => 'Строительного профиля'],
                            ['title' => 'Дизайна и декоративно‑прикладного искусства'],
                        ],
                    ],
                    [
                        'title' => 'Гос услуги',
                        'children' => [
                            ['title' => 'Государственные символы'],
                            ['title' => 'Перечень государственных услуг'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Жас маман',
                'children' => [
                    [
                        'title' => 'Образовательные программы',
                        'children' => [
                            [
                                'title' => 'Строительные специальности',
                                'children' => [
                                    ['title' => 'Гражданское строительство'],
                                    ['title' => 'Промышленное строительство'],
                                    ['title' => 'Дорожное строительство'],
                                    ['title' => 'Мостостроение'],
                                ],
                            ],
                            [
                                'title' => 'Архитектура и дизайн',
                                'children' => [
                                    ['title' => 'Жилой дизайн'],
                                    ['title' => 'Коммерческий дизайн'],
                                    ['title' => 'Ландшафтный дизайн'],
                                    ['title' => 'Интерьерный дизайн'],
                                ],
                            ],
                            [
                                'title' => 'Технические специальности',
                                'children' => [
                                    ['title' => 'Электротехника'],
                                    ['title' => 'Механика'],
                                    ['title' => 'Автоматизация'],
                                    ['title' => 'Робототехника'],
                                ],
                            ],
                            [
                                'title' => 'Управление и экономика',
                                'children' => [
                                    ['title' => 'Управление проектами'],
                                    ['title' => 'Управление строительством'],
                                    ['title' => 'Финансовый менеджмент'],
                                    ['title' => 'Управление персоналом'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Карьерные возможности',
                        'children' => [
                            [
                                'title' => 'Рынок труда',
                                'children' => [
                                    ['title' => 'Местный рынок'],
                                    ['title' => 'Национальный рынок'],
                                    ['title' => 'Международный рынок'],
                                    ['title' => 'Удаленная работа'],
                                ],
                            ],
                            [
                                'title' => 'Зарплатные ожидания',
                                'children' => [
                                    ['title' => 'Начальный уровень'],
                                    ['title' => 'Средний уровень'],
                                    ['title' => 'Старший уровень'],
                                    ['title' => 'Руководящий уровень'],
                                ],
                            ],
                            [
                                'title' => 'Карьерный рост',
                                'children' => [
                                    ['title' => 'Продвижение по службе'],
                                    ['title' => 'Развитие навыков'],
                                    ['title' => 'Сертификация'],
                                    ['title' => 'Предпринимательство'],
                                ],
                            ],
                        ],
                    ],
                    ['title' => 'Стажировки'],
                    [
                        'title' => 'Наставничество',
                        'children' => [
                            [
                                'title' => 'Наши наставники',
                                'children' => [
                                    ['title' => 'Старшие наставники'],
                                    ['title' => 'Отраслевые наставники'],
                                    ['title' => 'Наставники‑выпускники'],
                                    ['title' => 'Приглашенные наставники'],
                                ],
                            ],
                            [
                                'title' => 'Программы наставничества',
                                'children' => [
                                    ['title' => 'Индивидуальное наставничество'],
                                    ['title' => 'Групповое наставничество'],
                                    ['title' => 'Взаимное наставничество'],
                                    ['title' => 'Виртуальное наставничество'],
                                ],
                            ],
                            [
                                'title' => 'Истории успеха',
                                'children' => [
                                    ['title' => 'Успехи студентов'],
                                    ['title' => 'Успехи выпускников'],
                                    ['title' => 'Смена карьеры'],
                                    ['title' => 'Бизнес‑успехи'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Студентам',
                'children' => [
                    ['title' => 'SKILLS паспорт'],
                    ['title' => 'Центр обслуживание студентов'],
                    ['title' => 'Кружки и секции'],
                    ['title' => 'Факультативы'],
                ],
            ],
            [
                'title' => 'Абитуриентам',
                'children' => [
                    ['title' => 'Информация'],
                    ['title' => 'Правила приема'],
                    ['title' => 'Специальности'],
                ],
            ],
            [
                'title' => 'УМО',
                'children' => [
                    ['title' => 'УМО'],
                ],
            ],
        ];
    }

    public function getMenu(): array
    {
        return $this->menu;
    }

    /**
     * Сохранить меню в JSON-файл
     */
    public static function saveMenu(array $menu): bool
    {
        $menuFile = ROOT_PATH . '/data/menu.json';
        $dir = dirname($menuFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $json = json_encode(['menu' => $menu], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return file_put_contents($menuFile, $json) !== false;
    }

    public function getSlug(string $title): string
    {
        $slug = mb_strtolower($title);
        $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Собирает все валидные slug-пути из меню (для проверки 404)
     */
    private function collectSlugsFromMenu(array $items, string $prefix = ''): array
    {
        $slugs = [];
        foreach ($items as $item) {
            $pathSlug = !empty($item['slug'])
                ? ltrim($item['slug'], '/')
                : (!empty($item['url']) && !str_starts_with($item['url'] ?? '', 'http') && ($item['url'] ?? '') !== '#'
                    ? ltrim($item['url'], '/')
                    : $this->getSlug($item['title']));
            $fullSlug = $prefix ? $prefix . '/' . $pathSlug : $pathSlug;
            if ($fullSlug !== '' && $fullSlug !== '#') {
                $slugs[] = $fullSlug;
            }
            if (!empty($item['children'])) {
                $slugs = array_merge(
                    $slugs,
                    $this->collectSlugsFromMenu($item['children'], $fullSlug)
                );
            }
        }
        return $slugs;
    }

    /**
     * Проверяет, есть ли путь (slug) в структуре меню
     */
    public function isPathInMenu(string $slug): bool
    {
        $slug = trim($slug, '/');
        if ($slug === '') {
            return true; // index
        }
        $allSlugs = $this->collectSlugsFromMenu($this->menu);
        return in_array($slug, $allSlugs, true);
    }

    public function resolveSlugsToTitles(array $slugParts): array
    {
        $result = [];
        $items = $this->menu;
        foreach ($slugParts as $slug) {
            $found = null;
            foreach ($items as $item) {
                $itemSlug = $item['slug'] ?? $this->getSlug($item['title']);
                if ($itemSlug === $slug) {
                    $found = $item['title'];
                    $items = $item['children'] ?? [];
                    break;
                }
            }
            if ($found !== null) {
                $result[] = $found;
            } else {
                $result[] = str_replace('-', ' ', ucfirst($slug));
                break;
            }
        }
        return $result;
    }

    /**
     * Находит полный путь хлебных крошек для slug (в т.ч. плоского, например blog-direktora).
     * Возвращает массив ['title' => string, 'slug' => string]; «Главная» добавляется в шаблоне.
     */
    public function getBreadcrumbForSlug(string $slug): array
    {
        $slug = trim($slug, '/');
        if ($slug === '' || $slug === 'index') {
            return [['title' => 'Главная', 'slug' => '']];
        }
        $path = $this->findPathInMenu($this->menu, $slug, []);
        if ($path !== null) {
            return $path;
        }
        $parts = explode('/', $slug);
        $titles = $this->resolveSlugsToTitles($parts);
        return array_map(fn($t, $i) => ['title' => $t, 'slug' => $parts[$i] ?? ''], $titles, array_keys($titles));
    }

    private function findPathInMenu(array $items, string $targetSlug, array $pathSoFar): ?array
    {
        foreach ($items as $item) {
            $itemSlug = $item['slug'] ?? $this->getSlug($item['title']);
            $currentPath = array_merge($pathSoFar, [['title' => $item['title'], 'slug' => $itemSlug]]);
            if ($itemSlug === $targetSlug) {
                return $currentPath;
            }
            if (!empty($item['children'])) {
                $found = $this->findPathInMenu($item['children'], $targetSlug, $currentPath);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        return null;
    }

    public function buildMenuWithUrls(array $items, array $parentPath = []): array
    {
        $result = [];
        foreach ($items as $item) {
            $path = array_merge($parentPath, [$item['title']]);
            $slug = !empty($item['slug']) ? ltrim($item['slug'], '/') : $this->getSlug($item['title']);

            // Как в WordPress: приоритет slug (страница) → url (произвольная ссылка) → путь из меню
            if (!empty($item['slug'])) {
                $url = BASE_URL . '/' . ltrim($item['slug'], '/');
            } elseif (!empty($item['url'])) {
                $url = str_starts_with($item['url'], 'http') ? $item['url'] : (BASE_URL . '/' . ltrim($item['url'], '/'));
            } else {
                $fullSlug = implode('/', array_map(fn($t) => $this->getSlug($t), $path));
                $url = BASE_URL . '/' . $fullSlug;
            }

            $node = [
                'title' => $item['title'],
                'url' => $url,
                'slug' => $slug,
                'path' => $path,
            ];
            if (!empty($item['children'])) {
                $node['children'] = $this->buildMenuWithUrls($item['children'], $path);
            }
            $result[] = $node;
        }
        return $result;
    }

    /**
     * Возвращает данные для сайдбар-меню: заголовок раздела и пункты (для текущего slug).
     * Если страница входит в раздел с подпунктами — возвращает массив, иначе null.
     */
    public function getSidebarItems(string $slug): ?array
    {
        $slug = trim($slug, '/');
        if ($slug === '' || $slug === 'index') {
            return null;
        }
        $menuWithUrls = $this->buildMenuWithUrls($this->menu);
        $found = $this->findSidebarParent($menuWithUrls, $slug, null);
        return $found;
    }

    /**
     * Ищет родительский раздел, у которого есть дети (для отображения в сайдбаре).
     */
    private function findSidebarParent(array $items, string $targetSlug, ?array $parentData): ?array
    {
        foreach ($items as $item) {
            if (empty($item['children'])) {
                continue;
            }
            $itemSlug = $item['slug'] ?? $this->getSlug($item['title']);
            $fullSlug = $this->buildFullSlugFromPath($item['path'] ?? [$item['title']]);

            $childrenForSidebar = [];
            foreach ($item['children'] as $child) {
                $childSlug = $child['slug'] ?? $this->getSlug($child['title']);
                $childFullSlug = $this->buildFullSlugFromPath($child['path'] ?? [$item['title'], $child['title']]);
                $childUrl = $child['url'] ?? (BASE_URL . '/' . $childFullSlug);
                $isActive = $this->slugMatches($targetSlug, $childFullSlug) || $this->slugMatches($targetSlug, $childSlug)
                    || str_starts_with($targetSlug, $childFullSlug . '/') || str_starts_with($childFullSlug, $targetSlug . '/');
                $childrenForSidebar[] = [
                    'title' => $child['title'],
                    'url' => $childUrl,
                    'isActive' => $isActive,
                ];
            }
            $sectionData = [
                'sectionTitle' => $item['title'],
                'sectionUrl' => ($item['url'] !== '#' && !empty($item['url'])) ? $item['url'] : (BASE_URL . '/' . $itemSlug),
                'items' => $childrenForSidebar,
            ];
            if ($this->slugMatches($targetSlug, $fullSlug) || str_starts_with($targetSlug, $fullSlug . '/') || str_starts_with($fullSlug, $targetSlug . '/')) {
                return $sectionData;
            }
            if ($this->slugContainedInPaths($item['children'], $targetSlug)) {
                $deeper = $this->findSidebarParent($item['children'], $targetSlug, $sectionData);
                return $deeper ?? $sectionData;
            }
        }
        return $parentData;
    }

    private function slugMatches(string $target, string $candidate): bool
    {
        return $target === $candidate || $target === trim($candidate, '/');
    }

    private function slugContainedInPaths(array $items, string $targetSlug): bool
    {
        foreach ($items as $item) {
            $path = $item['path'] ?? [];
            $fullSlug = $this->buildFullSlugFromPath($path);
            $itemSlug = $item['slug'] ?? $this->getSlug(end($path) ?: $item['title']);
            if ($this->slugMatches($targetSlug, $fullSlug) || $this->slugMatches($targetSlug, $itemSlug)
                || str_starts_with($targetSlug, $fullSlug . '/') || str_starts_with($targetSlug, $itemSlug . '/')) {
                return true;
            }
            if (!empty($item['children']) && $this->slugContainedInPaths($item['children'], $targetSlug)) {
                return true;
            }
        }
        return false;
    }

    private function buildFullSlugFromPath(array $path): string
    {
        if (empty($path)) {
            return '';
        }
        $titles = is_array($path[0] ?? null) ? array_column($path, 'title') : $path;
        return implode('/', array_map([$this, 'getSlug'], $titles));
    }
}
