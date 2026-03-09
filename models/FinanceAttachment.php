<?php

namespace App\Models;

class FinanceAttachment
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getByEntity(string $entityType, int $entityId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM anexos_financeiro WHERE entity_type = ? AND entity_id = ? ORDER BY created_at DESC");
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll();
    }

    public function add(string $entityType, int $entityId, string $filename, string $originalName, string $mime, int $size): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO anexos_financeiro (entity_type, entity_id, filename, original_name, mime, size) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$entityType, $entityId, $filename, $originalName, $mime, $size]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM anexos_financeiro WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM anexos_financeiro WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
