<?php
namespace App\Models;

use App\Core\Model;

class Chamado extends Model {
    protected string $table = 'chamados';

    /**
     * Abre um chamado e grava a descrição inicial como a primeira mensagem
     * da conversa (autor = empresa). Retorna o id do chamado criado.
     */
    public function abrir(int $empresaId, ?int $licencaId, ?int $usuarioId, string $autorNome, string $assunto, string $mensagemInicial): int {
        $this->db->execute(
            "INSERT INTO chamados (empresa_id, licenca_id, usuario_id, assunto, status, criado_em)
             VALUES (?, ?, ?, ?, 'aberto', NOW())",
            [$empresaId, $licencaId, $usuarioId, $assunto]
        );
        $chamadoId = (int)$this->db->lastInsertId();

        $this->adicionarMensagem($chamadoId, 'empresa', $usuarioId, $autorNome, $mensagemInicial);
        return $chamadoId;
    }

    /**
     * Adiciona uma mensagem à conversa e "toca" o chamado (atualizado_em).
     * Quando a equipe (admin) responde um chamado ainda 'aberto', ele passa
     * automaticamente para 'em_andamento'.
     */
    public function adicionarMensagem(int $chamadoId, string $autorTipo, ?int $autorId, string $autorNome, string $mensagem): void {
        $this->db->execute(
            "INSERT INTO chamado_mensagens (chamado_id, autor_tipo, autor_id, autor_nome, mensagem, criado_em)
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$chamadoId, $autorTipo, $autorId, $autorNome, $mensagem]
        );

        if ($autorTipo === 'admin') {
            $this->db->execute(
                "UPDATE chamados SET status = IF(status='aberto','em_andamento',status), atualizado_em = NOW() WHERE id = ?",
                [$chamadoId]
            );
        } else {
            $this->db->execute("UPDATE chamados SET atualizado_em = NOW() WHERE id = ?", [$chamadoId]);
        }
    }

    public function mensagens(int $chamadoId): array {
        return $this->db->query(
            "SELECT * FROM chamado_mensagens WHERE chamado_id = ? ORDER BY criado_em ASC, id ASC",
            [$chamadoId]
        );
    }

    public function mudarStatus(int $id, string $status): void {
        if (!in_array($status, ['aberto', 'em_andamento', 'resolvido'], true)) return;
        $this->db->execute(
            "UPDATE chamados SET status = ?, atualizado_em = NOW() WHERE id = ?",
            [$status, $id]
        );
    }

    /** Chamados de uma empresa (com a chave da licença, se houver). */
    public function porEmpresa(int $empresaId): array {
        return $this->db->query(
            "SELECT c.*, l.chave AS licenca_chave
             FROM chamados c
             LEFT JOIN licencas l ON l.id = c.licenca_id
             WHERE c.empresa_id = ?
             ORDER BY c.atualizado_em DESC",
            [$empresaId]
        );
    }

    /** Todos os chamados (admin), com nome da empresa e chave da licença. */
    public function todos(): array {
        return $this->db->query(
            "SELECT c.*, e.nome AS empresa_nome, l.chave AS licenca_chave, l.tipo AS licenca_tipo
             FROM chamados c
             LEFT JOIN empresas e ON e.id = c.empresa_id
             LEFT JOIN licencas l ON l.id = c.licenca_id
             ORDER BY c.atualizado_em DESC"
        );
    }

    /** Um chamado com empresa e licença já resolvidas. */
    public function verComContexto(int $id): ?array {
        return $this->db->queryOne(
            "SELECT c.*, e.nome AS empresa_nome, l.chave AS licenca_chave, l.tipo AS licenca_tipo
             FROM chamados c
             LEFT JOIN empresas e ON e.id = c.empresa_id
             LEFT JOIN licencas l ON l.id = c.licenca_id
             WHERE c.id = ?",
            [$id]
        );
    }

    /** Quantos chamados ainda não estão resolvidos (badge do menu admin). */
    public function contarAbertos(): int {
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM chamados WHERE status <> 'resolvido'");
        return (int)($row['total'] ?? 0);
    }
}
