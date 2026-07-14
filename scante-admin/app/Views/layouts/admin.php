<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?> — Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --primary:#0F2A3D; --accent:#00D67A; }
  body  { background:#f4f6f9; font-size:.92rem; }
  .sidebar { width:240px; min-height:100vh; background:var(--primary); position:fixed; top:0; left:0; z-index:100; }
  .sidebar .brand { padding:24px 20px 16px; color:#fff; font-size:1.4rem; font-weight:700; border-bottom:1px solid rgba(255,255,255,.1); }
  .sidebar .brand span { color:var(--accent); }
  .sidebar .nav-link { color:rgba(255,255,255,.7); padding:10px 20px; border-radius:0; display:flex; align-items:center; gap:10px; }
  .sidebar .nav-link:hover, .sidebar .nav-link.active { color:#fff; background:rgba(255,255,255,.1); }
  .sidebar .nav-section { padding:16px 20px 6px; font-size:.7rem; text-transform:uppercase; color:rgba(255,255,255,.35); letter-spacing:1px; }
  .main { margin-left:240px; padding:24px; }
  .topbar { background:#fff; border-bottom:1px solid #e5e7eb; padding:12px 24px; margin-left:240px; position:sticky; top:0; z-index:99; display:flex; align-items:center; justify-content:space-between; }
  .badge-ativa    { background:#d1fae5; color:#065f46; }
  .badge-trial    { background:#fef3c7; color:#92400e; }
  .badge-expirada { background:#fee2e2; color:#991b1b; }
  .badge-revogada { background:#f3f4f6; color:#6b7280; }
  .badge-pendente { background:#dbeafe; color:#1e40af; }
  .card { border:none; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.07); }
  .stat-card { padding:20px; }
  .stat-card .value { font-size:2rem; font-weight:700; color:var(--primary); }
  .stat-card .label { color:#6b7280; font-size:.82rem; }
  .btn-accent { background:var(--accent); color:var(--primary); font-weight:600; border:none; }
  .btn-accent:hover { background:#00b866; color:var(--primary); }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="brand">Scan<span>TE</span></div>
  <nav class="mt-2">
    <div class="nav-section">Principal</div>
    <a href="<?= APP_URL ?>/admin" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>

    <div class="nav-section">Clientes</div>
    <a href="<?= APP_URL ?>/admin/empresas" class="nav-link"><i class="bi bi-building"></i> Empresas</a>
    <a href="<?= APP_URL ?>/admin/licencas" class="nav-link"><i class="bi bi-key"></i> Licenças</a>
    <a href="<?= APP_URL ?>/admin/dispositivos" class="nav-link"><i class="bi bi-phone"></i> Dispositivos</a>
    <a href="<?= APP_URL ?>/admin/relay-licencas" class="nav-link"><i class="bi bi-hdd-network"></i> Licenças do Relay</a>

    <div class="nav-section">Financeiro</div>
    <a href="<?= APP_URL ?>/admin/pagamentos" class="nav-link"><i class="bi bi-cash-stack"></i> Pagamentos</a>

    <div class="nav-section">Configurações</div>
    <a href="<?= APP_URL ?>/admin/configuracoes" class="nav-link"><i class="bi bi-sliders"></i> Gateways de Pagamento</a>

    <div class="nav-section">Suporte</div>
    <a href="<?= APP_URL ?>/admin/manual" class="nav-link" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Manual do App</a>

    <div class="nav-section">Conta</div>
    <a href="<?= APP_URL ?>/logout" class="nav-link"><i class="bi bi-box-arrow-right"></i> Sair</a>
  </nav>
</div>

<!-- Topbar -->
<div class="topbar">
  <div class="text-muted"><?= APP_NAME ?></div>
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-person-circle fs-5"></i>
    <span><?= htmlspecialchars(\App\Core\Auth::nome()) ?></span>
    <span class="badge bg-danger">Admin</span>
  </div>
</div>

<!-- Conteúdo -->
<div class="main">
  <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
