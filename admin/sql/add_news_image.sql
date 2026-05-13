-- Добавить поле картинки для новостей (выполните один раз)
ALTER TABLE news ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;
