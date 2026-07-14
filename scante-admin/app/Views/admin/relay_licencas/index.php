<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'warning' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-4">
  <h4 class="fw-bold mb-0">Licenças do ScanTE Relay</h4>
  <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#modalGerar">
    <i class="bi bi-plus-lg me-1"></i>Gerar licença
  </button>
</div>

<p class="text-muted small">
  O ScanTE Relay é o programa que as empresas instalam pra manter a sessão Telnet viva quando a
  internet do coletor cai. Ele exige uma licença offline (sem precisar de internet) — gere aqui,
  copie o texto ou baixe o arquivo <code>.lic</code> e envie pro cliente colar/importar na tela do relay.
</p>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Cliente</th>
            <th>Serial</th>
            <th>Sessões</th>
            <th>Dispositivos</th>
            <th>Validade</th>
            <th>Gerada em</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($licencas as $l): $expirada = strtotime($l['expira_em']) < time(); ?>
          <tr>
            <td><?= htmlspecialchars($l['cliente']) ?></td>
            <td><code><?= htmlspecialchars($l['serial']) ?></code></td>
            <td><?= (int)$l['max_sessions'] === 0 ? 'Ilimitado' : (int)$l['max_sessions'] ?></td>
            <td><?= (int)$l['max_devices'] === 0 ? 'Ilimitado' : (int)$l['max_devices'] ?></td>
            <td class="<?= $expirada ? 'text-danger fw-semibold' : '' ?>">
              <?= date('d/m/Y', strtotime($l['expira_em'])) ?>
              <?= $expirada ? ' (expirada)' : '' ?>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($l['criada_em'])) ?></td>
            <td class="d-flex gap-1">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-ver-texto"
                data-texto="<?= htmlspecialchars($l['licenca_texto']) ?>" data-bs-toggle="modal" data-bs-target="#modalTexto">
                <i class="bi bi-eye"></i>
              </button>
              <a href="<?= APP_URL ?>/admin/relay-licencas/<?= $l['id'] ?>/baixar" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($licencas)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma licença de relay gerada ainda.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Gerar -->
<div class="modal fade" id="modalGerar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= APP_URL ?>/admin/relay-licencas/gerar">
        <div class="modal-header">
          <h5 class="modal-title">Gerar licença do Relay</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Cliente / Empresa</label>
              <input type="text" name="cliente" class="form-control" placeholder="Ex: Empresa ABC Ltda" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Sessões máximas <span class="text-muted fw-normal">(0 = ilimitado)</span></label>
              <input type="number" name="max_sessions" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dispositivos máximos <span class="text-muted fw-normal">(0 = ilimitado)</span></label>
              <input type="number" name="max_devices" class="form-control" value="1" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Validade</label>
              <input type="date" name="expira_em" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Versão suportada</label>
              <input type="text" name="release_suportado" class="form-control" value="1.0">
            </div>
            <div class="col-12">
              <label class="form-label">Endereço do servidor <span class="text-muted fw-normal">(opcional, só informativo)</span></label>
              <input type="text" name="server_host" class="form-control" placeholder="Ex: 192.168.0.103">
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

<!-- Modal ver texto da licença -->
<div class="modal fade" id="modalTexto" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Texto da licença</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Cole esse texto no campo "Chave de licença" na tela do ScanTE Relay.</p>
        <textarea id="textoLicenca" class="form-control font-monospace" style="font-size:.72rem" rows="6" readonly></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-accent btn-sm" id="btnCopiarTexto">Copiar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-ver-texto').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.getElementById('textoLicenca').value = btn.dataset.texto;
  });
});
document.getElementById('btnCopiarTexto').addEventListener('click', function () {
  const ta = document.getElementById('textoLicenca');
  ta.select();
  navigator.clipboard?.writeText(ta.value);
});
</script>
