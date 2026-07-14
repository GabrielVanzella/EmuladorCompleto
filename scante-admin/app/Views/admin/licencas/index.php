<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Licenças</h4>
  <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#modalGerar">
    <i class="bi bi-plus-lg me-1"></i>Gerar Licença
  </button>
</div>

<!-- Cards de estatísticas -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="small text-muted">Total</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100 border-success">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-success"><?= (int)($stats['ativas'] ?? 0) ?></div>
        <div class="small text-muted">Ativas</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100 border-warning">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-warning"><?= (int)($stats['trial'] ?? 0) ?></div>
        <div class="small text-muted">Trial</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100 border-primary">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-primary"><?= (int)($stats['pendentes'] ?? 0) ?></div>
        <div class="small text-muted">Solicitações pendentes</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100 border-danger">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-danger"><?= (int)($stats['expirando_7d'] ?? 0) ?></div>
        <div class="small text-muted">Expirando em 7d</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-secondary"><?= (int)($stats['expiradas'] ?? 0) ?></div>
        <div class="small text-muted">Expiradas</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-danger"><?= (int)($stats['revogadas'] ?? 0) ?></div>
        <div class="small text-muted">Revogadas</div>
      </div>
    </div>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1 text-muted">Chave</label>
        <input type="text" id="filtroChave" class="form-control form-control-sm" placeholder="Buscar chave...">
      </div>
      <div class="col-12 col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1 text-muted">Empresa</label>
        <select id="filtroEmpresa" class="form-select form-select-sm">
          <option value="">Todas as empresas</option>
          <?php foreach ($empresas as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-sm-4 col-md-2 col-xl-2">
        <label class="form-label form-label-sm mb-1 text-muted">Status</label>
        <select id="filtroStatus" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="pendente">Pendente (aguardando aprovação)</option>
          <option value="ativa">Ativa</option>
          <option value="trial">Trial</option>
          <option value="expirada">Expirada</option>
          <option value="revogada">Revogada</option>
        </select>
      </div>
      <div class="col-6 col-sm-4 col-md-2 col-xl-2">
        <label class="form-label form-label-sm mb-1 text-muted">Tipo</label>
        <select id="filtroTipo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="trial">Trial</option>
          <option value="mensal">Mensal</option>
          <option value="anual">Anual</option>
          <option value="vitalicia">Vitalícia</option>
        </select>
      </div>
      <div class="col-6 col-sm-4 col-md-2 col-xl-2">
        <label class="form-label form-label-sm mb-1 text-muted">Dispositivo</label>
        <select id="filtroDispositivo" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="livre">Livre</option>
          <option value="vinculado">Vinculado</option>
        </select>
      </div>
      <div class="col-6 col-sm-6 col-md-3 col-xl-2">
        <label class="form-label form-label-sm mb-1 text-muted">Expiração</label>
        <select id="filtroExpiracao" class="form-select form-select-sm">
          <option value="">Todas</option>
          <option value="expirando7">Expira em 7 dias</option>
          <option value="expirando30">Expira em 30 dias</option>
          <option value="expirada">Já expirada</option>
          <option value="vitalicia">Vitalícia</option>
        </select>
      </div>
      <div class="col-12 col-sm-6 col-md col-xl d-flex align-items-end gap-2">
        <button id="btnLimpar" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-x-circle me-1"></i>Limpar
        </button>
        <span id="contadorResultados" class="small text-muted ms-1 align-self-center"></span>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" id="tabelaLicencas">
        <thead class="table-light">
          <tr>
            <th>Chave</th>
            <th>Empresa</th>
            <th>Tipo</th>
            <th>Status</th>
            <th>Dispositivo</th>
            <th>Expira</th>
            <th>Último acesso</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($licencas as $l):
            $diff = $l['expira_em'] ? ceil((strtotime($l['expira_em']) - time()) / 86400) : null;
            if ($l['tipo'] === 'vitalicia') {
                $expiraCat = 'vitalicia';
            } elseif ($l['status'] === 'expirada' || ($diff !== null && $diff <= 0)) {
                $expiraCat = 'expirada';
            } elseif ($diff !== null && $diff <= 7) {
                $expiraCat = 'expirando7';
            } elseif ($diff !== null && $diff <= 30) {
                $expiraCat = 'expirando30';
            } else {
                $expiraCat = 'ok';
            }
        ?>
          <tr
            data-status="<?= $l['status'] ?>"
            data-tipo="<?= $l['tipo'] ?>"
            data-empresa-id="<?= (int)$l['empresa_id'] ?>"
            data-dispositivo="<?= $l['device_id'] ? 'vinculado' : 'livre' ?>"
            data-expira-cat="<?= $expiraCat ?>"
          >
            <td><code class="chave-cell"><?= $l['chave'] ?></code></td>
            <td><?= htmlspecialchars($l['empresa_nome'] ?? '—') ?></td>
            <td><?= ucfirst($l['tipo']) ?></td>
            <td>
              <span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
              <?php if ($l['status'] === 'pendente' && !empty($l['payment_id'])): ?>
                <span class="badge bg-success" title="Pagamento confirmado, aguardando aprovação"><i class="bi bi-credit-card"></i> Pago</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($l['device_id']): ?>
                <i class="bi bi-phone-fill text-success"></i>
                <small><?= htmlspecialchars($l['device_nome'] ?? substr($l['device_id'], 0, 10) . '…') ?></small>
              <?php else: ?>
                <span class="text-muted">Livre</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($l['expira_em']): ?>
                <span class="<?= $diff !== null && $diff <= 7 ? 'text-danger fw-semibold' : ($diff !== null && $diff <= 30 ? 'text-warning fw-semibold' : '') ?>">
                  <?= date('d/m/Y', strtotime($l['expira_em'])) ?>
                  <?php if ($diff !== null): ?>
                    <?= $diff > 0 ? "($diff d)" : '<span class="badge bg-danger">Expirado</span>' ?>
                  <?php endif; ?>
                </span>
              <?php else: ?>
                <span class="text-success">∞ Vitalícia</span>
              <?php endif; ?>
            </td>
            <td><?= $l['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($l['ultimo_acesso'])) : '—' ?></td>
            <td class="d-flex gap-1">
              <?php if ($l['status'] === 'pendente'): ?>
              <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $l['id'] ?>/aprovar">
                <button type="submit" class="btn btn-sm btn-primary">
                  <i class="bi bi-check-lg me-1"></i>Aprovar<?= (int)$l['quantidade'] > 1 ? ' (' . (int)$l['quantidade'] . ')' : '' ?>
                </button>
              </form>
              <?php endif; ?>
              <a href="<?= APP_URL ?>/admin/licencas/<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="semResultados" class="text-center text-muted py-4 d-none">
      Nenhuma licença encontrada com os filtros selecionados.
    </div>
  </div>
</div>

<!-- Modal Gerar -->
<div class="modal fade" id="modalGerar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/licencas/gerar">
        <div class="modal-header">
          <h5 class="modal-title">Gerar Licença</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Empresa</label>
              <select name="empresa_id" class="form-select" required>
                <option value="">— selecione —</option>
                <?php foreach ($empresas as $e): ?>
                  <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select">
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
            <div class="col-12">
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
(function () {
  const filtros = {
    chave:       document.getElementById('filtroChave'),
    empresa:     document.getElementById('filtroEmpresa'),
    status:      document.getElementById('filtroStatus'),
    tipo:        document.getElementById('filtroTipo'),
    dispositivo: document.getElementById('filtroDispositivo'),
    expiracao:   document.getElementById('filtroExpiracao'),
  };
  const contador     = document.getElementById('contadorResultados');
  const semResultados = document.getElementById('semResultados');
  const btnLimpar    = document.getElementById('btnLimpar');
  const linhas       = document.querySelectorAll('#tabelaLicencas tbody tr');

  function filtrar() {
    const chave       = filtros.chave.value.toLowerCase().trim();
    const empresa     = filtros.empresa.value;
    const status      = filtros.status.value;
    const tipo        = filtros.tipo.value;
    const dispositivo = filtros.dispositivo.value;
    const expiracao   = filtros.expiracao.value;

    let visiveis = 0;

    linhas.forEach(tr => {
      const matchChave = !chave || tr.querySelector('.chave-cell').textContent.toLowerCase().includes(chave);
      const matchEmpresa = !empresa || tr.dataset.empresaId === empresa;
      const matchStatus = !status || tr.dataset.status === status;
      const matchTipo = !tipo || tr.dataset.tipo === tipo;
      const matchDispositivo = !dispositivo || tr.dataset.dispositivo === dispositivo;

      let matchExpiracao = true;
      if (expiracao) {
        const cat = tr.dataset.expiraCat;
        if (expiracao === 'expirando7') {
          matchExpiracao = cat === 'expirando7';
        } else if (expiracao === 'expirando30') {
          matchExpiracao = cat === 'expirando7' || cat === 'expirando30';
        } else if (expiracao === 'expirada') {
          matchExpiracao = cat === 'expirada';
        } else if (expiracao === 'vitalicia') {
          matchExpiracao = cat === 'vitalicia';
        }
      }

      const visivel = matchChave && matchEmpresa && matchStatus && matchTipo && matchDispositivo && matchExpiracao;
      tr.style.display = visivel ? '' : 'none';
      if (visivel) visiveis++;
    });

    contador.textContent = visiveis + ' licença' + (visiveis !== 1 ? 's' : '');
    semResultados.classList.toggle('d-none', visiveis > 0);
  }

  Object.values(filtros).forEach(el => {
    el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', filtrar);
  });

  btnLimpar.addEventListener('click', () => {
    filtros.chave.value = '';
    filtros.empresa.value = '';
    filtros.status.value = '';
    filtros.tipo.value = '';
    filtros.dispositivo.value = '';
    filtros.expiracao.value = '';
    filtrar();
  });

  filtrar();
})();
</script>
