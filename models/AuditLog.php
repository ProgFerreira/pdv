<?php

namespace App\Models;

class AuditLog
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Registra uma ação no histórico de auditoria.
     *
     * @param string $action Ex: login, logout, sale_create, sale_cancel, pos_discount, access_denied
     * @param string $entity Ex: user, sale, product
     * @param int|null $entityId
     * @param array|null $metadata Dados extras (serão salvos como JSON)
     */
    public function log(string $action, string $entity, ?int $entityId = null, ?array $metadata = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $metaJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO audit_logs (user_id, action, entity, entity_id, metadata_json, ip, user_agent)
            VALUES (:user_id, :action, :entity, :entity_id, :metadata_json, :ip, :user_agent)
        ");
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'metadata_json' => $metaJson,
            'ip' => $ip ? substr($ip, 0, 45) : null,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
        ]);
    }

    /**
     * Lista logs com filtros.
     *
     * @param array{f start_date?: string, end_date?: string, user_id?: int, action?: string, entity?: string } $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "
            SELECT al.*, u.name as user_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(al.created_at) >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(al.created_at) <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = :action";
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['entity'])) {
            $sql .= " AND al.entity = :entity";
            $params['entity'] = $filters['entity'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $type = in_array($k, ['user_id', 'limit', 'offset'], true) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(':' . $k, $v, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
