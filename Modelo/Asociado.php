<?php

require_once __DIR__ . '/../Config/conexion.php';

class Asociado
{
    private $conexion;
    private $datos;

    public function __construct($datos)
    {
        $this->conexion = (new Conexion())->conectar();
        $this->datos = $datos;
        $this->datos['password_hash'] = !empty($datos['password'])
            ? password_hash($datos['password'], PASSWORD_DEFAULT)
            : null;
    }

    public function guardar()
    {
        $sql = 'INSERT INTO asociados
                (documento, nombres, apellidos, fechaNacimiento, genero, direccionResidencia, ciudad, telefono, email, fechaIngreso, estado, password_hash)
                VALUES
                (:documento, :nombres, :apellidos, :fechaNacimiento, :genero, :direccionResidencia, :ciudad, :telefono, :email, :fechaIngreso, :estado, :password_hash)';

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':documento' => $this->datos['documento'],
            ':nombres' => $this->datos['nombres'],
            ':apellidos' => $this->datos['apellidos'],
            ':fechaNacimiento' => $this->datos['fechaNacimiento'],
            ':genero' => $this->datos['genero'],
            ':direccionResidencia' => $this->datos['direccionResidencia'],
            ':ciudad' => $this->datos['ciudad'],
            ':telefono' => $this->datos['telefono'],
            ':email' => $this->datos['email'],
            ':fechaIngreso' => $this->datos['fechaIngreso'],
            ':estado' => $this->datos['estado'],
            ':password_hash' => $this->datos['password_hash'],
        ]);
    }
}
