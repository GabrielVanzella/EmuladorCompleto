<?php if (!empty($erro)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= APP_URL ?>/empresa/chamados" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h4 class="fw-bold mb-0">Abrir chamado</h4>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= APP_URL ?>/empresa/chamados/novo">

      <div class="mb-3">
        <label class="form-label fw-semibold">Sobre qual licença?</label>
        <select name="licenca_id" class="form-select">
          <option value="geral">Assunto geral (não é de uma licença específica)</option>
          <?php foreach ($licencas as $l): ?>
            <option value="<?= $l['id'] ?>"><?= $l['chave'] ?> — <?= ucfirst($l['tipo']) ?> (<?= ucfirst($l['status']) ?>)</option>
          <?php endforeach; ?>
        </select>
        <div class="form-text">Escolher a licença ajuda nossa equipe a te atender mais rápido.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Assunto</label>
        <input type="text" name="assunto" class="form-control" maxlength="200"
               placeholder="Ex: Coletor não conecta no Protheus" required
               value="<?= htmlspecialchars($_POST['assunto'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Descreva o problema ou dúvida</label>
        <textarea name="mensagem" class="form-control" rows="6" required
                  placeholder="Conte com detalhes o que está acontecendo..."><?= htmlspecialchars($_POST['mensagem'] ?? '') ?></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-accent"><i class="bi bi-send me-1"></i> Enviar chamado</button>
        <a href="<?= APP_URL ?>/empresa/chamados" class="btn btn-outline-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
