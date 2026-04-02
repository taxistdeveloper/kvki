<?php
/**
 * Маршрутизатор для обработки URL
 */
class Router
{
    private Menu $menu;
    private string $currentPath = '';

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
        $this->parseRequest();
    }

    private function parseRequest(): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH);
        $base = rtrim(BASE_URL, '/');
        $this->currentPath = $base ? preg_replace('#^' . preg_quote($base, '#') . '#', '', $path) : $path;
        $this->currentPath = '/' . trim($this->currentPath, '/');
        if ($this->currentPath === '/' || $this->currentPath === '') {
            $this->currentPath = '/index';
        }
    }

    public function getCurrentPath(): string
    {
        return $this->currentPath;
    }

    public function getPageSlug(): string
    {
        return trim($this->currentPath, '/') ?: 'index';
    }

    public function buildUrl(array $breadcrumb): string
    {
        $slugs = array_map([$this->menu, 'getSlug'], $breadcrumb);
        return BASE_URL . '/' . implode('/', $slugs);
    }

    public function getBreadcrumbFromPath(): array
    {
        $slug = $this->getPageSlug();
        return $this->menu->getBreadcrumbForSlug($slug);
    }
}
