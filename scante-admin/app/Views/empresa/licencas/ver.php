<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?= APP_URL ?>/empresa/licencas" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Detalhes da Licença</h4>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-key me-2"></i>Licença</h6>
        <dl style="font-size:.88rem" class="mb-0">
          <dt class="text-muted">Chave</dt>
          <dd><code class="fs-6"><?= $licenca['chave'] ?></code></dd>
          <dt class="text-muted">Status</dt>
          <dd><span class="badge badge-<?= $licenca['status'] ?>"><?= ucfirst($licenca['status']) ?></span></dd>
          <dt class="text-muted">Tipo</dt>
          <dd><?= ucfirst($licenca['tipo']) ?></dd>
          <dt class="text-muted">Validade</dt>
          <dd>
            <?php if ($licenca['expira_em']): ?>
              <?php $diff = ceil((strtotime($licenca['expira_em']) - time()) / 86400); ?>
              <?= date('d/m/Y', strtotime($licenca['expira_em'])) ?>
              <span class="<?= $diff <= 7 ? 'text-danger fw-semibold' : 'text-muted' ?>">
                (<?= $diff > 0 ? "$diff dias" : 'expirada' ?>)
              </span>
            <?php else: ?>
              <span class="text-success">Vitalícia ∞</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-phone me-2"></i>Dispositivo vinculado</h6>
        <?php if ($licenca['device_id']): ?>
          <div class="d-flex align-items-center gap-3 mb-3">
            <i class="bi bi-phone-fill text-success fs-2"></i>
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($licenca['device_nome'] ?? 'Dispositivo') ?></div>
              <small class="text-muted"><?= $licenca['device_id'] ?></small><br>
              <small class="text-muted">Vinculado em: <?= $licenca['vinculada_em'] ? date('d/m/Y', strtotime($licenca['vinculada_em'])) : '—' ?></small>
            </div>
          </div>

          <?php if ($licenca['status'] === 'ativa' || $licenca['status'] === 'trial'): ?>
            <div class="alert alert-warning py-2" style="font-size:.85rem">
              <i class="bi bi-info-circle me-1"></i>
              Se seu dispositivo foi perdido, roubado ou quebrado, solicite a transferência abaixo.
            </div>
            <button class="btn btn-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalTransferir">
              <i class="bi bi-arrow-left-right me-1"></i>Transferir para outro dispositivo
            </button>
          <?php endif; ?>
        <?php else: ?>
          <div class="text-center text-muted py-3">
            <i class="bi bi-phone fs-2 d-block mb-2 opacity-25"></i>
            Licença livre — abra o ScanTE e insira esta chave para vincular ao dispositivo.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Histórico -->
<?php if (!empty($historico)): ?>
<div class="card">
  <div class="card-body">
    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Histórico</h6>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr><th>Ação</th><th>Dispositivo</th><th>Motivo</th><th>Data</th></tr>
        </thead>
        <tbody>
        <?php foreach ($historico as $h): ?>
          <tr>
            <td><span class="badge bg-secondary"><?= ucfirst($h['acao']) ?></span></td>
            <td><small><?= htmlspecialchars($h['device_nome'] ?? $h['device_id']) ?></small></td>
            <td><?= htmlspecialchars($h['motivo'] ?? '—') ?></td>
            <td><?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Transferir -->
<div class="modal fade" id="modalTransferir" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/empresa/licencas/<?= $licenca['id'] ?>/transferir">
        <div class="modal-header">
          <h5 class="modal-title">Transferir licença</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">O dispositivo atual será desvinculado. A licença ficará livre e poderá ser ativada em outro aparelho.</p>
          <label class="form-label fw-semibold">Motivo da transferência *</label>
          <select name="motivo" class="form-select mb-2">
            <option value="Dispositivo quebrado">Dispositivo quebrado</option>
            <option value="Dispositivo perdido">Dispositivo perdido</option>
            <option value="Dispositivo roubado">Dispositivo roubado</option>
            <option value="Troca de aparelho">Troca de aparelho</option>
          </select>
          <input type="text" name="motivo_outro" class="form-control form-control-sm" placeholder="Ou descreva o motivo...">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Confirmar transferência</button>
        </div>
      </form>
    </div>
  </div>
</div>
