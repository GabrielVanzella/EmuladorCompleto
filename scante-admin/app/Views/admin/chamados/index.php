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

<h4 class="fw-bold mb-4">Chamados de Suporte</h4>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">#</th>
          <th>Empresa</th>
          <th>Assunto</th>
          <th>Licença</th>
          <th>Status</th>
          <th>Atualizado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($chamados as $c): ?>
        <?php [$label, $cls] = $statusMap[$c['status']] ?? ['—', 'bg-secondary']; ?>
        <tr>
          <td class="ps-3 text-muted">#<?= $c['id'] ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($c['empresa_nome'] ?? '—') ?></td>
          <td><?= htmlspecialchars($c['assunto']) ?></td>
          <td>
            <?php if ($c['licenca_chave']): ?>
              <code style="font-size:.8rem"><?= $c['licenca_chave'] ?></code>
              <span class="text-muted" style="font-size:.75rem">(<?= $c['licenca_tipo'] ?>)</span>
            <?php else: ?>
              <span class="text-muted" style="font-size:.82rem">Geral</span>
            <?php endif; ?>
          </td>
          <td><span class="badge <?= $cls ?>"><?= $label ?></span></td>
          <td class="text-muted" style="font-size:.82rem"><?= date('d/m/Y H:i', strtotime($c['atualizado_em'])) ?></td>
          <td class="text-end pe-3">
            <a href="<?= APP_URL ?>/admin/chamados/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">Abrir</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($chamados)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">Nenhum chamado aberto até agora.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
