# 🏢 Sistema Interno de Gestão

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php)
![AdminLTE](https://img.shields.io/badge/AdminLTE-3.x-blue?style=for-the-badge)

Aplicação em Laravel com AdminLTE focada em Controle de Estoque e Pedido de Compras, com gestão de permissões e relatórios. Fluxo simples de compras: o usuário solicita, um autorizador aprova ou rejeita, e o solicitante acompanha.

## 📋 Módulos Ativos

- **Pedido de Compras**
  - Solicitação de itens (com `num_pedido` por envio)
  - Autorização (pendentes, aprovadas, rejeitadas) com agrupamento e interações
  - Minhas Interações (solicitante) e Acompanhar Pedido (somente leitura)
- **Controle de Estoque (BRS)**
  - Catálogo básico de produtos (`estoque`)
  - Relatórios e integrações com Centro de Custo
- **Relatórios**
  - Estoque, Centro de Custo, Funcionário
- **Gerenciamento de Usuários/Permissões**
  - Perfis, permissões por perfil e controle de acesso via Gates

> Observação: módulos de RH, Diárias e Cronograma foram desativados/ocultos neste projeto.

## 🛠️ Tecnologias

- Backend: Laravel 10.x
- Frontend: AdminLTE 3.x + Bootstrap
- Banco: MySQL
- Autenticação/Autorização: Laravel Auth + Permissões por Perfil

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
3. (Opcional) Rode as migrações futuras quando quiser iniciar do zero
```bash
php artisan migrate
```
4. Inicie o servidor
```bash
php artisan serve
```

Acesso padrão: `http://localhost:8000`

## 📁 Estrutura

```
app/                    # Controllers, Models
config/adminlte.php     # Menu e plugins
resources/views/        # Blades (Pedido de Compras e Estoque)
routes/web.php          # Rotas principais
```

## 🔐 Permissões

- Permissões ficam em `permissions` e são ligadas a `profiles` via `profile_permissions`.
- O menu usa Gates para exibir apenas o que o usuário pode acessar.

## 🧭 Fluxo de Pedido de Compras

1) Usuário cria a solicitação com itens e prioridade
2) Sistema gera `num_pedido` e salva itens na tabela `solicitacao`
3) Autorizador visualiza grupos pendentes, aprova/rejeita e pode enviar mensagens
4) Solicitante acompanha em “Minhas Interações” ou “Acompanhar Pedido” (read-only)

## 🧰 Scripts úteis

- `php artisan route:clear`, `config:clear`, `cache:clear`, `view:clear`

## 🤝 Contribuição

1. Crie uma branch (`git checkout -b feature/minha-feature`)
2. Commit (`git commit -m "feat: minha feature"`)
3. Push (`git push origin feature/minha-feature`)
4. Abra um PR

## 📝 Licença

Projeto proprietário para uso interno.

---
**Desenvolvido com ❤️ usando Laravel**