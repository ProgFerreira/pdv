<?php
/**
 * Instalação completa para banco vazio (ex.: Hostinger).
 * Cria: users, categories, products, sales, sale_items, customers, permissions, role_permissions, audit_logs.
 * Acesse: https://seu-dominio.com/pdv/run_install.php
 * Apague ou proteja este arquivo após usar.
 */
date_default_timezone_set('America/Sao_Paulo');
define('PROJECT_ROOT', __DIR__);
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

function runSql($pdo, $sql) {
    $parts = explode(';', $sql);
    foreach ($parts as $part) {
        $statement = trim($part);
        if ($statement === '' || strpos($statement, '--') === 0) {
            continue;
        }
        $pdo->exec($statement . ';');
    }
}

echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Instalação PDV</title></head><body style="font-family:system-ui;max-width:560px;margin:2rem auto;padding:1.5rem;">';
echo '<h1>Instalação do banco de dados</h1>';

try {
    // 1) Schema base (users, categories, products, sales, sale_items)
    $schema1 = <<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    amount_paid DECIMAL(10, 2),
    change_amount DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT IGNORE INTO categories (id, name) VALUES
(1, 'Terços'),
(2, 'Bíblias'),
(3, 'Imagens'),
(4, 'Livros'),
(5, 'Outros');
SQL;
    runSql($pdo, $schema1);
    // Admin: senha admin123 (hash gerado no servidor para compatibilidade)
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $st = $pdo->prepare("INSERT INTO users (id, name, username, password_hash, role) VALUES (1, 'Administrador', 'admin', ?, 'admin') ON DUPLICATE KEY UPDATE password_hash = ?");
    $st->execute([$adminHash, $adminHash]);
    echo '<p>OK: Tabelas base (users, categories, products, sales, sale_items). Login: admin / admin123</p>';

    // 2) Schema v2 (customers, customer_id em sales)
    $schema2 = <<<'SQL'
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO customers (id, name, phone) VALUES (1, 'Cliente Balcão', '000000000');
SQL;
    runSql($pdo, $schema2);
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'sales' AND column_name = 'customer_id'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE sales ADD COLUMN customer_id INT NULL DEFAULT NULL AFTER user_id");
        $pdo->exec("ALTER TABLE sales ADD FOREIGN KEY (customer_id) REFERENCES customers(id)");
    }
    echo '<p>OK: Clientes e coluna customer_id em sales.</p>';

    // 2.5) Setores (usado no PDV e em products/users)
    $pdo->exec("CREATE TABLE IF NOT EXISTS sectors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("INSERT IGNORE INTO sectors (id, name, active) VALUES (1, 'Principal', 1)");
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'sector_id'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN sector_id INT NULL DEFAULT 1 AFTER category_id");
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'sector_id'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN sector_id INT NULL DEFAULT 1 AFTER role");
    }
    echo '<p>OK: Tabela sectors e colunas sector_id.</p>';

    // 2.6) Caixa (cash_registers, cash_movements, sales.cash_register_id)
    $pdo->exec("CREATE TABLE IF NOT EXISTS cash_registers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        opening_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
        closing_balance DECIMAL(12,2) NULL,
        status ENUM('open','closed') NOT NULL DEFAULT 'open',
        opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        closed_at TIMESTAMP NULL,
        notes TEXT NULL,
        INDEX idx_user_status (user_id, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cash_movements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cash_register_id INT NOT NULL,
        type VARCHAR(32) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        description VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_cash_register_id (cash_register_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'sales' AND column_name = 'cash_register_id'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE sales ADD COLUMN cash_register_id INT NULL AFTER user_id");
    }
    foreach (['status' => "ADD COLUMN status VARCHAR(32) NULL DEFAULT 'completed'", 'cancelled_at' => "ADD COLUMN cancelled_at TIMESTAMP NULL", 'cancelled_by' => "ADD COLUMN cancelled_by INT NULL"] as $col => $sql) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'sales' AND column_name = '$col'");
        if ($stmt && (int) $stmt->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE sales " . $sql);
        }
    }
    echo '<p>OK: Tabelas cash_registers e cash_movements.</p>';

    // 3) Migration v12 (permissions, role_permissions, audit_logs)
    $schema12 = <<<'SQL'
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role VARCHAR(32) NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(64) NOT NULL,
    entity VARCHAR(64) NOT NULL,
    entity_id INT NULL,
    metadata_json TEXT NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action_entity (action, entity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO permissions (`key`, name, description) VALUES
('dashboard', 'Dashboard', 'Acesso ao painel inicial'),
('pos', 'PDV', 'Ponto de venda e vendas'),
('sale_view', 'Ver vendas', 'Consultar vendas e recibos'),
('sale_cancel', 'Cancelar venda', 'Cancelar vendas'),
('customer', 'Clientes', 'Cadastro e gestão de clientes'),
('product', 'Produtos', 'Produtos, insumos e fichas técnicas'),
('stock', 'Estoque', 'Movimentação e controle de estoque'),
('category', 'Categorias', 'Categorias de produtos'),
('brand', 'Marcas', 'Marcas de produtos'),
('giftcard', 'Vale-presente', 'Gestão de vales e cartões'),
('report', 'Relatórios', 'Relatórios de vendas e estoque'),
('audit', 'Auditoria', 'Visualizar logs de auditoria'),
('user', 'Usuários', 'Cadastro de usuários'),
('permission_manage', 'Permissões', 'Atribuir permissões aos papéis'),
('cash', 'Caixa', 'Abertura/fechamento e movimentações de caixa'),
('receivable', 'Contas a receber', 'Contas a receber'),
('finance_dashboard', 'Financeiro', 'Plano de contas e fluxo de caixa'),
('investment_manage', 'Investimentos', 'Módulo de investimentos');

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'cashier', id FROM permissions WHERE `key` IN (
    'dashboard', 'pos', 'sale_view', 'product', 'customer', 'cash', 'giftcard'
);
SQL;
    runSql($pdo, $schema12);
    echo '<p>OK: Permissões e auditoria (v12).</p>';

    echo '<p><strong>Instalação concluída.</strong></p>';
    echo '<p>Login padrão: <strong>admin</strong> / senha: <strong>admin123</strong></p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=auth/login">Ir para o login</a></p>';
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_install.php</code> do servidor após usar (segurança).</p>';

} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;"><strong>Erro:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</body></html>';
