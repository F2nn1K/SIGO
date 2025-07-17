@extends('adminlte::page')

@section('title', 'Documentos RH')

@section('content_header')
<div class="header-highlight"></div>
<h1 class="m-0 text-dark">Documentos RH</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alertas de sucesso ou erro -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-ban"></i> Erro!</h5>
        {{ session('error') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header bg-light">
            <h3 class="card-title">Formulário de Documentos</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('rh.documentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <!-- Campo para Nome -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                    </div>
                    
                    <!-- Espaço para Foto -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Foto 3x4</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*">
                                    <label class="custom-file-label" for="foto">Escolher arquivo</label>
                                </div>
                            </div>
                            <div class="mt-2">
                                <img id="preview-foto" src="#" alt="Preview" style="max-width: 150px; max-height: 200px; display: none;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                <h5>Documentos Necessários (somente PDF)</h5>
                
                <div class="row">
                    <!-- Documentos -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doc_fotos">02 fotos 3x4</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="doc_fotos" name="doc_fotos" accept="application/pdf">
                                    <label class="custom-file-label" for="doc_fotos">Escolher arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doc_carteira_saude">Carteira de saúde atualizada com foto 3x4</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="doc_carteira_saude" name="doc_carteira_saude" accept="application/pdf">
                                    <label class="custom-file-label" for="doc_carteira_saude">Escolher arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doc_exame">Encaminhamento para exame admissional (empresa) *</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="doc_exame" name="doc_exame" accept="application/pdf" required>
                                    <label class="custom-file-label" for="doc_exame">Escolher arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doc_antecedente">Antecedente cível e criminal *</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="doc_antecedente" name="doc_antecedente" accept="application/pdf" required>
                                    <label class="custom-file-label" for="doc_antecedente">Escolher arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Enviar Documentos</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* Destaque azul no topo */
    .header-highlight {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #4f46e5, #8b5cf6);
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.7);
        z-index: 100;
        margin-top: -1px;
    }
    
    .content-header {
        position: relative;
        padding-top: 1.5rem;
        box-shadow: 0 4px 12px -5px rgba(59, 130, 246, 0.15);
        margin-bottom: 1.5rem;
        background: linear-gradient(180deg, #f9fafb 0%, rgba(249, 250, 251, 0) 100%);
    }
</style>
@endpush

@push('js')
<script>
    // Preview da foto quando selecionada
    $(document).ready(function() {
        // Atualiza o nome do arquivo selecionado
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
        
        // Preview da foto
        $("#foto").change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-foto').attr('src', e.target.result);
                    $('#preview-foto').show();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endpush 