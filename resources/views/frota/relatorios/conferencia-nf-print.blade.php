<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Conferência de NF - Lote #{{ $lote->id ?? '' }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; font-size: 12px; }
        th { background: #f2f2f2; }
        .header { margin-bottom: 10px; }
    </style>
    <script>
        // Não fazer print automático quando carregado via iframe
        if (window.self === window.top) {
            window.onload = function(){ window.print(); };
        }
    </script>
</head>
<body>
    <div class="header">
        <h3>Conferência de NF - Lote #{{ $lote->id ?? '' }}</h3>
        <div>NF: {{ $lote->numero_nf ?? '' }} | Itens: {{ $lote->qtd_itens ?? 0 }} | Total: R$ {{ number_format($lote->total_valor ?? 0, 2, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Veículo</th>
                <th>KM</th>
                <th>Litros</th>
                <th>Valor</th>
                <th>Preço/L</th>
                <th>Posto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itens as $i)
            <tr>
                <td>{{ $i->data }}</td>
                <td>{{ $i->placa ?: $i->veiculo_id }}</td>
                <td>{{ number_format($i->km, 0, ',', '.') }} km</td>
                <td>{{ number_format($i->litros, 3, ',', '.') }}</td>
                <td>R$ {{ number_format($i->valor, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($i->preco_litro, 3, ',', '.') }}</td>
                <td>{{ $i->posto }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
<!-- Gerado para impressão -->
</html>


