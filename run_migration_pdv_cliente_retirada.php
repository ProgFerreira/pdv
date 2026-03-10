<?php
/**
 * Executa as migrations 008 (endereço do cliente + delivery_address) e 009 (is_pickup).
 * Necessário para: cadastro de novo cliente no PDV com endereço, e impressão de "Cliente retira" no cupom.
 * Uso: php run_migration_pdv_cliente_retirada.php
 * Ou acesse pelo navegador (uma vez): /run_migration_pdv_cliente_retirada.php
 */
date_default_timezone_set('America/Sao_Paulo');
require_once __DIR__ . '/config/env.php';

$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'pdv';
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

header('Content-Type: text/html; charset=utf-8');
$isCli = (php_sapi_name() === 'cli');

function out($msg) {
    global $isCli;
    if ($isCli) {
        echo $msg . "\n";
    } else {
        echo nl2br(htmlspecialchars($msg)) . "<br>";
    }
}

try {
    $pdo = new PDO(
        "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]
    );
} catch (PDOException $e) {
    out("Erro de conexão: " . $e->getMessage());
    exit(1);
}

out("Executando migrations PDV (cliente + retirada)...");

$dir = __DIR__ . '/database/migrations';
$files = ['008_customer_address_delivery.sql', '009_sales_is_pickup.sql'];
$ok = true;

foreach ($files as $name) {
    $path = $dir . '/' . $name;
    if (!is_file($path)) {
        out("Arquivo não encontrado: $name");
        continue;
    }
    $sql = file_get_contents($path);
    if ($sql === false) {
        out("Erro ao ler: $name");
        $ok = false;
        continue;
    }
    $sql = preg_replace('/--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    try {
        foreach ($statements as $stmt) {
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
        }
        out("OK: $name");
    } catch (PDOException $e) {
        out("Erro em $name: " . $e->getMessage());
        $ok = false;
    }
}

if ($ok) {
    out("Concluído. Agora você pode cadastrar novo cliente no PDV e usar 'Retirada no local' (cupom imprime 'Cliente retira').");
} else {
    out("Alguma migration falhou. Verifique as mensagens acima.");
}
