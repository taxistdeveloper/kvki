<?php
/**
 * Instagram Graph API — получение постов
 * Требуется: Instagram Business/Creator + Facebook Page
 */
class InstagramApi
{
    private const GRAPH_URL = 'https://graph.facebook.com/v21.0';

    /**
     * Получает Instagram ID и Page Access Token.
     * Поддерживает: User Token (→ /me/accounts) и Page Token (→ /me).
     */
    public static function getInstagramIdFromToken(string $accessToken): ?array
    {
        // 1. Пробуем /me/accounts (User Token → список страниц)
        $url = self::GRAPH_URL . '/me/accounts?fields=access_token,instagram_business_account{id,username}&access_token=' . urlencode($accessToken);
        $data = self::fetch($url);
        if ($data && !empty($data['data'])) {
            foreach ($data['data'] as $page) {
                $ig = $page['instagram_business_account'] ?? null;
                $pageToken = $page['access_token'] ?? null;
                if ($ig && !empty($ig['id']) && $pageToken) {
                    return [
                        'ig_user_id' => $ig['id'],
                        'username' => $ig['username'] ?? null,
                        'page_access_token' => $pageToken,
                    ];
                }
            }
        }

        // 2. Пробуем /me (Page Token — когда в Explorer выбрана страница)
        self::$lastError = '';
        $url2 = self::GRAPH_URL . '/me?fields=instagram_business_account{id,username}&access_token=' . urlencode($accessToken);
        $data2 = self::fetch($url2);
        if ($data2 && !empty($data2['instagram_business_account']['id'])) {
            $ig = $data2['instagram_business_account'];
            return [
                'ig_user_id' => $ig['id'],
                'username' => $ig['username'] ?? null,
                'page_access_token' => $accessToken, // Page token уже подходит
            ];
        }

        // Ошибка
        if (!self::$lastError) {
            self::$lastError = 'Нет Facebook-страниц с Instagram. Подключите @ktsk.kz как Business/Creator к Facebook Page (business.facebook.com → Настройки → Instagram).';
        }
        return null;
    }

    public static function fetchMedia(string $accessToken, string $igUserId, int $limit = 12): array
    {
        $fields = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp';
        $url = self::GRAPH_URL . '/' . $igUserId . '/media?fields=' . urlencode($fields) . '&limit=' . $limit . '&access_token=' . urlencode($accessToken);
        $data = self::fetch($url);
        if (!$data || empty($data['data'])) {
            return [];
        }
        $posts = [];
        foreach ($data['data'] as $item) {
            $permalink = $item['permalink'] ?? '';
            if (empty($permalink)) continue;
            $posts[] = [
                'post_url' => $permalink,
                'caption' => $item['caption'] ?? null,
                'media_type' => $item['media_type'] ?? 'IMAGE',
                'media_url' => $item['media_url'] ?? $item['thumbnail_url'] ?? null,
            ];
        }
        return $posts;
    }

    private static function fetch(string $url): ?array
    {
        self::$lastError = '';
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ];
        // HTTP 0 на localhost часто из-за SSL — отключаем проверку для разработки
        if (self::isLocalhost()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);
        if ($httpCode !== 200 || !$response) {
            $decoded = $response ? json_decode($response, true) : null;
            if ($decoded && isset($decoded['error']['message'])) {
                self::$lastError = $decoded['error']['message'];
            } elseif ($httpCode === 0 && $curlErr) {
                self::$lastError = 'Нет соединения с API: ' . $curlErr . '. Проверьте интернет и настройки PHP (allow_url_fopen, curl).';
            } else {
                self::$lastError = 'HTTP ' . $httpCode . ($response ? ': ' . mb_substr(strip_tags($response), 0, 150) : '');
            }
            return null;
        }
        $decoded = json_decode($response, true);
        if (isset($decoded['error'])) {
            self::$lastError = $decoded['error']['message'] ?? json_encode($decoded['error']);
            return null;
        }
        return $decoded;
    }

    /** Последняя ошибка API (для отладки) */
    public static $lastError = '';

    private static function isLocalhost(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return $host === 'localhost' || $host === '127.0.0.1' || str_starts_with($host, 'localhost:') || str_starts_with($host, '127.0.0.1:');
    }
}
