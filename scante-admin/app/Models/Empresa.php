<?php
namespace App\Models;

use App\Core\Model;

class Empresa extends Model {
    protected string $table = 'empresas';

    public function create(array $data): int {
        $this->db->execute(
            "INSERT INTO empresas (nome, cnpj, email, telefone, contato, ativo) VALUES (?,?,?,?,?,1)",
            [$data['nome'], $data['cnpj'] ?? null, $data['email'], $data['telefone'] ?? null, $data['contato'] ?? null]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $this->db->execute(
            "UPDATE empresas SET nome=?, cnpj=?, email=?, telefone=?, contato=?, ativo=? WHERE id=?",
            [$data['nome'], $data['cnpj'] ?? null, $data['email'], $data['telefone'] ?? null, $data['contato'] ?? null, $data['ativo'] ?? 1, $id]
        );
    }

    public function comEstatisticas(): array {
        return $this->db->query("
            SELECT e.*,
                COUNT(l.id)                                        AS total_licencas,
                SUM(l.status = 'ativa')                           AS licencas_ativas,
                SUM(l.status = 'trial')                           AS licencas_trial,
                SUM(l.status = 'expirada')                        AS licencas_expiradas
            FROM empresas e
            LEFT JOIN licencas l ON l.empresa_id = e.id
            GROUP BY e.id
            ORDER BY e.nome
        ");
    }

    /** Empresas ativas (para selects de atribuição). */
    public function ativas(): array {
        return $this->db->query("SELECT id, nome FROM empresas WHERE ativo = 1 ORDER BY nome");
    }

    /** Salva o tema (cores + cabeçalho) do app e incrementa a versão do config. */
    public function salvarTema(int $id, string $temaJson): void {
        $this->db->execute(
            "UPDATE empresas SET config_tema = ?, config_versao = config_versao + 1 WHERE id = ?",
            [$temaJson, $id]
        );
    }

    /** Salva as barras de ferramentas (teclas) do app e incrementa a versão do config. */
    public function salvarTeclas(int $id, string $teclasJson): void {
        $this->db->execute(
            "UPDATE empresas SET config_teclas = ?, config_versao = config_versao + 1 WHERE id = ?",
            [$teclasJson, $id]
        );
    }

    /** Incrementa a versão do config (usado ao trocar a logo, por ex.). */
    public function bumpConfigVersao(int $id): void {
        $this->db->execute("UPDATE empresas SET config_versao = config_versao + 1 WHERE id = ?", [$id]);
    }

    /**
     * Busca uma empresa pelo CNPJ, ignorando formatação (pontos/traço/barra).
     * Usado no checkout público pra não deixar a mesma empresa se cadastrar
     * duas vezes com o CNPJ formatado de jeitos diferentes.
     */
    public function findByCnpj(string $cnpj): ?array {
        $digits = preg_replace('/\D/', '', $cnpj);
        if (!$digits) return null;

        foreach ($this->db->query("SELECT * FROM empresas WHERE cnpj IS NOT NULL AND cnpj != ''") as $e) {
            if (preg_replace('/\D/', '', $e['cnpj']) === $digits) {
                return $e;
            }
        }
        return null;
    }
}
