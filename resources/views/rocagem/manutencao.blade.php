@extends('adminlte::page')

@section('title', 'Roçagem - Manutenções')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-tools text-primary mr-3"></i>
            Manutenções de Roçagem
        </h1>
        <p class="text-muted mt-1 mb-0">Gerencie as manutenções das roçadeiras</p>
    </div>
    <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalManutencao">
            <i class="fas fa-plus mr-1"></i>
            Nova Manutenção
        </button>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    @php($__isAdmin = optional(auth()->user()->profile)->name === 'Admin')
    <!-- Cards de estatísticas -->
    @if($__isAdmin)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-primary">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h3 id="man_total_mes">0</h3>
                    <p class="mb-0">Total do Mês</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 id="man_custo_total">R$ 0,00</h3>
                    <p class="mb-0">Custo Total</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3 id="man_pendentes">0</h3>
                    <p class="mb-0">Pendentes</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-danger">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3 id="man_vencidas">0</h3>
                    <p class="mb-0">Vencidas</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Manutenções -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Histórico de Manutenções
            </h5>
        </div>
        <div class="card-body">
            <!-- Desktop/Tablet -->
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-striped" id="tabelaManutencoes">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Equipamento</th>
                                <th>Responsável</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Horas</th>
                                <th>Custo</th>
                                <th>Próxima</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile: lista simplificada -->
            <div class="d-block d-md-none">
                <div id="listaManutencoesMobile" class="list-group"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Manutenção -->
<div class="modal fade" id="modalManutencao" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tools mr-2"></i>
                    Nova Manutenção
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formManutencao">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Equipamento</label>
                                <select id="equip_id" class="form-control">
                                    <option value="">Selecione o equipamento...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Data <span class="text-danger">*</span></label>
                                <input id="data" type="date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select id="tipo" class="form-control" required>
                                    <option value="">Selecione...</option>
                                    <option value="preventiva">Preventiva</option>
                                    <option value="corretiva">Corretiva</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Horas de Uso <span class="text-danger">*</span></label>
                                <input id="horas" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 1.250" required>
                                <small id="horasAtualHint" class="text-muted d-block"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Custo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input id="custo" type="text" inputmode="decimal" class="form-control" placeholder="Ex: 350,00">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descrição <span class="text-danger">*</span></label>
                                <textarea id="descricao" class="form-control" rows="3" placeholder="Descreva o serviço realizado" maxlength="200" required></textarea>
                                <small class="text-muted d-block text-right"><span id="descricaoCount">0</span>/200</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Oficina/Prestador</label>
                                <input id="oficina" type="text" class="form-control" placeholder="Ex: Oficina João - Centro">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Próxima Manutenção (Horas)</label>
                                <input id="proxima_horas" type="text" inputmode="numeric" class="form-control" placeholder="Ex: 1.500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="manutencao_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}

.btn-sm {
    margin: 0 2px;
}

.table th:nth-child(1), .table td:nth-child(1) { width: 100px; }
.table th:nth-child(2), .table td:nth-child(2) { width: 140px; }
.table th:nth-child(3), .table td:nth-child(3) { width: 120px; }
.table th:nth-child(4), .table td:nth-child(4) { width: 110px; }
.table th:nth-child(5), .table td:nth-child(5) { min-width: 200px; }
.table th:nth-child(6), .table td:nth-child(6) { width: 110px; text-align: right; white-space: nowrap; }
.table th:nth-child(7), .table td:nth-child(7) { width: 120px; text-align: right; white-space: nowrap; }
.table th:nth-child(8), .table td:nth-child(8) { width: 130px; white-space: nowrap; }
.table th:nth-child(9), .table td:nth-child(9) { width: 120px; text-align: center; white-space: nowrap; }

