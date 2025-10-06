@extends('adminlte::page')

@section('title', 'Roçagem - Abastecimentos')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-gas-pump text-primary mr-3"></i>
            Abastecimentos
        </h1>
        <p class="text-muted mt-1 mb-0">Registre os abastecimentos das roçadeiras</p>
    </div>
    <div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAbastecimento">
            <i class="fas fa-plus mr-1"></i>
            Novo Abastecimento
        </button>
    </div>

</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
    $__perfil = optional(auth()->user()->profile)->name ?? '';
    $__isGestorFrotas = in_array($__perfil, ['Gestão de Frotas', 'Gestao de Frotas'], true);
    $__isAdmin = isset($isAdmin) ? $isAdmin : ($__perfil === 'Admin' || $__isGestorFrotas || (auth()->user() && method_exists(auth()->user(),'temPermissao') && (auth()->user()->temPermissao('Gestão de Frotas') || auth()->user()->temPermissao('Gestao de Frotas'))));
@endphp
<div class="container-fluid">
    <!-- Filtro por Mês -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="filtroMesRoc" class="font-weight-bold mb-1">Filtrar por mês</label>
            <div class="d-flex">
                <input type="month" id="filtroMesRoc" class="form-control mr-2">
                <button type="button" id="btnLimparMesRoc" class="btn btn-outline-secondary">Limpar</button>
            </div>
        </div>
    </div>
    <!-- Cards de estatísticas -->
    @if($__isAdmin)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-primary">
                <div class="card-body text-center">
                    <i class="fas fa-gas-pump fa-2x mb-2"></i>
                    <h3 id="totalAbastecimentos">0</h3>
                    <p class="mb-0">Total do Mês</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-success">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h3 id="custoTotal">R$ 0,00</h3>
                    <p class="mb-0">Custo Total</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-info">
                <div class="card-body text-center">
                    <i class="fas fa-tint fa-2x mb-2"></i>
                    <h3 id="totalLitros">0L</h3>
                    <p class="mb-0">Total de Litros</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card card-warning">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x mb-2"></i>
                    <h3 id="precoMedio">R$ 0,00</h3>
                    <p class="mb-0">Preço Médio/L</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Abastecimentos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Histórico de Abastecimentos
            </h5>
        </div>
        <div class="card-body">
            <!-- Desktop/Tablet -->
            <div id="abastDesktop" class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-striped" id="tabelaAbastecimentos">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Local da Roçagem</th>
                                <th>Litros</th>
                                <th>Valor Total</th>
                                <th>Preço/L</th>
                                <th>Posto</th>
                                <th>Usuário</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile: lista simplificada -->
            <div id="abastMobile" class="d-block d-md-none">
                <div id="listaAbastecimentosMobile" class="list-group"></div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Cadastrar Abastecimento -->
<div class="modal fade" id="modalAbastecimento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-gas-pump mr-2"></i>
                    Novo Abastecimento
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formAbastecimento">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="local_id">Local da Roçagem <span class="text-danger">*</span></label>
                                <select id="local_id" class="form-control" required>
                                    <option value="">Selecione o local...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data">Data <span class="text-danger">*</span></label>
                                <input id="data" type="date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="litros">Litros <span class="text-danger">*</span></label>
                                <input id="litros" type="text" inputmode="decimal" class="form-control" placeholder="Ex: 45.50" required>
                                <small class="text-muted">Use ponto como separador decimal.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valor">Valor Total <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input id="valor" type="text" inputmode="numeric" class="form-control text-right" placeholder="Ex: 250,75" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="posto">Posto</label>
                                <input id="posto" type="text" class="form-control" placeholder="Ex: Posto Shell - Centro" value="Auto Posto Estrela D'alva">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="abastecimento_id">
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
}

.btn-sm {
    margin: 0 2px;
}

