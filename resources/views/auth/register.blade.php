<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Brisas Gems</title>
    <link rel="icon" href="{{ asset('assets/img/icons/icono.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
    <style>
        :root { --bs-primary: #009688; }
        body { background-color: #f0f2f5; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .btn-primary { background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .btn-primary:hover { background-color: #00796b; border-color: #00796b; }
        .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card p-4">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Crear una Cuenta</h2>
                        <p class="text-center text-muted mb-4">Únete a Brisas Gems y personaliza tus joyas</p>

                        {{-- Errores de validación --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Formulario de Registro -->
                        <form action="{{ route('register.handle') }}" method="POST">
                            @csrf

                            {{-- Información Personal --}}
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

                            {{-- Documento de Identidad --}}
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

                            {{-- Contacto --}}
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

                            {{-- Contraseña --}}
                            <div class="row">
                                <div class="col-md-12 mb-4">
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
                                    <small class="form-text text-muted">Debe tener al menos 8 caracteres</small>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>