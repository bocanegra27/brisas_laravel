@php
    $role = Session::get('user_role');
    $isDesigner = $role === 'ROLE_DESIGNER' || $role === 'ROLE_DISEÑADOR';
    $isUser = $role === 'ROLE_USUARIO' || $role === 'ROLE_USER' || $role === 'ROLE_CLIENTE';
    $isAdmin = !($isDesigner || $isUser);
@endphp

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
                        @if(!$isUser)
                        <th>Cliente</th>
                        @endif
                        <th>Diseñador</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                        @if(!$isDesigner && !$isUser)
                        <th class="text-center">Eliminar</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="pedidosTableBody">
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
                        
                        @if(!$isUser)
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
                            <div class="action-buttons d-flex gap-2 align-items-center justify-content-center">
                                {{-- Admin: todos los botones --}}
                                @if($isAdmin)
                                    <a href="{{ route('admin.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" 
                                       class="btn-action btn-gestionar btn btn-sm btn-primary"
                                       data-bs-toggle="tooltip" title="Gestionar pedido">
                                        <i class="bi bi-gear-fill"></i>
                                    </a>

                                    <button onclick="cambiarEstadoPedido({{ $pedido['pedId'] }}, {{ $pedido['estado']['estId'] ?? ($pedido['estId'] ?? 1) }})" 
                                            class="btn-action btn-status btn btn-sm btn-outline-secondary" 
                                            data-bs-toggle="tooltip" title="Cambiar estado">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>

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
                                @elseif($isDesigner)
                                    {{-- Diseñador: solo gestionar --}}
                                    <a href="{{ route('designer.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" 
                                       class="btn-action btn-gestionar btn btn-sm btn-primary"
                                       data-bs-toggle="tooltip" title="Gestionar pedido">
                                        <i class="bi bi-gear-fill"></i>
                                    </a>
                                @elseif($isUser)
                                    {{-- Usuario: solo ver detalles (su propia vista) --}}
                                    <a href="{{ route('user.pedidos.show', ['id' => $pedido['pedId']]) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                        Ver Detalles
                                    </a>
                                @else
                                    {{-- Fallback: mostrar solo gestionar --}}
                                    <a href="{{ route('admin.pedidos.gestionar', ['id' => $pedido['pedId']]) }}" 
                                       class="btn-action btn-gestionar btn btn-sm btn-primary"
                                       data-bs-toggle="tooltip" title="Gestionar pedido">
                                        <i class="bi bi-gear-fill"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                        
                        @if(!$isDesigner && !$isUser)
                        <td class="text-center">
                            <button onclick="eliminarPedido({{ $pedido['pedId'] }}, '{{ $pedido['pedCodigo'] }}')" 
                                    class="btn-action btn-delete btn btn-sm btn-outline-danger" 
                                    data-bs-toggle="tooltip" title="Eliminar Permanentemente">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isUser ? 6 : 8 }}" class="text-center py-5">
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
