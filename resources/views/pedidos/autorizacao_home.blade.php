@extends('adminlte::page')

@section('title', 'Autorizações de Compras')

@section('content_header')
<h1 class="m-0 text-dark font-weight-bold"><i class="fas fa-gavel text-primary mr-2"></i>Autorizações</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning shadow-sm box-hover">
                <div class="inner">
                    <h3 id="count-pendentes" class="mb-0">0</h3>
                    <p class="mb-0">Pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="{{ route('pedidos.autorizacao.pendentes') }}" class="small-box-footer">
                    Ver pendentes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success shadow-sm box-hover">
                <div class="inner">
                    <h3 id="count-aprovadas" class="mb-0">0</h3>
                    <p class="mb-0">Aprovadas</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
                <a href="{{ route('pedidos.autorizacao.aprovadas') }}" class="small-box-footer">
                    Ver aprovadas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-danger shadow-sm box-hover">
                <div class="inner">
                    <h3 id="count-rejeitadas" class="mb-0">0</h3>
                    <p class="mb-0">Rejeitadas</p>
                </div>
                <div class="icon"><i class="fas fa-times"></i></div>
                <a href="{{ route('pedidos.autorizacao.rejeitadas') }}" class="small-box-footer">
                    Ver rejeitadas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(function(){ atualizarContagens(); setInterval(atualizarContagens, 30000); });
function atualizarContagens(){
  $.get('/api/pedidos-pendentes', function(r){ if(r.success) $('#count-pendentes').text(r.data.length); });
  $.get('/api/pedidos-aprovados', function(r){ if(r.success) $('#count-aprovadas').text(r.data.length); });
  $.get('/api/pedidos-rejeitados', function(r){ if(r.success) $('#count-rejeitadas').text(r.data.length); });
}
</script>
@stop

@section('css')
<style>
.box-hover { transition: transform .15s ease, box-shadow .15s ease; }
.box-hover:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.small-box .inner h3 { font-weight: 700; }
.small-box .icon { color: rgba(255,255,255,.7); }
</style>
@stop


