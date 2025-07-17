<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Últimas Tarefas</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <ul class="products-list product-list-in-card pl-2 pr-2">
                @forelse($ultimasTarefas as $tarefa)
                    <li class="item">
                        <div class="product-info">
                            <a href="{{ route('rh.edit', $tarefa->id) }}" class="product-title">
                                {{ Str::limit($tarefa->descricao, 50) }}
                                @if($tarefa->status == 'Pendente')
                                    <span class="badge badge-secondary float-right">{{ $tarefa->status }}</span>
                                @elseif($tarefa->status == 'Em andamento')
                                    <span class="badge badge-warning float-right">{{ $tarefa->status }}</span>
                                @elseif($tarefa->status == 'Concluída')
                                    <span class="badge badge-success float-right">{{ $tarefa->status }}</span>
                                @else
                                    <span class="badge badge-info float-right">{{ $tarefa->status }}</span>
                                @endif
                            </a>
                            <span class="product-description">
                                Criado por {{ $tarefa->usuario_nome }} em {{ $tarefa->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="item">
                        <div class="product-info text-center py-3">
                            <span class="text-muted">Nenhuma tarefa cadastrada</span>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('rh.administrador') }}" class="text-primary">Ver Todas as Tarefas</a>
        </div>
    </div>
</div> 