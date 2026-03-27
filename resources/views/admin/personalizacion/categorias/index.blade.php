@extends('layouts.app')

@section('title', 'Gestión de Categorías')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard-shared.css') }}">
@endpush

@section('content')
<div class="categorias-container">
    <div class="container-fluid py-5">

        {{-- Header con Stats Pills --}}
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-collection me-3"></i>Gestión de Categorías</h1>
                    <div class="stats-pills mt-3">
                        <div class="pill-stat">
                            <i class="bi bi-collection-fill text-primary"></i>
                            <span class="pill-label">Total:</span>
                            <strong class="pill-value">{{ count($categorias) }}</strong>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                        <i class="bi bi-plus-lg me-2"></i>Nueva Categoría
                    </button>
                </div>
            </div>
        </div>

        {{-- Mensajes de Feedback --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Card contenedor del grid --}}
        <div class="card animate-in animate-delay-5 border-0 shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-grid-fill me-2"></i>Lista de Categorías</h5>
                </div>
            </div>
            <div class="card-body p-4">

                {{-- Grid de Categorías --}}
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @forelse($categorias as $cat)
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 card-hover-effect">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="icon-square bg-light text-primary rounded-3 p-3">
                                            <i class="bi bi-gem fs-4"></i>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill">
                                            ID: {{ $cat['id'] }}
                                        </span>
                                    </div>

                                    <h5 class="card-title fw-bold text-dark">{{ $cat['nombre'] }}</h5>
                                    <p class="text-muted small mb-4">
                                        Slug: <code class="text-secondary bg-light px-2 py-1 rounded">{{ $cat['slug'] }}</code>
                                    </p>

                                    <div class="mt-auto d-flex gap-2">
                                        <a href="{{ route('admin.personalizacion.opciones.index', ['catId' => $cat['id']]) }}"
                                           class="btn btn-outline-primary flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-gear-fill"></i> Opciones
                                        </a>

                                        <form action="{{ route('admin.personalizacion.categorias.eliminar', $cat['id']) }}" method="POST"
                                            onsubmit="return confirm('¿Estás seguro? Esto borrará TODAS las opciones y valores asociados a esta categoría.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar Categoría">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>No hay categorías registradas aún. ¡Crea la primera para comenzar!</div>
                            </div>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Modal para Crear Categoría --}}
<div class="modal fade" id="modalCrearCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-folder-plus me-2"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.personalizacion.categorias.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nombreCategoria" class="form-label fw-bold">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="nombreCategoria" name="nombre"
                               placeholder="Ej: Relojes de Lujo" required autofocus>
                        <div class="form-text">El nombre visible para el cliente.</div>
                    </div>
                    <div class="mb-3">
                        <label for="slugCategoria" class="form-label fw-bold">Slug (URL)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">/</span>
                            <input type="text" class="form-control" id="slugCategoria" name="slug"
                                   placeholder="relojes-de-lujo" required readonly>
                        </div>
                        <div class="form-text">Se genera automáticamente. Se usará para crear las carpetas de imágenes.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script para generar Slug automático --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nombreInput = document.getElementById('nombreCategoria');
        const slugInput = document.getElementById('slugCategoria');

        nombreInput.addEventListener('input', function() {
            let nombre = this.value;
            let slug = nombre.toLowerCase()
                             .trim()
                             .replace(/[áéíóúüñ]/g, function(l) {
                                 const map = {'á':'a','é':'e','í':'i','ó':'o','ú':'u','ü':'u','ñ':'n'};
                                 return map[l];
                             })
                             .replace(/[^a-z0-9\s-]/g, '')
                             .replace(/\s+/g, '-')
                             .replace(/-+/g, '-');
            slugInput.value = slug;
        });
    });
</script>

{{-- Estilos extra para esta vista --}}
<style>
    .card-hover-effect {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-hover-effect:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
    .icon-square {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

@endsection