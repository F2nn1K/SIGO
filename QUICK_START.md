# ⚡ Início Rápido - Sistema BRS (SIGO)

Guia rápido para ter o sistema rodando em minutos.

---

## 🚀 Setup em 5 Minutos

### 1️⃣ Clone e Instale

```bash
git clone https://github.com/F2nn1K/SIGO.git
cd SIGO
composer install --optimize-autoloader --no-dev
```

### 2️⃣ Configure

```bash
cp .env.example .env
php artisan key:generate
```

Edite `.env` com suas credenciais:
```env
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
APP_URL=https://seudominio.com.br
LICENSE_ALLOWED_DOMAIN=seudominio.com.br
```

### 3️⃣ Crie a Estrutura do Banco

**IMPORTANTE**: Execute o SQL da estrutura do banco manualmente (não há migrations).

### 4️⃣ Configure Permissões

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

### 5️⃣ Otimize e Inicie

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Acesse: `https://seudominio.com.br`

---

## 🎯 Credenciais Padrão

Após popular o banco:
- **Usuário**: admin
- **Senha**: (conforme configurado no banco)

⚠️ **IMPORTANTE**: Altere a senha padrão após primeiro login!

---

## 📦 Estrutura de Diretórios

```
Seu servidor/
├── beta2/              ← Código Laravel (fora do webroot)
│   ├── app/
│   ├── config/
│   ├── resources/
│   ├── routes/
│   └── storage/
│
└── public_html/        ← Webroot público (DocumentRoot)
    ├── index.php
    ├── .htaccess
    ├── css/
    ├── js/
    └── vendor/
```

---

## 🔑 Permissões Principais

| Permissão | Descrição |
|-----------|-----------|
| `doc_dp` | Inclusão de documentos DP |
| `vis_func` | Gestão de funcionários |
| `controle-estoque` | Módulo de estoque |
| `solicitacao-pedidos` | Criar pedidos |
| `autorizacao-pedidos` | Aprovar pedidos |
| `veiculos` | Gestão de frota |
| `gerenciar-usuarios` | Admin - usuários |
| `gerenciar-permissoes` | Admin - permissões |

Perfil **Admin** tem acesso total automático.

---

## 🛠️ Comandos Úteis

```bash
# Limpar cache
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear

# Recriar cache (produção)
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Ver logs
tail -f storage/logs/laravel.log

# Verificar conexão com banco
php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 📱 Módulos Disponíveis

- ✅ **DP**: Gestão de funcionários e documentos
- ✅ **Pedidos**: Solicitação e autorização de compras
- ✅ **Estoque**: Controle com mín/máx
- ✅ **Frota**: Veículos, abastecimentos, manutenções
- ✅ **Relatórios**: DP, Estoque, Frota, Pedidos
- ✅ **Admin**: Usuários, perfis e permissões

---

## ⚠️ Troubleshooting

### Erro 500
```bash
# Ver o erro
tail -50 storage/logs/laravel.log

# Verificar permissões
chmod -R 775 storage bootstrap/cache
```

### Página em branco
```bash
# Temporariamente ativar debug
# Editar .env: APP_DEBUG=true
# Ver erro no navegador
# LEMBRAR de voltar para false depois!
```

### Assets não carregam
```bash
# Verificar DocumentRoot
# Deve apontar para public_html/

# Verificar .htaccess
ls -la public_html/.htaccess
```

### Licença expirada
```bash
# Acessar tela de upload
https://seudominio.com.br/license

# Ou desabilitar temporariamente
# .env: LICENSE_ENABLED=false
```

---

## 📞 Precisa de Ajuda?

1. 📖 Leia a [Documentação Completa](README.md)
2. 🚀 Veja o [Guia de Deploy](DEPLOY.md)
3. 🏗️ Entenda a [Arquitetura](ARCHITECTURE.md)
4. 📧 Entre em contato: leo.vdf3@gmail.com

---

**Tempo estimado de setup**: 5-10 minutos  
**Dificuldade**: ⭐⭐ Fácil (com conhecimento básico de Laravel)

Bom trabalho! 🎉

