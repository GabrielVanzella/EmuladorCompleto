<div class="relay-card text-center">
  <div class="relay-icon">
    <i class="bi bi-hdd-network text-white" style="font-size:2.2rem"></i>
  </div>

  <h4 class="fw-bold mb-2">ScanTE Relay</h4>
  <p class="text-muted mb-4">
    Um pequeno programa que roda no computador da sua empresa e mantém a sessão do coletor
    conectada ao seu sistema (Protheus, AS/400 e outros), mesmo quando a internet do Wi-Fi
    do coletor cai por alguns instantes. Enquanto a conexão volta, a tela só congela — nada
    é perdido, e a sessão reconecta sozinha.
  </p>

  <a href="<?= htmlspecialchars($downloadUrl) ?>" class="btn-download mb-2">
    <i class="bi bi-download me-2"></i>Baixar ScanTE Relay para Windows
  </a>
  <div class="text-muted mb-4" style="font-size:.78rem">Windows 7 ou superior · não precisa de administrador · ~6 MB</div>

  <div class="text-start">
    <h6 class="fw-bold mb-2">O que ele faz</h6>
    <div class="feature-item"><i class="bi bi-shield-check"></i><div>Segura a conexão do coletor mesmo se a internet cair por alguns segundos/minutos</div></div>
    <div class="feature-item"><i class="bi bi-arrow-repeat"></i><div>Reconecta automaticamente ao sistema assim que a internet volta, sem precisar reabrir a sessão</div></div>
    <div class="feature-item"><i class="bi bi-key"></i><div>Ativado com uma licença enviada pela equipe ScanTE (sem precisar de internet pra validar)</div></div>

    <h6 class="fw-bold mt-4 mb-2">Como instalar</h6>
    <div class="step-item"><div class="step-num">1</div><div>Baixe e execute o instalador acima (não precisa de permissão de administrador)</div></div>
    <div class="step-item"><div class="step-num">2</div><div>Na primeira vez que abrir, cole a chave de licença que a equipe ScanTE te enviou</div></div>
    <div class="step-item"><div class="step-num">3</div><div>Deixe o ScanTE Relay aberto enquanto usa o coletor</div></div>
  </div>

  <div class="security-note mt-4" style="background:#f8fafc;border-radius:10px;padding:12px 16px;font-size:.78rem;color:#64748b">
    Ainda não tem uma licença do relay? Escreva pra <strong>scante@scante.com.br</strong>.
  </div>
</div>
