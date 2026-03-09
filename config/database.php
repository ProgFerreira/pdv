<?php
/**
 * Configuração do Banco de Dados.
 * Variáveis vêm do .env (via config/env.php). Usa $_ENV primeiro (Hostinger e outros hosts).
 */
$dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'pdv_religioso';
$dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$dbPass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

$appUrl = rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost/PDV/', '/') . '/';
define('BASE_URL', $appUrl);

$isProduction = ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development') === 'production';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    // Em produção (ex: Hostinger) o banco já existe; sem permissão CREATE DATABASE
    if (!$isProduction) {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', DB_NAME) . "`");
    }
    $pdo->exec("USE `" . str_replace('`', '``', DB_NAME) . "`");
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
