@extends('layouts.app')

@section('title', 'Detalles del Pedido - ' . ($pedido['pedCodigo'] ?? 'N/A'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/header-minimal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/gestionar-pedido.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <style>
        /* Corregir posicionamiento del header - FORZAR */
        .header-minimal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1000 !important;
            background: white !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        
        /* Espacio para el header fijo */
        body {
            padding-top: 80px !important;
        }
        
        main {
            padding-top: 0 !important;
        }
        
        /* Prevenir FOUC (Flash of Unstyled Content) */
        .container-fluid {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .container-fluid.loaded {
            opacity: 1;
        }
        
        /* Estilos para Model Viewer */
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
        
        .viewer-controls button,
        .viewer-controls a {
            padding: 5px 12px;
            font-size: 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .viewer-controls button:hover,
        .viewer-controls a:hover {
            background: #f0f0f0;
        }

        .galeria-item {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .galeria-item img {
            transition: transform 0.3s ease;
        }

        .galeria-item:hover img {
            transform: scale(1.05);
        }

        .galeria-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .galeria-item:hover .galeria-overlay {
            opacity: 1;
        }

        .galeria-overlay i {
            color: white;
            font-size: 24px;
        }

        .modal-fullscreen {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.95);
            cursor: pointer;
        }

        .modal-fullscreen img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .modal-fullscreen-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
        }

        .modal-fullscreen-close:hover {
            color: #ccc;
        }

        /* Timeline styles */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 5px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: white;
            border: 2px solid #009688;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .timeline-content {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .info-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

<div class="container-fluid py-4">
    {{-- Header del Pedido --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-receipt-cutoff me-2"></i>
                        Pedido {{ $pedido['pedCodigo'] ?? 'N/A' }}
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3 me-1"></i>
                        Creado: {{ date('d/m/Y H:i', strtotime($pedido['pedFechaCreacion'] ?? 'now')) }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('user.pedidos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Mis Pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- DEBUG INFO - Solo visible en desarrollo --}}
    @if(config('app.debug'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6><i class="bi bi-bug me-2"></i>Información de Depuración:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <small>
                            <strong>Pedido ID:</strong> {{ $pedido['pedId'] ?? 'NULL' }}<br>
                            <strong>Código:</strong> {{ $pedido['pedCodigo'] ?? 'NULL' }}<br>
                            <strong>Estado:</strong> {{ $pedido['estadoNombre'] ?? 'NULL' }}<br>
                            <strong>RenderPath:</strong> {{ $pedido['renderPath'] ?? 'NULL' }}<br>
                            <strong>FotosFinales:</strong> {{ isset($pedido['fotosFinales']) ? count($pedido['fotosFinales']) : 0 }} elementos
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small>
                            <strong>Historial:</strong> {{ isset($pedido['historial']) ? count($pedido['historial']) : 0 }} elementos<br>
                            <strong>Tipo:</strong> {{ $pedido['pedTipo'] ?? 'NULL' }}<br>
                            <strong>Metal:</strong> {{ $pedido['pedMetal'] ?? 'NULL' }}<br>
                            <strong>Piedra:</strong> {{ $pedido['pedPiedra'] ?? 'NULL' }}<br>
                            <strong>Cliente:</strong> {{ $pedido['clienteDetalles']['nombre'] ?? 'NULL' }}
                        </small>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
                        <i class="bi bi-code-slash me-1"></i>Mostrar/Ocultar JSON
                    </button>
                    <div style="display: none;">
                        <small><strong>Datos completos del pedido:</strong></small>
                        <pre class="small bg-light p-2 rounded mt-2" style="max-height: 200px; overflow-y: auto; font-size: 11px;">{{ json_encode($pedido, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Estado Actual --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card animate-in">
                <h5 class="card-title">
                    <i class="bi bi-info-circle-fill me-2"></i>Información del Pedido
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Código:</strong> {{ $pedido['pedCodigo'] ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <strong>Estado:</strong> 
                            <span class="estado-badge estado-{{ Str::slug($pedido['estadoNombre'] ?? 'desconocido', '_') }} fs-6 ms-2">
                                {{ $pedido['estadoNombre'] ?? 'Desconocido' }}
                            </span>
                        </p>
                        <p class="mb-2">
                            <strong>Fecha Creación:</strong> {{ date('d/m/Y H:i', strtotime($pedido['pedFechaCreacion'] ?? 'now')) }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Tipo:</strong> {{ $pedido['pedTipo'] ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <strong>Metal:</strong> {{ $pedido['pedMetal'] ?? 'N/A' }}
                        </p>
                        <p class="mb-2">
                            <strong>Piedra:</strong> {{ $pedido['pedPiedra'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                @if($pedido['pedComentarios'] ?? null)
                    <div class="mt-3">
                        <h6>Comentarios:</h6>
                        <p class="mb-0">{{ $pedido['pedComentarios'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Render 3D --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card animate-in animate-delay-1">
                <h5 class="card-title">
                    <i class="bi bi-vector-pen me-2"></i>Render Oficial
                </h5>
                <div class="card-content border rounded bg-white p-3">
                    <div class="row align-items-center">
                        {{-- Visualizador del Render --}}
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
                                    {{-- VISOR 3D INTERACTIVO --}}
                                    <model-viewer
                                        src="{{ route('archivos.ver', ['path' => $renderPath]) }}"
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
                                    </model-viewer>
                                    
                                    {{-- CONTROLES DEL VISOR --}}
                                    <div class="viewer-controls">
                                        <button onclick="resetearCamara()" title="Resetear cámara">
                                            <i class="bi bi-arrow-counterclockwise"></i> Resetear
                                        </button>
                                        <button onclick="capturarScreenshot()" title="Captura de pantalla">
                                            <i class="bi bi-camera"></i> Captura
                                        </button>
                                        <a href="{{ route('archivos.ver', ['path' => $renderPath]) }}" 
                                           download="{{ $renderPath }}" title="Descargar modelo">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    </div>
                                    
                                    <p class="small text-muted mt-2">
                                        <i class="bi bi-box"></i> Modelo 3D interactivo
                                    </p>
                                @else
                                    {{-- IMAGEN ESTÁTICA --}}
                                    <img src="{{ route('archivos.ver', ['path' => $renderPath]) }}" 
                                         class="img-fluid rounded shadow-sm" 
                                         style="max-height: 250px; cursor: pointer;" 
                                         onclick="openImageModal('{{ route('archivos.ver', ['path' => $renderPath]) }}')"
                                         alt="Render Oficial">
                                    <p class="small text-muted mt-2">Diseño oficial cargado</p>
                                @endif
                            @else
                                {{-- ESTADO VACÍO --}}
                                <div class="text-muted">
                                    <i class="bi bi-vector-pen display-4 d-block mb-2"></i>
                                    <p>No se ha cargado el diseño oficial aún.</p>
                                    <p class="small">El diseño oficial estará disponible cuando el equipo de diseño lo apruebe.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Información del Render --}}
                        <div class="col-md-5 ps-md-4">
                            <h6 class="small fw-bold mb-3">Información del Diseño:</h6>
                            <div class="small">
                                @if($renderPath)
                                    <p class="mb-2">
                                        <i class="bi bi-file-earmark me-2"></i>
                                        <strong>Archivo:</strong> {{ basename($renderPath) }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-hdd me-2"></i>
                                        <strong>Tipo:</strong> 
                                        @if($esModelo3D)
                                            Modelo 3D ({{ strtoupper($extension) }})
                                        @else
                                            Imagen ({{ strtoupper($extension) }})
                                        @endif
                                    </p>
                                @endif
                                <p class="mb-2">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Estado:</strong> 
                                    @if($renderPath)
                                        <span class="text-success">Disponible</span>
                                    @else
                                        <span class="text-warning">Pendiente</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Galería de Fotos Finales --}}
    @if(isset($pedido['fotosFinales']) && is_array($pedido['fotosFinales']) && count($pedido['fotosFinales']) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card animate-in animate-delay-2">
                <h5 class="card-title">
                    <i class="bi bi-camera-fill me-2"></i>Galería de Producto Real
                    <span class="badge bg-success ms-2">{{ count($pedido['fotosFinales']) }} fotos</span>
                </h5>
                <div class="row g-2">
                    @foreach($pedido['fotosFinales'] as $index => $foto)
                        @php
                            $fotoPath = $foto['fotImagenFinal'] ?? null;
                        @endphp
                        @if($fotoPath)
                            <div class="col-6 col-md-4 col-lg-3 mb-3">
                                <div class="galeria-item position-relative">
                                    <img src="{{ route('archivos.ver', ['path' => $fotoPath]) }}" 
                                         class="img-fluid rounded border shadow-sm img-thumbnail-gallery" 
                                         style="aspect-ratio: 1/1; object-fit: cover; cursor: pointer;"
                                         onclick="openImageModal('{{ route('archivos.ver', ['path' => $fotoPath]) }}')"
                                         alt="Foto del producto {{ $index + 1 }}">
                                    <div class="galeria-overlay">
                                        <i class="bi bi-zoom-in"></i>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    {{ date('d/m/Y', strtotime($foto['fotFechaSubida'] ?? 'now')) }}
                                </small>
                            </div>
                        @endif
                    @endforeach
                </div>
                <p class="small text-muted mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Haz clic en cualquier imagen para verla en tamaño completo
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card animate-in animate-delay-2">
                <h5 class="card-title">
                    <i class="bi bi-camera-fill me-2"></i>Galería de Producto Real
                </h5>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-camera display-4 d-block mb-2"></i>
                    <p>Aún no hay fotos del producto final.</p>
                    <p class="small">Las fotos estarán disponibles cuando el producto esté terminado.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Línea de Tiempo --}}
    @if(isset($pedido['historial']) && is_array($pedido['historial']) && count($pedido['historial']) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card animate-in animate-delay-3">
                <h5 class="card-title">
                    <i class="bi bi-clock-history me-2"></i>Línea de Tiempo del Pedido
                    <span class="badge bg-info ms-2">{{ count($pedido['historial']) }} cambios</span>
                </h5>
                <div class="timeline">
                    @foreach($pedido['historial'] as $item)
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong>{{ $item['estadoNombre'] ?? 'Estado actualizado' }}</strong>
                                    <small class="text-muted ms-2">
                                        @php
                                            $fecha = null;
                                            if (!empty($item['hisFechaCambio'])) {
                                                try {
                                                    $fecha = new DateTime($item['hisFechaCambio']);
                                                    echo $fecha->format('d/m/Y H:i');
                                                } catch (Exception $e) {
                                                    echo 'Fecha no disponible';
                                                }
                                            } else {
                                                echo 'Fecha no registrada';
                                            }
                                        @endphp
                                    </small>
                                </div>
                                @if($item['hisComentarios'] ?? null)
                                    <p class="mb-1">{{ $item['hisComentarios'] }}</p>
                                @endif
                                @if($item['hisImagen'] ?? null)
                                    <div class="mb-2">
                                        <img src="{{ route('archivos.ver', ['path' => $item['hisImagen']]) }}" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px; cursor: pointer;"
                                             onclick="openImageModal('{{ route('archivos.ver', ['path' => $item['hisImagen']]) }}')"
                                             alt="Imagen del historial">
                                    </div>
                                @endif
                                @if($item['responsableNombre'] ?? null)
                                    <small class="text-muted">
                                        <i class="bi bi-person-fill me-1"></i>
                                        Por: {{ $item['responsableNombre'] }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="info-card animate-in animate-delay-3">
                <h5 class="card-title">
                    <i class="bi bi-clock-history me-2"></i>Línea de Tiempo del Pedido
                </h5>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                    <p>No hay historial de cambios disponible.</p>
                    <p class="small">El historial aparecerá cuando el pedido comience a procesarse.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Modal fullscreen para imágenes --}}
<div id="imageModal" class="modal-fullscreen" onclick="closeImageModal()">
    <span class="modal-fullscreen-close">&times;</span>
    <img id="modalImage" src="" alt="Imagen ampliada">
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
<script>
// Prevenir FOUC - mostrar contenido cuando esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Pequeña demora para asegurar que todos los estilos carguen
    setTimeout(function() {
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.classList.add('loaded');
        }
    }, 100);
});

// Funciones para el visor 3D
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

// Funciones para el modal de imágenes
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').style.display = 'block';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
});

// Debug: Verificar que los datos carguen correctamente
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DEBUG PEDIDO SHOW ===');
    
    // Datos del pedido desde Blade
    const pedidoData = @json($pedido);
    console.log('Pedido:', pedidoData);
    console.log('RenderPath:', pedidoData.renderPath || 'null');
    console.log('FotosFinales:', pedidoData.fotosFinales || []);
    console.log('Historial:', pedidoData.historial || []);
    
    // Verificar si hay datos faltantes
    if (!pedidoData.renderPath) {
        console.warn('⚠️ No hay renderPath disponible');
    }
    
    if (!pedidoData.fotosFinales || pedidoData.fotosFinales.length === 0) {
        console.warn('⚠️ No hay fotosFinales disponibles');
    }
    
    if (!pedidoData.historial || pedidoData.historial.length === 0) {
        console.warn('⚠️ No hay historial disponible');
    }
    
    // Mostrar información detallada del pedido
    console.log('=== INFORMACIÓN COMPLETA DEL PEDIDO ===');
    console.log('Código:', pedidoData.pedCodigo);
    console.log('Estado:', pedidoData.estadoNombre);
    console.log('Tipo:', pedidoData.pedTipo);
    console.log('Metal:', pedidoData.pedMetal);
    console.log('Piedra:', pedidoData.pedPiedra);
    console.log('Comentarios:', pedidoData.pedComentarios);
});
</script>
@endpush
