@extends('layouts.app')

@section('title', 'Editar Usuario - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/usuarios.css') }}">
@endpush

@section('content')
<div class="usuarios-container">
    <div class="container py-5">
        
        {{-- Header --}}
        <div class="dashboard-header animate-in mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-pencil-square me-3"></i>Editar Usuario</h1>
                    <p class="mb-0">Actualiza la información del usuario</p>
                </div>
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver al listado
                </a>
            </div>
        </div>

        {{-- Mensajes de error --}}
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Info del usuario --}}
        <div class="card user-info-banner animate-in mb-4">
            <div class="card-body d-flex align-items-center gap-4">
                <div class="user-avatar-large">
                    {{ strtoupper(substr($usuario['nombre'], 0, 1)) }}
                </div>
                <div>
                    <h4 class="mb-1">{{ $usuario['nombre'] }}</h4>
                    <p class="text-muted mb-2">{{ $usuario['correo'] }}</p>
                    <div class="d-flex gap-2">
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
                        
                        @if($usuario['activo'])
                            <span class="badge-estado badge-activo">
                                <i class="bi bi-check-circle-fill"></i> Activo
                            </span>
                        @else
                            <span class="badge-estado badge-inactivo">
                                <i class="bi bi-x-circle-fill"></i> Inactivo
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulario --}}
        <div class="card form-card animate-in animate-delay-1">
            <div class="card-body p-5">
                <form action="{{ route('usuarios.update', $usuario['id']) }}" method="POST" id="formEditarUsuario">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        {{-- Información Personal --}}
                        <div class="col-12">
                            <h5 class="section-title">
                                <i class="bi bi-person-badge me-2"></i>Información Personal
                            </h5>
                        </div>

                        {{-- Nombre Completo --}}
                        <div class="col-md-6">
                            <label for="nombre" class="form-label required">Nombre Completo</label>
                            <div class="input-icon">
                                <i class="bi bi-person-fill"></i>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre', $usuario['nombre']) }}"
                                       placeholder="Ej: Juan Pérez"
                                       required
                                       minlength="3"
                                       maxlength="100">
                            </div>
                            @error('nombre')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Correo Electrónico --}}
                        <div class="col-md-6">
                            <label for="correo" class="form-label required">Correo Electrónico</label>
                            <div class="input-icon">
                                <i class="bi bi-envelope-fill"></i>
                                <input type="email" 
                                       class="form-control @error('correo') is-invalid @enderror" 
                                       id="correo" 
                                       name="correo" 
                                       value="{{ old('correo', $usuario['correo']) }}"
                                       placeholder="ejemplo@correo.com"
                                       required
                                       maxlength="100">
                            </div>
                            @error('correo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-6">
                            <label for="telefono" class="form-label required">Teléfono</label>
                            <div class="input-icon">
                                <i class="bi bi-telephone-fill"></i>
                                <input type="text" 
                                       class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="{{ old('telefono', $usuario['telefono']) }}"
                                       placeholder="3001234567"
                                       required
                                       pattern="[0-9]{10}"
                                       maxlength="10">
                            </div>
                            <small class="form-text text-muted">Debe contener exactamente 10 dígitos</small>
                            @error('telefono')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipo de Documento --}}
                        <div class="col-md-3">
                            <label for="tipdocId" class="form-label required">Tipo Documento</label>
                            <select class="form-select @error('tipdocId') is-invalid @enderror" 
                                    id="tipdocId" 
                                    name="tipdocId" 
                                    required>
                                <option value="">Seleccionar...</option>
                                <option value="1" {{ old('tipdocId', $usuario['tipdocId']) == '1' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="2" {{ old('tipdocId', $usuario['tipdocId']) == '2' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                <option value="3" {{ old('tipdocId', $usuario['tipdocId']) == '3' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                            @error('tipdocId')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Número de Documento --}}
                        <div class="col-md-3">
                            <label for="docnum" class="form-label required">Número Documento</label>
                            <input type="text" 
                                   class="form-control @error('docnum') is-invalid @enderror" 
                                   id="docnum" 
                                   name="docnum" 
                                   value="{{ old('docnum', $usuario['docnum']) }}"
                                   placeholder="1234567890"
                                   required
                                   maxlength="20">
                            @error('docnum')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Separador --}}
                        <div class="col-12"><hr class="my-2"></div>

                        {{-- Contraseña --}}
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center gap-3">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                                <div>
                                    <strong>Cambio de Contraseña:</strong> 
                                    <span class="text-muted">Esta funcionalidad estará disponible próximamente. Por ahora, la contraseña se mantiene sin cambios.</span>
                                </div>
                            </div>
                        </div>

                        {{-- Separador --}}
                        <div class="col-12"><hr class="my-2"></div>

                        {{-- Rol y Estado --}}
                        <div class="col-12">
                            <h5 class="section-title">
                                <i class="bi bi-shield-fill-check me-2"></i>Rol y Estado
                            </h5>
                        </div>

                        {{-- Rol --}}
                        <div class="col-md-6">
                            <label for="rolId" class="form-label required">Rol del Usuario</label>
                            <select class="form-select @error('rolId') is-invalid @enderror" 
                                    id="rolId" 
                                    name="rolId" 
                                    required>
                                <option value="">Seleccionar rol...</option>
                                <option value="1" {{ old('rolId', $usuario['rolId']) == '1' ? 'selected' : '' }}>
                                    Usuario (Cliente)
                                </option>
                                <option value="2" {{ old('rolId', $usuario['rolId']) == '2' ? 'selected' : '' }}>
                                    Administrador
                                </option>
                                <option value="3" {{ old('rolId', $usuario['rolId']) == '3' ? 'selected' : '' }}>
                                    Diseñador
                                </option>
                            </select>
                            @error('rolId')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-6">
                            <label class="form-label required">Estado del Usuario</label>
                            <div class="estado-toggle-container">
                                <label class="estado-toggle">
                                    <input type="checkbox" 
                                           name="activo" 
                                           id="activo" 
                                           value="1" 
                                           {{ old('activo', $usuario['activo']) == '1' || old('activo', $usuario['activo']) === true ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                    <span class="label-text">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <span id="estadoTexto">{{ $usuario['activo'] ? 'Usuario Activo' : 'Usuario Inactivo' }}</span>
                                    </span>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                {{ $usuario['activo'] ? 'El usuario puede acceder al sistema' : 'El usuario no puede acceder al sistema' }}
                            </small>
                        </div>

                        {{-- Información adicional --}}
                        <div class="col-12">
                            <div class="info-box">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1">ID de Usuario</small>
                                        <strong>#{{ $usuario['id'] }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1">Origen de Registro</small>
                                        <strong>{{ ucfirst($usuario['origen']) }}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1">Documento</small>
                                        <strong>{{ $usuario['tipdocNombre'] }} - {{ $usuario['docnum'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="col-12 mt-5">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-create btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle estado text
    document.getElementById('activo').addEventListener('change', function() {
        const texto = document.getElementById('estadoTexto');
        if (this.checked) {
            texto.textContent = 'Usuario Activo';
        } else {
            texto.textContent = 'Usuario Inactivo';
        }
    });

    // Solo números en teléfono
    document.getElementById('telefono').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
@endpush