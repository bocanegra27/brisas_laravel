@extends('layouts.app')

@section('title', 'Panel de Administración - Brisas Gems')

@push('styles')
{{-- Bootstrap Icons ya está incluido en app.blade.php --}}
{{-- Dashboard shared CSS --}}
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/utilities.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
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

    {{-- Gestión General - Ahora con 4 Columnas --}}
    <h2 class="section-header animate-in animate-delay-1">Gestión del Sistema</h2>
    <div class="row g-3 g-md-4">
        
        {{-- 1. GESTIÓN DE USUARIOS --}}
        <div class="col-xl-3 col-md-6 animate-in animate-delay-1">
            <a href="{{ route('admin.usuarios.index') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-success-soft mx-auto">
                            <i class="bi bi-people text-success"></i>
                        </div>
                        <p class="card-text">Usuarios</p>
                        <h2 class="display-4 text-success">{{ $data['totalUsuarios'] ?? (($data['totalUsuariosActivos'] ?? 0) + ($data['totalUsuariosInactivos'] ?? 0)) }}</h2>
                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <span class="badge badge-success">{{ $data['totalUsuariosActivos'] ?? 0 }} Activos</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 2. MENSAJES/CONTACTOS --}}
        <div class="col-xl-3 col-md-6 animate-in animate-delay-2">
            <a href="{{ route('admin.mensajes.index') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-danger-soft mx-auto">
                            <i class="bi bi-envelope-exclamation text-danger"></i>
                        </div>
                        <p class="card-text">Mensajes</p>
                        <h2 class="display-4 text-danger">{{ $data['totalContactos'] ?? 0 }}</h2>
                        <div class="d-flex justify-content-center gap-1 mt-2">
                            <span class="badge badge-warning">Pendientes: {{ $data['totalContactosPendientes'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- 3. PEDIDOS --}}
        <div class="col-xl-3 col-md-6 animate-in animate-delay-3">
            <a href="{{ route('admin.pedidos.index') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-secondary-soft mx-auto">
                            <i class="bi bi-box-seam text-secondary"></i>
                        </div>
                        <p class="card-text">Producción</p>
                        <h2 class="display-4 text-secondary">{{ $data['totalPedidosActivos'] ?? 0 }}</h2>
                        <span class="trend up"><i class="bi bi-arrow-up"></i> Activos</span>
                    </div>
                </div>
            </a>
        </div>

        {{-- 4. NUEVA: GESTIÓN DE PERSONALIZACIÓN --}}
        <div class="col-xl-3 col-md-6 animate-in animate-delay-4">
            <a href="{{ route('admin.personalizacion.categorias.index') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-indigo-soft mx-auto">
                            <i class="bi bi-gem text-indigo"></i>
                        </div>
                        <p class="card-text">Personalización</p>
                        <h2 class="display-4 text-indigo">{{ $data['totalCategorias'] ?? 2 }}</h2>
                        <div class="mt-2 text-muted small">
                            Gestionar Catálogo Dinámico
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

{{-- Estado de la Producción --}}
<h2 class="section-header animate-in animate-delay-1">Estado de la Producción</h2>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 g-md-4 mb-5">

    <div class="col animate-in animate-delay-1">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-warning-soft mx-auto">
                        <i class="bi bi-clipboard-check text-warning"></i>
                    </div>
                    <p class="card-text">Cotización</p>
                    <h2 class="display-4 text-warning">{{ $data['pedidosCotizacionPendiente'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-2">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-danger-soft mx-auto">
                        <i class="bi bi-cash-coin text-danger"></i>
                    </div>
                    <p class="card-text">Pago Pendiente</p>
                    <h2 class="display-4 text-danger">{{ $data['pedidosPagoDisenoPendiente'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-3">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-primary-soft mx-auto">
                        <i class="bi bi-palette2 text-primary"></i>
                    </div>
                    <p class="card-text">En Diseño</p>
                    <h2 class="display-4 text-primary">{{ $data['pedidosDisenoEnProceso'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-4">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-success-soft mx-auto">
                        <i class="bi bi-check-circle text-success"></i>
                    </div>
                    <p class="card-text">Diseño OK</p>
                    <h2 class="display-4 text-success">{{ $data['pedidosDisenoAprobado'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-5">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-info-soft mx-auto">
                        <i class="bi bi-gem text-info"></i>
                    </div>
                    <p class="card-text">Tallado</p>
                    <h2 class="display-4 text-info">{{ $data['pedidosEnTallado'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-6">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-secondary-soft mx-auto">
                        <i class="bi bi-gear text-secondary"></i>
                    </div>
                    <p class="card-text">Engaste</p>
                    <h2 class="display-4 text-secondary">{{ $data['pedidosEnEngaste'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-7">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-warning-soft mx-auto">
                        <i class="bi bi-brightness-high text-warning"></i>
                    </div>
                    <p class="card-text">Pulido</p>
                    <h2 class="display-4 text-warning">{{ $data['pedidosEnPulido'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-8">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-info-soft mx-auto">
                        <i class="bi bi-search text-info"></i>
                    </div>
                    <p class="card-text">Inspección</p>
                    <h2 class="display-4 text-info">{{ $data['pedidosInspeccionCalidad'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-9">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-success-soft mx-auto">
                        <i class="bi bi-check-all text-success"></i>
                    </div>
                    <p class="card-text">Finalizados</p>
                    <h2 class="display-4 text-success">{{ $data['pedidosFinalizados'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col animate-in animate-delay-10">
        <div class="stat-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-danger-soft mx-auto">
                        <i class="bi bi-x-circle text-danger"></i>
                    </div>
                    <p class="card-text">Cancelados</p>
                    <h2 class="display-4 text-danger">{{ $data['pedidosCancelados'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

</div>


</main>
@endsection