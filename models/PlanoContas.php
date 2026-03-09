<?php

namespace App\Models;

class PlanoContas
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll($tipo = null, $ativoOnly = false)
    {
        $sql = "SELECT * FROM plano_contas WHERE 1=1";
        $params = [];

        if ($tipo) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $tipo;
        }

        if ($ativoOnly) {
            $sql .= " AND ativo = 1";
        }

        $sql .= " ORDER BY tipo, nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM plano_contas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO plano_contas (tipo, nome, pai_id, ativo) VALUES (:tipo, :nome, :pai_id, :ativo)");
        return $stmt->execute([
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'pai_id' => $data['pai_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("UPDATE plano_contas SET tipo = :tipo, nome = :nome, pai_id = :pai_id, ativo = :ativo WHERE id = :id");
        return $stmt->execute([
            'id' => $id,
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'pai_id' => $data['pai_id'] ?? null,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
}
