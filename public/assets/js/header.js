document.addEventListener("DOMContentLoaded", () => {
  const icono = document.getElementById("icono-usuario");
  const menu = document.getElementById("menu-usuario");

  if (icono && menu) {
    icono.addEventListener("click", () => {
      menu.classList.toggle("activo");
    });

    // Cerrar menú al hacer click fuera
    document.addEventListener("click", (e) => {
      if (!menu.contains(e.target) && !icono.contains(e.target)) {
        menu.classList.remove("activo");
      }
    });
  }
});
