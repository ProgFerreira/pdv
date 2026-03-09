<?php

namespace App\Controllers;

use App\Models\CashRegister;

class DashboardController extends BaseController
{
    public function index()
    {
        global $pdo;

        // 0. Check Cash Status
        $cashModel = new CashRegister();
        $openRegister = $cashModel->getOpenRegister($_SESSION['user_id']);

        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $params = ['start' => $startDate, 'end' => $endDate];

        // 1. Period Summary
        $sqlPeriod = "SELECT COUNT(*) as count, SUM(total) as total FROM sales WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlPeriod .= " AND sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }
        $stmtPeriod = $pdo->prepare($sqlPeriod);
        $stmtPeriod->execute($params);
        $periodStats = $stmtPeriod->fetch();

        // 2. Low Stock Alerts
        $sqlStock = "SELECT p.*, s.name as sector_name FROM products p LEFT JOIN sectors s ON p.sector_id = s.id WHERE p.stock <= 5 AND p.active = 1";
        $stockParams = [];
        if ($sectorId !== 'all') {
            $sqlStock .= " AND p.sector_id = :sectorId";
            $stockParams['sectorId'] = $sectorId;
        }
        $sqlStock .= " ORDER BY p.stock ASC LIMIT 5";
        $stmtStock = $pdo->prepare($sqlStock);
        $stmtStock->execute($stockParams);
        $lowStock = $stmtStock->fetchAll();

        // 3. Chart Data (Daily aggregation within range)
        $chartLabels = [];
        $chartData = [];

        // Single query for performance
        $sqlChart = "
            SELECT DATE(created_at) as sale_date, SUM(total) as total 
            FROM sales 
            WHERE DATE(created_at) BETWEEN :start AND :end";
        if ($sectorId !== 'all') {
            $sqlChart .= " AND sector_id = :sectorId";
        }
        $sqlChart .= " GROUP BY DATE(created_at)";
        $stmtChart = $pdo->prepare($sqlChart);
        $stmtChart->execute($params);
        $salesByDate = $stmtChart->fetchAll(\PDO::FETCH_KEY_PAIR); // ['2023-01-01' => 150.00, ...]

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($start, $interval, $end->modify('+1 day'));

        foreach ($period as $dt) {
            $d = $dt->format("Y-m-d");
            $chartLabels[] = $dt->format("d/m");
            $chartData[] = $salesByDate[$d] ?? 0;
        }

        // 4. Financial Widgets (contas_pagar e contas_receber) — tolerante se tabelas não existirem
        $pendingPayable = 0;
        $pendingReceivable = 0;
        try {
            $stmtPayable = $pdo->prepare("SELECT COALESCE(SUM(saldo_aberto), 0) as pending FROM contas_pagar WHERE status != 'PAGO'");
            $stmtPayable->execute([]);
            $pendingPayable = (float) ($stmtPayable->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            // Tabela contas_pagar pode não existir (ex.: instalação nova ou run_fix_finance_tables não executado)
        }
        try {
            $stmtReceivable = $pdo->prepare("SELECT COALESCE(SUM(saldo_aberto), 0) as pending FROM contas_receber WHERE status != 'RECEBIDO'");
            $stmtReceivable->execute([]);
            $pendingReceivable = (float) ($stmtReceivable->fetchColumn() ?: 0);
        } catch (\Throwable $e) {
            // Tabela contas_receber pode não existir
        }

        // Current Cash balance from OPEN register
        if ($openRegister) {
            $cashSummary = $cashModel->getSummary($openRegister['id']);
            $currentBalance = $cashSummary['current_balance'];
        } else {
            $currentBalance = 0;
        }

        $this->render('dashboard/index', [
            'openRegister' => $openRegister,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodStats' => $periodStats,
            'lowStock' => $lowStock,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'pendingPayable' => $pendingPayable ?? 0,
            'pendingReceivable' => $pendingReceivable ?? 0,
            'currentBalance' => $currentBalance ?? 0,
        ]);
    }
}
