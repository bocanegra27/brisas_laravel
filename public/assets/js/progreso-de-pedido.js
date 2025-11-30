// RF-019: Notificaciones automáticas (solo manuales)
function mostrarNotificacion(mensaje) {
  const modal = document.getElementById('modalNotificacion');
  document.getElementById('mensaje-notificacion').textContent = mensaje;
  modal.style.display = 'flex';
  
  // Cerrar modal después de 5 segundos
  setTimeout(() => {
    modal.style.display = 'none';
  }, 5000);
}

// Event listeners básicos
document.addEventListener('DOMContentLoaded', () => {
  // Cerrar modal
  document.querySelector('.cerrar-modal').addEventListener('click', () => {
    document.getElementById('modalNotificacion').style.display = 'none';
  });

  // Notificar problema
  document.querySelector('.boton-problema').addEventListener('click', () => {
    mostrarNotificacion("Hemos recibido tu reporte. Te contactaremos pronto.");
  });

  // Cambiar imagen principal al hacer clic en miniaturas
  document.querySelectorAll('.miniaturas img').forEach(miniatura => {
    miniatura.addEventListener('click', () => {
      document.querySelector('.imagen-principal').src = miniatura.src;
    });
  });
});