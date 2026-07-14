<?php
namespace App\Models;

use App\Core\Model;

class RelayLicenca extends Model {
    protected string $table = 'relay_licencas';

    public function criar(array $d): int {
        $this->db->execute(
            "INSERT INTO relay_licencas
                (cliente, serial, max_sessions, max_devices, expira_em, release_suportado, server_host, licenca_texto, criado_por, criada_em)
             VALUES (?,?,?,?,?,?,?,?,?,NOW())",
            [
                $d['cliente'], $d['serial'], $d['max_sessions'], $d['max_devices'],
                $d['expira_em'], $d['release_suportado'], $d['server_host'] ?: null,
                $d['licenca_texto'], $d['criado_por'],
            ]
        );
        return (int)$this->db->lastInsertId();
    }
}
