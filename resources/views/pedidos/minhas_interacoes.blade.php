@extends('adminlte::page')

@section('title', 'Meus Pedidos e Interações')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-comments text-primary mr-2"></i>Meus Pedidos e Interações</h1>
@stop

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Histórico dos meus pedidos de compras</h3>
    <span class="text-muted small">Apenas pedidos feitos por você</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0" id="tabela-meus-pedidos">
        <thead>
          <tr>
            <th>Nº Pedido</th>
            <th>Data</th>
            <th>Centro de Custo</th>
            <th>Prioridade</th>
            <th>Status</th>
            <th>Itens</th>
            <th class="text-nowrap">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="7" class="text-center text-muted" id="empty-row">
              <i class="fas fa-info-circle fa-2x mb-2"></i><br>Nenhum pedido encontrado
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal detalhes/interações -->
<div class="modal fade" id="modalInteracoes" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-comment-dots mr-2"></i>Interações do Pedido <span id="md-numero"></span></h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-1"><strong>Data:</strong> <span id="md-data"></span></p>
            <p class="mb-1"><strong>Centro de Custo:</strong> <span id="md-cc"></span></p>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Prioridade:</strong> <span id="md-prioridade"></span></p>
            <p class="mb-1"><strong>Status:</strong> <span id="md-status"></span></p>
          </div>
        </div>
        <hr/>
        <p class="mb-2"><strong>Itens</strong></p>
        <ul class="list-group mb-3" id="md-itens"></ul>
        <div class="form-group mb-2">
          <label for="md-msg" class="mb-1">Enviar mensagem ao autorizador</label>
          <textarea id="md-msg" class="form-control" rows="2" placeholder="Digite sua mensagem..."></textarea>
          <div class="text-right mt-2">
            <button class="btn btn-primary btn-sm" onclick="enviarMensagem()"><i class="fas fa-paper-plane mr-1"></i>Enviar</button>
          </div>
        </div>
        <p class="mb-2"><strong>Interações</strong></p>
        <ul class="list-group mb-3" id="md-interacoes"></ul>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
  
</div>
@stop

@section('js')
<script>
$(function(){ carregarPedidos(); });

function carregarPedidos(){
  $.get('/api/pedidos/minhas-interacoes', function(resp){
    const tbody = $('#tabela-meus-pedidos tbody'); tbody.empty();
    if(!resp.success || !resp.data || resp.data.length === 0){
      tbody.append('<tr><td colspan="7" class="text-center text-muted">Nenhum pedido encontrado</td></tr>');
      return;
    }

    resp.data.forEach(function(p){
      const itensResumo = p.itens.length + ' itens';
      const tr = `
        <tr>
          <td><span class="badge badge-dark">${p.num_pedido}</span></td>
          <td>${(p.data_solicitacao||'').replace('T',' ').substring(0,16)}</td>
          <td>${p.centro_custo_nome||'—'}</td>
          <td><span class="badge badge-${p.prioridade}">${(p.prioridade||'').toUpperCase()}</span></td>
          <td>${(p.aprovacao||'pendente').toUpperCase()}</td>
          <td>${itensResumo}</td>
          <td class="text-nowrap">
            <button class="btn btn-outline-primary btn-sm" onclick='abrirInteracoes(${JSON.stringify(p)})'>
              <i class="fas fa-search"></i>
            </button>
          </td>
        </tr>`;
      tbody.append(tr);
    });
  });
}

function abrirInteracoes(p){
  $('#md-numero').text(p.num_pedido);
  $('#md-data').text((p.data_solicitacao||'').replace('T',' ').substring(0,16));
  $('#md-cc').text(p.centro_custo_nome||'—');
  $('#md-prioridade').text((p.prioridade||'').toUpperCase());
  $('#md-status').text((p.aprovacao||'pendente').toUpperCase());
  $('#md-itens').html(p.itens.map(i=>`<li class="list-group-item d-flex justify-content-between"><span>${i.produto_nome}</span><span class="badge badge-secondary">${i.quantidade}</span></li>`).join(''));

  // Carregar interações do primeiro item (representativo) — pode ser refinado para somar todas
  if(p.itens && p.itens.length){
    $.get(`/api/pedidos/${p.itens[0].id}/interacoes`, function(resp){
      if(resp.success){
        const lis = resp.data.map(it => `<li class="list-group-item"><strong>${it.usuario}</strong> — <span class="text-muted">${(it.created_at||'').replace('T',' ').substring(0,16)}</span><br>${formatTipo(it.tipo)}${it.mensagem ? ': '+escapeHtml(it.mensagem) : ''}</li>`).join('');
        $('#md-interacoes').html(lis || '<li class="list-group-item text-muted">Sem interações</li>');
      } else {
        $('#md-interacoes').html('<li class="list-group-item text-muted">Sem interações</li>');
      }
      $('#modalInteracoes').modal('show');
      // guardar id do primeiro item para enviar mensagem
      $('#modalInteracoes').data('itemId', p.itens[0].id);
    });
  } else {
    $('#md-interacoes').html('<li class="list-group-item text-muted">Sem interações</li>');
    $('#modalInteracoes').modal('show');
  }
}

function enviarMensagem(){
  const itemId = $('#modalInteracoes').data('itemId');
  const mensagem = ($('#md-msg').val()||'').trim();
  if(!itemId){ return; }
  if(!mensagem){
    Swal.fire('Atenção', 'Digite uma mensagem.', 'warning');
    return;
  }
  $.post(`/api/pedidos/${itemId}/interagir`, { mensagem }, function(resp){
    if(resp && resp.success){
      $('#md-msg').val('');
      // recarregar interações
      $.get(`/api/pedidos/${itemId}/interacoes`, function(r){
        if(r.success){
          const lis = r.data.map(it => `<li class="list-group-item"><strong>${it.usuario}</strong> — <span class="text-muted">${(it.created_at||'').replace('T',' ').substring(0,16)}</span><br>${formatTipo(it.tipo)}${it.mensagem ? ': '+escapeHtml(it.mensagem) : ''}</li>`).join('');
          $('#md-interacoes').html(lis || '<li class="list-group-item text-muted">Sem interações</li>');
        }
      });
      Swal.fire('Enviado', 'Mensagem registrada com sucesso.', 'success');
    } else {
      Swal.fire('Erro', (resp && resp.message) ? resp.message : 'Não foi possível enviar.', 'error');
    }
  }).fail(function(xhr){
    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Falha ao enviar mensagem';
    Swal.fire('Erro', msg, 'error');
  });
}

function formatTipo(t){
  if(!t) return '';
  if(t === 'aprovacao') return '<span class="badge badge-success">APROVAÇÃO</span>';
  if(t === 'rejeicao') return '<span class="badge badge-danger">REJEIÇÃO</span>';
  return `<span class="badge badge-info">${t.toUpperCase()}</span>`;
}

function escapeHtml(str){
  return (str||'').replace(/[&<>"]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; });
}
</script>
@stop


