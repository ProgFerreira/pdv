<?php

declare(strict_types=1);

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Exception;

/**
 * Serviço de impressão de cupom não fiscal ESC/POS (Epson TM-T20X e compatíveis).
 * Suporta impressora local (USB) ou compartilhada na rede Windows.
 */
class ReceiptPrinterService
{
    private string $printerName;
    private int $printerCols;
    private ?string $printerCodepage;
    private string $logPath;

    public function __construct()
    {
        $this->printerName = (string) ($_ENV['PRINTER_NAME'] ?? getenv('PRINTER_NAME') ?: '');
        $this->printerCols = (int) ($_ENV['PRINTER_COLS'] ?? getenv('PRINTER_COLS') ?: 32);
        $codepage = $_ENV['PRINTER_CODEPAGE'] ?? getenv('PRINTER_CODEPAGE');
        $this->printerCodepage = ($codepage !== null && $codepage !== false && $codepage !== '') ? trim((string) $codepage) : null;
        $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $this->logPath = $logDir . DIRECTORY_SEPARATOR . 'print.log';
    }

    /**
     * Formata uma linha em duas colunas: texto à esquerda e valor à direita.
     */
    public function formatLineColumns(string $left, string $right, int $cols = 0): string
    {
        $cols = $cols > 0 ? $cols : $this->printerCols;
        $left = $this->normalizeText($left);
        $right = $this->normalizeText($right);
        $lenLeft = $this->strWidth($left);
        $lenRight = $this->strWidth($right);
        $space = $cols - $lenLeft - $lenRight;
        if ($space < 1) {
            $space = 1;
        }
        return $left . str_repeat(' ', $space) . $right;
    }

    /**
     * Normaliza texto para a impressora: preferir UTF-8; se PRINTER_CODEPAGE estiver setado,
     * converte (ex: CP860, CP1252). Fallback: remove caracteres não suportados.
     */
    public function normalizeText(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        if ($this->printerCodepage !== null) {
            $target = $this->mapCodepageToEncoding($this->printerCodepage);
            if ($target !== null) {
                $converted = @iconv('UTF-8', $target . '//IGNORE//TRANSLIT', $text);
                if ($converted !== false) {
                    return $converted;
                }
                $converted = @iconv('UTF-8', $target . '//IGNORE', $text);
                if ($converted !== false) {
                    return $converted;
                }
            }
        }
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        return $clean !== false ? $clean : (preg_replace('/[^\x20-\x7E]/', '', $text) ?: $text);
    }

    private function mapCodepageToEncoding(string $cp): ?string
    {
        $map = [
            'CP860' => 'CP860',
            'CP850' => 'CP850',
            'CP1252' => 'CP1252',
            'CP437' => 'CP437',
            'ISO-8859-1' => 'ISO-8859-1',
        ];
        $upper = strtoupper($cp);
        return $map[$upper] ?? $map[$cp] ?? $cp;
    }

    private function strWidth(string $s): int
    {
        if (function_exists('mb_strwidth')) {
            return mb_strwidth($s, 'UTF-8');
        }
        return strlen($s);
    }

