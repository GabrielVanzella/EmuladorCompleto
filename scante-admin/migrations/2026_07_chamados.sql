-- ============================================================
-- Migração: chamados de suporte (portal da empresa)
-- Rode uma única vez no banco já existente (local ou Hostinger).
-- ============================================================

-- Chamado aberto por uma empresa, opcionalmente ligado a uma licença dela.
CREATE TABLE IF NOT EXISTS chamados (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id    INT UNSIGNED NOT NULL,
  licenca_id    INT UNSIGNED NULL,            -- NULL = assunto geral (não é de uma licença específica)
  usuario_id    INT UNSIGNED NULL,            -- usuário da empresa que abriu
  assunto       VARCHAR(200) NOT NULL,
  status        ENUM('aberto','em_andamento','resolvido') NOT NULL DEFAULT 'aberto',
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
  FOREIGN KEY (licenca_id) REFERENCES licencas(id) ON DELETE SET NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_empresa (empresa_id),
  INDEX idx_status (status),
  INDEX idx_atualizado (atualizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mensagens da conversa dentro de um chamado (empresa <-> equipe ScanTE).
-- A descrição inicial do chamado é a primeira mensagem (autor_tipo='empresa').
CREATE TABLE IF NOT EXISTS chamado_mensagens (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chamado_id  INT UNSIGNED NOT NULL,
  autor_tipo  ENUM('empresa','admin') NOT NULL,
  autor_id    INT UNSIGNED NULL,
  autor_nome  VARCHAR(150) NULL,
  mensagem    TEXT NOT NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
  INDEX idx_chamado (chamado_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
