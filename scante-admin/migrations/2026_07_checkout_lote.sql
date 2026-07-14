-- ============================================================
-- Migração: checkout em lote (quantidade + anos de suporte)
-- Rode uma única vez no banco já existente (local ou Hostinger).
-- Todas as colunas são nullable/com default — seguro rodar em
-- cima de dados de produção sem perder nada.
-- ============================================================

ALTER TABLE licencas
  ADD COLUMN email        VARCHAR(150) NULL AFTER device_nome,
  ADD COLUMN telefone     VARCHAR(30)  NULL AFTER email,
  ADD COLUMN quantidade   INT UNSIGNED NOT NULL DEFAULT 1 AFTER tipo,
  ADD COLUMN anos_suporte TINYINT UNSIGNED NULL AFTER quantidade,
  ADD INDEX idx_payment (payment_id);
