-- Дополнительные настройки header: YouTube, Instagram, домашний телефон, ссылки из страниц
INSERT INTO header_settings (`key`, `value`) VALUES
('youtube', ''),
('instagram', ''),
('phone_home', ''),
('header_about_links', '[{"title":"О колледже","url":"/o-nas"},{"title":"Руководство","url":"/o-nas/rukovodstvo"},{"title":"История","url":"/o-nas/istoriya"}]')
ON DUPLICATE KEY UPDATE `key` = `key`;
