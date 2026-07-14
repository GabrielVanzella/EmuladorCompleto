-- ============================================================
-- Migração: licenças do ScanTE Relay
-- Rode uma única vez no banco já existente (local ou Hostinger).
-- ============================================================

CREATE TABLE IF NOT EXISTS relay_licencas (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente           VARCHAR(200) NOT NULL,
  serial            VARCHAR(50) NOT NULL UNIQUE,
  max_sessions      INT UNSIGNED NOT NULL DEFAULT 0,
  max_devices       INT UNSIGNED NOT NULL DEFAULT 0,
  expira_em         DATE NOT NULL,
  release_suportado VARCHAR(20) NULL,
  server_host       VARCHAR(100) NULL,
  licenca_texto     TEXT NOT NULL,
  criado_por        INT UNSIGNED NULL,
  criada_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