@media (max-width: 767.98px) {
  .list-group-item { border-radius: 10px; margin-bottom: .75rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
}
</style>
@stop

@section('js')
<script>
var IS_ADMIN = @json(optional(auth()->user()->profile)->name === 'Admin');
var EQUIP_MAP = {};

$(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    carregarEquipamentosNoSelect().always(function(){
        carregarLista();
    });

    $('#formManutencao').on('submit', function(e){
        e.preventDefault();
        salvarManutencao();
    });

    $('#custo').on('input', function(){
        $(this).val(formatarMoedaDigitacao($(this).val()));
    });

    $('#horas, #proxima_horas').on('input', function(){
        this.value = formatHorasBR(this.value);
    });

    $('#descricao').on('input', function(){
        var len = $(this).val().length;
        if (len > 200) {
            $(this).val($(this).val().substring(0, 200));
            len = 200;
        }
        $('#descricaoCount').text(len);
    }).trigger('input');

    $('#modalManutencao').on('hidden.bs.modal', function(){
        setModalReadOnly(false);
        $('#formManutencao')[0].reset();
        $('#manutencao_id').val('');
    });
});

function formatarMoedaDigitacao(valor){
    const digits = String(valor).replace(/\D/g, '');
    const inteiro = digits.length > 2 ? digits.slice(0, -2) : '0';
    const centavos = digits.padStart(3, '0').slice(-2);
    const inteiroFormatado = Number(inteiro).toLocaleString('pt-BR');
    return `${inteiroFormatado},${centavos}`;
}

function formatHorasBR(value){
    const digitsOnly = String(value || '').replace(/\D/g, '');
    if (!digitsOnly) return '';
    return Number(digitsOnly).toLocaleString('pt-BR');
}

