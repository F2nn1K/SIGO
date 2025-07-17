<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Diárias por Departamento</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div style="height: 200px; position: relative;">
                        <canvas id="grafico-departamentos"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departamentos as $index => $departamento)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: {{ $cores[$index] ?? '#007bff' }}; width: 10px; height: 10px; display: inline-block;"></span>
                                            {{ $departamento->departamento ?? 'Não informado' }}
                                        </td>
                                        <td class="text-center">{{ $departamento->total }}</td>
                                        <td class="text-right">R$ {{ number_format($departamento->valor_total, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3">Nenhum departamento encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('diarias.relatorio') }}" class="text-primary">Ver Relatório Completo</a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            // Inicializar o gráfico de pizza para departamentos
            const ctx = document.getElementById('grafico-departamentos').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: @json($labels ?? []),
                    datasets: [{
                        data: @json($totais ?? []),
                        backgroundColor: @json($cores ?? []),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    return label + ': ' + value + ' diárias';
                                }
                            }
                        }
                    }
                }
            });

            // Atualizar o gráfico quando os dados do componente forem atualizados
            Livewire.on('diariasDadosAtualizados', (event) => {
                chart.data.labels = event.labels || [];
                chart.data.datasets[0].data = event.totais || [];
                chart.data.datasets[0].backgroundColor = event.cores || [];
                chart.update();
            });
        });
    </script>
    @endpush
</div> 