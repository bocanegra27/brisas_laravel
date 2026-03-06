@extends('layouts.app')

@section('title', 'Mis Pedidos')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/header-minimal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos-estados.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">

    <style>
        .header-minimal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1000 !important;
            background: white !important;
            border-bottom: 1px solid #e9ecef !important;
        }

        body { padding-top: 80px !important; }
        main { padding-top: 0 !important; }

        /* Modal */
        .modal-detalle-pedido { max-width: 95vw !important; width: 95vw !important; }
        .modal-detalle-pedido .modal-content { height: 95vh !important; max-height: 95vh !important; }
        .modal-detalle-pedido .modal-body { height: calc(95vh - 140px) !important; overflow-y: auto !important; padding: 15px !important; }
        @media (min-width: 1400px) {
            .modal-detalle-pedido { max-width: 1400px !important; width: 1400px !important; }
        }

        /* Galería */
        .galeria-item {
            position: relative; overflow: hidden; cursor: pointer;
            transition: all 0.3s ease; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .galeria-item:hover { transform: scale(1.03) translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .galeria-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0.55);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .galeria-item:hover .galeria-overlay { opacity: 1; }
        .galeria-overlay i { color: white; font-size: 26px; }
        .img-thumbnail-gallery { border-radius: 12px; transition: transform 0.3s ease; }

        /* Secciones internas del modal */
        .galeria-section, .render-section {
            background: #f8f9fa; border-radius: 14px; padding: 20px;
            border: 1px solid rgba(229,231,235,0.8);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .galeria-section h6, .render-section h6 {
            color: #1f2937; font-weight: 600; margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .galeria-section h6 i { color: #3b82f6; }
        .render-section h6 i { color: #10b981; }
        .render-container { border-radius: 10px; overflow: hidden; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }

        /* Timeline del modal */
        .timeline {
            background: #f8f9fa; border-radius: 14px; padding: 20px;
            border: 1px solid rgba(229,231,235,0.8);
        }
        .timeline h6 { color: #1f2937; font-weight: 600; margin-bottom: 16px; }
        .timeline-item { position: relative; margin-bottom: 20px; padding-left: 38px; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-marker {
            position: absolute; left: 0; top: 4px;
            width: 22px; height: 22px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 6px rgba(16,185,129,0.3);
        }
        .timeline-marker i { color: white; font-size: 11px; }
        .timeline-content {
            background: #fff; border: 1px solid #e9ecef; border-radius: 10px;
            padding: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s ease;
        }
        .timeline-content:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .timeline-header strong { color: #1f2937; font-weight: 600; }
        .timeline-image-link .btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none; color: white; padding: 6px 14px;
            border-radius: 8px; font-size: 13px;
            box-shadow: 0 2px 6px rgba(59,130,246,0.3);
            transition: all 0.2s ease;
        }
        .timeline-image-link .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(59,130,246,0.4); }

        /* Info del pedido en modal */
        .pedido-detalle {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 16px; padding: 24px;
            border: 1px solid rgba(229,231,235,0.8);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Badges de estado */
        .badge-estado {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.4rem 1rem; border-radius: 50px;
            font-size: 0.78rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.4px;
            white-space: nowrap; border: 1px solid;
            background: rgba(255,255,255,0.9);
        }
        .badge-estado.badge-pendiente  { color: #d97706; border-color: rgba(217,119,6,0.2); background: rgba(217,119,6,0.06); }
        .badge-estado.badge-confirmado { color: #16a34a; border-color: rgba(22,163,74,0.2); background: rgba(22,163,74,0.06); }
        .badge-estado.badge-diseno     { color: #8b5cf6; border-color: rgba(139,92,246,0.2); background: rgba(139,92,246,0.06); }
        .badge-estado.badge-aprobado   { color: #3b82f6; border-color: rgba(59,130,246,0.2); background: rgba(59,130,246,0.06); }
        .badge-estado.badge-produccion { color: #0891b2; border-color: rgba(8,145,178,0.2); background: rgba(8,145,178,0.06); }
        .badge-estado.badge-calidad    { color: #059669; border-color: rgba(5,150,105,0.2); background: rgba(5,150,105,0.06); }
        .badge-estado.badge-listo      { color: #15803d; border-color: rgba(21,128,61,0.2); background: rgba(21,128,61,0.06); }
        .badge-estado.badge-camino     { color: #ea580c; border-color: rgba(234,88,12,0.2); background: rgba(234,88,12,0.06); }
        .badge-estado.badge-entregado  { color: #14532d; border-color: rgba(20,83,45,0.2); background: rgba(20,83,45,0.06); }
        .badge-estado.badge-cancelado  { color: #dc2626; border-color: rgba(220,38,38,0.2); background: rgba(220,38,38,0.06); }
        .badge-estado.badge-secondary  { color: #6b7280; border-color: rgba(107,114,128,0.2); background: rgba(107,114,128,0.06); }

        /* Model Viewer */
        model-viewer { width: 100%; height: 320px; background-color: #f5f5f5; border-radius: 8px; }
        model-viewer::part(default-progress-bar) { background-color: #009688; }
        .viewer-controls { display: flex; gap: 8px; margin-top: 10px; justify-content: center; flex-wrap: wrap; }
        .viewer-controls button, .viewer-controls a {
            padding: 5px 12px; font-size: 12px; border: 1px solid #ddd;
            background: white; border-radius: 6px; cursor: pointer;
            text-decoration: none; color: inherit;
            display: inline-flex; align-items: center; gap: 4px;
            transition: background 0.2s;
        }
        .viewer-controls button:hover, .viewer-controls a:hover { background: #f0f0f0; }

        /* Fullscreen modal */
        .modal-fullscreen {
            display: none; position: fixed; z-index: 9999; inset: 0;
            background-color: rgba(0,0,0,0.95);
            backdrop-filter: blur(8px); cursor: pointer;
            animation: fadeIn 0.25s ease;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-fullscreen img {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%; max-height: 90%; object-fit: contain;
            border-radius: 10px;
            animation: zoomIn 0.25s ease;
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: translate(-50%, -50%) scale(0.85); }
            to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }
        .modal-fullscreen-close {
            position: absolute; top: 18px; right: 30px;
            color: white; font-size: 36px; font-weight: bold;
            z-index: 10000; background: rgba(255,255,255,0.1);
            border-radius: 50%; width: 46px; height: 46px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
        }
        .modal-fullscreen-close:hover { background: rgba(255,255,255,0.22); transform: scale(1.1); }
    </style>
@endpush

@section('content')
<div class="container-fluid py-5">

    {{-- Header --}}
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
                    @foreach(($estados ?? []) as $estado)
                        @php
                            $estadoId   = (int)($estado['id'] ?? 0);
                            $count      = (int)($stats['porEstado'][$estadoId] ?? 0);
                            $badgeClass = match($estadoId) {
                                1 => 'badge-pendiente', 2 => 'badge-confirmado',
                                3 => 'badge-diseno',    4 => 'badge-aprobado',
                                5 => 'badge-produccion',6 => 'badge-calidad',
                                7 => 'badge-listo',     8 => 'badge-camino',
                                9 => 'badge-entregado', 10 => 'badge-cancelado',
                                default => 'badge-secondary'
                            };
                            $icon = match($estadoId) {
                                1 => 'bi-clock-fill',     2 => 'bi-credit-card-fill',
                                3 => 'bi-palette-fill',   4 => 'bi-check2-circle',
                                5 => 'bi-gear-fill',      6 => 'bi-gem',
                                7 => 'bi-stars',          8 => 'bi-truck',
                                9 => 'bi-box-seam-fill',  10 => 'bi-x-circle-fill',
                                default => 'bi-info-circle'
                            };
                        @endphp
                        @if($estadoId && isset($estado['nombre']))
                            <div class="pill-stat">
                                <i class="bi {{ $icon }} estado-color {{ $badgeClass }}"></i>
                                <span class="pill-label">{{ $estado['nombre'] }}:</span>
                                <strong class="pill-value">{{ $count }}</strong>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <a href="{{ url('/') }}" class="btn btn-secondary">
                <i class="bi bi-house me-2"></i>Volver al Inicio
            </a>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-in mt-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate-in mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="my-4"></div>

    {{-- Tabla --}}
    <div class="card shadow-sm animate-in">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchCodigo" class="form-control"
                               placeholder="Buscar por código de pedido..."
                               value="{{ $filtros['codigo'] ?? '' }}">
                    </div>
                </div>
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
                <div class="col-md-3">
                    <select id="pageSize" class="form-select">
                        <option value="10" {{ ($pageSize ?? 10) == 10 ? 'selected' : '' }}>10 por página</option>
                        <option value="25" {{ ($pageSize ?? 10) == 25 ? 'selected' : '' }}>25 por página</option>
                        <option value="50" {{ ($pageSize ?? 10) == 50 ? 'selected' : '' }}>50 por página</option>
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
                                    <td><strong>{{ $pedido['pedCodigo'] ?? 'N/A' }}</strong></td>
                                    <td>
                                        @php
                                            $fechaRaw = $pedido['pedFechaCreacion'] ?? null;
                                            $fechaFormateada = 'N/A';
                                            if (!empty($fechaRaw)) {
                                                try { $fechaFormateada = \Carbon\Carbon::parse($fechaRaw)->format('d/m/Y H:i'); }
                                                catch (\Exception $e) { $fechaFormateada = $fechaRaw; }
                                            }
                                        @endphp
                                        {{ $fechaFormateada }}
                                    </td>
                                    <td>
                                        @php
                                            $estadoCrudo = $pedido['estadoNombre'] ?? ($pedido['estado']['estNombre'] ?? 'desconocido');
                                            $estadoLimpio = $estadoMapeo[$estadoCrudo] ?? $estadoCrudo;
                                            $estadoId = (int)($pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1));
                                            $badgeClass = match($estadoId) {
                                                1 => 'badge-pendiente', 2 => 'badge-confirmado',
                                                3 => 'badge-diseno',    4 => 'badge-aprobado',
                                                5 => 'badge-produccion',6 => 'badge-calidad',
                                                7 => 'badge-listo',     8 => 'badge-camino',
                                                9 => 'badge-entregado', 10 => 'badge-cancelado',
                                                default => 'badge-secondary'
                                            };
                                        @endphp
                                        <span class="badge-estado {{ $badgeClass }}">{{ $estadoLimpio }}</span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-block" style="max-width: 200px;"
                                              title="{{ $pedido['pedComentarios'] ?? 'Sin comentarios' }}">
                                            {{ $pedido['pedComentarios'] ?? 'Sin comentarios' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="verDetalles({{ $pedido['pedId'] ?? $pedido['id'] ?? 0 }})"
                                                    title="Ver detalles y línea de tiempo">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if(isset($pedido['estadoNombre']) && in_array($pedido['estadoNombre'], ['Cotización Pendiente', 'Pago Diseño Pendiente']))
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        onclick="contactarSoporte({{ $pedido['pedId'] ?? $pedido['id'] ?? 0 }})"
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

                @if(($totalPages ?? 0) > 1)
                    <div class="card-footer bg-light">
                        <nav>
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
                                @for($i = 1; $i <= $totalPages; $i++)
                                    <li class="page-item {{ $i == ($currentPage + 1) ? 'active' : '' }}">
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

{{-- Modal de detalles --}}
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-detalle-pedido">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt-cutoff me-2"></i>Detalles del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detallesContenido">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Fullscreen image modal --}}
<div id="imageModal" class="modal-fullscreen" onclick="closeImageModal()">
    <span class="modal-fullscreen-close">&times;</span>
    <img id="modalImage" src="" alt="Imagen ampliada">
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
<script>
// =============================================================
// URL BASE PARA ARCHIVOS — ÚNICA FUENTE DE VERDAD
// Usa la ruta de Laravel generada por Blade para garantizar
// que siempre apunte al proxy correcto de UserPedidoController
// =============================================================
const ARCHIVO_BASE_URL = '{{ url("/user/pedidos/ver-archivo") }}/';

// =============================================================
// VER DETALLES (abre modal y llama al endpoint)
// =============================================================
function verDetalles(pedidoId) {
    if (!pedidoId) return;

    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
    document.getElementById('detallesContenido').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-3 small">Cargando detalles...</p>
        </div>`;
    modal.show();

    fetch(`/user/pedidos/${pedidoId}/detalles`)
        .then(response => {
            if (!response.ok) throw new Error(`Error HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);
            document.getElementById('detallesContenido').innerHTML = renderDetalles(data);
        })
        .catch(error => {
            document.getElementById('detallesContenido').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${error.message || 'Error al cargar los detalles del pedido.'}
                </div>`;
        });
}

// =============================================================
// CONTACTAR SOPORTE
// =============================================================
function contactarSoporte(pedidoId) {
    Swal.fire({
        title: 'Contactar Soporte',
        text: '¿Deseas contactar al equipo de soporte sobre este pedido?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, contactar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3b82f6'
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("contacto.create") }}?pedido=' + pedidoId;
        }
    });
}

// =============================================================
// MAPEO DE ESTADO → CLASE CSS
// =============================================================
function getEstadoBadgeClass(estadoNombre) {
    if (!estadoNombre || estadoNombre === 'N/A') return 'badge-secondary';
    const e = estadoNombre.toLowerCase();
    if (e.includes('cotizaci'))                            return 'badge-pendiente';
    if (e.includes('pago'))                                return 'badge-camino';
    if (e.includes('dise') && e.includes('proceso'))       return 'badge-diseno';
    if (e.includes('dise') && e.includes('aprob'))         return 'badge-aprobado';
    if (e.includes('tallado') || e.includes('producci'))   return 'badge-produccion';
    if (e.includes('engaste'))                             return 'badge-calidad';
    if (e.includes('pulido'))                              return 'badge-pendiente';
    if (e.includes('inspecci') || e.includes('calidad'))   return 'badge-calidad';
    if (e.includes('finalizado') || e.includes('listo'))   return 'badge-listo';
    if (e.includes('entregado'))                           return 'badge-entregado';
    if (e.includes('cancelado'))                           return 'badge-cancelado';
    if (e.includes('confirmado'))                          return 'badge-confirmado';
    return 'badge-secondary';
}

// =============================================================
// RENDER PRINCIPAL DEL MODAL
// =============================================================
function renderDetalles(pedido) {

    // ── HISTORIAL ──────────────────────────────────────────
    let historialHtml = '';
    if (pedido.historial && pedido.historial.length > 0) {
        const items = pedido.historial.map(item => {
            let fecha = 'Fecha no disponible';
            if (item.hisFechaCambio) {
                try {
                    const d = new Date(item.hisFechaCambio);
                    if (!isNaN(d)) fecha = d.toLocaleString('es-ES', {
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                } catch(e) {}
            }

            // ✅ URL de evidencia usando la ruta correcta de Laravel
            const evidenciaHtml = item.hisImagen
                ? `<div class="timeline-image-link mt-2">
                       <a href="${ARCHIVO_BASE_URL}${item.hisImagen}" target="_blank" class="btn btn-sm">
                           <i class="bi bi-image me-1"></i>Ver Evidencia
                       </a>
                   </div>`
                : '';

            return `
                <div class="timeline-item">
                    <div class="timeline-marker"><i class="bi bi-check-lg"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>${item.estadoNombre || item.estNombre || 'Estado actualizado'}</strong>
                            <small class="text-muted">${fecha}</small>
                        </div>
                        ${item.hisComentarios ? `<p class="mb-1 small">${item.hisComentarios}</p>` : ''}
                        ${item.responsableNombre ? `<small class="text-muted"><i class="bi bi-person me-1"></i>${item.responsableNombre}</small>` : ''}
                        ${evidenciaHtml}
                    </div>
                </div>`;
        }).join('');

        historialHtml = `
            <div class="timeline">
                <h6><i class="bi bi-clock-history me-2"></i>Línea de Tiempo</h6>
                ${items}
            </div>`;
    } else {
        historialHtml = `
            <div class="timeline">
                <h6><i class="bi bi-clock-history me-2"></i>Línea de Tiempo</h6>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-info-circle display-6 d-block mb-2"></i>
                    <p class="small">No hay historial disponible aún.</p>
                </div>
            </div>`;
    }

    // ── RENDER / DISEÑO OFICIAL ────────────────────────────
    let renderHtml = '';
    if (pedido.renderPath && pedido.renderPath.trim() !== '') {
        const rp      = pedido.renderPath;
        const isGLB   = rp.toLowerCase().endsWith('.glb') || rp.toLowerCase().endsWith('.gltf');
        const isImg   = ['.jpg','.jpeg','.png','.gif','.webp'].some(e => rp.toLowerCase().endsWith(e));

        // ✅ URL usando la ruta correcta de Laravel
        const fileUrl = `${ARCHIVO_BASE_URL}${rp}`;

        if (isGLB) {
            renderHtml = `
                <div class="render-section mb-3">
                    <h6><i class="bi bi-box me-2"></i>Diseño Oficial 3D</h6>
                    <div class="render-container">
                        <model-viewer src="${fileUrl}" alt="Modelo 3D"
                            auto-rotate camera-controls touch-action="pan-y"
                            style="width:100%;height:300px;background:#f5f5f5;border-radius:8px;"
                            loading="eager">
                        </model-viewer>
                        <div class="viewer-controls mt-2">
                            <button onclick="resetearCamara()"><i class="bi bi-arrow-counterclockwise"></i> Resetear</button>
                            <button onclick="capturarScreenshot()"><i class="bi bi-camera"></i> Capturar</button>
                            <a href="${fileUrl}" download><i class="bi bi-download"></i> Descargar</a>
                        </div>
                    </div>
                </div>`;
        } else if (isImg) {
            renderHtml = `
                <div class="render-section mb-3">
                    <h6><i class="bi bi-vector-pen me-2"></i>Diseño Oficial</h6>
                    <div class="render-container text-center p-3">
                        <img src="${fileUrl}"
                             class="img-fluid rounded shadow-sm"
                             style="max-height:280px;cursor:pointer;"
                             onclick="openImageModal('${fileUrl}')"
                             alt="Render Oficial">
                        <p class="small text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i>Haz clic para ver en tamaño completo
                        </p>
                    </div>
                </div>`;
        } else {
            renderHtml = `
                <div class="render-section mb-3">
                    <h6><i class="bi bi-vector-pen me-2"></i>Diseño Oficial</h6>
                    <div class="text-center p-3">
                        <i class="bi bi-file-earmark display-4 text-muted d-block mb-2"></i>
                        <a href="${fileUrl}" download class="btn btn-sm btn-primary">
                            <i class="bi bi-download me-1"></i>Descargar Archivo
                        </a>
                    </div>
                </div>`;
        }
    } else {
        renderHtml = `
            <div class="render-section mb-3">
                <h6><i class="bi bi-vector-pen me-2"></i>Diseño Oficial</h6>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-hourglass-split display-4 d-block mb-2"></i>
                    <p class="small">El diseño oficial aún está siendo preparado.</p>
                </div>
            </div>`;
    }

    // ── GALERÍA DE FOTOS FINALES ───────────────────────────
    let galeriaHtml = '';
    if (pedido.fotosFinales && pedido.fotosFinales.length > 0) {
        const fotos = pedido.fotosFinales.map((foto, i) => {
            const fotoPath = foto.fotImagenFinal ?? foto.renImagen ?? null;
            if (!fotoPath) return '';

            // ✅ URL usando la ruta correcta de Laravel
            const fotoUrl = `${ARCHIVO_BASE_URL}${fotoPath}`;

            return `
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="galeria-item" onclick="openImageModal('${fotoUrl}')">
                        <img src="${fotoUrl}"
                             class="img-fluid img-thumbnail-gallery"
                             style="aspect-ratio:1/1;object-fit:cover;width:100%;"
                             alt="Foto del producto ${i + 1}"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwIDAgNDAwIDQwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNmMWY1ZjkiLz48dGV4dCB4PSIyMDAiIHk9IjIxMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1mYW1pbHk9InNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiPk5vIGRpc3BvbmlibGU8L3RleHQ+PC9zdmc+';">
                        <div class="galeria-overlay"><i class="bi bi-zoom-in"></i></div>
                    </div>
                </div>`;
        }).join('');

        galeriaHtml = `
            <div class="galeria-section mb-3">
                <h6><i class="bi bi-camera-fill me-2"></i>Galería de Producto Real</h6>
                <div class="row g-2">${fotos}</div>
            </div>`;
    } else {
        galeriaHtml = `
            <div class="galeria-section mb-3">
                <h6><i class="bi bi-camera-fill me-2"></i>Galería de Producto Real</h6>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-image display-4 d-block mb-2"></i>
                    <p class="small">Las fotos estarán disponibles cuando el producto esté terminado.</p>
                </div>
            </div>`;
    }

    // ── LAYOUT FINAL ──────────────────────────────────────
    const fechaStr = pedido.pedFechaCreacion
        ? new Date(pedido.pedFechaCreacion).toLocaleDateString('es-ES')
        : 'N/A';

    return `
        <div class="pedido-detalle">
            <div class="row mb-3">
                <div class="col-md-4">
                    <p class="small text-muted mb-1">CÓDIGO</p>
                    <p class="fs-5 fw-bold text-primary mb-0">${pedido.pedCodigo || 'N/A'}</p>
                </div>
                <div class="col-md-4">
                    <p class="small text-muted mb-1">ESTADO</p>
                    <span class="badge-estado ${getEstadoBadgeClass(pedido.estadoNombre)}">
                        ${pedido.estadoNombre || 'N/A'}
                    </span>
                </div>
                <div class="col-md-4">
                    <p class="small text-muted mb-1">FECHA</p>
                    <p class="mb-0">${fechaStr}</p>
                </div>
            </div>

            ${pedido.pedComentarios ? `
            <div class="row mb-3">
                <div class="col-12">
                    <p class="small text-muted mb-1">COMENTARIOS</p>
                    <p class="small mb-0">${pedido.pedComentarios}</p>
                </div>
            </div>` : ''}

            <div class="row g-3">
                <div class="col-lg-6">${renderHtml}</div>
                <div class="col-lg-6" style="max-height:420px;overflow-y:auto;">${historialHtml}</div>
                <div class="col-lg-6">${galeriaHtml}</div>
            </div>
        </div>`;
}

// =============================================================
// VISOR 3D
// =============================================================
function resetearCamara() {
    const v = document.querySelector('model-viewer');
    if (v) { v.resetTurntableRotation(); v.cameraOrbit = 'auto auto auto'; }
}
function capturarScreenshot() {
    const v = document.querySelector('model-viewer');
    if (v) v.toBlob().then(blob => {
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'render-3d.png';
        a.click();
    });
}

// =============================================================
// FULLSCREEN IMAGE MODAL
// =============================================================
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'block';
}
function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeImageModal(); });

// =============================================================
// FILTROS Y BÚSQUEDA
// =============================================================
document.addEventListener('DOMContentLoaded', function () {
    const searchInput   = document.getElementById('searchCodigo');
    const estadoSelect  = document.getElementById('filterEstado');
    const pageSizeSelect = document.getElementById('pageSize');

    if (searchInput)    searchInput.addEventListener('input', debounce(aplicarFiltros, 350));
    if (estadoSelect)   estadoSelect.addEventListener('change', aplicarFiltros);
    if (pageSizeSelect) pageSizeSelect.addEventListener('change', aplicarFiltros);
});

function aplicarFiltros() {
    const params    = new URLSearchParams(window.location.search);
    const search    = document.getElementById('searchCodigo')?.value;
    const estado    = document.getElementById('filterEstado')?.value;
    const pageSize  = document.getElementById('pageSize')?.value;

    search   ? params.set('codigo',   search)   : params.delete('codigo');
    estado   ? params.set('estadoId', estado)   : params.delete('estadoId');
    pageSize ? params.set('size',     pageSize) : params.delete('size');
    params.delete('page'); // reset a página 0 al filtrar

    window.location.href = '?' + params.toString();
}

function debounce(fn, wait) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}
</script>
@endpush