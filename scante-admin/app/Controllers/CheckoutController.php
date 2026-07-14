<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Licenca;
use App\Models\Empresa;
use App\Models\Configuracao;
use App\Services\PagarmeService;
use App\Services\MercadoPagoService;

class CheckoutController extends Controller {

    private const ANOS_MIN = 1;
    private const ANOS_MAX = 5;
    private const QTD_MAX  = 200;

    /** Preço por ano de suporte (editável em Admin → Configurações). */
    private function precoAno(): float {
        return (float)(new Configuracao())->get('preco_ano_suporte', (string)PRECO_ANUAL);
    }

    // ----------------------------------------------------------------
    // Formulário de dados
    // ----------------------------------------------------------------

    public function index(): void {
        $this->view('checkout.index', [
            'precoAno' => $this->precoAno(),
            'dados'    => [],
            'erro'     => null,
        ], 'checkout');
    }

    // ----------------------------------------------------------------
    // Processa formulário → cria licença(s) pendente(s) → redireciona
    // ----------------------------------------------------------------

    public function processar(): void {
        $email       = trim($this->input('email', ''));
        $telefone    = trim($this->input('telefone', ''));
        $quantidade  = min(max((int)$this->input('quantidade', 1), 1), self::QTD_MAX);
        $anosSuporte = min(max((int)$this->input('anos_suporte', 1), self::ANOS_MIN), self::ANOS_MAX);

        $novaEmpresaNome    = trim($this->input('nova_empresa_nome', ''));
        $novaEmpresaCnpj    = trim($this->input('nova_empresa_cnpj', ''));
        $novaEmpresaContato = trim($this->input('nova_empresa_contato', ''));

        if (!$email || !$novaEmpresaNome || !$novaEmpresaCnpj) {
            $this->view('checkout.index', [
                'precoAno' => $this->precoAno(),
                'dados'    => compact('email', 'telefone', 'quantidade', 'anosSuporte') + [
                    'novaEmpresaNome' => $novaEmpresaNome, 'novaEmpresaCnpj' => $novaEmpresaCnpj,
                ],
                'erro' => 'Preencha nome da empresa, CNPJ e e-mail.',
            ], 'checkout');
            return;
        }

        // Já existe empresa com esse CNPJ? Reaproveita em vez de duplicar.
        $empresaExistente = (new Empresa())->findByCnpj($novaEmpresaCnpj);
        if ($empresaExistente) {
            $empresaId = (int)$empresaExistente['id'];
        } else {
            $empresaId = (new Empresa())->create([
                'nome'     => $novaEmpresaNome,
                'cnpj'     => $novaEmpresaCnpj,
                'email'    => $email,
                'telefone' => $telefone ?: null,
                'contato'  => $novaEmpresaContato ?: null,
            ]);
        }

        $licencaId = (new Licenca())->criarPendenteLote(
            $empresaId, $quantidade, $anosSuporte, $email, $telefone
        );

        $this->redirectTo(APP_URL . '/checkout/pagamento?id=' . $licencaId . '&h=' . $this->token($licencaId));
    }

    // ----------------------------------------------------------------
    // Página de pagamento
    // ----------------------------------------------------------------

    public function pagamento(): void {
        $licencaId = (int)($_GET['id'] ?? 0);
        $token     = $_GET['h'] ?? '';

        if (!$licencaId || !hash_equals($this->token($licencaId), $token)) {
            $this->redirectTo(APP_URL . '/checkout');
            return;
        }

        $licenca = (new Licenca())->findById($licencaId);
        if (!$licenca || $licenca['status'] !== 'pendente') {
            $this->redirectTo(APP_URL . '/checkout/sucesso?id=' . $licencaId . '&h=' . $token);
            return;
        }

        $quantidade  = (int)$licenca['quantidade'];
        $anosSuporte = (int)$licenca['anos_suporte'];
        $valor       = $this->precoAno() * $anosSuporte * $quantidade;

        $gateway = Configuracao::gatewayAtivo();
        $cfg     = new Configuracao();

        $pixData = null;
        $erroGw  = isset($_GET['erro']);

        $descricao = "ScanTE — Suporte {$anosSuporte} ano(s) × {$quantidade} licença(s)";

        if ($gateway === 'pagarme' && !$erroGw) {
            $sk = $cfg->get('pagarme_secret_key');
            if ($sk) {
                try {
                    $svc     = new PagarmeService($sk);
                    $pixData = $svc->criarOrdemPix(
                        $licencaId, $valor, $descricao,
                        $licenca['email'] ?: 'cliente@scante.com',
                        $licenca['email'] ?: 'Cliente ScanTE',
                        3600
                    );
                } catch (\Throwable $e) {
                    $erroGw = true;
                    error_log('[Pagar.me] ' . $e->getMessage());
                }
            }
        }

        $this->view('checkout.pagamento', [
            'licenca'     => $licenca,
            'licencaId'   => $licencaId,
            'token'       => $token,
            'quantidade'  => $quantidade,
            'anosSuporte' => $anosSuporte,
            'descricao'   => $descricao,
            'valor'       => $valor,
            'gateway'     => $gateway,
            'pixData'     => $pixData,
            'erroGw'      => $erroGw,
            'mpPublicKey' => $cfg->get('mp_public_key'),
        ], 'checkout');
    }

