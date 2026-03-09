-- Migration 007: Ficha Técnica / Formação de Preço (galeteria)
-- Adapta a tabela products com campos opcionais e cria ingredientes + fichas técnicas.
-- Não quebra o sistema: colunas novas são NULL/default; produtos existentes continuam iguais.

-- 1) Adicionar colunas opcionais em products (se não existirem)
SET @dbname = DATABASE();

SET @tablename = 'products';
SET @columnname = 'yield_target_grams';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN yield_target_grams INT NULL DEFAULT NULL COMMENT ''Porção final do prato em gramas (ficha técnica)'''
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @columnname = 'margin_percent';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN margin_percent DECIMAL(5,2) NULL DEFAULT 65.00 COMMENT ''Margem bruta % para preço sugerido (ficha técnica)'''
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Tabela de insumos (matérias-primas)
CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NULL,
    name VARCHAR(150) NOT NULL,
    unit ENUM('kg','g','l','ml','un') NOT NULL DEFAULT 'kg',
    cost_per_unit DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ingredients_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Ficha técnica (1 por produto)
CREATE TABLE IF NOT EXISTS technical_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_technical_sheets_product (product_id),
    CONSTRAINT fk_technical_sheets_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Itens da ficha técnica
CREATE TABLE IF NOT EXISTS technical_sheet_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sheet_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    item_classification VARCHAR(80) NULL COMMENT 'proteína, base, acompanhamento, embalagem, etc',
    item_qty_brut DECIMAL(12,4) NOT NULL DEFAULT 0,
    item_qty_net DECIMAL(12,4) NULL,
    item_unit ENUM('g','kg','ml','l','un') NOT NULL DEFAULT 'g',
    item_yield_percent DECIMAL(6,2) NULL,
    item_cost_per_unit DECIMAL(12,4) NULL,
    item_total_cost DECIMAL(12,4) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_tsi_sheet (sheet_id),
    KEY idx_tsi_product (ingredient_id),
    CONSTRAINT fk_tsi_sheet FOREIGN KEY (sheet_id) REFERENCES technical_sheets(id) ON DELETE CASCADE,
    CONSTRAINT fk_tsi_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
