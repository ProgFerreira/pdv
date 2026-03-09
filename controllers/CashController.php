<?php

namespace App\Controllers;

use App\Models\CashRegister;
use App\Models\User;

class CashController
{
    public function open()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = str_replace(',', '.', $_POST['amount']);
            $cashModel = new CashRegister();

            if ($cashModel->open($_SESSION['user_id'], $amount)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao abrir caixa']);
            }
            exit;
        }
    }

    public function close()
    {
        if (!hasPermission('cash')) {
            $audit = new \App\Models\AuditLog();
            $audit->log('access_denied', 'cash', null, ['action' => 'close']);
            header('Location: ' . BASE_URL . '?route=dashboard/index&error=unauthorized');
            exit;
        }
        $cashModel = new CashRegister();
        $register = $cashModel->getOpenRegister($_SESSION['user_id']);

        if (!$register) {
            header('Location: ' . BASE_URL . '?route=dashboard/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = str_replace(',', '.', $_POST['closing_balance']);
            $notes = $_POST['notes'] ?? '';

            if ($cashModel->close($register['id'], $amount, $notes)) {
                // Redirect to report
                header('Location: ' . BASE_URL . '?route=cash/report&id=' . $register['id']);
                exit;
            } else {
                $error = "Erro ao fechar caixa.";
            }
        }

        // Prepare data for closing view (Totals)
        $summary = $cashModel->getSummary($register['id']);
        $paymentMethods = $cashModel->getSalesByPaymentMethod($register['id']);
        require 'views/cash/close.php';
    }

    public function report()
    {
        $id = $_GET['id'] ?? 0;
        $cashModel = new CashRegister();
        $register = $cashModel->getById($id);

        if (!$register) {
            header('Location: ' . BASE_URL . '?route=dashboard/index');
            exit;
        }

        $summary = $cashModel->getSummary($id);
        $paymentMethods = $cashModel->getSalesByPaymentMethod($id);
        $movements = $cashModel->getMovements($id);

        require 'views/cash/report.php';
    }

    public function status()
    {
        header('Content-Type: application/json');
        $cashModel = new CashRegister();
        $register = $cashModel->getOpenRegister($_SESSION['user_id']);
        echo json_encode(['isOpen' => (bool) $register, 'register' => $register]);
        exit;
    }

    public function movement()
    {
        // Handle Supply (Suprimento) or Bleed (Sangria)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type']; // 'supply' or 'bleed'
            $amount = str_replace(',', '.', $_POST['amount']);
            $description = $_POST['description'];

            $cashModel = new CashRegister();
            $register = $cashModel->getOpenRegister($_SESSION['user_id']);

            if ($register) {
                $cashModel->addMovement($register['id'], $type, $amount, $description);
                header('Location: ' . BASE_URL . '?route=pos/index&msg=movement_added');
            }
            exit;
        }
    }

    public function history()
    {
        $cashModel = new CashRegister();
        
        // Processar filtros
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $registers = $cashModel->getAll($filters);
        $totals = $cashModel->getTotals($filters);
        
        // Buscar vendas para cada caixa
        $salesByRegister = [];
        foreach ($registers as $reg) {
            $salesByRegister[$reg['id']] = $cashModel->getSales($reg['id']);
        }
        
        // Buscar lista de usuários para o filtro
        $userModel = new User();
        $users = $userModel->getAll();
        
        require 'views/cash/history.php';
    }

    public function index()
    {
        // Alias para history
        $this->history();
    }

    public function view()
    {
        $id = $_GET['id'] ?? 0;
        $cashModel = new CashRegister();
        $register = $cashModel->getById($id);

        if (!$register) {
            header('Location: ' . BASE_URL . '?route=cash/history&error=not_found');
            exit;
        }

        $summary = $cashModel->getSummary($id);
        $paymentMethods = $cashModel->getSalesByPaymentMethod($id);
        $movements = $cashModel->getMovements($id);

        require __DIR__ . '/../views/cash/view.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $cashModel = new CashRegister();
        $register = $cashModel->getById($id);

        if (!$register) {
            header('Location: ' . BASE_URL . '?route=cash/history&error=not_found');
            exit;
        }

        // Não permitir editar caixas abertos (só fechados)
        if ($register['status'] === 'open') {
            header('Location: ' . BASE_URL . '?route=cash/history&error=cannot_edit_open');
            exit;
        }

        require 'views/cash/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=cash/history');
            exit;
        }

        $id = $_POST['id'] ?? 0;
        $cashModel = new CashRegister();
        $register = $cashModel->getById($id);

        if (!$register) {
            header('Location: ' . BASE_URL . '?route=cash/history&error=not_found');
            exit;
        }

        // Não permitir editar caixas abertos
        if ($register['status'] === 'open') {
            header('Location: ' . BASE_URL . '?route=cash/history&error=cannot_edit_open');
            exit;
        }

        $data = [
            'opening_balance' => str_replace(',', '.', $_POST['opening_balance']),
            'closing_balance' => !empty($_POST['closing_balance']) ? str_replace(',', '.', $_POST['closing_balance']) : null,
            'notes' => $_POST['notes'] ?? ''
        ];

        if ($cashModel->update($id, $data)) {
            header('Location: ' . BASE_URL . '?route=cash/history&success=updated');
        } else {
            header('Location: ' . BASE_URL . '?route=cash/edit&id=' . $id . '&error=update_failed');
        }
        exit;
    }

    public function delete()
    {
        if (!hasPermission('cash')) {
            $audit = new \App\Models\AuditLog();
            $audit->log('access_denied', 'cash', null, ['action' => 'delete']);
            header('Location: ' . BASE_URL . '?route=cash/history&error=unauthorized');
            exit;
        }
        $id = $_GET['id'] ?? 0;
        $cashModel = new CashRegister();
        $register = $cashModel->getById($id);

        if (!$register) {
            header('Location: ' . BASE_URL . '?route=cash/history&error=not_found');
            exit;
        }

        // Não permitir excluir caixas abertos
        if ($register['status'] === 'open') {
            header('Location: ' . BASE_URL . '?route=cash/history&error=cannot_delete_open');
            exit;
        }

        if ($cashModel->delete($id)) {
            header('Location: ' . BASE_URL . '?route=cash/history&success=deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=cash/history&error=delete_failed');
        }
        exit;
    }
}
