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
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <a href="{{ route('designer.pedidos.index') }}" class="btn-back">
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
            
            {{-- ===========================================================
                COLUMNA IZQUIERDA: ACCIONES Y ARCHIVOS (4 col) - IGUAL QUE ADMIN
                =========================================================== --}}
            <div class="col-lg-4">
                
                {{-- [FASE 1] CARD: CAMBIAR ESTADO (Prioridad #1 por Usabilidad) --}}
                <div class="info-card animate-in border-primary shadow-sm mb-4">
                    <h5 class="card-title text-primary">
                        <i class="bi bi-arrow-left-right me-2"></i>Actualizar Estado
                    </h5>
                    <div class="card-content">
                        <form id="formCambiarEstado" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nuevo Estado</label>
                                <select name="estadoId" id="nuevoEstadoSelect" class="form-select border-primary" required>
                                    @foreach($estados as $id => $nombre)
                                        <option value="{{ $id }}" {{ $estadoId == $id ? 'selected' : '' }}>
                                            {{ $nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Comentarios del Cambio</label>
                                <textarea name="comentarios" id="comentariosEstado" class="form-control" rows="2" 
                                        placeholder="¿Qué se hizo en este paso?">{{ $pedido['pedComentarios'] ?? '' }}</textarea>
                            </div>

                            {{-- Campo de Evidencia (Fase 1) --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">
                                    <i class="bi bi-camera me-1"></i> Foto de Evidencia (Opcional)
                                </label>
                                <input type="file" name="his_imagen" id="his_imagen" class="form-control form-control-sm" accept="image/*">
                            </div>
                            
                            <button type="button" onclick="actualizarEstadoPedido(event, {{ $pedido['pedId'] }})" class="btn btn-primary w-100 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2"></i>Registrar Avance
                            </button>
                        </form>
                    </div>
                </div>

                {{-- [FASE 2] CARD: RENDER 3D VINCULADO (ESTACIÓN DE DISEÑO) - IGUAL QUE ADMIN --}}
                <div class="info-card animate-in animate-delay-1 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-palette-fill me-2"></i>Render Oficial
                        </h5>
                        @if($estadoId == 3)
                            <span class="badge bg-warning text-dark"><i class="bi bi-pencil-square me-1"></i>En Diseño</span>
                        @endif
                    </div>
                    <div class="card-content border rounded bg-white p-3">
                        <div class="row align-items-center">
                            {{-- Visualizador del Render actual --}}
                            <div class="col-md-7 text-center border-end py-3">
                                @php
                                    $renderPath = $pedido['renderPath'] ?? null;
                                    $esModelo3D = false;
                                    
                                    if ($renderPath) {
                                        $extension = strtolower(pathinfo($renderPath, PATHINFO_EXTENSION));
                                        $esModelo3D = in_array($extension, ['glb', 'gltf']);
                                    }
                                @endphp

                                @if($renderPath)
                                    @if($esModelo3D)
                                        {{-- 🔥 VISOR 3D INTERACTIVO --}}
                                        <model-viewer
                                            src="/designer/pedidos/ver-archivo/{{ $renderPath }}"
                                            alt="Modelo 3D del diseño"
                                            auto-rotate
                                            camera-controls
                                            touch-action="pan-y"
                                            shadow-intensity="1"
                                            exposure="1"
                                            environment-image="neutral"
                                            min-camera-orbit="auto auto 5%"
                                            max-camera-orbit="auto auto 100%"
                                        >
                                            <div class="progress-bar" slot="progress-bar">
                                                <div class="update-bar"></div>
                                            </div>
                                            
                                            {{-- Botón de AR (Opcional) --}}
                                            <button slot="ar-button" class="btn btn-sm btn-primary">
                                                <i class="bi bi-phone"></i> Ver en AR
                                            </button>
                                        </model-viewer>
                                        
                                        {{-- 🔥 CONTROLES DEL VISOR --}}
                                        <div class="viewer-controls">
                                            <button onclick="resetearCamara()" title="Resetear cámara">
                                                <i class="bi bi-arrow-counterclockwise"></i> Resetear
                                            </button>
                                            <button onclick="capturarScreenshot()" title="Captura de pantalla">
                                                <i class="bi bi-camera"></i> Captura
                                            </button>
                                            <button onclick="window.open('/designer/pedidos/ver-archivo/{{ $renderPath }}', '_blank')" 
                                                    title="Descargar modelo">
                                                <i class="bi bi-download"></i> Descargar
                                            </button>
                                        </div>
                                        
                                        <p class="small text-muted mt-2">
                                            <i class="bi bi-box"></i> Modelo 3D interactivo
                                        </p>
                                    @else
                                        {{-- 🔥 IMAGEN ESTÁTICA --}}
                                        <img src="/designer/pedidos/ver-archivo/{{ $renderPath }}" 
                                            class="img-fluid rounded shadow-sm" 
                                            style="max-height: 250px; cursor: pointer;" 
                                            onclick="window.open(this.src, '_blank')"
                                            alt="Render Oficial">
                                        <p class="small text-muted mt-2">Diseño oficial cargado</p>
                                    @endif
                                @else
                                    {{-- 🔥 ESTADO VACÍO --}}
                                    <div class="text-muted">
                                        <i class="bi bi-vector-pen display-4 d-block mb-2"></i>
                                        <p>No se ha cargado el diseño oficial aún.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- 🔥 COLUMNA DEL FORMULARIO (col-md-5) --}}
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
                                @else
                                    <div class="alert alert-light border-0 small mb-0">
                                        <i class="bi bi-info-circle me-1"></i> 
                                        El diseño oficial se establece durante la fase de <strong>Diseño en Proceso</strong>.
                                    </div>
                                @endif
                            </div> {{-- FIN del col-md-5 --}}
                        </div>
                    </div>
                </div>

                {{-- CARD: GALERÍA DE PRODUCTO TERMINADO - IGUAL QUE ADMIN --}}
                <div class="info-card animate-in animate-delay-2 mb-4">
                    <h5 class="card-title text-success">
                        <i class="bi bi-camera-fill me-2"></i>Galería de Producto Real
                    </h5>
                    <div class="card-content">
                        {{-- Subida de archivos (Solo para admin/disañador) --}}
                        @if(Session::get('user_role') !== 'ROLE_USUARIO')
                            <form id="formFotoFinal" action="{{ route('designer.pedidos.subir-producto-final', $pedido['pedId']) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group mb-3">
                                    <input type="file" name="producto_foto" class="form-control form-control-sm" accept="image/*" required>
                                    <button class="btn btn-sm btn-success" type="submit">Añadir Foto</button>
                                </div>
                            </form>
                        @endif
                        
                        {{-- Galería Multiphoto --}}
                        <div class="row g-2 mt-2" id="galeriaFotosFinales">
                            @if(isset($pedido['fotosFinales']) && is_array($pedido['fotosFinales']) && count($pedido['fotosFinales']) > 0)
                                @foreach($pedido['fotosFinales'] as $foto)
                                    <div class="col-4 position-relative">
                                        <img src="/designer/pedidos/ver-archivo/{{ $foto['fpfRuta'] ?? $foto['fotImagenFinal'] ?? 'default' }}" 
                                            class="img-fluid rounded border shadow-sm img-thumbnail-gallery" 
                                            style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                            onclick="window.open(this.src)">
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12 text-center py-3 text-muted">
                                    <small>No hay fotos reales cargadas todavía.</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- CARD: INFORMACIÓN DEL CLIENTE - IGUAL QUE ADMIN --}}
                <div class="info-card animate-in animate-delay-1">
                    <h5 class="card-title">
                        <i class="bi bi-person-circle me-2"></i>Información del Cliente
                    </h5>
                    <div class="card-content">
                        @php
                            $cliente = $pedido['clienteDetalles'] ?? null;
                            $tipo = $cliente['tipo'] ?? null;
                            
                            $config = match($tipo) {
                                'usuario_registrado' => [
                                    'nombre' => $cliente['usuNombre'] ?? 'Usuario Registrado',
                                    'correo' => $cliente['usuCorreo'] ?? '',
                                    'telefono' => $cliente['usuTelefono'] ?? '',
                                    'label' => 'Registrado',
                                    'icon' => 'person-check-fill',
                                    'badge' => 'registrado',
                                    'mostrarId' => $pedido['usuIdCliente'] ?? null
                                ],
                                'contacto_externo' => [
                                    'nombre' => $cliente['conNombre'] ?? 'Cliente Externo',
                                    'correo' => $cliente['conCorreo'] ?? '',
                                    'telefono' => $cliente['conTelefono'] ?? '',
                                    'label' => 'Externo',
                                    'icon' => 'telephone-fill',
                                    'badge' => 'externo',
                                    'mostrarId' => $pedido['conId'] ?? null
                                ],
                                default => [
                                    'nombre' => $pedido['nombreCliente'] ?? 'Cliente',
                                    'correo' => '',
                                    'telefono' => '',
                                    'label' => 'Sin detalles',
                                    'icon' => 'person-fill',
                                    'badge' => 'externo',
                                    'mostrarId' => null
                                ]
                            };
                        @endphp

                        <div class="client-info">
                            <div class="client-avatar">
                                @if($tipo === 'usuario_registrado')
                                    {{ strtoupper(substr($config['nombre'], 0, 1)) }}
                                @else
                                    <i class="bi bi-person-fill"></i>
                                @endif
                            </div>
                            <div class="client-details">
                                <p class="client-name mb-1">{{ $config['nombre'] }}</p>
                                @if($config['correo'])
                                    <p class="client-email small mb-1"><i class="bi bi-envelope me-1"></i>{{ $config['correo'] }}</p>
                                @endif
                                @if($config['telefono'])
                                    <p class="client-phone small text-muted"><i class="bi bi-telephone me-1"></i>{{ $config['telefono'] }}</p>
                                @endif
                                <span class="client-type badge-{{ $config['badge'] }} mt-2">
                                    <i class="bi bi-{{ $config['icon'] }}"></i> {{ $config['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===========================================================
                COLUMNA DERECHA: SEGUIMIENTO Y RESULTADOS (8 col) - IGUAL QUE ADMIN
                =========================================================== --}}
            <div class="col-lg-8">
                
                {{-- [FASE 1] CARD: LÍNEA DE TIEMPO --}}
                <div class="timeline-card animate-in mb-4">
                    <h5 class="card-title">
                        <i class="bi bi-clock-history me-2"></i>Historial del Pedido
                    </h5>
                    <div class="timeline-container" id="historialTimeline">
                        <div class="text-center py-5" id="timelineLoading">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2">Cargando historial...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
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
document.getElementById('formCambiarEstado').addEventListener('submit', function(e) {
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
            showAlert('success', 'Estado actualizado correctamente');
            // Recargar la página para mostrar los cambios
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('danger', data.message || 'Error al actualizar el estado');
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

// ===============================================
// 2. LÍNEA DE TIEMPO (HISTORIAL)
// ===============================================

function cargarHistorialPedido(pedidoId) {
    const container = document.getElementById('historialTimeline');
    const loading = document.getElementById('timelineLoading');
    
    // Mostrar loading
    if (loading) {
        loading.style.display = 'block';
    }
    
    fetch(`/designer/pedidos/${pedidoId}/historial`, {
        headers: {
            'Authorization': 'Bearer ' + (localStorage.getItem('jwt_token') || sessionStorage.getItem('jwt_token') || ''),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (loading) {
            loading.style.display = 'none';
        }
        
        if (data.success && data.historial) {
            renderizarTimeline(container, data.historial);
        } else {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No se pudo cargar el historial
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error al cargar historial:', error);
        if (loading) {
            loading.style.display = 'none';
        }
        container.innerHTML = `
            <div class="text-center py-4 text-danger">
                <i class="bi bi-wifi-off me-2"></i>
                Error de conexión al cargar historial
            </div>
        `;
    });
}

function renderizarTimeline(container, historial) {
    let html = '<div class="timeline-vertical">';
    
    historial.forEach((item, index) => {
        const info = getEstadoInfo(item.estId);
        
        const fecha = new Date(item.hisFechaCambio).toLocaleString('es-CO', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        const esActual = index === 0 && item.estId !== 10;
        
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
                        <a href="/designer/pedidos/ver-archivo/${item.hisImagen}" target="_blank" class="btn btn-sm btn-outline-secondary">
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

// ===============================================
// 3. FUNCIONES AUXILIARES
// ===============================================

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
}

// ===============================================
// NUEVO: MANEJO DE SUBIDA DE ARCHIVOS
// ===============================================

// Subir Render 3D
document.getElementById('formSubirRender')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Subiendo Diseño...',
        text: 'Enviando render al servidor y actualizando historial',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Error en el servidor');
        return data;
    })
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: '¡Diseño Cargado!',
            text: 'El render ha sido vinculado al pedido correctamente.',
            confirmButtonColor: '#009688'
        }).then(() => {
            window.location.reload();
        });
    })
    .catch(error => {
        console.error('Error al subir diseño:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al subir',
            text: error.message,
            footer: 'Verifica que el archivo no supere los 10MB y que el servidor Java esté activo.'
        });
    });
});

// Subir Foto Producto Final
document.getElementById('formFotoFinal')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Subiendo Producto Final...',
        text: 'Cargando evidencia fotográfica del trabajo terminado.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Error en el servidor');
        return data;
    })
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: '¡Producto Registrado!',
            text: data.message,
            confirmButtonColor: '#009688'
        }).then(() => {
            window.location.reload(); // Recarga para ver la foto en la galería
        });
    })
    .catch(error => {
        console.error('Error al subir foto:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al subir',
            text: error.message
        });
    });
});

// ===============================================
// FUNCIONES PARA VISOR 3D (IGUAL QUE ADMIN)
// ===============================================}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cargando historial para pedido ID:', pedidoId);
    
    if (pedidoId && pedidoId > 0) {
        cargarHistorialPedido(pedidoId);
    } else {
        console.error('ID de pedido inválido:', pedidoId);
        const container = document.getElementById('historialTimeline');
        if (container) {
            container.innerHTML = 
                '<p class="text-danger text-center py-4">Error: ID de pedido no válido.</p>';
        }
    }
});

// ===============================================
// FUNCIONES PARA VISOR 3D (IGUAL QUE ADMIN)
// ===============================================

function resetearCamara() {
    const viewer = document.querySelector('model-viewer');
    if (viewer) {
        viewer.resetTurntableRotation();
        viewer.cameraOrbit = 'auto auto auto';
    }
}

function capturarScreenshot() {
    const viewer = document.querySelector('model-viewer');
    if (viewer) {
        viewer.toBlob().then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'render-3d.png';
            a.click();
        });
    }
}
</script>
@endpush
