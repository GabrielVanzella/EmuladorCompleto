<div class="checkout-card text-center">
  <div class="checkout-card-body" style="padding:40px 28px">
    <div class="mb-4">
      <div style="width:72px;height:72px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
        <i class="bi bi-x-lg text-danger" style="font-size:2rem"></i>
      </div>
    </div>
    <h4 class="fw-bold mb-2">Pagamento não concluído</h4>
    <p class="text-muted mb-4">O pagamento foi cancelado ou não foi aprovado. Você pode tentar novamente.</p>
    <a href="<?= APP_URL ?>/checkout?device_id=<?= urlencode($_GET['device_id'] ?? '') ?>&device_nome=<?= urlencode($_GET['device_nome'] ?? '') ?>"
       class="btn-pay" style="display:inline-block;text-decoration:none;padding:12px 32px;border-radius:12px">
      Tentar novamente
    </a>
  </div>
</div>
