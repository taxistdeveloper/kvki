-- Админ-панель КВКИ: установка таблиц
-- Выполните этот скрипт в БД kvki

-- Администраторы
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Объявления (главная страница)
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `date` VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    excerpt TEXT,
    is_important TINYINT(1) DEFAULT 0,
    views INT UNSIGNED DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Слайды главного экрана
CREATE TABLE IF NOT EXISTS hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    `text` TEXT,
    image_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Новости
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    `date` VARCHAR(20) NOT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    views INT UNSIGNED DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_date (date),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Партнёры
CREATE TABLE IF NOT EXISTS partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(500) DEFAULT '#',
    image_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Страницы (если ещё нет)
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    meta_description VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Пароль по умолчанию: admin / admin123
INSERT INTO admin_users (username, password_hash) 
VALUES ('admin', '$2y$10$FmE9qOuRP05vA14fHbXJguHr3ir9T/35XB69b0kpDRIliC8s30n/2')
ON DUPLICATE KEY UPDATE username = username;

-- Начальные объявления (url: относительный путь, например /abiturientam/pravila-priema)
INSERT INTO announcements (`date`, title, url, excerpt, is_important, sort_order) VALUES
('12.03.2025', 'Приём документов на 2025–2026 учебный год', '/abiturientam/pravila-priema', 'Документы принимаются с 1 июня по 25 августа. Список необходимых документов и порядок подачи.', 1, 0),
('11.03.2025', 'Изменение расписания на 13 марта', '/o-nas', 'В связи с проведением олимпиады занятия 13 марта переносятся. Уточняйте расписание у кураторов.', 0, 1),
('08.03.2025', 'Выходной день 8 марта', '/o-nas', 'Колледж не работает 8 марта. Занятия возобновляются 10 марта.', 0, 2);

-- Начальные слайды
INSERT INTO hero_slides (title, `text`, image_url, sort_order) VALUES
('Карагандинский высший колледж инжиниринга', 'Современное образовательное учреждение. Готовим специалистов в строительстве, архитектуре и дизайне.', '', 0),
('Образование в строительстве и дизайне', 'Более 50 лет опыта. Практико-ориентированное обучение и востребованные специальности.', '', 1),
('Приём 2025 — подайте заявку', 'Узнайте о правилах приёма, специальностях и станьте студентом КВКИ.', '', 2);

-- Посты Instagram (ссылки на посты для embed)
CREATE TABLE IF NOT EXISTS instagram_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_url VARCHAR(500) NOT NULL,
    caption VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    source VARCHAR(20) DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Настройки Instagram API (автосинхронизация)
CREATE TABLE IF NOT EXISTS instagram_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token VARCHAR(500) NOT NULL,
    ig_user_id VARCHAR(50) NOT NULL,
    last_sync_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Настройки верхнего header
CREATE TABLE IF NOT EXISTS header_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(80) NOT NULL UNIQUE,
    `value` TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO header_settings (`key`, `value`) VALUES
('logo_abbr', 'КВКИ'),
('logo_title', 'Карагандинский высший колледж'),
('logo_subtitle', 'инжиниринга'),
('phone_primary', '+7 (747) 094 10 00'),
('phone_secondary', '+7 (700) 123 45 67'),
('whatsapp', '77470941000'),
('telegram', 'https://t.me/kvki_college'),
('about_o_kolledzhe', '/o-nas'),
('about_rukovodstvo', '/o-nas/rukovodstvo'),
('about_istoriya', '/o-nas/istoriya'),
('baza_znaniy_url', '/baza-znaniy'),
('top_bar_visible', '1'),
('youtube', ''),
('instagram', ''),
('phone_home', ''),
('header_about_links', '[{"title":"О колледже","url":"/o-nas"},{"title":"Руководство","url":"/o-nas/rukovodstvo"},{"title":"История","url":"/o-nas/istoriya"}]')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Настройки footer
CREATE TABLE IF NOT EXISTS footer_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(80) NOT NULL UNIQUE,
    `value` TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO footer_settings (`key`, `value`) VALUES
('footer_address', 'ул. Кирпичная 8, г. Караганда, Казахстан'),
('footer_email', 'info@kvki.kz'),
('footer_about_links', '[{"title":"О нас","url":"/o-nas"},{"title":"История колледжа","url":"/istoriya-kolledzha"},{"title":"Вакансии","url":"/trudoustroystva"},{"title":"Новости","url":"/novosti"},{"title":"Галерея","url":"/o-nas"}]'),
('footer_admission_links', '[{"title":"Список документов","url":"/kak-podat-dokumenty-na-postuplenie-v-kolledzh-onlayn"},{"title":"Специальности","url":"/spetsialnosti"},{"title":"Правила приёма","url":"/pravila-priema"},{"title":"Обратная связь","url":"/o-nas"}]'),
('footer_anticor_title', 'Антикоррупционный комплекс'),
('footer_anticor_links', '[{"title":"Картограмма коррупции","url":"/kartogramma-korruptsii"},{"title":"Контакты антикора","url":"/antikorruptsionnyy-kompleks"}]'),
('footer_hotline', '1424'),
('footer_map_embed_url', 'https://www.openstreetmap.org/export/embed.html?bbox=73.082%2C49.797%2C73.095%2C49.808&amp;layer=mapnik&amp;marker=49.8027%2C73.0876'),
('footer_2gis_url', 'https://2gis.kz/karaganda/search/Кирпичная%208'),
('footer_yandex_url', 'https://yandex.ru/maps/?pt=73.0876,49.8027&z=17&l=map'),
('footer_bottom_links', '[{"title":"Условия использования","url":"/o-nas"},{"title":"Политика конфиденциальности","url":"/o-nas"},{"title":"Государственные символы","url":"/o-nas/gos-uslugi/gosudarstvennye-simvoly"}]')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- Начальные партнёры
INSERT INTO partners (name, url, image_url, sort_order) VALUES
('Партнёр 1', '#', '', 0),
('Партнёр 2', '#', '', 1),
('Партнёр 3', '#', '', 2),
('Партнёр 4', '#', '', 3);
