@extends('layouts.app')

@section('title', 'Gestionar Pedido - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/gestionar-pedido.css') }}">
@endpush

@section('content')
<div class="gestionar-pedido-container">
    <div class="container-fluid py-4">
        
        {{-- Header del pedido --}}
        <div class="pedido-header animate-in">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <a href="{{ route('admin.pedidos.index') }}" class="btn-back">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="pedido-codigo mb-2">{{ $pedido['pedCodigo'] }}</h1>
                            <p class="pedido-fecha mb-0">
                                <i class="bi bi-calendar3 me-2"></i>
                                Creado: {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'])->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    @php
                        $estadoId = $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1);
                        $estadoNombre = $pedido['estado']['estNombre'] ?? ($pedido['estadoNombre'] ?? 'Desconocido');
                        
                        $badgeClass = match($estadoId) {
                            1 => 'badge-pendiente',
                            2 => 'badge-confirmado',
                            3 => 'badge-diseno',
                            4 => 'badge-aprobado',
                            5 => 'badge-produccion',
                            6 => 'badge-calidad',
                            7 => 'badge-listo',
                            8 => 'badge-camino',
                            9 => 'badge-entregado',
                            10 => 'badge-cancelado',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <div class="estado-actual-badge {{ $badgeClass }}">
                        <span class="label">Estado Actual</span>
                        <span class="estado">{{ $estadoNombre }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            
            {{-- Columna Izquierda: Informacion y Acciones --}}
            <div class="col-lg-4">
                
                {{-- Card: Informacion del Cliente --}}
                <div class="info-card animate-in animate-delay-1">
                    <h5 class="card-title">
                        <i class="bi bi-person-circle me-2"></i>Cliente
                    </h5>
                    <div class="card-content">
                        @if(isset($pedido['usuario']) && $pedido['usuario'])
                            <div class="client-info">
                                <div class="client-avatar">
                                    {{ strtoupper(substr($pedido['usuario']['nombre'], 0, 1)) }}
                                </div>
                                <div class="client-details">
                                    <p class="client-name">{{ $pedido['usuario']['nombre'] }}</p>
                                    <p class="client-email">{{ $pedido['usuario']['correo'] }}</p>
                                    <span class="client-type badge-registrado">
                                        <i class="bi bi-person-check-fill"></i> Registrado
                                    </span>
                                </div>
                            </div>
                        @elseif(isset($pedido['pedIdentificadorCliente']) && $pedido['pedIdentificadorCliente'])
                            <div class="client-info">
                                <div class="client-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="client-details">
                                    <p class="client-name">{{ $pedido['pedIdentificadorCliente'] }}</p>
                                    <span class="client-type badge-externo">
                                        <i class="bi bi-telephone-fill"></i> Externo
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-person-x display-6 d-block mb-2"></i>
                                <p class="mb-0">Sin cliente asignado</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card: Cambiar Estado --}}
                <div class="info-card animate-in animate-delay-2">
                    <h5 class="card-title">
                        <i class="bi bi-arrow-left-right me-2"></i>Actualizar Estado
                    </h5>
                    <div class="card-content">
                        <form id="formCambiarEstado" onsubmit="return actualizarEstadoPedido(event, {{ $pedido['pedId'] }})">
                            <div class="mb-3">
                                <label class="form-label">Nuevo Estado</label>
                                <select id="nuevoEstadoSelect" class="form-select" required>
                                    @foreach($estados as $id => $nombre)
                                    <option value="{{ $id }}" {{ $estadoId == $id ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comentarios</label>
                                <textarea id="comentariosEstado" class="form-control" rows="3" 
                                          placeholder="Agregar notas sobre este cambio...">{{ $pedido['pedComentarios'] ?? '' }}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle-fill me-2"></i>Actualizar Estado
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Card: Personalizacion Vinculada --}}
                @if((isset($pedido['personalizacion']) && $pedido['personalizacion']) || (isset($pedido['perId']) && $pedido['perId']))
                <div class="info-card animate-in animate-delay-3">
                    <h5 class="card-title">
                        <i class="bi bi-gem me-2"></i>Personalización Vinculada
                    </h5>
                    <div class="card-content">
                        @php
                            $perId = $pedido['personalizacion']['perId'] ?? ($pedido['perId'] ?? null);
                        @endphp
                        <div class="personalizacion-link">
                            <button onclick="verDetallesPersonalizacion({{ $perId }})" class="btn-ver-personalizacion">
                                <i class="bi bi-eye-fill me-2"></i>Ver Diseño Personalizado
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Card: Contacto Origen --}}
                @if(isset($pedido['conId']) && $pedido['conId'])
                <div class="info-card animate-in animate-delay-4">
                    <h5 class="card-title">
                        <i class="bi bi-chat-dots-fill me-2"></i>Mensaje Original
                    </h5>
                    <div class="card-content">
                        <p class="text-muted small mb-2">Este pedido se creó desde un mensaje de contacto</p>
                        <button onclick="verMensajeOrigen({{ $pedido['conId'] }})" class="btn-ver-mensaje">
                            <i class="bi bi-envelope-open-fill me-2"></i>Ver Mensaje Original
                        </button>
                    </div>
                </div>
                @endif

            </div>

            {{-- Columna Derecha: Timeline y Detalles --}}
            <div class="col-lg-8">
                
                {{-- Timeline de Estados --}}
                <div class="timeline-card animate-in animate-delay-2">
                    <h5 class="card-title">
                        <i class="bi bi-clock-history me-2"></i>Progreso del Pedido
                    </h5>
                    <div class="timeline-container">
                        @php
                            $todosEstados = [
                                1 => ['nombre' => 'Pendiente Confirmacion', 'icono' => 'bi-clock-fill'],
                                2 => ['nombre' => 'Confirmado', 'icono' => 'bi-check-circle-fill'],
                                3 => ['nombre' => 'En Diseno', 'icono' => 'bi-palette-fill'],
                                4 => ['nombre' => 'Aprobado por Cliente', 'icono' => 'bi-hand-thumbs-up-fill'],
                                5 => ['nombre' => 'En Produccion', 'icono' => 'bi-gear-fill'],
                                6 => ['nombre' => 'Control de Calidad', 'icono' => 'bi-shield-check-fill'],
                                7 => ['nombre' => 'Listo para Entrega', 'icono' => 'bi-box-seam-fill'],
                                8 => ['nombre' => 'En Camino', 'icono' => 'bi-truck'],
                                9 => ['nombre' => 'Entregado', 'icono' => 'bi-gift-fill'],
                                10 => ['nombre' => 'Cancelado', 'icono' => 'bi-x-circle-fill']
                            ];
                        @endphp

                        <div class="timeline-vertical">
                            @foreach($todosEstados as $id => $info)
                                @php
                                    $esActual = ($estadoId == $id);
                                    $completado = ($id < $estadoId && $estadoId != 10);
                                    $cancelado = ($estadoId == 10);
                                    
                                    $claseEstado = '';
                                    if ($cancelado && $id != 10) {
                                        $claseEstado = 'timeline-item-disabled';
                                    } elseif ($esActual) {
                                        $claseEstado = 'timeline-item-active';
                                    } elseif ($completado) {
                                        $claseEstado = 'timeline-item-completed';
                                    } else {
                                        $claseEstado = 'timeline-item-pending';
                                    }
                                @endphp
                                
                                <div class="timeline-item {{ $claseEstado }}">
                                    <div class="timeline-marker">
                                        <i class="bi {{ $info['icono'] }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ $info['nombre'] }}</h6>
                                        @if($esActual)
                                            <span class="timeline-badge">Estado Actual</span>
                                        @elseif($completado)
                                            <span class="timeline-badge completed">Completado</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Card: Imagenes y Archivos --}}
                <div class="info-card animate-in animate-delay-3">
                    <h5 class="card-title">
                        <i class="bi bi-images me-2"></i>Imágenes del Pedido
                    </h5>
                    <div class="card-content">
                        <div class="imagenes-section">
                            <p class="text-muted text-center py-4">
                                <i class="bi bi-image display-6 d-block mb-2"></i>
                                No hay imágenes adjuntas aún
                            </p>
                            <button class="btn btn-outline-primary w-100">
                                <i class="bi bi-cloud-upload-fill me-2"></i>Subir Imagen
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Card: Comentarios Generales --}}
                @if($pedido['pedComentarios'])
                <div class="info-card animate-in animate-delay-4">
                    <h5 class="card-title">
                        <i class="bi bi-sticky-fill me-2"></i>Comentarios
                    </h5>
                    <div class="card-content">
                        <div class="comentarios-box">
                            {{ $pedido['pedComentarios'] }}
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Modal para ver personalizacion --}}
<div class="modal fade" id="modalPersonalizacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-gem me-2"></i>Detalles de Personalización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalPersonalizacionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script>
// Actualizar estado del pedido
function actualizarEstadoPedido(event, pedidoId) {
    event.preventDefault();
    
    const estadoId = document.getElementById('nuevoEstadoSelect').value;
    const comentarios = document.getElementById('comentariosEstado').value;
    
    Swal.fire({
        title: 'Actualizando...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`/admin/pedidos/${pedidoId}/estado`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            estadoId: parseInt(estadoId),
            comentarios: comentarios
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Exito',
                text: 'Estado actualizado correctamente',
                icon: 'success',
                confirmButtonColor: '#009688'
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Error al actualizar');
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: error.message,
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    });
    
    return false;
}

// Ver detalles de personalizacion
function verDetallesPersonalizacion(perId) {
    const modal = new bootstrap.Modal(document.getElementById('modalPersonalizacion'));
    modal.show();
    
    fetch(`/api/personalizaciones/${perId}/detalles`, {
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('jwt_token') || '')
        }
    })
    .then(response => response.json())
    .then(data => {
        let html = '<div class="row g-3">';
        
        if (data.detalles) {
            data.detalles.forEach(detalle => {
                html += `
                    <div class="col-md-6">
                        <div class="detalle-personalizacion">
                            <strong>${detalle.valNombre}:</strong>
                            <span>${detalle.opcionNombre}</span>
                        </div>
                    </div>
                `;
            });
        }
        
        html += '</div>';
        document.getElementById('modalPersonalizacionContent').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('modalPersonalizacionContent').innerHTML = 
            '<p class="text-danger">Error al cargar la personalizacion</p>';
    });
}

// Ver mensaje origen
function verMensajeOrigen(conId) {
    window.location.href = `/admin/mensajes?highlight=${conId}`;
}
</script>
@endpush