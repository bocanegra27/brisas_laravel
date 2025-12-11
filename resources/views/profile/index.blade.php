@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        
        {{-- COLUMNA IZQUIERDA: DATOS DEL PERFIL --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-primary"><i class="bi bi-person-badge me-2"></i>Mi Información Personal</h5>
                </div>
                <div class="card-body p-4">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('perfil.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Nombre --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control" value="{{ $usuario['nombre'] }}" required>
                            </div>

                            {{-- Correo --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control" value="{{ $usuario['correo'] }}" required>
                            </div>

                            {{-- Teléfono --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Teléfono / Celular</label>
                                <input type="text" name="telefono" class="form-control" value="{{ $usuario['telefono'] }}">
                            </div>

                            {{-- Documento --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Número de Documento</label>
                                <input type="text" name="docnum" class="form-control" value="{{ $usuario['docnum'] }}">
                            </div>
                            
                            {{-- Rol (Solo lectura) --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Rol Actual</label>
                                <input type="text" class="form-control bg-light" value="{{ $usuario['rolNombre'] }}" readonly>
                                <small class="text-muted">Para cambiar tu rol, contacta a un administrador.</small>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: CAMBIAR CONTRASEÑA --}}
        <div class="col-lg-4">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold text-danger"><i class="bi bi-shield-lock me-2"></i>Seguridad</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('perfil.password') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="password_nueva" class="form-control" required minlength="8">
                            <div class="form-text">Mínimo 8 caracteres.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva</label>
                            <input type="password" name="password_nueva_confirmation" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-outline-danger">
                                Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection