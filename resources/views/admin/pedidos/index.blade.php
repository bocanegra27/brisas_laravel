@extends('layouts.app') {{-- Asumiendo que este es tu layout principal --}}

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">📦 Pedidos</h1>
        <div class="d-flex gap-2">
            {{-- Filtro Rápido --}}
            <form action="{{ route('admin.pedidos.index') }}" method="GET" class="d-flex">
                <select name="estadoId" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $id => $nombre)
                        <option value="{{ $id }}" {{ $filtroEstado == $id ? 'selected' : '' }}>
                            {{ $id }} - {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="row g-4">
        
        {{-- COLUMNA IZQUIERDA: CREAR PEDIDO --}}
        <aside class="col-lg-4">
            <div class="card border-0 shadow sticky-top" style="top: 20px; border-radius: 15px;">
                <div class="card-header bg-emerald text-white text-center py-3" style="border-radius: 15px 15px 0 0; background-color: #198754;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-robot me-2"></i>Nuevo Encargo</h5>
                </div>
                <div class="card-body p-4 bg-light">
                    
                    <form method="POST" action="{{ route('admin.pedidos.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- Aviso de automatización --}}
                        <div class="alert alert-info border-0 d-flex align-items-center p-2 mb-3 shadow-sm">
                            <i class="bi bi-magic me-2 fs-4"></i>
                            <div class="lh-1">
                                <small>Modo <strong>Inteligente</strong> activo.</small><br>
                                <small class="text-muted" style="font-size: 0.75rem;">Código generado automáticamente.</small>
                            </div>
                        </div>

                        {{-- SELECCIÓN DE CLIENTE --}}
                        <div class="mb-3">
                            <label for="clienteId" class="form-label fw-bold text-secondary small text-uppercase">Cliente</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-person-badge"></i></span>
                                <select class="form-select border-0" id="clienteId" name="clienteId" required>
                                    <option value="" selected disabled>Seleccione un cliente...</option>
                                    
                                    @if(isset($datosFormulario->clientes) && count($datosFormulario->clientes) > 0)
                                        @foreach($datosFormulario->clientes as $cliente)
                                            <option value="{{ $cliente->id }}">
                                                {{ $cliente->nombre }} (Doc: {{ $cliente->documento }})
                                            </option>
                                        @endforeach
                                    @else
                                        <option disabled>No hay clientes cargados</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-text text-end">
                                {{--  MEJORA: Enlace funcional a crear usuario --}}
                                <a href="{{ route('admin.usuarios.crear') }}" class="text-decoration-none small text-success fw-bold">
                                    <i class="bi bi-plus-circle"></i> Nuevo Cliente
                                </a>
                            </div>
                        </div>

                        <hr class="border-secondary opacity-10 my-3">

                        {{-- PERSONALIZACIÓN --}}
                        <h6 class="fw-bold text-dark mb-3 small"><i class="bi bi-sliders me-2"></i>Configuración de Joya</h6>
                        
                        <div class="row g-2 mb-3">
                            @if(isset($datosFormulario->opciones))
                                @foreach($datosFormulario->opciones as $tituloOpcion => $valores)
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ strtoupper($tituloOpcion) }}</label>
                                    <select class="form-select form-select-sm border-0 shadow-sm" name="valoresPersonalizacion[]">
                                        <option value="" selected disabled>-- Seleccionar --</option>
                                        @foreach($valores as $valor)
                                            <option value="{{ $valor->id }}">{{ $valor->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small">Instrucciones / Detalles</label>
                            <textarea class="form-control border-0 shadow-sm" name="pedComentarios" rows="3" placeholder="Detalles de la joya, grabados..." style="resize: none;" required></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small"><i class="bi bi-image me-1"></i>Render Inicial (Opcional)</label>
                            <input type="file" name="render" class="form-control form-control-sm border-0 shadow-sm" accept="image/*,.glb,.gltf">
                        </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th> <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
    @forelse($pedidos as $pedido)
    <tr>
        <td class="fw-bold text-primary">
            {{-- Usamos pedCodigo o pedId --}}
            #{{ $pedido['pedCodigo'] ?? $pedido['pedId'] }}
        </td>
        <td>
            <div class="d-flex flex-column">
                {{-- CORRECCIÓN: El JSON trae 'pedIdentificadorCliente' o a veces es null --}}
                <span class="fw-bold">
                    {{ $pedido['pedIdentificadorCliente'] ?? 'Cliente Anónimo / Web' }}
                </span>
                <small class="text-muted">
                    {{-- ID de Sesión o Contacto si no hay nombre --}}
                    @if(empty($pedido['pedIdentificadorCliente']))
                        Sesión: {{ $pedido['sesionId'] ?? 'N/A' }}
                    @endif
                </small>
            </div>
        </td>
        <td>
            {{-- CORRECCIÓN: Nombre de variable 'pedFechaCreacion' (camelCase) --}}
            {{ \Carbon\Carbon::parse($pedido['pedFechaCreacion'] ?? now())->format('d/m/Y H:i') }}
        </td>
        <td>
            {{-- CORRECCIÓN: El estado viene directo como string en 'estadoNombre' --}}
            @php
                $estadoNombre = $pedido['estadoNombre'] ?? 'Desconocido';
                // Asignar colores según el texto del estado
                $badgeColor = match(true) {
                    str_contains($estadoNombre, 'pendiente') => 'warning',
                    str_contains($estadoNombre, 'proceso') => 'primary',
                    str_contains($estadoNombre, 'entregado') => 'success',
                    str_contains($estadoNombre, 'cancelado') => 'danger',
                    default => 'secondary'
                };
            @endphp
            <span class="badge bg-{{ $badgeColor }} rounded-pill text-uppercase">
                {{ str_replace('_', ' ', $estadoNombre) }}
            </span>
        </td>
        <td>
            --
        </td>
        <td class="text-end">
            <a href="{{ route('admin.pedidos.ver', $pedido['pedId']) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-eye"></i> Gestionar
            </a>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-box-seam display-4"></i>
                <p class="mt-2">No hay pedidos registrados.</p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
                </table>
            </div>
        </aside>

        {{-- COLUMNA DERECHA: LISTADO VISUAL --}}
        <section class="col-lg-8">
            <h5 class="fw-bold text-secondary mb-3 px-2">Línea de Producción</h5>

            @forelse($pedidos as $p)
                @php
                    $codigo = $p->pedCodigo ?? 'PENDIENTE';
                    $estado = $p->estId ?? 1;
                    $fecha = $p->pedFechaCreacion ?? date('Y-m-d');
                    $id = $p->pedId ?? $p->ped_id ?? null;
                    //  Recuperamos el nombre del cliente (enviado desde el backend)
                    $cliente = $p->clienteNombre ?? 'Cliente Desconocido';
                    
                    $nombreEstado = match($estado) {
                        1 => 'Diseño', 2 => 'Tallado', 3 => 'Engaste',
                        4 => 'Pulido', 5 => 'Finalizado', 6 => 'Cancelado',
                        default => 'Desconocido',
                    };
                    
                    $progreso = match($estado) {
                        1 => 15, 2 => 35, 3 => 60, 4 => 85, 5 => 100, default => 5
                    };
                    
                    $colorEstado = match($estado) {
                        1 => 'info', 2 => 'warning', 3 => 'primary',
                        4 => 'secondary', 5 => 'success', 6 => 'danger', default => 'dark'
                    };
                @endphp

                @if($id)
                    <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 12px;" data-pedido-id="{{ $id }}">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                
                                {{-- INFO PRINCIPAL --}}
                                <div class="col-md-8 p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">{{ $codigo }}</h5>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> {{ date('d M Y', strtotime($fecha)) }}</small>
                                            
                                            {{--  MEJORA: Mostrar Nombre del Cliente --}}
                                            <div class="mt-2">
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    <i class="bi bi-person-circle text-secondary me-1"></i> {{ $cliente }}
                                                </span>
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $colorEstado }} bg-opacity-25 text-{{ $colorEstado }} px-3 py-1 rounded-pill border border-{{ $colorEstado }}">
                                            {{ strtoupper($nombreEstado) }}
                                        </span>
                                    </div>

                                    <p class="text-muted small bg-light p-3 rounded mb-3 border-start border-3 border-{{ $colorEstado }}">
                                        {{ $p->pedComentarios }}
                                    </p>

                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Avance del proceso</span>
                                        <span>{{ $progreso }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px;">
                                        <div class="progress-bar bg-{{ $colorEstado }} progress-bar-striped progress-bar-animated" 
                                             role="progressbar" 
                                             style="width: {{ $progreso }}%"
                                             aria-valuenow="{{ $progreso }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"></div>
                                    </div>
                                </div>

                                {{-- ACCIONES Y VISOR --}}
                                <div class="col-md-4 bg-light border-start d-flex flex-column align-items-center justify-content-center p-3 text-center">
                                    
                                    <div class="visor-contenedor shadow-sm bg-white rounded-3 overflow-hidden mb-3 position-relative d-flex align-items-center justify-content-center" style="width: 100%; height: 180px;">
                                        
                                        @if(isset($p->renderPath) && $p->renderPath)
                                            @php
                                                $ext = pathinfo($p->renderPath, PATHINFO_EXTENSION);
                                                $url = 'http://localhost:8080/' . $p->renderPath;
                                            @endphp

                                            @if(strtolower($ext) === 'glb' || strtolower($ext) === 'gltf')
                                                <model-viewer 
                                                    src="{{ $url }}" 
                                                    alt="Modelo 3D Joya"
                                                    auto-rotate
                                                    camera-controls
                                                    shadow-intensity="1"
                                                    style="width: 100%; height: 100%; background-color: #f8f9fa;"
                                                    loading="lazy">
                                                </model-viewer>
                                                <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm" style="z-index: 10;">
                                                    <i class="bi bi-box-seam me-1"></i>3D
                                                </span>
                                            @else
                                                <img src="{{ $url }}" alt="Render" style="width: 100%; height: 100%; object-fit: cover;">
                                                <span class="badge bg-secondary position-absolute top-0 end-0 m-2 shadow-sm">
                                                    <i class="bi bi-image me-1"></i>IMG
                                                </span>
                                            @endif
                                        @else
                                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted w-100">
                                                @if($estado == 5) 
                                                    <i class="bi bi-box2-heart text-success fs-1"></i>
                                                @elseif($estado == 6) 
                                                    <i class="bi bi-x-lg text-danger fs-1"></i>
                                                @else 
                                                    <i class="bi bi-tools text-warning fs-1 opacity-50"></i>
                                                    <small class="mt-2">En proceso</small>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <button class="btn btn-outline-dark btn-sm w-100 rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#panel-{{ $id }}">
                                        <i class="bi bi-sliders me-1"></i> Gestionar
                                    </button>
                                </div>
                            </div>

                            {{-- PANEL DE GESTIÓN --}}
                            <div class="collapse bg-white border-top p-3" id="panel-{{ $id }}">
                                <form method="POST" action="{{ route('pedidos.update', $id) }}" enctype="multipart/form-data" data-form-pedido-id="{{ $id }}">
                                    @csrf @method('PUT')
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Actualizar Etapa</label>
                                            <select class="form-select form-select-sm" name="estId">
                                                <option value="1" {{ $estado == 1 ? 'selected' : '' }}>1. Diseño</option>
                                                <option value="2" {{ $estado == 2 ? 'selected' : '' }}>2. Tallado</option>
                                                <option value="3" {{ $estado == 3 ? 'selected' : '' }}>3. Engaste</option>
                                                <option value="4" {{ $estado == 4 ? 'selected' : '' }}>4. Pulido</option>
                                                <option value="5" {{ $estado == 5 ? 'selected' : '' }}>5. Finalizado</option>
                                                <option value="6" {{ $estado == 6 ? 'selected' : '' }}>Cancelado</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Actualizar Render / Modelo</label>
                                            <input type="file" name="render" class="form-control form-control-sm" accept="image/*,.glb,.gltf">
                                        </div>

                                        <div class="col-12 d-flex justify-content-between align-items-center mt-3 border-top pt-2">
                                            <button type="submit" form="delete-form-{{ $id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Seguro que deseas eliminar este pedido?')">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>

                                            <button type="submit" class="btn btn-sm btn-success px-4 fw-bold">
                                                <i class="bi bi-check-lg"></i> Guardar Cambios
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <form id="delete-form-{{ $id }}" action="{{ route('pedidos.destroy', $id) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </div>

                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted opacity-50"></i>
                    <p class="text-muted mt-2">No hay pedidos registrados.</p>
                </div>
            @endforelse

            {{-- Paginación Simple --}}
            @if($pagination['totalPages'] > 1)
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination">
                        <li class="page-item {{ $pagination['number'] == 0 ? 'disabled' : '' }}">
                            <a class="page-link" href="?page={{ $pagination['number'] - 1 }}&estadoId={{ $filtroEstado }}">Anterior</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Página {{ $pagination['number'] + 1 }} de {{ $pagination['totalPages'] }}</span>
                        </li>
                        <li class="page-item {{ $pagination['number'] + 1 >= $pagination['totalPages'] ? 'disabled' : '' }}">
                            <a class="page-link" href="?page={{ $pagination['number'] + 1 }}&estadoId={{ $filtroEstado }}">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection