@extends('layouts.app')

@section('title', 'Mis Pedidos Asignados - Brisas Gems')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
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
                        <div class="pill-stat">
                            <i class="bi bi-clock-fill" style="color: #f59e0b;"></i>
                            <span class="pill-label">Pendientes:</span>
                            <strong class="pill-value">{{ $stats['pendientes'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="pill-label">Confirmados:</span>
                            <strong class="pill-value">{{ $stats['confirmados'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-gear-fill" style="color: #3b82f6;"></i>
                            <span class="pill-label">Producción:</span>
                            <strong class="pill-value">{{ $stats['produccion'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-box-seam-fill" style="color: #10b981;"></i>
                            <span class="pill-label">Entregados:</span>
                            <strong class="pill-value">{{ $stats['entregados'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
                <a href="{{ route('designer.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                </a>
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
        <div class="card shadow-sm animate-in">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center g-3">
                    {{-- Buscador --}}
                    <div class="col-md-5">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchCodigo" class="form-control" 
                                   placeholder="Buscar por código de pedido..."
                                   value="{{ $filtros['codigo'] ?? '' }}">
                        </div>
                    </div>
                    
                    {{-- Filtro por estado --}}
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
                    
                    {{-- Tamaño de página --}}
                    <div class="col-md-3">
                        <select id="pageSize" class="form-select">
                            <option value="10" {{ $pageSize == 10 ? 'selected' : '' }}>10 por página</option>
                            <option value="25" {{ $pageSize == 25 ? 'selected' : '' }}>25 por página</option>
                            <option value="50" {{ $pageSize == 50 ? 'selected' : '' }}>50 por página</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                @if($pedidos && count($pedidos) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                            <tr>
                                <td>
                                    <strong>#{{ $pedido['pedId'] ?? 'N/A' }}</strong>
                                    @if(!empty($pedido['pedIdentificadorCliente']))
                                    <br><small class="text-muted">{{ $pedido['pedIdentificadorCliente'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ strtoupper(substr($pedido['clienteDetalles']['nombre'] ?? 'NN', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $pedido['clienteDetalles']['nombre'] ?? 'Sin nombre' }}</div>
                                            @if(!empty($pedido['clienteDetalles']['correo']))
                                            <small class="text-muted">{{ $pedido['clienteDetalles']['correo'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge estado-badge estado-{{ $pedido['estado']['estId'] ?? 1 }}">
                                        {{ $pedido['estado']['estNombre'] ?? 'Desconocido' }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ date('d/m/Y', strtotime($pedido['pedFechaCreacion'] ?? 'now')) }}</div>
                                    <small class="text-muted">{{ date('H:i', strtotime($pedido['pedFechaCreacion'] ?? 'now')) }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('designer.pedidos.gestionar', $pedido['pedId']) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Gestionar pedido">
                                            <i class="bi bi-gear"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalles({{ $pedido['pedId'] }})"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($totalPages > 1)
                <div class="card-footer bg-light">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            {{-- Página anterior --}}
                            @if($currentPage > 0)
                            <li class="page-item">
                                <a class="page-link" href="{{ url()->current() }}?page={{ $currentPage - 1 }}&size={{ $pageSize }}{{ isset($filtros['estadoId']) ? '&estadoId=' . $filtros['estadoId'] : '' }}{{ isset($filtros['codigo']) ? '&codigo=' . $filtros['codigo'] : '' }}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            @endif

                            {{-- Números de página --}}
                            @for($i = max(0, $currentPage - 2); $i <= min($totalPages - 1, $currentPage + 2); $i++)
                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ url()->current() }}?page={{ $i }}&size={{ $pageSize }}{{ isset($filtros['estadoId']) ? '&estadoId=' . $filtros['estadoId'] : '' }}{{ isset($filtros['codigo']) ? '&codigo=' . $filtros['codigo'] : '' }}">
                                    {{ $i + 1 }}
                                </a>
                            </li>
                            @endfor

                            {{-- Página siguiente --}}
                            @if($currentPage < $totalPages - 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ url()->current() }}?page={{ $currentPage + 1 }}&size={{ $pageSize }}{{ isset($filtros['estadoId']) ? '&estadoId=' . $filtros['estadoId'] : '' }}{{ isset($filtros['codigo']) ? '&codigo=' . $filtros['codigo'] : '' }}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </nav>
                </div>
                @endif

                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No tienes pedidos asignados</h4>
                    <p class="text-muted">No se encontraron pedidos para tu cuenta de diseñador.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

        {{-- Información de resultados --}}
        @if($pedidos && count($pedidos) > 0)
        <div class="text-center mt-3">
            <small class="text-muted">
                Mostrando {{ count($pedidos) }} de {{ $totalElements }} pedidos 
                @if($totalPages > 1)
                (Página {{ $currentPage + 1 }} de {{ $totalPages }})
                @endif
            </small>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.js"></script>
<script>
// Variables globales
let currentPage = {{ $currentPage }};
let pageSize = {{ $pageSize }};
let totalPages = {{ $totalPages }};

// Aplicar filtros automáticamente
document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
document.getElementById('pageSize').addEventListener('change', aplicarFiltros);

// Búsqueda con debounce
let searchTimeout;
document.getElementById('searchCodigo').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(aplicarFiltros, 500);
});

function aplicarFiltros() {
    const estado = document.getElementById('filterEstado').value;
    const codigo = document.getElementById('searchCodigo').value.trim();
    const size = document.getElementById('pageSize').value;
    
    const params = new URLSearchParams();
    if (estado) params.append('estadoId', estado);
    if (codigo) params.append('codigo', codigo);
    params.append('size', size);
    params.append('page', 0); // Resetear a primera página
    
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

// Ver detalles del pedido
function verDetalles(pedidoId) {
    fetch(`/designer/pedidos/${pedidoId}/detalles`)
        .then(response => response.json())
        .then(data => {
            mostrarModalDetalles(data);
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los detalles del pedido'
            });
        });
}

// Mostrar modal con detalles
function mostrarModalDetalles(pedido) {
    const fechaCreacion = new Date(pedido.pedFechaCreacion).toLocaleString('es-ES');
    
    Swal.fire({
        title: `Detalles del Pedido #${pedido.pedId}`,
        html: `
            <div class="text-start">
                <div class="row mb-3">
                    <div class="col-6"><strong>ID Pedido:</strong></div>
                    <div class="col-6">#${pedido.pedId}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Cliente:</strong></div>
                    <div class="col-6">${pedido.clienteDetalles?.nombre || 'N/A'}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Correo:</strong></div>
                    <div class="col-6">${pedido.clienteDetalles?.correo || 'N/A'}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Estado:</strong></div>
                    <div class="col-6">
                        <span class="badge estado-badge estado-${pedido.estado?.estId || 1}">
                            ${pedido.estado?.estNombre || 'Desconocido'}
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Fecha Creación:</strong></div>
                    <div class="col-6">${fechaCreacion}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Identificador:</strong></div>
                    <div class="col-6">${pedido.pedIdentificadorCliente || 'N/A'}</div>
                </div>
                ${pedido.pedComentarios ? `
                <div class="row mb-3">
                    <div class="col-6"><strong>Comentarios:</strong></div>
                    <div class="col-6">${pedido.pedComentarios}</div>
                </div>
                ` : ''}
            </div>
        `,
        icon: 'info',
        width: '600px',
        confirmButtonText: 'Cerrar',
        showCloseButton: true
    });
}

// Animaciones al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Animar las stats pills
    const pills = document.querySelectorAll('.pill-stat');
    pills.forEach((pill, index) => {
        setTimeout(() => {
            pill.style.opacity = '0';
            pill.style.transform = 'translateY(20px)';
            pill.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                pill.style.opacity = '1';
                pill.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
    
    // Animar filas de la tabla
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        row.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, index * 50);
    });
});

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K para enfocar búsqueda
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('searchCodigo').focus();
    }
    
    // Esc para limpiar búsqueda
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('searchCodigo');
        if (searchInput.value && document.activeElement === searchInput) {
            searchInput.value = '';
            aplicarFiltros();
        }
    }
});
</script>

{{-- Estilos específicos para esta vista --}}
<style>
.estado-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    font-weight: 500;
}

.estado-1 { background-color: #6c757d; }
.estado-2 { background-color: #fd7e14; }
.estado-3 { background-color: #0dcaf0; }
.estado-4 { background-color: #20c997; }
.estado-5 { background-color: #0d6efd; }
.estado-6 { background-color: #6f42c1; }
.estado-7 { background-color: #d63384; }
.estado-8 { background-color: #198754; }
.estado-9 { background-color: #198754; }
.estado-10 { background-color: #dc3545; }

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
}

.search-box input {
    padding-left: 40px;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin: 0 0.125rem;
}

.table th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.card-footer {
    border-top: 1px solid #dee2e6;
}

.pagination .page-link {
    color: #0d6efd;
    border-color: #dee2e6;
}

.pagination .page-link:hover {
    color: #0a58ca;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush
