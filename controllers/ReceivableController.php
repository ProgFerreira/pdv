<?php

namespace App\Controllers;

use App\Models\Receivable;
use App\Models\FinancialAccount;
use App\Models\Customer;
use App\Models\PlanoContas;

class ReceivableController
{
    private $receivableModel;

    public function __construct()
    {
        $this->receivableModel = new Receivable();
    }

    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'origem' => $_GET['origem'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];

        $list = $this->receivableModel->getAll($filters);

        $accModel = new FinancialAccount();
        $accounts = $accModel->getAll(true);

        $customerModel = new Customer();
        $customers = $customerModel->getAll();

        $planoModel = new PlanoContas();
        $categoriesReceita = $planoModel->getAll('RECEITA');

        require 'views/receivable/index.php';
    }

    public function pay()
    {
        if (!hasPermission('receivable')) {
            $audit = new \App\Models\AuditLog();
            $audit->log('access_denied', 'receivable', null, ['action' => 'pay']);
            header('Location: ' . BASE_URL . '?route=receivable/index&error=unauthorized');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $amount = str_replace(',', '.', $_POST['amount']);
            $contaId = $_POST['conta_bancaria_id'];
            $forma = $_POST['forma_recebimento'];

            if ($this->receivableModel->addPayment($id, $amount, $contaId, $forma)) {
                header('Location: ' . BASE_URL . '?route=receivable/index&success=paid');
            } else {
                header('Location: ' . BASE_URL . '?route=receivable/index&error=failed');
            }
            exit;
        }
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $descricao = trim($_POST['descricao'] ?? '');
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        if ($descricao === '' || $categoriaId < 1) {
            header('Location: ' . BASE_URL . '?route=receivable/index&error=validation');
            exit;
        }
        $data = [
            'cliente_id' => !empty($_POST['cliente_id']) ? (int) $_POST['cliente_id'] : null,
            'origem' => in_array($_POST['origem'] ?? '', ['PDV', 'DELIVERY', 'MANUAL'], true) ? $_POST['origem'] : 'MANUAL',
            'descricao' => $descricao,
            'valor_total' => str_replace(',', '.', (string) ($_POST['valor_total'] ?? '0')),
            'data_competencia' => $_POST['data_competencia'] ?? date('Y-m-d'),
            'data_vencimento' => $_POST['data_vencimento'] ?? date('Y-m-d'),
            'categoria_id' => $categoriaId,
            'numero_documento' => trim($_POST['numero_documento'] ?? '') ?: null,
            'forma_recebimento' => $_POST['forma_recebimento'] ?? null,
            'observacoes' => trim($_POST['observacoes'] ?? '') ?: null,
            'valor_recebido' => isset($_POST['valor_recebido']) ? str_replace(',', '.', (string) $_POST['valor_recebido']) : 0,
            'conta_bancaria_id' => !empty($_POST['conta_bancaria_id']) ? (int) $_POST['conta_bancaria_id'] : null
        ];
        try {
            if ($this->receivableModel->create($data)) {
                header('Location: ' . BASE_URL . '?route=receivable/index&success=created');
            } else {
                header('Location: ' . BASE_URL . '?route=receivable/index&error=create_failed');
            }
        } catch (Exception $e) {
            header('Location: ' . BASE_URL . '?route=receivable/index&error=create_failed');
        }
        exit;
    }
}
