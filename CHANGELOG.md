# 📝 Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

---

## [1.0.0] - 2025-10-06

### 🎉 Lançamento Inicial

Primeira versão pública do Sistema BRS (SIGO).

### ✨ Adicionado

#### Módulo Departamento Pessoal (DP)
- Sistema completo de gestão de funcionários
- Upload de documentos (Contrato, RG, CPF, CNH, etc.) com suporte a BLOB + Storage
- Gestão de atestados médicos com controle de períodos
- Sistema de advertências disciplinares
- Controle de EPIs (Equipamentos de Proteção Individual)
- Gerenciamento de contra-cheques
- Controle de férias
- Gestão de 13º salário
- Processamento de rescisões
- Controle de frequência
- Gestão de certificados
- Sistema de ASOS
- Termo aditivo
- Ordem de Serviço
- Geração de dossiê completo em ZIP
- Visualização de PDF completo por funcionário
- Relatório de absenteísmo
- Relatório de funcionários ativos/inativos

#### Módulo Pedidos de Compras
- Solicitação de pedidos com numeração automática (formato: PED-YYYYMMDD-HHMMSS-XXX)
- Sistema de autorização com aprovação/rejeição em lote
- Agrupamento inteligente por envio (SHA1 hash)
- Sistema de interações e mensagens entre solicitante e autorizador
- Duplicação de pedidos anteriores
- Bloqueio de itens por usuário (restrições)
- Gerenciamento de produtos (estoque_pedido)
- Acompanhamento de pedidos (read-only)
- Minhas Interações (histórico do solicitante)
- Relatórios de pedidos por centro de custo
- Relatório geral de pedidos com filtros
- Layout de impressão otimizado
- **Filtro de mês** na página de autorizações (home)

#### Módulo Controle de Estoque
- Catálogo completo de produtos
- Registro de entradas de estoque
- Controle de baixas (saídas) com rastreamento
- Estoque mínimo e máximo com alertas
- Integração com centros de custo
- Histórico de movimentações por funcionário
- Relatório de estoque com filtros avançados
- Relatório por centro de custo
- Relatório por funcionário
- Relatório por produto
- Relatório de estoque mínimo/máximo
- Exportação para Excel

#### Módulo Gestão de Frota
- Cadastro e controle de veículos
- Registro de abastecimentos com cálculo automático de consumo
- Gerenciamento de manutenções (preventivas e corretivas)
- Controle de viagens e quilometragem
- Ocorrências da frota com upload de fotos
- Gestor de ocorrências (status e acompanhamento)
- Licenciamento anual com alertas de vencimento
- Conferência de Notas Fiscais de abastecimento
- Relatório de abastecimento
- Relatório de consumo
- Relatório de custo total
- Relatório de manutenções
- Relatório de km percorrido
- Relatório de conferência de NF

#### Módulo Roçagem
- Controle de equipamentos de roçagem
- Registro de manutenções específicas
- Gerenciamento de abastecimentos

#### Sistema de Administração
- Gerenciamento completo de usuários
- Sistema de perfis com permissões granulares
- Interface de gestão de permissões
- Dashboard executivo com:
  - Gráficos de centros de custo mais ativos
  - Produtos mais retirados
  - Status da frota
  - Consumo mensal
  - Veículos mais utilizados
  - Alertas de licenciamento
- Sistema de licenciamento com assinatura digital SHA256

#### Infraestrutura e Segurança
- CSRF Protection em todas as rotas POST/PUT/DELETE
- Rate Limiting (throttle) por rota (60-120 req/min)
- SQL Injection Prevention (Eloquent ORM + Query Builder)
- XSS Protection (headers e sanitização)
- Middleware de licenciamento global
- Headers de segurança (.htaccess)
- Validação robusta de uploads
- Logs estruturados

#### Performance e UX
- Cache agressivo de assets (1 ano)
- Compressão Brotli/Gzip
- Preload de recursos críticos
- PWA configurado com Service Worker
- Interface responsiva (mobile-first)
- SweetAlert2 para alertas modernos
- DataTables para tabelas interativas
- Formatação brasileira (datas e valores)
- Dark mode preparado (desabilitado por padrão)

### 🔧 Melhorado
- Arquitetura organizada por módulos
- Controllers focados em responsabilidades específicas
- Helpers reutilizáveis (ArquivoHelper)
- Filtros de menu dinâmicos
- Sistema de cache de views Blade

### 🔒 Segurança
- APP_DEBUG=false em produção
- Dados sensíveis não commitados (.gitignore)
- Bloqueio de .env, .git, composer.json via .htaccess
- Validação de tipos MIME em uploads
- Sanitização de inputs

### 📚 Documentação
- README.md completo e profissional
- DEPLOY.md com guia passo a passo
- SECURITY.md com política de segurança
- CONTRIBUTING.md com diretrizes de contribuição
- ARCHITECTURE.md com detalhes técnicos
- QUICK_START.md para setup rápido
- GIT_COMMANDS.md com comandos Git
- LICENSE proprietária
- Issue templates para GitHub

---

## [Unreleased]

### 🔜 Planejado
- Testes automatizados (Feature e Unit)
- CI/CD com GitHub Actions
- Cache de permissões por requisição
- Repository Pattern consistente
- API REST documentada (OpenAPI/Swagger)
- Relatórios com gráficos avançados (Chart.js)
- Sistema de notificações em tempo real (WebSockets)
- Logs estruturados com contexto (Monolog)
- Auditoria completa de operações
- Backup automatizado

---

## 📌 Versionamento

O projeto segue [Semantic Versioning](https://semver.org/lang/pt-BR/):

- **MAJOR**: Mudanças incompatíveis na API
- **MINOR**: Novas funcionalidades compatíveis
- **PATCH**: Correções de bugs compatíveis

Formato: `MAJOR.MINOR.PATCH`

---

## 🔗 Links

- [Repositório](https://github.com/F2nn1K/SIGO)
- [Issues](https://github.com/F2nn1K/SIGO/issues)
- [Releases](https://github.com/F2nn1K/SIGO/releases)

---

**Mantido por**: Leonardo Vicente Dantas Ferreira (leo.vdf3@gmail.com)

