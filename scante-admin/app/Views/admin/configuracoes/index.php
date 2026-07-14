<?php
function mascarar(string $val): string {
    if (!$val) return '';
    $len = strlen($val);
    if ($len <= 8) return str_repeat('*', $len);
    return substr($val, 0, 6) . str_repeat('*', max(0, $len - 10)) . substr($val, -4);
}

$gatewayAtivo = $cfg['gateway_ativo'] ?? 'dev';
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center mb-4 gap-2">
  <h4 class="fw-bold mb-0"><i class="bi bi-sliders me-2"></i>Configurações de Pagamento</h4>
</div>

<!-- Status atual -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card text-center p-3 h-100 <?= $status['ativo_ok'] ? 'border-success' : 'border-warning' ?>"
         style="border-width:2px!important">
      <div class="mb-2">
        <?php if ($gatewayAtivo === 'pagarme'): ?>
          <span class="badge bg-primary fs-6 px-3 py-2"><i class="bi bi-credit-card me-1"></i>Pagar.me</span>
        <?php elseif ($gatewayAtivo === 'mercadopago'): ?>
          <span class="badge bg-info fs-6 px-3 py-2" style="background:#009ee3!important"><i class="bi bi-credit-card me-1"></i>Mercado Pago</span>
        <?php else: ?>
          <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-tools me-1"></i>Modo Dev</span>
        <?php endif; ?>
      </div>
      <div class="text-muted small">Gateway ativo</div>
      <?php if (!$status['ativo_ok']): ?>
      <div class="text-warning small mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Chave não configurada</div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center p-3 h-100">
      <div class="mb-2">
        <?php if ($status['pagarme_ok']): ?>
          <i class="bi bi-check-circle-fill text-success" style="font-size:1.8rem"></i>
        <?php else: ?>
          <i class="bi bi-x-circle-fill text-muted" style="font-size:1.8rem"></i>
        <?php endif; ?>
      </div>
      <div class="fw-semibold">Pagar.me</div>
      <div class="text-muted small"><?= $status['pagarme_ok'] ? 'Chave configurada' : 'Sem chave' ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center p-3 h-100">
      <div class="mb-2">
        <?php if ($status['mp_ok']): ?>
          <i class="bi bi-check-circle-fill text-success" style="font-size:1.8rem"></i>
        <?php else: ?>
          <i class="bi bi-x-circle-fill text-muted" style="font-size:1.8rem"></i>
        <?php endif; ?>
      </div>
      <div class="fw-semibold">Mercado Pago</div>
      <div class="text-muted small"><?= $status['mp_ok'] ? 'Chave configurada' : 'Sem chave' ?></div>
    </div>
  </div>
</div>

