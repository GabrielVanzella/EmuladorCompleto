# Manual do ScanTE

**ScanTE — Conecta · Sincroniza · Simplifica**
Software de comunicação entre coletores de dados e sistemas corporativos via terminal (Telnet).

> Este manual descreve **cada funcionalidade** do aplicativo, para que serve e como usar.
> Legenda de status: ✅ funcional · 🔧 em desenvolvimento

---

## 1. Visão geral

O ScanTE é um emulador de terminal **Telnet** para Android. Ele permite que coletores de
dados (e celulares) se conectem a sistemas corporativos (Protheus/TOTVS, AS/400, entre
outros) que funcionam por linha de comando / tela de texto, exibindo a tela do sistema e
permitindo digitar e navegar como num terminal de verdade.

**Fluxo básico de uso:**
1. Abrir o app → tela de **Licença** (período de teste ou licença vitalícia)
2. **Sessões** → lista de servidores cadastrados
3. Criar/abrir uma sessão → **Terminal** conectado ao sistema

---

## 2. Tela de Licença ✅

É a primeira tela ao abrir o app.

- **Status / Dias restantes / Tipo:** mostra a situação da licença (Teste Gratuito por
  tempo limitado, ou Licença Vitalícia após a compra).
- **Continuar:** entra no app (disponível durante o teste ou com licença válida).
- **Comprar Licença:** inicia a compra da licença vitalícia (via Mercado Pago).
- **Ativar com chave (novo):** campo para inserir a chave de licença no formato
  `SCTE-XXXXXX-XXXXXX-XXXXXX`. Ao tocar em **Ativar**, o app valida a chave no servidor
  ScanTE Admin, vincula o dispositivo e ativa a licença imediatamente. Use este campo
  quando a empresa já gerou e enviou uma chave para o dispositivo.

---

## 3. Tela de Sessões ✅

Lista os servidores (sessões) cadastrados. Cada item mostra o **nome** e o **endereço:porta**.

- **Tocar numa sessão:** abre a configuração dela (nome, IP, porta).
- **Botão + (canto inferior):** cria uma nova sessão.
- **3 pontinhos de cada sessão:** Conectar, Editar, Configuração avançada, Remover.

### Menu geral (3 pontos no topo) ✅
Ações que valem para o app / sessões:

| Opção | Para que serve |
|---|---|
| **Configurações** | Abre o painel de configurações (Comunicação, Emulação, Tela, Dispositivos) |
| **Novo** | Cria uma nova sessão |
| **Remover** | Apaga uma sessão escolhida |
| **Renomear** | Muda o nome de uma sessão |
| **Ajuda** | Texto de ajuda rápida |
| **Importação** | Importa sessões de um arquivo (.json) |
| **Exportação** | Compartilha/salva as sessões num arquivo (.json) — backup |
| **Modelos** | Abre o gerenciador de modelos de sessão (ver seção 3.1) |
| **Calculadora** | Abre a calculadora flutuante (ver seção 11) |
| **Opções gerais** | Configurações de comportamento do app (ver seção 7) |
| **Sobre ScanTE** | Versão e informações do app |

### 3.1. Modelos de sessão ✅

Modelos são presets de conexão que agilizam a criação de novas sessões sem precisar digitar o endereço e a porta toda vez.

**Como usar:**
1. Menu (3 pontos) → **Modelos**
2. Toque em **+** para criar um novo modelo: informe nome, host, porta e uma descrição opcional.
3. Para usar um modelo: toque no item da lista ou use o menu do modelo → **Usar modelo**.  
   O formulário "Novo Host" será aberto já preenchido com os dados do modelo — basta confirmar ou ajustar.
4. O modelo não é excluído ao ser usado; fica salvo para uso futuro.

**Gerenciar modelos** (menu de 3 pontos em cada item da lista):
| Ação | O que faz |
|---|---|
| **Usar modelo** | Abre "Novo Host" pré-preenchido |
| **Editar** | Permite alterar nome, host, porta e descrição |
| **Excluir** | Remove o modelo permanentemente |

---

## 4. Tela do Terminal ✅

É onde você vê e opera o sistema conectado.

- **Digitação direta:** toque na tela do terminal (ou no ícone de teclado ao lado de
  "Desconectar") para abrir o teclado. O que você digita vai **direto** para o sistema —
  é assim que se preenche Usuário, Senha e comandos.
- **Ícone de teclado:** mostra/oculta o teclado virtual.
- **Desconectar:** encerra a conexão e volta às Sessões.
- **◀ Voltar às sessões (sem desconectar):** botão no canto esquerdo da barra — volta para
  a tela de Sessões **mantendo a conexão ativa** em segundo plano. Para retomar, toque
  novamente na sessão na lista (ela exibirá **● Conectado**).
- **⇄ Badge de sessão:** aparece quando há **2 sessões ativas** simultaneamente. Toque
  para voltar à lista de Sessões e escolher qual sessão operar.
