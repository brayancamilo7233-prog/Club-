<?php

require_once '../Modelo/Conexion.php';

try {

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("Método HTTP no permitido");
    }

    $documento = $_POST['documento'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $genero = $_POST['genero'];
    $direccionResidencia = $_POST['direccionResidencia'];
    $ciudad = $_POST['ciudad'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];

    $conexion = new Conexion();
    $pdo = $conexion->conectar();

    $sql = "INSERT INTO asociados
            (documento, nombre, apellido, fechaNacimiento, genero,
             direccionResidencia, ciudad, telefono, email)
            VALUES
            (:documento, :nombre, :apellido, :fechaNacimiento, :genero,
             :direccionResidencia, :ciudad, :telefono, :email)";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':documento', $documento);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':fechaNacimiento', $fechaNacimiento);
    $stmt->bindParam(':genero', $genero);
    $stmt->bindParam(':direccionResidencia', $direccionResidencia);
    $stmt->bindParam(':ciudad', $ciudad);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':email', $email);

    $stmt->execute();

    echo "Asociado registrado correctamente";

} catch (Exception $e) {

    echo "Error: " . $e->getMessage();
}