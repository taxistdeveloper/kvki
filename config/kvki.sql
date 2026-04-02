-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Мар 18 2026 г., 12:08
-- Версия сервера: 5.7.24
-- Версия PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `kvki`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$FmE9qOuRP05vA14fHbXJguHr3ir9T/35XB69b0kpDRIliC8s30n/2', '2026-03-16 07:18:26');

-- --------------------------------------------------------

--
-- Структура таблицы `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `date` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `excerpt` text,
  `is_important` tinyint(1) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `views` int(10) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `announcements`
--

INSERT INTO `announcements` (`id`, `date`, `title`, `url`, `excerpt`, `is_important`, `sort_order`, `created_at`, `views`) VALUES
(7, '16.03.2026', 'Тестовый', '/ob-yavleniya/testovyy', 'тестовый', 1, 0, '2026-03-16 11:20:08', 20);

-- --------------------------------------------------------

--
-- Структура таблицы `e_resource_apps`
--

CREATE TABLE `e_resource_apps` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT '',
  `url` varchar(500) DEFAULT NULL,
  `tag` varchar(60) DEFAULT NULL,
  `status` enum('active','dev','disabled') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `e_resource_apps`
--

INSERT INTO `e_resource_apps` (`id`, `name`, `description`, `url`, `tag`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Portal.app', 'База студентов Система управления студентами', 'https://portal.krg-ktsk.kz/', '1.0', 'active', 4, '2026-03-17 09:31:00', '2026-03-17 09:33:44'),
(4, 'Inventory.app', 'Вход в инвентарь', 'http://inventory.ktsk.kz/', '2.0', 'active', 5, '2026-03-17 09:31:00', '2026-03-17 09:33:41'),
(5, 'Museum.app', 'Система для входа в Музей', NULL, '2.0', 'disabled', 6, '2026-03-17 09:31:00', '2026-03-17 09:33:38'),
(6, 'ktsk.app', 'Наш колледж ktsk', 'https://college.ktsk.kz/', NULL, 'active', 1, '2026-03-17 09:31:00', '2026-03-17 09:33:44'),
(7, 'cloud.app', 'Вход в облако архива', '#', 'В разработке', 'dev', 7, '2026-03-17 09:31:00', '2026-03-17 09:31:00'),
(8, 'Soon', 'Скоро', '#', NULL, 'dev', 8, '2026-03-17 09:31:00', '2026-03-17 09:31:00');

-- --------------------------------------------------------

--
-- Структура таблицы `footer_settings`
--

CREATE TABLE `footer_settings` (
  `id` int(11) NOT NULL,
  `key` varchar(80) NOT NULL,
  `value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `footer_settings`
--

