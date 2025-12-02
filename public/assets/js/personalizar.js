// Objeto global con la selección del usuario
const seleccion = { gema: null, forma: null, material: null, tamano: null, talla: null };

// Detectar BASE_URL desde el body
const BASE_URL = document.body.dataset.baseUrl;

// Inicializar botones dinámicos
document.querySelectorAll(".btn-opcion").forEach(btn => {
  btn.addEventListener("click", () => {
    const grupo = btn.closest("[data-key]");
    if (!grupo) return;

    const key = grupo.dataset.key;         // gema, forma, material, tamano, talla
    const value = btn.dataset.valor;       // ← coincide con data-valor

    // Marcar activo
    grupo.querySelectorAll("button").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    // Guardar selección
    seleccion[key] = value;

    // Refrescar inputs ocultos
    const hidden = document.getElementById("f-" + key);
    if (hidden) hidden.value = value;

    // Solo actualizar vistas cuando hay gema, forma y material
    actualizarVistas();
  });
});

// Inicializar según botones activos al cargar
document.addEventListener("DOMContentLoaded", () => {
  ["vista-superior","vista-frontal","vista-perfil","vista-principal"].forEach(id => {
    const img = document.getElementById(id);
    if (img) {
      img.onerror = () => { img.src = `${BASE_URL}/assets/img/personalizacionproductos/placeholder.jpg`; };
    }
  });

  document.querySelectorAll("[data-key]").forEach(grupo => {
    const key = grupo.dataset.key;
    const activo = grupo.querySelector("button.active");
    if (activo) {
      const value = activo.dataset.valor;
      seleccion[key] = value;
      const hidden = document.getElementById("f-" + key);
      if (hidden) hidden.value = value;
    }
  });

  actualizarVistas();
});

// Cambiar vista principal al hacer clic en miniaturas
function cambiarVista(imgElem) {
  const principal = document.getElementById("vista-principal");
  if (principal && imgElem) {
    principal.src = imgElem.src;
    principal.alt = imgElem.alt;
  }
}

// Construir ruta de imagen
function rutaVista(gema, forma, material, vista) {
  return `${BASE_URL}/assets/img/personalizacionproductos/vistas-anillos/${gema}/${forma}/${material}/${vista}.jpg`;
}

// Actualizar las 3 miniaturas y la imagen principal
function actualizarVistas() {
  const gema = seleccion["gema"];
  const forma = seleccion["forma"];
  const material = seleccion["material"];
  if (!gema || !forma || !material) return;

  ["superior", "frontal", "perfil"].forEach(v => {
    const img = document.getElementById("vista-" + v);
    if (img) img.src = rutaVista(gema, forma, material, v);
  });

  const principal = document.getElementById("vista-principal");
  if (principal) principal.src = rutaVista(gema, forma, material, "superior");
}

// Validación básica al enviar
document.getElementById("form-personalizar").addEventListener("submit", (e) => {
  const faltan = Object.entries(seleccion).filter(([,v]) => !v).map(([k]) => k);
  if (faltan.length) {
    e.preventDefault();
    alert("Faltan: " + faltan.join(", "));
  }
});
