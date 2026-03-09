<?php

namespace App\Controllers;

use App\Models\CashMovement;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\FinancialAccount;

class CashFlowController
{
    public function index()
    {
        $movementModel = new CashMovement();
        $payableModel = new Payable();
        $receivableModel = new Receivable();

        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-t')
        ];

        // Realized (Movements)
        $realized = $movementModel->getAll($filters);

        // Forecasted (Payerbles/Receivables by Due Date)
        $forecastedP = $payableModel->getAll(['status' => 'ABERTO', 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]);
        $forecastedR = $receivableModel->getAll(['status' => 'ABERTO', 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']]);

        require 'views/report/cash_flow.php';
    }

    public function dashboard()
    {
        $payableModel = new Payable();
        $receivableModel = new Receivable();
        $accModel = new FinancialAccount();

        $data = [
            'accounts' => $accModel->getAll(true),
            'payables_today' => $payableModel->getAll(['status' => 'ABERTO', 'end_date' => date('Y-m-d')]),
            'receivables_today' => $receivableModel->getAll(['status' => 'ABERTO', 'end_date' => date('Y-m-d')]),
            'payables_7d' => $payableModel->getAll(['status' => 'ABERTO', 'end_date' => date('Y-m-d', strtotime('+7 days'))]),
            'receivables_7d' => $receivableModel->getAll(['status' => 'ABERTO', 'end_date' => date('Y-m-d', strtotime('+7 days'))]),
        ];

        require 'views/finance/dashboard.php';
    }
}
