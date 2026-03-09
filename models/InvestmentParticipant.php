<?php

namespace App\Models;

class InvestmentParticipant
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll(bool $includeDeleted = false): array
    {
        $sql = "SELECT id, name, contact, document, created_at, updated_at, deleted_at FROM investment_participants WHERE 1=1";
        if (!$includeDeleted) {
            $sql .= " AND (deleted_at IS NULL)";
        }
        $sql .= " ORDER BY name";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** Para selects/datalist (ativos apenas) */
    public function listForSelect(): array
    {
        $stmt = $this->pdo->query("SELECT id, name FROM investment_participants WHERE deleted_at IS NULL ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, name, contact, document, created_at, updated_at FROM investment_participants WHERE id = :id AND (deleted_at IS NULL)");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO investment_participants (name, contact, document)
            VALUES (:name, :contact, :document)
        ");
        return $stmt->execute([
            'name'    => trim($data['name'] ?? ''),
            'contact' => trim($data['contact'] ?? '') ?: null,
            'document'=> trim($data['document'] ?? '') ?: null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE investment_participants SET name = :name, contact = :contact, document = :document, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'      => $id,
            'name'    => trim($data['name'] ?? ''),
            'contact' => trim($data['contact'] ?? '') ?: null,
            'document'=> trim($data['document'] ?? '') ?: null,
        ]);
    }

    /** Soft delete */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE investment_participants SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
