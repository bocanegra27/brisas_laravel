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
                    @php
                        $cliente = $pedido['clienteDetalles'] ?? null;
                        $tipo = $cliente['tipo'] ?? null;
                        
                        // Configuración por tipo de cliente
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
                            'sin_detalles' => [
                                'nombre' => $cliente['nombre'] ?? $pedido['nombreCliente'] ?? 'Cliente',
                                'correo' => '',
                                'telefono' => '',
                                'label' => 'Sin Detalles',
                                'icon' => 'person-fill',
                                'badge' => 'externo',
                                'mostrarId' => null
                            ],
                            default => [
                                'nombre' => $pedido['nombreCliente'] ?? 'Sin Cliente Asignado',
                                'correo' => '',
                                'telefono' => '',
                                'label' => 'Desconocido',
                                'icon' => 'question-circle',
                                'badge' => 'externo',
                                'mostrarId' => null
                            ]
                        };
                    @endphp

                    @if($config['nombre'] !== 'Sin Cliente Asignado')
                        <div class="client-info">
                            <div class="client-avatar">
                                @if($tipo === 'usuario_registrado')
                                    {{ strtoupper(substr($config['nombre'], 0, 1)) }}
                                @else
                                    <i class="bi bi-person-fill"></i>
                                @endif
                            </div>
                            <div class="client-details">
                                <p class="client-name">{{ $config['nombre'] }}</p>
                                @if($config['correo'])
                                    <p class="client-email"><i class="bi bi-envelope me-1"></i>{{ $config['correo'] }}</p>
                                @endif
                                @if($config['telefono'])
                                    <p class="client-phone text-muted"><i class="bi bi-telephone me-1"></i>{{ $config['telefono'] }}</p>
                                @endif
                                
                                <span class="client-type badge-{{ $config['badge'] }}">
                                    <i class="bi bi-{{ $config['icon'] }}"></i> 
                                    {{ $config['label'] }}
                                </span>
                                
                                {{-- ID del cliente --}}
                                @if($config['mostrarId'])
                                    <span class="text-muted small d-block mt-1">
                                        <i class="bi bi-hash"></i> 
                                        ID {{ $tipo === 'usuario_registrado' ? 'Usuario' : 'Contacto' }}: {{ $config['mostrarId'] }}
                                    </span>
                                @endif
                                
                                {{-- Token de sesión si existe --}}
                                @if(!empty($pedido['sesionToken']))
                                    <span class="client-type badge-sesion mt-1 d-block">
                                        <i class="bi bi-key"></i> Sesión: {{ $pedido['sesionToken'] }}
                                    </span>
                                @endif
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
                    
                    {{--  CONTENEDOR DONDE SE RENDERIZA EL HISTORIAL REAL --}}
                    <div class="timeline-container" id="historialTimeline">
                        <div class="text-center py-5" id="timelineLoading">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted mt-2">Cargando historial...</p>
                        </div>
                        {{-- Aquí se inyectarán las entradas de historial con JS --}}
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
    // ===============================================
    // Configuración Global y Mapeos
    // ===============================================

    // Mapeo de Estados
    const ESTADOS_MAP = {
        1: { nombre: '1. Cotización Pendiente', icono: 'bi-clock-fill', clase: 'pendiente' },
        2: { nombre: '2. Pago Diseño Pendiente', icono: 'bi-check-circle-fill', clase: 'confirmado' },
        3: { nombre: '3. Diseño en Proceso', icono: 'bi-palette-fill', clase: 'diseno' },
        4: { nombre: '4. Diseño Aprobado', icono: 'bi-hand-thumbs-up-fill', clase: 'aprobado' },
        5: { nombre: '5. Tallado (Producción)', icono: 'bi-gear-fill', clase: 'produccion' },
        6: { nombre: '6. Engaste', icono: 'bi-gem', clase: 'produccion' },
        7: { nombre: '7. Pulido', icono: 'bi-sparkle', clase: 'produccion' },
        8: { nombre: '8. Inspección de Calidad', icono: 'bi-shield-check-fill', clase: 'calidad' },
        9: { nombre: '9. Finalizado (Entrega)', icono: 'bi-gift-fill', clase: 'finalizado' },
        10: { nombre: '10. Cancelado', icono: 'bi-x-circle-fill', clase: 'cancelado' }
    };
    
    // Variables globales
    const pedidoId = {{ $pedido['pedId'] ?? 0 }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Cargando historial para pedido ID:', pedidoId);
        
        if (pedidoId && pedidoId > 0) {
            cargarHistorialPedido(pedidoId);
        } else {
            console.error('ID de pedido inválido:', pedidoId);
            document.getElementById('historialTimeline').innerHTML = 
                '<p class="text-danger text-center py-4">Error: ID de pedido no válido.</p>';
        }
    });

    // ===============================================
    // 1. ACTUALIZACIÓN DE ESTADO
    // ===============================================

    function actualizarEstadoPedido(event, pedidoId) {
        event.preventDefault();
        
        const estadoId = document.getElementById('nuevoEstadoSelect').value;
        const comentarios = document.getElementById('comentariosEstado').value;
        
        Swal.fire({
            title: 'Actualizando...',
            text: 'Registrando cambio en el historial',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`/admin/pedidos/${pedidoId}/estado-historial`, { 
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken 
            },
            body: JSON.stringify({
                estadoId: parseInt(estadoId),
                comentarios: comentarios
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || `Error HTTP ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#009688'
                }).then(() => {
                    window.location.reload(); 
                });
            } else {
                throw new Error(data.message || 'Error al actualizar (API).');
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

    // ===============================================
    // 2. CARGA DEL TIMELINE
    // ===============================================

    function cargarHistorialPedido(id) {
        // Validación
        if (!id || id === 0) {
            console.error("ID de pedido no válido:", id);
            document.getElementById('historialTimeline').innerHTML = 
                '<p class="text-danger text-center py-4">Error: ID de pedido no válido.</p>';
            return; 
        }
        
        // Construir la URL correctamente usando template string
        const url = `/admin/pedidos/${id}/historial`;
        
        console.log('Fetching historial desde:', url);

        fetch(url, {
            method: 'GET',
            headers: { 
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            } 
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            const container = document.getElementById('historialTimeline');
            container.innerHTML = '';

            if (data.success && data.historial && data.historial.length > 0) {
                renderizarTimeline(container, data.historial);
            } else {
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-info-circle display-6 d-block mb-2"></i>
                        Aún no hay registros en el historial de este pedido.
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error cargando historial:', error);
            document.getElementById('historialTimeline').innerHTML = 
                `<p class="text-danger text-center py-4">Error: ${error.message}</p>`;
        });
    }
    
    function renderizarTimeline(container, historial) {
        let html = '<div class="timeline-vertical">';
        
        historial.forEach((item, index) => {
            const info = ESTADOS_MAP[item.estId] || { 
                nombre: 'Desconocido', 
                icono: 'bi-question-circle', 
                clase: 'secundario' 
            };
            
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
                            <a href="${item.hisImagen}" target="_blank" class="btn btn-sm btn-outline-secondary">
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

    function verDetallesPersonalizacion(perId) {
        const modal = new bootstrap.Modal(document.getElementById('modalPersonalizacion'));
        modal.show();
        
        const modalContent = document.getElementById('modalPersonalizacionContent');
        modalContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
        
        fetch(`/api/personalizaciones/${perId}/detalles`, {
            headers: {
                'Authorization': 'Bearer ' + (localStorage.getItem('jwt_token') || sessionStorage.getItem('jwt_token') || '')
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
            modalContent.innerHTML = html;
        })
        .catch(error => {
             modalContent.innerHTML = '<p class="text-danger">Error al cargar la personalización</p>';
        });
    }

    function verMensajeOrigen(conId) {
        window.location.href = `/admin/mensajes?highlight=${conId}`;
    }
</script>
@endpush