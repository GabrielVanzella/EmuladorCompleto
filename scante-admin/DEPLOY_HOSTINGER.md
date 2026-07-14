# Deploy na Hostinger — ScanTE Admin

Banco: `u508103998_scante` · Usuário: `u508103998_scante_user` · Domínio: `scante.com.br`

Pasta no servidor (atual): `public_html/scante-admin/`

---

## 1. Criar as tabelas (phpMyAdmin)

1. hPanel → **Bancos de dados** → **phpMyAdmin** no banco `u508103998_scante`.
2. Selecione o banco `u508103998_scante` na coluna da esquerda.
3. Aba **Importar** → escolha o arquivo **`database_hostinger.sql`** → **Executar**.

> Use `database_hostinger.sql` (NÃO o `database.sql`). O `database.sql` tem `CREATE DATABASE`/`USE`
> que não funcionam na Hostinger. O arquivo de produção cria as 7 tabelas e o usuário admin.

Ao final você deve ver as tabelas: `empresas, usuarios, licencas, historico_dispositivos,
pagamentos, configuracoes, dispositivos`.

## 2. Configurar a senha do banco

Edite **`config/config.php`** e troque a linha da senha pela senha real do usuário MySQL:

```php
define('DB_PASS', 'SUA_SENHA_REAL_AQUI');
```

Depois **re-envie o `config/config.php`** por FTP (ou edite direto no **Gerenciador de
Arquivos** do hPanel). Os demais valores (host `localhost`, nome e usuário do banco,
`API_SECRET`) já estão preenchidos.

## 3. Confirmar arquivos enviados

Garanta que estes também subiram por FTP (o FTP costuma ignorar arquivos que começam com ponto):
- `.htaccess` na raiz de `scante-admin/` (protege `config/`, `app/` e os `.sql`)
- `public/.htaccess`

## 4. Acessar o painel

Com a estrutura atual (subpasta), a URL é:

```
https://scante.com.br/scante-admin/public/login
```

Login inicial: **admin@scante.com** / **admin123** → **troque a senha logo após entrar.**

## 5. (Opcional, recomendado) URL limpa: scante.com.br

Para o painel abrir em `https://scante.com.br` (sem `/scante-admin/public`), escolha UMA opção:

- **Mudar Document Root** (hPanel → Avançado/Website → apontar para `.../scante-admin/public`), ou
- Mover o conteúdo de `public/` para dentro de `public_html/` e `app/` + `config/` para a pasta
  acima de `public_html/`.

Depois, em `config/config.php`, troque para:
```php
define('APP_URL', 'https://scante.com.br');
```

## Checklist
- [ ] SQL importado (`database_hostinger.sql`)
- [ ] `DB_PASS` preenchida e `config.php` re-enviado
- [ ] `.htaccess` (raiz e public) no servidor
- [ ] SSL ativo (cadeado) no domínio
- [ ] Login OK e senha do admin trocada
