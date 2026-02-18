@extends('layouts.app')

@section('title', 'Mis Pedidos Asignados - Brisas Gems')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos-estados.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="pedidos-container">
    <div class="container-fluid py-5">
        {{-- Header con Stats Pills --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-palette-fill me-3"></i>Mis Pedidos Asignados</h1>
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-receipt-cutoff text-primary"></i>
                            <span class="pill-label">Total:</span>
                            <strong class="pill-value">{{ $stats['total'] ?? 0 }}</strong>
                        </div>
                        @foreach(($estados ?? []) as $estado)
                            @php
                                $estadoId = (int) ($estado['id'] ?? 0);
                                $estadoNombre = $estado['nombre'] ?? 'Estado';
                                $count = (int) ($stats['porEstado'][$estadoId] ?? 0);
                                $badgeClass = match($estadoId) {
                                    1 => 'badge-pendiente',
                                    2 => 'badge-confirmado',
                                    3 => 'badge-diseno',
                                    4 => 'badge-aprobado',
                                    5 => 'badge-produccion',
                                    6 => 'badge-calidad',
                                    7 => 'badge-listo',
                                    8 => 'badge-camino',
                                    9 => 'badge-entregado',
                                    10 => 'badge-cancelado',
                                    default => 'badge-secondary'
                                };
                                $icon = match($estadoId) {
                                    1 => 'bi-clock-fill',
                                    2 => 'bi-credit-card-fill',
                                    3 => 'bi-palette-fill',
                                    4 => 'bi-check2-circle',
                                    5 => 'bi-gear-fill',
                                    6 => 'bi-gem',
                                    7 => 'bi-stars',
                                    8 => 'bi-truck',
                                    9 => 'bi-box-seam-fill',
                                    10 => 'bi-x-circle-fill',
                                    default => 'bi-info-circle'
                                };
                            @endphp
                            <div class="pill-stat">
                                <i class="bi {{ $icon }} estado-color {{ $badgeClass }}"></i>
                                <span class="pill-label">{{ $estadoNombre }}:</span>
                                <strong class="pill-value">{{ $count }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Mensajes de exito/error --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Tabla de pedidos (USANDO EL MISMO COMPONENTE QUE EL ADMIN) --}}
        @include('components.pedidos.tabla-listado', ['pedidos' => $pedidos, 'filtros' => $filtros, 'estados' => $estados, 'pageSize' => $pageSize, 'estadoMapeo' => $estadoMapeo])
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.js"></script>
<script>
// Variables globales
const designerId = {{ Session::get('user_id') ?? 0 }};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Designer pedidos page loaded');
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
