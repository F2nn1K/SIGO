@extends('adminlte::page')

@section('title', 'Autorizações Pendentes')

@section('plugins.Sweetalert2', true)

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-clock text-warning mr-2"></i>Autorizações Pendentes</h1>
@stop

@section('content')
<div class="card card-primary shadow-sm">
  <div class="card-body p-0">
    <div class="px-3 py-2 bg-primary text-white d-flex justify-content-between align-items-center">
      <strong>Solicitações Pendentes de Autorização</strong>
      <a href="{{ route('pedidos.autorizacao') }}" class="btn btn-outline-light btn-sm text-white"><i class="fas fa-arrow-left mr-1"></i>Voltar</a>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle" id="tabela-pendentes">
        <thead>
          <tr>
            <th>Nº Pedido</th>
            <th>Data Solicitação</th>
            <th>Solicitante</th>
            <th>Produto</th>
            <th>Quantidade</th>
            <th>Prioridade</th>
            <th>Centro Custo</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="8" class="text-center text-muted" id="empty-row">
              <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
              Nenhuma solicitação pendente de autorização
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
$(function(){ carregarPendentes(); setInterval(carregarPendentes, 30000); });
function carregarPendentes(){
  $.get('/api/pedidos-pendentes-agrupados', function(resp){
    if(!resp.success) return; const grupos = resp.data || [];
    $('#badge-pendentes').text(`${grupos.length} pendentes`);
    const tbody = $('#tabela-pendentes tbody'); tbody.empty();
    if(grupos.length===0){ tbody.append(`<tr><td colspan="8" class="text-center text-muted" id="empty-row">
      <i class=\"fas fa-check-circle fa-2x mb-2 text-success\"></i><br>
      Nenhuma solicitação pendente de autorização
    </td></tr>`); return; }
    const esc = s=>String(s||'').replace(/[&<>\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    const prioBadge = p=>{ const k=String(p||'').toLowerCase(); return k==='alta'?'danger':(k==='media'?'warning':'secondary'); };
    const prioRow = p=>{ const k=String(p||'').toLowerCase(); return k==='alta'?'tr-prio-alta':(k==='media'?'tr-prio-media':'tr-prio-baixa'); };
    grupos.forEach(function(g){
      const rowCls = prioRow(g.prioridade);
      const badgeCls = prioBadge(g.prioridade);
      const tr = `
        <tr class="${rowCls}">
          <td><span class="badge badge-dark">${g.num_pedido||'—'}</span></td>
          <td>${(g.data_solicitacao||'').replace('T',' ').substring(0,16)}</td>
          <td>${esc(g.solicitante||'—')}</td>
          <td>
            <div class="d-flex align-items-center">
              <div class="rounded bg-light mr-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                <i class="fas fa-box text-primary"></i>
              </div>
              <div>
                <div class="font-weight-600">${g.itens} itens</div>
                <small class="text-muted">Qtd total: ${g.quantidade_total||0}</small>
              </div>
            </div>
          </td>
          <td>${g.quantidade_total||'—'}</td>
           <td><span class="badge badge-${badgeCls}">${(g.prioridade||'').toUpperCase()}</span></td>
          <td>${esc(g.centro_custo_nome||'—')}</td>
          <td class="text-nowrap">
            <button class="btn btn-outline-primary btn-sm" onclick="abrirGrupo('${g.grupo_hash}')" title="Detalhar"><i class="fas fa-search"></i></button>
          </td>
        </tr>`; tbody.append(tr);
    });
  });
}

function abrirGrupo(hash){
  $.get(`/api/pedidos-agrupado/${hash}`, function(resp){
    if(!resp.success) return;
    const cab = resp.data.cabecalho; const itens = resp.data.itens; const interacoes = resp.data.interacoes||[];
    const lista = itens.map(i => `<li class=\"list-group-item d-flex justify-content-between\"><span>${i.produto_nome}</span><span class=\"badge badge-secondary\">${i.quantidade}</span></li>`).join('');
    const html = `
      <div class=\"modal fade\" id=\"modalGrupo\" tabindex=\"-1\" role=\"dialog\">
        <div class=\"modal-dialog modal-lg\" role=\"document\"><div class=\"modal-content\">
          <div class=\"modal-header\"> 
            <h4 class=\"modal-title mb-0\"><i class=\"fas fa-file-alt mr-2\"></i>Detalhes da Solicitação</h4> 
            <button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span>&times;</span></button> 
          </div>
          <div class=\"modal-body\">
            <div class=\"row\">
              <div class=\"col-md-6\"><p><strong>Nº Pedido:</strong> ${cab.num_pedido||'—'}</p><p><strong>Data:</strong> ${cab.data_solicitacao}</p><p><strong>Solicitante:</strong> ${cab.solicitante}</p></div>
              <div class=\"col-md-6\"><p><strong>Prioridade:</strong> ${cab.prioridade.toUpperCase()}</p><p><strong>Centro de Custo:</strong> ${cab.centro_custo_nome}</p></div>
            </div>
            <p><strong>Observações:</strong></p>
            <div class=\"bg-light p-2 rounded\">${cab.observacao || '—'}</div>
            <hr/>
            <p class=\"mb-2\"><strong>Itens</strong></p>
            <ul class=\"list-group\">${lista}</ul>
            <hr/>
            <div class=\"form-group mb-2\"> 
              <label for=\"msg_interacao\" class=\"mb-1\">Interagir com o solicitante</label>
              <textarea id=\"msg_interacao\" class=\"form-control\" rows=\"2\" placeholder=\"Mensagem ao solicitante\"></textarea>
              <div class=\"d-flex justify-content-between align-items-center mt-2\"> 
                <div></div>
                <div>
                  <button class=\"btn btn-success btn-sm mr-1\" onclick=\"aprovarGrupo('${hash}')\"><i class=\"fas fa-check mr-1\"></i>Aprovar</button> 
                  <button class=\"btn btn-danger btn-sm mr-1\" onclick=\"rejeitarGrupo('${hash}')\"><i class=\"fas fa-times mr-1\"></i>Rejeitar</button> 
                  <button class=\"btn btn-outline-primary btn-sm\" onclick=\"enviarMensagemGrupo('${hash}')\"><i class=\"fas fa-paper-plane mr-1\"></i>Enviar mensagem</button> 
                </div>
              </div> 
            </div>
            <p class=\"mb-1\"><strong>Interações</strong></p>
            <ul class=\"list-group\" id=\"lista-interacoes\">${interacoes.map(int => `
              <li class='list-group-item'>
                <strong>${int.usuario}</strong>
                <span class='text-muted'>${(int.created_at||'').replace('T',' ').substring(0,16)}</span><br>
                ${formatTipo(int.tipo)}${int.mensagem ? ': '+escapeHtml(int.mensagem) : ''}
              </li>
            `).join('') || '<li class=\'list-group-item text-muted\'>Sem interações</li>'}</ul>
          </div>
          <div class=\"modal-footer\"> 
            <button class=\"btn btn-secondary\" data-dismiss=\"modal\">Fechar</button> 
          </div>
        </div></div>
      </div>`;
    $('#modalGrupo').remove(); $('body').append(html); $('#modalGrupo').modal('show');
  });
}

function enviarMensagemGrupo(hash){
  const mensagem = ($('#msg_interacao').val()||'').trim();
  if(!mensagem){ Swal.fire('Atenção','Digite uma mensagem.','warning'); return; }
  $.post(`/api/pedidos-agrupado/${hash}/mensagem`, {mensagem}, function(r){
    if(r && r.success){
      // após enviar, recarrega detalhes para listar interação
      $.get(`/api/pedidos-agrupado/${hash}`, function(resp){
        if(resp.success){
          const interacoes = resp.data.interacoes||[];
          const lis = interacoes.map(int => `
            <li class='list-group-item'>
              <strong>${int.usuario}</strong>
              <span class='text-muted'>${(int.created_at||'').replace('T',' ').substring(0,16)}</span><br>
              ${formatTipo(int.tipo)}${int.mensagem ? ': '+escapeHtml(int.mensagem) : ''}
            </li>`).join('') || "<li class='list-group-item text-muted'>Sem interações</li>";
          $('#lista-interacoes').html(lis);
          $('#msg_interacao').val('');
          toastr && toastr.success('Mensagem enviada');
        }
      });
    } else {
      Swal.fire('Erro', (r && r.message) ? r.message : 'Não foi possível enviar.', 'error');
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
  if(t === 'comentario') return '<span class="badge badge-info">COMENTÁRIO</span>';
  return `<span class="badge badge-secondary">${(t||'').toUpperCase()}</span>`;
}

function escapeHtml(str){
  return (str||'').replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
}

function aprovarGrupo(hash){
  const mensagem = $('#msg_interacao').val();
  Swal.fire({
    title: 'Confirmar aprovação',
    text: 'Deseja aprovar este pedido de compras?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, aprovar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if(!result.isConfirmed) return;
    Swal.fire({
      title: 'Processando...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });
    $.ajax({
      url:`/api/pedidos-agrupado/${hash}/aprovar`, method:'PUT', data:{mensagem},
      success:function(r){
        Swal.close();
        if(r && r.success){
          $('#modalGrupo').modal('hide');
          Swal.fire('Aprovado!', 'Pedido aprovado com sucesso.', 'success');
          carregarPendentes();
        } else {
          Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível aprovar.', 'warning');
        }
      },
      error:function(xhr){
        Swal.close();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao aprovar pedido.';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
}

function rejeitarGrupo(hash){
  const mensagem = $('#msg_interacao').val();
  Swal.fire({
    title: 'Confirmar rejeição',
    text: 'Deseja rejeitar este pedido de compras?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, rejeitar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if(!result.isConfirmed) return;
    Swal.fire({
      title: 'Processando...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });
    $.ajax({
      url:`/api/pedidos-agrupado/${hash}/rejeitar`, method:'PUT', data:{mensagem},
      success:function(r){
        Swal.close();
        if(r && r.success){
          $('#modalGrupo').modal('hide');
          Swal.fire('Rejeitado!', 'Pedido rejeitado com sucesso.', 'success');
          carregarPendentes();
        } else {
          Swal.fire('Atenção', (r && r.message) ? r.message : 'Não foi possível rejeitar.', 'warning');
        }
      },
      error:function(xhr){
        Swal.close();
        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao rejeitar pedido.';
        Swal.fire('Erro', msg, 'error');
      }
    });
  });
}
</script>
<style>
.tr-prio-alta { border-left: 4px solid #dc3545; }
.tr-prio-media { border-left: 4px solid #ffc107; }
.tr-prio-baixa { border-left: 4px solid #6c757d; }
</style>
@stop


