@extends('layouts.app')

@section('title', 'Registro - Brisas Gems')

@push('styles')
<style>
    /* Variables de color de Brisas Gems */
    :root {
        --primary-color: #009688; 
        --primary-hover: #00796b;
    }

    /* Estilo del Botón Elegante (Igual al de Ingresar) */
    .btn-elegante {
        background-color: transparent !important;
        color: var(--primary-color) !important;
        border: 2px solid var(--primary-color) !important;
        padding: 12px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-elegante:hover {
        background-color: var(--primary-color) !important;
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 150, 136, 0.3);
        border-color: var(--primary-color) !important;
    }

    /* Mejora sutil para la tarjeta de registro */
    .card {
        border: none !important;
        border-radius: 20px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
    }

    /* Color de los links */
    .text-decoration-none.fw-bold {
        color: var(--primary-color) !important;
    }
</style>
@endpush

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card p-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Crear una Cuenta</h2>
                    <p class="text-center text-muted mb-4">Únete a Brisas Gems y personaliza tus joyas</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="registroForm" method="POST" action="{{ route('register.handle') }}">
                        @csrf
                        <input type="hidden" id="anonymousTokenInput" name="anonymousToken">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('nombre') is-invalid @enderror"
                                    id="nombre"
                                    name="nombre"
                                    value="{{ old('nombre') }}"
                                    placeholder="Ej: María García Rodríguez"
                                    required
                                    autofocus
                                >
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="correo" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    class="form-control @error('correo') is-invalid @enderror"
                                    id="correo"
                                    name="correo"
                                    value="{{ old('correo') }}"
                                    placeholder="ejemplo@correo.com"
                                    required
                                >
                                @error('correo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipdocId" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                                <select
                                    class="form-select @error('tipdocId') is-invalid @enderror"
                                    id="tipdocId"
                                    name="tipdocId"
                                    required
                                >
                                    <option value="">Seleccione...</option>
                                    <option value="1" {{ old('tipdocId') == 1 ? 'selected' : '' }}>Cédula de ciudadanía</option>
                                    <option value="2" {{ old('tipdocId') == 2 ? 'selected' : '' }}>Cédula de extranjería</option>
                                    <option value="3" {{ old('tipdocId') == 3 ? 'selected' : '' }}>Pasaporte</option>
                                    <option value="4" {{ old('tipdocId') == 4 ? 'selected' : '' }}>NIT</option>
                                </select>
                                @error('tipdocId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="docnum" class="form-label">Número de Documento <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    class="form-control @error('docnum') is-invalid @enderror"
                                    id="docnum"
                                    name="docnum"
                                    value="{{ old('docnum') }}"
                                    placeholder="Ej: 1012345678"
                                    required
                                >
                                @error('docnum')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="telefono" class="form-label">Teléfono (Opcional)</label>
                                <input
                                    type="tel"
                                    class="form-control @error('telefono') is-invalid @enderror"
                                    id="telefono"
                                    name="telefono"
                                    value="{{ old('telefono') }}"
                                    placeholder="Ej: 300 123 4567"
                                >
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                <input
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    minlength="8"
                                    placeholder="Mínimo 8 caracteres"
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    minlength="8"
                                    placeholder="Repite la contraseña"
                                    required
                                >
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                           <button type="submit" class="btn btn-elegante btn-lg" id="registerButton">
                                Registrarse
                            </button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                Al registrarte, aceptas nuestros términos y condiciones
                            </small>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">¿Ya tienes una cuenta?
                            <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Inicia sesión aquí</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('anonymous_sesion_token');
    if (token) {
        document.getElementById('anonymousTokenInput').value = token;
    }

    @if(session('success'))
        localStorage.removeItem('anonymous_sesion_token');
        localStorage.removeItem('anonymous_sesion_id');
    @endif
    });
</script>
@endpush