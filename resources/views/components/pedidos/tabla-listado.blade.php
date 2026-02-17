{{-- Componente maestro de tabla de pedidos --}}
<div class="table-responsive">
    <div class="search-box mb-3">
        <i class="bi bi-search"></i>
        <input type="text" id="searchCodigo" class="form-control" 
               placeholder="Buscar por código de pedido..."
               value="{{ $filtros['codigo'] ?? '' }}">
    </div>
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
                        <span class="text-muted">Desconocido</span>
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
                    @endphp
                    <span class="text-secondary fw-medium">{{ $estadoLimpio }}</span>
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
