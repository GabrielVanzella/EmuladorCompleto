<div class="wizard-card">

  <!-- ===== Barra de progresso ===== -->
  <div class="wz-progress">
    <div class="wz-step-dot active" data-dot="1"><span>1</span><label>Pacote</label></div>
    <div class="wz-line" data-line="1"></div>
    <div class="wz-step-dot" data-dot="2"><span>2</span><label>Empresa</label></div>
    <div class="wz-line" data-line="2"></div>
    <div class="wz-step-dot" data-dot="3"><span>3</span><label>Contato</label></div>
    <div class="wz-line" data-line="3"></div>
    <div class="wz-step-dot" data-dot="4"><span>4</span><label>Confirmar</label></div>
  </div>

  <?php if ($erro): ?>
  <div class="alert alert-danger alert-sm py-2 mb-3" style="font-size:.85rem">
    <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($erro) ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/checkout" id="formCheckout" novalidate>

    <!-- ============================================================ -->
    <!-- STEP 1 — Pacote (quantidade + anos de suporte)                -->
    <!-- ============================================================ -->
    <section class="wz-step" data-step="1">
      <h4 class="wz-title">Quantas licenças você precisa?</h4>
      <p class="wz-subtitle">Escolha a quantidade de dispositivos que vão usar o ScanTE.</p>

      <div class="qty-stepper">
        <button type="button" class="qty-btn" id="btnQtyMinus" aria-label="Diminuir">–</button>
        <input type="number" name="quantidade" id="inputQuantidade" min="1" max="200" value="1" inputmode="numeric">
        <button type="button" class="qty-btn" id="btnQtyPlus" aria-label="Aumentar">+</button>
      </div>
      <div class="qty-label">licença<span id="qtyPlural">s</span></div>

      <h4 class="wz-title mt-4">Por quantos anos de suporte?</h4>
      <p class="wz-subtitle">O suporte cobre atualizações e ativação da licença durante o período.</p>

      <div class="anos-grid" id="anosGrid">
        <?php for ($a = 1; $a <= 5; $a++): ?>
        <label class="anos-card <?= $a === 1 ? 'selected' : '' ?>">
          <input type="radio" name="anos_suporte" value="<?= $a ?>" <?= $a === 1 ? 'checked' : '' ?>>
          <div class="anos-num"><?= $a ?></div>
          <div class="anos-txt">ano<?= $a > 1 ? 's' : '' ?> de<br>suporte</div>
        </label>
        <?php endfor; ?>
      </div>

      <div class="total-box">
        <div class="text-muted small">Total do pacote</div>
        <div class="total-price" id="valorExibido">—</div>
        <div class="text-muted" style="font-size:.72rem">R$ <?= number_format($precoAno, 2, ',', '.') ?> por ano de suporte, por licença</div>
      </div>

      <button type="button" class="btn-pay wz-next" data-goto="2">
        Continuar <i class="bi bi-arrow-right ms-2"></i>
      </button>
    </section>

    <!-- ============================================================ -->
    <!-- STEP 2 — Empresa                                              -->
    <!-- ============================================================ -->
    <section class="wz-step d-none" data-step="2">
      <h4 class="wz-title">Qual empresa vai usar essas licenças?</h4>
      <p class="wz-subtitle">Usamos o CNPJ pra identificar sua empresa e evitar cadastro duplicado.</p>

      <div class="mb-3">
        <label class="form-label">Nome da empresa</label>
        <input type="text" name="nova_empresa_nome" id="novaEmpresaNome" class="form-control form-control-lg"
               placeholder="Ex: Empresa ABC Ltda" autocomplete="organization">
      </div>
      <div class="mb-3">
        <label class="form-label">CNPJ</label>
        <input type="text" name="nova_empresa_cnpj" id="novaEmpresaCnpj" class="form-control form-control-lg"
               placeholder="00.000.000/0000-00" inputmode="numeric" maxlength="18">
        <div class="invalid-feedback-wz" id="erroCnpj">Digite um CNPJ válido (14 dígitos).</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Responsável <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="text" name="nova_empresa_contato" class="form-control form-control-lg" placeholder="Nome do responsável" autocomplete="name">
      </div>

      <div class="wz-nav">
        <button type="button" class="btn-back wz-back" data-goto="1"><i class="bi bi-arrow-left me-2"></i>Voltar</button>
        <button type="button" class="btn-pay wz-next" data-goto="3">Continuar <i class="bi bi-arrow-right ms-2"></i></button>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- STEP 3 — Contato                                              -->
    <!-- ============================================================ -->
    <section class="wz-step d-none" data-step="3">
      <h4 class="wz-title">Como podemos falar com você?</h4>
      <p class="wz-subtitle">Vamos usar isso para confirmar o pagamento e liberar as chaves.</p>

      <div class="mb-3">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" id="inputEmail" class="form-control form-control-lg" placeholder="seu@email.com">
        <div class="invalid-feedback-wz" id="erroEmail">Digite um e-mail válido.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Telefone / WhatsApp <span class="text-muted fw-normal">(opcional)</span></label>
        <input type="tel" name="telefone" class="form-control form-control-lg" placeholder="(11) 99999-9999">
      </div>

      <div class="wz-nav">
        <button type="button" class="btn-back wz-back" data-goto="2"><i class="bi bi-arrow-left me-2"></i>Voltar</button>
        <button type="button" class="btn-pay wz-next" data-goto="4">Continuar <i class="bi bi-arrow-right ms-2"></i></button>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- STEP 4 — Confirmação                                          -->
    <!-- ============================================================ -->
    <section class="wz-step d-none" data-step="4">
      <h4 class="wz-title">Confira e finalize</h4>
      <p class="wz-subtitle">Depois de confirmar, você vai para a página de pagamento.</p>

      <div class="resumo-card">
        <div class="resumo-row"><span>Licenças</span><strong id="rQtd">—</strong></div>
        <div class="resumo-row"><span>Suporte</span><strong id="rAnos">—</strong></div>
        <div class="resumo-row"><span>Empresa</span><strong id="rEmpresa">—</strong></div>
        <div class="resumo-row"><span>E-mail</span><strong id="rEmail">—</strong></div>
        <div class="resumo-row total"><span>Total</span><strong id="rTotal">—</strong></div>
      </div>

      <div class="wz-nav">
        <button type="button" class="btn-back wz-back" data-goto="3"><i class="bi bi-arrow-left me-2"></i>Voltar</button>
        <button type="submit" class="btn-pay">
          <i class="bi bi-lock-fill me-2"></i>Continuar para o pagamento
        </button>
      </div>
    </section>

  </form>

  <div class="divider">pagamento seguro</div>
  <div class="security-note text-center">
    <i class="bi bi-shield-fill-check text-success me-1"></i>
    Pagamento processado pelo <strong>Mercado Pago</strong>. Suas licenças são liberadas após a confirmação da nossa equipe.
  </div>
