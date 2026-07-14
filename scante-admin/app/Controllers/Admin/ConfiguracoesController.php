<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Configuracao;

class ConfiguracoesController extends Controller {

    private array $chaves = [
        'gateway_ativo',
        'pagarme_secret_key',
        'pagarme_public_key',
        'mp_access_token',
        'mp_public_key',
        'mp_webhook_secret',
        'preco_ano_suporte',
        'email_notificacoes',
    ];

    public function index(): void {
        Auth::requireAdmin();

        $cfg    = new Configuracao();
        $dados  = $cfg->getMultiple($this->chaves);
        if (empty($dados['preco_ano_suporte'])) {
            $dados['preco_ano_suporte'] = (string)PRECO_ANUAL;
        }
        if (empty($dados['email_notificacoes'])) {
            $dados['email_notificacoes'] = 'scante@scante.com.br';
        }
        $status = $this->detectarStatus($dados);

        $this->view('admin.configuracoes.index', [
            'cfg'    => $dados,
            'status' => $status,
            'flash'  => $this->getFlash(),
        ], 'admin');
    }

    public function salvar(): void {
        Auth::requireAdmin();

        $cfg     = new Configuracao();
        $gateway = $this->input('gateway_ativo', 'dev');

        // Apenas aceita valores válidos
        if (!in_array($gateway, ['pagarme', 'mercadopago', 'dev'])) {
            $gateway = 'dev';
        }
        $cfg->set('gateway_ativo', $gateway);

        // Preço por ano de suporte (checkout em lote)
        $precoAno = (float)str_replace(',', '.', (string)$this->input('preco_ano_suporte', ''));
        if ($precoAno > 0) {
            $cfg->set('preco_ano_suporte', (string)$precoAno);
        }

        // E-mail que recebe aviso de novas solicitações de licenças
        $this->salvarSePreenchido($cfg, 'email_notificacoes');

        // Pagar.me — só atualiza se o campo não estiver em branco
        $this->salvarSePreenchido($cfg, 'pagarme_secret_key');
        $this->salvarSePreenchido($cfg, 'pagarme_public_key');

        // Mercado Pago
        $this->salvarSePreenchido($cfg, 'mp_access_token');
        $this->salvarSePreenchido($cfg, 'mp_public_key');
        $this->salvarSePreenchido($cfg, 'mp_webhook_secret');

        $this->flash('success', 'Configurações salvas.');
        $this->redirect('/admin/configuracoes');
    }

    public function limparChave(): void {
        Auth::requireAdmin();

        $chave = $this->input('chave', '');
        if (in_array($chave, $this->chaves) && $chave !== 'gateway_ativo') {
            (new Configuracao())->set($chave, '');
        }
        $this->flash('success', 'Chave removida.');
        $this->redirect('/admin/configuracoes');
    }

    // ----------------------------------------------------------------

    private function salvarSePreenchido(Configuracao $cfg, string $chave): void {
        $val = trim($this->input($chave, ''));
        // Placeholder mascarado? Não salva
        if ($val && !str_contains($val, '****')) {
            $cfg->set($chave, $val);
        }
    }

    private function detectarStatus(array $dados): array {
        $gateway = $dados['gateway_ativo'] ?? 'dev';
        $pm      = !empty($dados['pagarme_secret_key']);
        $mp      = !empty($dados['mp_access_token']);

        return [
            'gateway'    => $gateway,
            'pagarme_ok' => $pm,
            'mp_ok'      => $mp,
            'ativo_ok'   => match($gateway) {
                'pagarme'      => $pm,
                'mercadopago'  => $mp,
                default        => true,
            },
        ];
    }
}
