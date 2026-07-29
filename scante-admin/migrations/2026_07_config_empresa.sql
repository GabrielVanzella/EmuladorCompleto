-- ============================================================
-- Migração: personalização por empresa (Aparência + Teclas)
-- A própria empresa configura o visual/teclas do app no portal dela;
-- todos os coletores da empresa herdam (travado). Entregue via a API
-- de licença e cacheado no app.
-- Rode uma única vez no banco já existente (local ou Hostinger).
-- ============================================================

ALTER TABLE empresas
  ADD COLUMN config_tema   TEXT NULL          AFTER ativo,   -- JSON: cores + cabeçalho
  ADD COLUMN config_teclas TEXT NULL          AFTER config_tema, -- JSON: barras de ferramentas (List<List<botão>>)
  ADD COLUMN config_versao INT UNSIGNED NOT NULL DEFAULT 0 AFTER config_teclas; -- sobe a cada salvar (cache-busting no app)

-- A logo de cada empresa é salva em: public/uploads/logos/{empresa_id}.png
-- (criada no upload; a URL é montada com ?v=config_versao pra furar cache)
