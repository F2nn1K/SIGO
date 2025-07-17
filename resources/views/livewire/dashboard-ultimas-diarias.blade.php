<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Últimas Diárias</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <ul class="products-list product-list-in-card pl-2 pr-2">
                @forelse($ultimasDiarias as $diaria)
                    <li class="item">
                        <div class="product-info">
                            <a href="javascript:void(0)" class="product-title">
                                {{ Str::limit($diaria->nome, 40) }}
                                <span class="badge badge-info float-right">R$ {{ number_format($diaria->diaria, 2, ',', '.') }}</span>
                            </a>
                            <span class="product-description">
                                {{ $diaria->departamento }} - {{ $diaria->referencia }} - {{ $diaria->data_inclusao->format('d/m/Y') }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="item">
                        <div class="product-info text-center py-3">
                            <span class="text-muted">Nenhuma diária cadastrada</span>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('diarias.index') }}" class="text-primary">Ver Todas as Diárias</a>
        </div>
    </div>
</div> 