- **Barras de ferramentas:** botões de atalho (setas, Enter, Ctrl+letra, etc.) configuráveis
  (ver seção 6.3). Inclui os botões **Copiar** e **Colar** para interação com a área de transferência.
- **Campos de preenchimento destacados:** os campos editáveis do sistema (ex: Usuário,
  Senha) aparecem com cor de fundo, para você ver onde digitar.
- **Menu 3 pontos → Sessões (manter conexão):** volta às Sessões sem desconectar (igual
  ao botão ◀).

### 4.1 Copiar e colar ✅

Os botões **Copy** e **Paste** da barra de ferramentas permitem integrar o terminal com a área de transferência do Android.

| Botão | O que faz |
|---|---|
| **Copy** | Copia o conteúdo atual da tela do terminal para a área de transferência (texto puro) |
| **Paste** | Envia o texto da área de transferência diretamente ao servidor (como se tivesse digitado) |

> **Dica:** Para adicionar esses botões à barra, vá em Configurações → Barras de ferramentas e adicione as ações "Copy" e "Paste".

### 4.2 Múltiplas sessões simultâneas ✅

O ScanTE permite **até 2 conexões Telnet ativas ao mesmo tempo**, em servidores diferentes.

**Como usar:**
1. Conecte à primeira sessão normalmente → terminal abre.
2. Toque em **◀** (ou menu → Sessões) para voltar à lista **sem fechar a conexão**.
3. Na lista, a sessão aparece com **● Conectado** ao lado do endereço.
4. Toque em **Conectar** na segunda sessão → segundo terminal abre.
5. Para alternar entre os terminais abertos, use o botão **⇄** que aparece na barra
   superior quando há 2 sessões ativas.

**Limite:** ao tentar abrir uma 3ª sessão, o app exibe aviso _"Máximo de 2 sessões ativas"_.
Desconecte uma delas primeiro usando o botão **Desconectar** no terminal correspondente.

---

## 5. Configurações → Comunicação

### 5.1. Telnet Opções

Parâmetros de como o app conversa com o servidor. **As Telnet Opções são globais** (valem
para o app todo).

#### Conexão
| Função | Para que serve | Status |
|---|---|---|
| **Endereço do servidor** | Mostra o endereço:porta do servidor (vem da sessão cadastrada; é só leitura — para mudar, edite a sessão). | ✅ |
| **Tipo de terminal** | Informa ao sistema "que terminal você é" (VT100, VT220, ANSI…). Muda como o sistema desenha a tela. O Protheus normalmente usa VT220. | ✅ |
| **Terminador de linha** | O que é enviado quando você aperta **Enter**: `CR+LF`, `CR` ou `LF`. Se o Enter "não avança" a tela, troque aqui (muitos ERPs usam só `CR`). | ✅ |
| **Usar IP para BRK** | A tecla **Break** passa a enviar "Interromper Processo" (aborta o comando atual, tipo um Ctrl+C) em vez do Break comum. Útil em AS/400 e mainframes. | ✅ |
| **Modo binário** | Liga a transmissão de 8 bits (caracteres completos). Use quando precisar de **acentos** (ç, ã, é) corretos. | ✅ |
| **Simular paridade** | Remove o 8º bit dos dados recebidos (trata como 7 bits). Use quando aparecem **caracteres estranhos** por causa de bit de paridade. ⚠️ Não use junto com Modo binário (são opostos). | ✅ |
| **Mantenha o tipo vivo** | Mantém a conexão ativa (keep-alive) para não cair por inatividade. **TCP** = usa o keep-alive do sistema. **NVT** = o app envia um sinal invisível (NOP) periodicamente. **Desligado** = sem keep-alive. | ✅ |
| **Mantenha o intervalo vivo** | De quantos em quantos **segundos** o sinal de keep-alive NVT é enviado (ex: 60). Vale quando o tipo é NVT. | ✅ |

#### Login Telnet (login automático) ✅
Sequência para o app fazer login sozinho: aguarda o pedido de login → envia o usuário →
aguarda o pedido de senha → envia a senha → aguarda o prompt → envia um comando.

**Como funciona:** Você preenche os 6 campos (prompts e credenciais) na tela Telnet Opções.
Ao conectar, o app monitora o texto que chega do servidor. Quando encontra o **"Aguarde o 
prompt de login"** (ex: `login:`), envia automaticamente o valor de **"Faça login com"** 
(usuário). Depois aguarda **"Aguarde a solicitação de senha"** e envia a **"Senha"**. 
Se um **"Aguarde o prompt de comando"** for configurado, aguarda e envia **"Faça o comando"**.

**Exemplo:** Para um Protheus que pede `login:`, `Password:` e depois mostra `>`, configure:
- Aguarde o prompt de login: `login:`
- Faça login com: `seu_usuario`
- Aguarde a solicitação de senha: `Password:`
- Senha: `sua_senha`
- Aguarde o prompt de comando: `>`
- Faça o comando: `` (deixe vazio se não quer enviar nada após login)

