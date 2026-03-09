<?php

namespace App\Controllers;

use App\Models\Category;

class CategoryController
{
    public function index()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        require 'views/categories/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryModel = new Category();
            $data = ['name' => $_POST['name']];

            if ($categoryModel->create($data)) {
                header('Location: ' . BASE_URL . '?route=category/index');
                exit;
            } else {
                $error = "Erro ao cadastrar categoria.";
            }
        }

        $isEdit = false;
        require 'views/categories/form.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? 0;
        $categoryModel = new Category();
        $category = $categoryModel->getById($id);

        if (!$category) {
            header('Location: ' . BASE_URL . '?route=category/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = ['name' => $_POST['name']];

            if ($categoryModel->update($id, $data)) {
                header('Location: ' . BASE_URL . '?route=category/index');
                exit;
            } else {
                $error = "Erro ao atualizar categoria.";
            }
        }

        $isEdit = true;
        require 'views/categories/form.php';
    }

    public function delete()
    {
        $id = $_GET['id'] ?? 0;
        $categoryModel = new Category();

        if ($categoryModel->delete($id)) {
            header('Location: ' . BASE_URL . '?route=category/index');
        } else {
            header('Location: ' . BASE_URL . '?route=category/index&error=constraint');
        }
        exit;
    }
}
