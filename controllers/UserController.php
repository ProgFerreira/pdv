<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Sector;

class UserController
{
    public function index()
    {
        $userModel = new User();
        $users = $userModel->getAll();
        require 'views/user/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = new User();
            if ($userModel->create($_POST)) {
                header('Location: ' . BASE_URL . '?route=user/index&success=created');
                exit;
            } else {
                $error = "Erro ao criar usuário.";
            }
        }
        $sectorModel = new Sector();
        $sectors = $sectorModel->getAll();
        require 'views/user/form.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        $userModel = new User();
        $user = $userModel->getById($id);

        if (!$user) {
            header('Location: ' . BASE_URL . '?route=user/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($userModel->update($id, $_POST)) {
                header('Location: ' . BASE_URL . '?route=user/index&success=updated');
                exit;
            } else {
                $error = "Erro ao atualizar usuário.";
            }
        }
        $sectorModel = new Sector();
        $sectors = $sectorModel->getAll();
        require 'views/user/form.php';
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $userModel = new User();
            if ($userModel->delete($id)) {
                header('Location: ' . BASE_URL . '?route=user/index&success=deleted');
            } else {
                header('Location: ' . BASE_URL . '?route=user/index&error=cannot_delete_self');
            }
        }
        exit;
    }
}
