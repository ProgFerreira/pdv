<?php
/**
 * Cria tabelas financeiras: suppliers, plano_contas, contas_pagar, contas_receber.
 * Resolve erro "Table contas_pagar doesn't exist" no dashboard.
 * Acesse: https://seu-dominio.com/pdv/run_fix_finance_tables.php
 * Apague este arquivo após usar (segurança).
 */
date_default_timezone_set('America/Sao_Paulo');
define('PROJECT_ROOT', __DIR__);
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        contact_person VARCHAR(100) NULL,
        phone VARCHAR(30) NULL,
        email VARCHAR(100) NULL,
        address TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS plano_contas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        tipo ENUM('RECEITA','DESPESA') NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contas_pagar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fornecedor_id INT NULL,
        numero_documento VARCHAR(50) NULL,
        descricao VARCHAR(255) NULL,
        valor_total DECIMAL(12,2) NOT NULL DEFAULT 0,
        valor_pago DECIMAL(12,2) NOT NULL DEFAULT 0,
        saldo_aberto DECIMAL(12,2) NOT NULL DEFAULT 0,
        data_competencia DATE NULL,
        data_vencimento DATE NULL,
        data_pagamento DATE NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'ABERTO',
        categoria_id INT NULL DEFAULT 0,
        forma_pagamento VARCHAR(50) NULL,
        observacoes TEXT NULL,
        recorrente TINYINT(1) DEFAULT 0,
        regra_recorrencia VARCHAR(20) NULL,
        parcelado TINYINT(1) DEFAULT 0,
        qtd_parcelas INT DEFAULT 1,
        pai_id INT NULL,
        anexo_path VARCHAR(255) NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_vencimento (data_vencimento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contas_receber (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NULL,
        numero_documento VARCHAR(50) NULL,
        origem VARCHAR(20) NULL DEFAULT 'MANUAL',
        descricao VARCHAR(255) NULL,
        valor_total DECIMAL(12,2) NOT NULL DEFAULT 0,
        valor_recebido DECIMAL(12,2) NOT NULL DEFAULT 0,
        saldo_aberto DECIMAL(12,2) NOT NULL DEFAULT 0,
        data_competencia DATE NULL,
        data_vencimento DATE NULL,
        data_recebimento DATE NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'ABERTO',
        forma_recebimento VARCHAR(50) NULL,
        categoria_id INT NULL DEFAULT 0,
        observacoes TEXT NULL,
        pdv_venda_id INT NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_vencimento (data_vencimento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Financeiro</title></head><body style="font-family:system-ui;max-width:480px;margin:2rem auto;padding:1.5rem;">';
    echo '<h1>Tabelas financeiras criadas</h1><p><code>suppliers</code>, <code>plano_contas</code>, <code>contas_pagar</code>, <code>contas_receber</code>.</p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=dashboard/index">Ir para o Dashboard</a></p>';
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_fix_finance_tables.php</code> do servidor após usar.</p></body></html>';
} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
