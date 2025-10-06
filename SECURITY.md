# 🔐 Política de Segurança

## Versões Suportadas

| Versão | Suporte          |
|--------|------------------|
| 1.0.x  | ✅ Suportada     |
| < 1.0  | ❌ Não suportada |

---

## 🛡️ Reportar Vulnerabilidades

A segurança é uma prioridade máxima para o Sistema BRS (SIGO).

### Como Reportar

**NÃO** abra issues públicas para vulnerabilidades de segurança.

Em vez disso:

1. **Envie um e-mail** para: leo.vdf3@gmail.com
2. **Assunto**: `[SEGURANÇA] Descrição breve da vulnerabilidade`
3. **Inclua**:
   - Descrição detalhada da vulnerabilidade
   - Passos para reproduzir
   - Impacto potencial
   - Sugestão de correção (se houver)

### Processo de Resposta

1. **Confirmação**: Responderemos em até 48 horas úteis
2. **Avaliação**: Analisaremos a vulnerabilidade em até 7 dias
3. **Correção**: Desenvolveremos e testaremos a correção
4. **Divulgação**: Após correção aplicada, divulgaremos de forma responsável

---

## 🔒 Medidas de Segurança Implementadas

### Aplicação

- ✅ **CSRF Protection**: Token CSRF em todas as requisições POST/PUT/DELETE
- ✅ **SQL Injection Prevention**: Uso exclusivo de Query Builder e Eloquent com bindings
- ✅ **XSS Protection**: Sanitização de inputs e headers de segurança
- ✅ **Rate Limiting**: Throttle de 60-120 req/min por rota
- ✅ **Autenticação**: Laravel Auth com bcrypt/Argon2
- ✅ **Autorização**: Gates e Policies granulares
- ✅ **Validação de Uploads**: Tipo MIME, tamanho e extensões permitidas
- ✅ **Session Security**: Cookies HttpOnly e SameSite

### Servidor

- ✅ **Headers de Segurança**:
  - `X-Frame-Options: SAMEORIGIN`
  - `X-Content-Type-Options: nosniff`
  - `X-XSS-Protection: 1; mode=block`
- ✅ **Bloqueio de Arquivos Sensíveis**: `.env`, `.git`, `composer.json`
- ✅ **Compressão e Cache**: Otimização de performance
- ✅ **Licenciamento**: Assinatura digital SHA256

### Dados

- ✅ **Criptografia**: APP_KEY usado para encryption
- ✅ **Hashing de Senhas**: Bcrypt com salt automático
- ✅ **Logs Sanitizados**: Sem exposição de dados sensíveis
- ✅ **Backup**: Sistema de backup recomendado

---

## ⚠️ Vulnerabilidades Conhecidas

Atualmente não há vulnerabilidades conhecidas não corrigidas.

Histórico de correções será mantido aqui conforme necessário.

---

## 🔐 Boas Práticas de Segurança

### Para Administradores

1. **Ambiente de Produção**:
   - `APP_DEBUG=false` sempre
   - `APP_ENV=production`
   - HTTPS configurado e forçado
   - Senha forte do banco de dados
   - Usuário MySQL não-root com permissões mínimas

2. **Controle de Acesso**:
   - Revisar permissões de usuários regularmente
   - Remover usuários inativos
   - Auditar logs de acesso periodicamente

3. **Backup**:
   - Backup diário do banco de dados
   - Backup semanal de arquivos
   - Testar restauração periodicamente

4. **Monitoramento**:
   - Revisar `storage/logs/laravel.log` regularmente
   - Configurar alertas para erros 500
   - Monitorar uso de recursos do servidor

5. **Atualizações**:
   - Manter Laravel atualizado
   - Atualizar dependências do Composer regularmente
   - Aplicar patches de segurança prontamente

### Para Desenvolvedores

1. **Nunca**:
   - Commitar `.env` com dados reais
   - Incluir senhas ou tokens no código
   - Usar `eval()` ou funções perigosas
   - Expor stack traces em produção
   - Desabilitar validações de segurança

2. **Sempre**:
   - Validar e sanitizar todos os inputs
   - Usar Eloquent/Query Builder (nunca SQL direto)
   - Implementar autorização (Gates/Policies)
   - Logar operações críticas
   - Revisar código antes de commit

3. **Upload de Arquivos**:
   ```php
   // ✅ BOM
   $request->validate([
       'arquivo' => 'required|file|mimes:pdf,jpg,png|max:10240'
   ]);
   
   // ❌ EVITAR
   move_uploaded_file($_FILES['arquivo']['tmp_name'], 'uploads/');
   ```

4. **Queries**:
   ```php
   // ✅ BOM
   User::where('email', $email)->first();
   DB::table('users')->where('id', $id)->update(['name' => $name]);
   
   // ❌ EVITAR
   DB::select("SELECT * FROM users WHERE email = '$email'");
   ```

---

## 🔍 Auditoria de Segurança

### Checklist

- [ ] `.env` não está no repositório
- [ ] `APP_DEBUG=false` em produção
- [ ] Senhas estão hasheadas
- [ ] Inputs são validados
- [ ] Outputs são escapados
- [ ] CSRF está ativo
- [ ] Rate limiting configurado
- [ ] Headers de segurança configurados
- [ ] Uploads validados
- [ ] Logs não expõem dados sensíveis

### Ferramentas Recomendadas

```bash
# Verificar dependências vulneráveis
composer audit

# Análise estática de código
./vendor/bin/phpstan analyse

# Verificar código com Pint
./vendor/bin/pint --test
```

---

## 📞 Contato de Segurança

**E-mail de Segurança**: leo.vdf3@gmail.com

**Tempo de Resposta**:
- Crítico: 24 horas
- Alto: 48 horas
- Médio: 7 dias
- Baixo: 14 dias

---

## 📜 Histórico de Segurança

### 2025-01-06 - v1.0.0
- Lançamento inicial
- Implementação completa de CSRF, Rate Limiting e Headers de segurança
- Sistema de licenciamento com assinatura digital

---

**Última atualização**: Janeiro 2025

