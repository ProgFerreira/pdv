<?php
ob_start(null, 0);
date_default_timezone_set('America/Sao_Paulo');
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/config/env.php';

// Sessão segura: httponly, samesite, secure em HTTPS
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) (getenv('SESSION_SECURE') ?: 0) === 1;
$samesite = getenv('SESSION_SAMESITE') ?: 'Lax';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => $samesite
]);
session_start();

// Headers de segurança (antes de qualquer output)
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self';");

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$isProduction = (getenv('APP_ENV') ?: 'development') === 'production';
set_exception_handler(function (Throwable $e) use ($isProduction) {
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents($logDir . DIRECTORY_SEPARATOR . 'error.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND | LOCK_EX);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if ($isProduction) {
        $view = __DIR__ . '/views/errors/500.php';
        include __DIR__ . '/views/errors/500.php';
    } else {
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    }
});

// Verificar se a migration v12 (permissões/auditoria) foi executada
try {
    $stmt = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'permissions'");
    if (!$stmt || !$stmt->fetch()) {
        $migrationHint = (function () {
            $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/PDV';
            $path = str_replace('\\', '/', __DIR__);
            return $base . '/run_migration_v12.php';
        })();
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        echo '<title>Migration necessária</title>';
        echo '<style>body{font-family:system-ui,sans-serif;max-width:560px;margin:80px auto;padding:24px;background:#f8fafc;}';
        echo '.box{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:32px;box-shadow:0 4px 6px rgba(0,0,0,.05);}';
        echo 'h1{color:#0f172a;font-size:1.5rem;margin:0 0 16px;}';
        echo 'p{color:#475569;line-height:1.6;margin:0 0 16px;}';
        echo 'code{background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:.9em;}';
        echo 'a{color:#0284c7;font-weight:600;} a:hover{text-decoration:underline;}';
        echo '</style></head><body><div class="box">';
        echo '<h1>⚠️ Tabelas de segurança não encontradas</h1>';
        echo '<p>As tabelas <code>permissions</code>, <code>role_permissions</code> e <code>audit_logs</code> não existem. Execute a migration v12 antes de usar o sistema.</p>';
        echo '<p><strong>No terminal:</strong><br><code>php run_migration_v12.php</code></p>';
        echo '<p><strong>Ou acesse:</strong> <a href="' . htmlspecialchars($migrationHint) . '">' . htmlspecialchars($migrationHint) . '</a></p>';
        echo '<p>Depois, faça logout e login novamente para carregar as permissões.</p>';
        echo '</div></body></html>';
        exit;
    }
} catch (PDOException $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro</title></head><body style="font-family:sans-serif;padding:2rem;">';
    echo '<h1>Erro ao verificar banco</h1><p>' . htmlspecialchars($e->getMessage()) . '</p></body></html>';
    exit;
}

// Roteamento simples
$route = $_GET['route'] ?? 'auth/login';

$parts = explode('/', $route);
$controllerName = ucfirst($parts[0]) . 'Controller';
$actionName = $parts[1] ?? 'index';

// Proteção de rotas (exceto login)
if (!isset($_SESSION['user_id']) && $controllerName !== 'AuthController') {
    header('Location: ' . BASE_URL . '?route=auth/login');
    exit;
}

// Controle de Acesso (Papéis)
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Controle de acesso por tela (permissões)
$routeKey = $parts[0] . '/' . $actionName;
$routesPermissions = require __DIR__ . '/config/routes_permissions.php';
$skipPermissionCheck = in_array($routeKey, ['auth/login', 'auth/logout', 'auth/switchSector'], true);

if (!$skipPermissionCheck && isset($_SESSION['user_id']) && isset($routesPermissions[$routeKey])) {
    $requiredPerm = $routesPermissions[$routeKey];
    if (!hasPermission($requiredPerm)) {
        $audit = new \App\Models\AuditLog();
        $audit->log('access_denied', 'route', null, ['route' => $routeKey, 'required' => $requiredPerm]);
        header('Location: ' . BASE_URL . '?route=dashboard/index&error=unauthorized');
        exit;
    }
}

// POST com JSON: ler body uma vez e expor csrf_token para validate_csrf + body para o controller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $jsonBody = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : [];
    $jsonBody = is_array($jsonBody) ? $jsonBody : [];
    if (isset($jsonBody['csrf_token'])) {
        $_POST['csrf_token'] = $jsonBody['csrf_token'];
    }
    $GLOBALS['_JSON_BODY'] = $jsonBody;
}

// CSRF: validar em todas as requisições POST (e PUT/DELETE via _method)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validate_csrf()) {
    if (isset($_SESSION['user_id'])) {
        $audit = new \App\Models\AuditLog();
        $audit->log('csrf_failed', 'route', null, ['route' => $routeKey ?? ($_GET['route'] ?? '')]);
    }
    header('HTTP/1.1 403 Forbidden');
    $errView = __DIR__ . '/views/errors/403.php';
    if (is_file($errView)) {
        include $errView;
    } else {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Acesso negado</title></head><body><h1>403 - Token inválido</h1><p>Recarregue a página e tente novamente.</p></body></html>';
    }
    exit;
}

// Layout data (header): setor atual e lista de setores — evita lógica de banco na view (pular para print/receipt)
if (($routeKey ?? '') !== 'print/receipt') {
    if (isset($_SESSION['user_id'])) {
        $sectorModel = new \App\Models\Sector();
        $allSectors = $sectorModel->getAll();
        $currentSectorId = $_SESSION['sector_id'] ?? 1;
        $currentSector = array_filter($allSectors, fn($s) => (string) ($s['id'] ?? '') === (string) $currentSectorId);
        $currentSectorName = ($currentSectorId === 'all') ? 'Global' : (!empty($currentSector) ? reset($currentSector)['name'] : 'Indefinido');
    } else {
        $allSectors = [];
        $currentSectorId = null;
        $currentSectorName = '';
    }
}

$controllerFqcn = 'App\\Controllers\\' . ucfirst($parts[0]) . 'Controller';
if (class_exists($controllerFqcn)) {
    $controller = new $controllerFqcn();
    if (method_exists($controller, $actionName)) {
        $controller->$actionName();
    } else {
        if (!headers_sent()) {
            http_response_code(404);
        }
        include __DIR__ . '/views/errors/404.php';
    }
} else {
    if ($route === 'auth/login') {
        echo "Sistema de PDV Iniciado. Configure o AuthController.";
    } else {
        if (!headers_sent()) {
            http_response_code(404);
        }
        include __DIR__ . '/views/errors/404.php';
    }
}
