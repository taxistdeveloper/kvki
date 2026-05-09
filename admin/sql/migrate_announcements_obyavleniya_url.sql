-- Однократно: заменить старый префикс URL объявлений на канонический /obyavleniya/
UPDATE announcements
SET url = REPLACE(url, '/ob-yavleniya/', '/obyavleniya/')
WHERE url LIKE '%/ob-yavleniya/%' OR url LIKE 'ob-yavleniya/%';
