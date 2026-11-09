<?php

header('Content-Type: application/json; charset=utf-8');

function responder($estado, $success, $mensaje)
{
    http_response_code($estado);
    echo json_encode(
        ['success' => $success, 'mensaje' => $mensaje],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, false, 'Método no permitido');
}

require_once __DIR__ . '/Asociado.php';

$cuerpo = file_get_contents('php://input');
$datos = json_decode($cuerpo, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
    responder(400, false, 'El cuerpo de la solicitud debe ser JSON válido');
}

$camposObligatorios = [
    'documento',
    'nombres',
    'apellidos',
    'fechaNacimiento',
    'genero',
    'direccionResidencia',
    'ciudad',
    'telefono',
    'email',
];

foreach ($camposObligatorios as $campo) {
    if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === '') {
        responder(400, false, 'Falta un campo obligatorio: ' . $campo);
    }
    $datos[$campo] = trim((string) $datos[$campo]);
}

if (filter_var($datos['email'], FILTER_VALIDATE_EMAIL) === false) {
    responder(400, false, 'El correo electrónico no es válido');
}

$crearCuenta = isset($_GET['crearCuenta']) && $_GET['crearCuenta'] === '1';
if ($crearCuenta && (!isset($datos['password']) || !is_string($datos['password']) || strlen($datos['password']) < 8)) {
    responder(400, false, 'La contraseña debe tener al menos 8 caracteres');
}
if (isset($datos['password']) && (!is_string($datos['password']) || strlen($datos['password']) < 8)) {
    responder(400, false, 'La contraseña debe tener al menos 8 caracteres');
}

if (!in_array($datos['genero'], ['Masculino', 'Femenino', 'otro'], true)) {
    responder(400, false, 'El género seleccionado no es válido');
}

if (!in_array($datos['estado'], ['activo', 'inactivo'], true)) {
    responder(400, false, 'El estado seleccionado no es válido');
}

foreach (['fechaNacimiento', 'fechaIngreso'] as $campoFecha) {
    if ($campoFecha === 'fechaIngreso' && empty($datos[$campoFecha])) {
        $datos[$campoFecha] = date('Y-m-d');
    }
    $fecha = DateTime::createFromFormat('!Y-m-d', $datos[$campoFecha]);
    if (!$fecha || $fecha->format('Y-m-d') !== $datos[$campoFecha]) {
        responder(400, false, 'La fecha indicada no es válida');
    }
}

$datos['estado'] = isset($datos['estado']) ? trim((string) $datos['estado']) : 'activo';
if (!in_array($datos['estado'], ['activo', 'inactivo'], true)) {
    responder(400, false, 'El estado seleccionado no es válido');
}

try {
    $asociado = new Asociado($datos);
    $asociado->guardar();
    responder(201, true, 'Asociado guardado exitosamente');
} catch (PDOException $error) {
    error_log($error->getMessage());

    if ($error->getCode() === '23000') {
        responder(409, false, 'Ya existe un asociado con ese documento o correo electrónico');
    }

    responder(500, false, 'No fue posible guardar el asociado. Verifica la conexión con la base de datos.');
} catch (Throwable $error) {
    error_log($error->getMessage());
    responder(500, false, 'Ocurrió un error interno al guardar el asociado');
}
