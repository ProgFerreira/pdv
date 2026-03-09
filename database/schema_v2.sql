
-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Adicionar coluna customer_id em sales se não existir
SET @dbname = DATABASE();
SET @tablename = "sales";
SET @columnname = "customer_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE sales ADD COLUMN customer_id INT NULL DEFAULT NULL AFTER user_id, ADD FOREIGN KEY (customer_id) REFERENCES customers(id);"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Inserir Cliente Padrão
INSERT IGNORE INTO customers (id, name, phone) VALUES (1, 'Cliente Balcão', '000000000');
