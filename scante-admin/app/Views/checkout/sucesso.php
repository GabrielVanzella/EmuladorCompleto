<div class="checkout-card text-center">
  <div class="checkout-card-body" style="padding:40px 28px">

    <?php if ($aguardandoPagamento ?? false): ?>
    <!-- Aguardando confirmação do pagamento (ex: PIX) -->
    <div class="mb-4">
      <div style="width:72px;height:72px;background:#fef9c3;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
        <i class="bi bi-hourglass-split text-warning" style="font-size:2rem"></i>
      </div>
    </div>
    <h4 class="fw-bold mb-2">Aguardando confirmação…</h4>
    <p class="text-muted mb-4">Assim que o pagamento for confirmado, nossa equipe vai revisar e liberar suas licenças.</p>
    <div id="statusPendente" class="badge-device mb-4">
      <div class="spinner-border spinner-border-sm text-warning me-2" role="status"></div>
      <span>Verificando pagamento…</span>
    </div>
    <script>
    (function poll() {
      const id  = <?= (int)($licencaId ?? 0) ?>;
      const h   = <?= json_encode($token ?? '') ?>;
      if (!id) return;
      fetch('/checkout/status?id=' + id + '&h=' + h)
        .then(r => r.json())
        .then(data => {
          if (data.pago || data.status === 'ativa') {
            location.reload();
          } else {
            setTimeout(poll, 3000);
          }
        })
        .catch(() => setTimeout(poll, 5000));
    })();
    </script>

    <?php elseif (!empty($chaves)): ?>
    <!-- Aprovado pela equipe: licença(s) ativa(s) -->
    <div class="mb-4">
      <div style="width:72px;height:72px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
        <i class="bi bi-check-lg text-success" style="font-size:2rem"></i>
      </div>
    </div>
    <h4 class="fw-bold mb-2">Pagamento aprovado!</h4>
    <p class="text-muted mb-3">
      <?= count($chaves) > 1 ? count($chaves) . ' licenças foram ativadas' : 'Sua licença foi ativada' ?> com sucesso.
    </p>

    <p class="text-muted" style="font-size:.82rem">
      Copie a(s) chave(s) abaixo e use a opção <strong>"Clique Aqui para Ativar"</strong> no ScanTE
      (uma chave por dispositivo):
    </p>

    <?php foreach ($chaves as $i => $c): ?>
    <div class="input-group mb-2" style="max-width:360px;margin:0 auto">
      <input type="text" class="form-control text-center fw-bold font-monospace chave-input"
             value="<?= htmlspecialchars($c) ?>" readonly>
      <button class="btn btn-outline-secondary btn-copiar" type="button" data-idx="<?= $i ?>">
        <i class="bi bi-clipboard"></i>
      </button>
    </div>
    <?php endforeach; ?>

    <div class="security-note mt-3">
      Guarde essas chaves em local seguro.<br>
      Em caso de dúvidas, entre em contato com o suporte.
    </div>

    <script>
    document.querySelectorAll('.btn-copiar').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const input = btn.closest('.input-group').querySelector('.chave-input');
        navigator.clipboard?.writeText(input.value).catch(() => {
          input.select(); document.execCommand('copy');
        });
        const icon = btn.querySelector('i');
        icon.className = 'bi bi-check2 text-success';
        setTimeout(() => icon.className = 'bi bi-clipboard', 1500);
      });
    });
    </script>

    <?php elseif ($aguardandoAprovacao ?? false): ?>
    <!-- Pagamento confirmado, mas ainda aguardando aprovação manual no admin -->
    <div class="mb-4">
      <div style="width:72px;height:72px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
        <i class="bi bi-hourglass-split text-primary" style="font-size:2rem"></i>
      </div>
    </div>
    <h4 class="fw-bold mb-2">Pagamento confirmado!</h4>
    <p class="text-muted mb-4">Sua compra está sendo revisada pela nossa equipe. Assim que aprovada, você poderá ativar suas licenças.</p>
    <div id="statusAprovacao" class="badge-device mb-4">
      <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
      <span>Aguardando aprovação…</span>
    </div>
    <div class="security-note">
      Isso costuma ser rápido. Mantenha esta página aberta — assim que aprovado, suas chaves aparecem aqui automaticamente.
    </div>
    <script>
    (function poll() {
      const id = <?= (int)($licencaId ?? 0) ?>;
      const h  = <?= json_encode($token ?? '') ?>;
      if (!id) return;
      fetch('/checkout/status?id=' + id + '&h=' + h)
        .then(r => r.json())
        .then(data => {
          if (data.status === 'ativa') {
            location.reload();
          } else {
            setTimeout(poll, 5000);
          }
        })
        .catch(() => setTimeout(poll, 8000));
    })();
    </script>

    <?php else: ?>
    <!-- Sucesso genérico (sem chave disponível) -->
    <div class="mb-4">
      <div style="width:72px;height:72px;background:#d1fae5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
        <i class="bi bi-check-lg text-success" style="font-size:2rem"></i>
      </div>
    </div>
    <h4 class="fw-bold mb-2">Pagamento aprovado!</h4>
    <p class="text-muted mb-4">Seu pedido foi processado com sucesso.</p>
    <div class="security-note mb-4">
      Em caso de dúvidas, entre em contato com o suporte.
    </div>
    <?php endif; ?>

  </div>
</div>
