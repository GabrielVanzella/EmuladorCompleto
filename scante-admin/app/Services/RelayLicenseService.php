<?php
namespace App\Services;

/**
 * Assina licenças offline do ScanTE Relay (Ed25519, via sodium).
 * Formato do texto de licença: "SRL1.<payload_base64>.<assinatura_base64>"
 * A chave privada só existe aqui (config RELAY_LICENSE_PRIVATE_KEY); o relay
 * (Go) só tem a chave pública, então não consegue gerar/forjar licenças.
 */
class RelayLicenseService {

    private const PREFIX = 'SRL1';

    /** Gera o texto assinado da licença a partir dos dados do formulário. */
    public static function gerar(array $dados): string {
        $payload = [
            'customer'     => $dados['customer'],
            'serial'       => $dados['serial'],
            'max_sessions' => (int)$dados['max_sessions'],
            'max_devices'  => (int)$dados['max_devices'],
            'expiry'       => $dados['expiry'], // YYYY-MM-DD
            'release'      => $dados['release'] ?: '1.0',
            'server_host'  => $dados['server_host'] ?: '',
            'issued_at'    => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        $json      = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $secretKey = base64_decode(RELAY_LICENSE_PRIVATE_KEY);
        $signature = sodium_crypto_sign_detached($json, $secretKey);

        return self::PREFIX . '.' . base64_encode($json) . '.' . base64_encode($signature);
    }

    public static function gerarSerial(): string {
        return 'SR-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