<form method="POST" action="<?= APP_URL ?>/admin/configuracoes/salvar">

  <!-- Gateway ativo -->
  <div class="card mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-toggles me-2"></i>Gateway ativo</div>
    <div class="card-body">
      <p class="text-muted small mb-3">Selecione qual processador de pagamento será usado na página de checkout.</p>
      <div class="row g-3">

        <div class="col-md-4">
          <label class="gateway-option <?= $gatewayAtivo === 'pagarme' ? 'selected' : '' ?>">
            <input type="radio" name="gateway_ativo" value="pagarme"
                   <?= $gatewayAtivo === 'pagarme' ? 'checked' : '' ?> onchange="marcarGateway(this)">
            <div class="gateway-content">
              <div class="gateway-icon bg-primary text-white"><i class="bi bi-qr-code"></i></div>
              <div class="fw-bold">Pagar.me</div>
              <div class="text-muted small">Pix com QR Code inline</div>
              <div class="mt-2">
                <span class="badge bg-light text-dark border"><i class="bi bi-qr-code me-1"></i>Pix</span>
                <span class="badge bg-light text-dark border"><i class="bi bi-receipt me-1"></i>Boleto</span>
              </div>
            </div>
          </label>
        </div>

        <div class="col-md-4">
          <label class="gateway-option <?= $gatewayAtivo === 'mercadopago' ? 'selected' : '' ?>">
            <input type="radio" name="gateway_ativo" value="mercadopago"
                   <?= $gatewayAtivo === 'mercadopago' ? 'checked' : '' ?> onchange="marcarGateway(this)">
            <div class="gateway-content">
              <div class="gateway-icon" style="background:#009ee3; color:#fff"><i class="bi bi-box-arrow-up-right"></i></div>
              <div class="fw-bold">Mercado Pago</div>
              <div class="text-muted small">Redirect para Checkout Pro</div>
              <div class="mt-2">
                <span class="badge bg-light text-dark border"><i class="bi bi-credit-card me-1"></i>Cartão</span>
                <span class="badge bg-light text-dark border"><i class="bi bi-qr-code me-1"></i>Pix</span>
                <span class="badge bg-light text-dark border"><i class="bi bi-receipt me-1"></i>Boleto</span>
              </div>
            </div>
          </label>
        </div>

        <div class="col-md-4">
          <label class="gateway-option <?= $gatewayAtivo === 'dev' ? 'selected' : '' ?>">
            <input type="radio" name="gateway_ativo" value="dev"
                   <?= $gatewayAtivo === 'dev' ? 'checked' : '' ?> onchange="marcarGateway(this)">
            <div class="gateway-content">
              <div class="gateway-icon bg-warning text-dark"><i class="bi bi-tools"></i></div>
              <div class="fw-bold">Modo Desenvolvimento</div>
              <div class="text-muted small">Ativa licença direto, sem gateway</div>
              <div class="mt-2">
                <span class="badge bg-warning text-dark">Somente local</span>
              </div>
            </div>
          </label>
        </div>

      </div>
    </div>
  </div>

  <!-- Preço por ano de suporte -->
  <div class="card mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-tag me-2"></i>Preço do checkout em lote</div>
    <div class="card-body">
      <p class="text-muted small mb-3">
        Valor cobrado por <strong>ano de suporte, por licença</strong>, no checkout público de compra em lote
        (<code><?= APP_URL ?>/checkout</code>). Total = preço × anos × quantidade, sem desconto por volume.
      </p>
      <label class="form-label fw-semibold">Preço por ano (R$)</label>
      <input type="text" name="preco_ano_suporte" class="form-control" style="max-width:200px"
             value="<?= htmlspecialchars($cfg['preco_ano_suporte'] ?? '') ?>" placeholder="199,90">
    </div>
  </div>

  <!-- Notificação de novas solicitações -->
  <div class="card mb-3">
    <div class="card-header fw-semibold"><i class="bi bi-envelope me-2"></i>Notificação de novas solicitações</div>
    <div class="card-body">
      <p class="text-muted small mb-3">
        Quando um cliente termina o checkout em lote, ele não paga direto — vira uma <strong>solicitação
        pendente</strong> em <a href="<?= APP_URL ?>/admin/licencas">Licenças</a> aguardando sua aprovação.
        Esse e-mail avisa que chegou um pedido novo.
      </p>
      <label class="form-label fw-semibold">E-mail para receber os avisos</label>
      <input type="email" name="email_notificacoes" class="form-control" style="max-width:320px"
             value="<?= htmlspecialchars($cfg['email_notificacoes'] ?? '') ?>" placeholder="contato@scante.com.br">
    </div>
  </div>

  <!-- Pagar.me -->
  <div class="card mb-3" id="secaoPagarme" style="<?= $gatewayAtivo !== 'pagarme' ? 'opacity:.6' : '' ?>">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span class="fw-semibold"><i class="bi bi-qr-code me-2"></i>Pagar.me</span>
      <a href="https://dashboard.pagar.me" target="_blank" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir dashboard
      </a>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Secret Key <span class="text-muted fw-normal">(sk_test_... ou sk_...)</span></label>
          <?php $pmSk = $cfg['pagarme_secret_key'] ?? ''; ?>
          <?php if ($pmSk): ?>
          <div class="input-group">
            <input type="text" class="form-control font-monospace" value="<?= mascarar($pmSk) ?>" readonly>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="limparChave('pagarme_secret_key')" title="Remover">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          <div class="form-text text-success"><i class="bi bi-check-circle me-1"></i>Configurada</div>
          <div class="mt-2">
            <label class="form-label small text-muted">Substituir por nova chave:</label>
            <input type="password" name="pagarme_secret_key" class="form-control form-control-sm font-monospace"
                   placeholder="sk_test_nova_chave..." autocomplete="off">
          </div>
          <?php else: ?>
          <input type="password" name="pagarme_secret_key" class="form-control font-monospace"
                 placeholder="sk_test_XXXXXXXXXXXXXXXX" autocomplete="off">
          <div class="form-text">Encontre em: Dashboard → Desenvolvimento → Chaves</div>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Public Key <span class="text-muted fw-normal">(pk_test_... ou pk_...)</span></label>
          <?php $pmPk = $cfg['pagarme_public_key'] ?? ''; ?>
          <?php if ($pmPk): ?>
          <div class="input-group">
            <input type="text" class="form-control font-monospace" value="<?= mascarar($pmPk) ?>" readonly>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="limparChave('pagarme_public_key')" title="Remover">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          <div class="form-text text-success"><i class="bi bi-check-circle me-1"></i>Configurada</div>
          <div class="mt-2">
            <label class="form-label small text-muted">Substituir:</label>
            <input type="password" name="pagarme_public_key" class="form-control form-control-sm font-monospace"
                   placeholder="pk_test_nova_chave..." autocomplete="off">
          </div>
          <?php else: ?>
          <input type="password" name="pagarme_public_key" class="form-control font-monospace"
                 placeholder="pk_test_XXXXXXXXXXXXXXXX" autocomplete="off">
          <?php endif; ?>
        </div>
      </div>
      <div class="alert alert-light border mt-3 mb-0 small">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Webhook URL para configurar no Pagar.me:</strong>
        <code class="ms-1"><?= APP_URL ?>/api/webhook/pagarme</code>
        — Evento: <code>order.paid</code>
      </div>
    </div>
  </div>

  <!-- Mercado Pago -->
  <div class="card mb-3" id="secaoMp" style="<?= $gatewayAtivo !== 'mercadopago' ? 'opacity:.6' : '' ?>">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span class="fw-semibold"><i class="bi bi-credit-card me-2"></i>Mercado Pago</span>
      <a href="https://www.mercadopago.com.br/developers" target="_blank" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir dashboard
      </a>
    </div>
    <div class="card-body">
      <div class="alert alert-info border-0 small mb-3" style="background:#e8f4fd">
        <i class="bi bi-info-circle me-1"></i>
        Ambas as chaves estão em <strong>Mercado Pago Developers → Sua aplicação → Credenciais de teste / produção</strong>.
        <br>Access Token começa com <code>TEST-</code> (sandbox) ou <code>APP_USR-</code> (produção).
        Public Key começa com <code>TEST-</code> ou <code>APP_USR-</code> também.
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Access Token <span class="text-muted fw-normal">(servidor)</span></label>
          <?php $mpTk = $cfg['mp_access_token'] ?? ''; ?>
          <?php if ($mpTk): ?>
          <div class="input-group">
            <input type="text" class="form-control font-monospace" value="<?= mascarar($mpTk) ?>" readonly>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="limparChave('mp_access_token')" title="Remover"><i class="bi bi-trash"></i></button>
          </div>
          <div class="form-text text-success"><i class="bi bi-check-circle me-1"></i>Configurado</div>
          <div class="mt-2">
            <label class="form-label small text-muted">Substituir:</label>
            <input type="password" name="mp_access_token" class="form-control form-control-sm font-monospace"
                   placeholder="TEST-XXXXX ou APP_USR-XXXXX" autocomplete="off">
          </div>
          <?php else: ?>
          <input type="password" name="mp_access_token" class="form-control font-monospace"
                 placeholder="TEST-XXXXX ou APP_USR-XXXXX" autocomplete="off">
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Public Key <span class="text-muted fw-normal">(frontend · Bricks)</span></label>
          <?php $mpPk = $cfg['mp_public_key'] ?? ''; ?>
          <?php if ($mpPk): ?>
          <div class="input-group">
            <input type="text" class="form-control font-monospace" value="<?= mascarar($mpPk) ?>" readonly>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="limparChave('mp_public_key')" title="Remover"><i class="bi bi-trash"></i></button>
          </div>
          <div class="form-text text-success"><i class="bi bi-check-circle me-1"></i>Configurada</div>
          <div class="mt-2">
            <label class="form-label small text-muted">Substituir:</label>
            <input type="password" name="mp_public_key" class="form-control form-control-sm font-monospace"
                   placeholder="TEST-XXXXX ou APP_USR-XXXXX" autocomplete="off">
          </div>
          <?php else: ?>
          <input type="password" name="mp_public_key" class="form-control font-monospace"
                 placeholder="TEST-XXXXX ou APP_USR-XXXXX" autocomplete="off">
          <div class="form-text text-danger"><i class="bi bi-exclamation-circle me-1"></i>Obrigatória para o Checkout Transparente</div>
          <?php endif; ?>
        </div>
        <div class="col-md-12">
          <label class="form-label fw-semibold">Webhook Secret <span class="text-muted fw-normal">(opcional)</span></label>
          <?php $mpWh = $cfg['mp_webhook_secret'] ?? ''; ?>
          <?php if ($mpWh): ?>
          <div class="input-group">
            <input type="text" class="form-control font-monospace" value="<?= mascarar($mpWh) ?>" readonly>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="limparChave('mp_webhook_secret')" title="Remover">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          <?php else: ?>
          <input type="password" name="mp_webhook_secret" class="form-control font-monospace"
                 placeholder="opcional" autocomplete="off">
          <?php endif; ?>
        </div>
      </div>
      <div class="alert alert-light border mt-3 mb-0 small">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Webhook URL para configurar no Mercado Pago:</strong>
        <code class="ms-1"><?= APP_URL ?>/api/webhook/mercadopago</code>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-accent px-4">
      <i class="bi bi-floppy me-1"></i>Salvar configurações
    </button>
    <a href="<?= APP_URL ?>/admin" class="btn btn-outline-secondary">Cancelar</a>
  </div>

