// Encabezado de index.html: muestra el usuario y permite cerrar sesión.
// Es aparte de Dashboart.js porque las rutas desde la raíz son distintas.
document.addEventListener("DOMContentLoaded", () => {
  if (localStorage.getItem("logueado") !== "true") {
    document.getElementById("btnIniciarSesion").classList.remove("d-none");
    return;
  }

  document.getElementById("usuarioActual").textContent = localStorage.getItem("nombre") || "Usuario";
  document.getElementById("rolActual").textContent =
    localStorage.getItem("rol") === "admin" ? "admin" : "asociado";
  document.getElementById("datosUsuario").classList.remove("d-none");

  if (localStorage.getItem("rol") === "admin") {
    document.querySelectorAll(".solo-admin").forEach((tarjeta) => tarjeta.classList.remove("d-none"));
  }

  const btnCerrarSesion = document.getElementById("btnCerrarSesion");
  btnCerrarSesion.classList.remove("d-none");
  btnCerrarSesion.addEventListener("click", cerrarSesion);
});

async function cerrarSesion() {
  try {
    const respuesta = await fetch("Controlador/CerrarSesion.php", {
      method: "POST",
      credentials: "same-origin",
    });
    const resultado = await respuesta.json().catch(() => ({}));

    if (!respuesta.ok || !resultado.success) {
      throw new Error("No se pudo cerrar la sesión en el servidor.");
    }
  } catch (error) {
    console.error("Error al cerrar sesión:", error);
  } finally {
    localStorage.clear();
    window.location.href = "Vista/login.html";
  }
}
