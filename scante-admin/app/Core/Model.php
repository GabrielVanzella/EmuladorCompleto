<?php
namespace App\Core;

abstract class Model {
    protected Database $db;
    protected string $table = '';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll(string $order = 'id DESC'): array {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY $order");
    }

    public function findById(int $id): ?array {
        return $this->db->queryOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findBy(string $column, mixed $value): ?array {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE $column = ?", [$value]
        );
    }

    public function findAllBy(string $column, mixed $value, string $order = 'id DESC'): array {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE $column = ? ORDER BY $order", [$value]
        );
    }

    public function delete(int $id): int {
        return $this->db->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function count(string $where = '1', array $params = []): int {
        $row = $this->db->queryOne("SELECT COUNT(*) as total FROM {$this->table} WHERE $where", $params);
        return (int)($row['total'] ?? 0);
    }
}
