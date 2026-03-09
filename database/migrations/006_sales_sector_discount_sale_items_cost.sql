-- Colunas necessárias para o PDV (sales: sector_id, discount_amount; sale_items: cost_price)
-- Execute uma vez. Se as colunas já existirem, o script não falha.

-- sales: sector_id (para multi-setor)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'sector_id');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN sector_id INT NULL DEFAULT 1 AFTER cash_register_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sales: discount_amount
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'discount_amount');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER total', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sale_items: cost_price (custo unitário para relatório de lucro)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sale_items' AND COLUMN_NAME = 'cost_price');
SET @sql = IF(@col = 0, 'ALTER TABLE sale_items ADD COLUMN cost_price DECIMAL(10,2) NULL DEFAULT NULL AFTER unit_price', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
