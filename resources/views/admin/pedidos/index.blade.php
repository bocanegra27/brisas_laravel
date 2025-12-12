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
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
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

        {{-- Tabla de pedidos --}}
        <div class="card pedidos-table-card animate-in animate-delay-5">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Pedidos</h5>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                            {{-- Busqueda por codigo --}}
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
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table pedidos-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha Creación</th>
                                <th>Cliente</th>
                                <th>Diseñador</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="pedidosTableBody">
                            @forelse($pedidos as $pedido)
                            <tr class="pedido-row">
                                <td class="fw-bold">#{{ $pedido['pedCodigo'] }}</td>
                                
                                <td>
                                    @php
                                        $fechaLocal = \Carbon\Carbon::parse($pedido['pedFechaCreacion'])
                                            ->setTimezone(config('app.timezone')); // Asume tu timezone (ej: America/Bogota)
                                    @endphp
                                    
                                    <small class="text-muted d-block">
                                        {{ $fechaLocal->format('d/m/Y') }}
                                    </small>
                                    <span class="fw-medium">
                                        {{ $fechaLocal->format('h:i a') }} {{-- h:i a for 12-hour clock (ej: 06:40 pm) --}}
                                    </span>
                                </td>
                                
                                {{-- COLUMNA CLIENTE (Prioriza nombreCliente enriquecido) --}}
                                <td>
                                    @if (!empty($pedido['nombreCliente']))
                                        {{ $pedido['nombreCliente'] }}
                                    @elseif (!empty($pedido['pedIdentificadorCliente']))
                                        <span class="text-muted">{{ $pedido['pedIdentificadorCliente'] }}</span>
                                    @else
                                        <span class="text-muted">Desconocido</span>
                                    @endif
                                </td>
                                
                                {{--  COLUMNA DISEÑADOR (Muestra nombreEmpleado) --}}
                                <td>
                                    @php
                                        $nombreEmpleado = $pedido['nombreEmpleado'] ?? 'PENDIENTE ASIGNAR';
                                    @endphp

                                    @if ($nombreEmpleado === 'PENDIENTE ASIGNAR')
                                        <span class="badge bg-warning text-dark">{{ $nombreEmpleado }}</span>
                                    @else
                                        {{ $nombreEmpleado }}
                                    @endif
                                </td>

                                {{-- Columna Estado --}}
                                <td>
                                    @php
                                        // Obtener el nombre crudo de la BD (ej: 'pago_diseno_pendiente')
                                        $estadoCrudo = $pedido['estadoNombre'] ?? ($pedido['estado']['estNombre'] ?? 'desconocido');
                                        
                                        // Usar la variable mapeada, cayendo al nombre crudo si falla el mapeo (aunque no debería)
                                        $estadoLimpio = $estadoMapeo[$estadoCrudo] ?? $estadoCrudo;
                                    @endphp
                                    <span class="text-secondary fw-medium">{{ $estadoLimpio }}</span>
                                </td>
                                
                                <td>
                                    <div class="action-buttons d-flex gap-2 align-items-center">
                                        {{-- Gestionar pedido --}}
                                        <a href="{{ route('admin.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" 
                                           class="btn-action btn-gestionar btn btn-sm btn-primary"
                                           data-bs-toggle="tooltip" title="Gestionar pedido">
                                            <i class="bi bi-gear-fill"></i>
                                        </a>

                                        {{-- Cambiar estado rapido --}}
                                        <button onclick="cambiarEstadoPedido({{ $pedido['pedId'] }}, {{ $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1) }})" 
                                                class="btn-action btn-status btn btn-sm btn-outline-secondary" 
                                                data-bs-toggle="tooltip" title="Cambiar estado">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>

                                        {{-- Botón Asignar/Reasignar Diseñador --}}
                                        <button type="button" 
                                                class="btn-action btn-asignar btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAsignarDisenador"
                                                data-pedidoid="{{ $pedido['pedId'] }}"
                                                data-actualdisenadorid="{{ $pedido['usuIdEmpleado'] ?? '' }}"
                                                data-actualdisenadornombre="{{ $pedido['nombreEmpleado'] ?? '' }}"
                                                title="{{ ($pedido['usuIdEmpleado'] ?? null) ? 'Reasignar Diseñador' : 'Asignar Diseñador' }}">
                                            <i class="bi bi-person-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted mb-0">No hay pedidos registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Footer con paginacion --}}
            @if($totalElements > 0)
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="pagination-info mb-0">
                            Mostrando {{ ($currentPage * $pageSize) + 1 }} 
                            a {{ min(($currentPage + 1) * $pageSize, $totalElements) }} 
                            de {{ $totalElements }} pedidos
                        </p>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="Paginacion de pedidos">
                            <ul class="pagination justify-content-end mb-0">
                                {{-- Primera pagina --}}
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page=0&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-double-left"></i>
                                    </a>
                                </li>
                                
                                {{-- Anterior --}}
                                <li class="page-item {{ $currentPage == 0 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $currentPage - 1 }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                
                                {{-- Paginas numeradas --}}
                                @for($i = max(0, $currentPage - 2); $i <= min($totalPages - 1, $currentPage + 2); $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="?page={{ $i }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        {{ $i + 1 }}
                                    </a>
                                </li>
                                @endfor
                                
                                {{-- Siguiente --}}
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $currentPage + 1 }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                
                                {{-- Ultima pagina --}}
                                <li class="page-item {{ $currentPage >= $totalPages - 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="?page={{ $totalPages - 1 }}&size={{ $pageSize }}&estadoId={{ $filtros['estadoId'] ?? '' }}&codigo={{ $filtros['codigo'] ?? '' }}">
                                        <i class="bi bi-chevron-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal para cambiar estado (Asegúrate de que este bloque NO esté dentro del @forelse) --}}
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
                            {{--  Bucle para poblar con los datos de Spring Boot --}}
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
        // Modal Asignar Diseñador: comportamiento y envío
        // ---------------------------------------------------
        document.addEventListener('DOMContentLoaded', function () {
            const modalAsignarEl = document.getElementById('modalAsignarDisenador');
            const asignarPedidoId = document.getElementById('asignarPedidoId');
            const disenadorSelect = document.getElementById('disenadorSelect');
            const formAsignar = document.getElementById('formAsignarDisenador');

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : null;

            // Función para cargar diseñadores vía AJAX si no vienen pasados desde el backend
            async function cargarDisenadoresSiNecesario() {
                const hasOptions = Array.from(disenadorSelect.options).some(opt => opt.value && opt.value !== '');
                if (hasOptions) return;

                try {
                    const res = await fetch('/admin/disenadores/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) return;
                    const json = await res.json();
                    if (!Array.isArray(json)) return;

                    disenadorSelect.innerHTML = '<option value="">Seleccione un diseñador</option>';
                    json.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.usuId ?? d.id ?? '';
                        opt.textContent = d.nombre ?? d.nombreCompleto ?? (d.correo ?? 'Empleado');
                        disenadorSelect.appendChild(opt);
                    });
                } catch (err) {
                    console.warn('No se pudieron cargar diseñadores automáticamente:', err);
                }
            }

            // Cuando se abre el modal, rellenar los campos
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

            // Submit del formulario — PATCH al endpoint Laravel que hace proxy a Spring Boot
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
    </script>
@endpush
