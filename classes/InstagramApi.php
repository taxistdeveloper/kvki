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
     * Поддерживает: User Token (→ /me/accounts), прямой запрос GET /{page-id} (если me/accounts пуст из‑за Facebook Login for Business),
     * и Page Token (→ /me).
     *
     * @param string|null $facebookPageId Числовой ID Facebook Page (как в Graph: /2244…?fields=…). Не путать с ID пользователя / бизнес-портфеля.
     */
    public static function getInstagramIdFromToken(string $accessToken, ?string $facebookPageId = null): ?array
    {
        $facebookPageId = $facebookPageId !== null ? trim($facebookPageId) : '';
        if ($facebookPageId !== '' && preg_match('/^\d+$/', $facebookPageId)) {
            $direct = self::fetchInstagramFromPageId($accessToken, $facebookPageId);
            if ($direct !== null) {
                return $direct;
            }
            // Ошибка прямого запроса уже в $lastError; ниже пробуем me/accounts.
        }

        // 1. Пробуем /me/accounts (User Token → список страниц)
        $fields = 'access_token,instagram_business_account{id,username}';
        $url = self::GRAPH_URL . '/me/accounts?fields=' . rawurlencode($fields) . '&access_token=' . urlencode($accessToken);
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
            if (!self::$lastError) {
                self::$lastError = 'Страницы в аккаунте есть, но ни к одной не подключён Instagram Business/Creator (или нет прав на поле instagram_business_account). Привяжите Instagram к странице в Meta Business Suite.';
            }
            return null;
        }

        // Пустой me/accounts: чаще User без страниц; реже Page token — тогда /me всё ещё указывает на страницу.
        if ($data !== null && isset($data['data']) && is_array($data['data']) && $data['data'] === []) {
            $fromMe = self::fetchInstagramFromMeAsPage($accessToken);
            if ($fromMe !== null) {
                return $fromMe;
            }
            if (self::isNonexistingInstagramOnMeError(self::$lastError)) {
                self::$lastError = 'У узла «me» для этого маркера нет поля instagram_business_account: это User token, а не Page token. Нужны страницы в me/accounts (роль на Facebook Page) или маркер страницы из Graph API Explorer («Получить маркер доступа к Странице»).';
            } elseif (!self::$lastError) {
                self::$lastError = 'Список страниц пуст (me/accounts). Часто так бывает при Facebook Login for Business: в Graph Explorer укажите тот же User token и запрос по ID страницы — тогда укажите этот числовой ID страницы в поле ниже и нажмите «Подключить» снова. Либо вставьте Page Access Token.';
            }
            return null;
        }

        // 2. /me/accounts вернул null (ошибка сети или такой токен не подходит к edge) — пробуем /me как страницу (Page Access Token).
        $fromMe = self::fetchInstagramFromMeAsPage($accessToken);
        if ($fromMe !== null) {
            return $fromMe;
        }

        // Ошибка
        if (!self::$lastError) {
            self::$lastError = 'Нет Facebook-страниц с привязанным Instagram Business/Creator для этого токена. Создайте или получите роль на Page, привяжите Instagram в Meta Business Suite, затем используйте User token (me/accounts) или Page token в Graph API Explorer.';
        }
        return null;
    }

    public static function fetchMedia(string $accessToken, string $igUserId, int $limit = 12): array
    {
        $fields = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp';
        $url = self::GRAPH_URL . '/' . rawurlencode($igUserId) . '/media?fields=' . rawurlencode($fields) . '&limit=' . $limit . '&access_token=' . urlencode($accessToken);
        $data = self::fetch($url);
        if (!$data || empty($data['data'])) {
            return [];
        }
        $posts = [];
        foreach ($data['data'] as $item) {
            $permalink = $item['permalink'] ?? '';
            if (empty($permalink)) continue;
            $type = $item['media_type'] ?? 'IMAGE';
            // Для VIDEO media_url — это mp4; для <img> нужен thumbnail_url. CDN-ссылки ещё и быстро протухают — см. cacheThumbnail().
            $previewUrl = ($type === 'VIDEO')
                ? ($item['thumbnail_url'] ?? null)
                : ($item['media_url'] ?? $item['thumbnail_url'] ?? null);
            $posts[] = [
                'ig_media_id' => $item['id'] ?? null,
                'post_url' => $permalink,
                'caption' => $item['caption'] ?? null,
                'media_type' => $type,
                'media_url' => $previewUrl,
            ];
        }
        return $posts;
    }

    /**
     * Скачивает превью на диск (обход протухания и блокировок CDN Instagram при hotlink).
     * @return string|null относительный путь от корня сайта: assets/cache/instagram/…
     */
    public static function cacheThumbnail(?string $imageUrl, ?string $igMediaId): ?string
    {
        $imageUrl = $imageUrl !== null ? trim($imageUrl) : '';
        $igMediaId = $igMediaId !== null ? trim($igMediaId) : '';
        if ($imageUrl === '' || $igMediaId === '' || !str_starts_with($imageUrl, 'http')) {
            return null;
        }
        $safe = preg_replace('/[^0-9A-Za-z_-]/', '_', $igMediaId);
        if ($safe === '') {
            return null;
        }
        $rel = 'assets/cache/instagram/' . $safe . '.jpg';
        $dir = ROOT_PATH . '/assets/cache/instagram';
        $full = ROOT_PATH . '/' . $rel;
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return null;
            }
        }
        if (is_file($full) && filesize($full) > 1000) {
            return $rel;
        }
        $ch = curl_init($imageUrl);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; compatible; KVKI-InstagramCache/1.0)',
                'Accept: image/webp,image/apng,image/*,*/*;q=0.8',
            ],
        ];
        if (self::isLocalhost()) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        curl_setopt_array($ch, $opts);
        $binary = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || !is_string($binary) || strlen($binary) < 500) {
            return null;
        }
        if (@file_put_contents($full, $binary) === false) {
            return null;
        }
        return $rel;
    }

    /**
     * User token + числовой Page ID — обход пустого me/accounts (типично для «Вход для компаний» с выбором только части страниц).
     */
    private static function fetchInstagramFromPageId(string $accessToken, string $pageId): ?array
    {
        $fields = 'access_token,instagram_business_account{id,username}';
        $url = self::GRAPH_URL . '/' . rawurlencode($pageId) . '?fields=' . rawurlencode($fields) . '&access_token=' . urlencode($accessToken);
        $data = self::fetch($url);
        if (!$data) {
            return null;
        }
        $ig = $data['instagram_business_account'] ?? null;
        $pageToken = $data['access_token'] ?? null;
        if ($ig && !empty($ig['id']) && $pageToken) {
            return [
                'ig_user_id' => $ig['id'],
                'username' => $ig['username'] ?? null,
                'page_access_token' => $pageToken,
            ];
        }
        if ($ig && !empty($ig['id']) && !$pageToken) {
            self::$lastError = 'Instagram найден, но в ответе нет access_token страницы. В Graph API Explorer нажмите «Получить маркер доступа к Странице» для этой Page или расширьте права User token (страницы) и повторите.';
            return null;
        }
        if (!self::$lastError) {
            self::$lastError = 'По этому ID страницы не удалось получить instagram_business_account. Проверьте ID (это ID Page в Graph, не пользователя) и что Instagram Business привязан к этой Page.';
        }
        return null;
    }

    /** GET /me?fields=instagram_business_account — имеет смысл только когда маркер относится к Page (не к User). */
    private static function fetchInstagramFromMeAsPage(string $accessToken): ?array
    {
        $fieldsMe = 'instagram_business_account{id,username}';
        $url = self::GRAPH_URL . '/me?fields=' . rawurlencode($fieldsMe) . '&access_token=' . urlencode($accessToken);
        $data2 = self::fetch($url);
        if ($data2 && !empty($data2['instagram_business_account']['id'])) {
            $ig = $data2['instagram_business_account'];
            return [
                'ig_user_id' => $ig['id'],
                'username' => $ig['username'] ?? null,
                'page_access_token' => $accessToken,
            ];
        }
        return null;
    }

    private static function isNonexistingInstagramOnMeError(string $msg): bool
    {
        return $msg !== ''
            && (stripos($msg, 'nonexisting field') !== false || stripos($msg, 'does not exist') !== false)
            && stripos($msg, 'instagram_business_account') !== false;
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