#### Sockets seguros (SSL) ✅
Conexão criptografada via TLS — os dados entre o app e o servidor ficam protegidos.

| Campo | Para que serve |
|---|---|
| **Sockets seguros (SSL)** | Liga/desliga a criptografia TLS. Quando marcado, a conexão usa TLS em vez de Telnet puro. Necessário quando o servidor exige porta segura (ex: 992). |
| **Autenticar certificado do servidor** | (Configuração reservada para versão futura — atualmente o app aceita qualquer certificado do servidor automaticamente). |
| **Arquivo de certificado cliente** | Caminho para um arquivo `.p12` ou `.pfx` no dispositivo — usado quando o servidor exige que **você** se identifique com um certificado. Deixe vazio se não precisar. |
| **Senha do certificado cliente** | Senha de proteção do arquivo `.p12/.pfx`, se houver. |

**Quando usar:** Servidores Telnet/S (Telnet over SSL), ou conexões em que a empresa exige canal criptografado (ex: auditoria, dados financeiros).

#### Conexão SSH ✅
Conexão via protocolo SSH — canal criptografado e autenticado, alternativa ao Telnet puro.

| Campo | Para que serve |
|---|---|
| **Conexão SSH** | Liga/desliga o modo SSH. Quando marcado, o app conecta via SSH em vez de Telnet. |
| **Servidor SSH** | Endereço e porta do servidor SSH (ex: `192.168.1.10:22`). Se vazio, usa o endereço da sessão. |
| **Usuário SSH** | Nome de usuário para autenticar no servidor SSH. |
| **Senha SSH** | Senha do usuário (autenticação por senha). |
| **Chave privada SSH** | Caminho para o arquivo de chave privada no dispositivo (PEM/OpenSSH). Deixe vazio para autenticar só por senha. |
| **Keep-alive SSH** | Intervalo em segundos para envio de sinal de keep-alive SSH (ex: 60). Deixe 0 para desativar. |

**Quando usar:** Quando o servidor não aceita Telnet puro e exige SSH (porta 22), ou quando a empresa requer canal autenticado e criptografado.

### 5.2. Servidor proxy ✅
Conectar através de um servidor intermediário (proxy). Útil quando o dispositivo está numa
rede que não acessa o servidor Telnet/SSH diretamente e exige passagem por um proxy corporativo.

#### Servidor proxy

| Campo | Para que serve |
|---|---|
| **Usar servidor proxy** | Liga/desliga o redirecionamento pelo proxy. |
| **Endereço (IP ou hostname)** | IP ou nome do servidor proxy (ex: `192.168.1.1` ou `proxy.empresa.com`). |
| **Porta** | Porta do proxy (padrão: **3128**). |
| **Comunicação segura (HTTPS CONNECT)** | Usa TLS ao se conectar ao proxy em vez de HTTP simples. |

#### Autenticação (opcional)

| Campo | Para que serve |
|---|---|
| **Usuário** | Nome de usuário exigido pelo proxy. Deixe vazio se o proxy não exige autenticação. |
| **Senha** | Senha correspondente ao usuário do proxy. |

Quando usuário e senha são informados, o app inclui automaticamente o cabeçalho
`Proxy-Authorization: Basic <credenciais>` na requisição `HTTP CONNECT` — compatível
com proxies Squid, Nginx, ISA Server e similares.

#### Manutenção de conexão

| Campo | Para que serve |
|---|---|
| **Ao desconectar manualmente** | Segundos que o proxy mantém o canal com o servidor após você desconectar (0 = fecha imediatamente). |
| **Se conexão cair** | Segundos que o proxy aguarda antes de encerrar o canal quando a conexão cai inesperadamente (padrão: 300). |

#### Diagnóstico

Toque em **Testar conexão ao proxy** para verificar se o app consegue alcançar o proxy pelo
endereço e porta configurados. O resultado aparece como notificação na tela:
- ✓ Proxy alcançável em `host:porta`
- ✗ Proxy inacessível + descrição do erro

**Como funciona:** O app abre uma conexão TCP com o proxy e envia um comando `HTTP CONNECT` pedindo
ao proxy para abrir um túnel até o servidor destino. A partir daí, o tráfego Telnet ou SSL passa
por dentro desse túnel de forma transparente. Proxies com autenticação Basic são suportados.

> **ScanTE Relay:** Para persistência de sessão (tela não trava quando a internet cai), configure aqui o IP do servidor **ScanTE Relay** com porta **2323**. Veja a seção 5.3.

### 5.3. ScanTE Relay Server — Persistência de Sessão ✅

O **ScanTE Relay** é um servidor que roda dentro da rede local da empresa. Ele funciona como um ponto intermediário entre os coletores e o ERP, com um benefício crucial: **quando a internet cai, a sessão não fecha**.

