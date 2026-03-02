@extends('layouts.app')

@section('title', 'Gestión de Pedidos - Brisas Gems')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos-estados.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="pedidos-container">
    <div class="container-fluid py-5">
        {{-- Header con Stats Pills --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-cart-check-fill me-3"></i>Gestión de Pedidos</h1>
                    <p class="text-muted mb-0">Bienvenido {{ Session::get('user_name', 'Administrador') }}</p>
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-receipt-cutoff text-primary"></i>
                            <span class="pill-label">Total:</span>
                            <strong class="pill-value">{{ $stats['total'] ?? 0 }}</strong>
                        </div>
                        @php
                            $estadoPillConfig = [
                                1 => ['icon' => 'bi-clock-fill', 'color' => '#f59e0b'],
                                2 => ['icon' => 'bi-credit-card-fill', 'color' => '#22c55e'],
                                3 => ['icon' => 'bi-palette-fill', 'color' => '#8b5cf6'],
                                4 => ['icon' => 'bi-check2-circle', 'color' => '#3b82f6'],
                                5 => ['icon' => 'bi-gear-fill', 'color' => '#06b6d4'],
                                6 => ['icon' => 'bi-gem', 'color' => '#10b981'],
                                7 => ['icon' => 'bi-stars', 'color' => '#16a34a'],
                                8 => ['icon' => 'bi-truck', 'color' => '#fb923c'],
                                9 => ['icon' => 'bi-box-seam-fill', 'color' => '#15803d'],
                                10 => ['icon' => 'bi-x-circle-fill', 'color' => '#ef4444'],
                            ];
                        @endphp

                        @foreach(($estados ?? []) as $estado)
                            @php
                                $estadoId = $estado['id'] ?? null;
                                $estadoNombre = $estado['nombre'] ?? null;
                                $conf = $estadoId ? ($estadoPillConfig[$estadoId] ?? null) : null;
                                $icon = $conf['icon'] ?? 'bi-info-circle';
                                $color = $conf['color'] ?? '#6b7280';
                                $count = $estadoId ? ($stats['porEstado'][$estadoId] ?? 0) : 0;
                            @endphp
                            @if($estadoId && $estadoNombre)
                                <div class="pill-stat">
                                    <i class="bi {{ $icon }}" style="color: {{ $color }};"></i>
                                    <span class="pill-label">{{ $estadoNombre }}:</span>
                                    <strong class="pill-value">{{ $count }}</strong>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    @if(Session::get('user_role') === 'ROLE_ADMINISTRADOR')
                    <button
                        type="button"
                        class="btn btn-dark d-inline-flex align-items-center gap-2"
                        style="border-radius: 999px; padding: 0.4rem 1.1rem; font-size: 0.85rem; font-weight: 600;"
                        data-bs-toggle="modal"
                        data-bs-target="#modalCrearPedido">
                        <i class="bi bi-plus-lg" style="font-size: 0.8rem;"></i>
                        Nuevo Pedido
                    </button>
                    @endif
                </div>
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

        {{-- Tabla de pedidos (componente maestro) --}}
        @include('components.pedidos.tabla-listado', ['pedidos' => $pedidos, 'filtros' => $filtros, 'estados' => $estados, 'pageSize' => $pageSize, 'estadoMapeo' => $estadoMapeo])
    </div>
</div>

{{-- Modal para asignar/reasignar diseñador --}}
<div class="modal fade" id="modalAsignarDisenador" tabindex="-1" aria-labelledby="modalAsignarDisenadorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarDisenadorLabel">Asignar Diseñador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAsignarDisenador">
                    <input type="hidden" id="pedidoId" name="pedidoId">
                    <div class="mb-3">
                        <label for="disenadorId" class="form-label">Seleccionar Diseñador:</label>
                        <select id="disenadorId" name="disenadorId" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <!-- Los diseñadores se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Diseñador actual: <span id="disenadorActual">No asignado</span></small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="asignarDisenador()">Asignar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: CREAR PEDIDO --}}
