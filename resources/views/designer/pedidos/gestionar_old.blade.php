@extends('layouts.app')

@section('title', 'Gestionar Pedido - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/gestionar-pedido.css') }}">

{{-- NUEVO: Estilos para Model Viewer --}}
<style>
    model-viewer {
        width: 100%;
        height: 350px;
        background-color: #f5f5f5;
        border-radius: 8px;
    }
    
    model-viewer::part(default-progress-bar) {
        background-color: #009688;
    }
    
    .viewer-controls {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        justify-content: center;
    }
    
    .viewer-controls button {
        padding: 5px 12px;
        font-size: 12px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .viewer-controls button:hover {
        background: #f0f0f0;
    }
</style>
@endpush

@section('content')
<div class="gestionar-pedido-container">
    <div class="container-fluid py-4">
        
        {{-- Header del pedido --}}
        <div class="pedido-header animate-in">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h3 mb-2">
                        <i class="fas fa-tasks me-2"></i>
                        Gestionar Pedido #{{ $pedido['pedId'] ?? 'N/A' }}
                    </h1>
                    <p class="text-muted mb-0">
                        Cliente: {{ $pedido['clienteDetalles']['nombre'] ?? 'No especificado' }}
                        @if(!empty($pedido['clienteDetalles']['correo']))
                            | {{ $pedido['clienteDetalles']['correo'] }}
                        @endif
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('designer.pedidos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Mis Pedidos
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabs de navegación --}}
        <ul class="nav nav-tabs mb-4" id="pedidoTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="detalles-tab" data-bs-toggle="tab" data-bs-target="#detalles" type="button" role="tab">
                    <i class="fas fa-info-circle me-1"></i> Detalles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
                    <i class="fas fa-history me-1"></i> Historial
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="archivos-tab" data-bs-toggle="tab" data-bs-target="#archivos" type="button" role="tab">
                    <i class="fas fa-images me-1"></i> Archivos
                </button>
            </li>
        </ul>

        {{-- Contenido de los tabs --}}
        <div class="tab-content" id="pedidoTabsContent">
            
            {{-- Tab Detalles --}}
            <div class="tab-pane fade show active" id="detalles" role="tabpanel">
                <div class="row">
                    {{-- Información principal --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pedido</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>ID Pedido:</strong></div>
                                    <div class="col-sm-8">#{{ $pedido['pedId'] ?? 'N/A' }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Estado Actual:</strong></div>
                                    <div class="col-sm-8">
                                        <span class="badge estado-badge estado-{{ $pedido['estado']['estId'] ?? 1 }}">
                                            {{ $pedido['estado']['estNombre'] ?? 'Desconocido' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Fecha Creación:</strong></div>
                                    <div class="col-sm-8">{{ date('d/m/Y H:i', strtotime($pedido['pedFechaCreacion'] ?? 'now')) }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Identificador:</strong></div>
                                    <div class="col-sm-8">{{ $pedido['pedIdentificadorCliente'] ?? 'N/A' }}</div>
                                </div>
                                @if(!empty($pedido['pedComentarios']))
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Comentarios:</strong></div>
                                    <div class="col-sm-8">{{ $pedido['pedComentarios'] }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actualización de estado --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Actualizar Estado</h5>
                            </div>
                            <div class="card-body">
                                <form id="formActualizarEstado">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="estadoId" class="form-label">Nuevo Estado</label>
                                        <select class="form-select" id="estadoId" name="estadoId" required>
                                            <option value="">Seleccionar estado...</option>
                                            @foreach($estados as $id => $nombre)
                                                <option value="{{ $id }}" {{ ($pedido['estado']['estId'] ?? 1) == $id ? 'selected' : '' }}>
                                                    {{ $nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="comentarios" class="form-label">Comentarios del cambio</label>
                                        <textarea class="form-control" id="comentarios" name="comentarios" rows="3" placeholder="Describe los detalles del cambio de estado..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="his_imagen" class="form-label">Imagen adjunta (opcional)</label>
                                        <input type="file" class="form-control" id="his_imagen" name="his_imagen" accept="image/*">
                                        <small class="form-text text-muted">Máximo 5MB, formatos: JPG, PNG, WebP</small>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-save me-2"></i>Actualizar Estado
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Información del cliente --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información del Cliente</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Nombre:</strong> {{ $pedido['clienteDetalles']['nombre'] ?? 'No especificado' }}</p>
                                        <p><strong>Tipo:</strong> 
                                            @switch($pedido['clienteDetalles']['tipo'] ?? 'sin_detalles')
                                                @case('usuario_registrado')
                                                    <span class="badge bg-primary">Usuario Registrado</span>
                                                    @break
                                                @case('contacto_externo')
                                                    <span class="badge bg-info">Contacto Externo</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Sin Detalles</span>
                                            @endswitch
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        @if(!empty($pedido['clienteDetalles']['correo']))
                                            <p><strong>Correo:</strong> {{ $pedido['clienteDetalles']['correo'] }}</p>
                                        @endif
                                        @if(!empty($pedido['clienteDetalles']['telefono']))
                                            <p><strong>Teléfono:</strong> {{ $pedido['clienteDetalles']['telefono'] }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        @if(!empty($pedido['clienteDetalles']['identificador']))
                                            <p><strong>Identificador:</strong> {{ $pedido['clienteDetalles']['identificador'] }}</p>
                                        @endif
                                        @if(!empty($pedido['clienteDetalles']['usuDocnum']))
                                            <p><strong>Documento:</strong> {{ $pedido['clienteDetalles']['usuDocnum'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Historial --}}
            <div class="tab-pane fade" id="historial" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Cambios</h5>
                    </div>
                    <div class="card-body">
                        <div id="historial-container">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando historial...</span>
                                </div>
                                <p class="mt-2">Cargando historial del pedido...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab Archivos --}}
            <div class="tab-pane fade" id="archivos" role="tabpanel">
                <div class="row">
                    {{-- Subir diseño --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Subir Diseño/Render</h5>
                            </div>
                            <div class="col-md-5 ps-md-4">
                                @php
                                    $rol = Session::get('user_role');
                                    $estadoActualId = $pedido['estId'] ?? ($pedido['estado']['estId'] ?? 0);
                                @endphp

                                @if($rol !== 'ROLE_USUARIO' && $estadoActualId == 3)
                                    <h6 class="small fw-bold mb-3">Subir Propuesta de Diseño:</h6>
                                    <form id="formSubirRender" action="{{ route('designer.pedidos.subir-diseno', $pedido['pedId']) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <input type="file" 
                                                   name="diseno_archivo" 
                                                   class="form-control form-control-sm" 
                                                   accept="image/*,.glb,.gltf" 
                                                   required>
                                            <div class="form-text small">
                                                📷 Imágenes: JPG, PNG, WebP<br>
                                                🎨 Modelos 3D: GLB, GLTF
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-dark w-100">
                                            <i class="bi bi-cloud-upload me-2"></i>Cargar Diseño Oficial
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Subir foto producto final --}}
                    <div class="col-lg-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-camera me-2"></i>Subir Foto Producto Final</h5>
                            </div>
                            <div class="col-md-5 ps-md-4">
                                <form id="formSubirProducto" action="{{ route('designer.pedidos.subir-producto-final', $pedido['pedId']) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="producto_foto" class="form-label">Foto del Producto</label>
                                        <input type="file" 
                                               id="producto_foto"
                                               name="producto_foto" 
                                               class="form-control" 
                                               accept="image/*" 
                                               required>
                                        <div class="form-text">Formatos: JPG, PNG, WebP (Máx 10MB)</div>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-camera me-2"></i>Subir Foto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Galería de archivos existentes --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-images me-2"></i>Archivos del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div id="archivos-container">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando archivos...</span>
                                </div>
                                <p class="mt-2">Cargando archivos del pedido...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/model-viewer.min.js') }}"></script>
<script>
// Variables globales
const pedidoId = {{ $pedido['pedId'] ?? 0 }};
const estadoActualId = {{ $pedido['estId'] ?? ($pedido['estado']['estId'] ?? 0) }};

// Función para obtener información del estado
function getEstadoInfo(estadoId) {
    const estados = {
        1: { nombre: 'Pendiente', icono: 'bi-clock' },
        2: { nombre: 'En Diseño', icono: 'bi-palette' },
        3: { nombre: 'Diseño Completado', icono: 'bi-check-circle' },
        4: { nombre: 'Aprobado', icono: 'bi-check2-circle' },
        5: { nombre: 'En Producción', icono: 'bi-gear' },
        6: { nombre: 'Engaste', icono: 'bi-gem' },
        7: { nombre: 'Pulido', icono: 'bi-shine' },
        8: { nombre: 'Control de Calidad', icono: 'bi-clipboard-check' },
        9: { nombre: 'Finalizado', icono: 'bi-trophy' },
        10: { nombre: 'Cancelado', icono: 'bi-x-circle' }
    };
    return estados[estadoId] || { nombre: 'Desconocido', icono: 'bi-question-circle' };
}

// Actualizar estado
document.getElementById('formActualizarEstado').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...';
    
    fetch(`/designer/pedidos/${pedidoId}/actualizar-estado-historial`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            this.reset();
            cargarHistorial(); // Recargar historial
            setTimeout(() => {
                window.location.reload(); // Recargar página para mostrar nuevo estado
            }, 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error al actualizar el estado');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Subir diseño
document.getElementById('formSubirDiseno').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subiendo...';
    
    fetch(`/designer/pedidos/${pedidoId}/subir-diseno`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            this.reset();
            cargarArchivos(); // Recargar archivos
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error al subir el diseño');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Subir foto producto final
document.getElementById('formSubirProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subiendo...';
    
    fetch(`/designer/pedidos/${pedidoId}/subir-producto-final`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            this.reset();
            cargarArchivos(); // Recargar archivos
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error al subir la foto');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Cargar historial
function cargarHistorial() {
    fetch(`/api/designer/pedidos/${pedidoId}/historial`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarHistorial(data.historial);
            } else {
                document.getElementById('historial-container').innerHTML = 
                    '<div class="alert alert-warning">No se pudo cargar el historial</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('historial-container').innerHTML = 
                '<div class="alert alert-danger">Error al cargar el historial</div>';
        });
}

// Mostrar historial
function mostrarHistorial(historial) {
    const container = document.getElementById('historial-container');
    
    if (!historial || historial.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No hay cambios en el historial</div>';
        return;
    }
    
    let html = '<div class="timeline">';
    
    historial.forEach(item => {
        const fecha = item.hisFecha ? new Date(item.hisFecha).toLocaleString('es-ES') : 'Fecha no disponible';
        const info = getEstadoInfo(item.estId);
        const esActual = item.estId === estadoActualId;
        
        let claseItem = 'timeline-item-completed';
        if (item.estId === 10) {
            claseItem = 'timeline-item-cancelado';
        } else if (esActual) {
             claseItem = 'timeline-item-active';
        }
        
        html += `
            <div class="timeline-item ${claseItem}">
                <div class="timeline-marker">
                    <i class="bi ${info.icono}"></i>
                </div>
                <div class="timeline-content">
                    <h6 class="timeline-title">
                        ${info.nombre}
                        ${esActual ? '<span class="timeline-badge active">ESTADO ACTUAL</span>' : ''}
                        ${item.estId === 10 ? '<span class="timeline-badge cancelado">CANCELADO</span>' : ''}
                    </h6>
                    <span class="timeline-date">${fecha}</span>
                    <p class="timeline-comment mt-1">${item.hisComentarios || 'Cambio registrado sin notas.'}</p>
                    <p class="timeline-responsible small text-muted">Responsable: ${item.responsableNombre || 'Sistema'}</p>
                    
                    ${item.hisImagen ? `<div class="timeline-image-link mt-2">
                        <a href="/designer/pedidos/ver-archivo?path=${item.hisImagen}" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-image"></i> Ver Evidencia
                        </a>
                    </div>` : ''}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Cargar archivos del pedido
function cargarArchivos() {
    fetch(`/api/designer/pedidos/${pedidoId}/detalles`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarArchivos(data.pedido);
            } else {
                console.error('Error al cargar archivos:', data.message);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
}

// Mostrar archivos
function mostrarArchivos(pedido) {
    const container = document.getElementById('archivos-container');
    
    let html = '<div class="row">';
    
    // Renders 3D
    if (pedido.renderPath) {
        html += `
            <div class="col-md-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-cube me-2"></i>Render 3D</h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($pedido["renderPath"]))
                                    {{-- MODELO 3D INTERACTIVO (nuevo) --}}
                                    <model-viewer src="/designer/pedidos/ver-archivo?path={{ $pedido["renderPath"] }}" 
                                                  auto-rotate camera-controls 
                                                  shadow-intensity="1"
                                                  ar-modes="webxr scene-viewer quick-look"
                                                  alt="Render 3D del pedido">
                                    </model-viewer>
                                    <div class="viewer-controls">
                                        <button onclick="resetCamera()">Reiniciar Cámara</button>
                                        <button onclick="toggleAutoRotate()">Auto Rotar</button>
                                    </div>
                                    
                                    <p class="small text-muted mt-2">
                                        <i class="bi bi-box"></i> Modelo 3D interactivo
                                    </p>
                                @else
                                    {{-- IMAGEN ESTÁTICA (tu código actual) --}}
                                    <img src="/designer/pedidos/ver-archivo?path={{ $pedido["renderPath"] }}" 
                                        class="img-fluid rounded shadow-sm" 
                                        style="max-height: 250px; cursor: pointer;" 
                                        onclick="window.open(this.src, '_blank')"
                                        alt="Render Oficial">
                                    <p class="small text-muted mt-2">Diseño oficial cargado</p>
                                @endif
                    </div>
                </div>
            </div>
        `;
    }
    
    // Fotos finales
    if (pedido.fotosFinales && pedido.fotosFinales.length > 0) {
        html += '<div class="col-12"><h5 class="mb-3">Fotos del Producto Final</h5></div>';
        
        pedido.fotosFinales.forEach((foto, index) => {
            $fotoRuta = $foto["fpfRuta"] ?? "";
            $fotoIndex = $index + 1;
            
            html .= '
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="'. route("designer.pedidos.ver-archivo", ["path" => $fotoRuta]) .'" 
                             class="card-img-top" 
                             alt="Foto del producto '.$fotoIndex.'"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">Foto '.$fotoIndex.'</small>
                        </div>
                    </div>
                </div>
            ';
        });
    }
    
    if (!pedido.renderPath && (!pedido.fotosFinales || pedido.fotosFinales.length === 0)) {
        html += `
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    No hay archivos cargados para este pedido
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    container.innerHTML = html;
}

// Funciones auxiliares
function getEstadoClass(estadoId) {
    const classes = {
        1: 'estado-pendiente',
        2: 'estado-pago',
        3: 'estado-diseno',
        4: 'estado-aprobado',
        5: 'estado-produccion',
        6: 'estado-engaste',
        7: 'estado-pulido',
        8: 'estado-calidad',
        9: 'estado-finalizado',
        10: 'estado-cancelado'
    };
    return classes[estadoId] || 'estado-default';
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function resetCamera() {
    const viewer = document.querySelector('model-viewer');
    if (viewer) {
        viewer.cameraReset();
    }
}

function toggleAutoRotate() {
    const viewer = document.querySelector('model-viewer');
    if (viewer) {
        viewer.autoRotate = !viewer.autoRotate;
    }
}

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarHistorial();
    cargarArchivos();
});
</script>
@endpush
