(() => {
  const tabla = document.getElementById("tablaActividades");
  const contenedorTabla = document.getElementById("contenedorTabla");
  const cargando = document.getElementById("cargandoActividades");
  const sinActividades = document.getElementById("sinActividades");
  const mensaje = document.getElementById("mensajeActividades");
  const buscador = document.getElementById("buscarActividad");
  const modal = new bootstrap.Modal(document.getElementById("modalActividad"));

  let actividades = [];

  const ahora = new Date();
  const hoy = [
    ahora.getFullYear(),
    String(ahora.getMonth() + 1).padStart(2, "0"),
    String(ahora.getDate()).padStart(2, "0"),
  ].join("-");
  const esProxima = (actividad) => actividad.fecha >= hoy;

  const formatoFecha = new Intl.DateTimeFormat("es-CO", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    timeZone: "UTC",
  });
  const mostrarFecha = (fecha) => formatoFecha.format(new Date(`${fecha}T00:00:00Z`));

  const celda = (texto, clase = "") => {
    const td = document.createElement("td");
    td.textContent = texto;
    if (clase) td.className = clase;
    return td;
  };

  const abrirDetalle = (actividad) => {
    document.getElementById("tituloModalActividad").textContent = actividad.nombre;
    document.getElementById("descripcionModal").textContent = actividad.descripcion;
    document.getElementById("fechaModal").textContent = mostrarFecha(actividad.fecha);
    document.getElementById("horaModal").textContent = actividad.hora;
    document.getElementById("lugarModal").textContent = actividad.lugar;
    document.getElementById("responsableModal").textContent = actividad.responsable;
    modal.show();
  };

  const render = () => {
    const texto = buscador.value.trim().toLowerCase();
    const estado = document.querySelector('input[name="filtroEstado"]:checked').value;

    const filtradas = actividades.filter((actividad) => {
      const coincideTexto = [actividad.nombre, actividad.lugar, actividad.responsable]
        .some((valor) => valor.toLowerCase().includes(texto));
      const coincideEstado =
        estado === "todas" ||
        (estado === "proxima" && esProxima(actividad)) ||
        (estado === "realizada" && !esProxima(actividad));
      return coincideTexto && coincideEstado;
    });

    tabla.replaceChildren();
    filtradas.forEach((actividad) => {
      const fila = document.createElement("tr");
      fila.append(
        celda(actividad.nombre, "fw-semibold"),
        celda(mostrarFecha(actividad.fecha), "text-nowrap"),
        celda(actividad.hora),
        celda(actividad.lugar),
        celda(actividad.responsable)
      );

      const tdEstado = document.createElement("td");
      const badge = document.createElement("span");
      const proxima = esProxima(actividad);
      badge.className = `badge rounded-pill ${proxima ? "text-bg-success" : "text-bg-secondary"}`;
      badge.textContent = proxima ? "Próxima" : "Realizada";
      tdEstado.append(badge);

      const tdAccion = document.createElement("td");
      tdAccion.className = "text-end";
      const boton = document.createElement("button");
      boton.type = "button";
      boton.className = "btn btn-sm btn-outline-primary";
      boton.innerHTML = '<i class="bi bi-eye"></i><span class="visually-hidden">Ver detalle</span>';
      boton.addEventListener("click", () => abrirDetalle(actividad));
      tdAccion.append(boton);

      fila.append(tdEstado, tdAccion);
      tabla.append(fila);
    });

    contenedorTabla.classList.toggle("d-none", filtradas.length === 0);
    sinActividades.classList.toggle("d-none", filtradas.length !== 0);
  };

  const actualizarResumen = () => {
    const proximas = actividades.filter(esProxima).length;
    document.getElementById("totalActividades").textContent = actividades.length;
    document.getElementById("totalProximas").textContent = proximas;
    document.getElementById("totalRealizadas").textContent = actividades.length - proximas;
  };

  fetch("../Modelo/obtenerActividades.php")
    .then((respuesta) => respuesta.json())
    .then((resultado) => {
      if (!resultado.success) throw new Error(resultado.mensaje);
      actividades = resultado.actividades;
      actualizarResumen();
      render();
    })
    .catch((error) => {
      mensaje.textContent = error.message || "No se pudieron cargar las actividades.";
      mensaje.classList.remove("d-none");
    })
    .finally(() => cargando.classList.add("d-none"));

  buscador.addEventListener("input", render);
  document.querySelectorAll('input[name="filtroEstado"]').forEach((radio) =>
    radio.addEventListener("change", render)
  );
})();
