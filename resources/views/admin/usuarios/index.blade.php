@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/usuarios.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="usuarios-container">
    <div class="container-fluid py-5">
        
        {{-- Header Section --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-people-fill me-3"></i>Gestión de Usuarios</h1>
                    <p class="mb-0">Administra todos los usuarios del sistema</p>
                </div>
                <a href="{{ route('admin.usuarios.crear') }}" class="btn btn-create">
                    <i class="bi bi-plus-circle me-2"></i>Crear Usuario
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

        {{-- Tabla de usuarios --}}
        <div class="card usuarios-table-card animate-in animate-delay-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Usuarios</h5>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            {{-- Búsqueda --}}
                            <div class="col-md-5">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o correo...">
                                </div>
                            </div>
                            {{-- Filtro por rol --}}
                            <div class="col-md-3">
                                <select id="filterRol" class="form-select">
                                    <option value="">Todos los roles</option>
                                    <option value="1" {{ (isset($filtros['rolId']) && $filtros['rolId'] == '1') ? 'selected' : '' }}>Usuario</option>
                                    <option value="2" {{ (isset($filtros['rolId']) && $filtros['rolId'] == '2') ? 'selected' : '' }}>Administrador</option>
                                    <option value="3" {{ (isset($filtros['rolId']) && $filtros['rolId'] == '3') ? 'selected' : '' }}>Diseñador</option>
                                </select>
                            </div>
                            {{-- Filtro por estado --}}
                            <div class="col-md-4">
                                <select id="filterEstado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="true" {{ (isset($filtros['activo']) && $filtros['activo'] === 'true') ? 'selected' : '' }}>Activos</option>
                                    <option value="false" {{ (isset($filtros['activo']) && $filtros['activo'] === 'false') ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table usuarios-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Documento</th>
                                <th>Estado</th>
                                <th>Origen</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosTableBody">
                            @forelse($usuarios as $usuario)
                            <tr class="usuario-row {{ !$usuario['activo'] ? 'inactive-user' : '' }}" data-nombre="{{ strtolower($usuario['nombre']) }}" data-correo="{{ strtolower($usuario['correo']) }}">
                                <td class="fw-bold">#{{ $usuario['id'] }}</td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-small">
                                            {{ strtoupper(substr($usuario['nombre'], 0, 1)) }}
                                        </div>
                                        <span>{{ $usuario['nombre'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $usuario['correo'] }}</td>
                                <td>{{ $usuario['telefono'] }}</td>
                                <td>
                                    @if($usuario['rolId'] == 1)
                                        <span class="badge-rol badge-rol-user">
                                            <i class="bi bi-person-fill"></i> Usuario
                                        </span>
                                    @elseif($usuario['rolId'] == 2)
                                        <span class="badge-rol badge-rol-admin">
                                            <i class="bi bi-shield-fill-check"></i> Administrador
                                        </span>
                                    @elseif($usuario['rolId'] == 3)
                                        <span class="badge-rol badge-rol-designer">
                                            <i class="bi bi-palette-fill"></i> Diseñador
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted d-block">{{ $usuario['tipdocNombre'] ?? 'N/A' }}</small>
                                    <span class="fw-medium">{{ $usuario['docnum'] }}</span>
                                </td>
                                <td>
                                    @if($usuario['activo'])
                                        <span class="badge-estado badge-activo">
                                            <i class="bi bi-check-circle-fill"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge-estado badge-inactivo">
                                            <i class="bi bi-x-circle-fill"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-origen">{{ ucfirst($usuario['origen']) }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.usuarios.editar', $usuario['id']) }}" 
                                           class="btn-action btn-edit" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        
                                        <button onclick="toggleEstado({{ $usuario['id'] }}, {{ $usuario['activo'] ? 'false' : 'true' }})" 
                                                class="btn-action {{ $usuario['activo'] ? 'btn-deactivate' : 'btn-activate' }}" 
                                                data-bs-toggle="tooltip" 
                                                title="{{ $usuario['activo'] ? 'Desactivar' : 'Activar' }}">
                                            <i class="bi bi-{{ $usuario['activo'] ? 'slash-circle' : 'check-circle' }}-fill"></i>
                                        </button>
                                        
                                        <button onclick="eliminarUsuario({{ $usuario['id'] }}, '{{ addslashes($usuario['nombre']) }}')" 
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
                                    <p class="text-muted">No hay usuarios registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Paginación --}}
            @if($totalPages > 1)
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="pagination-info">
                            Mostrando 
                            <strong>{{ ($currentPage * $pageSize) + 1 }}</strong> - 
                            <strong>{{ min(($currentPage + 1) * $pageSize, $totalElements) }}</strong> 
                            de <strong>{{ $totalElements }}</strong> registros
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <select id="pageSize" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="10" {{ $pageSize == 10 ? 'selected' : '' }}>10 por página</option>
                            <option value="25" {{ $pageSize == 25 ? 'selected' : '' }}>25 por página</option>
                            <option value="50" {{ $pageSize == 50 ? 'selected' : '' }}>50 por página</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <nav>
                            <ul class="pagination justify-content-end mb-0">
                                {{-- Botón anterior --}}
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ route('admin.usuarios.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                {{-- Números de página --}}
                                @for($i = 0; $i < $totalPages; $i++)
                                    @if($i == 0 || $i == $totalPages - 1 || abs($i - $currentPage) <= 2)
                                        <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ route('admin.usuarios.index', array_merge(request()->query(), ['page' => $i])) }}">
                                                {{ $i + 1 }}
                                            </a>
                                        </li>
                                    @elseif(abs($i - $currentPage) == 3)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endfor

                                {{-- Botón siguiente --}}
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ route('admin.usuarios.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                                        <i class="bi bi-chevron-right"></i>
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

    {{-- Estadísticas --}}
        <div class="row g-4 mb-5">
            <div class="col-md-4 animate-in animate-delay-1">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper bg-primary-gradient">
                                <i class="bi bi-people text-white"></i>
                            </div>
                            <p class="card-text">Total Usuarios</p>
                            <h2 class="display-4 text-primary">{{ $stats['total'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate-in animate-delay-2">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper bg-success-gradient">
                                <i class="bi bi-check-circle text-white"></i>
                            </div>
                            <p class="card-text">Usuarios Activos</p>
                            <h2 class="display-4 text-success">{{ $stats['activos'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate-in animate-delay-3">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper bg-secondary" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;">
                                <i class="bi bi-x-circle text-white"></i>
                            </div>
                            <p class="card-text">Usuarios Inactivos</p>
                            <h2 class="display-4" style="color: #6b7280;">{{ $stats['inactivos'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/usuarios.js') }}"></script>
<script>
    // Inicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
@endpush