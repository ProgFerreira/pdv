<?php
/**
 * Testa conexão com o banco configurado no .env / .env.local.
 * Acesse: http://localhost/pdv_aliança/test_db_connection.php
 * Apague após validar.
 */
date_default_timezone_set('America/Sao_Paulo');
require __DIR__ . '/config/env.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:system-ui;padding:1.5rem;">';

$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '(não definido)';
$name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
$pass = array_key_exists('DB_PASS', $_ENV) ? $_ENV['DB_PASS'] : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

echo "DB_HOST = {$host}\n";
echo "DB_NAME = {$name}\n";
echo "DB_USER = {$user}\n\n";

if ($host === 'SUBSTITUA_HOSTNAME_HPANEL' || $host === '' || $host === 'localhost' && strpos($name, 'u256572549') === 0) {
    echo "⚠️  Para usar a base da Hostinger no PC:\n";
    echo "   - DB_HOST não pode ser 'localhost' (isso é só no servidor).\n";
    echo "   - Preencha DB_HOST em .env.local com o hostname do hPanel (MySQL remoto).\n";
    echo "   - Ative MySQL remoto e libere seu IP no hPanel.\n\n";
}

try {
    require __DIR__ . '/config/database.php';
    $n = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "✅ Conexão OK\n";
    echo "Usuários na base: {$n}\n";
    echo "\nAbra o sistema: " . (defined('BASE_URL') ? BASE_URL : '/') . "?route=auth/login\n";
} catch (Throwable $e) {
    echo "❌ Falha: " . htmlspecialchars($e->getMessage()) . "\n";
    if (strpos($e->getMessage(), '1045') !== false) {
        echo "\nDica: erro 1045 = IP não liberado no MySQL remoto da Hostinger.\n";
        echo "hPanel → Bancos de dados → MySQL remoto → adicione seu IP público.\n";
    }
}
echo '</pre>';
