<?php
$statusMap = [
  'aberto'       => ['Aberto', 'bg-primary'],
  'em_andamento' => ['Em andamento', 'bg-warning text-dark'],
  'resolvido'    => ['Resolvido', 'bg-success'],
];
?>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Meus Chamados</h4>
  <a href="<?= APP_URL ?>/empresa/chamados/novo" class="btn btn-accent">
    <i class="bi bi-plus-lg me-1"></i> Abrir chamado
  </a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">#</th>
          <th>Assunto</th>
          <th>Licença</th>
          <th>Status</th>
          <th>Última atualização</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($chamados as $c): ?>
        <?php [$label, $cls] = $statusMap[$c['status']] ?? ['—', 'bg-secondary']; ?>
        <tr>
          <td class="ps-3 text-muted">#<?= $c['id'] ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($c['assunto']) ?></td>
          <td>
            <?php if ($c['licenca_chave']): ?>
              <code style="font-size:.8rem"><?= $c['licenca_chave'] ?></code>
            <?php else: ?>
              <span class="text-muted" style="font-size:.82rem">Assunto geral</span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $cls ?>"><?= $label ?></span></td>
          <td class="text-muted" style="font-size:.82rem"><?= date('d/m/Y H:i', strtotime($c['atualizado_em'])) ?></td>
          <td class="text-end pe-3">
            <a href="<?= APP_URL ?>/empresa/chamados/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">Abrir</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($chamados)): ?>
        <tr><td colspan="6" class="text-center text-muted py-5">
          Você ainda não abriu nenhum chamado.<br>
          <a href="<?= APP_URL ?>/empresa/chamados/novo" class="btn btn-accent btn-sm mt-3">Abrir o primeiro chamado</a>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
