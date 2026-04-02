-- Добавить счётчик просмотров для объявлений (выполните один раз)
ALTER TABLE announcements ADD COLUMN views INT UNSIGNED DEFAULT 0;
