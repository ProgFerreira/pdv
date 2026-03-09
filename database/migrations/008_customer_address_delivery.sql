-- Endereço de entrega do cliente (CEP, logradouro, número, complemento) e endereço impresso no cupom
-- Execute uma vez. Colunas adicionadas apenas se não existirem.

-- customers: campos de endereço estruturado (preenchidos via CEP/ViaCEP)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'cep');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN cep VARCHAR(10) NULL AFTER email', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'address_street');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN address_street VARCHAR(255) NULL AFTER cep', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'address_number');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN address_number VARCHAR(20) NULL AFTER address_street', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'address_complement');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN address_complement VARCHAR(100) NULL AFTER address_number', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'address_neighborhood');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN address_neighborhood VARCHAR(100) NULL AFTER address_complement', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'address_city');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN address_city VARCHAR(100) NULL AFTER address_neighborhood', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'address_state');
SET @sql = IF(@col = 0, 'ALTER TABLE customers ADD COLUMN address_state VARCHAR(2) NULL AFTER address_city', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sales: endereço de entrega no momento da venda (para imprimir no cupom)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'delivery_address');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN delivery_address TEXT NULL AFTER customer_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
