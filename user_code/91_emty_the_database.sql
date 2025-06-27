-- Database Variable setzen
SET @database = 'db_445253_6';

-- Foreign Key Constraints entfernen
SELECT 1 as sort_order, CONCAT('ALTER TABLE `', table_name, '` DROP FOREIGN KEY `', constraint_name, '`;') AS statement
FROM information_schema.key_column_usage
WHERE referenced_table_schema = @database
AND constraint_name != 'PRIMARY'

UNION ALL

-- Views löschen
SELECT 2, CONCAT('DROP VIEW IF EXISTS `', table_name, '`;')
FROM information_schema.views
WHERE table_schema = @database

UNION ALL

-- Stored Procedures löschen
SELECT 3, CONCAT('DROP PROCEDURE IF EXISTS `', routine_name, '`;')
FROM information_schema.routines
WHERE routine_schema = @database
AND routine_type = 'PROCEDURE'

UNION ALL

-- Functions löschen
SELECT 4, CONCAT('DROP FUNCTION IF EXISTS `', routine_name, '`;')
FROM information_schema.routines
WHERE routine_schema = @database
AND routine_type = 'FUNCTION'

UNION ALL

-- Triggers löschen
SELECT 5, CONCAT('DROP TRIGGER IF EXISTS `', trigger_name, '`;')
FROM information_schema.triggers
WHERE trigger_schema = @database

UNION ALL

-- Events löschen
SELECT 6, CONCAT('DROP EVENT IF EXISTS `', event_name, '`;')
FROM information_schema.events
WHERE event_schema = @database

UNION ALL

-- Tabellen löschen
SELECT 7, CONCAT('DROP TABLE IF EXISTS `', table_name, '`;')
FROM information_schema.tables
WHERE table_schema = @database
AND table_type = 'BASE TABLE'

