<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/../Config/conexion.php';
    $conexion = (new Conexion())->conectar();
    $consulta = $conexion->query(
        "SELECT id, nombre, descripcion, fecha, TIME_FORMAT(hora, '%H:%i') AS hora, lugar, responsable
         FROM actividades
         ORDER BY fecha DESC, hora DESC"
    );

    echo json_encode(
        ['success' => true, 'actividades' => $consulta->fetchAll()],
        JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(
        ['success' => false, 'mensaje' => 'No fue posible cargar las actividades'],
        JSON_UNESCAPED_UNICODE
    );
}
