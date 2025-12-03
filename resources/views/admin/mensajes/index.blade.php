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
        
        {{-- Header Section --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-chat-dots-fill me-3"></i>Gestión de Mensajes</h1>
                    <p class="mb-0">Administra todos los mensajes y formularios de contacto</p>
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

        {{-- Estadísticas --}}
        <div class="row g-4 mb-5">
            <div class="col-md-3 animate-in animate-delay-1">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper bg-primary-gradient">
                                <i class="bi bi-envelope-fill text-white"></i>
                            </div>
                            <p class="card-text">Total Mensajes</p>
                            <h2 class="display-4 text-primary">{{ $stats['total'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 animate-in animate-delay-2">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper bg-warning-gradient">
                                <i class="bi bi-clock-fill text-white"></i>
                            </div>
                            <p class="card-text">Pendientes</p>
                            <h2 class="display-4" style="color: #f59e0b;">{{ $stats['pendientes'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 animate-in animate-delay-3">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper bg-success-gradient">
                                <i class="bi bi-check-circle-fill text-white"></i>
                            </div>
                            <p class="card-text">Atendidos</p>
                            <h2 class="display-4 text-success">{{ $stats['atendidos'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 animate-in animate-delay-4">
                <div class="stat-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="icon-wrapper" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                                <i class="bi bi-archive-fill text-white"></i>
                            </div>
                            <p class="card-text">Archivados</p>
                            <h2 class="display-4" style="color: #6b7280;">{{ $stats['archivados'] ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de mensajes --}}
        <div class="card mensajes-table-card animate-in animate-delay-5">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Mensajes</h5>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-3">
                            {{-- Búsqueda --}}
                            <div class="col-md-6">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre, correo o mensaje...">
                                </div>
                            </div>
                            {{-- Filtro por vía --}}
                            <div class="col-md-3">
                                <select id="filterVia" class="form-select">
                                    <option value="">Todas las vías</option>
                                    <option value="formulario" {{ (isset($filtros['via']) && $filtros['via'] == 'formulario') ? 'selected' : '' }}>Formulario</option>
                                    <option value="whatsapp" {{ (isset($filtros['via']) && $filtros['via'] == 'whatsapp') ? 'selected' : '' }}>WhatsApp</option>
                                </select>
                            </div>
                            {{-- Filtro por estado --}}
                            <div class="col-md-3">
                                <select id="filterEstado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" {{ (isset($filtros['estado']) && $filtros['estado'] === 'pendiente') ? 'selected' : '' }}>Pendientes</option>
                                    <option value="atendido" {{ (isset($filtros['estado']) && $filtros['estado'] === 'atendido') ? 'selected' : '' }}>Atendidos</option>
                                    <option value="archivado" {{ (isset($filtros['estado']) && $filtros['estado'] === 'archivado') ? 'selected' : '' }}>Archivados</option>
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
                                <th>Vía</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th class="text-center">Acciones</th>
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
                                    <small class="text-muted d-block">{{ \Carbon\Carbon::parse($mensaje['fechaEnvio'])->format('d/m/Y') }}</small>
                                    <span class="fw-medium">{{ \Carbon\Carbon::parse($mensaje['fechaEnvio'])->format('H:i') }}</span>
                                </td>
                                <td>
                                    @if($mensaje['via'] == 'formulario')
                                        <span class="badge-via badge-via-formulario">
                                            <i class="bi bi-envelope-fill"></i> Formulario
                                        </span>
                                    @else
                                        <span class="badge-via badge-via-whatsapp">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
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
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="verDetalle({{ $mensaje['id'] }})" 
                                                class="btn-action btn-view" 
                                                data-bs-toggle="tooltip" 
                                                title="Ver detalles">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        
                                        <button onclick="cambiarEstadoRapido({{ $mensaje['id'] }}, '{{ $mensaje['estado'] }}')" 
                                                class="btn-action btn-status" 
                                                data-bs-toggle="tooltip" 
                                                title="Cambiar estado">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                        
                                        <button onclick="eliminarMensaje({{ $mensaje['id'] }}, '{{ addslashes($mensaje['nombre']) }}')" 
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
                                    <p class="text-muted">No hay mensajes registrados</p>
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

{{-- Modal de Detalles --}}
@include('admin.mensajes._modal_detalle')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script src="{{ asset('assets/js/mensajes.js') }}"></script>
<script>
    // Inicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
@endpush