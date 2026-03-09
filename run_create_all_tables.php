<?php
/**
 * Cria TODAS as tabelas necessárias do PDV em um único script.
 * Use em servidor (ex.: Hostinger) quando o banco está vazio ou faltam tabelas.
 * Acesse: https://seu-dominio.com/pdv/run_create_all_tables.php
 * Apague este arquivo após usar (segurança).
 *
 * Tabelas criadas: users, categories, products, sales, sale_items, customers,
 * sectors, cash_registers, cash_movements, permissions, role_permissions, audit_logs,
 * suppliers, brands, plano_contas, contas_bancarias, contas_pagar, contas_receber,
 * anexos_financeiro, investimentos, investment_participants, investment_loan_payments,
 * investment_assets, gift_cards, gift_card_logs, stock_entries, stock_entry_items,
 * stock_batches, ingredients, technical_sheets, technical_sheet_items.
 * + Colunas adicionais em sales e products quando necessário.
 */
date_default_timezone_set('America/Sao_Paulo');
define('PROJECT_ROOT', __DIR__);
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

function execSql($pdo, $sql) {
    $sql = trim($sql);
    if ($sql === '' || strpos($sql, '--') === 0) return;
    try {
        $pdo->exec($sql . (substr(rtrim($sql), -1) === ';' ? '' : ';'));
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') === false && strpos($e->getMessage(), 'already exists') === false) {
            throw $e;
        }
    }
}

function addColumnIfMissing($pdo, $table, $column, $definition) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = '$table' AND column_name = '$column'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN $definition");
    }
}

$created = [];
$errors = [];

