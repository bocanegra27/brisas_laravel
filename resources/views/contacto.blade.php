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

/* ============================================
   RESUMEN DE PERSONALIZACIÓN (ESTILO SOBRIO/LISTA)
   ============================================ */
.resumen-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #009688; /* La única línea de color del contenedor */
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.resumen-box h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 1rem;
}

.resumen-list {
    display: flex;
    flex-direction: column;
}

.resumen-item {
    display: flex;
    align-items: center;
    padding: 0.8rem 0;
    border-bottom: 1px solid #f1f5f9; /* Línea separadora muy sutil entre datos */
    font-size: 0.95rem;
}

.resumen-item:last-child {
    border-bottom: none; /* Quitamos la línea al último elemento */
    padding-bottom: 0;
}

.item-label {
    color: #64748b;
    font-weight: 600;
    min-width: 140px; /* Ancho fijo para que los valores queden perfectamente alineados */
}

.item-value {
    color: #0f172a;
    font-weight: 600;
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

/* Botón enviar - Estilo Elegante Brisas Gems */
.btn-enviar {
    background-color: transparent !important;
    color: #009688 !important;
    border: 2px solid #009688 !important;
    font-weight: 700 !important;
    padding: 1rem 3rem;
    border-radius: 50px;
    font-size: 1.1rem;
    transition: all 0.3s ease !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-transform: none !important; /* Mantiene minúsculas */
}

.btn-enviar:hover {
    background-color: #009688 !important;
    color: white !important;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 150, 136, 0.3);
}

.btn-enviar i {
    transition: transform 0.3s ease;
}

.btn-enviar:hover i {
    transform: translateX(4px) translateY(-2px); /* Efecto de despegue al icono de avión */
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
                        
                        @if($resumen)
                        <div class="resumen-box">
                            <h5>Resumen de tu Diseño</h5>
                            
                            @php
                                // Parsea el bloque de texto gigante en líneas individuales
                                $lineas = explode("\n", $resumen);
                                $detalles = [];
                                $categoria = '';
                                
                                foreach($lineas as $linea) {
                                    $linea = trim($linea);
                                    if (str_starts_with($linea, 'CATEGORÍA:')) {
                                        $categoria = trim(str_replace('CATEGORÍA:', '', $linea));
                                    } elseif (str_starts_with($linea, '•')) {
                                        $detalles[] = trim(str_replace('•', '', $linea));
                                    }
                                }
                            @endphp

                            <div class="resumen-list mt-3">
                                @if($categoria)
                                <div class="resumen-item">
                                    <span class="item-label">Categoría:</span>
                                    <span class="item-value">{{ ucfirst(strtolower($categoria)) }}</span>
                                </div>
                                @endif

                                @foreach($detalles as $detalle)
                                    @php 
                                        $partes = explode(':', $detalle, 2); 
                                        $opcion = $partes[0] ?? '';
                                        $valor = $partes[1] ?? '';
                                    @endphp
                                    <div class="resumen-item">
                                        <span class="item-label">{{ trim($opcion) }}:</span>
                                        <span class="item-value">{{ trim($valor) }}</span>
                                    </div>
                                @endforeach
                            </div>
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
                                    value="{{ old('nombre', $usuario['nombre'] ?? '') }}"
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
                                    value="{{ old('correo', $usuario['correo'] ?? '') }}"
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
                                    value="{{ old('telefono', $usuario['telefono'] ?? '') }}"
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
                                    required>{{ old('mensaje') }}</textarea>
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
                                            Acepto los términos y condiciones y la política de privacidad
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
// CARGAR SESIÓN EN FORMULARIO DE CONTACTO (VERSION CON TOKEN)
// ============================================
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', async function() {
        @if(session()->has('user_id'))
            return;
        @endif

        const STORAGE_TOKEN = 'anonymous_sesion_token';
        const STORAGE_ID    = 'anonymous_sesion_id';
        const inputSesionId = document.getElementById('input-sesion-id');

        try {
            let sesToken = localStorage.getItem(STORAGE_TOKEN);
            let sesId    = localStorage.getItem(STORAGE_ID);

            if (!sesToken || !sesId) {
                // Inyectamos la URL centralizada desde Laravel hacia JavaScript
                const apiUrl = "{{ config('services.spring_api.url') }}/sesiones-anonimas";
                
                const res  = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                sesToken = data.sesToken;
                sesId    = String(data.sesId);
                localStorage.setItem(STORAGE_TOKEN, sesToken);
                localStorage.setItem(STORAGE_ID, sesId);
            }

            if (inputSesionId) {
                inputSesionId.value = sesId;
            }
        } catch (e) {
            console.warn('No se pudo crear/recuperar sesion anonima:', e);
        }
    });
})();
</script>
@endpush