<?php
/**
 * Configuração do Banco de Dados.
 * Variáveis vêm do .env (via config/env.php). Usa $_ENV primeiro (Hostinger e outros hosts).
 */
$envVal = static function (string $key, string $default = ''): string {
    if (array_key_exists($key, $_ENV)) {
        return (string) $_ENV[$key];
    }
    $v = getenv($key);
    return $v !== false ? (string) $v : $default;
};

$dbHost = $envVal('DB_HOST', 'localhost');
$dbName = $envVal('DB_NAME', 'u256572549_pdv');
$dbUser = $envVal('DB_USER', 'u256572549_pdv_admin');
$dbPass = $envVal('DB_PASS', '3N*JOc$5W#o');

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

$appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
$appUrl = trim($appUrl);
// Em produção/web: se APP_URL não estiver definido ou for localhost, monta a base a partir da requisição
if ($appUrl === '' || strpos($appUrl, 'localhost') !== false) {
    if (!empty($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '/index.php';
        $basePath = rtrim(dirname($script), '/\\') . '/';
        $appUrl = $protocol . '://' . $host . $basePath;
    } else {
        $appUrl = 'http://localhost/PDV/';
    }
}
$appUrl = rtrim($appUrl, '/') . '/';
define('BASE_URL', $appUrl);

$isProduction = $envVal('APP_ENV', 'development') === 'production';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    // Só cria banco em MySQL local (WAMP); remoto/Hostinger já existe e não permite CREATE
    $isLocalMysql = in_array(strtolower(DB_HOST), ['localhost', '127.0.0.1', '::1'], true);
    if (!$isProduction && $isLocalMysql) {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', DB_NAME) . "`");
    }
    $pdo->exec("USE `" . str_replace('`', '``', DB_NAME) . "`");
    // Sessão MySQL em horário de Brasília (evita NOW()/CURDATE() em UTC na Hostinger)
    $pdo->exec("SET time_zone = '-03:00'");
} catch (PDOException $e) {
    if ($isProduction) {
        error_log('Database connection error: ' . $e->getMessage());
        header('Content-Type: text/html; charset=utf-8');
        $errView = dirname(__DIR__) . '/views/errors/500.php';
        if (is_file($errView)) {
            include $errView;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro</title></head><body><h1>Erro no servidor</h1><p>Tente novamente mais tarde.</p></body></html>';
        }
        exit;
    }
    die('Erro na conexão com o banco de dados: ' . htmlspecialchars($e->getMessage()));
}
