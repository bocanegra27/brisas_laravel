@extends('layouts.app')

@section('title', 'Gestion de Pedidos - Brisas Gems')

@push('styles')
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
                    <h1><i class="bi bi-cart-check-fill me-3"></i>Gestion de Pedidos</h1>
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
                            <span class="pill-label">Produccion:</span>
                            <strong class="pill-value">{{ $stats['produccion'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-box-seam-fill" style="color: #10b981;"></i>
                            <span class="pill-label">Entregados:</span>
                            <strong class="pill-value">{{ $stats['entregados'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
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
        <div class="card pedidos-table-card animate-in animate-delay-5">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Pedidos</h5>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            {{-- Busqueda por codigo --}}
                            <div class="col-md-5">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="searchCodigo" class="form-control" 
                                           placeholder="Buscar por codigo de pedido..."
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
                            
                            {{-- Tamano de pagina --}}
                            <div class="col-md-3">
                                <select id="pageSize" class="form-select">
                                    <option value="10" {{ $pageSize == 10 ? 'selected' : '' }}>10 por pagina</option>
                                    <option value="25" {{ $pageSize == 25 ? 'selected' : '' }}>25 por pagina</option>
                                    <option value="50" {{ $pageSize == 50 ? 'selected' : '' }}>50 por pagina</option>
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
                                <th>ID</th>
                                <th>Codigo</th>
                                <th>Cliente</th>
                                <th>Fecha Creacion</th>
                                <th>Estado</th>
                                <th>Personalizacion</th>
                                <th>Comentarios</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="pedidosTableBody">
                            @forelse($pedidos as $pedido)
                            <tr class="pedido-row">
                                <td class="fw-bold">#{{ $pedido['pedId'] }}</td>
                                
                                <td>
                                    <span class="codigo-pedido">{{ $pedido['pedCodigo'] }}</span>
                                </td>
                                
                                <td>
                                    @if(isset($pedido['usuario']) && $pedido['usuario'])
                                    <div class="user-info">
                                        <div class="user-avatar-small">
                                            {{ strtoupper(substr($pedido['usuario']['nombre'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="d-block">{{ $pedido['usuario']['nombre'] }}</span>
                                            <small class="text-muted">{{ $pedido['usuario']['correo'] }}</small>
                                        </div>
                                    </div>
                                    @elseif(isset($pedido['pedIdentificadorCliente']) && $pedido['pedIdentificadorCliente'])
                                    <span class="text-muted">{{ $pedido['pedIdentificadorCliente'] }}</span>
                                    @else
                                    <span class="text-muted">Sin asignar</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <small class="text-muted d-block">
                                        {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('d/m/Y') }}
                                    </small>
                                    <span class="fw-medium">
                                        {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('H:i') }}
                                    </span>
                                </td>
                                
                                <td>
                                    @php
                                        // Obtener estado ID y nombre con fallbacks
                                        $estadoId = $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1);
                                        $estadoNombre = $pedido['estado']['estNombre'] ?? ($pedido['estadoNombre'] ?? 'Desconocido');
                                        
                                        // Mapeo de badgeClass
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
                                        
                                        $iconClass = match($estadoId) {
                                            1 => 'bi-clock-fill',
                                            2 => 'bi-check-circle-fill',
                                            3 => 'bi-palette-fill',
                                            4 => 'bi-hand-thumbs-up-fill',
                                            5 => 'bi-gear-fill',
                                            6 => 'bi-shield-check-fill',
                                            7 => 'bi-box-seam-fill',
                                            8 => 'bi-truck',
                                            9 => 'bi-gift-fill',
                                            10 => 'bi-x-circle-fill',
                                            default => 'bi-circle-fill'
                                        };
                                    @endphp
                                    
                                    <span class="badge-estado {{ $badgeClass }}">
                                        <i class="bi {{ $iconClass }}"></i> {{ $estadoNombre }}
                                    </span>
                                </td>
                                
                                <td>
                                    @if((isset($pedido['personalizacion']) && $pedido['personalizacion']) || (isset($pedido['perId']) && $pedido['perId']))
                                    @php
                                        $perId = $pedido['personalizacion']['perId'] ?? ($pedido['perId'] ?? null);
                                    @endphp
                                    @if($perId)
                                    <button onclick="verPersonalizacion({{ $perId }})" 
                                            class="badge-personalizacion-btn">
                                        <i class="bi bi-gem"></i> Ver diseno
                                    </button>
                                    @else
                                    <span class="text-muted">Sin personalizacion</span>
                                    @endif
                                    @else
                                    <span class="text-muted">Sin personalizacion</span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($pedido['pedComentarios'])
                                    <div class="comentario-preview" data-bs-toggle="tooltip" 
                                         title="{{ $pedido['pedComentarios'] }}">
                                        {{ Str::limit($pedido['pedComentarios'], 50) }}
                                    </div>
                                    @else
                                    <span class="text-muted">Sin comentarios</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="action-buttons">
                                        {{-- Ver detalles --}}
                                        <a href="{{ route('admin.pedidos.ver', $pedido['pedId']) }}" 
                                           class="btn-action btn-view" 
                                           data-bs-toggle="tooltip" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        
                                        {{-- Cambiar estado --}}
                                        <button onclick="cambiarEstadoPedido({{ $pedido['pedId'] }}, {{ $estadoId }})" 
                                                class="btn-action btn-status" 
                                                data-bs-toggle="tooltip" 
                                                title="Cambiar estado">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        
                                        {{-- Editar --}}
                                        <button onclick="editarPedido({{ $pedido['pedId'] }})" 
                                                class="btn-action btn-edit" 
                                                data-bs-toggle="tooltip" 
                                                title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        
                                        {{-- Eliminar --}}
                                        <button onclick="eliminarPedido({{ $pedido['pedId'] }}, '{{ e($pedido['pedCodigo']) }}')" 
                                                class="btn-action btn-delete" 
                                                data-bs-toggle="tooltip" 
                                                title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No hay pedidos registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Footer con paginacion --}}
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
                                {{-- Primera pagina --}}
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page=0&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                
                                {{-- Anterior --}}
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $currentPage - 1 }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                {{-- Paginas numeradas --}}
                                @for($i = max(0, $currentPage - 2); $i <= min($totalPages - 1, $currentPage + 2); $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="?page={{ $i }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        {{ $i + 1 }}
                                    </a>
                                </li>
                                @endfor
                                
                                {{-- Siguiente --}}
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $currentPage + 1 }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                
                                {{-- Ultima pagina --}}
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $totalPages - 1 }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
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

{{-- Modal para cambiar estado --}}
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right me-2"></i>Cambiar Estado del Pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <input type="hidden" id="pedidoIdEstado" name="pedidoId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuevo Estado</label>
                        <select class="form-select" id="nuevoEstado" name="estadoId" required>
                            @foreach($estados as $estado)
                            <option value="{{ $estado['id'] }}">{{ $estado['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Los cambios de estado se registraran automaticamente en el historial del pedido.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">
                    <i class="bi bi-check-circle-fill me-2"></i>Cambiar Estado
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/pedidos.js') }}"></script>
<script>
    // Inicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    
    // Aplicar filtros
    document.getElementById('filterEstado')?.addEventListener('change', aplicarFiltros);
    document.getElementById('pageSize')?.addEventListener('change', aplicarFiltros);
    
    // Busqueda por codigo
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
</script>
@endpush