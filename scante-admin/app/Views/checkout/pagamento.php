<?php
$valorFmt        = 'R$ ' . number_format($valor, 2, ',', '.');
$temPix          = !empty($pixData['qr_code']);
$statusUrl       = APP_URL . '/checkout/status?id=' . $licencaId . '&h=' . urlencode($token);
$processarUrl    = APP_URL . '/checkout/processar-pagamento';
$sucessoUrl      = APP_URL . '/checkout/sucesso?id=' . $licencaId . '&h=' . urlencode($token);
?>

<!-- SDK Mercado Pago (só carrega se for MP com chave pública) -->
<?php if ($gateway === 'mercadopago' && $mpPublicKey): ?>
<script src="https://sdk.mercadopago.com/js/v2"></script>
<?php endif; ?>

<div class="pg-wrap">

  <!-- ===== CABEÇALHO ===== -->
  <div class="pg-header">
    <div class="pg-brand">Scan<span>TE</span></div>
    <div class="pg-steps">
      <div class="ps done"><div class="ps-dot"><i class="bi bi-check"></i></div><span>Dados</span></div>
      <div class="ps-line done"></div>
      <div class="ps active"><div class="ps-dot">2</div><span>Pagamento</span></div>
      <div class="ps-line"></div>
      <div class="ps"><div class="ps-dot">3</div><span>Confirmação</span></div>
    </div>
    <div style="width:80px"></div>
  </div>

  <!-- ===== CORPO ===== -->
  <div class="pg-body">
    <div class="pg-grid">

      <!-- COLUNA ESQUERDA: Resumo -->
      <div class="pg-col-left">
        <div class="summary-card">
          <div class="summary-product">
            <div class="summary-icon"><i class="bi bi-key"></i></div>
            <div>
              <div class="fw-bold fs-6"><?= htmlspecialchars($descricao) ?></div>
              <div class="text-muted" style="font-size:.82rem"><?= $anosSuporte * 365 ?> dias de acesso por licença</div>
            </div>
          </div>

          <ul class="summary-benefits">
            <li><i class="bi bi-check-circle-fill"></i><?= $quantidade ?> licença<?= $quantidade > 1 ? 's' : '' ?></li>
            <li><i class="bi bi-check-circle-fill"></i><?= $anosSuporte ?> ano<?= $anosSuporte > 1 ? 's' : '' ?> de suporte cada</li>
          </ul>

          <div class="summary-total">
            <span class="text-muted">Total a pagar</span>
            <div>
              <div class="summary-price"><?= $valorFmt ?></div>
              <div class="text-muted" style="font-size:.75rem">pagamento único · sem recorrência</div>
            </div>
          </div>

          <div class="summary-secure">
            <i class="bi bi-shield-fill-check"></i>
            Compra 100% segura e protegida
          </div>
        </div>

        <a href="<?= APP_URL ?>/checkout" class="back-link">
          <i class="bi bi-arrow-left me-1"></i>Alterar dados
        </a>
      </div>

      <!-- COLUNA DIREITA: Pagamento -->
      <div class="pg-col-right">

        <?php if ($erroGw): ?>
        <div class="alert alert-danger d-flex gap-2" style="border-radius:12px">
          <i class="bi bi-exclamation-triangle-fill mt-1"></i>
          <div>Erro ao conectar com o gateway. <a href="<?= APP_URL ?>/checkout/pagamento?id=<?= $licencaId ?>&h=<?= urlencode($token) ?>">Tentar novamente</a>.</div>
        </div>
        <?php endif; ?>

        <?php if ($gateway === 'dev'): ?>
        <!-- ======= MODO DEV ======= -->
        <div class="pay-card">
          <div class="pay-card-header">
            <div class="pay-badge dev"><i class="bi bi-tools me-1"></i>Modo desenvolvimento</div>
            <p class="text-muted small mt-2 mb-0">Nenhum gateway configurado. Clique abaixo para simular o pagamento.</p>
          </div>
          <div class="pay-review">
            <div class="pr-row"><span>E-mail</span><strong><?= htmlspecialchars($licenca['email'] ?? '—') ?></strong></div>
            <div class="pr-row"><span>Plano</span><strong><?= htmlspecialchars($descricao) ?></strong></div>
            <div class="pr-row total"><span>Total</span><strong><?= $valorFmt ?></strong></div>
          </div>
          <form method="POST" action="<?= APP_URL ?>/checkout/pagar">
            <input type="hidden" name="licenca_id" value="<?= $licencaId ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <button type="submit" class="btn-pay">
              <i class="bi bi-lightning-fill me-2"></i>Ativar licença (DEV) — <?= $valorFmt ?>
            </button>
          </form>
        </div>

        <?php elseif ($gateway === 'mercadopago' && $mpPublicKey): ?>
        <!-- ======= MERCADO PAGO TRANSPARENT (BRICKS) ======= -->
        <div class="pay-card">
          <div class="pay-card-header">
            <div class="pay-badge mp">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="me-1"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z"/></svg>
              Mercado Pago · Checkout Transparente
            </div>
            <p class="text-muted small mt-2 mb-0">Pague com cartão, Pix ou boleto sem sair desta página.</p>
          </div>

          <!-- Loader enquanto o Brick inicializa -->
          <div id="brickLoader" class="brick-loader">
            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
            Carregando formulário de pagamento...
          </div>

          <!-- Container do Brick -->
          <div id="paymentBrick_container"></div>

          <!-- Mensagem de erro do brick -->
          <div id="brickErro" class="alert alert-danger mt-3 d-none" style="border-radius:10px"></div>
        </div>

        <!-- ======= OVERLAY PIX QR CODE (aparece após escolher Pix no Brick) ======= -->
        <div id="pixOverlay" class="d-none">
          <div class="pay-card">
            <div class="pay-card-header">
              <div class="pay-badge pix"><i class="bi bi-qr-code me-1"></i>Pix · Mercado Pago</div>
              <p class="text-muted small mt-2 mb-0">Escaneie o QR Code com o app do seu banco. A licença é liberada <strong>automaticamente</strong> após a confirmação.</p>
            </div>

            <!-- QR Code image -->
            <div class="qr-box mt-3">
              <img id="pixQrImg" src="" alt="QR Code Pix" class="qr-img">
              <div id="pixPaid" class="qr-paid d-none">
                <i class="bi bi-check-circle-fill"></i>
                <div>Pagamento confirmado!</div>
              </div>
            </div>

            <!-- Código copia e cola -->
            <div class="input-group mt-3 mb-2">
              <input type="text" id="pixQrCode" class="form-control form-control-sm font-monospace"
                     placeholder="Código Pix..." readonly style="font-size:.68rem">
              <button class="btn btn-outline-secondary btn-sm" onclick="copiarPixMp()">
                <i class="bi bi-clipboard" id="icoPixMp"></i>
              </button>
            </div>

            <!-- Countdown e status -->
            <div class="d-flex align-items-center justify-content-between mb-3" style="font-size:.8rem">
              <div class="text-muted"><i class="bi bi-clock me-1"></i>Expira em <span id="pixCountdown" class="fw-semibold">30:00</span></div>
              <div id="pixPollStatus" class="text-muted">
                <span class="spinner-border spinner-border-sm me-1"></span>Aguardando pagamento...
              </div>
            </div>

            <button class="btn btn-outline-secondary btn-sm w-100" onclick="voltarBrick()">
              <i class="bi bi-arrow-left me-1"></i>Escolher outro método
            </button>
          </div>
        </div>

        <?php elseif ($gateway === 'mercadopago' && !$mpPublicKey): ?>
        <!-- MP sem Public Key -->
        <div class="pay-card">
          <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle me-2"></i>
            A <strong>Public Key</strong> do Mercado Pago não está configurada.
            <a href="<?= APP_URL ?>/admin/configuracoes" class="alert-link">Configurar agora</a>.
          </div>
        </div>

        <?php elseif ($gateway === 'pagarme' && $temPix): ?>
        <!-- ======= PAGAR.ME PIX ======= -->
        <div class="pay-card">
          <div class="pay-card-header">
            <div class="pay-badge pix"><i class="bi bi-qr-code me-1"></i>Pix · Pagar.me</div>
            <p class="text-muted small mt-2 mb-0">Escaneie o QR Code ou copie o código. Confirmação automática.</p>
          </div>

          <div class="qr-box">
            <?php if (!empty($pixData['qr_code_url'])): ?>
            <img src="<?= htmlspecialchars($pixData['qr_code_url']) ?>" alt="QR Code Pix" class="qr-img">
            <?php else: ?>
            <div class="qr-ph"><i class="bi bi-qr-code"></i></div>
            <?php endif; ?>
            <div id="qrPaid" class="qr-paid d-none">
              <i class="bi bi-check-circle-fill"></i>
              <div>Pagamento confirmado!</div>
            </div>
          </div>

          <div class="input-group mt-3 mb-2">
            <input type="text" id="pixCode" class="form-control form-control-sm font-monospace"
                   value="<?= htmlspecialchars($pixData['qr_code'] ?? '') ?>" readonly style="font-size:.7rem">
            <button class="btn btn-outline-secondary btn-sm" onclick="copiarPix()">
              <i class="bi bi-clipboard" id="icoPix"></i>
            </button>
          </div>

          <?php if (!empty($pixData['expires_at'])): ?>
          <div class="text-center text-muted" style="font-size:.8rem">
            <i class="bi bi-clock me-1"></i>Expira em <span id="countdown" class="fw-semibold">60:00</span>
          </div>
          <?php endif; ?>

          <div id="pixWait" class="pix-wait">
            <div class="spinner-border spinner-border-sm"></div>
            Aguardando confirmação do pagamento...
          </div>
        </div>

        <?php else: ?>
        <div class="pay-card">
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Não foi possível carregar o pagamento. <a href="<?= APP_URL ?>/checkout/pagamento?id=<?= $licencaId ?>&h=<?= urlencode($token) ?>">Tentar novamente</a>.
          </div>
        </div>
        <?php endif; ?>

      </div><!-- col-right -->
    </div><!-- grid -->
  </div><!-- body -->
