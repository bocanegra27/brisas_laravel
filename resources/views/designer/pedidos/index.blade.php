@extends('layouts.app')

@section('title', 'Gestión de Pedidos - Mi Área')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="pedidos-container">
    <div class="container-fluid py-5">
        
        {{-- Header con Stats Pills (Usamos stats vacías del controlador Designer) --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    {{-- Título ajustado para el diseñador --}}
                    <h1><i class="bi bi-palette-fill me-3"></i>Mis Pedidos Asignados</h1>
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-receipt-cutoff text-primary"></i>
                            <span class="pill-label">Total Asignados:</span>
                            <strong class="pill-value">{{ $totalElements ?? 0 }}</strong>
                        </div>
                        {{-- Mantenemos las estadísticas básicas si el controlador Designer las provee --}}
                        <div class="pill-stat">
                            <i class="bi bi-clock-fill" style="color: #f59e0b;"></i>
                            <span class="pill-label">Pendientes:</span>
                            <strong class="pill-value">{{ $stats['pendientes'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-gear-fill" style="color: #3b82f6;"></i>
                            <span class="pill-label">En Proceso:</span>
                            <strong class="pill-value">{{ $stats['produccion'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-box-seam-fill" style="color: #10b981;"></i>
                            <span class="pill-label">Finalizados:</span>
                            <strong class="pill-value">{{ $stats['entregados'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    {{-- RUTA AJUSTADA: De admin.dashboard a designer.dashboard --}}
                    <a href="{{ route('designer.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house-door-fill"></i>
                    </a>
                    {{-- RUTA AJUSTADA: De admin.pedidos.create a designer.pedidos.create --}}
                    <a href="{{ route('designer.pedidos.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Crear Nuevo Pedido
                    </a>
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

        {{-- Tabla de pedidos (componente compartido por roles) --}}
        @include('components.pedidos.tabla-listado', [
            'pedidos' => $pedidos,
            'estados' => $estados,
            'filtros' => $filtros,
            'pageSize' => $pageSize,
            'currentPage' => $currentPage,
            'totalElements' => $totalElements,
            'totalPages' => $totalPages,
            'estadoMapeo' => $estadoMapeo,
            'disenadores' => $disenadores ?? []
        ])
    </div>
</div>

{{-- ELIMINAMOS TODOS LOS MODALES DEL ADMIN (Cambiar Estado Rápido y Asignar Diseñador) ya que el diseñador no los necesita, 
    y el modal de Cambiar Estado Rápido se maneja con la función cambiarEstadoPedido, la cual debe ser modificada. --}}

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('assets/js/pedidos.js') }}"></script> 

    <script>
        // Inicializar tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
        
        // Lógica de Filtros (Se mantiene)
        document.getElementById('filterEstado')?.addEventListener('change', aplicarFiltros);
        document.getElementById('pageSize')?.addEventListener('change', aplicarFiltros);
        
        let timeoutCodigo;
        document.getElementById('searchCodigo')?.addEventListener('input', function() {
            clearTimeout(timeoutCodigo);
            timeoutCodigo = setTimeout(aplicarFiltros, 500);
        });
        
        function aplicarFiltros() {
            const estadoId = document.getElementById('filterEstado')?.value || '';
            const codigo = document.getElementById('searchCodigo')?.value || '';
            const size = document.getElementById('pageSize')?.value || '10';
            
            const url = new URL(window.location.href);
            url.searchParams.set('page', '0');
            url.searchParams.set('size', size);
            url.searchParams.set('estadoId', estadoId);
            url.searchParams.set('codigo', codigo);
            
            window.location.href = url.toString();
        }

        // ---------------------------------------------------
        // Lógica de Cambio de Estado Rápido (Simplificada para el Diseñador)
        // ---------------------------------------------------
        
        /**
         * En el listado del diseñador, el cambio de estado rápido ahora redirige
         * directamente a la vista de gestionar, ya que debe añadir comentarios/evidencia.
         */
        function cambiarEstadoPedido(pedidoId, estadoActualId) {
            // RUTA AJUSTADA: Redirigir a la vista de gestión del diseñador
            const urlGestion = `{{ route('designer.pedidos.gestionar', ['id' => 'PEDIDO_ID']) }}`.replace('PEDIDO_ID', pedidoId);
            window.location.href = urlGestion;
        }

        // 🚫 SE ELIMINARON TODAS LAS DEMÁS FUNCIONES JS DE ADMINISTRADOR (eliminarPedido, asignación, confirmarCambioEstado).
    </script>
@endpush