document.querySelectorAll(".club-form").forEach((form) => {
  const message = form.querySelector('[role="status"]');
  const submitButton = form.querySelector('[type="submit"]');

  const showMessage = (text, success) => {
    message.textContent = text;
    message.classList.toggle("alert-success", success);
    message.classList.toggle("alert-danger", !success);
    message.classList.remove("d-none");
  };

  const associatedSelect = form.querySelector('select[name="asociado"]');
  if (associatedSelect) {
    fetch("../Modelo/obtenerAsociados.php")
      .then((response) => {
        // Sin sesión: ir al inicio de sesión
        if (response.status === 401) {
          window.location.href = "login.html";
          return new Promise(() => {});
        }
        return response.json();
      })
      .then((result) => {
        if (!result.success) throw new Error(result.mensaje);
        result.asociados.forEach((asociado) => {
          const option = document.createElement("option");
          option.value = asociado.documento;
          option.textContent = `${asociado.nombre} (${asociado.documento})`;
          associatedSelect.append(option);
        });
        if (result.asociados.length === 0) {
          showMessage("Registra primero un asociado para poder continuar.", false);
        }
      })
      .catch(() => showMessage("No se pudo cargar la lista de asociados.", false));
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    form.classList.add("was-validated");

    if (!form.checkValidity()) {
      form.reportValidity();
      message.classList.add("d-none");
      return;
    }

    submitButton.disabled = true;
    try {
      const esAsociado = form.dataset.tipo === "asociado";
      const endpoint = esAsociado
        ? "../Modelo/guardarAsociado.php"
        : `../Controlador/guardarRegistro.php?tipo=${encodeURIComponent(form.dataset.tipo)}`;
      const response = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
      });
      // Sin sesión: ir al inicio de sesión
      if (response.status === 401) {
        window.location.href = "login.html";
        return;
      }
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.mensaje || "No fue posible guardar los datos.");
      }

      form.reset();
      form.classList.remove("was-validated");
      showMessage(result.mensaje, true);
    } catch (error) {
      showMessage(error.message || "No fue posible conectar con el servidor.", false);
    } finally {
      submitButton.disabled = false;
    }
  });

  form.addEventListener("reset", () => {
    form.classList.remove("was-validated");
    message.classList.add("d-none");
    message.classList.remove("alert-danger");
    message.classList.add("alert-success");
    message.textContent = "";
  });
});
