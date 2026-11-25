const formulario = document.querySelector("#formRegistro, #formPerfil");
const esPerfil = formulario !== null && formulario.id === "formPerfil";

// Datos del usuario que inició sesión (solo en Vista/perfil.html)
let perfilCargado = null;

function mostrarPerfil(perfil) {
    perfilCargado = perfil;
    const campos = formulario.elements;

    ["documento", "nombres", "apellidos", "fechaNacimiento",
     "direccionResidencia", "telefono", "email"].forEach((campo) => {
        campos[campo].value = perfil[campo] ?? "";
    });

    // Género: se marca el radio que coincide (sin importar mayúsculas)
    const genero = String(perfil.genero ?? "").toLowerCase();
    formulario.querySelectorAll('input[name="genero"]').forEach((radio) => {
        radio.checked = radio.value.toLowerCase() === genero;
    });

    // Ciudad: si la guardada no está en la lista, se agrega para no perderla
    const ciudad = String(perfil.ciudad ?? "");
    const selectCiudad = campos.ciudad;
    const opcion = [...selectCiudad.options].find(
        (op) => op.value.toLowerCase() === ciudad.toLowerCase()
    );
    if (opcion) {
        selectCiudad.value = opcion.value;
    } else if (ciudad !== "") {
        selectCiudad.add(new Option(ciudad, ciudad, true, true));
    }
}

async function cargarPerfil() {
    const mensajeRegistro = document.getElementById("mensajeRegistro");
    try {
        const respuesta = await fetch(formulario.action, { credentials: "same-origin" });
        const resultado = await respuesta.json().catch(() => ({}));

        if (respuesta.status === 401) {
            window.location.href = "login.html";
            return;
        }
        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || "No fue posible cargar tu perfil.");
        }

        mostrarPerfil(resultado.perfil);
    } catch (error) {
        mensajeRegistro.textContent = error.message;
        mensajeRegistro.dataset.tipo = "error";
    }
}

if (esPerfil) {
    cargarPerfil();

    // "Deshacer cambios" vuelve a los datos cargados en lugar de dejar el formulario vacío
    formulario.addEventListener("reset", (event) => {
        if (perfilCargado) {
            event.preventDefault();
            mostrarPerfil(perfilCargado);
            document.getElementById("mensajeRegistro").textContent = "";
        }
    });
}

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

            if (esPerfil && respuesta.status === 401) {
                window.location.href = "login.html";
                return;
            }

            if (!respuesta.ok || !resultado.success) {
                mensajeRegistro.textContent =
                    resultado.mensaje || "No fue posible guardar el asociado.";
                mensajeRegistro.dataset.tipo = "error";
                return;
            }

            mensajeRegistro.textContent = resultado.mensaje;
            mensajeRegistro.dataset.tipo = "exito";

            if (esPerfil) {
                // En Mi perfil los datos se quedan en pantalla (ya guardados)
                mostrarPerfil(resultado.perfil);
            } else {
                formulario.reset();
            }
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
