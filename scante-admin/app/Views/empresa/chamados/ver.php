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
  <a href="<?= APP_URL ?>/empresa/chamados" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0">Chamado #<?= $chamado['id'] ?></h4>
  <span class="badge <?= $cls ?> ms-1"><?= $label ?></span>
</div>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="fw-semibold mb-1"><?= htmlspecialchars($chamado['assunto']) ?></h5>
    <div class="text-muted" style="font-size:.85rem">
      <i class="bi bi-key me-1"></i>
      <?php if ($chamado['licenca_chave']): ?>
        <code><?= $chamado['licenca_chave'] ?></code>
      <?php else: ?>
        Assunto geral
      <?php endif; ?>
      · Aberto em <?= date('d/m/Y H:i', strtotime($chamado['criado_em'])) ?>
    </div>
  </div>
</div>

<!-- Conversa -->
<div class="card mb-3">
  <div class="card-body">
    <?php foreach ($mensagens as $m): ?>
      <?php $daEmpresa = $m['autor_tipo'] === 'empresa'; ?>
      <div class="d-flex mb-3 <?= $daEmpresa ? 'justify-content-end' : 'justify-content-start' ?>">
        <div style="max-width:80%">
          <div class="p-3 rounded-3 <?= $daEmpresa ? 'bg-light' : 'text-white' ?>"
               style="<?= $daEmpresa ? '' : 'background:#0F2A3D' ?>">
            <div class="fw-semibold mb-1" style="font-size:.8rem">
              <?= $daEmpresa ? '<i class="bi bi-building me-1"></i>' : '<i class="bi bi-headset me-1"></i> Equipe ScanTE — ' ?>
              <?= htmlspecialchars($m['autor_nome'] ?? '') ?>
            </div>
            <div style="white-space:pre-wrap"><?= htmlspecialchars($m['mensagem']) ?></div>
          </div>
          <div class="text-muted mt-1 <?= $daEmpresa ? 'text-end' : '' ?>" style="font-size:.72rem">
            <?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Responder -->
<?php if ($chamado['status'] !== 'resolvido'): ?>
<div class="card">
  <div class="card-body">
    <form method="post" action="<?= APP_URL ?>/empresa/chamados/<?= $chamado['id'] ?>/responder">
      <label class="form-label fw-semibold">Responder</label>
      <textarea name="mensagem" class="form-control mb-2" rows="3" required placeholder="Escreva sua resposta..."></textarea>
      <button type="submit" class="btn btn-accent"><i class="bi bi-send me-1"></i> Enviar</button>
    </form>
  </div>
</div>
<?php else: ?>
<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-1"></i> Este chamado foi marcado como resolvido pela equipe.</div>
<?php endif; ?>
