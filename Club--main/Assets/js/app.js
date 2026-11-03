document.querySelectorAll(".club-form").forEach((form) => {
  const message = form.querySelector('[role="status"]');

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    form.classList.add("was-validated");

    if (!form.checkValidity()) {
      form.reportValidity();
      message.classList.add("d-none");
      return;
    }

    message.textContent =
      "Formulario validado correctamente. Esta demostración no almacena datos.";
    message.classList.remove("d-none");
  });

  form.addEventListener("reset", () => {
    form.classList.remove("was-validated");
    message.classList.add("d-none");
    message.textContent = "";
  });
});
