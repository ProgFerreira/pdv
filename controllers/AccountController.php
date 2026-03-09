<?php

namespace App\Controllers;

use App\Models\FinancialAccount;

class AccountController
{
    private $model;

    public function __construct()
    {
        $this->model = new FinancialAccount();
    }

    public function index()
    {
        $accounts = $this->model->getAll();
        require 'views/config/accounts.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'saldo_inicial' => $_POST['saldo_inicial'] ?? 0,
                'ativo' => isset($_POST['ativo']) ? 1 : 0
            ];

            if ($this->model->create($data)) {
                header('Location: ' . BASE_URL . '?route=account/index&success=1');
            } else {
                header('Location: ' . BASE_URL . '?route=account/index&error=1');
            }
            exit;
        }
    }
}
