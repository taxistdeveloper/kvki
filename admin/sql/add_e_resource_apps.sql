-- Приложения e-Resource (единое окно входа)
CREATE TABLE IF NOT EXISTS e_resource_apps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) DEFAULT '',
    url VARCHAR(500) DEFAULT NULL,
    tag VARCHAR(60) DEFAULT NULL,
    status ENUM('active', 'dev', 'disabled') NOT NULL DEFAULT 'active',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Данные по умолчанию (запустите миграцию один раз)
INSERT INTO e_resource_apps (name, `description`, url, tag, status, sort_order) VALUES
('Besaspap.app', 'Система входа для Besasap', 'http://besaspap.ktsk.kz/', '1.0', 'active', 1),
('Hr.app', 'Портал вакансий Hr', 'https://enbek.ktsk.kz/', '1.0', 'active', 2),
('Students.app', 'Студенты', '#', 'В разработке', 'dev', 3),
('Inventory.app', 'Вход в инвентарь', 'http://inventory.ktsk.kz/', '2.0', 'active', 4),
('Museum.app', 'Система для входа в Музей', NULL, '2.0', 'disabled', 5),
('ktsk.app', 'Наш колледж ktsk', 'https://college.ktsk.kz/', NULL, 'active', 6),
('cloud.app', 'Вход в облако архива', '#', 'В разработке', 'dev', 7),
('Soon', 'Скоро', '#', NULL, 'dev', 8);
