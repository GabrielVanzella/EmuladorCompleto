<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<h4 class="fw-bold mb-4">Minhas Licenças</h4>

<div class="row g-3">
<?php foreach ($licencas as $l): ?>
  <?php
    $diasRestantes = $l['expira_em'] ? ceil((strtotime($l['expira_em']) - time()) / 86400) : null;
    $expirando = $diasRestantes !== null && $diasRestantes <= 7 && $diasRestantes >= 0;
  ?>
  <div class="col-md-6 col-lg-4">
    <div class="card h-100 <?= $expirando ? 'border-warning' : '' ?>">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge badge-<?= $l['status'] ?> fs-6"><?= ucfirst($l['status']) ?></span>
          <span class="text-muted" style="font-size:.78rem"><?= ucfirst($l['tipo']) ?></span>
        </div>
        <code class="d-block mb-3"><?= $l['chave'] ?></code>

        <div class="mb-2" style="font-size:.85rem">
          <i class="bi bi-phone me-1"></i>
          <?php if ($l['device_id']): ?>
            <span class="text-success"><?= htmlspecialchars($l['device_nome'] ?? 'Dispositivo vinculado') ?></span>
          <?php else: ?>
            <span class="text-muted">Livre — aguardando primeiro uso</span>
          <?php endif; ?>
        </div>

        <div class="mb-3" style="font-size:.85rem">
          <i class="bi bi-calendar me-1"></i>
          <?php if ($l['expira_em']): ?>
            <?php if ($diasRestantes > 0): ?>
              <span class="<?= $expirando ? 'text-danger fw-semibold' : '' ?>">
                <?= $expirando ? "⚠️ " : "" ?><?= $diasRestantes ?> dias restantes
              </span>
            <?php else: ?>
              <span class="text-danger">Expirada</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="text-success">Vitalícia</span>
          <?php endif; ?>
        </div>

        <a href="<?= APP_URL ?>/empresa/licencas/<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">
          Detalhes
        </a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php if (empty($licencas)): ?>
  <div class="col-12">
    <div class="card text-center py-5">
      <p class="text-muted mb-0">Nenhuma licença disponível. Entre em contato com o suporte.</p>
    </div>
  </div>
<?php endif; ?>
</div>
