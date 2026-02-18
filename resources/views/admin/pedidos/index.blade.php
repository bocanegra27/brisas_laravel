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
                <div>
                    <span class="role-badge">
                        <i class="bi bi-shield-check"></i>
                        Administrador
                    </span>
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
</script>
@endpush
