-- Etapa "Saiu para entrega" na fila de pedidos (entre "Em preparação" e "Entregue")
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'out_for_delivery_at');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN out_for_delivery_at DATETIME NULL AFTER delivered_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
