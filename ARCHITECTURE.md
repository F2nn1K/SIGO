# 🏗️ Arquitetura do Sistema BRS (SIGO)

Este documento descreve a arquitetura técnica e decisões de design do sistema.

---

## 📐 Visão Geral da Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENTE                               │
│  (Navegador Web - Desktop/Mobile)                           │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTPS
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                   CAMADA WEB                                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Apache/Nginx + mod_rewrite                          │   │
│  │  • .htaccess (cache, compressão, security headers)   │   │
│  │  • DocumentRoot: public_html/                        │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                 CAMADA DE APLICAÇÃO                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Laravel 10 (PHP 8.1+)                               │   │
│  │  • Routing (web.php)                                 │   │
│  │  • Middleware (Auth, License, Permissions)           │   │
│  │  • Controllers (36 controllers)                      │   │
│  │  • Services (License, Odometer)                      │   │
│  │  • Filters (MenuPermissionFilter)                    │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│              CAMADA DE DADOS                                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Eloquent ORM + Query Builder                        │   │
│  │  • 20+ Models                                        │   │
│  │  • Relationships (BelongsTo, HasMany)                │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                   BANCO DE DADOS                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  MySQL 8.0+ / MariaDB 10.3+                          │   │
│  │  • Tabelas relacionais (normalizadas)                │   │
│  │  • Armazenamento BLOB (arquivos)                     │   │
│  │  • Índices otimizados                                │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                 ARMAZENAMENTO DE ARQUIVOS                    │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  storage/app/public/                                 │   │
│  │  • Documentos DP                                     │   │
│  │  • Atestados, Advertências                           │   │
│  │  • Contra-cheques, Férias, etc.                      │   │
│  │  • Fotos de ocorrências da frota                     │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Fluxo de Requisição

```
1. Cliente                →  2. Apache/Nginx       →  3. index.php
   (Browser)                   (.htaccess)             (public_html/)
                                                            │
                                                            ▼
4. Laravel Bootstrap     ←  5. Middleware Stack   ←  6. Routing
   (app.php)                   • CheckLicense           (web.php)
                               • Authenticate
                               • Authorize (can:)
                               • Throttle
                                    │
                                    ▼
7. Controller            →  8. Service/Helper     →  9. Model/Query
   (ex: PedidoComprasCtrl)     (ArquivoHelper)         (Eloquent)
                                    │
                                    ▼
10. View Rendering       ←  11. Response          ←  12. Database
    (Blade)                     (JSON/HTML)            (MySQL)
        │
        ▼
13. Cliente
    (Browser exibe HTML/JSON)
```

---

## 🎯 Padrões e Decisões Arquiteturais

### 1. MVC com Services

- **Models**: Representam entidades do banco, com relacionamentos Eloquent
- **Views**: Blade templates com AdminLTE
- **Controllers**: Orquestram fluxo; lógica complexa delegada a Services
- **Services**: Lógica de negócio reutilizável (ex: `LicenseService`, `OdometerService`)

### 2. Repository Pattern (Parcial)

- Query Builder usado extensivamente para queries complexas
- Eloquent para operações CRUD simples
- Helpers para operações específicas (`ArquivoHelper`)

### 3. Sistema de Permissões

```
User ──belongsTo──> Profile ──belongsToMany──> Permission
                      │
                      └─> profile_permissions (pivot)
```

- Gates gerados dinamicamente no boot (`AuthServiceProvider`)
- Verificação via `can:` middleware nas rotas
- Filtro de menu via `MenuPermissionFilter`
- Perfil Admin tem bypass automático

### 4. Armazenamento Híbrido

**Estratégia**: Storage + BLOB (fallback)

```php
// ArquivoHelper::salvar()
1. Salva em storage/app/public/
2. Também salva BLOB no banco (redundância temporária)

// ArquivoHelper::download()
1. Tenta ler de storage/app/public/ (rápido)
2. Fallback para BLOB (se storage falhar)
```

**Razão**: Compatibilidade e resiliência; permite migração gradual.

### 5. Licenciamento

```
┌─────────────────────────┐
│  Middleware Global      │
│  CheckLicense           │
│  (todas requisições)    │
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│  LicenseService         │
│  • Verifica assinatura  │
│  • Valida domínio       │
│  • Checa expiração      │
│  • Grace period         │
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│  storage/app/license/   │
│  • license.lic          │
│  • license_pub.pem      │
└─────────────────────────┘
```

**Assinatura**: SHA256 com chave pública/privada (RSA)

### 6. Frontend - AdminLTE com jQuery

- **Não usa Vite/Mix para AdminLTE** (`laravel_asset_bundling=false`)
- Assets servidos diretamente de `public_html/vendor/`
- CDN para DataTables/SweetAlert2
- PWA configurado com Service Worker

