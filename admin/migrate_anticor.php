<?php
/**
 * Миграция: таблица anticor_documents для антикоррупционного комплекса
 * Выполните один раз: http://ваш-сайт/kvki/admin/migrate_anticor.php
 */
require_once dirname(__DIR__) . '/config/config.php';
$db = Database::tryGetInstance();
if (!$db) {
    die('Database not available');
}

$uploadDir = dirname(__DIR__) . '/storage/anticor';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$sql = file_get_contents(__DIR__ . '/sql/add_anticor_documents.sql');
try {
    $db->exec($sql);

    $count = (int)$db->query('SELECT COUNT(*) FROM anticor_documents')->fetchColumn();
    if ($count === 0) {
        $seed = [
            ['Комплаенс офицер жайлы', 'storage/anticor/koplaeans-oficer-zhayly.pdf', 1],
            ['ПОЛОЖЕНИЕ об антикоррупционной комплаенс-службе', 'storage/anticor/polozhenie-antikorr-komplaens.pdf', 2],
            ['Кодекс корпоративной этики и поведения преподавателей и работников Карагандинский высший колледж инжиниринга', 'storage/anticor/kodeks-korporativnoi-etiki.pdf', 3],
            ['Протокол КВКИ', 'storage/anticor/protokoll-ktsk.pdf', 4],
            ['Антикоррупционная политика Карагандинский высший колледж инжиниринга', 'storage/anticor/antikorruptsionnaya-politika-ktsk.pdf', 5],
            ['Инструкция по противодействию коррупции', 'storage/anticor/instrukciya-po-protivodeystviyu-korrupcii-kommunalnogo-gosudarstvennogo-kazyonnogo-predpriyatiya.pdf', 6],
            ['Меры по противодействию коррупции', 'storage/anticor/mery-po-protivodeystviyu-korupycii.pdf', 7],
            ['План реализации Антикоррупционной стратегии', 'storage/anticor/plan-realizacii-antikorupcionnoy-strategii.pdf', 8],
            ['Политика предотвращения, выявления и урегулирования конфликта интересов', 'storage/anticor/politika-predotvrashcheniya-vyyavleniya-i-uregulirovaniya-konflikta-interesov-kgkp-karagandinskiy-tehniko-stroytelnyy-kolledzh.pdf', 9],
            ['Протокол проведения разъяснительных и обучающих мероприятий', 'storage/anticor/protokol-provedenie-razyasnitelnyh-i-obuchayushchih-meropryatiy.pdf', 10],
            ['Сыбайлас жемқорлыққа қарсы комплаенс қызметі туралы ЕРЕЖЕ', 'storage/anticor/sybaylas-zhemorlya-arsy-komplaens-yzmet-turaly-erezhe.pdf', 11],
            ['Іс-шара сипаттамасы', 'storage/anticor/is-shara-sipattamasy.pdf', 12],
        ];
        $stmt = $db->prepare('INSERT INTO anticor_documents (title, file_path, sort_order, is_active) VALUES (?,?,?,1)');
        foreach ($seed as $row) {
            $stmt->execute($row);
        }
    }

    echo "Таблица 'anticor_documents' создана успешно.";
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
}
