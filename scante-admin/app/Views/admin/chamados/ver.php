<?php
$statusMap = [
  'aberto'       => ['Aberto', 'bg-primary'],
  'em_andamento' => ['Em andamento', 'bg-warning text-dark'],
  'resolvido'    => ['Resolvido', 'bg-success'],
];
[$label, $cls] = $statusMap[$chamado['status']] ?? ['—', 'bg-secondary'];
?>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= APP_URL ?>/admin/chamados" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0">Chamado #<?= $chamado['id'] ?></h4>
  <span class="badge <?= $cls ?> ms-1"><?= $label ?></span>
</div>

<div class="row g-3">
  <!-- Coluna esquerda: contexto + status -->
  <div class="col-lg-4 order-lg-2">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Informações</h6>
        <div class="mb-2" style="font-size:.85rem">
          <span class="text-muted">Empresa</span><br>
          <span class="fw-semibold"><?= htmlspecialchars($chamado['empresa_nome'] ?? '—') ?></span>
        </div>
        <div class="mb-2" style="font-size:.85rem">
          <span class="text-muted">Licença</span><br>
          <?php if ($chamado['licenca_chave']): ?>
            <code><?= $chamado['licenca_chave'] ?></code>
            <a href="<?= APP_URL ?>/admin/licencas" class="ms-1" style="font-size:.78rem">ver</a>
            <div class="text-muted" style="font-size:.78rem"><?= ucfirst($chamado['licenca_tipo'] ?? '') ?></div>
          <?php else: ?>
            <span class="text-muted">Assunto geral</span>
          <?php endif; ?>
        </div>
        <div class="mb-3" style="font-size:.85rem">
          <span class="text-muted">Aberto em</span><br>
          <?= date('d/m/Y H:i', strtotime($chamado['criado_em'])) ?>
        </div>

        <hr>
        <form method="post" action="<?= APP_URL ?>/admin/chamados/<?= $chamado['id'] ?>/status">
          <label class="form-label fw-semibold" style="font-size:.85rem">Alterar status</label>
          <div class="input-group input-group-sm">
            <select name="status" class="form-select">
              <option value="aberto"       <?= $chamado['status']==='aberto'?'selected':'' ?>>Aberto</option>
              <option value="em_andamento" <?= $chamado['status']==='em_andamento'?'selected':'' ?>>Em andamento</option>
              <option value="resolvido"    <?= $chamado['status']==='resolvido'?'selected':'' ?>>Resolvido</option>
            </select>
            <button class="btn btn-outline-secondary" type="submit">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Coluna direita: conversa -->
  <div class="col-lg-8 order-lg-1">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="fw-semibold mb-3"><?= htmlspecialchars($chamado['assunto']) ?></h5>
        <?php foreach ($mensagens as $m): ?>
          <?php $daEmpresa = $m['autor_tipo'] === 'empresa'; ?>
          <div class="d-flex mb-3 <?= $daEmpresa ? 'justify-content-start' : 'justify-content-end' ?>">
            <div style="max-width:80%">
              <div class="p-3 rounded-3 <?= $daEmpresa ? 'bg-light' : 'text-white' ?>"
                   style="<?= $daEmpresa ? '' : 'background:#00885a' ?>">
                <div class="fw-semibold mb-1" style="font-size:.8rem">
                  <?= $daEmpresa ? '<i class="bi bi-building me-1"></i>' : '<i class="bi bi-headset me-1"></i> ' ?>
                  <?= htmlspecialchars($m['autor_nome'] ?? '') ?>
                  <?= $daEmpresa ? '' : '<span class="opacity-75">(equipe)</span>' ?>
                </div>
                <div style="white-space:pre-wrap"><?= htmlspecialchars($m['mensagem']) ?></div>
              </div>
              <div class="text-muted mt-1 <?= $daEmpresa ? '' : 'text-end' ?>" style="font-size:.72rem">
                <?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="post" action="<?= APP_URL ?>/admin/chamados/<?= $chamado['id'] ?>/responder">
          <label class="form-label fw-semibold">Responder à empresa</label>
          <textarea name="mensagem" class="form-control mb-2" rows="4" required placeholder="Escreva a resposta..."></textarea>
          <button type="submit" class="btn btn-accent"><i class="bi bi-send me-1"></i> Enviar resposta</button>
        </form>
      </div>
    </div>
  </div>
</div>
