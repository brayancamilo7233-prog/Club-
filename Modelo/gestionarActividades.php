<?php

// API de actividades usada por Config/Actividades.js
//   GET    -> lista las actividades (cualquier usuario con sesión)
//   POST   -> crea una actividad      (solo admin)
//   PUT    -> edita una actividad     (solo admin, requiere "idActividad")
//   DELETE -> elimina una actividad   (solo admin, requiere "idActividad")

session_start();

header('Content-Type: application/json; charset=utf-8');

function responder($estado, $datos)
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function error($estado, $mensaje)
{
    responder($estado, ['success' => false, 'mensaje' => $mensaje]);
}

function fechaValida($valor)
{
    $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
    return $fecha && $fecha->format('Y-m-d') === $valor;
}

// Revisa los campos del formulario antes de guardar o editar
function validarActividad($datos)
{
    $obligatorios = [
        'nombreActividad' => 'el nombre',
        'descripcion' => 'la descripción',
        'fecha' => 'la fecha',
        'hora' => 'la hora',
        'lugar' => 'el lugar',
        'responsable' => 'el responsable',
    ];

    foreach ($obligatorios as $campo => $nombre) {
        if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === '') {
            error(400, "Falta $nombre de la actividad");
        }
    }

    if (!fechaValida($datos['fecha'])) {
        error(400, 'La fecha de la actividad no es válida');
    }
    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $datos['hora'])) {
        error(400, 'La hora de la actividad no es válida');
    }
    if (isset($datos['totalRecaudado']) && !is_scalar($datos['totalRecaudado'])) {
        error(400, 'El total recaudado no es válido');
    }
}

$metodo = $_SERVER['REQUEST_METHOD'];

if (!in_array($metodo, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    error(405, 'Método no permitido');
}

// Hay que iniciar sesión para usar el módulo
if (empty($_SESSION['rol'])) {
    error(401, 'Debes iniciar sesión');
}

$esAdmin = $_SESSION['rol'] === 'admin';

// Crear, editar y eliminar es solo para administradores
if ($metodo !== 'GET' && !$esAdmin) {
    error(403, 'Solo un administrador puede modificar actividades');
}

$datos = [];
if ($metodo !== 'GET') {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
        error(400, 'El cuerpo de la solicitud debe ser JSON válido');
    }
}

try {
    require_once __DIR__ . '/Actividad.php';
    $actividad = new Actividad();

    if ($metodo === 'GET') {
        responder(200, [
            'success' => true,
            'rol' => $_SESSION['rol'],
            'actividades' => $actividad->obtenerActividades(),
        ]);
    }

    if ($metodo === 'POST') {
        validarActividad($datos);
        if (!$actividad->guardar($datos)) {
            error(400, 'El total recaudado debe ser un número mayor o igual a 0');
        }
        responder(201, ['success' => true, 'mensaje' => 'Actividad guardada correctamente']);
    }

    if ($metodo === 'PUT') {
        validarActividad($datos);
        if (!$actividad->editar($datos)) {
            error(404, 'La actividad no existe o el total recaudado no es válido');
        }
        responder(200, ['success' => true, 'mensaje' => 'Actividad actualizada correctamente']);
    }

    // DELETE
    if (!$actividad->eliminar($datos['idActividad'] ?? null)) {
        error(404, 'La actividad no existe');
    }
    responder(200, ['success' => true, 'mensaje' => 'Actividad eliminada correctamente']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    error(500, 'No fue posible procesar la solicitud. Verifica la conexión con la base de datos.');
}
