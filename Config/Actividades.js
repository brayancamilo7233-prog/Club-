const listaActividades = document.getElementById("listaActividades");

// Elementos de la vista Vista/listaActividades.html
const contenedorTabla = document.getElementById("contenedorTabla");
const cargando = document.getElementById("cargandoActividades");
const mensaje = document.getElementById("mensajeActividades");
const buscador = document.getElementById("buscarActividad");
const botonNueva = document.getElementById("nuevaActividad");
const formulario = document.getElementById("formActividad");
const modalDetalle = new bootstrap.Modal(document.getElementById("modalActividad"));
const modalFormulario = new bootstrap.Modal(document.getElementById("modalFormActividad"));

let esAdministrador = false;
let actividades = [];

async function solicitarApi(metodo, datos) {
  const opciones = {
    method: metodo,
    headers: { "Content-Type": "application/json" },
  };

  if (datos) {
    opciones.body = JSON.stringify(datos);
  }

  const respuesta = await fetch("../Modelo/gestionarActividades.php", opciones);
  const resultado = await respuesta.json().catch(() => ({}));

  if (respuesta.status === 401) {
    window.location.href = "login.html";
    // Detiene el resto del código mientras el navegador cambia de página
    return new Promise(() => {});
  }

  if (!respuesta.ok || !resultado.success) {
    throw new Error(resultado.mensaje || "Error en la solicitud");
  }

  return resultado;
}

async function cargarActividades() {
  try {
    const resultado = await solicitarApi("GET");

    esAdministrador = resultado.rol === "admin";
    configurarAcciones();

    actividades = resultado.actividades;
    actualizarResumen();
    mostrarActividades();
  } catch (error) {
    // Igual que el alert de clase, pero el aviso se muestra dentro de la página
    mostrarMensaje("Error al cargar las actividades: " + error.message, false);
  } finally {
    cargando.classList.add("d-none");
  }
}

// Muestra u oculta lo que solo puede usar el administrador
function configurarAcciones() {
  botonNueva.classList.toggle("d-none", !esAdministrador);
}

// ---------- Utilidades ----------

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

// Igual que en clase: si no hay valor muestra "Pendiente"; si hay, lo muestra en pesos colombianos
const mostrarPesos = (valor) =>
  valor === null || valor === undefined
    ? "Pendiente"
    : Number(valor).toLocaleString("es-CO", {
        style: "currency",
        currency: "COP",
        maximumFractionDigits: 0,
      });

function mostrarMensaje(texto, exito) {
  mensaje.textContent = texto;
  mensaje.classList.toggle("alert-success", exito);
  mensaje.classList.toggle("alert-danger", !exito);
  mensaje.classList.remove("d-none");
}

// ---------- Tabla y resumen ----------

function actualizarResumen() {
  const proximas = actividades.filter(esProxima).length;
  document.getElementById("totalActividades").textContent = actividades.length;
  document.getElementById("totalProximas").textContent = proximas;
  document.getElementById("totalRealizadas").textContent = actividades.length - proximas;
}

// Devuelve solo las actividades que coinciden con el buscador y el filtro de estado
function filtrarActividades() {
  const texto = buscador.value.trim().toLowerCase();
  const estado = document.querySelector('input[name="filtroEstado"]:checked').value;

  return actividades.filter((actividad) => {
    const coincideTexto = [actividad.nombre, actividad.lugar, actividad.responsable]
      .some((valor) => String(valor ?? "").toLowerCase().includes(texto));
    const coincideEstado =
      estado === "todas" ||
      (estado === "proxima" && esProxima(actividad)) ||
      (estado === "realizada" && !esProxima(actividad));
    return coincideTexto && coincideEstado;
  });
}

