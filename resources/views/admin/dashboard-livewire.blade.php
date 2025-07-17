@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@stop

@section('content')
    <livewire:dashboard />
@stop

@section('css')
<style>
    .small-box {
        border-radius: 0.5rem;
        overflow: hidden;
        position: relative;
        margin-bottom: 20px;
    }
    .small-box .icon {
        opacity: 0.7;
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 70px;
        color: rgba(255, 255, 255, 0.2);
    }
    .small-box .inner {
        padding: 20px;
    }
    .small-box .inner h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        white-space: nowrap;
        color: #fff;
    }
    .small-box .inner p {
        font-size: 1rem;
        margin-bottom: 0;
        color: #fff;
    }
    .small-box .small-box-footer {
        background-color: rgba(0, 0, 0, 0.1);
        color: #fff;
        display: block;
        padding: 5px 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 10;
    }
    .small-box .small-box-footer:hover {
        background-color: rgba(0, 0, 0, 0.15);
        color: #fff;
    }
    .card {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .card-header {
        background-color: rgba(0,0,0,0.03);
    }
    .products-list .product-title {
        font-weight: 600;
    }
    .product-description {
        color: #6c757d;
    }
    .table td, .table th {
        padding: 0.5rem 1rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
@stop 