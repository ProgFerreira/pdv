<?php

/**
 * Escapa saída para HTML (proteção XSS).
 * Regra: toda string vinda do banco (nomes, descrições, mensagens) → e().
 * IDs numéricos em atributos podem ficar sem e().
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Formata valor numérico como moeda em R$ (padrão BR).
 */
function money($value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

/**
 * Formata data para exibição (dd/mm/yyyy ou dd/mm/yyyy HH:mm).
 * @param string|DateTimeInterface|null $date
 */
function date_br($date, bool $withTime = false): string
{
    if ($date === null || $date === '') {
        return '';
    }
    if (is_string($date)) {
        try {
            $date = new DateTimeImmutable($date);
        } catch (Exception $e) {
            return '';
        }
    }
    return $date->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
}

/**
 * Verifica se o usuário logado tem a permissão indicada.
 * Admin sempre tem acesso.
 */
function hasPermission(string $key): bool
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        return true;
    }
    $perms = $_SESSION['permissions'] ?? [];
    return in_array($key, $perms, true);
}

/**
 * CSRF: gera ou retorna o token da sessão.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF: retorna o input hidden para incluir em formulários POST/PUT/DELETE.
 */
function csrf_field(): string
{
    $t = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * CSRF: valida o token enviado no request. Retorna true se válido.
 * Para POST JSON, o index.php preenche $_POST['csrf_token'] a partir do body antes de chamar esta função.
 */
function validate_csrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return $token !== '' && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Rate limit login: retorna true se o IP está bloqueado (excedeu tentativas).
 * Usa storage/rate_limit_login.json. Janela e máx tentativas vêm do .env.
 */
function login_rate_limit_exceeded(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $maxAttempts = (int) (getenv('RATE_LIMIT_LOGIN_ATTEMPTS') ?: 5);
    $windowSec = (int) (getenv('RATE_LIMIT_LOGIN_WINDOW') ?: 900);
    $storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
    $file = $storageDir . DIRECTORY_SEPARATOR . 'rate_limit_login.json';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }
    $now = time();
    $data = [];
    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $data = json_decode($raw, true) ?: [];
        }
    }
    $key = md5($ip);
    if (!isset($data[$key])) {
        return false;
    }
    $entry = $data[$key];
    if ($now > $entry['until']) {
        return false;
    }
    return $entry['count'] >= $maxAttempts;
}

/**
 * Registra uma tentativa de login falha (incrementa contador por IP).
 */
function login_rate_limit_record_failure(): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $maxAttempts = (int) (getenv('RATE_LIMIT_LOGIN_ATTEMPTS') ?: 5);
    $windowSec = (int) (getenv('RATE_LIMIT_LOGIN_WINDOW') ?: 900);
    $storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage';
    $file = $storageDir . DIRECTORY_SEPARATOR . 'rate_limit_login.json';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0755, true);
    }
    $now = time();
    $data = [];
    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if ($raw !== false) {
            $data = json_decode($raw, true) ?: [];
        }
    }
    $key = md5($ip);
    if (!isset($data[$key]) || $now > ($data[$key]['until'] ?? 0)) {
        $data[$key] = ['count' => 1, 'until' => $now + $windowSec];
    } else {
        $data[$key]['count'] = ($data[$key]['count'] ?? 0) + 1;
    }
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

/**
 * Lê e remove a mensagem flash da sessão para exibir na view.
 * @return array{type: string, message: string}|null
 */
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Helpers de cálculo para Ficha Técnica (formação de preço)
$calcFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'Calc.php';
if (is_file($calcFile)) {
    require_once $calcFile;
}
