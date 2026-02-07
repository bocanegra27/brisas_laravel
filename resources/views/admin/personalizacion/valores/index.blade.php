@extends('layouts.app')

@section('title', 'Gestión de Valores')

@section('content')
<div class="container mt-4 animate-in">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="mb-1">
                <a href="{{ route('admin.personalizacion.opciones.index', ['catId' => $opcion['catId'] ?? '']) }}" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
            <h2 class="mb-0">
                <i class="bi bi-list-nested me-2 text-primary"></i>
                Valores para <span class="text-primary">{{ $opcion['nombre'] }}</span>
            </h2>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearValor">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Valor
        </button>
    </div>

    {{-- Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Grid de Valores --}}
    <div class="row row-cols-2 row-cols-md-4 row-cols-xl-5 g-4">
        @forelse($valores as $valor)
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center p-4">
                        
                        {{-- Si tiene imagen la muestra, si no, muestra un icono de texto --}}
                        @if(!empty($valor['imagen']))
                            <img src="http://localhost:8080/{{ $valor['imagen'] }}" class="mb-3" style="height: 50px; object-fit: contain;">
                        @else
                            <div class="avatar-placeholder bg-light text-primary rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-type-h1 fs-3"></i>
                            </div>
                        @endif

                        <h5 class="fw-bold text-dark mb-0">{{ $valor['nombre'] }}</h5>
                        <small class="text-muted">ID: {{ $valor['id'] }}</small>
                    </div>

                    <div class="card-footer bg-white border-0 pt-0 pb-3">
                        <form action="{{ route('admin.personalizacion.valores.eliminar', $valor['id']) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash"></i> Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">No hay valores creados (ej: Oro, Plata). ¡Crea el primero!</div></div>
        @endforelse
    </div>
</div>

{{-- Modal Simplificado (SOLO NOMBRE) --}}
<div class="modal fade" id="modalCrearValor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo Valor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.personalizacion.valores.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="opcId" value="{{ $opcId }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Ej: Oro Amarillo" required autofocus>
                    </div>
                    
                    {{-- Input de archivo OCULTO o OPCIONAL (si decides usarlo a futuro) --}}
                    <div class="collapse" id="campoImagen">
                        <div class="mb-3">
                            <label class="form-label">Imagen (Opcional)</label>
                            <input type="file" class="form-control" name="archivo">
                        </div>
                    </div>
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#campoImagen">
                        ¿Subir imagen? (Opcional)
                    </button>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection