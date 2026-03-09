<?php

namespace App\Controllers;

use App\Models\Payable;
use App\Models\Supplier;
use App\Models\PlanoContas;
use App\Models\FinancialAccount;

class PayableController
{
    private $payableModel;

    public function __construct()
    {
        $this->payableModel = new Payable();
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
            'supplier_id' => $_GET['supplier_id'] ?? null,
            'categoria_id' => $_GET['categoria_id'] ?? null
        ];

        $payables = $this->payableModel->getAll($filters);
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();
        $planoModel = new PlanoContas();
        $categories = $planoModel->getAll('DESPESA'); // Default for payable
        $financialAccounts = (new FinancialAccount())->getAll(true);

        require 'views/payable/index.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $data = [
            'descricao' => trim($_POST['descricao'] ?? $_POST['description'] ?? ''),
            'valor_total' => str_replace(',', '.', (string) ($_POST['valor_total'] ?? '0')),
            'data_competencia' => $_POST['data_competencia'] ?? date('Y-m-d'),
            'data_vencimento' => $_POST['data_vencimento'] ?? date('Y-m-d'),
            'supplier_id' => !empty($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : null,
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0),
            'numero_documento' => trim($_POST['numero_documento'] ?? '') ?: null,
            'forma_pagamento' => $_POST['forma_pagamento'] ?? null,
            'observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
            'recorrente' => isset($_POST['recorrente']),
            'regra_recorrencia' => $_POST['regra_recorrencia'] ?? null,
            'parcelado' => isset($_POST['parcelado']),
            'qtd_parcelas' => max(1, (int) ($_POST['qtd_parcelas'] ?? 1)),
            'valor_pago' => isset($_POST['valor_pago']) ? str_replace(',', '.', (string) $_POST['valor_pago']) : 0,
            'conta_bancaria_id' => !empty($_POST['conta_bancaria_id']) ? (int) $_POST['conta_bancaria_id'] : null
        ];
        if ($data['categoria_id'] < 1) {
            header('Location: ' . BASE_URL . '?route=payable/index&error=categoria');
            exit;
        }
        if ($data['descricao'] === '') {
            header('Location: ' . BASE_URL . '?route=payable/index&error=descricao');
            exit;
        }
        try {
            if ($this->payableModel->create($data)) {
                header('Location: ' . BASE_URL . '?route=payable/index&success=1');
                exit;
            }
        } catch (Exception $e) {
            header('Location: ' . BASE_URL . '?route=payable/index&error=1');
            exit;
        }
    }

    public function pay()
    {
        if (!hasPermission('cash')) {
            $audit = new \App\Models\AuditLog();
            $audit->log('access_denied', 'payable', null, ['action' => 'pay']);
            header('Location: ' . BASE_URL . '?route=payable/index&error=unauthorized');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $amount = str_replace(',', '.', $_POST['amount']);
            $contaId = $_POST['conta_bancaria_id'];
            $forma = $_POST['forma_pagamento'];

            if ($this->payableModel->addPayment($id, $amount, $contaId, $forma)) {
                header('Location: ' . BASE_URL . '?route=payable/index&payment_success=1');
                exit;
            }
        }
    }

    public function cancel()
    {
        if (!hasPermission('cash')) {
            $audit = new \App\Models\AuditLog();
            $audit->log('access_denied', 'payable', null, ['action' => 'cancel']);
            header('Location: ' . BASE_URL . '?route=payable/index&error=unauthorized');
            exit;
        }
        $id = $_GET['id'] ?? null;
        if ($id) {
            global $pdo;
            $stmt = $pdo->prepare("UPDATE contas_pagar SET status = 'CANCELADO', saldo_aberto = 0 WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: ' . BASE_URL . '?route=payable/index&cancelled=1');
            exit;
        }
    }
}
