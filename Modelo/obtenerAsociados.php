<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

// La lista de asociados (nombres y documentos) solo la ven usuarios con sesión
if (empty($_SESSION['rol'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensaje' => 'Debes iniciar sesión'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/../Config/conexion.php';
    $conexion = (new Conexion())->conectar();
    $consulta = $conexion->query(
        "SELECT documento, CONCAT_WS(' ', nombres, apellidos) AS nombre
         FROM asociados
         WHERE estado = 'activo'
         ORDER BY apellidos, nombres"
    );

    echo json_encode(
        ['success' => true, 'asociados' => $consulta->fetchAll()],
        JSON_UNESCAPED_UNICODE
    );
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(
        ['success' => false, 'mensaje' => 'No fue posible cargar los asociados'],
        JSON_UNESCAPED_UNICODE
    );
}
