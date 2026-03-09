<?php
/**
 * Cria a tabela sectors e colunas sector_id se não existirem.
 * Acesse: https://seu-dominio.com/pdv/run_fix_sectors.php
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
    $pdo->exec("CREATE TABLE IF NOT EXISTS sectors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("INSERT IGNORE INTO sectors (id, name, active) VALUES (1, 'Principal', 1)");

    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'sector_id'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN sector_id INT NULL DEFAULT 1");
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'sector_id'");
    if ($stmt && (int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN sector_id INT NULL DEFAULT 1");
    }

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Setores</title></head><body style="font-family:system-ui;max-width:480px;margin:2rem auto;padding:1.5rem;">';
    echo '<h1>Tabela sectors criada</h1><p>Setor padrão <strong>Principal</strong> (id=1) inserido. Colunas sector_id adicionadas em products e users se não existiam.</p>';
    echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=dashboard/index">Ir para o sistema</a></p>';
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_fix_sectors.php</code> do servidor após usar.</p></body></html>';
} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
