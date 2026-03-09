<?php

namespace App\Controllers;

use App\Services\ReceiptPrinterService;

/**
 * Endpoint de impressão de cupom não fiscal (ESC/POS).
 * POST ?route=print/receipt com JSON no body.
 */
class PrintController
{
    public function receipt(): void
    {
        $send = function (array $data): void {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        };

        $payload = null;
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $send(['ok' => false, 'error' => 'Método não permitido. Use POST.']);
                return;
            }

            $input = $GLOBALS['_JSON_BODY'] ?? null;
            if ($input === null) {
                $raw = file_get_contents('php://input');
                $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
            }
            if (!is_array($input)) {
                $input = [];
            }

            $errors = $this->validatePayload($input);
            if ($errors !== []) {
                $send(['ok' => false, 'error' => implode(' ', $errors)]);
                return;
            }

            $payload = $this->buildPayload($input);
            $service = new ReceiptPrinterService();
            $service->printNonFiscalReceipt($payload);
            $send(['ok' => true]);
        } catch (\Throwable $e) {
            $this->logPrintError($e, $payload);
            $send([
                'ok' => false,
                'error' => 'Falha ao imprimir: ' . $e->getMessage(),
            ]);
        }
    }

    private function logPrintError(\Throwable $e, ?array $payload): void
    {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $line = date('Y-m-d H:i:s') . ' ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        if ($payload !== null) {
            $line .= ' order=' . ($payload['order_number'] ?? '');
        }
        $line .= "\n";
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'print.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function validatePayload(array $input): array
    {
        $errors = [];
        if (empty($input['store_name']) || trim((string) $input['store_name']) === '') {
            $errors[] = 'store_name é obrigatório.';
        }
        if (empty($input['items']) || !is_array($input['items'])) {
            $errors[] = 'items é obrigatório e deve ser um array.';
        } else {
            foreach ($input['items'] as $i => $item) {
                if (!is_array($item)) {
                    $errors[] = "items[$i] deve ser um objeto.";
                } else {
                    if (empty($item['desc']) && !isset($item['desc'])) {
                        $errors[] = "items[$i].desc é obrigatório.";
                    }
                    if (!isset($item['qty']) || (is_numeric($item['qty']) && (int) $item['qty'] < 0)) {
                        $errors[] = "items[$i].qty deve ser um número >= 0.";
                    }
                    if (!isset($item['unit']) || !is_numeric($item['unit'])) {
                        $errors[] = "items[$i].unit deve ser um número.";
                    }
                }
            }
        }
        return $errors;
    }

    private function buildPayload(array $input): array
    {
        $items = [];
        foreach ($input['items'] as $item) {
            $items[] = [
                'desc' => (string) ($item['desc'] ?? ''),
                'qty' => (int) ($item['qty'] ?? 1),
                'unit' => (float) ($item['unit'] ?? 0),
            ];
        }
        return [
            'store_name' => trim((string) ($input['store_name'] ?? '')),
            'title' => trim((string) ($input['title'] ?? 'CUPOM NAO FISCAL')),
            'order_number' => (string) ($input['order_number'] ?? ''),
            'customer_name' => trim((string) ($input['customer_name'] ?? '')),
            'datetime' => trim((string) ($input['datetime'] ?? date('Y-m-d H:i:s'))),
            'payment_method' => trim((string) ($input['payment_method'] ?? '')),
            'items' => $items,
            'notes' => trim((string) ($input['notes'] ?? 'Obrigado pela preferencia!')),
        ];
    }
}
