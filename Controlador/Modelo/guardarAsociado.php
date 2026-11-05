<?php
header("content-type: application/json; charset=UTF-8");
require_once _DIR_ . '/../Modelo/Perfil.php';
try{

    if($_SERVER['REQUEST_METHOD']){
        http_response_code(405);
        //Saltar a catch y mostrar el mensaje
        throw new Exception("Metodo HTTP no permitido", 405);
    }
    //Leer el contenido JSON de la solicitud
    $json = file_get_contents('php://input');
    //Decodificar el JSON en un array asociativo
    $datos = json_decode($json, true);
    if(!is_array($datos) || json_last_error() !== JSON_ERROR_NONE){
        http_response_code(400);
        throw new Exception("Datos inválidos", 400);
    }

    $campos=['documento', 'nombres', 'apellidos', 'fechaNacimiento', 'genero', 'direccionResidencia', 'ciudad', 'telefono', 'email'];

    $asociado = new Asociado($datos);

    $resultado = $asociado->guardar();
    if($resultado){
        http_response_code(201);
        echo json_encode(['mensaje' => 'Asociado guardado exitosamente']);
    } else {
        http_response_code(500);
        throw new Exception("Error al guardar el asociado", 500);
    }
    //error de codigo por metodo HTTP, datos inválidos o error al guardar el asociado
    catch (PDOException $e) {
        if ($e->getCode() == 23000) // Código de error para violación de clave única (duplicado)
            {
            http_response_code(409);// Código de error para conflicto
            echo json_encode(['error' => 'El asociado ya existe']);
        } else {
            http_response_code(500);// Código de error para error interno del servidor
            echo json_encode(['error' => 'Error al guardar el asociado: ' . $e->getMessage()]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}