---

## 📦 Módulos e Controllers

### Módulo DP (Departamento Pessoal)

```
DocumentosDPController (3718 linhas)
├── Inclusão de funcionários
├── Upload de documentos (Contrato, RG, CPF, etc.)
├── Gestão de atestados
├── Gestão de advertências
├── Controle de EPIs
├── Contra-cheques
├── Férias
├── 13º salário
├── Rescisões
├── Frequência
├── Certificados
├── ASOS
└── Geração de dossiê completo (ZIP)

OrdemServicoController
└── Ordens de serviço de DP

RelatorioDPController
├── Relatório geral de DP
└── Exportação Excel
```

### Módulo Pedidos de Compras

```
PedidoComprasController (2304 linhas)
├── Solicitação de pedidos
├── Autorização (aprovação/rejeição)
├── Agrupamento por envio (SHA1 hash)
├── Interações e mensagens
├── Duplicação de pedidos
├── Gestão de produtos (estoque_pedido)
├── Bloqueio de itens por usuário
└── Relatórios agregados
```

### Módulo Estoque

```
ControleEstoqueController
├── Catálogo de produtos
├── Entradas de estoque
├── Baixas (saídas)
└── Integração com centro de custo

EstoqueMinMaxController
└── Configuração de estoque mínimo/máximo

RelatorioEstoqueController
RelatorioCentroCustoController
RelatorioPorFuncionarioController
RelatorioProdutoEstoqueController
└── Relatórios diversos com exportação Excel
```

### Módulo Frota

```
VeiculoController
├── CRUD de veículos
└── Licenciamento

AbastecimentoController
├── Registro de abastecimentos
└── Cálculo de consumo

ManutencaoController
├── Manutenções preventivas/corretivas
└── Controle de custos

ViagemController
├── Registro de viagens
└── Controle de km

OcorrenciaController
├── Registro de ocorrências
├── Upload de fotos
└── Gestor de ocorrências

NFAbastecimentoController
└── Conferência de NFs

Relatórios: Km, Consumo, Custo, Manutenções, Conferência NF
```

### Módulo Roçagem

```
RocagemManutencaoController
└── Manutenção de equipamentos de roçagem

RocagemAbastecimentosController
└── Abastecimentos de roçagem
```

---

## 🔐 Camadas de Segurança

### 1. Infraestrutura
- `.htaccess`: Bloqueio de .env, .git, source maps
- Headers: X-Frame-Options, X-XSS-Protection, nosniff
- HTTPS obrigatório em produção

### 2. Middleware
```php
Global:
├── CheckLicense (verifica licença)
├── TrimStrings (sanitização)
└── ConvertEmptyStringsToNull

Route Groups:
├── auth (autenticação Laravel)
├── can:permissao (autorização via Gates)
└── throttle:60,1 (rate limiting)
```

### 3. Validação
- Request validation em todos os endpoints POST/PUT
- File validation: MIME, tamanho, extensão
- CSRF token obrigatório

### 4. Autorização
- Gates dinâmicos por permissão
- Verificação em 3 níveis:
  1. Rota (middleware `can:`)
  2. Controller (checks manuais se necessário)
  3. View (blade `@can` directives)

---

## 💾 Estratégia de Dados

### Banco de Dados

**Sem Migrations**: Estrutura gerenciada manualmente

**Razão**: Flexibilidade em produção; evita conflitos em deploys.

**Tabelas principais**:

```
users
├── id, name, login, password, profile_id
└── relationships: profile

profiles
├── id, name, description
└── relationships: permissions (many-to-many)

permissions
├── id, name, code, description
└── relationships: profiles (many-to-many)

profile_permissions (pivot)
└── profile_id, permission_id

funcionarios
├── id, nome, cpf, sexo, funcao, status
└── relationships: documentos, atestados, advertencias, etc.

solicitacao (pedidos)
├── id, num_pedido, usuario_id, centro_custo_id
├── produto_nome, quantidade, valor
├── aprovacao (pendente/aprovado/rejeitado)
└── data_solicitacao, data_aprovacao

veiculos
├── id, placa, marca, modelo, km_atual, status
└── relationships: abastecimentos, manutencoes, viagens

estoque
├── id, nome, codigo, categoria, centro_custo_id
└── relationships: baixas

centro_custo
├── id, nome, codigo
└── usado em múltiplos módulos
```

### Índices Recomendados

