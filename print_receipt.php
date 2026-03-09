<?php
/**
 * Endpoint dedicado para impressão de cupom (ESC/POS).
 * POST com JSON (store_name, items, csrf_token, etc).
 * Retorna sempre JSON. Não inclui layout nem outras rotas.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();
$sendJsonError = function (string $message) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
    }
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
};
set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$sendJsonError) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (is_dir($dir) || @mkdir($dir, 0755, true)) {
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'print.log', date('Y-m-d H:i:s') . " [$errno] $errstr in $errfile:$errline\n", FILE_APPEND | LOCK_EX);
    }
    return true;
});
set_exception_handler(function (Throwable $e) use (&$sendJsonError) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (is_dir($dir) || @mkdir($dir, 0755, true)) {
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'print.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n", FILE_APPEND | LOCK_EX);
    }
    $sendJsonError('Erro: ' . $e->getMessage());
});
register_shutdown_function(function () use (&$sendJsonError) {
    $err = error_get_last();
    if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (is_dir($dir) || @mkdir($dir, 0755, true)) {
            @file_put_contents($dir . DIRECTORY_SEPARATOR . 'print.log', date('Y-m-d H:i:s') . ' FATAL ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line'] . "\n", FILE_APPEND | LOCK_EX);
        }
        $sendJsonError('Erro interno. Verifique storage/logs/print.log');
    }
});
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/config/env.php';

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int)(getenv('SESSION_SECURE') ?: 0) === 1;
$samesite = getenv('SESSION_SAMESITE') ?: 'Lax';
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'domain' => '', 'secure' => $secure, 'httponly' => true, 'samesite' => $samesite]);
session_start();

require_once __DIR__ . '/config/helpers.php';

$send = function (array $data) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $out = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($out === false) {
        $out = '{"ok":false,"error":"Erro ao gerar JSON"}';
    }
    echo $out;
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $send(['ok' => false, 'error' => 'Método não permitido. Use POST.']);
}

$raw = file_get_contents('php://input');
$input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
$input = is_array($input) ? $input : [];
if (isset($input['csrf_token'])) {
    $_POST['csrf_token'] = $input['csrf_token'];
}
if (!function_exists('validate_csrf') || !validate_csrf()) {
    $send(['ok' => false, 'error' => 'Sessão ou token inválido. Abra o cupom de novo e tente.']);
}
$isCashReport = isset($input['type']) && (string)$input['type'] === 'cash_report';
if (!isset($_SESSION['user_id']) || !function_exists('hasPermission')) {
    $send(['ok' => false, 'error' => 'Sem permissão para imprimir.']);
}
if ($isCashReport) {
    if (!hasPermission('cash')) {
        $send(['ok' => false, 'error' => 'Sem permissão para imprimir fechamento de caixa.']);
    }
} else {
    if (!hasPermission('pos')) {
        $send(['ok' => false, 'error' => 'Sem permissão para imprimir.']);
    }
}

$errors = [];
if (empty($input['store_name']) || trim((string)($input['store_name'] ?? '')) === '') {
    $errors[] = 'store_name é obrigatório.';
}

if ($isCashReport) {
    if (empty($input['lines']) || !is_array($input['lines'])) {
        $errors[] = 'lines é obrigatório para fechamento de caixa.';
    }
    if ($errors !== []) {
        $send(['ok' => false, 'error' => implode(' ', $errors)]);
    }
    $lines = [];
    foreach ($input['lines'] as $line) {
        $lines[] = [
            'label' => trim((string)($line['label'] ?? '')),
            'value' => trim((string)($line['value'] ?? '')),
        ];
    }
    $payload = [
        'store_name' => trim((string)($input['store_name'] ?? '')),
        'store_cnpj' => trim((string)($input['store_cnpj'] ?? '')),
        'store_address' => trim((string)($input['store_address'] ?? '')),
        'store_phone' => trim((string)($input['store_phone'] ?? '')),
        'title' => trim((string)($input['title'] ?? 'FECHAMENTO DE CAIXA')),
        'order_number' => (string)($input['order_number'] ?? ''),
        'user_name' => trim((string)($input['user_name'] ?? '')),
        'opened_at' => trim((string)($input['opened_at'] ?? '')),
        'closed_at' => trim((string)($input['closed_at'] ?? '')),
        'lines' => $lines,
        'payment_methods' => is_array($input['payment_methods'] ?? null) ? $input['payment_methods'] : [],
        'notes' => trim((string)($input['notes'] ?? 'Sistema PDV')),
    ];
    try {
        $service = new \App\Services\ReceiptPrinterService();
        $service->printCashReport($payload);
        $send(['ok' => true]);
    } catch (Throwable $e) {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'print.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n", FILE_APPEND | LOCK_EX);
        $msg = $e->getMessage();
        if (stripos($msg, 'smbclient') !== false && (stripos($msg, 'not found') !== false || stripos($msg, 'exit code 127') !== false)) {
            $msg = 'Impressão direta não está disponível neste servidor (hosting). Use "Imprimir (tela)" para imprimir pelo navegador.';
        } elseif (stripos($msg, 'Failed to copy file to printer') !== false || stripos($msg, 'Failed to write') !== false) {
            $msg = 'Impressora inacessível. Compartilhe a impressora no Windows (nome igual ao .env) ou use no .env: PRINTER_NAME="\\\\SEU-PC\\NomeCompartilhado"';
        } else {
            $msg = 'Falha ao imprimir: ' . $msg;
        }
        $send(['ok' => false, 'error' => $msg]);
    }
    exit;
}

if (empty($input['items']) || !is_array($input['items'])) {
    $errors[] = 'items é obrigatório e deve ser um array.';
} else {
    foreach ($input['items'] as $i => $item) {
        if (!is_array($item)) {
            $errors[] = "items[$i] deve ser um objeto.";
        } else {
            if (!isset($item['desc'])) {
                $errors[] = "items[$i].desc é obrigatório.";
            }
            if (!isset($item['unit']) || !is_numeric($item['unit'] ?? null)) {
                $errors[] = "items[$i].unit deve ser um número.";
            }
        }
    }
}
if ($errors !== []) {
    $send(['ok' => false, 'error' => implode(' ', $errors)]);
}

$items = [];
foreach ($input['items'] as $item) {
    $items[] = [
        'desc' => (string)($item['desc'] ?? ''),
        'qty' => (int)($item['qty'] ?? 1),
        'unit' => (float)($item['unit'] ?? 0),
    ];
}
$payload = [
    'store_name' => trim((string)($input['store_name'] ?? '')),
    'store_cnpj' => trim((string)($input['store_cnpj'] ?? '')),
    'store_address' => trim((string)($input['store_address'] ?? '')),
    'store_phone' => trim((string)($input['store_phone'] ?? '')),
    'title' => trim((string)($input['title'] ?? 'CUPOM NAO FISCAL')),
    'order_number' => (string)($input['order_number'] ?? ''),
    'customer_name' => trim((string)($input['customer_name'] ?? '')),
    'delivery_address' => trim((string)($input['delivery_address'] ?? '')),
    'datetime' => trim((string)($input['datetime'] ?? date('d/m/Y H:i'))),
    'payment_method' => trim((string)($input['payment_method'] ?? '')),
    'total' => (float)($input['total'] ?? 0),
    'amount_paid' => (float)($input['amount_paid'] ?? 0),
    'change_amount' => (float)($input['change_amount'] ?? 0),
    'discount_amount' => (float)($input['discount_amount'] ?? 0),
    'items' => $items,
    'notes' => trim((string)($input['notes'] ?? 'Obrigado pela preferencia!')),
];

try {
    $service = new \App\Services\ReceiptPrinterService();
    $service->printNonFiscalReceipt($payload);
    $send(['ok' => true]);
} catch (Throwable $e) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    @file_put_contents($dir . DIRECTORY_SEPARATOR . 'print.log', date('Y-m-d H:i:s') . ' ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n", FILE_APPEND | LOCK_EX);
    $msg = $e->getMessage();
    if (stripos($msg, 'smbclient') !== false && (stripos($msg, 'not found') !== false || stripos($msg, 'exit code 127') !== false)) {
        $msg = 'Impressão direta não está disponível neste servidor (hosting). Use "Imprimir (tela)" para imprimir pelo navegador.';
    } elseif (stripos($msg, 'Failed to copy file to printer') !== false || stripos($msg, 'Failed to write') !== false) {
        $msg = 'Impressora inacessível. Compartilhe a impressora no Windows (nome igual ao .env) ou use no .env: PRINTER_NAME="\\\\SEU-PC\\NomeCompartilhado"';
    } else {
        $msg = 'Falha ao imprimir: ' . $msg;
    }
    $send(['ok' => false, 'error' => $msg]);
}
