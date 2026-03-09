<?php
/**
 * Executa a migration v12 (permissões e auditoria).
 * Acesse via navegador ou: php run_migration_v12.php
 * Se o arquivo .sql não existir no servidor, usa o SQL embutido abaixo.
 */
date_default_timezone_set('America/Sao_Paulo');
define('PROJECT_ROOT', __DIR__);
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

$sqlFile = __DIR__ . '/database/schema_v12_security_audit.sql';

if (is_file($sqlFile)) {
    $sql = file_get_contents($sqlFile);
} else {
    // Fallback: SQL embutido (evita erro quando o .sql não foi enviado ao servidor)
    $sql = <<<'SQL'
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
}

try {
    $parts = explode(';', $sql);
    foreach ($parts as $part) {
        $statement = trim($part);
        if ($statement === '' || strpos($statement, '--') === 0) {
            continue;
        }
        $pdo->exec($statement . ';');
    }
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Migration v12</title></head><body>';
    echo '<h1>Migration v12 executada com sucesso</h1>';
    echo '<p>As tabelas <code>permissions</code>, <code>role_permissions</code> e <code>audit_logs</code> foram criadas/atualizadas.</p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=auth/login">Ir para o login</a></p>';
    echo '</body></html>';
} catch (PDOException $e) {
    echo '<h1>Erro ao executar migration</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
