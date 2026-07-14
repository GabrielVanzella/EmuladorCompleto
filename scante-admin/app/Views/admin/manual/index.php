<!-- CAPA -->
<div class="cover">
  <div class="cover-badge">Documentação oficial</div>
  <h1>Manual do <span>ScanTE</span></h1>
  <p class="tagline">Conecta · Sincroniza · Simplifica — Software de comunicação entre coletores de dados e sistemas corporativos via terminal Telnet.</p>
  <div class="cover-meta">
    <span>📄 Versão 1.0</span>
    <span>📅 <?= date('d/m/Y') ?></span>
    <span>🤖 Android</span>
  </div>
</div>

<!-- CORPO -->
<div class="doc-body">

  <!-- Legenda -->
  <div class="legend">
    <strong>Legenda de status:</strong>
    <span>✅ Funcional</span>
    <span>🔧 Em desenvolvimento</span>
  </div>

  <!-- SUMÁRIO -->
  <div class="toc">
    <h2>Sumário</h2>
    <ol>
      <li><a href="#s1">Visão geral</a></li>
      <li><a href="#s2">Tela de Licença</a></li>
      <li><a href="#s3">Tela de Sessões</a></li>
      <li><a href="#s4">Tela do Terminal</a></li>
      <li><a href="#s5">Configurações → Comunicação</a></li>
      <li><a href="#s6">Configurações → Tela</a></li>
      <li><a href="#s7">Opções gerais</a></li>
      <li><a href="#s8">Configurações → Emulação</a></li>
      <li><a href="#s9">Configurações → Dispositivos</a></li>
      <li><a href="#s10">Teclado personalizado ScanTE</a></li>
      <li><a href="#s11">Calculadora flutuante</a></li>
      <li><a href="#s12">Glossário rápido</a></li>
    </ol>
  </div>

  <!-- 1. Visão geral -->
  <div class="section" id="s1">
    <div class="section-header">
      <span class="section-num">01</span>
      <h2>Visão geral</h2>
    </div>
    <p>O ScanTE é um emulador de terminal <strong>Telnet</strong> para Android. Ele permite que coletores de dados (e celulares) se conectem a sistemas corporativos (Protheus/TOTVS, AS/400, entre outros) que funcionam por linha de comando / tela de texto, exibindo a tela do sistema e permitindo digitar e navegar como num terminal de verdade.</p>
    <p><strong>Fluxo básico de uso:</strong></p>
    <ol class="flow">
      <li>Abrir o app → tela de <strong>Licença</strong> (período de teste ou licença vitalícia)</li>
      <li><strong>Sessões</strong> → lista de servidores cadastrados</li>
      <li>Criar/abrir uma sessão → <strong>Terminal</strong> conectado ao sistema</li>
    </ol>
  </div>

  <!-- 2. Licença -->
  <div class="section" id="s2">
    <div class="section-header">
      <span class="section-num">02</span>
      <h2>Tela de Licença</h2>
      <span class="status-tag tag-ok">✅ Funcional</span>
    </div>
    <p>É a primeira tela ao abrir o app.</p>
    <table class="data-table">
      <thead><tr><th>Item</th><th>Para que serve</th></tr></thead>
      <tbody>
        <tr><td>Status / Dias restantes / Tipo</td><td>Mostra a situação da licença (Teste Gratuito por tempo limitado, ou Licença Vitalícia após a compra).</td></tr>
        <tr><td>Continuar</td><td>Entra no app (disponível durante o teste ou com licença válida).</td></tr>
        <tr><td>Comprar Licença</td><td>Inicia a compra da licença vitalícia (via Mercado Pago).</td></tr>
        <tr><td>Ativar com chave</td><td>Campo para inserir a chave no formato <code>SCTE-XXXXXX-XXXXXX-XXXXXX</code>. O app valida no servidor ScanTE Admin, vincula o dispositivo e ativa imediatamente. Use quando a empresa já gerou e enviou uma chave.</td></tr>
      </tbody>
    </table>
  </div>

  <!-- 3. Sessões -->
  <div class="section" id="s3">
    <div class="section-header">
      <span class="section-num">03</span>
      <h2>Tela de Sessões</h2>
      <span class="status-tag tag-ok">✅ Funcional</span>
    </div>
    <p>Lista os servidores (sessões) cadastrados. Cada item mostra o <strong>nome</strong> e o <strong>endereço:porta</strong>.</p>
    <ul class="flow">
      <li><strong>Tocar numa sessão:</strong> abre a configuração dela (nome, IP, porta).</li>
      <li><strong>Botão + (canto inferior):</strong> cria uma nova sessão.</li>
      <li><strong>3 pontinhos de cada sessão:</strong> Conectar, Editar, Configuração avançada, Remover.</li>
    </ul>

    <div class="subsection">
      <h3>Menu geral (3 pontos no topo)</h3>
      <table class="data-table">
        <thead><tr><th>Opção</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Configurações</td><td>Abre o painel de configurações (Comunicação, Emulação, Tela, Dispositivos)</td></tr>
          <tr><td>Novo</td><td>Cria uma nova sessão</td></tr>
          <tr><td>Remover</td><td>Apaga uma sessão escolhida</td></tr>
          <tr><td>Renomear</td><td>Muda o nome de uma sessão</td></tr>
          <tr><td>Ajuda</td><td>Texto de ajuda rápida</td></tr>
          <tr><td>Importação</td><td>Importa sessões de um arquivo (.json)</td></tr>
          <tr><td>Exportação</td><td>Compartilha/salva as sessões num arquivo (.json) — backup</td></tr>
          <tr><td>Modelos</td><td>🔧 Modelos prontos de sessão</td></tr>
          <tr><td>Calculadora</td><td>Abre a calculadora flutuante (ver seção 11)</td></tr>
          <tr><td>Opções gerais</td><td>Configurações de comportamento do app (ver seção 7)</td></tr>
          <tr><td>Sobre ScanTE</td><td>Versão e informações do app</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 4. Terminal -->
  <div class="section" id="s4">
    <div class="section-header">
      <span class="section-num">04</span>
      <h2>Tela do Terminal</h2>
      <span class="status-tag tag-ok">✅ Funcional</span>
    </div>
    <p>É onde você vê e opera o sistema conectado.</p>
    <table class="data-table">
      <thead><tr><th>Elemento</th><th>Para que serve</th></tr></thead>
      <tbody>
        <tr><td>Digitação direta</td><td>Toque na tela do terminal (ou no ícone de teclado) para abrir o teclado. O que você digita vai <strong>direto</strong> para o sistema — é assim que se preenche Usuário, Senha e comandos.</td></tr>
        <tr><td>Ícone de teclado</td><td>Mostra/oculta o teclado virtual.</td></tr>
        <tr><td>Desconectar</td><td>Encerra a conexão e volta às Sessões.</td></tr>
        <tr><td>◀ Voltar às sessões</td><td>Volta para a tela de Sessões <strong>mantendo a conexão ativa</strong> em segundo plano. Para retomar, toque novamente na sessão (ela exibirá <strong>● Conectado</strong>).</td></tr>
        <tr><td>⇄ Badge de sessão</td><td>Aparece quando há <strong>2 sessões ativas</strong> simultaneamente. Toque para escolher qual sessão operar.</td></tr>
        <tr><td>Barras de ferramentas</td><td>Botões de atalho (setas, Enter, Ctrl+letra, etc.) configuráveis (ver seção 6.3).</td></tr>
        <tr><td>Campos destacados</td><td>Os campos editáveis do sistema aparecem com cor de fundo, para você ver onde digitar.</td></tr>
      </tbody>
    </table>

    <div class="subsection">
      <h3>4.1 Múltiplas sessões simultâneas</h3>
      <p>O ScanTE permite <strong>até 2 conexões Telnet ativas ao mesmo tempo</strong>, em servidores diferentes.</p>
      <ol class="flow">
        <li>Conecte à primeira sessão normalmente → terminal abre.</li>
        <li>Toque em <strong>◀</strong> para voltar à lista <strong>sem fechar a conexão</strong>.</li>
        <li>Na lista, a sessão aparece com <strong>● Conectado</strong> ao lado do endereço.</li>
        <li>Toque em <strong>Conectar</strong> na segunda sessão → segundo terminal abre.</li>
        <li>Para alternar, use o botão <strong>⇄</strong> que aparece na barra superior.</li>
      </ol>
      <div class="callout warn">⚠️ <strong>Limite:</strong> ao tentar abrir uma 3ª sessão, o app exibe <em>"Máximo de 2 sessões ativas"</em>. Desconecte uma delas primeiro.</div>
    </div>
  </div>

  <!-- 5. Comunicação -->
  <div class="section" id="s5">
    <div class="section-header">
      <span class="section-num">05</span>
      <h2>Configurações → Comunicação</h2>
    </div>

    <div class="subsection">
      <h3>5.1 Telnet Opções</h3>
      <p>Parâmetros de como o app conversa com o servidor. <strong>As Telnet Opções são globais</strong> (valem para o app todo).</p>

      <h4>Conexão</h4>
      <table class="data-table">
        <thead><tr><th>Função</th><th>Para que serve</th><th></th></tr></thead>
        <tbody>
          <tr><td>Endereço do servidor</td><td>Endereço:porta da sessão (somente leitura — para mudar, edite a sessão).</td><td>✅</td></tr>
          <tr><td>Tipo de terminal</td><td>Informa ao sistema "que terminal você é" (VT100, VT220, ANSI…). O Protheus normalmente usa VT220.</td><td>✅</td></tr>
          <tr><td>Terminador de linha</td><td>O que é enviado ao apertar <strong>Enter</strong>: <code>CR+LF</code>, <code>CR</code> ou <code>LF</code>. Se o Enter "não avança" a tela, troque aqui.</td><td>✅</td></tr>
          <tr><td>Usar IP para BRK</td><td>A tecla <strong>Break</strong> passa a enviar "Interromper Processo" (aborta o comando atual). Útil em AS/400 e mainframes.</td><td>✅</td></tr>
          <tr><td>Modo binário</td><td>Liga transmissão de 8 bits. Use quando precisar de <strong>acentos</strong> (ç, ã, é) corretos.</td><td>✅</td></tr>
          <tr><td>Simular paridade</td><td>Remove o 8º bit dos dados recebidos. Use quando aparecem <strong>caracteres estranhos</strong>. ⚠️ Não use junto com Modo binário.</td><td>✅</td></tr>
          <tr><td>Mantenha o tipo vivo</td><td><strong>TCP</strong> = keep-alive do sistema. <strong>NVT</strong> = app envia sinal invisível periodicamente. <strong>Desligado</strong> = sem keep-alive.</td><td>✅</td></tr>
          <tr><td>Mantenha o intervalo vivo</td><td>De quantos em quantos <strong>segundos</strong> o sinal NVT é enviado (ex: 60). Vale quando o tipo é NVT.</td><td>✅</td></tr>
        </tbody>
      </table>

      <h4>Login Telnet (login automático)</h4>
      <p>Sequência para o app fazer login sozinho: aguarda o pedido de login → envia o usuário → aguarda senha → envia a senha → aguarda prompt → envia comando.</p>
      <div class="callout">
        <strong>Exemplo para Protheus:</strong><br>
        Aguarde prompt de login: <code>login:</code> · Faça login com: <code>seu_usuario</code><br>
        Aguarde solicitação de senha: <code>Password:</code> · Senha: <code>sua_senha</code><br>
        Aguarde prompt de comando: <code>&gt;</code> · Faça o comando: <em>(deixe vazio)</em>
      </div>

      <h4>Sockets seguros (SSL)</h4>
      <table class="data-table">
        <thead><tr><th>Campo</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Sockets seguros (SSL)</td><td>Liga/desliga a criptografia TLS. Necessário quando o servidor exige porta segura (ex: 992).</td></tr>
          <tr><td>Autenticar certificado do servidor</td><td>Configuração reservada para versão futura — atualmente o app aceita qualquer certificado automaticamente.</td></tr>
          <tr><td>Arquivo de certificado cliente</td><td>Caminho para arquivo <code>.p12</code> ou <code>.pfx</code> — quando o servidor exige que <strong>você</strong> se identifique com um certificado.</td></tr>
          <tr><td>Senha do certificado cliente</td><td>Senha de proteção do arquivo <code>.p12/.pfx</code>.</td></tr>
        </tbody>
      </table>

      <h4>Conexão SSH</h4>
      <table class="data-table">
        <thead><tr><th>Campo</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Conexão SSH</td><td>Liga/desliga o modo SSH. Quando marcado, o app conecta via SSH em vez de Telnet.</td></tr>
          <tr><td>Servidor SSH</td><td>Endereço e porta do servidor SSH (ex: <code>192.168.1.10:22</code>). Se vazio, usa o endereço da sessão.</td></tr>
          <tr><td>Usuário SSH</td><td>Nome de usuário para autenticar no servidor SSH.</td></tr>
          <tr><td>Senha SSH</td><td>Senha do usuário (autenticação por senha).</td></tr>
          <tr><td>Chave privada SSH</td><td>Caminho para arquivo de chave privada (PEM/OpenSSH). Deixe vazio para autenticar só por senha.</td></tr>
          <tr><td>Keep-alive SSH</td><td>Intervalo em segundos para sinal de keep-alive SSH (ex: 60). Deixe 0 para desativar.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>5.2 Servidor proxy</h3>
      <p>Conectar através de um servidor intermediário. Útil quando o dispositivo não acessa o servidor Telnet/SSH diretamente.</p>
      <table class="data-table">
        <thead><tr><th>Campo</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Usar servidor proxy</td><td>Liga/desliga o redirecionamento pelo proxy.</td></tr>
          <tr><td>Endereço do proxy</td><td>IP ou nome do servidor proxy (ex: <code>192.168.1.1</code> ou <code>proxy.empresa.com</code>). Para especificar porta direto: <code>host:porta</code>.</td></tr>
          <tr><td>Porta</td><td>Porta do proxy (padrão: 30855). Usada se não informada no campo de endereço.</td></tr>
          <tr><td>Comunicação segura</td><td>Usa HTTPS ao conectar no proxy (em vez de HTTP simples).</td></tr>
          <tr><td>Manter conexão quando usuário desconectar</td><td>Segundos que o proxy mantém o canal após você desconectar (0 = fecha imediatamente).</td></tr>
          <tr><td>Manter conexão quando conexão for perdida</td><td>Segundos que o proxy aguarda antes de encerrar o canal quando a conexão cai (padrão: 300).</td></tr>
        </tbody>
      </table>
      <div class="callout">O app abre uma conexão TCP com o proxy e envia um comando <code>HTTP CONNECT</code> pedindo um túnel até o servidor destino. O tráfego Telnet ou SSL passa por dentro desse túnel de forma transparente.</div>
    </div>
  </div>

  <!-- 6. Tela -->
  <div class="section" id="s6">
    <div class="section-header">
      <span class="section-num">06</span>
      <h2>Configurações → Tela</h2>
    </div>

    <div class="subsection">
      <h3>6.1 Opções de tela</h3>
      <table class="data-table">
        <thead><tr><th>Item</th><th>Para que serve</th><th></th></tr></thead>
        <tbody>
          <tr><td>Tamanho da fonte</td><td>Tamanho do texto no terminal</td><td>✅</td></tr>
          <tr><td>Cursor piscando</td><td>Liga/desliga a piscada do cursor</td><td>✅</td></tr>
          <tr><td>Campos 3D de fundo branco</td><td>Estilo visual dos campos de entrada</td><td>✅</td></tr>
          <tr><td>Nome da fonte, Tipo/Cor do cursor, Mostrar barra de ferramentas, Limitar visualização, Toque duas vezes</td><td>Demais ajustes de exibição</td><td>🔧</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>6.2 Cores da tela</h3>
      <table class="data-table">
        <thead><tr><th>Item</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Primeiro plano</td><td>Cor do texto do terminal.</td></tr>
          <tr><td>Plano de fundo</td><td>Cor de fundo do terminal.</td></tr>
          <tr><td>Status em primeiro plano / Plano de fundo do status</td><td>Cores da barra superior do terminal.</td></tr>
          <tr><td>Campos de preenchimento</td><td>Cor de destaque dos campos editáveis (Usuário, Senha…).</td></tr>
          <tr><td>Ajuste de cor (escuro/brilhante)</td><td>🔧 Em desenvolvimento.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>6.3 Configuração da barra de ferramentas</h3>
      <p>São até <strong>6 barras</strong> de botões que aparecem no terminal. Cada botão envia uma tecla/comando (setas, Enter, Esc, Ctrl+letra, texto, Connect/Disconnect…).</p>
      <table class="data-table">
        <thead><tr><th>Ação</th><th>Como fazer</th></tr></thead>
        <tbody>
          <tr><td>+ Nova barra</td><td>Cria uma nova barra (máximo 6).</td></tr>
          <tr><td>▲ / ▼</td><td>Reordena a posição das barras.</td></tr>
          <tr><td>🗑 Remover</td><td>Remove a barra (pede confirmação).</td></tr>
          <tr><td>Adicionar botão</td><td>Toque no chip para abrir a lista de teclas/comandos disponíveis.</td></tr>
          <tr><td>✎ Renomear botão</td><td>Define um nome personalizado. Ex: adicione F1 e renomeie para "Iniciar" — no terminal aparecerá "Iniciar", mas enviará F1.</td></tr>
          <tr><td>✕ Remover botão</td><td>Remove o botão da barra.</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 7. Opções gerais -->
  <div class="section" id="s7">
    <div class="section-header">
      <span class="section-num">07</span>
      <h2>Opções gerais</h2>
      <span class="status-tag tag-ok">✅ Funcional</span>
    </div>
    <table class="data-table">
      <thead><tr><th>Opção</th><th>Para que serve</th></tr></thead>
      <tbody>
        <tr><td>Orientação da tela</td><td>Retrato, Paisagem ou Automático</td></tr>
        <tr><td>Conexão automática na inicialização</td><td>Conecta sozinho na última sessão ao abrir o app</td></tr>
        <tr><td>Reconectar após conexão perdida</td><td>Reconecta sozinho se a conexão cair sem ser por você</td></tr>
        <tr><td>Desconectar na tela de bloqueio</td><td>Encerra a conexão quando o celular bloqueia</td></tr>
        <tr><td>Nunca bloquear a tela quando conectado</td><td>Mantém a tela acesa durante o uso</td></tr>
        <tr><td>Ignorar otimização de bateria</td><td>Evita que o Android "durma" o app em segundo plano</td></tr>
        <tr><td>Teclado ativado</td><td>Mostra/oculta a área de digitação no terminal</td></tr>
      </tbody>
    </table>
  </div>

  <!-- 8. Emulação -->
  <div class="section" id="s8">
    <div class="section-header">
      <span class="section-num">08</span>
      <h2>Configurações → Emulação</h2>
    </div>

    <div class="subsection">
      <h3>VT Opções</h3>
      <p>Ajustes finos do comportamento do terminal VT. Cada opção é salva automaticamente ao sair da tela e passa a valer na <strong>próxima conexão</strong>.</p>
      <table class="data-table">
        <thead><tr><th>Opção</th><th>O que faz</th></tr></thead>
        <tbody>
          <tr><td>ECHO mode</td><td>Mostra na tela o que você digita, mesmo quando o servidor não devolve. Ligue se o que digita "não aparece"; desligue se aparecer <strong>dobrado</strong>.</td></tr>
          <tr><td>Modo ROLO</td><td>Ligado: ao chegar no fim da tela, o conteúdo rola para cima. Desligado: a tela volta ao topo sem rolar.</td></tr>
          <tr><td>Modo de linha</td><td><em>Desativado</em> (segue o ECHO mode) · <em>Local</em> (força eco local) · <em>Remoto</em> (nunca ecoa localmente).</td></tr>
          <tr><td>Adicionar LFs a CRs</td><td>Cada "Enter" (CR) também avança uma linha. Ligue se o texto recebido fica todo "grudado" na mesma linha.</td></tr>
          <tr><td>Nenhuma coluna 81</td><td>Trava o cursor na coluna 80 em vez de "vazar" para a linha seguinte. Evita quebras indevidas em telas de 80 colunas.</td></tr>
          <tr><td>Ação da tecla Backspace</td><td><em>BS</em> (envia 0x08) ou <em>DEL</em> (envia 0x7F). Mude se a tecla apagar não funcionar no sistema.</td></tr>
          <tr><td>String de resposta</td><td>Texto que o app envia automaticamente quando o host pergunta "quem é você?" (caractere ENQ).</td></tr>
          <tr><td>VT DA Alias</td><td>Modelo de terminal informado ao sistema (VT52, VT100, VT220, VT320, ANSI).</td></tr>
          <tr><td>F5 envia sequência PuTTY</td><td>Faz a tecla F5 enviar o código no padrão PuTTY em vez do padrão VT.</td></tr>
          <tr><td>Silenciar alarme do host</td><td>Quando ligado, ignora o "bip" (BEL) enviado pelo sistema. Desligado: toca um som e vibra o aparelho.</td></tr>
          <tr><td>Máximo de alarmes consecutivos</td><td>Limita quantos bips seguidos podem tocar (Max = sem limite).</td></tr>
          <tr><td>Ignorar sequências de escape desconhecidas</td><td>Descarta silenciosamente códigos de controle que o app não reconhece, evitando "lixo" na tela.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>Transliteração</h3>
      <p>Controla como os caracteres são codificados e convertidos entre o app e o host.</p>

      <h4>Caracteres de 16 bits</h4>
      <table class="data-table">
        <thead><tr><th>Opção</th><th>O que faz</th></tr></thead>
        <tbody>
          <tr><td>Codificação UTF-8</td><td>Quando ligado, o app usa UTF-8 para enviar e receber. Necessário para sistemas que operam com Unicode (ex: Java/web). Quando desligado, usa o charset selecionado em "Conjunto de caracteres hospedeiros".</td></tr>
        </tbody>
      </table>

      <h4>Caracteres de 8 bits</h4>
      <table class="data-table">
        <thead><tr><th>Opção</th><th>O que faz</th></tr></thead>
        <tbody>
          <tr><td>8-bit Host</td><td>Ligado: o app transmite caracteres de 8 bits (bytes 0x80–0xFF). Desligado: mascara o 8º bit, forçando 7 bits — para hosts antigos.</td></tr>
          <tr><td>Permitir letras minúsculas para o host</td><td>Ligado: envia o que o usuário digita sem alterar caixa. Desligado: converte para MAIÚSCULAS antes de enviar.</td></tr>
          <tr><td>Conjunto de caracteres hospedeiros</td><td>Codificação usada para interpretar os bytes do servidor. Use <strong>Padrão (Latim 1)</strong> para maioria dos ERPs brasileiros, ou <strong>Windows-1252</strong> para sistemas Windows antigos.</td></tr>
          <tr><td>Transliteração nacional (host de 7 bits)</td><td>Para hosts que só aceitam ASCII 7 bits, substitui automaticamente os acentos ao <strong>enviar</strong> (ex: "ç" → "c", "ã" → "a").</td></tr>
          <tr><td>Use codificação SISO</td><td>Ativa suporte a códigos SI (0x0F) e SO (0x0E), usados por alguns hosts IBM legados para alternar entre conjuntos de caracteres.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>Geral</h3>
      <table class="data-table">
        <thead><tr><th>Opção</th><th>O que faz</th></tr></thead>
        <tbody>
          <tr><td>BS destrutivo</td><td>Quando ligado, o Backspace apaga o caractere na posição anterior além de mover o cursor. Desligado: apenas move o cursor (padrão VT).</td></tr>
          <tr><td>Capturar em CR</td><td>O que o terminal faz ao receber CR do servidor: <em>Desativado</em> (só move para coluna 1) · <em>LF</em> (também avança linha) · <em>CR+LF</em> (avança explicitamente).</td></tr>
          <tr><td>Comprimento da rolagem (em páginas)</td><td>Quantas páginas de histórico o terminal guarda para scroll-back (padrão: 32).</td></tr>
          <tr><td>Largura inicial da tela</td><td>Número de colunas da grade: 80 (padrão VT100) ou 132 (modo largo VT220). Também enviado ao servidor via NAWS.</td></tr>
          <tr><td>Altura inicial da tela (linhas)</td><td>Número de linhas da grade (padrão: 24). Também enviado ao servidor via NAWS.</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 9. Dispositivos -->
  <div class="section" id="s9">
    <div class="section-header">
      <span class="section-num">09</span>
      <h2>Configurações → Dispositivos</h2>
    </div>

    <div class="subsection">
      <h3>9.1 Configuração de impressão</h3>
      <table class="data-table">
        <thead><tr><th>Campo</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Tipo de impressora</td><td>Modelo/fabricante da impressora: Padrão, Epson ESC/POS, Star, Zebra ZPL, Citizen ou Bixolon.</td></tr>
          <tr><td>Tempo limite da impressora (s)</td><td>Segundos que o app aguarda a impressora responder antes de cancelar (padrão: 5).</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>9.2 Configuração do leitor de código de barras</h3>
      <table class="data-table">
        <thead><tr><th>Campo</th><th>Para que serve</th></tr></thead>
        <tbody>
          <tr><td>Tipo de dispositivo leitor</td><td>Fabricante/modelo do coletor: Honeywell, Zebra, Datalogic, Bluebird, Urovo, Newland, Sunmi ou Genérico.</td></tr>
          <tr><td>Ação após verificação</td><td>O que o app faz após uma leitura: <em>Nenhum</em> · <em>Enter</em> · <em>Tab</em> · <em>Enter + Tab</em>.</td></tr>
          <tr><td>Remover caracteres no início</td><td>Descarta N caracteres do início do código lido (0–10). Remove prefixos/identificadores do símbolo.</td></tr>
          <tr><td>Remover caracteres no final</td><td>Descarta N caracteres do final do código lido (0–10). Remove sufixos ou checkdigit extra.</td></tr>
          <tr><td>Adicione texto antes</td><td>Texto fixo inserido antes do código lido ao enviar ao sistema.</td></tr>
          <tr><td>Adicione texto depois</td><td>Texto fixo inserido depois do código lido ao enviar ao sistema.</td></tr>
          <tr><td>Usar mapeamento de teclado</td><td>Quando ligado, o leitor é tratado como teclado físico — as teclas lidas passam pelo mapeamento configurado no terminal.</td></tr>
          <tr><td>Mostrar na linha de status</td><td>Exibe um indicador na barra de status quando uma leitura é realizada.</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 10. Teclado -->
  <div class="section" id="s10">
    <div class="section-header">
      <span class="section-num">10</span>
      <h2>Teclado personalizado ScanTE</h2>
      <span class="status-tag tag-ok">✅ Funcional</span>
    </div>
    <p>O ScanTE inclui um teclado próprio (IME) otimizado para uso em terminais.</p>
    <div class="callout">Para ativar: <strong>Configurações do Android → Gerenciar teclados → ScanTE Keyboard</strong> (ligar).</div>

    <div class="subsection">
      <h3>Como usar</h3>
      <table class="data-table">
        <thead><tr><th>Gesto / Botão</th><th>Ação</th></tr></thead>
        <tbody>
          <tr><td>Deslize para a esquerda</td><td>Vai para a próxima página do teclado</td></tr>
          <tr><td>Deslize para a direita</td><td>Volta para a página anterior</td></tr>
          <tr><td>Toque em "ABC / 123 / P1…" (abas)</td><td>Navega diretamente para aquela página</td></tr>
          <tr><td>⇧ (shift)</td><td>Ativa maiúsculas (próximo caractere) · toque novamente = fixo</td></tr>
          <tr><td>⇪ Capslock</td><td>Fixa maiúsculas</td></tr>
          <tr><td>?!</td><td>Abre teclado de símbolos especiais</td></tr>
          <tr><td>↵</td><td>Envia Enter ao sistema</td></tr>
          <tr><td>⌫</td><td>Apaga o último caractere</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>Páginas padrão</h3>
      <table class="data-table">
        <thead><tr><th>Página</th><th>Conteúdo</th></tr></thead>
        <tbody>
          <tr><td>ABC</td><td>QWERTY completo com números, símbolos e espaço</td></tr>
          <tr><td>123</td><td>Teclado numérico (0–9, operadores) + linha de símbolos configurável</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>Configurar o teclado</h3>
      <p>Acesse <strong>Configurações → Teclado → Configurar teclado ScanTE</strong>:</p>
      <table class="data-table">
        <thead><tr><th>Opção</th><th>O que faz</th></tr></thead>
        <tbody>
          <tr><td>Página QWERTY (letras)</td><td>Ativa/desativa a página ABC no teclado</td></tr>
          <tr><td>Página numérica (0-9)</td><td>Ativa/desativa a página 123</td></tr>
          <tr><td>Linha de símbolos</td><td>Define os caracteres exibidos na linha scrollável do topo da página numérica</td></tr>
          <tr><td>＋ Adicionar página</td><td>Cria uma nova página personalizada (preset ou em branco)</td></tr>
        </tbody>
      </table>
      <p>Adicione quantas páginas quiser com botões de terminal (setas, teclas Fn, Ctrl+letra, comandos customizados). Cada página é editável individualmente ou via presets prontos (Navegação, Teclas F, Ctrl A-Z, Símbolos…).</p>
    </div>
  </div>

  <!-- 11. Calculadora -->
  <div class="section" id="s11">
    <div class="section-header">
      <span class="section-num">11</span>
      <h2>Calculadora flutuante</h2>
      <span class="status-tag tag-ok">✅ Funcional</span>
    </div>
    <p>O ScanTE inclui uma <strong>calculadora que flutua sobre qualquer tela</strong> do app e pode ser minimizada para um botão arrastável, sem interromper o trabalho no terminal.</p>

    <div class="subsection">
      <h3>Como abrir</h3>
      <ul class="flow">
        <li>Na tela de <strong>Sessões</strong> → menu 3 pontos → <strong>Calculadora</strong></li>
        <li>Na tela do <strong>Terminal</strong> → menu 3 pontos → <strong>Calculadora</strong></li>
      </ul>
      <div class="callout">Na primeira vez, o Android pedirá permissão para "exibir sobre outros apps" — toque em <strong>Permitir</strong> e volte ao ScanTE; a calculadora abrirá automaticamente.</div>
    </div>

    <div class="subsection">
      <h3>Usar a calculadora</h3>
      <table class="data-table">
        <thead><tr><th>Botão</th><th>Função</th></tr></thead>
        <tbody>
          <tr><td>C</td><td>Limpa tudo (começa do zero)</td></tr>
          <tr><td>±</td><td>Inverte o sinal do número (positivo/negativo)</td></tr>
          <tr><td>%</td><td>Divide por 100 (percentual)</td></tr>
          <tr><td>÷ × - +</td><td>Operações aritméticas</td></tr>
          <tr><td>=</td><td>Calcula o resultado</td></tr>
          <tr><td>.</td><td>Ponto decimal</td></tr>
        </tbody>
      </table>
    </div>

    <div class="subsection">
      <h3>Minimizar e mover</h3>
      <table class="data-table">
        <thead><tr><th>Ação</th><th>Como fazer</th></tr></thead>
        <tbody>
          <tr><td>Minimizar</td><td>Toque no ícone ⬇ no cabeçalho — a calculadora vira um botão 🧮</td></tr>
          <tr><td>Restaurar</td><td>Toque no botão 🧮</td></tr>
          <tr><td>Mover</td><td>Arraste pelo cabeçalho "⠿ Calculadora" (janela completa) ou arraste o botão 🧮 (minimizado)</td></tr>
          <tr><td>Fechar</td><td>Toque no ✕ no cabeçalho, ou na notificação → Fechar</td></tr>
        </tbody>
      </table>
      <p>A calculadora permanece visível mesmo ao trocar de tela dentro do app.</p>
    </div>
  </div>

  <!-- 12. Glossário -->
  <div class="section" id="s12">
    <div class="section-header">
      <span class="section-num">12</span>
      <h2>Glossário rápido</h2>
    </div>
    <dl class="glossary">
      <dt>Telnet</dt><dd>Protocolo de terminal por texto (RFC 854).</dd>
      <dt>CR / LF / CR+LF</dt><dd>Caracteres invisíveis de "fim de linha" enviados ao apertar Enter.</dd>
      <dt>Tipo de terminal (VT100/VT220)</dt><dd>"Modelo" de terminal que o sistema usa para desenhar a tela.</dd>
      <dt>Modo binário</dt><dd>Transmissão de 8 bits (necessário para acentos).</dd>
      <dt>Paridade</dt><dd>Bit extra de verificação em links antigos de 7 bits.</dd>
      <dt>Keep-alive</dt><dd>Mensagens periódicas para manter a conexão viva.</dd>
      <dt>Proxy</dt><dd>Servidor intermediário entre o app e o sistema corporativo.</dd>
      <dt>SSH</dt><dd>Protocolo de acesso remoto criptografado (porta 22).</dd>
      <dt>SSL/TLS</dt><dd>Camada de criptografia para proteger os dados em trânsito.</dd>
      <dt>NAWS</dt><dd>Negotiate About Window Size — negociação de tamanho de tela via Telnet.</dd>
    </dl>
  </div>

</div><!-- /doc-body -->

<!-- Rodapé -->
<div class="doc-footer">
  Manual ScanTE &nbsp;·&nbsp; <?= date('Y') ?> &nbsp;·&nbsp; Documento atualizado conforme novas funcionalidades são concluídas.
</div>
