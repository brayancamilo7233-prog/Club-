<?php

header('Content-Type: application/json; charset=utf-8');

function responderRegistro($estado, $success, $mensaje)
{
    http_response_code($estado);
    echo json_encode(
        ['success' => $success, 'mensaje' => $mensaje],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function campoRegistro($datos, $campo)
{
    if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === '') {
        responderRegistro(400, false, 'Falta un campo obligatorio: ' . $campo);
    }

    return trim((string) $datos[$campo]);
}

function fechaRegistroValida($valor)
{
    $fecha = DateTime::createFromFormat('!Y-m-d', $valor);
    return $fecha && $fecha->format('Y-m-d') === $valor;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderRegistro(405, false, 'Método no permitido');
}

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
if (!in_array($tipo, ['actividad', 'pago', 'prestamo'], true)) {
    responderRegistro(400, false, 'Tipo de registro no válido');
}

$datos = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
    responderRegistro(400, false, 'El cuerpo de la solicitud debe ser JSON válido');
}

try {
    require_once __DIR__ . '/../Config/conexion.php';
    $conexion = (new Conexion())->conectar();

    if ($tipo === 'actividad') {
        $nombre = campoRegistro($datos, 'nombreActividad');
        $descripcion = campoRegistro($datos, 'descripcion');
        $fecha = campoRegistro($datos, 'fecha');
        $hora = campoRegistro($datos, 'hora');
        $lugar = campoRegistro($datos, 'lugar');
        $responsable = campoRegistro($datos, 'responsable');

        if (!fechaRegistroValida($fecha)) {
            responderRegistro(400, false, 'La fecha de la actividad no es válida');
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $hora)) {
            responderRegistro(400, false, 'La hora de la actividad no es válida');
        }

        $consulta = $conexion->prepare(
            'INSERT INTO actividades (nombre, descripcion, fecha, hora, lugar, responsable)
             VALUES (:nombre, :descripcion, :fecha, :hora, :lugar, :responsable)'
        );
        $consulta->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':lugar' => $lugar,
            ':responsable' => $responsable,
        ]);
        responderRegistro(201, true, 'Actividad guardada correctamente');
    }

    if ($tipo === 'pago') {
        $numeroRecibo = campoRegistro($datos, 'numeroRecibo');
        $asociado = campoRegistro($datos, 'asociado');
        $concepto = campoRegistro($datos, 'concepto');
        $valor = campoRegistro($datos, 'valor');
        $fechaPago = campoRegistro($datos, 'fechaPago');
        $metodoPago = campoRegistro($datos, 'metodoPago');

        if (!is_numeric($valor) || (float) $valor <= 0) {
            responderRegistro(400, false, 'El valor del pago debe ser mayor que cero');
        }
        if (!fechaRegistroValida($fechaPago)) {
            responderRegistro(400, false, 'La fecha del pago no es válida');
        }
        if (!in_array($metodoPago, ['efectivo', 'transferencia', 'tarjeta'], true)) {
            responderRegistro(400, false, 'El método de pago no es válido');
        }

        $consulta = $conexion->prepare(
            'INSERT INTO pagos (numeroRecibo, asociadoDocumento, concepto, valor, fechaPago, metodoPago)
             VALUES (:numeroRecibo, :asociado, :concepto, :valor, :fechaPago, :metodoPago)'
        );
        $consulta->execute([
            ':numeroRecibo' => $numeroRecibo,
            ':asociado' => $asociado,
            ':concepto' => $concepto,
            ':valor' => $valor,
            ':fechaPago' => $fechaPago,
            ':metodoPago' => $metodoPago,
        ]);
        responderRegistro(201, true, 'Pago guardado correctamente');
    }

    $codigoPrestamo = campoRegistro($datos, 'codigoPrestamo');
    $asociado = campoRegistro($datos, 'asociado');
    $monto = campoRegistro($datos, 'monto');
    $fechaSolicitud = campoRegistro($datos, 'fechaSolicitud');
    $plazo = campoRegistro($datos, 'plazo');
    $observaciones = isset($datos['observaciones']) ? trim((string) $datos['observaciones']) : '';

    if (!is_numeric($monto) || (float) $monto <= 0) {
        responderRegistro(400, false, 'El monto solicitado debe ser mayor que cero');
    }
    if (!fechaRegistroValida($fechaSolicitud)) {
        responderRegistro(400, false, 'La fecha de solicitud no es válida');
    }
    if (filter_var($plazo, FILTER_VALIDATE_INT) === false || (int) $plazo < 1 || (int) $plazo > 65535) {
        responderRegistro(400, false, 'El plazo debe ser un número entero de meses mayor que cero');
    }

    $consulta = $conexion->prepare(
        'INSERT INTO prestamos (codigoPrestamo, asociadoDocumento, monto, fechaSolicitud, plazoMeses, observaciones)
         VALUES (:codigoPrestamo, :asociado, :monto, :fechaSolicitud, :plazo, :observaciones)'
    );
    $consulta->execute([
        ':codigoPrestamo' => $codigoPrestamo,
        ':asociado' => $asociado,
        ':monto' => $monto,
        ':fechaSolicitud' => $fechaSolicitud,
        ':plazo' => $plazo,
        ':observaciones' => $observaciones === '' ? null : $observaciones,
    ]);
    responderRegistro(201, true, 'Préstamo guardado correctamente');
} catch (PDOException $error) {
    error_log($error->getMessage());

    if ($error->getCode() === '23000') {
        responderRegistro(409, false, 'El código o recibo ya existe, o el asociado seleccionado no es válido');
    }

    responderRegistro(500, false, 'No fue posible guardar los datos. Verifica la conexión y las tablas de la base de datos.');
} catch (Throwable $error) {
    error_log($error->getMessage());
    responderRegistro(500, false, 'Ocurrió un error interno al guardar los datos');
}