INSERT INTO `footer_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'footer_address', 'ул. Кирпичная 8, г. Караганда, Казахстан', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(2, 'footer_email', 'info@kvki.kz', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(3, 'footer_about_links', '[{\"title\":\"О нас\",\"url\":\"/o-nas\"},{\"title\":\"История колледжа\",\"url\":\"/istoriya-kolledzha\"},{\"title\":\"Вакансии\",\"url\":\"/trudoustroystva\"},{\"title\":\"Новости\",\"url\":\"/novosti\"},{\"title\":\"Галерея\",\"url\":\"/o-nas\"}]', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(4, 'footer_admission_links', '[{\"title\":\"Список документов\",\"url\":\"/kak-podat-dokumenty-na-postuplenie-v-kolledzh-onlayn\"},{\"title\":\"Специальности\",\"url\":\"/spetsialnosti\"},{\"title\":\"Правила приёма\",\"url\":\"/pravila-priema\"},{\"title\":\"Обратная связь\",\"url\":\"/o-nas\"}]', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(5, 'footer_anticor_title', 'Антикоррупционный комплекс', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(6, 'footer_anticor_links', '[{\"title\":\"Картограмма коррупции\",\"url\":\"/kartogramma-korruptsii\"},{\"title\":\"Контакты антикора\",\"url\":\"/antikorruptsionnyy-kompleks\"}]', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(7, 'footer_hotline', '1424', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(8, 'footer_map_embed_url', 'https://www.openstreetmap.org/export/embed.html?bbox=73.082%2C49.797%2C73.095%2C49.808&amp;layer=mapnik&amp;marker=49.8027%2C73.0876', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(9, 'footer_2gis_url', 'https://2gis.kz/karaganda/search/Кирпичная%208', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(10, 'footer_yandex_url', 'https://yandex.ru/maps/?pt=73.0876,49.8027&z=17&l=map', '2026-03-17 09:16:08', '2026-03-17 09:16:08'),
(11, 'footer_bottom_links', '[{\"title\":\"Условия использования\",\"url\":\"/o-nas\"},{\"title\":\"Политика конфиденциальности\",\"url\":\"/o-nas\"},{\"title\":\"Государственные символы\",\"url\":\"/o-nas/gos-uslugi/gosudarstvennye-simvoly\"}]', '2026-03-17 09:16:08', '2026-03-17 09:16:08');

-- --------------------------------------------------------

--
-- Структура таблицы `header_settings`
--

CREATE TABLE `header_settings` (
  `id` int(11) NOT NULL,
  `key` varchar(80) NOT NULL,
  `value` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `header_settings`
--

INSERT INTO `header_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'logo_abbr', 'КВКИ', '2026-03-17 07:35:09', '2026-03-17 07:35:09'),
(2, 'logo_title', 'Карагандинский высший колледж', '2026-03-17 07:35:09', '2026-03-17 07:35:09'),
(3, 'logo_subtitle', 'инжиниринга', '2026-03-17 07:35:09', '2026-03-17 07:35:09'),
(4, 'phone_primary', '+7 (747) 572-97-91', '2026-03-17 07:35:09', '2026-03-17 08:38:09'),
(5, 'phone_secondary', '+7 (747) 572-97-91', '2026-03-17 07:35:09', '2026-03-17 08:38:09'),
(6, 'whatsapp', '+7 (747) 572-97-91', '2026-03-17 07:35:09', '2026-03-17 08:38:09'),
(7, 'telegram', 'https://t.me/kvki_college', '2026-03-17 07:35:09', '2026-03-17 07:35:09'),
(8, 'about_o_kolledzhe', '', '2026-03-17 07:35:09', '2026-03-17 08:14:39'),
(9, 'about_rukovodstvo', '', '2026-03-17 07:35:09', '2026-03-17 08:14:39'),
(10, 'about_istoriya', '', '2026-03-17 07:35:09', '2026-03-17 08:14:39'),
(11, 'baza_znaniy_url', 'https://krg-ktsk.kz/', '2026-03-17 07:35:09', '2026-03-17 08:30:39'),
(12, 'top_bar_visible', '1', '2026-03-17 07:35:09', '2026-03-17 07:35:09'),
(18, 'phone_home', '+7 7212 44-12-65', '2026-03-17 08:14:39', '2026-03-17 08:38:09'),
(21, 'youtube', '#', '2026-03-17 08:14:39', '2026-03-17 08:14:39'),
(22, 'instagram', '#', '2026-03-17 08:14:39', '2026-03-17 08:14:39'),
(28, 'header_about_links', '[{\"title\":\"УМО\",\"url\":\"#\"},{\"title\":\"Руководство\",\"url\":\"\\/o-nas\\/rukovodstvo\"},{\"title\":\"История\",\"url\":\"\\/o-nas\\/istoriya\"}]', '2026-03-17 08:14:39', '2026-03-17 08:29:25');

-- --------------------------------------------------------

--
-- Структура таблицы `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text,
  `image_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `title`, `text`, `image_url`, `sort_order`, `created_at`) VALUES
(1, 'Карагандинский высший колледж инжиниринга', 'Современное образовательное учреждение. Готовим специалистов в строительстве, архитектуре и дизайне.', '', 0, '2026-03-16 07:18:26'),
(2, 'Образование в строительстве и дизайне', 'Более 50 лет опыта. Практико-ориентированное обучение и востребованные специальности.', '', 1, '2026-03-16 07:18:26'),
(3, 'Приём 2025 — подайте заявку', 'Узнайте о правилах приёма, специальностях и станьте студентом КВКИ.', '', 2, '2026-03-16 07:18:26');

