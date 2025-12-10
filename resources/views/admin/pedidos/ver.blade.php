@extends('layouts.app')

@section('title', 'Detalle del Pedido')

@section('content')

{{-- 
    🛡️ BLOQUE DE NORMALIZACIÓN DE DATOS 
    Esto evita el error "Undefined array key" si el API cambia entre camelCase y snake_case 
--}}
@php
    // 1. ID del Pedido
    $pedId = $pedido['pedId'] ?? $pedido['ped_id'] ?? $pedido['id'] ?? null;
    
    // 2. Código
    $pedCodigo = $pedido['pedCodigo'] ?? $pedido['ped_codigo'] ?? 'SIN-CODIGO';
    
    // 3. Fecha
    $fechaRaw = $pedido['pedFechaCreacion'] ?? $pedido['ped_fecha_creacion'] ?? now();
    $fecha = \Carbon\Carbon::parse($fechaRaw);

    // 4. Estado (Buscamos el objeto estado o el ID directo)
    $estId = $pedido['estado']['estId'] ?? $pedido['estado']['est_id'] ?? $pedido['est_id'] ?? $pedido['estId'] ?? 1;
    $estNombre = $pedido['estado']['est_nombre'] ?? $pedido['estadoNombre'] ?? 'Desconocido';
    
    // 5. Usuario/Cliente (Manejo seguro si viene null)
    $usuario = $pedido['usuario'] ?? null;
    $clienteNombre = $usuario['nombre'] ?? $pedido['pedIdentificadorCliente'] ?? 'Cliente Anónimo / Invitado';
    $clienteCorreo = $usuario['correo'] ?? 'Sin correo registrado';
    $clienteTel = $usuario['telefono'] ?? 'Sin teléfono';

    // 6. Comentarios
    $comentarios = $pedido['pedComentarios'] ?? $pedido['ped_comentarios'] ?? '';
    
    // 7. Detalles (Personalización)
    $detalles = $pedido['detalles'] ?? [];

    // Barra de progreso
    $porcentaje = ($estId / 10) * 100;
    $colorBarra = $estId == 10 ? 'danger' : 'success';
@endphp

<div class="container-fluid p-4">
    
    {{-- Header y Navegación --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.pedidos.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="bi bi-arrow-left"></i> Volver a pedidos
            </a>
            <h1 class="h3 fw-bold">
                Pedido <span class="text-primary">#{{ $pedCodigo }}</span>
            </h1>
            <span class="text-muted">Creado el {{ $fecha->format('d F, Y - H:i') }}</span>
        </div>
        
        <div class="text-end">
            {{-- Botón Debug (Solo visible en desarrollo para ver qué datos llegan realmente) --}}
            {{-- <button class="btn btn-sm btn-warning" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                <i class="bi bi-bug"></i> Debug
            </button> --}}
            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button>
        </div>
    </div>

    {{-- Debug Info (Oculto por defecto) --}}
    <div class="collapse mb-3" id="debugInfo">
        <div class="card card-body bg-dark text-white font-monospace small">
            @json($pedido)
        </div>
    </div>

    {{-- STEPPER DE ESTADO --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-4">
            <h6 class="text-uppercase text-muted fw-bold mb-3 small">Progreso del Pedido (Estado: {{ $estId }}/10)</h6>
            <div class="position-relative mb-2">
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-{{ $colorBarra }}" role="progressbar" style="width: {{ $porcentaje }}%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span>Solicitado</span>
                    <span>Diseño</span>
                    <span>Producción</span>
                    <span>Envíado</span>
                    <span>Entregado</span>
                </div>
            </div>
            <div class="text-center mt-3">
                <span class="badge bg-{{ $colorBarra }} fs-6 px-3 py-2">
                    {{ str_replace('_', ' ', $estNombre) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- COLUMNA IZQUIERDA: DETALLES DEL PRODUCTO --}}
        <div class="col-lg-7">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-primary"><i class="bi bi-gem me-2"></i>Detalles de la Joya</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light rounded p-4 text-center mb-4 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                        <div class="text-muted">
                            <i class="bi bi-card-image display-1"></i>
                            <p class="mt-2">Render de Personalización</p>
                            @if(isset($pedido['renderPath']) && $pedido['renderPath'])
                                <small>Ruta: {{ $pedido['renderPath'] }}</small>
                            @else
                                <small>(Sin imagen previa)</small>
                            @endif
                        </div>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Especificaciones Técnicas</h6>
                    
                    @if(count($detalles) > 0)
                        <div class="row">
                            @foreach($detalles as $detalle)
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">{{ $detalle['valNombre'] ?? $detalle['val_nombre'] ?? 'Característica' }}</label>
                                <div class="fs-5">{{ $detalle['opcionNombre'] ?? $detalle['opcion_nombre'] ?? 'Valor' }}</div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> No hay detalles de personalización vinculados.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: GESTIÓN Y CLIENTE --}}
        <div class="col-lg-5">
            
            {{-- 1. Panel de Cliente --}}
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 fw-bold"><i class="bi bi-person me-2"></i>Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            {{ substr($clienteNombre, 0, 1) }}
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $clienteNombre }}</h5>
                            <small class="text-muted">{{ $usuario ? 'Cliente Registrado' : 'Cliente Externo' }}</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0"><i class="bi bi-envelope me-2 text-muted"></i> {{ $clienteCorreo }}</li>
                        <li class="list-group-item px-0"><i class="bi bi-telephone me-2 text-muted"></i> {{ $clienteTel }}</li>
                    </ul>
                    <div class="mt-3">
                        <a href="#" class="btn btn-outline-primary w-100 btn-sm"><i class="bi bi-whatsapp"></i> Contactar por WhatsApp</a>
                    </div>
                </div>
            </div>

            {{-- 2. Panel de Gestión (AQUÍ ESTABA EL ERROR) --}}
            <div class="card shadow border-0 bg-light">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="m-0 fw-bold"><i class="bi bi-sliders me-2"></i>Gestión del Pedido</h5>
                </div>
                <div class="card-body">
                    {{-- Usamos la variable $pedId que calculamos al principio --}}
                    @if($pedId)
                        <form action="{{ route('admin.pedidos.update', $pedId) }}" method="POST">
                            @csrf
                            @method('PUT')
    
                            <div class="mb-3">
                                <label class="form-label fw-bold">Actualizar Estado</label>
                                <select name="estadoId" class="form-select form-select-lg shadow-none border-primary">
                                    @foreach($estados as $id => $nombre)
                                        <option value="{{ $id }}" {{ $estId == $id ? 'selected' : '' }}>
                                            {{ $id }} - {{ $nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
    
                            <div class="mb-3">
                                <label class="form-label fw-bold">Notas Internas</label>
                                <textarea name="comentarios" class="form-control" rows="3">{{ $comentarios }}</textarea>
                            </div>
    
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="bi bi-check-circle-fill me-2"></i> Guardar Cambios
                            </button>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            Error: No se pudo identificar el ID del pedido para actualizarlo.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection