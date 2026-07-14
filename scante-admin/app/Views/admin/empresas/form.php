<div class="d-flex align-items-center mb-4 gap-2">
  <a href="<?= APP_URL ?>/admin/empresas" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><?= $empresa ? 'Editar Empresa' : 'Nova Empresa' ?></h4>
</div>

<?php if ($erro): ?>
<div class="alert alert-danger"><?= $erro ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px">
  <div class="card-body">
    <form method="POST">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label fw-semibold">Nome da empresa *</label>
          <input type="text" name="nome" class="form-control"
            value="<?= htmlspecialchars($empresa['nome'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">CNPJ</label>
          <input type="text" name="cnpj" class="form-control"
            value="<?= htmlspecialchars($empresa['cnpj'] ?? '') ?>" placeholder="00.000.000/0001-00">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Telefone</label>
          <input type="text" name="telefone" class="form-control"
            value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label fw-semibold">E-mail *</label>
          <input type="email" name="email" class="form-control"
            value="<?= htmlspecialchars($empresa['email'] ?? '') ?>" required>
          <?php if (!$empresa): ?>
          <small class="text-muted">Este e-mail será o login de acesso da empresa.</small>
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Contato</label>
          <input type="text" name="contato" class="form-control"
            value="<?= htmlspecialchars($empresa['contato'] ?? '') ?>">
        </div>
        <?php if ($empresa): ?>
        <div class="col-12">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="ativo" value="1"
              <?= $empresa['ativo'] ? 'checked' : '' ?>>
            <label class="form-check-label">Empresa ativa</label>
          </div>
        </div>
        <?php endif; ?>
        <div class="col-12 d-flex gap-2 mt-2">
          <button type="submit" class="btn btn-accent">
            <i class="bi bi-check-lg me-1"></i><?= $empresa ? 'Salvar alterações' : 'Criar empresa' ?>
          </button>
          <a href="<?= APP_URL ?>/admin/empresas" class="btn btn-outline-secondary">Cancelar</a>
        </div>
      </div>
    </form>
  </div>
</div>