try {
    // --- BASE ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role ENUM('admin','cashier') DEFAULT 'cashier', active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY, category_id INT NULL, name VARCHAR(100) NOT NULL, price DECIMAL(12,2) NOT NULL DEFAULT 0, stock INT DEFAULT 0, active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS sales (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, total DECIMAL(12,2) NOT NULL, payment_method VARCHAR(50) NULL, amount_paid DECIMAL(12,2) NULL, change_amount DECIMAL(12,2) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS sale_items (id INT AUTO_INCREMENT PRIMARY KEY, sale_id INT NULL, product_id INT NULL, quantity INT NOT NULL, unit_price DECIMAL(12,2) NOT NULL, subtotal DECIMAL(12,2) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS customers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, phone VARCHAR(20) NULL, email VARCHAR(100) NULL, address TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    addColumnIfMissing($pdo, 'users', 'sector_id', 'sector_id INT NULL DEFAULT 1');
    addColumnIfMissing($pdo, 'products', 'code', 'code VARCHAR(50) NULL');
    addColumnIfMissing($pdo, 'products', 'brand_id', 'brand_id INT NULL');
    addColumnIfMissing($pdo, 'products', 'cost_price', 'cost_price DECIMAL(12,2) NULL');
    addColumnIfMissing($pdo, 'products', 'unit', "unit VARCHAR(20) NULL DEFAULT 'un'");
    addColumnIfMissing($pdo, 'products', 'location', 'location VARCHAR(100) NULL');
    addColumnIfMissing($pdo, 'products', 'ean', 'ean VARCHAR(20) NULL');
    addColumnIfMissing($pdo, 'products', 'observations', 'observations TEXT NULL');
    addColumnIfMissing($pdo, 'products', 'image', 'image VARCHAR(255) NULL');
    addColumnIfMissing($pdo, 'products', 'sector_id', 'sector_id INT NULL DEFAULT 1');
    addColumnIfMissing($pdo, 'products', 'is_gift_card', 'is_gift_card TINYINT(1) DEFAULT 0');
    addColumnIfMissing($pdo, 'products', 'is_consigned', 'is_consigned TINYINT(1) DEFAULT 0');
    addColumnIfMissing($pdo, 'products', 'supplier_id', 'supplier_id INT NULL');
    addColumnIfMissing($pdo, 'products', 'yield_target_grams', 'yield_target_grams INT NULL');
    addColumnIfMissing($pdo, 'products', 'margin_percent', 'margin_percent DECIMAL(5,2) NULL DEFAULT 65');

    addColumnIfMissing($pdo, 'sales', 'customer_id', 'customer_id INT NULL');
    addColumnIfMissing($pdo, 'sales', 'cash_register_id', 'cash_register_id INT NULL');
    addColumnIfMissing($pdo, 'sales', 'discount_amount', 'discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0');
    addColumnIfMissing($pdo, 'sales', 'sector_id', 'sector_id INT NULL DEFAULT 1');
    addColumnIfMissing($pdo, 'sales', 'status', "status VARCHAR(32) NULL DEFAULT 'completed'");
    addColumnIfMissing($pdo, 'sales', 'cancelled_at', 'cancelled_at TIMESTAMP NULL');
    addColumnIfMissing($pdo, 'sales', 'cancelled_by', 'cancelled_by INT NULL');

    addColumnIfMissing($pdo, 'sale_items', 'cost_price', 'cost_price DECIMAL(12,2) NULL');

    execSql($pdo, "INSERT IGNORE INTO categories (id, name) VALUES (1,'Terços'),(2,'Bíblias'),(3,'Imagens'),(4,'Livros'),(5,'Outros')");
    execSql($pdo, "INSERT IGNORE INTO customers (id, name, phone) VALUES (1,'Cliente Balcão','000000000')");

    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $st = $pdo->prepare("INSERT INTO users (id, name, username, password_hash, role) VALUES (1,'Administrador','admin',?,'admin') ON DUPLICATE KEY UPDATE password_hash = ?");
    $st->execute([$adminHash, $adminHash]);

    // --- SETORES E CAIXA ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS sectors (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "INSERT IGNORE INTO sectors (id, name, active) VALUES (1,'Principal',1)");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS cash_registers (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0, closing_balance DECIMAL(12,2) NULL, status ENUM('open','closed') NOT NULL DEFAULT 'open', opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, closed_at TIMESTAMP NULL, notes TEXT NULL, INDEX idx_user_status (user_id, status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS cash_movements (id INT AUTO_INCREMENT PRIMARY KEY, cash_register_id INT NOT NULL, type VARCHAR(32) NOT NULL, amount DECIMAL(12,2) NOT NULL, description VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_cash_register_id (cash_register_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // --- PERMISSÕES E AUDITORIA ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS permissions (id INT AUTO_INCREMENT PRIMARY KEY, `key` VARCHAR(64) NOT NULL UNIQUE, name VARCHAR(100) NOT NULL, description VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS role_permissions (role VARCHAR(32) NOT NULL, permission_id INT NOT NULL, PRIMARY KEY (role, permission_id), INDEX idx_role (role)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, action VARCHAR(64) NOT NULL, entity VARCHAR(64) NOT NULL, entity_id INT NULL, metadata_json TEXT NULL, ip VARCHAR(45) NULL, user_agent VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_user_id (user_id), INDEX idx_created_at (created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $perms = "('dashboard','Dashboard',''),('pos','PDV',''),('sale_view','Ver vendas',''),('sale_cancel','Cancelar venda',''),('customer','Clientes',''),('product','Produtos',''),('stock','Estoque',''),('category','Categorias',''),('brand','Marcas',''),('giftcard','Vale-presente',''),('report','Relatórios',''),('audit','Auditoria',''),('user','Usuários',''),('permission_manage','Permissões',''),('cash','Caixa',''),('receivable','Contas a receber',''),('finance_dashboard','Financeiro',''),('investment_manage','Investimentos','')";
    execSql($pdo, "INSERT IGNORE INTO permissions (`key`, name, description) VALUES $perms");
    execSql($pdo, "INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'admin', id FROM permissions");
    execSql($pdo, "INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'cashier', id FROM permissions WHERE `key` IN ('dashboard','pos','sale_view','product','customer','cash','giftcard')");

    // --- FORNECEDORES E MARCAS ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS suppliers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, contact_person VARCHAR(100) NULL, phone VARCHAR(30) NULL, email VARCHAR(100) NULL, address TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS brands (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // --- FINANCEIRO ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS plano_contas (id INT AUTO_INCREMENT PRIMARY KEY, tipo VARCHAR(20) NULL, nome VARCHAR(100) NOT NULL, pai_id INT NULL, ativo TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS contas_bancarias (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL, tipo VARCHAR(50) NULL, saldo_inicial DECIMAL(12,2) DEFAULT 0, ativo TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS contas_pagar (id INT AUTO_INCREMENT PRIMARY KEY, fornecedor_id INT NULL, numero_documento VARCHAR(50) NULL, descricao VARCHAR(255) NULL, valor_total DECIMAL(12,2) NOT NULL DEFAULT 0, valor_pago DECIMAL(12,2) NOT NULL DEFAULT 0, saldo_aberto DECIMAL(12,2) NOT NULL DEFAULT 0, data_competencia DATE NULL, data_vencimento DATE NULL, data_pagamento DATE NULL, status VARCHAR(20) NOT NULL DEFAULT 'ABERTO', categoria_id INT NULL, forma_pagamento VARCHAR(50) NULL, observacoes TEXT NULL, recorrente TINYINT(1) DEFAULT 0, regra_recorrencia VARCHAR(20) NULL, parcelado TINYINT(1) DEFAULT 0, qtd_parcelas INT DEFAULT 1, pai_id INT NULL, anexo_path VARCHAR(255) NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS contas_receber (id INT AUTO_INCREMENT PRIMARY KEY, cliente_id INT NULL, numero_documento VARCHAR(50) NULL, origem VARCHAR(20) NULL DEFAULT 'MANUAL', descricao VARCHAR(255) NULL, valor_total DECIMAL(12,2) NOT NULL DEFAULT 0, valor_recebido DECIMAL(12,2) NOT NULL DEFAULT 0, saldo_aberto DECIMAL(12,2) NOT NULL DEFAULT 0, data_competencia DATE NULL, data_vencimento DATE NULL, data_recebimento DATE NULL, status VARCHAR(20) NOT NULL DEFAULT 'ABERTO', forma_recebimento VARCHAR(50) NULL, categoria_id INT NULL, observacoes TEXT NULL, pdv_venda_id INT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_status (status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS anexos_financeiro (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, entity_type ENUM('payable','receivable') NOT NULL, entity_id INT UNSIGNED NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime VARCHAR(100) NOT NULL, size INT UNSIGNED NOT NULL DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_entity (entity_type, entity_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // --- INVESTIMENTOS ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS investimentos (id INT AUTO_INCREMENT PRIMARY KEY, `data` DATE NOT NULL, pessoa VARCHAR(255) NULL, valor DECIMAL(12,2) NOT NULL DEFAULT 0, produto VARCHAR(255) NULL, tipo VARCHAR(50) NOT NULL DEFAULT 'compra', estado VARCHAR(20) NULL, observacoes TEXT NULL, documento_numero VARCHAR(100) NULL, quantidade INT NOT NULL DEFAULT 1, data_devolucao_prevista DATE NULL, forma_pagamento VARCHAR(50) NULL, categoria_ativo VARCHAR(50) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_data (`data`), INDEX idx_tipo (tipo)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS investment_participants (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, contact VARCHAR(255) NULL, document VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, INDEX idx_deleted (deleted_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS investment_loan_payments (id INT AUTO_INCREMENT PRIMARY KEY, investimento_id INT NOT NULL, data_pagamento DATE NOT NULL, valor_pago DECIMAL(12,2) NOT NULL DEFAULT 0, forma_pagamento VARCHAR(50) NULL, comprovante VARCHAR(255) NULL, observacao TEXT NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_investimento (investimento_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS investment_assets (id INT AUTO_INCREMENT PRIMARY KEY, descricao VARCHAR(255) NOT NULL, categoria VARCHAR(100) NULL, valor_estimado DECIMAL(12,2) NULL, data_entrada DATE NOT NULL, responsavel_id INT NULL, origem VARCHAR(50) NOT NULL DEFAULT 'comprado', localizacao VARCHAR(100) NULL, observacoes TEXT NULL, comprovante_arquivo VARCHAR(255) NULL, vida_util_meses INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NULL, deleted_at TIMESTAMP NULL, INDEX idx_deleted (deleted_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    addColumnIfMissing($pdo, 'investimentos', 'participant_id', 'participant_id INT NULL');
    addColumnIfMissing($pdo, 'investimentos', 'finalidade', 'finalidade VARCHAR(100) NULL');
    addColumnIfMissing($pdo, 'investimentos', 'status', 'status VARCHAR(30) NULL');
    addColumnIfMissing($pdo, 'investimentos', 'deleted_at', 'deleted_at TIMESTAMP NULL');

    // --- VALE-PRESENTE ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS gift_cards (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50) NOT NULL UNIQUE, initial_value DECIMAL(12,2) NOT NULL, balance DECIMAL(12,2) NOT NULL, customer_id INT NULL, sale_id INT NULL, expiry_date DATE NULL, status VARCHAR(20) DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS gift_card_logs (id INT AUTO_INCREMENT PRIMARY KEY, gift_card_id INT NOT NULL, sale_id INT NULL, type VARCHAR(20) NOT NULL, amount DECIMAL(12,2) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_gift_card (gift_card_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // --- ESTOQUE (Lotes) ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS stock_entries (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, reference VARCHAR(100) NULL, supplier VARCHAR(255) NULL, supplier_id INT NULL, purchase_id INT NULL, total_amount DECIMAL(12,2) DEFAULT 0, entry_date DATE NULL, notes TEXT NULL, sector_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS stock_entry_items (id INT AUTO_INCREMENT PRIMARY KEY, entry_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, cost_price DECIMAL(12,2) NULL, INDEX idx_entry (entry_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS stock_batches (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, stock_entry_id INT NOT NULL, initial_quantity INT NOT NULL, current_quantity INT NOT NULL, cost_price DECIMAL(12,2) NULL, INDEX idx_product (product_id), INDEX idx_entry (stock_entry_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // --- FICHA TÉCNICA ---
    execSql($pdo, "CREATE TABLE IF NOT EXISTS ingredients (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50) NULL, name VARCHAR(150) NOT NULL, unit VARCHAR(10) NOT NULL DEFAULT 'kg', cost_per_unit DECIMAL(12,4) NOT NULL DEFAULT 0, active TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY uk_code (code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS technical_sheets (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_product (product_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    execSql($pdo, "CREATE TABLE IF NOT EXISTS technical_sheet_items (id INT AUTO_INCREMENT PRIMARY KEY, sheet_id INT NOT NULL, ingredient_id INT NOT NULL, quantity_gross DECIMAL(12,4) NOT NULL, quantity_net DECIMAL(12,4) NULL, yield_percent DECIMAL(5,2) NULL, item_cost_per_unit DECIMAL(12,4) NULL, item_total_cost DECIMAL(12,4) NULL, INDEX idx_sheet (sheet_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $created[] = 'Todas as tabelas e colunas necessárias';
} catch (PDOException $e) {
    $errors[] = $e->getMessage();
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Criar todas as tabelas</title></head><body style="font-family:system-ui;max-width:560px;margin:2rem auto;padding:1.5rem;">';
echo '<h1>Criação de tabelas do PDV</h1>';
if (!empty($errors)) {
    echo '<p style="color:#b91c1c;"><strong>Erro:</strong> ' . htmlspecialchars(implode(' ', $errors)) . '</p>';
} else {
    echo '<p style="color:#059669;"><strong>Concluído.</strong> Tabelas criadas ou já existentes (CREATE TABLE IF NOT EXISTS / ADD COLUMN se faltando).</p>';
    echo '<p>Login: <strong>admin</strong> / Senha: <strong>admin123</strong></p>';
}
echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=dashboard/index">Ir para o Dashboard</a></p>';
echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_create_all_tables.php</code> do servidor após usar.</p>';
echo '</body></html>';
