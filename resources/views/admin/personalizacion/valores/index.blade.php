@extends('layouts.app')

@section('title', 'Gestión de Valores')

@section('content')
<div class="container-fluid py-5">
    {{-- Header con Stats Pills --}}
    <div class="dashboard-header animate-in">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-list-nested me-3"></i>Gestión de Valores</h1>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <a href="{{ route('admin.personalizacion.opciones.index', ['catId' => $opcion['catId'] ?? '']) }}" class="text-decoration-none text-muted small">
                        <i class="bi bi-arrow-left"></i> Volver a Opciones
                    </a>
                    <span class="text-muted">|</span>
                    <span class="text-muted">Valores para <span class="text-primary">{{ $opcion['nombre'] }}</span></span>
                </div>
                <p class="text-muted mb-0">Agrega las opciones disponibles para cada característica (Ej: Oro, Plata, 18K).</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearValor">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Valor
            </button>
        </div>
    </div>

    {{-- Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Grid de Valores --}}
    <div class="row row-cols-2 row-cols-md-4 row-cols-xl-5 g-4">
        @forelse($valores as $valor)
            <div class="col">
                <div class="card h-100 shadow-sm border-0 card-hover-effect">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center p-4">
                        
                        {{-- Ícono / Placeholder --}}
                        @if(!empty($valor['imagen']))
                            <img src="{{ str_replace('/api', '', config('services.spring_api.url')) }}/assets/img/personalizacion/{{ $catSlug }}/opciones/{{ $opcId }}/{{ $valor['imagen'] }}"
                                 class="mb-3 rounded-circle border p-1" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                            <div class="avatar-placeholder bg-primary-subtle text-primary rounded-circle mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                                <span class="fs-3 fw-bold">{{ strtoupper(substr($valor['nombre'], 0, 1)) }}</span>
                            </div>
                        @endif

                        <h5 class="fw-bold text-dark mb-1">{{ $valor['nombre'] }}</h5>
                        <small class="text-muted mb-3">ID: {{ $valor['id'] }}</small>
                    </div>

                    <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                        <form action="{{ route('admin.personalizacion.valores.eliminar', $valor['id']) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i> Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">No hay valores creados.</div></div>
        @endforelse
    </div>
</div>

{{-- MODAL 1: Crear Valor --}}
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
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="collapse" id="campoImagenIcono">
                        <div class="mb-3">
                            <label class="form-label small">Ícono (Opcional)</label>
                            <input type="file" class="form-control" name="archivo" accept="image/png, image/jpeg">
                        </div>
                    </div>
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#campoImagenIcono">
                        ¿Subir ícono?
                    </button>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: Gestionar Vistas (Con Indicadores Visuales) --}}
<div class="modal fade" id="modalVistas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-layers-fill me-2"></i>Vistas: <span id="lblValorNombre" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 text-center">
                    
                    {{-- SUPERIOR --}}
                    <div class="col-md-4">
                        {{-- ID para cambiar el color --}}
                        <div id="card-superior" class="card h-100 border shadow-sm transition-all">
                            <div class="card-header bg-transparent fw-bold border-bottom-0 pt-3">
                                {{-- Icono de estado --}}
                                <i id="icon-superior" class="bi bi-arrow-up-circle me-1"></i> Superior
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.personalizacion.valores.subirVista') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="valorId" id="inputValorIdSuperior">
                                    <input type="hidden" name="tipo" value="superior">
                                    <input type="file" name="archivo" class="form-control form-control-sm mb-2" required accept="image/png">
                                    <button type="submit" class="btn btn-sm btn-outline-dark w-100">Subir</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- FRONTAL --}}
                    <div class="col-md-4">
                        <div id="card-frontal" class="card h-100 border shadow-sm transition-all">
                            <div class="card-header bg-transparent fw-bold border-bottom-0 pt-3">
                                <i id="icon-frontal" class="bi bi-circle me-1"></i> Frontal
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.personalizacion.valores.subirVista') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="valorId" id="inputValorIdFrontal">
                                    <input type="hidden" name="tipo" value="frontal">
                                    <input type="file" name="archivo" class="form-control form-control-sm mb-2" required accept="image/png">
                                    <button type="submit" class="btn btn-sm btn-outline-dark w-100">Subir</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- PERFIL --}}
                    <div class="col-md-4">
                        <div id="card-perfil" class="card h-100 border shadow-sm transition-all">
                            <div class="card-header bg-transparent fw-bold border-bottom-0 pt-3">
                                <i id="icon-perfil" class="bi bi-arrow-right-circle me-1"></i> Perfil
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.personalizacion.valores.subirVista') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="valorId" id="inputValorIdPerfil">
                                    <input type="hidden" name="tipo" value="perfil">
                                    <input type="file" name="archivo" class="form-control form-control-sm mb-2" required accept="image/png">
                                    <button type="submit" class="btn btn-sm btn-outline-dark w-100">Subir</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const SERVER_BASE_URL = "{{ str_replace('/api', '', config('services.spring_api.url')) }}";
    const modalVistas = document.getElementById('modalVistas');
    
    function verificarImagen(url, cardId, iconId) {
        const img = new Image();
        img.src = url + '?t=' + new Date().getTime();
        
        img.onload = function() {
            const card = document.getElementById(cardId);
            const icon = document.getElementById(iconId);
            
            card.classList.remove('border');
            card.classList.add('border-success', 'bg-success-subtle');
            
            icon.classList.remove('bi-circle', 'bi-arrow-up-circle', 'bi-arrow-right-circle');
            icon.classList.add('bi-check-circle-fill', 'text-success');
        };
        
        img.onerror = function() {
            const card = document.getElementById(cardId);
            const icon = document.getElementById(iconId);
            
            card.classList.remove('border-success', 'bg-success-subtle');
            card.classList.add('border');
            
            if(iconId.includes('superior')) icon.className = 'bi bi-arrow-up-circle me-1';
            if(iconId.includes('frontal')) icon.className = 'bi bi-circle me-1';
            if(iconId.includes('perfil')) icon.className = 'bi bi-arrow-right-circle me-1';
        }
    }

    if (modalVistas) {
        modalVistas.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-valor-id');
            const nombre = button.getAttribute('data-valor-nombre');
            
            const catSlug = button.getAttribute('data-cat-slug');
            const opcId = button.getAttribute('data-opc-id');

            document.getElementById('lblValorNombre').textContent = nombre;
            document.getElementById('inputValorIdSuperior').value = id;
            document.getElementById('inputValorIdFrontal').value = id;
            document.getElementById('inputValorIdPerfil').value = id;

            const baseUrl = `${SERVER_BASE_URL}/assets/img/personalizacion/${catSlug}/opciones/${opcId}/`;

            verificarImagen(`${baseUrl}${id}_superior.png`, 'card-superior', 'icon-superior');
            verificarImagen(`${baseUrl}${id}_frontal.png`, 'card-frontal', 'icon-frontal');
            verificarImagen(`${baseUrl}${id}_perfil.png`, 'card-perfil', 'icon-perfil');
        });
    }
</script>

<style>
    .transition-all { transition: all 0.3s ease; }
    .bg-success-subtle { background-color: #d1e7dd; }
</style>
@endpush