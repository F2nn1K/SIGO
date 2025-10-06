# 🚀 Guia de Deploy - Sistema BRS (SIGO)

Este guia contém instruções passo a passo para deploy do sistema em ambiente de produção.

---

## 📋 Pré-requisitos

### Servidor
- PHP 8.1 ou superior
- MySQL 8.0+ ou MariaDB 10.3+
- Apache 2.4+ com mod_rewrite habilitado
- Composer 2.0+
- Extensões PHP necessárias (ver README.md)

### Acesso
- SSH ao servidor
- Acesso ao banco de dados MySQL
- Permissões de escrita em `storage/` e `bootstrap/cache/`

---

## 🛠️ Passo a Passo

### 1. Preparar o Ambiente

```bash
# Conectar via SSH
ssh usuario@seuservidor.com.br

# Navegar até o diretório web
cd ~/public_html  # ou caminho do seu webroot
```

### 2. Clonar o Repositório

```bash
# Fazer backup do diretório atual (se houver)
mv beta2 beta2_backup_$(date +%Y%m%d)

# Clonar o repositório
git clone https://github.com/F2nn1K/SIGO.git beta2
cd beta2
```

### 3. Instalar Dependências

```bash
# Instalar dependências PHP
composer install --optimize-autoloader --no-dev

# Limpar caches antigos
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### 4. Configurar Ambiente

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar configurações
nano .env  # ou vi .env
```

Configure as seguintes variáveis no `.env`:

```env
APP_NAME="Sistema BRS"
APP_ENV=production
APP_KEY=  # Será gerado no próximo passo
APP_DEBUG=false
APP_URL=https://seudominio.com.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_banco_aqui
DB_USERNAME=seu_usuario_aqui
DB_PASSWORD=sua_senha_segura_aqui

MAIL_MAILER=log  # Ou configure SMTP se for usar e-mail

LICENSE_ENABLED=true
LICENSE_ALLOWED_DOMAIN=seudominio.com.br
```

### 5. Gerar Chave da Aplicação

```bash
php artisan key:generate
```

### 6. Configurar Permissões de Diretórios

```bash
# Dar permissões corretas
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # Ajustar usuário conforme servidor

# Criar link simbólico do storage
php artisan storage:link
```

### 7. Configurar o Banco de Dados

**IMPORTANTE**: Este sistema NÃO utiliza migrations. A estrutura do banco deve ser criada manualmente.

```bash
# Fazer backup do banco atual (se houver)
mysqldump -u seu_usuario -p seu_banco > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar estrutura do banco (se você tem um dump)
mysql -u seu_usuario -p seu_banco < estrutura_banco.sql
```

### 8. Otimizar para Produção

```bash
# Cachear configurações
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar autoload do Composer
composer dump-autoload --optimize --classmap-authoritative
```

### 9. Configurar DocumentRoot

**Para Hostinger/cPanel:**

O webroot deve apontar para `public_html` (já está correto se você clonou na raiz).

Estrutura esperada:
```
~/
├── beta2/              # Código da aplicação Laravel
│   ├── app/
│   ├── config/
│   ├── resources/
│   └── ...
└── public_html/        # Webroot público (DocumentRoot)
    ├── index.php
    ├── .htaccess
    ├── css/
    ├── js/
    └── vendor/
```

**Para Apache virtual host:**

```apache
<VirtualHost *:80>
    ServerName seudominio.com.br
    DocumentRoot /var/www/SIGO/public_html

    <Directory /var/www/SIGO/public_html>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sigo-error.log
    CustomLog ${APACHE_LOG_DIR}/sigo-access.log combined
</VirtualHost>
```

### 10. Configurar o Licenciamento

```bash
# Criar diretório de licença
mkdir -p storage/app/license
chmod 775 storage/app/license

# Fazer upload dos arquivos de licença
# - license.lic (arquivo de licença)
# - license_pub.pem (chave pública)
```

Ou acesse via navegador:
```
https://seudominio.com.br/license
```

E faça upload pela interface.

### 11. Testar a Aplicação

```bash
# Verificar se há erros
php artisan config:cache
tail -f storage/logs/laravel.log
```

Acesse via navegador:
```
https://seudominio.com.br
```

---

## 🔄 Atualizações Futuras

### Atualizar o Sistema

```bash
# Navegar até o diretório
cd ~/beta2

# Fazer backup
tar -czf backup_$(date +%Y%m%d_%H%M%S).tar.gz ../beta2

# Backup do banco
mysqldump -u usuario -p banco > db_backup_$(date +%Y%m%d_%H%M%S).sql

# Atualizar código
git pull origin main

# Atualizar dependências
composer install --optimize-autoloader --no-dev

# Limpar e refazer cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🐛 Troubleshooting

### Erro 500 - Internal Server Error

1. Verificar permissões de `storage/` e `bootstrap/cache/`
2. Checar `storage/logs/laravel.log` para detalhes
3. Verificar se `.env` está configurado corretamente
4. Confirmar que `APP_KEY` está definido

```bash
# Ver últimas linhas do log
tail -50 storage/logs/laravel.log
```

### Erro 403 - Forbidden

1. Verificar permissões de arquivos
2. Checar configuração do `.htaccess`
3. Confirmar que mod_rewrite está habilitado

```bash
# Verificar mod_rewrite (Apache)
apachectl -M | grep rewrite
```

### Página em branco

1. Verificar `APP_DEBUG=true` temporariamente (apenas para debug)
2. Checar logs do PHP e Apache
3. Verificar se o `index.php` está acessível

### Erro de permissões no banco

```sql
-- Conceder permissões corretas ao usuário
GRANT SELECT, INSERT, UPDATE, DELETE ON nome_banco.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Cache desatualizado

```bash
# Limpar todos os caches
php artisan optimize:clear
```

---

## 📊 Monitoramento

### Logs Importantes

```bash
# Log da aplicação Laravel
tail -f storage/logs/laravel.log

# Log do Apache (caminho pode variar)
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

### Verificar Status do Sistema

```bash
# Versão do PHP
php -v

# Extensões PHP instaladas
php -m

# Verificar conexão com banco
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 🔐 Segurança Pós-Deploy

### Checklist de Segurança

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_ENV=production` no `.env`
- [ ] Senha forte do banco de dados
- [ ] HTTPS configurado (SSL/TLS)
- [ ] Arquivos `.env` e `.git` protegidos
- [ ] Permissões corretas em `storage/` (775 ou 755)
- [ ] Backup automático configurado
- [ ] Logs sendo monitorados
- [ ] Rate limiting ativo
- [ ] Licença válida configurada

### Hardening Adicional (Opcional)

```bash
# Desabilitar listagem de diretórios
# Já configurado no .htaccess com: Options -Indexes

# Restringir acesso SSH apenas por chave
# Editar /etc/ssh/sshd_config:
# PasswordAuthentication no

# Firewall - permitir apenas portas necessárias
# ufw allow 80/tcp
# ufw allow 443/tcp
# ufw allow 22/tcp
# ufw enable
```

---

## 📞 Suporte

Em caso de problemas durante o deploy:

1. Verifique os logs: `storage/logs/laravel.log`
2. Consulte a seção de Troubleshooting acima
3. Entre em contato: leo.vdf3@gmail.com

---

**Última atualização**: Outubro 2025

