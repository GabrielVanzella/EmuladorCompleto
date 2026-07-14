<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?= APP_URL ?>/admin/licencas" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Licença</h4>
  <code class="ms-2 fs-6"><?= $licenca['chave'] ?></code>
</div>

<div class="row g-3 mb-4">
  <!-- Dados da licença -->
  <div class="col-md-5">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-key me-2"></i>Dados</h6>
        <dl style="font-size:.88rem" class="mb-0">
          <dt class="text-muted">Status</dt>
          <dd><span class="badge badge-<?= $licenca['status'] ?> fs-6"><?= ucfirst($licenca['status']) ?></span></dd>
          <dt class="text-muted">Tipo</dt>
          <dd><?= ucfirst($licenca['tipo']) ?></dd>
          <?php if ((int)($licenca['quantidade'] ?? 1) > 1 || !empty($licenca['anos_suporte'])): ?>
          <dt class="text-muted">Pedido em lote</dt>
          <dd><?= (int)$licenca['quantidade'] ?> licença(s) × <?= (int)$licenca['anos_suporte'] ?> ano(s) de suporte</dd>
          <?php endif; ?>
          <?php if (!empty($licenca['email'])): ?>
          <dt class="text-muted">Contato</dt>
          <dd><?= htmlspecialchars($licenca['email']) ?><?= !empty($licenca['telefone']) ? ' · ' . htmlspecialchars($licenca['telefone']) : '' ?></dd>
          <?php endif; ?>
          <dt class="text-muted">Criada em</dt>
          <dd><?= date('d/m/Y H:i', strtotime($licenca['criada_em'])) ?></dd>
          <?php if ($licenca['tipo'] !== 'vitalicia'): ?>
          <dt class="text-muted">Expira em</dt>
          <dd>
            <?php if ($licenca['expira_em']): ?>
              <?php $diff = ceil((strtotime($licenca['expira_em']) - time()) / 86400); ?>
              <?= date('d/m/Y H:i', strtotime($licenca['expira_em'])) ?>
              <span class="<?= $diff <= 7 ? 'text-danger' : 'text-muted' ?>">
                (<?= $diff > 0 ? "$diff dias restantes" : 'expirada' ?>)
              </span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </dd>
          <?php endif; ?>
          <dt class="text-muted">Último acesso</dt>
          <dd><?= $licenca['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($licenca['ultimo_acesso'])) : '—' ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <!-- Dispositivo vinculado -->
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-phone me-2"></i>Dispositivo</h6>
        <?php if ($licenca['device_id']): ?>
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-phone-fill text-success fs-3"></i>
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($licenca['device_nome'] ?? 'Dispositivo desconhecido') ?></div>
              <small class="text-muted"><?= $licenca['device_id'] ?></small>
            </div>
          </div>
          <small class="text-muted">Vinculado em: <?= $licenca['vinculada_em'] ? date('d/m/Y H:i', strtotime($licenca['vinculada_em'])) : '—' ?></small>
          <hr>
          <!-- Transferência -->
          <button class="btn btn-sm btn-warning w-100" data-bs-toggle="modal" data-bs-target="#modalTransferir">
            <i class="bi bi-arrow-left-right me-1"></i>Desvincular dispositivo
          </button>
        <?php else: ?>
          <div class="text-muted text-center py-3">
            <i class="bi bi-phone fs-2 d-block mb-1 opacity-25"></i>
            Sem dispositivo vinculado.<br>
            <small>O próximo app a usar esta chave será vinculado automaticamente.</small>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Ações -->
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="bi bi-gear me-2"></i>Ações</h6>
        <div class="d-grid gap-2">
          <?php if ($licenca['status'] === 'pendente'): ?>
            <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $licenca['id'] ?>/aprovar">
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-check-lg me-1"></i>Aprovar solicitação
                <?= (int)$licenca['quantidade'] > 1 ? ' (' . (int)$licenca['quantidade'] . ' licenças)' : '' ?>
              </button>
            </form>
          <?php endif; ?>
          <?php if ($licenca['status'] === 'revogada'): ?>
            <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $licenca['id'] ?>/reativar">
              <button type="submit" class="btn btn-outline-success btn-sm w-100">
                <i class="bi bi-check-circle me-1"></i>Reativar licença
              </button>
            </form>
          <?php elseif ($licenca['status'] !== 'pendente'): ?>
            <button class="btn btn-outline-danger btn-sm"
              data-bs-toggle="modal" data-bs-target="#modalRevogar">
              <i class="bi bi-x-circle me-1"></i>Revogar licença
            </button>
          <?php endif; ?>

          <?php if ($licenca['tipo'] !== 'vitalicia'): ?>
          <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEstender">
            <i class="bi bi-calendar-plus me-1"></i>Estender validade
          </button>
          <?php endif; ?>
          <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlterarTipo">
            <i class="bi bi-arrow-repeat me-1"></i>Alterar tipo
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Histórico -->
<div class="card">
  <div class="card-body">
    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Histórico de dispositivos</h6>
    <?php if (empty($historico)): ?>
      <p class="text-muted mb-0">Nenhum registro.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm mb-0">
          <thead class="table-light">
            <tr><th>Ação</th><th>Device ID</th><th>Dispositivo</th><th>Motivo</th><th>Por</th><th>Data</th></tr>
          </thead>
          <tbody>
          <?php foreach ($historico as $h): ?>
            <tr>
              <td><span class="badge bg-secondary"><?= ucfirst($h['acao']) ?></span></td>
              <td><small><?= htmlspecialchars($h['device_id']) ?></small></td>
              <td><?= htmlspecialchars($h['device_nome'] ?? '—') ?></td>
              <td><?= htmlspecialchars($h['motivo'] ?? '—') ?></td>
              <td><?= htmlspecialchars($h['usuario_nome'] ?? 'Sistema') ?></td>
              <td><?= date('d/m/Y H:i', strtotime($h['criado_em'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Revogar -->
<div class="modal fade" id="modalRevogar" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title text-danger">Revogar licença</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">A licença ficará inativa e o app será bloqueado. Confirma?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Não</button>
        <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $licenca['id'] ?>/revogar">
          <button type="submit" class="btn btn-danger btn-sm">Sim, revogar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Transferir -->
<div class="modal fade" id="modalTransferir" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $licenca['id'] ?>/transferir">
        <div class="modal-header"><h5 class="modal-title">Desvincular dispositivo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <p class="text-muted">O dispositivo atual será desvinculado. A licença poderá ser usada em outro aparelho.</p>
          <label class="form-label">Motivo</label>
          <input type="text" name="motivo" class="form-control" placeholder="Ex: dispositivo quebrado" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Desvincular</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Alterar Tipo -->
<div class="modal fade" id="modalAlterarTipo" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $licenca['id'] ?>/alterar-tipo">
        <div class="modal-header">
          <h5 class="modal-title">Alterar tipo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Novo tipo</label>
            <select name="tipo" id="alterarTipoSelect" class="form-select" required>
              <option value="trial"    <?= $licenca['tipo'] === 'trial'    ? 'selected' : '' ?>>Trial</option>
              <option value="mensal"   <?= $licenca['tipo'] === 'mensal'   ? 'selected' : '' ?>>Mensal</option>
              <option value="anual"    <?= $licenca['tipo'] === 'anual'    ? 'selected' : '' ?>>Anual</option>
              <option value="vitalicia"<?= $licenca['tipo'] === 'vitalicia'? 'selected' : '' ?>>Vitalícia</option>
            </select>
          </div>
          <div id="wrapperDiasAlteracao">
            <label class="form-label" id="labelDiasAlteracao">Nova validade (dias a partir de hoje)</label>
            <input type="number" name="dias" id="inputDiasAlteracao" class="form-control" placeholder="Ex: 30" min="1">
            <div class="form-text" id="textoDiasAlteracao"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-accent btn-sm">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
(function () {
  const tipoAtual = <?= json_encode($licenca['tipo']) ?>;
  const sel   = document.getElementById('alterarTipoSelect');
  const wrap  = document.getElementById('wrapperDiasAlteracao');
  const input = document.getElementById('inputDiasAlteracao');
  const texto = document.getElementById('textoDiasAlteracao');

  function atualizar() {
    const novoTipo = sel.value;
    if (novoTipo === 'vitalicia') {
      wrap.style.display = 'none';
      input.required = false;
    } else {
      wrap.style.display = '';
      if (tipoAtual === 'vitalicia') {
        // Mudando de vitalícia: sem data atual, dias é obrigatório
        input.required = true;
        texto.textContent = 'Obrigatório ao sair do tipo Vitalícia.';
        texto.className = 'form-text text-warning';
      } else {
        input.required = false;
        texto.textContent = 'Deixe em branco para manter a data atual.';
        texto.className = 'form-text text-muted';
      }
    }
  }

  sel.addEventListener('change', atualizar);
  atualizar();
})();
</script>

<!-- Modal Estender -->
<div class="modal fade" id="modalEstender" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/licencas/<?= $licenca['id'] ?>/estender">
        <div class="modal-header"><h5 class="modal-title">Estender validade</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label">Adicionar quantos dias?</label>
          <input type="number" name="dias" class="form-control" value="30" min="1" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-accent btn-sm">Estender</button>
        </div>
      </form>
    </div>
  </div>
</div>
