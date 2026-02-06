@extends('layouts.app')

@section('title', 'Gestión de Pedidos - Brisas Gems')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pedidos.css') }}">
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
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-receipt-cutoff text-primary"></i>
                            <span class="pill-label">Total:</span>
                            <strong class="pill-value">{{ $stats['total'] ?? 0 }}</strong>
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
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house-door-fill"></i>
                    </a>
                    <a href="{{ route('admin.pedidos.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Crear Nuevo Pedido
                    </a>
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

        {{-- Tabla de pedidos (componente compartido por roles) --}}
        @include('components.pedidos.tabla-listado', [
            'pedidos' => $pedidos,
            'estados' => $estados,
            'filtros' => $filtros,
            'pageSize' => $pageSize,
            'currentPage' => $currentPage,
            'totalElements' => $totalElements,
            'totalPages' => $totalPages,
            'estadoMapeo' => $estadoMapeo,
            'disenadores' => $disenadores ?? []
        ])
    </div>
</div>

{{-- Modales (Sin cambios en el HTML) --}}
{{-- Modal para cambiar estado --}}
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Cambiar Estado del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <input type="hidden" id="pedidoIdEstado" name="pedidoId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuevo Estado</label>
                        <select class="form-select" id="nuevoEstado" name="estadoId" required>
                            @foreach($estados as $estado)
                            <option value="{{ $estado['id'] }}">{{ $estado['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentarios de Historial (Opcional)</label>
                        <textarea class="form-control" id="comentariosEstado" name="comentarios" rows="3" placeholder="Ej: Pago de anticipo recibido, asignado a diseñador Miguel."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Los cambios de estado se registrarán automáticamente en el historial del pedido.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">
                    <i class="bi bi-check-circle-fill me-2"></i>Cambiar Estado
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Asignar Diseñador --}}
<div class="modal fade" id="modalAsignarDisenador" tabindex="-1" aria-labelledby="modalAsignarDisenadorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarDisenadorLabel">Asignar/Reasignar Diseñador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formAsignarDisenador">
                <div class="modal-body">
                    <input type="hidden" id="asignarPedidoId" name="pedidoId">
                    
                    <div class="mb-3">
                        <label for="disenadorSelect" class="form-label">Seleccionar Diseñador</label>
                        <select class="form-select" id="disenadorSelect" name="usuIdEmpleado" required>
                            <option value="">Seleccione un diseñador</option>
                            {{-- Bucle para poblar con los datos de Spring Boot --}}
                            @foreach($disenadores as $disenador)
                                <option value="{{ $disenador['id'] }}">
                                    {{ $disenador['nombre'] }} ({{ $disenador['rolNombre'] }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">El pedido será asignado a este empleado (diseñador).</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Guardar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    {{-- Mantengo la referencia a pedidos.js, pero añado el script de eliminación aquí para claridad --}}
    <script src="{{ asset('assets/js/pedidos.js') }}"></script> 

    <script>
        // Inicializar tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
        
        // Aplicar filtros
        document.getElementById('filterEstado')?.addEventListener('change', aplicarFiltros);
        document.getElementById('pageSize')?.addEventListener('change', aplicarFiltros);
        
        // Búsqueda por código (debounce)
        let timeoutCodigo;
        document.getElementById('searchCodigo')?.addEventListener('input', function() {
            clearTimeout(timeoutCodigo);
            timeoutCodigo = setTimeout(aplicarFiltros, 500);
        });
        
        function aplicarFiltros() {
            const estadoId = document.getElementById('filterEstado')?.value || '';
            const codigo = document.getElementById('searchCodigo')?.value || '';
            const size = document.getElementById('pageSize')?.value || '10';
            
            const url = new URL(window.location.href);
            url.searchParams.set('page', '0');
            url.searchParams.set('size', size);
            url.searchParams.set('estadoId', estadoId);
            url.searchParams.set('codigo', codigo);
            
            window.location.href = url.toString();
        }

        // ---------------------------------------------------
        // Función de ELIMINACIÓN de Pedido (Mantenida)
        // ---------------------------------------------------

        /**
         * Muestra una alerta de confirmación y envía una petición DELETE si se confirma.
         * @param {number} pedidoId ID del pedido a eliminar.
         * @param {string} pedidoCodigo Código visible del pedido (ej: P-202401-A02).
         */
        function eliminarPedido(pedidoId, pedidoCodigo) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Esta acción **eliminará permanentemente** el pedido <strong>#${pedidoCodigo}</strong>.<br>No podrás revertir este cambio.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Rojo de peligro
                cancelButtonColor: '#6c757d', // Gris secundario
                confirmButtonText: '<i class="bi bi-trash-fill me-1"></i> Sí, ¡Eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Procesando la eliminación del pedido.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // 1. Enviar petición DELETE vía AJAX
                    fetch(`/admin/pedidos/${pedidoId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(({ status, body }) => {
                        if (status === 200) {
                            Swal.fire({
                                title: 'Eliminado!',
                                text: body.message || 'El pedido ha sido eliminado.',
                                icon: 'success'
                            }).then(() => {
                                // Recargar la página para ver el listado actualizado
                                window.location.reload(); 
                            });
                        } else {
                            // Manejar errores de la API (ej: 404, 500)
                            throw new Error(body.message || `Error (${status}) al eliminar el pedido.`);
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error de Eliminación', error.message, 'error');
                    });
                }
            });
        }
        
        // ---------------------------------------------------
        // Lógica de Asignación (Mantenida)
        // ---------------------------------------------------
        document.addEventListener('DOMContentLoaded', function () {
            const modalAsignarEl = document.getElementById('modalAsignarDisenador');
            const asignarPedidoId = document.getElementById('asignarPedidoId');
            const disenadorSelect = document.getElementById('disenadorSelect');
            const formAsignar = document.getElementById('formAsignarDisenador');

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : null;

            async function cargarDisenadoresSiNecesario() {
                // Ya se cargan los diseñadores vía Blade, así que esta función se puede omitir o mantener como fallback.
                const hasOptions = Array.from(disenadorSelect.options).some(opt => opt.value && opt.value !== '');
                if (hasOptions) return;
                // Código para cargar diseñadores si fuera necesario...
            }

            modalAsignarEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const pedidoId = button.getAttribute('data-pedidoid');
                const actualDisenadorId = button.getAttribute('data-actualdisenadorid') || '';

                asignarPedidoId.value = pedidoId;

                cargarDisenadoresSiNecesario().then(() => {
                    if (actualDisenadorId) {
                        disenadorSelect.value = actualDisenadorId;
                    } else {
                        disenadorSelect.value = '';
                    }
                });
            });

            formAsignar.addEventListener('submit', async function (e) {
                e.preventDefault();
                const pedidoId = asignarPedidoId.value;
                const nuevoDisenadorId = disenadorSelect.value;

                if (!nuevoDisenadorId) {
                    Swal.fire('Advertencia', 'Debe seleccionar un diseñador.', 'warning');
                    return;
                }

                const confirm = await Swal.fire({
                    title: 'Confirmar asignación',
                    text: '¿Deseas asignar este diseñador al pedido?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, asignar',
                    cancelButtonText: 'Cancelar'
                });

                if (!confirm.isConfirmed) return;

                Swal.fire({
                    title: 'Asignando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const res = await fetch(`/admin/pedidos/${pedidoId}/asignar-empleado`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                        },
                        body: JSON.stringify({ usuIdEmpleado: parseInt(nuevoDisenadorId, 10) })
                    });

                    if (!res.ok) {
                        const body = await res.json().catch(() => ({}));
                        throw new Error(body.message || 'Error en la asignación');
                    }

                    const data = await res.json();

                    Swal.fire('Hecho', 'Diseñador asignado correctamente.', 'success').then(() => {
                        const modal = bootstrap.Modal.getInstance(modalAsignarEl);
                        modal?.hide();
                        location.reload();
                    });

                } catch (err) {
                    Swal.fire('Error', err.message || 'No se pudo asignar el diseñador.', 'error');
                }
            });
        });
        
        // ---------------------------------------------------
        // Lógica de Cambio de Estado Rápido (Actualizada para usar el endpoint de historial/estado)
        // ---------------------------------------------------
        
        function cambiarEstadoPedido(pedidoId, estadoActualId) {
            // Lógica para mostrar el modal de cambio de estado
            const modalCambiarEstado = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
            document.getElementById('pedidoIdEstado').value = pedidoId;
            document.getElementById('nuevoEstado').value = estadoActualId;
            document.getElementById('comentariosEstado').value = '';
            modalCambiarEstado.show();
        }

        async function confirmarCambioEstado() {
            const pedidoId = document.getElementById('pedidoIdEstado').value;
            const nuevoEstadoId = document.getElementById('nuevoEstado').value;
            const comentarios = document.getElementById('comentariosEstado').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            Swal.fire({
                title: 'Actualizando Estado...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                // 🚨 CAMBIO CLAVE: Utilizamos el endpoint PATCH/estado-historial, ya que el endpoint PUT/update fue eliminado del controlador.
                const res = await fetch(`/admin/pedidos/${pedidoId}/estado-historial`, {
                    method: 'POST', // Usamos POST para enviar datos tipo JSON con Method Override
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'PATCH' // Simula el método PATCH
                    },
                    body: JSON.stringify({ 
                        estadoId: parseInt(nuevoEstadoId, 10), 
                        comentarios: comentarios 
                    })
                });

                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.message || 'Error al comunicarse con la API.');
                }

                Swal.fire('Estado Actualizado', 'El estado del pedido se ha modificado correctamente.', 'success').then(() => {
                    location.reload();
                });

            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }
    </script>
@endpush