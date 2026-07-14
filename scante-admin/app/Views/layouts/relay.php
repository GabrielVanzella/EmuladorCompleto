<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ScanTE Relay — Download</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --primary:#0F2A3D; --accent:#00D67A; }
  body { background: linear-gradient(135deg, #0F2A3D 0%, #1a4a6e 100%); min-height: 100vh; font-size: .95rem; }
  .relay-wrap { max-width: 720px; margin: 0 auto; padding: 48px 16px 60px; }
  .relay-brand { text-align: center; color: #fff; margin-bottom: 28px; }
  .relay-brand .logo { font-size: 2rem; font-weight: 800; }
  .relay-brand .logo span { color: var(--accent); }
  .relay-card { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.3); overflow: hidden; padding: 40px; }
  .relay-icon { width: 84px; height: 84px; border-radius: 22px; margin: 0 auto 20px; display:flex; align-items:center; justify-content:center; background:var(--primary); }
  .btn-download { background: var(--accent); color: var(--primary); font-weight: 700; border: none; padding: 16px; font-size: 1.05rem; border-radius: 12px; width: 100%; text-decoration:none; display:block; text-align:center; transition: background .15s; }
  .btn-download:hover { background: #00b866; color: var(--primary); }
  .feature-item { display:flex; gap:12px; padding: 10px 0; }
  .feature-item i { color: var(--accent); font-size: 1.2rem; flex-shrink:0; }
  .step-item { display:flex; gap:14px; align-items:flex-start; padding: 8px 0; }
  .step-num { width:26px; height:26px; border-radius:50%; background:#f0fdf9; color:var(--primary); font-weight:700; font-size:.8rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
</style>
</head>
<body>
<div class="relay-wrap">
  <div class="relay-brand">
    <div class="logo">Scan<span>TE</span></div>
    <p style="opacity:.65;font-size:.9rem;margin-top:6px">Relay — mantenha a sessão viva mesmo com a internet instável</p>
  </div>
  <?= $content ?>
  <div class="text-center mt-4" style="color:rgba(255,255,255,.35);font-size:.75rem">
    © <?= date('Y') ?> ScanTE · Todos os direitos reservados
  </div>
</div>
</body>
</html>
