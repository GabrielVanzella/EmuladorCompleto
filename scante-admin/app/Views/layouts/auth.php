<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: linear-gradient(135deg, #0F2A3D 0%, #081722 100%); min-height: 100vh; }
  .card { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
  .brand { color: #fff; font-size: 2rem; font-weight: 700; letter-spacing: 1px; }
  .brand span { color: #00D67A; }
  .btn-primary { background: #00D67A; border-color: #00D67A; color: #0F2A3D; font-weight: 600; }
  .btn-primary:hover { background: #00b866; border-color: #00b866; color: #0F2A3D; }
</style>
</head>
<body class="d-flex align-items-center justify-content-center">
<div style="width:100%;max-width:420px;padding:20px">
  <div class="text-center mb-4">
    <div class="brand">Scan<span>TE</span></div>
    <small class="text-white-50">Painel de Gerenciamento</small>
  </div>
  <?= $content ?>
</div>
</body>
</html>
