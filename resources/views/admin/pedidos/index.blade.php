@extends('layouts.app') {{-- Asumiendo que este es tu layout principal --}}

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">📦 Pedidos</h1>
        <div class="d-flex gap-2">
            {{-- Filtro Rápido --}}
            <form action="{{ route('admin.pedidos.index') }}" method="GET" class="d-flex">
                <select name="estadoId" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $id => $nombre)
                        <option value="{{ $id }}" {{ $filtroEstado == $id ? 'selected' : '' }}>
                            {{ $id }} - {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th> <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
    @forelse($pedidos as $pedido)
    <tr>
        <td class="fw-bold text-primary">
            {{-- Usamos pedCodigo o pedId --}}
            #{{ $pedido['pedCodigo'] ?? $pedido['pedId'] }}
        </td>
        <td>
            <div class="d-flex flex-column">
                {{-- CORRECCIÓN: El JSON trae 'pedIdentificadorCliente' o a veces es null --}}
                <span class="fw-bold">
                    {{ $pedido['pedIdentificadorCliente'] ?? 'Cliente Anónimo / Web' }}
                </span>
                <small class="text-muted">
                    {{-- ID de Sesión o Contacto si no hay nombre --}}
                    @if(empty($pedido['pedIdentificadorCliente']))
                        Sesión: {{ $pedido['sesionId'] ?? 'N/A' }}
                    @endif
                </small>
            </div>
        </td>
        <td>
            {{-- CORRECCIÓN: Nombre de variable 'pedFechaCreacion' (camelCase) --}}
            {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'] ?? now())->format('d/m/Y H:i') }}
        </td>
        <td>
            {{-- CORRECCIÓN: El estado viene directo como string en 'estadoNombre' --}}
            @php
                $estadoNombre = $pedido['estadoNombre'] ?? 'Desconocido';
                // Asignar colores según el texto del estado
                $badgeColor = match(true) {
                    str_contains($estadoNombre, 'pendiente') => 'warning',
                    str_contains($estadoNombre, 'proceso') => 'primary',
                    str_contains($estadoNombre, 'entregado') => 'success',
                    str_contains($estadoNombre, 'cancelado') => 'danger',
                    default => 'secondary'
                };
            @endphp
            <span class="badge bg-{{ $badgeColor }} rounded-pill text-uppercase">
                {{ str_replace('_', ' ', $estadoNombre) }}
            </span>
        </td>
        <td>
            --
        </td>
        <td class="text-end">
            <a href="{{ route('admin.pedidos.ver', $pedido['pedId']) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-eye"></i> Gestionar
            </a>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-box-seam display-4"></i>
                <p class="mt-2">No hay pedidos registrados.</p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
                </table>
            </div>

            {{-- Paginación Simple --}}
            @if($pagination['totalPages'] > 1)
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination">
                        <li class="page-item {{ $pagination['number'] == 0 ? 'disabled' : '' }}">
                            <a class="page-link" href="?page={{ $pagination['number'] - 1 }}&estadoId={{ $filtroEstado }}">Anterior</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Página {{ $pagination['number'] + 1 }} de {{ $pagination['totalPages'] }}</span>
                        </li>
                        <li class="page-item {{ $pagination['number'] + 1 >= $pagination['totalPages'] ? 'disabled' : '' }}">
                            <a class="page-link" href="?page={{ $pagination['number'] + 1 }}&estadoId={{ $filtroEstado }}">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection