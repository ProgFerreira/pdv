-- Vincular vendas ao caixa (fluxo de caixa)
-- Execute uma vez. Se a coluna já existir, ignore o erro ou comente o ALTER.

-- Adiciona coluna cash_register_id na tabela sales (se não existir)
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'sales'
      AND COLUMN_NAME = 'cash_register_id'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE sales ADD COLUMN cash_register_id INT NULL AFTER user_id, ADD INDEX idx_sales_cash_register (cash_register_id)',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
