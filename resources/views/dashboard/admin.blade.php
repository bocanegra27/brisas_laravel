@extends('layouts.app')

@section('title', 'Panel de Administración - Brisas Gems')

@push('styles')
{{-- Bootstrap Icons ya está incluido en app.blade.php --}}
{{-- Dashboard shared CSS --}}
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}" />
@endpush

@section('content')
<main class="container mt-4 pb-5">
    {{-- Header --}}
    <div class="dashboard-header animate-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-speedometer2 me-2"></i>Panel de Administración</h1>
                <p class="text-muted mb-0">Bienvenido {{ Session::get('user_name', 'Administrador') }}</p>
            </div>
            <div>
                <span class="role-badge">
                    <i class="bi bi-shield-check"></i>
                    Administrador
                </span>
            </div>
        </div>
    </div>

    {{-- Estado de la Producción --}}
    <h2 class="section-header animate-in animate-delay-1">Estado de la Producción</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 g-md-4 mb-5">
        <div class="col animate-in animate-delay-1">
            <a href="{{ url('/pedidos?estado=diseño') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-primary-soft mx-auto">
                            <i class="bi bi-palette2 text-primary"></i>
                        </div>
                        <p class="card-text">En Diseño</p>
                        <h2 class="display-4 text-primary">{{ $data['pedidosEnDiseño'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +2 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-2">
            <a href="{{ url('/pedidos?estado=tallado') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-info-soft mx-auto">
                            <i class="bi bi-gem text-info"></i>
                        </div>
                        <p class="card-text">En Tallado</p>
                        <h2 class="display-4 text-info">{{ $data['pedidosEnTallado'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +1 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-3">
            <a href="{{ url('/pedidos?estado=engaste') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-secondary-soft mx-auto">
                            <i class="bi bi-gear text-secondary"></i>
                        </div>
                        <p class="card-text">En Engaste</p>
                        <h2 class="display-4 text-secondary">{{ $data['pedidosEnEngaste'] ?? 0 }}</h2>
                        <span class="trend">
                            <i class="bi bi-dash"></i> Sin cambios
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-4">
            <a href="{{ url('/pedidos?estado=pulido') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-warning-soft mx-auto">
                            <i class="bi bi-brightness-high text-warning"></i>
                        </div>
                        <p class="card-text">En Pulido</p>
                        <h2 class="display-4 text-warning">{{ $data['pedidosEnPulido'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +3 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-5">
            <a href="{{ url('/pedidos?estado=cancelados') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-danger-soft mx-auto">
                            <i class="bi bi-x-circle text-danger"></i>
                        </div>
                        <p class="card-text">Cancelados</p>
                        <h2 class="display-4 text-danger">{{ $data['pedidosCancelados'] ?? 0 }}</h2>
                        <span class="trend down">
                            <i class="bi bi-arrow-down"></i> -1 hoy
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Gestión General - ACTUALIZADO --}}
    <h2 class="section-header animate-in animate-delay-2">Gestión General</h2>
    <div class="row g-3 g-md-4">
        {{-- 1. GESTIÓN DE USUARIOS --}}
        <div class="col-lg-4 col-md-6 animate-in animate-delay-1">
            <a href="{{ url('/admin/usuarios') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-success-soft mx-auto">
                            <i class="bi bi-people text-success"></i>
                        </div>
                        <p class="card-text">Gestión de Usuarios</p>
                        <h2 class="display-4 text-success">{{ ($data['totalUsuariosActivos'] ?? 0) + ($data['totalUsuariosInactivos'] ?? 0) }}</h2>
                        <div class="d-flex justify-content-center gap-3 mt-2">
                            <span class="badge badge-success">
                                <i class="bi bi-check-circle"></i> {{ $data['totalUsuariosActivos'] ?? 0 }} Activos
                            </span>
                            <span class="badge badge-secondary">
                                <i class="bi bi-dash-circle"></i> {{ $data['totalUsuariosInactivos'] ?? 0 }} Inactivos
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 2. MENSAJES/CONTACTOS --}}
        <div class="col-lg-4 col-md-6 animate-in animate-delay-2">
            <a href="{{ url('/admin/mensajes') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-danger-soft mx-auto">
                            <i class="bi bi-envelope-exclamation text-danger"></i>
                        </div>
                        <p class="card-text">Mensajes</p>
                        <h2 class="display-4 text-danger">{{ $data['totalContactosPendientes'] ?? 0 }}</h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +5 nuevos
                        </span>
                    </div>
                </div>
            </a>
        </div>

        {{-- 3. PEDIDOS --}}
        <div class="col-lg-4 col-md-6 animate-in animate-delay-3">
            <a href="{{ url('/admin/pedidos') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-secondary-soft mx-auto">
                            <i class="bi bi-box-seam text-secondary"></i>
                        </div>
                        <p class="card-text">Pedidos</p>
                        <h2 class="display-4 text-secondary">
                            {{ 
                                ($data['pedidosEnDiseño'] ?? 0) + 
                                ($data['pedidosEnTallado'] ?? 0) + 
                                ($data['pedidosEnEngaste'] ?? 0) + 
                                ($data['pedidosEnPulido'] ?? 0) 
                            }}
                        </h2>
                        <span class="trend up">
                            <i class="bi bi-arrow-up"></i> +6 esta semana
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</main>
@endsection