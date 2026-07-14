<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?> — Área da Empresa</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --primary:#0F2A3D; --accent:#00D67A; }
  body  { background:#f4f6f9; font-size:.92rem; }
  .sidebar { width:230px; min-height:100vh; background:var(--primary); position:fixed; top:0; left:0; z-index:100; }
  .sidebar .brand { padding:24px 20px 16px; color:#fff; font-size:1.4rem; font-weight:700; border-bottom:1px solid rgba(255,255,255,.1); }
  .sidebar .brand span { color:var(--accent); }
  .sidebar .nav-link { color:rgba(255,255,255,.7); padding:10px 20px; display:flex; align-items:center; gap:10px; }
  .sidebar .nav-link:hover, .sidebar .nav-link.active { color:#fff; background:rgba(255,255,255,.1); }
  .sidebar .empresa-nome { padding:10px 20px; color:rgba(255,255,255,.5); font-size:.78rem; }
  .main { margin-left:230px; padding:24px; }
  .topbar { background:#fff; border-bottom:1px solid #e5e7eb; padding:12px 24px; margin-left:230px; position:sticky; top:0; z-index:99; display:flex; align-items:center; justify-content:space-between; }
  .card  { border:none; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.07); }
  .badge-ativa    { background:#d1fae5; color:#065f46; }
  .badge-trial    { background:#fef3c7; color:#92400e; }
  .badge-expirada { background:#fee2e2; color:#991b1b; }
  .badge-revogada { background:#f3f4f6; color:#6b7280; }
  .btn-accent { background:var(--accent); color:var(--primary); font-weight:600; border:none; }
  .btn-accent:hover { background:#00b866; color:var(--primary); }
</style>
</head>
<body>

<div class="sidebar">
  <div class="brand">Scan<span>TE</span></div>
  <?php $emp = \App\Core\Auth::nome(); ?>
  <div class="empresa-nome"><i class="bi bi-building me-1"></i><?= htmlspecialchars($emp) ?></div>
  <nav>
    <a href="<?= APP_URL ?>/empresa" class="nav-link"><i class="bi bi-speedometer2"></i> Início</a>
    <a href="<?= APP_URL ?>/empresa/licencas" class="nav-link"><i class="bi bi-key"></i> Minhas Licenças</a>
    <a href="<?= APP_URL ?>/logout" class="nav-link mt-4"><i class="bi bi-box-arrow-right"></i> Sair</a>
  </nav>
</div>

<div class="topbar">
  <div class="text-muted"><?= APP_NAME ?></div>
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-building fs-5"></i>
    <span><?= htmlspecialchars(\App\Core\Auth::nome()) ?></span>
  </div>
</div>

<div class="main">
  <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