    // ----------------------------------------------------------------
    // POST /checkout/pagar — dev e mercadopago
    // ----------------------------------------------------------------

    public function pagar(): void {
        $licencaId = (int)$this->input('licenca_id');
        $token     = $this->input('token', '');

        if (!$licencaId || !hash_equals($this->token($licencaId), $token)) {
            $this->redirectTo(APP_URL . '/checkout');
            return;
        }

        $licenca = (new Licenca())->findById($licencaId);
        if (!$licenca || $licenca['status'] !== 'pendente') {
            $this->redirectTo(APP_URL . '/checkout/sucesso?id=' . $licencaId . '&h=' . $token);
            return;
        }

        $quantidade  = (int)$licenca['quantidade'];
        $anosSuporte = (int)$licenca['anos_suporte'];
        $valor       = $this->precoAno() * $anosSuporte * $quantidade;
        $gateway     = Configuracao::gatewayAtivo();
        $cfg         = new Configuracao();

        if ($gateway === 'mercadopago') {
            $accessToken = $cfg->get('mp_access_token');
            if (!$accessToken) {
                $this->redirectTo(APP_URL . '/checkout/pagamento?id=' . $licencaId . '&h=' . $token . '&erro=1');
                return;
            }
            try {
                $url = (new MercadoPagoService($accessToken))->criarPreferencia(
                    $licencaId, 'anual', $valor,
                    $licenca['email'] ?: 'cliente@scante.com',
                    'ScanTE',
                    APP_URL,
                    $quantidade
                );
                // Guarda na sessão para recuperar o pedido na página de sucesso
                $_SESSION['checkout_licenca_id'] = $licencaId;
                $_SESSION['checkout_token']      = $this->token($licencaId);
                $this->redirectTo($url);
            } catch (\Throwable $e) {
                error_log('[MercadoPago] ' . $e->getMessage());
                $this->redirectTo(APP_URL . '/checkout/pagamento?id=' . $licencaId . '&h=' . $token . '&erro=1');
            }
            return;
        }

        // Pagar.me: pagamento vem via webhook, não por aqui
        if ($gateway === 'pagarme') {
            $_SESSION['checkout_licenca_id'] = $licencaId;
            $_SESSION['checkout_token']      = $this->token($licencaId);
            $this->redirectTo(APP_URL . '/checkout/pagamento?id=' . $licencaId . '&h=' . $token);
            return;
        }

        // Modo dev: registra o "pagamento" e passa id+token para a página de
        // sucesso — as chaves só valem depois que o admin aprovar.
        (new Licenca())->registrarPagamento($licencaId, 'DEV-' . $licencaId);
        $this->redirectTo(APP_URL . '/checkout/sucesso?id=' . $licencaId . '&h=' . $this->token($licencaId));
    }

    // ----------------------------------------------------------------
    // POST /checkout/processar-pagamento — Checkout Transparente (Bricks)
    // ----------------------------------------------------------------

    public function processarPagamento(): void {
        header('Content-Type: application/json');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $licencaId = (int)($body['licenca_id'] ?? 0);
        $token     = $body['checkout_token'] ?? '';

        if (!$licencaId || !hash_equals($this->token($licencaId), $token)) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Token inválido.']);
            exit;
        }

