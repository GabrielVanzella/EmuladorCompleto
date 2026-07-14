<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Licenca;
use App\Models\Configuracao;

class LicencaController extends Controller {

    // Autenticação simples via header Authorization: Bearer <API_SECRET>
    private function autenticar(): void {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
        $token  = str_replace('Bearer ', '', $header);
        if ($token !== API_SECRET) {
            $this->json(['erro' => 'Não autorizado.'], 401);
        }
    }

    /** POST /api/licenca/validar — chamado pelo app Android ao abrir */
    public function validar(): void {
        $this->autenticar();
        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $chave     = trim($body['chave'] ?? '');
        $deviceId  = trim($body['device_id'] ?? '');
        $deviceNome = trim($body['device_nome'] ?? '');

        if (!$chave || !$deviceId) {
            $this->json(['valida' => false, 'mensagem' => 'Parâmetros incompletos.'], 400);
        }

        $resultado = (new Licenca())->validarParaApp($chave, $deviceId, $deviceNome);
        $this->json($resultado, $resultado['valida'] ? 200 : 403);
    }

    /** POST /api/webhook/mercadopago — chamado pelo Mercado Pago após pagamento */
    public function webhookMercadoPago(): void {
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $cfg   = new Configuracao();
        $token = $cfg->get('mp_access_token');

        if (!$token) {
            $this->json(['ok' => true, 'aviso' => 'MP não configurado']);
            return;
        }

        $tipo  = $body['type'] ?? '';
        $payId = $body['data']['id'] ?? null;

        if ($tipo !== 'payment' || !$payId) {
            $this->json(['ok' => true]);
            return;
        }

        // Consulta o pagamento na API do Mercado Pago
        $ch = curl_init("https://api.mercadopago.com/v1/payments/$payId");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = json_decode(curl_exec($ch), true);
        unset($ch);

        if (($response['status'] ?? '') !== 'approved') {
            $this->json(['ok' => true]);
        }

        // Pega a licença pelo external_reference (deve ser o ID da licença)
        $licencaId = $response['external_reference'] ?? null;
        $tipo      = $response['metadata']['tipo'] ?? 'mensal';
        $dias      = (int)($response['metadata']['dias'] ?? 30);

        if ($licencaId) {
            $model   = new Licenca();
            $licenca = $model->findById((int)$licencaId);

            if ($licenca && $licenca['status'] === 'pendente') {
                // Licença criada pelo checkout: registra o pagamento (fica
                // aguardando aprovação manual no admin, ver Licenca::aprovar)
                $model->registrarPagamento((int)$licencaId, $payId);
            } else {
                // Licença existente: apenas estende
                $model->estender((int)$licencaId, $dias);
            }

            // Registra pagamento
            $this->db()->execute(
                "INSERT INTO pagamentos (licenca_id, payment_id, valor, tipo, status, criado_em) VALUES (?,?,?,?,'approved',NOW())",
                [$licencaId, $payId, $response['transaction_amount'], $tipo]
            );
        }

        $this->json(['ok' => true]);
    }

    private function db(): \App\Core\Database {
        return \App\Core\Database::getInstance();
    }
}