-- --------------------------------------------------------

--
-- Структура таблицы `instagram_posts`
--

CREATE TABLE `instagram_posts` (
  `id` int(11) NOT NULL,
  `post_url` varchar(500) NOT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(20) DEFAULT 'manual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `date` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `views` int(10) UNSIGNED DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `date`, `views`, `is_active`, `created_at`, `updated_at`, `image_url`) VALUES
(1, 'Тестовый', 'testovyy', 'новости тестовый', 'фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы фывфывфывфывыфвыфвфывыфвыфвфы ', '17.03.2026', 18, 1, '2026-03-17 07:02:14', '2026-03-18 09:49:12', '/kvki/assets/images/news/news-1.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `meta_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `meta_description`, `parent_id`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'index', 'Главная', '<h1>Добро пожаловать в КГКП Карагандинский высший колледж инжиниринга</h1><p>Современное образовательное учреждение технического профиля.</p>', 'Карагандинский высший колледж инжиниринга - образование в области строительства и дизайна', NULL, 0, 1, '2026-03-12 10:27:56', '2026-03-12 10:27:56'),
(4, 'blog-direktora', 'Блог директора', '<div class=\"blog-direktor not-prose\">\r\n  <!-- Hero — приветствие -->\r\n  <div class=\"relative overflow-hidden rounded-3xl bg-gradient-to-br from-sage-700/15 via-cream-50 to-sage-500/10 border border-sage-400/20 p-8 lg:p-12 mb-12 shadow-sm\">\r\n    <div class=\"absolute top-0 right-0 w-80 h-80 bg-sage-400/5 rounded-full -translate-y-1/2 translate-x-1/2\" aria-hidden=\"true\"></div>\r\n    <div class=\"absolute bottom-0 left-0 w-48 h-48 bg-sage-500/5 rounded-full translate-y-1/2 -translate-x-1/2\" aria-hidden=\"true\"></div>\r\n    <div class=\"relative z-10\">\r\n      <span class=\"inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-sage-500/15 text-sage-700 text-sm font-semibold mb-6\">Блог директора</span>\r\n      <h1 class=\"text-2xl lg:text-4xl font-extrabold text-ink-800 mb-3 tracking-tight leading-tight\">Добро пожаловать в мой блог</h1>\r\n      <p class=\"text-ink-600 leading-relaxed text-base lg:text-lg max-w-2xl\">\r\n        Я рада приветствовать Вас! Надеюсь, этот ресурс станет площадкой для обсуждения вопросов деятельности колледжа. Вы можете оставить комментарии, задать вопросы, внести предложения. Мы с радостью выслушаем идеи, которые могут улучшить работу нашего колледжа.\r\n      </p>\r\n    </div>\r\n  </div>\r\n\r\n  <!-- Карточка директора -->\r\n  <div class=\"rounded-3xl border border-cream-200 bg-white shadow-lg shadow-sage-900/5 overflow-hidden mb-12\">\r\n    <div class=\"flex flex-col lg:flex-row\">\r\n      <!-- Фото -->\r\n      <div class=\"lg:w-72 xl:w-80 flex-shrink-0 bg-gradient-to-b from-cream-100/80 to-cream-50 p-8 lg:p-10 flex items-center justify-center\">\r\n        <div class=\"relative group\">\r\n          <div class=\"absolute -inset-3 rounded-2xl bg-sage-500/10 group-hover:bg-sage-500/15 transition-colors duration-300\" aria-hidden=\"true\"></div>\r\n          <img src=\"{{BASE_URL}}/assets/images/direktor/direktor.jpg\" \r\n               alt=\"Сапарова Асель Сагинбаевна — директор КГКП «Карагандинский технико–строительный колледж»\"\r\n               class=\"relative w-48 h-60 lg:w-56 lg:h-72 object-cover rounded-2xl shadow-xl ring-2 ring-white\"\r\n               onerror=\"this.src=\'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&amp;fit=crop&amp;w=400&amp;q=80\'; this.alt=\'Фото директора\';\">\r\n        </div>\r\n      </div>\r\n\r\n      <!-- Контент -->\r\n      <div class=\"flex-1 p-6 lg:p-10 lg:pl-12\">\r\n        <div class=\"flex flex-wrap gap-2 mb-4\">\r\n          <span class=\"inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-sage-600 text-white\">Директор</span>\r\n          <span class=\"inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-sage-500/20 text-sage-700\">С 2022 года</span>\r\n        </div>\r\n        <h2 class=\"text-2xl lg:text-3xl font-bold text-ink-800 mb-1\">Сапарова Асель Сагинбаевна</h2>\r\n        <p class=\"text-sage-600 font-medium mb-8\">КГКП «Карагандинский технико–строительный колледж»</p>\r\n\r\n        <!-- Краткие факты -->\r\n        <div class=\"grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8\">\r\n          <div class=\"p-4 rounded-xl bg-cream-50 border border-cream-200 hover:border-sage-400/40 transition-colors\">\r\n            <div class=\"text-xl font-bold text-sage-700\">12 лет</div>\r\n            <div class=\"text-xs text-ink-600 mt-0.5\">руководства</div>\r\n          </div>\r\n          <div class=\"p-4 rounded-xl bg-cream-50 border border-cream-200 hover:border-sage-400/40 transition-colors\">\r\n            <div class=\"text-xl font-bold text-sage-700\">С 2004</div>\r\n            <div class=\"text-xs text-ink-600 mt-0.5\">в образовании</div>\r\n          </div>\r\n          <div class=\"p-4 rounded-xl bg-cream-50 border border-cream-200 hover:border-sage-400/40 transition-colors\">\r\n            <div class=\"text-xl font-bold text-sage-700\">Высшая</div>\r\n            <div class=\"text-xs text-ink-600 mt-0.5\">категория</div>\r\n          </div>\r\n          <div class=\"p-4 rounded-xl bg-cream-50 border border-cream-200 hover:border-sage-400/40 transition-colors\">\r\n            <div class=\"text-xl font-bold text-sage-700\">Магистр</div>\r\n            <div class=\"text-xs text-ink-600 mt-0.5\">юр. наук</div>\r\n          </div>\r\n        </div>\r\n\r\n        <div class=\"text-ink-600 leading-relaxed space-y-5 text-[15px]\">\r\n          <p>Опыт работы в организации образования с 2004 года. Стаж работы на руководящей должности директора составляет — 12 лет. Педагог высшей квалификационной категории, руководитель первой категории по должности директор. Ученое звание — магистр юридических наук.</p>\r\n          <div class=\"p-5 rounded-2xl bg-sage-500/5 border border-sage-500/10\">\r\n            <p class=\"font-semibold text-ink-800 mb-2 flex items-center gap-2\">\r\n              <svg class=\"w-4 h-4 text-sage-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z\"/></svg>\r\n              Награды\r\n            </p>\r\n            <p class=\"text-ink-600 text-sm leading-relaxed\">Благодарственные письма Акима района им. Казыбек би, Акима Октябрьского района, Акима Карагандинской области, почётная грамота Акима г. Караганды, почетная грамота Министерства образования и науки РК, благодарственное письмо Карагандинской областной организации профсоюза работников образования и науки, диплом III степени «Лучший руководитель образовательного учреждения – 2018», обладатель нагрудного знака «Ы. Алтынсарин».</p>\r\n          </div>\r\n          <p>Сапарова А.С. обладает высокими профессиональными знаниями для выполнения возложенных должностных обязанностей и исключительным практическим опытом. Блестяще планирует работу, при решении тех или иных вопросов исходит из интересов дела. Директор — квалифицированный специалист, знающий основы общетеоретических дисциплин в объеме, необходимом для решения педагогических, научно-методических и организационно-управленческих задач. В своей деятельности руководствуется принципами ответственности, конфиденциальности, доброжелательности.</p>\r\n        </div>\r\n      </div>\r\n    </div>\r\n  </div>\r\n\r\n  <!-- CTA-блок -->\r\n  <div class=\"flex flex-col sm:flex-row gap-4\">\r\n    <a href=\"mailto:info@kvki.kz?subject=Вопрос%20директору\" \r\n       class=\"group flex-1 inline-flex items-center justify-center gap-3 px-8 py-4 bg-sage-600 text-white font-semibold rounded-2xl hover:bg-sage-700 hover:shadow-xl hover:shadow-sage-600/25 transition-all duration-200\">\r\n      <span class=\"flex items-center justify-center w-11 h-11 rounded-xl bg-white/20 group-hover:bg-white/30 transition-colors\">\r\n        <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">\r\n          <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\"/>\r\n        </svg>\r\n      </span>\r\n      Задать вопрос директору\r\n    </a>\r\n    <a href=\"{{BASE_URL}}/svedeniya-o-deklaratsii\" \r\n       class=\"group flex-1 inline-flex items-center justify-center gap-3 px-8 py-4 bg-cream-50 border-2 border-cream-200 text-sage-700 font-semibold rounded-2xl hover:border-sage-400 hover:bg-cream-100 hover:shadow-lg transition-all duration-200\">\r\n      <span class=\"flex items-center justify-center w-11 h-11 rounded-xl bg-sage-500/10 text-sage-600 group-hover:bg-sage-500/20 transition-colors\">\r\n        <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">\r\n          <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\"/>\r\n        </svg>\r\n      </span>\r\n      Сведения о декларации\r\n    </a>\r\n  </div>\r\n</div>\r\n', '', NULL, 0, 1, '2026-03-16 08:36:21', '2026-03-18 08:30:49'),
(5, 'istoriya-kolledzha', 'История колледжа', '<p><br></p>', '', NULL, 0, 1, '2026-03-16 11:36:17', '2026-03-16 11:36:17'),
(6, 'metodicheskaya-rabota', 'Методическая работа', '<p><br></p>', '', NULL, 0, 1, '2026-03-16 11:39:02', '2026-03-16 11:39:02'),
(7, 'tsel-i-napravlenie', 'Цель и Направление', '<p><br></p>', 'Цель и Направление', NULL, 0, 1, '2026-03-16 11:44:19', '2026-03-16 11:44:19'),
(8, 'antikorruptsionnyy-kompleks', 'Антикоррупционный комплекс', '<p><br></p>', 'Антикоррупционный комплекс', NULL, 0, 1, '2026-03-16 11:44:57', '2026-03-16 11:44:57'),
(9, 'trudoustroystva', 'Трудоустройства', '<p><br></p>', 'Трудоустройства', NULL, 0, 1, '2026-03-16 11:56:50', '2026-03-16 11:56:50'),
(10, 'sotsialnye-partnery', 'Социальные партнеры', '<p><br></p>', 'Социальные партнеры', NULL, 0, 1, '2026-03-16 11:56:57', '2026-03-16 11:56:57'),
(11, 'stroitelnoe-tekhnicheskoe-otdelenie', 'Строительное техническое отделение', '<p><br></p>', 'Строительное техническое отделение', NULL, 0, 1, '2026-03-16 12:03:06', '2026-03-16 12:03:06'),
(12, 'arkhitektury-dizayna-i-dekorativno-prikladnogo-iskusstva', 'Архитектуры, дизайна и декоративно-прикладного искусства', '<p><br></p>', 'Архитектуры, дизайна и декоративно-прикладного искусства', NULL, 0, 1, '2026-03-16 12:03:34', '2026-03-16 12:03:34'),
(13, 'professionalno-tekhnicheskie', 'Профессионально-технические', '<p><br></p>', 'Профессионально-технические', NULL, 0, 1, '2026-03-16 12:03:54', '2026-03-16 12:03:54'),
(15, 'gosudarstvennye-simvoly', 'Государственные символы', '<p><br></p>', 'Государственные символы', NULL, 0, 1, '2026-03-16 12:15:54', '2026-03-16 12:15:54'),
(16, 'perechen-gosudarstvennykh-uslug', 'Перечень государственных услуг', '<p><br></p>', 'Перечень государственных услуг', NULL, 0, 1, '2026-03-16 12:16:25', '2026-03-16 12:16:25'),
(17, 'zhas-maman', 'Жас маман', '<p><br></p>', 'Жас маман', NULL, 0, 1, '2026-03-17 03:30:24', '2026-03-17 03:30:24'),
(18, 'pravila-priema', 'Правила приёма', '<p><br></p>', 'Правила приёма', NULL, 0, 1, '2026-03-17 04:28:14', '2026-03-17 04:28:14'),
(19, 'kak-podat-dokumenty-na-postuplenie-v-kolledzh-onlayn', 'Как подать документы на поступление в колледж онлайн?', '<p><br></p>', 'Как подать документы на поступление в колледж онлайн?', NULL, 0, 1, '2026-03-17 04:29:17', '2026-03-17 04:29:17'),
(20, 'kak-postupit-v-kolledzh-abiturientu', 'Как поступить в колледж абитуриенту', '<p><br></p>', 'Как поступить в колледж абитуриенту', NULL, 0, 1, '2026-03-17 04:31:29', '2026-03-17 04:31:29'),
(21, 'spetsialnosti', 'Специальности', '<p><br></p>', 'Специальности', NULL, 0, 1, '2026-03-17 04:32:15', '2026-03-17 04:32:15'),
(22, 'kruzhki-i-kluby', 'Кружки и клубы', '<p><br></p>', 'Кружки и клубы', NULL, 0, 1, '2026-03-17 04:33:39', '2026-03-17 04:33:39'),
(23, 'obschezhitie', 'Общежитие', '<p><br></p>', 'Общежитие', NULL, 0, 1, '2026-03-17 04:34:08', '2026-03-17 04:34:08'),
(24, 'tsentr-obsluzhivaniya-studentov', 'Центр обслуживания студентов', '<p><br></p>', 'Центр обслуживания студентов', NULL, 0, 1, '2026-03-17 04:36:18', '2026-03-17 04:36:18'),
(25, 'video', 'Видео', '<p><br></p>', 'Видео', NULL, 0, 1, '2026-03-17 06:57:09', '2026-03-17 06:57:09'),
(26, 'worldskills', 'WorldSkills', '<p><br></p>', 'WorldSkills', NULL, 0, 1, '2026-03-17 06:57:52', '2026-03-17 06:57:52'),
(27, 'kartogramma-korruptsii', 'Картограмма коррупции', '<p><br></p>', 'Картограмма коррупции', NULL, 0, 1, '2026-03-17 07:29:59', '2026-03-17 07:29:59');

-- --------------------------------------------------------

--
-- Структура таблицы `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(500) DEFAULT '#',
  `image_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `partners`
--

INSERT INTO `partners` (`id`, `name`, `url`, `image_url`, `sort_order`, `created_at`) VALUES
(1, 'Тестовый', '#', '/kvki/assets/images/partners/partner-1.jpg', 0, '2026-03-16 07:18:26'),
(2, 'Партнёр 2', '#', '', 1, '2026-03-16 07:18:26'),
(3, 'Партнёр 3', '#', '', 2, '2026-03-16 07:18:26'),
(4, 'Партнёр 4', '#', '', 3, '2026-03-16 07:18:26');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Индексы таблицы `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `e_resource_apps`
--
ALTER TABLE `e_resource_apps`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `footer_settings`
--
ALTER TABLE `footer_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Индексы таблицы `header_settings`
--
ALTER TABLE `header_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Индексы таблицы `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `instagram_posts`
--
ALTER TABLE `instagram_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_active` (`is_active`);

--
-- Индексы таблицы `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Индексы таблицы `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `e_resource_apps`
--
ALTER TABLE `e_resource_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `footer_settings`
--
ALTER TABLE `footer_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `header_settings`
--
ALTER TABLE `header_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT для таблицы `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `instagram_posts`
--
ALTER TABLE `instagram_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT для таблицы `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