#### O problema que o relay resolve

| Sem relay | Com relay |
|---|---|
| Internet cai → app fecha a conexão | Internet cai → tela **congela** |
| Operador precisa fazer login novamente | Internet volta → sessão **retoma automaticamente** |
| Dados não salvos se perdidos | Operador continua do ponto onde parou |

#### Arquitetura

```
[Coletor ScanTE]  ←—— Wi-Fi local (estável) ——→  [ScanTE Relay .exe]  ←—— internet ——→  [ERP/Telnet]
```

O relay fica na rede local (Wi-Fi) — essa conexão é sempre estável. Só o trecho relay→ERP depende da internet.

#### Instalar o Relay Server (TI da empresa)

**Pré-requisitos:**
- PC ou servidor Windows na rede local, ligado durante o uso dos coletores
- Go instalado uma única vez para compilar: https://go.dev/dl/

**Passos:**
1. Baixe a pasta `scante-relay/` com os arquivos do servidor
2. Execute `build.bat` — gera o `scante-relay.exe` (sem dependências)
3. Copie `scante-relay.exe` e `scante-relay.json` para `C:\ScanTE-Relay\`
4. Edite `scante-relay.json` se necessário (veja campos abaixo)
5. Execute `scante-relay.exe` — terminal exibe as conexões ativas
6. Para iniciar com o Windows: crie uma tarefa no Agendador de Tarefas

**scante-relay.json:**
```json
{
  "listen_addr": "0.0.0.0:2323",
  "keepalive_interval": 30,
  "reconnect_max_sec": 60,
  "max_sessions": 0,
  "dial_timeout_sec": 15,
  "log_file": "scante-relay.log"
}
```

| Campo | Descrição | Padrão |
|---|---|---|
| `listen_addr` | Porta de escuta (todos os dispositivos da rede) | `0.0.0.0:2323` |
| `keepalive_interval` | Segundos entre keepalives enviados ao ERP | `30` |
| `reconnect_max_sec` | Espera máxima entre tentativas de reconexão | `60` |
| `max_sessions` | Limite de coletores simultâneos (0 = ilimitado) | `0` |
| `log_file` | Arquivo de log (relativo ao .exe) | `scante-relay.log` |

#### Configurar o coletor para usar o relay

1. **Configurações → Comunicação → Servidor proxy**
2. Marque **Usar servidor proxy**
3. **Endereço:** IP do servidor onde o relay está rodando (ex: `192.168.1.10`)
4. **Porta:** `2323`
5. Salve e conecte normalmente

#### Verificar funcionamento

No console do relay, cada conexão é exibida:
```
[S0001] Conectado a erp.empresa.com:23
[S0002] Conectado a erp.empresa.com:23
Status: 2 sessão(ões) ativa(s)
```

Quando internet cai e volta:
```
[S0001] Servidor caiu — aguardando reconexão
[S0001] Reconectando a erp.empresa.com:23 em 2s...
[S0001] Reconectado a erp.empresa.com:23 com sucesso
```

---

## 6. Configurações → Tela

### 6.1. Opções de tela ✅
| Item | Para que serve |
|---|---|
| **Tamanho da fonte** | Tamanho do texto no terminal (8 a 24 sp) |
| **Nome da fonte** | Fonte monoespaçada: Padrão, Courier New, Droid Sans Mono |
| **Tipo de cursor** | Forma do cursor no terminal: **Bloco** (█), **Barra** (semitransparente), **Sublinhado** (_), **Nenhum** |
| **Cor do cursor** | Cor do cursor: Verde, Branco, Ciano, Amarelo, Vermelho, Azul, Laranja |
| **Cursor piscando** | Liga/desliga a piscada do cursor (intervalo: 500 ms) |
| **Campos 3D de fundo branco** | Fundo branco em campos de preenchimento (complementa Campos variáveis 3D) |
| **Campos variáveis 3D** | Como exibir os campos de preenchimento: _Ligado sem atributos_ (destaque simples), _Ligado com atributos_ (destaque + sublinhado), _Desligado_ (sem destaque) |
| **Mostrar barra de ferramentas** | Quando exibir a barra de botões: **Automático** (aparece ao conectar), **Sempre** (sempre visível), **Nunca** (sempre oculta) |
| **Limitar visualização da tela** | Limita quantas linhas o terminal exibe: Sem limite, 26×20, 40×24, 80×24, 132×24 |
| **Toque duas vezes** | Ação ao dar duplo toque na tela do terminal: _Redefinir tamanho da tela_, _Zoom in_ (+2 sp), _Zoom out_ (−2 sp), _Nenhum_ |

### 6.2. Cores da tela ✅

**Seção Seleção de cores**

| Opção | O que controla |
|---|---|
| **Primeiro plano** | Cor padrão do texto do terminal |
| **Plano de fundo** | Cor de fundo do terminal |
| **Status em primeiro plano** | Cor do texto na barra de status (topo) |
| **Plano de fundo do status** | Fundo da barra de status |
| **Campos de preenchimento** | Destaque dos campos editáveis (Usuário, Senha…) |

**Seção Ajuste de cor** ✅

| Opção | O que controla |
|---|---|
| **Primeiro plano escuro** | Cor usada para texto *dim* (SGR 2). Padrão automático: escurece 50 % a cor do texto |
| **Primeiro plano brilhante** | Cor usada para texto *negrito* quando o modo está em "Cor brilhante" ou "Negrito+Cor". Padrão: clareia 50 % |
| **Plano de fundo** | Cor de fundo aplicada atrás do texto *dim*. Padrão: sem fundo |

> **Dica:** Deixe todas as opções de ajuste em "Automático" (quadrado tracejado vermelho) para o comportamento padrão. Defina uma cor para personalizar como texto escuro ou brilhante é renderizado.

### 6.3. Configuração da barra de ferramentas ✅
São até **6 barras** de botões que aparecem no terminal. Cada botão envia uma tecla/comando
(setas, Enter, Esc, Ctrl+letra, texto, Connect/Disconnect…).

**Gerenciar barras:**
- **+ Nova barra de ferramentas:** cria uma nova barra (máximo 6).
- **▲ / ▼:** reordena a posição das barras.
- **🗑:** remove a barra (pede confirmação).

**Gerenciar botões dentro de uma barra:**
- **Adicionar (chip):** toque para abrir a lista de teclas/comandos disponíveis e adicionar à barra.
- **✎ (renomear):** define um nome personalizado para o botão. Ex: adicione F1 e renomeie
  para "Iniciar" — no terminal aparecerá o rótulo "Iniciar", mas enviará a tecla F1.
- **✕:** remove o botão da barra.

### 6.4. VT Mapeamento de atributos ✅

Define como os atributos de vídeo VT100/ANSI são exibidos na tela.

| Atributo | Opções disponíveis | Descrição |
|---|---|---|
| **Negrito** (SGR 1) | Negrito / Cor brilhante / Negrito+Cor / Nenhum | Como renderizar texto em negrito |
| **Sublinhado** (SGR 4) | Ativo / Inativo | Exibe sublinhado em texto com atributo sublinhado |
| **Piscante** (SGR 5) | Negrito / Cor brilhante / Nenhum | Como renderizar texto com atributo piscante (sem piscar de fato) |

**Opções de Negrito explicadas:**
- **Negrito** — aplica peso de fonte negrito (padrão, comportamento clássico VT).
- **Cor brilhante** — clareia a cor do texto em 50 % em vez de usar negrito; útil em fontes que não distinguem peso.
- **Negrito+Cor** — aplica negrito *e* claria a cor ao mesmo tempo.
- **Nenhum** — ignora o atributo negrito (texto aparece igual ao normal).

> **Nota:** "Piscante" não pisca de fato (sem suporte em TextView Android), mas pode ser mapeado para negrito ou cor brilhante para indicar visualmente o atributo.

---

## 7. Opções gerais ✅
| Opção | Para que serve |
|---|---|
| **Orientação da tela** | Retrato, Paisagem ou Automático |
| **Conexão automática na inicialização** | Conecta sozinho na última sessão ao abrir o app |
| **Reconectar após conexão perdida** | Reconecta sozinho se a conexão cair sem ser por você |
| **Desconectar na tela de bloqueio** | Encerra a conexão quando o celular bloqueia |
| **Nunca bloquear a tela quando conectado** | Mantém a tela acesa durante o uso |
| **Ignorar otimização de bateria** | Evita que o Android "durma" o app |
| **Teclado ativado** | Mostra/oculta a área de digitação no terminal |

---

## 8. Configurações → Emulação

### VT Opções ✅
Ajustes finos do comportamento do terminal VT. Cada opção é salva automaticamente ao sair
da tela e passa a valer na **próxima conexão**.

| Opção | O que faz |
|---|---|
| **ECHO mode** | Mostra na tela o que você digita, mesmo quando o servidor não devolve (eco local). Deixe ligado se o que digita "não aparece"; desligue se aparecer **dobrado**. |
| **Modo ROLO** | Ligado: ao chegar no fim da tela, o conteúdo rola para cima (como um terminal comum). Desligado: a tela volta ao topo sem rolar. |
| **Modo de linha** | *Desativado* (padrão, segue o ECHO mode) · *Local* (força eco local) · *Remoto* (nunca ecoa localmente). |
| **Adicionar LFs a CRs** | Cada "Enter" (CR) também avança uma linha. Ligue se o texto recebido fica todo "grudado" na mesma linha. |
| **Nenhuma coluna 81** | Trava o cursor na coluna 80 em vez de "vazar" para a linha seguinte. Evita quebras de linha indevidas em telas de 80 colunas. |
| **Ação da tecla Backspace** | *BS* (envia 0x08) ou *DEL* (envia 0x7F). Mude se a tecla apagar não funcionar no sistema. |
| **String de resposta** | Texto que o app envia automaticamente quando o host pergunta "quem é você?" (caractere ENQ). |
| **VT DA Alias** | Modelo de terminal informado ao sistema (VT52, VT100, VT220, VT320, ANSI). Também define a resposta de identificação. |
| **F5 envia sequência PuTTY** | Faz a tecla F5 enviar o código no padrão PuTTY em vez do padrão VT. |
| **Silenciar alarme do host** | Quando ligado, ignora o "bip" (BEL) enviado pelo sistema. Desligado: toca um som e vibra o aparelho. |
| **Máximo de alarmes de host consecutivos** | Limita quantos bips seguidos podem tocar (Max = sem limite). |
| **Ignorar sequências de escape desconhecidas** | Descarta silenciosamente códigos de controle que o app não reconhece, evitando "lixo" na tela. |

### Transliteração ✅
Controla como os caracteres são codificados e convertidos entre o app e o host.
As configurações são salvas ao sair da tela e aplicadas na próxima conexão.

#### Caracteres de 16 bits
| Opção | O que faz |
|---|---|
| **Codificação UTF-8** | Quando ligado, o app usa UTF-8 para enviar e receber. Necessário para sistemas que operam com Unicode (ex: Java/web). Quando desligado, usa o charset selecionado em "Conjunto de caracteres hospedeiros". |

#### Caracteres de 8 bits
| Opção | O que faz |
|---|---|
| **8-bit Host** | Ligado: o app transmite caracteres de 8 bits (bytes 0x80–0xFF) ao servidor. Desligado: mascara o 8º bit, forçando todos os bytes a 7 bits — para hosts antigos que não aceitam dados de 8 bits. |
| **Permitir letras minúsculas para o host** | Ligado: envia o que o usuário digita sem alterar caixa. Desligado: converte todas as letras para MAIÚSCULAS antes de enviar. |
| **Conjunto de caracteres hospedeiros** | Define a codificação usada para interpretar os bytes recebidos do servidor. Use **Padrão (Latim 1)** para maioria dos ERPs brasileiros, **Windows-1252** para sistemas Windows antigos, ou a codificação específica do seu servidor (ISO 8859-2, Cirílico, etc.). |
| **Transliteração nacional (host de 7 bits)** | Para hosts que só aceitam ASCII 7 bits, esta opção substitui automaticamente os acentos pelo caractere base ao **enviar** (ex: "ç" → "c", "ã" → "a"). Selecione o idioma para usar o mapa correto. |
| **Use codificação SISO** | Ativa o suporte a códigos de controle SI (0x0F) e SO (0x0E), usados por alguns hosts para alternar entre dois conjuntos de caracteres. Use somente se o sistema exige (geralmente hosts IBM legados). |

### Geral ✅
Ajustes de comportamento geral do emulador de terminal.

| Opção | O que faz |
|---|---|
| **BS destrutivo** | Quando ligado, a tecla Backspace apaga o caractere na posição anterior além de mover o cursor. Quando desligado, apenas move o cursor (comportamento padrão VT). |
| **Capturar em CR** | O que o terminal faz ao receber um CR (retorno de carro) do servidor. *Desativado*: apenas move para coluna 1. *LF*: também avança uma linha (útil para sistemas que enviam só CR como terminador). *CR+LF*: avança linha explicitamente. |
| **Comprimento da rolagem (em páginas)** | Quantas páginas de histórico o terminal guarda para scroll-back (padrão: 32). |
| **Largura inicial da tela** | Número de colunas da grade do terminal: 80 (padrão VT100) ou 132 (modo largo VT220). Também é enviado ao servidor via NAWS (negociação de tamanho). |
| **Altura inicial da tela (linhas)** | Número de linhas da grade do terminal (padrão: 24). Também é enviado ao servidor via NAWS. |

## 9. Configurações → Dispositivos

### 9.1. Configuração de impressão ✅

O ScanTE imprime o conteúdo da tela do terminal diretamente em impressoras térmicas via
**ESC/POS** — o protocolo padrão de impressoras Epson, Star, Citizen, Bixolon e similares.
A conexão pode ser por **Bluetooth** (impressora pareada com o coletor) ou **Wi-Fi/TCP**
(impressora de rede no galpão).

#### Conexão

| Campo | Para que serve |
|---|---|
| **Tipo de conexão** | **Bluetooth** — impressora pareada via BT clássico (SPP). **Wi-Fi** — impressora de rede (protocolo JetDirect, porta 9100). |
| **Dispositivo Bluetooth** | Seleciona qual impressora BT pareada usar. Pare a impressora no Android antes de selecionar aqui. |
| **Endereço IP** | IP da impressora na rede local (somente modo Wi-Fi). Exemplo: `192.168.1.100`. |
| **Porta** | Porta TCP da impressora — padrão **9100** (JetDirect). Alterar somente se a impressora usar outra porta. |

#### Impressora

| Campo | Para que serve |
|---|---|
| **Tipo de impressora** | Fabricante/modelo: Padrão, Epson ESC/POS, Star, Zebra ZPL, Citizen, Bixolon. Afeta o comando de corte (ZPL usa corte total; os demais usam corte parcial). |
| **Timeout de conexão (s)** | Segundos que o app aguarda a impressora responder antes de cancelar (padrão: 5). |

#### Como imprimir a tela do terminal

1. Configure a impressora em **Configurações → Dispositivos → Configuração de impressão**.
2. Pare a impressora no Android (se Bluetooth) ou verifique o IP (se Wi-Fi).
3. No terminal conectado, adicione o botão **PRINT** na barra de ferramentas
   (**Configurações → Tela → Barras de ferramentas**) ou use-o quando disponível.
4. Toque em **PRINT** — o app conecta, envia a tela atual e corta o papel automaticamente.

#### Imprimir página de teste

Na tela de configuração, toque em **Imprimir página de teste** para verificar se a
conexão com a impressora está funcionando corretamente.

### 9.2. Configuração do leitor de código de barras ✅

| Campo | Para que serve |
|---|---|
| **Tipo de dispositivo leitor** | Fabricante/modelo do coletor ou leitor de barras: Honeywell, Zebra, Datalogic, Bluebird, Urovo, Newland, Sunmi ou Genérico. |
| **Ação após verificação** | O que o app faz automaticamente após uma leitura bem-sucedida: *Nenhum* (só insere o texto), *Enter* (envia Enter), *Tab* (avança campo), *Enter + Tab*. |
| **Remover caracteres no início** | Descarta N caracteres do início do código lido (0–10). Útil para remover prefixos/identificadores do símbolo. |
| **Remover caracteres no final** | Descarta N caracteres do final do código lido (0–10). Útil para remover sufixos ou checkdigit extra. |
| **Adicione texto antes** | Texto fixo inserido antes do código lido ao enviar ao sistema. |
| **Adicione texto depois** | Texto fixo inserido depois do código lido ao enviar ao sistema. |
| **Usar mapeamento de teclado** | Quando ligado, o leitor é tratado como teclado físico — as teclas lidas passam pelo mapeamento configurado no terminal. |
| **Mostrar na linha de status** | Exibe um indicador na barra de status quando uma leitura é realizada. |

### 9.3. Log de sessão ✅

O ScanTE registra automaticamente todas as conexões realizadas e os comandos enviados ao
servidor, sem precisar ativar nenhuma configuração.

#### O que é registrado?

| Dado | Descrição |
|---|---|
| **Sessão** | Servidor conectado (host:porta), data/hora de início e fim, duração, motivo de desconexão |
| **Comandos** | Cada texto enviado ao servidor pelo campo de entrada (Enter pressionado) |

#### Onde fica?

As informações são salvas localmente no dispositivo em **SharedPreferences** (`telnet_session_log`).
Elas permanecem disponíveis entre reinicializações do app, até que sejam apagadas.

#### Limites de armazenamento

| Tipo | Limite |
|---|---|
| **Sessões** | Últimas 100 sessões (as mais antigas são removidas automaticamente) |
| **Comandos** | Últimos 500 comandos enviados em todas as sessões |

#### Ciclo de vida de uma sessão

1. **Conectar** → ScanTE cria um registro de sessão com `startTime` = agora.
2. **Enviar comandos** → cada texto digitado é salvo vinculado ao `sessionId`.
3. **Desconectar** → o registro recebe `endTime` e o motivo (ex: "User disconnect", "Connection lost").

> **Nota:** Os dados ficam apenas no dispositivo. Para exportar o histórico, use os recursos de
> backup do Android ou faça uma exportação de sessões pelo menu de Sessões.

---

## 10. Teclado personalizado ScanTE ✅

O ScanTE inclui um teclado próprio (IME) otimizado para uso em terminais.
Para ativá-lo: **Configurações do Android → Gerenciar teclados → ScanTE Keyboard** (ligar).

### Como usar

| Gesto / Botão | Ação |
|---|---|
| Deslize para a esquerda | Vai para a próxima página do teclado |
| Deslize para a direita | Volta para a página anterior |
| Toque em "ABC / 123 / P1…" (abas) | Navega diretamente para aquela página |
| **⇧** (shift) | Ativa maiúsculas (próximo caractere) · toque novamente = fixo |
| **⇪** | Capslock ativado |
| **?!** | Abre teclado de símbolos especiais |
| **↵** | Envia Enter ao sistema |
| **⌫** | Apaga o último caractere |

### Páginas padrão

| Página | Conteúdo |
|---|---|
| **ABC** | QWERTY completo com números, símbolos e espaço |
| **123** | Teclado numérico (0–9, operadores) + linha de símbolos configurável |

### Configurar o teclado

Acesse **Configurações → Teclado → Configurar teclado ScanTE**:

| Opção | O que faz |
|---|---|
| **Página QWERTY (letras)** | Ativa/desativa a página ABC no teclado |
| **Página numérica (0-9)** | Ativa/desativa a página 123 |
| **Linha de símbolos** | Define os caracteres exibidos na linha scrollável do topo da página numérica |
| **＋ Adicionar página** | Cria uma nova página personalizada (escolha de um preset ou em branco) |

### Páginas personalizadas

Adicione quantas páginas quiser com botões de terminal (setas, teclas Fn, Ctrl+letra,
comandos customizados). Cada página é editável: adicione/remova botões individualmente
ou use presets prontos (Navegação, Teclas F, Ctrl A-Z, Símbolos…).

---

## 11. Calculadora flutuante ✅

O ScanTE inclui uma **calculadora que flutua sobre qualquer tela** do app e pode ser
minimizada para um botão arrastável, sem interromper o trabalho no terminal.

### Como abrir

- Na tela de **Sessões** → menu **3 pontos** → **Calculadora**
- Na tela do **Terminal** → menu **3 pontos** → **Calculadora**

Na primeira vez, o Android pedirá permissão para "exibir sobre outros apps" — toque em
**Permitir** e volte ao ScanTE; a calculadora abrirá automaticamente.

### Usar a calculadora

| Botão | Função |
|---|---|
| **C** | Limpa tudo (começa do zero) |
| **±** | Inverte o sinal do número (positivo/negativo) |
| **%** | Divide por 100 (percentual) |
| **÷ × - +** | Operações aritméticas |
| **=** | Calcula o resultado |
| **.** | Ponto decimal |

### Minimizar e mover

| Ação | Como fazer |
|---|---|
| **Minimizar** | Toque no ícone **⬇** no cabeçalho — a calculadora vira um botão 🧮 |
| **Restaurar** | Toque no botão 🧮 |
| **Mover** | Arraste pelo cabeçalho "⠿  Calculadora" (janela completa) ou arraste o botão 🧮 (minimizado) |
| **Fechar** | Toque no **✕** no cabeçalho, ou na notificação → **Fechar** |

A calculadora permanece visível mesmo ao trocar de tela dentro do app.

---

## 12. Painel de Administração — Dispositivos ✅

O painel **scante-admin** inclui uma seção **Dispositivos** que exibe todos os aparelhos
que já abriram o app, com ou sem licença.

### Como funciona

A cada vez que o app é aberto, ele envia automaticamente ao servidor as seguintes informações:
- Nome e ID do dispositivo
- Versão do app
- Chave de licença (se houver)

Essas informações ficam registradas e visíveis no painel em **Menu → Dispositivos**.

### Dashboard — Dispositivos

| Card | O que mostra |
|---|---|
| **Total** | Quantidade de dispositivos distintos que já abriram o app |
| **Online agora** | Dispositivos que abriram o app nos últimos 5 minutos |
| **Com licença ativa** | Aparelhos com licença ativa vinculada |
| **Sem licença** | Aparelhos que ainda não ativaram uma licença |

### Tabela de dispositivos

| Coluna | Descrição |
|---|---|
| **Dispositivo** | Nome do aparelho (Fabricante + Modelo) e Device ID (truncado) |
| **Empresa** | Empresa vinculada à licença (quando houver) |
| **Licença** | Chave SCTE ativada no dispositivo (quando houver) |
| **Status** | Ativa · Trial · Expirada · Revogada · Sem licença |
| **Primeiro acesso** | Data/hora da primeira abertura do app neste aparelho |
| **Último acesso** | Data/hora da última abertura — exibe badge **Online** quando < 5 min |
| **Versão** | Versão do app instalado no aparelho |

### Filtros disponíveis

- **Buscar:** pesquisa por nome do dispositivo, empresa ou chave de licença.
- **Status Licença:** filtra por Ativa, Sem licença, Expirada, Revogada ou Trial.
- **Presença:** exibe apenas dispositivos Online agora ou Offline.

---

## 13. Glossário rápido

- **Telnet:** protocolo de terminal por texto (RFC 854).
- **CR / LF / CR+LF:** caracteres invisíveis de "fim de linha" enviados ao apertar Enter.
- **Tipo de terminal (VT100/VT220):** "modelo" de terminal que o sistema usa para desenhar a tela.
- **Modo binário:** transmissão de 8 bits (necessário para acentos).
- **Paridade:** bit extra de verificação em links antigos de 7 bits.
- **Keep-alive:** mensagens periódicas para manter a conexão viva.
- **Proxy:** servidor intermediário entre o app e o sistema.

---

*Documento atualizado conforme novas funcionalidades são concluídas.*
