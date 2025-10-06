@extends('adminlte::page')

@section('title', 'Funcionários Ativos/Inativos')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
  <div>
    <h1 class="m-0 text-dark font-weight-bold">
      <i class="fas fa-users text-primary mr-2"></i>
      Relatório de Funcionários Ativos/Inativos
    </h1>
    <small class="text-muted">Filtre por status, empresa e período de cadastro (opcional).</small>
  </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <i class="fas fa-filter mr-2"></i> Filtros
    </div>
    <div class="card-body">
      <form id="formFuncStatus">
        <div class="form-row">
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Status</label>
            <select id="status" class="form-control">
              <option value="">Todos</option>
              <option value="trabalhando">Trabalhando</option>
              <option value="afastado">Afastado</option>
              <option value="ferias">Férias</option>
              <option value="demitido">Demitido</option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Data início (opcional)</label>
            <input id="data_inicio" type="date" class="form-control">
          </div>
          <div class="col-md-4 mb-3">
            <label class="font-weight-bold">Data fim (opcional)</label>
            <input id="data_fim" type="date" class="form-control">
          </div>
        </div>
        <div class="form-row">
          <div class="col-md-12 d-flex align-items-end">
            <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i>Gerar Relatório</button>
            <button type="button" id="btnLimpar" class="btn btn-secondary"><i class="fas fa-eraser mr-1"></i>Limpar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card mt-3" id="cardResultados" style="display:none;">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0" id="tabela">
          <thead class="thead-dark">
            <tr>
              <th width="60">ID</th>
              <th>Nome</th>
              <th width="110">CPF</th>
              <th width="110">Sexo</th>
              <th>Função</th>
              <th width="110" class="text-center">Status</th>
              <th>Empresa</th>
              <th width="140">Criado em</th>
              <th width="140">Atualizado em</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@stop

@section('js')
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$('#formFuncStatus').on('submit', function(e){
  e.preventDefault();
  const dados = {
    status: $('#status').val(),
    data_inicio: $('#data_inicio').val(),
    data_fim: $('#data_fim').val()
  };
  $.post('/api/relatorios/funcionarios-ativos-inativos', dados, function(r){
    if(!(r && r.success)) { alert(r && r.message ? r.message : 'Falha ao buscar.'); return; }
    const tbody = $('#tabela tbody'); tbody.empty();
    (r.data||[]).forEach(function(f){
      const s = String(f.status||'').toLowerCase();
      let st = '<span class="badge badge-secondary">' + (f.status||'—') + '</span>';
      if (s==='trabalhando' || s==='ativo') st = '<span class="badge badge-success">Trabalhando</span>';
      else if (s==='afastado') st = '<span class="badge badge-warning">Afastado</span>';
      else if (s==='ferias' || s==='férias') st = '<span class="badge badge-info">Férias</span>';
      else if (s==='demitido' || s==='inativo') st = '<span class="badge badge-danger">Demitido</span>';
      const tr = `
        <tr>
          <td>${f.id}</td>
          <td>${escapeHtml(f.nome||'')}</td>
          <td>${escapeHtml(f.cpf||'')}</td>
          <td>${escapeHtml(f.sexo||'')}</td>
          <td>${escapeHtml(f.funcao||'')}</td>
          <td class="text-center">${st}</td>
          <td>${escapeHtml(f.empresa||'')}</td>
          <td>${formatarData(f.created_at)}</td>
          <td>${formatarData(f.updated_at)}</td>
        </tr>`;
      tbody.append(tr);
    });
    $('#cardResultados').toggle((r.data||[]).length>0);
  }).fail(function(xhr){ alert('Erro: '+(xhr.responseJSON?.message||'servidor')); });
});

$('#btnLimpar').on('click', function(){
  $('#formFuncStatus')[0].reset();
  $('#cardResultados').hide();
});

function formatarData(iso){ if(!iso) return '—'; const d=new Date(iso); if(isNaN(d)) return '—'; return d.toLocaleDateString('pt-BR'); }
function escapeHtml(t){ const div=document.createElement('div'); div.textContent=t||''; return div.innerHTML; }
</script>
@stop


