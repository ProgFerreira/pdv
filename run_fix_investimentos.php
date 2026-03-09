<?php
/**
 * Cria a tabela investimentos (módulo de investimentos).
 * Acesse: https://seu-dominio.com/pdv/run_fix_investimentos.php
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
    $pdo->exec("CREATE TABLE IF NOT EXISTS investimentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        `data` DATE NOT NULL COMMENT 'Data do lançamento',
        pessoa VARCHAR(255) DEFAULT NULL COMMENT 'Sócio, doador, quem emprestou',
        valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        produto VARCHAR(255) DEFAULT NULL COMMENT 'Descrição do produto/equipamento',
        tipo VARCHAR(50) NOT NULL DEFAULT 'compra' COMMENT 'compra, doacao, emprestimo, aporte_socio, investimento_dinheiro',
        estado VARCHAR(20) DEFAULT NULL COMMENT 'novo, usado',
        observacoes TEXT NULL,
        documento_numero VARCHAR(100) NULL,
        quantidade INT NOT NULL DEFAULT 1,
        data_devolucao_prevista DATE NULL,
        forma_pagamento VARCHAR(50) NULL,
        categoria_ativo VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_data (`data`),
        INDEX idx_tipo (tipo),
        INDEX idx_estado (estado),
        INDEX idx_categoria_ativo (categoria_ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Investimentos</title></head><body style="font-family:system-ui;max-width:480px;margin:2rem auto;padding:1.5rem;">';
    echo '<h1>Tabela investimentos criada</h1><p>A tabela <code>investimentos</code> foi criada com sucesso.</p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=investment/index">Ir para Investimentos</a></p>';
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_fix_investimentos.php</code> do servidor após usar.</p></body></html>';
} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
