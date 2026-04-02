# КГКП Карагандинский высший колледж инжиниринга

Сайт образовательного учреждения. Современный UI/UX, PHP OOP, MySQL, TailwindCSS.

## Требования

- PHP 7.4+
- MySQL 5.7+ / MariaDB
- Apache с mod_rewrite (MAMP)

## Установка

1. **База данных:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. **Конфигурация:** Отредактируйте `config/database.php` под ваши учётные данные.

3. **BASE_URL:** В `config/config.php` измените `BASE_URL` если сайт не в подпапке `/kvki`.

## Структура

```
kvki/
├── assets/css/      # Стили
├── classes/         # PHP OOP (Database, Menu, Page, Router)
├── config/          # Конфигурация
├── data/            # menu.json
├── database/        # schema.sql
├── templates/       # header.php, footer.php
├── index.php        # Точка входа
└── .htaccess        # ЧПУ
```

## Меню

Меню настраивается в `data/menu.json`. Структура поддерживает вложенность до 4 уровней.

## Добавление контента

Контент страниц хранится в таблице `pages`. При отсутствии записи показывается заглушка.
