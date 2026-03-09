<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Models\AuditLog;

class AuthController extends BaseController
{
    public function index()
    {
        $this->login();
    }

    public function login()
    {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (login_rate_limit_exceeded()) {
                $error = 'Muitas tentativas de login. Tente novamente em alguns minutos.';
                $this->render('auth/login', ['error' => $error]);
                return;
            }
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new User();
            $user = $userModel->login($username, $password);

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                $permModel = new Permission();
                $_SESSION['permissions'] = $permModel->getKeysByRole($user['role']);

                if ($user['role'] === 'admin') {
                    $_SESSION['sector_id'] = 'all';
                } else {
                    $_SESSION['sector_id'] = $user['sector_id'] ?? 1;
                }

                $audit = new AuditLog();
                $audit->log('login', 'user', (int) $user['id'], ['username' => $user['username']]);

                $this->redirect('dashboard/index');
            }
            login_rate_limit_record_failure();
            $audit = new AuditLog();
            $audit->log('login_failed', 'user', null, ['username' => $username]);
            $error = 'Usuário ou senha inválidos.';
        }
        $this->render('auth/login', ['error' => $error]);
    }

    public function switchSector()
    {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }
        $id = $_GET['id'] ?? 1;
        if ($_SESSION['user_role'] === 'admin') {
            $_SESSION['sector_id'] = ($id === 'all') ? 'all' : (int) $id;
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        exit;
    }

    public function logout()
    {
        $userId = $_SESSION['user_id'] ?? null;
        $audit = new AuditLog();
        $audit->log('logout', 'user', $userId ? (int) $userId : null);
        session_destroy();
        $this->redirect('auth/login');
    }
}
