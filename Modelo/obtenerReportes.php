<?php
// API de reportes usada por Config/Reportes.js (solo admin)
//   GET ?tipo=asociados|actividades|pagos|prestamos[&desde=AAAA-MM-DD][&hasta=AAAA-MM-DD]

session_start();

header('Content-Type: application/json; charset=utf-8');

// Lee una fecha opcional de la URL: devuelve null si no se envió
function leerFecha($campo)
{
    $valor = $_GET[$campo] ?? '';
    if ($valor === '') {
        return null;
    }

    $fecha = is_string($valor) ? DateTime::createFromFormat('!Y-m-d', $valor) : false;
    if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
        http_response_code(400);
        throw new Exception("La fecha '$campo' no es válida");
    }

    return $valor;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        throw new Exception('Método no permitido');
    }

    if (!isset($_SESSION['documento'], $_SESSION['rol'])) {
        http_response_code(401);
        throw new Exception('Inicia sesión para consultar los reportes');
    }

    if ($_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        throw new Exception('Solo el administrador puede consultar los reportes');
    }

    // Consulta, columna de fecha para filtrar y orden de cada reporte
    $reportes = [
        'asociados' => [
            'sql' => "SELECT documento, CONCAT_WS(' ', nombres, apellidos) AS nombre, email, telefono, ciudad, fechaIngreso, estado, rol
                      FROM asociados",
            'fecha' => 'fechaIngreso',
            'orden' => 'apellidos, nombres',
        ],
        'actividades' => [
            'sql' => "SELECT nombre, fecha, TIME_FORMAT(hora, '%H:%i') AS hora, lugar, responsable, totalRecaudado
                      FROM actividades",
            'fecha' => 'fecha',
            'orden' => 'fecha DESC, hora DESC',
        ],
        'pagos' => [
            'sql' => "SELECT p.numeroRecibo, CONCAT_WS(' ', a.nombres, a.apellidos) AS asociado, p.asociadoDocumento,
                             p.concepto, p.fechaPago, p.metodoPago, p.valor
                      FROM pagos p
                      LEFT JOIN asociados a ON a.documento = p.asociadoDocumento",
            'fecha' => 'p.fechaPago',
            'orden' => 'p.fechaPago DESC, p.numeroRecibo',
        ],
        'prestamos' => [
            'sql' => "SELECT p.codigoPrestamo, CONCAT_WS(' ', a.nombres, a.apellidos) AS asociado, p.asociadoDocumento,
                             p.fechaSolicitud, p.plazoMeses, p.observaciones, p.monto
                      FROM prestamos p
                      LEFT JOIN asociados a ON a.documento = p.asociadoDocumento",
            'fecha' => 'p.fechaSolicitud',
            'orden' => 'p.fechaSolicitud DESC, p.codigoPrestamo',
        ],
    ];

    $tipo = $_GET['tipo'] ?? '';
    if (!is_string($tipo) || !isset($reportes[$tipo])) {
        http_response_code(400);
        throw new Exception('El tipo de reporte no es válido');
    }

    $desde = leerFecha('desde');
    $hasta = leerFecha('hasta');
    if ($desde !== null && $hasta !== null && $desde > $hasta) {
        http_response_code(400);
        throw new Exception('La fecha inicial no puede ser mayor que la fecha final');
    }

    $reporte = $reportes[$tipo];
    $condiciones = [];
    $parametros = [];
    if ($desde !== null) {
        $condiciones[] = $reporte['fecha'] . ' >= :desde';
        $parametros[':desde'] = $desde;
    }
    if ($hasta !== null) {
        $condiciones[] = $reporte['fecha'] . ' <= :hasta';
        $parametros[':hasta'] = $hasta;
    }

    $sql = $reporte['sql']
        . ($condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '')
        . ' ORDER BY ' . $reporte['orden'];

    require_once __DIR__ . '/../Config/conexion.php';
    $conexion = (new Conexion())->conectar();

    $consulta = $conexion->prepare($sql);
    $consulta->execute($parametros);

    echo json_encode(
        ['success' => true, 'filas' => $consulta->fetchAll()],
        JSON_UNESCAPED_UNICODE
    );
} catch (PDOException $e) {
    // El detalle técnico va al registro de PHP, no al usuario
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible consultar los reportes'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
