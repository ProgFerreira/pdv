-- WhatsApp enviado e pedido entregue (para destacar na listagem de vendas)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'whatsapp_sent_at');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN whatsapp_sent_at DATETIME NULL AFTER status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'delivered_at');
SET @sql = IF(@col = 0, 'ALTER TABLE sales ADD COLUMN delivered_at DATETIME NULL AFTER whatsapp_sent_at', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
