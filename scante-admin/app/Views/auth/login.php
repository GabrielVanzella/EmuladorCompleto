<div class="card p-4">
  <h5 class="mb-4 fw-bold text-center">Entrar</h5>

  <?php if ($erro): ?>
  <div class="alert alert-danger py-2"><?= $erro ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/login">
    <div class="mb-3">
      <label class="form-label">E-mail</label>
      <input type="email" name="email" class="form-control" required autofocus>
    </div>
    <div class="mb-4">
      <label class="form-label">Senha</label>
      <input type="password" name="senha" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Entrar</button>
  </form>
</div>
