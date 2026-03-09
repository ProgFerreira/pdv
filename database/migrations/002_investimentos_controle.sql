-- Controle de Investimentos: ativos imobilizados, aporte de sócios, doações, empréstimos
-- Execute este arquivo no MySQL. Se já existir tabela investimentos com estrutura antiga, faça backup dos dados antes.

DROP TABLE IF EXISTS investimentos;

CREATE TABLE investimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data DATE NOT NULL COMMENT 'Data do lançamento',
    pessoa VARCHAR(255) DEFAULT NULL COMMENT 'Sócio, doador, quem emprestou ou forneceu',
    valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    produto VARCHAR(255) DEFAULT NULL COMMENT 'Descrição do produto/equipamento',
    tipo VARCHAR(50) NOT NULL DEFAULT 'compra' COMMENT 'compra, doacao, emprestimo, aporte_socio, investimento_dinheiro',
    estado VARCHAR(20) DEFAULT NULL COMMENT 'novo, usado - estado do equipamento',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_data (data),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    INDEX idx_pessoa (pessoa(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