/* Mobile cards style for list items */
@media (max-width: 767.98px) {
  #abastMobile .list-group-item { border-radius: 10px; margin-bottom: .75rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
  #abastMobile .list-group-item .meta { font-size: .85rem; color: #6c757d; }
  #abastMobile .list-group-item .price { font-weight: 600; }
}
</style>
@stop

@section('js')
<script>
// Expor flag de permissão para o JS
const IS_ADMIN = @json($__isAdmin ?? false);
var EQUIP_MAP = {};

$(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    carregarLocais();
    // Setup filtro mês (padrão mês atual)
    (function(){ const d=new Date(); const p=n=>String(n).padStart(2,'0'); $('#filtroMesRoc').val(`${d.getFullYear()}-${p(d.getMonth()+1)}`); })();
    $('#filtroMesRoc').on('change', carregarLista);
    $('#btnLimparMesRoc').on('click', function(){ $('#filtroMesRoc').val(''); carregarLista(); });
    carregarLista();

    $('#formAbastecimento').on('submit', function(e){
        e.preventDefault();
        salvarAbastecimento();
    });
    // Máscara BRL
    function maskMoedaBR(val){
        let digits = String(val||'').replace(/\D/g,'');
        if (!digits) return '0,00';
        const num = parseInt(digits, 10);
        const cents = (num/100).toFixed(2);
        return Number(cents).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    $('#valor').on('input', function(){
        this.value = maskMoedaBR(this.value);
        const el = this; setTimeout(()=>{ try{ el.selectionStart = el.selectionEnd = el.value.length; }catch(e){} }, 0);
    }).on('focus click', function(){
        if (!this.value) this.value = '0,00';
        const el = this; setTimeout(()=>{ try{ el.selectionStart = el.selectionEnd = el.value.length; }catch(e){} }, 0);
    });
    // Litros
    $('#litros').on('input', function(){
        let v = String(this.value || '');
        v = v.replace(/[^0-9.]/g, '');
        const parts = v.split('.');
        if (parts.length > 2) {
            v = parts[0] + '.' + parts.slice(1).join('');
        }
        this.value = v;
    });
});

function carregarLocais(){
    $.get('/rocagem/api/locais').done(function(locais){
        const sel = $('#local_id');
        sel.find('option:not(:first)').remove();
        locais.forEach(local => {
            sel.append(`<option value="${local.id}">${local.nome}</option>`);
        });
    });
}

