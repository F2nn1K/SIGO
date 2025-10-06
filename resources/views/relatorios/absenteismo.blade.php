@extends('adminlte::page')

@section('title', 'Relatório Absentismo')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-user-times text-primary mr-3"></i>
            Relatório Absentismo
        </h1>
        <p class="text-muted mt-1 mb-0">Controle de atestados e faltas do funcionário</p>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Card de Filtros -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter mr-2"></i>
                Filtros de Pesquisa
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Data inicial
                    </label>
                    <input type="date" id="dt_ini" class="form-control" />
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Data final
                    </label>
                    <input type="date" id="dt_fim" class="form-control" />
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">
                        <i class="fas fa-building mr-1"></i>
                        Centro de Custo
                    </label>
                    <select id="cc_id" class="form-control">
                        <option value="">Todos os centros</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="font-weight-bold text-dark">
                        <i class="fas fa-user mr-1"></i>
                        Funcionário
                    </label>
                    <div class="position-relative">
                        <input type="text" id="func_q" class="form-control" placeholder="Digite 3 letras..." autocomplete="off" />
                        <input type="hidden" id="func_id" />
                        <div id="func_sug" class="dropdown-menu w-100" style="display:none; max-height:240px; overflow:auto; z-index:1050;"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <button id="btnBuscar" class="btn btn-primary btn-block">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button id="btnLimpar" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-eraser mr-2"></i>Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de Resultado -->
    <div class="card card-success card-outline">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-table mr-2"></i>
                Resultado - Absentismo
            </h3>
            <div class="card-tools">
                <span class="badge badge-light" id="contador">0 registros</span>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped" id="tabela_abs">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 25%;">Funcionário</th>
                        <th style="width: 15%;">Data Atestado</th>
                        <th style="width: 15%;">Dias de Afastamento</th>
                        <th style="width: 20%;">Tipo</th>
                        <th style="width: 15%;">Atestado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-search fa-2x text-muted mb-2"></i>
                            <br>Execute uma busca para ver os resultados
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
document.addEventListener('DOMContentLoaded', function(){
    const hoje = new Date().toISOString().slice(0,10);
    document.getElementById('dt_ini').value = hoje;
    document.getElementById('dt_fim').value = hoje;
    document.getElementById('btnBuscar').addEventListener('click', buscar);
    document.getElementById('btnLimpar').addEventListener('click', limparFiltros);
    carregarCentrosCusto();
    wireFuncionarioAutocomplete();
});

