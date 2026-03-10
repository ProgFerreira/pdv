-- Observação do pedido no PDV (ex: "mandar sem farofa")
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'observation');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN observation TEXT NULL AFTER is_pickup', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
