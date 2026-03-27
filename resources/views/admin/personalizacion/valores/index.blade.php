@extends('layouts.app')

@section('title', 'Gestión de Valores')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
@endpush

@section('content')
<div class="dashboard-container">
    <div class="container-fluid py-5">

        {{-- Header con Stats Pills --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-list-nested me-3"></i>Gestión de Valores</h1>
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-list-nested text-primary"></i>
                            <span class="pill-label">Total:</span>
                            <strong class="pill-value">{{ count($valores) }}</strong>
                        </div>
                        <div class="pill-stat">
                            <i class="bi bi-sliders" style="color: var(--dash-primary)"></i>
                            <span class="pill-label">Opción:</span>
                            <strong class="pill-value">{{ $opcion['nombre'] }}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.personalizacion.opciones.index', ['catId' => $opcion['catId'] ?? '']) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Opciones
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearValor">
                        <i class="bi bi-plus-lg me-2"></i>Nuevo Valor
                    </button>
                </div>
            </div>
        </div>

        {{-- Feedback --}}
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

        {{-- Card contenedor del grid --}}
        <div class="card animate-in animate-delay-5 border-0 shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-grid-fill me-2"></i>Lista de Valores</h5>
                    <small class="text-muted">Agrega las opciones disponibles para cada característica.</small>
                </div>
            </div>
            <div class="card-body p-4">

                {{-- Grid de Valores (Restaurado a tu versión original) --}}
                <div class="row row-cols-2 row-cols-md-4 row-cols-xl-5 g-4">
                    @forelse($valores as $valor)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 card-hover-effect">
                                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center p-4">
                                    
                                    {{-- Placeholder Circular --}}
                                    <div class="avatar-placeholder bg-primary-subtle text-primary rounded-circle mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                                        <span class="fs-3 fw-bold">{{ strtoupper(substr($valor['nombre'], 0, 1)) }}</span>
                                    </div>

                                    <h5 class="fw-bold text-dark mb-1">{{ $valor['nombre'] }}</h5>
                                    <small class="text-muted mb-3">ID: {{ $valor['id'] }}</small>
                                </div>

                                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                    <form action="{{ route('admin.personalizacion.valores.eliminar', $valor['id']) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                            <i class="bi bi-trash me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">No hay valores creados.</div>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Modal Crear Valor (Solo Nombre) --}}
<div class="modal fade" id="modalCrearValor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Nuevo Valor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.personalizacion.valores.store') }}" method="POST">
                @csrf
                <input type="hidden" name="opcId" value="{{ $opcId }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Valor</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Ej: Oro de 18K" required autofocus>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card-hover-effect {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-hover-effect:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
</style>
@endsection