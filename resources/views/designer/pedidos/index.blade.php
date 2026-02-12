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
                            <strong class="pill-value">{{ $totalElements ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-clock-fill" style="color: #f59e0b;"></i>
                            <span class="pill-label">Pendientes:</span>
                            <strong class="pill-value">{{ $stats['pendientes'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-brush-fill text-success"></i>
                            <span class="pill-label">Diseño:</span>
                            <strong class="pill-value">{{ $stats['diseno'] ?? 0 }}</strong>
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
                @if(isset($pedidos) && count($pedidos) > 0)
                <div class="table-responsive">
                    <table class="table pedidos-table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Fecha Creación</th>
                                <th>Estado</th>
                                <th>Comentarios</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidos as $pedido)
                            <tr>
                                <td>
                                    <strong>{{ $pedido['pedCodigo'] ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2 text-muted"></i>
                                        {{ $pedido['nombreCliente'] ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    {{ $pedido['pedFechaCreacion'] ?? 'N/A' }}
                                </td>
                                <td>
                                    @if(isset($pedido['estadoNombre']))
                                        <span class="estado-badge estado-{{ Str::slug($pedido['estadoNombre'], '_') }}">
                                            {{ $pedido['estadoNombre'] }}
                                        </span>
                                    @else
                                        <span class="estado-badge estado-desconocido">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-truncate d-block" style="max-width: 200px;" 
                                          title="{{ $pedido['pedComentarios'] ?? 'Sin comentarios' }}">
                                        {{ $pedido['pedComentarios'] ?? 'Sin comentarios' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        {{-- Gestionar pedido (vista completa) --}}
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="gestionarPedido({{ $pedido['pedId'] ?? $pedido['id'] ?? 'N/A' }})"
                                                title="Gestionar pedido">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        
                                        {{-- Subir diseño si está en proceso --}}
                                        @if(isset($pedido['estadoNombre']) && in_array($pedido['estadoNombre'], ['Diseño en Proceso', 'Diseño Aprobado']))
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="subirDiseno({{ $pedido['pedId'] ?? $pedido['id'] ?? 'N/A' }})"
                                                title="Subir diseño/render">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                        @endif
                                        
                                        {{-- Contactar cliente --}}
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="contactarCliente({{ $pedido['pedId'] ?? $pedido['id'] ?? 'N/A' }})"
                                                title="Contactar cliente">
                                            <i class="bi bi-chat-dots"></i>
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
                            @for($i = 1; $i <= $totalPages; $i++)
                            <li class="page-item {{ $i == $currentPage + 1 ? 'active' : '' }}">
                                <a class="page-link" href="?page={{ $i - 1 }}&size={{ $pageSize }}">{{ $i }}</a>
                            </li>
                            @endfor
                        </ul>
                    </nav>
                </div>
                @endif
                
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No tienes pedidos asignados</h5>
                    <p class="text-muted">Cuando te asignen pedidos, aparecerán aquí para su gestión.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal para gestión de pedido --}}
<div class="modal fade" id="gestionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestión de Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="gestionContenido">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para subir diseño --}}
<div class="modal fade" id="subirDisenoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Diseño/Render</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="subirDisenoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="pedidoIdDiseno" name="pedidoId">
                    
                    <div class="mb-3">
                        <label for="disenoArchivo" class="form-label">Archivo de Diseño</label>
                        <input type="file" class="form-control" id="disenoArchivo" name="diseno_archivo" 
                               accept=".glb,.gltf,.png,.jpg,.jpeg,.webp" required>
                        <div class="form-text">
                            Formatos permitidos: GLB, GLTF, PNG, JPG, JPEG, WEBP (Máx. 50MB)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentariosDiseno" class="form-label">Comentarios</label>
                        <textarea class="form-control" id="comentariosDiseno" name="comentarios" rows="3" 
                                  placeholder="Describe los cambios o detalles del diseño..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarDiseno()">
                    <i class="bi bi-upload me-2"></i>Subir Diseño
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function gestionarPedido(pedidoId) {
    // Mostrar modal con spinner
    const modal = new bootstrap.Modal(document.getElementById('gestionModal'));
    document.getElementById('gestionContenido').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    modal.show();
    
    // Cargar vista de gestión completa (similar a admin)
    fetch(`/designer/pedidos/${pedidoId}/gestionar`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('gestionContenido').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('gestionContenido').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Error al cargar la vista de gestión del pedido.
                </div>
            `;
        });
}

function subirDiseno(pedidoId) {
    document.getElementById('pedidoIdDiseno').value = pedidoId;
    const modal = new bootstrap.Modal(document.getElementById('subirDisenoModal'));
    modal.show();
}

function guardarDiseno() {
    const form = document.getElementById('subirDisenoForm');
    const formData = new FormData(form);
    
    fetch(`/designer/pedidos/${formData.get('pedidoId')}/subir-diseno`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('subirDisenoModal')).hide();
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al subir el diseño'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión al subir el diseño'
        });
    });
}

function contactarCliente(pedidoId) {
    Swal.fire({
        title: 'Contactar Cliente',
        text: 'Esta función te permitirá comunicarte directamente con el cliente sobre su pedido.',
        icon: 'info',
        confirmButtonText: 'Entendido'
    });
}

// Filtros y búsqueda (misma lógica que usuario)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchCodigo');
    const estadoSelect = document.getElementById('filterEstado');
    const pageSizeSelect = document.getElementById('pageSize');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            aplicarFiltros();
        }, 300));
    }
    
    if (estadoSelect) {
        estadoSelect.addEventListener('change', aplicarFiltros);
    }
    
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', aplicarFiltros);
    }
});

function aplicarFiltros() {
    const params = new URLSearchParams(window.location.search);
    
    const search = document.getElementById('searchCodigo').value;
    const estado = document.getElementById('filterEstado').value;
    const pageSize = document.getElementById('pageSize').value;
    
    if (search) params.set('codigo', search);
    if (estado) params.set('estadoId', estado);
    if (pageSize) params.set('size', pageSize);
    
    window.location.href = '?' + params.toString();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush
