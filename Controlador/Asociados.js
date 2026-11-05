const formulario = document.getElementById("formRegistro");

if (formulario) {
    formulario.addEventListener("submit", async function (event) {
        event.preventDefault();

        const botonGuardar = formulario.querySelector('button[type="submit"]');
        const mensajeRegistro = document.getElementById("mensajeRegistro");

        if (!botonGuardar || !mensajeRegistro) {
            console.error("Falta el botón de envío o el área de mensajes del formulario.");
            return;
        }

        const asociado = Object.fromEntries(new FormData(formulario).entries());

        botonGuardar.disabled = true;
        mensajeRegistro.textContent = "Guardando...";
        mensajeRegistro.dataset.tipo = "";

        try {
            const respuesta = await fetch(formulario.action, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(asociado),
            });
            const resultado = await respuesta.json();

            if (!respuesta.ok || !resultado.success) {
                mensajeRegistro.textContent =
                    resultado.mensaje || "No fue posible guardar el asociado.";
                mensajeRegistro.dataset.tipo = "error";
                return;
            }

            mensajeRegistro.textContent = resultado.mensaje;
            mensajeRegistro.dataset.tipo = "exito";
            formulario.reset();
        } catch (error) {
            console.error("Error al enviar el formulario:", error);
            mensajeRegistro.textContent =
                "Ocurrió un error al enviar el formulario. Por favor, inténtalo de nuevo.";
            mensajeRegistro.dataset.tipo = "error";
        } finally {
            botonGuardar.disabled = false;
        }
    });
}
