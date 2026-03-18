{{-- Componente maestro de tabla de pedidos --}}
<div class="card pedidos-table-card animate-in">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Lista de Pedidos</h5>
            </div>
            <div class="col-md-9">
                <div class="row g-2">

                    <div class="col-md-3">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="filterCodigo" class="form-control"
                                   placeholder="Buscar por codigo..."
                                   value="{{ $filtros['codigo'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-3">
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

                    @if(Session::get('user_role') === 'ROLE_ADMINISTRADOR')
                    <div class="col-md-2">
                        <select id="filterCliente" class="form-select">
                            <option value="">Todos los clientes</option>
                            @foreach($clientes ?? [] as $cliente)
                                <option value="{{ $cliente['id'] }}"
                                    {{ (isset($filtros['usuIdCliente']) && $filtros['usuIdCliente'] == $cliente['id']) ? 'selected' : '' }}>
                                    {{ $cliente['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filterDisenador" class="form-select">
                            <option value="">Todos los disenadores</option>
                            @foreach($disenadores ?? [] as $d)
                                <option value="{{ $d['usuId'] ?? $d['id'] }}"
                                    {{ (isset($filtros['usuIdEmpleado']) && $filtros['usuIdEmpleado'] == ($d['usuId'] ?? $d['id'])) ? 'selected' : '' }}>
                                    {{ $d['usuNombre'] ?? $d['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                            <i class="bi bi-x-circle me-1"></i>Limpiar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table pedidos-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha Creación</th>
                    @if(Session::get('user_role') !== 'ROLE_USUARIO')
                        <th>Cliente</th>
                    @endif
                    <th>Diseñador</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                <tr class="pedido-row">
                    <td class="fw-bold">#{{ $pedido['pedCodigo'] }}</td>
                    <td>
                        @php
                            $fechaLocal = \Carbon\Carbon::parse($pedido['pedFechaCreacion'])
                                ->setTimezone(config('app.timezone'));
                        @endphp
                        <small class="text-muted d-block">
                            {{ $fechaLocal->format('d/m/Y') }}
                        </small>
                        <span class="fw-medium">
                            {{ $fechaLocal->format('h:i a') }}
                        </span>
                    </td>
                    @if(Session::get('user_role') !== 'ROLE_USUARIO')
                    <td>
                        @if (!empty($pedido['nombreCliente']))
                            {{ $pedido['nombreCliente'] }}
                        @elseif (!empty($pedido['pedIdentificadorCliente']))
                            <span class="text-muted">{{ $pedido['pedIdentificadorCliente'] }}</span>
                        @else
                            <span class="text-muted small">Sin cliente</span>
                        @endif
                    </td>
                    @endif
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
                    <td>
                        @php
                            $estadoCrudo = $pedido['estadoNombre'] ?? ($pedido['estado']['estNombre'] ?? 'desconocido');
                            $estadoLimpio = $estadoMapeo[$estadoCrudo] ?? $estadoCrudo;
                            
                            // ID numérico del estado para mapeo de colores (igual que en gestionar.blade.php)
                            $estadoId = $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1);
                            
                            // Clase CSS por estado ID para usar la misma paleta que gestionar.blade.php
                            $badgeClass = match($estadoId) {
                                1 => 'badge-pendiente',        // Cotización Pendiente - Amarillo
                                2 => 'badge-confirmado',       // Pago Diseño Pendiente - Rojo
                                3 => 'badge-diseno',           // Diseño en Proceso - Azul
                                4 => 'badge-aprobado',         // Diseño Aprobado - Verde Claro
                                5 => 'badge-produccion',       // Tallado - Púrpura
                                6 => 'badge-calidad',          // Engaste - Cyan
                                7 => 'badge-listo',            // Pulido - Verde Oscuro
                                8 => 'badge-camino',           // Inspección - Naranja
                                9 => 'badge-entregado',        // Finalizado - Verde Intenso
                                10 => 'badge-cancelado',       // Cancelado - Gris
                                default => 'badge-secondary'    // Default - Gris Claro
                            };
                        @endphp
                        <span class="badge-estado {{ $badgeClass }}">{{ $estadoLimpio }}</span>
                    </td>
                    <td>
                        <div class="action-buttons d-flex gap-2 align-items-center">
                            @if(Session::get('user_role') === 'ROLE_ADMINISTRADOR')
                                {{-- Admin: todos los botones --}}
                                <a href="{{ route('admin.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" class="btn-action btn-gestionar btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Gestionar pedido">
                                    <i class="bi bi-gear-fill"></i>
                                </a>
                                <button onclick="cambiarEstadoPedido({{ $pedido['pedId'] }}, {{ $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1) }})" class="btn-action btn-status btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Cambiar estado">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                                <button type="button" class="btn-action btn-asignar btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalAsignarDisenador" data-pedidoid="{{ $pedido['pedId'] }}" data-actualdisenadorid="{{ $pedido['usuIdEmpleado'] ?? '' }}" data-actualdisenadornombre="{{ $pedido['nombreEmpleado'] ?? '' }}" title="{{ ($pedido['usuIdEmpleado'] ?? null) ? 'Reasignar Diseñador' : 'Asignar Diseñador' }}">
                                    <i class="bi bi-person-plus"></i>
                                </button>
                                <button type="button"
                                        class="btn-action btn btn-sm btn-warning"
                                        onclick="abrirModalAsignarCliente({{ $pedido['pedId'] }}, '{{ addslashes($pedido['pedCodigo']) }}')"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Asignar cliente">
                                    <i class="bi bi-person-badge"></i>
                                </button>
                            @elseif(Session::get('user_role') === 'ROLE_DISEÑADOR')
                                {{-- Diseñador: solo Gestionar --}}
                                <a href="{{ route('designer.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" class="btn-action btn-gestionar btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Gestionar pedido">
                                    <i class="bi bi-gear-fill"></i>
                                </a>
                            @elseif(Session::get('user_role') === 'ROLE_USUARIO')
                                {{-- Usuario: solo Ver Detalles --}}
                                <a href="{{ route('user.pedidos.index', ['id' => $pedido['pedId']]) }}" class="btn-action btn-ver btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
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
