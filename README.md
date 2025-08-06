# 🏢 Sistema Interno de Gestão Empresarial

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.x-blue?style=for-the-badge)
![Livewire](https://img.shields.io/badge/Livewire-3.x-fb70a9?style=for-the-badge)

Sistema de gestão empresarial desenvolvido em Laravel com interface AdminLTE, projetado para otimizar processos internos de RH, controle de estoque, gestão de diárias e cronogramas corporativos.

## 📋 Funcionalidades Principais

### 🧑‍💼 **Módulo RH (Recursos Humanos)**
- Gerenciamento de problemas e solicitações de RH
- Sistema de anotações e acompanhamento
- Controle de prioridades e status
- Histórico completo de interações

### 📅 **Cronograma Corporativo**
- Gestão de eventos e atividades
- Calendário interativo
- Controle de prazos e marcos
- Notificações automáticas

### 💰 **Gestão de Diárias**
- Cadastro e controle de diárias de funcionários
- Aprovação de solicitações
- Relatórios detalhados
- Integração com centros de custo

### 📦 **Controle de Estoque (BRS)**
- Gerenciamento completo de produtos
- Controle de entradas e saídas
- Alertas de produtos em falta
- Dashboard com gráficos analíticos
- Integração com centros de custo

### 👥 **Gerenciamento de Usuários**
- Sistema de perfis e permissões granulares
- Controle de acesso por módulos
- Gerenciamento de usuários ativos/inativos
- Interface administrativa completa

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 10.x
- **Frontend**: AdminLTE 3.x + Bootstrap
- **Interatividade**: Livewire 3.x
- **Banco de Dados**: MySQL
- **Autenticação**: Laravel Auth + Sistema de Permissões Customizado
- **UI Components**: DataTables, Chart.js, FontAwesome

## 🚀 Configuração e Instalação

### Pré-requisitos
- PHP 8.1 ou superior
- Composer
- MySQL
- XAMPP (recomendado para desenvolvimento local)

### Instalação Rápida

1. **Clone o repositório**
   ```bash
   git clone [seu-repositorio]
   cd sistema-interno
   ```

2. **Configure o ambiente**
   ```bash
   # Execute o script de configuração automática
   ./bat/config_local.bat
   ```

3. **Execute as migrações**
   ```bash
   # Script para criar estrutura do banco
   ./bat/run_migrations.bat
   ```

4. **Crie o usuário administrador**
   ```bash
   # Opção A: Script automático
   ./bat/create_admin.bat
   
   # Opção B: Via phpMyAdmin
   # Execute o arquivo criar_admin_apos_migracao.sql
   ```

5. **Inicie o servidor**
   ```bash
   ./bat/start_server.bat
   ```

### Acesso ao Sistema
- **URL**: http://localhost:8000
- **Usuário**: admin
- **Senha**: 123456

## 📁 Estrutura do Projeto

```
├── app/
│   ├── Http/Controllers/     # Controllers principais
│   ├── Livewire/            # Componentes Livewire
│   ├── Models/              # Models Eloquent
│   └── Filters/             # Filtros de permissão
├── database/
│   ├── migrations/          # Migrações do banco
│   └── seeders/             # Seeders de dados iniciais
├── resources/
│   └── views/               # Templates Blade
├── routes/
│   └── web.php              # Rotas da aplicação
└── bat/                     # Scripts de automação
```

## 🔐 Sistema de Permissões

O sistema utiliza um controle granular de permissões organizado em grupos:

- **Administrativas**: Controle total do sistema
- **RH**: Recursos humanos e cronograma
- **Diárias**: Gestão de diárias
- **Relatórios**: Acesso a relatórios
- **Controle de Estoque**: Gestão de produtos e estoque

## 📊 Dashboard e Relatórios

- Dashboard interativo com gráficos em tempo real
- Relatórios por centro de custo
- Análise de produtos mais solicitados
- Métricas de performance por período

## 🔧 Scripts de Automação

O projeto inclui scripts batch para facilitar tarefas comuns:

- `config_local.bat` - Configuração inicial completa
- `run_migrations.bat` - Execução de migrações
- `create_admin.bat` - Criação de usuário admin
- `start_server.bat` - Inicialização do servidor
- `clear_cache.bat` - Limpeza de cache

## 🤝 Contribuição

Para contribuir com o projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📝 Licença

Este projeto é proprietário e desenvolvido para uso interno da empresa.

## 🆘 Suporte

Para suporte técnico ou dúvidas sobre o sistema, consulte a documentação interna ou entre em contato com a equipe de desenvolvimento.

---

**Desenvolvido com ❤️ usando Laravel**