<?php

namespace App\Controllers;

use App\Models\Brand;

class BrandController
{
    public function index()
    {
        $brandModel = new Brand();
        $brands = $brandModel->getAll();
        require 'views/brands/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $brandModel = new Brand();
            $data = ['name' => $_POST['name']];

            if ($brandModel->create($data)) {
                header('Location: ' . BASE_URL . '?route=brand/index');
                exit;
            } else {
                $error = "Erro ao cadastrar marca.";
            }
        }

        $isEdit = false;
        require 'views/brands/form.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $brandModel = new Brand();
        $brand = $brandModel->getById($id);

        if (!$brand) {
            header('Location: ' . BASE_URL . '?route=brand/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = ['name' => $_POST['name']];

            if ($brandModel->update($id, $data)) {
                header('Location: ' . BASE_URL . '?route=brand/index');
                exit;
            } else {
                $error = "Erro ao atualizar marca.";
            }
        }

        $isEdit = true;
        require 'views/brands/form.php';
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $brandModel = new Brand();

        if ($brandModel->delete($id)) {
            header('Location: ' . BASE_URL . '?route=brand/index');
        } else {
            header('Location: ' . BASE_URL . '?route=brand/index&error=constraint');
        }
        exit;
    }
}
