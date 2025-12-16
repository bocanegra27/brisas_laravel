@extends('layouts.app')
@section('title', 'Mis Pedidos - Brisas Gems')

@section('content')
<div class="container py-5">
    
    {{-- Encabezado Estilo Dashboard --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1">
                        <i class="bi bi-list-check me-2 text-primary"></i>Mis Pedidos
                    </h2>
                    <p class="text-muted mb-0">Gestiona y rastrea el estado de tus joyas personalizadas</p>
                </div>
                <div>
                    <a href="{{ route('personalizar.index') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Nuevo Pedido
                    </a>
                </div>
            </div>

            {{-- Filtros (Visuales por ahora) --}}
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por código o descripción...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option selected>Todos los estados</option>
                        <option value="1">Pendientes</option>
                        <option value="2">En Proceso</option>
                        <option value="3">Completados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Pedidos --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-4 text-muted text-uppercase small fw-bold">Código</th>
                            <th class="py-3 text-muted text-uppercase small fw-bold">Fecha</th>
                            <th class="py-3 text-muted text-uppercase small fw-bold">Descripción</th>
                            <th class="py-3 text-muted text-uppercase small fw-bold">Precio Est.</th>
                            <th class="py-3 text-muted text-uppercase small fw-bold">Estado</th>
                            <th class="py-3 pe-4 text-end text-muted text-uppercase small fw-bold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            <tr>
                                <td class="ps-4 fw-bold text-primary">
                                    #{{ $pedido['pedCodigo'] }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-dark">
                                            {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('d M, Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('h:i A') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                        {{ $pedido['pedComentarios'] ?? 'Pedido Personalizado' }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    {{-- Usamos el operador ?? 0 para evitar errores si viene null --}}
                                    ${{ number_format($pedido['pedCostoTotal'] ?? 0, 0) }}
                                </td>
                                <td>
                                    {{-- Lógica de Badges según estado --}}
                                    @php
                                        $estadoClass = match($pedido['estId']) {
                                            1, 2 => 'bg-secondary-subtle text-secondary border-secondary', // Pendiente
                                            9 => 'bg-success-subtle text-success border-success',          // Entregado
                                            10 => 'bg-danger-subtle text-danger border-danger',            // Cancelado
                                            default => 'bg-info-subtle text-info border-info'              // En proceso
                                        };
                                        $iconClass = match($pedido['estId']) {
                                            9 => 'bi-check-circle-fill',
                                            10 => 'bi-x-circle-fill',
                                            default => 'bi-clock-fill'
                                        };
                                    @endphp
                                    <span class="badge rounded-pill border {{ $estadoClass }} px-3 py-2">
                                        <i class="bi {{ $iconClass }} me-1"></i>
                                        {{ $pedido['estadoNombre'] ?? 'En Proceso' }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('user.pedidos.show', $pedido['pedId']) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                       title="Ver detalles">
                                        Ver Seguimiento <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                        <h5 class="fw-normal">No tienes pedidos registrados</h5>
                                        <p class="small">¡Comienza a crear tu joya única hoy mismo!</p>
                                        <a href="{{ route('personalizar.index') }}" class="btn btn-primary btn-sm mt-2">
                                            Crear Nuevo Pedido
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Paginación (Visual por ahora) --}}
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <small class="text-muted">Mostrando {{ count($pedidos) }} pedidos</small>
                {{-- Si implementas paginación real en el futuro, aquí irían los links --}}
            </div>
        </div>
    </div>
</div>
@endsection