</form>

<!-- Form oculto para limpar chave -->
<form method="POST" action="<?= APP_URL ?>/admin/configuracoes/limpar-chave" id="formLimpar">
  <input type="hidden" name="chave" id="chaveLimpar">
</form>

<style>
.gateway-option {
  display: block;
  cursor: pointer;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  transition: border-color .15s, box-shadow .15s;
  height: 100%;
}
.gateway-option input[type=radio] { display: none; }
.gateway-option:hover { border-color: var(--accent); }
.gateway-option.selected { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,212,116,.15); }
.gateway-content { text-align: center; }
.gateway-icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; margin: 0 auto 10px;
}
</style>

<script>
function marcarGateway(radio) {
  document.querySelectorAll('.gateway-option').forEach(el => el.classList.remove('selected'));
  radio.closest('.gateway-option').classList.add('selected');

  const v = radio.value;
  document.getElementById('secaoPagarme').style.opacity = v === 'pagarme'      ? '1' : '.6';
  document.getElementById('secaoMp').style.opacity      = v === 'mercadopago'  ? '1' : '.6';
}

function limparChave(chave) {
  if (!confirm('Remover esta chave?')) return;
  document.getElementById('chaveLimpar').value = chave;
  document.getElementById('formLimpar').submit();
}
</script>
