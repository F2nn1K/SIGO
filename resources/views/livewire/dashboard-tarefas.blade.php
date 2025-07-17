<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tarefas por Usuário</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th class="text-center">Em Andamento</th>
                        <th class="text-center">Concluídas</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario['nome'] }}</td>
                            <td class="text-center">{{ $usuario['emAndamento'] }}</td>
                            <td class="text-center">{{ $usuario['concluidas'] }}</td>
                            <td class="text-center">{{ $usuario['total'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3">Nenhum usuário com tarefas encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('rh.tarefas-por-usuarios') }}" class="text-primary">Ver Todos</a>
        </div>
    </div>
</div> 