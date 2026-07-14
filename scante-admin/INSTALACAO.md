# ScanTE Admin — Instalação

## Requisitos
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.4+
- Apache com mod_rewrite habilitado

## Passos

### 1. Banco de dados
```bash
mysql -u root -p -e "CREATE DATABASE scante_admin CHARACTER SET utf8mb4"
mysql -u root -p scante_admin < database.sql
```

### 2. Configuração
Edite `config/config.php`:
```php
define('APP_URL',  'http://seu-dominio.com/scante-admin/public');
define('DB_HOST',  'localhost');
define('DB_NAME',  'scante_admin');
define('DB_USER',  'seu_usuario');
define('DB_PASS',  'sua_senha');
define('API_SECRET', 'chave_secreta_para_o_app_android');
```

### 3. Apache (VirtualHost)
O DocumentRoot deve apontar para a pasta `public/`:
```apache
<VirtualHost *:80>
    ServerName admin.scante.com
    DocumentRoot /var/www/scante-admin/public
    <Directory /var/www/scante-admin/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4. Primeiro acesso
- URL: `http://seu-dominio/login`
- E-mail: `admin@scante.com`
- Senha: `admin123`  ← **altere imediatamente!**

---

## Estrutura de áreas

| Área | URL | Acesso |
|---|---|---|
| Admin | `/admin` | Você (desenvolvedor) |
| Empresa | `/empresa` | Clientes |
| API Android | `/api/licenca/validar` | App ScanTE |

---

## API para o app Android

### Validar licença
```
POST /api/licenca/validar
Authorization: Bearer SUA_API_SECRET

{
  "chave":       "SCTE-XXXXXX-XXXXXX-XXXXXX",
  "device_id":   "android_id_do_dispositivo",
  "device_nome": "Coletor Motorola MC330"
}
```

**Retorno sucesso:**
```json
{ "valida": true, "tipo": "ativa", "dias_restantes": 28, "vitalicia": false }
```

**Retorno erro:**
```json
{ "valida": false, "mensagem": "Licença vinculada a outro dispositivo." }
```
