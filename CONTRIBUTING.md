# 🤝 Guia de Contribuição - Sistema BRS (SIGO)

Obrigado por considerar contribuir com o Sistema BRS! Este documento fornece diretrizes para contribuições.

---

## 📋 Código de Conduta

- Seja respeitoso e profissional
- Mantenha o código limpo e bem documentado
- Siga os padrões estabelecidos no projeto
- Teste suas alterações antes de submeter

---

## 🔄 Processo de Contribuição

### 1. Fork e Clone

```bash
# Fork o repositório via GitHub
# Clone seu fork
git clone https://github.com/seu-usuario/SIGO.git
cd SIGO
```

### 2. Configurar Ambiente de Desenvolvimento

```bash
# Instalar dependências
composer install
npm install

# Copiar .env
cp .env.example .env
php artisan key:generate

# Configurar banco local
# Editar .env com suas credenciais locais
```

### 3. Criar Branch

```bash
# Sempre crie uma branch para sua feature/fix
git checkout -b feature/nome-da-feature

# Ou para correções
git checkout -b fix/descricao-do-bug
```

### 4. Desenvolver

- Escreva código limpo e legível
- Adicione comentários quando necessário
- Siga PSR-12 (padrão Laravel)
- Evite duplicação de código

### 5. Testar

```bash
# Executar testes (quando implementados)
php artisan test

# Verificar código com Pint
./vendor/bin/pint
```

### 6. Commit

Use Conventional Commits:

```bash
# Formato: tipo(escopo): descrição
git commit -m "feat(pedidos): adiciona filtro de mês"
git commit -m "fix(dp): corrige upload de arquivos grandes"
git commit -m "docs: atualiza README com novos endpoints"
```

**Tipos de commit:**
- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação (sem mudança de lógica)
- `refactor:` Refatoração
- `perf:` Melhoria de performance
- `test:` Testes
- `chore:` Manutenção

### 7. Push e Pull Request

```bash
# Push para seu fork
git push origin feature/nome-da-feature
```

Abra um Pull Request no GitHub com:
- Título descritivo
- Descrição detalhada das mudanças
- Screenshots (se alteração visual)
- Referência a issues relacionadas

---

## 🎯 Diretrizes de Código

### PHP/Laravel

```php
// ✅ BOM
public function aprovarPedido(Request $request, int $id): JsonResponse
{
    $request->validate([
        'observacao' => 'nullable|string|max:500'
    ]);
    
    $pedido = Pedido::findOrFail($id);
    $pedido->aprovar(auth()->user(), $request->observacao);
    
    return response()->json([
        'success' => true,
        'message' => 'Pedido aprovado com sucesso'
    ]);
}

// ❌ EVITAR
public function aprovar($id)
{
    $p = DB::table('pedidos')->where('id',$id)->first();
    DB::table('pedidos')->where('id',$id)->update(['status'=>'ok']);
    return ['ok'=>true];
}
```

### JavaScript/jQuery

```javascript
// ✅ BOM
function carregarPedidos(filtros) {
    $.ajax({
        url: '/api/pedidos',
        method: 'GET',
        data: filtros,
        success: function(response) {
            if (response.success) {
                exibirPedidos(response.data);
            }
        },
        error: function(xhr) {
            Swal.fire('Erro!', 'Falha ao carregar pedidos', 'error');
        }
    });
}

// ❌ EVITAR
function load(){
  $.get('/api/pedidos',function(d){$('#x').html(d);});
}
```

### Blade Templates

```blade
{{-- ✅ BOM --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $titulo }}</h3>
    </div>
    <div class="card-body">
        @foreach($itens as $item)
            <p>{{ $item->nome }}</p>
        @endforeach
    </div>
</div>

{{-- ❌ EVITAR --}}
<div><h3><?php echo $titulo; ?></h3>
@foreach($itens as $item)<p>{{$item->nome}}</p>@endforeach
</div>
```

---

## 🧪 Testes

### Escrevendo Testes

```php
// tests/Feature/PedidoTest.php
public function test_usuario_pode_criar_pedido()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/api/pedidos', [
            'centro_custo_id' => 1,
            'produtos' => [
                ['nome' => 'Produto Teste', 'quantidade' => 5]
            ],
            'prioridade' => 'media'
        ]);
    
    $response->assertStatus(200)
             ->assertJson(['success' => true]);
}
```

### Executando Testes

```bash
# Todos os testes
php artisan test

# Teste específico
php artisan test --filter test_usuario_pode_criar_pedido

# Com coverage
php artisan test --coverage
```

---

## 📝 Documentação

### Documentar APIs

Use comentários PHPDoc:

```php
/**
 * Aprova um pedido de compras
 *
 * @param \Illuminate\Http\Request $request
 * @param int $id ID do pedido
 * @return \Illuminate\Http\JsonResponse
 * 
 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
 */
public function aprovar(Request $request, int $id): JsonResponse
{
    // ...
}
```

### Documentar Rotas

Mantenha comentários nas rotas:

```php
// Pedidos de Compras - Autorização
Route::middleware(['can:autorizacao-pedidos'])->group(function () {
    // Lista pedidos pendentes agrupados
    Route::get('/api/pedidos-pendentes-agrupados', [/* ... */]);
});
```

---

## 🚫 O Que NÃO Fazer

### Nunca commitar:
- ❌ Arquivo `.env` com dados reais
- ❌ Senhas ou credenciais
- ❌ Arquivos de licença
- ❌ Dados de clientes/usuários
- ❌ Arquivos de backup (`.sql`, `.bak`)
- ❌ Arquivos de log
- ❌ Diretórios `vendor/` e `node_modules/`

### Evitar:
- ❌ Código comentado em excesso
- ❌ `dd()`, `dump()`, `var_dump()` em produção
- ❌ `echo`, `print_r()` para debug
- ❌ Queries SQL diretas sem sanitização
- ❌ Hard-coded de valores que devem ser configuráveis
- ❌ Lógica de negócio nas views

---

## 🎨 Padrões de Estilo

### PHP
- Seguir PSR-12
- Usar type hints
- Preferir Eloquent sobre Query Builder
- Controllers focados (máx. 300-400 linhas)
- Extrair lógica complexa para Services

### Frontend
- Usar Bootstrap 5 classes padrão
- Preferir componentes AdminLTE
- SweetAlert2 para alertas
- jQuery para DOM manipulation simples
- Código JavaScript modular

### Banco de Dados
- Não usar migrations (estrutura gerenciada manualmente)
- Usar índices em colunas de busca
- Normalização adequada (3NF)
- Foreign keys para integridade referencial

---

## 📚 Recursos

- [Documentação Laravel](https://laravel.com/docs/10.x)
- [AdminLTE Docs](https://adminlte.io/docs/3.1/)
- [SweetAlert2 Docs](https://sweetalert2.github.io/)
- [DataTables Docs](https://datatables.net/manual/)

---

## 💬 Dúvidas?

- Abra uma [Issue](https://github.com/F2nn1K/SIGO/issues) com sua dúvida
- Entre em contato: leo.vdf3@gmail.com

---

Obrigado por contribuir! 🎉

