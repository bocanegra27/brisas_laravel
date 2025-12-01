@extends('layouts.app')

@section('title', 'Panel del Diseñador - Brisas Gems')

@push('styles')
{{-- Bootstrap Icons ya está incluido en app.blade.php --}}
{{-- Dashboard shared CSS --}}
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}" />
@endpush

@section('content')
<main class="container mt-4 pb-5">
    {{-- Header --}}
    <div class="dashboard-header animate-in" style="background: linear-gradient(135deg, var(--dash-primary) 0%, var(--dash-primary-dark) 100%); color: white;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 style="-webkit-text-fill-color: white;"><i class="bi bi-palette2 me-2"></i>Espacio Creativo</h1>
                <p class="mb-0" style="color: rgba(255,255,255,0.9);">¡Hola {{ Session::get('user_name', 'Diseñador') }}! Dale vida a nuevas creaciones hoy ✨</p>
            </div>
            <div>
                <span class="role-badge" style="background: rgba(255, 255, 255, 0.2); border: 2px solid rgba(255, 255, 255, 0.3);">
                    <i class="bi bi-brush-fill"></i>
                    Diseñador
                </span>
            </div>
        </div>
    </div>

    {{-- Mis Actividades --}}
    <h2 class="section-header animate-in animate-delay-1" style="display: flex; align-items: center; gap: 1rem;">
        <span style="width: 6px; height: 40px; background: linear-gradient(180deg, var(--dash-primary) 0%, var(--dash-accent) 100%); border-radius: 3px;"></span>
        Mis Actividades
    </h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-md-4 mb-5">
        <div class="col animate-in animate-delay-1">
            {{-- Ruta pendiente: /designer/disenos --}}
            <a href="{{ url('/designer/dashboard') }}" class="stat-card">
                <div class="card">
                    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; opacity: 0.08; z-index: 0; background: linear-gradient(135deg, #009688 0%, #00796b 100%);"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-primary-gradient mx-auto">
                            <i class="bi bi-palette2 text-white"></i>
                        </div>
                        <p class="card-text">Diseños Activos</p>
                        <h2 class="display-4 text-primary" style="font-family: 'Fraunces', serif;">{{ $data['disenosActivos'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-2">
            {{-- Ruta pendiente: /designer/renders --}}
            <a href="{{ url('/designer/dashboard') }}" class="stat-card">
                <div class="card">
                    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; opacity: 0.08; z-index: 0; background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-info-gradient mx-auto">
                            <i class="bi bi-cube text-white"></i>
                        </div>
                        <p class="card-text">Renders Pendientes</p>
                        <h2 class="display-4 text-info" style="font-family: 'Fraunces', serif;">{{ $data['rendersPendientes'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-3">
            {{-- Ruta pendiente: /designer/comunicacion --}}
            <a href="{{ url('/designer/dashboard') }}" class="stat-card">
                <div class="card">
                    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; opacity: 0.08; z-index: 0; background: linear-gradient(135deg, #ffc107 0%, #f57c00 100%);"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-warning-gradient mx-auto">
                            <i class="bi bi-chat-dots text-white"></i>
                        </div>
                        <p class="card-text">Mensajes</p>
                        <h2 class="display-4 text-warning" style="font-family: 'Fraunces', serif;">{{ $data['comunicacionesPendientes'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-4">
            <a href="{{ url('/pedidos') }}" class="stat-card">
                <div class="card">
                    <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; border-radius: 50%; opacity: 0.08; z-index: 0; background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);"></div>
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-success-gradient mx-auto">
                            <i class="bi bi-clipboard-check text-white"></i>
                        </div>
                        <p class="card-text">Pedidos Asignados</p>
                        <h2 class="display-4 text-success" style="font-family: 'Fraunces', serif;">{{ $data['pedidosAsignados'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Acciones Rápidas --}}
    <h2 class="section-header animate-in animate-delay-2" style="display: flex; align-items: center; gap: 1rem;">
        <span style="width: 6px; height: 40px; background: linear-gradient(180deg, var(--dash-primary) 0%, var(--dash-accent) 100%); border-radius: 3px;"></span>
        Acciones Rápidas
    </h2>
    <div class="row g-3 g-md-4">
        <div class="col-md-6 animate-in animate-delay-1">
            {{-- Ruta pendiente: /designer/nuevo-diseno --}}
            <a href="{{ url('/designer/dashboard') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-primary-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-plus-circle text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-plus-circle me-2"></i>Nuevo Diseño</h6>
                        <p class="mb-3">Comienza un diseño desde cero con todas las herramientas disponibles</p>
                        <span class="btn btn-primary btn-sm">Comenzar Ahora</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 animate-in animate-delay-2">
            {{-- Ruta pendiente: /designer/renders --}}
            <a href="{{ url('/designer/dashboard') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-info-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-clock-history text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-clock-history me-2"></i>Revisar Renders</h6>
                        <p class="mb-3">Revisa y aprueba los renders 3D pendientes de tus diseños</p>
                        <span class="btn btn-info btn-sm">Revisar Ahora</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 animate-in animate-delay-3">
            {{-- Ruta pendiente: /designer/comunicacion --}}
            <a href="{{ url('/designer/dashboard') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-warning-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-chat-square-text text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-chat-square-text me-2"></i>Comunicación con Clientes</h6>
                        <p class="mb-3">Revisa mensajes y retroalimentación sobre tus diseños</p>
                        <span class="btn btn-warning btn-sm">Ver Mensajes</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 animate-in animate-delay-4">
            <a href="{{ url('/pedidos') }}" class="action-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-wrapper bg-success-gradient" style="width: 60px; height: 60px; border-radius: 12px; flex-shrink: 0;">
                        <i class="bi bi-list-check text-white" style="font-size: 1.75rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6><i class="bi bi-list-check me-2"></i>Gestionar Pedidos</h6>
                        <p class="mb-3">Administra todos los pedidos que tienes asignados</p>
                        <span class="btn btn-success btn-sm">Ver Pedidos</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</main>
@endsection