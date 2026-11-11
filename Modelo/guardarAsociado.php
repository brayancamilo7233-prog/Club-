<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Validar que el método sea POST; si no lo es, lanzar excepción
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Método no permitido.');
    }

    // Leer y decodificar el JSON enviado por fetch
    $datos = json_decode(file_get_contents('php://input'), true);

    if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        throw new Exception('Datos inválidos.');
    }

    $campos = ['documento', 'nombres', 'apellidos', 'fechaNacimiento', 'genero', 'direccionResidencia', 'ciudad', 'telefono', 'email'];

    // Desde registro.html (crear cuenta) la contraseña es obligatoria
    $crearCuenta = isset($_GET['crearCuenta']);
    if ($crearCuenta) {
        $campos[] = 'password';
    }

    // Validar que todos los campos obligatorios estén presentes y no estén vacíos
    foreach ($campos as $campo) {
        if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === '') {
            http_response_code(400);
            throw new Exception("El campo: '$campo' es obligatorio.");
        }
    }

    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        throw new Exception("El campo: 'email' no es válido.");
    }

    if ($crearCuenta && strlen($datos['password']) < 8) {
        http_response_code(400);
        throw new Exception('La contraseña debe tener mínimo 8 caracteres.');
    }

    require_once __DIR__ . '/../Controlador/Asociado.php';

    $asociado = new Asociado($datos);

    if (!$asociado->guardarAsociado()) {
        http_response_code(500);
        throw new Exception('Error al guardar el asociado.');
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'mensaje' => $crearCuenta ? 'Cuenta creada correctamente. Ya puedes iniciar sesión.' : 'Asociado guardado correctamente.',
    ]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Violación de clave única (documento o email repetido)
        http_response_code(409);
        echo json_encode(["success" => false, "mensaje" => "Ya existe un asociado con ese documento o email."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
    }
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}
