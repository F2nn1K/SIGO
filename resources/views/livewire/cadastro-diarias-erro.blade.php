<div class="alert alert-danger">
    <h4><i class="fas fa-exclamation-triangle mr-2"></i>Ocorreu um erro ao carregar o componente</h4>
    <p>Não foi possível carregar o formulário de cadastro de diárias. Por favor, tente as seguintes ações:</p>
    <ol>
        <li>Recarregue a página</li>
        <li>Limpe o cache do navegador</li>
        <li>Contate o administrador do sistema</li>
    </ol>
    
    @if(app()->environment('local') || app()->environment('development'))
    <div class="mt-3 p-3 bg-light">
        <strong>Detalhes do erro (apenas em ambiente de desenvolvimento):</strong>
        <pre class="mt-2">{{ $erro ?? 'Erro desconhecido' }}</pre>
    </div>
    @endif
    
    <div class="mt-3">
        <button class="btn btn-primary" onclick="window.location.reload()">
            <i class="fas fa-sync-alt mr-2"></i>Recarregar Página
        </button>
        <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
            <i class="fas fa-home mr-2"></i>Voltar para a Página Inicial
        </a>
    </div>
</div> 