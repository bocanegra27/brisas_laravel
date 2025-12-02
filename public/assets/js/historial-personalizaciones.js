document.addEventListener('DOMContentLoaded', () => {
  // Cachear selectores DOM
  const DOM = {
    filtroFecha: document.getElementById('filtro-fecha'),
    filtroEstado: document.getElementById('filtro-estado'),
    sinResultados: document.getElementById('sin-resultados'),
    personalizacionesContainer: document.getElementById('personalizaciones-container'),
    modalDetalles: document.getElementById('modalDetalles'),
    cerrarModal: document.querySelector('.cerrar-modal'),
    contenidoDetalles: document.getElementById('contenido-detalles'),
    tarjetas: document.querySelectorAll('.tarjeta-personalizacion')
  };

  // Constantes para estados y clases
  const ESTADOS = {
    COMPLETADO: 'completado',
    EN_PROCESO: 'en-proceso'
  };

  const CLASES = {
    OCULTO: 'oculto',
    ESTADO: 'estado'
  };

  // Datos de las personalizaciones
  const personalizaciones = [
    {
      id: 'P-001',
      fecha: '15/01/2025',
      estado: ESTADOS.COMPLETADO,
      imagen: '../imagenes/Portafolio/anillo sencillo verde frente.jpg',
      nombre: 'Anillo de compromiso con esmeralda',
      especificaciones: {
        material: 'Oro blanco 18k',
        piedraPrincipal: 'Esmeralda colombiana (1.2 ct)',
        piedrasSecundarias: 'Diamantes (0.5 ct total)',
        talla: '6.5',
        precio: '$2,500,000',
        fechaEntrega: '20/02/2025'
      },
      detallesAdicionales: 'Anillo personalizado con esmeralda central y diamantes en el aro. Diseño inspirado en la colección Selva.'
    },
    {
      id: 'P-002',
      fecha: '10/12/2024',
      estado: ESTADOS.EN_PROCESO,
      imagen: '../imagenes/Portafolio/anillo azul grande frente.jpg',
      nombre: 'Anillo de compromiso clásico',
      especificaciones: {
        material: 'Oro amarillo 14k',
        piedraPrincipal: 'Diamante (0.8 ct)',
        piedrasSecundarias: 'Diamantes (0.3 ct total)',
        talla: '7.0',
        precio: '$3,200,000',
        fechaEntrega: '15/01/2025'
      },
      detallesAdicionales: 'Diseño clásico de corte solitario con diamante central y pequeños diamantes en el aro. Elaborado a mano por nuestros artesanos.'
    }
  ];

  /**
   * Verifica si hay resultados visibles y actualiza la UI
   */
  const verificarResultados = () => {
    const visibles = document.querySelectorAll(`.tarjeta-personalizacion:not(.${CLASES.OCULTO})`).length;
    const hayResultados = visibles > 0;

    DOM.sinResultados.style.display = hayResultados ? 'none' : 'block';
    DOM.personalizacionesContainer.style.display = hayResultados ? 'block' : 'none';
  };

  /**
   * Aplica los filtros seleccionados a las tarjetas
   */
  const aplicarFiltros = () => {
    const estado = DOM.filtroEstado.value;
    const fecha = DOM.filtroFecha.value;

    DOM.tarjetas.forEach(tarjeta => {
      const estadoTarjeta = tarjeta.querySelector(`.${CLASES.ESTADO}`).classList[1];
      const coincideEstado = estado === 'todos' || estado === estadoTarjeta;
      const coincideFecha = true; // Implementar filtrado por fecha si es necesario

      tarjeta.classList.toggle(CLASES.OCULTO, !(coincideEstado && coincideFecha));
    });

    verificarResultados();
  };

  /**
   * Genera HTML para las especificaciones del producto
   * @param {Object} especificaciones 
   * @returns {string} HTML generado
   */
  const generarEspecificacionesHTML = (especificaciones) => {
    return Object.entries(especificaciones)
      .map(([key, value]) => {
        const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
        return `<li><strong>${label}:</strong> ${value}</li>`;
      })
      .join('');
  };

  /**
   * Muestra los detalles de una personalización en el modal
   * @param {string} id - ID de la personalización
   */
  const mostrarDetalles = (id) => {
    const item = personalizaciones.find(p => p.id === id);
    if (!item) return;

    const especificacionesHTML = generarEspecificacionesHTML(item.especificaciones);
    const estadoTexto = item.estado === ESTADOS.COMPLETADO ? 'Completado' : 'En proceso';

    DOM.contenidoDetalles.innerHTML = `
      <h2>${item.nombre}</h2>
      <div class="detalle-contenido">
        <div class="imagen-detalle">
          <img src="${item.imagen}" alt="${item.nombre}" loading="lazy">
        </div>
        <div class="info-detalle">
          <h3>Especificaciones</h3>
          <ul>
            <li><strong>Fecha:</strong> ${item.fecha}</li>
            <li><strong>Estado:</strong> <span class="${CLASES.ESTADO} ${item.estado}">${estadoTexto}</span></li>
            ${especificacionesHTML}
          </ul>
          <h3>Detalles adicionales</h3>
          <p>${item.detallesAdicionales}</p>
        </div>
      </div>
    `;

    DOM.modalDetalles.style.display = 'flex';
  };

  /**
   * Maneja el evento click en los botones de acción
   * @param {Event} e - Evento de click
   */
  const manejarClickAcciones = (e) => {
    const btnDetalle = e.target.closest('.ver-detalle');
    const btnReutilizar = e.target.closest('.reutilizar');

    if (btnDetalle) {
      const tarjeta = btnDetalle.closest('.tarjeta-personalizacion');
      mostrarDetalles(tarjeta?.dataset.id || 'P-001');
    }

    if (btnReutilizar) {
      alert('Funcionalidad de reutilizar diseño en desarrollo');
    }
  };

  // Event Listeners
  DOM.filtroFecha.addEventListener('change', aplicarFiltros);
  DOM.filtroEstado.addEventListener('change', aplicarFiltros);
  DOM.cerrarModal.addEventListener('click', () => DOM.modalDetalles.style.display = 'none');
  
  window.addEventListener('click', e => {
    if (e.target === DOM.modalDetalles) DOM.modalDetalles.style.display = 'none';
  });

  document.addEventListener('click', manejarClickAcciones);

  // Inicialización
  verificarResultados();
});