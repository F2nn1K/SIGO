# 🚀 Comandos Git para Push do Projeto

Este guia contém os comandos exatos para fazer o primeiro push do projeto para o GitHub.

---

## 📋 Pré-requisitos

1. Git instalado no seu computador
2. Repositório GitHub criado: https://github.com/F2nn1K/SIGO.git
3. Credenciais do GitHub configuradas

---

## 🔑 Configurar Git (Primeira Vez)

Se ainda não configurou o Git com seu e-mail:

```bash
git config --global user.name "F2nn1K"
git config --global user.email "leo.vdf3@gmail.com"
```

---

## 🚀 Comandos para Push Inicial

### 1. Navegar até a pasta do projeto

```bash
cd "C:\Users\TI\Documents\SII hostinguer\beta2"
```

### 2. Inicializar repositório Git (se ainda não foi feito)

```bash
git init
```

### 3. Adicionar remote do GitHub

```bash
git remote add origin https://github.com/F2nn1K/SIGO.git
```

Se já existir o remote, atualize:

```bash
git remote set-url origin https://github.com/F2nn1K/SIGO.git
```

### 4. Verificar o que será commitado

```bash
git status
```

Verifique se o arquivo `.env` NÃO aparece na lista (deve estar no .gitignore).

### 5. Adicionar todos os arquivos

```bash
git add .
```

### 6. Fazer o primeiro commit

```bash
git commit -m "feat: commit inicial do Sistema BRS (SIGO)

- Sistema completo de gestão com Laravel 10
- Módulos: DP, Pedidos, Estoque, Frota, Relatórios
- Interface AdminLTE 3 com permissões granulares
- Sistema de licenciamento
- Documentação completa (README, DEPLOY, SECURITY, CONTRIBUTING)"
```

### 7. Verificar branch atual

```bash
git branch
```

Se não estiver na branch `main`, renomeie:

```bash
git branch -M main
```

### 8. Fazer o push

```bash
git push -u origin main
```

**Nota**: Se o repositório já tiver conteúdo e você quiser substituir tudo:

```bash
git push -u origin main --force
```

⚠️ **ATENÇÃO**: `--force` apaga o histórico anterior. Use apenas se tiver certeza!

---

## 🔄 Comandos para Atualizações Futuras

### Após fazer mudanças no código:

```bash
# 1. Ver o que mudou
git status

# 2. Adicionar arquivos modificados
git add .

# 3. Commit com mensagem descritiva
git commit -m "feat: adiciona filtro de mês nas autorizações"

# 4. Push para o GitHub
git push origin main
```

---

## 🌿 Trabalhando com Branches

### Criar uma branch para nova feature:

```bash
# Criar e mudar para nova branch
git checkout -b feature/nome-da-feature

# Fazer suas alterações...

# Commit
git add .
git commit -m "feat: descrição da feature"

# Push da branch
git push origin feature/nome-da-feature
```

### Voltar para a branch principal:

```bash
git checkout main
```

### Fazer merge de uma branch:

```bash
# Estando na main
git checkout main

# Fazer merge
git merge feature/nome-da-feature

# Push
git push origin main
```

---

## 🛠️ Comandos Úteis

### Ver histórico de commits:

```bash
git log --oneline
```

### Desfazer último commit (mantém arquivos):

```bash
git reset --soft HEAD~1
```

### Ver diferenças antes de commit:

```bash
git diff
```

### Ver branches remotas:

```bash
git branch -r
```

### Atualizar do GitHub (pull):

```bash
git pull origin main
```

---

## ⚠️ Importante

### NÃO commite:

- ❌ Arquivo `.env` (já está no .gitignore)
- ❌ Pasta `vendor/` (já está no .gitignore)
- ❌ Pasta `node_modules/` (já está no .gitignore)
- ❌ Arquivos de log (já está no .gitignore)
- ❌ Arquivos de licença com dados reais (já está no .gitignore)

### Antes de cada push:

```bash
# Verificar se .env não será commitado
git status | grep .env

# Se aparecer, adicione ao .gitignore:
echo ".env" >> .gitignore
git add .gitignore
git commit -m "chore: atualiza .gitignore"
```

---

## 🆘 Problemas Comuns

### "Permission denied (publickey)"

Configure sua chave SSH ou use HTTPS com token:

```bash
# Usar HTTPS com token pessoal
git remote set-url origin https://TOKEN@github.com/F2nn1K/SIGO.git
```

### "Repository not found"

Verifique se o repositório existe e se você tem acesso:

```bash
git remote -v
```

### Conflitos ao fazer push

```bash
# Puxar mudanças do remote primeiro
git pull origin main

# Resolver conflitos manualmente
# Depois:
git add .
git commit -m "merge: resolve conflitos"
git push origin main
```

---

## ✅ Checklist Final Antes do Push

- [ ] `.env` está no `.gitignore`
- [ ] Não há senhas ou dados sensíveis no código
- [ ] `.gitignore` está correto
- [ ] README.md está atualizado
- [ ] Código está funcionando localmente
- [ ] Commit message é descritivo

---

## 📞 Ajuda

Se tiver problemas com Git:

1. Veja a documentação: https://git-scm.com/doc
2. Entre em contato: leo.vdf3@gmail.com

---

**Boa sorte com o deploy! 🎉**

