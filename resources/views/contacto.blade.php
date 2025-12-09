@extends('layouts.app')

@section('title', 'Contacto - Brisas Gems')

@push('styles')
<style>
/* ============================================
   FORMULARIO DE CONTACTO - BRISAS GEMS
   Estilo minimalista elegante
   ============================================ */

.contacto-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    padding: 4rem 0;
}

.contacto-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 3rem 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-left: 5px solid #009688;
    text-align: center;
}

.contacto-header h1 {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #009688 0%, #00796b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.contacto-header p {
    color: #64748b;
    font-size: 1.1rem;
}

/* Card del formulario */
.form-card {
    background: white;
    border: none;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    overflow: hidden;
}

.form-card .card-body {
    padding: 3rem;
}

/* Resumen de personalización */
.resumen-box {
    background: linear-gradient(135deg, rgba(0, 150, 136, 0.05) 0%, rgba(233, 30, 99, 0.05) 100%);
    border: 2px dashed #009688;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.resumen-box h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: #009688;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.resumen-content {
    white-space: pre-line;
    color: #1e293b;
    line-height: 1.8;
    font-size: 0.95rem;
}

/* Labels */
.form-label {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-label.required::after {
    content: '*';
    color: #ef4444;
    margin-left: 0.25rem;
}

/* Inputs */
.form-control, .form-select {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    padding: 0.875rem 1.25rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus, .form-select:focus {
    border-color: #009688;
    box-shadow: 0 0 0 4px rgba(0, 150, 136, 0.1);
}

.form-control.is-invalid {
    border-color: #ef4444;
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

/* Checkbox de términos */
.form-check {
    padding-left: 2rem;
}

.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    margin-top: 0.125rem;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #009688;
    border-color: #009688;
}

.form-check-input:focus {
    border-color: #009688;
    box-shadow: 0 0 0 4px rgba(0, 150, 136, 0.1);
}

.form-check-label {
    color: #64748b;
    font-size: 0.9rem;
    cursor: pointer;
}

.form-check-label a {
    color: #009688;
    text-decoration: none;
    font-weight: 600;
}

.form-check-label a:hover {
    text-decoration: underline;
}

/* Mensajes de error */
.invalid-feedback {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

.alert {
    border-radius: 16px;
    border: none;
    padding: 1.25rem 1.5rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
    border-left: 4px solid #22c55e;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-left: 4px solid #ef4444;
}

/* Botón enviar */
.btn-enviar {
    background: linear-gradient(135deg, #009688 0%, #00796b 100%);
    color: white;
    font-weight: 600;
    padding: 1rem 3rem;
    border-radius: 50px;
    border: none;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(0, 150, 136, 0.3);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-enviar:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 150, 136, 0.4);
}

.btn-enviar:active {
    transform: translateY(-1px);
}

.btn-enviar:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
.animate-delay-2 { animation-delay: 0.2s; opacity: 0; }

/* Responsive */
@media (max-width: 768px) {
    .contacto-header {
        padding: 2rem 1.5rem;
    }
    
    .contacto-header h1 {
        font-size: 1.75rem;
    }
    
    .form-card .card-body {
        padding: 2rem 1.5rem;
    }
    
    .btn-enviar {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@section('content')
<div class="contacto-container">
    <div class="container">
        
        <!-- Header -->
        <div class="contacto-header animate-in">
            <h1>Contáctanos</h1>
            <p>Completa tus datos y nos pondremos en contacto contigo pronto</p>
        </div>

        <!-- Mensajes flash -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-in animate-delay-1" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate-in animate-delay-1" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Formulario -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card animate-in animate-delay-2">
                    <div class="card-body">
                        
                        <!-- Resumen de personalización (si existe) -->
                        @if($resumen)
                        <div class="resumen-box">
                            <h5>
                                <i class="bi bi-gem"></i>
                                Tu Personalización
                            </h5>
                            <div class="resumen-content">{{ $resumen }}</div>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('contacto.store') }}" id="form-contacto">
                            @csrf

                            <!-- Campos ocultos -->
                            <input type="hidden" name="personalizacionId" value="{{ $personalizacionId }}">
                            <input type="hidden" name="sesionId" id="input-sesion-id">

                            <!-- Nombre -->
                            <div class="mb-4">
                                <label for="nombre" class="form-label required">Nombre completo</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('nombre') is-invalid @enderror" 
                                    id="nombre" 
                                    name="nombre" 
                                    value="{{ old('nombre') }}"
                                    placeholder="Ej: Juan Pérez"
                                    required>
                                @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Correo -->
                            <div class="mb-4">
                                <label for="correo" class="form-label required">Correo electrónico</label>
                                <input 
                                    type="email" 
                                    class="form-control @error('correo') is-invalid @enderror" 
                                    id="correo" 
                                    name="correo" 
                                    value="{{ old('correo') }}"
                                    placeholder="Ej: juan@example.com"
                                    required>
                                @error('correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="mb-4">
                                <label for="telefono" class="form-label required">Teléfono</label>
                                <input 
                                    type="tel" 
                                    class="form-control @error('telefono') is-invalid @enderror" 
                                    id="telefono" 
                                    name="telefono" 
                                    value="{{ old('telefono') }}"
                                    placeholder="Ej: 3001234567"
                                    required>
                                @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mensaje -->
                            <div class="mb-4">
                                <label for="mensaje" class="form-label required">Mensaje</label>
                                <textarea 
                                    class="form-control @error('mensaje') is-invalid @enderror" 
                                    id="mensaje" 
                                    name="mensaje" 
                                    placeholder="Cuéntanos más sobre lo que necesitas..."
                                    required>{{ old('mensaje', $resumen) }}</textarea>
                                @error('mensaje')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Términos y condiciones -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input @error('terminos') is-invalid @enderror" 
                                        type="checkbox" 
                                        id="terminos" 
                                        name="terminos"
                                        {{ old('terminos') ? 'checked' : '' }}
                                        required>
                                    <label class="form-check-label" for="terminos">
                                        Acepto los <a href="#" target="_blank">términos y condiciones</a> y la <a href="#" target="_blank">política de privacidad</a>
                                    </label>
                                    @error('terminos')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Botón enviar -->
                            <div class="d-grid">
                                <button type="submit" class="btn-enviar" id="btn-enviar">
                                    <i class="bi bi-send"></i>
                                    Enviar Mensaje
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ============================================
// CARGAR SESIÓN EN FORMULARIO DE CONTACTO
// ============================================
(function() {
    'use strict';
    
    const STORAGE_SESION_ID = 'brisas_sesion_id';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener sesionId del localStorage
        const sesionId = localStorage.getItem(STORAGE_SESION_ID);
        
        if (sesionId) {
            const inputSesionId = document.getElementById('input-sesion-id');
            if (inputSesionId) {
                inputSesionId.value = sesionId;
                console.log('✅ sesionId cargado en contacto:', sesionId);
            }
        } else {
            console.log('⚠️ No se encontró sesionId en localStorage');
        }
    });
    
})();
</script>
@endpush