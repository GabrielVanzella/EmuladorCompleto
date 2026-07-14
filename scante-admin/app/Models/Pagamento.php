<?php
namespace App\Models;

use App\Core\Model;

class Pagamento extends Model {
    protected string $table = 'pagamentos';

    public function todos(array $filtros = []): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['empresa_id'])) {
            $where[]  = 'l.empresa_id = ?';
            $params[] = (int)$filtros['empresa_id'];
        }
        if (!empty($filtros['status'])) {
            $where[]  = 'p.status = ?';
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['tipo'])) {
            $where[]  = 'p.tipo = ?';
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['de'])) {
            $where[]  = 'DATE(p.criado_em) >= ?';
            $params[] = $filtros['de'];
        }
        if (!empty($filtros['ate'])) {
            $where[]  = 'DATE(p.criado_em) <= ?';
            $params[] = $filtros['ate'];
        }

        $sql = "
            SELECT p.*,
                   l.chave      AS licenca_chave,
                   l.tipo       AS licenca_tipo,
                   e.id         AS empresa_id,
                   e.nome       AS empresa_nome
            FROM pagamentos p
            INNER JOIN licencas l ON l.id = p.licenca_id
            INNER JOIN empresas e ON e.id = l.empresa_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.criado_em DESC
        ";

        return $this->db->query($sql, $params);
    }

    public function estatisticas(array $filtros = []): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['empresa_id'])) {
            $where[]  = 'l.empresa_id = ?';
            $params[] = (int)$filtros['empresa_id'];
        }

        $sql = "
            SELECT
                COUNT(*)                                                                      AS total,
                COALESCE(SUM(CASE WHEN p.status='approved' THEN p.valor ELSE 0 END), 0)     AS total_aprovado,
                COALESCE(SUM(CASE WHEN p.status='approved'
                    AND YEAR(p.criado_em)=YEAR(NOW())
                    AND MONTH(p.criado_em)=MONTH(NOW())
                    THEN p.valor ELSE 0 END), 0)                                             AS total_mes,
                COALESCE(AVG(CASE WHEN p.status='approved' THEN p.valor END), 0)            AS ticket_medio,
                SUM(p.status='approved')                                                     AS qtd_aprovados,
                SUM(p.status='pending')                                                      AS qtd_pendentes,
                SUM(p.status='rejected' OR p.status='cancelled')                            AS qtd_rejeitados
            FROM pagamentos p
            INNER JOIN licencas l ON l.id = p.licenca_id
            WHERE " . implode(' AND ', $where) . "
        ";

        return $this->db->queryOne($sql, $params) ?? [];
    }

    public function porEmpresa(): array {
        return $this->db->query("
            SELECT
                e.id,
                e.nome,
                COUNT(p.id)                                                            AS total_pagamentos,
                COALESCE(SUM(CASE WHEN p.status='approved' THEN p.valor ELSE 0 END), 0) AS total_pago
            FROM empresas e
            LEFT JOIN licencas l ON l.empresa_id = e.id
            LEFT JOIN pagamentos p ON p.licenca_id = l.id
            GROUP BY e.id, e.nome
            HAVING total_pagamentos > 0
            ORDER BY total_pago DESC
        ");
    }

    public function registrar(int $licencaId, float $valor, string $tipo, string $status, string $descricao = ''): int {
        $paymentId = 'MANUAL-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $this->db->execute(
            "INSERT INTO pagamentos (licenca_id, payment_id, valor, tipo, status, criado_em) VALUES (?,?,?,?,?,NOW())",
            [$licencaId, $paymentId, $valor, $tipo, $status]
        );
        return (int)$this->db->lastInsertId();
    }

    public function excluir(int $id): void {
        $this->db->execute("DELETE FROM pagamentos WHERE id = ?", [$id]);
    }
}