function carregarLista(){
    const ONLY_MINE = @json(!($__isAdmin ?? false));
    const authId = @json(auth()->id());
    const baseParams = { ts: Date.now() };

    if (ONLY_MINE) {
        fetchAbastecimentos(Object.assign({}, baseParams, { user_id: authId }))
            .then(renderizar)
            .catch(() => renderizar([]));
    } else {
        // Gestor/Admin
        // 1) tenta sem filtro (se o backend permitir, já retorna tudo)
        fetchAbastecimentos(Object.assign({}, baseParams)).then(function(rows){
            const setUsers = new Set((rows||[]).map(r => String(r.user_id)));
            if (rows && rows.length && (setUsers.size > 1 || (setUsers.size === 1 && !setUsers.has(String(authId))))) {
                renderizar(rows);
            } else {
                // 2) fallback: varrer um range de IDs para agregar (evita depender de /api/usuarios)
                const MAX_SCAN_USERS = 60; // ajuste se necessário
                const reqs = [];
                for (let uid = 1; uid <= MAX_SCAN_USERS; uid++) {
                    reqs.push(fetchAbastecimentos(Object.assign({}, baseParams, { user_id: uid })));
                }
                Promise.all(reqs).then(results => {
                    const merged = [].concat.apply([], results);
                    renderizar(merged);
                }).catch(() => renderizar(rows||[]));
            }
        }).catch(() => renderizar([]));
    }

    function fetchAbastecimentos(params){
        return new Promise(function(resolve){
            $.get('/rocagem/api/abastecimentos', params)
                .done(rows => resolve(rows||[]))
                .fail(function(){
                    $.get('/api/rocagem/abastecimentos', params)
                        .done(rows => resolve(rows||[]))
                        .fail(()=> resolve([]));
                });
        });
    }

    function renderizar(rows){
        // Garantir fidelidade ao banco: remover duplicados por ID
        rows = Array.isArray(rows) ? rows : [];
        if (rows.length) {
            const byId = new Map();
            rows.forEach(r => { if (r && r.id != null) byId.set(String(r.id), r); });
            rows = Array.from(byId.values());
        }
        const tbody = $('#tabelaAbastecimentos tbody');
        const listaMobile = $('#listaAbastecimentosMobile');
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;

        tbody.empty();
        listaMobile.empty();

        // Se não for Admin/Gestão de Frotas, manter apenas registros do usuário logado (segurança extra)
        if (ONLY_MINE) rows = (rows||[]).filter(r => String(r.user_id) === String(authId));

        // Aplicar filtro de mês se selecionado (com fallback para o mês mais recente que tenha dados)
        let mesSel = $('#filtroMesRoc').val();
        const aplicarFiltroMes = (m) => {
            const [ano, mes] = m.split('-');
            const ultimoDia = new Date(Number(ano), Number(mes), 0).getDate();
            const ini = `${ano}-${mes}-01`;
            const fim = `${ano}-${mes}-${String(ultimoDia).padStart(2,'0')}`;
            return (rows||[]).filter(r => String(r.data) >= ini && String(r.data) <= fim);
        };
        if (mesSel) {
            let filtradosMes = aplicarFiltroMes(mesSel);
            if (filtradosMes.length === 0 && (rows||[]).length) {
                // escolher o mês mais recente com dados e aplicar automaticamente
                const maisRecente = (rows||[]).map(r => String(r.data).slice(0,7)).sort().reverse()[0];
                if (maisRecente) {
                    $('#filtroMesRoc').val(maisRecente);
                    mesSel = maisRecente;
                    filtradosMes = aplicarFiltroMes(mesSel);
                }
            }
            rows = filtradosMes;
        }

        let total = 0, litros = 0;
        if (!rows.length){
            if (isMobile) {
                listaMobile.append('<div class="list-group-item text-center text-muted">Nenhum abastecimento encontrado</div>');
            } else {
                tbody.append('<tr><td colspan="8" class="text-center text-muted">Nenhum abastecimento encontrado</td></tr>');
            }
        }

        // Agrupar por dia (como na Frota)
        const sorted = (rows||[]).slice().sort((a,b)=> String(b.data).localeCompare(String(a.data)));
        const dias = Array.from(new Set(sorted.map(r=>r.data)));
        dias.forEach(diaISO => {
            const header = new Date(diaISO + 'T00:00:00').toLocaleDateString('pt-BR');
            const doDia = sorted.filter(r => r.data === diaISO);
            if (!isMobile) {
                tbody.append(`<tr class="table-active"><td colspan="8" class="font-weight-bold">${header}</td></tr>`);
            } else {
                listaMobile.append(`<div class=\"list-group-item bg-light font-weight-bold\">${header}</div>`);
            }
            doDia.forEach(r => {
                const data = new Date(r.data + 'T00:00:00').toLocaleDateString('pt-BR');
                const preco = r.preco_litro ? Number(r.preco_litro).toFixed(3) : (Number(r.valor)/Number(r.litros)).toFixed(3);
                total += Number(r.valor||0);
                litros += Number(r.litros||0);
                if (!isMobile) {
                    tbody.append(`
                        <tr>
                            <td>${data}</td>
                            <td><strong>${r.local_rocagem||'-'}</strong></td>
                            <td>${Number(r.litros||0)}L</td>
                            <td>R$ ${Number(r.valor||0).toFixed(2)}</td>
                            <td>R$ ${preco}</td>
                            <td>${r.posto||''}</td>
                            <td>${(r.usuario && r.usuario.name) ? r.usuario.name : (r.user_name||r.user_id||'')}</td>
                            <td class="text-center">${IS_ADMIN ? `<button class=\"btn btn-sm btn-info\" onclick=\"editar(${r.id})\"><i class=\"fas fa-edit\"></i></button>
                                <button class=\"btn btn-sm btn-danger\" onclick=\"excluirReg(${r.id})\"><i class=\"fas fa-trash\"></i></button>` : ''}
                            </td>
                        </tr>
                    `);
                } else {
                    listaMobile.append(`
                        <div class=\"list-group-item\">\n                            <div class=\"d-flex justify-content-between align-items-center\">\n                                <div>\n                                    <div class=\"font-weight-bold\">${r.local_rocagem||'-'}</div>\n                                    <div class=\"meta\">${data}</div>\n                                </div>\n                                <div><span class=\"badge badge-info\">${Number(r.litros||0)}L</span></div>\n                            </div>\n                            <div class=\"d-flex justify-content-between mt-2\">\n                                <div class=\"meta\">Preço/L</div><div class=\"price\">R$ ${preco}</div>\n                                <div class=\"meta\">Total</div><div class=\"price\">R$ ${Number(r.valor||0).toFixed(2)}</div>\n                            </div>\n                            ${ IS_ADMIN ? `<div class=\"mt-2\"><div class=\"btn-group btn-group-sm w-100\"><button class=\"btn btn-info\" onclick=\"editar(${r.id})\"><i class=\"fas fa-edit\"></i></button><button class=\"btn btn-danger\" onclick=\"excluirReg(${r.id})\"><i class=\"fas fa-trash\"></i></button></div></div>` : ''}\n                        </div>
                    `);
                }
            });
        });

        // cards (somente o conjunto filtrado)
        $('#totalAbastecimentos').text(rows.length);
        const custoTotalFmt = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const litrosFmt = Number(litros).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#custoTotal').text('R$ ' + custoTotalFmt);
        $('#totalLitros').text(litrosFmt + 'L');
        const precoMedio = rows.length && litros > 0 ? (total / litros) : 0;
        $('#precoMedio').text('R$ ' + precoMedio.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }
}

function salvarAbastecimento(){
    const id = $('#abastecimento_id').val();
    const dados = {
        local_id: $('#local_id').val(),
        data: $('#data').val(),
        litros: $('#litros').val(),
        valor: (function(){
            const v = $('#valor').val();
            const norm = String(v||'').replace(/\./g,'').replace(/,/g,'.');
            return norm;
        })(),
        posto: $('#posto').val()
    };
    const url = id ? `/rocagem/api/abastecimentos/${id}` : '/rocagem/api/abastecimentos';
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: dados })
        .done(() => { 
            $('#modalAbastecimento').modal('hide'); 
            $('#formAbastecimento')[0].reset();
            $('#abastecimento_id').val('');
            carregarLista(); 
            Swal.fire({ icon: 'success', title: 'Salvo!', timer: 1400, showConfirmButton: false });
        })
        .fail(xhr => { 
            const msg = xhr.responseJSON?.message || 'Erro ao salvar';
            Swal.fire('Erro', msg, 'error'); 
        });
}

function editar(id){
    $.get('/rocagem/api/abastecimentos').done(function(rows){
        const r = rows.find(x => x.id == id);
        if(!r) return;
        $('#abastecimento_id').val(r.id);
        $('#local_id').val(r.local_id);
        $('#data').val(r.data);
        $('#litros').val(r.litros);
        // Formatar valor com máscara BRL
        const valorFormatado = Number(r.valor||0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#valor').val(valorFormatado);
        $('#posto').val(r.posto);
        $('#modalAbastecimento').modal('show');
    });
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
        $.ajax({ url: `/rocagem/api/abastecimentos/${id}`, method: 'DELETE' })
            .done(() => { carregarLista(); Swal.fire({ icon:'success', title:'Excluído!', timer:1200, showConfirmButton:false }); })
            .fail(() => Swal.fire('Erro', 'Erro ao excluir', 'error'));
    });
}
</script>
@stop


