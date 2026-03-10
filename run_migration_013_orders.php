<?php
/**
 * Executa a migration 013 (customer_orders + customer_order_items).
 * Uso: php run_migration_013_orders.php
 * Ou acesse via navegador: BASE_URL/run_migration_013_orders.php
 */
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

$file = __DIR__ . '/database/migrations/013_customer_orders.sql';
if (!is_file($file)) {
    die('Arquivo 013_customer_orders.sql não encontrado.');
}

$sql = file_get_contents($file);
try {
    $pdo->exec($sql);
    echo 'OK: Tabelas customer_orders e customer_order_items criadas.';
} catch (PDOException $e) {
    echo 'Erro: ' . $e->getMessage();
}
