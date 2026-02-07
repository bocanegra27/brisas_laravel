@extends('layouts.app')

@section('title', 'Gestión de Opciones')

@section('content')
<div class="container mt-4 animate-in">
    
    {{-- Breadcrumb / Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="mb-1">
                <a href="{{ route('admin.personalizacion.categorias.index') }}" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left"></i> Volver a Categorías
                </a>
            </div>
            <h2 class="mb-0">
                <i class="bi bi-list-check me-2 text-primary"></i>
                Opciones para <span class="text-primary">{{ $categoria['nombre'] ?? 'Categoría' }}</span>
            </h2>
            <p class="text-muted mb-0">Define qué características puede personalizar el cliente (Ej: Material, Talla).</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearOpcion">
            <i class="bi bi-plus-lg me-2"></i>Nueva Opción
        </button>
    </div>

    {{-- Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Grid de Opciones --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        @forelse($opciones as $opcion)
            <div class="col">
                <div class="card h-100 shadow-sm border-0 card-hover-effect">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="icon-square bg-light text-success rounded-3 p-3">
                                <i class="bi bi-sliders fs-4"></i>
                            </div>
                            <span class="badge bg-light text-muted border">ID: {{ $opcion['id'] }}</span>
                        </div>
                        
                        <h5 class="card-title fw-bold text-dark">{{ $opcion['nombre'] }}</h5>
                        <p class="text-muted small mb-4">
                            Pertenece a: <strong>{{ $categoria['nombre'] }}</strong>
                        </p>

                        <div class="mt-auto d-flex gap-2">
                            {{-- Botón Gestionar Valores (Nivel 3) --}}
                            <a href="{{ route('admin.personalizacion.valores.index', ['opcId' => $opcion['id']]) }}" 
                               class="btn btn-outline-primary flex-grow-1">
                                <i class="bi bi-images me-1"></i> Ver Valores
                            </a>

                            {{-- Botón Eliminar --}}
                            <form action="{{ route('admin.personalizacion.opciones.eliminar', $opcion['id']) }}" method="POST"
                                  onsubmit="return confirm('¿Borrar esta opción? Se borrarán también sus imágenes (valores).');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Eliminar Opción">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-circle fs-4 me-3"></i>
                    <div>
                        Esta categoría aún no tiene opciones configuradas.
                        <strong>¡Crea una (ej: "Tipo de Cierre") para empezar!</strong>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Crear Opción --}}
<div class="modal fade" id="modalCrearOpcion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nueva Opción de Personalización</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.personalizacion.opciones.store') }}" method="POST">
                @csrf
                {{-- Enviamos el ID de la categoría oculto --}}
                <input type="hidden" name="catId" value="{{ $catId }}">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Categoría Padre</label>
                        <input type="text" class="form-control bg-light" value="{{ $categoria['nombre'] }}" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label for="nombreOpcion" class="form-label fw-bold">Nombre de la Opción</label>
                        <input type="text" class="form-control" id="nombreOpcion" name="nombre" 
                               placeholder="Ej: Tipo de Cierre, Color de Correa..." required autofocus>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar Opción</button>
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
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
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