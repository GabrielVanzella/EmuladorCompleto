<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manual do ScanTE</title>
<style>
  /* ── Variáveis ── */
  :root {
    --primary: #0F2A3D;
    --accent:  #00D67A;
    --text:    #1a2733;
    --muted:   #64748b;
    --border:  #e2e8f0;
    --bg-alt:  #f8fafc;
  }

  /* ── Reset & Base ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { font-size: 15px; }
  body {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: var(--text);
    background: #eef1f5;
    line-height: 1.7;
  }

  /* ── Barra superior (só tela) ── */
  .print-bar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--primary);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 32px;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.25);
  }
  .print-bar .brand { font-size: 1.2rem; font-weight: 700; }
  .print-bar .brand span { color: var(--accent); }
  .print-bar .actions { display: flex; gap: 10px; align-items: center; }
  .btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    color: rgba(255,255,255,.75); text-decoration: none;
    font-size: .88rem; padding: 6px 12px; border-radius: 6px;
    transition: background .15s;
  }
  .btn-back:hover { background: rgba(255,255,255,.12); color: #fff; }
  .btn-pdf {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--accent); color: var(--primary);
    font-weight: 700; font-size: .88rem; border: none;
    padding: 8px 18px; border-radius: 8px; cursor: pointer;
    transition: background .15s;
  }
  .btn-pdf:hover { background: #00b866; }

  /* ── Container do documento ── */
  .doc-wrap {
    max-width: 860px;
    margin: 36px auto 60px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,.10);
    overflow: hidden;
  }

  /* ── Capa ── */
  .cover {
    background: linear-gradient(135deg, var(--primary) 60%, #1a4a6e 100%);
    padding: 56px 56px 48px;
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .cover::after {
    content: '';
    position: absolute;
    right: -60px; bottom: -60px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(0,214,122,.08);
  }
  .cover-badge {
    display: inline-block;
    background: rgba(0,214,122,.18);
    color: var(--accent);
    font-size: .75rem; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase;
    padding: 4px 12px; border-radius: 99px; margin-bottom: 20px;
  }
  .cover h1 {
    font-size: 2.4rem; font-weight: 800;
    line-height: 1.2; margin-bottom: 10px;
  }
  .cover h1 span { color: var(--accent); }
  .cover .tagline {
    font-size: 1rem; opacity: .75; margin-bottom: 28px;
    max-width: 480px;
  }
  .cover-meta {
    display: flex; gap: 24px; flex-wrap: wrap;
    font-size: .82rem; opacity: .6;
  }
  .cover-meta span { display: flex; align-items: center; gap: 5px; }

  /* ── Corpo do documento ── */
  .doc-body { padding: 48px 56px 56px; }

  /* ── Legenda ── */
  .legend {
    background: var(--bg-alt);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 20px;
    font-size: .84rem;
    color: var(--muted);
    margin-bottom: 40px;
    display: flex; gap: 20px; flex-wrap: wrap; align-items: center;
  }
  .legend strong { color: var(--text); }

  /* ── Sumário ── */
  .toc {
    background: var(--bg-alt);
    border-left: 4px solid var(--accent);
    border-radius: 0 10px 10px 0;
    padding: 24px 28px;
    margin-bottom: 48px;
  }
  .toc h2 { font-size: 1rem; font-weight: 700; margin-bottom: 14px; color: var(--primary); }
  .toc ol { padding-left: 20px; }
  .toc li { margin-bottom: 5px; font-size: .88rem; }
  .toc a { color: var(--primary); text-decoration: none; }
  .toc a:hover { color: var(--accent); text-decoration: underline; }

  /* ── Seções ── */
  .section { margin-bottom: 48px; }
  .section-header {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 18px; padding-bottom: 12px;
    border-bottom: 2px solid var(--border);
  }
  .section-num {
    background: var(--primary); color: var(--accent);
    font-size: .78rem; font-weight: 800;
    padding: 3px 9px; border-radius: 6px;
    letter-spacing: .5px;
    flex-shrink: 0;
  }
  .section-header h2 {
    font-size: 1.25rem; font-weight: 700;
    color: var(--primary); margin: 0;
  }
  .status-tag {
    margin-left: auto; font-size: .75rem; font-weight: 600;
    padding: 2px 8px; border-radius: 99px; flex-shrink: 0;
  }
  .tag-ok   { background: #d1fae5; color: #065f46; }
  .tag-dev  { background: #fef3c7; color: #92400e; }

  .subsection { margin: 24px 0 8px; }
  .subsection h3 {
    font-size: 1rem; font-weight: 700;
    color: var(--primary); margin-bottom: 10px;
    padding-left: 12px;
    border-left: 3px solid var(--accent);
  }
  .subsection h4 {
    font-size: .9rem; font-weight: 700;
    color: var(--muted); margin: 16px 0 8px;
  }

  p { margin-bottom: 12px; }
  p:last-child { margin-bottom: 0; }

  /* ── Fluxo de uso ── */
  .flow {
    background: var(--bg-alt);
    border-radius: 10px;
    padding: 16px 20px 16px 36px;
    margin: 12px 0;
  }
  .flow li { margin-bottom: 6px; font-size: .9rem; }

  /* ── Tabelas ── */
  .data-table {
    width: 100%; border-collapse: collapse;
    margin: 12px 0; font-size: .86rem;
  }
  .data-table thead tr {
    background: var(--primary); color: #fff;
  }
  .data-table thead th {
    padding: 10px 14px; text-align: left;
    font-weight: 600; font-size: .8rem;
    letter-spacing: .3px;
  }
  .data-table tbody tr:nth-child(even) { background: var(--bg-alt); }
  .data-table tbody tr:hover { background: #e9f5fe; }
  .data-table td { padding: 9px 14px; border-bottom: 1px solid var(--border); vertical-align: top; }
  .data-table td:first-child { font-weight: 600; white-space: nowrap; }

  /* ── Callout / Nota ── */
  .callout {
    border-left: 4px solid var(--accent);
    background: #f0fdf9;
    border-radius: 0 8px 8px 0;
    padding: 14px 18px;
    margin: 14px 0;
    font-size: .88rem;
    color: #0d5c3a;
  }
  .callout.warn {
    border-color: #f59e0b;
    background: #fffbeb;
    color: #78350f;
  }

  /* ── Inline code ── */
  code {
    background: #f1f5f9; color: #0f5c8a;
    font-size: .84em; padding: 1px 5px;
    border-radius: 4px; font-family: 'Consolas', monospace;
  }

  /* ── Glossário ── */
  .glossary dt { font-weight: 700; color: var(--primary); margin-top: 10px; }
  .glossary dd { margin-left: 16px; color: var(--muted); font-size: .88rem; }

  /* ── Rodapé ── */
  .doc-footer {
    background: var(--bg-alt);
    border-top: 1px solid var(--border);
    padding: 20px 56px;
    text-align: center;
    font-size: .78rem;
    color: var(--muted);
  }

  /* ══ PRINT ══ */
  @media print {
    body { background: #fff; font-size: 13px; }
    .print-bar { display: none; }
    .doc-wrap { margin: 0; border-radius: 0; box-shadow: none; max-width: 100%; }
    .cover { padding: 40px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .doc-body { padding: 32px 40px; }
    .data-table thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .data-table tbody tr:nth-child(even) { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .section { page-break-inside: avoid; }
    .section-header { page-break-after: avoid; }
    .toc { page-break-after: always; }
    a { color: inherit; text-decoration: none; }
    .doc-footer { page-break-inside: avoid; }
  }
</style>
</head>
<body>

<!-- Barra (só tela) -->
<div class="print-bar">
  <div class="brand">Scan<span>TE</span> <span style="font-weight:300;opacity:.5;font-size:.9rem">| Manual</span></div>
  <div class="actions">
    <a href="<?= APP_URL ?>/admin" class="btn-back">
      ← Voltar ao painel
    </a>
    <button class="btn-pdf" onclick="window.print()">
      ⬇ Baixar PDF
    </button>
  </div>
</div>

<div class="doc-wrap">
  <?= $content ?>
</div>

</body>
</html>
