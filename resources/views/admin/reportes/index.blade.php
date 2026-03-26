@extends('layouts.app')

@section('title', 'Reportes - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/utilities.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/admin-dashboard.css') }}" />
@endpush

@section('content')
<main class="container mt-4 pb-5">

    {{-- Header --}}
    <div class="dashboard-header animate-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-bar-chart-line-fill me-2"></i>Reportes Operativos</h1>
                <p class="text-muted mb-0">Bienvenido {{ Session::get('user_name', 'Administrador') }}</p>
            </div>
            <div>
                <span class="role-badge">
                    <i class="bi bi-shield-check"></i>
                    Administrador
                </span>
            </div>
        </div>
    </div>

    {{-- Pills de resumen --}}
    <h2 class="section-header animate-in animate-delay-1">Resumen General</h2>
    <div class="row g-3 g-md-4 mb-4">

        <div class="col-xl-3 col-md-6 animate-in animate-delay-1">
            <div class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-secondary-soft mx-auto">
                            <i class="bi bi-box-seam text-secondary"></i>
                        </div>
                        <p class="card-text">Total Pedidos</p>
                        <h2 class="display-4 text-secondary">{{ $totalPedidos }}</h2>
                        <div class="mt-2 text-muted small">En todos los estados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 animate-in animate-delay-2">
            <a href="{{ route('admin.mensajes.index') }}" class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-warning-soft mx-auto">
                            <i class="bi bi-envelope-exclamation text-warning"></i>
                        </div>
                        <p class="card-text">Contactos por Atender</p>
                        <h2 class="display-4 text-warning">{{ $contactosPendientes }}</h2>
                        <div class="mt-2 text-muted small">Oportunidades abiertas</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 animate-in animate-delay-3">
            <div class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-danger-soft mx-auto">
                            <i class="bi bi-file-earmark-x text-danger"></i>
                        </div>
                        <p class="card-text">Pedidos sin Render</p>
                        <h2 class="display-4 text-danger">{{ count($pedidosSinRender) }}</h2>
                        <div class="mt-2 text-muted small">En produccion activa</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 animate-in animate-delay-4">
            <div class="stat-card">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper bg-primary-soft mx-auto">
                            <i class="bi bi-people text-primary"></i>
                        </div>
                        <p class="card-text">Diseñadores Activos</p>
                        <h2 class="display-4 text-primary">{{ count($pedidosPorDisenador) }}</h2>
                        <div class="mt-2 text-muted small">Con pedidos asignados</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Pedidos por estado --}}
    <h2 class="section-header animate-in animate-delay-1">Estado de la Produccion</h2>
    @php
    function formatearEstado(string $nombre): string {
        return ucwords(str_replace('_', ' ', $nombre));
    }
    @endphp
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 g-md-4 mb-5">
        @php
            $estadoConfig = [
                1  => ['icon' => 'bi-clipboard-check',   'color' => 'warning'],
                2  => ['icon' => 'bi-cash-coin',          'color' => 'danger'],
                3  => ['icon' => 'bi-palette2',           'color' => 'primary'],
                4  => ['icon' => 'bi-check-circle',       'color' => 'success'],
                5  => ['icon' => 'bi-gem',                'color' => 'info'],
                6  => ['icon' => 'bi-gear',               'color' => 'secondary'],
                7  => ['icon' => 'bi-brightness-high',    'color' => 'warning'],
                8  => ['icon' => 'bi-search',             'color' => 'info'],
                9  => ['icon' => 'bi-check-all',          'color' => 'success'],
                10 => ['icon' => 'bi-x-circle',           'color' => 'danger'],
            ];
        @endphp

        @foreach($pedidosPorEstado as $index => $estado)
            @php
                $estId  = $estado['estId'] ?? ($index + 1);
                $conf   = $estadoConfig[$estId] ?? ['icon' => 'bi-circle', 'color' => 'secondary'];
                $delay  = $index + 1;
            @endphp
            <div class="col animate-in animate-delay-{{ $delay }}">
                <a href="{{ route('admin.pedidos.index', ['estadoId' => $estId]) }}" class="stat-card text-decoration-none">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="icon-wrapper bg-{{ $conf['color'] }}-soft mx-auto">
                                <i class="bi {{ $conf['icon'] }} text-{{ $conf['color'] }}"></i>
                            </div>
                            <p class="card-text">{{ formatearEstado($estado['estNombre'] ?? '') }}</p>
                            <h2 class="display-4 text-{{ $conf['color'] }}">{{ $estado['total'] ?? 0 }}</h2>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Carga por diseñador --}}
    <h2 class="section-header animate-in animate-delay-1">Carga por Diseñador</h2>
    <div class="row g-3 mb-5">
        @forelse($pedidosPorDisenador as $index => $d)
            @php $delay = $index + 1; @endphp
            <div class="col-xl-3 col-md-6 animate-in animate-delay-{{ $delay }}">
                <a href="{{ route('admin.pedidos.index', ['usuIdEmpleado' => $d['usuId']]) }}" class="stat-card text-decoration-none">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="icon-wrapper bg-indigo-soft mx-auto">
                                <i class="bi bi-person-workspace text-indigo"></i>
                            </div>
                            <p class="card-text">{{ $d['usuNombre'] ?? 'Diseñador' }}</p>
                            <h2 class="display-4 text-indigo">{{ $d['totalAsignados'] ?? 0 }}</h2>
                            <div class="mt-2 text-muted small">Pedidos asignados</div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 animate-in">
                <div class="card">
                    <div class="card-body text-center text-muted py-4">
                        <i class="bi bi-person-x fs-3 mb-2 d-block"></i>
                        No hay diseñadores con pedidos asignados actualmente.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pedidos sin render --}}
    <h2 class="section-header animate-in animate-delay-1">Pedidos sin Render Subido</h2>
    <div class="card animate-in">
        <div class="card-body p-0">
            @if(count($pedidosSinRender) === 0)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-check-circle-fill text-success fs-3 mb-2 d-block"></i>
                    Todos los pedidos en produccion tienen render registrado.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Codigo</th>
                                <th>Cliente</th>
                                <th>Diseñador</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidosSinRender as $pedido)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $pedido['pedCodigo'] ?? '-' }}</span>
                                    </td>
                                    <td>{{ $pedido['nombreCliente'] ?? 'Anonimo' }}</td>
                                    <td>{{ $pedido['nombreEmpleado'] ?? 'Sin asignar' }}</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            {{ $pedido['estadoNombre'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.pedidos.gestionar', $pedido['pedId']) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-right"></i> Gestionar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</main>
@endsection