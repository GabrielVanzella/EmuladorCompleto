<?php
namespace App\Services;

class PagarmeService {

    private const BASE_URL = 'https://api.pagar.me/core/v5';

    private string $secretKey;

    public function __construct(string $secretKey) {
        $this->secretKey = $secretKey;
    }

    /**
     * Cria um pedido com pagamento via Pix.
     * Retorna array com qr_code, qr_code_url, expires_at, order_id, charge_id.
     */
    public function criarOrdemPix(
        int    $licencaId,
        float  $valor,
        string $descricao,
        string $email,
        string $nome = 'Cliente ScanTE',
        int    $expiresIn = 3600
    ): array {
        $body = [
            'code'     => 'LIC-' . $licencaId,
            'currency' => 'BRL',
            'items'    => [[
                'amount'      => (int)round($valor * 100), // em centavos
                'description' => $descricao,
                'quantity'    => 1,
                'code'        => 'LIC-' . $licencaId,
            ]],
            'customer' => [
                'name'  => $nome,
                'email' => $email,
                'type'  => 'individual',
            ],
            'payments' => [[
                'payment_method' => 'pix',
                'pix'            => [
                    'expires_in' => $expiresIn,
                ],
            ]],
            'closed' => true,
        ];

        $response = $this->post('/orders', $body);

        if (empty($response['id'])) {
            throw new \RuntimeException(
                'Erro ao criar pedido no Pagar.me: ' . ($response['message'] ?? json_encode($response))
            );
        }

        $charge = $response['charges'][0] ?? [];
        $tx     = $charge['last_transaction'] ?? [];

        return [
            'order_id'    => $response['id'],
            'charge_id'   => $charge['id'] ?? null,
            'qr_code'     => $tx['qr_code']     ?? null,
            'qr_code_url' => $tx['qr_code_url'] ?? null,
            'expires_at'  => $tx['expires_at']  ?? null,
            'status'      => $response['status'] ?? 'pending',
        ];
    }

    /**
     * Consulta o status de um pedido pelo ID.
     */
    public function consultarPedido(string $orderId): array {
        return $this->get('/orders/' . $orderId);
    }

    // ----------------------------------------------------------------

    private function post(string $path, array $body): array {
        return $this->request('POST', $path, $body);
    }

    private function get(string $path): array {
        return $this->request('GET', $path);
    }

    private function request(string $method, string $path, ?array $body = null): array {
        $ch = curl_init(self::BASE_URL . $path);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->secretKey . ':'),
        ];

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        $ca = $this->caBundle();
        if ($ca) $opts[CURLOPT_CAINFO] = $ca;
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        unset($ch);

        if ($raw === false) {
            throw new \RuntimeException('Falha na conexão com o Pagar.me.');
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function caBundle(): ?string {
        $candidates = [
            'C:/laragon/etc/ssl/cacert.pem',
            ini_get('curl.cainfo') ?: '',
            ini_get('openssl.cafile') ?: '',
        ];
        foreach ($candidates as $p) {
            if ($p && file_exists($p)) return $p;
        }
        return null;
    }
}
