// js/gestion-pedido-detalle.js
document.addEventListener('DOMContentLoaded', () => {
  // Previsualizar render 3D
  const inputRender = document.getElementById('file-render');
  const preview = document.getElementById('preview-render');
  const feedbackR = document.getElementById('render-feedback');

  inputRender.addEventListener('change', () => {
    const file = inputRender.files[0];
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['obj','stl'].includes(ext)) {
      feedbackR.textContent = 'Formato no válido. Use .OBJ o .STL.';
      inputRender.value = '';
      return;
    }
    feedbackR.textContent = '';
    preview.src = URL.createObjectURL(file);
  });

  // Validación imagen final
  const inputPhoto = document.getElementById('file-photo');
  const feedbackP = document.getElementById('photo-feedback');
  inputPhoto.addEventListener('change', () => {
    const file = inputPhoto.files[0];
    if (!file) return;
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['jpg','jpeg','png'].includes(ext)) {
      feedbackP.textContent = 'Formato no válido. Use JPG o PNG.';
      inputPhoto.value = '';
    } else {
      feedbackP.textContent = '';
    }
  });
});
