@extends('adminlte::page')

@section('title', 'Autorizações Rejeitadas')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-times text-danger mr-2"></i>Autorizações Rejeitadas</h1>
@stop

@section('content')
<div class="card card-danger shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-danger text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Rejeitadas</strong>
      <div class="d-flex align-items-center">
        <a href="{{ route('pedidos.autorizacao') }}" class="btn btn-outline-light btn-sm mr-2 text-white"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
        <span class="badge badge-light" id="badge-rejeitadas">0 rejeitadas</span>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-rejeitadas">
        <thead>
          <tr>
            <th>Nº Pedido</th>
            <th>Data Solicitação</th>
            <th>Solicitante</th>
            <th>Itens</th>
            <th>Qtd Total</th>
            <th>Prioridade</th>
            <th>Centro Custo</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="6" class="text-center text-muted" id="empty-row">
              <i class="fas fa-info-circle fa-2x mb-2 text-secondary"></i><br>
              Nenhum registro encontrado
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$(function(){ carregarRejeitados(); });
function carregarRejeitados(){
  $.get('/api/pedidos-rejeitados-agrupados', function(resp){
    if(!resp.success) return; const grupos = resp.data || [];
    $('#badge-rejeitadas').text(`${grupos.length} rejeitadas`);
    const tbody = $('#tabela-rejeitadas tbody'); tbody.empty();
    if(grupos.length===0){ tbody.append(`<tr><td colspan=\"7\" class=\"text-center text-muted\" id=\"empty-row\">Nenhum registro encontrado</td></tr>`); return; }
    grupos.forEach(function(g){
      const tr = `
        <tr>
          <td><span class="badge badge-dark">${g.num_pedido||'—'}</span></td>
          <td>${(g.data_solicitacao||'').replace('T',' ').substring(0,16)}</td>
          <td>${g.solicitante||'—'}</td>
          <td>${g.itens} itens</td>
          <td>${g.quantidade_total||0}</td>
          <td><span class="badge badge-${g.prioridade}">${(g.prioridade||'').toUpperCase()}</span></td>
          <td>${g.centro_custo_nome||'—'}</td>
        </tr>`; tbody.append(tr);
    });
  });
}
</script>
@stop


