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

        {{-- Tabla de pedidos --}}
        <div class="card pedidos-table-card animate-in animate-delay-5">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Pedidos</h5>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            {{-- Filtros (se mantienen, pero buscan solo dentro de los asignados) --}}
                            <div class="col-md-5">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="searchCodigo" class="form-control" 
                                            placeholder="Buscar por código de pedido..."
                                            value="{{ $filtros['codigo'] ?? '' }}">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <select id="filterEstado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    @foreach($estados as $estado)
                                    <option value="{{ $estado['id'] }}" 
                                        {{ (isset($filtros['estadoId']) && $filtros['estadoId'] == $estado['id']) ? 'selected' : '' }}>
                                        {{ $estado['nombre'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <select id="pageSize" class="form-select">
                                    <option value="10" {{ $pageSize == 10 ? 'selected' : '' }}>10 por página</option>
                                    <option value="25" {{ $pageSize == 25 ? 'selected' : '' }}>25 por página</option>
                                    <option value="50" {{ $pageSize == 50 ? 'selected' : '' }}>50 por página</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table pedidos-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha Creación</th>
                                <th>Cliente</th>
                                <th>Diseñador</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                                {{-- 🚫 SE ELIMINA LA COLUMNA DE ELIMINAR --}}
                            </tr>
                        </thead>
                        <tbody id="pedidosTableBody">
                            @forelse($pedidos as $pedido)
                            <tr class="pedido-row">
                                <td class="fw-bold">#{{ $pedido['pedCodigo'] }}</td>
                                
                                <td>
                                    @php
                                        $fechaLocal = \Carbon\Carbon::parse($pedido['pedFechaCreacion'])
                                            ->setTimezone(config('app.timezone')); 
                                    @endphp
                                    
                                    <small class="text-muted d-block">
                                        {{ $fechaLocal->format('d/m/Y') }}
                                    </small>
                                    <span class="fw-medium">
                                        {{ $fechaLocal->format('h:i a') }} 
                                    </span>
                                </td>
                                
                                {{-- COLUMNA CLIENTE (Se mantiene) --}}
                                <td>
                                    @if (!empty($pedido['nombreCliente']))
                                        {{ $pedido['nombreCliente'] }}
                                    @elseif (!empty($pedido['pedIdentificadorCliente']))
                                        <span class="text-muted">{{ $pedido['pedIdentificadorCliente'] }}</span>
                                    @else
                                        <span class="text-muted">Desconocido</span>
                                    @endif
                                </td>
                                
                                {{-- COLUMNA DISEÑADOR (Se mantiene, siempre debería ser el diseñador actual) --}}
                                <td>
                                    @php
                                        $nombreEmpleado = $pedido['nombreEmpleado'] ?? 'PENDIENTE ASIGNAR';
                                    @endphp

                                    @if ($nombreEmpleado === 'PENDIENTE ASIGNAR')
                                        <span class="badge bg-warning text-dark">{{ $nombreEmpleado }}</span>
                                    @else
                                        {{ $nombreEmpleado }}
                                    @endif
                                </td>

                                {{-- Columna Estado (Se mantiene) --}}
                                <td>
                                    @php
                                        $estadoCrudo = $pedido['estadoNombre'] ?? ($pedido['estado']['estNombre'] ?? 'desconocido');
                                        $estadoLimpio = $estadoMapeo[$estadoCrudo] ?? $estadoCrudo;
                                    @endphp
                                    <span class="text-secondary fw-medium">{{ $estadoLimpio }}</span>
                                </td>
                                
                                {{-- COLUMNA ACCIONES (Eliminamos Asignar Diseñador) --}}
                                <td class="text-center">
                                    <div class="action-buttons d-flex gap-2 align-items-center justify-content-center">
                                        {{-- Gestionar pedido --}}
                                        {{-- RUTA AJUSTADA: De admin.pedidos.gestionar a designer.pedidos.gestionar --}}
                                        <a href="{{ route('designer.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" 
                                           class="btn-action btn-gestionar btn btn-sm btn-primary"
                                           data-bs-toggle="tooltip" title="Gestionar pedido">
                                            <i class="bi bi-gear-fill"></i>
                                        </a>

                                        {{-- Cambiar estado rapido --}}
                                        <button onclick="cambiarEstadoPedido({{ $pedido['pedId'] }}, {{ $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1) }})" 
                                                class="btn-action btn-status btn btn-sm btn-outline-secondary" 
                                                data-bs-toggle="tooltip" title="Cambiar estado">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        
                                        {{-- 🚫 SE ELIMINA EL BOTÓN DE ASIGNAR DISEÑADOR --}}
                                    </div>
                                </td>
                                
                                {{-- 🚫 SE ELIMINA LA COLUMNA VACÍA DE ELIMINAR --}}
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No tienes pedidos asignados actualmente.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Footer con paginacion (Se mantiene, solo rutas ajustadas) --}}
            @if($totalElements > 0)
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="pagination-info mb-0">
                            Mostrando {{ ($currentPage * $pageSize) + 1 }} 
                            a {{ min(($currentPage + 1) * $pageSize, $totalElements) }} 
                            de {{ $totalElements }} pedidos
                        </p>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Paginacion de pedidos">
                            <ul class="pagination justify-content-end mb-0">
                                @php
                                    // Helper para generar la query string
                                    $query = "&size=$pageSize&estadoId={$filtros['estadoId']}&codigo={$filtros['codigo']}";
                                @endphp
                                
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page=0{{ $query }}">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $currentPage - 1 }}{{ $query }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                @for($i = max(0, $currentPage - 2); $i <= min($totalPages - 1, $currentPage + 2); $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="?page={{ $i }}{{ $query }}">
                                        {{ $i + 1 }}
                                    </a>
                                </li>
                                @endfor
                                
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $currentPage + 1 }}{{ $query }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $totalPages - 1 }}{{ $query }}">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            @endif
        </div>
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