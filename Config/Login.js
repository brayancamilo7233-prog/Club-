const formulario = document.getElementById("login-form");
const mensaje = document.getElementById("mensaje");

if (formulario) {
  const boton = formulario.querySelector("button[type='submit']");

  formulario.addEventListener("submit", async function (e) {
    e.preventDefault();
    if (mensaje) mensaje.textContent = "";
    if (boton) boton.disabled = true;

    try {
      const respuesta = await fetch(formulario.action, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(
          Object.fromEntries(new FormData(formulario).entries())
        ),
      });

      // Leer el cuerpo una sola vez como texto y luego intentar convertirlo a JSON
      const texto = await respuesta.text();
      let resultado;
      try {
        resultado = JSON.parse(texto);
      } catch {
        console.error("Respuesta no válida del servidor:", texto);
        throw new Error("La respuesta del servidor no es válida.");
      }

      if (!respuesta.ok || !resultado.success) {
        const detalle = resultado?.message || "No fue posible iniciar sesión. Por favor, verifica tus credenciales.";
        if (mensaje) mensaje.textContent = detalle;
        return;
      }

      localStorage.setItem("logueado", "true");
      localStorage.setItem("rol", resultado.rol);
      localStorage.setItem("nombre", resultado.nombre);

      window.location.href = "Dashboart.html";
    } catch (error) {
      console.error("Error al iniciar sesión:", error);
      if (mensaje) {
        mensaje.textContent =
          error?.message ||
          "Ocurrió un error al intentar iniciar sesión. Por favor, inténtalo de nuevo más tarde.";
      }
    } finally {
      if (boton) boton.disabled = false;
    }
  });
}
