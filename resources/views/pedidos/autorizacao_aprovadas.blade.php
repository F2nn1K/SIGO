@extends('adminlte::page')

@section('title', 'Autorizações Aprovadas')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-check text-success mr-2"></i>Autorizações Aprovadas</h1>
@stop

@section('content')
<div class="card card-success shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-success text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Aprovadas</strong>
      <a href="{{ route('pedidos.autorizacao') }}" class="btn btn-outline-light btn-sm text-white"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-aprovadas">
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
$(function(){ carregarAprovados(); });
function carregarAprovados(){
  $.get('/api/pedidos-aprovados-agrupados', function(resp){
    if(!resp.success) return; const grupos = resp.data || [];
    $('#badge-aprovadas').text(`${grupos.length} aprovadas`);
    const tbody = $('#tabela-aprovadas tbody'); tbody.empty();
      if(grupos.length===0){ tbody.append(`<tr><td colspan="7" class="text-center text-muted" id="empty-row">Nenhum registro encontrado</td></tr>`); return; }
    const esc = s=>String(s||'').replace(/[&<>\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    grupos.forEach(function(g){
      const tr = `
        <tr>
            <td><span class="badge badge-dark">${g.num_pedido||'—'}</span></td>
          <td>${(g.data_solicitacao||'').replace('T',' ').substring(0,16)}</td>
          <td>${esc(g.solicitante||'—')}</td>
          <td>${g.itens} itens</td>
          <td>${g.quantidade_total||0}</td>
          <td><span class="badge badge-${g.prioridade}">${(g.prioridade||'').toUpperCase()}</span></td>
          <td>${esc(g.centro_custo_nome||'—')}</td>
        </tr>`; tbody.append(tr);
    });
  });
}
</script>
@stop