function limparFiltros(){
    document.getElementById('dt_ini').value = '';
    document.getElementById('dt_fim').value = '';
    document.getElementById('cc_id').value = '';
    document.getElementById('func_q').value = '';
    document.getElementById('func_id').value = '';
    document.getElementById('func_sug').style.display = 'none';
    const tbody = document.querySelector('#tabela_abs tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-search fa-2x text-muted mb-2"></i><br>Execute uma busca para ver os resultados</td></tr>';
    document.getElementById('contador').textContent = '0 registros';
}

async function buscar(){
    const di = document.getElementById('dt_ini').value;
    const df = document.getElementById('dt_fim').value;
    const cc = document.getElementById('cc_id').value;
    const q = document.getElementById('func_q').value.trim();
    const tbody = document.querySelector('#tabela_abs tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin text-primary"></i> Carregando...</td></tr>';
    const params = new URLSearchParams();
    if (di) params.set('data_ini', di);
    if (df) params.set('data_fim', df);
    if (cc) params.set('centro_custo_id', cc);
    const fid = document.getElementById('func_id').value;
    if (fid) params.set('func_id', fid);
    else if (q) params.set('q', q);
    try {
        const res = await fetch('/api/relatorios/absenteismo?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        const j = await res.json();
        render(j.data||[]);
    } catch(e){
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Erro ao carregar</td></tr>';
    }
}

function render(rows){
    const tbody = document.querySelector('#tabela_abs tbody');
    const contador = document.getElementById('contador');
    
    if (!rows.length){
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x text-muted mb-2"></i><br>Nenhum registro encontrado</td></tr>';
        contador.textContent = '0 registros';
        return;
    }
    
    // Agrupar por funcionário (prioriza funcionario_id; fallback para nome)
    const gruposMap = rows.reduce((acc, r) => {
        const key = (r.funcionario_id != null ? String(r.funcionario_id) : (r.funcionario_nome || '0')).trim();
        if (!acc[key]) {
            acc[key] = { nome: r.funcionario_nome || '—', itens: [] };
        }
        acc[key].itens.push(r);
        return acc;
    }, {});

    const grupos = Object.values(gruposMap);
    // Ordenar itens de cada grupo por data
    grupos.forEach(g => {
        g.itens.sort((a, b) => String(a.data_atestado||'').localeCompare(String(b.data_atestado||'')));
    });

    const totalFuncs = grupos.length;
    const totalAtest = rows.length;
    contador.textContent = `${totalFuncs} funcionário${totalFuncs>1?'s':''}, ${totalAtest} atestado${totalAtest>1?'s':''}`;

    tbody.innerHTML = grupos.map((g, gi) => {
        const headerRow = `
            <tr class="table-active">
                <td class="text-center font-weight-bold">${gi+1}</td>
                <td colspan="5">
                    <i class="fas fa-user text-primary mr-2"></i>
                    <strong>${escapeHtml(g.nome)}</strong>
                    <span class="badge badge-success ml-2">${g.itens.length} atestado${g.itens.length>1?'s':''}</span>
                </td>
            </tr>
        `;
        const itemRows = g.itens.map((r, idx) => `
            <tr>
                <td class="text-center text-muted">${idx+1}</td>
                <td></td>
                <td>
                    <span class="badge badge-info">
                        <i class="fas fa-calendar mr-1"></i>
                        ${r.data_atestado ? new Date(r.data_atestado+'T00:00:00').toLocaleDateString('pt-BR') : '—'}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge badge-warning">
                        ${Number(r.dias_afastamento||0)} dia${Number(r.dias_afastamento||0) !== 1 ? 's' : ''}
                    </span>
                </td>
                <td>
                    <span class="badge badge-secondary">
                        ${escapeHtml(r.tipo_atestado || '—')}
                    </span>
                </td>
                <td class="text-center">
                    <a class="btn btn-sm btn-outline-primary" href="/relatorios/absenteismo/atestado/${r.id}" target="_blank" title="Visualizar atestado">
                        <i class="fas fa-file-medical"></i> Ver
                    </a>
                </td>
            </tr>
        `).join('');
        return headerRow + itemRows;
    }).join('');
}

function escapeHtml(s){
    const d = document.createElement('div');
    d.innerText = s == null ? '' : String(s);
    return d.innerHTML;
}

function carregarCentrosCusto(){
    fetch('/api/centros-custo', { headers: { 'X-Requested-With': 'XMLHttpRequest' }}).
        then(r => r.json()).
        then(resp => {
            const sel = document.getElementById('cc_id');
            if (!resp || !resp.success) return;
            (resp.data||[]).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id; opt.textContent = c.nome;
                sel.appendChild(opt);
            });
        }).
        catch(()=>{});
}

function wireFuncionarioAutocomplete(){
    const input = document.getElementById('func_q');
    const hidden = document.getElementById('func_id');
    const sug = document.getElementById('func_sug');
    function hide(){ sug.style.display='none'; }
    function show(){ sug.style.display='block'; }
    input.addEventListener('input', async function(){
        const termo = input.value.trim();
        hidden.value = '';
        if (termo.length < 3){ hide(); return; }
        try{
            const r = await fetch('/api/funcionarios/buscar', { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')}, body: JSON.stringify({ termo }) });
            const lista = await r.json();
            sug.innerHTML = '';
            (lista||[]).forEach(f => {
                const a = document.createElement('a');
                a.href = '#'; a.className='dropdown-item';
                a.textContent = `${f.nome} (${f.cpf||''})`;
                a.addEventListener('click', function(ev){ ev.preventDefault(); input.value = f.nome; hidden.value = f.id; hide(); });
                sug.appendChild(a);
            });
            if (sug.children.length>0) show(); else hide();
        }catch(e){ hide(); }
    });
    document.addEventListener('click', function(e){ if (!sug.contains(e.target) && e.target!==input) hide(); });
}
</script>
@stop


