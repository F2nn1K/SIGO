# 🏢 Sistema Interno de Gestão

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.x-blue?style=for-the-badge)

Sistema completo de gestão empresarial em Laravel com AdminLTE, incluindo Controle de Estoque, Pedidos de Compras, Gestão de Documentos de DP e Relatórios avançados. Interface moderna com SweetAlert2, formatação brasileira de datas e sistema robusto de permissões.

## 📋 Módulos Ativos

### 🛒 **Pedido de Compras**
- Solicitação de itens com numeração automática (`num_pedido`)
- Sistema de autorização com aprovação/rejeição
- Agrupamento inteligente de pedidos por solicitante
- Acompanhamento em tempo real (Minhas Interações)
- Interface de aprovação com mensagens e prioridades
- Layout de impressão otimizado

### 📦 **Controle de Estoque (BRS)**
- Catálogo completo de produtos
- Controle de baixas e saídas de materiais
- Integração com centros de custo
- Rastreamento de retiradas por funcionário
- Sistema EPI (Equipamentos de Proteção Individual)

### 📋 **Documentos DP (Departamento Pessoal)**
- **Inclusão de Documentos**: Formulário completo para cadastro de funcionários
  - Dados pessoais (Nome, CPF, Sexo, Função)
  - Upload múltiplo de documentos (MEDIUMBLOB)
  - Validação de arquivos (PDF, JPG, PNG)
- **Gestão de Funcionários**: Interface de busca e visualização
  - Busca inteligente por nome (mínimo 3 caracteres)
  - Visualização completa de documentos anexados
  - Download e visualização de PDFs em nova aba
- **Atestados Médicos**: Sistema completo de gestão
  - Anexação de atestados com data e observações
  - Histórico organizado por funcionário
- **Advertências**: Controle disciplinar
  - Registro de advertências com documentação
  - Rastreamento por funcionário
- **EPI (Materiais Retirados)**: Controle de equipamentos
  - Agrupamento por lançamentos (não por item individual)
  - Modal detalhado com histórico completo
  - Rastreamento de quem entregou e quando
- **Controle de Status**: Gestão de situação do funcionário
  - Botões para: Demitir, Afastar, Férias, Readmitir
  - Histórico de mudanças de status

### 📊 **Relatórios Avançados**
- **Relatório de Estoque**: Filtros por produto e período
- **Relatório por Centro de Custo**: Análise de gastos departamentais  
- **Relatório por Funcionário**: Controle de materiais retirados
  - Filtro preciso por data (DD/MM/AAAA)
  - Correção para filtros de um único dia
- **Exportação em Excel** para todos os relatórios

### 👥 **Gerenciamento de Usuários**
- Sistema de perfis e permissões granulares
- Controle de acesso por Gates do Laravel
- Permissões específicas: `doc_dp`, `vis_func`, etc.
- Interface administrativa para gestão de permissões

### 🎨 **Interface Moderna**
- **SweetAlert2** para todos os alertas e confirmações
- **Layout responsivo** com AdminLTE 3.x
- **Formatação brasileira** de datas (DD/MM/AAAA HH:MM)
- **Modais organizados** com tabelas estruturadas
- **Design moderno** com cards e elementos visuais

> **Observação**: Módulos de RH, Diárias e Cronograma foram desativados/ocultos para focar nas funcionalidades principais.

## 🛠️ Tecnologias

- **Backend**: Laravel 10.x com Eloquent ORM
- **Frontend**: AdminLTE 3.x + Bootstrap 4.x + SweetAlert2
- **Banco de Dados**: MySQL com armazenamento BLOB para arquivos
- **Autenticação**: Laravel Auth + Sistema de Gates
- **Permissões**: Sistema granular por perfil de usuário
- **JavaScript**: jQuery + Fetch API para interações assíncronas
- **Validação**: Client-side e Server-side
- **Arquivos**: Upload e armazenamento em MEDIUMBLOB
- **Relatórios**: Exportação Excel nativa
- **Segurança**: CSRF Protection, Rate Limiting, Input Sanitization

## 🚀 Instalação Rápida

1. Clone o repositório
```bash
git clone https://github.com/F2nn1K/SII.git
cd SII
```
2. Configure o `.env` e gere a key
```bash
cp .env.example .env
php artisan key:generate
```
3. (Opcional) Rode as migrações quando quiser iniciar do zero
```bash
php artisan migrate
```
4. Inicie o servidor
```bash
php artisan serve
```

Acesso padrão: `http://localhost:8000`

## 📁 Estrutura do Projeto

```
app/
├── Http/Controllers/
│   ├── DocumentosDPController.php    # Gestão completa de Documentos DP
│   ├── RelatorioPorFuncionarioController.php
│   └── [outros controllers...]
├── Models/
│   ├── Funcionario.php              # Model central de funcionários
│   ├── Baixa.php                    # Model para controle de materiais
│   └── [outros models...]
├── Middleware/
│   └── CheckPermission.php          # Middleware de permissões

config/
└── adminlte.php                     # Configuração do menu e plugins

resources/views/
├── documentos-dp/
│   ├── inclusao.blade.php           # Formulário de inclusão
│   └── funcionarios.blade.php       # Interface de gestão
├── pedidos/                         # Views de pedidos de compras
├── relatorios/                      # Views de relatórios
└── layouts/app.blade.php            # Layout principal

routes/
└── web.php                          # Rotas com middleware de permissões

database/
├── migrations/                      # Migrações do banco
└── seeders/                         # Seeders de permissões
```

## 🔐 Sistema de Permissões

