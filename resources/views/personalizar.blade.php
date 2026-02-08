@extends('layouts.app')

@section('title', 'Diseña tu ' . ($categoria['nombre'] ?? 'Joya'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/personalizar.css') }}" />
<style>
    /* Estilos sutiles sin relleno azul */
    .option-btn {
        width: 100%;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        background: white;
        transition: all 0.2s;
        font-weight: 500;
        color: #555;
        text-align: center;
        font-size: 0.95rem;
    }
    .option-btn:hover {
        border-color: #aaa;
        background-color: #f8f9fa;
    }
    /* Estilo Activo: Borde oscuro elegante */
    .option-btn.active {
        border-color: #222;
        background-color: white;
        color: #000;
        font-weight: 600;
        border-width: 2px;
    }
    .preview-container {
        background-color: #f8f9fa;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        min-height: 450px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="personalizar-container animate-in">
    <div class="container my-5">

        {{-- Menú de Categorías --}}
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <ul class="nav nav-pills justify-content-center bg-white p-2 rounded shadow-sm border">
                    @foreach($categorias as $cat)
                        <li class="nav-item">
                            <a class="nav-link {{ $cat['slug'] === $categoria['slug'] ? 'active bg-dark' : 'text-dark' }} fw-bold px-4" 
                               href="{{ route('personalizar.show', ['slug' => $cat['slug']]) }}">
                                {{ $cat['nombre'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <div class="text-center mb-5">
            <h1 class="display-6 fw-bold">Personalizador de {{ $categoria['nombre'] }}</h1>
        </div>

        <div class="row g-5">
            {{-- IZQUIERDA: VISUALIZADOR --}}
            <div class="col-lg-7">
                <div class="sticky-top" style="top: 20px; z-index: 10;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="preview-container">
                                <div id="loading-preview" class="spinner-border text-primary position-absolute" role="status" style="z-index: 20; display:none;"></div>
                                <img id="vista-principal" src="" alt="Vista previa" class="img-fluid" style="max-height: 450px; opacity: 0; transition: opacity 0.3s;">
                                <div id="error-imagen" class="text-center position-absolute text-danger" style="display: none;">
                                    <i class="bi bi-card-image fs-1"></i>
                                    <p class="mt-2 fw-bold mb-0">Vista no disponible</p>
                                </div>
                            </div>
                            
                            {{-- Controles de Vista --}}
                            <div class="d-flex justify-content-center gap-2 py-3 bg-white border-top">
                                <button class="btn btn-outline-dark rounded-circle btn-sm" onclick="cambiarVista('anterior')"><i class="bi bi-chevron-left"></i></button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-dark btn-sm active" data-vista="superior" onclick="setVista('superior')">Superior</button>
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-vista="frontal" onclick="setVista('frontal')">Frontal</button>
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-vista="perfil" onclick="setVista('perfil')">Perfil</button>
                                </div>
                                <button class="btn btn-outline-dark rounded-circle btn-sm" onclick="cambiarVista('siguiente')"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DERECHA: OPCIONES --}}
            <div class="col-lg-5">
                <form method="POST" action="{{ route('personalizar.guardar') }}" id="form-personalizar">
                    @csrf
                    <input type="hidden" name="catId" value="{{ $categoria['id'] }}">
                    <div id="data-categoria" data-slug="{{ $categoria['slug'] }}"></div>

                    @foreach($opciones as $opcion)
                        <div class="mb-4 option-group">
                            <h6 class="fw-bold text-uppercase text-muted small border-bottom pb-2 mb-3">{{ $opcion['nombre'] }}</h6>
                            <div class="options-grid">
                                @foreach($opcion['valores'] as $index => $valor)
                                    <button type="button" 
                                            class="option-btn {{ $index === 0 ? 'active' : '' }}" 
                                            data-opcion-id="{{ $opcion['id'] }}"
                                            data-valor-id="{{ $valor['id'] }}"
                                            data-valor-slug="{{ \Illuminate\Support\Str::slug($valor['nombre']) }}"
                                            data-valor-nombre="{{ $valor['nombre'] }}"
                                            onclick="seleccionarValor(this)">
                                        {{ $valor['nombre'] }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="opciones[{{ $opcion['id'] }}]" id="input-opcion-{{ $opcion['id'] }}" value="{{ $opcion['valores'][0]['id'] ?? '' }}">
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-dark w-100 py-3 fw-bold text-uppercase mt-4">Guardar Diseño</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ESTADO GLOBAL ÚNICO
    let estado = {
        vista: 'superior',
        slugsSeleccionados: []
    };

    document.addEventListener('DOMContentLoaded', () => {
        recalcularEstado();
    });

    function seleccionarValor(boton) {
        const grupo = boton.closest('.option-group');
        grupo.querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
        boton.classList.add('active');

        // Actualizar input para el form
        const opcId = boton.dataset.opcionId;
        document.getElementById('input-opcion-' + opcId).value = boton.dataset.valorId;

        recalcularEstado();
    }

    function recalcularEstado() {
        estado.slugsSeleccionados = [];
        const exclusiones = ['talla', 'tamaño'];

        document.querySelectorAll('.option-group').forEach(grupo => {
            const titulo = grupo.querySelector('h6').innerText.toLowerCase();
            const esEfectivo = !exclusiones.some(p => titulo.includes(p));

            if (esEfectivo) {
                const activo = grupo.querySelector('.option-btn.active');
                if (activo) estado.slugsSeleccionados.push(activo.dataset.valorSlug);
            }
        });
        actualizarImagen();
    }

    function setVista(v) {
        estado.vista = v;
        document.querySelectorAll('[data-vista]').forEach(b => b.classList.toggle('active', b.dataset.vista === v));
        actualizarImagen();
    }

    function cambiarVista(dir) {
        const vistas = ['superior', 'frontal', 'perfil'];
        let i = vistas.indexOf(estado.vista);
        i = (dir === 'siguiente') ? (i + 1) % 3 : (i - 1 + 3) % 3;
        setVista(vistas[i]);
    }

    function actualizarImagen() {
        const img = document.getElementById('vista-principal');
        const loader = document.getElementById('loading-preview');
        const error = document.getElementById('error-imagen');
        
        const catSlug = document.getElementById('data-categoria').dataset.slug;
        const baseUrl = `http://localhost:8080/assets/img/personalizacion/${catSlug}`;
        const rutaOpciones = estado.slugsSeleccionados.join('/');
        const urlFinal = `${baseUrl}/${rutaOpciones}/${estado.vista}.jpg`;

        console.log("Cargando:", urlFinal);

        loader.style.display = 'block';
        img.style.opacity = '0.3';
        error.style.display = 'none';

        const preload = new Image();
        preload.onload = () => {
            img.src = urlFinal;
            img.style.opacity = '1';
            loader.style.display = 'none';
        };
        preload.onerror = () => {
            loader.style.display = 'none';
            img.style.opacity = '0';
            error.style.display = 'block';
        };
        preload.src = urlFinal;
    }
</script>
@endpush