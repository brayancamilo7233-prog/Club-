const formulario = document.getElementById("formReportes");
const botonGuardar = document.getElementById("guardarReporte");
const mensaje = document.getElementById("mensajeReportes");

// Columnas del archivo de cada reporte: [campo que envía el PHP, título, formato]
const reportes = {
  asociados: {
    columnas: [
      ["documento", "Documento"],
      ["nombre", "Nombre"],
      ["email", "Correo"],
      ["telefono", "Teléfono"],
      ["ciudad", "Ciudad"],
      ["fechaIngreso", "Ingreso"],
      ["estado", "Estado"],
      ["rol", "Rol"],
    ],
  },
  actividades: {
    columnas: [
      ["nombre", "Actividad"],
      ["fecha", "Fecha"],
      ["hora", "Hora"],
      ["lugar", "Lugar"],
      ["responsable", "Responsable"],
      ["totalRecaudado", "Total recaudado", "pesos"],
    ],
  },
  pagos: {
    columnas: [
      ["numeroRecibo", "Recibo"],
      ["asociado", "Asociado"],
      ["asociadoDocumento", "Documento"],
      ["concepto", "Concepto"],
      ["fechaPago", "Fecha"],
      ["metodoPago", "Método"],
      ["valor", "Valor", "pesos"],
    ],
  },
  prestamos: {
    columnas: [
      ["codigoPrestamo", "Código"],
      ["asociado", "Asociado"],
      ["asociadoDocumento", "Documento"],
      ["fechaSolicitud", "Solicitud"],
      ["plazoMeses", "Plazo (meses)"],
      ["observaciones", "Observaciones"],
      ["monto", "Monto", "pesos"],
    ],
  },
};

let filas = [];
let tipoActual = "asociados";
let numeroConsulta = 0;

async function cargarReporte() {
  // Si se cambian los filtros rápido, solo vale la respuesta de la última consulta
  const consulta = ++numeroConsulta;
  const parametros = new URLSearchParams(new FormData(formulario));
  botonGuardar.disabled = true;

  try {
    const respuesta = await fetch(`../Modelo/obtenerReportes.php?${parametros}`);
    const resultado = await respuesta.json().catch(() => ({}));
    if (consulta !== numeroConsulta) return;

    if (respuesta.status === 401) {
      window.location.href = "login.html";
      return;
    }
    if (!respuesta.ok || !resultado.success) {
      throw new Error(resultado.mensaje || "No fue posible cargar el reporte.");
    }

    tipoActual = parametros.get("tipo");
    filas = resultado.filas;
    mostrarMensaje(filas.length === 0 ? "No hay registros para este reporte." : "");
  } catch (error) {
    if (consulta !== numeroConsulta) return;
    // Sin filas no se puede guardar un reporte viejo por error
    filas = [];
    mostrarMensaje(error.message);
  }

  botonGuardar.disabled = filas.length === 0;
}

// Sin texto, el aviso se oculta
function mostrarMensaje(texto) {
  mensaje.textContent = texto;
  mensaje.classList.toggle("d-none", texto === "");
}

// ---------- Guardar (archivo CSV que abre Excel) ----------

function celdaCsv(valor) {
  let texto = String(valor ?? "");
  // Evita que Excel ejecute como fórmula un texto escrito por un usuario
  if (/^[=+\-@\t\r]/.test(texto)) texto = `'${texto}`;
  return `"${texto.replaceAll('"', '""')}"`;
}

function crearCsv() {
  const { columnas } = reportes[tipoActual];
  const lineas = [
    columnas.map(([, titulo]) => celdaCsv(titulo)),
    ...filas.map((registro) =>
      columnas.map(([campo, , formato]) =>
        // Excel en español usa coma para los decimales
        celdaCsv(formato === "pesos" ? String(registro[campo]).replace(".", ",") : registro[campo])
      )
    ),
  ];
  return lineas.map((linea) => linea.join(";")).join("\r\n");
}

function guardarReporte(event) {
  event.preventDefault();
  if (filas.length === 0) return;

  // ﻿ hace que Excel reconozca las tildes
  const archivo = new Blob(["﻿" + crearCsv()], { type: "text/csv;charset=utf-8" });
  const enlace = document.createElement("a");
  enlace.href = URL.createObjectURL(archivo);
  enlace.download = `reporte_${tipoActual}.csv`;
  enlace.click();
  setTimeout(() => URL.revokeObjectURL(enlace.href), 1000);
}

// ---------- Eventos ----------

formulario.addEventListener("change", cargarReporte);
formulario.addEventListener("submit", guardarReporte);
// "reset" se dispara antes de que los campos se vacíen: se espera a que terminen
formulario.addEventListener("reset", () => setTimeout(cargarReporte));

cargarReporte();