function formatarNumeroBR(valor){
    return Number(valor||0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function carregarEquipamentosNoSelect(){
    return $.get('/rocagem/api/equipamentos', { only_usable: 1 }).done(function(eqps){
        const sel = $('#equip_id');
        sel.find('option:not(:first)').remove();
        EQUIP_MAP = {};
        (eqps||[]).forEach(v => {
            const label = `${v.codigo||v.id} - ${v.nome||''}`;
            EQUIP_MAP[Number(v.id)] = label;
            sel.append(`<option value="${v.id}" data-horas="${v.horas_uso||0}">${label}</option>`);
        });
        sel.on('change', function(){
            const horas = $(this).find('option:selected').data('horas');
            $('#horasAtualHint').text(horas ? `Horas atuais: ${Number(horas).toLocaleString()}h` : '');
        }).trigger('change');
    });
}

var EQUIP_MAP_ALL = {};
function carregarEquipamentosParaDisplay(){
    return $.get('/rocagem/api/equipamentos', { ts: Date.now() }).done(function(eqps){
        EQUIP_MAP_ALL = {};
        (eqps||[]).forEach(v => {
            const label = `${v.codigo||v.id} - ${v.nome||''}`;
            EQUIP_MAP_ALL[Number(v.id)] = label;
        });
        atualizarRotulosEquipamentos();
    });
}

function atualizarRotulosEquipamentos(){
    $('.celula-equip').each(function(){
        const id = Number($(this).data('equip-id'));
        const label = EQUIP_MAP_ALL[id] || EQUIP_MAP[id] || id;
        if ($(this).is('td')) {
            const strong = $(this).find('strong');
            if (strong.length) {
                strong.text(label);
            } else {
                $(this).text(label);
            }
        } else {
            $(this).text(label);
        }
    });
}

function carregarLista(){
    carregarEquipamentosParaDisplay();
    $.get('/rocagem/api/manutencoes')
    .done(function(resp){
        const rows = Array.isArray(resp) ? resp : (Array.isArray(resp?.data) ? resp.data : []);
        const tbody = $('#tabelaManutencoes tbody');
        const listaMobile = $('#listaManutencoesMobile');
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        tbody.find('tr').remove();
        listaMobile.empty();

        let total = 0; let pend = 0; let venc = 0; let doMes = 0;
        const hoje = new Date();

        if (!rows.length){
            if (isMobile) {
                listaMobile.append('<div class="list-group-item text-center text-muted">Nenhuma manutenção encontrada</div>');
            } else {
                tbody.append('<tr><td colspan="9" class="text-center text-muted">Nenhuma manutenção encontrada</td></tr>');
            }
        }

        rows.forEach(r => {
            const data = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR');
            const tipoBadge = r.tipo === 'preventiva'
                ? '<span class="badge badge-success">Preventiva</span>'
                : '<span class="badge badge-warning">Corretiva</span>';
            const proxPartes = [];
            if (r.proxima_data) proxPartes.push(new Date(r.proxima_data + 'T00:00:00').toLocaleDateString('pt-BR'));
            if (r.proxima_horas) proxPartes.push(Number(r.proxima_horas).toLocaleString() + 'h');
            const prox = proxPartes.join('<br>');

            total += Number(r.custo||0);
            if (r.status === 'agendada' || r.status === 'em_andamento') pend++;
            if (r.proxima_data && new Date(String(r.proxima_data)+ 'T00:00:00') < hoje) venc++;
            if (r.data) {
                const dLocal = new Date(String(r.data) + 'T00:00:00');
                if (dLocal.getMonth() === hoje.getMonth() && dLocal.getFullYear() === hoje.getFullYear()) doMes++;
            }

            const acoesDesktop = IS_ADMIN
                ? `<button class="btn btn-sm btn-info" onclick="editar(${r.id})"><i class="fas fa-edit"></i></button>
                   <button class="btn btn-sm btn-danger" onclick="excluirReg(${r.id})"><i class="fas fa-trash"></i></button>`
                : `<button class="btn btn-sm btn-primary" onclick="ver(${r.id})"><i class="fas fa-eye"></i></button>`;

            if (!isMobile) {
                const labelEquip = EQUIP_MAP_ALL[Number(r.equip_id)] || EQUIP_MAP[Number(r.equip_id)] || r.equip_id;
                tbody.append(`
                    <tr>
                        <td>${data}</td>
                        <td class="celula-equip" data-equip-id="${r.equip_id}"><strong>${labelEquip}</strong></td>
                        <td>${r.user_name || '-'}</td>
                        <td>${tipoBadge}</td>
                        <td>${r.descricao||''}</td>
                        <td>${Number(r.horas||0).toLocaleString()}h</td>
                        <td>R$ ${formatarNumeroBR(Number(r.custo||0))}</td>
                        <td>${prox || '-'}</td>
                        <td class="text-center">${acoesDesktop}</td>
                    </tr>
                `);
            } else {
                const acaoMobile = IS_ADMIN
                    ? `<div class="btn-group btn-group-sm w-100">
                            <button class="btn btn-info" onclick="editar(${r.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger" onclick="excluirReg(${r.id})"><i class="fas fa-trash"></i></button>
                       </div>`
                    : `<button class="btn btn-primary btn-block" onclick="ver(${r.id})"><i class="fas fa-eye mr-1"></i> Ver</button>`;

                listaMobile.append(`
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="font-weight-bold celula-equip" data-equip-id="${r.equip_id}">${EQUIP_MAP_ALL[Number(r.equip_id)] || EQUIP_MAP[Number(r.equip_id)] || r.equip_id}</div>
                                <small class="text-muted">${data} • ${Number(r.horas||0).toLocaleString()}h</small>
                            </div>
                            <div class="text-right">
                                <div>${tipoBadge}</div>
                                <small class="text-muted">${r.user_name ? r.user_name + ' • ' : ''}R$ ${formatarNumeroBR(Number(r.custo||0))}</small>
                            </div>
                        </div>
                        ${prox ? `<div class="mt-1"><small class="text-muted">Próx.: ${prox}</small></div>` : ''}
                        <div class="mt-2">${acaoMobile}</div>
                    </div>
                `);
            }
        });

        atualizarRotulosEquipamentos();

        $('#man_total_mes').text(doMes);
        $('#man_custo_total').text('R$ ' + formatarNumeroBR(total));
        $('#man_pendentes').text(pend);
        $('#man_vencidas').text(venc);
    })
    .fail(function(xhr){
        const tbody = $('#tabelaManutencoes tbody');
        const listaMobile = $('#listaManutencoesMobile');
        tbody.find('tr').remove();
        listaMobile.empty();
        const msg = xhr.status + ' ' + (xhr.responseJSON?.message || 'Falha ao carregar manutenções');
        if (window.matchMedia('(max-width: 767.98px)').matches) {
            listaMobile.append(`<div class="list-group-item text-center text-danger">${msg}</div>`);
        } else {
            tbody.append(`<tr><td colspan="9" class="text-center text-danger">${msg}</td></tr>`);
        }
        Swal.fire('Erro', msg, 'error');
    });
}

function salvarManutencao(){
    const id = $('#manutencao_id').val();
    const dados = {
        equip_id: $('#equip_id').val(),
        data: $('#data').val(),
        tipo: $('#tipo').val(),
        descricao: $('#descricao').val(),
        horas: $('#horas').val().replace(/\D/g, ''),
        custo: Number(String($('#custo').val()).replace(/\D/g, '')) / 100,
        oficina: $('#oficina').val(),
        proxima_horas: $('#proxima_horas').val().replace(/\D/g, '')
    };
    const url = id ? `/rocagem/api/manutencoes/${id}` : '/rocagem/api/manutencoes';
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: dados })
        .done(() => { 
            $('#modalManutencao').modal('hide'); 
            carregarLista();
            Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1400, showConfirmButton: false });
        })
        .fail(xhr => { 
            const msg = xhr.responseJSON?.message || 'Erro ao salvar';
            Swal.fire('Erro', msg, 'error'); 
        });
}

