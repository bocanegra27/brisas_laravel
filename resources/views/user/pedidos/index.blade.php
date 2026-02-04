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