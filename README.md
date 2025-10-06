# 🏢 Sistema BRS (SIGO) - Sistema Integrado de Gestão Organizacional

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.x-blue?style=for-the-badge)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-Proprietário-yellow?style=for-the-badge)

Sistema completo de gestão empresarial desenvolvido em Laravel 10 com interface AdminLTE 3. Gerencia Recursos Humanos (DP), Pedidos de Compras, Controle de Estoque, Gestão de Frota e Relatórios avançados com sistema robusto de permissões granulares.

---

## 📋 Índice

- [Funcionalidades](#-funcionalidades)
- [Tecnologias](#-tecnologias)
- [Requisitos](#-requisitos)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Módulos Principais](#-módulos-principais)
- [Sistema de Permissões](#-sistema-de-permissões)
- [Segurança](#-segurança)
- [Performance](#-performance)
- [Contribuição](#-contribuição)
- [Licença](#-licença)
- [Suporte](#-suporte)

---

## ✨ Funcionalidades

### 📋 Departamento Pessoal (DP)
- ✅ Cadastro completo de funcionários com upload de documentos (PDF/Imagens)
- ✅ Gestão de atestados médicos com controle de períodos
- ✅ Registro e acompanhamento de advertências
- ✅ Controle de EPIs (Equipamentos de Proteção Individual)
- ✅ Gerenciamento de contracheques, férias, 13º salário e rescisões
- ✅ Controle de frequência e certificados
- ✅ Ordem de Serviço com histórico completo
- ✅ Geração de dossiê completo em ZIP por funcionário
- ✅ Relatórios de absenteísmo e funcionários ativos/inativos

### 🛒 Pedidos de Compras
- ✅ Solicitação de pedidos com numeração automática
- ✅ Sistema de autorização com aprovação/rejeição em lote
- ✅ Agrupamento inteligente por envio
- ✅ Mensagens e interações entre solicitante e autorizador
- ✅ Duplicação de pedidos anteriores
- ✅ Bloqueio de itens por usuário
- ✅ Gerenciamento de produtos e preços
- ✅ Relatórios por centro de custo e período
- ✅ Filtro de pedidos por mês
- ✅ Layout de impressão otimizado

### 📦 Controle de Estoque
- ✅ Catálogo completo de produtos
- ✅ Controle de entradas e saídas
- ✅ Estoque mínimo e máximo com alertas
- ✅ Rastreamento por centro de custo
- ✅ Histórico de movimentações por funcionário
- ✅ Relatórios de estoque com filtros avançados
- ✅ Exportação para Excel

### 🚗 Gestão de Frota
- ✅ Cadastro e controle de veículos
- ✅ Registro de abastecimentos com cálculo de consumo
- ✅ Gerenciamento de manutenções preventivas e corretivas
- ✅ Controle de viagens e quilometragem
- ✅ Ocorrências da frota com upload de fotos
- ✅ Gestão de licenciamento anual com alertas de vencimento
- ✅ Conferência de Notas Fiscais de abastecimento
- ✅ Relatórios de consumo, custo total, km percorrido e manutenções

### 🌿 Roçagem
- ✅ Controle de equipamentos de roçagem
- ✅ Registro de manutenções específicas
- ✅ Gerenciamento de abastecimentos

### 📊 Relatórios Avançados
- ✅ Relatórios de estoque por produto, centro de custo e funcionário
- ✅ Relatórios de pedidos de compras com status
- ✅ Relatórios de DP (funcionários, documentos, absenteísmo)
- ✅ Relatórios de frota (abastecimento, consumo, custo, manutenções)
- ✅ Exportação em Excel de todos os relatórios
- ✅ Filtros por período, status e categorias

### 👥 Administração
- ✅ Gerenciamento completo de usuários
- ✅ Sistema de perfis e permissões granulares
- ✅ Dashboard executivo com gráficos e indicadores
- ✅ Sistema de licenciamento com assinatura digital
- ✅ Logs de auditoria

---

## 🛠️ Tecnologias

### Backend
- **Laravel 10.x** - Framework PHP moderno e robusto
- **PHP 8.1+** - Linguagem de programação
- **MySQL 8.0+** - Banco de dados relacional
- **Eloquent ORM** - Object-Relational Mapping
- **Laravel Sanctum** - Autenticação API
- **PhpSpreadsheet** - Exportação Excel
- **Carbon** - Manipulação de datas

### Frontend
- **AdminLTE 3.x** - Template administrativo responsivo
- **Bootstrap 5.x** - Framework CSS
- **jQuery 3.x** - Biblioteca JavaScript
- **SweetAlert2** - Modais e alertas modernos
- **DataTables** - Tabelas interativas
- **FontAwesome** - Ícones
- **Chart.js** - Gráficos (Dashboard)

### DevOps & Infraestrutura
- **Composer** - Gerenciador de dependências PHP
- **NPM** - Gerenciador de dependências JavaScript
- **Vite** - Build tool (opcional)
- **Apache/Nginx** - Servidor web
- **Git** - Controle de versão

---

## 📋 Requisitos

### Requisitos Mínimos
- PHP >= 8.1
- MySQL >= 8.0 ou MariaDB >= 10.3
- Apache 2.4+ ou Nginx 1.18+
- Composer >= 2.0
- Node.js >= 16.x e NPM >= 8.x (opcional, para build de assets)
- Extensões PHP:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD ou Imagick (para manipulação de imagens)
  - Zip (para geração de arquivos compactados)

### Requisitos Recomendados
- PHP 8.2+
- 2GB RAM
- 50GB de armazenamento (dependendo do volume de arquivos)
- SSL/TLS configurado (HTTPS)
- mod_rewrite habilitado (Apache) ou configuração equivalente (Nginx)

---

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/F2nn1K/SIGO.git
cd SIGO
```

### 2. Instale as dependências do Composer

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Configure o arquivo de ambiente

```bash
cp .env.example .env
```

Edite o arquivo `.env` e configure:
- Dados do banco de dados (DB_*)
- URL da aplicação (APP_URL)
- Domínio da licença (LICENSE_ALLOWED_DOMAIN)

### 4. Gere a chave da aplicação

```bash
php artisan key:generate
```

### 5. Configure o banco de dados

**Importante**: Este sistema **não utiliza migrations**. A estrutura do banco de dados deve ser criada manualmente ou restaurada a partir de um backup.

Estrutura mínima necessária:
- `users`, `profiles`, `permissions`, `profile_permissions`
- `funcionarios` e tabelas relacionadas (documentos, atestados, etc.)
- `estoque`, `baixas`, `centro_custo`
- `solicitacao`, `estoque_pedido`, `interacao`
- `veiculos`, `abastecimentos`, `manutencoes`, `viagens`
- E outras conforme os módulos utilizados

### 6. Configure o storage

```bash
php artisan storage:link
```

Crie os diretórios necessários:
```bash
mkdir -p storage/app/public/documentos
mkdir -p storage/app/public/atestados
mkdir -p storage/app/public/advertencias
mkdir -p storage/app/license
chmod -R 775 storage bootstrap/cache
```

### 7. (Opcional) Compile os assets

```bash
npm install
npm run build
```

### 8. Configure o servidor web

#### Apache (.htaccess)
O arquivo `.htaccess` já está incluído em `public_html/` com:
- Cache agressivo de assets (1 ano)
- Compressão Brotli/Gzip
- Security headers
- Bloqueio de arquivos sensíveis

#### Nginx
Exemplo de configuração básica:

```nginx
server {
    listen 80;
    server_name seudominio.com.br;
    root /caminho/para/SIGO/public_html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 9. Acesse o sistema

Abra o navegador e acesse: `https://seudominio.com.br`

**Credenciais padrão** (se banco já populado):
- Usuário: admin
- Senha: (conforme configurado no banco)

---

## ⚙️ Configuração

### Arquivo .env

Principais variáveis de configuração:

```env
# Aplicação
APP_NAME="Sistema BRS"
APP_ENV=production
APP_DEBUG=false  # SEMPRE false em produção
APP_URL=https://seudominio.com.br

# Banco de dados
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# E-mail (recomendado: log para evitar erros)
MAIL_MAILER=log

# Licenciamento
LICENSE_ENABLED=true
LICENSE_ALLOWED_DOMAIN=seudominio.com.br
```

### Licenciamento

O sistema possui um módulo de licenciamento baseado em assinatura digital:

1. O arquivo de licença deve ser colocado em `storage/app/license/license.lic`
2. A chave pública em `storage/app/license/license_pub.pem`
3. Configure `LICENSE_ENABLED=true` e `LICENSE_ALLOWED_DOMAIN` no `.env`

Para desabilitar temporariamente (apenas desenvolvimento):
```env
LICENSE_ENABLED=false
```

---

## 📁 Estrutura do Projeto

```
SIGO/
├── app/
│   ├── Console/          # Comandos Artisan
│   ├── Exceptions/       # Tratamento de exceções
│   ├── Filters/          # Filtros (ex: MenuPermissionFilter)
│   ├── Helpers/          # Helpers reutilizáveis (ArquivoHelper)
│   ├── Http/
│   │   ├── Controllers/  # 36 controllers organizados por módulo
│   │   ├── Middleware/   # Middlewares (Auth, License, Permissions)
│   │   └── Kernel.php
│   ├── Livewire/         # Componentes Livewire
│   ├── Models/           # 20+ Models Eloquent
│   ├── Providers/        # Service Providers
│   └── Services/         # Services (LicenseService, OdometerService)
├── bootstrap/            # Bootstrap do Laravel
├── config/               # Arquivos de configuração
│   ├── adminlte.php      # Menu e configuração AdminLTE
│   ├── database.php
│   ├── filesystems.php
│   └── license.php
├── database/
│   ├── factories/
│   ├── migrations/       # (vazio - sem migrations)
│   └── seeders/          # Seeders de permissões
├── public_html/          # Webroot (DocumentRoot do servidor)
│   ├── css/              # CSS customizados
│   ├── js/               # JavaScript customizados
│   ├── img/              # Imagens e logos
│   ├── vendor/           # Assets de bibliotecas (AdminLTE, Bootstrap, etc.)
│   ├── .htaccess         # Configuração Apache
│   └── index.php         # Entry point
├── resources/
│   ├── css/
│   ├── js/
│   ├── sass/
│   └── views/            # Views Blade organizadas por módulo
│       ├── admin/
│       ├── auth/
│       ├── documentos-dp/
│       ├── frota/
│       ├── pedidos/
│       ├── relatorios/
│       └── layouts/
├── routes/
│   ├── web.php           # Rotas web (principais)
│   ├── api.php           # Rotas API
│   └── console.php
├── storage/              # Arquivos gerados e logs
│   ├── app/
│   │   ├── public/       # Arquivos públicos (storage:link)
│   │   └── license/      # Arquivos de licença
│   ├── framework/
│   └── logs/
├── tests/                # Testes automatizados
├── vendor/               # Dependências Composer
├── .env.example          # Exemplo de configuração
├── .gitignore
├── composer.json
├── package.json
└── README.md
```

---

## 📦 Módulos Principais

### 1. Documentos DP (`DocumentosDPController`)
**Rotas principais:**
- `GET /documentos-dp/inclusao` - Formulário de inclusão de funcionário
- `POST /documentos-dp/inclusao` - Salvar novo funcionário
- `GET /documentos-dp/funcionarios` - Gestão de funcionários
- `GET /documentos-dp/ordem-servico` - Ordens de Serviço

**Permissões:**
- `doc_dp` - Acesso à inclusão de documentos
- `vis_func` - Visualização e gestão de funcionários
- `ord_serv` - Gestão de ordens de serviço

### 2. Pedidos de Compras (`PedidoComprasController`)
**Rotas principais:**
- `GET /pedidos/solicitacao` - Nova solicitação
- `POST /api/pedidos` - Criar pedido
- `GET /pedidos/autorizacao` - Home de autorizações
- `GET /pedidos/autorizacao/pendentes` - Pedidos pendentes
- `PUT /api/pedidos-agrupado/{hash}/aprovar` - Aprovar grupo
- `PUT /api/pedidos-agrupado/{hash}/rejeitar` - Rejeitar grupo

**Permissões:**
- `solicitacao-pedidos` - Criar solicitações
- `autorizacao-pedidos` - Autorizar pedidos
- `dup_ped` - Duplicar pedidos
- `ati_prod` - Gerenciar produtos
- `bloq_ite` - Bloquear itens

### 3. Controle de Estoque (`ControleEstoqueController`)
**Rotas principais:**
- `GET /brs/controle-estoque` - Tela principal
- `POST /api/entradas` - Registrar entrada
- `POST /api/baixas` - Registrar baixa
- `GET /api/produtos` - Listar produtos

**Permissões:**
- `controle-estoque` - Acesso ao módulo
- `est_mm` - Estoque mínimo/máximo

### 4. Gestão de Frota (Múltiplos Controllers)
**Controllers:**
- `VeiculoController` - Gerenciamento de veículos
- `AbastecimentoController` - Abastecimentos
- `ManutencaoController` - Manutenções
- `ViagemController` - Viagens
- `OcorrenciaController` - Ocorrências

**Permissões:**
- `veiculos`, `abastecimento`, `manutencao`, `viagens`, `ocorrencia`
- `licens` - Licenciamento
- Relatórios: `rel_abast`, `rel_consm`, `rel_cust`, `rel_km`, `Rel_manu`

### 5. Relatórios (Múltiplos Controllers)
**Controllers específicos:**
- `RelatorioEstoqueController`
- `RelatorioDPController`
- `RelatorioFrotaController`
- `RelatorioCentroCustoController`
- E outros...

**Permissões por relatório:**
- `relatorio-estoque`, `rel_dp`, `rel_abse`, `rel_ati-ina`
- `rel_pc`, `rel_ped_cc`
- E outras específicas por tipo

---

## 🔐 Sistema de Permissões

### Estrutura

O sistema utiliza um modelo de permissões granulares baseado em **perfis**:

1. **Perfis** (`profiles`): Agrupam usuários (ex: Admin, Gestor, Operador)
2. **Permissões** (`permissions`): Definem acessos específicos (ex: `doc_dp`, `vis_func`)
3. **Relacionamento** (`profile_permissions`): Many-to-many entre perfis e permissões

### Como Funciona

1. Cada usuário possui um `profile_id`
2. O perfil está associado a múltiplas permissões
3. Gates do Laravel são gerados dinamicamente a partir das permissões
4. Rotas protegidas com middleware `can:permissao`
5. Menu AdminLTE filtrado automaticamente via `MenuPermissionFilter`

### Perfil Admin

Usuários com perfil `Admin` têm bypass automático em todas as verificações de permissão.

### Principais Permissões

| Código | Nome | Descrição |
|--------|------|-----------|
| `doc_dp` | Documentos DP | Inclusão de documentos e funcionários |
| `vis_func` | Visualizar Funcionários | Gestão completa de funcionários |
| `ord_serv` | Ordem de Serviço | Criar e gerenciar OS |
| `controle-estoque` | Controle de Estoque | Acesso ao módulo de estoque |
| `solicitacao-pedidos` | Solicitação de Pedidos | Criar pedidos de compras |
| `autorizacao-pedidos` | Autorização de Pedidos | Aprovar/rejeitar pedidos |
| `veiculos` | Veículos | Gerenciar veículos |
| `rel_dp` | Relatório DP | Relatórios de departamento pessoal |
| `gerenciar-usuarios` | Gerenciar Usuários | Administração de usuários |
| `gerenciar-permissoes` | Gerenciar Permissões | Administração de permissões |

---

## 🔒 Segurança

### Implementado

- ✅ **CSRF Protection**: Proteção contra Cross-Site Request Forgery em todas as rotas POST/PUT/DELETE
- ✅ **Rate Limiting**: Throttle de 60-120 requisições por minuto por rota
- ✅ **SQL Injection Prevention**: Uso exclusivo de Eloquent ORM e Query Builder com bindings
- ✅ **XSS Protection**: Headers `X-XSS-Protection` e sanitização de inputs
- ✅ **Clickjacking Protection**: Header `X-Frame-Options: SAMEORIGIN`
- ✅ **Content Type Sniffing**: Header `X-Content-Type-Options: nosniff`
- ✅ **Autenticação robusta**: Laravel Auth com hash bcrypt
- ✅ **Licenciamento**: Assinatura digital SHA256 com validação de domínio
- ✅ **Validação de uploads**: Tipo MIME, tamanho e extensão de arquivos
- ✅ **Bloqueio de arquivos sensíveis**: `.env`, `.git`, `composer.json` bloqueados via `.htaccess`

### Recomendações Adicionais

Para ambientes de alta segurança:

1. **HTTPS obrigatório**: Configure SSL/TLS e force redirecionamento
2. **HSTS**: Adicione ao `.htaccess`:
   ```apache
   Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
   ```
3. **Content Security Policy (CSP)**: Configure políticas adequadas
4. **Backup regular**: Automatize backups do banco e arquivos
5. **Atualizações**: Mantenha Laravel e dependências atualizadas
6. **Monitoramento**: Configure logs centralizados e alertas

---

## ⚡ Performance

### Otimizações Implementadas

#### Frontend
- ✅ Cache agressivo de assets estáticos (1 ano)
- ✅ Compressão Brotli/Gzip habilitada
- ✅ Minificação de CSS/JS via Vite
- ✅ Preload de recursos críticos
- ✅ PWA configurado com Service Worker
- ✅ Lazy loading de modais e conteúdo dinâmico

#### Backend
- ✅ Query optimization com joins eficientes
- ✅ Uso de indexes em colunas de busca
- ✅ Cache de configuração e rotas
- ✅ Eager loading para relacionamentos
- ✅ Rate limiting para prevenir abuso

#### Servidor
- ✅ OPcache PHP habilitado (recomendado)
- ✅ Compressão de resposta HTTP
- ✅ Keep-Alive habilitado
- ✅ Headers de cache otimizados

### Comandos de Otimização

```bash
# Cache de configuração (produção)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpar cache (desenvolvimento)
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🤝 Contribuição

Contribuições são bem-vindas! Para contribuir:

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'feat: Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

### Padrões de Commit

Utilizamos Conventional Commits:

- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação (sem mudança de código)
- `refactor:` Refatoração de código
- `perf:` Melhoria de performance
- `test:` Adição/correção de testes
- `chore:` Tarefas de manutenção

---

## 📄 Licença

Este projeto é **proprietário** e de uso interno. Todos os direitos reservados.

**Não é permitido:**
- Redistribuição do código
- Uso comercial sem autorização
- Modificação e distribuição de versões derivadas

Para solicitar licenciamento ou autorização de uso, entre em contato.

---

## 📞 Suporte

### Reportar Problemas

Para reportar bugs ou solicitar funcionalidades:

1. Abra uma [Issue](https://github.com/F2nn1K/SIGO/issues)
2. Descreva o problema/solicitação detalhadamente
3. Inclua screenshots se aplicável
4. Informe versão do PHP, Laravel e navegador

### Contato

- **E-mail**: leo.vdf3@gmail.com
- **Repositório**: https://github.com/F2nn1K/SIGO
- **Desenvolvedor**: Leonardo Vitor de Freitas

---

## 🙏 Agradecimentos

- [Laravel](https://laravel.com/) - Framework PHP
- [AdminLTE](https://adminlte.io/) - Template administrativo
- [SweetAlert2](https://sweetalert2.github.io/) - Alertas modernos
- [DataTables](https://datatables.net/) - Tabelas interativas
- [FontAwesome](https://fontawesome.com/) - Ícones

---

## 📝 Changelog

### [1.0.0] - 2025-01-06

#### Adicionado
- Sistema completo de Departamento Pessoal (DP)
- Módulo de Pedidos de Compras com autorização
- Controle de Estoque com mínimo/máximo
- Gestão completa de Frota
- Sistema de Relatórios avançados
- Dashboard executivo com gráficos
- Sistema de licenciamento
- Sistema de permissões granulares
- Filtro de mês nas autorizações de pedidos

#### Melhorias
- Performance otimizada com cache agressivo
- Interface responsiva e moderna
- Exportação Excel em todos os relatórios
- Upload e download de arquivos otimizado (Storage + BLOB)

---

<p align="center">
  Desenvolvido com ❤️ por <a href="mailto:leo.vdf3@gmail.com">Leonardo Vitor de Freitas</a>
</p>

<p align="center">
  <sub>© 2025 Sistema BRS (SIGO). Todos os direitos reservados.</sub>
</p>