### Estrutura de Permissões
- **Tabela `permissions`**: Define todas as permissões disponíveis
- **Tabela `profiles`**: Perfis de usuário (Admin, Gestor, Funcionário, etc.)
- **Tabela `profile_permissions`**: Relacionamento many-to-many
- **Gates do Laravel**: Controle fino de acesso por funcionalidade

### Permissões Principais
- `doc_dp`: Acesso ao módulo Documentos DP (inclusão)
- `vis_func`: Visualização e gestão de funcionários
- `est_aprova`: Aprovação de pedidos de compras
- `rel_estoque`: Acesso a relatórios de estoque
- `admin_perms`: Gerenciamento de permissões do sistema

### Middleware de Segurança
- **Rate Limiting**: 60 requests por minuto por usuário
- **CSRF Protection**: Proteção contra ataques cross-site
- **Input Sanitization**: Limpeza automática de inputs

## 🧭 Fluxo de Pedido de Compras

1) Usuário cria a solicitação com itens e prioridade  
2) Sistema gera `num_pedido` e salva itens na tabela `solicitacao`  
3) Autorizador visualiza grupos pendentes, aprova/rejeita e pode enviar mensagens  
4) Solicitante acompanha em “Minhas Interações” ou “Acompanhar Pedido” (read-only)

## 🆕 Últimas Atualizações

### ✨ **Módulo Documentos DP** (Nova Funcionalidade)
- **Sistema completo de gestão de funcionários** com documentação digital
- **Upload e armazenamento de arquivos** em MEDIUMBLOB para recuperação futura
- **Interface de busca inteligente** com filtros dinâmicos
- **Controle de atestados e advertências** com histórico completo

### 🔧 **Melhorias na Interface**
- **SweetAlert2** substituindo alerts nativos para UX moderna
- **Formatação brasileira** de datas em todo o sistema (DD/MM/AAAA)
- **Design responsivo** atualizado com cards modernos
- **Modais estruturados** com tabelas organizadas

### 📊 **Sistema EPI Aprimorado**
- **Agrupamento por lançamentos** ao invés de itens individuais
- **Modal detalhado** para visualização completa do histórico
- **Rastreamento de entregas** com usuário e data
- **Interface intuitiva** com badges e indicadores visuais

### 🐛 **Correções Importantes**
- **Filtro de data** no Relatório por Funcionário funcionando corretamente
- **Remoção de logs** de debug em produção
- **Validação aprimorada** de uploads de arquivos
- **Performance otimizada** em consultas do banco

### 🔒 **Segurança Reforçada**
- **Rate limiting** implementado em todas as rotas sensíveis
- **Sanitização avançada** de inputs do usuário
- **Validação robusta** de arquivos e formulários
- **Proteção CSRF** em todas as operações POST

## 🧰 Scripts e Comandos Úteis

### Limpeza de Cache
```bash
php artisan route:clear     # Limpa cache de rotas
php artisan config:clear    # Limpa cache de configuração
php artisan cache:clear     # Limpa cache da aplicação
php artisan view:clear      # Limpa cache de views
```

### Desenvolvimento
```bash
php artisan make:controller NomeController    # Criar controller
php artisan make:model NomeModel             # Criar model
php artisan make:migration create_table      # Criar migration
php artisan migrate                          # Executar migrations
```

### Banco de Dados
```bash
php artisan db:seed                          # Popular banco com seeders
php artisan migrate:fresh --seed            # Resetar e popular banco
```

### Permissões (Scripts customizados)
```bash
php mostrar_perfis_permissoes.php           # Visualizar permissões
php verificar_tabelas_perfis.php            # Verificar estrutura
```

## 💾 Estrutura do Banco de Dados

### Tabelas Principais
- **`funcionarios`**: Tabela central com dados dos funcionários
- **`funcionarios_documentos`**: Documentos anexados (MEDIUMBLOB)
- **`funcionarios_atestados`**: Atestados médicos (MEDIUMBLOB)
- **`funcionarios_advertencias`**: Advertências disciplinares (MEDIUMBLOB)
- **`funcionarios_logs`**: Log de mudanças de status
- **`baixas`**: Controle de materiais retirados (EPI)
- **`estoque`**: Catálogo de produtos disponíveis
- **`solicitacao`**: Pedidos de compras com aprovação
- **`users`**: Usuários do sistema
- **`profiles`** e **`permissions`**: Sistema de permissões

### Características Técnicas
- **Armazenamento BLOB**: PDFs e imagens armazenados diretamente no banco
- **Relacionamentos**: Foreign keys para integridade referencial  
- **Indexação**: Índices otimizados para consultas frequentes
- **Versionamento**: Controle de mudanças via timestamps
- **Logs**: Auditoria completa de operações críticas

## 📈 Performance e Otimização

### Frontend
- **Lazy Loading** de modais e conteúdo dinâmico
- **Cache de consultas** para busca de funcionários
- **Compressão de assets** CSS e JavaScript
- **Otimização de imagens** e ícones

### Backend  
- **Query optimization** com joins eficientes
- **Eager loading** para relacionamentos
- **Rate limiting** para prevenir abuso
- **Memória otimizada** para upload de arquivos

## 🔗 Links
- **Repositório**: https://github.com/F2nn1K/SII
- **Contato**: leo.vdf3@gmail.com
- **Documentação**: Ver arquivos de instrução no repositório

## 🤝 Contribuição
1. Crie uma branch (`git checkout -b feature/minha-feature`)
2. Commit (`git commit -m "feat: minha feature"`)
3. Push (`git push origin feature/minha-feature`)
4. Abra um PR

## 📝 Licença
Projeto proprietário para uso interno.

---
**Desenvolvido com ❤️ usando Laravel**