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

        <div class="row g-5">
            {{-- IZQUIERDA: VISUALIZADOR --}}
            <div class="col-lg-7">
                <div class="sticky-top" style="top: 20px; z-index: 10;">
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
                    const res  = await fetch('http://localhost:8080/api/sesiones-anonimas', {
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

    // Separé esta lógica de recalcularEstado para poder usarla antes de cargar las imágenes
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

    // MAGIA: Función que sondea el servidor de Spring Boot para ver qué archivos existen
    async function autoDetectarVistas() {
        const catSlug = document.getElementById('data-categoria').dataset.slug;
        const baseUrl = `http://localhost:8080/uploads/personalizacion/${catSlug}`;
        const rutaOpciones = estado.slugsSeleccionados.join('/');

        estado.vistasDisponibles = [];

        // Creamos promesas para probar cada imagen al mismo tiempo (más rápido)
        const promesas = VISTAS_POSIBLES.map(vistaNombre => {
            return new Promise(resolve => {
                const imgTemp = new Image();
                imgTemp.onload = () => {
                    // Si carga bien, la vista existe
                    estado.vistasDisponibles.push(vistaNombre);
                    resolve();
                };
                imgTemp.onerror = () => {
                    // Si da error (404), la ignoramos
                    resolve();
                };
                // OJO: Asumo que usas .jpg como en tu código frontend. 
                // Si en Spring Boot estás guardando .png, cambia la extensión aquí.
                imgTemp.src = `${baseUrl}/${rutaOpciones}/${vistaNombre}.jpg`;
            });
        });

        // Esperamos a que termine de sondear todas
        await Promise.all(promesas);

        // Ordenamos el array para que siempre quede: superior -> frontal -> perfil
        estado.vistasDisponibles.sort((a, b) => VISTAS_POSIBLES.indexOf(a) - VISTAS_POSIBLES.indexOf(b));

        // Si no encontró ninguna (error de ruta), ponemos frontal por defecto para que no explote
        if (estado.vistasDisponibles.length === 0) {
            estado.vistasDisponibles = ['frontal'];
        }

        // Seleccionamos la primera vista disponible como la principal
        estado.vista = estado.vistasDisponibles[0];

        // Construimos los botones y pintamos la imagen
        renderizarBotones();
        actualizarImagen();
    }

    function renderizarBotones() {
        const contenedor = document.getElementById('contenedor-botones-vista');
        contenedor.innerHTML = ''; // Limpiamos

        // Si solo hay 1 vista, no pintamos nada (o podrías pintar solo el texto)
        if (estado.vistasDisponibles.length <= 1) return;

        let html = '';
        
        // Flecha izquierda
        html += `<button class="btn btn-outline-dark rounded-circle btn-sm" onclick="cambiarVista('anterior')"><i class="bi bi-chevron-left"></i></button>`;
        
        // Botones del centro
        html += `<div class="btn-group" role="group">`;
        estado.vistasDisponibles.forEach(vista => {
            const capitalizada = vista.charAt(0).toUpperCase() + vista.slice(1);
            const claseActiva = vista === estado.vista ? 'active' : '';
            html += `<button type="button" class="btn view-btn ${claseActiva}" data-vista="${vista}" onclick="setVista('${vista}')">${capitalizada}</button>`;
        });
        html += `</div>`;

        // Flecha derecha
        html += `<button class="btn btn-outline-dark rounded-circle btn-sm" onclick="cambiarVista('siguiente')"><i class="bi bi-chevron-right"></i></button>`;

        contenedor.innerHTML = html;
    }

    function setVista(v) {
        estado.vista = v;
        // Actualizamos la clase active de los botones dinámicos
        document.querySelectorAll('[data-vista]').forEach(b => {
            b.classList.toggle('active', b.dataset.vista === v);
        });
        actualizarImagen();
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
        // ... (Tu código de actualizarImagen queda exactamente igual) ...
        const img = document.getElementById('vista-principal');
        const loader = document.getElementById('loading-preview');
        const error = document.getElementById('error-imagen');
        
        const catSlug = document.getElementById('data-categoria').dataset.slug;
        const baseUrl = `http://localhost:8080/uploads/personalizacion/${catSlug}`;
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