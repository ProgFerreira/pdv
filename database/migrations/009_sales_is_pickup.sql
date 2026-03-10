-- Venda com retirada no local (não precisa de endereço de entrega)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'is_pickup');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN is_pickup TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_address', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