</div><!-- wrap -->

<style>
/* ===== Layout geral ===== */
.pg-wrap { min-height:100vh; display:flex; flex-direction:column; }

.pg-header {
  background:rgba(255,255,255,.06);
  backdrop-filter:blur(12px);
  border-bottom:1px solid rgba(255,255,255,.1);
  padding:14px 32px;
  display:flex; align-items:center; justify-content:space-between;
}
.pg-brand { color:#fff; font-size:1.3rem; font-weight:800; letter-spacing:-.02em; }
.pg-brand span { color:#00d67a; }

/* Steps */
.pg-steps { display:flex; align-items:center; gap:0; }
.ps { display:flex; flex-direction:column; align-items:center; gap:4px; font-size:.72rem; color:rgba(255,255,255,.4); }
.ps-dot { width:28px; height:28px; border-radius:50%; background:rgba(255,255,255,.1); border:2px solid rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; }
.ps.active .ps-dot { background:#3b82f6; border-color:#3b82f6; color:#fff; box-shadow:0 0 0 4px rgba(59,130,246,.2); }
.ps.active { color:#fff; }
.ps.done .ps-dot { background:#10b981; border-color:#10b981; color:#fff; }
.ps.done { color:rgba(255,255,255,.6); }
.ps-line { width:48px; height:2px; background:rgba(255,255,255,.12); margin:0 6px 16px; }
.ps-line.done { background:#10b981; }

/* Body */
.pg-body { flex:1; display:flex; align-items:flex-start; justify-content:center; padding:32px 20px; }
.pg-grid { display:grid; grid-template-columns:380px 1fr; gap:28px; width:100%; max-width:900px; }
@media(max-width:768px){ .pg-grid{ grid-template-columns:1fr; } }

/* ===== Summary card ===== */
.summary-card {
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.1);
  border-radius:16px; padding:24px;
  color:#fff; backdrop-filter:blur(8px);
}
.summary-product { display:flex; align-items:center; gap:14px; margin-bottom:20px; }
.summary-icon {
  width:44px; height:44px; border-radius:12px; flex-shrink:0;
  background:linear-gradient(135deg,#3b82f6,#7c3aed);
  display:flex; align-items:center; justify-content:center; font-size:1.3rem; color:#fff;
}
.summary-benefits { list-style:none; padding:0; margin:0 0 20px; }
.summary-benefits li { display:flex; align-items:center; gap:8px; padding:5px 0; font-size:.85rem; color:rgba(255,255,255,.8); }
.summary-benefits li i { color:#10b981; font-size:.9rem; flex-shrink:0; }
.summary-device {
  display:flex; align-items:center; gap:10px;
  background:rgba(255,255,255,.05); border-radius:10px;
  padding:10px 14px; margin-bottom:20px;
  border:1px solid rgba(255,255,255,.08); font-size:.82rem;
}
.summary-device code { font-size:.75rem; color:rgba(255,255,255,.5); display:block; }
.summary-total {
  display:flex; justify-content:space-between; align-items:center;
  border-top:1px solid rgba(255,255,255,.1); padding-top:16px; margin-bottom:16px;
}
.summary-price { font-size:1.6rem; font-weight:800; color:#fff; line-height:1; }
.summary-secure { font-size:.75rem; color:rgba(255,255,255,.45); display:flex; align-items:center; gap:6px; }
.summary-secure i { color:#10b981; }
.back-link { display:block; text-align:center; margin-top:14px; font-size:.8rem; color:rgba(255,255,255,.45); text-decoration:none; }
.back-link:hover { color:rgba(255,255,255,.7); }

/* ===== Payment card ===== */
.pay-card { background:#fff; border-radius:16px; padding:28px; box-shadow:0 8px 48px rgba(0,0,0,.3); }
.pay-card-header { margin-bottom:20px; }
.pay-badge {
  display:inline-flex; align-items:center; border-radius:8px;
  padding:5px 12px; font-size:.8rem; font-weight:700;
}
.pay-badge.dev { background:#fef3c7; color:#92400e; }
.pay-badge.mp  { background:#e8f4fd; color:#005cb2; }
.pay-badge.pix { background:#e6faf5; color:#065f46; }

/* Review */
.pay-review { background:#f8fafc; border-radius:10px; padding:4px 16px; margin-bottom:20px; border:1px solid #e5e7eb; }
.pr-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f0f0; font-size:.9rem; color:#374151; }
.pr-row:last-child { border:none; }
.pr-row.total { font-size:1rem; }
.pr-row.total strong { font-size:1.1rem; }

/* Pay button */
.btn-pay {
  width:100%; padding:14px; border:none; border-radius:12px;
  background:linear-gradient(135deg,#3b82f6,#7c3aed);
  color:#fff; font-size:1rem; font-weight:700; cursor:pointer;
  transition:opacity .2s,transform .1s;
}
.btn-pay:hover { opacity:.9; }
.btn-pay:active { transform:scale(.98); }

/* Brick loader */
.brick-loader { display:flex; align-items:center; color:#6b7280; font-size:.85rem; padding:20px 0; }

/* Pix */
.qr-box { position:relative; display:flex; justify-content:center; background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:20px; }
.qr-img { width:180px; height:180px; display:block; }
.qr-ph { width:180px; height:180px; display:flex; align-items:center; justify-content:center; font-size:5rem; color:#d1d5db; }
.qr-paid { position:absolute; inset:0; border-radius:12px; background:rgba(0,0,0,.6); backdrop-filter:blur(3px); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; color:#fff; font-weight:700; font-size:1rem; }
.qr-paid i { font-size:2.5rem; color:#10b981; }
.pix-wait { display:flex; align-items:center; gap:8px; color:#6b7280; font-size:.82rem; margin-top:12px; justify-content:center; }
</style>

<script>
const STATUS_URL   = <?= json_encode($statusUrl) ?>;
const PROCESSAR_URL = <?= json_encode($processarUrl) ?>;
const SUCESSO_URL  = <?= json_encode($sucessoUrl) ?>;
const TEM_PIX      = <?= $temPix ? 'true' : 'false' ?>;
const IS_MP_BRICKS = <?= ($gateway === 'mercadopago' && $mpPublicKey) ? 'true' : 'false' ?>;

// ===== PIX polling =====
let countdownTimer = null;
if (TEM_PIX) {
  const poll = setInterval(async () => {
    try {
      const d = await (await fetch(STATUS_URL)).json();
      if (d.pago || d.status === 'ativa') {
        clearInterval(poll);
        if (countdownTimer) clearInterval(countdownTimer);
        document.getElementById('qrPaid').classList.remove('d-none');
        document.getElementById('pixWait').innerHTML = '<i class="bi bi-check-circle-fill text-success me-2"></i><span class="text-success fw-semibold">Confirmado! Redirecionando...</span>';
        setTimeout(() => window.location.href = SUCESSO_URL, 2000);
      }
    } catch(_) {}
  }, 4000);

  <?php if (!empty($pixData['expires_at'])): ?>
  let totalSec = 3600;
  const timerEl = document.getElementById('countdown');
  countdownTimer = setInterval(() => {
    if (--totalSec <= 0) { clearInterval(countdownTimer); timerEl.textContent = 'Expirado'; return; }
    timerEl.textContent = String(Math.floor(totalSec/60)).padStart(2,'0') + ':' + String(totalSec%60).padStart(2,'0');
  }, 1000);
  <?php endif; ?>
}

// ===== PIX QR CODE via Mercado Pago =====
let pixPollInterval = null;
let pixCountdownInterval = null;

function mostrarPixQr(data) {
  // Esconde o Brick, mostra overlay do Pix
  document.querySelector('.pay-card').style.display = 'none';
  const overlay = document.getElementById('pixOverlay');
  overlay.classList.remove('d-none');

  // QR Code image — usa base64 se disponível, senão usa URL gerada pelo texto
  const img = document.getElementById('pixQrImg');
  if (data.qr_code_base64) {
    img.src = 'data:image/png;base64,' + data.qr_code_base64;
  } else {
    img.style.display = 'none';
  }

  // Código copia e cola
  if (data.qr_code) {
    document.getElementById('pixQrCode').value = data.qr_code;
  }

  // Countdown 30 minutos (1800s)
  let secs = 1800;
  const cdEl = document.getElementById('pixCountdown');
  pixCountdownInterval = setInterval(() => {
    if (--secs <= 0) { clearInterval(pixCountdownInterval); cdEl.textContent = 'Expirado'; return; }
    cdEl.textContent = String(Math.floor(secs/60)).padStart(2,'0') + ':' + String(secs%60).padStart(2,'0');
  }, 1000);

  // Polling a cada 4s para verificar se a licença foi ativada
  pixPollInterval = setInterval(async () => {
    try {
      const r = await fetch(STATUS_URL);
      const d = await r.json();
      if (d.pago || d.status === 'ativa') {
        clearInterval(pixPollInterval);
        clearInterval(pixCountdownInterval);
        document.getElementById('pixPaid').classList.remove('d-none');
        document.getElementById('pixPollStatus').innerHTML =
          '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">Pago! Redirecionando...</span>';
        setTimeout(() => window.location.href = SUCESSO_URL, 2000);
      }
    } catch(_) {}
  }, 4000);
}

function voltarBrick() {
  clearInterval(pixPollInterval);
  clearInterval(pixCountdownInterval);
  document.getElementById('pixOverlay').classList.add('d-none');
  document.querySelector('.pay-card').style.display = '';
}

function copiarPixMp() {
  navigator.clipboard.writeText(document.getElementById('pixQrCode').value).then(() => {
    const ic = document.getElementById('icoPixMp');
    ic.className = 'bi bi-check-lg text-success';
    setTimeout(() => ic.className = 'bi bi-clipboard', 2000);
  });
}

function copiarPix() {
  navigator.clipboard.writeText(document.getElementById('pixCode').value).then(() => {
    const ic = document.getElementById('icoPix');
    ic.className = 'bi bi-check-lg text-success';
    setTimeout(() => ic.className = 'bi bi-clipboard', 2000);
  });
}

// ===== MERCADO PAGO BRICKS =====
<?php if ($gateway === 'mercadopago' && $mpPublicKey): ?>
(async function initBricks() {
  const mp = new MercadoPago(<?= json_encode($mpPublicKey) ?>, { locale: 'pt-BR' });
  const builder = mp.bricks();

  const settings = {
    initialization: {
      amount: <?= json_encode((float)$valor) ?>,
    },
    customization: {
      visual: {
        hideFormTitle: true,
        style: { theme: 'default' },
      },
      paymentMethods: {
        creditCard: 'all',
        debitCard:  'all',
        ticket:     'all',
        bankTransfer: 'all',
        mercadoPago: 'all',
        atm: 'none',
      },
    },
    callbacks: {
      onReady: () => {
        const loader = document.getElementById('brickLoader');
        if (loader) loader.style.display = 'none';
      },
      onSubmit: ({ selectedPaymentMethod, formData }) => {
        return new Promise((resolve, reject) => {
          fetch(PROCESSAR_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              licenca_id:     <?= $licencaId ?>,
              checkout_token: <?= json_encode($token) ?>,
              ...formData,
            }),
          })
          .then(r => r.json())
          .then(data => {
            // ── Pix pendente: mostra QR code inline ──
            if (data.status === 'pending' && (data.qr_code || data.qr_code_base64)) {
              resolve(); // libera o Brick (sem erro)
              mostrarPixQr(data);
              return;
            }
            // ── Aprovado (cartão/débito): vai para sucesso ──
            if (data.status === 'approved') {
              resolve();
              window.location.href = SUCESSO_URL;
              return;
            }
            // ── Rejeitado / erro ──
            const erroEl = document.getElementById('brickErro');
            erroEl.textContent = data.mensagem || 'Pagamento recusado. Tente outro método de pagamento.';
            erroEl.classList.remove('d-none');
            reject(new Error(data.mensagem));
          })
          .catch(err => {
            document.getElementById('brickErro').textContent = 'Erro de conexão. Tente novamente.';
            document.getElementById('brickErro').classList.remove('d-none');
            reject(err);
          });
        });
      },
      onError: (error) => {
        console.error('[MP Bricks]', error);
      },
    },
  };

  try {
    await builder.create('payment', 'paymentBrick_container', settings);
  } catch(e) {
    console.error('[MP Bricks init]', e);
    document.getElementById('brickLoader').innerHTML = '<span class="text-danger">Erro ao carregar o formulário de pagamento.</span>';
  }
})();
<?php endif; ?>
</script>
