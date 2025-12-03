// Script para gestionar la carga, edición y eliminación de inspiraciones vía AJAX
// Debes enlazar este archivo en tu HTML de gestión de inspiración

document.addEventListener('DOMContentLoaded', function() {
  cargarInspiraciones();

  // Eliminar inspiración
  document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('boton-aceptar')) {
      const card = e.target.closest('.card.carta');
      if (!card) return;
      const porId = card.dataset.porId;
      if (!porId) return;
      if (confirm('¿Estás seguro de eliminar este diseño?')) {
        fetch('../php/inspiracion/eliminar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'por_id=' + encodeURIComponent(porId)
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            card.remove();
            alert('Diseño eliminado correctamente');
          } else {
            alert('Error al eliminar: ' + (data.error || '')); 
          }
        });
      }
    }
  });
});

function cargarInspiraciones() {
  fetch('../php/inspiracion/listar.php')
    .then(r => r.json())
    .then(data => {
      const row = document.querySelector('.row');
      row.innerHTML = '';
      data.forEach(ins => {
        row.innerHTML += crearCard(ins);
      });
    });
}

function crearCard(ins) {
  // Corregir la ruta de la imagen para que funcione desde el admin
  let imgSrc = ins.por_imagen;
  if (imgSrc && !imgSrc.startsWith('..')) {
    imgSrc = '../' + imgSrc;
  }
  return `
  <div class="carta" data-por-id="${ins.por_id}">
    <div class="cara card-body frente">
      <div class="card-img-container">
        <img src="${imgSrc}" alt="Joyería">
      </div>
      <h5 class="card-title"><strong>${ins.por_titulo}</strong></h5>
      <p class="card-text">${ins.por_descripcion}</p>
    </div>
    <div class="cara card-body atras">
      <h5 class="card-title"><strong>Detalles del Diseño</strong></h5>
      <ul class="list-unstyled">
        <li>○ Categoría: ${ins.por_categoria}</li>
      </ul>
      <div class="d-flex justify-content-between gap-3 mt-3">
        <a href="formulario-catalogo.php?por_id=${ins.por_id}" class="btn btn-warning btn-sm boton-editar">
          <i class="bi bi-pencil-square"></i> Editar
        </a>
        <button class="btn btn-danger btn-sm boton-aceptar">
          <i class="bi bi-trash"></i> Eliminar
        </button>
      </div>
    </div>
  </div>
  `;
}
