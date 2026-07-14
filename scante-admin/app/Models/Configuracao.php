<?php
namespace App\Models;

use App\Core\Database;

class Configuracao {

    private static array $cache = [];

    public function get(string $chave, string $default = ''): string {
        if (array_key_exists($chave, self::$cache)) {
            return self::$cache[$chave];
        }
        $row = Database::getInstance()->queryOne(
            'SELECT valor FROM configuracoes WHERE chave = ?', [$chave]
        );
        $val = ($row !== null && $row['valor'] !== null) ? $row['valor'] : $default;
        self::$cache[$chave] = $val;
        return $val;
    }

    public function set(string $chave, string $valor): void {
        Database::getInstance()->execute(
            'INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = ?, atualizado_em = NOW()',
            [$chave, $valor, $valor]
        );
        self::$cache[$chave] = $valor;
    }

    public function setMultiple(array $data): void {
        foreach ($data as $k => $v) {
            $this->set((string)$k, (string)$v);
        }
    }

    public function getMultiple(array $chaves): array {
        $result = [];
        foreach ($chaves as $c) {
            $result[$c] = $this->get($c);
        }
        return $result;
    }

    /** Retorna o gateway ativo: 'pagarme' | 'mercadopago' | 'dev' */
    public static function gatewayAtivo(): string {
        return (new self())->get('gateway_ativo', 'dev');
    }
}
