<?php

namespace App\Models;

class FinancialAccount
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAll($ativoOnly = false)
    {
        $sql = "SELECT * FROM contas_bancarias WHERE 1=1";
        if ($ativoOnly) {
            $sql .= " AND ativo = 1";
        }
        $sql .= " ORDER BY nome ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contas_bancarias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO contas_bancarias (nome, tipo, saldo_inicial, ativo) VALUES (:nome, :tipo, :saldo_inicial, :ativo)");
        return $stmt->execute([
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'saldo_inicial' => $data['saldo_inicial'] ?? 0,
            'ativo' => $data['ativo'] ?? 1
        ]);
    }

    public function updateBalance($id, $amount, $type = 'ENTRADA')
    {
        $sql = ($type === 'ENTRADA')
            ? "UPDATE contas_bancarias SET saldo_inicial = saldo_inicial + :amount WHERE id = :id"
            : "UPDATE contas_bancarias SET saldo_inicial = saldo_inicial - :amount WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['amount' => $amount, 'id' => $id]);
    }
}
