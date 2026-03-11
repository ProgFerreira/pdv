<?php
/**
 * Executa a migration 016 - Tabela notas_fiscais_compras.
 * Uso: php run_migration_notas_fiscais_compras.php
 */
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

$sqlFile = __DIR__ . '/database/migrations/016_notas_fiscais_compras.sql';
if (!is_file($sqlFile)) {
    echo "Arquivo não encontrado: 016_notas_fiscais_compras.sql\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
try {
    $pdo->exec($sql);
    echo "Migration executada: tabela notas_fiscais_compras criada.\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
