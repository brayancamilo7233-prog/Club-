<?php
// API de actividades usada por Config/Actividades.js
//   GET    -> lista las actividades (cualquier usuario con sesión)
//   POST   -> crea una actividad      (solo admin)
//   PUT    -> edita una actividad     (solo admin, requiere "idActividad")
//   DELETE -> elimina una actividad   (solo admin, requiere "idActividad")

session_start();

header("Content-Type: application/json; charset=UTF-8");

// Lee el JSON que envía fetch y lo devuelve como arreglo
function leerDatos()
{
    $datos = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    // Un JSON válido como "123" o "null" no trae los campos: tiene que ser un objeto
    if (!is_array($datos)) {
        http_response_code(400);
        throw new Exception("Los datos enviados no tienen el formato esperado");
    }

    return $datos;
}

function fechaValida($valor)
{
    $fecha = DateTime::createFromFormat("!Y-m-d", $valor);
    return $fecha && $fecha->format("Y-m-d") === $valor;
}

// Revisa los campos del formulario y devuelve los datos si todo está bien
function validarActividad($datos)
{
    $obligatorios = [
        "nombreActividad" => "el nombre",
        "descripcion" => "la descripción",
        "fecha" => "la fecha",
        "hora" => "la hora",
        "lugar" => "el lugar",
        "responsable" => "el responsable",
    ];

    foreach ($obligatorios as $campo => $nombre) {
        if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === "") {
            http_response_code(400);
            throw new Exception("Falta $nombre de la actividad");
        }
    }

    if (!fechaValida($datos["fecha"])) {
        http_response_code(400);
        throw new Exception("La fecha de la actividad no es válida");
    }

    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $datos["hora"])) {
        http_response_code(400);
        throw new Exception("La hora de la actividad no es válida");
    }

    if (isset($datos["totalRecaudado"]) && !is_scalar($datos["totalRecaudado"])) {
        http_response_code(400);
        throw new Exception("El total recaudado no es válido");
    }

    return $datos;
}

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar las actividades");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];

    if (!in_array($metodo, ["GET", "POST", "PUT", "DELETE"], true)) {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    if ($metodo !== "GET" && $_SESSION["rol"] !== "admin") {
        http_response_code(403);
        throw new Exception("No tienes permisos para realizar esta acción");
    }

    require_once __DIR__ . "/Actividad.php";
    $actividad = new Actividad();

    switch ($metodo) {
        case "GET":
            $resultado = [
                "success" => true,
                "rol" => $_SESSION["rol"],
                "actividades" => $actividad->obtenerActividades(),
            ];
            http_response_code(200);
            break;

        case "POST":
            $datos = validarActividad(leerDatos());

            if (!$actividad->guardar($datos)) {
                http_response_code(400);
                throw new Exception("El total recaudado debe ser un número mayor o igual a 0");
            }

            // La profe usa "message"; aquí es "mensaje" porque Config/Actividades.js lee resultado.mensaje
            $resultado = [
                "success" => true,
                "mensaje" => "Actividad creada exitosamente",
            ];
            http_response_code(201);
            break;

        case "PUT":
            $datos = validarActividad(leerDatos());

            if (!$actividad->editar($datos)) {
                http_response_code(404);
                throw new Exception("La actividad no existe o el total recaudado no es válido");
            }

            $resultado = [
                "success" => true,
                "mensaje" => "Actividad actualizada exitosamente",
            ];
            http_response_code(200);
            break;

        case "DELETE":
            $datos = leerDatos();

            if (!$actividad->eliminar($datos["idActividad"] ?? null)) {
                http_response_code(404);
                throw new Exception("La actividad no existe");
            }

            $resultado = [
                "success" => true,
                "mensaje" => "Actividad eliminada exitosamente",
            ];
            http_response_code(200);
            break;
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Error de base de datos: el detalle va al registro de PHP, no al usuario
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "No fue posible procesar la solicitud. Verifica la conexión con la base de datos.",
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Si nadie puso un código de error, es un error interno
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode([
        "success" => false,
        "mensaje" => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // Cualquier otro fallo de PHP (por ejemplo un TypeError): respuesta JSON en vez de una página de error
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "Ocurrió un error interno al procesar la solicitud",
    ], JSON_UNESCAPED_UNICODE);
}