```sql
-- Pedidos (otimizar consultas agrupadas)
CREATE INDEX idx_solicitacao_aprovacao ON solicitacao(aprovacao);
CREATE INDEX idx_solicitacao_num_pedido ON solicitacao(num_pedido);
CREATE INDEX idx_solicitacao_data ON solicitacao(data_solicitacao);
CREATE INDEX idx_solicitacao_usuario ON solicitacao(usuario_id);

-- Permissões (usadas em menu e gates)
CREATE INDEX idx_profile_perms ON profile_permissions(profile_id, permission_id);

-- Frota
CREATE INDEX idx_abastecimentos_data ON abastecimentos(data);
CREATE INDEX idx_viagens_veiculo ON viagens(vehicle_id);

-- DP
CREATE INDEX idx_funcionarios_status ON funcionarios(status);
CREATE INDEX idx_funcionarios_cpf ON funcionarios(cpf);
```

---

## 🔄 Fluxos de Negócio

### Fluxo: Aprovação de Pedido de Compras

```
1. Usuário cria solicitação
   └─> POST /api/pedidos
       └─> PedidoComprasController::store()
           ├─> Valida dados
           ├─> Gera num_pedido único
           ├─> Salva itens na tabela solicitacao
           └─> Retorna sucesso

2. Autorizador visualiza pendentes
   └─> GET /api/pedidos-pendentes-agrupados
       └─> PedidoComprasController::pedidosPendentesAgrupados()
           ├─> Agrupa por num_pedido
           ├─> Calcula totais
           └─> Retorna lista

3. Autorizador aprova grupo
   └─> PUT /api/pedidos-agrupado/{hash}/aprovar
       └─> PedidoComprasController::aprovarGrupo()
           ├─> Busca todos itens do num_pedido
           ├─> Atualiza aprovacao='aprovado'
           ├─> Registra interação
           └─> Retorna sucesso

4. Solicitante acompanha
   └─> GET /pedidos/minhas-interacoes
       └─> Exibe status e interações
```

### Fluxo: Upload de Documento DP

```
1. Usuário preenche formulário
   └─> /documentos-dp/inclusao

2. Submit com arquivos
   └─> POST /documentos-dp/inclusao
       └─> DocumentosDPController::store()
           ├─> Valida CPF (único ou atualiza)
           ├─> Salva funcionário
           ├─> Para cada arquivo:
           │   ├─> ArquivoHelper::salvar()
           │   │   ├─> Salva em storage/app/public/
           │   │   └─> Retorna conteúdo + path
           │   ├─> Insere em funcionarios_documentos
           │   └─> Salva BLOB + path
           └─> Retorna sucesso

3. Download de documento
   └─> GET /documentos-dp/arquivo/{id}
       └─> DocumentosDPController::downloadBLOB()
           └─> ArquivoHelper::download()
               ├─> Tenta ler de storage (rápido)
               └─> Fallback para BLOB (lento)
```

### Fluxo: Licenciamento

```
1. Middleware verifica a cada requisição
   └─> CheckLicense::handle()
       └─> LicenseService::status()
           ├─> Valida assinatura SHA256
           ├─> Verifica domínio
           ├─> Checa expiração
           └─> Retorna status

2. Se expirado/inválido
   └─> Redireciona para /license

3. Admin faz upload de nova licença
   └─> POST /license/upload
       └─> LicenseController::upload()
           ├─> Valida arquivo .lic
           ├─> Salva em storage/app/license/
           └─> Próxima requisição será aceita
```

---

## 🎨 Camada de Apresentação

### Estrutura de Views

```
resources/views/
├── layouts/
│   └── app.blade.php (base: extends adminlte::page)
│       ├─> Meta tags mobile
│       ├─> Preload de assets
│       ├─> PWA
│       └─> CSRF token global
│
├── admin/
│   ├── dashboard-livewire.blade.php
│   ├── gerenciar-usuarios.blade.php
│   ├── gerenciar-permissoes.blade.php
│   └── perfis.blade.php
│
├── documentos-dp/
│   ├── inclusao.blade.php (formulário)
│   ├── funcionarios.blade.php (gestão)
│   └── ordem-servico-*.blade.php
│
├── pedidos/
│   ├── solicitacao.blade.php
│   ├── autorizacao_home.blade.php (cards com contadores)
│   ├── autorizacao_pendentes.blade.php
│   ├── autorizacao_aprovadas.blade.php
│   └── autorizacao_rejeitadas.blade.php
│
├── frota/
│   ├── veiculos/index.blade.php
│   ├── abastecimentos/index.blade.php
│   └── relatorios/*.blade.php
│
└── relatorios/
    ├── dp.blade.php
    ├── estoque.blade.php
    └── *.blade.php
```

### Stack Frontend

```
AdminLTE 3.x
├── Bootstrap 5
├── jQuery 3.x
├── SweetAlert2 (CDN)
├── DataTables (CDN + local)
├── FontAwesome
└── Chart.js (Dashboard)
```

### Padrão de Interação

