@extends('layouts.app')

@section('title', 'Personaliza tu Joya - Brisas Gems')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/personalizar.css') }}" />
@endpush

@section('content')
<div class="personalizar-container">
    <div class="container my-5">
        
        <!-- Header de personalización -->
        <div class="personalizar-header text-center mb-5 animate-in">
            <h1>Diseña tu Joya Perfecta</h1>
            <p class="text-muted">Personaliza cada detalle y crea una pieza única</p>
        </div>

        <!-- Mensajes flash -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger animate-in">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Contenido principal -->
        <div class="row g-4">
            
            <!-- Columna izquierda: Vista previa -->
            <div class="col-lg-6">
                <div class="preview-section card shadow-sm animate-in animate-delay-1">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-4">Vista Previa</h5>
                        
                        <!-- Loading spinner -->
                        <div class="loading-spinner" id="loading-preview" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        
                        <!-- Imagen principal - AHORA USA PROXY -->
                        <div class="preview-image-container">
                            <img 
                                id="vista-principal" 
                                src="{{ url('/imagen/vista-anillo?gema=diamante&forma=redonda&material=oro-amarillo&vista=frontal') }}" 
                                alt="Vista previa de la joya" 
                                class="img-fluid preview-image">
                        </div>
                        
                        <!-- Controles de vista -->
                        <div class="view-controls d-flex justify-content-center align-items-center gap-3 mt-4">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-vista-anterior">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <span class="badge bg-primary px-3 py-2" id="current-view-label">Vista Frontal</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-vista-siguiente">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                        
                        <!-- Miniaturas de vistas - AHORA USAN PROXY -->
                        <div class="view-thumbnails d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="thumbnail-btn" data-view="superior">
                                <img src="{{ url('/imagen/vista-anillo?gema=diamante&forma=redonda&material=oro-amarillo&vista=superior') }}" 
                                     alt="Vista Superior" class="img-thumbnail">
                                <span class="thumbnail-label">Superior</span>
                            </button>
                            <button type="button" class="thumbnail-btn active" data-view="frontal">
                                <img src="{{ url('/imagen/vista-anillo?gema=diamante&forma=redonda&material=oro-amarillo&vista=frontal') }}" 
                                     alt="Vista Frontal" class="img-thumbnail">
                                <span class="thumbnail-label">Frontal</span>
                            </button>
                            <button type="button" class="thumbnail-btn" data-view="perfil">
                                <img src="{{ url('/imagen/vista-anillo?gema=diamante&forma=redonda&material=oro-amarillo&vista=perfil') }}" 
                                     alt="Vista Perfil" class="img-thumbnail">
                                <span class="thumbnail-label">Perfil</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Opciones -->
            <div class="col-lg-6">
                <div class="options-section card shadow-sm animate-in animate-delay-2">
                    <div class="card-body">
                        <form method="POST" action="{{ route('personalizar.guardar') }}" id="form-personalizar">
                            @csrf

                            <!-- Forma de la gema -->
                            @if(isset($valores['forma']) && !empty($valores['forma']))
                            <div class="option-group mb-4">
                                <h5 class="option-title">
                                    <i class="bi bi-gem me-2"></i>Forma de la Gema
                                </h5>
                                <div class="options-grid">
                                    @foreach($valores['forma'] as $index => $valor)
                                    <button type="button" 
                                            class="option-btn {{ $index === 0 ? 'active' : '' }}" 
                                            data-category="forma"
                                            data-value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valor['nombre'])) }}">
                                        @if(!empty($valor['imagen']))
                                        <img src="{{ url('/imagen/icono-opcion?categoria=forma&archivo=' . urlencode($valor['imagen'])) }}" 
                                             alt="{{ $valor['nombre'] }}" 
                                             class="option-icon">
                                        @endif
                                        <span class="option-label">{{ $valor['nombre'] }}</span>
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="forma" id="input-forma" value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valores['forma'][0]['nombre'] ?? '')) }}">
                            </div>
                            @endif

                            <!-- Gema central -->
                            @if(isset($valores['gema']) && !empty($valores['gema']))
                            <div class="option-group mb-4">
                                <h5 class="option-title">
                                    <i class="bi bi-star me-2"></i>Gema Central
                                </h5>
                                <div class="options-grid">
                                    @foreach($valores['gema'] as $index => $valor)
                                    <button type="button" 
                                            class="option-btn {{ $index === 0 ? 'active' : '' }}" 
                                            data-category="gema"
                                            data-value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valor['nombre'])) }}">
                                        @if(!empty($valor['imagen']))
                                        <img src="{{ url('/imagen/icono-opcion?categoria=gema&archivo=' . urlencode($valor['imagen'])) }}" 
                                             alt="{{ $valor['nombre'] }}" 
                                             class="option-icon">
                                        @endif
                                        <span class="option-label">{{ $valor['nombre'] }}</span>
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="gema" id="input-gema" value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valores['gema'][0]['nombre'] ?? '')) }}">
                            </div>
                            @endif

                            <!-- Material -->
                            @if(isset($valores['material']) && !empty($valores['material']))
                            <div class="option-group mb-4">
                                <h5 class="option-title">
                                    <i class="bi bi-palette me-2"></i>Material
                                </h5>
                                <div class="options-grid">
                                    @foreach($valores['material'] as $index => $valor)
                                    <button type="button" 
                                            class="option-btn {{ $index === 0 ? 'active' : '' }}" 
                                            data-category="material"
                                            data-value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valor['nombre'])) }}">
                                        @if(!empty($valor['imagen']))
                                        <img src="{{ url('/imagen/icono-opcion?categoria=material&archivo=' . urlencode($valor['imagen'])) }}" 
                                             alt="{{ $valor['nombre'] }}" 
                                             class="option-icon">
                                        @endif
                                        <span class="option-label">{{ $valor['nombre'] }}</span>
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="material" id="input-material" value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valores['material'][0]['nombre'] ?? '')) }}">
                            </div>
                            @endif

                            <!-- Tamaño y Talla -->
                            <div class="row g-3 mb-4">
                                <!-- Tamaño -->
                                @if(isset($valores['tamano']) && !empty($valores['tamano']))
                                <div class="col-md-6">
                                    <div class="option-group">
                                        <h5 class="option-title">
                                            <i class="bi bi-rulers me-2"></i>Tamaño
                                        </h5>
                                        <select class="form-select" name="tamano" id="select-tamano">
                                            @foreach($valores['tamano'] as $valor)
                                            <option value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valor['nombre'])) }}">
                                                {{ $valor['nombre'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif

                                <!-- Talla -->
                                @if(isset($valores['talla']) && !empty($valores['talla']))
                                <div class="col-md-6">
                                    <div class="option-group">
                                        <h5 class="option-title">
                                            <i class="bi bi-circle me-2"></i>Talla
                                        </h5>
                                        <select class="form-select" name="talla" id="select-talla">
                                            @foreach($valores['talla'] as $valor)
                                            <option value="{{ strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'a', 'e', 'i', 'o', 'u'], $valor['nombre'])) }}">
                                                {{ $valor['nombre'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Resumen -->
                            <div class="summary-box mb-4">
                                <h6 class="mb-3">
                                    <i class="bi bi-card-checklist me-2"></i>Resumen de tu Personalización
                                </h6>
                                <div id="summary-content" class="summary-content">
                                    <p class="mb-1"><strong>Forma:</strong> <span id="summary-forma">-</span></p>
                                    <p class="mb-1"><strong>Gema:</strong> <span id="summary-gema">-</span></p>
                                    <p class="mb-1"><strong>Material:</strong> <span id="summary-material">-</span></p>
                                    <p class="mb-1"><strong>Tamaño:</strong> <span id="summary-tamano">-</span></p>
                                    <p class="mb-0"><strong>Talla:</strong> <span id="summary-talla">-</span></p>
                                </div>
                            </div>

                            <!-- Botón guardar -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="btn-guardar">
                                    <i class="bi bi-save me-2"></i>Guardar Personalización
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/personalizar.js') }}"></script>
@endpush