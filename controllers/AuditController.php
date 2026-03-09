<?php

namespace App\Controllers;

use App\Models\AuditLog;
use App\Models\User;

class AuditController
{
    public function index()
    {
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'user_id' => !empty($_GET['user_id']) ? (int) $_GET['user_id'] : null,
            'action' => $_GET['action'] ?? '',
            'entity' => $_GET['entity'] ?? '',
        ];

        $auditModel = new AuditLog();
        $logs = $auditModel->getAll($filters, 200, 0);

        $userModel = new User();
        $users = $userModel->getAll();

        require 'views/audit/index.php';
    }
}
