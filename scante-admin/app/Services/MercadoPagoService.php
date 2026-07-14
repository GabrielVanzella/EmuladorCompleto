<?php
namespace App\Services;

class MercadoPagoService {

    private const BASE_URL = 'https://api.mercadopago.com';

    private string $accessToken;

    public function __construct(string $accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * Cria preferência de pagamento e retorna a URL do Checkout Pro (init_point).
     */
    public function criarPreferencia(
        int    $licencaId,
        string $tipo,
        float  $valor,
        string $email,
        string $deviceNome,
        string $appUrl,
        int    $quantidade = 1
    ): string {
        $body = [
            'items' => [[
                'title'      => $quantidade > 1
                    ? "ScanTE — Suporte {$tipo} ({$quantidade}x)"
                    : 'ScanTE — Licença ' . ucfirst($tipo),
                'quantity'   => $quantidade,
                'unit_price' => (float)$valor / $quantidade,
                'currency_id' => 'BRL',
            ]],
            'payer'              => ['email' => $email],
            'external_reference' => (string)$licencaId,
            'metadata'           => ['tipo' => $tipo, 'device_nome' => $deviceNome],
            'back_urls'          => [
                'success' => $appUrl . '/checkout/sucesso',
                'failure' => $appUrl . '/checkout/cancelado',
                'pending' => $appUrl . '/checkout/sucesso',
            ],
            'auto_return'      => 'approved',
        ];
        if (!preg_match('#https?://(localhost|127\.\d+\.\d+\.\d+)#i', $appUrl)) {
            $body['notification_url'] = $appUrl . '/api/webhook/mercadopago';
        }

        $resp = $this->post('/checkout/preferences', $body);

        if (empty($resp['init_point'])) {
            throw new \RuntimeException(
                'Mercado Pago não retornou init_point: ' . json_encode($resp)
            );
        }

        return $resp['init_point'];
    }

    /**
     * Processa pagamento transparente (Checkout Bricks).
     * Recebe o formData do Brick e chama POST /v1/payments.
     * Retorna ['status' => 'approved'|'pending'|'rejected', 'id' => string, 'mensagem' => string]
     */
    public function processarPagamento(
        int    $licencaId,
        string $tipo,
        float  $valor,
        string $email,
        array  $formData,
        string $appUrl,
        int    $quantidade = 1
    ): array {
        $extra = [
            'transaction_amount'  => $valor,
            'description'         => $quantidade > 1
                ? "ScanTE — Suporte {$tipo} ({$quantidade}x)"
                : 'ScanTE — Licença ' . ucfirst($tipo),
            'external_reference'  => (string)$licencaId,
            'statement_descriptor' => 'ScanTE',
        ];
        // Only send notification_url for publicly accessible hosts
        if (!preg_match('#https?://(localhost|127\.\d+\.\d+\.\d+)#i', $appUrl)) {
            $extra['notification_url'] = $appUrl . '/api/webhook/mercadopago';
        }
        $body = array_merge($formData, $extra);

        // Garante que o payer.email seja o cadastrado
        if (empty($body['payer']['email'])) {
            $body['payer']['email'] = $email;
        }

        $resp   = $this->post('/v1/payments', $body, 'LIC-' . $licencaId . '-' . time());
        $status = $resp['status'] ?? 'error';
        $payId  = (string)($resp['id'] ?? '');

        // Para Pix pendente, inclui os dados do QR code
        $pixTx = $resp['point_of_interaction']['transaction_data'] ?? [];

        return [
            'status'        => $status,
            'id'            => $payId,
            'mensagem'      => $resp['message'] ?? ($resp['cause'][0]['description'] ?? ''),
            'qr_code'       => $pixTx['qr_code']        ?? null,
            'qr_code_base64'=> $pixTx['qr_code_base64'] ?? null,
            'expires_at'    => $resp['date_of_expiration'] ?? null,
        ];
    }

    // ----------------------------------------------------------------

    private function post(string $path, array $body, ?string $idempotencyKey = null): array {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ];
        if ($idempotencyKey) {
            $headers[] = 'X-Idempotency-Key: ' . $idempotencyKey;
        }

        $ch = curl_init(self::BASE_URL . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
        ];
        $ca = $this->caBundle();
        if ($ca) $opts[CURLOPT_CAINFO] = $ca;
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        unset($ch);
        return is_string($raw) ? (json_decode($raw, true) ?? []) : [];
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
