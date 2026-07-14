<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?= APP_URL ?>/admin/empresas" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><?= htmlspecialchars($empresa['nome']) ?></h4>
  <a href="<?= APP_URL ?>/admin/empresas/<?= $empresa['id'] ?>/editar" class="btn btn-sm btn-outline-primary ms-auto">
    <i class="bi bi-pencil me-1"></i>Editar
  </a>
</div>

<div class="row g-3 mb-4">
  <!-- Info da empresa -->
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Dados da Empresa</h6>
        <dl class="mb-0" style="font-size:.88rem">
          <dt class="text-muted">CNPJ</dt>
          <dd><?= htmlspecialchars($empresa['cnpj'] ?? '—') ?></dd>
          <dt class="text-muted">E-mail</dt>
          <dd><?= htmlspecialchars($empresa['email']) ?></dd>
          <dt class="text-muted">Telefone</dt>
          <dd><?= htmlspecialchars($empresa['telefone'] ?? '—') ?></dd>
          <dt class="text-muted">Contato</dt>
          <dd><?= htmlspecialchars($empresa['contato'] ?? '—') ?></dd>
          <dt class="text-muted">Status</dt>
          <dd><?= $empresa['ativo'] ? '<span class="badge bg-success">Ativa</span>' : '<span class="badge bg-secondary">Inativa</span>' ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <!-- Usuários de acesso -->
  <div class="col-md-8">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-people me-2"></i>Usuários de Acesso</h6>
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr><th>Nome</th><th>E-mail</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['nome']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= $u['ativo'] ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Link de pagamento para o cliente -->
<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-link-45deg me-2"></i>Link de pagamento</h6>
      <span class="badge bg-success">Enviar ao cliente</span>
    </div>
    <p class="text-muted small mb-3">
      Envie este link para o cliente comprar ou renovar uma licença. O formulário já vem pré-preenchido com os dados da empresa.
    </p>
    <?php
      $linkPagamento = APP_URL . '/checkout?empresa_id=' . $empresa['id']
        . '&email=' . urlencode($empresa['email']);
    ?>
    <div class="input-group mb-2">
      <input type="text" id="inputLinkPagamento" class="form-control form-control-sm font-monospace"
             value="<?= htmlspecialchars($linkPagamento) ?>" readonly>
      <button class="btn btn-sm btn-outline-secondary" onclick="copiarLink()" title="Copiar">
        <i class="bi bi-clipboard" id="iconeCopiar"></i>
      </button>
    </div>
    <div class="d-flex gap-2">
      <a href="https://wa.me/?text=<?= urlencode('Olá! Segue o link para ativar sua licença ScanTE: ' . $linkPagamento) ?>"
         target="_blank" class="btn btn-sm btn-success">
        <i class="bi bi-whatsapp me-1"></i>Enviar por WhatsApp
      </a>
      <a href="<?= htmlspecialchars($linkPagamento) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Abrir link
      </a>
    </div>
  </div>
</div>

<!-- Licenças da empresa -->
<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h6 class="fw-bold mb-0"><i class="bi bi-key me-2"></i>Licenças</h6>
      <button class="btn btn-sm btn-accent" data-bs-toggle="modal" data-bs-target="#modalGerar">
        <i class="bi bi-plus-lg me-1"></i>Gerar Licença
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Chave</th><th>Tipo</th><th>Status</th><th>Dispositivo</th><th>Expira</th><th>Último acesso</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($licencas as $l): ?>
          <tr>
            <td><code><?= $l['chave'] ?></code></td>
            <td><?= ucfirst($l['tipo']) ?></td>
            <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
            <td><?= $l['device_id'] ? '<i class="bi bi-phone-fill text-success"></i> ' . htmlspecialchars($l['device_nome'] ?? $l['device_id']) : '<span class="text-muted">—</span>' ?></td>
            <td><?= $l['expira_em'] ? date('d/m/Y', strtotime($l['expira_em'])) : '∞' ?></td>
            <td><?= $l['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($l['ultimo_acesso'])) : '—' ?></td>
            <td><a href="<?= APP_URL ?>/admin/licencas/<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($licencas)): ?>
          <tr><td colspan="7" class="text-center text-muted py-3">Nenhuma licença.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Gerar Licença -->
<div class="modal fade" id="modalGerar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/empresas/<?= $empresa['id'] ?>/gerar-licencas">
        <div class="modal-header">
          <h5 class="modal-title">Gerar Licenças — <?= htmlspecialchars($empresa['nome']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Tipo</label>
              <select name="tipo" id="tipoLicenca" class="form-select">
                <option value="trial">Trial</option>
                <option value="mensal">Mensal</option>
                <option value="anual">Anual</option>
                <option value="vitalicia">Vitalícia</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Quantidade</label>
              <input type="number" name="quantidade" class="form-control" value="1" min="1" max="50">
            </div>
            <div id="wrapperDias" class="col-12">
              <label class="form-label">Dias de validade</label>
              <input type="number" name="dias" class="form-control" value="30" min="1">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-accent">Gerar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('tipoLicenca').addEventListener('change', function () {
  document.getElementById('wrapperDias').style.display = this.value === 'vitalicia' ? 'none' : '';
});

function copiarLink() {
  const input = document.getElementById('inputLinkPagamento');
  navigator.clipboard.writeText(input.value).then(() => {
    const icon = document.getElementById('iconeCopiar');
    icon.className = 'bi bi-check-lg text-success';
    setTimeout(() => icon.className = 'bi bi-clipboard', 2000);
  });
}
</script>
