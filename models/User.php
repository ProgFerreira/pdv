<?php

namespace App\Models;

class User
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function login($username, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username AND active = 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, username, password_hash, role, active, sector_id) 
            VALUES (:name, :username, :password_hash, :role, :active, :sector_id)
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'username' => $data['username'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'cashier',
            'active' => $data['active'] ?? 1,
            'sector_id' => $data['sector_id'] ?: null
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE users SET name = :name, username = :username, role = :role, active = :active, sector_id = :sector_id";
        $params = [
            'id' => $id,
            'name' => $data['name'],
            'username' => $data['username'],
            'role' => $data['role'],
            'active' => $data['active'],
            'sector_id' => $data['sector_id'] ?: null
        ];

        if (!empty($data['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        // Don't allow deleting the last admin or yourself
        if ($id == $_SESSION['user_id'])
            return false;

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
