@extends('layouts.app')

@section('title', 'Gestionar Pedido #' . $pedido['pedCodigo'])

@section('content')
<div class="container-fluid py-4">

    {{-- BARRA SUPERIOR DE RESUMEN Y NAVEGACIÓN --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Gestión de Pedido <span class="text-primary fw-bold">#{{ $pedido['pedCodigo'] }}</span></h2>
            <p class="text-muted small">Creado el: {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('d/m/Y h:i A') }}</p>
        </div>
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    {{-- SECCIÓN DE ESTADO ACTUAL Y ASIGNACIÓN --}}
    <div class="row mb-5 g-4">
        
        {{-- CARD 1: ESTADO ACTUAL --}}
        <div class="col-md-4">
            <div class="card h-100 shadow border-start border-3 border-info">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-funnel me-2"></i>Estado Actual</h6>
                    <h3 class="card-title text-info">{{ $estados[$pedido['estId']] ?? 'Desconocido' }}</h3>
                    <p class="card-text small text-secondary">
                        Actualizado por última vez: {{ \Carbon\Carbon::parse($pedido['historial'][0]['hisFechaCambio'] ?? $pedido['pedFechaCreacion'])->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        {{-- CARD 2: CLIENTE --}}
        <div class="col-md-4">
            <div class="card h-100 shadow border-start border-3 border-success">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Cliente</h6>
                    <h4 class="card-title text-success">{{ $pedido['nombreCliente'] ?? 'Anónimo' }}</h4>
                    <p class="card-text small mb-0">
                        @if (isset($pedido['clienteDetalles']['usuCorreo']))
                            <i class="bi bi-envelope me-1"></i> {{ $pedido['clienteDetalles']['usuCorreo'] }}
                        @endif
                    </p>
                    <p class="card-text small">
                        @if (isset($pedido['clienteDetalles']['usuTelefono']))
                            <i class="bi bi-telephone me-1"></i> {{ $pedido['clienteDetalles']['usuTelefono'] }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- CARD 3: DISEÑADOR ASIGNADO --}}
        <div class="col-md-4">
            <div class="card h-100 shadow border-start border-3 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase fw-bold mb-3"><i class="bi bi-palette me-2"></i>Diseñador</h6>
                    <h4 class="card-title text-danger">{{ $pedido['nombreEmpleado'] ?? 'Pendiente de Asignar' }}</h4>
                    <p class="card-text small text-secondary">
                        @if (isset($pedido['usuIdEmpleado']))
                            ID Empleado: {{ $pedido['usuIdEmpleado'] }}
                        @else
                            Asigne un diseñador para iniciar el flujo de trabajo.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENIDO PRINCIPAL (FORMULARIO Y HISTORIAL) --}}
    <div class="row g-4">
        
        {{-- COLUMNA IZQUIERDA: Formulario de Acción (Sticky) --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4 sticky-top border-0" style="top: 20px; z-index: 100;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Actualizar Estado y Evidencia</h5>
                </div>
                <div class="card-body">
                    <form id="formCambiarEstado" enctype="multipart/form-data" onsubmit="return actualizarEstadoPedido(event, {{ $pedido['pedId'] }})">
                        @csrf
                        
                        {{-- Selección de Estado --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nuevo Estado</label>
                            <select id="nuevoEstadoSelect" class="form-select" required>
                                @foreach($estados as $id => $nombre)
                                    <option value="{{ $id }}" {{ $estadoId == $id ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Comentarios --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Comentarios / Observaciones</label>
                            <textarea id="comentariosEstado" class="form-control" rows="4" 
                                      placeholder="Describe el avance para el historial...">{{ $pedido['pedComentarios'] ?? '' }}</textarea>
                        </div>

                        {{-- Carga de Imagen --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Evidencia Fotográfica (Opcional)</label>
                            <input type="file" id="evidenciaImagen" class="form-control" accept="image/*">
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Sube una foto para registrar el avance visual.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
            
            {{-- Descripción del Pedido y Detalles --}}
            <div class="card shadow border-0 mt-4">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-chat-left-text me-2"></i>Descripción del Pedido</h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary">{{ $pedido['pedComentarios'] ?? 'No hay una descripción detallada registrada.' }}</p>
                    
                    @if(isset($pedido['personalizacion']) || isset($pedido['conId']))
                    <hr>
                    <h6 class="fw-bold mb-3">Enlaces Rápidos</h6>
                    @if(isset($pedido['personalizacion']))
                        <button class="btn btn-outline-info w-100 mb-2 text-start">
                            <i class="bi bi-gem me-2"></i> Ver Diseño Personalizado
                        </button>
                    @endif
                    @if(isset($pedido['conId']))
                        <a href="#" class="btn btn-outline-secondary w-100 text-start">
                            <i class="bi bi-envelope me-2"></i> Ver Mensaje Original
                        </a>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Historial de Movimientos (TIME-LINE) --}}
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-timeline me-2"></i>Historial de Movimientos (Timeline)</h5>
                </div>
                <div class="card-body pt-4">
                    
                    @if (empty($historial))
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-3"></i>
                            No hay historial registrado para este pedido.
                        </div>
                    @else
                        
                        {{-- Estructura de Timeline con clases Bootstrap (simulado) --}}
                        <div class="timeline-container">
                            @foreach($historial as $evento)
                            <div class="timeline-item d-flex mb-4">
                                {{-- Icono de Estado --}}
                                <div class="timeline-badge me-3">
                                    <i class="bi bi-circle-fill text-{{ $loop->first ? 'info' : 'secondary' }} small"></i>
                                </div>
                                
                                {{-- Contenido del Evento --}}
                                <div class="timeline-content flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="badge bg-{{ $loop->first ? 'info' : 'light text-secondary border' }} mb-1">
                                            {{ $estados[$evento['estId']] ?? 'Estado ' . $evento['estId'] }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($evento['hisFechaCambio'])->format('d/m/Y h:i A') }}
                                        </small>
                                    </div>
                                    
                                    <p class="fw-bold mb-1 mt-1">{{ $evento['hisComentarios'] ?? 'Sin comentarios' }}</p>
                                    
                                    <footer class="blockquote-footer small mt-1">
                                        Responsable: <cite title="Source Title">{{ $evento['responsableNombre'] ?? 'Sistema' }}</cite>
                                    </footer>

                                    {{-- Evidencia Fotográfica (Destacada) --}}
                                    @if(!empty($evento['hisImagen']))
                                        @php
                                            $rutaImagen = $evento['hisImagen'];
                                            if(str_starts_with($rutaImagen, '/')) $rutaImagen = substr($rutaImagen, 1);
                                            $urlImagen = "http://127.0.0.1:8080/" . $rutaImagen;
                                        @endphp
                                        <a href="{{ $urlImagen }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-image"></i> Ver Evidencia
                                        </a>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function actualizarEstadoPedido(event, pedidoId) {
        event.preventDefault();
        
        const estadoId = document.getElementById('nuevoEstadoSelect').value;
        const comentarios = document.getElementById('comentariosEstado').value;
        const imagenInput = document.getElementById('evidenciaImagen');
        
        let formData = new FormData();
        formData.append('estadoId', estadoId);
        formData.append('comentarios', comentarios);
        
        if (imagenInput.files.length > 0) {
            formData.append('imagen', imagenInput.files[0]);
        }

        Swal.fire({
            title: 'Guardando...',
            text: 'Actualizando estado y subiendo imagen...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Enviamos la petición POST (simulando PATCH)
        fetch(`/admin/pedidos/${pedidoId}/estado-historial`, { 
            method: 'POST', 
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-HTTP-Method-Override': 'PATCH'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status >= 400) throw new Error(body.message || 'Error en el servidor');

            if (body.success) {
                Swal.fire({
                    title: '¡Actualizado!',
                    text: 'El estado ha cambiado correctamente.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => window.location.reload());
            } else {
                throw new Error(body.message || 'Error desconocido.');
            }
        })
        .catch(error => {
            Swal.fire('Error', error.message, 'error');
        });
        
        return false;
    }
</script>
@endpush

{{-- 💡 ESTILOS ADICIONALES (Se recomienda poner esto en main.css) --}}
@push('styles')
<style>
/* Estilos para simular un Timeline simple en la columna derecha */
.timeline-container {
    position: relative;
    padding-left: 20px; /* Espacio para la línea vertical */
}
.timeline-container:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 2px;
    background-color: #dee2e6; /* Gris claro para la línea */
}
.timeline-item {
    position: relative;
    margin-left: 10px; /* Separación del punto a la línea */
    /* Usamos d-flex para alinear el punto con el contenido */
}
.timeline-badge {
    position: absolute;
    left: -18px; /* Ajusta para centrar el círculo en la línea */
    top: 5px;
    z-index: 10;
    background-color: #fff;
    padding: 2px;
    border-radius: 50%;
    border: 2px solid #fff; /* Pequeño borde para que destaque sobre la línea */
}
.timeline-content {
    background-color: #f8f9fa; /* Fondo suave para cada evento */
    padding: 10px 15px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}
</style>
@endpush