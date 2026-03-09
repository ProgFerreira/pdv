<?php

namespace App\Controllers;

use App\Models\PlanoContas;

class PlanoContasController
{
    private $model;

    public function __construct()
    {
        $this->model = new PlanoContas();
    }

    public function index()
    {
        $categories = $this->model->getAll();
        require 'views/config/plano_contas.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tipo' => $_POST['tipo'],
                'nome' => $_POST['nome'],
                'pai_id' => !empty($_POST['pai_id']) ? $_POST['pai_id'] : null,
                'ativo' => isset($_POST['ativo']) ? 1 : 0
            ];

            if ($this->model->create($data)) {
                header('Location: ' . BASE_URL . '?route=planoContas/index&success=1');
            } else {
                header('Location: ' . BASE_URL . '?route=planoContas/index&error=1');
            }
            exit;
        }
    }
}
