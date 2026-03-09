<?php
/**
 * Adiciona colunas status, cancelled_at, cancelled_by na tabela sales.
 * Acesse: https://seu-dominio.com/pdv/run_fix_sales_status.php
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
    $cols = ['status' => "ADD COLUMN status VARCHAR(32) NULL DEFAULT 'completed' AFTER change_amount",
             'cancelled_at' => "ADD COLUMN cancelled_at TIMESTAMP NULL AFTER status",
             'cancelled_by' => "ADD COLUMN cancelled_by INT NULL AFTER cancelled_at"];
    foreach ($cols as $name => $addSql) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'sales' AND column_name = '$name'");
        if ($stmt && (int) $stmt->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE sales " . $addSql);
        }
    }

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Vendas</title></head><body style="font-family:system-ui;max-width:480px;margin:2rem auto;padding:1.5rem;">';
    echo '<h1>Colunas de status em sales</h1><p>Colunas <code>status</code>, <code>cancelled_at</code>, <code>cancelled_by</code> adicionadas em <code>sales</code> (se não existiam).</p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=sale/index">Ir para Vendas</a></p>';
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_fix_sales_status.php</code> do servidor após usar.</p></body></html>';
} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
