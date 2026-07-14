<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<h4 class="fw-bold mb-4">Olá, <?= htmlspecialchars($empresa['nome']) ?> 👋</h4>

<!-- Cards resumo -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.8rem;font-weight:700;color:#0F2A3D"><?= count($licencas) ?></div>
      <div class="text-muted" style="font-size:.82rem">Total</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.8rem;font-weight:700;color:#065f46"><?= $ativas ?></div>
      <div class="text-muted" style="font-size:.82rem">Ativas</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.8rem;font-weight:700;color:#92400e"><?= $trial ?></div>
      <div class="text-muted" style="font-size:.82rem">Trial</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center p-3">
      <div style="font-size:1.8rem;font-weight:700;color:#991b1b"><?= $expiradas ?></div>
      <div class="text-muted" style="font-size:.82rem">Expiradas</div>
    </div>
  </div>
</div>

<!-- Lista rápida -->
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="fw-bold mb-0">Suas licenças</h6>
      <a href="<?= APP_URL ?>/empresa/licencas" class="btn btn-sm btn-outline-secondary">Ver todas</a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Chave</th><th>Status</th><th>Dispositivo</th><th>Expira</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach (array_slice($licencas, 0, 5) as $l): ?>
          <tr>
            <td><code><?= $l['chave'] ?></code></td>
            <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
            <td>
              <?= $l['device_id']
                ? '<i class="bi bi-phone-fill text-success"></i> ' . htmlspecialchars($l['device_nome'] ?? 'Vinculado')
                : '<span class="text-muted">Livre</span>' ?>
            </td>
            <td><?= $l['expira_em'] ? date('d/m/Y', strtotime($l['expira_em'])) : '∞' ?></td>
            <td><a href="<?= APP_URL ?>/empresa/licencas/<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
