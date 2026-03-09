<?php
/**
 * Redefine a senha do usuário admin para: admin123
 * Acesse: https://seu-dominio.com/pdv/run_reset_admin_password.php
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

$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    $updated = $stmt->rowCount();
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Senha redefinida</title></head><body style="font-family:system-ui;max-width:480px;margin:2rem auto;padding:1.5rem;">';
    if ($updated > 0) {
        echo '<h1>Senha redefinida</h1><p>Login: <strong>admin</strong><br>Senha: <strong>' . htmlspecialchars($newPassword) . '</strong></p>';
        echo '<p><a href="' . htmlspecialchars(BASE_URL ?? '/', ENT_QUOTES, 'UTF-8') . '?route=auth/login">Ir para o login</a></p>';
    } else {
        echo '<h1>Nenhum usuário atualizado</h1><p>Não existe usuário com username <code>admin</code> na tabela <code>users</code>.</p>';
    }
    echo '<p style="color:#b45309;margin-top:2rem;">Apague <code>run_reset_admin_password.php</code> do servidor após usar.</p>';
    echo '</body></html>';
} catch (PDOException $e) {
    echo '<p style="color:#b91c1c;">Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
