@extends('layouts.app')

@section('title', 'Mis Pedidos')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/header-minimal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <style>
        /* FORZAR HEADER ARRIBA - CORRECCIÓN DEFINITIVA */
        .header-minimal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1000 !important;
            background: white !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        
        /* Espacio para header fijo */
        body {
            padding-top: 80px !important;
        }
        
        main {
            padding-top: 0 !important;
        }
        
        /* Modal más ancho para mejor visualización */
        .modal-detalle-pedido {
            max-width: 95vw !important;
            width: 95vw !important;
        }
        
        .modal-detalle-pedido .modal-content {
            height: 95vh !important;
            max-height: 95vh !important;
        }
        
        .modal-detalle-pedido .modal-body {
            height: calc(95vh - 140px) !important;
            overflow-y: auto !important;
            padding: 15px !important;
        }
        
        /* Ajustes para pantallas grandes */
        @media (min-width: 1400px) {
            .modal-detalle-pedido {
                max-width: 1400px !important;
                width: 1400px !important;
            }
        }
        
        /* Estilos mejorados para la galería de productos */
        .galeria-item {
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .galeria-item:hover {
            transform: scale(1.03) translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .galeria-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(2px);
        }
        
        .galeria-item:hover .galeria-overlay {
            opacity: 1;
        }
        
        .galeria-overlay i {
            color: white;
            font-size: 28px;
            transform: scale(0.8);
            transition: all 0.3s ease;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .galeria-item:hover .galeria-overlay i {
            transform: scale(1);
        }
        
        .img-thumbnail-gallery {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
        }
        
        .img-thumbnail-gallery:hover {
            transform: scale(1.05);
        }
        
        /* Mejoras visuales para la sección de galería */
        .galeria-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }
        
        .galeria-section h6 {
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .galeria-section h6 i {
            color: #3b82f6;
        }
        
        /* Mejoras visuales para la sección de render */
        .render-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }
        
        .render-section h6 {
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .render-section h6 i {
            color: #10b981;
        }
        
        .render-container {
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Mejoras visuales para la línea de tiempo */
        .timeline {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }
        
        .timeline h6 {
            color: #1f2937;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .timeline h6::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 2px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 24px;
            padding-left: 40px;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-marker {
            position: absolute;
            left: 0;
            top: 6px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }
        
        .timeline-marker i {
            color: white;
            font-size: 12px;
        }
        
        .timeline-content {
            background: #ffffff;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .timeline-content:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .timeline-header strong {
            color: #1f2937;
            font-weight: 600;
        }
        
        .timeline-image-link {
            margin-top: 12px;
        }
        
        .timeline-image-link .btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        
        .timeline-image-link .btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }
        
        /* Mejoras para la información básica */
        .pedido-detalle {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(229, 231, 235, 0.8);
        }
        
        .pedido-detalle .small.text-muted {
            color: #6b7280 !important;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Estados de pedidos más coloridos y atractivos */
        .estado-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .estado-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .estado-badge:hover::before {
            left: 100%;
        }
        
        .estado-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Estados específicos con colores vibrantes */
        .estado-cotizacion_pendiente {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            border: 1px solid #f59e0b;
        }
        
        .estado-pago_diseno_pendiente {
            background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
            color: white;
            border: 1px solid #f97316;
        }
        
        .estado-diseno_en_proceso {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: white;
            border: 1px solid #3b82f6;
        }
        
        .estado-diseno_aprobado {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: white;
            border: 1px solid #10b981;
        }
        
        .estado-tallado_produccion {
            background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
            color: white;
            border: 1px solid #8b5cf6;
        }
        
        .estado-engaste {
            background: linear-gradient(135deg, #f472b6 0%, #ec4899 100%);
            color: white;
            border: 1px solid #ec4899;
        }
        
        .estado-pulido {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            border: 1px solid #f59e0b;
        }
        
        .estado-inspeccion_calidad {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            border: 1px solid #0891b2;
        }
        
        .estado-finalizado_listo_entrega {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: 1px solid #059669;
            animation: pulse-success 2s infinite;
        }
        
        .estado-cancelado {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: 1px solid #dc2626;
        }
        
        .estado-desconocido {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            border: 1px solid #4b5563;
        }
        
        @keyframes pulse-success {
            0%, 100% {
                box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
            }
            50% {
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.6);
            }
        }
        
        /* Indicador de estado activo en línea de tiempo */
        .timeline-item.active .timeline-marker {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            animation: pulse-marker 2s infinite;
        }
        
        @keyframes pulse-marker {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
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

        /* Mejoras para el modal fullscreen de imágenes */
        .modal-fullscreen {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            cursor: pointer;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-fullscreen img {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: zoomIn 0.3s ease;
        }
        
        @keyframes zoomIn {
            from { 
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to { 
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
        .modal-fullscreen-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            transition: all 0.3s ease;
            z-index: 10000;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .modal-fullscreen-close:hover {
            color: #f3f4f6;
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-5">
    {{-- Header con Stats Pills --}}
    <div class="dashboard-header animate-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-cart-check-fill me-3"></i>Mis Pedidos</h1>
                <div class="stats-pills mt-3">
                    <div class="pill-stat">
                        <i class="bi bi-receipt-cutoff text-primary"></i>
                        <span class="pill-label">Total:</span>
                        <strong class="pill-value">{{ $totalElements ?? 0 }}</strong>
                    </div>
                    <div class="pill-stat">
                        <i class="bi bi-clock-fill" style="color: #f59e0b;"></i>
                        <span class="pill-label">Pendientes:</span>
                        <strong class="pill-value">{{ $stats['pendientes'] ?? 0 }}</strong>
                    </div>
                    <div class="pill-stat">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span class="pill-label">Confirmados:</span>
                        <strong class="pill-value">{{ $stats['confirmados'] ?? 0 }}</strong>
                    </div>
                    <div class="pill-stat">
                        <i class="bi bi-gear-fill" style="color: #3b82f6;"></i>
                        <span class="pill-label">Producción:</span>
                        <strong class="pill-value">{{ $stats['produccion'] ?? 0 }}</strong>
                    </div>
                    <div class="pill-stat">
                        <i class="bi bi-box-seam-fill" style="color: #10b981;"></i>
                        <span class="pill-label">Entregados:</span>
                        <strong class="pill-value">{{ $stats['entregados'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
            <a href="{{ url('/') }}" class="btn btn-secondary">
                <i class="bi bi-house me-2"></i>Volver al Inicio
            </a>
        </div>
    </div>

    {{-- Mensajes de exito/error --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Separador visual --}}
    <div class="my-4"></div>

    {{-- Tabla de pedidos --}}
    <div class="card shadow-sm animate-in">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center g-3">
                {{-- Buscador --}}
                <div class="col-md-5">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchCodigo" class="form-control" 
                               placeholder="Buscar por código de pedido..."
                               value="{{ $filtros['codigo'] ?? '' }}">
                    </div>
                </div>
                
                {{-- Filtro por estado --}}
                <div class="col-md-4">
                    <select id="filterEstado" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $estado)
                        <option value="{{ $estado['id'] }}" 
                            {{ (isset($filtros['estadoId']) && $filtros['estadoId'] == $estado['id']) ? 'selected' : '' }}>
                            {{ $estado['nombre'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Tamaño de página --}}
                <div class="col-md-3">
                    <select id="pageSize" class="form-select">
                        <option value="10" {{ $pageSize == 10 ? 'selected' : '' }}>10 por página</option>
                        <option value="25" {{ $pageSize == 25 ? 'selected' : '' }}>25 por página</option>
                        <option value="50" {{ $pageSize == 50 ? 'selected' : '' }}>50 por página</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if(isset($pedidos) && count($pedidos) > 0)
            <div class="table-responsive">
                <table class="table pedidos-table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Comentarios</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidos as $pedido)
                        <tr>
                            <td>
                                <strong>{{ $pedido['pedCodigo'] ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                {{ $pedido['pedFechaCreacion'] ?? 'N/A' }}
                            </td>
                            <td>
                                @if(isset($pedido['estadoNombre']))
                                    <span class="estado-badge estado-{{ Str::slug($pedido['estadoNombre'], '_') }}">
                                        {{ $pedido['estadoNombre'] }}
                                    </span>
                                @else
                                    <span class="estado-badge estado-desconocido">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-truncate d-block" style="max-width: 200px;" 
                                      title="{{ $pedido['pedComentarios'] ?? 'Sin comentarios' }}">
                                    {{ $pedido['pedComentarios'] ?? 'Sin comentarios' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    {{-- Ver detalles y línea de tiempo --}}
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="verDetalles({{ $pedido['pedId'] ?? $pedido['id'] ?? 'N/A' }})"
                                            title="Ver detalles y línea de tiempo">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    {{-- Contactar soporte si está pendiente --}}
                                    @if(isset($pedido['estadoNombre']) && in_array($pedido['estadoNombre'], ['Cotización Pendiente', 'Pago Diseño Pendiente']))
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="contactarSoporte({{ $pedido['pedId'] ?? $pedido['id'] ?? 'N/A' }})"
                                            title="Contactar soporte">
                                        <i class="bi bi-chat-dots"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Paginación --}}
            @if($totalPages > 1)
            <div class="card-footer bg-light">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        {{-- Lógica de paginación simplificada --}}
                        @for($i = 1; $i <= $totalPages; $i++)
                        <li class="page-item {{ $i == $currentPage + 1 ? 'active' : '' }}">
                            <a class="page-link" href="?page={{ $i - 1 }}&size={{ $pageSize }}">{{ $i }}</a>
                        </li>
                        @endfor
                    </ul>
                </nav>
            </div>
            @endif
            
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="text-muted mt-3">No tienes pedidos registrados</h5>
                <p class="text-muted">Cuando realices tu primer pedido, aparecerá aquí.</p>
                <a href="{{ route('personalizar.index') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Realizar mi primer pedido
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal para ver detalles y línea de tiempo --}}
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-detalle-pedido">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesContenido">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
function verDetalles(pedidoId) {
    // Mostrar modal con spinner
    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
    document.getElementById('detallesContenido').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    modal.show();
    
    // Cargar detalles del pedido
    fetch(`/user/pedidos/${pedidoId}/detalles`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            // Aquí renderizamos los detalles y la línea de tiempo
            document.getElementById('detallesContenido').innerHTML = renderDetalles(data);
        })
        .catch(error => {
            document.getElementById('detallesContenido').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${error.message || 'Error al cargar los detalles del pedido.'}
                </div>
            `;
        });
}

function contactarSoporte(pedidoId) {
    Swal.fire({
        title: 'Contactar Soporte',
        text: '¿Deseas contactar al equipo de soporte sobre este pedido?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, contactar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir a contacto o abrir chat
            window.location.href = '{{ route("contacto.create") }}?pedido=' + pedidoId;
        }
    });
}

function renderDetalles(pedido) {
    console.log('=== INICIO RENDER DETALLES ===');
    console.log('Pedido completo:', pedido);
    
    // Implementar renderizado de detalles y línea de tiempo
    let historialHtml = '';
    
    if (pedido.historial && pedido.historial.length > 0) {
        historialHtml = `
            <div class="timeline mt-4">
                <h6 class="mb-3">Línea de Tiempo del Pedido</h6>
                <div class="timeline-container">
        `;
        
        pedido.historial.forEach((item, index) => {
            // Manejo robusto de fechas
            let fecha = 'Fecha no disponible';
            if (item.hisFechaCambio) {
                try {
                    const dateObj = new Date(item.hisFechaCambio);
                    if (!isNaN(dateObj.getTime())) {
                        fecha = dateObj.toLocaleDateString('es-ES', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                } catch (error) {
                    console.warn('Error al procesar fecha:', item.hisFechaCambio, error);
                    fecha = 'Fecha inválida';
                }
            }
            
            historialHtml += `
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>${item.estadoNombre || 'Estado actualizado'}</strong>
                            <small class="text-muted ms-2">${fecha}</small>
                        </div>
                        ${item.hisComentarios ? `<p class="mb-1">${item.hisComentarios}</p>` : ''}
                        ${item.usuarioResponsable ? `<small class="text-muted">Por: ${item.usuarioResponsable}</small>` : ''}
                        ${item.hisImagen ? `<div class="timeline-image-link mt-2">
                            <a href="/api/archivos/proxy/${item.hisImagen}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-image"></i> Ver Evidencia
                            </a>
                        </div>` : ''}
                    </div>
                </div>
            `;
        });
        
        historialHtml += `
                </div>
            </div>
        `;
    } else {
        historialHtml = '<p class="text-muted">No hay historial disponible para este pedido.</p>';
    }
    
    // Render del diseño oficial
    let renderHtml = '';
    console.log('=== VERIFICANDO RENDER ===');
    console.log('renderPath:', pedido.renderPath);
    console.log('Tipo de renderPath:', typeof pedido.renderPath);
    console.log('Valor truthy:', !!pedido.renderPath);
    
    if (pedido.renderPath && pedido.renderPath.trim() !== '') {
        console.log('Render encontrado y válido:', pedido.renderPath);
        
        // Determinar el tipo de archivo
        const isGLB = pedido.renderPath.toLowerCase().endsWith('.glb');
        const isImage = ['.jpg', '.jpeg', '.png', '.gif', '.webp'].some(ext => 
            pedido.renderPath.toLowerCase().endsWith(ext)
        );
        
        const baseUrl = window.location.origin;
        const imageUrl = `${baseUrl}/api/archivos/proxy/${pedido.renderPath}`;
        
        console.log('URL de render generada:', imageUrl);
        console.log('Es GLB:', isGLB, 'Es imagen:', isImage);
        
        if (isGLB) {
            // Para archivos GLB, mostrar visor 3D interactivo como en el admin
            renderHtml = `
                <div class="render-section mb-4">
                    <h6 class="mb-3">
                        <i class="bi bi-vector-pen me-2"></i>Diseño Oficial 3D
                    </h6>
                    <div class="render-container">
                        <model-viewer
                            src="${imageUrl}"
                            alt="Modelo 3D del diseño"
                            auto-rotate
                            camera-controls
                            touch-action="pan-y"
                            style="width: 100%; height: 350px; background-color: #f5f5f5; border-radius: 8px;"
                            loading="eager">
                        </model-viewer>
                        
                        <div class="viewer-controls">
                            <button onclick="resetearCamara()" title="Resetear cámara">
                                <i class="bi bi-arrow-counterclockwise"></i> Resetear
                            </button>
                            <button onclick="capturarScreenshot()" title="Capturar pantalla">
                                <i class="bi bi-camera"></i> Capturar
                            </button>
                            <a href="${imageUrl}" download="${pedido.renderPath}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Descargar
                            </a>
                        </div>
                        
                        <p class="small text-muted mt-2">
                            <i class="bi bi-box"></i> Modelo 3D interactivo - Usa el mouse para rotar y hacer zoom
                        </p>
                    </div>
                </div>
            `;
        } else if (isImage) {
            // Para imágenes, mostrar normalmente
            renderHtml = `
                <div class="render-section mb-4">
                    <h6 class="mb-3">
                        <i class="bi bi-vector-pen me-2"></i>Diseño Oficial
                    </h6>
                    <div class="render-container">
                        <img src="${imageUrl}" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 300px; cursor: pointer;" 
                             onclick="openImageModal('${imageUrl}')"
                             onerror="console.error('Error cargando render:', '${pedido.renderPath}');"
                             onload="console.log('Render cargado exitosamente:', this.src);"
                             alt="Render Oficial">
                        <p class="small text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Haz clic en la imagen para verla en tamaño completo
                        </p>
                    </div>
                </div>
            `;
        } else {
            // Para otros tipos de archivo
            renderHtml = `
                <div class="render-section mb-4">
                    <h6 class="mb-3">
                        <i class="bi bi-vector-pen me-2"></i>Diseño Oficial
                    </h6>
                    <div class="render-container text-center">
                        <div class="alert alert-warning">
                            <i class="bi bi-file-earmark display-4 d-block mb-3"></i>
                            <h5>Archivo de Diseño Disponible</h5>
                            <p class="mb-3">Formato de archivo no compatible con vista previa</p>
                            <a href="${imageUrl}" download="${pedido.renderPath}" 
                               class="btn btn-primary">
                                <i class="bi bi-download me-2"></i>Descargar Archivo
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }
    } else {
        console.log('No hay render path o está vacío');
        renderHtml = `
            <div class="render-section mb-4">
                <h6 class="mb-3">
                    <i class="bi bi-vector-pen me-2"></i>Diseño Oficial
                </h6>
                <div class="text-center text-muted p-4 border rounded">
                    <i class="bi bi-hourglass-split display-4 mb-2"></i>
                    <p>El diseño oficial aún está siendo preparado por nuestro equipo de diseño.</p>
                    <small>Te notificaremos cuando esté disponible para su revisión.</small>
                </div>
            </div>
        `;
    }
    
    // Galería de productos finales
    let galeriaHtml = '';
    if (pedido.fotosFinales && pedido.fotosFinales.length > 0) {
        console.log('Fotos encontradas:', pedido.fotosFinales); // Debug
        galeriaHtml = `
            <div class="galeria-section mb-4">
                <h6 class="mb-3">
                    <i class="bi bi-camera-fill me-2"></i>Galería de Producto Real
                </h6>
                <div class="row g-2">
        `;
        
        pedido.fotosFinales.forEach((foto, index) => {
            // El campo correcto es 'fotImagenFinal' según el admin
            const fotoPath = foto.fotImagenFinal || foto.fotImagenFinal || foto.renImagen;
            console.log('Procesando foto:', foto, 'Path:', fotoPath); // Debug
            
            if (fotoPath) {
                const baseUrl = window.location.origin;
                const imageUrl = `${baseUrl}/api/archivos/proxy/${fotoPath}`;
                
                galeriaHtml += `
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="galeria-item position-relative" onclick="openImageModal('${imageUrl}')" style="cursor: pointer;">
                            <img src="${imageUrl}" 
                                 class="img-fluid rounded border shadow-sm img-thumbnail-gallery" 
                                 style="aspect-ratio: 1/1; object-fit: cover; pointer-events: none;"
                                 onerror="console.error('Error cargando imagen:', '${fotoPath}'); this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwIDAgNDAwIDQwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNmMWY1ZjkiLz48Y2lyY2xlIGN4PSIyMDAiIGN5PSIxODAiIHI9IjYwIiBmaWxsPSIjY2JkNWUxIi8+PHBhdGggZD0iTTE0MCAyNjAgTDI2MCAyNjAgTDIwMCAzMjAgWiIgZmlsbD0iI2NiZDVlMSIvPjx0ZXh0IHg9IjIwMCIgeT0iMzYwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTYiIGZpbGw9IiM2NDc0OGIiPkltYWdlbiBubyBkaXNwb25pYmxlPC90ZXh0Pjwvc3ZnPg==';"
                                 onload="console.log('Foto cargada exitosamente:', this.src);"
                                 alt="Foto del producto ${index + 1}">
                            <div class="galeria-overlay">
                                <i class="bi bi-zoom-in"></i>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                console.warn('Foto sin path:', foto); // Debug
            }
        });
        
        galeriaHtml += `
                </div>
            </div>
        `;
    } else {
        console.log('No hay fotos finales'); // Debug
        galeriaHtml = `
            <div class="galeria-section mb-4">
                <h6 class="mb-3">
                    <i class="bi bi-camera-fill me-2"></i>Galería de Producto Real
                </h6>
                <div class="text-center text-muted p-4 border rounded">
                    <i class="bi bi-image display-4 mb-2"></i>
                    <p>Aún no hay fotos del producto final disponibles.</p>
                    <small>Las fotos se agregarán cuando el producto esté terminado y listo para entrega.</small>
                </div>
            </div>
        `;
    }
    
    return `
        <div class="pedido-detalle">
            <!-- Información básica compacta -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <h6 class="small text-muted mb-1">Código</h6>
                    <p class="fs-5 fw-bold text-primary mb-0">${pedido.pedCodigo || 'N/A'}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="small text-muted mb-1">Estado</h6>
                    <div class="mt-2">
                        <span class="estado-badge estado-${pedido.estadoNombre ? pedido.estadoNombre.toLowerCase().replace(/\s+/g, '_') : 'desconocido'}">
                            ${pedido.estadoNombre || 'N/A'}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="small text-muted mb-1">Fecha</h6>
                    <p class="mb-0">${new Date(pedido.pedFechaCreacion).toLocaleDateString('es-ES')}</p>
                </div>
            </div>
            
            ${pedido.pedComentarios ? `
            <div class="row mb-3">
                <div class="col-12">
                    <h6 class="small text-muted mb-1">Comentarios</h6>
                    <p class="small mb-0">${pedido.pedComentarios}</p>
                </div>
            </div>
            ` : ''}
            
            <!-- Grid 2x2 para contenido principal -->
            <div class="row g-3">
                <!-- Render (arriba izquierda) -->
                <div class="col-lg-6">
                    <div class="h-100">
                        ${renderHtml}
                    </div>
                </div>
                
                <!-- Línea de tiempo (arriba derecha) -->
                <div class="col-lg-6">
                    <div class="h-100 overflow-auto" style="max-height: 400px;">
                        ${historialHtml}
                    </div>
                </div>
                
                <!-- Galería (abajo izquierda) -->
                <div class="col-lg-6">
                    ${galeriaHtml}
                </div>
                
                <!-- Espacio reservado para balance -->
                <div class="col-lg-6">
                    <!-- Contenido adicional si es necesario -->
                </div>
            </div>
        </div>
    `;
}

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

// Filtros y búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchCodigo');
    const estadoSelect = document.getElementById('filterEstado');
    const pageSizeSelect = document.getElementById('pageSize');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            aplicarFiltros();
        }, 300));
    }
    
    if (estadoSelect) {
        estadoSelect.addEventListener('change', aplicarFiltros);
    }
    
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', aplicarFiltros);
    }
});

function aplicarFiltros() {
    const params = new URLSearchParams(window.location.search);
    
    const search = document.getElementById('searchCodigo').value;
    const estado = document.getElementById('filterEstado').value;
    const pageSize = document.getElementById('pageSize').value;
    
    if (search) params.set('codigo', search);
    if (estado) params.set('estadoId', estado);
    if (pageSize) params.set('size', pageSize);
    
    window.location.href = '?' + params.toString();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush
@endsection
