<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Pagamentos</h4>
  <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#modalRegistrar">
    <i class="bi bi-plus-lg me-1"></i>Registrar pagamento
  </button>
</div>

<!-- Cards de estatísticas -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-success">R$ <?= number_format((float)($stats['total_aprovado'] ?? 0), 2, ',', '.') ?></div>
        <div class="small text-muted">Total arrecadado</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100 border-success">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-success">R$ <?= number_format((float)($stats['total_mes'] ?? 0), 2, ',', '.') ?></div>
        <div class="small text-muted">Este mês</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold">R$ <?= number_format((float)($stats['ticket_medio'] ?? 0), 2, ',', '.') ?></div>
        <div class="small text-muted">Ticket médio</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="small text-muted">Total de transações</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- Resumo por empresa -->
  <?php if (!empty($porEmpresa)): ?>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Por empresa</h6>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($porEmpresa as $pe): ?>
          <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
            <div>
              <div class="fw-semibold small"><?= htmlspecialchars($pe['nome']) ?></div>
              <div class="text-muted" style="font-size:.78rem"><?= (int)$pe['total_pagamentos'] ?> transação(ões)</div>
            </div>
            <span class="fw-bold text-success small">R$ <?= number_format((float)$pe['total_pago'], 2, ',', '.') ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Status rápido -->
  <div class="col-md-<?= !empty($porEmpresa) ? '8' : '12' ?>">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2"></i>Status das transações</h6>
        <div class="row g-2">
          <div class="col-4 text-center">
            <div class="fs-4 fw-bold text-success"><?= (int)($stats['qtd_aprovados'] ?? 0) ?></div>
            <div class="small text-muted">Aprovados</div>
          </div>
          <div class="col-4 text-center">
            <div class="fs-4 fw-bold text-warning"><?= (int)($stats['qtd_pendentes'] ?? 0) ?></div>
            <div class="small text-muted">Pendentes</div>
          </div>
          <div class="col-4 text-center">
            <div class="fs-4 fw-bold text-danger"><?= (int)($stats['qtd_rejeitados'] ?? 0) ?></div>
            <div class="small text-muted">Rejeitados</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-3">
    <form method="GET" action="<?= APP_URL ?>/admin/pagamentos">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-6 col-md-3">
          <label class="form-label form-label-sm mb-1 text-muted">Empresa</label>
          <select name="empresa_id" class="form-select form-select-sm">
            <option value="">Todas as empresas</option>
            <?php foreach ($empresas as $e): ?>
              <option value="<?= $e['id'] ?>" <?= ($filtros['empresa_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1 text-muted">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="approved"  <?= ($filtros['status'] ?? '') === 'approved'  ? 'selected' : '' ?>>Aprovado</option>
            <option value="pending"   <?= ($filtros['status'] ?? '') === 'pending'   ? 'selected' : '' ?>>Pendente</option>
            <option value="rejected"  <?= ($filtros['status'] ?? '') === 'rejected'  ? 'selected' : '' ?>>Rejeitado</option>
            <option value="cancelled" <?= ($filtros['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1 text-muted">Tipo</label>
          <select name="tipo" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="mensal"   <?= ($filtros['tipo'] ?? '') === 'mensal'   ? 'selected' : '' ?>>Mensal</option>
            <option value="anual"    <?= ($filtros['tipo'] ?? '') === 'anual'    ? 'selected' : '' ?>>Anual</option>
            <option value="vitalicia"<?= ($filtros['tipo'] ?? '') === 'vitalicia'? 'selected' : '' ?>>Vitalícia</option>
            <option value="manual"   <?= ($filtros['tipo'] ?? '') === 'manual'   ? 'selected' : '' ?>>Manual</option>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1 text-muted">De</label>
          <input type="date" name="de" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['de'] ?? '') ?>">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label form-label-sm mb-1 text-muted">Até</label>
          <input type="date" name="ate" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['ate'] ?? '') ?>">
        </div>
        <div class="col-12 col-md d-flex gap-2 align-items-end">
          <button type="submit" class="btn btn-sm btn-accent">Filtrar</button>
          <a href="<?= APP_URL ?>/admin/pagamentos" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Tabela -->
<div class="card">
  <div class="card-body p-0">
    <?php if (empty($pagamentos)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-credit-card fs-2 d-block mb-2 opacity-25"></i>
        Nenhum pagamento encontrado.
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Data</th>
            <th>Empresa</th>
            <th>Licença</th>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Status</th>
            <th>ID transação</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pagamentos as $p): ?>
          <tr>
            <td class="text-nowrap">
              <div><?= date('d/m/Y', strtotime($p['criado_em'])) ?></div>
              <small class="text-muted"><?= date('H:i', strtotime($p['criado_em'])) ?></small>
            </td>
            <td>
              <a href="<?= APP_URL ?>/admin/empresas/<?= $p['empresa_id'] ?>" class="text-decoration-none fw-semibold">
                <?= htmlspecialchars($p['empresa_nome']) ?>
              </a>
            </td>
            <td>
              <a href="<?= APP_URL ?>/admin/licencas/<?= $p['licenca_id'] ?>" class="text-decoration-none">
                <code style="font-size:.78rem"><?= $p['licenca_chave'] ?></code>
              </a>
            </td>
            <td><?= ucfirst($p['tipo'] ?? '—') ?></td>
            <td class="fw-semibold <?= ($p['status'] === 'approved') ? 'text-success' : '' ?>">
              R$ <?= number_format((float)($p['valor'] ?? 0), 2, ',', '.') ?>
            </td>
            <td>
              <?php
                $badgeMap = [
                  'approved'  => 'success',
                  'pending'   => 'warning',
                  'rejected'  => 'danger',
                  'cancelled' => 'secondary',
                ];
                $statusLabel = [
                  'approved'  => 'Aprovado',
                  'pending'   => 'Pendente',
                  'rejected'  => 'Rejeitado',
                  'cancelled' => 'Cancelado',
                ];
                $bs = $badgeMap[$p['status']] ?? 'secondary';
                $sl = $statusLabel[$p['status']] ?? ucfirst($p['status']);
              ?>
              <span class="badge bg-<?= $bs ?>"><?= $sl ?></span>
            </td>
            <td>
              <small class="text-muted font-monospace"><?= htmlspecialchars($p['payment_id']) ?></small>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-danger"
                onclick="confirmarExcluir(<?= $p['id'] ?>, '<?= htmlspecialchars($p['empresa_nome']) ?>')"
                title="Remover">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="4" class="text-end fw-semibold">Total aprovado:</td>
            <td class="fw-bold text-success">
              R$ <?= number_format((float)($stats['total_aprovado'] ?? 0), 2, ',', '.') ?>
            </td>
            <td colspan="3"></td>
          </tr>
        </tfoot>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal registrar pagamento -->
<div class="modal fade" id="modalRegistrar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/pagamentos/registrar">
        <div class="modal-header">
          <h5 class="modal-title">Registrar pagamento manual</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Empresa</label>
              <select id="selectEmpresaModal" class="form-select" required>
                <option value="">— selecione a empresa —</option>
                <?php foreach ($empresas as $e): ?>
                  <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Licença</label>
              <select name="licenca_id" id="selectLicencaModal" class="form-select" required disabled>
                <option value="">— selecione a empresa primeiro —</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Valor (R$)</label>
              <input type="text" name="valor" class="form-control" placeholder="0,00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select">
                <option value="manual">Manual / PIX / Transferência</option>
                <option value="mensal">Mensal</option>
                <option value="anual">Anual</option>
                <option value="vitalicia">Vitalícia</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="approved">Aprovado</option>
                <option value="pending">Pendente</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-accent">Registrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Form oculto para excluir -->
<form id="formExcluir" method="POST" style="display:none">
  <input type="hidden" name="_action" value="excluir">
</form>

<script>
// Carrega licenças da empresa selecionada no modal
const licencasPorEmpresa = <?= json_encode(
    array_reduce(
        (new \App\Models\Licenca())->comEmpresa(),
        function($carry, $l) {
            $carry[$l['empresa_id']][] = ['id' => $l['id'], 'chave' => $l['chave'], 'tipo' => $l['tipo']];
            return $carry;
        },
        []
    )
, JSON_UNESCAPED_UNICODE) ?>;

document.getElementById('selectEmpresaModal').addEventListener('change', function () {
  const sel = document.getElementById('selectLicencaModal');
  const empresaId = this.value;
  sel.innerHTML = '<option value="">— selecione —</option>';
  sel.disabled = !empresaId;
  if (!empresaId) return;
  const lista = licencasPorEmpresa[empresaId] || [];
  lista.forEach(l => {
    const opt = document.createElement('option');
    opt.value = l.id;
    opt.textContent = l.chave + ' (' + l.tipo + ')';
    sel.appendChild(opt);
  });
});

// Confirmar exclusão
function confirmarExcluir(id, empresa) {
  if (!confirm('Remover pagamento de "' + empresa + '"?\nEsta ação não pode ser desfeita.')) return;
  window.location.href = '<?= APP_URL ?>/admin/pagamentos/' + id + '/excluir';
}
</script>
