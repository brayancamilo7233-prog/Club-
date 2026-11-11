<?php

session_start();

header("Content-Type: application/json; charset=utf-8");

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }

    $email = $datos['email'] ?? "";
    $password = $datos['password'] ?? "";

    if (!is_string($email) || !is_string($password) || $email === "" || $password === "") {
        http_response_code(400);
        throw new Exception("Email o contraseña inválidos");
    }

    require_once __DIR__ . '/../../Config/conexion.php';
    $conexion = (new Conexion())->conectar();

    $consulta = $conexion->prepare("SELECT * FROM asociados WHERE email = :email");
    $consulta->execute([':email' => $email]);
    $asociado = $consulta->fetch();

    if (!$asociado || empty($asociado['password_hash']) || !password_verify($password, $asociado['password_hash'])) {
        http_response_code(401);
        throw new Exception("Email o contraseña incorrectos");
    }

    // Nuevo ID de sesión al autenticarse (evita fijación de sesión)
    session_regenerate_id(true);

    // Solo existen dos roles: admin y asociado
    $_SESSION["rol"] = strtolower($asociado['rol'] ?? '') === 'admin' ? 'admin' : 'asociado';
    $_SESSION["documento"] = $asociado['documento'];

    echo json_encode([
        "success" => true,
        "rol" => $_SESSION["rol"],
        "nombre" => $asociado["nombres"] . " " . $asociado["apellidos"],
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "No fue posible conectar con la base de datos.",
    ]);
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}