    /**
     * Imprime cupom não fiscal a partir do payload (store_name, title, items, etc).
     * Layout 80mm: cabeçalho em destaque, itens em colunas, total em negrito, cut no final.
     */
    public function printNonFiscalReceipt(array $payload): void
    {
        if ($this->printerName === '') {
            throw new Exception('PRINTER_NAME não configurado no .env');
        }

        $printer = null;
        try {
            $connector = new WindowsPrintConnector($this->printerName);
            $printer = new Printer($connector);

            if ($this->printerCodepage !== null) {
                $table = $this->codepageToTableNumber($this->printerCodepage);
                if ($table !== null) {
                    try {
                        $printer->selectCharacterTable($table);
                    } catch (Exception $e) {
                        // Perfil da impressora pode não ter essa tabela; ignora
                    }
                }
            }

            $cols = $this->printerCols;
            $storeName = $this->normalizeText((string) ($payload['store_name'] ?? ''));
            $storeCnpj = $this->normalizeText((string) ($payload['store_cnpj'] ?? ''));
            $storeAddress = $this->normalizeText((string) ($payload['store_address'] ?? ''));
            $storePhone = $this->normalizeText((string) ($payload['store_phone'] ?? ''));
            $title = $this->normalizeText((string) ($payload['title'] ?? 'CUPOM NAO FISCAL'));
            $orderNumber = (string) ($payload['order_number'] ?? '');
            $customerName = $this->normalizeText((string) ($payload['customer_name'] ?? ''));
            $customerPhone = $this->normalizeText((string) ($payload['customer_phone'] ?? ''));
            $deliveryAddress = $this->normalizeText((string) ($payload['delivery_address'] ?? ''));
            $isPickup = !empty($payload['is_pickup']);
            $datetime = (string) ($payload['datetime'] ?? date('d/m/Y H:i'));
            $paymentMethod = $this->normalizeText((string) ($payload['payment_method'] ?? ''));
            $totalGeral = (float) ($payload['total'] ?? 0);
            $amountPaid = (float) ($payload['amount_paid'] ?? 0);
            $changeAmount = (float) ($payload['change_amount'] ?? 0);
            $discountAmount = (float) ($payload['discount_amount'] ?? 0);
            $items = $payload['items'] ?? [];
            $notes = $this->normalizeText((string) ($payload['notes'] ?? 'Obrigado pela preferencia!'));

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->text($storeName . "\n");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            if ($storeCnpj !== '') {
                $printer->text($storeCnpj . "\n");
            }
            if ($storeAddress !== '') {
                $printer->text($storeAddress . "\n");
            }
            if ($storePhone !== '') {
                $printer->text($storePhone . "\n");
            }
            $printer->text(str_repeat('-', $cols) . "\n");
            $printer->text($title . "\n");
            $printer->text($this->normalizeText('Venda: # ') . $orderNumber . "\n");
            $printer->text($this->normalizeText('Data: ') . $datetime . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text(str_repeat('-', $cols) . "\n");

            $printer->text($this->formatLineColumns($this->normalizeText('ITEM'), $this->normalizeText('QTD') . '  ' . $this->normalizeText('VALOR'), $cols) . "\n");
            $printer->text(str_repeat('-', $cols) . "\n");

            foreach ($items as $item) {
                $desc = $this->normalizeText((string) ($item['desc'] ?? ''));
                $qty = (int) ($item['qty'] ?? 1);
                $unit = (float) ($item['unit'] ?? 0);
                $subtotal = round($qty * $unit, 2);
                if ($totalGeral <= 0) {
                    $totalGeral += $subtotal;
                }
                $descMaxLen = $cols;
                if ($this->strWidth($desc) > $descMaxLen) {
                    $desc = mb_strimwidth($desc, 0, $descMaxLen, '', 'UTF-8');
                }
                $printer->text($desc . "\n");
                $unitStr = $this->normalizeText('Un: R$ ') . number_format($unit, 2, ',', '.');
                $qtyStr = 'x ' . $qty;
                $subStr = 'R$ ' . number_format($subtotal, 2, ',', '.');
                $printer->text($this->formatLineColumns($unitStr, $qtyStr . '  ' . $subStr, $cols) . "\n");
            }

            $printer->text(str_repeat('-', $cols) . "\n");
            if ($discountAmount > 0) {
                $printer->text($this->formatLineColumns($this->normalizeText('Subtotal:'), 'R$ ' . number_format($totalGeral + $discountAmount, 2, ',', '.'), $cols) . "\n");
                $printer->text($this->formatLineColumns($this->normalizeText('Desconto:'), '- R$ ' . number_format($discountAmount, 2, ',', '.'), $cols) . "\n");
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text($this->formatLineColumns($this->normalizeText('TOTAL A PAGAR:'), 'R$ ' . number_format($totalGeral, 2, ',', '.'), $cols) . "\n");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text(str_repeat('-', $cols) . "\n");
            $printer->text($this->formatLineColumns($this->normalizeText('Forma Pagto:'), $paymentMethod, $cols) . "\n");
            $printer->text($this->formatLineColumns($this->normalizeText('Valor Pago:'), 'R$ ' . number_format($amountPaid, 2, ',', '.'), $cols) . "\n");
            $printer->text($this->formatLineColumns($this->normalizeText('Troco:'), 'R$ ' . number_format($changeAmount, 2, ',', '.'), $cols) . "\n");
            $printer->text(str_repeat('-', $cols) . "\n");

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            if ($customerName !== '') {
                $printer->text($this->normalizeText('Cliente: ') . $customerName . "\n");
                if ($customerPhone !== '') {
                    $printer->text($this->normalizeText('Tel: ') . $customerPhone . "\n");
                }
            } else {
                $printer->text($this->normalizeText('Consumidor Final') . "\n");
            }
            if ($isPickup) {
                $printer->text($this->normalizeText('Retirada no local') . "\n");
            } elseif ($deliveryAddress !== '') {
                $printer->text($this->normalizeText('Entrega: ') . $deliveryAddress . "\n");
            }
            $printer->text("\n" . $notes . "\n");
            $printer->text($this->normalizeText('Sistema PDV v1.0') . "\n");
            $printer->text($this->normalizeText('Nao e documento fiscal') . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            // Corte com apenas 1 linha de avanço (default era 3 = muito papel em branco)
            $printer->cut(Printer::CUT_FULL, 1);
            // $printer->pulse(); // Gaveta (opcional)
        } finally {
            if ($printer !== null) {
                $printer->close();
            }
        }
    }

    /**
     * Imprime fechamento de caixa (cupom não fiscal) no mesmo layout térmico.
     * Payload: store_*, title, order_number, user_name, opened_at, closed_at, lines[], payment_methods[], notes.
     */
    public function printCashReport(array $payload): void
    {
        if ($this->printerName === '') {
            throw new Exception('PRINTER_NAME não configurado no .env');
        }

        $printer = null;
        try {
            $connector = new WindowsPrintConnector($this->printerName);
            $printer = new Printer($connector);

            if ($this->printerCodepage !== null) {
                $table = $this->codepageToTableNumber($this->printerCodepage);
                if ($table !== null) {
                    try {
                        $printer->selectCharacterTable($table);
                    } catch (Exception $e) {
                        // Perfil da impressora pode não ter essa tabela; ignora
                    }
                }
            }

            $cols = $this->printerCols;
            $storeName = $this->normalizeText((string) ($payload['store_name'] ?? ''));
            $storeCnpj = $this->normalizeText((string) ($payload['store_cnpj'] ?? ''));
            $storeAddress = $this->normalizeText((string) ($payload['store_address'] ?? ''));
            $storePhone = $this->normalizeText((string) ($payload['store_phone'] ?? ''));
            $title = $this->normalizeText((string) ($payload['title'] ?? 'FECHAMENTO DE CAIXA'));
            $orderNumber = (string) ($payload['order_number'] ?? '');
            $userName = $this->normalizeText((string) ($payload['user_name'] ?? ''));
            $openedAt = (string) ($payload['opened_at'] ?? '');
            $closedAt = (string) ($payload['closed_at'] ?? '');
            $lines = $payload['lines'] ?? [];
            $paymentMethods = $payload['payment_methods'] ?? [];
            $notes = $this->normalizeText((string) ($payload['notes'] ?? 'Sistema PDV'));

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->text($storeName . "\n");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            if ($storeCnpj !== '') {
                $printer->text($storeCnpj . "\n");
            }
            if ($storeAddress !== '') {
                $printer->text($storeAddress . "\n");
            }
            if ($storePhone !== '') {
                $printer->text($storePhone . "\n");
            }
            $printer->text(str_repeat('-', $cols) . "\n");
            $printer->text($title . "\n");
            $printer->text($this->normalizeText('Caixa: # ') . $orderNumber . "\n");
            $printer->text($this->normalizeText('Operador: ') . $userName . "\n");
            $printer->text($this->normalizeText('Abertura: ') . $openedAt . "\n");
            $printer->text($this->normalizeText('Fechamento: ') . $closedAt . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text(str_repeat('-', $cols) . "\n");

            foreach ($lines as $line) {
                $label = $this->normalizeText((string) ($line['label'] ?? ''));
                $value = (string) ($line['value'] ?? '');
                if ($label === '' && $value === '') {
                    continue;
                }
                $printer->text($this->formatLineColumns($label, $value, $cols) . "\n");
            }

            $printer->text(str_repeat('-', $cols) . "\n");
            $printer->text($this->normalizeText('Vendas por Pagamento') . "\n");
            $printer->text(str_repeat('-', $cols) . "\n");
            foreach ($paymentMethods as $pm) {
                $name = $this->normalizeText((string) ($pm['payment_method'] ?? $pm['name'] ?? ''));
                $count = (int) ($pm['count'] ?? 0);
                $total = (float) ($pm['total'] ?? 0);
                $lineLabel = $name . ' (' . $count . 'x)';
                $printer->text($this->formatLineColumns($lineLabel, 'R$ ' . number_format($total, 2, ',', '.'), $cols) . "\n");
            }
            $printer->text(str_repeat('-', $cols) . "\n");

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\n" . $notes . "\n");
            $printer->text($this->normalizeText('Nao e documento fiscal') . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            $printer->cut(Printer::CUT_FULL, 1);
        } finally {
            if ($printer !== null) {
                $printer->close();
            }
        }
    }

    private function codepageToTableNumber(string $cp): ?int
    {
        $map = [
            'CP860' => 16,
            'CP850' => 2,
            'CP1252' => 0,
            'CP437' => 0,
        ];
        $upper = strtoupper($cp);
        return $map[$upper] ?? null;
    }

    public function getPrinterName(): string
    {
        return $this->printerName;
    }

    public function getPrinterCols(): int
    {
        return $this->printerCols;
    }

    public function logError(string $message, array $payloadResumido = []): void
    {
        $line = date('Y-m-d H:i:s') . ' ' . $message;
        if ($payloadResumido !== []) {
            $line .= ' ' . json_encode($payloadResumido, JSON_UNESCAPED_UNICODE);
        }
        $line .= "\n";
        @file_put_contents($this->logPath, $line, FILE_APPEND | LOCK_EX);
    }
}
