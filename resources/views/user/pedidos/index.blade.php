@extends('layouts.app')
@section('title', 'Mis Pedidos - Brisas Gems')

@section('content')
<div class="container py-5">
    
    {{-- Encabezado Estilo Dashboard --}}
    <div>
        <div class="card-body p-4">
            <div class="welcome-section animate-in">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>¡Bienvenido, {{ Session::get('user_name', 'Usuario') }}! <span class="wave">👋</span></h2>
                <p>Gestiona tus pedidos y crea las joyas de tus sueños</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('personalizar.index') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Crear Nueva Joya
                </a>
            </div>
        </div>
    </div>

    {{-- Tabla de Pedidos (componente compartido) --}}
    @include('components.pedidos.tabla-listado', [
        'pedidos' => $pedidos,
        'estados' => $estados ?? [],
        'filtros' => $filtros ?? [],
        'pageSize' => $pageSize ?? 10,
        'currentPage' => $currentPage ?? 0,
        'totalElements' => $totalElements ?? count($pedidos),
        'totalPages' => $totalPages ?? 1,
        'estadoMapeo' => $estadoMapeo ?? [],
    ])
</div>
@endsection