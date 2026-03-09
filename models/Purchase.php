<?php

namespace App\Models;

class Purchase
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Inserir na tabela de compras
            $stmt = $this->pdo->prepare("
                INSERT INTO purchases (supplier_id, total_amount, sector_id, notes) 
                VALUES (:supplier_id, :total_amount, :sector_id, :notes)
            ");
            $stmt->execute([
                'supplier_id' => $data['supplier_id'],
                'total_amount' => $data['total_amount'],
                'sector_id' => $data['sector_id'],
                'notes' => $data['notes'] ?? ''
            ]);
            $purchaseId = $this->pdo->lastInsertId();

            // 2. Se solicitado, criar entrada no Contas a Pagar
            if (!empty($data['create_payable'])) {
                $payable = new Payable();
                $payable->create([
                    'description' => "Compra #" . $purchaseId . " - " . ($data['supplier_name'] ?? 'Fornecedor'),
                    'total_amount' => $data['total_amount'],
                    'due_date' => $data['due_date'] ?? date('Y-m-d'),
                    'supplier_id' => $data['supplier_id'],
                    'purchase_id' => $purchaseId,
                    'sector_id' => $data['sector_id'],
                    'notes' => "Gerado automaticamente via Gestão de Compras"
                ]);
            }

            $this->pdo->commit();
            return $purchaseId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getAll()
    {
        $sectorId = $_SESSION['sector_id'] ?? 1;
        $sql = "SELECT p.*, s.name as supplier_name, sec.name as sector_name 
                FROM purchases p 
                JOIN suppliers s ON p.supplier_id = s.id 
                JOIN sectors sec ON p.sector_id = sec.id 
                WHERE 1=1";

        $params = [];
        if ($sectorId !== 'all') {
            $sql .= " AND p.sector_id = :sectorId";
            $params['sectorId'] = $sectorId;
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
