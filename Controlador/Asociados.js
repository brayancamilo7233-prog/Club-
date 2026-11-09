const formulario = document.querySelector("#formRegistro, #formPerfil");

if (formulario) {
    formulario.addEventListener("submit", async function (event) {
        event.preventDefault();

        const botonGuardar = formulario.querySelector('button[type="submit"]');
        const mensajeRegistro = document.getElementById("mensajeRegistro");

        if (!botonGuardar || !mensajeRegistro) {
            console.error("Falta el botón de envío o el área de mensajes del formulario.");
            return;
        }

        const datosFormulario = Object.fromEntries(new FormData(formulario).entries());
        const confirmarContrasena = datosFormulario.confirmPassword;

        if (confirmarContrasena !== undefined) {
            if (datosFormulario.password !== confirmarContrasena) {
                mensajeRegistro.textContent = "Las contraseñas no coinciden.";
                mensajeRegistro.dataset.tipo = "error";
                formulario.elements.confirmPassword.focus();
                return;
            }
            delete datosFormulario.confirmPassword;
        }

        botonGuardar.disabled = true;
        mensajeRegistro.textContent = "Guardando...";
        mensajeRegistro.dataset.tipo = "";

        try {
            const respuesta = await fetch(formulario.action, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(datosFormulario),
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
