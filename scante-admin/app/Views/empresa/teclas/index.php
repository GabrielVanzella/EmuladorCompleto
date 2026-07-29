<?php
$barrasJson   = htmlspecialchars(json_encode($barras), ENT_QUOTES);
$catalogoJson = htmlspecialchars(json_encode($catalogo), ENT_QUOTES);
?>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
  <?= $flash['message'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-1">
  <h4 class="fw-bold mb-0">Teclas do app</h4>
  <button type="button" class="btn btn-accent" id="btn-salvar"><i class="bi bi-check-lg me-1"></i> Salvar teclas</button>
</div>
<p class="text-muted mb-4" style="font-size:.88rem">
  Monte as barras de botões que aparecem no terminal. Vale pra <strong>todos os coletores da sua empresa</strong>.
  Máximo de <?= $maxBars ?> barras.
</p>

<div id="bars"></div>

<button type="button" class="btn btn-outline-secondary mt-2" id="btn-nova-barra">
  <i class="bi bi-plus-lg me-1"></i> Nova barra
</button>

<form method="post" action="<?= APP_URL ?>/empresa/teclas/salvar" id="form-teclas">
  <input type="hidden" name="barras_json" id="barras_json">
</form>

<!-- Modal: adicionar botão -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Adicionar botão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="catalogo"></div>
        <hr>
        <h6 class="fw-bold">Criar botão personalizado</h6>
        <div class="row g-2 align-items-end">
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem">Rótulo</label>
            <input type="text" class="form-control form-control-sm" id="c_label" maxlength="12" placeholder="Ex: OK">
          </div>
          <div class="col-md-4">
            <label class="form-label" style="font-size:.8rem">Tipo</label>
            <select class="form-select form-select-sm" id="c_tipo">
              <option value="TEXT">Texto livre</option>
              <option value="CTRL">Ctrl + letra</option>
              <option value="F">Tecla F (1-12)</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" style="font-size:.8rem">Valor</label>
            <input type="text" class="form-control form-control-sm" id="c_valor" placeholder="Ex: S / C / 5">
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-accent w-100" id="c_add">Criar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: editar botão -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar botão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label" style="font-size:.82rem">Rótulo</label>
          <input type="text" class="form-control" id="e_label" maxlength="12">
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:.82rem">Cor do botão</label>
          <div class="d-flex align-items-center gap-2">
            <input type="color" class="form-control form-control-color" id="e_color" value="#2E5C6E">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="e_color_padrao">
              <label class="form-check-label" for="e_color_padrao" style="font-size:.8rem">Cor padrão</label>
            </div>
          </div>
        </div>
        <div class="text-muted" style="font-size:.8rem">Ação: <code id="e_action"></code></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger me-auto" id="e_remove"><i class="bi bi-trash"></i> Remover</button>
        <button type="button" class="btn btn-accent" id="e_save">Salvar</button>
      </div>
    </div>
  </div>
</div>

<style>
  .bar-card { border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:12px; background:#fff; }
  .bar-head { display:flex; align-items:center; gap:6px; margin-bottom:8px; }
  .bar-title { font-weight:600; font-size:.85rem; color:#374151; margin-right:auto; }
  .chip { display:inline-flex; align-items:center; gap:6px; color:#fff; border:none; border-radius:6px;
          padding:6px 10px; margin:3px; font-size:.82rem; cursor:pointer; }
  .chip small { opacity:.7; font-weight:400; }
  .cat-btn { color:#fff; background:#2E5C6E; border:none; border-radius:6px; padding:5px 10px; margin:3px; font-size:.82rem; cursor:pointer; }
  .cat-sec { font-size:.72rem; text-transform:uppercase; color:#9ca3af; letter-spacing:1px; margin:12px 0 4px; }
</style>

<script>
const CAT = JSON.parse('<?= $catalogoJson ?>');
const MAX_BARS = <?= (int)$maxBars ?>;
let bars = JSON.parse('<?= $barrasJson ?>');
let addTargetBar = null;   // índice da barra que o modal "adicionar" vai preencher
let editRef = null;        // {bar, idx} do botão em edição

const DEF_COLOR = '#2E5C6E';
function el(id){ return document.getElementById(id); }

// -------- render --------
function render(){
  const cont = el('bars');
  cont.innerHTML = '';
  bars.forEach((barra, bi) => {
    const card = document.createElement('div');
    card.className = 'bar-card';

    const head = document.createElement('div');
    head.className = 'bar-head';
    head.innerHTML = `<span class="bar-title">Barra ${bi+1}</span>`;
    head.appendChild(iconBtn('bi-arrow-up',    () => moveBar(bi, -1), bi === 0));
    head.appendChild(iconBtn('bi-arrow-down',  () => moveBar(bi, +1), bi === bars.length-1));
    head.appendChild(iconBtn('bi-trash text-danger', () => delBar(bi), bars.length <= 1));
    card.appendChild(head);

    barra.forEach((b, idx) => {
      const chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'chip';
      chip.style.background = normColor(b.color) || DEF_COLOR;
      chip.innerHTML = `${escapeHtml(b.label)} <small>${escapeHtml(descreve(b.action))}</small>`;
      chip.addEventListener('click', () => openEdit(bi, idx));
      card.appendChild(chip);
    });

    const add = document.createElement('button');
    add.type = 'button';
    add.className = 'btn btn-sm btn-outline-secondary';
    add.style.margin = '3px';
    add.innerHTML = '<i class="bi bi-plus"></i> botão';
    add.addEventListener('click', () => openAdd(bi));
    card.appendChild(add);

    cont.appendChild(card);
  });
  el('btn-nova-barra').style.display = bars.length >= MAX_BARS ? 'none' : '';
}

function iconBtn(icon, onClick, disabled){
  const b = document.createElement('button');
  b.type = 'button';
  b.className = 'btn btn-sm btn-light';
  b.innerHTML = `<i class="bi ${icon}"></i>`;
  b.disabled = !!disabled;
  b.addEventListener('click', onClick);
  return b;
}

// -------- barras --------
function moveBar(i, dir){ const j=i+dir; if(j<0||j>=bars.length) return; [bars[i],bars[j]]=[bars[j],bars[i]]; render(); }
function delBar(i){ if(bars.length<=1) return; if(confirm('Remover a barra '+(i+1)+'?')){ bars.splice(i,1); render(); } }
el('btn-nova-barra').addEventListener('click', () => { if(bars.length<MAX_BARS){ bars.push([]); render(); } });

// -------- adicionar --------
function openAdd(bi){
  addTargetBar = bi;
  const box = el('catalogo');
  box.innerHTML = '';
  Object.entries(CAT).forEach(([sec, botoes]) => {
    const h = document.createElement('div'); h.className='cat-sec'; h.textContent=sec; box.appendChild(h);
    botoes.forEach(b => {
      const btn = document.createElement('button');
      btn.type='button'; btn.className='cat-btn'; btn.textContent=b.label;
      btn.title = b.action;
      btn.addEventListener('click', () => { addBtn(bi, {label:b.label, action:b.action, color:''}); });
      box.appendChild(btn);
    });
  });
  bootstrap.Modal.getOrCreateInstance(el('modalAdd')).show();
}
function addBtn(bi, btn){ bars[bi].push(btn); render(); }

el('c_add').addEventListener('click', () => {
  const label = el('c_label').value.trim();
  const tipo  = el('c_tipo').value;
  const valor = el('c_valor').value.trim();
  if(!label || !valor){ alert('Preencha rótulo e valor.'); return; }
  let action;
  if(tipo==='TEXT') action = 'TEXT:'+valor;
  else if(tipo==='CTRL') action = 'CTRL_'+valor[0].toUpperCase();
  else action = 'F'+Math.min(12, Math.max(1, parseInt(valor)||1));
  addBtn(addTargetBar, {label, action, color:''});
  el('c_label').value=''; el('c_valor').value='';
});

// -------- editar --------
function openEdit(bi, idx){
  editRef = {bar:bi, idx};
  const b = bars[bi][idx];
  el('e_label').value = b.label;
  el('e_action').textContent = b.action;
  const c = normColor(b.color);
  el('e_color_padrao').checked = !c;
  el('e_color').value = to6(c || DEF_COLOR);
  el('e_color').disabled = !c;
  bootstrap.Modal.getOrCreateInstance(el('modalEdit')).show();
}
el('e_color_padrao').addEventListener('change', () => { el('e_color').disabled = el('e_color_padrao').checked; });
el('e_save').addEventListener('click', () => {
  const b = bars[editRef.bar][editRef.idx];
  b.label = el('e_label').value.trim() || b.label;
  b.color = el('e_color_padrao').checked ? '' : el('e_color').value;
  bootstrap.Modal.getInstance(el('modalEdit')).hide();
  render();
});
el('e_remove').addEventListener('click', () => {
  bars[editRef.bar].splice(editRef.idx, 1);
  bootstrap.Modal.getInstance(el('modalEdit')).hide();
  render();
});

// -------- salvar --------
el('btn-salvar').addEventListener('click', () => {
  el('barras_json').value = JSON.stringify(bars);
  el('form-teclas').submit();
});

// -------- helpers --------
function normColor(c){ c=(c||'').trim(); return /^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/.test(c) ? c : ''; }
function to6(c){ // #AARRGGBB -> #RRGGBB (color input só aceita 6)
  if(c.length===9) return '#'+c.slice(3);
  return c.length===7 ? c : '#2E5C6E';
}
function descreve(a){
  if(a.startsWith('TEXT:')) return '"'+a.slice(5)+'"';
  if(a.startsWith('CTRL_')) return 'Ctrl+'+a.slice(5);
  return a;
}
function escapeHtml(s){ return (s||'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

render();
</script>
