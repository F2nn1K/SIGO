@extends('adminlte::page')

@section('title', 'Novo Problema RH')

@section('plugins.Sweetalert2', true)
@section('plugins.TempusDominusBs4', true)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Cadastrar Novo Problema</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('rh.administrador') }}">Administrador RH</a></li>
                    <li class="breadcrumb-item active">Novo Problema</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Formulário de Cadastro</h3>
                    </div>
                    <form id="form-cadastro" action="{{ route('rh.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="descricao">Descrição do Problema</label>
                                <input type="text" class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" value="{{ old('descricao') }}" required>
                                @error('descricao')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="detalhes">Detalhes</label>
                                <textarea class="form-control @error('detalhes') is-invalid @enderror" id="detalhes" name="detalhes" rows="4">{{ old('detalhes') }}</textarea>
                                @error('detalhes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="Pendente" {{ old('status') == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                    <option value="Em andamento" {{ old('status') == 'Em andamento' ? 'selected' : '' }}>Em andamento</option>
                                    <option value="Concluído" {{ old('status') == 'Concluído' ? 'selected' : '' }}>Concluído</option>
                                    <option value="No prazo" {{ old('status') == 'No prazo' ? 'selected' : '' }}>No prazo</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('rh.administrador') }}" class="btn btn-secondary btn-cancelar">Cancelar</a>
                            <button type="submit" class="btn btn-primary float-right">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Tempus Dominus Bootstrap 4 para o DateTimePicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
@stop

@section('js')
    <!-- Moment.js (necessário para o datetimepicker) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- Tempus Dominus Bootstrap 4 para o DateTimePicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mostrar erros de validação com SweetAlert2
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Atenção!',
                    html: `@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach`,
                    confirmButtonText: 'Entendi'
                });
            @endif

            // Confirmação ao cancelar formulário
            $('.btn-cancelar').click(function(e) {
                e.preventDefault();
                
                Swal.fire({
                    icon: 'question',
                    title: 'Cancelar cadastro?',
                    text: 'Se cancelar, os dados preenchidos serão perdidos.',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, cancelar',
                    cancelButtonText: 'Não, continuar editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('rh.administrador') }}";
                    }
                });
            });

            // Validação do formulário antes de enviar
            $('#form-cadastro').submit(function(e) {
                const descricao = $('#descricao').val().trim();
                
                if (descricao === '') {
                    e.preventDefault();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Campo obrigatório',
                        text: 'Por favor, informe a descrição do problema.',
                        confirmButtonText: 'Entendi'
                    });
                    
                    $('#descricao').focus();
                    return false;
                }
                
                return true;
            });
        });
    </script>
@stop 