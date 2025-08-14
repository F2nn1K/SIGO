@extends('adminlte::page')
@section('title', 'Meus Aprovados')
@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-check text-success mr-2"></i>Meus Aprovados</h1>
@stop
@section('content')
<div class="card card-success shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-success text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Aprovadas</strong>
      <a href="{{ route('pedidos.acompanhar') }}" class="btn btn-outline-light btn-sm text-white"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0" id="tabela">
        <thead><tr><th>Nº Pedido</th><th>Data</th><th>Centro Custo</th><th>Itens</th><th>Qtd Total</th><th>Prioridade</th><th>Ações</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

@include('pedidos.partials_modal_acompanhar')
@stop
@section('js')
<script>
// Função para formatar data no padrão brasileiro (DD/MM/AAAA HH:MM)
function formatarDataBR(dataISO) {
    if (!dataISO) return '—';
    const data = new Date(dataISO);
    if (isNaN(data.getTime())) return '—';
    
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
}

$(function(){ carregar(); });
function carregar(){
  $.get('/api/pedidos/acompanhar/lista', function(resp){
    const tbody = $('#tabela tbody'); tbody.empty();
    const dados = (resp.data||[]).filter(d => (d.status||'pendente')==='aprovado');
    if(dados.length===0){ tbody.append('<tr><td colspan="7" class="text-center text-muted">Nenhum registro</td></tr>'); return; }
    dados.forEach(p => tbody.append(row(p)));
  });
}
function row(p){
  return `<tr>
    <td><span class="badge badge-dark">${p.num_pedido}</span></td>
    <td>${formatarDataBR(p.data_solicitacao)}</td>
    <td>${p.centro_custo_nome||'—'}</td>
    <td>${p.itens} itens</td>
    <td>${p.quantidade_total||0}</td>
    <td><span class="badge badge-${p.prioridade}">${(p.prioridade||'').toUpperCase()}</span></td>
    <td><button class="btn btn-outline-primary btn-sm" onclick="abrir('${p.grupo_hash}')"><i class="fas fa-search"></i></button></td>
  </tr>`;
}
function abrir(hash){
  $.get(`/api/pedidos/acompanhar/${hash}`, function(resp){
    if(!resp.success) return; const h=resp.data.cabecalho, itens=resp.data.itens||[], ints=resp.data.interacoes||[];
    $('#num').text(h.num_pedido); $('#cc').text(h.centro_custo_nome||'—'); $('#pri').text((h.prioridade||'').toUpperCase());
    $('#itens').html(itens.map(i=>`<li class=\"list-group-item d-flex justify-content-between\"><span>${i.produto_nome}</span><span class=\"badge badge-secondary\">${i.quantidade}</span></li>`).join(''));
    $('#ints').html(ints.map(it=>`<li class=\"list-group-item\"><strong>${it.usuario}</strong> — <span class=\"text-muted\">${formatarDataBR(it.created_at)}</span><br>${it.tipo.toUpperCase()}${it.mensagem?': '+it.mensagem:''}</li>`).join('')||'<li class=\"list-group-item text-muted\">Sem interações</li>');
    $('#modal').modal('show');
  });
}
</script>
@stop

