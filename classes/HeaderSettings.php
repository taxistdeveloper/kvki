<?php
/**
 * Настройки верхнего header сайта
 */
class HeaderSettings
{
    private static ?array $settings = null;

    private static function getDefaults(): array
    {
        return [
            'logo_abbr' => 'КВКИ',
            'logo_title' => 'Карагандинский высший колледж',
            'logo_subtitle' => 'инжиниринга',
            'phone_primary' => '+7 (747) 094 10 00',
            'phone_secondary' => '+7 (700) 123 45 67',
            'whatsapp' => '77470941000',
            'telegram' => 'https://t.me/kvki_college',
            'about_o_kolledzhe' => '/o-nas',
            'about_rukovodstvo' => '/o-nas/rukovodstvo',
            'about_istoriya' => '/o-nas/istoriya',
            'baza_znaniy_url' => '/baza-znaniy',
            'top_bar_visible' => '1',
            'youtube' => '',
            'instagram' => '',
            'phone_home' => '',
            'header_about_links' => '[{"title":"О колледже","url":"/o-nas"},{"title":"Руководство","url":"/o-nas/rukovodstvo"},{"title":"История","url":"/o-nas/istoriya"}]',
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
            $stmt = $db->query('SELECT `key`, `value` FROM header_settings');
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
    public static function getAboutLinks(): array
    {
        $s = self::load();
        $json = $s['header_about_links'] ?? '';
        $decoded = $json ? json_decode($json, true) : null;
        if (is_array($decoded) && !empty($decoded)) {
            return array_values(array_filter($decoded, fn($x) => !empty($x['title']) && !empty($x['url'])));
        }
        // Fallback: старый формат (about_o_kolledzhe и т.д.)
        $links = [];
        if (!empty($s['about_o_kolledzhe'])) $links[] = ['title' => 'О колледже', 'url' => $s['about_o_kolledzhe']];
        if (!empty($s['about_rukovodstvo'])) $links[] = ['title' => 'Руководство', 'url' => $s['about_rukovodstvo']];
        if (!empty($s['about_istoriya'])) $links[] = ['title' => 'История', 'url' => $s['about_istoriya']];
        return $links;
    }
}
