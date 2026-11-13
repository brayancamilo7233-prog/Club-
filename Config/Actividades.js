const listaActividades = document.getElementById("listaActividades");

// Elementos de la vista Vista/listaActividades.html
const contenedorTabla = document.getElementById("contenedorTabla");
const cargando = document.getElementById("cargandoActividades");
const sinActividades = document.getElementById("sinActividades");
const mensaje = document.getElementById("mensajeActividades");
const buscador = document.getElementById("buscarActividad");
const botonNueva = document.getElementById("nuevaActividad");
const formulario = document.getElementById("formActividad");
const modalDetalle = new bootstrap.Modal(document.getElementById("modalActividad"));
const modalFormulario = new bootstrap.Modal(document.getElementById("modalFormActividad"));

let actividades = [];
let esAdmin = false;

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
    actividades = resultado.actividades;
    esAdmin = resultado.rol === "admin";

    botonNueva.classList.toggle("d-none", !esAdmin);
    actualizarResumen();
    mostrarActividades();
  } catch (error) {
    mostrarMensaje(error.message || "No se pudieron cargar las actividades.", false);
  } finally {
    cargando.classList.add("d-none");
  }
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

const formatoPesos = new Intl.NumberFormat("es-CO", {
  style: "currency",
  currency: "COP",
  maximumFractionDigits: 0,
});
const mostrarPesos = (valor) => formatoPesos.format(Number(valor) || 0);

function mostrarMensaje(texto, exito) {
  mensaje.textContent = texto;
  mensaje.classList.toggle("alert-success", exito);
  mensaje.classList.toggle("alert-danger", !exito);
  mensaje.classList.remove("d-none");
}

function celda(texto, clase = "") {
  const td = document.createElement("td");
  td.textContent = texto;
  if (clase) td.className = clase;
  return td;
}

function botonIcono(icono, clase, etiqueta, accion) {
  const boton = document.createElement("button");
  boton.type = "button";
  boton.className = `btn btn-sm ${clase}`;
  boton.title = etiqueta;
  boton.innerHTML = `<i class="bi ${icono}"></i><span class="visually-hidden">${etiqueta}</span>`;
  boton.addEventListener("click", accion);
  return boton;
}

// ---------- Tabla y resumen ----------

function actualizarResumen() {
  const proximas = actividades.filter(esProxima).length;
  document.getElementById("totalActividades").textContent = actividades.length;
  document.getElementById("totalProximas").textContent = proximas;
  document.getElementById("totalRealizadas").textContent = actividades.length - proximas;
}

function mostrarActividades() {
  const texto = buscador.value.trim().toLowerCase();
  const estado = document.querySelector('input[name="filtroEstado"]:checked').value;

  const filtradas = actividades.filter((actividad) => {
    const coincideTexto = [actividad.nombre, actividad.lugar, actividad.responsable]
      .some((valor) => String(valor ?? "").toLowerCase().includes(texto));
    const coincideEstado =
      estado === "todas" ||
      (estado === "proxima" && esProxima(actividad)) ||
      (estado === "realizada" && !esProxima(actividad));
    return coincideTexto && coincideEstado;
  });

  listaActividades.replaceChildren();
  filtradas.forEach((actividad) => {
    const fila = document.createElement("tr");
    fila.append(
      celda(actividad.nombre, "fw-semibold"),
      celda(mostrarFecha(actividad.fecha), "text-nowrap"),
      celda(actividad.hora),
      celda(actividad.lugar),
      celda(actividad.responsable),
      celda(mostrarPesos(actividad.totalRecaudado), "text-nowrap")
    );

    const tdEstado = document.createElement("td");
    const badge = document.createElement("span");
    const proxima = esProxima(actividad);
    badge.className = `badge rounded-pill ${proxima ? "text-bg-success" : "text-bg-secondary"}`;
    badge.textContent = proxima ? "Próxima" : "Realizada";
    tdEstado.append(badge);

    // Todos pueden ver el detalle; editar y eliminar solo el administrador
    const tdAcciones = document.createElement("td");
    tdAcciones.className = "text-end text-nowrap";
    tdAcciones.append(
      botonIcono("bi-eye", "btn-outline-primary", "Ver detalle", () => abrirDetalle(actividad))
    );
    if (esAdmin) {
      tdAcciones.append(
        botonIcono("bi-pencil", "btn-outline-secondary ms-1", "Editar", () => abrirFormulario(actividad)),
        botonIcono("bi-trash", "btn-outline-danger ms-1", "Eliminar", () => eliminarActividad(actividad))
      );
    }

    fila.append(tdEstado, tdAcciones);
    listaActividades.append(fila);
  });

  contenedorTabla.classList.toggle("d-none", filtradas.length === 0);
  sinActividades.classList.toggle("d-none", filtradas.length !== 0);
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