function mostrarActividades() {
  listaActividades.replaceChildren();
  contenedorTabla.classList.remove("d-none");

  const actividadesFiltradas = filtrarActividades();

  if (actividadesFiltradas.length === 0) {
    const fila = document.createElement("tr");
    const celda = document.createElement("td");
    // En clase es esAdministrador ? 5 : 4 porque allá la columna Acciones se oculta.
    // Aquí Acciones siempre se ve (todos tienen "Ver detalle"), así que se cuentan las columnas
    celda.colSpan = document.querySelectorAll("#contenedorTabla thead th").length;
    celda.className = "text-center py-4 text-muted";
    celda.textContent =
      actividades.length === 0
        ? "No hay actividades disponibles."
        : "No hay actividades que coincidan con la búsqueda.";
    fila.append(celda);
    listaActividades.append(fila);
    return;
  }

  actividadesFiltradas.forEach((actividad) => {
    const fila = document.createElement("tr");
    // En clase: nombreActividad, fechaEvento y municipio. Aquí se usan los nombres
    // de las columnas de esta base de datos: nombre, fecha y lugar
    fila.append(
      crearCelda(actividad.nombre, "fw-semibold"),
      crearCelda(mostrarFecha(actividad.fecha), "text-nowrap"),
      crearCelda(actividad.hora),
      crearCelda(actividad.lugar),
      crearCelda(actividad.responsable),
      crearCelda(mostrarPesos(actividad.totalRecaudado), "text-nowrap")
    );

    const tdEstado = document.createElement("td");
    const badge = document.createElement("span");
    const proxima = esProxima(actividad);
    badge.className = `badge rounded-pill ${proxima ? "text-bg-success" : "text-bg-secondary"}`;
    badge.textContent = proxima ? "Próxima" : "Realizada";
    tdEstado.append(badge);

    // La celda de acciones siempre existe porque todos pueden ver el detalle
    const acciones = document.createElement("td");
    acciones.className = "text-end text-nowrap";

    const botonDetalle = document.createElement("button");
    botonDetalle.type = "button";
    botonDetalle.className = "btn btn-sm btn-outline-primary";
    botonDetalle.title = "Ver detalle";
    botonDetalle.innerHTML = '<i class="bi bi-eye"></i>';
    botonDetalle.addEventListener("click", () => abrirDetalle(actividad));
    acciones.append(botonDetalle);

    // Editar y eliminar solo para el administrador
    if (esAdministrador) {
      const botonEditar = document.createElement("button");
      botonEditar.type = "button";
      botonEditar.className = "btn btn-sm btn-outline-secondary ms-1";
      botonEditar.title = "Editar";
      botonEditar.innerHTML = '<i class="bi bi-pencil"></i>';
      botonEditar.addEventListener("click", () => abrirFormulario(actividad));

      const botonEliminar = document.createElement("button");
      botonEliminar.type = "button";
      botonEliminar.className = "btn btn-sm btn-outline-danger ms-1";
      botonEliminar.title = "Eliminar";
      botonEliminar.innerHTML = '<i class="bi bi-trash"></i>';
      botonEliminar.addEventListener("click", () => eliminarActividad(actividad));

      acciones.append(botonEditar, botonEliminar);
    }

    fila.append(tdEstado, acciones);
    listaActividades.append(fila);
  });
}

function crearCelda(texto, clase = "") {
  const celda = document.createElement("td");
  celda.textContent = texto;
  // "clase" es opcional y solo da el diseño (negrita, text-nowrap)
  if (clase) celda.className = clase;
  return celda;
}

// ---------- Detalle ----------

function abrirDetalle(actividad) {
  document.getElementById("tituloModalActividad").textContent = actividad.nombre;
  document.getElementById("descripcionModal").textContent = actividad.descripcion;
  document.getElementById("fechaModal").textContent = mostrarFecha(actividad.fecha);
  document.getElementById("horaModal").textContent = actividad.hora;
  document.getElementById("lugarModal").textContent = actividad.lugar;
  document.getElementById("responsableModal").textContent = actividad.responsable;
  document.getElementById("recaudadoModal").textContent = mostrarPesos(actividad.totalRecaudado);
  modalDetalle.show();
}

// ---------- Crear / editar / eliminar (solo admin) ----------

function abrirFormulario(actividad) {
  formulario.reset();
  formulario.classList.remove("was-validated");
  const campos = formulario.elements;

  if (actividad) {
    document.getElementById("tituloFormActividad").textContent = "Editar actividad";
    campos.idActividad.value = actividad.id;
    campos.nombreActividad.value = actividad.nombre;
    campos.descripcion.value = actividad.descripcion;
    campos.fecha.value = actividad.fecha;
    campos.hora.value = actividad.hora;
    campos.lugar.value = actividad.lugar;
    campos.responsable.value = actividad.responsable;
    campos.totalRecaudado.value = actividad.totalRecaudado;
  } else {
    document.getElementById("tituloFormActividad").textContent = "Nueva actividad";
    campos.idActividad.value = "";
  }

  modalFormulario.show();
}

async function guardarActividad(event) {
  event.preventDefault();
  formulario.classList.add("was-validated");
  if (!formulario.checkValidity()) return;

  const datos = Object.fromEntries(new FormData(formulario).entries());
  const editando = datos.idActividad !== "";
  if (!editando) delete datos.idActividad;

  const botonGuardar = formulario.querySelector('[type="submit"]');
  botonGuardar.disabled = true;
  try {
    const resultado = await solicitarApi(editando ? "PUT" : "POST", datos);
    modalFormulario.hide();
    mostrarMensaje(resultado.mensaje, true);
    await cargarActividades();
  } catch (error) {
    modalFormulario.hide();
    mostrarMensaje(error.message, false);
  } finally {
    botonGuardar.disabled = false;
  }
}

async function eliminarActividad(actividad) {
  if (!confirm(`¿Eliminar la actividad "${actividad.nombre}"? Esta acción no se puede deshacer.`)) {
    return;
  }
  try {
    const resultado = await solicitarApi("DELETE", { idActividad: actividad.id });
    mostrarMensaje(resultado.mensaje, true);
    await cargarActividades();
  } catch (error) {
    mostrarMensaje(error.message, false);
  }
}

// ---------- Eventos ----------

buscador.addEventListener("input", mostrarActividades);
document.querySelectorAll('input[name="filtroEstado"]').forEach((radio) =>
  radio.addEventListener("change", mostrarActividades)
);
botonNueva.addEventListener("click", () => abrirFormulario(null));
formulario.addEventListener("submit", guardarActividad);

cargarActividades();