@if(Session::get('user_role') === 'ROLE_ADMINISTRADOR')
<div class="modal fade" id="modalCrearPedido" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-gem me-2" style="color: #009688;"></i>Nuevo Pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Estado inicial</label>
                    <select id="cpEstado" class="form-select">
                        @foreach($estados as $estado)
                            <option value="{{ $estado['id'] }}" {{ $estado['id'] == 1 ? 'selected' : '' }}>
                                {{ $estado['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de cliente</label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="cpTipoCliente" id="cpTipoRegistrado" value="registrado" checked>
                        <label class="btn btn-sm btn-outline-secondary flex-fill text-center" for="cpTipoRegistrado">
                            <i class="bi bi-person-check me-1"></i>Registrado
                        </label>
                        <input type="radio" class="btn-check" name="cpTipoCliente" id="cpTipoExterno" value="externo">
                        <label class="btn btn-sm btn-outline-secondary flex-fill text-center" for="cpTipoExterno">
                            <i class="bi bi-telephone me-1"></i>Externo
                        </label>
                    </div>
                </div>

                {{-- Cliente registrado: select directo con la lista ya cargada --}}
                <div id="bloqueRegistrado" class="mb-3">
                    <label class="form-label">Cliente</label>
                    <select id="cpUsuIdCliente" class="form-select">
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes ?? [] as $cliente)
                            <option value="{{ $cliente['id'] }}">
                                {{ $cliente['nombre'] }} — {{ $cliente['correo'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Cliente externo: texto libre --}}
                <div id="bloqueExterno" class="mb-3" style="display:none;">
                    <label class="form-label">Nombre y teléfono</label>
                    <input type="text" id="cpIdentificador" class="form-control"
                           placeholder="Ej: Laura Martinez - 3101234567">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Diseñador asignado <span class="text-muted fw-normal small">(opcional)</span>
                    </label>
                    <select id="cpEmpleado" class="form-select">
                        <option value="">Sin asignar</option>
                        @foreach($disenadores ?? [] as $d)
                            <option value="{{ $d['usuId'] ?? $d['id'] }}">
                                {{ $d['usuNombre'] ?? $d['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label">
                        Comentarios <span class="text-muted fw-normal small">(opcional)</span>
                    </label>
                    <textarea id="cpComentarios" class="form-control" rows="2"
                              placeholder="Notas internas..."></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark fw-semibold" onclick="submitCrearPedido()">
                    <i class="bi bi-plus-lg me-1"></i>Crear Pedido
                </button>
            </div>
        </div>
    </div>
</div>
@endif


{{-- MODAL: ASIGNAR CLIENTE --}}
@if(Session::get('user_role') === 'ROLE_ADMINISTRADOR')
<div class="modal fade" id="modalAsignarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2" style="color: #009688;"></i>Asignar Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Pedido: <strong id="acCodigoPedido"></strong>
                </p>

                <div class="mb-3">
                    <label class="form-label">Cliente registrado</label>
                    <select id="acUsuIdCliente" class="form-select">
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes ?? [] as $cliente)
                            <option value="{{ $cliente['id'] }}">
                                {{ $cliente['nombre'] }} — {{ $cliente['correo'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" id="acPedidoId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark fw-semibold" onclick="confirmarAsignarCliente()">
                    <i class="bi bi-person-check me-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const estadosDisponibles = @json($estados);
    const disenadoresDisponibles = @json($disenadores);
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Configurar modal para asignar diseñador
    const modalAsignarDisenador = document.getElementById('modalAsignarDisenador');
    if (modalAsignarDisenador) {
        modalAsignarDisenador.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const pedidoId = button.getAttribute('data-pedidoid');
            const actualDisenadorId = button.getAttribute('data-actualdisenadorid');
            const actualDisenadorNombre = button.getAttribute('data-actualdisenadornombre');
            
            document.getElementById('pedidoId').value = pedidoId;
            document.getElementById('disenadorActual').textContent = actualDisenadorNombre || 'No asignado';
            
            // Cargar diseñadores disponibles
            cargarDisenadores(disenadoresDisponibles);
        });
    }
});

// Función para cambiar estado del pedido
function cambiarEstadoPedido(pedidoId, estadoActualId) {
    console.log('cambiarEstadoPedido llamado con:', { pedidoId, estadoActualId });
    
    const estadosDisponibles = @json($estados);
    console.log('Estados disponibles:', estadosDisponibles);
    
    // Generar opciones de estados dinámicamente
    let opcionesHtml = '<option value="">Seleccionar...</option>';
    // $estados viene como array de objetos: [{id, nombre}, ...]
    if (Array.isArray(estadosDisponibles)) {
        estadosDisponibles.forEach((estado) => {
            const id = estado?.id;
            const nombre = estado?.nombre;
            if (!id || !nombre) return;
            if (String(id) !== String(estadoActualId)) {
                opcionesHtml += `<option value="${id}">${nombre}</option>`;
            }
        });
    } else {
        // Fallback si en algún entorno viene como mapa {id: nombre}
        for (const [id, nombre] of Object.entries(estadosDisponibles)) {
            if (String(id) !== String(estadoActualId)) {
                opcionesHtml += `<option value="${id}">${nombre}</option>`;
            }
        }
    }
    
    console.log('Opciones HTML generadas:', opcionesHtml);
    
    Swal.fire({
        title: 'Cambiar Estado',
        html: `
            <div class="text-start">
                <p>Selecciona el nuevo estado para este pedido:</p>
                <select id="nuevoEstado" class="form-select">
                    ${opcionesHtml}
                </select>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const nuevoEstado = document.getElementById('nuevoEstado').value;
            console.log('Estado seleccionado:', nuevoEstado);
            if (!nuevoEstado) {
                Swal.showValidationMessage('Por favor selecciona un estado');
                return false;
            }
            return { nuevoEstado };
        }
    }).then((result) => {
        console.log('Resultado de SweetAlert:', result);
        if (result.isConfirmed) {
            // Realizar la llamada a la API para cambiar el estado
            const url = `/admin/pedidos/${pedidoId}/estado`;
            console.log('Enviando petición a:', url);
            
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nuevoEstadoId: Number(result.value.nuevoEstado),
                    comentarios: null
                })
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.success) {
                    Swal.fire('Éxito', 'Estado actualizado correctamente', 'success')
                    .then(() => {
                        location.reload(); // Recargar para ver los cambios
                    });
                } else {
                    Swal.fire('Error', data.message || 'No se pudo actualizar el estado', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrió un error al actualizar el estado', 'error');
            });
        }
    });
}

// Función para cargar diseñadores disponibles
function cargarDisenadores(disenadoresDisponibles) {
    console.log('Cargando diseñadores (desde servidor Laravel)...');
    console.log('Diseñadores disponibles:', disenadoresDisponibles);

    const select = document.getElementById('disenadorId');
    if (!select) {
        console.error('No se encontró el select disenadorId');
        return;
    }

    select.innerHTML = '<option value="">Seleccionar...</option>';

    if (!Array.isArray(disenadoresDisponibles) || disenadoresDisponibles.length === 0) {
        select.innerHTML = '<option value="">No hay diseñadores disponibles</option>';
        return;
    }

    disenadoresDisponibles.forEach((usuario) => {
        const usuId = usuario?.usuId ?? usuario?.id;
        const usuNombre = usuario?.usuNombre ?? usuario?.nombre;
        const usuApellido = usuario?.usuApellido ?? usuario?.apellido;
        if (!usuId) return;

        const option = document.createElement('option');
        option.value = usuId;
        option.textContent = `${usuNombre ?? ''} ${usuApellido ?? ''}`.trim() || `ID ${usuId}`;
        select.appendChild(option);
    });
}

// Función para asignar diseñador
function asignarDisenador() {
    const pedidoId = document.getElementById('pedidoId').value;
    const disenadorId = document.getElementById('disenadorId').value;
    
    if (!disenadorId) {
        Swal.fire('Error', 'Por favor selecciona un diseñador', 'warning');
        return;
    }
    
    fetch(`/admin/pedidos/${pedidoId}/asignar-empleado`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            usuIdEmpleado: disenadorId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', 'Diseñador asignado correctamente', 'success')
            .then(() => {
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAsignarDisenador'));
                modal.hide();
                // Recargar para ver los cambios
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'No se pudo asignar el diseñador', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ocurrió un error al asignar el diseñador', 'error');
    });
}

// ==================================================
// MODAL CREAR PEDIDO
// ==================================================

document.querySelectorAll('input[name="cpTipoCliente"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const esRegistrado = this.value === 'registrado';
        document.getElementById('bloqueRegistrado').style.display = esRegistrado ? 'block' : 'none';
        document.getElementById('bloqueExterno').style.display    = esRegistrado ? 'none'  : 'block';
        if (esRegistrado) {
            document.getElementById('cpIdentificador').value = '';
        } else {
            document.getElementById('cpUsuIdCliente').value = '';
        }
    });
});

function submitCrearPedido() {
    const tipo        = document.querySelector('input[name="cpTipoCliente"]:checked').value;
    const estId       = document.getElementById('cpEstado').value;
    const empleadoId  = document.getElementById('cpEmpleado').value;
    const comentarios = document.getElementById('cpComentarios').value.trim();

    const payload = { estId: parseInt(estId) };

    if (tipo === 'registrado') {
        const clienteId = document.getElementById('cpUsuIdCliente').value;
        if (!clienteId) {
            Swal.fire({ icon: 'warning', title: 'Falta el cliente', text: 'Selecciona un cliente de la lista.', confirmButtonColor: '#009688' });
            return;
        }
        payload.usuIdCliente = parseInt(clienteId);
    } else {
        const identificador = document.getElementById('cpIdentificador').value.trim();
        if (!identificador) {
            Swal.fire({ icon: 'warning', title: 'Falta el identificador', text: 'Escribe el nombre y teléfono del cliente.', confirmButtonColor: '#009688' });
            return;
        }
        payload.pedIdentificadorCliente = identificador;
    }

    if (empleadoId) payload.usuIdEmpleado = parseInt(empleadoId);
    if (comentarios) payload.pedComentarios = comentarios;

    Swal.fire({ title: 'Creando pedido...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    fetch('/admin/pedidos/crear-manual', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Pedido creado', text: 'Código: ' + (data.pedido?.pedCodigo ?? ''), confirmButtonColor: '#009688' })
            .then(() => location.reload());
        } else {
            throw new Error(data.message || 'Error al crear el pedido.');
        }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#ef4444' }));
}

// ==================================================
// MODAL ASIGNAR CLIENTE
// ==================================================

function abrirModalAsignarCliente(pedidoId, codigo) {
    document.getElementById('acPedidoId').value = pedidoId;
    document.getElementById('acCodigoPedido').textContent = codigo;
    document.getElementById('acUsuIdCliente').value = '';
    new bootstrap.Modal(document.getElementById('modalAsignarCliente')).show();
}

function confirmarAsignarCliente() {
    const pedidoId   = document.getElementById('acPedidoId').value;
    const clienteId  = document.getElementById('acUsuIdCliente').value;

    if (!clienteId) {
        Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#009688' });
        return;
    }

    Swal.fire({ title: 'Asignando...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    fetch(`/admin/pedidos/${pedidoId}/asignar-cliente`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ usuIdCliente: parseInt(clienteId) })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Cliente asignado', confirmButtonColor: '#009688' })
            .then(() => location.reload());
        } else {
            throw new Error(data.message || 'Error al asignar.');
        }
    })
    .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#ef4444' }));
}
</script>
@endpush
