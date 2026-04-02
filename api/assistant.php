<?php
/**
 * API ИИ-ассистента КВКИ
 * Принимает сообщения чата и возвращает ответ через OpenAI API
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}

require_once dirname(__DIR__) . '/config/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$messages = $input['messages'] ?? [];

if (empty($messages) || !is_array($messages)) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверный формат запроса']);
    exit;
}

$systemPrompt = <<<PROMPT
Ты — ИИ-ассистент КВКИ (Карагандинский высший колледж инжиниринга). Ты помогаешь посетителям сайта колледжа.

О колледже:
- Полное название: КГКП «Карагандинский высший колледж инжиниринга»
- Адрес: ул. Кирпичная 8, г. Караганда, Казахстан
- Специальности: гражданское строительство, архитектура и дизайн, информационные технологии, электротехника и др.
- Более 50 лет опыта, 2000+ студентов, 15+ специальностей
- Сайт: информация о приёме, правилах поступления, новостях, объявлениях

Твои задачи:
- Отвечать на вопросы о колледже, приёме, специальностях, контактах
- Быть вежливым и полезным
- Если не знаешь точного ответа — предложи связаться с приёмной комиссией или посмотреть разделы сайта
- Отвечай кратко и по делу, на русском языке
PROMPT;

$apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : (getenv('OPENAI_API_KEY') ?: '');

if (empty($apiKey)) {
    // Режим без API: простые ответы по ключевым словам
    $lastMsg = end($messages);
    $text = is_array($lastMsg) ? ($lastMsg['content'] ?? '') : (string)$lastMsg;
    $text = mb_strtolower(trim($text));

    $fallbackReplies = [
        'приём' => 'Информация о приёме документов доступна в разделе «Абитуриентам» → «Правила приёма». Документы принимаются с 1 июня по 25 августа. Для уточнений звоните в приёмную комиссию.',
        'документ' => 'Список документов для поступления смотрите в разделе «Абитуриентам» → «Правила приёма». Обычно нужны: аттестат, паспорт, фото, медицинская справка.',
        'специальност' => 'В колледже более 15 специальностей: гражданское строительство, архитектура и дизайн, информационные технологии, электротехника и др. Подробнее — в разделе «Образовательные программы».',
        'контакт' => 'Адрес: ул. Кирпичная 8, г. Караганда. Телефон приёмной комиссии указан в шапке сайта. Также можно написать на email или в соцсети.',
        'адрес' => 'Колледж находится по адресу: ул. Кирпичная 8, г. Караганда, Казахстан.',
        'здравствуй' => 'Здравствуйте! Я ассистент КВКИ. Чем могу помочь? Спрашивайте о приёме, специальностях или контактах.',
        'привет' => 'Привет! Я помогу с информацией о колледже. Задайте вопрос.',
        'спасибо' => 'Пожалуйста! Обращайтесь, если появятся ещё вопросы.',
    ];

    $reply = 'По вашему вопросу рекомендую обратиться в приёмную комиссию или изучить разделы сайта: «Абитуриентам», «О колледже», «Новости». Контакты указаны в шапке и подвале сайта.';
    foreach ($fallbackReplies as $keyword => $resp) {
        if (mb_strpos($text, $keyword) !== false) {
            $reply = $resp;
            break;
        }
    }

    echo json_encode(['reply' => $reply]);
    exit;
}

// Формируем сообщения для OpenAI
$apiMessages = [
    ['role' => 'system', 'content' => $systemPrompt],
];

foreach ($messages as $m) {
    $role = $m['role'] ?? 'user';
    $content = $m['content'] ?? '';
    if ($content !== '') {
        $apiMessages[] = ['role' => $role, 'content' => $content];
    }
}

$payload = [
    'model' => 'gpt-4o-mini',
    'messages' => $apiMessages,
    'max_tokens' => 500,
    'temperature' => 0.7,
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode(['error' => 'Ошибка соединения с сервисом. Попробуйте позже.']);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200) {
    $errMsg = $data['error']['message'] ?? 'Ошибка сервиса';
    http_response_code($httpCode >= 500 ? 502 : 400);
    echo json_encode(['error' => $errMsg]);
    exit;
}

$reply = $data['choices'][0]['message']['content'] ?? '';
echo json_encode(['reply' => trim($reply)]);
