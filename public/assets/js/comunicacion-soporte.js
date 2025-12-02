document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const formulario = document.getElementById('formulario-contacto');
    const botonWhatsApp = document.getElementById('boton-whatsapp');
    const verTerminos = document.getElementById('ver-terminos');
    const modalTerminos = document.getElementById('modalTerminos');
    const cerrarModal = document.querySelector('.cerrar-modal');
    const aceptarTerminos = document.querySelector('.boton-aceptar-terminos');
    
    // ID único para el formulario
    function generarIdUnico() {
      return 'FORM-' + Date.now().toString(36) + Math.random().toString(36).substr(2, 5).toUpperCase();
    }
    
    // Mostrar modal de términos
    verTerminos.addEventListener('click', function(e) {
      e.preventDefault();
      modalTerminos.style.display = 'flex';
    });
    
    // Cerrar modal
    cerrarModal.addEventListener('click', function() {
      modalTerminos.style.display = 'none';
    });
    
    // Aceptar términos
    aceptarTerminos.addEventListener('click', function() {
      document.getElementById('terminos').checked = true;
      modalTerminos.style.display = 'none';
    });
    
    // Envío del formulario
    formulario.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validar términos aceptados
      if (!document.getElementById('terminos').checked) {
        alert('Debe aceptar los términos y condiciones para continuar');
        return;
      }
      
      // Generar ID único
      const formId = generarIdUnico();
      
      // Simular envío (en producción sería una llamada AJAX)
      console.log('Formulario enviado con ID:', formId);
      
      // Mostrar confirmación
      alert(`¡Formulario enviado con éxito!\nSu número de seguimiento es: ${formId}`);
      
      // Habilitar WhatsApp
      botonWhatsApp.removeAttribute('disabled');
      
      // Resetear formulario (opcional)
      formulario.reset();
    });
    
    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(e) {
      if (e.target === modalTerminos) {
        modalTerminos.style.display = 'none';
      }
    });
  });