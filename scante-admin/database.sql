-- ============================================================
-- ScanTE Admin — Banco de dados
-- Execute: mysql -u root -p scante_admin < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS scante_admin
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE scante_admin;

-- Empresas clientes
CREATE TABLE empresas (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(200) NOT NULL,
  cnpj       VARCHAR(20),
  email      VARCHAR(150) NOT NULL,
  telefone   VARCHAR(30),
  contato    VARCHAR(150),
  ativo      TINYINT(1) NOT NULL DEFAULT 1,
  criada_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuários (admin + usuários das empresas)
CREATE TABLE usuarios (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(150) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  senha      VARCHAR(255) NOT NULL,
  tipo       ENUM('admin','empresa') NOT NULL DEFAULT 'empresa',
  empresa_id INT UNSIGNED NULL,
  ativo      TINYINT(1) NOT NULL DEFAULT 1,
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Licenças
CREATE TABLE licencas (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chave         VARCHAR(24) NOT NULL UNIQUE,      -- SCTE-XXXXXX-XXXXXX-XXXXXX
  empresa_id    INT UNSIGNED NULL,
  tipo          ENUM('trial','mensal','anual','vitalicia') NOT NULL DEFAULT 'trial',
  quantidade    INT UNSIGNED NOT NULL DEFAULT 1,  -- nº de licenças do mesmo pedido (checkout em lote)
  anos_suporte  TINYINT UNSIGNED NULL,            -- anos de suporte escolhidos no checkout em lote
  status        ENUM('trial','ativa','expirada','revogada','pendente') NOT NULL DEFAULT 'trial',
  device_id     VARCHAR(100) NULL,
  device_nome   VARCHAR(200) NULL,
  email         VARCHAR(150) NULL,
  telefone      VARCHAR(30) NULL,
  vinculada_em  DATETIME NULL,
  ultimo_acesso DATETIME NULL,
  expira_em     DATETIME NULL,                    -- NULL = vitalícia
  payment_id    VARCHAR(100) NULL,
  criada_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
  INDEX idx_chave (chave),
  INDEX idx_device (device_id),
  INDEX idx_status (status),
  INDEX idx_expira (expira_em),
  INDEX idx_payment (payment_id)
) ENGINE=InnoDB;

-- Histórico de dispositivos (vincular / transferência)
CREATE TABLE historico_dispositivos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  licenca_id  INT UNSIGNED NOT NULL,
  device_id   VARCHAR(100) NOT NULL,
  device_nome VARCHAR(200),
  acao        ENUM('vincular','transferencia') NOT NULL,
  motivo      VARCHAR(300),
  usuario_id  INT UNSIGNED NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (licenca_id) REFERENCES licencas(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Pagamentos
CREATE TABLE pagamentos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  licenca_id  INT UNSIGNED NOT NULL,
  payment_id  VARCHAR(100) NOT NULL,
  valor       DECIMAL(10,2),
  tipo        VARCHAR(50),
  status      VARCHAR(50) DEFAULT 'approved',
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (licenca_id) REFERENCES licencas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Configurações (gateway de pagamento, chaves)
CREATE TABLE configuracoes (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chave         VARCHAR(100) NOT NULL UNIQUE,
  valor         TEXT NULL,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Dispositivos (ping do app Android)
CREATE TABLE dispositivos (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id       VARCHAR(100) NOT NULL UNIQUE,
  device_nome     VARCHAR(200) NULL,
  app_version     VARCHAR(50) NULL,
  licenca_id      INT UNSIGNED NULL,
  empresa_id      INT UNSIGNED NULL,
  empresa_nome    VARCHAR(200) NULL,
  status_licenca  VARCHAR(30) NOT NULL DEFAULT 'sem_licenca',
  chave_licenca   VARCHAR(24) NULL,
  primeiro_acesso DATETIME NULL,
  ultimo_acesso   DATETIME NULL,
  INDEX idx_status_licenca (status_licenca),
  INDEX idx_ultimo_acesso (ultimo_acesso)
) ENGINE=InnoDB;

-- Licenças do ScanTE Relay (offline, assinadas com Ed25519 — ver RelayLicenseService)
CREATE TABLE relay_licencas (
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
) ENGINE=InnoDB;

-- ============================================================
-- Usuário admin padrão
-- Senha: admin123 (ALTERE após o primeiro login!)
-- ============================================================
INSERT INTO usuarios (nome, email, senha, tipo)
VALUES (
  'Administrador',
  'admin@scante.com',
  '$2y$10$Ibe8kenBumX1n0J0DaK1eewapluSNcgWhCIgmw6j/.tbJpivEftVy', -- admin123
  'admin'
);
