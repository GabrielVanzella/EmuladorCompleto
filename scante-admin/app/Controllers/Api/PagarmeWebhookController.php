<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Licenca;

class PagarmeWebhookController extends Controller {

    private array $precos = [
        'mensal'    => PRECO_MENSAL,
        'anual'     => PRECO_ANUAL,
        'vitalicia' => PRECO_VITALICIA,
    ];

    public function handle(): void {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?? [];

        $type  = $body['type']    ?? '';
        $data  = $body['data']    ?? [];
        $orderId = $data['id']    ?? '';
        $status  = $data['status'] ?? '';
        $code    = $data['code']   ?? ''; // nosso "LIC-{id}"

        // Só processa pagamentos confirmados
        if ($type !== 'order.paid' && $status !== 'paid') {
            $this->json(['ok' => true]);
            return;
        }

        // Extrai o ID da licença do código do pedido (formato: LIC-123)
        if (!preg_match('/^LIC-(\d+)$/', $code, $m)) {
            $this->json(['ok' => true, 'aviso' => 'Código de pedido não reconhecido']);
            return;
        }

        $licencaId = (int)$m[1];
        $model     = new Licenca();
        $licenca   = $model->findById($licencaId);

        if (!$licenca) {
            $this->json(['ok' => true, 'aviso' => 'Licença não encontrada']);
            return;
        }

        // Pega ID da charge para registrar como payment_id
        $chargeId = $data['charges'][0]['id'] ?? $orderId;
        $tipo     = $licenca['tipo'];
        $valor    = $this->precos[$tipo] ?? PRECO_MENSAL;

        if ($licenca['status'] === 'pendente') {
            // Licença criada pelo checkout: registra o pagamento (fica
            // aguardando aprovação manual no admin, ver Licenca::aprovar)
            $model->registrarPagamento($licencaId, $chargeId);
        }
        // Já ativa? Ignora (webhook pode ser chamado mais de uma vez)

        // Registra pagamento na tabela pagamentos
        $this->db()->execute(
            "INSERT IGNORE INTO pagamentos (licenca_id, payment_id, valor, tipo, status, criado_em)
             VALUES (?, ?, ?, ?, 'approved', NOW())",
            [$licencaId, $chargeId, $valor, $tipo]
        );

        $this->json(['ok' => true]);
    }

    private function db(): \App\Core\Database {
        return \App\Core\Database::getInstance();
    }
}
