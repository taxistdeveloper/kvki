<?php
/**
 * Класс страницы - получение контента из файла, БД или шаблонов
 * При сохранении в админке создаётся файл content/pages/{slug}.html для редактирования в IDE
 */
class Page
{
    private const CONTENT_DIR = 'content/pages';

    private ?PDO $db = null;
    private Menu $menu;

    public function __construct()
    {
        $this->menu = new Menu();
    }

    private function getDb(): ?PDO
    {
        if ($this->db === null) {
            $this->db = Database::tryGetInstance();
        }
        return $this->db;
    }

    /**
     * Безопасный slug для пути к файлу
     */
    private static function getSafeSlug(string $slug): string
    {
        $safeSlug = str_replace(['..', "\0"], '', trim($slug, '/'));
        return preg_replace('/[^a-zA-Zа-яА-ЯёЁ0-9\-_\/]/u', '', $safeSlug);
    }

    /**
     * Путь к файлу контента по slug (например o-nas/istoriya → content/pages/o-nas/istoriya.html)
     */
    public static function getContentFilePath(string $slug): string
    {
        return ROOT_PATH . '/' . self::CONTENT_DIR . '/' . self::getSafeSlug($slug) . '.html';
    }

    /**
     * Путь к PHP-шаблону (content/pages/{slug}.php) — сливается с сайтом через page-layout
     */
    public static function getTemplateFilePath(string $slug): string
    {
        return ROOT_PATH . '/' . self::CONTENT_DIR . '/' . self::getSafeSlug($slug) . '.php';
    }

    /**
     * Есть ли PHP-шаблон для страницы
     */
    public static function hasTemplateFile(string $slug): bool
    {
        $file = self::getTemplateFilePath($slug);
        return file_exists($file) && is_readable($file);
    }

    /**
     * Сохранить контент в файл (создаёт директории при необходимости)
     */
    public static function saveContentToFile(string $slug, string $content): bool
    {
        $file = self::getContentFilePath($slug);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return file_put_contents($file, $content) !== false;
    }

    /**
     * Прочитать контент из файла (если существует)
     */
    public static function getContentFromFile(string $slug): ?string
    {
        $file = self::getContentFilePath($slug);
        if (file_exists($file) && is_readable($file)) {
            return file_get_contents($file);
        }
        return null;
    }

    public function findBySlug(string $slug): ?array
    {
        $db = $this->getDb();
        if (!$db) {
            return null;
        }
        try {
            $stmt = $db->prepare('SELECT * FROM pages WHERE slug = ? AND is_active = 1');
            $stmt->execute([$slug]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getContent(string $slug): string
    {
        // 1. Файл (приоритет — можно редактировать в IDE)
        $fileContent = self::getContentFromFile($slug);
        if ($fileContent !== null) {
            return $fileContent;
        }
        // 2. БД
        $page = $this->findBySlug($slug);
        if ($page) {
            return $page['content'] ?? '';
        }
        return $this->getPlaceholderContent($slug);
    }

    private function getPlaceholderContent(string $slug): string
    {
        $parts = explode('/', $slug);
        $titles = $this->menu->resolveSlugsToTitles($parts);
        $title = !empty($titles) ? end($titles) : $this->slugToTitle($slug);
        return <<<HTML
        <div class="prose prose-lg max-w-none">
            <h1 class="text-2xl font-bold text-slate-800 mb-6">{$title}</h1>
            <p class="text-slate-600 leading-relaxed">
                Раздел находится в разработке. Здесь будет размещена информация по теме «{$title}».
            </p>
            <p class="text-slate-500 mt-4 text-sm">
                КГКП Карагандинский высший колледж инжиниринга — современное образовательное учреждение,
                готовящее специалистов в области строительства, архитектуры и дизайна.
            </p>
        </div>
        HTML;
    }

    private function slugToTitle(string $slug): string
    {
        $parts = explode('/', $slug);
        $last = end($parts);
        $last = str_replace(['-', '_'], ' ', $last);
        return mb_convert_case($last, MB_CASE_TITLE, 'UTF-8');
    }

    public function getMenu(): Menu
    {
        return $this->menu;
    }
}
