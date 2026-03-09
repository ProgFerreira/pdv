<?php

namespace App\Models;

class Permission
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Retorna as chaves de permissão do papel (role).
     *
     * @param string $role admin|cashier
     * @return string[]
     */
    public function getKeysByRole(string $role): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.`key` FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role = :role
        ");
        $stmt->execute(['role' => $role]);
        return array_column($stmt->fetchAll(), 'key');
    }

    /**
     * Lista todas as permissões (id, key, name, description).
     *
     * @return array<int, array{id: int, key: string, name: string, description: string|null}>
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, `key`, name, description FROM permissions ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retorna os IDs de permissão atribuídos ao role.
     *
     * @param string $role
     * @return int[]
     */
    public function getPermissionIdsByRole(string $role): array
    {
        $stmt = $this->pdo->prepare("SELECT permission_id FROM role_permissions WHERE role = :role");
        $stmt->execute(['role' => $role]);
        return array_map('intval', array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'permission_id'));
    }

    /**
     * Atualiza as permissões do role (substitui as atuais).
     *
     * @param string $role
     * @param int[] $permissionIds
     * @return bool
     */
    public function setRolePermissions(string $role, array $permissionIds): bool
    {
        try {
            $this->pdo->beginTransaction();
            $del = $this->pdo->prepare("DELETE FROM role_permissions WHERE role = :role");
            $del->execute(['role' => $role]);
            $ins = $this->pdo->prepare("INSERT INTO role_permissions (role, permission_id) VALUES (:role, :pid)");
            foreach ($permissionIds as $pid) {
                if ($pid <= 0) continue;
                $ins->execute(['role' => $role, 'pid' => $pid]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Grupos (roles) disponíveis. role => nome exibido.
     *
     * @return array<string, string>
     */
    public static function getRoles(): array
    {
        return [
            'admin' => 'Administrador',
            'cashier' => 'Caixa',
        ];
    }
}
