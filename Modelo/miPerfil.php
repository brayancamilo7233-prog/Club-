<?php
// Perfil del usuario que inició sesión (usado por Vista/perfil.html)
//   GET  -> devuelve sus datos
//   POST -> actualiza sus datos (el documento no se cambia: es la clave de la cuenta)

session_start();

header("Content-Type: application/json; charset=UTF-8");

function leerDatos()
{
    $datos = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($datos)) {
        http_response_code(400);
        throw new Exception("Los datos enviados no son válidos");
    }
    return $datos;
}

function validarPerfil($datos)
{
    $obligatorios = [
        "nombres" => "los nombres",
        "apellidos" => "los apellidos",
        "fechaNacimiento" => "la fecha de nacimiento",
        "genero" => "el género",
        "direccionResidencia" => "la dirección",
        "ciudad" => "la ciudad",
        "telefono" => "el teléfono",
        "email" => "el correo",
    ];

    foreach ($obligatorios as $campo => $nombre) {
        if (!isset($datos[$campo]) || !is_scalar($datos[$campo]) || trim((string) $datos[$campo]) === "") {
            http_response_code(400);
            throw new Exception("Debes ingresar $nombre");
        }
    }

    $fecha = DateTime::createFromFormat("!Y-m-d", $datos["fechaNacimiento"]);
    if (!$fecha || $fecha->format("Y-m-d") !== $datos["fechaNacimiento"]) {
        http_response_code(400);
        throw new Exception("La fecha de nacimiento no es válida");
    }

    if (!filter_var($datos["email"], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        throw new Exception("El correo electrónico no es válido");
    }

    return [
        ":nombres" => trim($datos["nombres"]),
        ":apellidos" => trim($datos["apellidos"]),
        ":fechaNacimiento" => $datos["fechaNacimiento"],
        ":genero" => trim($datos["genero"]),
        ":direccionResidencia" => trim($datos["direccionResidencia"]),
        ":ciudad" => trim($datos["ciudad"]),
        ":telefono" => trim((string) $datos["telefono"]),
        ":email" => strtolower(trim($datos["email"])),
    ];
}

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para ver tu perfil");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];
    if (!in_array($metodo, ["GET", "POST"], true)) {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    require_once __DIR__ . "/../Config/conexion.php";
    $conexion = (new Conexion())->conectar();
    $documento = $_SESSION["documento"];

    if ($metodo === "POST") {
        $valores = validarPerfil(leerDatos());
        $valores[":documento"] = $documento;

        $consulta = $conexion->prepare(
            "UPDATE asociados
             SET nombres = :nombres, apellidos = :apellidos, fechaNacimiento = :fechaNacimiento,
                 genero = :genero, direccionResidencia = :direccionResidencia, ciudad = :ciudad,
                 telefono = :telefono, email = :email
             WHERE documento = :documento"
        );
        $consulta->execute($valores);
    }

    // En GET y después de guardar se devuelven los datos actuales
    $consulta = $conexion->prepare(
        "SELECT documento, nombres, apellidos, fechaNacimiento, genero,
                direccionResidencia, ciudad, telefono, email
         FROM asociados
         WHERE documento = :documento"
    );
    $consulta->execute([":documento" => $documento]);
    $perfil = $consulta->fetch();

    if (!$perfil) {
        http_response_code(404);
        throw new Exception("No se encontraron los datos de tu cuenta");
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "mensaje" => $metodo === "POST" ? "Perfil actualizado correctamente" : "",
        "perfil" => $perfil,
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());
    if ($e->getCode() == 23000) {
        http_response_code(409);
        $mensaje = "Ese correo ya lo usa otro asociado";
    } else {
        http_response_code(500);
        $mensaje = "No fue posible consultar la base de datos";
    }
    echo json_encode(["success" => false, "mensaje" => $mensaje], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
