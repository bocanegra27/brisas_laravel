@extends('layouts.app')

@section('title', 'Crear Usuario - Brisas Gems')

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
                    <h1><i class="bi bi-person-plus-fill me-3"></i>Crear Nuevo Usuario</h1>
                    <p class="mb-0">Completa el formulario para registrar un nuevo usuario</p>
                </div>
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
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

        {{-- Formulario --}}
        <div class="card form-card animate-in animate-delay-1">
            <div class="card-body p-5">
                <form action="{{ route('admin.usuarios.store') }}" method="POST" id="formCrearUsuario">
                    @csrf

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
                                       value="{{ old('nombre') }}"
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
                                       value="{{ old('correo') }}"
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
                                       value="{{ old('telefono') }}"
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
                                <option value="1" {{ old('tipdocId') == '1' ? 'selected' : '' }}>Cédula de Ciudadanía</option>
                                <option value="2" {{ old('tipdocId') == '2' ? 'selected' : '' }}>Cédula de Extranjería</option>
                                <option value="3" {{ old('tipdocId') == '3' ? 'selected' : '' }}>Pasaporte</option>
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
                                   value="{{ old('docnum') }}"
                                   placeholder="1234567890"
                                   required
                                   maxlength="20">
                            @error('docnum')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Separador --}}
                        <div class="col-12"><hr class="my-2"></div>

                        {{-- Credenciales de Acceso --}}
                        <div class="col-12">
                            <h5 class="section-title">
                                <i class="bi bi-key-fill me-2"></i>Credenciales de Acceso
                            </h5>
                        </div>

                        {{-- Contraseña --}}
                        <div class="col-md-6">
                            <label for="password" class="form-label required">Contraseña</label>
                            <div class="input-icon">
                                <i class="bi bi-lock-fill"></i>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mínimo 8 caracteres"
                                       required
                                       minlength="8"
                                       maxlength="100">
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')">
                                    <i class="bi bi-eye-fill" id="password-icon"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Debe contener al menos 8 caracteres</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Confirmar Contraseña --}}
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label required">Confirmar Contraseña</label>
                            <div class="input-icon">
                                <i class="bi bi-lock-fill"></i>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Repite la contraseña"
                                       required
                                       minlength="8"
                                       maxlength="100">
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password_confirmation')">
                                    <i class="bi bi-eye-fill" id="password_confirmation-icon"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Debe coincidir con la contraseña</small>
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
                                <option value="1" {{ old('rolId') == '1' ? 'selected' : '' }}>
                                    <i class="bi bi-person-fill"></i> Usuario (Cliente)
                                </option>
                                <option value="2" {{ old('rolId') == '2' ? 'selected' : '' }}>
                                    <i class="bi bi-shield-fill-check"></i> Administrador
                                </option>
                                <option value="3" {{ old('rolId') == '3' ? 'selected' : '' }}>
                                    <i class="bi bi-palette-fill"></i> Diseñador
                                </option>
                            </select>
                            @error('rolId')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-6">
                            <label class="form-label required">Estado Inicial</label>
                            <div class="estado-toggle-container">
                                <label class="estado-toggle">
                                    <input type="checkbox" 
                                           name="activo" 
                                           id="activo" 
                                           value="1" 
                                           {{ old('activo', '1') == '1' ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                    <span class="label-text">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <span id="estadoTexto">Usuario Activo</span>
                                    </span>
                                </label>
                            </div>
                            <small class="form-text text-muted">El usuario podrá acceder al sistema inmediatamente</small>
                        </div>

                        {{-- Botones --}}
                        <div class="col-12 mt-5">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-create btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Crear Usuario
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
    // Toggle password visibility
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        }
    }

    // Validar contraseñas coinciden
    document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Las contraseñas no coinciden. Por favor, verifica.');
            document.getElementById('password_confirmation').focus();
            return false;
        }
    });

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