</div>

<style>
.wizard-card { background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,.3); overflow:hidden; padding:28px; }

/* Progresso */
.wz-progress { display:flex; align-items:flex-start; justify-content:center; margin-bottom:28px; }
.wz-step-dot { display:flex; flex-direction:column; align-items:center; gap:6px; width:64px; }
.wz-step-dot span { width:30px; height:30px; border-radius:50%; background:#eef2f4; color:#94a3b8; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; transition:all .25s; }
.wz-step-dot label { font-size:.68rem; color:#94a3b8; text-align:center; font-weight:600; }
.wz-step-dot.active span { background:var(--accent); color:var(--primary); }
.wz-step-dot.active label { color:var(--primary); }
.wz-step-dot.done span { background:var(--primary); color:#fff; }
.wz-line { flex:1; height:2px; background:#eef2f4; margin:15px -6px 0; max-width:60px; transition:background .25s; }
.wz-line.done { background:var(--primary); }

/* Steps */
.wz-step { animation:wzFade .3s ease; }
@keyframes wzFade { from{ opacity:0; transform:translateY(6px);} to{ opacity:1; transform:translateY(0);} }
.wz-title { font-weight:800; font-size:1.25rem; color:#0F2A3D; margin-bottom:4px; }
.wz-subtitle { color:#64748b; font-size:.87rem; margin-bottom:20px; }

/* Stepper de quantidade */
.qty-stepper { display:flex; align-items:center; justify-content:center; gap:18px; margin:8px 0 2px; }
.qty-btn { width:52px; height:52px; border-radius:50%; border:none; background:#f0fdf9; color:var(--primary); font-size:1.6rem; font-weight:700; cursor:pointer; transition:background .15s, transform .1s; }
.qty-btn:hover { background:var(--accent); }
.qty-btn:active { transform:scale(.92); }
#inputQuantidade { width:110px; text-align:center; font-size:2.2rem; font-weight:800; color:#0F2A3D; border:none; -moz-appearance:textfield; }
#inputQuantidade::-webkit-outer-spin-button, #inputQuantidade::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
.qty-label { text-align:center; color:#94a3b8; font-size:.82rem; margin-bottom:8px; }

/* Cards de anos */
.anos-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; margin-bottom:20px; }
.anos-card { position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; border:2px solid #e5e7eb; border-radius:14px; padding:14px 4px; cursor:pointer; transition:border-color .15s, box-shadow .15s; text-align:center; }
.anos-card input { position:absolute; opacity:0; pointer-events:none; }
.anos-card:hover { border-color:var(--accent); }
.anos-card.selected { border-color:var(--accent); box-shadow:0 0 0 3px rgba(0,214,122,.15); background:#f0fdf9; }
.anos-num { font-size:1.6rem; font-weight:800; color:#0F2A3D; }
.anos-txt { font-size:.66rem; color:#64748b; line-height:1.1; margin-top:2px; }
@media (max-width:420px) { .anos-grid { grid-template-columns:repeat(3,1fr); } }

/* Total */
.total-box { margin:8px 0 20px; padding:16px; border-radius:14px; background:#f0fdf9; border:1px solid #a7f3d0; text-align:center; }
.total-price { font-weight:800; font-size:1.7rem; color:#00b866; line-height:1.3; }

/* Navegação */
.wz-nav { display:flex; gap:10px; margin-top:8px; }
.wz-nav .btn-pay { flex:1; }
.btn-back { background:#f1f5f9; color:#374151; border:none; border-radius:12px; padding:14px 18px; font-weight:600; font-size:.92rem; }
.wz-next { margin-top:4px; }

.invalid-feedback-wz { display:none; color:#dc2626; font-size:.78rem; margin-top:4px; }
.invalid-feedback-wz.show { display:block; }

/* Resumo */
.resumo-card { background:#f8fafc; border:1px solid #e5e7eb; border-radius:14px; padding:6px 18px; margin-bottom:20px; }
.resumo-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eef2f4; font-size:.9rem; color:#374151; }
.resumo-row:last-child { border:none; }
.resumo-row.total { font-size:1.05rem; padding-top:14px; }
.resumo-row.total strong { color:#00b866; font-size:1.2rem; }
</style>

<script>
const PRECO_ANO = <?= json_encode($precoAno) ?>;

function formatBRL(v) {
  return 'R$ ' + v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ---------- Quantidade ----------
const inputQtd = document.getElementById('inputQuantidade');
function getQtd() { return Math.max(1, Math.min(200, parseInt(inputQtd.value, 10) || 1)); }
function setQtd(v) {
  inputQtd.value = Math.max(1, Math.min(200, v));
  document.getElementById('qtyPlural').style.display = getQtd() > 1 ? 'inline' : 'none';
  atualizarValor();
}
document.getElementById('btnQtyMinus').addEventListener('click', () => setQtd(getQtd() - 1));
document.getElementById('btnQtyPlus').addEventListener('click', () => setQtd(getQtd() + 1));
inputQtd.addEventListener('input', () => setQtd(getQtd()));

// ---------- Anos (cards) ----------
document.querySelectorAll('.anos-card').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('.anos-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    card.querySelector('input').checked = true;
    atualizarValor();
  });
});
function getAnos() { return parseInt(document.querySelector('.anos-card input:checked').value, 10); }

function atualizarValor() {
  document.getElementById('valorExibido').textContent = formatBRL(PRECO_ANO * getAnos() * getQtd());
}
atualizarValor();

// ---------- Máscara de CNPJ ----------
const inputCnpj = document.getElementById('novaEmpresaCnpj');
function maskCnpj(v) {
  v = v.replace(/\D/g, '').slice(0, 14);
  if (v.length > 12) v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
  else if (v.length > 8) v = v.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
  else if (v.length > 5) v = v.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
  else if (v.length > 2) v = v.replace(/(\d{2})(\d{1,3})/, '$1.$2');
  return v;
}
inputCnpj.addEventListener('input', () => { inputCnpj.value = maskCnpj(inputCnpj.value); });

// ---------- Navegação entre steps ----------
let stepAtual = 1;
function irPara(n) {
  document.querySelectorAll('.wz-step').forEach(s => s.classList.toggle('d-none', parseInt(s.dataset.step, 10) !== n));
  document.querySelectorAll('.wz-step-dot').forEach(d => {
    const num = parseInt(d.dataset.dot, 10);
    d.classList.toggle('active', num === n);
    d.classList.toggle('done', num < n);
  });
  document.querySelectorAll('.wz-line').forEach(l => {
    l.classList.toggle('done', parseInt(l.dataset.line, 10) < n);
  });
  if (n === 4) preencherResumo();
  stepAtual = n;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.querySelectorAll('.wz-next').forEach(btn => {
  btn.addEventListener('click', () => {
    if (!validarStep(stepAtual)) return;
    irPara(parseInt(btn.dataset.goto, 10));
  });
});
document.querySelectorAll('.wz-back').forEach(btn => {
  btn.addEventListener('click', () => irPara(parseInt(btn.dataset.goto, 10)));
});

function validarStep(n) {
  if (n === 2) {
    const nome = document.getElementById('novaEmpresaNome').value.trim();
    const cnpjDigits = inputCnpj.value.replace(/\D/g, '');
    const ok = nome.length > 0 && cnpjDigits.length === 14;
    document.getElementById('erroCnpj').classList.toggle('show', !ok);
    inputCnpj.classList.toggle('is-invalid', cnpjDigits.length > 0 && cnpjDigits.length !== 14);
    return ok;
  }
  if (n === 3) {
    const email = document.getElementById('inputEmail');
    const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());
    document.getElementById('erroEmail').classList.toggle('show', !ok);
    email.classList.toggle('is-invalid', !ok);
    return ok;
  }
  return true;
}

function preencherResumo() {
  const qtd  = getQtd();
  const anos = getAnos();
  document.getElementById('rQtd').textContent   = qtd + (qtd > 1 ? ' licenças' : ' licença');
  document.getElementById('rAnos').textContent  = anos + (anos > 1 ? ' anos' : ' ano');
  document.getElementById('rEmail').textContent = document.getElementById('inputEmail').value.trim();
  document.getElementById('rEmpresa').textContent = document.getElementById('novaEmpresaNome').value.trim() || 'não informada';
  document.getElementById('rTotal').textContent   = formatBRL(PRECO_ANO * anos * qtd);
}
</script>
