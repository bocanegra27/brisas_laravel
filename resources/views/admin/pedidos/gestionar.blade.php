@extends('layouts.app')

@section('title', 'Gestionar Pedido #' . $pedido['pedCodigo'])

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado con Botón Volver --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Gestionar Pedido <span class="text-primary">#{{ $pedido['pedCodigo'] }}</span></h2>
            <p class="text-muted mb-0">
                Cliente: <strong>{{ $pedido['clienteDetalles']['nombre'] ?? 'Anónimo' }}</strong> | 
                Fecha: {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('d/m/Y h:i A') }}
            </p>
        </div>
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Listado
        </a>
    </div>

    <div class="row g-4">
        
        {{-- COLUMNA IZQUIERDA: Formulario de Acción --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px; z-index: 100;">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Actualizar Estado</h5>
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
                                      placeholder="Describe el avance (ej: Se terminó el pulido, adjunto foto)...">{{ $pedido['pedComentarios'] ?? '' }}</textarea>
                        </div>

                        {{-- Carga de Imagen --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Evidencia Fotográfica</label>
                            <input type="file" id="evidenciaImagen" class="form-control" accept="image/*">
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Sube una foto para que el cliente vea el progreso.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-save me-2"></i>Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>

            {{-- Información Adicional --}}
            @if(isset($pedido['personalizacion']) || isset($pedido['conId']))
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Detalles Relacionados</h6>
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
                </div>
            </div>
            @endif
        </div>

        {{-- COLUMNA DERECHA: Tabla de Historial --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Movimientos</h5>
                    <span class="badge bg-light text-dark border">{{ count($historial) }} registros</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20%;">Fecha</th>
                                    <th style="width: 20%;">Estado</th>
                                    <th style="width: 35%;">Comentarios</th>
                                    <th style="width: 15%;">Responsable</th>
                                    <th style="width: 10%;" class="text-center">Evidencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historial as $evento)
                                    <tr>
                                        {{-- Fecha --}}
                                        <td class="small text-secondary">
                                            {{ \Carbon\Carbon::parse($evento['hisFechaCambio'])->format('d/m/Y') }}<br>
                                            {{ \Carbon\Carbon::parse($evento['hisFechaCambio'])->format('h:i A') }}
                                        </td>
                                        
                                        {{-- Estado --}}
                                        <td>
                                            <span class="badge rounded-pill bg-info text-dark border border-info-subtle">
                                                {{ $estados[$evento['estId']] ?? 'Estado ' . $evento['estId'] }}
                                            </span>
                                        </td>

                                        {{-- Comentarios --}}
                                        <td>
                                            <p class="mb-0 small text-truncate-3" style="max-width: 300px;">
                                                {{ $evento['hisComentarios'] ?? 'Sin comentarios' }}
                                            </p>
                                        </td>

                                        {{-- Responsable --}}
                                        <td class="small">
                                            <i class="bi bi-person me-1"></i>{{ $evento['responsableNombre'] ?? 'Sistema' }}
                                        </td>

                                        {{-- 🔥 IMAGEN / EVIDENCIA --}}
                                        <td class="text-center">
                                            @if(!empty($evento['hisImagen']))
                                                {{-- Usamos el puerto 8080 del API asumiendo que es local. 
                                                     Si está en producción, cambia la URL base. --}}
                                                @php
                                                    // Limpiamos la ruta por si viene con doble slash
                                                    $rutaImagen = $evento['hisImagen'];
                                                    if(str_starts_with($rutaImagen, '/')) $rutaImagen = substr($rutaImagen, 1);
                                                    $urlImagen = "http://127.0.0.1:8080/" . $rutaImagen;
                                                @endphp
                                                
                                                <a href="{{ $urlImagen }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver foto">
                                                    <i class="bi bi-image"></i> Ver
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-6 d-block mb-3"></i>
                                            No hay historial registrado para este pedido.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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