/**
 * MÓDULO DE PERSONALIZACIÓN - BRISAS GEMS
 * Gestión de estado, vistas y opciones
 * VERSIÓN PROXY: Consume imágenes desde Spring Boot vía Laravel
 */

// ============================================
// ESTADO DE LA PERSONALIZACIÓN
// ============================================
const PersonalizacionState = {
    forma: 'redonda',
    gema: 'diamante',
    material: 'oro-amarillo',
    tamano: '7-mm',
    talla: '5',
    vistaActual: 'superior',
    vistas: ['superior', 'frontal', 'perfil'],

    /**
     * Actualiza una selección
     */
    actualizar(categoria, valor) {
        this[categoria] = valor;
        this.actualizarVistaPrevia();
        this.actualizarResumen();
        this.guardarEnInputs();
    },

    /**
     * Cambia la vista actual
     */
    cambiarVista(vista) {
        if (this.vistas.includes(vista)) {
            this.vistaActual = vista;
            this.actualizarVistaPrevia();
            this.actualizarIndicadorVista();
        }
    },

    /**
     * Construye la URL del proxy para la imagen actual
     * Ahora usa el endpoint proxy de Laravel en lugar de acceso directo
     */
    construirUrlImagen() {
        const baseUrl = document.body.dataset.baseUrl || '';
        return `${baseUrl}/imagen/vista-anillo?gema=${this.gema}&forma=${this.forma}&material=${this.material}&vista=${this.vistaActual}`;
    },

    /**
     * Actualiza la vista previa de la imagen
     */
    actualizarVistaPrevia() {
        const vistaPrincipal = document.getElementById('vista-principal');
        const loadingPreview = document.getElementById('loading-preview');
        
        if (!vistaPrincipal) return;

        const nuevaUrl = this.construirUrlImagen();
        
        // Mostrar loading
        vistaPrincipal.classList.add('loading');
        if (loadingPreview) {
            loadingPreview.style.display = 'flex';
        }

        // Precargar imagen
        const img = new Image();
        img.onload = () => {
            vistaPrincipal.src = nuevaUrl;
            vistaPrincipal.classList.remove('loading');
            if (loadingPreview) {
                loadingPreview.style.display = 'none';
            }
            this.actualizarMiniaturas();
        };
        img.onerror = () => {
            console.error('Error al cargar imagen:', nuevaUrl);
            vistaPrincipal.classList.remove('loading');
            if (loadingPreview) {
                loadingPreview.style.display = 'none';
            }
            // Mostrar placeholder en caso de error
            vistaPrincipal.src = nuevaUrl; // El proxy retornará el SVG placeholder
        };
        img.src = nuevaUrl;
    },

    /**
     * Actualiza las miniaturas de vistas
     */
    actualizarMiniaturas() {
        const baseUrl = document.body.dataset.baseUrl || '';
        const thumbnails = document.querySelectorAll('.thumbnail-btn');
        
        thumbnails.forEach(btn => {
            const vista = btn.dataset.view;
            const img = btn.querySelector('img');
            if (img) {
                img.src = `${baseUrl}/imagen/vista-anillo?gema=${this.gema}&forma=${this.forma}&material=${this.material}&vista=${vista}`;
            }
        });
    },

    /**
     * Actualiza el indicador de vista actual
     */
    actualizarIndicadorVista() {
        const label = document.getElementById('current-view-label');
        if (label) {
            const vistaNombre = {
                'superior': 'Vista Superior',
                'frontal': 'Vista Frontal',
                'perfil': 'Vista Perfil'
            };
            label.textContent = vistaNombre[this.vistaActual] || 'Vista';
        }

        // Actualizar botón activo en miniaturas
        document.querySelectorAll('.thumbnail-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === this.vistaActual);
        });
    },

    /**
     * Actualiza el resumen textual
     */
    actualizarResumen() {
        const actualizarSpan = (id, valor) => {
            const span = document.getElementById(id);
            if (span) {
                span.textContent = this.formatearValor(valor);
            }
        };

        actualizarSpan('summary-forma', this.forma);
        actualizarSpan('summary-gema', this.gema);
        actualizarSpan('summary-material', this.material);
        actualizarSpan('summary-tamano', this.tamano);
        actualizarSpan('summary-talla', this.talla);
    },

    /**
     * Formatea un valor para mostrar
     */
    formatearValor(valor) {
        return valor
            .replace(/-/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    },

    /**
     * Guarda valores en los inputs hidden del form
     */
    guardarEnInputs() {
        const setInput = (id, valor) => {
            const input = document.getElementById(id);
            if (input) input.value = valor;
        };

        setInput('input-forma', this.forma);
        setInput('input-gema', this.gema);
        setInput('input-material', this.material);
    },

    /**
     * Vista siguiente
     */
    vistaSiguiente() {
        const indiceActual = this.vistas.indexOf(this.vistaActual);
        const siguienteIndice = (indiceActual + 1) % this.vistas.length;
        this.cambiarVista(this.vistas[siguienteIndice]);
    },

    /**
     * Vista anterior
     */
    vistaAnterior() {
        const indiceActual = this.vistas.indexOf(this.vistaActual);
        const anteriorIndice = indiceActual - 1 < 0 ? this.vistas.length - 1 : indiceActual - 1;
        this.cambiarVista(this.vistas[anteriorIndice]);
    }
};

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    inicializarOpciones();
    inicializarControlesVista();
    inicializarSelects();
    inicializarFormulario();
    
    // Inicializar resumen con valores por defecto
    PersonalizacionState.actualizarResumen();
    
    // Cargar imagen inicial
    PersonalizacionState.actualizarVistaPrevia();
});

