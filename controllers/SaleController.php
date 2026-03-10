<?php

namespace App\Controllers;

use App\Models\Sale;
use App\Models\Sector;
use App\Models\AuditLog;

class SaleController
{
    public function index()
    {
        $saleModel = new Sale();
        $sectorModel = new Sector();

        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-d'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'sector_id' => $_GET['sector_id'] ?? null,
            'cash_register_id' => $_GET['cash_register_id'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'customer_query' => $_GET['customer_query'] ?? ''
        ];

        $sales = $saleModel->getAll(100, 0, $filters);
        $totals = $saleModel->getTotals($filters);
        $productsSold = $saleModel->getQuantitySoldByProduct($filters);
        $sectors = $sectorModel->getAll();

        require 'views/sales/index.php';
    }

    public function view()
    {
        $id = $_GET['id'] ?? 0;
        $saleModel = new Sale();
        $sale = $saleModel->getById($id);

        if (!$sale) {
            header('Location: ' . BASE_URL . '?route=sale/index');
            exit;
        }

        require 'views/sales/view.php';
    }

    public function open()
    {
        $id = $_GET['id'] ?? 0;
        $saleModel = new Sale();
        $sale = $saleModel->getById($id);

        if (!$sale) {
            header('Location: ' . BASE_URL . '?route=sale/index');
            exit;
        }

        header('Location: ' . BASE_URL . '?route=pos/index&edit_sale=' . $id);
        exit;
    }

    public function cancel()
    {
        if (!hasPermission('sale_cancel')) {
            header('Location: ' . BASE_URL . '?route=dashboard/index&error=unauthorized');
            exit;
        }

        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $saleModel = new Sale();
        $sale = $saleModel->getById($id);

        if (!$sale) {
            header('Location: ' . BASE_URL . '?route=sale/index');
            exit;
        }

        if (isset($sale['status']) && $sale['status'] === 'cancelled') {
            header('Location: ' . BASE_URL . '?route=sale/view&id=' . $id . '&error=already_cancelled');
            exit;
        }

        if ($saleModel->cancel($id, (int) $_SESSION['user_id'])) {
            $audit = new AuditLog();
            $audit->log('sale_cancel', 'sale', $id, [
                'total' => $sale['total'],
                'payment_method' => $sale['payment_method'] ?? '',
            ]);
            header('Location: ' . BASE_URL . '?route=sale/index&success=cancelled');
            exit;
        }

        header('Location: ' . BASE_URL . '?route=sale/view&id=' . $id . '&error=cancel_failed');
        exit;
    }

    /**
     * Exporta a listagem de vendas (com os filtros atuais) para CSV/Excel.
     * UTF-8 com BOM para o Excel abrir corretamente com acentos.
     */
    public function exportExcel()
    {
        $saleModel = new Sale();

        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-d'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'sector_id' => $_GET['sector_id'] ?? null,
            'cash_register_id' => $_GET['cash_register_id'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'customer_query' => $_GET['customer_query'] ?? ''
        ];

        $sales = $saleModel->getAll(50000, 0, $filters);

        $filename = 'vendas_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            header('Location: ' . BASE_URL . '?route=sale/index&error=export');
            exit;
        }

        // BOM para Excel reconhecer UTF-8
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [
            'ID', 'Data', 'Hora', 'Caixa', 'Cliente', 'Telefone', 'Vendedor', 'Setor',
            'Forma de Pagamento', 'Total (R$)'
        ];
        fputcsv($out, $headers, ';');

        foreach ($sales as $s) {
            $row = [
                $s['id'] ?? '',
                date('d/m/Y', strtotime($s['created_at'] ?? 'now')),
                date('H:i', strtotime($s['created_at'] ?? 'now')),
                $s['cash_register_id'] ?? '-',
                trim((string)($s['customer_name'] ?? '')) ?: '-',
                trim((string)($s['customer_phone'] ?? '')) ?: '-',
                $s['user_name'] ?? '-',
                $s['sector_name'] ?? 'Loja',
                $s['payment_method'] ?? '',
                number_format((float)($s['total'] ?? 0), 2, ',', '.')
            ];
            fputcsv($out, $row, ';');
        }

        fclose($out);
        exit;
    }
}
