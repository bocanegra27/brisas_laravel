<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Brisas Gems</title>
    <link rel="icon" href="{{ asset('assets/img/icons/icono.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}" />
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

                        <form id="registroForm" onsubmit="handleRegistration(event)">
                            @csrf

                            <input type="hidden" id="anonymousTokenInput" name="anonymousToken"> 

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

                            {{-- Contraseña y Confirmación --}}
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
                                <button type="submit" class="btn btn-primary btn-lg" id="registerButton">Registrarse</button>
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
    <script>
        const STORAGE_SESION_TOKEN = 'anonymous_token';
        const API_BASE_URL = '{{ config("services.spring_api.url") }}'; // Usa la config de Laravel

        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem(STORAGE_SESION_TOKEN);
            if (token) {
                document.getElementById('anonymousTokenInput').value = token;
                console.log('✅ Token de sesión cargado para conversión:', token.substring(0, 8) + '...');
            }
        });

        async function handleRegistration(event) {
            event.preventDefault(); // Detener el envío de Laravel por defecto
            
            const registerButton = document.getElementById('registerButton');
            const token = document.getElementById('anonymousTokenInput').value;

            // 1. Recolectar datos del formulario
            const formData = {
                nombre: document.getElementById('nombre').value,
                correo: document.getElementById('correo').value,
                telefono: document.getElementById('telefono').value,
                tipdocId: parseInt(document.getElementById('tipdocId').value),
                docnum: document.getElementById('docnum').value,
                password: document.getElementById('password').value,
                rolId: 1 
            };
            
            // Si hay errores de validación, detenemos el proceso
            if (!formData.nombre || !formData.correo || !formData.password || !formData.tipdocId || !formData.docnum) {
                alert('Por favor, rellene todos los campos requeridos (*).');
                return;
            }

            registerButton.disabled = true;
            registerButton.textContent = 'Registrando...';
            
            try {
                let url = API_BASE_URL;
                let finalMessage = '';

                // 2. Determinar el Endpoint: Conversión o Registro Normal
                if (token) {
                    url += `/usuarios/registro/convertir/${token}`;
                    finalMessage = 'Cuenta creada y la trazabilidad histórica fue vinculada con éxito.';
                } else {
                    url += '/usuarios/crear'; 
                    finalMessage = 'Cuenta creada con éxito.';
                }
                
                // 3. Llamar a la API
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `Error HTTP ${response.status}`);
                }

                // 4. Éxito: Limpiar Local Storage y Redirigir
                if (token) {
                    localStorage.removeItem(STORAGE_SESION_TOKEN);
                }
                
                alert(finalMessage);
                window.location.href = '{{ route('login') }}'; 
                
            } catch (error) {
                // Mostrar los errores
                alert('Fallo en el registro: ' + error.message);
                registerButton.disabled = false;
                registerButton.textContent = 'Registrarse';
            }
        }
    </script>
</body>
</html>