function editar(id){
    $.get('/rocagem/api/manutencoes').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#manutencao_id').val(r.id);
        $('#equip_id').val(r.equip_id);
        $('#data').val(r.data);
        $('#tipo').val(r.tipo);
        $('#descricao').val(r.descricao);
        $('#horas').val(formatHorasBR(r.horas));
        $('#custo').val(formatarMoedaDigitacao(String(Math.round(Number(r.custo||0)*100))));
        $('#oficina').val(r.oficina);
        $('#proxima_horas').val(formatHorasBR(r.proxima_horas));
        $('#modalManutencao').modal('show');
    });
}

function ver(id){
    $.get('/rocagem/api/manutencoes').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#manutencao_id').val(r.id);
        $('#equip_id').val(r.equip_id).prop('disabled', true);
        $('#data').val(r.data).prop('disabled', true);
        $('#tipo').val(r.tipo).prop('disabled', true);
        $('#descricao').val(r.descricao).prop('readonly', true);
        $('#horas').val(formatHorasBR(r.horas)).prop('disabled', true);
        $('#custo').val(formatarMoedaDigitacao(String(Math.round(Number(r.custo||0)*100)))).prop('disabled', true);
        $('#oficina').val(r.oficina).prop('disabled', true);
        $('#proxima_horas').val(formatHorasBR(r.proxima_horas)).prop('disabled', true);
        setModalReadOnly(true);
        $('#modalManutencao').modal('show');
    });
}

function setModalReadOnly(readOnly){
    if (readOnly){
        $('#formManutencao button[type="submit"]').hide();
    } else {
        $('#equip_id, #data, #tipo, #horas, #custo, #oficina, #proxima_horas').prop('disabled', false);
        $('#descricao').prop('readonly', false);
        $('#formManutencao button[type="submit"]').show();
    }
}

function excluirReg(id){
    Swal.fire({
        title: 'Confirmar exclusão?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Excluir',
        cancelButtonText: 'Cancelar'
    }).then(res => {
        if(!res.isConfirmed) return;
        $.ajax({ url: `/rocagem/api/manutencoes/${id}`, method: 'DELETE' })
            .done(() => { carregarLista(); Swal.fire({ icon:'success', title:'Excluído!', timer:1200, showConfirmButton:false }); })
            .fail(() => Swal.fire('Erro', 'Erro ao excluir', 'error'));
    });
}
</script>
@stop

