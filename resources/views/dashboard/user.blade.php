@extends('layouts.app')

@section('title', 'Mi Panel - Brisas Gems')

@push('styles')
{{-- Bootstrap Icons ya está incluido en app.blade.php --}}
{{-- Dashboard shared CSS --}}
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}" />
@endpush

@section('content')
<main class="container mt-4 pb-5">
    {{-- Welcome Section --}}
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

    {{-- Mis Actividades --}}
    <h2 class="section-header animate-in animate-delay-1">
        <i class="bi bi-bar-chart-fill"></i>
        Mis Actividades
    </h2>
    <div class="row row-cols-1 row-cols-md-3 g-3 g-md-4 mb-5">
        <div class="col animate-in animate-delay-1">
            {{-- 🔥 CORRECCIÓN 1: Usar la ruta nombrada para evitar el error 404 --}}
            <a href="{{ route('user.pedidos.index') }}" class="stat-card">
                <div class="card">
                    <div class="card-body">
                        <div class="icon-wrapper bg-primary-soft">
                            <i class="bi bi-clock-history text-primary"></i>
                        </div>
                        <p class="card-text">Pedidos Activos</p>
                        <h2 class="display-4 text-primary">{{ $data['misPedidosActivos'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-2">
            {{-- Ruta pendiente: idealmente crear una ruta user.personalizaciones.index --}}
            <a href="#" class="stat-card">
                <div class="card">
                    <div class="card-body">
                        <div class="icon-wrapper bg-info-soft">
                            <i class="bi bi-palette2 text-info"></i>
                        </div>
                        <p class="card-text">Mis Personalizaciones</p>
                        <h2 class="display-4 text-info">{{ $data['misPersonalizaciones'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>

        <div class="col animate-in animate-delay-3">
            {{-- Ruta pendiente --}}
            <a href="#" class="stat-card">
                <div class="card">
                    <div class="card-body">
                        <div class="icon-wrapper bg-success-soft">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                        <p class="card-text">Pedidos Completados</p>
                        <h2 class="display-4 text-success">{{ $data['pedidosCompletados'] ?? 0 }}</h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Acciones Rápidas --}}
        <div class="col-lg-6 animate-in animate-delay-2">
            <h2 class="section-header">
                <i class="bi bi-lightning-fill"></i>
                Acciones Rápidas
            </h2>
            <div class="card user-info-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            {{-- Se recomienda usar route() si la tienes definida --}}
                            <a href="{{ route('personalizar.index') }}" class="quick-action-btn">
                                <i class="bi bi-plus-circle"></i>
                                <span>Personalizar Joya</span>
                            </a>
                        </div>
                        <div class="col-6">
                            {{-- Si no tienes ruta nombrada para inspiración, url() está bien --}}
                            <a href="{{ url('/inspiracion') }}" class="quick-action-btn">
                                <i class="bi bi-images"></i>
                                <span>Ver Inspiración</span>
                            </a>
                        </div>
                        <div class="col-6">
                            {{-- 🔥 CORRECCIÓN 2: Ruta correcta para Mis Pedidos --}}
                            <a href="{{ route('user.pedidos.index') }}" class="quick-action-btn">
                                <i class="bi bi-list-check"></i>
                                <span>Mis Pedidos</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('contacto.create') }}" class="quick-action-btn">
                                <i class="bi bi-headset"></i>
                                <span>Soporte</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mi Información --}}
        <div class="col-lg-6 animate-in animate-delay-3">
            <h2 class="section-header">
                <i class="bi bi-person-circle"></i>
                Mi Información
            </h2>
            <div class="card user-info-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar flex-shrink-0">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fw-bold">{{ Session::get('user_name', 'Usuario') }}</h6>
                            <p class="text-muted small mb-1">{{ Session::get('user_email', 'usuario@ejemplo.com') }}</p>
                            <span class="badge-custom" style="background: linear-gradient(135deg, var(--dash-primary) 0%, var(--dash-primary-dark) 100%); color: white; padding: 0.5rem 1rem; border-radius: 50px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">Cliente Premium</span>
                        </div>
                    </div>

                    <div class="user-stats">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <div class="user-stat-item" style="text-align: center; padding: 0.5rem;">
                                    <div class="h5 mb-0" style="font-weight: 800; color: var(--dash-primary);">{{ $data['misPedidosActivos'] ?? 0 }}</div>
                                    <small style="color: var(--dash-text-secondary); font-weight: 500;">Activos</small>
                                </div>
                            </div>
                            <div class="col-auto px-0">
                                <div class="user-stat-divider" style="width: 1px; background: #dee2e6; height: 40px; margin: auto 0;"></div>
                            </div>
                            <div class="col-4">
                                <div class="user-stat-item" style="text-align: center; padding: 0.5rem;">
                                    <div class="h5 mb-0" style="font-weight: 800; color: var(--dash-primary);">{{ $data['misPersonalizaciones'] ?? 0 }}</div>
                                    <small style="color: var(--dash-text-secondary); font-weight: 500;">Diseños</small>
                                </div>
                            </div>
                            <div class="col-auto px-0">
                                <div class="user-stat-divider" style="width: 1px; background: #dee2e6; height: 40px; margin: auto 0;"></div>
                            </div>
                            <div class="col-3">
                                <div class="user-stat-item" style="text-align: center; padding: 0.5rem;">
                                    <div class="h5 mb-0" style="font-weight: 800; color: var(--dash-primary);">{{ $data['pedidosCompletados'] ?? 0 }}</div>
                                    <small style="color: var(--dash-text-secondary); font-weight: 500;">Completados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection