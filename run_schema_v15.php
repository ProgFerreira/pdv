<?php
/**
 * Executa o schema_v15_comprehensive_finance.sql usando as credenciais do .env.
 * Uso: php run_schema_v15.php
 */
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$file = __DIR__ . '/database/schema_v15_comprehensive_finance.sql';
if (!is_file($file)) {
    fwrite(STDERR, "Arquivo não encontrado: $file\n");
    exit(1);
}

$sql = file_get_contents($file);
// Remove comentários de linha e blocos
$sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
// Separa por ; (statements)
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function ($s) { return strlen($s) > 5; }
);

$ok = 0;
$skip = 0;
foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    try {
        $pdo->exec($stmt);
        $ok++;
        echo ".";
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Ignorar "already exists" e "duplicate"
        if (stripos($msg, 'already exists') !== false || stripos($msg, 'Duplicate') !== false) {
            $skip++;
            echo "s";
        } else {
            echo "\nErro: " . $msg . "\nStatement: " . substr($stmt, 0, 80) . "...\n";
        }
    }
}

echo "\nConcluído: $ok executados, $skip ignorados (já existiam).\n";
