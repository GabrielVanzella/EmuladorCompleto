<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ScanTE — Comprar Licenças</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --primary:#0F2A3D; --accent:#00D67A; }
  body { background: linear-gradient(135deg, #0F2A3D 0%, #1a4a6e 100%); min-height: 100vh; font-size: .93rem; }
  .checkout-wrap { max-width: 480px; margin: 0 auto; padding: 40px 16px 60px; }
  .checkout-brand { text-align: center; color: #fff; margin-bottom: 32px; }
  .checkout-brand .logo { font-size: 2rem; font-weight: 800; }
  .checkout-brand .logo span { color: var(--accent); }
  .checkout-brand p { opacity: .65; font-size: .88rem; margin-top: 6px; }
  .checkout-card { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.3); overflow: hidden; }
  .checkout-card-header { background: var(--primary); padding: 24px 28px 20px; color: #fff; }
  .checkout-card-header h5 { font-weight: 700; margin: 0; font-size: 1.1rem; }
  .checkout-card-header p { opacity: .65; font-size: .83rem; margin: 4px 0 0; }
  .checkout-card-body { padding: 28px; }
  .btn-pay { background: var(--accent); color: var(--primary); font-weight: 700; border: none; padding: 14px; font-size: 1rem; border-radius: 12px; width: 100%; transition: background .15s; }
  .btn-pay:hover { background: #00b866; }
  .divider { display: flex; align-items: center; gap: 10px; color: #94a3b8; font-size: .8rem; margin: 18px 0; }
  .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
  .form-label { font-weight: 600; font-size: .85rem; color: #374151; }
  .form-control, .form-select { border-radius: 10px; border-color: #e2e8f0; font-size: .9rem; }
  .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,214,122,.15); }
  .badge-device { background: #f0fdf9; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 8px 14px; font-size: .83rem; }
  .security-note { background: #f8fafc; border-radius: 10px; padding: 12px 16px; font-size: .78rem; color: #64748b; }
</style>
</head>
<body>
<div class="checkout-wrap">
  <div class="checkout-brand">
    <div class="logo">Scan<span>TE</span></div>
    <p>Compre licenças ScanTE para sua equipe</p>
  </div>
  <?= $content ?>
  <div class="text-center mt-4" style="color:rgba(255,255,255,.35);font-size:.75rem">
    © <?= date('Y') ?> ScanTE · Todos os direitos reservados
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
