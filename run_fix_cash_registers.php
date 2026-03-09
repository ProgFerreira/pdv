<?php
/**
 * Cria tabelas cash_registers e cash_movements e coluna sales.cash_register_id.
 * Acesse: https://seu-dominio.com/pdv/run_fix_cash_registers.php
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

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Caixa</title></head><body style="font-family:system-ui;max-width:480px;margin:2rem auto;padding:1.5rem;">';
    echo '<h1>Tabelas de caixa criadas</h1><p><code>cash_registers</code>, <code>cash_movements</code> e coluna <code>sales.cash_register_id</code>.</p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=dashboard/index">Ir para o sistema</a></p>';
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_fix_cash_registers.php</code> do servidor após usar.</p></body></html>';
} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
