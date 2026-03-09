-- Migration v12: Permissões (RBAC) e Auditoria
-- Tabelas: permissions, role_permissions, audit_logs

-- Tabela de permissões (chave única para controle por tela)
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela role -> permission_id (role = admin | cashier, igual à coluna users.role)
CREATE TABLE IF NOT EXISTS role_permissions (
    role VARCHAR(32) NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role, permission_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de auditoria
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
    INDEX idx_action_entity (action, entity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões (chaves usadas em config/routes_permissions.php)
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

-- Admin: todas as permissões
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'admin', id FROM permissions;

-- Caixa: permissões básicas (PDV, ver vendas, produtos leitura, dashboard, caixa)
INSERT IGNORE INTO role_permissions (role, permission_id)
SELECT 'cashier', id FROM permissions WHERE `key` IN (
    'dashboard', 'pos', 'sale_view', 'product', 'customer', 'cash', 'giftcard'
);