        $licenca = (new Licenca())->findById($licencaId);
        if (!$licenca) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Licença não encontrada.']);
            exit;
        }
        if ($licenca['status'] === 'ativa') {
            echo json_encode(['status' => 'approved']);
            exit;
        }

        $cfg         = new Configuracao();
        $accessToken = $cfg->get('mp_access_token');
        if (!$accessToken) {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Gateway não configurado.']);
            exit;
        }

        $quantidade  = (int)$licenca['quantidade'];
        $anosSuporte = (int)$licenca['anos_suporte'];
        $valor       = $this->precoAno() * $anosSuporte * $quantidade;

        // Remove internal fields before forwarding to MP API
        $formData = $body;
        unset($formData['licenca_id'], $formData['checkout_token']);

        try {
            $resultado = (new MercadoPagoService($accessToken))->processarPagamento(
                $licencaId, 'anual', $valor,
                $licenca['email'] ?? '',
                $formData,
                APP_URL,
                $quantidade
            );

            // Pix pendente: não ativa ainda, devolve QR code para polling
            if ($resultado['status'] === 'pending' && !empty($resultado['qr_code'])) {
                echo json_encode($resultado); // contém qr_code, qr_code_base64
                exit;
            }

            // Aprovado (cartão/débito): registra o pagamento (aguarda aprovação
            // manual no admin) e devolve URL de sucesso
            if ($resultado['status'] === 'approved') {
                (new Licenca())->registrarPagamento($licencaId, $resultado['id']);
                $resultado['redirect_url'] = APP_URL . '/checkout/sucesso?id=' . $licencaId . '&h=' . $this->token($licencaId);
            }

            echo json_encode($resultado);
        } catch (\Throwable $e) {
            error_log('[MP Bricks] ' . $e->getMessage());
            echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao processar pagamento.']);
        }
        exit;
    }

    // ----------------------------------------------------------------
    // Polling JS — GET /checkout/status?id=X&h=Y
    // ----------------------------------------------------------------

    public function status(): void {
        header('Content-Type: application/json');
        $licencaId = (int)($_GET['id'] ?? 0);
        $token     = $_GET['h'] ?? '';

        if (!$licencaId || !hash_equals($this->token($licencaId), $token)) {
            echo json_encode(['status' => 'invalido']);
            exit;
        }

        $licenca = (new Licenca())->findById($licencaId);
        echo json_encode([
            'status' => $licenca['status'] ?? 'erro',
            'pago'   => !empty($licenca['payment_id'] ?? null),
        ]);
        exit;
    }

    // ----------------------------------------------------------------
    // Páginas de resultado
    // ----------------------------------------------------------------

    public function sucesso(): void {
        // Tenta recuperar o pedido pelo id+token (passado via query string ou sessão)
        $licencaId = (int)($_GET['id'] ?? $_SESSION['checkout_licenca_id'] ?? 0);
        $token     = $_GET['h'] ?? $_SESSION['checkout_token'] ?? '';

        $chaves        = [];
        $aguardandoPagamento = false;
        $aguardandoAprovacao = false;

        if ($licencaId && $token && hash_equals($this->token($licencaId), $token)) {
            $licenca = (new Licenca())->findById($licencaId);
            if ($licenca) {
                if ($licenca['status'] === 'ativa') {
                    $chaves = array_column(
                        (new Licenca())->findAllByPaymentId($licenca['payment_id']),
                        'chave'
                    );
                    if (!$chaves) $chaves = [$licenca['chave']]; // fallback de segurança
                } elseif (!empty($licenca['payment_id'])) {
                    // Pagamento confirmado, mas ainda aguardando aprovação manual no admin
                    $aguardandoAprovacao = true;
                } else {
                    // Pagamento ainda não confirmado (PIX / async) — JS vai fazer polling
                    $aguardandoPagamento = true;
                }
            }
        }

        // Limpa a sessão após usar
        unset($_SESSION['checkout_licenca_id'], $_SESSION['checkout_token']);

        $this->view('checkout.sucesso', [
            'chaves'               => $chaves,
            'aguardandoPagamento'  => $aguardandoPagamento,
            'aguardandoAprovacao'  => $aguardandoAprovacao,
            'licencaId'            => $licencaId,
            'token'                => $token,
        ], 'checkout');
    }

    public function cancelado(): void { $this->view('checkout.cancelado', [], 'checkout'); }

    // ----------------------------------------------------------------

    private function token(int $licencaId): string {
        return substr(hash_hmac('sha256', (string)$licencaId, API_SECRET), 0, 20);
    }
}
