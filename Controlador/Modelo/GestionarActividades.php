<?php 
session_start();

header("Content-Type: application/json ;charset=utf-8");

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar las actividades");
    }
   
    $metodo = $_SERVER["REQUEST_METHOD"];

    if (! in_array($metodo, ["GET", "POST", "PUT", "DELETE"])) {
        http_response_code(405);
        throw new Exception("Método no permitido");
    }

    if ($metodo !== "GET" && $_SESSION["rol"] !== "admin") {
        http_response_code(403);
        throw new Exception("No tienes permisos para realizar esta acción");
    }

    require_once("../Modelo/Actividad.php");
    $actividad = new Actividad();
    
   switch ($metodo) {
     case "GET":
        $resultado= [
            "success" => true,
            "rol" => $_SESSION["rol"],
            "actividades" => $actividad->obtenerActividades();
        ]
        
   }    
    
}catch () {
    );
}