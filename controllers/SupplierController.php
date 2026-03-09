<?php

namespace App\Controllers;

use App\Models\Supplier;

class SupplierController
{
    public function index()
    {
        $model = new Supplier();
        $suppliers = $model->getAll();
        require 'views/supplier/index.php';
    }

    public function create()
    {
        require 'views/supplier/form.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Supplier();
            if ($model->create($_POST)) {
                header('Location: ' . BASE_URL . '?route=supplier/index&success=created');
            } else {
                header('Location: ' . BASE_URL . '?route=supplier/create&error=failed');
            }
            exit;
        }
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $model = new Supplier();
        $supplier = $model->getById($id);
        if (!$supplier) {
            header('Location: ' . BASE_URL . '?route=supplier/index');
            exit;
        }
        require 'views/supplier/form.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $model = new Supplier();
            if ($model->update($id, $_POST)) {
                header('Location: ' . BASE_URL . '?route=supplier/index&success=updated');
            } else {
                header('Location: ' . BASE_URL . '?route=supplier/edit&id=' . $id . '&error=failed');
            }
            exit;
        }
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $model = new Supplier();
        if ($model->delete($id)) {
            header('Location: ' . BASE_URL . '?route=supplier/index&success=deleted');
        } else {
            header('Location: ' . BASE_URL . '?route=supplier/index&error=failed');
        }
        exit;
    }
}
