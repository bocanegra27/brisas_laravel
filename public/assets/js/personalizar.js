// Estado global de la personalización
let configuracionActual = {
    vistas: ['superior', 'frontal', 'perfil'],
    vistaActiva: 'superior',
    selecciones: {} // Aquí guardaremos { 'Material': 15, 'Gema': 19 }
};

document.addEventListener('DOMContentLoaded', function() {
    // 1. Inicializar selecciones con los valores marcados como 'active' por defecto
    document.querySelectorAll('.option-btn.active').forEach(btn => {
        const opcId = btn.dataset.opcionId;
        const valId = btn.dataset.valorId;
        configuracionActual.selecciones[opcId] = valId;
    });

    actualizarVisualizador();
});

// Función que se dispara al hacer clic en un valor
function seleccionarValor(boton) {
    const opcId = boton.dataset.opcionId;
    const valId = boton.dataset.valorId;

    // Actualizar estado
    configuracionActual.selecciones[opcId] = valId;

    // Actualizar UI (Clases active)
    const grupo = boton.closest('.options-grid');
    grupo.querySelectorAll('.option-btn').forEach(b => b.classList.remove('active'));
    boton.classList.add('active');

    actualizarVisualizador();
}

// La función que construye la URL y cambia la imagen
function actualizarVisualizador() {
    const imgPrincipal = document.getElementById('vista-principal');
    const loading = document.getElementById('loading-preview');

    // Mostramos feedback de carga
    if(loading) loading.style.display = 'block';
    imgPrincipal.style.opacity = '0.5';

    /**
     * LÓGICA DE COMPOSICIÓN:
     * En una joyería real, el anillo se compone de varias capas (Base + Gema).
     * Por ahora, vamos a mostrar la imagen del valor "Dominante" 
     * (el último en el que el usuario hizo clic o el Material).
     */
    
    // Obtenemos el último valor seleccionado para la prueba
    const valoresIds = Object.values(configuracionActual.selecciones);
    const ultimoValorId = valoresIds[valoresIds.length - 1];
    
    // Necesitamos el catSlug y opcId del botón activo para armar la ruta
    const botonActivo = document.querySelector(`.option-btn[data-valor-id="${ultimoValorId}"]`);
    const catSlug = document.querySelector('h1').dataset.catSlug; // Lo sacaremos del HTML
    const opcId = botonActivo.dataset.opcionId;

    // Construimos la ruta hacia tu Spring Boot
    const nuevaUrl = `http://localhost:8080/assets/img/personalizacion/${catSlug}/opciones/${opcId}/${ultimoValorId}_${configuracionActual.vistaActiva}.png`;

    // Cambiar la imagen
    imgPrincipal.src = nuevaUrl;

    imgPrincipal.onload = () => {
        if(loading) loading.style.display = 'none';
        imgPrincipal.style.opacity = '1';
    };

    imgPrincipal.onerror = () => {
        // Si no hay imagen (ej: para Tallas), podrías poner una imagen por defecto
        console.warn("No se encontró la vista específica en el servidor.");
        if(loading) loading.style.display = 'none';
    };
}