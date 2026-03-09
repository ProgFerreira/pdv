<?php
/**
 * Executa a migration 007 (Ficha Técnica: ingredients, technical_sheets, technical_sheet_items).
 * Uso: php run_migration_007.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = __DIR__;
if (is_file($baseDir . '/vendor/autoload.php')) {
    require_once $baseDir . '/vendor/autoload.php';
}
require_once $baseDir . '/config/env.php';

$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'pdv_religioso';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

echo "Conectando em {$host} / {$name}...\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage() . "\n";
    exit(1);
}

$run = function ($sql) use ($pdo) {
    $sql = trim($sql);
    if ($sql === '') return;
    $pdo->exec($sql);
};

// 1) Coluna yield_target_grams
echo "Adicionando coluna yield_target_grams (se não existir)...\n";
$run("SET @dbname = DATABASE()");
$run("SET @tablename = 'products'");
$run("SET @columnname = 'yield_target_grams'");
$run("SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN yield_target_grams INT NULL DEFAULT NULL COMMENT ''Porção final do prato em gramas (ficha técnica)'''
))");
$run("PREPARE stmt FROM @preparedStatement");
$run("EXECUTE stmt");
$run("DEALLOCATE PREPARE stmt");

// 2) Coluna margin_percent
echo "Adicionando coluna margin_percent (se não existir)...\n";
$run("SET @columnname = 'margin_percent'");
$run("SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
   WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE products ADD COLUMN margin_percent DECIMAL(5,2) NULL DEFAULT 65.00 COMMENT ''Margem bruta % para preço sugerido (ficha técnica)'''
))");
$run("PREPARE stmt FROM @preparedStatement");
$run("EXECUTE stmt");
$run("DEALLOCATE PREPARE stmt");

// 3) Tabela ingredients
echo "Criando tabela ingredients...\n";
$run("CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NULL,
    name VARCHAR(150) NOT NULL,
    unit ENUM('kg','g','l','ml','un') NOT NULL DEFAULT 'kg',
    cost_per_unit DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_ingredients_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 4) Tabela technical_sheets
echo "Criando tabela technical_sheets...\n";
$run("CREATE TABLE IF NOT EXISTS technical_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_technical_sheets_product (product_id),
    CONSTRAINT fk_technical_sheets_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 5) Tabela technical_sheet_items
echo "Criando tabela technical_sheet_items...\n";
$run("CREATE TABLE IF NOT EXISTS technical_sheet_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sheet_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    item_classification VARCHAR(80) NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

echo "Migration 007 executada com sucesso.\n";