/**
 * Inicializa los botones de opciones
 */
function inicializarOpciones() {
    const botones = document.querySelectorAll('.option-btn[data-category]');
    
    botones.forEach(btn => {
        btn.addEventListener('click', function() {
            const categoria = this.dataset.category;
            const valor = this.dataset.value;
            
            // Remover active de otros botones de la misma categoría
            const hermanos = document.querySelectorAll(`.option-btn[data-category="${categoria}"]`);
            hermanos.forEach(h => h.classList.remove('active'));
            
            // Activar este botón
            this.classList.add('active');
            
            // Actualizar estado
            PersonalizacionState.actualizar(categoria, valor);
        });
    });
}

/**
 * Inicializa los controles de navegación de vista
 */
function inicializarControlesVista() {
    // Botones prev/next
    const btnAnterior = document.getElementById('btn-vista-anterior');
    const btnSiguiente = document.getElementById('btn-vista-siguiente');
    
    if (btnAnterior) {
        btnAnterior.addEventListener('click', () => PersonalizacionState.vistaAnterior());
    }
    
    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', () => PersonalizacionState.vistaSiguiente());
    }
    
    // Miniaturas
    const thumbnails = document.querySelectorAll('.thumbnail-btn');
    thumbnails.forEach(btn => {
        btn.addEventListener('click', function() {
            const vista = this.dataset.view;
            PersonalizacionState.cambiarVista(vista);
        });
    });
    
    // Soporte para gestos de swipe en móvil
    let touchStartX = 0;
    let touchEndX = 0;
    
    const vistaPrincipal = document.getElementById('vista-principal');
    if (vistaPrincipal) {
        vistaPrincipal.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        vistaPrincipal.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }
    
    function handleSwipe() {
        const threshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                // Swipe izquierda - siguiente
                PersonalizacionState.vistaSiguiente();
            } else {
                // Swipe derecha - anterior
                PersonalizacionState.vistaAnterior();
            }
        }
    }
}

/**
 * Inicializa los selects de tamaño y talla
 */
function inicializarSelects() {
    const selectTamano = document.getElementById('select-tamano');
    const selectTalla = document.getElementById('select-talla');
    
    if (selectTamano) {
        selectTamano.addEventListener('change', function() {
            PersonalizacionState.actualizar('tamano', this.value);
        });
        
        // Establecer valor inicial
        if (selectTamano.value) {
            PersonalizacionState.actualizar('tamano', selectTamano.value);
        }
    }
    
    if (selectTalla) {
        selectTalla.addEventListener('change', function() {
            PersonalizacionState.actualizar('talla', this.value);
        });
        
        // Establecer valor inicial
        if (selectTalla.value) {
            PersonalizacionState.actualizar('talla', selectTalla.value);
        }
    }
}

/**
 * Inicializa validación del formulario
 */
function inicializarFormulario() {
    const form = document.getElementById('form-personalizar');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        const btnGuardar = document.getElementById('btn-guardar');
        
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        }
        
        // El formulario se envía normalmente
    });
}

/**
 * Auto-dismiss de alertas después de 5 segundos
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-info)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});

/**
 * Precarga de imágenes para mejorar performance
 * Usa el proxy de Laravel para precargar las 3 vistas
 */
function precargarImagenes() {
    const baseUrl = document.body.dataset.baseUrl || '';
    const { gema, forma, material } = PersonalizacionState;
    
    PersonalizacionState.vistas.forEach(vista => {
        const img = new Image();
        img.src = `${baseUrl}/imagen/vista-anillo?gema=${gema}&forma=${forma}&material=${material}&vista=${vista}`;
    });
}

// Precargar imágenes después de cargar la página
window.addEventListener('load', precargarImagenes);