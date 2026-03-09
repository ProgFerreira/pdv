<?php

namespace App\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\AuditLog;

class PermissionController
{
    public function index()
    {
        $permModel = new Permission();
        $permissions = $permModel->getAll();
        $roles = Permission::getRoles();
        $rolePerms = [];
        foreach (array_keys($roles) as $role) {
            $rolePerms[$role] = $permModel->getPermissionIdsByRole($role);
        }
        $modules = require __DIR__ . '/../config/perm_modules.php';

        $userModel = new User();
        $users = $userModel->getAll();
        $usersByRole = [];
        foreach (array_keys($roles) as $role) {
            $usersByRole[$role] = array_filter($users, fn ($u) => ($u['role'] ?? '') === $role);
        }

        require 'views/permission/index.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?route=permission/index');
            exit;
        }

        $perms = $_POST['perms'] ?? [];
        if (!is_array($perms)) {
            $perms = [];
        }

        $permModel = new Permission();
        $roles = Permission::getRoles();
        $ok = true;
        foreach (array_keys($roles) as $role) {
            $ids = isset($perms[$role]) && is_array($perms[$role]) ? array_map('intval', $perms[$role]) : [];
            if (!$permModel->setRolePermissions($role, $ids)) {
                $ok = false;
            }
        }

        $audit = new AuditLog();
        $audit->log('permission_update', 'role', null, ['roles' => array_keys($roles)]);

        if ($ok) {
            header('Location: ' . BASE_URL . '?route=permission/index&success=updated');
        } else {
            header('Location: ' . BASE_URL . '?route=permission/index&error=failed');
        }
        exit;
    }
}
