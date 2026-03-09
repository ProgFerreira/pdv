<?php
/**
 * Executa migrations SQL em ordem (schema.sql, schema_v2 ... schema_v15).
 * Cria tabela migrations para controlar o que já foi executado.
 * Uso: php database/run_migrations.php
 */
date_default_timezone_set('America/Sao_Paulo');
require_once dirname(__DIR__) . '/config/env.php';
require_once dirname(__DIR__) . '/config/database.php';

$migrationsTable = "CREATE TABLE IF NOT EXISTS _migrations (
    name VARCHAR(120) PRIMARY KEY,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$pdo->exec($migrationsTable);

$dir = __DIR__;
$files = glob($dir . '/schema*.sql');
usort($files, function ($a, $b) {
    $a = basename($a);
    $b = basename($b);
    if ($a === 'schema.sql') return -1;
    if ($b === 'schema.sql') return 1;
    return strcmp($a, $b);
});

$run = [];
foreach ($files as $path) {
    $name = basename($path);
    $stmt = $pdo->prepare("SELECT 1 FROM _migrations WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        continue;
    }
    $sql = file_get_contents($path);
    if ($sql === false) {
        echo "Erro ao ler: $name\n";
        exit(1);
    }
    try {
        $pdo->exec($sql);
        $pdo->prepare("INSERT INTO _migrations (name) VALUES (?)")->execute([$name]);
        $run[] = $name;
    } catch (PDOException $e) {
        echo "Erro em $name: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (count($run) === 0) {
    echo "Nenhuma migration pendente.\n";
} else {
    echo "Executadas: " . implode(', ', $run) . "\n";
}
