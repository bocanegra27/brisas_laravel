@extends('layouts.app')
@section('title', 'Detalle de Pedido')

@section('content')
<div class="container py-5">
    
    {{-- Botón Volver --}}
    <div class="mb-4">
        <a href="{{ route('user.pedidos.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-2"></i>Volver a mis pedidos
        </a>
    </div>

    <div class="row">
        {{-- COLUMNA IZQUIERDA: Info del Pedido --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0">Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Código de seguimiento</label>
                        {{-- Protección ?? por si no llega el código --}}
                        <div class="fw-bold fs-5 text-primary">#{{ $pedido['pedCodigo'] ?? 'SIN-CODIGO' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Estado Actual</label>
                        <div>
                            <span class="badge bg-info text-dark fs-6">
                                {{ $pedido['estadoNombre'] ?? 'Desconocido' }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Descripción</label>
                        <p class="mb-0">{{ $pedido['pedComentarios'] ?? 'Sin descripción disponible.' }}</p>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Estimado</span>
                            {{-- Protección para el precio (si es null, pone 0) --}}
                            <span class="fw-bold fs-5">${{ number_format($pedido['pedCostoTotal'] ?? 0, 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Timeline Vertical --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Historial de Progreso</h4>

                    @if(empty($historial))
                        <div class="alert alert-light text-center">
                            No hay registros de historial para este pedido aún.
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($historial as $evento)
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold text-primary mb-0">
                                                {{ $evento['estadoNombre'] ?? 'Estado' }}
                                            </h6>
                                            <small class="text-muted ms-2">
                                                @if(isset($evento['hisFechaCambio']))
                                                    {{ \Carbon\Carbon::parse($evento['hisFechaCambio'])->format('d M Y, h:i A') }}
                                                @else
                                                    --/--/--
                                                @endif
                                            </small>
                                        </div>
                                        
                                        <p class="mb-2 text-dark">
                                            {{ $evento['hisComentarios'] ?? 'Estado actualizado.' }}
                                        </p>
                                        
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="bi bi-person-circle me-1"></i>
                                            <span>Por: {{ $evento['responsableNombre'] ?? 'Sistema' }}</span>
                                        </div>

                                        {{-- 🔥 CORRECCIÓN: MOSTRAR IMAGEN DESDE SERVIDOR JAVA --}}
                                        @if(!empty($evento['hisImagen']))
                                            <div class="mt-3">
                                                @php
                                                    // Construimos la URL completa apuntando al backend Java (puerto 8080)
                                                    $rutaImagen = $evento['hisImagen'];
                                                    if(str_starts_with($rutaImagen, '/')) $rutaImagen = substr($rutaImagen, 1);
                                                    $urlImagen = "http://127.0.0.1:8080/" . $rutaImagen;
                                                @endphp

                                                <a href="{{ $urlImagen }}" target="_blank">
                                                    <img src="{{ $urlImagen }}" class="img-fluid rounded border shadow-sm" style="max-height: 250px;" alt="Evidencia">
                                                </a>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos del Timeline Vertical */
    .timeline {
        position: relative;
        padding-left: 30px;
        border-left: 2px solid #e9ecef;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2.5rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-marker {
        position: absolute;
        left: -37px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #009688;
        border: 3px solid #fff;
        box-shadow: 0 0 0 2px #009688;
    }
    .timeline-content {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        position: relative;
    }
    .timeline-content::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 6px;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid #f8f9fa;
    }
</style>
@endsection