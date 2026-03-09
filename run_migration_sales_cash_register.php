<?php
/**
 * Adiciona coluna cash_register_id na tabela sales (para fluxo de caixa).
 * Uso: php run_migration_sales_cash_register.php
 */
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM sales LIKE 'cash_register_id'");
    if ($stmt->rowCount() > 0) {
        echo "Coluna sales.cash_register_id já existe. Nada a fazer.\n";
        exit(0);
    }

    $pdo->exec("ALTER TABLE sales ADD COLUMN cash_register_id INT NULL AFTER user_id");
    $pdo->exec("ALTER TABLE sales ADD INDEX idx_sales_cash_register (cash_register_id)");
    echo "Coluna sales.cash_register_id adicionada com sucesso.\n";
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, "Erro: " . $e->getMessage() . "\n");
    exit(1);
}
