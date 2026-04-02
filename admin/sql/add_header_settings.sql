-- Настройки верхнего header сайта
CREATE TABLE IF NOT EXISTS header_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(80) NOT NULL UNIQUE,
    `value` TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Значения по умолчанию
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
