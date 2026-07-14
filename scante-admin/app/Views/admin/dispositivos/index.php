<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Dispositivos</h4>
  <span class="text-muted small">Atualizado em <?= date('H:i:s') ?> — <a href="" class="text-muted">Atualizar</a></span>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
<?php endif; ?>

<!-- Cards de estatísticas -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold"><?= $stats['total'] ?></div>
        <div class="small text-muted">Total</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100 border-success">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-success"><?= $stats['online'] ?></div>
        <div class="small text-muted">Online agora</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100 border-primary">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-primary"><?= $stats['comLicenca'] ?></div>
        <div class="small text-muted">Com licença ativa</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100">
      <div class="card-body py-3">
        <div class="fs-4 fw-bold text-secondary"><?= $stats['semLicenca'] ?></div>
        <div class="small text-muted">Sem licença</div>
      </div>
    </div>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-3">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label form-label-sm mb-1 text-muted">Buscar (dispositivo, empresa, chave)</label>
        <input type="text" id="filtroBusca" class="form-control form-control-sm" placeholder="Digitar...">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm mb-1 text-muted">Status Licença</label>
        <select id="filtroStatus" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="ativa">Ativa</option>
          <option value="sem_licenca">Sem licença</option>
          <option value="expirada">Expirada</option>
          <option value="revogada">Revogada</option>
          <option value="trial">Trial</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label form-label-sm mb-1 text-muted">Presença</label>
        <select id="filtroOnline" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="online">Online agora</option>
          <option value="offline">Offline</option>
        </select>
      </div>
      <div class="col-12 col-md d-flex align-items-end gap-2">
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
      <table class="table table-hover mb-0" id="tabelaDispositivos">
        <thead class="table-light">
          <tr>
            <th>Dispositivo</th>
            <th>Empresa</th>
            <th>Licença</th>
            <th>Status</th>
            <th>Primeiro acesso</th>
            <th>Último acesso</th>
            <th>Versão</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $agora = time();
        foreach ($dispositivos as $d):
            $isOnline = $d['ultimo_acesso'] && ($agora - strtotime($d['ultimo_acesso'])) < 300;
        ?>
          <tr
            data-status="<?= $d['status_licenca'] ?>"
            data-online="<?= $isOnline ? 'online' : 'offline' ?>"
            data-busca="<?= strtolower(htmlspecialchars(($d['device_nome'] ?? '') . ' ' . ($d['empresa_nome'] ?? '') . ' ' . ($d['chave_licenca'] ?? '') . ' ' . $d['device_id'])) ?>"
          >
            <td>
              <?php if ($isOnline): ?>
                <span class="badge bg-success me-1" title="Online agora">●</span>
              <?php endif; ?>
              <span><?= htmlspecialchars($d['device_nome'] ?? 'Desconhecido') ?></span><br>
              <small class="text-muted font-monospace"><?= htmlspecialchars(substr($d['device_id'], 0, 16)) ?>…</small>
            </td>
            <td><?= $d['empresa_nome'] ? htmlspecialchars($d['empresa_nome']) : '<span class="text-muted">—</span>' ?></td>
            <td>
              <?php if ($d['chave_licenca']): ?>
                <code class="small"><?= htmlspecialchars($d['chave_licenca']) ?></code>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php
              $badgeCor = match($d['status_licenca']) {
                  'ativa'       => 'success',
                  'trial'       => 'warning',
                  'expirada'    => 'danger',
                  'revogada'    => 'secondary',
                  default       => 'light text-dark',
              };
              $badgeLabel = match($d['status_licenca']) {
                  'ativa'       => 'Ativa',
                  'trial'       => 'Trial',
                  'expirada'    => 'Expirada',
                  'revogada'    => 'Revogada',
                  default       => 'Sem licença',
              };
              ?>
              <span class="badge bg-<?= $badgeCor ?>"><?= $badgeLabel ?></span>
            </td>
            <td><small><?= $d['primeiro_acesso'] ? date('d/m/Y H:i', strtotime($d['primeiro_acesso'])) : '—' ?></small></td>
            <td>
              <small><?= $d['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($d['ultimo_acesso'])) : '—' ?></small>
              <?php if ($isOnline): ?>
                <span class="badge bg-success ms-1">Online</span>
              <?php endif; ?>
            </td>
            <td><small class="text-muted"><?= htmlspecialchars($d['app_version'] ?? '—') ?></small></td>
            <td class="text-end text-nowrap">
              <button type="button" class="btn btn-sm btn-outline-primary btn-atribuir-empresa"
                      title="Atribuir empresa"
                      data-device="<?= htmlspecialchars($d['device_id']) ?>"
                      data-nome="<?= htmlspecialchars($d['device_nome'] ?? $d['device_id']) ?>">
                <i class="bi bi-building"></i>
              </button>
              <button type="button" class="btn btn-sm btn-outline-success btn-atribuir-licenca"
                      title="Atribuir licença"
                      data-device="<?= htmlspecialchars($d['device_id']) ?>"
                      data-nome="<?= htmlspecialchars($d['device_nome'] ?? $d['device_id']) ?>">
                <i class="bi bi-key"></i>
              </button>
              <form method="POST" action="<?= APP_URL ?>/admin/dispositivos/excluir" class="d-inline"
                    onsubmit="return confirm('Remover este dispositivo da lista? (não afeta a licença)');">
                <input type="hidden" name="device_id" value="<?= htmlspecialchars($d['device_id']) ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir dispositivo">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (empty($dispositivos)): ?>
    <div class="text-center text-muted py-5">
      <i class="bi bi-phone fs-2 d-block mb-2"></i>
      Nenhum dispositivo registrado ainda. Os dispositivos aparecem aqui após abrir o app.
    </div>
    <?php endif; ?>
    <div id="semResultados" class="text-center text-muted py-4 d-none">
      Nenhum dispositivo encontrado com os filtros selecionados.
    </div>
  </div>
</div>

<!-- Modal: Atribuir empresa -->
<div class="modal fade" id="modalEmpresa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="<?= APP_URL ?>/admin/dispositivos/atribuir-empresa">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-building me-2"></i>Atribuir empresa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Dispositivo: <strong id="empDeviceNome"></strong></p>
        <input type="hidden" name="device_id" id="empDeviceId">
        <label class="form-label">Empresa</label>
        <select name="empresa_id" class="form-select" required>
          <option value="">Selecione...</option>
          <?php foreach ($empresas as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nome']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($empresas)): ?>
          <p class="text-muted small mt-2 mb-0">Nenhuma empresa cadastrada. Crie uma em <a href="<?= APP_URL ?>/admin/empresas">Empresas</a>.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-accent">Atribuir</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Atribuir licença -->
<div class="modal fade" id="modalLicenca" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="<?= APP_URL ?>/admin/dispositivos/atribuir-licenca">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-key me-2"></i>Atribuir licença</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Dispositivo: <strong id="licDeviceNome"></strong></p>
        <input type="hidden" name="device_id" id="licDeviceId">
        <label class="form-label">Licença disponível</label>
        <select name="licenca_id" class="form-select" required>
          <option value="">Selecione...</option>
          <?php foreach ($licencasDisponiveis as $l): ?>
            <option value="<?= $l['id'] ?>">
              <?= htmlspecialchars($l['chave'] . ' — ' . ($l['empresa_nome'] ?? 'sem empresa') . ' (' . $l['tipo'] . '/' . $l['status'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($licencasDisponiveis)): ?>
          <p class="text-muted small mt-2 mb-0">Nenhuma licença disponível (ativa/trial e sem dispositivo). Gere uma em <a href="<?= APP_URL ?>/admin/licencas">Licenças</a>.</p>
        <?php else: ?>
          <p class="text-muted small mt-2 mb-0">A empresa do dispositivo será ajustada conforme a licença escolhida.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-accent">Atribuir</button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const busca    = document.getElementById('filtroBusca');
  const status   = document.getElementById('filtroStatus');
  const online   = document.getElementById('filtroOnline');
  const btnLimpar = document.getElementById('btnLimpar');
  const contador  = document.getElementById('contadorResultados');
  const semRes    = document.getElementById('semResultados');
  const linhas    = document.querySelectorAll('#tabelaDispositivos tbody tr');

  function filtrar() {
    const q      = busca.value.toLowerCase().trim();
    const st     = status.value;
    const ol     = online.value;
    let visiveis = 0;

    linhas.forEach(tr => {
      const matchBusca  = !q  || tr.dataset.busca.includes(q);
      const matchStatus = !st || tr.dataset.status === st;
      const matchOnline = !ol || tr.dataset.online === ol;
      const visivel = matchBusca && matchStatus && matchOnline;
      tr.style.display = visivel ? '' : 'none';
      if (visivel) visiveis++;
    });

    contador.textContent = visiveis + ' dispositivo' + (visiveis !== 1 ? 's' : '');
    semRes.classList.toggle('d-none', visiveis > 0 || linhas.length === 0);
  }

  busca.addEventListener('input', filtrar);
  status.addEventListener('change', filtrar);
  online.addEventListener('change', filtrar);
  btnLimpar.addEventListener('click', () => {
    busca.value = ''; status.value = ''; online.value = '';
    filtrar();
  });

  filtrar();
})();

// Abre os modais preenchendo o dispositivo selecionado.
// bootstrap.Modal é acessado só no clique (o bundle carrega depois deste script).
(function () {
  document.querySelectorAll('.btn-atribuir-empresa').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('empDeviceId').value = btn.dataset.device;
      document.getElementById('empDeviceNome').textContent = btn.dataset.nome;
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEmpresa')).show();
    });
  });

  document.querySelectorAll('.btn-atribuir-licenca').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('licDeviceId').value = btn.dataset.device;
      document.getElementById('licDeviceNome').textContent = btn.dataset.nome;
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalLicenca')).show();
    });
  });
})();
</script>
