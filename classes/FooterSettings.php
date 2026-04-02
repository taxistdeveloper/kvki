<?php
/**
 * Настройки footer сайта
 */
class FooterSettings
{
    private static ?array $settings = null;

    private static function getDefaults(): array
    {
        return [
            'footer_address' => 'ул. Кирпичная 8, г. Караганда, Казахстан',
            'footer_email' => 'info@kvki.kz',
            'footer_about_links' => '[{"title":"О нас","url":"/o-nas"},{"title":"История колледжа","url":"/istoriya-kolledzha"},{"title":"Вакансии","url":"/trudoustroystva"},{"title":"Новости","url":"/novosti"},{"title":"Галерея","url":"/o-nas"}]',
            'footer_admission_links' => '[{"title":"Список документов","url":"/kak-podat-dokumenty-na-postuplenie-v-kolledzh-onlayn"},{"title":"Специальности","url":"/spetsialnosti"},{"title":"Правила приёма","url":"/pravila-priema"},{"title":"Обратная связь","url":"/o-nas"}]',
            'footer_anticor_title' => 'Антикоррупционный комплекс',
            'footer_anticor_links' => '[{"title":"Картограмма коррупции","url":"/kartogramma-korruptsii"},{"title":"Контакты антикора","url":"/antikorruptsionnyy-kompleks"}]',
            'footer_hotline' => '1424',
            'footer_map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=73.082%2C49.797%2C73.095%2C49.808&amp;layer=mapnik&amp;marker=49.8027%2C73.0876',
            'footer_2gis_url' => 'https://2gis.kz/karaganda/search/Кирпичная%208',
            'footer_yandex_url' => 'https://yandex.ru/maps/?pt=73.0876,49.8027&z=17&l=map',
            'footer_bottom_links' => '[{"title":"Условия использования","url":"/o-nas"},{"title":"Политика конфиденциальности","url":"/o-nas"},{"title":"Государственные символы","url":"/o-nas/gos-uslugi/gosudarstvennye-simvoly"}]',
        ];
    }

    public static function load(): array
    {
        if (self::$settings !== null) {
            return self::$settings;
        }
        $defaults = self::getDefaults();
        try {
            $db = Database::getInstance();
            $stmt = $db->query('SELECT `key`, `value` FROM footer_settings');
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            self::$settings = array_merge($defaults, $rows ?: []);
        } catch (PDOException $e) {
            self::$settings = $defaults;
        }
        return self::$settings;
    }

    public static function get(string $key): string
    {
        $s = self::load();
        return (string)($s[$key] ?? '');
    }

    /** @return array<array{title:string,url:string}> */
    public static function getLinks(string $key): array
    {
        $s = self::load();
        $json = $s[$key] ?? '';
        $decoded = $json ? json_decode($json, true) : null;
        if (is_array($decoded) && !empty($decoded)) {
            return array_values(array_filter($decoded, fn($x) => !empty($x['title']) && !empty($x['url'])));
        }
        return [];
    }
}
