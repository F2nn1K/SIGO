@extends('adminlte::page')

@section('title', 'Licenciamento')
@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-id-card-alt text-primary mr-2"></i>
            Licenciamento
        </h1>
        <p class="text-muted mt-1 mb-0">Controle de Licenciamento</p>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid os-page">
    <div class="modern-card">
        <div class="card-header-modern">
            <h3 class="card-title-modern"><i class="fas fa-plus-circle mr-2 text-primary"></i>Novo Registro</h3>
        </div>
        <div class="card-body-modern">
            @if(session('success'))
            <script>
            document.addEventListener('DOMContentLoaded', function(){
                if (window.Swal) {
                    Swal.fire({icon:'success', title:'Sucesso', text:'{{ session('success') }}'});
                } else {
                    setTimeout(function(){ if (window.Swal) Swal.fire({icon:'success', title:'Sucesso', text:'{{ session('success') }}'}); }, 300);
                }
            });
            </script>
            @endif
            <form method="POST" action="/frota/licenciamento" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Veículo</label>
                        <select id="veiculo_id" name="veiculo_id" class="form-control modern-input" required></select>
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Ano exercício</label>
                        <input type="number" min="2000" max="2100" class="form-control modern-input" name="ano_exercicio" value="{{ date('Y') }}" required>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Data do pagamento</label>
                        <input type="date" class="form-control modern-input" name="data_pagamento">
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Valor pago</label>
                        <input type="text" class="form-control modern-input" name="valor" id="valor" placeholder="0,00">
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Comprovante (PDF/Imagem)</label>
                        <input type="file" class="form-control modern-input" name="comprovante" accept="image/*,application/pdf">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label class="font-weight-bold text-muted mb-2">Observações</label>
                        <textarea class="form-control modern-input" rows="1" name="observacoes"></textarea>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Placa:</strong> <span id="s_placa">-</span></p>
                                    <p class="mb-1"><strong>Modelo:</strong> <span id="s_modelo">-</span></p>
                                    <p class="mb-1"><strong>Último pagamento:</strong> <span id="s_ultimo">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Pago no ano atual:</strong> <span id="s_pago_ano" class="badge badge-secondary">-</span></p>
                                    <p class="mb-1"><strong>Próximo pagamento:</strong> <span id="s_proximo">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
.modern-input { border-radius: 12px; border: 2px solid #e2e8f0; padding: 10px 14px; font-size: 14px; background: #f8fafc; height: auto; width: 100%; box-sizing: border-box; }
.modern-input[type="file"], input[type="file"].modern-input { padding: 6px 10px; height: auto; }
select.modern-input { padding: 8px 12px; min-height: 42px; height: auto; }
.modern-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); background: #fff; outline: none; }
.modern-card { background: #fff; border-radius: 12px; border: 2px solid #f1f5f9; }
.os-page * { transition: none !important; }
.os-page a:hover { text-decoration: none !important; color: inherit !important; }
.os-page .btn:hover, .os-page .btn:focus, .os-page .btn:active { box-shadow: none !important; filter: none !important; transform: none !important; }
.os-page .modern-card:hover, .os-page .card:hover { box-shadow: none !important; transform: none !important; }
.os-page .list-group-item:hover { background-color: inherit !important; color: inherit !important; }
</style>
@stop

@section('js')
<script>
function formatBRLInput(el){
  el.addEventListener('input', () => {
    const onlyDigits = el.value.replace(/[^\d]/g, '');
    const asNumber = (parseInt(onlyDigits || '0', 10) / 100).toFixed(2);
    const br = asNumber.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    el.value = 'R$ ' + br;
  });
}

function setStatusUI(data){
  const veic = data?.veiculo || {};
  document.getElementById('s_placa').textContent = veic.placa || '-';
  document.getElementById('s_modelo').textContent = [veic.marca, veic.modelo].filter(Boolean).join(' ') || '-';
  const ultimoTxt = data?.ultimo ? `${data.ultimo.data_pagamento ? new Date(data.ultimo.data_pagamento).toLocaleDateString('pt-BR') : '—'} (${data.ultimo.ano_exercicio || '-'})` : '—';
  document.getElementById('s_ultimo').textContent = ultimoTxt;
  const badge = document.getElementById('s_pago_ano');
  const pago = !!data?.pago_este_ano;
  badge.textContent = pago ? 'Sim' : 'Não';
  badge.className = 'badge ' + (pago ? 'badge-success' : 'badge-danger');
  document.getElementById('s_proximo').textContent = data?.proximo_pagamento ? new Date(data.proximo_pagamento).toLocaleDateString('pt-BR') : '—';
}

document.addEventListener('DOMContentLoaded', async function(){
  const select = document.getElementById('veiculo_id');
  const valorEl = document.getElementById('valor');
  formatBRLInput(valorEl);

  // UX: bloquear duplo submit e mostrar progresso
  const form = document.querySelector('form[action="/frota/licenciamento"]');
  if (form) {
    form.addEventListener('submit', function(ev){
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';
      }
    });
  }

  select.innerHTML = '<option value="">Carregando veículos...</option>';
  try {
    const resp = await fetch('/frota/api/licenciamento/veiculos');
    const j = await resp.json();
    const lista = Array.isArray(j) ? j : (Array.isArray(j?.data) ? j.data : []);
    select.innerHTML = '<option value="">Selecione...</option>' +
      lista.map(v => `<option value="${v.id}">${v.placa} - ${v.marca || ''} ${v.modelo || ''}</option>`).join('');
  } catch(e){
    select.innerHTML = '<option value="">Erro ao carregar</option>';
  }

  let statusAbort;
  async function carregarStatus(){
    const id = select.value; if(!id) { setStatusUI({}); return; }
    if (statusAbort) statusAbort.abort();
    statusAbort = new AbortController();
    try {
      const r = await fetch(`/frota/api/licenciamento/status/${id}`, { signal: statusAbort.signal });
      if (!r.ok) throw new Error('Erro HTTP '+r.status);
      const j = await r.json();
      if(!j.success) throw new Error(j.message||'Falha no status');
      setStatusUI(j);
    } catch(err){
      setStatusUI({});
      if (window.Swal) {
        Swal.fire({ icon:'error', title:'Erro', text:'Falha ao consultar status do licenciamento.' });
      }
    }
  }

  select.addEventListener('change', carregarStatus);
});
</script>
@stop


