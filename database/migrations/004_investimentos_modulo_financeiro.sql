-- Módulo Financeiro-Societário: participantes, investimentos (cols extras), pagamentos de empréstimo, bens/equipamentos
-- Execute via: php run_migration_investments_v2.php

-- 1) Participantes (pessoas que aportam, emprestam ou doam)
CREATE TABLE IF NOT EXISTS investment_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(255) DEFAULT NULL,
    document VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_deleted (deleted_at),
    INDEX idx_name (name(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Pagamentos de empréstimos (baixas)
CREATE TABLE IF NOT EXISTS investment_loan_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investimento_id INT NOT NULL,
    data_pagamento DATE NOT NULL,
    valor_pago DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    comprovante VARCHAR(255) DEFAULT NULL,
    observacao TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_investimento (investimento_id),
    FOREIGN KEY (investimento_id) REFERENCES investimentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Bens/Equipamentos
CREATE TABLE IF NOT EXISTS investment_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL COMMENT 'freezer, forno, utensilio, etc',
    valor_estimado DECIMAL(12,2) DEFAULT NULL,
    data_entrada DATE NOT NULL,
    responsavel_id INT DEFAULT NULL COMMENT 'FK investment_participants',
    origem VARCHAR(50) NOT NULL DEFAULT 'comprado' COMMENT 'comprado, doado, emprestado',
    localizacao VARCHAR(100) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    comprovante_arquivo VARCHAR(255) DEFAULT NULL,
    vida_util_meses INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_deleted (deleted_at),
    INDEX idx_categoria (categoria(50)),
    INDEX idx_origem (origem),
    INDEX idx_responsavel (responsavel_id),
    FOREIGN KEY (responsavel_id) REFERENCES investment_participants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Colunas extras em investimentos (executar via run_migration_investments_v2.php com checagem)