1. **Carregamento inicial**: Blade renderiza HTML
2. **Interações**: AJAX via jQuery
3. **Feedback**: SweetAlert2 para alertas
4. **Tabelas**: DataTables para listagens
5. **Formulários**: Validação client-side + server-side

---

## ⚡ Performance

### Cache Strategy

```
Browser Cache (via .htaccess)
├── CSS/JS: 1 ano (immutable)
├── Imagens: 1 ano
├── Fontes: 1 ano
├── HTML: sem cache (no-store)
└── JSON/API: 1 dia

Laravel Cache
├── Config: php artisan config:cache
├── Routes: php artisan route:cache
└── Views: php artisan view:cache (Blade compilado)

Database Query Cache
└── Não implementado (oportunidade de melhoria)
```

### Compressão

- Brotli (se disponível): qualidade 6
- Gzip (fallback): todos os text/* e application/*

### Otimizações

- Eager loading de relacionamentos
- Limit nas queries de autocomplete (15-30 resultados)
- Throttle para prevenir spam
- Composer autoload otimizado (`--classmap-authoritative`)

---

## 🧪 Qualidade de Código

### Métricas (estimadas)

- **Controllers**: 36 (média ~200-400 linhas cada)
- **Models**: 20+ (leves, focados em relacionamentos)
- **Rotas**: ~150 rotas definidas (web.php ~1486 linhas)
- **Views**: 60+ arquivos Blade
- **TODOs no código**: 115 encontrados (débito técnico)

### Padrões Seguidos

- ✅ PSR-12 (Laravel Pint configurado)
- ✅ Eloquent ORM (evita SQL injection)
- ✅ Type hints em métodos recentes
- ✅ Try-catch em operações críticas
- ⚠️ Controllers muito longos (ex: DocumentosDPController 3718 linhas)
- ⚠️ Lógica em closures nas rotas (dificulta teste)

---

## 🔧 Manutenibilidade

### Pontos Fortes

- Código organizado por módulo
- Helpers reutilizáveis
- Comentários em pontos críticos
- Logs estruturados

### Pontos de Melhoria

- Extrair lógica de controllers para Services
- Adicionar testes automatizados
- Documentar APIs com OpenAPI/Swagger
- Reduzir duplicação de código
- Implementar Repository pattern consistentemente

---

## 📊 Dependências

### Backend (Composer)

```json
"require": {
    "php": "^8.1",
    "laravel/framework": "^10.10",
    "laravel/sanctum": "^3.3",
    "laravel/ui": "^4.6",
    "livewire/livewire": "^3.6",
    "jeroennoten/laravel-adminlte": "^3.15",
    "guzzlehttp/guzzle": "^7.2"
}
```

### Frontend (NPM)

```json
"devDependencies": {
    "vite": "^5.0.0",
    "laravel-vite-plugin": "^1.0.0",
    "bootstrap": "^5.2.3",
    "axios": "^1.6.4",
    "sass": "^1.56.1"
}
```

**CDN**:
- DataTables 1.10.19
- SweetAlert2 11
- jQuery 3.x (também local em public_html/vendor)

---

## 🚀 Deploy Strategy

### Ambientes

```
Desenvolvimento (Local)
├── APP_ENV=local
├── APP_DEBUG=true
├── MySQL local
└── XAMPP/WAMP/Laravel Valet

Staging (Opcional)
├── APP_ENV=staging
├── APP_DEBUG=false
├── Banco de teste
└── Dados anonimizados

Produção (Hostinger)
├── APP_ENV=production
├── APP_DEBUG=false
├── MySQL Hostinger
├── HTTPS obrigatório
└── LICENSE_ENABLED=true
```

### CI/CD (Não implementado)

Oportunidade para:
- GitHub Actions para testes automáticos
- Deploy automatizado
- Análise de código (PHPStan, Pint)

---

## 🔮 Roadmap Técnico

### Curto Prazo (Sprint 1-2)
- [ ] Implementar cache de permissões por requisição
- [ ] Extrair lógica de DocumentosDPController para DocumentoService
- [ ] Adicionar testes Feature básicos
- [ ] Documentar APIs principais

### Médio Prazo (Sprint 3-6)
- [ ] Implementar Repository pattern consistentemente
- [ ] Adicionar logs estruturados (Monolog com contexto)
- [ ] Configurar CI/CD com GitHub Actions
- [ ] Otimizar queries com índices adicionais

### Longo Prazo (6+ meses)
- [ ] Migrar para API REST + SPA (opcional)
- [ ] Implementar WebSockets para notificações em tempo real
- [ ] Adicionar relatórios com BI (Chart.js avançado ou similar)
- [ ] Sistema de auditoria completo

---

## 📚 Referências

- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [AdminLTE 3 Documentation](https://adminlte.io/docs/3.1/)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)

---

**Última atualização**: Outubro 2025
**Versão do documento**: 1.0

