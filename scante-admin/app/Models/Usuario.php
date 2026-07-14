<?php
namespace App\Models;

use App\Core\Model;

class Usuario extends Model {
    protected string $table = 'usuarios';

    public function findByEmail(string $email): ?array {
        return $this->db->queryOne(
            "SELECT * FROM usuarios WHERE email = ? AND ativo = 1", [$email]
        );
    }

    public function create(array $data): int {
        $this->db->execute(
            "INSERT INTO usuarios (nome, email, senha, tipo, empresa_id) VALUES (?,?,?,?,?)",
            [
                $data['nome'],
                $data['email'],
                password_hash($data['senha'], PASSWORD_BCRYPT),
                $data['tipo'],
                $data['empresa_id'] ?? null,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $fields = [];
        $params = [];
        foreach (['nome', 'email', 'ativo'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if (!empty($data['senha'])) {
            $fields[] = "senha = ?";
            $params[] = password_hash($data['senha'], PASSWORD_BCRYPT);
        }
        $params[] = $id;
        $this->db->execute("UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?", $params);
    }

    public function verificarSenha(string $senha, string $hash): bool {
        return password_verify($senha, $hash);
    }

    public function findByEmpresa(int $empresaId): array {
        return $this->db->query(
            "SELECT * FROM usuarios WHERE empresa_id = ? ORDER BY nome", [$empresaId]
        );
    }
}
