 @extends('layouts.app')

@section('title', 'Diseña tu ' . ($categoria['nombre'] ?? 'Joya'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/personalizar.css') }}" />
<style>

    /* 1. CONTENEDOR DE CATEGORÍAS (Menu superior) */
    .custom-nav-link {
        color: var(--color-neutral-600) !important;
        border-radius: 8px !important;
        transition: all 0.3s ease;
    }
    .custom-nav-link.active {
        background: transparent !important;
        color: var(--color-primary) !important;
        position: relative;
    }
    /* Línea esmeralda debajo de la categoría activa */
    .custom-nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 25%;
        right: 25%;
        height: 3px;
        background: var(--color-primary);
        border-radius: 50px;
    }

    /* 3. BOTONES DE VISTA (Frontal, Perfil, Superior) */
    .view-btn {
        background: white !important;
        border: 1px solid var(--color-neutral-200) !important;
        color: var(--color-neutral-600) !important;
    }
    .view-btn.active {
        color: var(--color-primary) !important;
        border-top: 3px solid var(--color-primary) !important;
    }

    /* 4. BOTÓN GUARDAR (Visible y Elegante) */
    .btn-primary {
        background: var(--color-primary) !important;
        border: none !important;
        border-radius: 50px !important;
        padding: 1rem !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(0, 150, 136, 0.2) !important;
    }

    /* Botón base: fondo blanco, borde muy suave y sin negrilla */
    .option-btn {
        width: 100%;
        padding: 15px 10px;
        border: 1px solid #edf2f7; /* Borde casi invisible */
        border-radius: 8px;
        background: #ffffff;
        transition: all 0.3s ease;
        font-weight: 400; /* Quitamos la negrilla */
        color: #4a5568;
        text-align: center;
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); /* Sombra mínima */
    }

    /* Efecto Hover */
    .option-btn:hover {
        background-color: #f8fafc;
        transform: translateY(-2px);
    }

    /* Botón Activo: Estilo "Producción" del Admin */
    .option-btn.active {
        border: 1px solid #edf2f7; /* Mantiene el borde suave */
        background-color: #ffffff;
        color: #009688; /* Color esmeralda de tu marca */
        font-weight: 500; /* Un poco más de peso pero sin ser bold pesado */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    /* La silueta superior (línea esmeralda) */
    .option-btn.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px; /* Grosor de la línea superior */
        background-color: #009688; /* Tu color principal */
        border-radius: 8px 8px 0 0;
    }

    /* Texto general y etiquetas de las opciones */
    .option-label, .option-group h6 {
        color: #1e293b !important; /* Un gris muy oscuro, casi negro azulado */
        font-weight: 600 !important;
    }

    /* Título principal con más presencia */
    .display-6 {
        color: #0f172a !important; /* El tono más oscuro de la escala */
        letter-spacing: -0.02em;
    }

    /* Texto de los botones no seleccionados */
    .option-btn {
        color: #475569 !important; /* Gris medio-oscuro para mejor lectura */
    }

        .preview-sticky-wrapper {
        position: sticky;
        top: 70px;
        z-index: 10;
    }

    @media (max-width: 991px) {
        .preview-sticky-wrapper {
            position: sticky;
            top: 60px;
            z-index: 100;
            background: white;
            padding-bottom: 8px;
        }

        .preview-container {
            min-height: 220px !important;
        }

        #vista-principal {
            max-height: 180px !important;
        }

        #contenedor-botones-vista {
            padding-top: 4px !important;
            padding-bottom: 4px !important;
        }
    }

    @media (max-width: 991px) {
        .preview-mobile-fixed {
            position: sticky;
            top: 60px;
            z-index: 100;
            background: white;
            text-align: center;
            padding: 8px 16px;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            margin: 0 12px 12px 12px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .col-lg-7 {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="personalizar-container animate-in">
    <div class="container mt-3 mb-5">

        {{-- Menú de Categorías --}}
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <ul class="nav nav-pills justify-content-center bg-white p-2 rounded shadow-sm border">
                    @foreach($categorias as $cat)
                        <li class="nav-item">
                            <a class="nav-link custom-nav-link {{ $cat['slug'] === $categoria['slug'] ? 'active' : '' }} fw-bold px-4" 
                               href="{{ route('personalizar.show', ['slug' => $cat['slug']]) }}">
                                {{ $cat['nombre'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="preview-mobile-fixed d-lg-none">
            <div class="d-flex justify-content-center align-items-center" style="min-height: 160px; position: relative;">
                <div id="loading-preview-mobile" class="spinner-border text-primary position-absolute" role="status" style="display:none;"></div>
                <img id="vista-principal-mobile" src="" alt="Vista previa"
                    style="max-height: 140px; width: auto; object-fit: contain; opacity: 0; transition: opacity 0.3s;">
                <div id="error-imagen-mobile" class="text-center text-danger position-absolute" style="display:none;">
                    <i class="bi bi-card-image fs-3"></i>
                </div>
            </div>
            <div id="contenedor-botones-vista-mobile" class="d-flex justify-content-center gap-2 pb-1"></div>
        </div>

        <div class="row g-5">
            {{-- IZQUIERDA: VISUALIZADOR --}}
            <div class="col-lg-7">
                <div class="preview-sticky-wrapper" style="top: 20px; z-index: 10;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                    <div class="preview-container d-flex justify-content-center align-items-center" 
                     style="background: radial-gradient(circle, #ffffff 0%, #f8fafc 100%); min-height: 450px; position: relative;">
                    
                    <div id="loading-preview" class="spinner-border text-primary position-absolute" role="status" style="z-index: 20; display:none;"></div>
                    
                    {{-- Imagen con estilo en línea para asegurar prioridad --}}
                    <img id="vista-principal" src="" alt="Vista previa" 
                         style="max-width: 90%; max-height: 400px; width: auto; height: auto; object-fit: contain; transition: opacity 0.3s; opacity: 0;">
                     
                    <div id="error-imagen" class="text-center position-absolute text-danger" style="display: none;">
                        <i class="bi bi-card-image fs-1"></i>
                        <p class="mt-2 fw-bold mb-0">Vista no disponible</p>
                    </div>
                </div>
                            
                            {{-- Controles de Vista --}}
                            <div class="d-flex justify-content-center gap-2 py-3 bg-white border-top" id="contenedor-botones-vista" style="min-height: 60px;">
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
                    <input type="hidden" name="sesionId" id="input-sesion-anonima" value="">
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

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold text-uppercase mt-4">Guardar Diseño</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ============================================
    // VARIABLES GLOBALES INYECTADAS DESDE LARAVEL
    // ============================================
    // Toma la URL del .env
    const API_BASE_URL = "{{ config('services.spring_api.url') }}";
    // Le quita el '/api' para acceder a la carpeta de imágenes
    const SERVER_BASE_URL = "{{ str_replace('/api', '', config('services.spring_api.url')) }}";

    // ESTADO GLOBAL ÚNICO
    let estado = {
        vista: 'frontal', // Valor temporal, se ajustará automáticamente
        slugsSeleccionados: [],
        vistasDisponibles: [] // Aquí guardaremos las vistas que sí existen
    };

    const VISTAS_POSIBLES = ['superior', 'frontal', 'perfil'];

    document.addEventListener('DOMContentLoaded', async () => {

        @if(!session()->has('user_id'))
            const STORAGE_TOKEN = 'anonymous_sesion_token';
            const STORAGE_ID    = 'anonymous_sesion_id';
            const inputSesion   = document.getElementById('input-sesion-anonima');

            try {
                let sesToken = localStorage.getItem(STORAGE_TOKEN);
                let sesId    = localStorage.getItem(STORAGE_ID);

                if (!sesToken || !sesId) {
                    // Usar la variable inyectada para la API
                    const res  = await fetch(`${API_BASE_URL}/sesiones-anonimas`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({})
                    });
                    const data = await res.json();
                    sesToken = data.sesToken;
                    sesId    = String(data.sesId);
                    localStorage.setItem(STORAGE_TOKEN, sesToken);
                    localStorage.setItem(STORAGE_ID, sesId);
                }

                if (inputSesion) {
                    inputSesion.value = sesId;
                }
            } catch (e) {
                console.warn('No se pudo crear/recuperar sesion anonima:', e);
            }
        @endif

        calcularSlugs();
        await autoDetectarVistas();
    });

    function calcularSlugs() {
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
    }

    function seleccionarValor(boton) {
        const grupo = boton.closest('.option-group');
        grupo.querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
        boton.classList.add('active');

        const opcId = boton.dataset.opcionId;
        const inputOculto = document.getElementById('input-opcion-' + opcId);
        
        if (inputOculto) {
            inputOculto.value = boton.dataset.valorId;
        }

        recalcularEstado();
    }

    function recalcularEstado() {
        calcularSlugs();
        actualizarImagen();
    }

    // Función que sondea el servidor para ver qué archivos existen
    async function autoDetectarVistas() {
        const catSlug = document.getElementById('data-categoria').dataset.slug;
        
        // Usar SERVER_BASE_URL dinámico en lugar de localhost
        const baseUrl = `${SERVER_BASE_URL}/uploads/personalizacion/${catSlug}`;
        const rutaOpciones = estado.slugsSeleccionados.join('/');

        estado.vistasDisponibles = [];

        const promesas = VISTAS_POSIBLES.map(vistaNombre => {
            return new Promise(resolve => {
                const imgTemp = new Image();
                imgTemp.onload = () => {
                    estado.vistasDisponibles.push(vistaNombre);
                    resolve();
                };
                imgTemp.onerror = () => {
                    resolve();
                };
                imgTemp.src = `${baseUrl}/${rutaOpciones}/${vistaNombre}.jpg`;
            });
        });

        await Promise.all(promesas);

        estado.vistasDisponibles.sort((a, b) => VISTAS_POSIBLES.indexOf(a) - VISTAS_POSIBLES.indexOf(b));

        if (estado.vistasDisponibles.length === 0) {
            estado.vistasDisponibles = ['frontal'];
        }

        estado.vista = estado.vistasDisponibles[0];

        renderizarBotones();
        actualizarImagen();
    }

    function renderizarBotones() {
        const contenedor = document.getElementById('contenedor-botones-vista');
        contenedor.innerHTML = ''; 

        if (estado.vistasDisponibles.length <= 1) return;

        let html = '';
        
        html += `<button class="btn btn-outline-dark rounded-circle btn-sm" onclick="cambiarVista('anterior')"><i class="bi bi-chevron-left"></i></button>`;
        
        html += `<div class="btn-group" role="group">`;
        estado.vistasDisponibles.forEach(vista => {
            const capitalizada = vista.charAt(0).toUpperCase() + vista.slice(1);
            const claseActiva = vista === estado.vista ? 'active' : '';
            html += `<button type="button" class="btn view-btn ${claseActiva}" data-vista="${vista}" onclick="setVista('${vista}')">${capitalizada}</button>`;
        });
        html += `</div>`;

        html += `<button class="btn btn-outline-dark rounded-circle btn-sm" onclick="cambiarVista('siguiente')"><i class="bi bi-chevron-right"></i></button>`;

        contenedor.innerHTML = html;

        const contenedorMobile = document.getElementById('contenedor-botones-vista-mobile');
        if (contenedorMobile) contenedorMobile.innerHTML = contenedor.innerHTML;
    }

    function setVista(v) {
        estado.vista = v;
        document.querySelectorAll('[data-vista]').forEach(b => {
            b.classList.toggle('active', b.dataset.vista === v);
        });
        actualizarImagen();
        document.querySelectorAll('[data-vista]').forEach(b => {
        b.classList.toggle('active', b.dataset.vista === v);
});
    }

    function cambiarVista(dir) {
        if (estado.vistasDisponibles.length <= 1) return;

        let i = estado.vistasDisponibles.indexOf(estado.vista);
        if (dir === 'siguiente') {
            i = (i + 1) % estado.vistasDisponibles.length;
        } else {
            i = (i - 1 + estado.vistasDisponibles.length) % estado.vistasDisponibles.length;
        }
        setVista(estado.vistasDisponibles[i]);
    }

    function actualizarImagen() {
        const img = document.getElementById('vista-principal');
        const loader = document.getElementById('loading-preview');
        const error = document.getElementById('error-imagen');
        
        const catSlug = document.getElementById('data-categoria').dataset.slug;
        
        // Usar SERVER_BASE_URL dinámico
        const baseUrl = `${SERVER_BASE_URL}/uploads/personalizacion/${catSlug}`;
        const rutaOpciones = estado.slugsSeleccionados.join('/');
        const urlFinal = `${baseUrl}/${rutaOpciones}/${estado.vista}.jpg`;

        loader.style.display = 'block';
        img.style.opacity = '0.3';
        error.style.display = 'none';

        const preload = new Image();
        preload.onload = () => {
            img.src = urlFinal;
            img.style.opacity = '1';
            loader.style.display = 'none';
            // Sincronizar mobile
            const imgMobile = document.getElementById('vista-principal-mobile');
            if (imgMobile) { imgMobile.src = urlFinal; imgMobile.style.opacity = '1'; }
        };
        preload.onerror = () => {
            loader.style.display = 'none';
            img.style.opacity = '0';
            error.style.display = 'block';
            const imgMobile = document.getElementById('vista-principal-mobile');
            if (imgMobile) imgMobile.style.opacity = '0';
        };
        preload.src = urlFinal;
    }
</script>
@endpush