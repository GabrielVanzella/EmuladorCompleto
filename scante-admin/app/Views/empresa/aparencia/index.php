<?php
$cores = $tema['cores'] ?? [];
$cab   = $tema['cabecalho'] ?? ['mostrar' => false, 'nome' => ''];
$val   = fn($k, $def = '') => htmlspecialchars($cores[$k] ?? $def);
// Para cores opcionais (campo/status_fundo): vazio = usar padrão
$campoVazio  = ($cores['campo'] ?? '') === '';
$stbgVazio   = ($cores['status_fundo'] ?? '') === '';
$presetsJson = htmlspecialchars(json_encode($presets), ENT_QUOTES);
?>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<h4 class="fw-bold mb-1">Aparência do app</h4>
<p class="text-muted mb-4" style="font-size:.88rem">
  Personalize o visual do terminal e o cabeçalho. Vale pra <strong>todos os coletores da sua empresa</strong>
  e é aplicado automaticamente — o funcionário não precisa configurar nada.
</p>

<form method="post" action="<?= APP_URL ?>/empresa/aparencia/salvar" enctype="multipart/form-data">
<div class="row g-4">

  <!-- Configuração -->
  <div class="col-lg-7">
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Tema base</h6>
        <div class="d-flex flex-wrap gap-2 mb-2" id="presets">
          <?php
            $nomes = ['classico'=>'Clássico (verde)','escuro'=>'Moderno escuro','claro'=>'Moderno claro','alto_contraste'=>'Alto contraste'];
            foreach ($nomes as $pk => $pn):
          ?>
            <button type="button" class="btn btn-sm <?= ($tema['preset'] ?? '')===$pk ? 'btn-accent' : 'btn-outline-secondary' ?>"
                    data-preset="<?= $pk ?>"><?= $pn ?></button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="preset" id="preset" value="<?= htmlspecialchars($tema['preset'] ?? 'classico') ?>">
        <div class="form-text">Escolha um ponto de partida e ajuste as cores abaixo se quiser.</div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Cores do terminal</h6>

        <div class="row g-3">
          <div class="col-6 col-md-4">
            <label class="form-label" style="font-size:.82rem">Texto</label>
            <input type="color" class="form-control form-control-color w-100 js-cor" name="cor_texto" value="<?= $val('texto','#33FF33') ?>">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label" style="font-size:.82rem">Fundo</label>
            <input type="color" class="form-control form-control-color w-100 js-cor" name="cor_fundo" value="<?= $val('fundo','#000000') ?>">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label" style="font-size:.82rem">Campo (entrada)</label>
            <input type="color" class="form-control form-control-color w-100 js-cor-opt" id="pick_campo"
                   value="<?= $campoVazio ? '#004400' : $val('campo') ?>" <?= $campoVazio ? 'disabled' : '' ?>>
            <input type="hidden" name="cor_campo" id="cor_campo" value="<?= $campoVazio ? '' : $val('campo') ?>">
            <div class="form-check mt-1">
              <input class="form-check-input js-padrao" type="checkbox" id="campo_padrao" data-target="campo" <?= $campoVazio ? 'checked' : '' ?>>
              <label class="form-check-label" for="campo_padrao" style="font-size:.75rem">Usar padrão</label>
            </div>
          </div>
        </div>

        <hr class="my-3">
        <h6 class="fw-bold mb-3" style="font-size:.9rem">Barra do topo (status)</h6>
        <div class="row g-3">
          <div class="col-6 col-md-4">
            <label class="form-label" style="font-size:.82rem">Texto do topo</label>
            <input type="color" class="form-control form-control-color w-100 js-cor" name="cor_status_texto" value="<?= $val('status_texto','#FFFFFF') ?>">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label" style="font-size:.82rem">Fundo do topo</label>
            <input type="color" class="form-control form-control-color w-100 js-cor-opt" id="pick_status_fundo"
                   value="<?= $stbgVazio ? '#0F2A3D' : $val('status_fundo') ?>" <?= $stbgVazio ? 'disabled' : '' ?>>
            <input type="hidden" name="cor_status_fundo" id="cor_status_fundo" value="<?= $stbgVazio ? '' : $val('status_fundo') ?>">
            <div class="form-check mt-1">
              <input class="form-check-input js-padrao" type="checkbox" id="status_fundo_padrao" data-target="status_fundo" <?= $stbgVazio ? 'checked' : '' ?>>
              <label class="form-check-label" for="status_fundo_padrao" style="font-size:.75rem">Usar padrão</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Cabeçalho com a marca</h6>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="cabecalho_mostrar" name="cabecalho_mostrar" value="1" <?= !empty($cab['mostrar']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="cabecalho_mostrar">Mostrar logo/nome no topo do app</label>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:.82rem">Nome exibido</label>
          <input type="text" class="form-control" name="cabecalho_nome" id="cabecalho_nome" maxlength="40"
                 value="<?= htmlspecialchars($cab['nome'] ?? '') ?>" placeholder="Ex: <?= htmlspecialchars($empresa['nome']) ?>">
        </div>
        <div class="mb-2">
          <label class="form-label" style="font-size:.82rem">Logo (PNG/JPG, fundo transparente de preferência)</label>
          <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/webp">
        </div>
        <?php if ($logoUrl): ?>
          <div class="d-flex align-items-center gap-3 mt-2">
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="logo" style="height:40px;background:#0F2A3D;padding:4px;border-radius:6px">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remover_logo" name="remover_logo" value="1">
              <label class="form-check-label text-danger" for="remover_logo" style="font-size:.82rem">Remover logo atual</label>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg me-1"></i> Salvar aparência</button>
  </div>

  <!-- Prévia -->
  <div class="col-lg-5">
    <div class="card position-sticky" style="top:80px">
      <div class="card-body">
        <h6 class="fw-bold mb-3">Prévia</h6>
        <div style="border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
          <!-- topo -->
          <div id="pv-top" style="display:flex;align-items:center;gap:8px;padding:8px 10px">
            <img id="pv-logo" src="<?= htmlspecialchars($logoUrl ?? '') ?>" style="height:22px;<?= $logoUrl ? '' : 'display:none' ?>">
            <span id="pv-nome" style="font-weight:600;font-size:.8rem"><?= htmlspecialchars($cab['nome'] ?? '') ?></span>
            <span style="margin-left:auto;font-size:.72rem;opacity:.85">Conectado</span>
          </div>
          <!-- terminal -->
          <div id="pv-term" style="font-family:'Courier New',monospace;font-size:.8rem;line-height:1.35;padding:12px;min-height:180px;white-space:pre">
<span>TOTVS Varejo ORACLE</span>
<span>Data Base: 14/07/26</span>

<span>Usuario: <span id="pv-campo">42424242</span></span>
<span>Senha:</span>
          </div>
        </div>
        <div class="form-text mt-2">É só uma amostra — o app aplica as mesmas cores na tela real.</div>
      </div>
    </div>
  </div>
</div>
</form>

<script>
const PRESETS = JSON.parse('<?= $presetsJson ?>');

function el(id){ return document.getElementById(id); }

function corOf(name){
  const opt = { campo: 'cor_campo', status_fundo: 'cor_status_fundo' };
  if (opt[name]) return el(opt[name]).value;               // hidden (pode ser "")
  const inp = document.querySelector(`[name=cor_${name}]`);
  return inp ? inp.value : '';
}

function atualizarPreview(){
  const texto = corOf('texto') || '#33FF33';
  const fundo = corOf('fundo') || '#000000';
  const campo = corOf('campo');            // "" = reverso (usa cor do texto)
  const stTx  = corOf('status_texto') || '#FFFFFF';
  const stBg  = corOf('status_fundo') || '#0F2A3D';

  const term = el('pv-term');
  term.style.background = fundo;
  term.style.color = texto;

  const top = el('pv-top');
  top.style.background = stBg;
  top.style.color = stTx;

  // campo de entrada: se tem cor, usa; senão simula reverso (fundo=texto)
  const c = el('pv-campo');
  c.style.background = campo || texto;
  c.style.color = campo ? texto : fundo;
  c.style.padding = '0 2px';

  // cabeçalho on/off
  top.style.display = el('cabecalho_mostrar').checked ? 'flex' : 'none';
  el('pv-nome').textContent = el('cabecalho_nome').value || '';
}

// Presets
document.querySelectorAll('#presets [data-preset]').forEach(btn => {
  btn.addEventListener('click', () => {
    const p = PRESETS[btn.dataset.preset];
    el('preset').value = btn.dataset.preset;
    document.querySelectorAll('#presets [data-preset]').forEach(b => {
      b.classList.toggle('btn-accent', b === btn);
      b.classList.toggle('btn-outline-secondary', b !== btn);
    });
    if (!p) return;
    document.querySelector('[name=cor_texto]').value = p.texto || '#33FF33';
    document.querySelector('[name=cor_fundo]').value = p.fundo || '#000000';
    document.querySelector('[name=cor_status_texto]').value = p.status_texto || '#FFFFFF';
    setOpt('campo', p.campo);
    setOpt('status_fundo', p.status_fundo);
    atualizarPreview();
  });
});

// Cores opcionais (campo / status_fundo) com "usar padrão"
function setOpt(target, hex){
  const chk = el(target + '_padrao');
  const pick = el('pick_' + target);
  const hidden = el('cor_' + target);
  const vazio = !hex;
  chk.checked = vazio;
  pick.disabled = vazio;
  if (!vazio) pick.value = hex;
  hidden.value = vazio ? '' : (hex || pick.value);
}
document.querySelectorAll('.js-padrao').forEach(chk => {
  chk.addEventListener('change', () => {
    const t = chk.dataset.target;
    const pick = el('pick_' + t), hidden = el('cor_' + t);
    pick.disabled = chk.checked;
    hidden.value = chk.checked ? '' : pick.value;
    atualizarPreview();
  });
});
document.querySelectorAll('.js-cor-opt').forEach(pick => {
  pick.addEventListener('input', () => {
    const t = pick.id.replace('pick_','');
    el('cor_' + t).value = pick.value;
    atualizarPreview();
  });
});

document.querySelectorAll('.js-cor').forEach(i => i.addEventListener('input', atualizarPreview));
['cabecalho_mostrar','cabecalho_nome'].forEach(id => el(id).addEventListener('input', atualizarPreview));
atualizarPreview();
</script>
