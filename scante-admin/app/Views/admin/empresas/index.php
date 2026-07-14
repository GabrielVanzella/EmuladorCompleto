<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Empresas</h4>
  <a href="<?= APP_URL ?>/admin/empresas/criar" class="btn btn-accent">
    <i class="bi bi-plus-lg me-1"></i>Nova Empresa
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Empresa</th>
            <th>CNPJ</th>
            <th>E-mail</th>
            <th>Contato</th>
            <th class="text-center">Ativas</th>
            <th class="text-center">Trial</th>
            <th class="text-center">Total</th>
            <th class="text-center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($empresas as $e): ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars($e['nome']) ?></td>
            <td><?= htmlspecialchars($e['cnpj'] ?? '—') ?></td>
            <td><?= htmlspecialchars($e['email']) ?></td>
            <td><?= htmlspecialchars($e['contato'] ?? '—') ?></td>
            <td class="text-center">
              <span class="badge badge-ativa"><?= $e['licencas_ativas'] ?? 0 ?></span>
            </td>
            <td class="text-center">
              <span class="badge badge-trial"><?= $e['licencas_trial'] ?? 0 ?></span>
            </td>
            <td class="text-center"><?= $e['total_licencas'] ?? 0 ?></td>
            <td class="text-center">
              <?php if ($e['ativo']): ?>
                <span class="badge bg-success">Ativa</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inativa</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= APP_URL ?>/admin/empresas/<?= $e['id'] ?>" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= APP_URL ?>/admin/empresas/<?= $e['id'] ?>/editar" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger"
                  onclick="if(confirm('Remover empresa?')) window.location='<?= APP_URL ?>/admin/empresas/<?= $e['id'] ?>/excluir'">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($empresas)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Nenhuma empresa cadastrada.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
