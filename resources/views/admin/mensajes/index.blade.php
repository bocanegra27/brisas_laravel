@extends('layouts.app')

@section('title', 'Gestión de Mensajes - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/mensajes.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="mensajes-container">
    <div class="container-fluid py-5">
        {{-- Header con Stats Pills --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-chat-dots-fill me-3"></i>Gestión de Mensajes</h1>
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-envelope-fill text-primary"></i>
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
                            <span class="pill-label">Atendidos:</span>
                            <strong class="pill-value">{{ $stats['atendidos'] ?? 0 }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-archive-fill" style="color: #6b7280;"></i>
                            <span class="pill-label">Archivados:</span>
                            <strong class="pill-value">{{ $stats['archivados'] ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>
        </div>

        {{-- Mensajes de éxito/error --}}
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

        {{-- Tabla de mensajes --}}
        <div class="card mensajes-table-card animate-in animate-delay-5">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Mensajes</h5>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            {{-- Búsqueda --}}
                            <div class="col-md-4">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre, correo o mensaje...">
                                </div>
                            </div>
                            
                            {{-- Filtro por Tipo de Cliente --}}
                            <div class="col-md-3">
                                <select id="filterTipoCliente" class="form-select">
                                    <option value="">Todos los clientes</option>
                                    <option value="registrado" {{ (isset($filtros['tipoCliente']) && $filtros['tipoCliente'] == 'registrado') ? 'selected' : '' }}>
                                        Registrados
                                    </option>
                                    <option value="anonimo" {{ (isset($filtros['tipoCliente']) && $filtros['tipoCliente'] == 'anonimo') ? 'selected' : '' }}>
                                        Anónimos
                                    </option>
                                </select>
                            </div>
                            
                            {{-- Filtro por estado --}}
                            <div class="col-md-2">
                                <select id="filterEstado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" {{ (isset($filtros['estado']) && $filtros['estado'] === 'pendiente') ? 'selected' : '' }}>Pendientes</option>
                                    <option value="atendido" {{ (isset($filtros['estado']) && $filtros['estado'] === 'atendido') ? 'selected' : '' }}>Atendidos</option>
                                    <option value="archivado" {{ (isset($filtros['estado']) && $filtros['estado'] === 'archivado') ? 'selected' : '' }}>Archivados</option>
                                </select>
                            </div>
                            
                            {{-- NUEVO: Filtro por Personalización --}}
                            <div class="col-md-3">
                                <select id="filterPersonalizacion" class="form-select">
                                    <option value="">Con/Sin personalización</option>
                                    <option value="true" {{ (isset($filtros['tienePersonalizacion']) && $filtros['tienePersonalizacion'] === 'true') ? 'selected' : '' }}>
                                        Con personalización
                                    </option>
                                    <option value="false" {{ (isset($filtros['tienePersonalizacion']) && $filtros['tienePersonalizacion'] === 'false') ? 'selected' : '' }}>
                                        Sin personalización
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mensajes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                                <th> Tipo Cliente</th> {{-- Reemplaza "Vía" --}}
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th class="text-center"> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="mensajesTableBody">
                            @forelse($mensajes as $mensaje)
                            <tr class="mensaje-row {{ $mensaje['estado'] == 'archivado' ? 'archived-message' : '' }}" 
                                data-nombre="{{ strtolower($mensaje['nombre']) }}" 
                                data-correo="{{ strtolower($mensaje['correo']) }}"
                                data-mensaje="{{ strtolower($mensaje['mensaje']) }}">
                                
                                <td class="fw-bold">#{{ $mensaje['id'] }}</td>
                                
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-small">
                                            {{ strtoupper(substr($mensaje['nombre'], 0, 1)) }}
                                        </div>
                                        <span>{{ $mensaje['nombre'] }}</span>
                                    </div>
                                </td>
                                
                                <td>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-envelope-fill me-1"></i>{{ $mensaje['correo'] }}
                                    </small>
                                    <span class="fw-medium">
                                        <i class="bi bi-telephone-fill me-1"></i>{{ $mensaje['telefono'] }}
                                    </span>
                                </td>
                                
                                <td>
                                    <div class="mensaje-preview" data-bs-toggle="tooltip" title="{{ $mensaje['mensaje'] }}">
                                        {{ Str::limit($mensaje['mensaje'], 60) }}
                                    </div>
                                </td>
                                
                                <td>
                                    <small class="text-muted d-block">
                                        {{ \Carbon\Carbon::parse($mensaje['fechaEnvio'])->format('d/m/Y') }}
                                    </small>
                                    <span class="fw-medium">
                                        {{ \Carbon\Carbon::parse($mensaje['fechaEnvio'])->format('H:i') }}
                                    </span>
                                </td>
                                
                                {{-- Columna Tipo de Cliente --}}
                                <td>
                                    @if($mensaje['tipoCliente'] == 'registrado')
                                        <span class="badge-tipo-cliente badge-registrado">
                                            <i class="bi bi-person-check-fill"></i> Registrado
                                        </span>
                                    @else
                                        <span class="badge-tipo-cliente badge-anonimo">
                                            <i class="bi bi-incognito"></i> Anónimo
                                        </span>
                                    @endif
                                    
                                    {{-- Badge de personalización --}}
                                    @if($mensaje['tienePersonalizacion'])
                                        <span class="badge-personalizacion mt-1">
                                            <i class="bi bi-gem"></i> Con diseño
                                        </span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($mensaje['estado'] == 'pendiente')
                                        <span class="badge-estado badge-pendiente">
                                            <i class="bi bi-clock-fill"></i> Pendiente
                                        </span>
                                    @elseif($mensaje['estado'] == 'atendido')
                                        <span class="badge-estado badge-atendido">
                                            <i class="bi bi-check-circle-fill"></i> Atendido
                                        </span>
                                    @else
                                        <span class="badge-estado badge-archivado">
                                            <i class="bi bi-archive-fill"></i> Archivado
                                        </span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($mensaje['usuarioNombre'])
                                        <small class="text-muted d-block">Asociado:</small>
                                        <span class="fw-medium">{{ $mensaje['usuarioNombre'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    @if($mensaje['usuarioAdminNombre'])
                                        <small class="text-muted d-block mt-1">Admin:</small>
                                        <span class="fw-medium">{{ $mensaje['usuarioAdminNombre'] }}</span>
                                    @endif
                                </td>
                                
                                {{-- NUEVO: Acciones mejoradas --}}
                                <td>
                                    <div class="action-buttons">
                                        {{-- Ver detalles --}}
                                        <button onclick="verDetalleMejorado({{ $mensaje['id'] }}, {{ $mensaje['tienePersonalizacion'] ? 'true' : 'false' }})" 
                                                class="btn-action btn-view" 
                                                data-bs-toggle="tooltip" 
                                                title="Ver detalles">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        
                                        {{-- Cambiar estado --}}
                                        <button onclick="cambiarEstadoRapido({{ $mensaje['id'] }}, '{{ $mensaje['estado'] }}')" 
                                                class="btn-action btn-status" 
                                                data-bs-toggle="tooltip" 
                                                title="Cambiar estado">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        
                                        {{-- Crear pedido (SIEMPRE disponible) --}}
                                        <button onclick="crearPedidoDesdeMensaje({{ $mensaje['id'] }}, '{{ e($mensaje['nombre']) }}', {{ $mensaje['tienePersonalizacion'] ? 'true' : 'false' }})" 
                                                class="btn-action btn-pedido" 
                                                data-bs-toggle="tooltip" 
                                                title="Crear pedido">
                                            <i class="bi bi-cart-plus-fill"></i>
                                        </button>
                                        
                                        {{-- Eliminar --}}
                                        <button onclick="eliminarMensaje({{ $mensaje['id'] }}, '{{ e($mensaje['nombre']) }}')" 
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
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No hay mensajes registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal simplificado de detalles --}}
<div class="modal fade" id="modalDetalleMejorado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-envelope-open-fill me-2"></i>Detalles del Mensaje
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetalleContenido">
                {{-- Contenido dinámico cargado por JS --}}
            </div>
        </div>
    </div>
</div>

{{-- Modal de personalización --}}
<div class="modal fade" id="modalPersonalizacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-gem me-2"></i>Personalización Vinculada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalPersonalizacionContenido">
                {{-- Contenido dinámico --}}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/mensajes.js') }}"></script>
<script>
    // Inicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    
    //  Aplicar filtros con recarga
    document.getElementById('filterTipoCliente')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filterPersonalizacion')?.addEventListener('change', aplicarFiltros);
    
    function aplicarFiltros() {
        const tipoCliente = document.getElementById('filterTipoCliente')?.value || '';
        const estado = document.getElementById('filterEstado')?.value || '';
        const tienePersonalizacion = document.getElementById('filterPersonalizacion')?.value || '';
        
        const url = new URL(window.location.href);
        url.searchParams.set('tipoCliente', tipoCliente);
        url.searchParams.set('estado', estado);
        url.searchParams.set('tienePersonalizacion', tienePersonalizacion);
        
        window.location.href = url.toString();
    }
</script>
@endpush